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
            $compactAuthShowcaseTitle = $asideHeading ?: null;
            $compactAuthShowcaseText = $asideText ?: null;
        @endphp

        <div class="flex min-h-screen w-full bg-[#0A0A0B] text-[#E8E8EC]">
            <!-- Left Side: Image / Showcase (hidden on mobile) -->
            <div class="relative hidden w-1/2 lg:block">
                <img src="{{ site_asset('images/camera-arri.jpg') }}" alt="Cinematic Camera" class="absolute inset-0 h-full w-full object-cover">
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-[#0A0A0B] via-[#0A0A0B]/60 to-[#0A0A0B]/20"></div>
                <div class="absolute inset-0 bg-[#D4A843]/10 mix-blend-overlay"></div>
                
                <!-- Showcase Content -->
                <div class="absolute bottom-16 left-16 right-16 z-10">
                    @if ($compactAuthShowcaseTitle)
                        <h1 class="font-serif text-5xl font-bold tracking-tight text-white mb-6">
                            {{ $compactAuthShowcaseTitle }}
                        </h1>
                    @endif
                    @if ($compactAuthShowcaseText)
                        <p class="text-lg text-[#A0A0A8] max-w-xl">
                            {{ $compactAuthShowcaseText }}
                        </p>
                    @else
                        <p class="text-lg text-[#A0A0A8] max-w-xl">
                            Sewa kamera sinema, lighting, audio, drone, dan stabilizer profesional dengan proses yang rapi dari pilih alat sampai pembayaran.
                        </p>
                    @endif
                    
                    @if(count($asidePoints) > 0)
                        <ul class="mt-8 space-y-3">
                            @foreach($asidePoints as $point)
                            <li class="flex items-center gap-3 text-white font-medium">
                                <svg class="h-5 w-5 text-[#D4A843]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $point }}
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="relative flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 sm:px-16 md:px-24">
                <!-- Add a subtle background glow for mobile where there's no left side image -->
                <div class="pointer-events-none absolute inset-0 lg:hidden bg-[radial-gradient(circle_at_top,_rgba(212,168,67,0.12),transparent_28%),radial-gradient(circle_at_bottom,_rgba(212,168,67,0.06),transparent_22%)]"></div>

                <div class="mx-auto w-full max-w-md relative z-10">
                    <a href="{{ route('home') }}" class="mb-12 flex items-center transition-transform hover:scale-105 w-fit" data-skip-loader="true">
                        <x-brand.image
                            light="manake-logo-white.png"
                            dark="manake-logo-white.png"
                            alt="{{ $brandName }}"
                            img-class="h-10 w-auto object-contain"
                            :swap-in-dark="false"
                        />
                    </a>

                    @if ($heading ?? null)
                        <h2 class="text-3xl font-bold tracking-tight text-white mb-8 font-serif">
                            {{ $heading }}
                        </h2>
                    @endif

                    <div class="flex w-full flex-col gap-5">
                        {{ $slot }}
                    </div>
                    
                    @if ($showBackHome ?? true)
                        <a href="{{ $backUrl }}" class="mt-10 inline-flex w-fit items-center text-sm font-semibold text-[#A0A0A8] transition hover:text-[#D4A843]" data-skip-loader="true">
                            &larr; {{ $backLabel }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <x-chatbot-widget />
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
