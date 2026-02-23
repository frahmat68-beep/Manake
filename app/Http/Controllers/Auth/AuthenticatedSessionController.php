<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => __('Email atau password salah'),
            ])->withInput($request->only('email', 'auth_modal'));
        }

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
            if (Schema::hasTable('admins')) {
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

            return redirect()->intended(route('admin.dashboard'));
        }

        if (config('security.otp_required') && Schema::hasColumn('users', 'is_otp_verified')) {
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
