<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrelaunchReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_and_language_switch_redirect_back_and_store_preference(): void
    {
        $this->from(route('catalog'))
            ->post(route('theme.switch', 'dark'))
            ->assertRedirect(route('catalog'));
        $this->assertSame('dark', session('theme'));

        $this->from(route('catalog'))
            ->post(route('lang.switch', 'en'))
            ->assertRedirect(route('catalog'));
        $this->assertSame('en', session('locale'));
    }

    public function test_core_user_pages_render_in_main_booking_flow(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Prelaunch Camera',
            'slug' => 'prelaunch-camera',
        ]);

        $equipment = Equipment::create([
            'category_id' => $category->id,
            'name' => 'Sony A7S III',
            'slug' => 'sony-a7s-iii-prelaunch',
            'description' => 'Prelaunch testing gear',
            'price_per_day' => 650000,
            'stock' => 5,
            'status' => 'ready',
            'image' => null,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-PRELAUNCH-001',
            'midtrans_order_id' => 'MNK-PRELAUNCH-001',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 1300000,
            'rental_start_date' => now()->addDays(3)->toDateString(),
            'rental_end_date' => now()->addDays(4)->toDateString(),
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'price' => 650000,
            'subtotal' => 1300000,
            'rental_start_date' => now()->addDays(3)->toDateString(),
            'rental_end_date' => now()->addDays(4)->toDateString(),
            'rental_days' => 2,
        ]);

        $routes = [
            route('home'),
            route('catalog'),
            route('availability.board'),
            route('product.show', $equipment->slug),
            route('cart'),
            route('booking.history'),
            route('account.orders.show', $order),
            route('settings.index'),
            route('rental.rules'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_core_admin_operational_and_cms_pages_render(): void
    {
        $admin = Admin::create([
            'name' => 'Prelaunch Admin',
            'email' => 'prelaunch-admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        $routes = [
            route('admin.dashboard'),
            route('admin.orders.index'),
            route('admin.equipments.index'),
            route('admin.categories.index'),
            route('admin.users.index'),
            route('admin.copy.edit', 'landing'),
            route('admin.copy.edit', 'booking'),
            route('admin.settings.index'),
            route('admin.website.edit'),
            route('admin.content.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin, 'admin')->get($url)->assertOk();
        }
    }
}
