<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeHeroCarouselTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_ready_carousel_loads_ready_items_from_all_categories(): void
    {
        $camera = Category::create([
            'name' => 'Camera',
            'slug' => 'camera',
        ]);

        $audio = Category::create([
            'name' => 'Audio',
            'slug' => 'audio',
        ]);

        Equipment::create([
            'name' => 'Sony A7 III',
            'slug' => 'sony-a7-iii',
            'category_id' => $camera->id,
            'price_per_day' => 350000,
            'stock' => 5,
            'status' => 'ready',
        ]);

        Equipment::create([
            'name' => 'Zoom H6',
            'slug' => 'zoom-h6',
            'category_id' => $audio->id,
            'price_per_day' => 90000,
            'stock' => 4,
            'status' => 'ready',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Sony A7 III');
        $response->assertSee('Zoom H6');
        $response->assertSee('data-slide-count="2"', false);
    }

    public function test_home_ready_carousel_includes_fit_image_and_single_slide_fallback_logic(): void
    {
        $camera = Category::create([
            'name' => 'Camera',
            'slug' => 'camera',
        ]);

        Equipment::create([
            'name' => 'DJI RS3',
            'slug' => 'dji-rs3',
            'category_id' => $camera->id,
            'price_per_day' => 250000,
            'stock' => 2,
            'status' => 'ready',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('object-contain', false);
        $response->assertSee('watchOverflow: true', false);
        $response->assertDontSee('cloneNode(true)', false);
    }

    public function test_home_uses_relative_storage_urls_for_ready_item_images(): void
    {
        $camera = Category::create([
            'name' => 'Camera',
            'slug' => 'camera',
        ]);

        Storage::disk('public')->put('equipments/home-relative-path.png', 'fake-image');

        Equipment::create([
            'name' => 'Relative Path Camera',
            'slug' => 'relative-path-camera',
            'category_id' => $camera->id,
            'price_per_day' => 320000,
            'stock' => 1,
            'status' => 'ready',
            'image_path' => 'equipments/home-relative-path.png',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('/storage/equipments/home-relative-path.png', false);
        $response->assertDontSee('http://127.0.0.1:8000/storage/equipments/home-relative-path.png', false);
    }

    public function test_home_shows_damage_fee_alert_and_popup_when_additional_fee_is_outstanding(): void
    {
        $user = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-DAMAGE-OUTSTANDING',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'barang_kembali',
            'status' => 'paid',
            'total_amount' => 500000,
            'penalty_amount' => 0,
            'additional_fee' => 100000,
            'additional_fee_note' => 'terlambat pengembalian',
            'rental_start_date' => now()->subDays(2)->toDateString(),
            'rental_end_date' => now()->subDay()->toDateString(),
            'midtrans_order_id' => 'MNK-DAMAGE-OUTSTANDING',
            'paid_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Perhatian Tagihan Tambahan');
        $response->assertSee('Rp 100.000');
        $response->assertSee('id="damage-fee-popup"', false);
        $response->assertSee('data-damage-popup-pay', false);
    }

    public function test_home_hides_damage_fee_alert_after_damage_payment_is_paid(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-DAMAGE-PAID',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'barang_kembali',
            'status' => 'paid',
            'total_amount' => 500000,
            'penalty_amount' => 0,
            'additional_fee' => 100000,
            'additional_fee_note' => 'kerusakan ringan',
            'rental_start_date' => now()->subDays(2)->toDateString(),
            'rental_end_date' => now()->subDay()->toDateString(),
            'midtrans_order_id' => 'MNK-DAMAGE-PAID',
            'paid_at' => now()->subDay(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_MIDTRANS_DAMAGE,
            'midtrans_order_id' => 'MNK-DAMAGE-FEE-PAID',
            'transaction_status' => 'settlement',
            'gross_amount' => 100000,
            'status' => 'paid',
            'transaction_id' => 'trx-damage-paid-1',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Perhatian Tagihan Tambahan');
        $response->assertDontSee('id="damage-fee-popup"', false);
    }

    public function test_home_guest_rental_overview_shows_reserved_items_with_date_and_quantity(): void
    {
        $category = Category::create([
            'name' => 'Audio',
            'slug' => 'audio',
        ]);

        $equipment = Equipment::create([
            'name' => 'HT Wlan UHF',
            'slug' => 'ht-wlan-uhf',
            'category_id' => $category->id,
            'price_per_day' => 10000,
            'stock' => 30,
            'status' => 'ready',
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'order_number' => 'MNK-GUEST-RENTAL-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'barang_diambil',
            'status' => 'paid',
            'total_amount' => 120000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
            'midtrans_order_id' => 'MNK-GUEST-RENTAL-1',
            'paid_at' => now()->subHour(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'equipment_id' => $equipment->id,
            'qty' => 3,
            'price' => 10000,
            'subtotal' => 90000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
            'rental_days' => 3,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Ringkasan Alat Disewa');
        $response->assertSee('HT Wlan UHF');
        $response->assertSee('x3');
        $response->assertSee('Tanggal sewa:');
    }

    public function test_home_logged_in_user_uses_rental_overview_without_stat_cards(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('home'));

        $response->assertOk();
        $response->assertSee('Ringkasan Alat Disewa');
        $response->assertDontSee('Pending Bayar');
        $response->assertDontSee('Siap Diambil');
        $response->assertDontSee('Sedang Disewa');
    }
}
