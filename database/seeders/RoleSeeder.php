<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The guard every role is registered under.
     */
    protected string $guard = 'web';

    /**
     * Create the example roles and re-sync their permissions.
     *
     * Run again (e.g. after adding a permission in config/permissions.php)
     * to re-apply the "all permissions" / "*.view permissions" rules.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => $this->guard,
        ]);
        $admin->syncPermissions(Permission::all());

        $viewer = Role::firstOrCreate([
            'name' => 'Viewer',
            'guard_name' => $this->guard,
        ]);
        $viewer->syncPermissions(
            Permission::where('name', 'like', '%.view')->pluck('name')->all(),
        );
    }
}
