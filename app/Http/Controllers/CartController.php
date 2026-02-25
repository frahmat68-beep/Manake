<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Services\AvailabilityService;
use App\Services\CartService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function show(CartService $cart, PricingService $pricing)
    {
        $cartItems = $cart->items();
        $subtotal = $cart->subtotalPerDay();
        $estimatedSubtotal = collect($cartItems)->sum(fn (array $item) => $this->estimatedLineSubtotal($item));
        $pricingSummary = $pricing->calculateOrderTotals([], $estimatedSubtotal);
        $cartDateRange = $this->resolveCartDateLockRange($cartItems);
        $suggestedEquipments = collect();

        if (schema_table_exists_cached('equipments')) {
            $cartEquipmentIds = collect($cartItems)
                ->map(fn (array $item) => (int) ($item['equipment_id'] ?? $item['product_id'] ?? 0))
                ->filter(fn (int $equipmentId) => $equipmentId > 0)
                ->unique()
                ->values();

            $suggestionQuery = Equipment::query()
                ->with('category')
                ->where('stock', '>', 0)
                ->orderByDesc('updated_at')
                ->orderBy('name');

            if (schema_column_exists_cached('equipments', 'status')) {
                $suggestionQuery->where('status', 'ready');
            }

            if ($cartEquipmentIds->isNotEmpty()) {
                $suggestionQuery->whereNotIn('id', $cartEquipmentIds);
            }

            if (schema_table_exists_cached('order_items') && schema_table_exists_cached('orders')) {
                $suggestionQuery->withSum('activeOrderItems as reserved_units', 'qty');
            }

            $suggestedEquipments = $suggestionQuery
                ->limit(6)
                ->get()
                ->filter(fn (Equipment $equipment) => (int) ($equipment->available_units ?? $equipment->stock) > 0)
                ->take(4)
                ->values();
        }

        return view('cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'estimatedSubtotal' => $pricingSummary['subtotal'],
            'taxAmount' => $pricingSummary['tax'],
            'grandTotal' => $pricingSummary['total'],
            'suggestedEquipments' => $suggestedEquipments,
            'cartSuggestedStartDate' => $cartDateRange['start_date'] ?? null,
            'cartSuggestedEndDate' => $cartDateRange['end_date'] ?? null,
        ]);
    }

    public function add(Request $request, CartService $cart, AvailabilityService $availability)
    {
        $maxAllowedDate = $this->maxBookingDateString();
        $data = $request->validate([
            'equipment_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'integer', 'min:0'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
            'rental_start_date' => ['nullable', 'date', 'required_with:rental_end_date', 'after_or_equal:today', 'before_or_equal:' . $maxAllowedDate],
            'rental_end_date' => ['nullable', 'date', 'required_with:rental_start_date', 'after_or_equal:rental_start_date', 'before_or_equal:' . $maxAllowedDate],
        ]);

        if (empty($data['equipment_id']) && ! empty($data['product_id'])) {
            $data['equipment_id'] = $data['product_id'];
        }

        $equipmentId = (int) ($data['equipment_id'] ?? 0);
        if ($equipmentId <= 0) {
            return redirect()
                ->route('cart')
                ->with('error', __('Item cart tidak valid. Pilih alat dari katalog.'));
        }

        $equipment = Equipment::query()->with('category')->find($equipmentId);
        if (! $equipment) {
            return redirect()
                ->route('cart')
                ->with('error', __('Alat tidak ditemukan. Silakan refresh katalog.'));
        }

        $qty = max(1, min((int) ($data['qty'] ?? 1), 99));
        $startDate = $this->normalizeDateString($data['rental_start_date'] ?? null);
        $endDate = $this->normalizeDateString($data['rental_end_date'] ?? null);
        if (($startDate && ! $this->isDateWithinBookingWindow($startDate)) || ($endDate && ! $this->isDateWithinBookingWindow($endDate))) {
            return redirect()
                ->route('cart')
                ->with('error', __('Tanggal sewa hanya bisa dipilih dari hari ini sampai :date.', [
                    'date' => $this->formatBookingWindowEndDate(),
                ]));
        }

        $payload = [
            'equipment_id' => (int) $equipment->id,
            'name' => (string) $equipment->name,
            'slug' => (string) ($equipment->slug ?? ''),
            'category' => (string) ($equipment->category?->name ?? ''),
            'image' => $this->resolveEquipmentImage($equipment),
            'price' => (int) ($equipment->price_per_day ?? 0),
        ];
        if ($startDate && $endDate) {
            $payload['rental_start_date'] = $startDate;
            $payload['rental_end_date'] = $endDate;
        }

        $draftItems = collect($cart->items())->keyBy(fn (array $item) => (string) ($item['key'] ?? $this->resolveCartItemKey($item)));
        $draftKey = $this->resolveCartItemKey($payload);
        if ($draftItems->has($draftKey)) {
            $draft = $draftItems->get($draftKey);
            $draft['qty'] = max(1, min((int) ($draft['qty'] ?? 0) + $qty, 99));
            $draftItems->put($draftKey, $draft);
        } else {
            $payload['qty'] = $qty;
            $payload['key'] = $draftKey;
            $draftItems->put($draftKey, $payload);
        }

        $conflictMessage = $this->resolveCartConflictMessage(
            $draftItems->values()->all(),
            $availability
        );
        if ($conflictMessage) {
            return redirect()
                ->route('cart')
                ->with('error', $conflictMessage);
        }

        $cart->add($payload, $qty);

        return redirect()->route('cart')->with('success', __('ui.cart.messages.added'));
    }

    public function increment(string $key, CartService $cart, AvailabilityService $availability)
    {
        $draftItems = collect($cart->items())->keyBy(fn (array $item) => (string) ($item['key'] ?? $this->resolveCartItemKey($item)));
        if ($draftItems->has($key)) {
            $draft = $draftItems->get($key);
            $draft['qty'] = max(1, min((int) ($draft['qty'] ?? 0) + 1, 99));
            $draftItems->put($key, $draft);

            $conflictMessage = $this->resolveCartConflictMessage(
                $draftItems->values()->all(),
                $availability
            );
            if ($conflictMessage) {
                return redirect()
                    ->route('cart')
                    ->with('error', $conflictMessage);
            }
        }

        $cart->increase($key);

        return redirect()->route('cart')->with('success', __('ui.cart.messages.updated'));
    }

    public function decrement(string $key, CartService $cart)
    {
        $cart->decrease($key);

        return redirect()->route('cart')->with('success', __('ui.cart.messages.updated'));
    }

    public function update(Request $request, string $key, CartService $cart, AvailabilityService $availability)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $draftItems = collect($cart->items())->keyBy(fn (array $item) => (string) ($item['key'] ?? $this->resolveCartItemKey($item)));
        if ($draftItems->has($key)) {
            $draft = $draftItems->get($key);
            $draft['qty'] = max(1, min((int) $data['qty'], 99));
            $draftItems->put($key, $draft);

            $conflictMessage = $this->resolveCartConflictMessage(
                $draftItems->values()->all(),
                $availability
            );
            if ($conflictMessage) {
                return redirect()
                    ->route('cart')
                    ->with('error', $conflictMessage);
            }
        }

        $cart->updateQty($key, (int) $data['qty']);

        return redirect()->route('cart')->with('success', __('ui.cart.messages.updated'));
    }

    public function remove(string $key, CartService $cart)
    {
        $cart->remove($key);

        return redirect()->route('cart')->with('success', __('ui.cart.messages.removed'));
    }

    private function estimatedLineSubtotal(array $item): int
    {
        $qty = max((int) ($item['qty'] ?? 1), 1);
        $price = max((int) ($item['price'] ?? 0), 0);

        try {
            if (! empty($item['rental_start_date']) && ! empty($item['rental_end_date'])) {
                $start = Carbon::parse($item['rental_start_date'])->startOfDay();
                $end = Carbon::parse($item['rental_end_date'])->startOfDay();
                if ($end->gte($start)) {
                    $days = $start->diffInDays($end) + 1;

                    return $price * $qty * max($days, 1);
                }
            }
        } catch (\Throwable $exception) {
            // Fallback ke subtotal per hari ketika tanggal item tidak valid.
        }

        return $price * $qty;
    }

    private function resolveCartConflictMessage(array $draftItems, AvailabilityService $availability): ?string
    {
        $equipmentIds = collect($draftItems)
            ->map(fn (array $item) => (int) ($item['equipment_id'] ?? $item['product_id'] ?? 0))
            ->filter(fn (int $equipmentId) => $equipmentId > 0)
            ->unique()
            ->values();

        if ($equipmentIds->isEmpty()) {
            return null;
        }

        $equipments = Equipment::query()
            ->whereIn('id', $equipmentIds)
            ->get()
            ->keyBy('id');

        $missingIds = $equipmentIds->diff($equipments->keys());
        if ($missingIds->isNotEmpty()) {
            return __('Beberapa alat sudah tidak tersedia. Silakan refresh keranjang.');
        }

        $demandByEquipment = [];
        foreach ($draftItems as $item) {
            $equipmentId = (int) ($item['equipment_id'] ?? $item['product_id'] ?? 0);
            $equipment = $equipments->get($equipmentId);
            if (! $equipment) {
                continue;
            }

            $qty = max((int) ($item['qty'] ?? 1), 1);
            $stock = max((int) ($equipment->stock ?? 0), 0);
            if (($equipment->status ?? 'ready') !== 'ready') {
                return __(':name sedang tidak bisa disewa.', ['name' => $equipment->name]);
            }
            if ($stock < 1) {
                return __(':name sedang tidak tersedia.', ['name' => $equipment->name]);
            }
            if ($qty > $stock) {
                return __('Stok :name tersedia :stock unit.', ['name' => $equipment->name, 'stock' => $stock]);
            }

            $startDate = $this->normalizeDateString($item['rental_start_date'] ?? null);
            $endDate = $this->normalizeDateString($item['rental_end_date'] ?? null);
            if (! $startDate || ! $endDate) {
                $demandByEquipment[$equipmentId]['without_dates'] = (int) ($demandByEquipment[$equipmentId]['without_dates'] ?? 0) + $qty;
                continue;
            }
            if (! $this->isDateWithinBookingWindow($startDate) || ! $this->isDateWithinBookingWindow($endDate)) {
                return __('Tanggal sewa hanya bisa dipilih dari hari ini sampai :date.', [
                    'date' => $this->formatBookingWindowEndDate(),
                ]);
            }

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();
            if ($end->lt($start)) {
                continue;
            }

            $windowStart = $start->copy()->subDays(AvailabilityService::BUFFER_DAYS);
            $windowEnd = $end->copy()->addDays(AvailabilityService::BUFFER_DAYS);
            for ($cursor = $windowStart; $cursor->lte($windowEnd); $cursor->addDay()) {
                $dateKey = $cursor->toDateString();
                $demandByEquipment[$equipmentId]['daily'][$dateKey] = (int) ($demandByEquipment[$equipmentId]['daily'][$dateKey] ?? 0) + $qty;
            }
        }

        foreach ($demandByEquipment as $equipmentId => $demand) {
            $equipment = $equipments->get((int) $equipmentId);
            if (! $equipment) {
                continue;
            }

            $stock = max((int) ($equipment->stock ?? 0), 0);
            $withoutDates = (int) ($demand['without_dates'] ?? 0);
            if ($withoutDates > $stock) {
                return __('Stok :name tersedia :stock unit.', ['name' => $equipment->name, 'stock' => $stock]);
            }

            $dailyDemand = $demand['daily'] ?? [];
            if (! is_array($dailyDemand) || $dailyDemand === []) {
                continue;
            }

            $dateKeys = array_keys($dailyDemand);
            sort($dateKeys);
            $rangeStart = Carbon::parse($dateKeys[0])->startOfDay();
            $rangeEnd = Carbon::parse($dateKeys[count($dateKeys) - 1])->startOfDay();

            $reservedDaily = $availability->getDailyReservedUnits(
                $equipment,
                $rangeStart,
                $rangeEnd
            );

            $conflictDates = collect($dailyDemand)
                ->filter(function (int $requestedQty, string $dateKey) use ($reservedDaily, $stock) {
                    $reservedQty = (int) data_get($reservedDaily, $dateKey . '.qty', 0);
                    $availableQty = max($stock - $reservedQty, 0);

                    return $requestedQty > $availableQty;
                })
                ->keys()
                ->map(fn (string $dateKey) => Carbon::parse($dateKey)->translatedFormat('d M Y'))
                ->values();

            if ($conflictDates->isNotEmpty()) {
                return __(':name tidak tersedia untuk jumlah ini.', ['name' => $equipment->name]) . "\n"
                    . __('Tanggal bentrok: :dates.', ['dates' => $this->formatConflictDateList($conflictDates)]) . "\n"
                    . __('Silakan kurangi jumlah atau pilih tanggal lain.');
            }
        }

        return null;
    }

    private function normalizeDateString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function resolveCartItemKey(array $item): string
    {
        $dateKey = '';
        $startDate = $this->normalizeDateString($item['rental_start_date'] ?? null);
        $endDate = $this->normalizeDateString($item['rental_end_date'] ?? null);
        if ($startDate && $endDate) {
            $dateKey = '@' . $startDate . '_' . $endDate;
        }

        if (! empty($item['equipment_id'])) {
            return 'equipment:' . (int) $item['equipment_id'] . $dateKey;
        }

        if (! empty($item['product_id'])) {
            return 'product:' . (int) $item['product_id'] . $dateKey;
        }

        if (! empty($item['slug'])) {
            return 'slug:' . (string) $item['slug'] . $dateKey;
        }

        return 'item:' . (string) ($item['name'] ?? uniqid('', true)) . $dateKey;
    }

    private function resolveEquipmentImage(Equipment $equipment): string
    {
        $imagePath = $equipment->image_path ?: $equipment->image;
        if (is_string($imagePath) && $imagePath !== '') {
            if (Str::startsWith($imagePath, ['http://', 'https://'])) {
                return $imagePath;
            }

            return asset('storage/' . ltrim($imagePath, '/'));
        }

        return 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=600&q=80';
    }

    private function formatConflictDateList(\Illuminate\Support\Collection $dates, int $limit = 4): string
    {
        $visibleDates = $dates->take($limit);
        $remainingCount = max($dates->count() - $visibleDates->count(), 0);

        $result = $visibleDates->implode(', ');
        if ($remainingCount > 0) {
            $result .= __(', dan :count tanggal lain', ['count' => $remainingCount]);
        }

        return $result;
    }

    private function bookingWindowStart(): Carbon
    {
        return now()->startOfDay();
    }

    private function bookingWindowEnd(): Carbon
    {
        return now()->addMonthsNoOverflow(3)->startOfDay();
    }

    private function maxBookingDateString(): string
    {
        return $this->bookingWindowEnd()->toDateString();
    }

    private function isDateWithinBookingWindow(?string $date): bool
    {
        if (! $date) {
            return false;
        }

        try {
            $parsed = Carbon::parse($date)->startOfDay();
        } catch (\Throwable $exception) {
            return false;
        }

        return $parsed->betweenIncluded($this->bookingWindowStart(), $this->bookingWindowEnd());
    }

    private function formatBookingWindowEndDate(): string
    {
        return $this->bookingWindowEnd()->translatedFormat('d M Y');
    }

    private function resolveCartDateLockRange(array $cartItems): array
    {
        $validRanges = collect($cartItems)
            ->map(function (array $item): ?array {
                $startDate = $this->normalizeDateString($item['rental_start_date'] ?? null);
                $endDate = $this->normalizeDateString($item['rental_end_date'] ?? null);
                if (! $startDate || ! $endDate) {
                    return null;
                }
                if (! $this->isDateWithinBookingWindow($startDate) || ! $this->isDateWithinBookingWindow($endDate)) {
                    return null;
                }

                return [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
            })
            ->filter()
            ->values();

        if ($validRanges->isEmpty()) {
            return [];
        }

        $startDate = $validRanges
            ->pluck('start_date')
            ->sort()
            ->first();
        $endDate = $validRanges
            ->pluck('end_date')
            ->sortDesc()
            ->first();

        if (! is_string($startDate) || ! is_string($endDate)) {
            return [];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
