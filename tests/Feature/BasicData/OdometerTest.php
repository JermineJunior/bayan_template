<?php

namespace Tests\Feature\BasicData;

use App\Models\OdometerLog;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OdometerTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_recording_a_reading(): void
    {
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.odometer.store', $vehicle), ['reading' => 100])
            ->assertRedirect(route('login'));
    }

    public function test_user_without_the_vehicle_edit_permission_cannot_record_a_reading(): void
    {
        $this->actingUser(['vehicles.view']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.odometer.store', $vehicle), ['reading' => 100])
            ->assertForbidden();
    }

    public function test_user_with_vehicle_edit_can_record_a_normal_reading(): void
    {
        $this->actingUser(['vehicles.view', 'vehicles.edit']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.odometer.store', $vehicle), ['reading' => 100])
            ->assertRedirect()
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('odometer_logs', [
            'vehicle_id' => $vehicle->id,
            'reading' => 100,
            'is_correction' => false,
        ]);
        $this->assertSame('100.00', $vehicle->fresh()->current_odometer);
    }

    public function test_reading_is_required_and_numeric(): void
    {
        $this->actingUser(['vehicles.view', 'vehicles.edit']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.odometer.store', $vehicle), ['reading' => ''])
            ->assertSessionHasErrors('reading');

        $this->assertDatabaseCount('odometer_logs', 0);
    }

    public function test_a_reading_lower_than_current_requires_a_correction(): void
    {
        $this->actingUser(['vehicles.view', 'vehicles.edit']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 500]);

        $this->post(route('vehicles.odometer.store', $vehicle), ['reading' => 100])
            ->assertSessionHasErrors('reading');

        $this->assertDatabaseCount('odometer_logs', 0);
    }

    public function test_a_correction_requires_a_note(): void
    {
        $this->actingUser(['vehicles.view', 'vehicles.edit', 'odometer.correct']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.odometer.store', $vehicle), [
            'reading' => 50,
            'is_correction' => 1,
            'note' => '',
        ])->assertSessionHasErrors('note');

        $this->assertDatabaseCount('odometer_logs', 0);
    }

    public function test_a_user_without_correct_permission_cannot_submit_a_correction(): void
    {
        $this->actingUser(['vehicles.view', 'vehicles.edit']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 500]);

        $this->post(route('vehicles.odometer.store', $vehicle), [
            'reading' => 100,
            'is_correction' => 1,
            'note' => 'قراءة خاطئة سابقة',
        ])->assertSessionHasErrors('reading');

        $this->assertDatabaseCount('odometer_logs', 0);
    }

    public function test_a_valid_correction_records_the_reading_and_updates_the_odometer(): void
    {
        $this->actingUser(['vehicles.view', 'vehicles.edit', 'odometer.correct']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 500]);

        $this->post(route('vehicles.odometer.store', $vehicle), [
            'reading' => 100,
            'is_correction' => 1,
            'note' => 'قراءة خاطئة سابقة',
        ])->assertRedirect();

        $this->assertDatabaseHas('odometer_logs', [
            'vehicle_id' => $vehicle->id,
            'reading' => 100,
            'is_correction' => true,
        ]);
        $this->assertSame('100.00', $vehicle->fresh()->current_odometer);
    }

    public function test_creating_a_vehicle_records_the_initial_odometer_reading(): void
    {
        $this->actingUser(['vehicles.view', 'vehicles.create']);

        $this->post(route('vehicles.store'), [
            'internal_number' => 'V-001',
            'plate_number' => 'ABC 123',
            'status' => 'active',
            'initial_odometer' => 1500,
        ])->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::where('internal_number', 'V-001')->firstOrFail();

        $this->assertDatabaseHas('odometer_logs', [
            'vehicle_id' => $vehicle->id,
            'reading' => 1500,
            'is_correction' => false,
        ]);
        $this->assertSame('1500.00', $vehicle->fresh()->current_odometer);
    }

    public function test_updating_a_vehicle_never_changes_the_odometer(): void
    {
        $vehicle = Vehicle::create([
            'internal_number' => 'V-001',
            'plate_number' => 'ABC 123',
            'current_odometer' => 1000,
        ]);
        $this->actingUser(['vehicles.view', 'vehicles.edit']);

        $this->put(route('vehicles.update', $vehicle), [
            'internal_number' => 'V-001',
            'plate_number' => 'ABC 123',
            'status' => 'active',
            'current_odometer' => 999999,
        ])->assertRedirect(route('vehicles.index'));

        $this->assertSame('1000.00', $vehicle->fresh()->current_odometer);
        $this->assertSame(0, OdometerLog::count());
    }
}
