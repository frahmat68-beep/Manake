<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminManageController extends Controller
{
    // ======================================================================
    // APA YANG SAYA LIHAT?
    // -> [KONTROLLER MANAJEMEN AKUN ADMIN]
    // Kontroller ini mengatur daftar admin dan pembuatan admin baru (Khusus Super Admin).
    //
    // 🎓 KEMUNGKINAN PERTANYAAN DOSEN:
    // 1. "Di mana validasi pendaftaran akun admin baru dilakukan?"
    // 2. "Bagaimana Anda mengenkripsi password admin baru di database?"
    //
    // 🟢 APA YANG BISA SAYA UBAH? (Aman & Mudah)
    // - Batas Minimal Password: Anda bisa mengubah `'min:8'` pada aturan validasi untuk memperpendek atau memperpanjang minimal karakter password admin baru.
    //
    // 🟡 APA RISIKONYA? (Perlu Hati-hati)
    // - `Hash::make(...)`: Password wajib di-hash sebelum masuk ke database. Jika disimpan dalam bentuk teks biasa, login admin akan gagal karena sistem otentikasi mencocokkan password yang di-hash.
    //
    // 🔴 JANGAN DIUBAH!
    // - Pencegahan Hapus Diri Sendiri (baris 94): Super admin dilarang menghapus akunnya sendiri yang sedang aktif untuk mencegah terkunci dari sistem (lockout).
    // ======================================================================

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $admins = Admin::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.admins.index', [
            'admins' => $admins,
            'search' => $search,
            'activePage' => 'admins',
        ]);
    }

    public function create(): View
    {
        return view('admin.admins.create', [
            'activePage' => 'admins',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (auth('admin')->user()->role !== 'super_admin') {
            abort(403, __('Akses ditolak. Hanya Super Admin yang dapat menambahkan admin baru.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,super_admin'],
        ], [
            'name.required' => __('Nama admin wajib diisi.'),
            'email.required' => __('Email wajib diisi.'),
            'email.unique' => __('Email sudah terdaftar untuk admin lain.'),
            'password.required' => __('Password wajib diisi.'),
            'password.min' => __('Password minimal harus 8 karakter.'),
            'password.confirmed' => __('Konfirmasi password tidak cocok.'),
            'role.required' => __('Role wajib dipilih.'),
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'email_verified_at' => now(),
        ]);

        admin_audit('admin.create', 'admins', $admin->id, [
            'email' => $admin->email,
            'role' => $admin->role,
        ], auth('admin')->id());

        return redirect()
            ->route('admin.admins.index')
            ->with('success', __('Akun admin baru berhasil didaftarkan.'));
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        if (auth('admin')->user()->role !== 'super_admin') {
            abort(403, __('Akses ditolak. Hanya Super Admin yang dapat menghapus admin.'));
        }

        $currentAdminId = (int) auth('admin')->id();

        if ((int) $admin->id === $currentAdminId) {
            return back()->with('error', __('Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.'));
        }

        if ($admin->role === 'super_admin') {
            $superAdminsCount = Admin::where('role', 'super_admin')->count();
            if ($superAdminsCount <= 1) {
                return back()->with('error', __('Tidak dapat menghapus Super Admin terakhir di sistem.'));
            }
        }

        $email = $admin->email;
        $admin->delete();

        admin_audit('admin.delete', 'admins', $admin->id, [
            'email' => $email,
        ], $currentAdminId);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', __('Akun admin berhasil dihapus.'));
    }
}
