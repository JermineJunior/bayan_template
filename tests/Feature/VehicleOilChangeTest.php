<?php

namespace Tests\Feature;

use App\Models\Oil;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOilChange;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleOilChangeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the permission catalogue and return an authenticated user with
     * the given permissions.
     *
     * @param  array<int, string>  $permissions
     */
    private function actingUser(array $permissions = []): User
    {
        $this->seed(PermissionSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        $this->actingAs($user);

        return $user;
    }

    private function makeVehicle(): Vehicle
    {
        return Vehicle::create([
            'internal_number' => 'V-001',
            'plate_number' => 'ABC 123',
        ]);
    }

    private function makeOil(array $overrides = []): Oil
    {
        return Oil::create(array_merge([
            'oil_name' => 'زيت محرك 10W-30',
            'oil_code' => 'ENG-10W30',
            'oil_type' => 'engine',
            'oil_life' => 10000,
        ], $overrides));
    }

    private function changePayload(array $overrides = []): array
    {
        return array_merge([
            'oil_id' => $this->makeOil()->id,
            'last_change' => '2026-01-15',
            'odometer_when_change' => 25000,
            'cost' => 150,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login_when_accessing_oil_change_pages(): void
    {
        $vehicle = $this->makeVehicle();

        $this->get(route('vehicles.oil-changes.create', $vehicle))->assertRedirect(route('login'));
        $this->post(route('vehicles.oil-changes.store', $vehicle), $this->changePayload())->assertRedirect(route('login'));
    }

    public function test_user_without_oil_change_permissions_cannot_access_oil_change_pages(): void
    {
        $this->actingUser(['vehicles.view']);
        $vehicle = $this->makeVehicle();

        $this->get(route('vehicles.oil-changes.create', $vehicle))->assertForbidden();
        $this->post(route('vehicles.oil-changes.store', $vehicle), $this->changePayload())->assertForbidden();
    }

    public function test_create_page_shows_the_vehicle_and_grouped_oils(): void
    {
        $this->actingUser(['oil-changes.view']);
        $vehicle = $this->makeVehicle();
        $this->makeOil();
        $this->makeOil([
            'oil_name' => 'زيت فرامل DOT4',
            'oil_code' => 'BRK-DOT4',
            'oil_type' => 'brake',
        ]);

        $this->get(route('vehicles.oil-changes.create', $vehicle))
            ->assertOk()
            ->assertSee('V-001')
            ->assertSee('ABC 123')
            ->assertSee('زيت محرك 10W-30')
            ->assertSee('زيت فرامل DOT4')
            ->assertSee('محرك')
            ->assertSee('فرامل');
    }

    public function test_user_with_oil_change_create_can_store_a_change_via_record(): void
    {
        $user = $this->actingUser(['oil-changes.view', 'oil-changes.create']);
        $vehicle = $this->makeVehicle();
        $payload = $this->changePayload();

        $this->post(route('vehicles.oil-changes.store', $vehicle), $payload)
            ->assertRedirect(route('vehicles.show', $vehicle))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('vehicle_oil_changes', [
            'vehicle_id' => $vehicle->id,
            'oil_id' => $payload['oil_id'],
            'last_change' => '2026-01-15',
            'odometer_when_change' => 25000,
            'cost' => 150.00,
            'next_change_odometer' => 35000,
            'recorded_by' => $user->id,
        ]);

        $change = VehicleOilChange::where('vehicle_id', $vehicle->id)->firstOrFail();
        $this->assertSame('150.00', $change->cost);

        $this->assertDatabaseHas('expenses', [
            'vehicle_id' => $vehicle->id,
            'expense_type' => 'oil',
            'amount' => 150.00,
            'expense_date' => '2026-01-15',
            'sourceable_type' => VehicleOilChange::class,
            'sourceable_id' => $change->id,
            'recorded_by' => $user->id,
        ]);
    }

    public function test_oil_change_fields_are_validated(): void
    {
        $this->actingUser(['oil-changes.view', 'oil-changes.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.oil-changes.store', $vehicle), [
            'oil_id' => 999,
            'last_change' => '',
            'odometer_when_change' => 'abc',
            'cost' => 'abc',
        ])->assertSessionHasErrors(['oil_id', 'last_change', 'odometer_when_change', 'cost']);

        $this->assertDatabaseCount('vehicle_oil_changes', 0);
    }

    public function test_cost_is_validated(): void
    {
        $this->actingUser(['oil-changes.view', 'oil-changes.create']);
        $vehicle = $this->makeVehicle();
        $oilId = $this->makeOil()->id;

        $this->post(route('vehicles.oil-changes.store', $vehicle), [
            'oil_id' => $oilId,
            'last_change' => '2026-01-15',
            'odometer_when_change' => 25000,
            'cost' => -5,
        ])->assertSessionHasErrors('cost');

        $this->post(route('vehicles.oil-changes.store', $vehicle), [
            'oil_id' => $oilId,
            'last_change' => '2026-01-15',
            'odometer_when_change' => 25000,
            'cost' => '',
        ])->assertSessionHasErrors('cost');

        $this->assertDatabaseCount('vehicle_oil_changes', 0);
    }

    public function test_last_change_cannot_be_in_the_future(): void
    {
        $this->actingUser(['oil-changes.view', 'oil-changes.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.oil-changes.store', $vehicle), $this->changePayload([
            'last_change' => '2099-01-15',
        ]))->assertSessionHasErrors('last_change');

        $this->assertDatabaseCount('vehicle_oil_changes', 0);
    }

    public function test_storing_an_oil_change_never_touches_the_vehicle_odometer(): void
    {
        $this->actingUser(['oil-changes.view', 'oil-changes.create']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 5000]);

        $this->post(route('vehicles.oil-changes.store', $vehicle), $this->changePayload())
            ->assertRedirect(route('vehicles.show', $vehicle));

        $this->assertSame('5000.00', $vehicle->fresh()->current_odometer);
    }

    public function test_vehicle_show_page_displays_the_oil_status_and_history(): void
    {
        $user = $this->actingUser(['vehicles.view', 'oil-changes.view', 'oil-changes.create']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 34000]);
        $oil = $this->makeOil();
        VehicleOilChange::record(
            $vehicle,
            $oil,
            '2026-01-15',
            25000,
            $user,
            150.0,
        );

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('حالة الزيوت')
            ->assertSee('سجل تغيير الزيوت')
            ->assertSee('زيت محرك 10W-30')
            ->assertSee('تسجيل تغيير زيت')
            ->assertSee('+1,000 كم');
    }

    public function test_overdue_oil_change_is_marked_overdue_on_the_vehicle_page(): void
    {
        $user = $this->actingUser(['vehicles.view', 'oil-changes.view']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 40000]);
        $oil = $this->makeOil();
        VehicleOilChange::record(
            $vehicle,
            $oil,
            '2026-01-15',
            25000,
            $user,
            150.0,
        );

        $change = $vehicle->oilChanges()->firstOrFail();

        $this->assertTrue($change->is_overdue);
        $this->assertSame(-10000.0, $change->remaining_change);

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('متأخرة');
    }
}
