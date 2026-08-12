<?php

namespace Tests\Feature;

use App\Models\Filter;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFilterChange;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleFilterChangeTest extends TestCase
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

    private function makeFilter(array $overrides = []): Filter
    {
        return Filter::create(array_merge([
            'filter_name' => 'فلتر زيت محرك',
            'filter_code' => 'OIL-FLTR-01',
            'filter_type' => 'oil',
            'filter_life' => 10000,
        ], $overrides));
    }

    private function changePayload(array $overrides = []): array
    {
        return array_merge([
            'filter_id' => $this->makeFilter()->id,
            'last_change' => '2026-01-15',
            'odometer_when_change' => 25000,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login_when_accessing_filter_change_pages(): void
    {
        $vehicle = $this->makeVehicle();

        $this->get(route('vehicles.filter-changes.create', $vehicle))->assertRedirect(route('login'));
        $this->post(route('vehicles.filter-changes.store', $vehicle), $this->changePayload())->assertRedirect(route('login'));
    }

    public function test_user_without_filter_change_permissions_cannot_access_filter_change_pages(): void
    {
        $this->actingUser(['vehicles.view']);
        $vehicle = $this->makeVehicle();

        $this->get(route('vehicles.filter-changes.create', $vehicle))->assertForbidden();
        $this->post(route('vehicles.filter-changes.store', $vehicle), $this->changePayload())->assertForbidden();
    }

    public function test_create_page_shows_the_vehicle_and_grouped_filters(): void
    {
        $this->actingUser(['filter-changes.view']);
        $vehicle = $this->makeVehicle();
        $this->makeFilter();
        $this->makeFilter([
            'filter_name' => 'فلتر هواء خارجي',
            'filter_code' => 'AIR-FLTR-01',
            'filter_type' => 'air',
        ]);

        $this->get(route('vehicles.filter-changes.create', $vehicle))
            ->assertOk()
            ->assertSee('V-001')
            ->assertSee('ABC 123')
            ->assertSee('فلتر زيت محرك')
            ->assertSee('فلتر هواء خارجي')
            ->assertSee('زيت')
            ->assertSee('هواء');
    }

    public function test_user_with_filter_change_create_can_store_a_change_via_record(): void
    {
        $user = $this->actingUser(['filter-changes.view', 'filter-changes.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.filter-changes.store', $vehicle), $this->changePayload())
            ->assertRedirect(route('vehicles.show', $vehicle))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('vehicle_filter_changes', [
            'vehicle_id' => $vehicle->id,
            'filter_id' => $this->changePayload()['filter_id'],
            'last_change' => '2026-01-15',
            'odometer_when_change' => 25000,
            'next_change_odometer' => 35000,
            'recorded_by' => $user->id,
        ]);
    }

    public function test_filter_change_fields_are_validated(): void
    {
        $this->actingUser(['filter-changes.view', 'filter-changes.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.filter-changes.store', $vehicle), [
            'filter_id' => 999,
            'last_change' => '',
            'odometer_when_change' => 'abc',
        ])->assertSessionHasErrors(['filter_id', 'last_change', 'odometer_when_change']);

        $this->assertDatabaseCount('vehicle_filter_changes', 0);
    }

    public function test_last_change_cannot_be_in_the_future(): void
    {
        $this->actingUser(['filter-changes.view', 'filter-changes.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.filter-changes.store', $vehicle), $this->changePayload([
            'last_change' => '2099-01-15',
        ]))->assertSessionHasErrors('last_change');

        $this->assertDatabaseCount('vehicle_filter_changes', 0);
    }

    public function test_storing_a_filter_change_never_touches_the_vehicle_odometer(): void
    {
        $this->actingUser(['filter-changes.view', 'filter-changes.create']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 5000]);

        $this->post(route('vehicles.filter-changes.store', $vehicle), $this->changePayload())
            ->assertRedirect(route('vehicles.show', $vehicle));

        $this->assertSame('5000.00', $vehicle->fresh()->current_odometer);
    }

    public function test_vehicle_show_page_displays_the_filter_status_and_history(): void
    {
        $user = $this->actingUser(['vehicles.view', 'filter-changes.view', 'filter-changes.create']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 34000]);
        $filter = $this->makeFilter();
        VehicleFilterChange::record(
            $vehicle,
            $filter,
            '2026-01-15',
            25000,
            $user,
        );

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('حالة الفلاتر')
            ->assertSee('سجل تغيير الفلاتر')
            ->assertSee('فلتر زيت محرك')
            ->assertSee('تسجيل تغيير فلتر')
            ->assertSee('+1,000 كم');
    }

    public function test_overdue_filter_change_is_marked_overdue_on_the_vehicle_page(): void
    {
        $user = $this->actingUser(['vehicles.view', 'filter-changes.view']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 40000]);
        $filter = $this->makeFilter();
        VehicleFilterChange::record(
            $vehicle,
            $filter,
            '2026-01-15',
            25000,
            $user,
        );

        $change = $vehicle->filterChanges()->firstOrFail();

        $this->assertTrue($change->is_overdue);
        $this->assertSame(-10000.0, $change->remaining_change);

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('متأخرة');
    }
}
