@extends('layouts.admin', ['activePage' => 'admins'])

@section('title', __('Tambah Admin Baru'))
@section('page_title', __('Tambah Admin Baru'))

@push('head')
<style>
    .admin-admins-create-page {
        color: var(--admin-text);
    }

    .admin-admins-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-admins-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-admins-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-admins-title {
        color: var(--admin-text);
    }

    .admin-admins-muted {
        color: var(--admin-muted);
    }

    .admin-admins-input {
        width: 100%;
        min-height: 3.05rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0 1rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .admin-admins-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-admins-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-admins-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-admins-select {
        width: 100%;
        min-height: 3.05rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0 1rem;
        font-size: 0.875rem;
        outline: none;
        cursor: pointer;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .admin-admins-select:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-admins-select {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-admins-select {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
    }
</style>
@endpush

@section('content')
    <div class="admin-admins-create-page max-w-2xl mx-auto space-y-6">
        {{-- Page Header --}}
        <section>
            <a href="{{ route('admin.admins.index') }}" class="admin-admins-muted text-xs font-semibold hover:text-[#D4A843] transition">
                &larr; {{ __('Kembali ke Daftar Admin') }}
            </a>
            <h2 class="admin-admins-title mt-3 text-2xl font-black">
                {{ __('Daftarkan Admin Baru') }}
            </h2>
            <p class="admin-admins-muted mt-1 text-sm">
                {{ __('Buat akun administratif baru dengan level hak akses yang ditentukan.') }}
            </p>
        </section>

        {{-- Form Card --}}
        <section class="admin-admins-card p-6">
            <form method="POST" action="{{ route('admin.admins.store') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div class="space-y-1.5">
                    <label for="name" class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        {{ __('Nama Lengkap') }}
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="{{ __('Contoh: Ahmad Hidayat') }}"
                        class="admin-admins-input"
                        required
                        autofocus
                    >
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        {{ __('Alamat Email') }}
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="{{ __('Contoh: ahmad@manake.id') }}"
                        class="admin-admins-input"
                        required
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div class="space-y-1.5">
                    <label for="role" class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        {{ __('Tingkat Akses (Role)') }}
                    </label>
                    <select id="role" name="role" class="admin-admins-select" required>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin Operasional</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin (Akses Penuh)</option>
                    </select>
                    @error('role')
                        <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        {{ __('Password Baru') }}
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        class="admin-admins-input"
                        required
                    >
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Confirmation --}}
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        {{ __('Konfirmasi Password Baru') }}
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="••••••••"
                        class="admin-admins-input"
                        required
                    >
                </div>

                {{-- Actions --}}
                <div class="border-t admin-border pt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.admins.index') }}" class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 py-2 text-xs font-semibold transition">
                        {{ __('Batal') }}
                    </a>
                    <button type="submit" class="admin-accent-bg inline-flex min-h-10 items-center justify-center rounded-xl px-5 py-2 text-xs font-bold transition">
                        {{ __('Daftarkan Admin') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
