@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get(
        'theme_resolved',
        $initialThemePreference === 'dark' ? 'dark' : 'light'
    );
@endphp
<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="scroll-smooth {{ $initialThemeResolved === 'dark' ? 'dark' : '' }}"
    data-theme="manake-brand"
    data-theme-preference="{{ $initialThemePreference }}"
    data-theme-resolved="{{ $initialThemeResolved }}"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $assetWithVersion = static function (string $file): string {
                return site_asset($file);
            };

            $faviconUrl = $assetWithVersion('MANAKE-FAV-M.png');
            $brandName = site_setting('brand.name', config('app.name', 'Manake'));
            $pageTitle = $pageTitle ?: $brandName;
            $backUrl = $backUrl ?: route('home');
            $backLabel = $backLabel ?: __('app.auth.back_home');
            $asidePoints = array_values(array_filter($asidePoints));
        @endphp

        <title>{{ $pageTitle }}</title>
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
        @include('partials.theme-init')
        @include('partials.runtime-ui-assets')
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, sans-serif; }
        </style>
        {{ $head ?? '' }}
    </head>
    <body class="manake-shell antialiased" data-manake-shell="auth">
        @include('partials.page-loader')

        @php
            $compactAuthShowcaseTitle = $asideHeading ?: $heading ?: $brandName;
            $compactAuthShowcaseText = $asideText ?: null;
        @endphp

        <div class="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-[#0A0A0B] px-4 py-10 text-[#E8E8EC]">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(212,168,67,0.12),transparent_28%),radial-gradient(circle_at_bottom,_rgba(212,168,67,0.06),transparent_22%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(10,10,11,0.88),rgba(10,10,11,0.7))]"></div>

            <div class="relative z-10 w-full max-w-sm rounded-lg border border-[#1A1A1E] bg-[#111113] p-8 shadow-[0_0_80px_-20px_rgba(212,168,67,0.18)]">
                <a href="{{ route('home') }}" class="mb-8 flex items-center justify-center transition-transform hover:scale-105" data-skip-loader="true">
                    <x-brand.image
                        light="manake-logo-blue.png"
                        dark="manake-logo-blue.png"
                        alt="{{ $brandName }}"
                        img-class="h-16 w-auto object-contain"
                    />
                </a>

                @if ($heading ?? null)
                    <h2 class="text-3xl font-extrabold tracking-tighter !text-white mb-8 text-center">
                        {{ $heading }}
                    </h2>
                @endif

                <div class="flex w-full flex-col gap-4">
                    {{ $slot }}
                </div>
            </div>
            
            @if ($showBackHome)
                <a href="{{ $backUrl }}" class="relative z-10 mt-6 inline-flex w-fit items-center justify-center text-sm font-semibold text-[#A0A0A8] transition hover:text-[#D4A843]" data-skip-loader="true">
                    &larr; {{ $backLabel }}
                </a>
            @endif
        </div>

        @include('partials.theme-toggle')
        <script>
            window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            if (window.axios && window.csrfToken) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken;
            }
            window.fetchWithCsrf = (url, options = {}) => {
                const headers = new Headers(options.headers || {});
                if (window.csrfToken) {
                    headers.set('X-CSRF-TOKEN', window.csrfToken);
                }
                return fetch(url, { ...options, headers });
            };
        </script>
    </body>
</html>
