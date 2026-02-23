@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('Data Database'))
@section('page_title', __('Data Database'))

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Daftar Tabel Database') }}</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">
                {!! __('Pilih tabel untuk cek isi data. Edit data hanya aktif kalau <code>ADMIN_DB_EDIT_ENABLED=true</code>.') !!}
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($tables as $table)
                <a
                    href="{{ route('admin.db.table', $table) }}"
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm hover:shadow-md transition dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $table }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Buka isi tabel') }}</p>
                </a>
            @empty
                <div class="rounded-2xl border border-slate-100 bg-white p-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                    {{ __('Tidak ada tabel ditemukan.') }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
