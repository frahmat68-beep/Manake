<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    private PricingService $pricing;

    public function __construct(?PricingService $pricing = null)
    {
        $this->pricing = $pricing ?? new PricingService();
    }

    public function createSnapToken(Order $order): string
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);

        return Snap::getSnapToken($this->buildSnapPayload($order));
    }

    public function createDamageFeeSnapToken(Order $order, int $amount, string $externalOrderId): string
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);

        return Snap::getSnapToken($this->buildDamageFeePayload($order, $amount, $externalOrderId));
    }

    public function buildSnapPayload(Order $order): array
    {
        $pricingSummary = $this->pricing->calculateOrderTotals([], (int) ($order->total_amount ?? 0));
        $taxAmount = (int) ($pricingSummary['tax'] ?? 0);
        $grossAmount = (int) ($pricingSummary['total'] ?? 0);

        $customer = $order->user;
        $profile = $customer?->profile;
        $defaultRentalDays = $this->resolveRentalDays($order);
        $items = $order->items->map(function ($item) use ($defaultRentalDays) {
            $equipmentName = $item->equipment?->name ?? 'Rental Item';
            $qty = max((int) $item->qty, 1);
            $itemDays = (int) ($item->rental_days ?? 0);
            if ($itemDays < 1 && $item->rental_start_date && $item->rental_end_date) {
                $itemDays = max(
                    Carbon::parse($item->rental_start_date)->startOfDay()->diffInDays(Carbon::parse($item->rental_end_date)->startOfDay()) + 1,
                    1
                );
            }
            $itemDays = $itemDays > 0 ? $itemDays : $defaultRentalDays;
            $lineName = Str::limit(sprintf('%s (%dx, %d hari)', $equipmentName, $qty, $itemDays), 50, '');

            return [
                'id' => $item->equipment_id ?? ('item-' . $item->id),
                // Gunakan subtotal per item agar total item Midtrans selalu match dengan gross_amount.
                'price' => max((int) $item->subtotal, 0),
                'quantity' => 1,
                'name' => $lineName,
            ];
        })->filter(fn ($item) => $item['price'] > 0)->values()->all();

        if ($items === []) {
            $items = [[
                'id' => 'order-' . ($order->id ?? '0'),
                'price' => (int) ($pricingSummary['subtotal'] ?? 0),
                'quantity' => 1,
                'name' => 'Total Sewa',
            ]];
        }

        if ($taxAmount > 0) {
            $items[] = [
                'id' => 'tax-ppn-11',
                'price' => $taxAmount,
                'quantity' => 1,
                'name' => 'PPN 11%',
            ];
        }

        // Pastikan gross_amount = sum(item prices) agar Midtrans tidak reject.
        $grossAmount = array_sum(array_column($items, 'price'));

        return [
            'transaction_details' => [
                'order_id' => $order->midtrans_order_id,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $profile?->full_name ?? $customer?->name ?? 'User',
                'email' => $customer?->email ?? 'user@example.com',
                'phone' => $profile?->phone ?? $customer?->phone,
            ],
        ];
    }

    public function buildDamageFeePayload(Order $order, int $amount, string $externalOrderId): array
    {
        $grossAmount = max($amount, 0);
        $customer = $order->user;
        $profile = $customer?->profile;
        $orderLabel = $order->order_number ?: ('ORD-' . $order->id);

        return [
            'transaction_details' => [
                'order_id' => $externalOrderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [[
                'id' => 'damage-fee-' . $order->id,
                'price' => $grossAmount,
                'quantity' => 1,
                'name' => Str::limit('Tagihan Kerusakan ' . $orderLabel, 50, ''),
            ]],
            'customer_details' => [
                'first_name' => $profile?->full_name ?? $customer?->name ?? 'User',
                'email' => $customer?->email ?? 'user@example.com',
                'phone' => $profile?->phone ?? $customer?->phone,
            ],
            // Tagihan kerusakan memiliki masa bayar 3 hari.
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'day',
                'duration' => 3,
            ],
        ];
    }

    private function resolveRentalDays(Order $order): int
    {
        $startDate = $order->rental_start_date ? Carbon::parse($order->rental_start_date) : null;
        $endDate = $order->rental_end_date ? Carbon::parse($order->rental_end_date) : null;

        if (! $startDate || ! $endDate) {
            return 1;
        }

        return max($startDate->diffInDays($endDate) + 1, 1);
    }
}
