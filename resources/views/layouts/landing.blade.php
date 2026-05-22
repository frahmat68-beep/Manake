@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get('theme_resolved', $initialThemePreference === 'dark' ? 'dark' : 'light');
    $brandName = site_setting('brand.name', 'Manake');
    $isHomepage = request()->routeIs('home');
    $homeUrl = route('home');
    $navClass = $isHomepage
        ? 'text-sm font-medium text-white/65 transition hover:text-white'
        : 'text-sm font-medium text-slate-600 transition hover:text-slate-950';
    $headerClass = $isHomepage
        ? 'fixed inset-x-0 top-0 z-50 bg-transparent transition-all duration-300'
        : 'sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90';
    $containerClass = $isHomepage
        ? 'mx-auto flex h-16 max-w-7xl items-center justify-between px-6 md:px-10'
        : 'ui-container flex items-center justify-between gap-4 py-4';
    $menuHref = static fn (string $anchor): string => $isHomepage ? '#'.ltrim($anchor, '#') : $homeUrl.'#'.ltrim($anchor, '#');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth {{ $initialThemeResolved === 'dark' ? 'dark' : '' }}" data-theme="manake-brand" data-theme-preference="{{ $initialThemePreference }}" data-theme-resolved="{{ $initialThemeResolved }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', site_setting('seo.meta_description', setting('meta_description', 'Manake Rental menyediakan rental alat produksi profesional: kamera, lighting, drone, dan audio.')))">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', site_setting('seo.meta_title', setting('meta_title', setting('site_name', 'Manake.Id'))))</title>
    <link rel="icon" type="image/png" href="{{ site_asset('MANAKE-FAV-M.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @include('partials.runtime-ui-assets')
    @stack('head')
    <style>[x-cloak]{display:none!important;}</style>
</head>
<body class="ui-shell landing-shell antialiased selection:bg-amber-500/20 selection:text-amber-200">
    <header
        x-data="{ open: false, scrolled: false, init() { this.scrolled = window.scrollY > 24; window.addEventListener('scroll', () => { this.scrolled = window.scrollY > 24 }, { passive: true }) } }"
        class="{{ $headerClass }}"
        :class="{'bg-slate-950/90 backdrop-blur-xl border-b border-white/10': scrolled && {{ $isHomepage ? 'true' : 'false' }}, 'bg-transparent': !scrolled && {{ $isHomepage ? 'true' : 'false' }}}"
    >
        <div class="{{ $containerClass }}">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0" aria-label="{{ $brandName }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-500 text-slate-950 shadow-sm">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5" aria-hidden="true">
                        <path d="M4.5 7.5h5l1.5-2h2l1.5 2h5a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-16a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3.4" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </span>
                <span class="text-sm font-semibold tracking-[0.38em] text-white">{{ strtoupper($brandName) }}</span>
            </a>

            <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex" aria-label="{{ __('Navigasi utama') }}">
                <a href="{{ $menuHref('#equipment') }}" class="{{ $navClass }}">{{ __('Peralatan') }}</a>
                <a href="{{ $menuHref('#categories') }}" class="{{ $navClass }}">{{ __('Kategori') }}</a>
                <a href="{{ $menuHref('#about') }}" class="{{ $navClass }}">{{ __('Tentang Kami') }}</a>
                <a href="{{ $menuHref('#cara-sewa') }}" class="{{ $navClass }}">{{ __('Cara Sewa') }}</a>
                <a href="{{ $menuHref('#contact') }}" class="{{ $navClass }}">{{ __('Kontak') }}</a>
            </nav>

            <div class="ml-auto hidden items-center gap-4 sm:flex">
                <a href="{{ route('login') }}" class="text-sm font-medium text-white/65 transition hover:text-white">{{ __('Masuk') }}</a>
                <a href="{{ $menuHref('#equipment') }}" class="inline-flex items-center rounded-md bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">
                    {{ __('Lihat Peralatan') }}
                </a>
            </div>

            <button
                class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-white/10 bg-white/5 text-white lg:hidden"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-label="{{ __('Buka navigasi') }}"
            >
                <span class="sr-only">{{ __('Buka navigasi') }}</span>
                <svg x-show="!open" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <svg x-cloak x-show="open" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div x-cloak x-show="open" x-transition class="lg:hidden">
            <div class="ui-container pb-4">
                <div class="rounded-lg border border-white/10 bg-slate-950/95 p-4 shadow-2xl shadow-black/30 backdrop-blur-xl">
                    <nav class="grid gap-2">
                        <a href="{{ $menuHref('#equipment') }}" class="rounded-md px-3 py-2 text-sm text-white/80 hover:bg-white/5" @click="open = false">{{ __('Peralatan') }}</a>
                        <a href="{{ $menuHref('#categories') }}" class="rounded-md px-3 py-2 text-sm text-white/80 hover:bg-white/5" @click="open = false">{{ __('Kategori') }}</a>
                        <a href="{{ $menuHref('#about') }}" class="rounded-md px-3 py-2 text-sm text-white/80 hover:bg-white/5" @click="open = false">{{ __('Tentang Kami') }}</a>
                        <a href="{{ $menuHref('#cara-sewa') }}" class="rounded-md px-3 py-2 text-sm text-white/80 hover:bg-white/5" @click="open = false">{{ __('Cara Sewa') }}</a>
                        <a href="{{ $menuHref('#contact') }}" class="rounded-md px-3 py-2 text-sm text-white/80 hover:bg-white/5" @click="open = false">{{ __('Kontak') }}</a>
                    </nav>
                    <div class="mt-4 flex items-center gap-3 border-t border-white/10 pt-4">
                        <a href="{{ route('login') }}" class="text-sm text-white/65">{{ __('Masuk') }}</a>
                        <a href="{{ $menuHref('#equipment') }}" class="ml-auto inline-flex items-center rounded-md bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950">
                            {{ __('Lihat Peralatan') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="landing-main">
        @yield('content')
    </main>

    @unless($isHomepage)
        @include('partials.footer')
    @endunless

    @stack('scripts')
</body>
</html>
