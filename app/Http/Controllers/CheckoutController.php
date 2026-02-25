<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Equipment;
use App\Services\AvailabilityService;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show(Request $request, CartService $cart, PricingService $pricing)
    {
        $cartItems = $this->hydrateCartItems($cart->getItems());
        $request->user()->load('profile');
        $profile = $request->user()->profile;
        $subtotalPerDay = collect($cartItems)->sum(fn ($item) => ((int) $item['price']) * ((int) $item['qty']));
        $estimatedSubtotal = collect($cartItems)->sum(fn ($item) => (int) ($item['estimated_total'] ?? 0));
        $pricingSummary = $pricing->calculateOrderTotals([], $estimatedSubtotal);

        return view('checkout', [
            'cartItems' => $cartItems,
            'profile' => $profile,
            'subtotalPerDay' => $subtotalPerDay,
            'estimatedSubtotal' => $pricingSummary['subtotal'],
            'taxAmount' => $pricingSummary['tax'],
            'estimatedTotal' => $pricingSummary['total'],
        ]);
    }

    public function store(Request $request, CartService $cart, MidtransService $midtrans, AvailabilityService $availability, PricingService $pricing)
    {
        $cartItems = $this->hydrateCartItems($cart->getItems());
        $maxAllowedDate = $this->bookingWindowEnd()->toDateString();

        if (empty($cartItems)) {
            return response()->json([
                'message' => __('Cart masih kosong. Tambahkan item terlebih dahulu.'),
            ], 422);
        }

        $user = $request->user()->load('profile');

        if (! $user->profileIsComplete()) {
            return response()->json([
                'message' => __('Profil belum lengkap. Silakan lengkapi data diri.'),
            ], 422);
        }

        if (! $user->hasVerifiedPhone()) {
            return response()->json([
                'message' => __('Nomor telepon belum terverifikasi. Silakan verifikasi OTP dulu.'),
            ], 422);
        }

        $request->validate([
            'confirm_profile' => ['accepted'],
            'rental_start_date' => ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:' . $maxAllowedDate],
            'rental_end_date' => ['nullable', 'date', 'after_or_equal:rental_start_date', 'before_or_equal:' . $maxAllowedDate],
        ]);

        $fallbackStartDate = null;
        $fallbackEndDate = null;
        if ($request->filled('rental_start_date') && $request->filled('rental_end_date')) {
            try {
                $parsedStart = Carbon::parse((string) $request->input('rental_start_date'))->startOfDay();
                $parsedEnd = Carbon::parse((string) $request->input('rental_end_date'))->startOfDay();
                if ($parsedEnd->gte($parsedStart)) {
                    $fallbackStartDate = $parsedStart->toDateString();
                    $fallbackEndDate = $parsedEnd->toDateString();
                }
            } catch (\Throwable $exception) {
                $fallbackStartDate = null;
                $fallbackEndDate = null;
            }
        }

        if ($fallbackStartDate && $fallbackEndDate) {
            $cartItems = collect($cartItems)
                ->map(function (array $item) use ($fallbackStartDate, $fallbackEndDate) {
                    if (! empty($item['rental_start_date']) && ! empty($item['rental_end_date']) && (int) ($item['rental_days'] ?? 0) > 0) {
                        return $item;
                    }

                    $start = Carbon::parse($fallbackStartDate);
                    $end = Carbon::parse($fallbackEndDate);
                    $days = $start->diffInDays($end) + 1;

                    $item['rental_start_date'] = $fallbackStartDate;
                    $item['rental_end_date'] = $fallbackEndDate;
                    $item['rental_days'] = $days;
                    $item['estimated_total'] = (int) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 1) * max($days, 1);

                    return $item;
                })
                ->values()
                ->all();
        }

        $itemsWithoutDates = collect($cartItems)
            ->filter(fn ($item) => empty($item['rental_start_date']) || empty($item['rental_end_date']) || (int) ($item['rental_days'] ?? 0) < 1)
            ->pluck('name')
            ->filter()
            ->unique()
            ->values();

        if ($itemsWithoutDates->isNotEmpty()) {
            return response()->json([
                'message' => __('Tanggal sewa belum diisi untuk: :items. Update tanggal di katalog/detail lalu tambahkan ulang ke keranjang.', [
                    'items' => $itemsWithoutDates->implode(', '),
                ]),
            ], 422);
        }

        $itemsOutsideWindow = collect($cartItems)
            ->filter(function (array $item): bool {
                return ! $this->isDateWithinBookingWindow(data_get($item, 'rental_start_date'))
                    || ! $this->isDateWithinBookingWindow(data_get($item, 'rental_end_date'));
            })
            ->pluck('name')
            ->filter()
            ->unique()
            ->values();
        if ($itemsOutsideWindow->isNotEmpty()) {
            return response()->json([
                'message' => __('Tanggal sewa hanya bisa dipilih dari hari ini sampai :date untuk: :items.', [
                    'date' => $this->bookingWindowEnd()->translatedFormat('d M Y'),
                    'items' => $itemsOutsideWindow->implode(', '),
                ]),
            ], 422);
        }

        $equipmentIds = collect($cartItems)
            ->map(fn ($item) => $item['equipment_id'] ?? $item['product_id'] ?? null)
            ->filter()
            ->unique()
            ->values();

        if ($equipmentIds->isEmpty()) {
            return response()->json([
                'message' => __('Item di cart tidak valid. Silakan ulangi pilihan alat.'),
            ], 422);
        }

        $startDate = collect($cartItems)
            ->map(fn ($item) => Carbon::parse($item['rental_start_date'])->startOfDay())
            ->sortBy(fn (Carbon $date) => $date->timestamp)
            ->first();
        $endDate = collect($cartItems)
            ->map(fn ($item) => Carbon::parse($item['rental_end_date'])->startOfDay())
            ->sortByDesc(fn (Carbon $date) => $date->timestamp)
            ->first();

        $userId = (int) $user->id;

        try {
            $order = DB::transaction(function () use ($userId, $cartItems, $equipmentIds, $startDate, $endDate, $availability, $pricing) {
                $equipments = Equipment::query()
                    ->whereIn('id', $equipmentIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $missingEquipmentIds = $equipmentIds->diff($equipments->keys());
                if ($missingEquipmentIds->isNotEmpty()) {
                    throw new \RuntimeException(__('Beberapa alat tidak tersedia. Silakan perbarui cart.'), 422);
                }

                $itemsByEquipment = collect($cartItems)->groupBy(fn ($item) => (int) $item['equipment_id']);
                $requestedDailyByEquipment = $this->requestedDailyDemandByEquipment($cartItems);

                foreach ($itemsByEquipment as $equipmentId => $requestedItems) {
                    $equipment = $equipments->get((int) $equipmentId);
                    if (! $equipment) {
                        throw new \RuntimeException(__('Beberapa alat tidak tersedia. Silakan perbarui cart.'), 422);
                    }

                    if (($equipment->status ?? 'ready') !== 'ready') {
                        throw new \RuntimeException(__(':name sedang tidak bisa disewa.', ['name' => $equipment->name]), 422);
                    }

                    $reservedDaily = $availability->getDailyReservedUnits($equipment, $startDate, $endDate);
                    $requestedDaily = $requestedDailyByEquipment[(int) $equipmentId] ?? [];

                    $conflictDates = collect($requestedDaily)
                        ->filter(function (int $requestedQty, string $dateKey) use ($reservedDaily, $equipment) {
                            $reservedQty = (int) data_get($reservedDaily, $dateKey . '.qty', 0);
                            $availableQty = max(((int) $equipment->stock) - $reservedQty, 0);

                            return $requestedQty > $availableQty;
                        })
                        ->keys()
                        ->map(fn (string $dateKey) => Carbon::parse($dateKey)->format('d M Y'))
                        ->values();

                    if ($conflictDates->isNotEmpty()) {
                        $list = $conflictDates->take(4)->implode(', ');
                        if ($conflictDates->count() > 4) {
                            $list .= ', ...';
                        }

                        throw new \RuntimeException(__(':name tidak tersedia pada tanggal: :dates.', [
                            'name' => $equipment->name,
                            'dates' => $list,
                        ]), 422);
                    }
                }

                $order = Order::create([
                    'user_id' => $userId,
                    'order_number' => null,
                    'status_pembayaran' => Order::PAYMENT_PENDING,
                    'status_pesanan' => Order::STATUS_PENDING_PAYMENT,
                    'status' => 'pending',
                    'total_amount' => 0,
                    'rental_start_date' => $startDate,
                    'rental_end_date' => $endDate,
                ]);

                $subtotal = 0;
                $hasItemRentalDates = schema_column_exists_cached('order_items', 'rental_start_date')
                    && schema_column_exists_cached('order_items', 'rental_end_date');
                $hasItemRentalDays = schema_column_exists_cached('order_items', 'rental_days');

                foreach ($cartItems as $item) {
                    $equipmentId = (int) ($item['equipment_id'] ?? $item['product_id'] ?? 0);
                    $equipment = $equipments->get($equipmentId);
                    if (! $equipment) {
                        continue;
                    }

                    $price = (int) ($equipment->price_per_day ?? $item['price']);
                    $qty = (int) $item['qty'];
                    $days = max((int) ($item['rental_days'] ?? 1), 1);
                    $lineSubtotal = $price * $qty * $days;

                    $payload = [
                        'equipment_id' => $equipmentId,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $lineSubtotal,
                    ];

                    if ($hasItemRentalDates) {
                        $payload['rental_start_date'] = $item['rental_start_date'];
                        $payload['rental_end_date'] = $item['rental_end_date'];
                    }

                    if ($hasItemRentalDays) {
                        $payload['rental_days'] = $days;
                    }

                    $order->items()->create($payload);

                    $subtotal += $lineSubtotal;
                }

                $pricingSummary = $pricing->calculateOrderTotals([], $subtotal);

                $order->total_amount = $pricingSummary['subtotal'];
                $order->midtrans_order_id = $this->generateMidtransOrderId($order->id);
                $order->order_number = $order->midtrans_order_id;
                $order->save();

                Payment::updateOrCreate(
                    ['order_id' => $order->id, 'provider' => Payment::PROVIDER_MIDTRANS_RENTAL],
                    [
                        'midtrans_order_id' => $order->midtrans_order_id,
                        'snap_token' => $order->snap_token,
                        'status' => Order::PAYMENT_PENDING,
                        'transaction_status' => 'pending',
                        'gross_amount' => (int) $pricingSummary['total'],
                        'paid_at' => null,
                        'expired_at' => null,
                        'payload_json' => json_encode([
                            'generated_at' => now()->toDateTimeString(),
                            'subtotal' => (int) $pricingSummary['subtotal'],
                            'tax' => (int) $pricingSummary['tax'],
                            'total' => (int) $pricingSummary['total'],
                        ], JSON_UNESCAPED_UNICODE),
                    ]
                );

                return $order;
            });
        } catch (\RuntimeException $exception) {
            $status = $exception->getCode() === 422 ? 422 : 500;

            return response()->json([
                'message' => $exception->getMessage() ?: __('Checkout gagal diproses.'),
            ], $status);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => __('Checkout gagal diproses. Silakan coba lagi.'),
            ], 500);
        }

        $fallbackToOrderDetail = false;
        $checkoutMessage = null;

        try {
            $order->load(['items.equipment', 'user.profile']);
            $order->snap_token = $midtrans->createSnapToken($order);
            $order->save();

            $pricingSummary = $pricing->calculateOrderTotals([], (int) ($order->total_amount ?? 0));
            Payment::updateOrCreate(
                ['order_id' => $order->id, 'provider' => Payment::PROVIDER_MIDTRANS_RENTAL],
                [
                    'midtrans_order_id' => $order->midtrans_order_id,
                    'snap_token' => $order->snap_token,
                    'status' => Order::PAYMENT_PENDING,
                    'transaction_status' => 'pending',
                    'gross_amount' => (int) $pricingSummary['total'],
                    'paid_at' => null,
                    'expired_at' => null,
                    'payload_json' => json_encode([
                        'generated_at' => now()->toDateTimeString(),
                        'subtotal' => (int) $pricingSummary['subtotal'],
                        'tax' => (int) $pricingSummary['tax'],
                        'total' => (int) $pricingSummary['total'],
                    ], JSON_UNESCAPED_UNICODE),
                ]
            );
        } catch (\Throwable $exception) {
            report($exception);

            $fallbackToOrderDetail = true;
            $checkoutMessage = __('Pesanan berhasil dibuat, tapi sesi pembayaran sedang bermasalah. Buka detail order untuk lanjut bayar.');
        }

        $cart->clear();

        $signedInvoiceUrl = URL::temporarySignedRoute(
            'account.orders.receipt',
            now()->addMinutes(30),
            ['order' => $order->order_number]
        );

        return response()->json([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->midtrans_order_id,
            'snap_token' => $order->snap_token,
            'redirect_url_to_order_detail' => route('account.orders.show', $order),
            'redirect_url_to_invoice' => $signedInvoiceUrl,
            'refresh_status_url' => route('payments.refresh-status', $order),
            'fallback_to_order_detail' => $fallbackToOrderDetail,
            'message' => $checkoutMessage,
        ]);
    }

    private function generateMidtransOrderId(int $orderId): string
    {
        return 'MNK-' . now()->format('Ymd') . '-' . $orderId . '-' . Str::upper(Str::random(6));
    }

    private function hydrateCartItems(array $cartItems): array
    {
        return collect($cartItems)
            ->map(function ($item) {
                $equipmentId = (int) ($item['equipment_id'] ?? $item['product_id'] ?? 0);
                $qty = max((int) ($item['qty'] ?? 1), 1);
                $price = max((int) ($item['price'] ?? 0), 0);
                $startDate = null;
                $endDate = null;
                $days = null;

                try {
                    if (! empty($item['rental_start_date']) && ! empty($item['rental_end_date'])) {
                        $start = Carbon::parse($item['rental_start_date'])->startOfDay();
                        $end = Carbon::parse($item['rental_end_date'])->startOfDay();
                        if (
                            $end->gte($start)
                            && $this->isDateWithinBookingWindow($start->toDateString())
                            && $this->isDateWithinBookingWindow($end->toDateString())
                        ) {
                            $startDate = $start->toDateString();
                            $endDate = $end->toDateString();
                            $days = $start->diffInDays($end) + 1;
                        }
                    }
                } catch (\Throwable $exception) {
                    $startDate = null;
                    $endDate = null;
                    $days = null;
                }

                return array_merge($item, [
                    'equipment_id' => $equipmentId,
                    'qty' => $qty,
                    'price' => $price,
                    'rental_start_date' => $startDate,
                    'rental_end_date' => $endDate,
                    'rental_days' => $days,
                    'estimated_total' => $price * $qty * max((int) ($days ?? 1), 1),
                ]);
            })
            ->values()
            ->all();
    }

    private function requestedDailyDemandByEquipment(array $cartItems): array
    {
        $dailyDemand = [];

        foreach ($cartItems as $item) {
            $equipmentId = (int) ($item['equipment_id'] ?? 0);
            $qty = max((int) ($item['qty'] ?? 0), 0);
            $startDate = data_get($item, 'rental_start_date');
            $endDate = data_get($item, 'rental_end_date');

            if ($equipmentId <= 0 || $qty < 1 || ! $startDate || ! $endDate) {
                continue;
            }

            try {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->startOfDay();
                if ($end->lt($start)) {
                    continue;
                }

                $cursor = $start->copy()->subDays(AvailabilityService::BUFFER_DAYS);
                $windowEnd = $end->copy()->addDays(AvailabilityService::BUFFER_DAYS);

                while ($cursor->lte($windowEnd)) {
                    $dateKey = $cursor->toDateString();
                    $dailyDemand[$equipmentId][$dateKey] = ($dailyDemand[$equipmentId][$dateKey] ?? 0) + $qty;
                    $cursor->addDay();
                }
            } catch (\Throwable $exception) {
                continue;
            }
        }

        return $dailyDemand;
    }

    private function bookingWindowStart(): Carbon
    {
        return now()->startOfDay();
    }

    private function bookingWindowEnd(): Carbon
    {
        return now()->addMonthsNoOverflow(3)->startOfDay();
    }

    private function isDateWithinBookingWindow(?string $value): bool
    {
        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        try {
            $date = Carbon::parse($value)->startOfDay();
        } catch (\Throwable $exception) {
            return false;
        }

        return $date->betweenIncluded($this->bookingWindowStart(), $this->bookingWindowEnd());
    }
}
