@extends('layouts.app')

@section('title', __('ui.settings.title'))

@php
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
            outline: 2px solid rgba(212, 168, 67, 0.75);
            outline-offset: 2px;
        }

        .settings-option-card {
            position: relative;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(10, 10, 11, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .settings-option-card:hover {
            border-color: rgba(212, 168, 67, 0.35);
            background: rgba(212, 168, 67, 0.03);
            transform: translateY(-1px);
        }

        .settings-option-card[data-active="true"] {
            border-color: #D4A843;
            background: rgba(212, 168, 67, 0.08);
            box-shadow: 0 0 15px rgba(212, 168, 67, 0.12), inset 0 0 0 1px rgba(212, 168, 67, 0.15);
        }

        .settings-option-card .manake-preferences-choice__check {
            display: none;
        }

        .settings-option-card[data-active="true"] .manake-preferences-choice__check {
            display: inline-flex;
        }

        .settings-option-card[data-active="true"] .manake-preferences-choice__icon {
            color: #D4A843;
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
                                <span class="settings-option-card flex items-center gap-3.5 rounded-2xl px-5 py-4 text-sm font-semibold text-[#E8E8EC] transition" data-active="{{ $locale === $value ? 'true' : 'false' }}">
                                    <span class="manake-preferences-choice__icon shrink-0 text-[#A0A0A8] transition" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                            <path d="M2 12h20" />
                                        </svg>
                                    </span>
                                    <span class="flex-1 pr-6">{{ $label }}</span>
                                    <span class="manake-preferences-choice__check absolute right-5 top-1/2 -translate-y-1/2 text-[#D4A843]" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.02 7.08a1 1 0 0 1-1.42.002l-3.02-3.04a1 1 0 1 1 1.42-1.407l2.31 2.327 6.31-6.363a1 1 0 0 1 1.414-.013Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
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
                        @foreach ($themeOptions as $value => $themeOption)
                            <label class="settings-option block">
                                <input type="radio" name="theme" value="{{ $value }}" class="sr-only" @checked($theme === $value)>
                                <span class="settings-option-card flex items-start gap-3.5 rounded-2xl px-5 py-4 text-sm font-semibold text-[#E8E8EC] transition" data-active="{{ $theme === $value ? 'true' : 'false' }}">
                                    <span class="manake-preferences-choice__icon shrink-0 mt-0.5 text-[#A0A0A8] transition" aria-hidden="true">
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
                                        <span class="text-sm font-semibold text-[#E8E8EC]">{{ $themeOption['label'] }}</span>
                                        <span class="text-xs font-normal text-[#A0A0A8] leading-relaxed">{{ $themeOption['meta'] }}</span>
                                    </span>
                                    <span class="manake-preferences-choice__check absolute right-5 top-5 text-[#D4A843]" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.02 7.08a1 1 0 0 1-1.42.002l-3.02-3.04a1 1 0 1 1 1.42-1.407l2.31 2.327 6.31-6.363a1 1 0 0 1 1.414-.013Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="flex flex-col gap-4 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-[#A0A0A8] leading-relaxed">{{ __('ui.settings.summary_note') }}</p>
                    <button
                        type="submit"
                        id="settings-submit-button"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-[#D4A843] to-[#B8871F] px-8 py-3.5 text-sm font-semibold text-[#0A0A0B] shadow-[0_4px_20px_rgba(212,168,67,0.25)] transition duration-300 hover:-translate-y-0.5 hover:brightness-110 active:translate-y-0 active:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#D4A843]/40 disabled:pointer-events-none disabled:opacity-50 sm:w-auto"
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
