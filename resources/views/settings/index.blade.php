@extends('layouts.app')

@section('title', __('ui.settings.title'))
@section('page_title', __('ui.settings.title'))

@php
    $localeOptions = [
        'id' => [
            'label' => __('ui.languages.id'),
            'hint' => __('ui.settings.language_id_hint'),
        ],
        'en' => [
            'label' => __('ui.languages.en'),
            'hint' => __('ui.settings.language_en_hint'),
        ],
    ];

    $themeOptions = [
        'system' => [
            'label' => __('ui.settings.theme_system'),
            'hint' => __('ui.settings.theme_system_meta'),
        ],
        'dark' => [
            'label' => __('ui.settings.theme_dark'),
            'hint' => __('ui.settings.theme_dark_meta'),
        ],
        'light' => [
            'label' => __('ui.settings.theme_light'),
            'hint' => __('ui.settings.theme_light_meta'),
        ],
    ];

    $activeLocaleLabel = $localeOptions[$locale]['label'] ?? __('ui.languages.id');
    $activeThemeLabel = $themeOptions[$theme]['label'] ?? __('ui.settings.theme_system');
@endphp

@push('head')
    <style>
        .settings-enter {
            animation: settings-enter 520ms ease-out both;
        }

        .settings-card-in {
            animation: settings-card-in 520ms ease-out both;
        }

        .settings-option input:focus-visible + .settings-option-card {
            outline: 2px solid rgba(212, 168, 67, 0.75);
            outline-offset: 2px;
        }

        .settings-option-card[data-active="true"] {
            border-color: rgba(212, 168, 67, 0.45);
            background: rgba(212, 168, 67, 0.08);
            box-shadow: inset 0 0 0 1px rgba(212, 168, 67, 0.14);
        }

        @keyframes settings-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes settings-card-in {
            from {
                opacity: 0;
                transform: translateY(14px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .settings-enter,
            .settings-card-in {
                animation: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <section class="bg-[#0A0A0B]">
        <div class="mx-auto max-w-7xl">
            <header class="settings-enter rounded-3xl border border-white/10 bg-[#111113]/70 p-6 shadow-[0_28px_80px_-52px_rgba(0,0,0,0.9)] sm:p-8">
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold tracking-tight text-[#E8E8EC] sm:text-3xl">{{ __('ui.settings.title') }}</h1>
                    <p class="max-w-2xl text-sm leading-6 text-[#A0A0A8] sm:text-base">{{ __('ui.settings.subtitle') }}</p>
                </div>
            </header>

            @if (session('success') || session('status') === 'settings-updated' || $errors->any())
                <div class="mt-5 space-y-3">
                    @if (session('success') || session('status') === 'settings-updated')
                        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/8 px-4 py-3 text-sm text-emerald-200">
                            {{ session('success', __('ui.settings.saved')) }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-2xl border border-rose-400/20 bg-rose-500/8 px-4 py-3 text-sm text-rose-200">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(320px,0.75fr)] lg:items-start">
                <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="resolved_theme" id="resolved_theme_input" value="{{ $resolvedTheme }}">

                    <fieldset class="settings-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8">
                        <legend class="sr-only">{{ __('ui.settings.section_language') }}</legend>
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('ui.settings.section_language') }}</h2>
                            <p class="text-sm text-[#A0A0A8]">{{ __('ui.settings.section_language_hint') }}</p>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($localeOptions as $value => $option)
                                <label class="settings-option block">
                                    <input
                                        type="radio"
                                        name="locale"
                                        value="{{ $value }}"
                                        class="sr-only"
                                        @checked($locale === $value)
                                    >
                                    <span class="settings-option-card flex min-h-[112px] flex-col justify-between rounded-2xl border border-white/10 bg-[#0A0A0B]/75 p-4 transition hover:border-[#D4A843]/35" data-active="{{ $locale === $value ? 'true' : 'false' }}">
                                        <span class="flex items-start justify-between gap-3">
                                            <span>
                                                <span class="block text-base font-semibold text-[#E8E8EC]">{{ $option['label'] }}</span>
                                                <span class="mt-2 block text-sm leading-6 text-[#A0A0A8]">{{ $option['hint'] }}</span>
                                            </span>
                                            @if ($locale === $value)
                                                <span class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-200">{{ __('ui.settings.active_badge') }}</span>
                                            @endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset class="settings-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8" style="animation-delay: 80ms">
                        <legend class="sr-only">{{ __('ui.settings.section_theme') }}</legend>
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('ui.settings.section_theme') }}</h2>
                            <p class="text-sm text-[#A0A0A8]">{{ __('ui.settings.section_theme_hint') }}</p>
                        </div>

                        <div class="mt-5 grid gap-3">
                            @foreach ($themeOptions as $value => $option)
                                <label class="settings-option block">
                                    <input
                                        type="radio"
                                        name="theme"
                                        value="{{ $value }}"
                                        class="sr-only"
                                        @checked($theme === $value)
                                    >
                                    <span class="settings-option-card flex min-h-[96px] flex-col justify-between rounded-2xl border border-white/10 bg-[#0A0A0B]/75 p-4 transition hover:border-[#D4A843]/35" data-active="{{ $theme === $value ? 'true' : 'false' }}">
                                        <span class="flex items-start justify-between gap-3">
                                            <span>
                                                <span class="block text-base font-semibold text-[#E8E8EC]">{{ $option['label'] }}</span>
                                                <span class="mt-2 block text-sm leading-6 text-[#A0A0A8]">{{ $option['hint'] }}</span>
                                            </span>
                                            @if ($theme === $value)
                                                <span class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-200">{{ __('ui.settings.active_badge') }}</span>
                                            @endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="settings-card-in flex justify-stretch sm:justify-end" style="animation-delay: 140ms">
                        <button
                            type="submit"
                            id="settings-submit-button"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-[#D4A843] px-6 py-3.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/40 sm:w-auto"
                        >
                            <span class="submit-label">{{ __('ui.settings.save') }}</span>
                        </button>
                    </div>
                </form>

                <aside class="space-y-6">
                    <section class="settings-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6" style="animation-delay: 50ms">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('ui.settings.summary_title') }}</h2>
                            <p class="text-sm text-[#A0A0A8]">{{ __('ui.settings.summary_scope') }}</p>
                        </div>

                        <dl class="mt-5 space-y-3">
                            <div class="rounded-2xl border border-white/10 bg-[#0A0A0B]/75 px-4 py-3">
                                <dt class="text-xs font-medium text-[#A0A0A8]">{{ __('ui.settings.summary_language') }}</dt>
                                <dd class="mt-2 text-sm font-semibold text-[#E8E8EC]">{{ $activeLocaleLabel }}</dd>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-[#0A0A0B]/75 px-4 py-3">
                                <dt class="text-xs font-medium text-[#A0A0A8]">{{ __('ui.settings.summary_theme') }}</dt>
                                <dd class="mt-2 text-sm font-semibold text-[#E8E8EC]">{{ $activeThemeLabel }}</dd>
                            </div>
                        </dl>

                        <p class="mt-5 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm leading-6 text-[#A0A0A8]">
                            {{ __('ui.settings.summary_note') }}
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('form[action="{{ route('settings.update') }}"]');
            if (! form) {
                return;
            }

            const submitButton = document.getElementById('settings-submit-button');
            const submitLabel = submitButton?.querySelector('.submit-label');
            const resolvedThemeInput = document.getElementById('resolved_theme_input');

            const syncOptionState = (name) => {
                form.querySelectorAll(`input[name="${name}"]`).forEach((input) => {
                    const card = input.nextElementSibling;
                    if (!(card instanceof HTMLElement)) {
                        return;
                    }

                    card.dataset.active = input.checked ? 'true' : 'false';
                    const badge = card.querySelector('[data-active-badge]');
                    if (badge instanceof HTMLElement) {
                        badge.hidden = ! input.checked;
                    }
                });
            };

            const syncResolvedTheme = () => {
                const selectedTheme = form.querySelector('input[name="theme"]:checked')?.value || 'light';
                const resolvedTheme = window.ManakeTheme?.resolveTheme
                    ? window.ManakeTheme.resolveTheme(selectedTheme)
                    : (selectedTheme === 'dark' ? 'dark' : 'light');

                if (resolvedThemeInput) {
                    resolvedThemeInput.value = resolvedTheme;
                }
            };

            form.querySelectorAll('input[name="locale"], input[name="theme"]').forEach((input) => {
                input.addEventListener('change', () => {
                    syncOptionState(input.name);

                    if (input.name === 'theme') {
                        syncResolvedTheme();
                    }
                });
            });

            form.addEventListener('submit', () => {
                const selectedTheme = form.querySelector('input[name="theme"]:checked')?.value;
                const selectedLocale = form.querySelector('input[name="locale"]:checked')?.value;

                syncResolvedTheme();

                if (selectedTheme && window.ManakePreferences?.rememberTheme) {
                    window.ManakePreferences.rememberTheme(selectedTheme);
                }

                if (selectedLocale && window.ManakePreferences?.rememberLocale) {
                    window.ManakePreferences.rememberLocale(selectedLocale);
                }

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-80', 'cursor-wait');
                }

                if (submitLabel) {
                    submitLabel.textContent = @json(__('ui.settings.saving'));
                }
            });

            syncOptionState('locale');
            syncOptionState('theme');
            syncResolvedTheme();
        })();
    </script>
@endpush
