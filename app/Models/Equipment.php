<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

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

        return $this->hasMany(OrderItem::class, 'equipment_id')
            ->whereHas('order', function ($query) {
                $query->whereIn('status_pesanan', Order::ACTIVE_RENTAL_STATUSES);
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
}
