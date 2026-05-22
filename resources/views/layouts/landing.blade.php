@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get('theme_resolved', $initialThemePreference === 'dark' ? 'dark' : 'light');
    $brandName = site_setting('brand.name', 'Manake');
    $isHomepage = request()->routeIs('home');
    $homeUrl = route('home');
    $homeAnchorUrl = static fn (string $anchor): string => $isHomepage ? '#'.ltrim($anchor, '#') : $homeUrl.'#'.ltrim($anchor, '#');
    $navLinkClass = $isHomepage
        ? 'text-[15px] font-medium text-white/55 transition hover:text-white'
        : 'text-sm font-medium text-slate-600 transition hover:text-slate-950';
    $loginLinkClass = $isHomepage
        ? 'hidden text-sm font-medium text-white/65 transition hover:text-white sm:inline'
        : 'hidden text-sm font-medium text-slate-600 transition hover:text-slate-950 sm:inline';
    $ctaButtonClass = $isHomepage
        ? 'inline-flex items-center rounded-lg bg-amber-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-300 sm:px-5 lg:px-6'
        : 'inline-flex items-center rounded-lg bg-amber-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-300 sm:px-5 lg:px-6';
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
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @include('partials.runtime-ui-assets')
    @stack('head')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="ui-shell landing-shell antialiased selection:bg-blue-600/10 selection:text-blue-700" data-manake-shell="landing">
    <header class="{{ $isHomepage ? 'fixed inset-x-0 top-0 z-50 bg-transparent' : 'sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/90' }}">
        <div class="{{ $isHomepage ? 'mx-auto flex h-16 max-w-[1400px] items-center gap-6 px-4 sm:px-6 lg:px-10 lg:h-20' : 'ui-container flex items-center gap-4 py-4' }}">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0" aria-label="{{ $brandName }}">
                <x-brand.image light="manake-logo-white.png" dark="manake-logo-white.png" :alt="$brandName" img-class="h-9 w-auto" />
            </a>

            <nav class="{{ $isHomepage ? 'hidden flex-1 items-center justify-center gap-8 lg:flex' : 'ml-auto hidden items-center gap-1 md:flex' }}" aria-label="{{ __('Navigasi utama') }}">
                <a href="{{ $homeAnchorUrl('#equipment') }}" class="{{ $navLinkClass }}">{{ __('Peralatan') }}</a>
                <a href="{{ $homeAnchorUrl('#categories') }}" class="{{ $navLinkClass }}">{{ __('Kategori') }}</a>
                <a href="{{ $homeAnchorUrl('#about') }}" class="{{ $navLinkClass }}">{{ __('Tentang Kami') }}</a>
                <a href="{{ $homeAnchorUrl('#cara-sewa') }}" class="{{ $navLinkClass }}">{{ __('Cara Sewa') }}</a>
                <a href="{{ $homeAnchorUrl('#contact') }}" class="{{ $navLinkClass }}">{{ __('Kontak') }}</a>
            </nav>

            <div class="{{ $isHomepage ? 'ml-auto flex items-center gap-4' : 'ml-auto flex items-center gap-4' }}">
                <a href="{{ route('login') }}" class="{{ $loginLinkClass }}">
                    {{ __('Masuk') }}
                </a>
                <a href="{{ $homeAnchorUrl('#equipment') }}" class="{{ $ctaButtonClass }}">
                    {{ __('Lihat Peralatan') }}
                </a>
            </div>
        </div>
    </header>

    <main class="landing-main">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
