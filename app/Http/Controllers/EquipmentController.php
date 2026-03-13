<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Services\AvailabilityService;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $categories = collect();
        $activeCategory = null;
        $activeCategorySlug = $request->string('category')->trim()->value();
        $search = $request->string('q')->trim()->value();
        $hasCategoriesTable = schema_table_exists_cached('categories');
        $hasDescriptionColumn = false;

        if (! schema_table_exists_cached('equipments')) {
            return view('equipments.index', [
                'groupedEquipments' => collect(),
                'categories' => $categories,
                'activeCategory' => $activeCategory,
                'activeCategorySlug' => $activeCategorySlug,
                'search' => $search,
            ]);
        }

        if ($hasCategoriesTable) {
            $categories = Category::query()
                ->withCount('equipments')
                ->orderBy('name')
                ->get();
        }
        $hasDescriptionColumn = schema_column_exists_cached('equipments', 'description');

        $query = Equipment::query()
            ->with('category')
            ->orderByDesc('updated_at')
            ->orderBy('name');
        if (schema_table_exists_cached('order_items') && schema_table_exists_cached('orders')) {
            $query->withSum('activeOrderItems as reserved_units', 'qty');
        }

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search, $hasCategoriesTable, $hasDescriptionColumn) {
                $searchQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');

                if ($hasDescriptionColumn) {
                    $searchQuery->orWhere('description', 'like', '%' . $search . '%');
                }

                if ($hasCategoriesTable) {
                    $searchQuery->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%');
                    });
                }
            });
        }

        if ($activeCategorySlug !== '' && $categories->isNotEmpty()) {
            $activeCategory = $categories->firstWhere('slug', $activeCategorySlug);
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        $equipments = $query->get();

        $groupedEquipments = collect();

        if ($activeCategory) {
            $groupedEquipments->push([
                'category' => $activeCategory,
                'items' => $equipments->values(),
            ]);
        } else {
            foreach ($categories as $category) {
                $items = $equipments
                    ->where('category_id', $category->id)
                    ->values();

                if ($items->isEmpty()) {
                    continue;
                }

                $groupedEquipments->push([
                    'category' => $category,
                    'items' => $items,
                ]);
            }

            $uncategorized = $equipments
                ->filter(fn ($equipment) => ! $equipment->category_id)
                ->values();

            if ($uncategorized->isNotEmpty()) {
                $groupedEquipments->push([
                    'category' => (object) [
                        'name' => 'Lainnya',
                        'slug' => 'lainnya',
                        'description' => 'Peralatan tanpa kategori.',
                    ],
                    'items' => $uncategorized,
                ]);
            }
        }

        return view('equipments.index', [
            'groupedEquipments' => $groupedEquipments,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'activeCategorySlug' => $activeCategorySlug,
            'search' => $search,
        ]);
    }

    public function show(string $slug, AvailabilityService $availabilityService)
    {
        if (! schema_table_exists_cached('equipments')) {
            abort(404);
        }

        $equipmentQuery = Equipment::query()
            ->with('category')
            ->where('slug', $slug);
        if (schema_table_exists_cached('order_items') && schema_table_exists_cached('orders')) {
            $equipmentQuery->withSum('activeOrderItems as reserved_units', 'qty');
        }
        $equipment = $equipmentQuery->firstOrFail();
        $bookingRanges = $availabilityService->getBlockedSchedules(
            $equipment,
            auth('web')->check() ? (int) auth('web')->id() : null
        );

        return view('equipments.show', compact('equipment', 'bookingRanges'));
    }

    public function suggestions(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2 || ! schema_table_exists_cached('equipments')) {
            return response()->json(['data' => []]);
        }

        $builder = Equipment::query()
            ->with('category');
        if (schema_table_exists_cached('order_items') && schema_table_exists_cached('orders')) {
            $builder->withSum('activeOrderItems as reserved_units', 'qty');
        }

        $applyKeywordSearch = function ($searchBuilder) use ($query) {
            $searchBuilder->where(function ($nestedBuilder) use ($query) {
                $nestedBuilder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('slug', 'like', '%' . $query . '%');
            });
        };

        $items = (clone $builder)
            ->tap($applyKeywordSearch)
            ->get()
            ->sortBy(function (Equipment $equipment) use ($query) {
                $name = Str::lower((string) $equipment->name);
                $slug = Str::lower((string) $equipment->slug);
                $needle = Str::lower($query);

                return match (true) {
                    str_starts_with($name, $needle) => 0,
                    str_contains($name, $needle) => 1,
                    str_starts_with($slug, $needle) => 2,
                    str_contains($slug, $needle) => 3,
                    default => 10,
                };
            })
            ->values()
            ->take(4)
            ->values();

        if ($items->isEmpty()) {
            $normalizedQuery = Str::of($query)
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', ' ')
                ->squish()
                ->value();

            $fallbackItems = (clone $builder)
                ->when(schema_column_exists_cached('equipments', 'status'), fn ($fallbackQuery) => $fallbackQuery->where('status', 'ready'))
                ->orderBy('name')
                ->get();

            $rankedFallbackItems = $fallbackItems
                ->map(function (Equipment $equipment) use ($normalizedQuery) {
                    $candidates = collect([
                        (string) $equipment->name,
                        (string) $equipment->slug,
                    ])
                        ->filter()
                        ->flatMap(function (string $value) {
                            $normalized = Str::of($value)
                                ->lower()
                                ->ascii()
                                ->replaceMatches('/[^a-z0-9]+/', ' ')
                                ->squish()
                                ->value();

                            return collect([$normalized])
                                ->merge(explode(' ', $normalized));
                        })
                        ->filter();

                    $score = $candidates
                        ->map(function (string $value) use ($normalizedQuery) {
                            if ($value === '') {
                                return null;
                            }

                            if (str_contains($value, $normalizedQuery)) {
                                return 0;
                            }

                            similar_text($normalizedQuery, $value, $similarity);
                            $distance = levenshtein($normalizedQuery, $value);

                            return [
                                'distance' => $distance,
                                'similarity' => $similarity,
                            ];
                        })
                        ->filter()
                        ->sortBy(function ($item) {
                            if (is_int($item)) {
                                return -1000;
                            }

                            return ($item['distance'] * 10) - $item['similarity'];
                        })
                        ->first();

                    if ($score === null) {
                        return null;
                    }

                    if (is_int($score)) {
                        return [
                            'equipment' => $equipment,
                            'rank' => $score,
                            'similarity' => 100,
                            'distance' => 0,
                        ];
                    }

                    $queryLength = max(mb_strlen($normalizedQuery), 1);
                    $distanceThreshold = max(1, (int) ceil($queryLength / 4));

                    if ($score['similarity'] < 72 || $score['distance'] > $distanceThreshold) {
                        return null;
                    }

                    return [
                        'equipment' => $equipment,
                        'rank' => ($score['distance'] * 10) - $score['similarity'],
                        'similarity' => $score['similarity'],
                        'distance' => $score['distance'],
                    ];
                })
                ->filter()
                ->sortBy('rank')
                ->values();

            if ($rankedFallbackItems->isNotEmpty()) {
                $bestRank = (float) ($rankedFallbackItems->first()['rank'] ?? 0);

                $items = $rankedFallbackItems
                    ->filter(function (array $item) use ($bestRank) {
                        return ((float) $item['rank']) <= ($bestRank + 8);
                    })
                    ->take(4)
                    ->pluck('equipment')
                    ->values();
            } else {
                $items = collect();
            }
        }

        $matchingIds = $items
            ->filter(function (Equipment $equipment) use ($query) {
                return str_contains(Str::lower((string) $equipment->name), Str::lower($query))
                    || str_contains(Str::lower((string) $equipment->slug), Str::lower($query));
            })
            ->pluck('id')
            ->all();

        $data = $items->map(function (Equipment $equipment) use ($matchingIds) {
            $imagePath = (string) ($equipment->image_path ?? $equipment->image ?? '');
            $imageUrl = site_media_url($imagePath) ?: site_asset('MANAKE-FAV-M.png');

            return [
                'name' => (string) $equipment->name,
                'slug' => (string) $equipment->slug,
                'image_url' => $imageUrl,
                'price_per_day' => (int) ($equipment->price_per_day ?? 0),
                'available_units' => (int) ($equipment->available_units ?? 0),
                'detail_url' => route('product.show', $equipment->slug),
                'is_recommended' => ! in_array($equipment->id, $matchingIds, true),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    public function availability(Request $request, string $slug, AvailabilityService $availabilityService)
    {
        if (! schema_table_exists_cached('equipments')) {
            return response()->json([
                'ok' => false,
                'message' => __('ui.availability.not_available'),
            ], 404);
        }

        $equipment = Equipment::query()
            ->where('slug', $slug)
            ->first();

        if (! $equipment) {
            return response()->json([
                'ok' => false,
                'message' => __('ui.availability.not_available'),
            ], 404);
        }

        $maxAllowedDate = $this->bookingWindowEnd()->toDateString();
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . $maxAllowedDate],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:' . $maxAllowedDate],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $requestedQty = max((int) ($validated['qty'] ?? 1), 1);
        $startDate = Carbon::parse((string) $validated['start_date'])->startOfDay();
        $endDate = Carbon::parse((string) $validated['end_date'])->startOfDay();

        $evaluation = $availabilityService->evaluateRange(
            $equipment,
            $startDate,
            $endDate,
            $requestedQty
        );

        $durationDays = $startDate->diffInDays($endDate) + 1;
        $conflictCount = count($evaluation['conflicts']);
        $status = 'available';

        if (! $evaluation['ok']) {
            $status = $conflictCount >= $durationDays ? 'not_available' : 'partially_available';
        }

        $suggestions = $status !== 'available'
            ? $this->findAlternativeRanges($equipment, $availabilityService, $startDate, $durationDays, $requestedQty)
            : [];

        $daily = collect($evaluation['daily'])
            ->map(function (array $dayData, string $dateKey) use ($equipment) {
                $reserved = (int) ($dayData['qty'] ?? 0);
                $available = max((int) $equipment->stock - $reserved, 0);

                return [
                    'date' => $dateKey,
                    'reserved' => $reserved,
                    'available' => $available,
                ];
            })
            ->sortBy('date')
            ->values()
            ->all();

        $message = match ($status) {
            'available' => __('ui.availability.available'),
            'partially_available' => __('ui.availability.partial'),
            default => __('ui.availability.not_available'),
        };

        return response()->json([
            'ok' => $evaluation['ok'],
            'status' => $status,
            'message' => $message,
            'conflicts' => $evaluation['conflicts'],
            'daily' => $daily,
            'suggestions' => $suggestions,
        ]);
    }

    private function findAlternativeRanges(
        Equipment $equipment,
        AvailabilityService $availabilityService,
        Carbon $fromDate,
        int $durationDays,
        int $qty
    ): array {
        $maxSuggestions = 3;
        $searchLimitDays = 45;
        $suggestions = [];

        $cursor = $fromDate->copy()->addDay()->startOfDay();
        if ($cursor->lt($this->bookingWindowStart())) {
            $cursor = $this->bookingWindowStart()->copy();
        }

        $deadline = $fromDate->copy()->addDays($searchLimitDays)->startOfDay();
        $windowEnd = $this->bookingWindowEnd();
        if ($deadline->gt($windowEnd)) {
            $deadline = $windowEnd;
        }

        while ($cursor->lte($deadline) && count($suggestions) < $maxSuggestions) {
            $candidateEnd = $cursor->copy()->addDays(max($durationDays - 1, 0));
            if ($candidateEnd->gt($windowEnd)) {
                break;
            }
            $result = $availabilityService->evaluateRange($equipment, $cursor, $candidateEnd, $qty);

            if ($result['ok']) {
                $suggestions[] = [
                    'start_date' => $cursor->toDateString(),
                    'end_date' => $candidateEnd->toDateString(),
                ];
            }

            $cursor->addDay();
        }

        return $suggestions;
    }

    private function bookingWindowStart(): Carbon
    {
        return now()->startOfDay();
    }

    private function bookingWindowEnd(): Carbon
    {
        return now()->addMonthsNoOverflow(3)->startOfDay();
    }
}
