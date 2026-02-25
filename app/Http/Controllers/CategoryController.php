<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = collect();

        if (schema_table_exists_cached('categories')) {
            $categories = Category::query()
                ->withCount('equipments')
                ->orderBy('name')
                ->get();
        }

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function home()
    {
        $category = null;
        $productsReady = collect();
        $userOverview = null;
        $recentUserOrders = collect();
        $damageAlertOrder = null;
        $guestRentalSnapshot = collect();
        $canResolveUsage = schema_table_exists_cached('order_items') && schema_table_exists_cached('orders');

        if (schema_table_exists_cached('categories')) {
            $category = Cache::remember('home:first_category:v1', now()->addMinutes(5), function () {
                return Category::query()->orderBy('name')->first();
            });
        }

        if (schema_table_exists_cached('equipments')) {
            $productsReady = Cache::remember('home:products_ready:v2', now()->addSeconds(45), function () use ($canResolveUsage) {
                $equipmentQuery = Equipment::query()->orderBy('updated_at', 'desc');
                if ($canResolveUsage) {
                    $equipmentQuery->withSum('activeOrderItems as reserved_units', 'qty');
                }

                if (schema_column_exists_cached('equipments', 'status')) {
                    $equipmentQuery->where('status', 'ready');
                } else {
                    $equipmentQuery->where('stock', '>', 0);
                }

                return $equipmentQuery
                    ->get()
                    ->filter(fn ($equipment) => (int) $equipment->stock > 0)
                    ->take(8)
                    ->values();
            });
        }

        if (auth('web')->check() && schema_table_exists_cached('orders')) {
            $userId = (int) auth('web')->id();
            $baseOrderQuery = Order::query()
                ->where('user_id', $userId)
                ->with('damagePayment');
            $selectColumns = ['id', 'order_number', 'status_pembayaran', 'status_pesanan', 'total_amount', 'rental_start_date', 'rental_end_date'];
            if (schema_column_exists_cached('orders', 'additional_fee')) {
                $selectColumns[] = 'additional_fee';
            }
            if (schema_column_exists_cached('orders', 'penalty_amount')) {
                $selectColumns[] = 'penalty_amount';
            }
            if (schema_column_exists_cached('orders', 'additional_fee_note')) {
                $selectColumns[] = 'additional_fee_note';
            }

            $summary = (clone $baseOrderQuery)
                ->selectRaw('COUNT(*) as total_orders')
                ->selectRaw("SUM(CASE WHEN status_pembayaran = 'pending' THEN 1 ELSE 0 END) as pending_payment")
                ->selectRaw("SUM(CASE WHEN status_pesanan = 'lunas' THEN 1 ELSE 0 END) as ready_pickup")
                ->selectRaw("SUM(CASE WHEN status_pesanan = 'barang_diambil' THEN 1 ELSE 0 END) as on_rent")
                ->selectRaw("SUM(CASE WHEN status_pesanan IN ('barang_kembali', 'selesai') THEN 1 ELSE 0 END) as returned_orders")
                ->selectRaw("SUM(CASE WHEN status_pesanan = 'barang_rusak' THEN 1 ELSE 0 END) as damaged_orders")
                ->selectRaw("SUM(CASE WHEN status_pesanan = 'selesai' THEN 1 ELSE 0 END) as completed_orders")
                ->first();

            $userOverview = [
                'total_orders' => (int) ($summary?->total_orders ?? 0),
                'pending_payment' => (int) ($summary?->pending_payment ?? 0),
                'ready_pickup' => (int) ($summary?->ready_pickup ?? 0),
                'on_rent' => (int) ($summary?->on_rent ?? 0),
                'returned_orders' => (int) ($summary?->returned_orders ?? 0),
                'damaged_orders' => (int) ($summary?->damaged_orders ?? 0),
                'completed_orders' => (int) ($summary?->completed_orders ?? 0),
            ];

            $recentUserOrders = (clone $baseOrderQuery)
                ->latest()
                ->limit(5)
                ->get($selectColumns);

            $hasPenaltyColumn = schema_column_exists_cached('orders', 'penalty_amount');
            $hasAdditionalFeeColumn = schema_column_exists_cached('orders', 'additional_fee');

            if ($hasPenaltyColumn || $hasAdditionalFeeColumn) {
                $damageRelatedStatuses = [
                    Order::STATUS_RETURNED_OK,
                    Order::STATUS_RETURNED_DAMAGED,
                    Order::STATUS_RETURNED_LOST,
                    Order::STATUS_OVERDUE_DAMAGE_INVOICE,
                ];

                $damageAlertCandidates = Order::query()
                    ->where('user_id', $userId)
                    ->whereIn('status_pesanan', $damageRelatedStatuses)
                    ->where(function ($query) use ($hasPenaltyColumn, $hasAdditionalFeeColumn) {
                        if ($hasPenaltyColumn) {
                            $query->where('penalty_amount', '>', 0);
                        }

                        if ($hasAdditionalFeeColumn) {
                            $method = $hasPenaltyColumn ? 'orWhere' : 'where';
                            $query->{$method}('additional_fee', '>', 0);
                        }
                    })
                    ->with('damagePayment')
                    ->orderByDesc('updated_at')
                    ->limit(12)
                    ->get();

                $damageAlertOrder = $damageAlertCandidates->first(function (Order $order) {
                    return $order->resolvePenaltyAmount() > 0
                        && (string) ($order->damagePayment?->status ?? '') !== Order::PAYMENT_PAID;
                });
            }
        }

        if (schema_table_exists_cached('orders') && schema_table_exists_cached('order_items') && schema_table_exists_cached('equipments')) {
            $guestRentalSnapshot = Cache::remember('home:guest_snapshot:v2', now()->addSeconds(45), function () {
                $today = now()->startOfDay();

                return OrderItem::query()
                    ->with([
                        'equipment:id,name',
                        'order:id,status_pesanan,status_pembayaran,rental_start_date,rental_end_date',
                    ])
                    ->whereHas('order', function ($query) {
                        $query->whereIn('status_pesanan', Order::HOLD_SLOT_STATUSES);
                    })
                    ->latest('id')
                    ->limit(120)
                    ->get(['id', 'order_id', 'equipment_id', 'qty', 'rental_start_date', 'rental_end_date'])
                    ->map(function (OrderItem $item) {
                        $startDate = $item->rental_start_date ?: $item->order?->rental_start_date;
                        $endDate = $item->rental_end_date ?: $item->order?->rental_end_date;
                        if (! $startDate || ! $endDate) {
                            return null;
                        }

                        $startDateString = $startDate instanceof \Carbon\CarbonInterface
                            ? $startDate->toDateString()
                            : \Carbon\Carbon::parse($startDate)->toDateString();
                        $endDateString = $endDate instanceof \Carbon\CarbonInterface
                            ? $endDate->toDateString()
                            : \Carbon\Carbon::parse($endDate)->toDateString();

                        return [
                            'equipment_id' => (int) ($item->equipment_id ?? 0),
                            'name' => (string) ($item->equipment?->name ?: 'Equipment'),
                            'qty' => max((int) ($item->qty ?? 1), 1),
                            'start_date' => $startDateString,
                            'end_date' => $endDateString,
                        ];
                    })
                    ->filter(function ($item) use ($today) {
                        if (! is_array($item) || ($item['name'] ?? '') === '') {
                            return false;
                        }

                        return \Carbon\Carbon::parse((string) ($item['end_date'] ?? ''))->startOfDay()->gte($today);
                    })
                    ->groupBy(function (array $item) {
                        return implode('|', [
                            (int) ($item['equipment_id'] ?? 0),
                            (string) ($item['start_date'] ?? ''),
                            (string) ($item['end_date'] ?? ''),
                        ]);
                    })
                    ->map(function ($items) {
                        $first = $items->first();

                        return [
                            'name' => (string) ($first['name'] ?? 'Equipment'),
                            'qty' => (int) $items->sum('qty'),
                            'start_date' => $first['start_date'] ?? null,
                            'end_date' => $first['end_date'] ?? null,
                        ];
                    })
                    ->sortBy(function (array $item) {
                        $startDate = \Carbon\Carbon::parse((string) ($item['start_date'] ?? now()->toDateString()))->timestamp;

                        return $startDate . '|' . strtolower((string) ($item['name'] ?? ''));
                    })
                    ->take(5)
                    ->values();
            });
        }

        return view('welcome', compact('category', 'productsReady', 'userOverview', 'recentUserOrders', 'damageAlertOrder', 'guestRentalSnapshot'));
    }

    public function show(string $slug)
    {
        $category = null;
        $products = collect();
        $canResolveUsage = schema_table_exists_cached('order_items') && schema_table_exists_cached('orders');

        if (schema_table_exists_cached('categories')) {
            $categoryQuery = Category::query();

            if (schema_column_exists_cached('categories', 'slug')) {
                $categoryQuery->where('slug', $slug);
            } else {
                $nameGuess = Str::of($slug)->replace('-', ' ')->title()->value();
                $categoryQuery->where('name', $nameGuess);
            }

            $category = $categoryQuery->first();
        }

        if ($category) {
            $equipmentQuery = $category->equipments()
                ->orderByDesc('updated_at')
                ->orderBy('name');
            if ($canResolveUsage) {
                $equipmentQuery->withSum('activeOrderItems as reserved_units', 'qty');
            }
            $products = $equipmentQuery->get()->values();
        } else {
            $category = (object) [
                'name' => Str::of($slug)->replace('-', ' ')->title()->value(),
                'slug' => $slug,
                'description' => __('app.category.all_subtitle'),
            ];
        }

        return view('categories.show', compact('category', 'products'));
    }
}
