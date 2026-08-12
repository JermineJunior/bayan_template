<?php

namespace Tests\Feature\BasicData;

use App\Models\Driver;
use App\Models\DriverViolation;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverViolationTest extends TestCase
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

    private function makeDriver(): Driver
    {
        return Driver::create([
            'full_name' => 'أحمد محمد',
            'national_id' => '123456789',
            'status' => 'active',
        ]);
    }

    private function violationPayload(array $overrides = []): array
    {
        return array_merge([
            'violation_date' => '2026-01-15',
            'description' => 'تجاوز السرعة',
            'amount' => '150.00',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login_when_accessing_violation_pages(): void
    {
        $driver = $this->makeDriver();

        $this->get(route('drivers.violations.create', $driver))->assertRedirect(route('login'));
        $this->post(route('drivers.violations.store', $driver), $this->violationPayload())->assertRedirect(route('login'));
        $this->delete(route('violations.destroy', 1))->assertRedirect(route('login'));
    }

    public function test_user_without_violations_permissions_cannot_access_violation_pages(): void
    {
        $this->actingUser(['drivers.view']);
        $driver = $this->makeDriver();

        $this->get(route('drivers.violations.create', $driver))->assertForbidden();
        $this->post(route('drivers.violations.store', $driver), $this->violationPayload())->assertForbidden();
    }

    public function test_create_page_shows_the_driver(): void
    {
        $this->actingUser(['violations.view', 'violations.create']);
        $driver = $this->makeDriver();

        $this->get(route('drivers.violations.create', $driver))
            ->assertOk()
            ->assertSee('أحمد محمد')
            ->assertSee('123456789');
    }

    public function test_user_with_violations_create_can_store_a_violation(): void
    {
        $user = $this->actingUser(['violations.view', 'violations.create']);
        $driver = $this->makeDriver();

        $this->post(route('drivers.violations.store', $driver), $this->violationPayload())
            ->assertRedirect(route('drivers.show', $driver))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('driver_violations', [
            'driver_id' => $driver->id,
            'violation_date' => '2026-01-15',
            'description' => 'تجاوز السرعة',
            'amount' => '150.00',
            'recorded_by' => $user->id,
        ]);
    }

    public function test_violation_fields_are_validated(): void
    {
        $this->actingUser(['violations.view', 'violations.create']);
        $driver = $this->makeDriver();

        $this->post(route('drivers.violations.store', $driver), [
            'violation_date' => '',
            'description' => '',
            'amount' => 'abc',
        ])->assertSessionHasErrors(['violation_date', 'description', 'amount']);

        $this->assertDatabaseCount('driver_violations', 0);
    }

    public function test_violation_date_cannot_be_in_the_future(): void
    {
        $this->actingUser(['violations.view', 'violations.create']);
        $driver = $this->makeDriver();

        $this->post(route('drivers.violations.store', $driver), $this->violationPayload([
            'violation_date' => '2099-01-15',
        ]))->assertSessionHasErrors('violation_date');

        $this->assertDatabaseCount('driver_violations', 0);
    }

    public function test_amount_is_optional_and_stored_as_null_when_empty(): void
    {
        $this->actingUser(['violations.view', 'violations.create']);
        $driver = $this->makeDriver();

        $this->post(route('drivers.violations.store', $driver), $this->violationPayload([
            'amount' => '',
        ]))->assertRedirect(route('drivers.show', $driver));

        $violation = DriverViolation::where('driver_id', $driver->id)->firstOrFail();
        $this->assertNull($violation->amount);
    }

    public function test_amount_cannot_be_negative(): void
    {
        $this->actingUser(['violations.view', 'violations.create']);
        $driver = $this->makeDriver();

        $this->post(route('drivers.violations.store', $driver), $this->violationPayload([
            'amount' => '-10',
        ]))->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('driver_violations', 0);
    }

    public function test_user_with_violations_delete_can_delete_a_violation(): void
    {
        $user = $this->actingUser(['violations.view', 'violations.delete']);
        $driver = $this->makeDriver();
        $violation = DriverViolation::create([
            'driver_id' => $driver->id,
            'violation_date' => '2026-01-15',
            'description' => 'تجاوز السرعة',
            'amount' => 150.00,
            'recorded_by' => $user->id,
        ]);

        $this->delete(route('violations.destroy', $violation))
            ->assertRedirect()
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseMissing('driver_violations', ['id' => $violation->id]);
    }

    public function test_user_without_violations_delete_cannot_delete_a_violation(): void
    {
        $user = $this->actingUser(['violations.view']);
        $driver = $this->makeDriver();
        $violation = DriverViolation::create([
            'driver_id' => $driver->id,
            'violation_date' => '2026-01-15',
            'description' => 'تجاوز السرعة',
            'amount' => 150.00,
            'recorded_by' => $user->id,
        ]);

        $this->delete(route('violations.destroy', $violation))->assertForbidden();

        $this->assertDatabaseHas('driver_violations', ['id' => $violation->id]);
    }

    public function test_driver_show_page_displays_the_violations_panel(): void
    {
        $user = $this->actingUser(['drivers.view', 'violations.view', 'violations.create']);
        $driver = $this->makeDriver();
        DriverViolation::create([
            'driver_id' => $driver->id,
            'violation_date' => '2026-01-15',
            'description' => 'تجاوز السرعة',
            'amount' => 150.00,
            'recorded_by' => $user->id,
        ]);

        $this->get(route('drivers.show', $driver))
            ->assertOk()
            ->assertSee('سجل المخالفات')
            ->assertSee('تجاوز السرعة')
            ->assertSee('150.00')
            ->assertSee('تسجيل مخالفة');
    }
}
