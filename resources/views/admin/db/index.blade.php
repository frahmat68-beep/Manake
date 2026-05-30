@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('ui.admin_db.title'))
@section('page_title', __('ui.admin_db.page_title'))

@php
    $dbCopy = __('ui.admin_db');

    if (! is_array($dbCopy)) {
        $dbCopy = [
            'title' => 'Database Data',
            'page_title' => 'Database Data',
            'index' => [
                'kicker' => 'DB Explorer',
                'heading' => 'List of Database Tables',
                'subtitle' => 'Select a table to review its data contents. Editing is only active when ADMIN_DB_EDIT_ENABLED=true.',
                'warning' => 'The main database is Supabase production. Use this page for data audit only, not for mass experiments.',
                'open_table' => 'Open table contents',
                'empty' => 'No tables found.',
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
            <p class="admin-db-kicker">{{ $dbCopy['index']['kicker'] }}</p>
            <h2 class="admin-db-title mt-2 text-2xl font-black">
                {{ $dbCopy['index']['heading'] }}
            </h2>
            <p class="admin-db-muted mt-2 text-sm">
                {!! str_replace('ADMIN_DB_EDIT_ENABLED=true', '<code class="rounded bg-black/5 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10 text-slate-800 dark:text-slate-100">ADMIN_DB_EDIT_ENABLED=true</code>', e($dbCopy['index']['subtitle'])) !!}
            </p>
            <p class="admin-db-warning mt-4 rounded-xl px-4 py-3 text-xs font-bold">
                {{ $dbCopy['index']['warning'] }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($tables as $table)
                <a href="{{ route('admin.db.table', $table) }}" class="admin-db-table-card block p-4">
                    <p class="admin-db-title truncate text-sm font-black" title="{{ $table }}">
                        {{ $table }}
                    </p>
                    <p class="admin-db-muted mt-1 text-xs">
                        {{ $dbCopy['index']['open_table'] }}
                    </p>
                </a>
            @empty
                <div class="admin-db-card p-6 text-sm admin-db-muted">
                    {{ $dbCopy['index']['empty'] }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
