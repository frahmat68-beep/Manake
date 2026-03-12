<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\OrderPaymentLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_expire_pending_payments_marks_order_and_payment_expired(): void
    {
        $user = User::factory()->create();

        $expiredOrder = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-PENDING-OLD',
            'midtrans_order_id' => 'MNK-PENDING-OLD',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 500000,
            'rental_start_date' => now()->addDays(2)->toDateString(),
            'rental_end_date' => now()->addDays(3)->toDateString(),
        ]);
        $expiredOrder->forceFill([
            'created_at' => now()->subHours(25),
            'updated_at' => now()->subHours(25),
        ])->saveQuietly();

        Payment::create([
            'order_id' => $expiredOrder->id,
            'provider' => 'midtrans',
            'midtrans_order_id' => $expiredOrder->midtrans_order_id,
            'status' => 'pending',
            'transaction_status' => 'pending',
            'gross_amount' => 500000,
            'snap_token' => 'snap-old',
        ]);

        $freshOrder = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-PENDING-NEW',
            'midtrans_order_id' => 'MNK-PENDING-NEW',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 250000,
            'rental_start_date' => now()->addDays(4)->toDateString(),
            'rental_end_date' => now()->addDays(5)->toDateString(),
        ]);
        $freshOrder->forceFill([
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ])->saveQuietly();

        Payment::create([
            'order_id' => $freshOrder->id,
            'provider' => 'midtrans',
            'midtrans_order_id' => $freshOrder->midtrans_order_id,
            'status' => 'pending',
            'transaction_status' => 'pending',
            'gross_amount' => 250000,
            'snap_token' => 'snap-new',
        ]);

        $expiredCount = app(OrderPaymentLifecycleService::class)->expirePendingPayments(1440);

        $this->assertSame(1, $expiredCount);

        $this->assertDatabaseHas('orders', [
            'id' => $expiredOrder->id,
            'status_pembayaran' => 'expired',
            'status_pesanan' => 'expired',
            'status' => 'expired',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $expiredOrder->id,
            'provider' => 'midtrans',
            'status' => 'expired',
            'transaction_status' => 'expire',
        ]);

        $this->assertDatabaseHas('order_notifications', [
            'order_id' => $expiredOrder->id,
            'type' => 'payment_expired',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $freshOrder->id,
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
        ]);
    }
}
