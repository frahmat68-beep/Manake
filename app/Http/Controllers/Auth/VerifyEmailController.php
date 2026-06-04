<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $targetRoute = $user->hasVerifiedRentalIdentity() ? 'profile.complete' : 'profile';

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($targetRoute, ['verified' => 1])->with('success', __('Email sudah terverifikasi.'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $targetRoute = $user->hasVerifiedRentalIdentity() ? 'profile.complete' : 'profile';
        return redirect()->route($targetRoute, ['verified' => 1])->with('success', __('Email berhasil diverifikasi.'));
    }
}
