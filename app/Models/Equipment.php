<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price_per_day',
        'status',
        'description',
        'specifications',
        'stock',
        'image',
        'image_path',
    ];

    protected $casts = [
        'price_per_day' => 'integer',
        'stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function maintenanceWindows(): HasMany
    {
        return $this->hasMany(EquipmentMaintenanceWindow::class);
    }

    public function activeOrderItems(): HasMany
    {
        $today = now()->toDateString();
        $holdCutoff = now()->subMinutes(\App\Services\AvailabilityService::holdWindowMinutes());

        return $this->hasMany(OrderItem::class, 'equipment_id')
            ->whereHas('order', function ($query) use ($holdCutoff) {
                $query->where(function ($statusQuery) use ($holdCutoff) {
                    $statusQuery->where(function ($pendingQuery) use ($holdCutoff) {
                        $pendingQuery->where('status_pesanan', 'menunggu_pembayaran')
                            ->where('created_at', '>=', $holdCutoff);
                    })->orWhereIn('status_pesanan', \App\Services\AvailabilityService::blockingOrderStatuses());
                });
            })
            ->where(function ($query) use ($today) {
                $query->where(function ($itemDateQuery) use ($today) {
                    $itemDateQuery->whereNotNull('rental_start_date')
                        ->whereNotNull('rental_end_date')
                        ->whereDate('rental_start_date', '<=', $today)
                        ->whereDate('rental_end_date', '>=', $today);
                })->orWhere(function ($fallbackQuery) use ($today) {
                    $fallbackQuery->whereNull('rental_start_date')
                        ->whereNull('rental_end_date')
                        ->whereHas('order', function ($orderQuery) use ($today) {
                            $orderQuery->whereDate('rental_start_date', '<=', $today)
                                ->whereDate('rental_end_date', '>=', $today);
                        });
                });
            });
    }

    public function getReservedUnitsAttribute(): int
    {
        if (array_key_exists('reserved_units', $this->attributes)) {
            return (int) $this->attributes['reserved_units'];
        }

        if (! schema_table_exists_cached('order_items') || ! schema_table_exists_cached('orders')) {
            return 0;
        }

        return (int) $this->activeOrderItems()->sum('qty');
    }

    public function getAvailableUnitsAttribute(): int
    {
        if (($this->status ?? 'ready') !== 'ready') {
            return 0;
        }

        $available = ((int) $this->stock) - $this->reserved_units;

        return max($available, 0);
    }

    public function normalizedSpecifications(): Collection
    {
        $raw = $this->specifications ?: $this->description;
        $items = $this->normalizeSpecificationValue($raw);

        return collect($items)
            ->map(fn ($item) => trim((string) preg_replace('/^[-*\x{2022}\s]+/u', '', (string) $item)))
            ->filter()
            ->unique()
            ->values();
    }

    private function normalizeSpecificationValue(mixed $value): array
    {
        if (blank($value)) {
            return [];
        }

        if (is_array($value)) {
            return $this->flattenSpecificationArray($value);
        }

        $text = trim((string) $value);
        if ($text === '') {
            return [];
        }

        if (Str::startsWith($text, ['[', '{'])) {
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->flattenSpecificationArray($decoded);
            }
        }

        // Replace literal \n and \r\n with actual newlines
        $text = str_replace(['\r\n', '\n', '\r'], "\n", $text);

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        if (count($lines) <= 1) {
            $lines = preg_split('/\s*[;|]\s*/', $text) ?: [$text];
        }

        return array_values(array_filter(array_map('trim', $lines)));
    }

    private function flattenSpecificationArray(array $items, ?string $prefix = null): array
    {
        $output = [];
        $isList = array_is_list($items);

        foreach ($items as $key => $value) {
            $label = $isList ? $prefix : trim((string) $key);

            if (is_array($value)) {
                $output = array_merge($output, $this->flattenSpecificationArray($value, $label));

                continue;
            }

            $valueText = trim((string) $value);
            if ($valueText === '') {
                continue;
            }

            $output[] = $label ? "{$label}: {$valueText}" : $valueText;
        }

        return $output;
    }
}
