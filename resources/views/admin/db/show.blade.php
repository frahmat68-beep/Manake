@extends('layouts.admin', ['activePage' => 'db'])

@section('title', 'Detail Data')
@section('page_title', 'Data Database')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Data Baris</p>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $table }} #{{ data_get($record, $primaryKey) }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.db.table', $table) }}" class="text-sm text-slate-500 hover:text-blue-600 dark:text-slate-300">← Kembali ke Tabel</a>
                @if ($canEdit)
                    <a href="{{ route('admin.db.edit', [$table, data_get($record, $primaryKey)]) }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700">Ubah Data</a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="space-y-3 text-sm">
                @foreach ($columns as $column)
                    @php
                        $value = data_get($record, $column['Field']);
                        $display = is_null($value) ? '-' : (is_scalar($value) ? (string) $value : json_encode($value));
                    @endphp
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 py-2 dark:border-slate-800">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ $column['Field'] }}</span>
                        <span class="text-slate-800 dark:text-slate-100 break-all">{{ $display }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
