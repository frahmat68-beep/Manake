<?php

namespace App\Http\Controllers;

use App\Models\PhoneVerification;
use App\Services\PhoneOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class PhoneVerificationController extends Controller
{
    public function show(Request $request): View
    {
        $profile = null;
        if (schema_table_exists_cached('profiles')) {
            $profile = $request->user()->profile()->firstOrCreate([], [
                'is_completed' => false,
            ]);
        }

        $verification = PhoneVerification::query()
            ->where('user_id', $request->user()->id)
            ->first();

        return view('auth.phone-verify', [
            'profile' => $profile,
            'verification' => $verification,
        ]);
    }

    public function requestOtp(Request $request, PhoneOtpService $otpService): RedirectResponse
    {
        if (! schema_table_exists_cached('profiles') || ! schema_table_exists_cached('phone_verifications')) {
            return back()->withErrors(['phone' => __('Fitur verifikasi nomor belum siap. Jalankan migrasi dulu.')]);
        }

        $data = $request->validate([
            'phone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,13}$/'],
        ], [
            'phone.required' => __('Nomor telepon wajib diisi.'),
            'phone.regex' => __('Format nomor telepon tidak valid.'),
        ]);

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([], [
            'is_completed' => false,
        ]);

        $cooldownSeconds = max((int) config('security.phone_otp_resend_cooldown_seconds', 60), 1);
        $maxPerHour = max((int) config('security.phone_otp_resend_max_per_hour', 5), 1);
        $baseKey = 'phone-otp:request:user:' . $user->id;
        $cooldownKey = $baseKey . ':cooldown';
        $hourlyKey = $baseKey . ':hourly';
        if (Cache::has($cooldownKey)) {
            return back()->withErrors(['phone' => __('Tunggu sebentar sebelum meminta OTP lagi.')]);
        }

        $sentThisHour = (int) Cache::get($hourlyKey, 0);
        if ($sentThisHour >= $maxPerHour) {
            return back()->withErrors(['phone' => __('Batas kirim OTP tercapai. Coba lagi dalam 1 jam.')]);
        }

        $normalizedPhone = $this->normalizePhone($data['phone']);
        $phoneChanged = $profile->phone !== $normalizedPhone;
        $profile->phone = $normalizedPhone;
        if ($phoneChanged) {
            $profile->phone_verified_at = null;
        }
        $profile->save();

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otpTtlMinutes = max((int) config('security.phone_otp_ttl_minutes', 5), 1);
        PhoneVerification::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $normalizedPhone,
                'otp_hash' => Hash::make($otp),
                'otp_expires_at' => now()->addMinutes($otpTtlMinutes),
                'otp_attempts' => 0,
                'last_requested_at' => now(),
            ]
        );

        Cache::put($cooldownKey, true, now()->addSeconds($cooldownSeconds));
        Cache::put($hourlyKey, $sentThisHour + 1, now()->addHour());

        try {
            $otpService->sendOtp($user, $normalizedPhone, $otp);
            Log::info('Phone OTP requested.', [
                'user_id' => $user->id,
                'phone' => $normalizedPhone,
            ]);
        } catch (Throwable $exception) {
            Log::error('Phone OTP request failed.', [
                'user_id' => $user->id,
                'phone' => $normalizedPhone,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors(['phone' => __('Gagal mengirim OTP. Silakan coba lagi beberapa saat.')]);
        }

        return back()->with('status', __('OTP dikirim ke nomor :phone. (Mode dev: cek laravel.log)', [
            'phone' => $normalizedPhone,
        ]));
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        if (! schema_table_exists_cached('profiles') || ! schema_table_exists_cached('phone_verifications')) {
            return back()->withErrors(['otp' => __('Fitur verifikasi nomor belum siap. Jalankan migrasi dulu.')]);
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => __('Kode OTP wajib diisi.'),
            'otp.digits' => __('Kode OTP harus 6 digit.'),
        ]);

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([], [
            'is_completed' => false,
        ]);

        $verification = PhoneVerification::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $verification) {
            return back()->withErrors(['otp' => __('OTP belum diminta. Silakan request OTP terlebih dulu.')]);
        }

        if (now()->greaterThan($verification->otp_expires_at)) {
            return back()->withErrors(['otp' => __('OTP sudah kedaluwarsa. Silakan kirim ulang OTP.')]);
        }

        $maxAttempts = max((int) config('security.phone_otp_max_attempts', 5), 1);
        if ($verification->otp_attempts >= $maxAttempts) {
            return back()->withErrors(['otp' => __('Percobaan OTP terlalu banyak. Silakan request OTP baru.')]);
        }

        if (! Hash::check($data['otp'], $verification->otp_hash)) {
            $verification->increment('otp_attempts');
            return back()->withErrors(['otp' => __('Kode OTP salah.')]);
        }

        $profile->phone_verified_at = now();
        $profile->phone = $verification->phone;
        if ($user->profileIsComplete()) {
            $profile->is_completed = true;
            $profile->completed_at = $profile->completed_at ?: now();
        }
        $profile->save();

        $verification->delete();

        return redirect()->intended(route('profile.complete'))
            ->with('success', __('Nomor telepon berhasil diverifikasi.'));
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone) ?: '';
        if ($digits === '') {
            return $phone;
        }

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        return str_starts_with($digits, '0') ? $digits : ('0' . $digits);
    }
}
