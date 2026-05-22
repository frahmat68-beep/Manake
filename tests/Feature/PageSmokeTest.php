<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Equipment;
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

    public function test_home_page_renders_v0_cinematic_indonesian_hero_with_empty_database(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Rental Peralatan Profesional');
        $response->assertSee('Sewa');
        $response->assertSee('terbaik, kapan saja.');
        $response->assertSee('ARRI Alexa Mini LF');
        $response->assertSee('Diperbarui secara real time');
    }

    public function test_home_page_uses_database_equipment_and_categories_when_available(): void
    {
        $category = Category::query()->create([
            'name' => 'Kamera',
            'slug' => 'kamera',
            'description' => 'Kamera sinema profesional.',
        ]);

        $imagePath = 'equipments/home-page-test-' . Str::lower(Str::random(8)) . '.png';
        Storage::disk('public')->put($imagePath, 'fake-image');

        try {
            $equipment = Equipment::query()->create([
                'category_id' => $category->id,
                'name' => 'Sony FX3 Cinema Kit',
                'slug' => 'sony-fx3-cinema-kit',
                'price_per_day' => 650000,
                'status' => 'ready',
                'description' => 'Kamera cinema compact.',
                'specifications' => 'Sensor full-frame' . PHP_EOL . '4K 120fps',
                'stock' => 4,
                'image_path' => $imagePath,
                'image' => null,
            ]);

            $response = $this->get(route('home'));

            $response->assertOk();
            $response->assertSee('Sony FX3 Cinema Kit');
            $response->assertSee('Kamera');
            $response->assertSee(route('product.show', $equipment->slug));
            $response->assertSee('/assets/media/' . $imagePath, false);
        } finally {
            Storage::disk('public')->delete($imagePath);
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

    public function test_dark_theme_cookie_renders_dark_shell_and_dark_brand_assets(): void
    {
        $response = $this
            ->withCookie('theme', 'dark')
            ->get(route('login'));

        $response->assertOk();
        $response->assertSee('data-theme-preference="dark"', false);
        $response->assertSee('data-theme-resolved="dark"', false);
        $response->assertSee('/assets/public/manake-logo-blue.png?v=', false);
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

    public function test_product_detail_page_loads_for_public_equipment(): void
    {
        $equipment = Equipment::factory()->create([
            'status' => 'ready',
            'stock' => 3,
            'image_path' => null,
            'image' => null,
        ]);

        $this->get(route('product.show', $equipment->slug))->assertOk();
        $this->get(route('catalog'))->assertOk();
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
