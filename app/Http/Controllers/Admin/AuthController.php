<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            return $this->finalizeLogin($request);
        }

        $admin = $this->syncAdminFromUser($credentials['email'], $credentials['password']);

        if (! $admin) {
            $admin = $this->syncAdminFromEnv($credentials['email'], $credentials['password']);
        }

        if ($admin) {
            Auth::guard('admin')->login($admin);

            return $this->finalizeLogin($request);
        }

        return back()->withErrors([
            'email' => __('Email atau password admin salah'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($admin) {
            $this->storeAudit('admin_logout', $admin->id, $request);
        }

        return redirect()->route('admin.login');
    }

    private function finalizeLogin(Request $request)
    {
        $request->session()->regenerate();
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return back()->withErrors([
                'email' => __('Sesi admin tidak valid. Coba login ulang.'),
            ])->onlyInput('email');
        }

        if (! in_array($admin->role ?? 'admin', ['admin', 'super_admin'], true)) {
            Auth::guard('admin')->logout();

            return back()->withErrors([
                'email' => __('Akun tidak memiliki akses admin.'),
            ])->onlyInput('email');
        }

        if (! $admin->email_verified_at) {
            $admin->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $this->storeAudit('admin_login', $admin->id, $request);

        return redirect()->route('admin.dashboard');
    }

    private function syncAdminFromUser(string $email, string $password): ?Admin
    {
        if (! Schema::hasTable('users')) {
            return null;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        $role = $user->role ?? 'user';

        if (! in_array($role, ['admin', 'super_admin'], true)) {
            return null;
        }

        return Admin::updateOrCreate(
            ['email' => $user->email],
            [
                'name' => $user->name ?: 'Admin',
                'password' => $user->password,
                'role' => $role,
                'email_verified_at' => $user->email_verified_at ?: now(),
            ]
        );
    }

    private function syncAdminFromEnv(string $email, string $password): ?Admin
    {
        $superAdminEmail = env('SUPERADMIN_EMAIL', env('SUPER_ADMIN_EMAIL'));
        $superAdminPassword = env('SUPERADMIN_PASSWORD', env('SUPER_ADMIN_PASSWORD'));
        $superAdminName = 'Fikri Rachmat';

        if (! $superAdminEmail || ! $superAdminPassword) {
            return null;
        }

        if (strtolower($email) !== strtolower($superAdminEmail) || $password !== $superAdminPassword) {
            return null;
        }

        return Admin::updateOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => $superAdminName,
                'password' => Hash::make($superAdminPassword),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );
    }

    private function storeAudit(string $action, ?int $adminId, Request $request): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => $action,
            'payload_json' => json_encode([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }
}
