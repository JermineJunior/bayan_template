<?php

namespace Tests\Feature;

use App\Models\Oil;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOilChange;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OilTest extends TestCase
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

    private function oilPayload(array $overrides = []): array
    {
        return array_merge([
            'oil_name' => 'زيت محرك 10W-30',
            'oil_code' => 'ENG-10W30',
            'oil_type' => 'engine',
            'oil_life' => 10000,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login_when_accessing_oil_pages(): void
    {
        $oil = Oil::create($this->oilPayload());

        $this->get(route('oils.index'))->assertRedirect(route('login'));
        $this->get(route('oils.create'))->assertRedirect(route('login'));
        $this->post(route('oils.store'), $this->oilPayload())->assertRedirect(route('login'));
        $this->get(route('oils.edit', $oil))->assertRedirect(route('login'));
        $this->put(route('oils.update', $oil), $this->oilPayload())->assertRedirect(route('login'));
        $this->delete(route('oils.destroy', $oil))->assertRedirect(route('login'));
    }

    public function test_user_without_oil_permissions_cannot_access_oil_pages(): void
    {
        $this->actingUser(['vehicles.view']);
        $oil = Oil::create($this->oilPayload());

        $this->get(route('oils.index'))->assertForbidden();
        $this->get(route('oils.create'))->assertForbidden();
        $this->post(route('oils.store'), $this->oilPayload())->assertForbidden();
        $this->get(route('oils.edit', $oil))->assertForbidden();
        $this->put(route('oils.update', $oil), $this->oilPayload())->assertForbidden();
        $this->delete(route('oils.destroy', $oil))->assertForbidden();
    }

    public function test_oils_index_redirects_to_the_combined_catalog(): void
    {
        $this->actingUser(['oils.view']);

        $this->get(route('oils.index'))->assertRedirect(route('catalog.index'));
    }

    public function test_user_with_oil_view_can_access_the_combined_catalog(): void
    {
        $this->actingUser(['oils.view']);
        Oil::create($this->oilPayload());

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('زيت محرك 10W-30')
            ->assertSee('ENG-10W30')
            ->assertSee('محرك')
            ->assertSee('10,000 كم');
    }

    public function test_create_page_renders(): void
    {
        $this->actingUser(['oils.view']);

        $this->get(route('oils.create'))
            ->assertOk()
            ->assertSee('إضافة زيت جديد');
    }

    public function test_user_with_oil_create_can_store_an_oil(): void
    {
        $this->actingUser(['oils.view', 'oils.create']);

        $this->post(route('oils.store'), $this->oilPayload())
            ->assertRedirect(route('oils.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('oils', $this->oilPayload());
    }

    public function test_oil_fields_are_validated(): void
    {
        $this->actingUser(['oils.view', 'oils.create']);
        Oil::create($this->oilPayload(['oil_code' => 'ENG-10W30']));

        $this->post(route('oils.store'), $this->oilPayload([
            'oil_name' => '',
            'oil_code' => 'ENG-10W30',
            'oil_type' => 'bogus',
            'oil_life' => 0,
        ]))->assertSessionHasErrors(['oil_name', 'oil_code', 'oil_type', 'oil_life']);

        $this->assertDatabaseCount('oils', 1);
    }

    public function test_store_returns_the_new_oil_as_json_for_quick_add(): void
    {
        $this->actingUser(['oils.view', 'oils.create']);

        $this->postJson(route('oils.store'), $this->oilPayload())
            ->assertOk()
            ->assertJson([
                'oil_type' => 'engine',
            ])
            ->assertJsonPath('oil_name', 'زيت محرك 10W-30')
            ->assertJsonPath('id', Oil::where('oil_code', 'ENG-10W30')->firstOrFail()->id);

        $this->assertDatabaseHas('oils', ['oil_code' => 'ENG-10W30']);
    }

    public function test_store_returns_validation_errors_as_json_for_quick_add(): void
    {
        $this->actingUser(['oils.view', 'oils.create']);

        $this->postJson(route('oils.store'), $this->oilPayload(['oil_code' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('oil_code');

        $this->assertDatabaseCount('oils', 0);
    }

    public function test_user_with_oil_edit_can_update_an_oil(): void
    {
        $this->actingUser(['oils.view', 'oils.edit']);
        $oil = Oil::create($this->oilPayload());

        $this->put(route('oils.update', $oil), $this->oilPayload([
            'oil_name' => 'زيت محرك 5W-40',
            'oil_life' => 12000,
        ]))
            ->assertRedirect(route('oils.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('oils', [
            'id' => $oil->id,
            'oil_name' => 'زيت محرك 5W-40',
            'oil_life' => 12000,
        ]);
    }

    public function test_edit_page_renders(): void
    {
        $this->actingUser(['oils.view']);
        $oil = Oil::create($this->oilPayload());

        $this->get(route('oils.edit', $oil))
            ->assertOk()
            ->assertSee('زيت محرك 10W-30');
    }

    public function test_user_with_oil_delete_can_delete_an_unused_oil(): void
    {
        $this->actingUser(['oils.view', 'oils.delete']);
        $oil = Oil::create($this->oilPayload());

        $this->delete(route('oils.destroy', $oil))
            ->assertRedirect(route('oils.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseMissing('oils', ['id' => $oil->id]);
    }

    public function test_oil_with_change_history_cannot_be_deleted(): void
    {
        $user = $this->actingUser(['oils.view', 'oils.delete']);
        $vehicle = Vehicle::create([
            'internal_number' => 'V-001',
            'plate_number' => 'ABC 123',
        ]);
        $oil = Oil::create($this->oilPayload());
        VehicleOilChange::record(
            $vehicle,
            $oil,
            '2026-01-10',
            1000,
            $user,
            150.0,
        );

        $this->delete(route('oils.destroy', $oil))
            ->assertRedirect(route('oils.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('oils', ['id' => $oil->id]);
        $this->assertDatabaseHas('vehicle_oil_changes', ['oil_id' => $oil->id]);
    }
}
