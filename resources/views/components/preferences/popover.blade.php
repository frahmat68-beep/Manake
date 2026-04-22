@props([
    'locale' => 'id',
    'currentTheme' => 'light',
    'redirect' => null,
])

@php
    $redirect = $redirect ?: url()->full();
@endphp

<div {{ $attributes->class(['manake-preferences-popover rounded-[1.45rem] border p-3 shadow-2xl']) }}>
    <div class="space-y-4">
        <div class="space-y-1">
            <p class="manake-preferences-popover__title">{{ __('ui.nav.settings') }}</p>
        </div>

        <section class="space-y-2.5">
            <p class="manake-preferences-popover__label">{{ __('ui.nav.language') }}</p>
            <div class="manake-preferences-grid manake-preferences-grid--two">
                @foreach (['id' => __('ui.languages.id'), 'en' => __('ui.languages.en')] as $value => $label)
                    <form method="POST" action="{{ route('lang.switch', $value) }}">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $redirect }}">
                        <button
                            type="submit"
                            data-locale-option="{{ $value }}"
                            data-ui-active="{{ $locale === $value ? 'true' : 'false' }}"
                            class="manake-preferences-choice manake-preferences-choice--locale manake-preferences-choice--row w-full rounded-2xl border {{ $locale === $value ? 'is-active' : '' }}"
                        >
                            <span class="manake-preferences-choice__dot" aria-hidden="true"></span>
                            <span class="manake-preferences-choice__body">
                                <span class="manake-preferences-choice__title">{{ $label }}</span>
                            </span>
                            <span class="manake-preferences-choice__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[0.85rem] w-[0.85rem]" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.02 7.08a1 1 0 0 1-1.42.002l-3.02-3.04a1 1 0 1 1 1.42-1.407l2.31 2.327 6.31-6.363a1 1 0 0 1 1.414-.013Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
        </section>

        <div class="manake-preferences-divider h-px"></div>

        <section class="space-y-2.5">
            <p class="manake-preferences-popover__label">{{ __('ui.nav.theme') }}</p>
            <div class="manake-preferences-grid manake-preferences-grid--stacked">
                @foreach ([
                    'system' => ['label' => __('ui.settings.theme_system'), 'meta' => __('ui.settings.theme_system_meta'), 'icon' => 'monitor'],
                    'dark' => ['label' => __('ui.settings.theme_dark'), 'meta' => __('ui.settings.theme_dark_meta'), 'icon' => 'moon'],
                    'light' => ['label' => __('ui.settings.theme_light'), 'meta' => __('ui.settings.theme_light_meta'), 'icon' => 'sun'],
                ] as $value => $themeOption)
                    <form method="POST" action="{{ route('theme.switch', $value) }}">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $redirect }}">
                        @if ($value === 'system')
                            <input type="hidden" name="resolved" value="{{ $currentTheme === 'dark' ? 'dark' : 'light' }}">
                        @endif
                        <button
                            type="submit"
                            data-theme-option="{{ $value }}"
                            data-ui-active="{{ $currentTheme === $value ? 'true' : 'false' }}"
                            class="manake-preferences-choice manake-preferences-choice--theme manake-preferences-choice--row w-full rounded-2xl border {{ $currentTheme === $value ? 'is-active' : '' }}"
                        >
                            <span class="manake-preferences-choice__icon" aria-hidden="true">
                                @if ($themeOption['icon'] === 'monitor')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="12" rx="2" />
                                        <path d="M8 20h8" />
                                        <path d="M12 16v4" />
                                    </svg>
                                @elseif ($themeOption['icon'] === 'moon')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12.79A9 9 0 1 1 11.21 3c0 5 3.79 8.79 8.79 8.79Z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
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
                            <span class="manake-preferences-choice__body">
                                <span class="manake-preferences-choice__title">{{ $themeOption['label'] }}</span>
                                <span class="manake-preferences-choice__meta">{{ $themeOption['meta'] }}</span>
                            </span>
                            <span class="manake-preferences-choice__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[0.85rem] w-[0.85rem]" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.02 7.08a1 1 0 0 1-1.42.002l-3.02-3.04a1 1 0 1 1 1.42-1.407l2.31 2.327 6.31-6.363a1 1 0 0 1 1.414-.013Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
</div>
