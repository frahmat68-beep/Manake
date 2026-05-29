@extends('layouts.admin', ['activePage' => 'categories'])

@section('title', __('ui.admin_categories.form.create_title'))
@section('page_title', __('ui.admin_categories.form.create_page_title'))

@section('content')
    @php
        $categoriesCopy = __('ui.admin_categories');
        $formCopy = $categoriesCopy['form'];
    @endphp

    <div class="admin-categories-page mx-auto max-w-4xl space-y-5 sm:space-y-6">
        <section class="admin-categories-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="admin-categories-kicker">{{ $formCopy['create_kicker'] }}</p>
                    <h2 class="admin-categories-title mt-2 text-2xl font-black">{{ $formCopy['create_heading'] }}</h2>
                    <p class="admin-categories-muted mt-1 text-sm">{{ $formCopy['create_subtitle'] }}</p>
                </div>
                <a
                    href="{{ route('admin.categories.index') }}"
                    class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold transition"
                >
                    {{ $formCopy['back_to_list'] }}
                </a>
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-6 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-category-label">{{ $formCopy['name'] }}</label>
                        <input
                            name="name"
                            type="text"
                            placeholder="{{ $formCopy['name_placeholder'] }}"
                            required
                            class="admin-categories-input mt-2"
                            value="{{ old('name') }}"
                        >
                    </div>
                    <div>
                        <label class="admin-category-label">{{ $formCopy['slug'] }}</label>
                        <input
                            name="slug"
                            type="text"
                            placeholder="{{ $formCopy['slug_placeholder'] }}"
                            class="admin-categories-input mt-2"
                            value="{{ old('slug') }}"
                        >
                        <p class="admin-category-help">{{ $formCopy['slug_help'] }}</p>
                    </div>
                </div>

                <div>
                    <label class="admin-category-label">{{ $formCopy['description'] }}</label>
                    <textarea
                        name="description"
                        rows="4"
                        placeholder="{{ $formCopy['description_placeholder'] }}"
                        class="admin-categories-textarea mt-2"
                    >{{ old('description') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-xs admin-categories-muted">{{ $formCopy['create_note'] }}</p>
                    <div class="flex gap-2 w-full sm:w-auto justify-end">
                        <a
                            href="{{ route('admin.categories.index') }}"
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
