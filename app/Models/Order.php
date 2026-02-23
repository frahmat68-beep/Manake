<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUS_PENDING_PAYMENT = 'menunggu_pembayaran';

    public const STATUS_PROCESSING = 'diproses';

    public const STATUS_READY_PICKUP = 'lunas';

    public const STATUS_ON_RENT = 'barang_diambil';

    public const STATUS_RETURNED_OK = 'barang_kembali';

    public const STATUS_RETURNED_DAMAGED = 'barang_rusak';

    public const STATUS_RETURNED_LOST = 'barang_hilang';

    public const STATUS_COMPLETED = 'selesai';

    public const STATUS_CANCELLED = 'dibatalkan';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REFUNDED = 'refund';

    public const STATUS_OVERDUE_DAMAGE_INVOICE = 'overdue_denda';

    public const DAMAGE_FEE_REQUIRED_STATUSES = [
        self::STATUS_RETURNED_OK,
        self::STATUS_RETURNED_DAMAGED,
        self::STATUS_RETURNED_LOST,
        self::STATUS_OVERDUE_DAMAGE_INVOICE,
    ];

    public const ACTIVE_RENTAL_STATUSES = [
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_PROCESSING,
        self::STATUS_READY_PICKUP,
        self::STATUS_ON_RENT,
        self::STATUS_RETURNED_DAMAGED,
        self::STATUS_RETURNED_LOST,
    ];

    public const HOLD_SLOT_STATUSES = [
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_PROCESSING,
        self::STATUS_READY_PICKUP,
        self::STATUS_ON_RENT,
        self::STATUS_RETURNED_DAMAGED,
        self::STATUS_RETURNED_LOST,
    ];

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_EXPIRED = 'expired';

    public const PAYMENT_REFUNDED = 'refunded';

    private const ORDER_STATUS_TRANSITIONS = [
        self::STATUS_PENDING_PAYMENT => [
            self::STATUS_PROCESSING,
            self::STATUS_READY_PICKUP,
            self::STATUS_CANCELLED,
            self::STATUS_EXPIRED,
        ],
        self::STATUS_PROCESSING => [
            self::STATUS_READY_PICKUP,
            self::STATUS_ON_RENT,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_READY_PICKUP => [
            self::STATUS_ON_RENT,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_ON_RENT => [
            self::STATUS_RETURNED_OK,
            self::STATUS_RETURNED_DAMAGED,
            self::STATUS_RETURNED_LOST,
        ],
        self::STATUS_RETURNED_DAMAGED => [
            self::STATUS_OVERDUE_DAMAGE_INVOICE,
            self::STATUS_COMPLETED,
        ],
        self::STATUS_RETURNED_LOST => [
            self::STATUS_OVERDUE_DAMAGE_INVOICE,
            self::STATUS_COMPLETED,
        ],
        self::STATUS_RETURNED_OK => [
            self::STATUS_COMPLETED,
        ],
        self::STATUS_OVERDUE_DAMAGE_INVOICE => [
            self::STATUS_COMPLETED,
        ],
    ];

    protected $fillable = [
        'user_id',
        'order_number',
        'status_pembayaran',
        'status_pesanan',
        'status',
        'total_amount',
        'additional_fee',
        'penalty_amount',
        'shipping_amount',
        'additional_fee_note',
        'admin_note',
        'rental_start_date',
        'rental_end_date',
        'midtrans_order_id',
        'snap_token',
        'paid_at',
        'picked_up_at',
        'returned_at',
        'damaged_at',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'paid_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'returned_at' => 'datetime',
        'damaged_at' => 'datetime',
        'additional_fee' => 'integer',
        'penalty_amount' => 'integer',
        'shipping_amount' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)
            ->where('provider', Payment::PROVIDER_MIDTRANS_RENTAL)
            ->latestOfMany();
    }

    public function damagePayment(): HasOne
    {
        return $this->hasOne(Payment::class)
            ->where('provider', Payment::PROVIDER_MIDTRANS_DAMAGE)
            ->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(OrderNotification::class);
    }

    public function getGrandTotalAttribute(): int
    {
        $subtotal = (int) $this->total_amount;
        $tax = (int) round($subtotal * 0.11);
        $penalty = (int) ($this->penalty_amount ?? 0);
        if ($penalty <= 0) {
            $penalty = (int) ($this->additional_fee ?? 0);
        }

        return $subtotal + $tax + $penalty + (int) ($this->shipping_amount ?? 0);
    }

    public function canTransitionToOrderStatus(string $nextStatus): bool
    {
        $currentStatus = (string) ($this->status_pesanan ?? '');
        if ($nextStatus === $currentStatus) {
            return true;
        }

        return in_array($nextStatus, self::ORDER_STATUS_TRANSITIONS[$currentStatus] ?? [], true);
    }

    public function resolvePenaltyAmount(): int
    {
        $penalty = (int) ($this->penalty_amount ?? 0);
        if ($penalty <= 0) {
            $penalty = (int) ($this->additional_fee ?? 0);
        }

        return max($penalty, 0);
    }

    public function hasOutstandingDamageFee(): bool
    {
        if (! in_array((string) ($this->status_pesanan ?? ''), self::DAMAGE_FEE_REQUIRED_STATUSES, true)) {
            return false;
        }

        if ($this->resolvePenaltyAmount() <= 0) {
            return false;
        }

        $damagePayment = $this->relationLoaded('damagePayment')
            ? $this->damagePayment
            : $this->damagePayment()->latest('id')->first();

        return (string) ($damagePayment?->status ?? self::PAYMENT_PENDING) !== self::PAYMENT_PAID;
    }

    public function canAccessInvoice(): bool
    {
        if ((string) ($this->status_pembayaran ?? self::PAYMENT_PENDING) !== self::PAYMENT_PAID) {
            return false;
        }

        return ! $this->hasOutstandingDamageFee();
    }
}
