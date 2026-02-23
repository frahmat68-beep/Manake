<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $users = User::query()
            ->with('profile')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'activePage' => 'users',
        ]);
    }

    public function show(User $user): View
    {
        $user->load(['profile', 'orders' => fn ($query) => $query->latest()->limit(5)]);

        admin_audit('user.view_detail', 'users', $user->id, [
            'email' => $user->email,
        ], auth('admin')->id());

        return view('admin.users.show', [
            'user' => $user,
            'activePage' => 'users',
        ]);
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        $status = Password::broker('users')->sendResetLink(['email' => $user->email]);

        admin_audit('user.password_reset_link', 'users', $user->id, [
            'email' => $user->email,
            'status' => $status,
        ], auth('admin')->id());

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __('Link reset password berhasil dikirim ke email user.'));
        }

        // Fallback for environments without working outbound email.
        $token = Password::broker('users')->createToken($user);
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        admin_audit('user.password_reset_manual', 'users', $user->id, [
            'email' => $user->email,
            'status' => $status,
        ], auth('admin')->id());

        return back()->with('success', __('Email reset tidak terkirim. Kirim tautan reset manual ke user: :url', [
            'url' => $resetUrl,
        ]));
    }

    public function setPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.required' => __('Password baru wajib diisi.'),
            'new_password.confirmed' => __('Konfirmasi password tidak cocok.'),
            'new_password.min' => __('Password minimal 8 karakter.'),
        ]);

        $user->forceFill([
            'password' => Hash::make($data['new_password']),
        ])->save();

        admin_audit('user.password_reset_by_admin', 'users', $user->id, [
            'email' => $user->email,
        ], auth('admin')->id());

        return back()->with('success', __('Password user berhasil direset oleh admin.'));
    }
}
