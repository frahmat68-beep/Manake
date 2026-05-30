@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('ui.admin_db.show.title'))
@section('page_title', __('ui.admin_db.page_title'))

@php
    $dbCopy = __('ui.admin_db');

    if (! is_array($dbCopy)) {
        $dbCopy = [
            'title' => 'Database Data',
            'page_title' => 'Database Data',
            'show' => [
                'title' => 'Data Detail',
                'kicker' => 'Row Data',
                'back' => '← Back to Table',
                'edit' => 'Edit Data',
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
    <div class="admin-db-page mx-auto max-w-5xl space-y-5 sm:space-y-6">
        <div class="admin-db-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="admin-db-kicker">{{ $dbCopy['show']['kicker'] }}</p>
                    <h2 class="admin-db-title mt-2 text-2xl font-black">
                        {{ $table }} #{{ data_get($record, $primaryKey) }}
                    </h2>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.db.table', $table) }}" class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-bold transition">
                        {{ $dbCopy['show']['back'] }}
                    </a>

                    @if ($canEdit)
                        <a href="{{ route('admin.db.edit', [$table, data_get($record, $primaryKey)]) }}" class="admin-accent-bg inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-bold transition">
                            {{ $dbCopy['show']['edit'] }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="admin-db-card p-5 sm:p-6">
            <div class="grid grid-cols-1 gap-3">
                @foreach ($columns as $column)
                    @php
                        $value = data_get($record, $column['Field']);
                        $display = is_null($value) ? '-' : (is_scalar($value) ? (string) $value : json_encode($value));
                    @endphp
                    <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-raised)] px-4 py-3">
                        <p class="admin-db-kicker">{{ $column['Field'] }}</p>
                        <p class="admin-db-title mt-2 break-all text-sm font-bold">
                            {{ $display }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
