@extends('layouts.admin', ['activePage' => 'categories'])

@section('title', __('ui.admin_categories.title'))
@section('page_title', __('ui.admin_categories.page_title'))

@push('head')
<style>
    .admin-categories-page {
        color: var(--admin-text);
    }

    .admin-categories-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-categories-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-categories-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-categories-title {
        color: var(--admin-text);
    }

    .admin-categories-muted {
        color: var(--admin-muted);
    }

    .admin-categories-kicker {
        color: var(--admin-accent);
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-categories-input {
        width: 100%;
        min-height: 3.05rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0 1rem;
        outline: none;
    }

    .admin-categories-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-categories-table thead {
        background: var(--admin-surface-raised);
        color: var(--admin-muted);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table thead {
        background: #F8FAFC !important;
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table thead {
        background: #0A0A0B !important;
        color: #A0A0A8 !important;
    }

    .admin-categories-table tbody tr {
        background: transparent !important;
        color: var(--admin-text) !important;
        border-bottom: 1px solid var(--admin-border);
        transition: background-color 160ms ease, color 160ms ease;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr {
        border-bottom-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr {
        border-bottom-color: #1A1A1E !important;
    }

    .admin-categories-table tbody tr:last-child {
        border-bottom: 0 !important;
    }

    /* ── Subtle row hover — light mode ── */
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover {
        background: #F8FAFC !important;
        color: #111827 !important;
    }

    /* ── Subtle row hover — dark mode ── */
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover {
        background: #151519 !important;
        color: #E8E8EC !important;
    }

    /* Force cell-level override (outranks the global #0a0a0b rule in app.css) */
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover td {
        background-color: #F8FAFC !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover td {
        background-color: #151519 !important;
        color: #E8E8EC !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover .admin-categories-title {
        color: #111827 !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover .admin-categories-muted {
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover .admin-categories-title {
        color: #E8E8EC !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover .admin-categories-muted {
        color: #A0A0A8 !important;
    }

    .admin-categories-table-thead {
        background: var(--admin-surface-raised);
        color: var(--admin-muted);
    }
</style>
@endpush

    @php
        $categoriesCopy = __('ui.admin_categories');

        if (! is_array($categoriesCopy)) {
            $categoriesCopy = [
                'title' => 'Categories',
                'page_title' => 'Categories',
                'kicker' => 'Categories',
                'heading' => 'Catalog Categories',
                'subtitle' => 'Manage tool grouping and optimize slugs for the catalog structure.',
                'add_category' => '+ Add Category',
                'filters' => [
                    'search_placeholder' => 'Search categories...',
                    'search' => 'Search',
                    'reset' => 'Reset',
                ],
                'table' => [
                    'title' => 'Category List',
                    'subtitle' => 'Manage category names, slugs, and related equipment counts.',
                    'category' => 'Category',
                    'slug' => 'Slug',
                    'equipment' => 'Equipment',
                    'action' => 'Action',
                    'tool_count' => ':count tools',
                    'edit' => 'Edit',
                    'delete' => 'Delete',
                    'locked' => 'Locked',
                    'locked_title' => 'This category still has tools.',
                    'empty' => 'No categories found.',
                ],
            ];
        }
    @endphp

    <div class="admin-categories-page mx-auto max-w-7xl space-y-5 sm:space-y-6">
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

        {{-- Filter / Search Card --}}
        <section class="admin-categories-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="admin-categories-kicker">{{ $categoriesCopy['kicker'] }}</p>
                    <h2 class="admin-categories-title mt-2 text-2xl font-black">
                        {{ $categoriesCopy['heading'] }}
                    </h2>
                    <p class="admin-categories-muted mt-1 text-sm">
                        {{ $categoriesCopy['subtitle'] }}
                    </p>
                </div>

                <a
                    href="{{ route('admin.categories.create') }}"
                    class="admin-accent-bg inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-bold transition"
                >
                    {{ $categoriesCopy['add_category'] }}
                </a>
            </div>

            <form method="GET" action="{{ route('admin.categories.index') }}" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ $categoriesCopy['filters']['search_placeholder'] }}"
                    class="admin-categories-input sm:max-w-md"
                >

                <button class="admin-accent-bg inline-flex min-h-[3.05rem] items-center justify-center rounded-xl px-5 text-sm font-bold transition">
                    {{ $categoriesCopy['filters']['search'] }}
                </button>

                @if (!empty($search))
                    <a href="{{ route('admin.categories.index') }}" class="admin-secondary-button inline-flex min-h-[3.05rem] items-center justify-center rounded-xl px-4 text-sm font-semibold transition">
                        {{ $categoriesCopy['filters']['reset'] }}
                    </a>
                @endif
            </form>
        </section>

        {{-- Table Card --}}
        <section class="admin-categories-card overflow-hidden p-0">
            <div class="flex flex-col gap-1 border-b px-5 py-4 admin-border">
                <h3 class="admin-categories-title text-lg font-black">
                    {{ $categoriesCopy['table']['title'] }}
                </h3>
                <p class="admin-categories-muted text-sm">
                    {{ $categoriesCopy['table']['subtitle'] }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-categories-table w-full min-w-[760px] table-fixed text-sm">
                    <colgroup>
                        <col class="w-[32%]">
                        <col class="w-[28%]">
                        <col class="w-[18%]">
                        <col class="w-[22%]">
                    </colgroup>
                    <thead>
                        <tr class="admin-categories-table-thead">
                            <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.16em]">{{ $categoriesCopy['table']['category'] }}</th>
                            <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.16em]">{{ $categoriesCopy['table']['slug'] }}</th>
                            <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.16em]">{{ $categoriesCopy['table']['equipment'] }}</th>
                            <th class="px-5 py-4 text-right text-[11px] font-black uppercase tracking-[0.16em]">{{ $categoriesCopy['table']['action'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-5 py-4 align-middle">
                                    <p class="truncate font-bold admin-categories-title" title="{{ $category->name }}">
                                        {{ $category->name }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 align-middle admin-categories-muted">
                                    <p class="truncate" title="{{ $category->slug }}">
                                        {{ $category->slug }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 align-middle admin-categories-muted">
                                    {{ str_replace(':count', $category->equipments_count, $categoriesCopy['table']['tool_count']) }}
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <a
                                            href="{{ route('admin.categories.edit', $category->slug) }}"
                                            class="admin-secondary-button inline-flex min-h-9 items-center justify-center rounded-lg px-3 text-xs font-bold transition"
                                        >
                                            {{ $categoriesCopy['table']['edit'] }}
                                        </a>

                                        @if ((int) $category->equipments_count > 0)
                                            <button
                                                type="button"
                                                disabled
                                                class="admin-category-locked-button inline-flex min-h-9 cursor-not-allowed items-center justify-center rounded-lg px-3 text-xs font-bold"
                                                title="{{ $categoriesCopy['table']['locked_title'] }}"
                                            >
                                                {{ $categoriesCopy['table']['locked'] }}
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category->slug) }}" data-confirm="{{ __('ui.dialog.delete_admin_item') }}" data-confirm-title="{{ __('ui.dialog.title') }}" data-confirm-button="{{ __('ui.actions.remove') }}" data-cancel-button="{{ __('ui.dialog.cancel') }}" data-confirm-variant="danger">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex min-h-9 items-center justify-center rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 text-xs font-bold text-rose-600 transition hover:bg-rose-500 hover:text-white dark:text-rose-300">
                                                    {{ $categoriesCopy['table']['delete'] }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm admin-categories-muted">
                                    {{ $categoriesCopy['table']['empty'] }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($categories->hasPages())
            <div class="px-4">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
