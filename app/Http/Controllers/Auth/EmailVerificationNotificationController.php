<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $user = $request->user();
        $cooldownSeconds = max((int) config('security.email_verification_resend_cooldown_seconds', 60), 1);
        $maxPerHour = max((int) config('security.email_verification_resend_max_per_hour', 5), 1);
        $baseKey = 'email-verify:resend:user:' . $user->id;
        $cooldownKey = $baseKey . ':cooldown';
        $hourlyKey = $baseKey . ':hourly';
        if (Cache::has($cooldownKey)) {
            return back()->withErrors([
                'email' => __('Tunggu sebentar sebelum kirim ulang email verifikasi.'),
            ]);
        }

        $sentThisHour = (int) Cache::get($hourlyKey, 0);
        if ($sentThisHour >= $maxPerHour) {
            return back()->withErrors([
                'email' => __('Batas kirim email verifikasi tercapai. Coba lagi dalam 1 jam.'),
            ]);
        }

        Cache::put($cooldownKey, true, now()->addSeconds($cooldownSeconds));
        Cache::put($hourlyKey, $sentThisHour + 1, now()->addHour());

        try {
            $user->sendEmailVerificationNotification();
            Log::info('Verification email sent.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (Throwable $exception) {
            Log::error('Verification email failed.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'email' => __('Gagal mengirim email verifikasi. Coba lagi beberapa saat.'),
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
