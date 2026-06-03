<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'google_id',
        'google_token',
        'google_refresh_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

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

    public function hasCompleteRentalProfile(): bool
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

    public function profileIsComplete(): bool
    {
        return $this->hasCompleteRentalProfile();
    }

    public function hasVerifiedRentalIdentity(): bool
    {
        return $this->hasCompleteRentalProfile()
            && $this->hasVerifiedEmail()
            && (bool) optional($this->profile)->rental_consent_accepted_at;
    }

    public function getDisplayNameAttribute(): string
    {
        if (schema_table_exists_cached('profiles')) {
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
