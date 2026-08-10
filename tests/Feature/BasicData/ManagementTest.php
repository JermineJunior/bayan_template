<?php

namespace Tests\Feature\BasicData;

use App\Models\Department;
use App\Models\Management;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_accessing_managements(): void
    {
        $this->get(route('managements.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_managements(): void
    {
        $this->actingUser();

        $this->get(route('managements.index'))->assertForbidden();
    }

    public function test_user_with_basic_data_view_can_view_the_management_index(): void
    {
        Management::create(['name' => 'إدارة النقل']);
        $this->actingUser(['managements.view']);

        $this->get(route('managements.index'))
            ->assertOk()
            ->assertSee('إدارة النقل');
    }

    public function test_user_with_basic_data_create_can_create_a_management(): void
    {
        $this->actingUser(['managements.view', 'managements.create']);

        $this->post(route('managements.store'), [
            'name' => 'إدارة النقل',
            'status' => 'active',
        ])->assertRedirect(route('managements.index'));

        $this->assertDatabaseHas('management', ['name' => 'إدارة النقل', 'status' => 'active']);
    }

    public function test_management_status_is_required_and_must_be_valid(): void
    {
        $this->actingUser(['managements.view', 'managements.create']);

        $this->post(route('managements.store'), [
            'name' => 'إدارة النقل',
            'status' => '',
        ])->assertSessionHasErrors('status');

        $this->post(route('managements.store'), [
            'name' => 'إدارة النقل',
            'status' => 'bogus',
        ])->assertSessionHasErrors('status');

        $this->assertSame(0, Management::count());
    }

    public function test_management_name_is_required(): void
    {
        $this->actingUser(['managements.view', 'managements.create']);

        $this->post(route('managements.store'), [
            'name' => '',
            'status' => 'active',
        ])->assertSessionHasErrors('name');

        $this->assertSame(0, Management::count());
    }

    public function test_management_status_defaults_to_active(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);

        $this->assertDatabaseHas('management', ['name' => 'إدارة النقل', 'status' => 'active']);
        $this->assertSame('active', $management->fresh()->status);
    }

    public function test_user_with_basic_data_edit_can_update_a_management(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        $this->actingUser(['managements.view', 'managements.edit']);

        $this->put(route('managements.update', $management), [
            'name' => 'إدارة الصيانة',
            'status' => 'inactive',
        ])->assertRedirect(route('managements.index'));

        $this->assertDatabaseHas('management', [
            'id' => $management->id,
            'name' => 'إدارة الصيانة',
            'status' => 'inactive',
        ]);
    }

    public function test_user_with_basic_data_delete_can_delete_a_management(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        $this->actingUser(['managements.view', 'managements.delete']);

        $this->delete(route('managements.destroy', $management))
            ->assertRedirect(route('managements.index'));

        $this->assertDatabaseMissing('management', ['id' => $management->id]);
    }

    public function test_management_with_departments_cannot_be_deleted(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        Department::create(['name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['managements.view', 'managements.delete']);

        $this->delete(route('managements.destroy', $management))
            ->assertRedirect(route('managements.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('management', ['id' => $management->id]);
    }

    public function test_management_with_vehicles_cannot_be_deleted(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        Vehicle::forceCreate([
            'internal_number' => 'V-001',
            'plate_number' => 'ABC 123',
            'management_id' => $management->id,
        ]);
        $this->actingUser(['managements.view', 'managements.delete']);

        $this->delete(route('managements.destroy', $management))
            ->assertRedirect(route('managements.index'))
            ->assertSessionHas('flasher::envelopes');

        $this->assertDatabaseHas('management', ['id' => $management->id]);
    }

    public function test_user_without_basic_data_create_cannot_store_a_management(): void
    {
        $this->actingUser(['managements.view']);

        $this->post(route('managements.store'), [
            'name' => 'Sneaky',
            'status' => 'active',
        ])->assertForbidden();
    }
}
