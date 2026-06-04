<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileCompleted
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $user->load('profile');

        if (! schema_table_exists_cached('profiles')) {
            return redirect()
                ->route('profile')
                ->with('error', __('Profil belum siap, jalankan migrasi terlebih dahulu.'));
        }

        if (! $user->hasCompleteRentalProfile()) {
            return redirect()
                ->route('profile')
                ->with('warning', __('Lengkapi profil penyewaan sebelum memesan alat.'));
        }

        if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('profile')
                ->with('warning', __('Verifikasi email dan lengkapi profil sebelum melanjutkan pemesanan.'));
        }

        if (! optional($user->profile)->rental_consent_accepted_at) {
            return redirect()
                ->route('profile')
                ->with('warning', __('Setujui pernyataan tanggung jawab sewa sebelum melanjutkan pemesanan.'));
        }

        return $next($request);
    }
}
