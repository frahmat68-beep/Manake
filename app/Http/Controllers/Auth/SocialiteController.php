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
            
            return redirect()->intended(route('dashboard'));

        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal login menggunakan Google: ' . $e->getMessage(),
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
