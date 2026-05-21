@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get('theme_resolved', $initialThemePreference === 'dark' ? 'dark' : 'light');
    $brandName = site_setting('brand.name', 'Manake');
    $searchQuery = trim((string) request('q', ''));
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @include('partials.runtime-ui-assets')
    @stack('head')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="ui-shell landing-shell antialiased selection:bg-blue-600/10 selection:text-blue-700" data-manake-shell="landing">
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/88 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/88">
        <div class="ui-container flex items-center gap-4 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-brand.image light="manake-logo-blue.png" dark="manake-logo-blue.png" :alt="$brandName" img-class="h-9 w-auto" />
                <span class="hidden text-sm font-black tracking-[0.18em] text-slate-900 sm:inline">{{ $brandName }}</span>
            </a>

            <form method="GET" action="{{ route('catalog') }}" class="hidden flex-1 lg:block">
                <input type="text" name="q" value="{{ $searchQuery }}" placeholder="{{ __('Search gear, categories, or keywords...') }}" class="ui-input max-w-2xl">
            </form>

            <nav class="ml-auto hidden items-center gap-1 md:flex">
                <a href="{{ route('catalog') }}" class="rounded-full px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">{{ __('Catalog') }}</a>
                <a href="{{ route('availability.board') }}" class="rounded-full px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">{{ __('Availability') }}</a>
                <a href="{{ route('rental.rules') }}" class="rounded-full px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">{{ __('Rules') }}</a>
            </nav>

            @auth('web')
                <a href="{{ route('booking.history') }}" class="btn-primary ml-auto px-4 py-2.5 text-sm">{{ __('My Orders') }}</a>
            @else
                <a href="{{ route('login') }}" class="btn-secondary ml-auto px-4 py-2.5 text-sm">{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="btn-primary px-4 py-2.5 text-sm">{{ __('Register') }}</a>
            @endauth
        </div>
    </header>

    <main class="landing-main">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
