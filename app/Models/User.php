<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Throwable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'preferred_theme',
        'preferred_locale',
        'nik',
        'phone',
        'address',
        'otp_code',
        'otp_expires_at',
        'is_otp_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'otp_expires_at' => 'datetime',
        'is_otp_verified' => 'boolean',
    ];

    public function generateOtp()
    {
        $ttlMinutes = max((int) config('security.otp_ttl_minutes', 5), 1);

        $this->otp_code = random_int(100000, 999999);
        $this->otp_expires_at = now()->addMinutes($ttlMinutes);
        $this->is_otp_verified = false;
        $this->save();

        return $this->otp_code;
    }

    public function otpIsExpired(): bool
    {
        return $this->otp_expires_at && now()->greaterThan($this->otp_expires_at);
    }

    public function clearOtp()
    {
        $this->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'is_otp_verified' => true,
        ]);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderNotifications(): HasMany
    {
        return $this->hasMany(OrderNotification::class);
    }

    public function phoneVerification(): HasOne
    {
        return $this->hasOne(PhoneVerification::class);
    }

    public function hasVerifiedPhone(): bool
    {
        $profile = $this->profile;
        if (! $profile) {
            return false;
        }

        return $profile->phone_verified_at !== null;
    }

    public function profileIsComplete(): bool
    {
        $profile = $this->profile;
        if (! $profile) {
            return false;
        }

        $requiredFields = [
            $profile->full_name,
            $profile->nik,
            $profile->date_of_birth,
            $profile->phone,
            $profile->address_line,
            $profile->kelurahan,
            $profile->kecamatan,
            $profile->city,
            $profile->province,
            $profile->postal_code,
            $profile->emergency_name,
            $profile->emergency_relation,
            $profile->emergency_phone,
        ];

        foreach ($requiredFields as $value) {
            if (! filled($value)) {
                return false;
            }
        }

        if (! preg_match('/^[0-9]{16}$/', (string) $profile->nik)) {
            return false;
        }

        return true;
    }

    public function getDisplayNameAttribute(): string
    {
        if (Schema::hasTable('profiles')) {
            try {
                if ($this->profile && $this->profile->full_name) {
                    return $this->profile->full_name;
                }
            } catch (Throwable $exception) {
                // Ignore profile lookup errors and fallback to name/email.
            }
        }

        if (! empty($this->name)) {
            return $this->name;
        }

        return $this->email ?? 'User';
    }
}
