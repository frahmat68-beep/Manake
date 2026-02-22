<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingOverviewUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_history_uses_detail_for_active_and_invoice_for_recent(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'UI Test Category',
            'slug' => 'ui-test-category',
        ]);
        $equipment = Equipment::create([
            'category_id' => $category->id,
            'name' => 'UI Test Gear',
            'slug' => 'ui-test-gear',
            'description' => 'UI test equipment',
            'price_per_day' => 375000,
            'stock' => 5,
            'status' => 'ready',
            'image' => null,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-UI-TEST-1',
            'midtrans_order_id' => 'MNK-UI-TEST-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 750000,
            'rental_start_date' => now()->addDays(2)->toDateString(),
            'rental_end_date' => now()->addDays(3)->toDateString(),
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 2,
            'price' => 375000,
            'subtotal' => 750000,
            'rental_start_date' => now()->addDays(2)->toDateString(),
            'rental_end_date' => now()->addDays(3)->toDateString(),
            'rental_days' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('booking.history'));

        $response->assertOk();
        $response->assertDontSee('Lihat semua');
        $response->assertSee('Detail & Ubah Jadwal', false);
        $response->assertDontSee('>Riwayat</a>', false);
        $response->assertSee('Invoice');
    }
}
