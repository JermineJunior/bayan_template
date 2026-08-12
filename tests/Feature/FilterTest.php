<?php

namespace Tests\Feature;

use App\Models\Filter;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFilterChange;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterTest extends TestCase
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

    private function filterPayload(array $overrides = []): array
    {
        return array_merge([
            'filter_name' => 'فلتر زيت محرك',
            'filter_code' => 'OIL-FLTR-01',
            'filter_type' => 'oil',
            'filter_life' => 10000,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login_when_accessing_filter_pages(): void
    {
        $filter = Filter::create($this->filterPayload());

        $this->get(route('filters.index'))->assertRedirect(route('login'));
        $this->get(route('filters.create'))->assertRedirect(route('login'));
        $this->post(route('filters.store'), $this->filterPayload())->assertRedirect(route('login'));
        $this->get(route('filters.edit', $filter))->assertRedirect(route('login'));
        $this->put(route('filters.update', $filter), $this->filterPayload())->assertRedirect(route('login'));
        $this->delete(route('filters.destroy', $filter))->assertRedirect(route('login'));
    }

    public function test_user_without_filter_permissions_cannot_access_filter_pages(): void
    {
        $this->actingUser(['vehicles.view']);
        $filter = Filter::create($this->filterPayload());

        $this->get(route('filters.index'))->assertForbidden();
        $this->get(route('filters.create'))->assertForbidden();
        $this->post(route('filters.store'), $this->filterPayload())->assertForbidden();
        $this->get(route('filters.edit', $filter))->assertForbidden();
        $this->put(route('filters.update', $filter), $this->filterPayload())->assertForbidden();
        $this->delete(route('filters.destroy', $filter))->assertForbidden();
    }

    public function test_filters_index_redirects_to_the_combined_catalog(): void
    {
        $this->actingUser(['filters.view']);

        $this->get(route('filters.index'))->assertRedirect(route('catalog.index'));
    }

    public function test_user_with_filter_view_can_access_the_combined_catalog(): void
    {
        $this->actingUser(['filters.view']);
        Filter::create($this->filterPayload());

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('فلتر زيت محرك')
            ->assertSee('OIL-FLTR-01')
            ->assertSee('زيت')
            ->assertSee('10,000 كم');
    }

    public function test_create_page_renders(): void
    {
        $this->actingUser(['filters.view']);

        $this->get(route('filters.create'))
            ->assertOk()
            ->assertSee('إضافة فلتر جديد');
    }

    public function test_user_with_filter_create_can_store_a_filter(): void
    {
        $this->actingUser(['filters.view', 'filters.create']);

        $this->post(route('filters.store'), $this->filterPayload())
            ->assertRedirect(route('filters.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('filters', $this->filterPayload());
    }

    public function test_filter_fields_are_validated(): void
    {
        $this->actingUser(['filters.view', 'filters.create']);
        Filter::create($this->filterPayload(['filter_code' => 'OIL-FLTR-01']));

        $this->post(route('filters.store'), $this->filterPayload([
            'filter_name' => '',
            'filter_code' => 'OIL-FLTR-01',
            'filter_type' => 'bogus',
            'filter_life' => 0,
        ]))->assertSessionHasErrors(['filter_name', 'filter_code', 'filter_type', 'filter_life']);

        $this->assertDatabaseCount('filters', 1);
    }

    public function test_store_returns_the_new_filter_as_json_for_quick_add(): void
    {
        $this->actingUser(['filters.view', 'filters.create']);

        $this->postJson(route('filters.store'), $this->filterPayload())
            ->assertOk()
            ->assertJson([
                'filter_type' => 'oil',
            ])
            ->assertJsonPath('filter_name', 'فلتر زيت محرك')
            ->assertJsonPath('id', Filter::where('filter_code', 'OIL-FLTR-01')->firstOrFail()->id);

        $this->assertDatabaseHas('filters', ['filter_code' => 'OIL-FLTR-01']);
    }

    public function test_store_returns_validation_errors_as_json_for_quick_add(): void
    {
        $this->actingUser(['filters.view', 'filters.create']);

        $this->postJson(route('filters.store'), $this->filterPayload(['filter_code' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('filter_code');

        $this->assertDatabaseCount('filters', 0);
    }

    public function test_user_with_filter_edit_can_update_a_filter(): void
    {
        $this->actingUser(['filters.view', 'filters.edit']);
        $filter = Filter::create($this->filterPayload());

        $this->put(route('filters.update', $filter), $this->filterPayload([
            'filter_name' => 'فلتر هواء داخلي',
            'filter_life' => 15000,
        ]))
            ->assertRedirect(route('filters.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('filters', [
            'id' => $filter->id,
            'filter_name' => 'فلتر هواء داخلي',
            'filter_life' => 15000,
        ]);
    }

    public function test_edit_page_renders(): void
    {
        $this->actingUser(['filters.view']);
        $filter = Filter::create($this->filterPayload());

        $this->get(route('filters.edit', $filter))
            ->assertOk()
            ->assertSee('فلتر زيت محرك');
    }

    public function test_user_with_filter_delete_can_delete_an_unused_filter(): void
    {
        $this->actingUser(['filters.view', 'filters.delete']);
        $filter = Filter::create($this->filterPayload());

        $this->delete(route('filters.destroy', $filter))
            ->assertRedirect(route('filters.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseMissing('filters', ['id' => $filter->id]);
    }

    public function test_filter_with_change_history_cannot_be_deleted(): void
    {
        $user = $this->actingUser(['filters.view', 'filters.delete']);
        $vehicle = Vehicle::create([
            'internal_number' => 'V-001',
            'plate_number' => 'ABC 123',
        ]);
        $filter = Filter::create($this->filterPayload());
        VehicleFilterChange::record(
            $vehicle,
            $filter,
            '2026-01-10',
            1000,
            $user,
        );

        $this->delete(route('filters.destroy', $filter))
            ->assertRedirect(route('filters.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('filters', ['id' => $filter->id]);
        $this->assertDatabaseHas('vehicle_filter_changes', ['filter_id' => $filter->id]);
    }
}
