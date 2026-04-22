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
}
