@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('Data Database'))
@section('page_title', __('Data Database'))

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="mk-card rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">{{ __('Daftar Tabel Database') }}</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                {!! __('Pilih tabel untuk cek isi data. Edit data hanya aktif kalau <code>ADMIN_DB_EDIT_ENABLED=true</code>.') !!}
            </p>
            <p class="mt-3 rounded-lg border border-amber-500/25 bg-amber-950/25 px-4 py-3 text-xs font-semibold text-amber-200">
                {{ __('Database utama adalah Supabase production. Pakai halaman ini untuk audit data, bukan eksperimen massal.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($tables as $table)
                <a
                    href="{{ route('admin.db.table', $table) }}"
                    class="mk-card rounded-2xl p-4 transition hover:shadow-md"
                >
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $table }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Buka isi tabel') }}</p>
                </a>
            @empty
                <div class="mk-card rounded-2xl p-6 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Tidak ada tabel ditemukan.') }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
