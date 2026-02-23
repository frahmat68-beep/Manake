@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('Data Database'))
@section('page_title', __('Data Database'))

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Tabel') }}</p>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $table }}</h2>
            </div>
            <a href="{{ route('admin.db.index') }}" class="text-sm text-slate-500 hover:text-blue-600 dark:text-slate-300">{{ __('← Kembali ke Daftar Tabel') }}</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 dark:border-slate-800 dark:bg-slate-900">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-[1fr,2fr,auto] gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Filter Kolom') }}</label>
                    <select name="column" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="">{{ __('Semua') }}</option>
                        @foreach ($columns as $column)
                            <option value="{{ $column['Field'] }}" @selected($searchColumn === $column['Field'])>
                                {{ $column['Field'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Cari') }}</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $searchValue }}"
                        placeholder="{{ __('Ketik kata kunci') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    >
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">{{ __('Cari') }}</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-widest text-slate-400 dark:border-slate-800">
                            @foreach ($columns as $column)
                                <th class="px-3 py-2">{{ $column['Field'] }}</th>
                            @endforeach
                            <th class="px-3 py-2">{{ __('Tindakan') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php $rowArray = (array) $row; @endphp
                            <tr class="border-b border-slate-100 text-slate-700 dark:border-slate-800 dark:text-slate-200">
                                @foreach ($columns as $column)
                                    @php
                                        $value = $rowArray[$column['Field']] ?? null;
                                        $display = is_null($value) ? '-' : (is_scalar($value) ? (string) $value : json_encode($value));
                                    @endphp
                                    <td class="px-3 py-2 align-top">{{ $display }}</td>
                                @endforeach
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if ($primaryKey && isset($rowArray[$primaryKey]))
                                        <a href="{{ route('admin.db.show', [$table, $rowArray[$primaryKey]]) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">{{ __('Lihat') }}</a>
                                        @if ($canEdit)
                                            <span class="mx-2 text-slate-300">|</span>
                                            <a href="{{ route('admin.db.edit', [$table, $rowArray[$primaryKey]]) }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">{{ __('Ubah') }}</a>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}" class="px-3 py-6 text-center text-sm text-slate-500">{{ __('Tidak ada data.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
@endsection
