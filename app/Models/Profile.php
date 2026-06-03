<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'nik',
        'date_of_birth',
        'gender',
        'phone',
        'phone_verified_at',
        'address_line',
        'kelurahan',
        'kecamatan',
        'city',
        'province',
        'postal_code',
        'maps_url',
        'emergency_name',
        'emergency_relation',
        'emergency_phone',
        'alternative_phone',
        'instagram_handle',
        'organization_name',
        'organization_type',
        // Legacy fallback fields
        'address',
        'notes',
        'identity_number',
        'emergency_contact',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'date_of_birth' => 'date',
        'phone_verified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAddressTextAttribute(): string
    {
        $segments = array_filter([
            $this->address_line,
            $this->kelurahan ? 'Kel. ' . $this->kelurahan : null,
            $this->kecamatan ? 'Kec. ' . $this->kecamatan : null,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        if ($segments !== []) {
            return implode(', ', $segments);
        }

        return (string) ($this->address ?? '-');
    }

    public function getMaskedNikAttribute(): string
    {
        $rawNik = (string) ($this->nik ?: $this->identity_number ?: '');
        $digits = preg_replace('/[^0-9]/', '', $rawNik) ?: '';
        if ($digits === '') {
            return '-';
        }

        if (strlen($digits) <= 4) {
            return $digits;
        }

        $visible = substr($digits, -4);
        $hidden = str_repeat('x', max(strlen($digits) - 4, 0));
        $masked = $hidden . $visible;

        return implode('-', str_split($masked, 4));
    }
}
