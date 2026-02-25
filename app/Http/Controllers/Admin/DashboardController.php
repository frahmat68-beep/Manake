<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNotification;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $calendarMonth = (string) $request->query('calendar_month', '');

        if (! schema_table_exists_cached('orders')) {
            return view('admin.dashboard', [
                'operationalOrders' => collect(),
                'summary' => [
                    'ready_pickup' => 0,
                    'on_rent' => 0,
                    'returned' => 0,
                    'damaged' => 0,
                ],
                'financialSummary' => $this->buildFinancialSummary(),
                'rentalCalendar' => $this->buildRentalCalendar($calendarMonth),
            ]);
        }

        $basePaidOrders = Order::query()->where('status_pembayaran', 'paid');
        $summary = [
            'ready_pickup' => (clone $basePaidOrders)->where('status_pesanan', 'lunas')->count(),
            'on_rent' => (clone $basePaidOrders)->where('status_pesanan', 'barang_diambil')->count(),
            'returned' => (clone $basePaidOrders)->whereIn('status_pesanan', ['barang_kembali', 'selesai'])->count(),
            'damaged' => (clone $basePaidOrders)->where('status_pesanan', 'barang_rusak')->count(),
        ];

        $operationalOrders = (clone $basePaidOrders)
            ->with(['user:id,name,email', 'items.equipment:id,name'])
            ->whereIn('status_pesanan', ['lunas', 'barang_diambil', 'barang_rusak', 'barang_kembali'])
            ->orderByRaw("
                CASE status_pesanan
                    WHEN 'lunas' THEN 1
                    WHEN 'barang_diambil' THEN 2
                    WHEN 'barang_rusak' THEN 3
                    WHEN 'barang_kembali' THEN 4
                    ELSE 9
                END
            ")
            ->latest('updated_at')
            ->paginate(12);

        return view('admin.dashboard', [
            'summary' => $summary,
            'operationalOrders' => $operationalOrders,
            'financialSummary' => $this->buildFinancialSummary($basePaidOrders),
            'rentalCalendar' => $this->buildRentalCalendar($calendarMonth),
        ]);
    }

    public function updateOperationalStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status_pesanan' => ['required', 'in:barang_diambil,barang_kembali,barang_rusak'],
        ]);

        $nextStatus = $data['status_pesanan'];
        $prevStatus = (string) $order->status_pesanan;

        if (($order->status_pembayaran ?? 'pending') !== 'paid') {
            return back()->with('error', __('Order belum lunas, status operasional belum bisa diubah.'));
        }

        if ($nextStatus === 'barang_diambil') {
            $rentalStart = $order->rental_start_date ? $order->rental_start_date->copy()->startOfDay() : null;
            $pickupConfirmationOpenAt = $rentalStart?->copy()->subDay();
            if (! $rentalStart || ! $pickupConfirmationOpenAt || now()->lt($pickupConfirmationOpenAt)) {
                return back()->with('error', __('Konfirmasi barang diambil hanya bisa dilakukan mulai H-1 sebelum tanggal sewa.'));
            }
        }

        $allowedTransitions = match ($prevStatus) {
            Order::STATUS_READY_PICKUP => [Order::STATUS_ON_RENT],
            Order::STATUS_ON_RENT => [Order::STATUS_RETURNED_OK, Order::STATUS_RETURNED_DAMAGED],
            default => [],
        };

        if (! in_array($nextStatus, $allowedTransitions, true)) {
            return back()->with('error', __('Perubahan status tidak valid untuk kondisi order saat ini.'));
        }

        if ($prevStatus === $nextStatus) {
            return back()->with('success', __('Status operasional sudah sesuai.'));
        }

        $order->status_pesanan = $nextStatus;

        if ($nextStatus === 'barang_diambil' && ! $order->picked_up_at) {
            $order->picked_up_at = now();
        }

        if (in_array($nextStatus, ['barang_kembali', 'barang_rusak'], true) && ! $order->returned_at) {
            $order->returned_at = now();
        }

        if ($nextStatus === 'barang_rusak' && ! $order->damaged_at) {
            $order->damaged_at = now();
        }

        $order->save();

        if (schema_table_exists_cached('order_notifications')) {
            OrderNotification::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'type' => 'order_update',
                'title' => __('Update operasional') . ' ' . ($order->order_number ?: ('ORD-' . $order->id)),
                'message' => __('Status rental diperbarui: :before → :after.', [
                    'before' => $this->statusLabel($prevStatus),
                    'after' => $this->statusLabel($nextStatus),
                ]),
            ]);
        }

        admin_audit('order.update_operational_status', 'orders', $order->id, [
            'before' => $prevStatus,
            'after' => $nextStatus,
        ], auth('admin')->id());

        return back()->with('success', __('Status operasional berhasil diperbarui dan notifikasi user terkirim.'));
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'menunggu_pembayaran' => __('Menunggu Pembayaran'),
            'diproses' => __('Diproses Admin'),
            'lunas' => __('Siap Diambil'),
            'barang_diambil' => __('Barang Diambil'),
            'barang_kembali' => __('Barang Dikembalikan'),
            'barang_rusak' => __('Barang Rusak'),
            'barang_hilang' => __('Barang Hilang'),
            'overdue_denda' => __('Denda Overdue'),
            'selesai' => __('Selesai'),
            'expired' => __('Kedaluwarsa'),
            'dibatalkan' => __('Dibatalkan'),
            'refund' => __('Refund'),
            default => strtoupper((string) $status),
        };
    }

    private function buildRentalCalendar(string $monthValue = ''): array
    {
        $monthDate = $this->resolveMonthDate($monthValue);
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $dailyTotals = [];
        $events = collect();
        $activeOrderIds = [];

        if (schema_table_exists_cached('order_items') && schema_table_exists_cached('orders')) {
            $activeStatuses = [
                Order::STATUS_PROCESSING,
                Order::STATUS_READY_PICKUP,
                Order::STATUS_ON_RENT,
            ];

            $items = OrderItem::query()
                ->with(['order:id,order_number,midtrans_order_id,status_pesanan,status_pembayaran,rental_start_date,rental_end_date'])
                ->whereHas('order', function ($query) use ($monthStart, $monthEnd, $activeStatuses) {
                    $query->where('status_pembayaran', Order::PAYMENT_PAID)
                        ->whereIn('status_pesanan', $activeStatuses)
                        ->whereDate('rental_start_date', '<=', $monthEnd->toDateString())
                        ->whereDate('rental_end_date', '>=', $monthStart->toDateString());
                })
                ->get();

            foreach ($items as $item) {
                $order = $item->order;
                $itemStart = $item->rental_start_date
                    ? Carbon::parse($item->rental_start_date)->startOfDay()
                    : ($order?->rental_start_date ? Carbon::parse($order->rental_start_date)->startOfDay() : null);
                $itemEnd = $item->rental_end_date
                    ? Carbon::parse($item->rental_end_date)->startOfDay()
                    : ($order?->rental_end_date ? Carbon::parse($order->rental_end_date)->startOfDay() : null);

                if (! $itemStart || ! $itemEnd || $itemEnd->lt($itemStart)) {
                    continue;
                }

                $qty = max((int) ($item->qty ?? 0), 1);
                $rangeStart = $itemStart->lt($monthStart) ? $monthStart->copy() : $itemStart->copy();
                $rangeEnd = $itemEnd->gt($monthEnd) ? $monthEnd->copy() : $itemEnd->copy();

                for ($cursor = $rangeStart->copy(); $cursor->lte($rangeEnd); $cursor->addDay()) {
                    $dateKey = $cursor->toDateString();
                    $dailyTotals[$dateKey] = ($dailyTotals[$dateKey] ?? 0) + $qty;
                }

                if ($order && ! in_array($order->id, $activeOrderIds, true)) {
                    $activeOrderIds[] = $order->id;
                }

                $events->push([
                    'order_number' => $order?->order_number ?: $order?->midtrans_order_id ?: ('ORD-' . ($order?->id ?? $item->order_id)),
                    'start_date' => $itemStart->toDateString(),
                    'end_date' => $itemEnd->toDateString(),
                    'qty' => $qty,
                ]);
            }
        }

        $days = [];
        for ($cursor = $calendarStart->copy(); $cursor->lte($calendarEnd); $cursor->addDay()) {
            $dateKey = $cursor->toDateString();
            $days[] = [
                'date' => $dateKey,
                'day' => $cursor->day,
                'in_month' => $cursor->month === $monthStart->month,
                'total_qty' => (int) ($dailyTotals[$dateKey] ?? 0),
            ];
        }

        return [
            'month_label' => $monthStart->translatedFormat('F Y'),
            'month_value' => $monthStart->format('Y-m'),
            'previous_month' => $monthStart->copy()->subMonth()->format('Y-m'),
            'next_month' => $monthStart->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'total_unit_days' => array_sum($dailyTotals),
            'max_daily_units' => empty($dailyTotals) ? 0 : max($dailyTotals),
            'active_orders' => count($activeOrderIds),
            'events' => $events
                ->sortBy([
                    ['start_date', 'asc'],
                    ['order_number', 'asc'],
                ])
                ->values()
                ->take(8),
        ];
    }

    private function resolveMonthDate(string $monthValue): Carbon
    {
        if ($monthValue !== '' && preg_match('/^\d{4}-\d{2}$/', $monthValue) === 1) {
            return Carbon::createFromFormat('Y-m', $monthValue)->startOfMonth();
        }

        return now()->startOfMonth();
    }

    private function buildFinancialSummary($basePaidOrders = null): array
    {
        $summary = [
            'cash_in' => 0,
            'revenue' => 0,
            'tax' => 0,
            'damage_fee' => 0,
            'paid_orders' => 0,
        ];

        if (schema_table_exists_cached('orders')) {
            $paidOrdersQuery = $basePaidOrders
                ? (clone $basePaidOrders)
                : Order::query()->where('status_pembayaran', Order::PAYMENT_PAID);

            $paidOrders = $paidOrdersQuery
                ->get(['id', 'total_amount']);

            $summary['paid_orders'] = (int) $paidOrders->count();
            $summary['revenue'] = (int) $paidOrders->sum(fn (Order $order) => (int) ($order->total_amount ?? 0));
            $summary['tax'] = (int) $paidOrders->sum(fn (Order $order) => (int) round(((int) ($order->total_amount ?? 0)) * 0.11));
        }

        if (schema_table_exists_cached('payments')) {
            $summary['cash_in'] = (int) Payment::query()
                ->where('status', Order::PAYMENT_PAID)
                ->sum('gross_amount');

            $summary['damage_fee'] = (int) Payment::query()
                ->where('status', Order::PAYMENT_PAID)
                ->where('provider', Payment::PROVIDER_MIDTRANS_DAMAGE)
                ->sum('gross_amount');
        }

        if ($summary['cash_in'] <= 0) {
            $summary['cash_in'] = (int) ($summary['revenue'] + $summary['tax']);
        }

        return $summary;
    }
}
