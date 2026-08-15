<?php

namespace Tests\Feature\BasicData;

use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelLogTest extends TestCase
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

    private function fuelPayload(array $overrides = []): array
    {
        return array_merge([
            'filled_at' => '2026-01-10 08:30:00',
            'fuel_type' => '',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'discount' => '',
            'odometer_reading' => 1000,
            'driver_id' => '',
            'station' => 'محطة الخرطوم',
            'invoice_number' => 'INV-100',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login_when_accessing_fuel_pages(): void
    {
        $vehicle = $this->makeVehicle();

        $this->get(route('fuel-logs.index'))->assertRedirect(route('login'));
        $this->get(route('vehicles.fuel-logs.create', $vehicle))->assertRedirect(route('login'));
        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload())->assertRedirect(route('login'));
        $this->delete(route('fuel-logs.destroy', 1))->assertRedirect(route('login'));
    }

    public function test_user_without_fuel_permissions_cannot_access_fuel_pages(): void
    {
        $this->actingUser(['vehicles.view']);
        $vehicle = $this->makeVehicle();

        $this->get(route('fuel-logs.index'))->assertForbidden();
        $this->get(route('vehicles.fuel-logs.create', $vehicle))->assertForbidden();
        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload())->assertForbidden();
    }

    public function test_user_with_fuel_view_can_access_the_fuel_logs_index(): void
    {
        $user = $this->actingUser(['fuel.view', 'drivers.view']);
        $vehicle = $this->makeVehicle();
        $driver = Driver::create([
            'full_name' => 'أحمد محمد',
            'national_id' => '123456789',
            'status' => 'active',
        ]);
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);

        $this->get(route('fuel-logs.index'))
            ->assertOk()
            ->assertSee('V-001')
            ->assertSee('أحمد محمد');
    }

    public function test_fuel_logs_index_can_be_filtered_by_vehicle_and_date_range(): void
    {
        $user = $this->actingUser(['fuel.view']);
        $vehicle = $this->makeVehicle();
        $other = Vehicle::create(['internal_number' => 'V-002', 'plate_number' => 'DEF 456']);
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);
        FuelLog::create([
            'vehicle_id' => $other->id,
            'filled_at' => '2026-02-15 08:30:00',
            'liters' => 40,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 800,
            'recorded_by' => $user->id,
        ]);

        $this->get(route('fuel-logs.index', ['vehicle_id' => $vehicle->id]))
            ->assertSee('2026-01-10 08:30')
            ->assertDontSee('2026-02-15 08:30');

        $this->get(route('fuel-logs.index', ['date_from' => '2026-02-01', 'date_to' => '2026-02-28']))
            ->assertSee('2026-02-15 08:30')
            ->assertDontSee('2026-01-10 08:30');
    }

    public function test_create_page_shows_the_vehicle_and_drivers(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create', 'drivers.view']);
        $vehicle = $this->makeVehicle();
        $driver = Driver::create([
            'full_name' => 'أحمد محمد',
            'national_id' => '123456789',
            'status' => 'active',
        ]);

        $this->get(route('vehicles.fuel-logs.create', $vehicle))
            ->assertOk()
            ->assertSee('V-001')
            ->assertSee('ABC 123')
            ->assertSee('أحمد محمد')
            ->assertSee('بدون سائق');
    }

    public function test_user_with_fuel_create_can_store_a_fuel_log(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload())
            ->assertRedirect(route('vehicles.show', $vehicle))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('fuel_logs', [
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'station' => 'محطة الخرطوم',
            'invoice_number' => 'INV-100',
        ]);
    }

    public function test_storing_a_fuel_log_never_touches_the_vehicle_odometer(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();
        $vehicle->update(['current_odometer' => 5000]);

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload())
            ->assertRedirect(route('vehicles.show', $vehicle));

        $this->assertSame('5000.00', $vehicle->fresh()->current_odometer);
    }

    public function test_total_value_is_never_accepted_as_input(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload(['total_value' => 999999]))
            ->assertRedirect(route('vehicles.show', $vehicle));

        $log = FuelLog::where('vehicle_id', $vehicle->id)->firstOrFail();
        $this->assertSame('125.00', $log->total_value);
    }

    public function test_discount_is_stored_and_reduces_the_total_value(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload(['discount' => 10]))
            ->assertRedirect(route('vehicles.show', $vehicle));

        $log = FuelLog::where('vehicle_id', $vehicle->id)->firstOrFail();
        $this->assertSame('10.00', $log->discount);
        $this->assertSame('115.00', $log->total_value);
    }

    public function test_discount_is_optional_and_ignored_when_empty(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload())
            ->assertRedirect(route('vehicles.show', $vehicle));

        $log = FuelLog::where('vehicle_id', $vehicle->id)->firstOrFail();
        $this->assertNull($log->discount);
        $this->assertSame('125.00', $log->total_value);
    }

    public function test_discount_is_validated(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload(['discount' => -5]))
            ->assertSessionHasErrors('discount');

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload(['discount' => 'abc']))
            ->assertSessionHasErrors('discount');

        $this->assertDatabaseCount('fuel_logs', 0);
    }

    public function test_fuel_type_is_stored(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload(['fuel_type' => 'diesel']))
            ->assertRedirect(route('vehicles.show', $vehicle));

        $log = FuelLog::where('vehicle_id', $vehicle->id)->firstOrFail();
        $this->assertSame('diesel', $log->fuel_type);
    }

    public function test_fuel_type_is_optional_and_validated(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload())
            ->assertRedirect(route('vehicles.show', $vehicle));

        $this->assertNull(FuelLog::where('vehicle_id', $vehicle->id)->firstOrFail()->fuel_type);

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload([
            'fuel_type' => 'bogus',
            'odometer_reading' => 1100,
        ]))->assertSessionHasErrors('fuel_type');
    }

    public function test_fuel_log_fields_are_validated(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload([
            'filled_at' => '',
            'liters' => 0,
            'price_per_liter' => -1,
            'odometer_reading' => '',
        ]))->assertSessionHasErrors(['filled_at', 'liters', 'price_per_liter', 'odometer_reading']);

        $this->assertDatabaseCount('fuel_logs', 0);
    }

    public function test_fuel_log_driver_must_exist(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload(['driver_id' => 999]))
            ->assertSessionHasErrors('driver_id');

        $this->assertDatabaseCount('fuel_logs', 0);
    }

    public function test_odometer_reading_must_be_greater_than_the_last_fuel_log_reading(): void
    {
        $this->actingUser(['fuel.view', 'fuel.create']);
        $vehicle = $this->makeVehicle();

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload([
            'filled_at' => '2026-01-10 08:30:00',
            'odometer_reading' => 1000,
        ]))->assertRedirect(route('vehicles.show', $vehicle));

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload([
            'filled_at' => '2026-01-15 08:30:00',
            'odometer_reading' => 900,
        ]))->assertSessionHasErrors('odometer_reading');

        $this->assertDatabaseCount('fuel_logs', 1);

        $this->post(route('vehicles.fuel-logs.store', $vehicle), $this->fuelPayload([
            'filled_at' => '2026-01-15 08:30:00',
            'odometer_reading' => 1100,
        ]))->assertRedirect(route('vehicles.show', $vehicle));

        $this->assertDatabaseCount('fuel_logs', 2);
    }

    public function test_consumption_rate_is_null_for_the_first_fuel_log(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->makeVehicle();

        $log = FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);

        $this->assertNull($log->distance_since_last_fill);
        $this->assertNull($log->consumption_rate);
    }

    public function test_consumption_rate_is_computed_from_the_previous_fuel_log(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->makeVehicle();
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);
        $second = FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-20 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 3000,
            'recorded_by' => $user->id,
        ]);

        $this->assertSame(2000.0, $second->distance_since_last_fill);
        $this->assertSame(40.0, $second->consumption_rate);
    }

    public function test_consumption_rate_is_null_when_the_distance_is_zero(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->makeVehicle();
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);
        $same = FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-20 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);

        $this->assertSame(0.0, $same->distance_since_last_fill);
        $this->assertNull($same->consumption_rate);
    }

    public function test_vehicle_fuel_cost_per_kilometer_is_computed_from_fuel_logs(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->makeVehicle();
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-20 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 3000,
            'recorded_by' => $user->id,
        ]);

        $this->assertSame(0.13, $vehicle->fuelCostPerKilometer());
    }

    public function test_vehicle_fuel_cost_per_kilometer_is_null_when_insufficient_data(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->makeVehicle();

        $this->assertNull($vehicle->fuelCostPerKilometer());

        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);

        $this->assertNull($vehicle->fuelCostPerKilometer());
    }

    public function test_vehicle_fuel_cost_per_kilometer_is_null_when_no_distance_was_traveled(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->makeVehicle();
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-20 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);

        $this->assertNull($vehicle->fuelCostPerKilometer());
    }

    public function test_vehicle_average_monthly_fuel_consumption_is_computed_from_fuel_logs(): void
    {
        $user = User::factory()->create();
        $vehicle = $this->makeVehicle();
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-02-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 3000,
            'recorded_by' => $user->id,
        ]);

        $this->assertSame(98.19, $vehicle->averageMonthlyFuelConsumption());
    }

    public function test_vehicle_average_monthly_fuel_consumption_is_null_when_no_logs(): void
    {
        $vehicle = $this->makeVehicle();

        $this->assertNull($vehicle->averageMonthlyFuelConsumption());
    }

    public function test_vehicle_show_page_displays_the_fuel_stat_cards(): void
    {
        $user = $this->actingUser(['vehicles.view', 'fuel.view']);
        $vehicle = $this->makeVehicle();
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);
        FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-02-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 3000,
            'recorded_by' => $user->id,
        ]);

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('تكلفة الكيلومتر')
            ->assertSee('متوسط الاستهلاك الشهري');
    }

    public function test_user_with_fuel_delete_can_delete_a_fuel_log(): void
    {
        $user = $this->actingUser(['fuel.view', 'fuel.delete']);
        $vehicle = $this->makeVehicle();
        $log = FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);

        $this->delete(route('fuel-logs.destroy', $log))
            ->assertRedirect()
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseMissing('fuel_logs', ['id' => $log->id]);
    }

    public function test_user_without_fuel_delete_cannot_delete_a_fuel_log(): void
    {
        $user = $this->actingUser(['fuel.view']);
        $vehicle = $this->makeVehicle();
        $log = FuelLog::create([
            'vehicle_id' => $vehicle->id,
            'filled_at' => '2026-01-10 08:30:00',
            'liters' => 50,
            'price_per_liter' => 2.5,
            'total_value' => 125.0,
            'odometer_reading' => 1000,
            'recorded_by' => $user->id,
        ]);

        $this->delete(route('fuel-logs.destroy', $log))->assertForbidden();

        $this->assertDatabaseHas('fuel_logs', ['id' => $log->id]);
    }
}
