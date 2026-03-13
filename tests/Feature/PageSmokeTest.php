<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Smoke Admin',
            'email' => 'smoke-admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
    }

    public function test_public_pages_render_without_exception(): void
    {
        $routes = [
            route('home'),
            route('categories.index'),
            route('category.show', 'sample-category'),
            route('catalog'),
            route('rental.rules'),
            route('availability.board'),
            route('login'),
            route('register'),
            route('admin.login'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_login_page_uses_relative_logo_assets(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('/assets/public/manake-logo-blue.png?v=', false);
        $response->assertSee('/assets/public/MANAKE-FAV-M.png?v=', false);
        $response->assertDontSee('http://127.0.0.1:8000/manake-logo-blue.png', false);
    }

    public function test_core_shell_pages_do_not_depend_on_runtime_cdn_assets(): void
    {
        foreach ([route('home'), route('login'), route('admin.login')] as $url) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertDontSee('https://cdn.tailwindcss.com', false);
            $response->assertDontSee('/build/assets/', false);
            $response->assertSee('manake-inline-app-css', false);
        }
    }

    public function test_public_asset_routes_serve_logo_and_media_files(): void
    {
        $path = 'equipments/test-asset-route-' . Str::lower(Str::random(8)) . '.png';
        Storage::disk('public')->put($path, 'fake-image');

        try {
            $this->get('/assets/public/manake-logo-blue.png')->assertOk();

            $response = $this->get('/assets/media/' . $path);
            $response->assertOk();
            $this->assertSame('fake-image', $response->getContent());
        } finally {
            Storage::disk('public')->delete($path);
        }
    }

    public function test_admin_pages_render_without_exception_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();

        $routes = [
            route('admin.dashboard'),
            route('admin.equipments.index'),
            route('admin.categories.index'),
            route('admin.copy.edit', 'landing'),
            route('admin.content.index'),
            route('admin.settings.index'),
            route('admin.website.edit'),
            route('admin.orders.index'),
            route('admin.users.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin, 'admin')->get($url)->assertOk();
        }
    }
}
