<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_settings_page_renders_compact_settings_sections(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan');
        $response->assertSee('Ringkas');
        $response->assertSee('Bahasa aktif');
        $response->assertSee('Tema aktif');
    }

    public function test_admin_settings_routes_render_the_same_settings_surface(): void
    {
        $admin = Admin::create([
            'name' => 'Settings Admin',
            'email' => 'settings-admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        foreach (['admin.settings.index', 'admin.website.edit'] as $routeName) {
            $response = $this->actingAs($admin, 'admin')->get(route($routeName));

            $response->assertOk();
            $response->assertSee('Pengaturan Situs');
            $response->assertSee('Brand');
            $response->assertSee('Preview');
            $response->assertSee('Maintenance');
        }
    }
}
