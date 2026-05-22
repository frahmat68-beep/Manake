@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get('theme_resolved', $initialThemePreference === 'dark' ? 'dark' : 'light');
    $brandName = site_setting('brand.name', 'Manake');
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
    <header class="fixed inset-x-0 top-0 z-50 bg-transparent">
        <div class="mx-auto flex h-16 max-w-[1400px] items-center gap-6 px-4 sm:px-6 lg:px-10 lg:h-20">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0" aria-label="{{ $brandName }}">
                <x-brand.image light="manake-logo-white.png" dark="manake-logo-white.png" :alt="$brandName" img-class="h-9 w-auto" />
            </a>

            <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex" aria-label="{{ __('Navigasi utama') }}">
                <a href="#equipment" class="text-[15px] font-medium text-white/55 transition hover:text-white">{{ __('Peralatan') }}</a>
                <a href="#categories" class="text-[15px] font-medium text-white/55 transition hover:text-white">{{ __('Kategori') }}</a>
                <a href="#about" class="text-[15px] font-medium text-white/55 transition hover:text-white">{{ __('Tentang Kami') }}</a>
                <a href="#cara-sewa" class="text-[15px] font-medium text-white/55 transition hover:text-white">{{ __('Cara Sewa') }}</a>
                <a href="#contact" class="text-[15px] font-medium text-white/55 transition hover:text-white">{{ __('Kontak') }}</a>
            </nav>

            <div class="ml-auto flex items-center gap-4">
                <a href="{{ route('login') }}" class="hidden text-sm font-medium text-white/65 transition hover:text-white sm:inline">
                    {{ __('Masuk') }}
                </a>
                <a href="{{ route('catalog') }}" class="inline-flex items-center rounded-lg bg-amber-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-300 sm:px-5 lg:px-6">
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
