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
<body class="ui-shell landing-shell antialiased selection:bg-amber-500/20 selection:text-amber-900" data-manake-shell="landing">
    <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/55 text-white shadow-[0_18px_50px_rgba(15,23,42,0.2)] backdrop-blur-xl">
        <div class="ui-container flex items-center gap-4 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-brand.image light="manake-logo-blue.png" dark="manake-logo-blue.png" :alt="$brandName" img-class="h-9 w-auto" />
                <span class="hidden text-sm font-black tracking-[0.18em] text-white sm:inline">{{ $brandName }}</span>
            </a>

            <nav class="ml-auto hidden items-center gap-1 md:flex">
                <a href="{{ route('catalog') }}" class="rounded-full px-4 py-2 text-sm font-bold text-slate-200 transition hover:bg-white/10 hover:text-white">{{ __('Equipment') }}</a>
                <a href="{{ route('categories.index') }}" class="rounded-full px-4 py-2 text-sm font-bold text-slate-200 transition hover:bg-white/10 hover:text-white">{{ __('Categories') }}</a>
                <a href="{{ route('home') }}#about" class="rounded-full px-4 py-2 text-sm font-bold text-slate-200 transition hover:bg-white/10 hover:text-white">{{ __('About') }}</a>
                <a href="{{ route('contact') }}" class="rounded-full px-4 py-2 text-sm font-bold text-slate-200 transition hover:bg-white/10 hover:text-white">{{ __('Contact') }}</a>
            </nav>

            <a href="{{ route('login') }}" class="ml-auto inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/8 px-4 py-2.5 text-sm font-black text-white transition hover:bg-white/14">{{ __('Sign in') }}</a>
            <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-2xl border border-amber-300/30 bg-amber-400 px-4 py-2.5 text-sm font-black text-slate-950 shadow-lg shadow-amber-900/20 transition hover:-translate-y-0.5 hover:bg-amber-300">{{ __('Browse Gear') }}</a>
        </div>
    </header>

    <main class="landing-main">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
