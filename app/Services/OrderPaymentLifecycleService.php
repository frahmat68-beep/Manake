<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class OrderPaymentLifecycleService
{
    public function expirePendingPayments(int $minutes = 1440): int
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
                            $this->createExpiredNotification($lockedOrder);
                        }

                        $expiredCount++;
                    });
                }
            });

        return $expiredCount;
    }

    public function expirePendingOrderIfPastCutoff(Order $order, int $minutes = 1440): bool
    {
        if ((string) ($order->status_pembayaran ?? '') !== Order::PAYMENT_PENDING) {
            return false;
        }

        if ((string) ($order->status_pesanan ?? '') !== Order::STATUS_PENDING_PAYMENT) {
            return false;
        }

        if ($order->paid_at !== null) {
            return false;
        }

        $cutoff = now()->subMinutes(max($minutes, 1));
        if ($order->created_at === null || $order->created_at->gt($cutoff)) {
            return false;
        }

        $updated = false;

        DB::transaction(function () use ($order, $minutes, &$updated) {
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

            if ($lockedOrder->paid_at !== null || $lockedOrder->created_at === null || $lockedOrder->created_at->gt(now()->subMinutes(max($minutes, 1)))) {
                return;
            }

            $lockedOrder->status_pembayaran = Order::PAYMENT_EXPIRED;
            $lockedOrder->status_pesanan = Order::STATUS_EXPIRED;
            $lockedOrder->status = Order::STATUS_EXPIRED;
            $lockedOrder->save();

            Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('provider', Payment::PROVIDER_MIDTRANS_RENTAL)
                ->update([
                    'status' => Order::PAYMENT_EXPIRED,
                    'transaction_status' => 'expire',
                    'expired_at' => now(),
                    'updated_at' => now(),
                ]);

            if (schema_table_exists_cached('order_notifications') && (int) $lockedOrder->user_id > 0) {
                $this->createExpiredNotification($lockedOrder);
            }

            $updated = true;
        });

        if ($updated) {
            $order->refresh();
        }

        return $updated;
    }

    public function reconcileRentalPaymentState(Order $order): bool
    {
        $payment = $order->relationLoaded('payment')
            ? $order->payment
            : $order->payment()->latest('id')->first();

        if (! $payment) {
            return false;
        }

        $paymentStatus = (string) ($payment->status ?? '');
        if (! in_array($paymentStatus, [
            Order::PAYMENT_PAID,
            Order::PAYMENT_FAILED,
            Order::PAYMENT_EXPIRED,
            Order::PAYMENT_REFUNDED,
        ], true)) {
            return false;
        }

        $updated = false;

        DB::transaction(function () use ($order, &$updated) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                return;
            }

            $lockedPayment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('provider', Payment::PROVIDER_MIDTRANS_RENTAL)
                ->latest('id')
                ->first();

            if (! $lockedPayment) {
                return;
            }

            $paymentStatus = (string) ($lockedPayment->status ?? '');
            $hasChanges = false;

            if ($paymentStatus === Order::PAYMENT_PAID && $lockedOrder->status_pembayaran !== Order::PAYMENT_PAID) {
                $lockedOrder->status_pembayaran = Order::PAYMENT_PAID;
                if (
                    (string) $lockedOrder->status_pesanan === Order::STATUS_PENDING_PAYMENT
                    && $lockedOrder->canTransitionToOrderStatus(Order::STATUS_READY_PICKUP)
                ) {
                    $lockedOrder->status_pesanan = Order::STATUS_READY_PICKUP;
                }
                $lockedOrder->status = 'paid';
                $lockedOrder->paid_at = $lockedOrder->paid_at ?: ($lockedPayment->paid_at ?? now());
                $hasChanges = true;
            }

            if ($paymentStatus === Order::PAYMENT_FAILED && $lockedOrder->status_pembayaran !== Order::PAYMENT_FAILED) {
                $lockedOrder->status_pembayaran = Order::PAYMENT_FAILED;
                if ($lockedOrder->canTransitionToOrderStatus(Order::STATUS_CANCELLED)) {
                    $lockedOrder->status_pesanan = Order::STATUS_CANCELLED;
                }
                $lockedOrder->status = 'failed';
                $lockedOrder->paid_at = null;
                $hasChanges = true;
            }

            if ($paymentStatus === Order::PAYMENT_EXPIRED && $lockedOrder->status_pembayaran !== Order::PAYMENT_EXPIRED) {
                $lockedOrder->status_pembayaran = Order::PAYMENT_EXPIRED;
                if ($lockedOrder->canTransitionToOrderStatus(Order::STATUS_EXPIRED)) {
                    $lockedOrder->status_pesanan = Order::STATUS_EXPIRED;
                }
                $lockedOrder->status = 'expired';
                $lockedOrder->paid_at = null;
                $hasChanges = true;
            }

            if ($paymentStatus === Order::PAYMENT_REFUNDED && $lockedOrder->status_pembayaran !== Order::PAYMENT_REFUNDED) {
                $lockedOrder->status_pembayaran = Order::PAYMENT_REFUNDED;
                if ($lockedOrder->canTransitionToOrderStatus(Order::STATUS_REFUNDED)) {
                    $lockedOrder->status_pesanan = Order::STATUS_REFUNDED;
                }
                $lockedOrder->status = 'refunded';
                $lockedOrder->paid_at = null;
                $hasChanges = true;
            }

            if (! $hasChanges) {
                return;
            }

            $lockedOrder->save();
            $updated = true;
        });

        if ($updated) {
            $order->refresh();
        }

        return $updated;
    }

    private function createExpiredNotification(Order $order): void
    {
        OrderNotification::query()->create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => 'payment_expired',
            'title' => 'Pembayaran kedaluwarsa',
            'message' => 'Pesanan ' . ($order->order_number ?: ('ORD-' . $order->id)) . ' otomatis dibatalkan karena melewati batas 24 jam pembayaran.',
        ]);
    }
}
