<?php

namespace Database\Seeders;

use App\Services\SettingsService;
use App\Services\UserService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // Users are only ever created by an admin (user management step).
        // The initial admin uses a placeholder password — change it after
        // the first login once password management exists.
        app(UserService::class)->createUserWithRole([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => null,
            'password' => 'password',
        ], 'Admin');

        // Default app-wide settings, only created when absent so an existing
        // setup's values are never overwritten.
        $settings = app(SettingsService::class);

        if (! $settings->has('app_name')) {
            $settings->set('app_name', config('app.name', 'Vibe'));
        }
    }
}
