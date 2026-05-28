@extends('layouts.app')

@section('title', __('ui.settings.title'))

@php
    $resolvedTheme = $themeResolved ?? request()->attributes->get('theme_resolved', 'light');
    $isLight = $resolvedTheme === 'light';

    $localeOptions = [
        'id' => __('ui.languages.id'),
        'en' => __('ui.languages.en'),
    ];

    $themeOptions = [
        'system' => [
            'label' => __('ui.settings.theme_system'),
            'meta' => __('ui.settings.theme_system_meta'),
            'icon' => 'monitor'
        ],
        'dark' => [
            'label' => __('ui.settings.theme_dark'),
            'meta' => __('ui.settings.theme_dark_meta'),
            'icon' => 'moon'
        ],
        'light' => [
            'label' => __('ui.settings.theme_light'),
            'meta' => __('ui.settings.theme_light_meta'),
            'icon' => 'sun'
        ],
    ];
@endphp

@push('head')
    <style>
        .settings-shell-enter {
            animation: settings-shell-enter 480ms ease-out both;
        }

        .settings-option input:focus-visible + .settings-option-card {
            outline: 2px solid var(--manake-accent);
            outline-offset: 2px;
        }

        .settings-option-card {
            position: relative;
            cursor: pointer;
            border: 1px solid var(--manake-border);
            background: var(--manake-surface);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--manake-text);
        }

        .settings-option-card:hover {
            border-color: var(--manake-accent);
            background: var(--manake-accent-soft);
            transform: translateY(-1px);
        }

        .settings-option-card[data-active="true"] {
            border-color: var(--manake-accent) !important;
            background: var(--manake-accent-soft) !important;
            box-shadow: 0 0 15px var(--manake-accent-soft), inset 0 0 0 1px var(--manake-accent) !important;
        }

        .settings-option-card .manake-preferences-choice__check {
            display: none;
        }

        .settings-option-card[data-active="true"] .manake-preferences-choice__check {
            display: inline-flex;
            color: var(--manake-accent) !important;
        }

        .settings-option-card .manake-preferences-choice__icon {
            color: var(--manake-text-muted) !important;
            transition: color 0.15s ease-in-out;
        }

        .settings-option-card[data-active="true"] .manake-preferences-choice__icon {
            color: var(--manake-accent) !important;
        }

        /* Responsive light card / container styles */
        html[data-theme-resolved='light'] .settings-shell-enter > div {
            background-color: var(--manake-surface) !important;
            border-color: var(--manake-border) !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.04) !important;
        }

        html[data-theme-resolved='light'] fieldset legend {
            color: var(--manake-text) !important;
        }

        html[data-theme-resolved='light'] .h-px {
            background-color: var(--manake-border) !important;
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
        <div class="rounded-3xl border manake-border p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.9)] sm:p-8" style="background-color: var(--manake-surface);">
            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight manake-text sm:text-3xl">{{ __('ui.settings.title') }}</h1>
                <p class="text-sm leading-6 manake-text-muted sm:text-base">{{ __('ui.settings.subtitle') }}</p>
            </div>

            @if (session('success') || session('status') === 'settings-updated' || $errors->any())
                <div class="mt-5 space-y-3">
                    @if (session('success') || session('status') === 'settings-updated')
                        <div class="rounded-2xl border {{ $isLight ? 'border-emerald-500/20 bg-emerald-50 text-emerald-800' : 'border-emerald-400/20 bg-emerald-500/8 text-emerald-200' }} px-4 py-3 text-sm">
                            {{ __('ui.settings.saved') }}
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
                    <legend class="text-lg font-semibold manake-text">{{ __('ui.settings.section_language') }}</legend>
                    <p class="text-sm manake-text-muted">{{ __('ui.settings.section_language_hint') }}</p>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($localeOptions as $value => $label)
                            <label class="settings-option block">
                                <input type="radio" name="locale" value="{{ $value }}" class="sr-only" @checked($locale === $value)>
                                <span class="settings-option-card flex items-center gap-3.5 rounded-2xl px-5 py-4 text-sm font-semibold transition" data-active="{{ $locale === $value ? 'true' : 'false' }}">
                                    <span class="manake-preferences-choice__icon shrink-0 transition" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                            <path d="M2 12h20" />
                                        </svg>
                                    </span>
                                    <span class="flex-1 pr-6">{{ $label }}</span>
                                    <span class="manake-preferences-choice__check absolute right-5 top-1/2 -translate-y-1/2" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.02 7.08a1 1 0 0 1-1.42.002l-3.02-3.04a1 1 0 1 1 1.42-1.407l2.31 2.327 6.31-6.363a1 1 0 0 1 1.414-.013Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="h-px bg-[var(--manake-border)]"></div>

                <fieldset class="space-y-4">
                    <legend class="text-lg font-semibold manake-text">{{ __('ui.settings.section_theme') }}</legend>
                    <p class="text-sm manake-text-muted">{{ __('ui.settings.section_theme_hint') }}</p>

                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($themeOptions as $value => $themeOption)
                            <label class="settings-option block">
                                <input type="radio" name="theme" value="{{ $value }}" class="sr-only" @checked($theme === $value)>
                                <span class="settings-option-card flex items-start gap-3.5 rounded-2xl px-5 py-4 text-sm font-semibold transition" data-active="{{ $theme === $value ? 'true' : 'false' }}">
                                    <span class="manake-preferences-choice__icon shrink-0 mt-0.5 transition" aria-hidden="true">
                                        @if ($themeOption['icon'] === 'monitor')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="12" rx="2" />
                                                <path d="M8 20h8" />
                                                <path d="M12 16v4" />
                                            </svg>
                                        @elseif ($themeOption['icon'] === 'moon')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 12.79A9 9 0 1 1 11.21 3c0 5 3.79 8.79 8.79 8.79Z" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="4" />
                                                <path d="M12 2v2.5" />
                                                <path d="M12 19.5V22" />
                                                <path d="m4.93 4.93 1.77 1.77" />
                                                <path d="m17.3 17.3 1.77 1.77" />
                                                <path d="M2 12h2.5" />
                                                <path d="M19.5 12H22" />
                                                <path d="m4.93 19.07 1.77-1.77" />
                                                <path d="m17.3 6.7 1.77-1.77" />
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="flex-1 pr-6 flex flex-col gap-1">
                                        <span class="text-sm font-semibold manake-text">{{ $themeOption['label'] }}</span>
                                        <span class="text-xs font-normal manake-text-muted leading-relaxed">{{ $themeOption['meta'] }}</span>
                                    </span>
                                    <span class="manake-preferences-choice__check absolute right-5 top-5" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.02 7.08a1 1 0 0 1-1.42.002l-3.02-3.04a1 1 0 1 1 1.42-1.407l2.31 2.327 6.31-6.363a1 1 0 0 1 1.414-.013Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="flex flex-col gap-4 border-t border-manake-border pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm manake-text-muted leading-relaxed">{{ __('ui.settings.summary_note') }}</p>
                    <button
                        type="submit"
                        id="settings-submit-button"
                        class="manake-primary-button px-8 py-3.5 text-sm disabled:pointer-events-none disabled:opacity-50 sm:w-auto"
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
