@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('Detail Data'))
@section('page_title', __('Data Database'))

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ __('Data Baris') }}</p>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-50">{{ $table }} #{{ data_get($record, $primaryKey) }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.db.table', $table) }}" class="text-sm text-slate-500 hover:text-blue-600 dark:text-slate-400">{{ __('← Kembali ke Tabel') }}</a>
                @if ($canEdit)
                    <a href="{{ route('admin.db.edit', [$table, data_get($record, $primaryKey)]) }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700">{{ __('Ubah Data') }}</a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mk-card border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="mk-card rounded-2xl p-6">
            <div class="space-y-3 text-sm">
                @foreach ($columns as $column)
                    @php
                        $value = data_get($record, $column['Field']);
                        $display = is_null($value) ? '-' : (is_scalar($value) ? (string) $value : json_encode($value));
                    @endphp
                    <div class="flex flex-col border-b border-slate-100 py-2 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $column['Field'] }}</span>
                        <span class="break-all text-slate-800 dark:text-slate-100">{{ $display }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
