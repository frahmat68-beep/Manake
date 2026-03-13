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

    public function test_reconcile_rental_payment_state_updates_failed_order_status(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-FAILED-STATE',
            'midtrans_order_id' => 'MNK-FAILED-STATE',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 350000,
            'rental_start_date' => now()->addDays(1)->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'midtrans_order_id' => $order->midtrans_order_id,
            'status' => 'failed',
            'transaction_status' => 'deny',
            'gross_amount' => 350000,
            'snap_token' => 'snap-failed',
        ]);

        $updated = app(OrderPaymentLifecycleService::class)->reconcileRentalPaymentState($order);

        $this->assertTrue($updated);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_pembayaran' => 'failed',
            'status_pesanan' => 'dibatalkan',
            'status' => 'failed',
        ]);
    }
}
