<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The guard every permission is registered under.
     */
    protected string $guard = 'web';

    /**
     * Sync the permissions listed in config/permissions.php into the DB.
     *
     * Permissions present in the config but missing from the DB are created.
     * Permissions that exist in the DB but were removed from the config are
     * only reported (never deleted automatically, to avoid destroying role
     * assignments the moment a key is renamed).
     */
    public function run(): void
    {
        $configNames = collect(config('permissions'))
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        foreach ($configNames as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $this->guard,
            ]);
        }

        $stale = Permission::where('guard_name', $this->guard)
            ->whereNotIn('name', $configNames)
            ->orderBy('name')
            ->pluck('name');

        if ($stale->isNotEmpty()) {
            $this->command?->warn(sprintf(
                'Permissions in DB but missing from config/permissions.php: %s',
                $stale->implode(', '),
            ));
        }
    }
}
