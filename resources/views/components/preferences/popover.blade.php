@props([
    'locale' => 'id',
    'currentTheme' => 'light',
    'redirect' => null,
])

@php
    $redirect = $redirect ?: url()->full();
@endphp

<div {{ $attributes->class(['manake-preferences-popover rounded-[1.35rem] border p-3.5 shadow-2xl']) }}>
    <div class="space-y-3.5">
        <div class="space-y-1">
            <p class="manake-preferences-popover__title">{{ __('ui.nav.settings') }}</p>
            <p class="manake-preferences-popover__hint">{{ __('ui.settings.quick_hint') }}</p>
        </div>

        <div class="h-px bg-slate-200/90"></div>

        <section class="space-y-2">
            <div>
                <p class="manake-preferences-popover__label">{{ __('ui.nav.language') }}</p>
                <p class="manake-preferences-popover__hint">{{ __('ui.settings.section_language_hint') }}</p>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <a
                    href="{{ route('lang.switch', ['locale' => 'id', 'redirect' => $redirect]) }}"
                    data-locale-option="id"
                    data-ui-chip-option
                    data-ui-active="{{ $locale === 'id' ? 'true' : 'false' }}"
                    class="rounded-xl border px-3 py-2 text-center text-[13px] font-semibold {{ $locale === 'id' ? '!text-slate-900' : '!text-slate-700' }}"
                >
                    {{ __('ui.languages.id') }}
                </a>
                <a
                    href="{{ route('lang.switch', ['locale' => 'en', 'redirect' => $redirect]) }}"
                    data-locale-option="en"
                    data-ui-chip-option
                    data-ui-active="{{ $locale === 'en' ? 'true' : 'false' }}"
                    class="rounded-xl border px-3 py-2 text-center text-[13px] font-semibold {{ $locale === 'en' ? '!text-slate-900' : '!text-slate-700' }}"
                >
                    {{ __('ui.languages.en') }}
                </a>
            </div>
        </section>

        <div class="h-px bg-slate-200/90"></div>

        <section class="space-y-2">
            <div>
                <p class="manake-preferences-popover__label">{{ __('ui.nav.theme') }}</p>
                <p class="manake-preferences-popover__hint">{{ __('ui.settings.section_theme_hint') }}</p>
            </div>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                @foreach (['system' => __('ui.settings.theme_system'), 'dark' => __('ui.settings.theme_dark'), 'light' => __('ui.settings.theme_light')] as $value => $label)
                    <a
                        href="{{ route('theme.switch', ['theme' => $value, 'redirect' => $redirect]) }}"
                        data-theme-option="{{ $value }}"
                        data-ui-chip-option
                        data-ui-active="{{ $currentTheme === $value ? 'true' : 'false' }}"
                        class="rounded-xl border px-2.5 py-2 text-center text-[13px] font-semibold {{ $currentTheme === $value ? '!text-slate-900' : '!text-slate-700' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</div>
