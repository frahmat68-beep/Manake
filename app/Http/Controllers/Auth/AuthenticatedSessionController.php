<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $maxAttempts = max((int) config('security.login_max_attempts', 5), 1);
        $lockoutSeconds = max((int) config('security.login_lockout_seconds', 300), 60);
        $throttleKey = 'login:web:' . Str::lower((string) $credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => __('Terlalu banyak percobaan login. Coba lagi dalam :seconds detik.', [
                    'seconds' => $seconds,
                ]),
            ])->withInput($request->only('email', 'auth_modal'));
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, $lockoutSeconds);

            return back()->withErrors([
                'email' => __('Email atau password salah'),
            ])->withInput($request->only('email', 'auth_modal'));
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user = $request->user();
        $role = $user->role ?? 'user';
        $context = $request->input('login_context');

        if ($context === 'admin' && ! in_array($role, ['admin', 'super_admin'], true)) {
            Auth::logout();

            return back()->withErrors([
                'email' => __('Akun ini tidak memiliki akses admin.'),
            ])->withInput($request->only('email', 'auth_modal'));
        }

        if (in_array($role, ['admin', 'super_admin'], true)) {
            try {
                if (schema_table_exists_cached('admins')) {
                    $admin = Admin::updateOrCreate(
                        ['email' => $user->email],
                        [
                            'name' => $user->name ?: __('Admin'),
                            'password' => $user->password,
                            'role' => $role,
                            'email_verified_at' => $user->email_verified_at ?: now(),
                        ]
                    );

                    Auth::guard('admin')->login($admin);
                }
            } catch (Throwable $e) {
                Log::error('Admin Sync Failure in Login: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'trace' => $e->getTraceAsString()
                ]);
                // We logic fallback: don't crash, let them in as user or show specific error
                // For production security audit, we decide to allow the login to continue but without admin guard if sync fails
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        if (config('security.otp_required') && schema_column_exists_cached('users', 'is_otp_verified')) {
            if (! (bool) $user->is_otp_verified) {
                $request->session()->put('otp_verified', false);

                return redirect()->route('otp.form');
            }
        }

        $request->session()->put('otp_verified', true);

        return redirect()->intended(route('dashboard'));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
