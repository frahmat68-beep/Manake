@extends('layouts.admin', ['activePage' => 'equipments'])

@section('title', __('ui.admin_equipments.form.create_title'))
@section('page_title', __('ui.admin_equipments.form.create_page_title'))

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
</style>
@endpush

@section('content')
    @php
        $equipmentsCopy = __('ui.admin_equipments');
        $formCopy = $equipmentsCopy['form'];
    @endphp

    <div class="admin-equipment-form-page mx-auto max-w-5xl space-y-5 sm:space-y-6">
        <section class="admin-equipment-form-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="admin-equipment-form-kicker">{{ $formCopy['create_kicker'] }}</p>
                    <h2 class="admin-equipment-form-title mt-2 text-2xl font-black">
                        {{ $formCopy['create_heading'] }}
                    </h2>
                    <p class="admin-equipment-form-muted mt-1 text-sm">
                        {{ $formCopy['create_subtitle'] }}
                    </p>
                </div>

                <a
                    href="{{ route('admin.equipments.index') }}"
                    class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold transition"
                >
                    {{ $formCopy['back_to_list'] }}
                </a>
            </div>

            <form method="POST" action="{{ route('admin.equipments.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf

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
                            placeholder="{{ $formCopy['tool_name_placeholder'] }}"
                            required
                            class="admin-equipment-input mt-2"
                            value="{{ old('name') }}"
                        >
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['slug'] }}</label>
                        <input
                            name="slug"
                            type="text"
                            placeholder="{{ $formCopy['slug_placeholder'] }}"
                            class="admin-equipment-input mt-2"
                            value="{{ old('slug') }}"
                        >
                        <p class="admin-equipment-help">{{ $formCopy['slug_help'] }}</p>
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['category'] }}</label>
                        <select name="category_id" required class="admin-equipment-select mt-2">
                            <option value="">{{ $formCopy['select_category'] }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
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
                            placeholder="{{ $formCopy['price_placeholder'] }}"
                            required
                            class="admin-equipment-input mt-2"
                            value="{{ old('price_per_day') }}"
                        >
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['stock'] }}</label>
                        <input
                            name="stock"
                            type="number"
                            min="0"
                            max="9999"
                            placeholder="{{ $formCopy['stock_placeholder'] }}"
                            required
                            class="admin-equipment-input mt-2"
                            value="{{ old('stock', 1) }}"
                        >
                        <p class="admin-equipment-help">{{ $formCopy['stock_help_create'] }}</p>
                    </div>
                    <div>
                        <label class="admin-equipment-label">{{ $formCopy['status'] }}</label>
                        <select name="status" required class="admin-equipment-select mt-2">
                            <option value="ready" {{ old('status', 'ready') === 'ready' ? 'selected' : '' }}>
                                {{ $equipmentsCopy['status']['ready'] }}
                            </option>
                            <option value="unavailable" {{ old('status') === 'unavailable' ? 'selected' : '' }}>
                                {{ $equipmentsCopy['status']['unavailable'] }}
                            </option>
                            <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>
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
                        </div>

                        <label class="admin-secondary-button inline-flex cursor-pointer items-center justify-center rounded-xl px-4 py-2 text-xs font-semibold transition">
                            {{ $formCopy['select_file'] }}
                            <input name="image" type="file" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>

                <div class="admin-equipment-section">
                    <label class="admin-equipment-label">{{ $formCopy['specification'] }}</label>
                    <textarea
                        name="specifications"
                        rows="5"
                        placeholder="{{ $formCopy['specification_placeholder'] }}"
                        class="admin-equipment-textarea mt-2"
                    >{{ old('specifications') }}</textarea>
                </div>

                <div class="admin-equipment-section flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs admin-equipment-form-muted">
                        {{ $formCopy['create_note'] }}
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
                            {{ $formCopy['save_create'] }}
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection
