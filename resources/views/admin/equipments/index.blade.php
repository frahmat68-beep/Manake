@extends('layouts.admin', ['activePage' => 'equipments'])

@section('title', __('ui.admin_equipments.title'))
@section('page_title', __('ui.admin_equipments.page_title'))

@push('head')
<style>
    .admin-equipments-page {
        color: var(--admin-text);
    }

    .admin-equipments-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-equipments-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-equipments-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-equipments-title {
        color: var(--admin-text);
    }

    .admin-equipments-muted {
        color: var(--admin-muted);
    }

    .admin-equipments-subtle {
        color: var(--admin-subtle);
    }

    .admin-equipments-kicker {
        color: var(--admin-accent);
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-equipments-input {
        width: 100%;
        min-height: 3.05rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0 1rem;
        outline: none;
    }

    .admin-equipments-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-equipments-filter-panel {
        background: var(--admin-surface-raised);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.15rem;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-filter-panel {
        background: #F8FAFC !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-filter-panel {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
    }

    .admin-equipment-filter-chip {
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-muted);
        border-radius: 999px;
        padding: 0.45rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 700;
        transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease;
    }

    .admin-equipment-filter-chip:hover {
        border-color: var(--admin-accent-border);
        color: var(--admin-accent);
    }

    .admin-equipment-filter-chip.is-active {
        background: var(--admin-accent) !important;
        border-color: var(--admin-accent) !important;
        color: var(--admin-accent-text) !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipment-filter-chip:not(.is-active) {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #4B5563 !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipment-filter-chip:not(.is-active):hover {
        background: #EEF2FF !important;
        color: #2563EB !important;
        border-color: rgba(37, 99, 235, 0.28) !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipment-filter-chip:not(.is-active) {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        color: #A0A0A8 !important;
    }

    .admin-equipments-table thead {
        background: var(--admin-surface-raised);
        color: var(--admin-muted);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-table thead {
        background: #F8FAFC !important;
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-table thead {
        background: #0A0A0B !important;
        color: #A0A0A8 !important;
    }

    .admin-equipments-table tbody tr {
        background: transparent !important;
        color: var(--admin-text) !important;
        border-bottom: 1px solid var(--admin-border);
        transition: background-color 160ms ease, color 160ms ease;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr {
        border-bottom-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr {
        border-bottom-color: #1A1A1E !important;
    }

    .admin-equipments-table tbody tr:last-child {
        border-bottom: 0 !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr:hover {
        background: #F8FAFC !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr:hover {
        background: #151519 !important;
        color: #E8E8EC !important;
    }

    /* Force cell elements hover state color overrides for absolute readability */
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr:hover td {
        background-color: #F8FAFC !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr:hover td {
        background-color: #151519 !important;
        color: #E8E8EC !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr:hover .admin-equipments-title {
        color: #111827 !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr:hover .admin-equipments-muted {
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr:hover .admin-equipments-title {
        color: #E8E8EC !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-table tbody tr:hover .admin-equipments-muted {
        color: #A0A0A8 !important;
    }

    .admin-equipment-image {
        background: var(--admin-surface-raised);
        border: 1px solid var(--admin-border);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipment-image {
        background: #F8FAFC !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipment-image {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
    }

    .admin-equipments-table-header {
        background: var(--admin-surface);
        border-color: var(--admin-border);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipments-table-header {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipments-table-header {
        background: #111113 !important;
        border-color: #1A1A1E !important;
    }
</style>
@endpush

@section('content')
    @php
        $search = $search ?? '';
        $status = $status ?? '';
        $activeCategorySlug = $activeCategory?->slug ?? '';
        $hasActiveFilter = $search !== '' || $status !== '' || $activeCategorySlug !== '';
        $equipmentsCopy = __('ui.admin_equipments');

        $statusFilters = [
            ['value' => '', 'label' => $equipmentsCopy['filters']['all_status']],
            ['value' => 'ready', 'label' => $equipmentsCopy['status']['ready']],
            ['value' => 'maintenance', 'label' => $equipmentsCopy['status']['maintenance']],
            ['value' => 'unavailable', 'label' => $equipmentsCopy['status']['unavailable']],
        ];
    @endphp

    <div class="admin-equipments-page mx-auto max-w-7xl space-y-5 sm:space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header & Filters Card --}}
        <section class="admin-equipments-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="admin-equipments-kicker">{{ $equipmentsCopy['kicker'] }}</p>
                    <h2 class="admin-equipments-title mt-2 text-2xl font-black">{{ $equipmentsCopy['heading'] }}</h2>
                    <p class="admin-equipments-muted mt-1 text-sm">{{ $equipmentsCopy['subtitle'] }}</p>
                </div>
                <div class="flex w-full gap-2 sm:w-auto">
                    <a
                        href="{{ route('admin.equipments.create') }}"
                        class="admin-accent-bg inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold transition sm:w-auto"
                    >
                        {{ $equipmentsCopy['add_tool'] }}
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.equipments.index') }}" class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="category" value="{{ $activeCategorySlug }}">
                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:max-w-xl">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="{{ $equipmentsCopy['filters']['search_placeholder'] }}"
                        class="admin-equipments-input text-sm"
                    >
                    <button class="admin-accent-bg inline-flex min-h-[3.05rem] items-center justify-center rounded-xl px-5 text-sm font-bold transition">{{ $equipmentsCopy['filters']['search'] }}</button>
                    @if ($hasActiveFilter)
                        <a
                            href="{{ route('admin.equipments.index') }}"
                            class="admin-secondary-button inline-flex min-h-[3.05rem] items-center justify-center rounded-xl px-4 text-sm font-semibold transition"
                        >
                            {{ $equipmentsCopy['filters']['reset'] }}
                        </a>
                    @endif
                </div>
                <div class="text-xs font-medium admin-equipments-muted">
                    {{ $equipmentsCopy['filters']['showing'] }} <span class="font-semibold admin-equipments-title">{{ $equipments->total() }}</span> {{ $equipments->total() === 1 ? $equipmentsCopy['filters']['tool'] : $equipmentsCopy['filters']['tools'] }}
                </div>
            </form>

            <div class="admin-equipments-filter-panel mt-5 space-y-4 p-4">
                <div class="flex flex-wrap items-center gap-4">
                    <p class="admin-equipments-subtle text-[10px] font-bold uppercase tracking-[0.2em]">{{ $equipmentsCopy['filters']['status'] }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($statusFilters as $filter)
                            @php
                                $isActiveStatus = $status === $filter['value'];
                            @endphp
                            <a
                                href="{{ route('admin.equipments.index', array_filter([
                                    'q' => $search,
                                    'status' => $filter['value'],
                                    'category' => $activeCategorySlug,
                                ], fn ($value) => $value !== '')) }}"
                                class="admin-equipment-filter-chip {{ $isActiveStatus ? 'is-active' : '' }}"
                            >
                                {{ $filter['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 border-t admin-border pt-3">
                    <p class="admin-equipments-subtle text-[10px] font-bold uppercase tracking-[0.2em]">{{ $equipmentsCopy['filters']['categories'] }}</p>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('admin.equipments.index', array_filter([
                                'q' => $search,
                                'status' => $status,
                             ], fn ($value) => $value !== '')) }}"
                            class="admin-equipment-filter-chip {{ $activeCategorySlug === '' ? 'is-active' : '' }}"
                        >
                            {{ $equipmentsCopy['filters']['all_categories'] }}
                        </a>
                        @foreach ($categories as $category)
                            <a
                                href="{{ route('admin.equipments.index', array_filter([
                                    'q' => $search,
                                    'status' => $status,
                                    'category' => $category->slug,
                                ], fn ($value) => $value !== '')) }}"
                                class="admin-equipment-filter-chip {{ $activeCategorySlug === $category->slug ? 'is-active' : '' }}"
                            >
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Table Card --}}
        <section class="admin-equipments-card overflow-hidden p-0">
            <div class="admin-equipments-table-header flex flex-col gap-1 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="admin-equipments-title text-lg font-black">
                        {{ $equipmentsCopy['table']['title'] ?? $equipmentsCopy['heading'] }}
                    </h3>
                    <p class="admin-equipments-muted text-sm">
                        {{ $equipmentsCopy['table']['subtitle'] ?? $equipmentsCopy['subtitle'] }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-equipments-table w-full min-w-[1280px] table-fixed text-sm">
                    <colgroup>
                        <col class="w-[300px]">
                        <col class="w-[160px]">
                        <col class="w-[140px]">
                        <col class="w-[96px]">
                        <col class="w-[96px]">
                        <col class="w-[96px]">
                        <col class="w-[120px]">
                        <col class="w-[135px]">
                        <col class="w-[230px]">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['tool'] }}</th>
                            <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['slug'] }}</th>
                            <th class="px-5 py-4 text-right text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['price_per_day'] }}</th>
                            <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['total_stock'] }}</th>
                            <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['reserved'] }}</th>
                            <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['available'] }}</th>
                            <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['status'] }}</th>
                            <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['updated'] }}</th>
                            <th class="px-5 py-4 text-right text-[11px] font-black uppercase tracking-[0.16em]">{{ $equipmentsCopy['table']['action'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($equipments as $item)
                            @php
                                $statusValue = $item->status ?? 'ready';
                                $statusLabel = $equipmentsCopy['status'][$statusValue] ?? $statusValue;
                                $statusClass = $statusValue === 'ready'
                                    ? 'status-chip-success'
                                    : ($statusValue === 'maintenance' ? 'status-chip-warning' : 'status-chip-danger');
                                $reservedUnits = (int) ($item->reserved_units ?? 0);
                                $availableUnits = (int) $item->available_units;
                                $imageUrl = site_media_url($item->image_path ?? $item->image ?? null) ?: config('placeholders.equipment');
                            @endphp
                            <tr>
                                <td class="px-5 py-3 align-middle">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $item->name }}"
                                            class="admin-equipment-image h-12 w-12 shrink-0 rounded-xl object-contain p-1"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';"
                                        >
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold leading-5 admin-equipments-title" title="{{ $item->name }}">
                                                {{ $item->name }}
                                            </p>
                                            <p class="mt-0.5 truncate text-xs admin-equipments-muted" title="{{ $item->category?->name ?? '-' }}">
                                                {{ $item->category?->name ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 align-middle admin-equipments-muted">
                                    <p class="truncate text-sm" title="{{ $item->slug }}">
                                        {{ $item->slug }}
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-right align-middle font-bold whitespace-nowrap admin-equipments-title">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center align-middle font-bold admin-equipments-title">{{ $item->stock }}</td>
                                <td class="px-5 py-3 text-center align-middle font-bold text-amber-600">{{ $reservedUnits }}</td>
                                <td class="px-5 py-3 text-center align-middle">
                                    <span class="font-bold {{ $availableUnits > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $availableUnits }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center align-middle">
                                    <span class="status-chip {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center align-middle admin-equipments-muted whitespace-nowrap">{{ $item->updated_at?->format('d M Y') }}</td>
                                <td class="px-5 py-3 align-middle">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <a
                                            href="{{ route('product.show', $item->slug) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="admin-secondary-button inline-flex min-h-9 items-center justify-center rounded-lg px-3 text-xs font-bold transition"
                                        >
                                            {{ $equipmentsCopy['table']['view'] }}
                                        </a>
                                        <a
                                            href="{{ route('admin.equipments.edit', $item->slug) }}"
                                            class="admin-secondary-button inline-flex min-h-9 items-center justify-center rounded-lg px-3 text-xs font-bold transition"
                                        >
                                            {{ $equipmentsCopy['table']['edit'] }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.equipments.destroy', $item->slug) }}" data-confirm="{{ __('ui.dialog.delete_admin_item') }}" data-confirm-title="{{ __('ui.dialog.title') }}" data-confirm-button="{{ __('ui.actions.remove') }}" data-cancel-button="{{ __('ui.dialog.cancel') }}" data-confirm-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex min-h-9 items-center justify-center rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 text-xs font-bold text-rose-600 transition hover:bg-rose-500 hover:text-white dark:text-rose-300">
                                                {{ $equipmentsCopy['table']['delete'] }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-sm admin-equipments-muted">
                                    {{ $equipmentsCopy['table']['empty'] }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($equipments->hasPages())
            <div class="px-4">
                {{ $equipments->links() }}
            </div>
        @endif
    </div>
@endsection
