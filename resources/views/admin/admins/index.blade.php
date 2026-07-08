@extends('layouts.admin', ['activePage' => 'admins'])

@section('title', __('Kelola Admin'))
@section('page_title', __('Kelola Admin'))

@push('head')
<style>
    .admin-admins-page {
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

    .admin-admins-table thead {
        background: var(--admin-surface-raised);
        color: var(--admin-muted);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-admins-table thead {
        background: #F8FAFC !important;
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-admins-table thead {
        background: #0A0A0B !important;
        color: #A0A0A8 !important;
    }

    .admin-admins-table tbody tr {
        background: transparent !important;
        color: var(--admin-text) !important;
        border-bottom: 1px solid var(--admin-border);
        transition: background-color 160ms ease, color 160ms ease;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-admins-table tbody tr:hover {
        background: #F8FAFC !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-admins-table tbody tr:hover {
        background: #141416 !important;
    }
</style>
@endpush

@section('content')
    <div class="admin-admins-page space-y-6">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- Page Header --}}
        <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="admin-admins-title text-xs font-black uppercase tracking-[0.24em] text-[#D4A843]">
                    {{ __('Keamanan Sistem') }}
                </p>
                <h2 class="admin-admins-title mt-2 text-2xl font-black">
                    {{ __('Kelola Admin') }}
                </h2>
                <p class="admin-admins-muted mt-1 text-sm">
                    {{ __('Daftar dan manajemen akun administratif website Manake.') }}
                </p>
            </div>
            <div>
                <a href="{{ route('admin.admins.create') }}" class="admin-accent-bg inline-flex min-h-10 items-center justify-center rounded-xl px-4 py-2 text-xs font-bold transition">
                    {{ __('Tambah Admin Baru') }}
                </a>
            </div>
        </section>

        {{-- Search Section --}}
        <section>
            <form method="GET" action="{{ route('admin.admins.index') }}" class="flex flex-col gap-3 md:flex-row">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ __('Cari berdasarkan nama atau email admin...') }}"
                    class="admin-admins-input"
                >
                <button type="submit" class="admin-accent-bg inline-flex min-h-[3.05rem] items-center justify-center rounded-xl px-5 text-sm font-bold transition">
                    {{ __('Cari') }}
                </button>

                @if (!empty($search))
                    <a href="{{ route('admin.admins.index') }}" class="admin-secondary-button inline-flex min-h-[3.05rem] items-center justify-center rounded-xl px-4 text-sm font-semibold transition">
                        {{ __('Reset') }}
                    </a>
                @endif
            </form>
        </section>

        {{-- Table Card --}}
        <section class="admin-admins-card overflow-hidden p-0">
            <div class="flex flex-col gap-1 border-b px-5 py-4 admin-border">
                <h3 class="admin-admins-title text-base font-black">
                    {{ __('Semua Akun Admin') }}
                </h3>
                <p class="admin-admins-muted text-xs">
                    {{ __('Hak akses administratif sistem Manake.') }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-admins-table w-full min-w-[700px] table-fixed text-sm">
                    <colgroup>
                        <col class="w-[40%]">
                        <col class="w-[20%]">
                        <col class="w-[20%]">
                        <col class="w-[20%]">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.16em]">{{ __('Nama / Email') }}</th>
                            <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.16em]">{{ __('Hak Akses') }}</th>
                            <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.16em]">{{ __('Dibuat Pada') }}</th>
                            <th class="px-5 py-4 text-right text-[11px] font-black uppercase tracking-[0.16em]">{{ __('Tindakan') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($admins as $item)
                            <tr>
                                <td class="px-5 py-4 align-middle">
                                    <p class="truncate font-bold admin-admins-title" title="{{ $item->name }}">
                                        {{ $item->name }}
                                    </p>
                                    <p class="mt-0.5 truncate text-xs admin-admins-muted" title="{{ $item->email }}">
                                        {{ $item->email }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    @if ($item->role === 'super_admin')
                                        <span class="status-chip status-chip-success">Super Admin</span>
                                    @else
                                        <span class="status-chip status-chip-info">Admin</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle admin-admins-muted text-xs">
                                    {{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="px-5 py-4 text-right align-middle">
                                    @if ((int) $item->id !== (int) auth('admin')->id())
                                        <form 
                                            method="POST" 
                                            action="{{ route('admin.admins.destroy', $item) }}" 
                                            class="inline-block"
                                            data-confirm="{{ __('Apakah Anda yakin ingin menghapus akun admin :name? Tindakan ini tidak bisa dibatalkan.', ['name' => $item->name]) }}"
                                            data-confirm-title="{{ __('Hapus Akun Admin') }}"
                                            data-confirm-button="{{ __('Hapus') }}"
                                            data-cancel-button="{{ __('Batal') }}"
                                            data-confirm-variant="danger"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-secondary-button border-rose-500/20 text-rose-500 hover:bg-rose-500/10 inline-flex min-h-9 items-center justify-center rounded-lg px-3 text-xs font-bold transition">
                                                {{ __('Hapus') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs font-semibold admin-admins-muted italic">{{ __('Aktif (Anda)') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center admin-admins-muted">
                                    {{ __('Tidak ada admin yang ditemukan.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($admins->hasPages())
                <div class="border-t admin-border px-5 py-4">
                    {{ $admins->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
