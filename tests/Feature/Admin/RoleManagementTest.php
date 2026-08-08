<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_accessing_role_management(): void
    {
        $this->get(route('admin.roles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_role_management(): void
    {
        $this->actingUser();

        $this->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_user_with_roles_view_can_view_the_role_index(): void
    {
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $this->actingUser(['roles.view']);

        $this->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Admin');
    }

    public function test_user_with_roles_create_can_create_a_role_with_permissions(): void
    {
        $this->actingUser(['roles.view', 'roles.create']);

        $this->post(route('admin.roles.store'), [
            'name' => 'Editor',
            'permissions' => ['users.view', 'users.edit'],
        ])
            ->assertRedirect(route('admin.roles.index'));

        $role = Role::where('name', 'Editor')->first();

        $this->assertNotNull($role);
        $this->assertSame('web', $role->guard_name);
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('users.edit'));
        $this->assertFalse($role->hasPermissionTo('users.delete'));
    }

    public function test_a_role_may_be_created_without_permissions(): void
    {
        $this->actingUser(['roles.view', 'roles.create']);

        $this->post(route('admin.roles.store'), [
            'name' => 'Empty',
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertSame(0, Role::where('name', 'Empty')->first()->permissions->count());
    }

    public function test_role_name_is_required(): void
    {
        $this->actingUser(['roles.view', 'roles.create']);

        $this->post(route('admin.roles.store'), [
            'name' => '',
        ])->assertSessionHasErrors('name');

        $this->assertSame(0, Role::count());
    }

    public function test_role_name_must_be_unique(): void
    {
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $this->actingUser(['roles.view', 'roles.create']);

        $this->post(route('admin.roles.store'), [
            'name' => 'Admin',
        ])->assertSessionHasErrors('name');
    }

    public function test_permissions_must_come_from_the_catalogue(): void
    {
        $this->actingUser(['roles.view', 'roles.create']);

        $this->post(route('admin.roles.store'), [
            'name' => 'Hacker',
            'permissions' => ['does.not.exist'],
        ])->assertSessionHasErrors('permissions.0');

        $this->assertSame(0, Role::count());
    }

    public function test_user_with_roles_edit_can_update_a_role(): void
    {
        $this->actingUser(['roles.view', 'roles.edit']);

        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $role->givePermissionTo('users.view');

        $this->put(route('admin.roles.update', $role), [
            'name' => 'Senior Editor',
            'permissions' => ['users.view', 'users.edit', 'users.delete'],
        ])
            ->assertRedirect(route('admin.roles.index'));

        $role->refresh();

        $this->assertSame('Senior Editor', $role->name);
        $this->assertTrue($role->hasPermissionTo('users.edit'));
        $this->assertTrue($role->hasPermissionTo('users.delete'));
    }

    public function test_user_with_roles_delete_can_delete_a_role(): void
    {
        $role = Role::create(['name' => 'Temp', 'guard_name' => 'web']);
        $this->actingUser(['roles.view', 'roles.delete']);

        $this->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_user_without_roles_create_cannot_store_a_role(): void
    {
        $this->actingUser(['roles.view']);

        $this->post(route('admin.roles.store'), [
            'name' => 'Sneaky',
        ])->assertForbidden();
    }

    public function test_user_without_roles_delete_cannot_delete_a_role(): void
    {
        $role = Role::create(['name' => 'Temp', 'guard_name' => 'web']);
        $this->actingUser(['roles.view']);

        $this->delete(route('admin.roles.destroy', $role))->assertForbidden();

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_user_without_roles_edit_cannot_update_a_role(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $this->actingUser(['roles.view']);

        $this->put(route('admin.roles.update', $role), [
            'name' => 'Hacker',
        ])->assertForbidden();

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Editor']);
    }
}
