<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnsureProfileCompleted
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if (! schema_table_exists_cached('profiles')) {
            return redirect()
                ->route('profile.complete')
                ->with('error', __('Profil belum siap, jalankan migrasi terlebih dahulu.'));
        }

        $user->load('profile');

        if (! $user->profileIsComplete()) {
            return redirect()->guest(route('profile.complete'))
                ->with('error', __('Lengkapi profil dulu sebelum checkout.'));
        }

        if (! $user->hasVerifiedPhone()) {
            return redirect()->guest(route('phone.verify'))
                ->with('error', __('Verifikasi nomor telepon dulu sebelum checkout.'));
        }

        return $next($request);
    }
}
