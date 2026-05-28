<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_switch_accepts_same_host_absolute_redirect(): void
    {
        $response = $this
            ->withServerVariables([
                'HTTP_HOST' => 'manake.vercel.app',
                'HTTPS' => 'on',
            ])
            ->post('https://manake.vercel.app/theme/dark', [
                'redirect' => 'https://manake.vercel.app/booking/history',
            ]);

        $response->assertRedirect('https://manake.vercel.app/booking/history');
    }

    public function test_locale_switch_accepts_same_host_absolute_redirect(): void
    {
        $response = $this
            ->withServerVariables([
                'HTTP_HOST' => 'manake.vercel.app',
                'HTTPS' => 'on',
            ])
            ->post('https://manake.vercel.app/lang/en', [
                'redirect' => 'https://manake.vercel.app/booking/history',
            ]);

        $response->assertRedirect('https://manake.vercel.app/booking/history');
    }

    public function test_theme_switch_returns_json_payload_for_ajax_requests(): void
    {
        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('theme.switch', [
                'theme' => 'dark',
            ]), [
                'redirect' => '/booking/history',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('theme.preference', 'dark')
            ->assertJsonPath('theme.resolved', 'dark')
            ->assertJsonPath('redirect', '/booking/history');
    }

    public function test_locale_switch_returns_json_payload_for_ajax_requests(): void
    {
        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('lang.switch', [
                'locale' => 'en',
            ]), [
                'redirect' => '/booking/history',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('locale', 'en')
            ->assertJsonPath('redirect', '/booking/history');
    }

    public function test_settings_update_persists_user_theme_and_locale(): void
    {
        $user = User::factory()->create([
            'preferred_locale' => 'id',
            'preferred_theme' => 'light',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('settings.update'), [
                'locale' => 'en',
                'theme' => 'dark',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'preferred_locale' => 'en',
            'preferred_theme' => 'dark',
        ]);
    }

    public function test_guest_settings_update_persists_preferences_in_session_and_cookie(): void
    {
        $response = $this->from(route('settings.index'))
            ->post(route('settings.update'), [
                'locale' => 'en',
                'theme' => 'system',
                'resolved_theme' => 'dark',
            ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('locale', 'en');
        $response->assertSessionHas('theme', 'system');
        $response->assertCookie('locale', 'en');
        $response->assertCookie('theme', 'system');
        $response->assertCookie('theme_resolved', 'dark');
    }

    public function test_settings_update_validates_invalid_locale_and_theme(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('settings.index'))
            ->post(route('settings.update'), [
                'locale' => 'jp',
                'theme' => 'neon',
                'resolved_theme' => 'blue',
            ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHasErrors(['locale', 'theme', 'resolved_theme']);
    }

    public function test_saved_language_and_theme_stay_selected_after_redirect(): void
    {
        $user = User::factory()->create([
            'preferred_locale' => 'en',
            'preferred_theme' => 'dark',
        ]);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('value="en"', false);
        $response->assertSee('value="dark"', false);
        $response->assertSee('checked', false);
        $response->assertSee('Manage language and website appearance.');
        $response->assertSee('Active');
        $response->assertSee('Dark');
    }

    public function test_english_locale_changes_common_navbar_and_settings_text(): void
    {
        $response = $this->withCookie('locale', 'en')->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Settings');
        $response->assertSee('Manage language and website appearance.');

        $catalogResponse = $this->withCookie('locale', 'en')->get(route('catalog'));

        $catalogResponse->assertOk();
        $catalogResponse->assertSee('Check Availability');
        $catalogResponse->assertSee('About');
        $catalogResponse->assertSee('Rental Guide');
        $catalogResponse->assertSee('Login');
    }

    public function test_light_theme_uses_blue_logo_and_dark_theme_uses_white_logo(): void
    {
        $lightResponse = $this->withCookie('theme', 'light')
            ->withCookie('theme_resolved', 'light')
            ->get(route('catalog'));

        $lightResponse->assertOk();
        $lightResponse->assertSee('manake-logo-blue.png');

        $darkResponse = $this->withCookie('theme', 'dark')
            ->withCookie('theme_resolved', 'dark')
            ->get(route('catalog'));

        $darkResponse->assertOk();
        $darkResponse->assertSee('manake-logo-white.png');
    }
}
