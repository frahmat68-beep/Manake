<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AvailabilityBoardController extends Controller
{
    public function index(Request $request, AvailabilityService $availability): View| \Illuminate\Http\JsonResponse
    {
        try {
            $windowStartDate = now()->startOfDay();
            $windowEndDate = now()->addMonths(3);
            $monthDate = now()->startOfMonth();
            $monthStart = $monthDate->copy();
            $monthEnd = $monthDate->copy()->endOfMonth();
            $calendarStart = $monthStart->copy()->startOfWeek();
            $calendarEnd = $monthEnd->copy()->endOfWeek();
            $selectedDate = now()->startOfDay();
            $search = '';

            return view('availability.board', [
                'search' => '',
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
            $equipmentIds->isEmpty()
            || ! schema_table_exists_cached('order_items')
            || ! schema_table_exists_cached('orders')
        ) {
            return collect();
        }

        return OrderItem::query()
            ->with(['equipment', 'order'])
            ->whereIn('equipment_id', $equipmentIds->all())
            ->whereHas('order', function ($query) use ($calendarStart, $calendarEnd) {
                $query
                    ->whereIn('status_pesanan', Order::HOLD_SLOT_STATUSES)
                    ->whereDate('rental_start_date', '<=', $calendarEnd->toDateString())
                    ->whereDate('rental_end_date', '>=', $calendarStart->toDateString());
            })
            ->latest('id')
            ->limit(180)
            ->get()
            ->map(function (OrderItem $item) {
                $startDate = $item->rental_start_date ?: $item->order?->rental_start_date;
                $endDate = $item->rental_end_date ?: $item->order?->rental_end_date;
                if (! $startDate || ! $endDate) {
                    return null;
                }

                return [
                    'equipment_name' => (string) ($item->equipment?->name ?? 'Equipment'),
                    'order_number' => (string) ($item->order?->order_number ?? ('ORD-' . ($item->order?->id ?? 0))),
                    'status_pesanan' => (string) ($item->order?->status_pesanan ?? '-'),
                    'qty' => max((int) ($item->qty ?? 0), 1),
                    'start_date' => Carbon::parse($startDate)->toDateString(),
                    'end_date' => Carbon::parse($endDate)->toDateString(),
                ];
            })
            ->filter()
            ->sortBy([
                ['start_date', 'asc'],
                ['equipment_name', 'asc'],
            ])
            ->values();
    }

    private function buildDailySchedulesByDate(Collection $monthlySchedules, Carbon $calendarStart, Carbon $calendarEnd): array
    {
        if ($monthlySchedules->isEmpty()) {
            return [];
        }

        $result = [];

        foreach ($monthlySchedules as $schedule) {
            $startDate = $this->parseDate(data_get($schedule, 'start_date'));
            $endDate = $this->parseDate(data_get($schedule, 'end_date'));

            if (! $startDate || ! $endDate || $endDate->lt($startDate)) {
                continue;
            }

            $rangeStart = $startDate->lt($calendarStart) ? $calendarStart->copy() : $startDate->copy();
            $rangeEnd = $endDate->gt($calendarEnd) ? $calendarEnd->copy() : $endDate->copy();

            for ($cursor = $rangeStart->copy(); $cursor->lte($rangeEnd); $cursor->addDay()) {
                $dateKey = $cursor->toDateString();
                if (! array_key_exists($dateKey, $result)) {
                    $result[$dateKey] = [];
                }

                $result[$dateKey][] = [
                    'equipment_name' => (string) data_get($schedule, 'equipment_name', 'Equipment'),
                    'status_pesanan' => (string) data_get($schedule, 'status_pesanan', '-'),
                    'status_label' => $this->resolveOrderStatusLabel((string) data_get($schedule, 'status_pesanan', '')),
                    'qty' => max((int) data_get($schedule, 'qty', 1), 1),
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ];
            }
        }

        foreach ($result as $dateKey => $rows) {
            usort($rows, function (array $left, array $right): int {
                $leftName = (string) ($left['equipment_name'] ?? '');
                $rightName = (string) ($right['equipment_name'] ?? '');
                $nameCompare = strcasecmp($leftName, $rightName);
                if ($nameCompare !== 0) {
                    return $nameCompare;
                }

                return strcasecmp((string) ($left['status_label'] ?? ''), (string) ($right['status_label'] ?? ''));
            });

            $result[$dateKey] = $rows;
        }

        return $result;
    }

    private function resolveMonthDate(string $monthValue): Carbon
    {
        if ($monthValue !== '' && preg_match('/^\d{4}-\d{2}$/', $monthValue) === 1) {
            try {
                return Carbon::createFromFormat('Y-m', $monthValue)->startOfMonth();
            } catch (\Throwable $exception) {
                // Fallback to current month.
            }
        }

        return now()->startOfMonth();
    }

    private function resolveSelectedDate(
        string $selectedValue,
        Carbon $calendarStart,
        Carbon $calendarEnd,
        Carbon $windowStartDate,
        Carbon $windowEndDate
    ): Carbon
    {
        $defaultDate = $windowStartDate->copy();
        $selectedDate = null;

        if ($selectedValue !== '') {
            try {
                $selectedDate = Carbon::parse($selectedValue)->startOfDay();
            } catch (\Throwable $exception) {
                $selectedDate = null;
            }
        }

        if (! $selectedDate) {
            $selectedDate = $defaultDate;
        }

        if ($selectedDate->lt($windowStartDate)) {
            $selectedDate = $windowStartDate->copy();
        }

        if ($selectedDate->gt($windowEndDate)) {
            $selectedDate = $windowEndDate->copy();
        }

        if ($selectedDate->lt($calendarStart) || $selectedDate->gt($calendarEnd)) {
            if ($windowStartDate->gt($calendarStart) && $windowStartDate->lt($calendarEnd)) {
                return $windowStartDate->copy()->startOfDay();
            }

            return $calendarStart->copy()->startOfDay();
        }

        return $selectedDate;
    }

    private function clampMonthDate(Carbon $monthDate, Carbon $windowStartDate, Carbon $windowEndDate): Carbon
    {
        $minMonth = $windowStartDate->copy()->startOfMonth();
        $maxMonth = $windowEndDate->copy()->startOfMonth();

        if ($monthDate->lt($minMonth)) {
            return $minMonth;
        }

        if ($monthDate->gt($maxMonth)) {
            return $maxMonth;
        }

        return $monthDate;
    }

    private function bookingWindowStart(): Carbon
    {
        return now()->startOfDay();
    }

    private function bookingWindowEnd(): Carbon
    {
        return now()->addMonthsNoOverflow(3)->startOfDay();
    }

    private function resolveEquipmentStatusLabel(string $status): string
    {
        return match ($status) {
            'ready' => 'Ready',
            'maintenance' => 'Maintenance',
            'unavailable' => 'Unavailable',
            default => Str::of($status)->replace('_', ' ')->title()->value(),
        };
    }

    private function resolveStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'ready' => 'bg-emerald-100 text-emerald-700',
            'maintenance' => 'bg-amber-100 text-amber-700',
            'unavailable' => 'bg-rose-100 text-rose-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    private function resolveOrderStatusLabel(string $status): string
    {
        return match ($status) {
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'diproses' => 'Diproses',
            'lunas' => 'Lunas',
            'barang_dipinjam' => 'Sedang Disewa',
            'barang_diambil' => 'Sedang Disewa',
            'dikembalikan' => 'Dikembalikan',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'barang_rusak' => 'Klaim Kerusakan',
            default => Str::of($status)->replace('_', ' ')->title()->value(),
        };
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function resolveEquipmentImageUrl(Equipment $equipment): ?string
    {
        $path = (string) ($equipment->main_image_url ?? $equipment->image_url ?? $equipment->image_path ?? $equipment->image ?? '');
        
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        try {
            $resolved = site_media_url($path);
            if ($resolved) {
                return $resolved;
            }
        } catch (\Throwable $e) {}

        return asset('storage/' . ltrim($path, '/'));
    }

    private function resolveSourceLabel(string $type): string
    {
        return match ($type) {
            'booking' => 'Dipakai',
            'buffer_before' => 'Buffer Sebelum',
            'buffer_after' => 'Buffer Sesudah',
            'maintenance' => 'Maintenance',
            default => Str::of($type)->replace('_', ' ')->title()->value(),
        };
    }
}
