<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteController extends Controller
{
    /**
     * Redirect to the provider's authentication page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        if ($provider !== 'google') {
            abort(404);
        }

        if (! $this->googleOauthIsConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Login Google belum aktif karena konfigurasi OAuth production belum lengkap.',
                ]);
        }

        return Socialite::driver($provider)
            ->redirectUrl($this->getRedirectUrl())
            ->redirect();
    }

    /**
     * Handle the provider callback.
     */
    public function callback(string $provider): RedirectResponse
    {
        if ($provider !== 'google') {
            abort(404);
        }

        try {
            if (! $this->googleOauthIsConfigured()) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Login Google belum aktif karena konfigurasi OAuth production belum lengkap.',
                    ]);
            }

            $socialUser = Socialite::driver($provider)
                ->redirectUrl($this->getRedirectUrl())
                ->user();
            
            $user = User::where('google_id', $socialUser->getId())
                ->orWhere('email', $socialUser->getEmail())
                ->first();

            if ($user) {
                // Update social data if it's the same email but first time login via social
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $socialUser->getId(),
                        'google_token' => $socialUser->token,
                        'google_refresh_token' => $socialUser->refreshToken,
                    ]);
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Google User',
                    'email' => $socialUser->getEmail(),
                    'google_id' => $socialUser->getId(),
                    'google_token' => $socialUser->token,
                    'google_refresh_token' => $socialUser->refreshToken,
                    'password' => Hash::make(Str::random(24)), // Random password
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user);
            
            $role = $user->role ?? 'user';
            if (in_array($role, ['admin', 'super_admin'], true)) {
                return redirect()->intended(route('admin.dashboard'));
            }

            $user->loadMissing('profile');
            if (method_exists($user, 'hasVerifiedRentalIdentity') && $user->hasVerifiedRentalIdentity()) {
                session()->forget('after_profile_redirect');
                return redirect()->route('home');
            }

            $intendedUrl = session()->get('url.intended');
            $safeInternalUrl = null;
            if ($intendedUrl) {
                $parsed = parse_url($intendedUrl);
                $currentHost = request()->getHost();
                $intendedHost = $parsed['host'] ?? null;
                if ($intendedHost === null || $intendedHost === $currentHost) {
                    $path = $parsed['path'] ?? '/';
                    if (!in_array($path, ['/login', '/register', '/logout', '/profile', '/profile/complete'], true)) {
                        $safeInternalUrl = $path . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
                    }
                }
            }
            if ($safeInternalUrl) {
                session(['after_profile_redirect' => $safeInternalUrl]);
            }

            return redirect()->route('profile');

        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth Login failure: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal login menggunakan Google. Silakan coba lagi atau gunakan login email.',
            ]);
        }
    }

    /**
     * Get the resolved redirect URL for Google OAuth dynamically.
     */
    private function getRedirectUrl(): string
    {
        $configured = trim((string) config('services.google.redirect', ''));
        
        $currentHost = request()->getHost();
        $isCurrentLocal = in_array($currentHost, ['127.0.0.1', 'localhost'], true);
        
        if ($configured === '') {
            return url('/auth/google/callback');
        }
        
        $configuredHost = parse_url($configured, PHP_URL_HOST);
        $isConfiguredLocal = in_array($configuredHost, ['127.0.0.1', 'localhost'], true);
        
        // If current site is production but configured is local, override it dynamically
        if (!$isCurrentLocal && $isConfiguredLocal) {
            return url('/auth/google/callback');
        }
        
        return $configured;
    }

    private function googleOauthIsConfigured(): bool
    {
        return trim((string) config('services.google.client_id', '')) !== ''
            && trim((string) config('services.google.client_secret', '')) !== '';
    }
}
