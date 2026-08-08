<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_seeder_creates_every_permission_from_config(): void
    {
        $this->seed(PermissionSeeder::class);

        $expected = collect(config('permissions'))->flatten()->unique()->sort()->values();

        $this->assertSame(
            $expected->all(),
            Permission::orderBy('name')->pluck('name')->all(),
        );
    }

    public function test_permission_seeder_does_not_delete_permissions_removed_from_config(): void
    {
        Permission::create(['name' => 'legacy.flag', 'guard_name' => 'web']);

        $this->seed(PermissionSeeder::class);

        $this->assertDatabaseHas('permissions', ['name' => 'legacy.flag', 'guard_name' => 'web']);
    }

    public function test_role_seeder_creates_admin_and_viewer_roles(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = Role::where('name', 'Admin')->first();
        $viewer = Role::where('name', 'Viewer')->first();

        $this->assertNotNull($admin);
        $this->assertNotNull($viewer);
        $this->assertSame(collect(config('permissions'))->flatten()->unique()->count(), $admin->permissions->count());
        $this->assertSame(
            Permission::where('name', 'like', '%.view')->pluck('name')->sort()->all(),
            $viewer->permissions->pluck('name')->sort()->all(),
        );
    }

    public function test_a_user_holds_exactly_one_role(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $user = User::factory()->create();
        $service = app(UserService::class);

        $service->assignRole($user, 'Viewer');
        $service->assignRole($user, 'Admin');

        $this->assertSame(['Admin'], $user->fresh()->getRoleNames()->all());
    }

    public function test_a_user_inherits_permissions_from_their_role(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $user = User::factory()->create();
        app(UserService::class)->assignRole($user, 'Viewer');

        $this->assertTrue($user->can('users.view'));
        $this->assertTrue($user->can('roles.view'));
        $this->assertFalse($user->can('users.create'));
        $this->assertFalse($user->can('settings.edit'));
    }

    public function test_the_database_seeder_creates_the_admin_with_the_admin_role(): void
    {
        $this->seed();

        $admin = User::where('username', 'admin')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Admin'));
        $this->assertTrue($admin->can('users.delete'));
    }
}
