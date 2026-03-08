<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Order;
use App\Models\OrderNotification;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Order::query()->where('user_id', $request->user()->id);

        $orders = (clone $baseQuery)
            ->with(['items.equipment', 'payment', 'damagePayment'])
            ->latest()
            ->paginate(10);

        return view('overview.index', [
            'stats' => [
                'total_booking' => (clone $baseQuery)->count(),
                'active_rental' => (clone $baseQuery)->whereIn('status_pesanan', Order::ACTIVE_RENTAL_STATUSES)->count(),
                'completed' => (clone $baseQuery)->where('status_pesanan', 'selesai')->count(),
            ],
            'activeRentals' => $orders->getCollection()
                ->whereIn('status_pesanan', Order::ACTIVE_RENTAL_STATUSES)
                ->values(),
            'recentBookings' => $orders->getCollection()->values(),
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $this->ensureOwnedOrder($request, $order);
        $this->markOrderNotificationsAsRead($request, $order);

        $relations = ['items.equipment', 'payment', 'damagePayment'];
        if (schema_table_exists_cached('order_notifications')) {
            $relations['notifications'] = fn ($query) => $query->latest()->limit(8);
        }

        $order->load($relations);

        return view('account.orders.show', [
            'order' => $order,
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        $this->ensureOwnedOrder($request, $order);

        return redirect()->route('account.orders.show', $order)->with('pay_now', true);
    }

    public function reschedule(Request $request, Order $order, AvailabilityService $availability, PricingService $pricing)
    {
        $this->ensureOwnedOrder($request, $order);

        $allowedStatuses = [
            Order::STATUS_PENDING_PAYMENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_READY_PICKUP,
        ];

        if (! in_array((string) $order->status_pesanan, $allowedStatuses, true)) {
            return redirect()
                ->route('account.orders.show', $order)
                ->with('error', __('Reschedule hanya bisa dilakukan sebelum barang diambil.'));
        }

        $validated = $request->validate([
            'rental_start_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . $this->bookingWindowEnd()->toDateString()],
            'rental_end_date' => ['required', 'date', 'after_or_equal:rental_start_date', 'before_or_equal:' . $this->bookingWindowEnd()->toDateString()],
        ]);

        try {
            $newStartDate = Carbon::parse((string) $validated['rental_start_date'])->startOfDay();
            $newEndDate = Carbon::parse((string) $validated['rental_end_date'])->startOfDay();
        } catch (\Throwable $exception) {
            return redirect()
                ->route('account.orders.show', $order)
                ->with('error', __('Tanggal reschedule tidak valid.'));
        }

        $currentDurationDays = $this->resolveOrderDurationDays($order);
        $newDurationDays = (int) ($newStartDate->diffInDays($newEndDate) + 1);
        if ($currentDurationDays > 0 && $newDurationDays !== $currentDurationDays) {
            return redirect()
                ->route('account.orders.show', $order)
                ->with('error', __('Durasi reschedule harus tetap :days hari agar nominal pembayaran tetap sinkron.', [
                    'days' => $currentDurationDays,
                ]));
        }

        try {
            DB::transaction(function () use ($order, $newStartDate, $newEndDate, $newDurationDays, $availability, $pricing) {
                $order->load(['items.equipment', 'payment']);

                if ($order->items->isEmpty()) {
                    throw new \RuntimeException(__('Order tidak memiliki item untuk dijadwalkan ulang.'), 422);
                }

                $equipmentIds = $order->items
                    ->pluck('equipment_id')
                    ->filter(fn ($equipmentId) => (int) $equipmentId > 0)
                    ->unique()
                    ->values();

                if ($equipmentIds->isEmpty()) {
                    throw new \RuntimeException(__('Order tidak memiliki item equipment yang valid.'), 422);
                }

                $equipments = Equipment::query()
                    ->whereIn('id', $equipmentIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $missingEquipmentIds = $equipmentIds->diff($equipments->keys());
                if ($missingEquipmentIds->isNotEmpty()) {
                    throw new \RuntimeException(__('Sebagian item sudah tidak tersedia untuk reschedule.'), 422);
                }

                $requestedDailyByEquipment = [];
                foreach ($order->items as $item) {
                    $equipmentId = (int) ($item->equipment_id ?? 0);
                    $qty = max((int) ($item->qty ?? 0), 1);
                    if ($equipmentId <= 0) {
                        continue;
                    }

                    $windowStart = $newStartDate->copy()->subDays(AvailabilityService::BUFFER_DAYS);
                    $windowEnd = $newEndDate->copy()->addDays(AvailabilityService::BUFFER_DAYS);
                    for ($cursor = $windowStart; $cursor->lte($windowEnd); $cursor->addDay()) {
                        $dateKey = $cursor->toDateString();
                        $requestedDailyByEquipment[$equipmentId][$dateKey] = ($requestedDailyByEquipment[$equipmentId][$dateKey] ?? 0) + $qty;
                    }
                }

                foreach ($requestedDailyByEquipment as $equipmentId => $requestedDaily) {
                    $equipment = $equipments->get((int) $equipmentId);
                    if (! $equipment) {
                        throw new \RuntimeException(__('Sebagian item sudah tidak tersedia untuk reschedule.'), 422);
                    }

                    if (($equipment->status ?? 'ready') !== 'ready') {
                        throw new \RuntimeException(__(':name sedang tidak bisa disewa.', ['name' => $equipment->name]), 422);
                    }

                    $reservedDaily = $availability->getDailyReservedUnits(
                        $equipment,
                        $newStartDate,
                        $newEndDate,
                        (int) $order->id
                    );

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

                        throw new \RuntimeException(
                            __(':name sedang disewa pada tanggal: :dates. Silakan pilih tanggal lain.', [
                                'name' => $equipment->name,
                                'dates' => $list,
                            ]),
                            422
                        );
                    }
                }

                $subtotal = 0;
                $hasItemRentalDates = schema_column_exists_cached('order_items', 'rental_start_date')
                    && schema_column_exists_cached('order_items', 'rental_end_date');
                $hasItemRentalDays = schema_column_exists_cached('order_items', 'rental_days');

                foreach ($order->items as $item) {
                    $qty = max((int) ($item->qty ?? 0), 1);
                    $lineSubtotal = (int) ($item->price ?? 0) * $qty * max($newDurationDays, 1);

                    $itemPayload = [
                        'subtotal' => $lineSubtotal,
                    ];
                    if ($hasItemRentalDates) {
                        $itemPayload['rental_start_date'] = $newStartDate->toDateString();
                        $itemPayload['rental_end_date'] = $newEndDate->toDateString();
                    }
                    if ($hasItemRentalDays) {
                        $itemPayload['rental_days'] = $newDurationDays;
                    }

                    $item->fill($itemPayload)->save();
                    $subtotal += $lineSubtotal;
                }

                $pricingSummary = $pricing->calculateOrderTotals([], $subtotal);
                $order->rental_start_date = $newStartDate->toDateString();
                $order->rental_end_date = $newEndDate->toDateString();
                $order->total_amount = (int) $pricingSummary['subtotal'];
                $order->save();

                $payment = $order->payment;
                if ($payment && ($order->status_pembayaran ?? 'pending') !== Order::PAYMENT_PAID) {
                    $existingPayload = json_decode((string) ($payment->payload_json ?? ''), true);
                    if (! is_array($existingPayload)) {
                        $existingPayload = [];
                    }

                    $payment->gross_amount = (int) $pricingSummary['total'];
                    $payment->payload_json = json_encode(array_merge($existingPayload, [
                        'subtotal' => (int) $pricingSummary['subtotal'],
                        'tax' => (int) $pricingSummary['tax'],
                        'total' => (int) $pricingSummary['total'],
                        'rescheduled_at' => now()->toDateTimeString(),
                    ]), JSON_UNESCAPED_UNICODE);
                    $payment->save();
                }
            });
        } catch (\RuntimeException $exception) {
            $message = $exception->getMessage() ?: __('Reschedule gagal diproses.');

            return redirect()
                ->route('account.orders.show', $order)
                ->with('error', $message);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('account.orders.show', $order)
                ->with('error', __('Reschedule gagal diproses. Silakan coba lagi.'));
        }

        return redirect()
            ->route('account.orders.show', $order->fresh())
            ->with('success', __('Jadwal sewa berhasil diubah.'));
    }

    public function receipt(Request $request, Order $order)
    {
        $this->ensureOwnedOrder($request, $order);
        $this->markOrderNotificationsAsRead($request, $order);
        $order->loadMissing('damagePayment');

        if (! $order->canAccessInvoice()) {
            $message = $order->hasOutstandingDamageFee()
                ? __('Invoice tersedia setelah tagihan tambahan lunas.')
                : __('Invoice hanya tersedia setelah pembayaran berhasil.');

            return redirect()
                ->route('account.orders.show', $order)
                ->with('error', $message);
        }

        $order->load(['items.equipment', 'user.profile', 'payment']);

        return view('account.orders.receipt', [
            'order' => $order,
            'generatedAt' => now(),
        ]);
    }

    public function receiptPdf(Request $request, Order $order)
    {
        $this->ensureOwnedOrder($request, $order);
        $this->markOrderNotificationsAsRead($request, $order);
        $order->loadMissing('damagePayment');

        if (! $order->canAccessInvoice()) {
            $message = $order->hasOutstandingDamageFee()
                ? __('Invoice PDF tersedia setelah tagihan tambahan lunas.')
                : __('Invoice PDF hanya tersedia setelah pembayaran berhasil.');

            return redirect()
                ->route('account.orders.show', $order)
                ->with('error', $message);
        }

        $order->load(['items.equipment', 'user.profile', 'payment']);
        $paper = strtolower((string) $request->query('paper', 'a4'));
        $orientation = strtolower((string) $request->query('orientation', 'portrait'));
        $allowedPaper = ['a4', 'a5', 'a3', 'letter', 'legal'];
        if (! in_array($paper, $allowedPaper, true)) {
            $paper = 'a4';
        }
        if (! in_array($orientation, ['portrait', 'landscape'], true)) {
            $orientation = 'portrait';
        }

        $pdf = Pdf::loadView('account.orders.receipt-pdf', [
            'order' => $order,
            'generatedAt' => now(),
        ])->setPaper($paper, $orientation);

        $filename = 'Invoice-' . ($order->order_number ?: ('ORD-' . $order->id)) . '.pdf';
        $response = $request->boolean('inline')
            ? $pdf->stream($filename)
            : $pdf->download($filename);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function ensureOwnedOrder(Request $request, Order $order): void
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function markOrderNotificationsAsRead(Request $request, Order $order): void
    {
        if (! schema_table_exists_cached('order_notifications')) {
            return;
        }

        OrderNotification::query()
            ->where('user_id', (int) $request->user()->id)
            ->where('order_id', (int) $order->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }

    private function resolveOrderDurationDays(Order $order): int
    {
        try {
            $startDate = $order->rental_start_date ? Carbon::parse($order->rental_start_date)->startOfDay() : null;
            $endDate = $order->rental_end_date ? Carbon::parse($order->rental_end_date)->startOfDay() : null;
            if (! $startDate || ! $endDate || $endDate->lt($startDate)) {
                return 0;
            }

            return $startDate->diffInDays($endDate) + 1;
        } catch (\Throwable $exception) {
            return 0;
        }
    }

    private function bookingWindowEnd(): Carbon
    {
        return now()->addMonthsNoOverflow(3)->startOfDay();
    }
}
