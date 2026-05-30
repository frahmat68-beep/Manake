@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('ui.admin_db.title'))
@section('page_title', __('ui.admin_db.page_title'))

@php
    $dbCopy = __('ui.admin_db');

    if (! is_array($dbCopy)) {
        $dbCopy = [
            'title' => 'Database Data',
            'page_title' => 'Database Data',
            'table' => [
                'kicker' => 'Table',
                'back' => '← Back to Table List',
                'filter_column' => 'Filter Column',
                'all_columns' => 'All Columns',
                'search' => 'Search',
                'search_placeholder' => 'Type keyword',
                'actions' => 'Actions',
                'view' => 'View',
                'edit' => 'Edit',
                'empty' => 'No data found.',
            ],
        ];
    }
@endphp

@push('head')

<style>
    .admin-db-page {
        color: var(--admin-text);
    }

    .admin-db-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-db-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-db-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-db-title {
        color: var(--admin-text);
    }

    .admin-db-muted {
        color: var(--admin-muted);
    }

    .admin-db-subtle {
        color: var(--admin-subtle);
    }

    .admin-db-kicker {
        color: var(--admin-accent);
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-db-input {
        width: 100%;
        min-height: 2.9rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0.7rem 0.9rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .admin-db-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-db-table thead {
        background: var(--admin-surface-raised);
        color: var(--admin-muted);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table thead {
        background: #F8FAFC !important;
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table thead {
        background: #0A0A0B !important;
        color: #A0A0A8 !important;
    }

    .admin-db-table tbody tr {
        background: transparent !important;
        color: var(--admin-text) !important;
        border-bottom: 1px solid var(--admin-border);
        transition: background-color 160ms ease, color 160ms ease;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table tbody tr,
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table tbody tr td {
        background: #FFFFFF !important;
        color: #111827 !important;
        border-bottom-color: #E5E7EB !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table tbody tr:hover,
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table tbody tr:hover td {
        background: #F8FAFC !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table tbody tr,
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table tbody tr td {
        background: #111113 !important;
        color: #E8E8EC !important;
        border-bottom-color: #1A1A1E !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table tbody tr:hover,
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table tbody tr:hover td {
        background: #151519 !important;
        color: #E8E8EC !important;
    }

    .admin-db-warning {
        border: 1px solid rgba(245, 158, 11, 0.25);
        background: rgba(245, 158, 11, 0.1);
        color: #B45309;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-warning {
        background: rgba(217, 173, 63, 0.12) !important;
        color: #D9AD3F !important;
        border-color: rgba(217, 173, 63, 0.25) !important;
    }

    .admin-db-table-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1rem;
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .admin-db-table-card:hover {
        transform: translateY(-1px);
        border-color: var(--admin-accent);
        box-shadow: 0 16px 35px -28px rgba(0,0,0,0.35);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
    }
</style>

@endpush

@section('content')
    <div class="admin-db-page mx-auto max-w-7xl space-y-5 sm:space-y-6">
        <div class="admin-db-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="admin-db-kicker">{{ $dbCopy['table']['kicker'] }}</p>
                    <h2 class="admin-db-title mt-2 text-2xl font-black">{{ $table }}</h2>
                </div>
                <a href="{{ route('admin.db.index') }}" class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-bold transition">
                    {{ $dbCopy['table']['back'] }}
                </a>
            </div>
        </div>

        <div class="admin-db-card p-5 sm:p-6">
            <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(180px,0.35fr),minmax(0,1fr),auto]">
                <div>
                    <label class="admin-db-kicker">{{ $dbCopy['table']['filter_column'] }}</label>
                    <select name="column" class="admin-db-input mt-2">
                        <option value="">{{ $dbCopy['table']['all_columns'] }}</option>
                        @foreach ($columns as $column)
                            <option value="{{ $column['Field'] }}" @selected($searchColumn === $column['Field'])>
                                {{ $column['Field'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="admin-db-kicker">{{ $dbCopy['table']['search'] }}</label>
                    <input type="text" name="q" value="{{ $searchValue }}" placeholder="{{ $dbCopy['table']['search_placeholder'] }}" class="admin-db-input mt-2">
                </div>

                <div class="flex items-end">
                    <button class="admin-accent-bg inline-flex min-h-[2.9rem] w-full items-center justify-center rounded-xl px-5 text-sm font-bold transition">
                        {{ $dbCopy['table']['search'] }}
                    </button>
                </div>
            </form>
        </div>

        <div class="admin-db-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="admin-db-table min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-black uppercase tracking-[0.14em]">
                            @foreach ($columns as $column)
                                <th class="px-4 py-3">{{ $column['Field'] }}</th>
                            @endforeach
                            <th class="px-4 py-3">{{ $dbCopy['table']['actions'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php $rowArray = (array) $row; @endphp
                            <tr>
                                @foreach ($columns as $column)
                                    @php
                                        $value = $rowArray[$column['Field']] ?? null;
                                        $display = is_null($value) ? '-' : (is_scalar($value) ? (string) $value : json_encode($value));
                                    @endphp
                                    <td class="max-w-[260px] break-all px-4 py-3 align-top">
                                        {{ $display }}
                                    </td>
                                @endforeach

                                <td class="whitespace-nowrap px-4 py-3 align-top">
                                    @if ($primaryKey && isset($rowArray[$primaryKey]))
                                        <a href="{{ route('admin.db.show', [$table, $rowArray[$primaryKey]]) }}" class="font-bold text-[var(--admin-accent)] hover:underline">
                                            {{ $dbCopy['table']['view'] }}
                                        </a>
                                        @if ($canEdit)
                                            <span class="mx-2 admin-db-subtle">|</span>
                                            <a href="{{ route('admin.db.edit', [$table, $rowArray[$primaryKey]]) }}" class="font-bold text-[var(--admin-accent)] hover:underline">
                                                {{ $dbCopy['table']['edit'] }}
                                            </a>
                                        @endif
                                    @else
                                        <span class="admin-db-subtle">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}" class="px-4 py-10 text-center text-sm admin-db-muted">
                                    {{ $dbCopy['table']['empty'] }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--admin-border)] px-4 py-4">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
@endsection
