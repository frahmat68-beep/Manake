<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        $response->assertSee('/manake-logo-blue.png?v=', false);
        $response->assertSee('/MANAKE-FAV-M.png?v=', false);
        $response->assertDontSee('http://127.0.0.1:8000/manake-logo-blue.png', false);
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
