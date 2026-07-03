<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentMaintenanceWindow;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AvailabilityService
{
    // ======================================================================
    // APA YANG SAYA LIHAT?
    // -> [SISTEM PENGHITUNG KETERSEDIAAN ALAT (OTAK RENTAL)]
    // Kelas ini adalah "mesin kalkulator" yang menghitung ketersediaan alat.
    //
    // 🎓 KEMUNGKINAN PERTANYAAN DOSEN:
    // 1. "Bagaimana program Anda menangani waktu jeda pembersihan/pemeliharaan alat setelah sewa?"
    // 2. "Berapa lama pesanan sewa yang belum dibayar akan mengunci stok barang (Hold Window)?"
    //
    // 🟢 APA YANG BISA SAYA UBAH? (Aman & Mudah)
    // - Durasi Penguncian Bayar (`HOLD_WINDOW_MINUTES`): Ubah `60` ke waktu lain dalam satuan menit (misal `30` menit untuk membatalkan booking otomatis jika belum dibayar dalam 30 menit).
    // - Jeda Pemeliharaan/Buffer (`BUFFER_DAYS`): Ubah `1` ke angka hari lain (misal `2` hari jika studio butuh 2 hari jeda sebelum kamera bisa disewakan kembali ke orang lain).
    //
    // 🟡 APA RISIKONYA? (Perlu Hati-hati)
    // - Status Blokir (`BLOCKING_ORDER_STATUSES`): Daftar status order yang dianggap sedang menyewa alat. Jangan ganti string di dalamnya kecuali nama status di model Order juga disesuaikan.
    // ======================================================================

    public const HOLD_WINDOW_MINUTES = 60;

    public const BUFFER_DAYS = 1;

    private const BLOCKING_ORDER_STATUSES = [
        'diproses',
        'lunas',
        'barang_diambil',
        'barang_rusak',
        'barang_hilang',
    ];

    public static function blockingOrderStatuses(): array
    {
        return self::BLOCKING_ORDER_STATUSES;
    }

    public static function holdWindowMinutes(): int
    {
        return self::HOLD_WINDOW_MINUTES;
    }

    public function evaluateRange(Equipment $equipment, Carbon|string $startDate, Carbon|string $endDate, int $requestedQty = 1, ?int $ignoreOrderId = null): array
    {
        $start = $this->normalizeDate($startDate);
        $end = $this->normalizeDate($endDate);
        $requestedQty = max($requestedQty, 1);

        if (! $start || ! $end || $end->lt($start)) {
            return [
                'ok' => false,
                'reason' => 'invalid_date_range',
                'conflicts' => [],
                'daily' => [],
            ];
        }

        if (($equipment->status ?? 'ready') !== 'ready') {
            $bufferedStart = $start->copy()->subDays(self::BUFFER_DAYS);
            $bufferedEnd = $end->copy()->addDays(self::BUFFER_DAYS);

            return [
                'ok' => false,
                'reason' => ($equipment->status ?? 'unavailable') === 'maintenance' ? 'maintenance' : 'equipment_unavailable',
                'conflicts' => $this->dateRangeKeys($bufferedStart, $bufferedEnd),
                'daily' => [],
            ];
        }

        $bufferedStart = $start->copy()->subDays(self::BUFFER_DAYS);
        $bufferedEnd = $end->copy()->addDays(self::BUFFER_DAYS);

        // getDailyReservedUnits menambahkan buffer sendiri secara internal,
        // jadi kita pass range asli, bukan yang sudah di-buffer.
        $daily = $this->getDailyReservedUnits($equipment, $start, $end, $ignoreOrderId);
        $conflicts = [];

        foreach ($this->dateRangeKeys($bufferedStart, $bufferedEnd) as $dateKey) {
            $reserved = (int) data_get($daily, $dateKey . '.qty', 0);
            $available = max((int) $equipment->stock - $reserved, 0);

            if ($requestedQty > $available) {
                $conflicts[] = $dateKey;
            }
        }

        return [
            'ok' => $conflicts === [],
            'reason' => $conflicts === [] ? null : 'insufficient_stock',
            'conflicts' => $conflicts,
            'daily' => $daily,
        ];
    }

    public function getBatchDailyReservedUnits(
        Collection $equipments,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreOrderId = null
    ): array {
        $start = $this->normalizeDate($startDate);
        $end = $this->normalizeDate($endDate);
        if (! $start || ! $end || $end->lt($start) || $equipments->isEmpty()) {
            return [];
        }

        $results = [];
        foreach ($equipments as $equipment) {
            $results[$equipment->id] = [];
        }

        $windowStart = $start->copy()->subDays(self::BUFFER_DAYS);
        $windowEnd = $end->copy()->addDays(self::BUFFER_DAYS);

        if (! schema_table_exists_cached('order_items') || ! schema_table_exists_cached('orders')) {
            return $results;
        }

        $holdCutoff = now()->subMinutes(self::HOLD_WINDOW_MINUTES);
        $equipmentIds = $equipments->pluck('id')->all();

        $items = OrderItem::query()
            ->with(['order'])
            ->whereIn('equipment_id', $equipmentIds)
            ->whereHas('order', function ($query) use ($windowStart, $windowEnd, $holdCutoff, $ignoreOrderId) {
                $query
                    ->when($ignoreOrderId, fn ($subQuery) => $subQuery->where('id', '!=', $ignoreOrderId))
                    ->whereDate('rental_start_date', '<=', $windowEnd->toDateString())
                    ->whereDate('rental_end_date', '>=', $windowStart->toDateString())
                    ->where(function ($statusQuery) use ($holdCutoff) {
                        $statusQuery->where(function ($pendingQuery) use ($holdCutoff) {
                            $pendingQuery->where('status_pesanan', 'menunggu_pembayaran')
                                ->where('created_at', '>=', $holdCutoff);
                        })->orWhereIn('status_pesanan', self::BLOCKING_ORDER_STATUSES);
                    });
            })
            ->get();

        $stockMap = $equipments->pluck('stock', 'id')->all();

        foreach ($items as $item) {
            $eId = $item->equipment_id;
            $order = $item->order;
            $bookingStart = $this->normalizeDate($item->rental_start_date ?? $order?->rental_start_date);
            $bookingEnd = $this->normalizeDate($item->rental_end_date ?? $order?->rental_end_date);
            if (! $bookingStart || ! $bookingEnd || $bookingEnd->lt($bookingStart)) {
                continue;
            }

            $qty = max((int) ($item->qty ?? 0), 1);
            $orderNumber = (string) ($order?->order_number ?: $order?->midtrans_order_id ?: ('ORD-' . $order?->id));

            for ($cursor = $bookingStart->copy(); $cursor->lte($bookingEnd); $cursor->addDay()) {
                $this->appendDailyReason($results[$eId], $cursor, $start, $end, $qty, [
                    'type' => 'booking',
                    'order_number' => $orderNumber,
                ]);
            }

            for ($offset = 1; $offset <= self::BUFFER_DAYS; $offset++) {
                $bufferBeforeDate = $bookingStart->copy()->subDays($offset);
                $this->appendDailyReason($results[$eId], $bufferBeforeDate, $start, $end, $qty, [
                    'type' => 'buffer_before',
                    'order_number' => $orderNumber,
                ]);

                $bufferAfterDate = $bookingEnd->copy()->addDays($offset);
                $this->appendDailyReason($results[$eId], $bufferAfterDate, $start, $end, $qty, [
                    'type' => 'buffer_after',
                    'order_number' => $orderNumber,
                ]);
            }
        }

        if (schema_table_exists_cached('equipment_maintenance_windows')) {
            $maintenanceWindows = EquipmentMaintenanceWindow::query()
                ->whereIn('equipment_id', $equipmentIds)
                ->whereDate('start_date', '<=', $end->toDateString())
                ->whereDate('end_date', '>=', $start->toDateString())
                ->get();

            foreach ($maintenanceWindows as $window) {
                $eId = $window->equipment_id;
                $maintenanceStart = $this->normalizeDate($window->start_date);
                $maintenanceEnd = $this->normalizeDate($window->end_date);
                if (! $maintenanceStart || ! $maintenanceEnd || $maintenanceEnd->lt($maintenanceStart)) {
                    continue;
                }

                $stock = (int) ($stockMap[$eId] ?? 1);
                for ($cursor = $maintenanceStart->copy(); $cursor->lte($maintenanceEnd); $cursor->addDay()) {
                    $this->appendDailyReason($results[$eId], $cursor, $start, $end, max($stock, 1), [
                        'type' => 'maintenance',
                        'reason' => $window->reason,
                    ]);
                }
            }
        }

        return $results;
    }


    public function getDailyReservedUnits(
        Equipment $equipment,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreOrderId = null
    ): array {
        $start = $this->normalizeDate($startDate);
        $end = $this->normalizeDate($endDate);
        if (! $start || ! $end || $end->lt($start)) {
            return [];
        }

        $windowStart = $start->copy()->subDays(self::BUFFER_DAYS);
        $windowEnd = $end->copy()->addDays(self::BUFFER_DAYS);
        $daily = [];

        $items = $this->fetchBlockingOrderItems($equipment->id, $windowStart, $windowEnd, $ignoreOrderId);
        foreach ($items as $item) {
            $order = $item->order;
            $bookingStart = $this->normalizeDate($item->rental_start_date ?? $order?->rental_start_date);
            $bookingEnd = $this->normalizeDate($item->rental_end_date ?? $order?->rental_end_date);
            if (! $bookingStart || ! $bookingEnd || $bookingEnd->lt($bookingStart)) {
                continue;
            }

            $qty = max((int) ($item->qty ?? 0), 1);
            $orderNumber = (string) ($order?->order_number ?: $order?->midtrans_order_id ?: ('ORD-' . $order?->id));

            for ($cursor = $bookingStart->copy(); $cursor->lte($bookingEnd); $cursor->addDay()) {
                $this->appendDailyReason($daily, $cursor, $start, $end, $qty, [
                    'type' => 'booking',
                    'order_number' => $orderNumber,
                ]);
            }

            for ($offset = 1; $offset <= self::BUFFER_DAYS; $offset++) {
                $bufferBeforeDate = $bookingStart->copy()->subDays($offset);
                $this->appendDailyReason($daily, $bufferBeforeDate, $start, $end, $qty, [
                    'type' => 'buffer_before',
                    'order_number' => $orderNumber,
                ]);

                $bufferAfterDate = $bookingEnd->copy()->addDays($offset);
                $this->appendDailyReason($daily, $bufferAfterDate, $start, $end, $qty, [
                    'type' => 'buffer_after',
                    'order_number' => $orderNumber,
                ]);
            }
        }

        if (schema_table_exists_cached('equipment_maintenance_windows')) {
            $maintenanceWindows = EquipmentMaintenanceWindow::query()
                ->where('equipment_id', $equipment->id)
                ->whereDate('start_date', '<=', $end->toDateString())
                ->whereDate('end_date', '>=', $start->toDateString())
                ->get();

            foreach ($maintenanceWindows as $window) {
                $maintenanceStart = $this->normalizeDate($window->start_date);
                $maintenanceEnd = $this->normalizeDate($window->end_date);
                if (! $maintenanceStart || ! $maintenanceEnd || $maintenanceEnd->lt($maintenanceStart)) {
                    continue;
                }

                for ($cursor = $maintenanceStart->copy(); $cursor->lte($maintenanceEnd); $cursor->addDay()) {
                    $this->appendDailyReason($daily, $cursor, $start, $end, max((int) $equipment->stock, 1), [
                        'type' => 'maintenance',
                        'reason' => $window->reason,
                    ]);
                }
            }
        }

        return $daily;
    }

    public function getBlockedSchedules(
        Equipment $equipment,
        ?int $currentUserId = null,
        ?Carbon $from = null,
        ?Carbon $to = null
    ): Collection {
        $start = $from?->copy()->startOfDay() ?: now()->startOfDay();
        $end = $to?->copy()->startOfDay() ?: now()->addDays(120)->startOfDay();

        $entries = collect();

        $windowStart = $start->copy()->subDays(self::BUFFER_DAYS);
        $windowEnd = $end->copy()->addDays(self::BUFFER_DAYS);
        $items = $this->fetchBlockingOrderItems($equipment->id, $windowStart, $windowEnd);
        foreach ($items as $item) {
            $order = $item->order;
            $bookingStart = $this->normalizeDate($item->rental_start_date ?? $order?->rental_start_date);
            $bookingEnd = $this->normalizeDate($item->rental_end_date ?? $order?->rental_end_date);
            if (! $bookingStart || ! $bookingEnd || $bookingEnd->lt($bookingStart)) {
                continue;
            }

            $isCurrentUser = $currentUserId !== null
                && (int) ($order?->user_id ?? 0) === (int) $currentUserId;

            $bookingWindowStart = $bookingStart->copy()->subDays(self::BUFFER_DAYS);
            $bookingWindowEnd = $bookingEnd->copy()->addDays(self::BUFFER_DAYS);
            if ($bookingWindowEnd->lt($start) || $bookingWindowStart->gt($end)) {
                continue;
            }

            $orderNumber = (string) ($order?->order_number ?: $order?->midtrans_order_id ?: ('ORD-' . $order?->id));
            $entries->push([
                'type' => 'booking',
                'order_number' => $orderNumber,
                'start_date' => $bookingStart->toDateString(),
                'end_date' => $bookingEnd->toDateString(),
                'qty' => max((int) ($item->qty ?? 0), 1),
                'label' => 'Booked',
                'reason' => null,
                'is_current_user' => $isCurrentUser,
            ]);

            for ($offset = 1; $offset <= self::BUFFER_DAYS; $offset++) {
                $bufferBeforeDate = $bookingStart->copy()->subDays($offset);
                if ($bufferBeforeDate->gte($start) && $bufferBeforeDate->lte($end)) {
                    $entries->push([
                        'type' => 'buffer_before',
                        'order_number' => $orderNumber,
                        'start_date' => $bufferBeforeDate->toDateString(),
                        'end_date' => $bufferBeforeDate->toDateString(),
                        'qty' => max((int) ($item->qty ?? 0), 1),
                        'label' => 'Buffer Sebelum Sewa',
                        'reason' => null,
                        'is_current_user' => $isCurrentUser,
                    ]);
                }

                $bufferAfterDate = $bookingEnd->copy()->addDays($offset);
                if ($bufferAfterDate->gte($start) && $bufferAfterDate->lte($end)) {
                    $entries->push([
                        'type' => 'buffer_after',
                        'order_number' => $orderNumber,
                        'start_date' => $bufferAfterDate->toDateString(),
                        'end_date' => $bufferAfterDate->toDateString(),
                        'qty' => max((int) ($item->qty ?? 0), 1),
                        'label' => 'Buffer Setelah Sewa',
                        'reason' => null,
                        'is_current_user' => $isCurrentUser,
                    ]);
                }
            }
        }

        if (schema_table_exists_cached('equipment_maintenance_windows')) {
            $maintenanceWindows = EquipmentMaintenanceWindow::query()
                ->where('equipment_id', $equipment->id)
                ->whereDate('start_date', '<=', $end->toDateString())
                ->whereDate('end_date', '>=', $start->toDateString())
                ->get();

            foreach ($maintenanceWindows as $window) {
                $maintenanceStart = $this->normalizeDate($window->start_date);
                $maintenanceEnd = $this->normalizeDate($window->end_date);
                if (! $maintenanceStart || ! $maintenanceEnd || $maintenanceEnd->lt($maintenanceStart)) {
                    continue;
                }

                $entries->push([
                    'type' => 'maintenance',
                    'order_number' => null,
                    'start_date' => $maintenanceStart->toDateString(),
                    'end_date' => $maintenanceEnd->toDateString(),
                    'qty' => max((int) $equipment->stock, 1),
                    'label' => 'Maintenance',
                    'reason' => $window->reason,
                    'is_current_user' => false,
                ]);
            }
        }

        return $entries
            ->sortBy([
                ['start_date', 'asc'],
                ['type', 'asc'],
            ])
            ->values();
    }

    private function fetchBlockingOrderItems(
        int $equipmentId,
        Carbon $windowStart,
        Carbon $windowEnd,
        ?int $ignoreOrderId = null
    ): Collection {
        if (! schema_table_exists_cached('order_items') || ! schema_table_exists_cached('orders')) {
            return collect();
        }

        $holdCutoff = now()->subMinutes(self::HOLD_WINDOW_MINUTES);

        return OrderItem::query()
            ->with(['order'])
            ->where('equipment_id', $equipmentId)
            ->whereHas('order', function ($query) use ($windowStart, $windowEnd, $holdCutoff, $ignoreOrderId) {
                $query
                    ->when($ignoreOrderId, fn ($subQuery) => $subQuery->where('id', '!=', $ignoreOrderId))
                    ->whereDate('rental_start_date', '<=', $windowEnd->toDateString())
                    ->whereDate('rental_end_date', '>=', $windowStart->toDateString())
                    ->where(function ($statusQuery) use ($holdCutoff) {
                        $statusQuery->where(function ($pendingQuery) use ($holdCutoff) {
                            $pendingQuery->where('status_pesanan', 'menunggu_pembayaran')
                                ->where('created_at', '>=', $holdCutoff);
                        })->orWhereIn('status_pesanan', self::BLOCKING_ORDER_STATUSES);
                    });
            })
            ->get();
    }

    private function appendDailyReason(
        array &$daily,
        Carbon $cursor,
        Carbon $windowStart,
        Carbon $windowEnd,
        int $qty,
        array $source
    ): void {
        if (! ($cursor->gte($windowStart) && $cursor->lte($windowEnd))) {
            return;
        }

        $dateKey = $cursor->toDateString();
        if (! isset($daily[$dateKey])) {
            $daily[$dateKey] = [
                'qty' => 0,
                'sources' => [],
            ];
        }

        $daily[$dateKey]['qty'] += max($qty, 0);
        $daily[$dateKey]['sources'][] = $source;
    }

    private function normalizeDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->startOfDay();
        }

        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function dateRangeKeys(Carbon $start, Carbon $end): array
    {
        $dateRange = [];
        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDay()) {
            $dateRange[] = $cursor->toDateString();
        }

        return $dateRange;
    }
}
