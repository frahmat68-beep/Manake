<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OtpController extends Controller
{
    public function showForm()
    {
        if (! config('security.otp_required')) {
            return redirect()->route('dashboard');
        }

        return view('auth.otp');
    }

    public function verify(Request $request)
    {
        if (! config('security.otp_required')) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! schema_column_exists_cached('users', 'is_otp_verified')) {
            return redirect()->route('dashboard');
        }

        if (! $user->otp_code || ! $user->otp_expires_at) {
            return back()->withErrors(['otp' => __('Kode OTP belum tersedia. Silakan kirim ulang OTP.')]);
        }

        $maxAttempts = max((int) config('security.otp_resend_max_per_hour', 5), 1);
        $attemptKey = 'otp:verify:attempts:user:' . $user->id;
        $attempts = (int) Cache::get($attemptKey, 0);
        if ($attempts >= $maxAttempts) {
            return back()->withErrors(['otp' => __('Percobaan OTP terlalu banyak. Silakan kirim ulang OTP baru.')]);
        }

        if (
            $user->otp_code !== $request->otp ||
            now()->greaterThan($user->otp_expires_at)
        ) {
            Cache::put($attemptKey, $attempts + 1, now()->addHour());
            return back()->withErrors(['otp' => __('OTP salah atau sudah kedaluwarsa.')]);
        }

        $user->clearOtp();
        Cache::forget($attemptKey);
        $request->session()->put('otp_verified', true);

        return redirect()->route('profile.complete')->with('status', __('OTP berhasil diverifikasi.'));
    }

    public function resend(Request $request)
    {
        if (! config('security.otp_required')) {
            return redirect()->route('dashboard');
        }

        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $cooldownSeconds = max((int) config('security.otp_resend_cooldown_seconds', 60), 1);
        $maxPerHour = max((int) config('security.otp_resend_max_per_hour', 5), 1);
        $baseKey = 'otp:resend:user:' . $user->id;
        $cooldownKey = $baseKey . ':cooldown';
        $hourlyKey = $baseKey . ':hourly';

        if (Cache::has($cooldownKey)) {
            return back()->withErrors(['otp' => __('Tunggu sebentar sebelum meminta OTP lagi.')]);
        }

        $sentThisHour = (int) Cache::get($hourlyKey, 0);
        if ($sentThisHour >= $maxPerHour) {
            return back()->withErrors(['otp' => __('Batas kirim OTP tercapai. Coba lagi dalam 1 jam.')]);
        }

        Cache::put($cooldownKey, true, now()->addSeconds($cooldownSeconds));
        Cache::put($hourlyKey, $sentThisHour + 1, now()->addHour());

        try {
            $otp = $user->generateOtp();
            Mail::to($user->email)->send(new OtpMail($otp));
            Log::info('Email OTP sent.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send email OTP.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors(['otp' => __('Gagal mengirim OTP. Silakan coba lagi beberapa saat.')]);
        }

        $request->session()->put('otp_verified', false);

        return back()->with('status', __('OTP baru sudah dikirim ke email kamu.'));
    }
}
