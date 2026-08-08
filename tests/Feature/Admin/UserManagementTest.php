<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\UserService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    /**
     * Seed permissions + roles and return the given role.
     */
    private function viewerRole(): Role
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        return Role::where('name', 'Viewer')->firstOrFail();
    }

    public function test_guest_is_redirected_to_login_when_accessing_user_management(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_user_management(): void
    {
        $this->actingUser();

        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_user_with_users_view_can_view_the_user_index(): void
    {
        $role = $this->viewerRole();
        $this->actingUser(['users.view']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'email' => null,
            'password' => 'secret123',
        ], $role);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('jane')
            ->assertSee('Viewer');
    }

    public function test_user_with_users_create_can_create_a_user_with_a_role(): void
    {
        $role = $this->viewerRole();
        $this->actingUser(['users.view', 'users.create']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => 'jane',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'role_id' => $role->id,
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('username', 'jane')->first();

        $this->assertNotNull($user);
        $this->assertSame('Jane Doe', $user->name);
        $this->assertSame('jane@example.com', $user->email);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertSame(['Viewer'], $user->getRoleNames()->all());
    }

    public function test_a_user_may_be_created_without_a_password_and_one_is_generated(): void
    {
        $role = $this->viewerRole();
        $this->actingUser(['users.view', 'users.create']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => 'jane',
            'role_id' => $role->id,
        ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $user = User::where('username', 'jane')->first();

        $this->assertNotNull($user);
        $this->assertNotEmpty($user->password);
        $this->assertNotSame('secret123', $user->password);
    }

    public function test_username_is_required(): void
    {
        $role = $this->viewerRole();
        $this->actingUser(['users.view', 'users.create']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => '',
            'role_id' => $role->id,
        ])->assertSessionHasErrors('username');

        $this->assertDatabaseMissing('users', ['username' => 'jane']);
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'taken']);
        $role = $this->viewerRole();
        $this->actingUser(['users.view', 'users.create']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => 'taken',
            'role_id' => $role->id,
        ])->assertSessionHasErrors('username');
    }

    public function test_email_must_be_valid_when_present(): void
    {
        $role = $this->viewerRole();
        $this->actingUser(['users.view', 'users.create']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => 'jane',
            'email' => 'not-an-email',
            'role_id' => $role->id,
        ])->assertSessionHasErrors('email');
    }

    public function test_role_is_required(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.create']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => 'jane',
        ])->assertSessionHasErrors('role_id');
    }

    public function test_role_must_exist(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.create']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => 'jane',
            'role_id' => 9999,
        ])->assertSessionHasErrors('role_id');
    }

    public function test_user_with_users_edit_can_update_a_user(): void
    {
        $adminRole = $this->viewerRole();
        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'email' => null,
            'password' => 'secret123',
        ], $adminRole);

        $newRole = Role::where('name', 'Admin')->firstOrFail();
        $this->actingUser(['users.view', 'users.edit']);

        $this->put(route('admin.users.update', $user), [
            'name' => 'Jane Smith',
            'username' => 'jane',
            'email' => 'jane@example.com',
            'role_id' => $newRole->id,
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();

        $this->assertSame('Jane Smith', $user->name);
        $this->assertSame('jane@example.com', $user->email);
        $this->assertSame(['Admin'], $user->getRoleNames()->all());
    }

    public function test_user_with_users_delete_can_delete_a_user(): void
    {
        $this->viewerRole();
        $admin = $this->actingUser(['users.view', 'users.delete']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'secret123',
        ], 'Viewer');

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_an_admin_cannot_delete_themselves(): void
    {
        $admin = $this->actingUser(['users.view', 'users.delete']);

        $this->delete(route('admin.users.destroy', $admin))->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_reset_a_users_password(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.edit']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'oldpass123',
        ], 'Viewer');

        $this->post(route('admin.users.reset-password', $user), [
            'password' => 'newpass456',
        ])
            ->assertRedirect(route('admin.users.edit', $user))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('newpass456', $user->fresh()->password));
    }

    public function test_reset_password_requires_a_minimum_password_length(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.edit']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'oldpass123',
        ], 'Viewer');

        $this->post(route('admin.users.reset-password', $user), [
            'password' => 'short',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('oldpass123', $user->fresh()->password));
    }

    public function test_user_without_users_create_cannot_store_a_user(): void
    {
        $role = $this->viewerRole();
        $this->actingUser(['users.view']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => 'jane',
            'role_id' => $role->id,
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'jane']);
    }

    public function test_user_without_users_edit_cannot_update_or_reset_password(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'oldpass123',
        ], 'Viewer');

        $this->put(route('admin.users.update', $user), [
            'name' => 'Jane Smith',
            'username' => 'jane',
            'role_id' => $user->roles->first()->id,
        ])->assertForbidden();

        $this->post(route('admin.users.reset-password', $user), [
            'password' => 'newpass456',
        ])->assertForbidden();

        $this->assertTrue(Hash::check('oldpass123', $user->fresh()->password));
    }

    public function test_user_without_users_delete_cannot_delete_a_user(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'secret123',
        ], 'Viewer');

        $this->delete(route('admin.users.destroy', $user))->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_created_user_can_log_in_with_their_own_credentials(): void
    {
        $role = $this->viewerRole();
        $this->actingUser(['users.view', 'users.create']);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'secret123',
            'role_id' => $role->id,
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('username', 'jane')->firstOrFail();

        Auth::logout();

        $this->post(route('login'), [
            'username' => 'jane',
            'password' => 'secret123',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_reset_password_allows_the_user_to_log_in_again(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.edit']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'oldpass123',
        ], 'Viewer');

        $this->post(route('admin.users.reset-password', $user), [
            'password' => 'newpass456',
        ])->assertRedirect(route('admin.users.edit', $user));

        Auth::logout();

        $this->post(route('login'), [
            'username' => 'jane',
            'password' => 'newpass456',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_list_shows_account_status(): void
    {
        $this->actingUser(['users.view']);

        User::factory()->create(['name' => 'Active User', 'username' => 'activeuser']);
        User::factory()->create(['name' => 'Inactive User', 'username' => 'inactiveuser', 'is_active' => false]);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('نشط')
            ->assertSee('معطل');
    }

    public function test_user_with_users_edit_can_deactivate_a_user(): void
    {
        $this->actingUser(['users.view', 'users.edit']);

        $user = User::factory()->create();

        $this->post(route('admin.users.deactivate', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_user_with_users_edit_can_reactivate_a_user(): void
    {
        $this->actingUser(['users.view', 'users.edit']);

        $user = User::factory()->create(['is_active' => false]);

        $this->post(route('admin.users.activate', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_user_without_users_edit_cannot_deactivate_or_reactivate(): void
    {
        $this->actingUser(['users.view']);

        $user = User::factory()->create(['is_active' => true]);

        $this->post(route('admin.users.deactivate', $user))->assertForbidden();
        $this->assertTrue($user->fresh()->is_active);

        $this->post(route('admin.users.activate', $user))->assertForbidden();
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_an_admin_cannot_deactivate_themselves(): void
    {
        $admin = $this->actingUser(['users.view', 'users.edit']);

        $this->post(route('admin.users.deactivate', $admin))->assertForbidden();

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_a_deactivated_user_cannot_log_in_even_with_correct_credentials(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.edit']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'secret123',
        ], 'Viewer');

        $this->post(route('admin.users.deactivate', $user))->assertRedirect(route('admin.users.index'));

        Auth::logout();

        $this->post(route('login'), [
            'username' => 'jane',
            'password' => 'secret123',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_a_deactivated_user_with_wrong_password_still_gets_the_generic_error(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.edit']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'secret123',
        ], 'Viewer');

        $this->post(route('admin.users.deactivate', $user))->assertRedirect(route('admin.users.index'));

        Auth::logout();

        $response = $this->post(route('login'), [
            'username' => 'jane',
            'password' => 'wrongpass',
        ])->assertSessionHasErrors('username');

        $this->assertStringNotContainsString('تم تعطيل هذا الحساب', $response->getSession()->get('errors')->first('username'));
        $this->assertGuest();
    }

    public function test_reactivating_a_user_restores_login_access(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.edit']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'secret123',
        ], 'Viewer');

        $this->post(route('admin.users.deactivate', $user))->assertRedirect(route('admin.users.index'));
        $this->post(route('admin.users.activate', $user))->assertRedirect(route('admin.users.index'));

        Auth::logout();

        $this->post(route('login'), [
            'username' => 'jane',
            'password' => 'secret123',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_deactivated_user_with_an_open_session_is_forced_logged_out(): void
    {
        $this->viewerRole();
        $this->actingUser(['users.view', 'users.edit']);

        $user = app(UserService::class)->createUserWithRole([
            'name' => 'Jane Doe',
            'username' => 'jane',
            'password' => 'secret123',
        ], 'Viewer');

        // The admin deactivates jane while she still holds an authenticated session.
        $this->post(route('admin.users.deactivate', $user))->assertRedirect(route('admin.users.index'));

        // Jane's next request (her open session) is rejected and logged out.
        Auth::login($user);

        $this->get(route('home'))->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
