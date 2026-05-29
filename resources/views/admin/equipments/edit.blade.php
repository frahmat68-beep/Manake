@extends('layouts.admin', ['activePage' => 'equipments'])

@section('title', __('ui.admin_equipments.form.edit_title'))
@section('page_title', __('ui.admin_equipments.form.edit_page_title'))

@push('head')
<style>
    .admin-equipment-form-page {
        color: var(--admin-text);
    }

    .admin-equipment-form-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-equipment-form-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-equipment-form-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-equipment-form-title {
        color: var(--admin-text);
    }

    .admin-equipment-form-muted {
        color: var(--admin-muted);
    }

    .admin-equipment-form-subtle {
        color: var(--admin-subtle);
    }

    .admin-equipment-form-kicker {
        color: var(--admin-accent);
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-equipment-label {
        display: block;
        color: var(--admin-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .admin-equipment-input,
    .admin-equipment-select,
    .admin-equipment-textarea {
        width: 100%;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0.85rem 1rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .admin-equipment-input,
    .admin-equipment-select {
        min-height: 3.05rem;
    }

    .admin-equipment-textarea {
        min-height: 8.5rem;
        resize: vertical;
    }

    .admin-equipment-input:focus,
    .admin-equipment-select:focus,
    .admin-equipment-textarea:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    .admin-equipment-input::placeholder,
    .admin-equipment-textarea::placeholder {
        color: var(--admin-subtle);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipment-input,
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipment-select,
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipment-textarea {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipment-input,
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipment-select,
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipment-textarea {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-equipment-help {
        margin-top: 0.45rem;
        color: var(--admin-subtle);
        font-size: 0.75rem;
        line-height: 1.45;
    }

    .admin-equipment-upload {
        border: 1px dashed var(--admin-border);
        background: var(--admin-surface-raised);
        color: var(--admin-text);
        border-radius: 1rem;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipment-upload {
        background: #F8FAFC !important;
        border-color: #CBD5E1 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipment-upload {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
    }

    .admin-equipment-section {
        border-top: 1px solid var(--admin-border);
        padding-top: 1.25rem;
    }

    .admin-equipment-stat-card {
        border: 1px solid var(--admin-border);
        background: var(--admin-surface-raised);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-equipment-stat-card {
        border-color: #E5E7EB !important;
        background: #F8FAFC !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-equipment-stat-card {
        border-color: #1A1A1E !important;
        background: #0A0A0B !important;
    }
</style>
@endpush

@section('content')
    @php
        $equipmentsCopy = __('ui.admin_equipments');
        $formCopy = $equipmentsCopy['form'];
        $isEnglish = App::getLocale() === 'en';
        $daysOfWeek = $isEnglish
            ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            : ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    @endphp

    <div class="admin-equipment-form-page mx-auto max-w-5xl space-y-5 sm:space-y-6">
        <section class="admin-equipment-form-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="admin-equipment-form-kicker">{{ $formCopy['edit_kicker'] }}</p>
                    <h2 class="admin-equipment-form-title mt-2 text-2xl font-black">
                        {{ $formCopy['edit_heading'] }}
                    </h2>
                    <p class="admin-equipment-form-muted mt-1 text-sm">
                        {{ $formCopy['edit_subtitle'] }}
                    </p>
                </div>

                <a
                    href="{{ route('admin.equipments.index') }}"
                    class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold transition"
                >
                    {{ $formCopy['back_to_list'] }}
                </a>
            </div>

            {{-- Stat Cards --}}
            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="admin-equipment-stat-card rounded-xl px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider admin-equipment-form-muted">{{ $equipmentsCopy['table']['total_stock'] }}</p>
                    <p class="mt-1 text-xl font-bold admin-equipment-form-title">{{ $equipment->stock }}</p>
                </div>
                <div class="admin-equipment-stat-card rounded-xl px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider admin-equipment-form-muted">{{ $equipmentsCopy['table']['reserved'] }}</p>
                    <p class="mt-1 text-xl font-bold text-amber-600">{{ $equipment->reserved_units }}</p>
                </div>
                <div class="admin-equipment-stat-card rounded-xl px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider admin-equipment-form-muted">{{ $equipmentsCopy['table']['available'] }}</p>
                    <p class="mt-1 text-xl font-bold text-emerald-600">{{ $equipment->available_units }}</p>
                </div>
            </div>

            {{-- Calendar Section --}}
            <div class="admin-equipment-stat-card mt-5 rounded-2xl p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold admin-equipment-form-title">
                            {{ $isEnglish ? 'Tool Booking Calendar' : 'Kalender Pemakaian Alat' }}
                        </h3>
                        <p class="text-xs admin-equipment-form-muted">
                            {{ $isEnglish 
                                ? 'Tool status remains ready, but booking date conflicts will be rejected at checkout.' 
                                : 'Status alat tetap ready, tapi bentrok tanggal sewa akan ditolak saat checkout.' 
                            }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('admin.equipments.edit', ['slug' => $equipment->slug, 'month' => $bookingCalendar['previous_month']]) }}"
                            class="admin-secondary-button inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs font-semibold transition"
                        >
                            ←
                        </a>
                        <span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300">
                            {{ $bookingCalendar['month_label'] }}
                        </span>
                        <a
                            href="{{ route('admin.equipments.edit', ['slug' => $equipment->slug, 'month' => $bookingCalendar['next_month']]) }}"
                            class="admin-secondary-button inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs font-semibold transition"
                        >
                            →
                        </a>
                    </div>
                </div>

                <div class="mt-3 -mx-1 overflow-x-auto px-1 pb-1 sm:mx-0 sm:overflow-visible sm:px-0">
                    <div class="min-w-[640px]">
                        <div class="grid grid-cols-7 gap-2 text-center text-[10px] font-bold uppercase tracking-wide admin-equipment-form-muted">
                            @foreach ($daysOfWeek as $dayName)
                                <span>{{ $dayName }}</span>
                            @endforeach
                        </div>
                        <div class="mt-2 grid grid-cols-7 gap-2">
                            @foreach ($bookingCalendar['days'] as $day)
                                @php
                                    $inMonthClass = $day['in_month']
                                        ? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950'
                                        : 'border-slate-100 bg-slate-100/50 text-slate-400 dark:border-slate-800/40 dark:bg-slate-900/40 dark:text-slate-500';
                                @endphp
                                <div class="min-h-[68px] rounded-lg border px-2 py-2 {{ $inMonthClass }}">
                                    <p class="text-xs font-bold">{{ $day['day'] }}</p>
                                    @if ($day['booked_qty'] > 0)
                                        <p class="mt-1 rounded bg-amber-500/10 px-1.5 py-0.5 text-[9px] font-bold text-amber-600 dark:text-amber-400">
                                            {{ $isEnglish ? 'Booked' : 'Disewa' }} {{ $day['booked_qty'] }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-[10px] admin-equipment-form-subtle">-</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    @forelse ($bookingCalendar['events'] as $event)
                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                            <p class="font-bold text-slate-800 dark:text-slate-100">{{ $event['order_number'] }} • {{ $event['customer'] }}</p>
                            <p class="mt-0.5">{{ \Carbon\Carbon::parse($event['start_date'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($event['end_date'])->translatedFormat('d M Y') }} • Qty {{ $event['qty'] }}</p>
                        </div>
                    @empty
                        <p class="text-xs admin-equipment-form-muted">
                            {{ $isEnglish ? 'No rental schedule for this month yet.' : 'Belum ada jadwal sewa pada bulan ini.' }}
                        </p>
                    @endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('admin.equipments.update', $equipment->slug) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['tool_name'] }}</label>
                        <input
                            name="name"
                            type="text"
                            value="{{ old('name', $equipment->name) }}"
                            required
                            class="admin-equipment-input mt-2"
                        >
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['slug'] }}</label>
                        <input
                            name="slug"
                            type="text"
                            value="{{ old('slug', $equipment->slug) }}"
                            required
                            class="admin-equipment-input mt-2"
                        >
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['category'] }}</label>
                        <select name="category_id" required class="admin-equipment-select mt-2">
                            <option value="">{{ $formCopy['select_category'] }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id', $equipment->category_id) === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['price_per_day'] }}</label>
                        <input
                            name="price_per_day"
                            type="number"
                            value="{{ old('price_per_day', $equipment->price_per_day) }}"
                            required
                            class="admin-equipment-input mt-2"
                        >
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['stock'] }}</label>
                        <input
                            name="stock"
                            type="number"
                            min="0"
                            max="9999"
                            value="{{ old('stock', $equipment->stock) }}"
                            required
                            class="admin-equipment-input mt-2"
                        >
                        <p class="admin-equipment-help">{{ $formCopy['stock_help_edit'] }}</p>
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['status'] }}</label>
                        <select name="status" required class="admin-equipment-select mt-2">
                            <option value="ready" {{ old('status', $equipment->status) === 'ready' ? 'selected' : '' }}>
                                {{ $equipmentsCopy['status']['ready'] }}
                            </option>
                            <option value="unavailable" {{ old('status', $equipment->status) === 'unavailable' ? 'selected' : '' }}>
                                {{ $equipmentsCopy['status']['unavailable'] }}
                            </option>
                            <option value="maintenance" {{ old('status', $equipment->status) === 'maintenance' ? 'selected' : '' }}>
                                {{ $equipmentsCopy['status']['maintenance'] }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="admin-equipment-section">
                    <label class="admin-equipment-label">{{ $formCopy['upload_photo'] }}</label>

                    <div class="admin-equipment-upload mt-2 flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-bold admin-equipment-form-title">
                                {{ $formCopy['drag_drop'] }}
                            </p>
                            <p class="mt-1 text-xs admin-equipment-form-muted">
                                {{ $formCopy['file_help'] }}
                            </p>
                            @php
                                $imagePath = $equipment->image_path ?? $equipment->image;
                                $imageUrl = $imagePath ? site_media_url($imagePath) : null;
                            @endphp
                            @if ($imageUrl)
                                <div class="mt-3 flex flex-col gap-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider admin-equipment-form-muted">{{ $formCopy['current_photo'] }}</span>
                                    <img src="{{ $imageUrl }}" alt="{{ $equipment->name }}" class="h-24 w-24 rounded-xl object-contain border border-slate-200 bg-white p-1">
                                </div>
                            @endif
                        </div>

                        <label class="admin-secondary-button inline-flex cursor-pointer items-center justify-center rounded-xl px-4 py-2 text-xs font-semibold transition">
                            {{ $formCopy['replace_file'] }}
                            <input name="image" type="file" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>

                <div class="admin-equipment-section">
                    <label class="admin-equipment-label">{{ $formCopy['specification'] }}</label>
                    <textarea
                        name="specifications"
                        rows="5"
                        class="admin-equipment-textarea mt-2"
                    >{{ old('specifications', $equipment->specifications ?? $equipment->description) }}</textarea>
                </div>

                <div class="admin-equipment-section flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs admin-equipment-form-muted">
                        {{ $formCopy['edit_note'] }}
                    </p>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a
                            href="{{ route('admin.equipments.index') }}"
                            class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold transition"
                        >
                            {{ $formCopy['cancel'] }}
                        </a>

                        <button
                            type="submit"
                            class="admin-accent-bg inline-flex min-h-10 items-center justify-center rounded-xl px-5 text-sm font-bold transition"
                        >
                            {{ $formCopy['save_edit'] }}
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection
