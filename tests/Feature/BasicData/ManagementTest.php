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
        Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $this->actingUser(['basic-data.view']);

        $this->get(route('managements.index'))
            ->assertOk()
            ->assertSee('إدارة النقل');
    }

    public function test_user_with_basic_data_create_can_create_a_management(): void
    {
        $this->actingUser(['basic-data.view', 'basic-data.create']);

        $this->post(route('managements.store'), [
            'number' => 1,
            'name' => 'إدارة النقل',
        ])->assertRedirect(route('managements.index'));

        $this->assertDatabaseHas('management', ['number' => 1, 'name' => 'إدارة النقل']);
    }

    public function test_management_number_is_required_and_unique(): void
    {
        Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $this->actingUser(['basic-data.view', 'basic-data.create']);

        $this->post(route('managements.store'), [
            'number' => '',
            'name' => 'إدارة جديدة',
        ])->assertSessionHasErrors('number');

        $this->post(route('managements.store'), [
            'number' => 1,
            'name' => 'إدارة جديدة',
        ])->assertSessionHasErrors('number');

        $this->assertSame(1, Management::count());
    }

    public function test_management_name_is_required(): void
    {
        $this->actingUser(['basic-data.view', 'basic-data.create']);

        $this->post(route('managements.store'), [
            'number' => 1,
            'name' => '',
        ])->assertSessionHasErrors('name');

        $this->assertSame(0, Management::count());
    }

    public function test_user_with_basic_data_edit_can_update_a_management(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $this->actingUser(['basic-data.view', 'basic-data.edit']);

        $this->put(route('managements.update', $management), [
            'number' => 1,
            'name' => 'إدارة الصيانة',
        ])->assertRedirect(route('managements.index'));

        $this->assertDatabaseHas('management', ['id' => $management->id, 'name' => 'إدارة الصيانة']);
    }

    public function test_management_number_can_stay_the_same_when_updating(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $this->actingUser(['basic-data.view', 'basic-data.edit']);

        $this->put(route('managements.update', $management), [
            'number' => 1,
            'name' => 'إدارة النقل',
        ])->assertRedirect(route('managements.index'));
    }

    public function test_user_with_basic_data_delete_can_delete_a_management(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $this->actingUser(['basic-data.view', 'basic-data.delete']);

        $this->delete(route('managements.destroy', $management))
            ->assertRedirect(route('managements.index'));

        $this->assertDatabaseMissing('management', ['id' => $management->id]);
    }

    public function test_management_with_departments_cannot_be_deleted(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        Department::create(['number' => 1, 'name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['basic-data.view', 'basic-data.delete']);

        $this->delete(route('managements.destroy', $management))
            ->assertRedirect(route('managements.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('management', ['id' => $management->id]);
    }

    public function test_management_with_vehicles_cannot_be_deleted(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        Vehicle::forceCreate([
            'internal_number' => 'V-001',
            'plate_number' => 'ABC 123',
            'management_id' => $management->id,
        ]);
        $this->actingUser(['basic-data.view', 'basic-data.delete']);

        $this->delete(route('managements.destroy', $management))
            ->assertRedirect(route('managements.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('management', ['id' => $management->id]);
    }

    public function test_user_without_basic_data_create_cannot_store_a_management(): void
    {
        $this->actingUser(['basic-data.view']);

        $this->post(route('managements.store'), [
            'number' => 1,
            'name' => 'Sneaky',
        ])->assertForbidden();
    }
}
