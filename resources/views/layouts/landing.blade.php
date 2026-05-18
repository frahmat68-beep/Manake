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
    <style>
        [x-cloak] { display: none !important; }
        html, body { margin: 0; min-height: 100%; }
        body {
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, sans-serif;
            background: #fff;
            color: #0f172a;
            overflow-x: hidden;
        }
        .landing-shell :is(h1,h2,h3,h4,h5,h6) { color: inherit !important; }
        .landing-header {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(226,232,240,.78);
            background: rgba(255,255,255,.93);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 12px 36px rgba(15,23,42,.055);
        }
        .landing-search {
            box-shadow: inset 0 0 0 1px rgba(226,232,240,.92), 0 10px 30px rgba(15,23,42,.045);
        }
        .landing-main { min-height: 70vh; }
    </style>
</head>
<body class="landing-shell antialiased selection:bg-blue-600/10 selection:text-blue-700">
    <header class="landing-header">
        <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ $brandName }}">
                <img src="{{ site_asset('MANAKE-FAV-M.png') }}" alt="{{ $brandName }}" class="h-10 w-10 object-contain">
                <span class="hidden text-base font-black tracking-tight text-slate-950 sm:inline">{{ $brandName }}</span>
            </a>

            <form method="GET" action="{{ route('catalog') }}" class="landing-search relative ml-0 hidden flex-1 rounded-2xl bg-white/90 sm:block lg:ml-5 lg:max-w-xl">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 3.5a5 5 0 1 0 0 10 5 5 0 0 0 0-10ZM2 8.5a6.5 6.5 0 1 1 11.158 4.157l3.092 3.093a1 1 0 0 1-1.414 1.414l-3.093-3.092A6.5 6.5 0 0 1 2 8.5Z" clip-rule="evenodd" /></svg>
                </span>
                <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Search cameras, lighting, audio..." autocomplete="off" class="w-full rounded-2xl border-0 bg-transparent py-3 pl-11 pr-4 text-sm font-semibold text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0">
            </form>

            <nav class="ml-auto hidden items-center gap-1 text-sm font-bold text-slate-600 md:flex">
                <a href="{{ route('catalog') }}" class="rounded-xl px-3 py-2 transition hover:bg-slate-100 hover:text-blue-700">Catalog</a>
                <a href="{{ route('availability.board') }}" class="rounded-xl px-3 py-2 transition hover:bg-slate-100 hover:text-blue-700">Availability</a>
                <a href="{{ route('rental.rules') }}" class="rounded-xl px-3 py-2 transition hover:bg-slate-100 hover:text-blue-700">Rules</a>
            </nav>

            @auth('web')
                <a href="{{ route('cart') }}" class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700 sm:inline-flex">Cart</a>
                <a href="{{ route('booking.history') }}" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Orders</a>
            @else
                <a href="{{ route('login') }}" class="hidden rounded-2xl px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-100 sm:inline-flex">Login</a>
                <a href="{{ route('register') }}" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Register</a>
            @endauth
        </div>
    </header>

    <main class="landing-main">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white py-8 text-sm text-slate-500">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <p>© {{ now()->year }} Manake Rental. Professional production gear rental.</p>
            <div class="flex gap-4 font-bold text-slate-600">
                <a href="{{ route('catalog') }}" class="hover:text-blue-700">Catalog</a>
                <a href="{{ route('availability.board') }}" class="hover:text-blue-700">Availability</a>
                <a href="{{ route('contact') }}" class="hover:text-blue-700">Contact</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
