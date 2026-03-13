<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
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
    <body class="min-h-screen antialiased" data-manake-shell="auth">
        @include('partials.page-loader')

        @php
            $compactAuthShowcaseTitle = $asideHeading ?: $heading ?: $brandName;
            $compactAuthShowcaseText = $asideText ?: null;
        @endphp

        <div class="manake-auth-shell min-h-screen px-4 py-4 sm:px-6 sm:py-6">
            <div class="manake-auth-card mx-auto w-full max-w-5xl overflow-hidden rounded-[2rem] lg:grid lg:grid-cols-[minmax(0,0.96fr)_minmax(0,1.04fr)]">
                <div class="manake-auth-panel p-5 sm:p-7 lg:p-8">
                    <div class="space-y-3">
                        <a href="{{ route('home') }}" class="inline-flex items-center" data-skip-loader="true" aria-label="{{ $brandName }}">
                            <x-brand.image
                                light="manake-logo-blue.png"
                                dark="manake-logo-white.png"
                                :alt="$brandName"
                                img-class="h-[2.2rem] w-auto"
                            />
                        </a>

                        @if ($eyebrow || $heading || $subheading)
                            <div class="space-y-1.5">
                                @if ($eyebrow)
                                    <span class="manake-kicker">{{ $eyebrow }}</span>
                                @endif
                                @if ($heading)
                                    <h1 class="text-3xl font-semibold tracking-[-0.03em] text-slate-950">{{ $heading }}</h1>
                                @endif
                        @if ($subheading)
                            <p class="max-w-md text-sm leading-6 text-slate-500">{{ $subheading }}</p>
                        @endif
                            </div>
                        @endif
                    </div>

                    <div class="mt-6">
                        {{ $slot }}
                    </div>
                </div>

                <div class="manake-auth-showcase hidden p-7 text-slate-100 lg:flex lg:flex-col lg:justify-between lg:p-8">
                    <div class="space-y-5">
                        <x-brand.image
                            light="manake-logo-white.png"
                            dark="manake-logo-white.png"
                            :alt="$brandName"
                            img-class="h-[2.6rem] w-auto"
                        />
                        <div class="space-y-2">
                            <h2 class="text-[1.75rem] font-semibold leading-tight tracking-[-0.04em] text-white">
                                {{ $compactAuthShowcaseTitle }}
                            </h2>
                            @if ($compactAuthShowcaseText)
                                <p class="max-w-sm text-sm leading-6 text-blue-100/80">
                                    {{ $compactAuthShowcaseText }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($showBackHome)
                        <a href="{{ $backUrl }}" class="mt-8 inline-flex w-fit items-center justify-center rounded-2xl border border-white/20 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10" data-skip-loader="true">
                            {{ $backLabel }}
                        </a>
                    @endif
                </div>
            </div>
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
