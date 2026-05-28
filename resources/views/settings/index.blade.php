@extends('layouts.app')

@section('title', __('ui.settings.title'))

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

@push('head')
    <style>
        .settings-shell-enter {
            animation: settings-shell-enter 480ms ease-out both;
        }

        .settings-option input:focus-visible + .settings-option-card {
            outline: 2px solid rgba(212, 168, 67, 0.75);
            outline-offset: 2px;
        }

        .settings-option-card[data-active="true"] {
            border-color: rgba(212, 168, 67, 0.42);
            background: rgba(212, 168, 67, 0.08);
            box-shadow: inset 0 0 0 1px rgba(212, 168, 67, 0.16);
        }

        @keyframes settings-shell-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .settings-shell-enter {
                animation: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <section class="mx-auto max-w-4xl settings-shell-enter">
        <div class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.9)] sm:p-8">
            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight text-[#E8E8EC] sm:text-3xl">{{ __('ui.settings.title') }}</h1>
                <p class="text-sm leading-6 text-[#A0A0A8] sm:text-base">{{ __('ui.settings.subtitle') }}</p>
            </div>

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

            <form method="POST" action="{{ route('settings.update') }}" class="mt-6 space-y-6">
                @csrf
                <input type="hidden" name="resolved_theme" id="resolved_theme_input" value="{{ $resolvedTheme }}">

                <fieldset class="space-y-4">
                    <legend class="text-lg font-semibold text-[#E8E8EC]">{{ __('ui.settings.section_language') }}</legend>
                    <p class="text-sm text-[#A0A0A8]">{{ __('ui.settings.section_language_hint') }}</p>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($localeOptions as $value => $label)
                            <label class="settings-option block">
                                <input type="radio" name="locale" value="{{ $value }}" class="sr-only" @checked($locale === $value)>
                                <span class="settings-option-card flex items-center justify-between rounded-2xl border border-white/10 bg-[#0A0A0B]/75 px-4 py-3 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35" data-active="{{ $locale === $value ? 'true' : 'false' }}">
                                    <span>{{ $label }}</span>
                                    @if ($locale === $value)
                                        <span class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-200">{{ __('ui.settings.active_badge') }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="h-px bg-white/10"></div>

                <fieldset class="space-y-4">
                    <legend class="text-lg font-semibold text-[#E8E8EC]">{{ __('ui.settings.section_theme') }}</legend>
                    <p class="text-sm text-[#A0A0A8]">{{ __('ui.settings.section_theme_hint') }}</p>

                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($themeOptions as $value => $label)
                            <label class="settings-option block">
                                <input type="radio" name="theme" value="{{ $value }}" class="sr-only" @checked($theme === $value)>
                                <span class="settings-option-card flex items-center justify-between rounded-2xl border border-white/10 bg-[#0A0A0B]/75 px-4 py-3 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35" data-active="{{ $theme === $value ? 'true' : 'false' }}">
                                    <span>{{ $label }}</span>
                                    @if ($theme === $value)
                                        <span class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-200">{{ __('ui.settings.active_badge') }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="flex flex-col gap-3 border-t border-white/10 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-[#A0A0A8]">{{ __('ui.settings.summary_note') }}</p>
                    <button
                        type="submit"
                        id="settings-submit-button"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-[#D4A843] px-6 py-3.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/40 sm:w-auto"
                    >
                        <span class="submit-label">{{ __('ui.settings.save') }}</span>
                    </button>
                </div>
            </form>
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
