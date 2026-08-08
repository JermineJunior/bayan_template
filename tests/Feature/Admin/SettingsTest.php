<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\SettingsService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_accessing_settings(): void
    {
        $this->get(route('admin.settings.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_settings(): void
    {
        $this->actingUser();

        $this->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_user_with_settings_edit_can_view_the_settings_screen(): void
    {
        $this->actingUser(['settings.edit']);

        $this->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('إعدادات التطبيق')
            ->assertSee('Vibe');
    }

    public function test_user_with_settings_edit_can_update_the_app_name(): void
    {
        $this->actingUser(['settings.edit']);

        $this->put(route('admin.settings.update'), [
            'app_name' => 'MyApp',
        ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status');

        $this->assertSame('MyApp', app(SettingsService::class)->get('app_name'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('MyApp');
    }

    public function test_updated_app_name_is_visible_to_other_users(): void
    {
        $this->actingUser(['settings.edit']);

        $this->put(route('admin.settings.update'), [
            'app_name' => 'SharedName',
        ])->assertRedirect(route('admin.settings.edit'));

        $other = User::factory()->create();

        $this->actingAs($other);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('SharedName');
    }

    public function test_app_name_is_required(): void
    {
        $this->actingUser(['settings.edit']);

        $this->put(route('admin.settings.update'), [
            'app_name' => '',
        ])->assertSessionHasErrors('app_name');
    }

    public function test_user_without_settings_edit_cannot_update_settings(): void
    {
        $this->actingUser();

        $this->put(route('admin.settings.update'), [
            'app_name' => 'MyApp',
        ])->assertForbidden();

        $this->assertDatabaseMissing('settings', ['key' => 'app_name']);
    }

    public function test_a_logo_can_be_uploaded_and_is_served_from_public_disk(): void
    {
        Storage::fake('public');
        $this->actingUser(['settings.edit']);

        $this->put(route('admin.settings.update'), [
            'app_name' => 'Vibe',
            'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
        ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status');

        $logoPath = app(SettingsService::class)->get('logo_path');

        $this->assertNotNull($logoPath);
        $this->assertStringStartsWith('logos/', $logoPath);
        Storage::disk('public')->assertExists($logoPath);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/storage/'.$logoPath);
    }

    public function test_an_uploaded_file_must_be_an_image(): void
    {
        $this->actingUser(['settings.edit']);

        $this->put(route('admin.settings.update'), [
            'app_name' => 'Vibe',
            'logo' => UploadedFile::fake()->create('notes.txt', 10),
        ])->assertSessionHasErrors('logo');
    }
}
