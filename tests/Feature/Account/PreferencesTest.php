<?php

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferencesTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        return $user;
    }

    public function test_guest_is_redirected_to_login_when_accessing_preferences(): void
    {
        $this->get(route('account.preferences.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_an_authenticated_user_can_view_their_preferences(): void
    {
        $this->actingUser();

        $this->get(route('account.preferences.edit'))
            ->assertOk()
            ->assertSee('تفضيلاتي')
            ->assertSee('حجم الخط');
    }

    public function test_a_user_can_update_their_font_size(): void
    {
        $user = $this->actingUser();

        $this->put(route('account.preferences.update'), [
            'font_size' => 'large',
        ])
            ->assertRedirect(route('account.preferences.edit'))
            ->assertSessionHas('status');

        $this->assertSame('large', $user->fresh()->font_size);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-font-size="large"', false);
    }

    public function test_font_size_must_be_valid(): void
    {
        $user = $this->actingUser();

        $this->put(route('account.preferences.update'), [
            'font_size' => 'huge',
        ])->assertSessionHasErrors('font_size');

        $this->assertSame('default', $user->fresh()->font_size);
    }

    public function test_font_size_is_per_user(): void
    {
        $first = User::factory()->create(['font_size' => 'small']);
        $second = User::factory()->create(['font_size' => 'large']);
        $third = User::factory()->create();

        $this->actingAs($first);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-font-size="small"', false);

        $this->actingAs($second);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-font-size="large"', false);

        $this->actingAs($third);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-font-size="default"', false);
    }
}
