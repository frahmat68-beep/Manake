<?php

namespace App\Services;

use App\Models\User;
use App\Mail\PhoneOtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PhoneOtpService
{
    public function sendOtp(User $user, string $phone, string $otp): void
    {
        $driver = env('PHONE_OTP_DRIVER', 'mail');
        $logInLocal = filter_var(env('PHONE_OTP_LOG_IN_LOCAL', true), FILTER_VALIDATE_BOOLEAN);

        if ($driver === 'log' || (app()->environment('local') && $logInLocal)) {
            Log::info('PHONE_OTP_SENT', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $phone,
                'otp' => $otp,
                'driver' => 'log',
                'sent_at' => now()->toIso8601String(),
            ]);
        }

        if ($driver === 'mail') {
            $ttlMinutes = max((int) config('security.phone_otp_ttl_minutes', 5), 1);
            Mail::to($user->email)->send(new PhoneOtpMail(
                otp: $otp,
                phone: $phone,
                recipientName: (string) ($user->display_name ?? $user->name ?? 'Pelanggan Manake'),
                expiresInMinutes: $ttlMinutes,
            ));
        }
    }
}
