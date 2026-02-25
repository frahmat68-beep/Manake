<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class OrderReminderService
{
    public function dispatchDueReturnReminders(?int $userId = null): int
    {
        if (! schema_table_exists_cached('orders') || ! schema_table_exists_cached('order_notifications')) {
            return 0;
        }

        $now = now();
        $created = 0;

        $orders = Order::query()
            ->where('status_pembayaran', 'paid')
            ->whereIn('status_pesanan', ['diproses', 'lunas', 'barang_diambil'])
            ->whereNotNull('rental_end_date')
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->whereDate('rental_end_date', '<=', $now->copy()->addDay()->toDateString())
            ->whereDate('rental_end_date', '>=', $now->copy()->subDay()->toDateString())
            ->get(['id', 'user_id', 'order_number', 'rental_end_date']);

        foreach ($orders as $order) {
            if ((int) $order->user_id <= 0 || ! $order->rental_end_date) {
                continue;
            }

            $endAt = Carbon::parse($order->rental_end_date)->endOfDay();
            $minutesLeft = $now->diffInMinutes($endAt, false);

            if ($minutesLeft <= 0) {
                continue;
            }

            if ($minutesLeft <= 360) {
                $created += $this->createReminderIfMissing($order, 'rental_return_reminder_6h', 6, $endAt);
            }

            if ($minutesLeft <= 180) {
                $created += $this->createReminderIfMissing($order, 'rental_return_reminder_3h', 3, $endAt);
            }
        }

        return $created;
    }

    private function createReminderIfMissing(Order $order, string $type, int $hours, Carbon $endAt): int
    {
        $exists = OrderNotification::query()
            ->where('order_id', $order->id)
            ->where('type', $type)
            ->exists();

        if ($exists) {
            return 0;
        }

        $orderNumber = $order->order_number ?: ('ORD-' . $order->id);
        $endLabel = $endAt->format('d M Y H:i');

        OrderNotification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => $type,
            'title' => "Pengingat pengembalian {$hours} jam lagi",
            'message' => "Pesanan {$orderNumber} akan berakhir pada {$endLabel}. Mohon siapkan pengembalian alat tepat waktu.",
        ]);

        return 1;
    }
}
