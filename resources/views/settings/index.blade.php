@extends('layouts.app')

@section('title', __('ui.settings.title'))
@section('page_title', __('ui.settings.title'))

@php
    $localeOptions = [
        'id' => __('ui.languages.id'),
        'en' => __('ui.languages.en'),
    ];

    $themeOptions = [
        'system' => __('ui.settings.theme_system'),
        'dark' => __('ui.settings.theme_dark'),
        'light' => __('ui.settings.theme_light'),
    ];
@endphp

@section('content')
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_18rem]">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
            @csrf

            <section class="card rounded-[2rem] p-6 shadow-sm sm:p-7">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-semibold tracking-[-0.03em] text-blue-700">{{ __('ui.settings.title') }}</h1>
                        <p class="text-sm text-slate-500">{{ __('ui.settings.subtitle') }}</p>
                    </div>
                    @if (session('status') === 'settings-updated')
                        <span class="status-chip status-chip-success">{{ __('ui.settings.saved') }}</span>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <section class="rounded-[1.5rem] border border-slate-200 bg-slate-50/70 p-4">
                        <div class="space-y-1">
                            <h2 class="text-sm font-semibold text-slate-900">{{ __('ui.settings.section_language') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('ui.settings.section_language_hint') }}</p>
                        </div>

                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach ($localeOptions as $value => $label)
                                <label class="block">
                                    <input
                                        type="radio"
                                        name="locale"
                                        value="{{ $value }}"
                                        class="peer sr-only"
                                        {{ $locale === $value ? 'checked' : '' }}
                                    >
                                    <span class="flex min-h-[3.35rem] items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-blue-200 hover:text-blue-600">
                                        <span>{{ $label }}</span>
                                        @if ($locale === $value)
                                            <span class="status-chip status-chip-info text-[10px]">{{ __('ui.settings.active_badge') }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('locale')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </section>

                    <section class="rounded-[1.5rem] border border-slate-200 bg-slate-50/70 p-4">
                        <div class="space-y-1">
                            <h2 class="text-sm font-semibold text-slate-900">{{ __('ui.settings.section_theme') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('ui.settings.section_theme_hint') }}</p>
                        </div>

                        <div class="mt-4 grid gap-2">
                            @foreach ($themeOptions as $value => $label)
                                <label class="block">
                                    <input
                                        type="radio"
                                        name="theme"
                                        value="{{ $value }}"
                                        class="peer sr-only"
                                        {{ $theme === $value ? 'checked' : '' }}
                                    >
                                    <span class="flex min-h-[3.35rem] items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-blue-200 hover:text-blue-600">
                                        <span>{{ $label }}</span>
                                        @if ($theme === $value)
                                            <span class="status-chip status-chip-info text-[10px]">{{ __('ui.settings.active_badge') }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('theme')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </section>
                </div>

                <div class="mt-6 flex justify-end border-t border-slate-200 pt-4">
                    <button class="btn-primary inline-flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold">
                        {{ __('ui.settings.save') }}
                    </button>
                </div>
            </section>
        </form>

        <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
            <section class="card rounded-[2rem] p-5 shadow-sm">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('ui.settings.summary_title') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('ui.settings.summary_scope') }}</p>
                </div>

                <dl class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('ui.settings.summary_language') }}</dt>
                        <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $localeOptions[$locale] ?? __('ui.languages.id') }}</dd>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('ui.settings.summary_theme') }}</dt>
                        <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $themeOptions[$theme] ?? __('ui.settings.theme_system') }}</dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('form[action="{{ route('settings.update') }}"]');
            if (!form) {
                return;
            }

            form.addEventListener('submit', () => {
                const selectedTheme = form.querySelector('input[name="theme"]:checked')?.value;
                const selectedLocale = form.querySelector('input[name="locale"]:checked')?.value;

                if (selectedTheme && window.ManakePreferences?.rememberTheme) {
                    window.ManakePreferences.rememberTheme(selectedTheme);
                }

                if (selectedLocale && window.ManakePreferences?.rememberLocale) {
                    window.ManakePreferences.rememberLocale(selectedLocale);
                }
            });
        })();
    </script>
@endpush
