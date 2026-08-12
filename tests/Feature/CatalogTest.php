<?php

namespace Tests\Feature;

use App\Models\Filter;
use App\Models\Oil;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_accessing_the_catalog(): void
    {
        $this->get(route('catalog.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_oil_or_filter_permissions_cannot_access_the_catalog(): void
    {
        $this->actingUser(['vehicles.view']);

        $this->get(route('catalog.index'))->assertForbidden();
    }

    public function test_catalog_shows_both_oils_and_filters_tables(): void
    {
        $this->actingUser(['oils.view', 'filters.view']);
        Oil::create([
            'oil_name' => 'زيت محرك 10W-30',
            'oil_code' => 'ENG-10W30',
            'oil_type' => 'engine',
            'oil_life' => 10000,
        ]);
        Filter::create([
            'filter_name' => 'فلتر زيت محرك',
            'filter_code' => 'OIL-FLTR-01',
            'filter_type' => 'oil',
            'filter_life' => 10000,
        ]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('الزيوت والفلاتر')
            ->assertSee('زيت محرك 10W-30')
            ->assertSee('فلتر زيت محرك');
    }

    public function test_catalog_with_only_oils_permission_hides_the_filters_table(): void
    {
        $this->actingUser(['oils.view']);
        Filter::create([
            'filter_name' => 'فلتر زيت محرك',
            'filter_code' => 'OIL-FLTR-01',
            'filter_type' => 'oil',
            'filter_life' => 10000,
        ]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertDontSee('فلتر زيت محرك');
    }
}
