<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\OrderItem;
use App\Models\Order;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AvailabilityBoardController extends Controller
{
    public function index(Request $request, AvailabilityService $availability): View
    {
        try {
            $windowStartDate = $this->bookingWindowStart();
            $windowEndDate = $this->bookingWindowEnd();
            
            $monthRaw = (string) $request->query('month', '');
            $monthDate = $this->resolveMonthDate($monthRaw);
            $monthDate = $this->clampMonthDate($monthDate, $windowStartDate, $windowEndDate);
            
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();
            $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
            $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
            
            $dateRaw = (string) $request->query('date', '');
            $selectedDate = $this->resolveSelectedDate(
                $dateRaw,
                $calendarStart,
                $calendarEnd,
                $windowStartDate,
                $windowEndDate
            );
            $search = trim((string) $request->query('q', ''));

            if (! schema_table_exists_cached('equipments')) {
                return $this->fallbackView($search, $monthDate, $monthStart, $monthEnd, $calendarStart, $calendarEnd, $selectedDate, $windowStartDate, $windowEndDate);
            }

            $dateKeys = [];
            $calendarTotals = [];
            for ($cursor = $calendarStart->copy(); $cursor->lte($calendarEnd); $cursor->addDay()) {
                $dateKey = $cursor->toDateString();
                $dateKeys[] = $dateKey;
                $calendarTotals[$dateKey] = [
                    'reserved_units' => 0,
                    'busy_equipments' => 0,
                    'booking_equipments' => 0,
                    'buffer_equipments' => 0,
                    'status_blocked_equipments' => 0,
                ];
            }

            $equipmentQuery = Equipment::query()
                ->with('category')
                ->orderBy('name');

            if ($search !== '') {
                $equipmentQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                });
            }

            $equipments = $equipmentQuery->limit(24)->get();
            $equipmentIds = $equipments->pluck('id')->all();

            // Optimization: Load all reservations for these equipments in one go
            $allReservations = [];
            if (!empty($equipmentIds)) {
                $allReservations = $availability->getBatchDailyReservedUnits($equipments, $calendarStart, $calendarEnd);
            }

            $selectedDateKey = $selectedDate->toDateString();
            $equipmentRows = collect();
            $selectedBusyRows = collect();
            $selectedFreeRows = collect();

            foreach ($equipments as $equipment) {
                $daily = $allReservations[$equipment->id] ?? [];
                $statusValue = (string) ($equipment->status ?? 'ready');
                $statusLabel = $this->resolveEquipmentStatusLabel($statusValue);

                $dayCells = [];
                foreach ($dateKeys as $dateKey) {
                    $reserved = (int) data_get($daily, $dateKey . '.qty', 0);
                    $available = max((int) $equipment->stock - $reserved, 0);
                    $isBlockedByStatus = $statusValue !== 'ready';
                    $isBlocked = $isBlockedByStatus || $available <= 0;
                    $sourceTypes = collect(data_get($daily, $dateKey . '.sources', []))
                        ->pluck('type')
                        ->filter(fn ($type) => is_string($type) && $type !== '')
                        ->values();
                    $hasBooking = $sourceTypes->contains(fn (string $type) => in_array($type, ['booking', 'maintenance'], true));
                    $hasBuffer = $sourceTypes->contains(fn (string $type) => in_array($type, ['buffer_before', 'buffer_after'], true));

                    $dayCells[$dateKey] = [
                        'reserved' => $reserved,
                        'available' => $available,
                        'is_blocked' => $isBlocked,
                    ];

                    if ($reserved > 0) {
                        $calendarTotals[$dateKey]['reserved_units'] += $reserved;
                    }
                    if ($isBlocked || $reserved > 0) {
                        $calendarTotals[$dateKey]['busy_equipments']++;
                    }
                    if ($isBlockedByStatus) {
                        $calendarTotals[$dateKey]['status_blocked_equipments']++;
                    }
                    if ($hasBooking || $isBlockedByStatus) {
                        $calendarTotals[$dateKey]['booking_equipments']++;
                    } elseif ($hasBuffer) {
                        $calendarTotals[$dateKey]['buffer_equipments']++;
                    }
                }

                $selectedCell = $dayCells[$selectedDateKey] ?? [
                    'reserved' => 0,
                    'available' => (int) $equipment->stock,
                    'is_blocked' => $statusValue !== 'ready',
                ];

                $sources = collect(data_get($daily, $selectedDateKey . '.sources', []));
                $sourceLabels = $sources
                    ->pluck('type')
                    ->filter(fn ($type) => is_string($type) && $type !== '')
                    ->map(fn (string $type) => $this->resolveSourceLabel($type))
                    ->unique()
                    ->values();

                if ($sourceLabels->isEmpty() && $statusValue !== 'ready') {
                    $sourceLabels = collect([$statusLabel]);
                }

                $orderNumbers = $sources
                    ->pluck('order_number')
                    ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                    ->unique()
                    ->values();

                $row = [
                    'id' => (int) $equipment->id,
                    'name' => (string) $equipment->name,
                    'category' => (string) ($equipment->category?->name ?? '-'),
                    'category_id' => (int) ($equipment->category_id ?? 0),
                    'slug' => (string) ($equipment->slug ?? ''),
                    'price_per_day' => (int) ($equipment->price_per_day ?? 0),
                    'image_url' => $this->resolveEquipmentImageUrl($equipment),
                    'stock' => (int) ($equipment->stock ?? 0),
                    'status' => $statusValue,
                    'status_label' => $statusLabel,
                    'status_badge_class' => $this->resolveStatusBadgeClass($statusValue),
                    'day_cells' => $dayCells,
                    'selected_reserved' => (int) ($selectedCell['reserved'] ?? 0),
                    'selected_available' => (int) ($selectedCell['available'] ?? 0),
                    'selected_is_blocked' => (bool) ($selectedCell['is_blocked'] ?? false),
                    'source_labels' => $sourceLabels,
                    'order_numbers' => $orderNumbers,
                ];

                $equipmentRows->push($row);

                if (($row['selected_is_blocked'] ?? false) || ($row['selected_reserved'] ?? 0) > 0) {
                    $selectedBusyRows->push($row);
                } else {
                    $selectedFreeRows->push($row);
                }
            }

            $totalEquipments = $equipmentRows->count();
            $selectedBusyCount = $selectedBusyRows->count();
            $selectedReservedUnits = (int) $selectedBusyRows->sum('selected_reserved');

            $calendarDays = collect($dateKeys)->map(function (string $dateKey) use ($calendarTotals, $calendarStart, $monthStart, $selectedDateKey, $totalEquipments, $windowStartDate, $windowEndDate) {
                $date = Carbon::parse($dateKey)->startOfDay();
                $busyEquipments = (int) data_get($calendarTotals, $dateKey . '.busy_equipments', 0);
                $reservedUnits = (int) data_get($calendarTotals, $dateKey . '.reserved_units', 0);
                $bookingEquipments = (int) data_get($calendarTotals, $dateKey . '.booking_equipments', 0);
                $bufferEquipments = (int) data_get($calendarTotals, $dateKey . '.buffer_equipments', 0);
                $isSelectable = $date->betweenIncluded($windowStartDate, $windowEndDate);

                $tone = 'calm';
                if ($bookingEquipments > 0) {
                    $tone = 'critical';
                } elseif ($bufferEquipments > 0) {
                    $tone = 'busy';
                }

                return [
                    'date' => $dateKey,
                    'day' => $date->day,
                    'in_month' => $date->month === $monthStart->month,
                    'is_today' => $date->isSameDay(now()),
                    'is_selected' => $dateKey === $selectedDateKey,
                    'is_selectable' => $isSelectable,
                    'reserved_units' => $reservedUnits,
                    'busy_equipments' => $busyEquipments,
                    'booking_equipments' => $bookingEquipments,
                    'buffer_equipments' => $bufferEquipments,
                    'available_equipments' => max($totalEquipments - $busyEquipments, 0),
                    'tone' => $tone,
                    'week_index' => $date->isBefore($calendarStart) ? 0 : floor($date->diffInDays($calendarStart) / 7),
                ];
            });

            $monthlySchedules = $this->loadMonthlySchedules(
                $equipmentRows->pluck('id')->filter()->values(),
                $calendarStart,
                $calendarEnd
            );
            $dailySchedulesByDate = $this->buildDailySchedulesByDate($monthlySchedules, $calendarStart, $calendarEnd);

            return view('availability.board', [
                'search' => $search,
                'monthDate' => $monthDate,
                'monthStart' => $monthStart,
                'monthEnd' => $monthEnd,
                'calendarStart' => $calendarStart,
                'calendarEnd' => $calendarEnd,
                'selectedDate' => $selectedDate,
                'windowStartDate' => $windowStartDate,
                'windowEndDate' => $windowEndDate,
                'dateKeys' => $dateKeys,
                'calendarDays' => $calendarDays,
                'equipmentRows' => $equipmentRows,
                'selectedBusyRows' => $selectedBusyRows
                    ->sortByDesc('selected_reserved')
                    ->values(),
                'selectedFreeRows' => $selectedFreeRows
                    ->sortBy('name')
                    ->values(),
                'monthlySchedules' => $monthlySchedules,
                'dailySchedulesByDate' => $dailySchedulesByDate,
                'summary' => [
                    'total_equipments' => $totalEquipments,
                    'busy_equipments' => $selectedBusyCount,
                    'available_equipments' => max($totalEquipments - $selectedBusyCount, 0),
                    'reserved_units' => $selectedReservedUnits,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return $this->fallbackView($search ?? '', now()->startOfMonth(), now()->startOfMonth(), now()->endOfMonth(), now()->startOfWeek(), now()->endOfWeek(), now(), now(), now()->addMonths(3));
        }
    }

    private function fallbackView($search, $monthDate, $monthStart, $monthEnd, $calendarStart, $calendarEnd, $selectedDate, $windowStartDate, $windowEndDate)
    {
        return view('availability.board', [
            'search' => $search,
            'monthDate' => $monthDate,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'calendarStart' => $calendarStart,
            'calendarEnd' => $calendarEnd,
            'selectedDate' => $selectedDate,
            'windowStartDate' => $windowStartDate,
            'windowEndDate' => $windowEndDate,
            'dateKeys' => [],
            'calendarDays' => collect(),
            'equipmentRows' => collect(),
            'selectedBusyRows' => collect(),
            'selectedFreeRows' => collect(),
            'monthlySchedules' => collect(),
            'dailySchedulesByDate' => [],
            'summary' => [
                'total_equipments' => 0,
                'busy_equipments' => 0,
                'available_equipments' => 0,
                'reserved_units' => 0,
            ],
            'error' => true,
        ]);
    }


    private function loadMonthlySchedules(Collection $equipmentIds, Carbon $calendarStart, Carbon $calendarEnd): Collection
    {
        if (
            ! schema_table_exists_cached('order_items')
            || ! schema_table_exists_cached('orders')
        ) {
            return collect();
        }

        return OrderItem::query()
            ->with(['order:id,user_id,order_number,midtrans_order_id,status_pesanan,status_pembayaran,rental_start_date,rental_end_date,created_at'])
            ->whereIn('equipment_id', $equipmentIds)
            ->whereHas('order', function ($query) use ($calendarStart, $calendarEnd) {
                $query->whereIn('status_pesanan', Order::HOLD_SLOT_STATUSES)
                    ->whereDate('rental_start_date', '<=', $calendarEnd->toDateString())
                    ->whereDate('rental_end_date', '>=', $calendarStart->toDateString());
            })
            ->limit(180)
            ->get()
            ->map(function (OrderItem $item) {
                $startDate = $item->rental_start_date ?: $item->order?->rental_start_date;
                $endDate = $item->rental_end_date ?: $item->order?->rental_end_date;

                if (! $startDate || ! $endDate) {
                    return null;
                }

                return [
                    'id' => (int) $item->id,
                    'equipment_id' => (int) $item->equipment_id,
                    'order_number' => (string) ($item->order?->order_number ?: $item->order?->midtrans_order_id),
                    'start_date' => (string) $startDate->toDateString(),
                    'end_date' => (string) $endDate->toDateString(),
                    'qty' => (int) $item->qty,
                ];
            })
            ->filter()
            ->values();
    }

    private function buildDailySchedulesByDate(Collection $monthlySchedules, Carbon $calendarStart, Carbon $calendarEnd): array
    {
        $result = [];
        for ($cursor = $calendarStart->copy(); $cursor->lte($calendarEnd); $cursor->addDay()) {
            $result[$cursor->toDateString()] = [];
        }

        foreach ($monthlySchedules as $schedule) {
            $startDate = $this->parseDate(data_get($schedule, 'start_date'));
            $endDate = $this->parseDate(data_get($schedule, 'end_date'));

            if (! $startDate || ! $endDate) {
                continue;
            }

            for ($cursor = $startDate->copy(); $cursor->lte($endDate); $cursor->addDay()) {
                $dateKey = $cursor->toDateString();
                if (isset($result[$dateKey])) {
                    $result[$dateKey][] = $schedule;
                }
            }
        }

        foreach ($result as $dateKey => $rows) {
            usort($rows, function (array $left, array $right): int {
                $res = strcmp((string) data_get($left, 'start_date', ''), (string) data_get($right, 'start_date', ''));
                if ($res === 0) {
                    return ((int) data_get($left, 'id', 0)) - ((int) data_get($right, 'id', 0));
                }
                return $res;
            });
            $result[$dateKey] = $rows;
        }

        return $result;
    }

    private function bookingWindowStart(): Carbon
    {
        return now()->startOfDay();
    }

    private function bookingWindowEnd(): Carbon
    {
        return now()->addMonths(4)->endOfMonth();
    }

    private function resolveMonthDate(string $raw): Carbon
    {
        if ($raw === '') {
            return now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $raw)->startOfMonth();
        } catch (\Throwable $exception) {
            return now()->startOfMonth();
        }
    }

    private function clampMonthDate(Carbon $date, Carbon $start, Carbon $end): Carbon
    {
        if ($date->isBefore($start->copy()->startOfMonth())) {
            return $start->copy()->startOfMonth();
        }
        if ($date->isAfter($end->copy()->startOfMonth())) {
            return $end->copy()->startOfMonth();
        }
        return $date;
    }

    private function resolveSelectedDate(string $raw, Carbon $calendarStart, Carbon $calendarEnd, Carbon $windowStart, Carbon $windowEnd): Carbon
    {
        $selectedDate = null;
        if ($raw !== '') {
            try {
                $selectedDate = Carbon::parse($raw)->startOfDay();
            } catch (\Throwable $exception) {
                $selectedDate = null;
            }
        }

        if (! $selectedDate) {
            $selectedDate = now()->startOfDay();
        }

        if ($selectedDate->isBefore($calendarStart)) {
            $selectedDate = $calendarStart->copy();
        }
        if ($selectedDate->isAfter($calendarEnd)) {
            $selectedDate = $calendarEnd->copy();
        }

        if (! $selectedDate->betweenIncluded($windowStart, $windowEnd)) {
            return now()->startOfDay();
        }

        return $selectedDate;
    }

    private function resolveEquipmentImageUrl(Equipment $equipment): ?string
    {
        $imagePath = (string) ($equipment->main_image_url ?? $equipment->image_url ?? '');
        if ($imagePath === '') {
            return null;
        }

        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }

        if (function_exists('site_media_url')) {
            $resolvedImageUrl = site_media_url($imagePath);
            if ($resolvedImageUrl) {
                return $resolvedImageUrl;
            }
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }

    private function resolveEquipmentStatusLabel(string $status): string
    {
        return match ($status) {
            'ready' => 'Tersedia',
            'maintenance' => 'Pemeliharaan',
            'broken' => 'Rusak',
            'unavailable' => 'Tidak Tersedia',
            default => str($status)->replace('_', ' ')->title()->value(),
        };
    }

    private function resolveSourceLabel(string $type): string
    {
        return match ($type) {
            'booking' => 'Disewa',
            'maintenance' => 'Pemeliharaan',
            'buffer_before' => 'Persiapan (Buffer)',
            'buffer_after' => 'Pengembalian (Buffer)',
            default => str($type)->replace('_', ' ')->title()->value(),
        };
    }

    private function resolveStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'ready' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
            'maintenance' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
            'broken' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-400',
        };
    }

    private function parseDate(mixed $value): ?Carbon
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
}
