<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EquipmentStoreRequest;
use App\Http\Requests\Admin\EquipmentUpdateRequest;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\OrderItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->trim()->value();
        $status = $request->string('status')->trim()->value();
        $categorySlug = $request->string('category')->trim()->value();

        $query = Equipment::query()
            ->with('category')
            ->orderByDesc('updated_at');
        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            $query->withSum('activeOrderItems as reserved_units', 'qty');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['ready', 'unavailable', 'maintenance'], true)) {
            $query->where('status', $status);
        }

        $categories = Category::query()->orderBy('name')->get();
        $activeCategory = null;

        if ($categorySlug !== '') {
            $activeCategory = $categories->firstWhere('slug', $categorySlug);
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        $equipments = $query->paginate(10)->withQueryString();

        return view('admin.equipments.index', [
            'equipments' => $equipments,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'search' => $search,
            'status' => $status,
            'activePage' => 'equipments',
        ]);
    }

    public function create()
    {
        return view('admin.equipments.create', [
            'categories' => Category::query()->orderBy('name')->get(),
            'activePage' => 'equipments',
        ]);
    }

    public function store(EquipmentStoreRequest $request)
    {
        $data = $request->validated();
        $this->normalizeSpecificationsPayload($data);
        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?? null, $data['name']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('equipments', 'public');
        }

        $equipment = Equipment::create($data);
        admin_audit('equipment.store', 'equipments', $equipment->id, [
            'name' => $equipment->name,
            'slug' => $equipment->slug,
            'status' => $equipment->status,
            'stock' => $equipment->stock,
        ], auth('admin')->id());

        return redirect()
            ->route('admin.equipments.index')
            ->with('success', __('Equipment berhasil ditambahkan.'));
    }

    public function edit(Request $request, string $slug)
    {
        $equipmentQuery = Equipment::query()
            ->where('slug', $slug)
            ->with('category');
        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            $equipmentQuery->withSum('activeOrderItems as reserved_units', 'qty');
        }
        $equipment = $equipmentQuery->firstOrFail();
        $bookingCalendar = $this->buildBookingCalendar(
            $equipment->id,
            $request->string('month')->trim()->value()
        );

        return view('admin.equipments.edit', [
            'equipment' => $equipment,
            'categories' => Category::query()->orderBy('name')->get(),
            'bookingCalendar' => $bookingCalendar,
            'activePage' => 'equipments',
        ]);
    }

    public function update(EquipmentUpdateRequest $request, string $slug)
    {
        $equipment = Equipment::query()->where('slug', $slug)->firstOrFail();
        $data = $request->validated();
        $this->normalizeSpecificationsPayload($data);
        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?? null, $data['name'], $equipment->id);

        if ($request->hasFile('image')) {
            if ($equipment->image_path) {
                Storage::disk('public')->delete($equipment->image_path);
            }
            $data['image_path'] = $request->file('image')->store('equipments', 'public');
        }

        $equipment->update($data);
        admin_audit('equipment.update', 'equipments', $equipment->id, $data, auth('admin')->id());

        return redirect()
            ->route('admin.equipments.index')
            ->with('success', __('Equipment berhasil diperbarui.'));
    }

    public function destroy(string $slug)
    {
        $equipment = Equipment::query()->where('slug', $slug)->firstOrFail();
        $snapshot = $equipment->only(['id', 'name', 'slug', 'status', 'stock']);

        if ($equipment->image_path) {
            Storage::disk('public')->delete($equipment->image_path);
        }

        $equipment->delete();
        admin_audit('equipment.destroy', 'equipments', $snapshot['id'], $snapshot, auth('admin')->id());

        return redirect()
            ->route('admin.equipments.index')
            ->with('success', __('Equipment berhasil dihapus.'));
    }

    private function generateUniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        if ($base === '') {
            $base = 'equipment';
        }

        $candidate = $base;
        $counter = 2;

        while ($this->slugExists($candidate, $ignoreId)) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Equipment::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    private function normalizeSpecificationsPayload(array &$data): void
    {
        $specifications = trim((string) ($data['specifications'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($specifications === '' && $description !== '') {
            $specifications = $description;
        }

        if ($description === '' && $specifications !== '') {
            $description = $specifications;
        }

        $data['specifications'] = $specifications !== '' ? $specifications : null;
        $data['description'] = $description !== '' ? $description : null;
    }

    private function buildBookingCalendar(int $equipmentId, string $monthValue = ''): array
    {
        $monthDate = $this->resolveMonthDate($monthValue);
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $bookings = collect();
        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            $bookings = OrderItem::query()
                ->with(['order.user'])
                ->where('equipment_id', $equipmentId)
                ->whereHas('order', function ($query) use ($monthStart, $monthEnd) {
                    $query->whereIn('status_pesanan', Order::ACTIVE_RENTAL_STATUSES)
                        ->whereDate('rental_start_date', '<=', $monthEnd->toDateString())
                        ->whereDate('rental_end_date', '>=', $monthStart->toDateString());
                })
                ->get();
        }

        $bookedByDate = [];
        $events = [];

        foreach ($bookings as $item) {
            $order = $item->order;
            $itemStart = $item->rental_start_date
                ? Carbon::parse($item->rental_start_date)->startOfDay()
                : ($order?->rental_start_date ? Carbon::parse($order->rental_start_date)->startOfDay() : null);
            $itemEnd = $item->rental_end_date
                ? Carbon::parse($item->rental_end_date)->startOfDay()
                : ($order?->rental_end_date ? Carbon::parse($order->rental_end_date)->startOfDay() : null);

            if (! $itemStart || ! $itemEnd) {
                continue;
            }

            $startDate = $itemStart->copy();
            $endDate = $itemEnd->copy();
            $rangeStart = $startDate->lt($monthStart) ? $monthStart->copy() : $startDate->copy();
            $rangeEnd = $endDate->gt($monthEnd) ? $monthEnd->copy() : $endDate->copy();

            for ($cursor = $rangeStart->copy(); $cursor->lte($rangeEnd); $cursor->addDay()) {
                $dateKey = $cursor->toDateString();
                $bookedByDate[$dateKey] = ($bookedByDate[$dateKey] ?? 0) + (int) $item->qty;
            }

            $events[] = [
                'order_number' => $order->order_number ?: $order->midtrans_order_id,
                'customer' => $order->user?->display_name ?: $order->user?->name ?: __('Pengguna'),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'qty' => (int) $item->qty,
            ];
        }

        $days = [];
        for ($cursor = $calendarStart->copy(); $cursor->lte($calendarEnd); $cursor->addDay()) {
            $dateKey = $cursor->toDateString();
            $days[] = [
                'date' => $dateKey,
                'day' => $cursor->day,
                'in_month' => $cursor->month === $monthStart->month,
                'booked_qty' => (int) ($bookedByDate[$dateKey] ?? 0),
            ];
        }

        return [
            'month_label' => $monthStart->translatedFormat('F Y'),
            'month_value' => $monthStart->format('Y-m'),
            'previous_month' => $monthStart->copy()->subMonth()->format('Y-m'),
            'next_month' => $monthStart->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'events' => collect($events)->sortBy('start_date')->values(),
        ];
    }

    private function resolveMonthDate(string $monthValue): Carbon
    {
        if ($monthValue !== '' && preg_match('/^\d{4}-\d{2}$/', $monthValue) === 1) {
            return Carbon::createFromFormat('Y-m', $monthValue)->startOfMonth();
        }

        return now()->startOfMonth();
    }
}
