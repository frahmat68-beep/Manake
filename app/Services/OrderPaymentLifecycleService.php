<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderPaymentLifecycleService
{
    public function expirePendingPayments(int $minutes = 60): int
    {
        if (! schema_table_exists_cached('orders')) {
            return 0;
        }

        $cutoff = now()->subMinutes(max($minutes, 1));
        $expiredCount = 0;

        Order::query()
            ->where('status_pembayaran', Order::PAYMENT_PENDING)
            ->where('status_pesanan', Order::STATUS_PENDING_PAYMENT)
            ->whereNull('paid_at')
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$expiredCount) {
                foreach ($orders as $order) {
                    DB::transaction(function () use ($order, &$expiredCount) {
                        $lockedOrder = Order::query()
                            ->whereKey($order->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedOrder) {
                            return;
                        }

                        if ($lockedOrder->status_pembayaran !== Order::PAYMENT_PENDING || $lockedOrder->status_pesanan !== Order::STATUS_PENDING_PAYMENT) {
                            return;
                        }

                        if ($lockedOrder->paid_at !== null) {
                            return;
                        }

                        $lockedOrder->status_pembayaran = Order::PAYMENT_EXPIRED;
                        $lockedOrder->status_pesanan = Order::STATUS_EXPIRED;
                        $lockedOrder->status = 'expired';
                        $lockedOrder->save();

                        Payment::query()
                            ->where('order_id', $lockedOrder->id)
                            ->where('provider', Payment::PROVIDER_MIDTRANS_RENTAL)
                            ->update([
                                'status' => 'expired',
                                'transaction_status' => 'expire',
                                'expired_at' => now(),
                                'updated_at' => now(),
                            ]);

                        if (schema_table_exists_cached('order_notifications') && (int) $lockedOrder->user_id > 0) {
                            OrderNotification::query()->create([
                                'user_id' => $lockedOrder->user_id,
                                'order_id' => $lockedOrder->id,
                                'type' => 'payment_expired',
                                'title' => 'Pembayaran kadaluwarsa',
                                'message' => 'Pesanan ' . ($lockedOrder->order_number ?: ('ORD-' . $lockedOrder->id)) . ' otomatis dibatalkan karena melewati batas 1 jam pembayaran.',
                            ]);
                        }

                        $expiredCount++;
                    });
                }
            });

        return $expiredCount;
    }
}
