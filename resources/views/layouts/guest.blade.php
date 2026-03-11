<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @php
            $assetWithVersion = static function (string $file): string {
                return site_asset($file);
            };
            $faviconLightUrl = $assetWithVersion('MANAKE-FAV-M.png');
            $faviconDarkUrl = $assetWithVersion('MANAKE-FAV-M-white.png');
            $logoUrlLight = $assetWithVersion('manake-logo-blue.png');
            $logoUrlDark = $assetWithVersion('manake-logo-white.png');
        @endphp
        <link
            rel="icon"
            type="image/png"
            href="{{ $faviconLightUrl }}"
            data-theme-favicon
            data-light="{{ $faviconLightUrl }}"
            data-dark="{{ $faviconDarkUrl }}"
        >
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
        @include('partials.theme-init')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/theme.css', 'resources/js/app.js'])
        @php
            $resolveHexColor = static function ($value, string $fallback): string {
                $resolved = trim((string) $value);
                return preg_match('/^#([A-Fa-f0-9]{6})$/', $resolved) ? $resolved : $fallback;
            };
            $resolveIn = static function ($value, array $allowed, string $fallback): string {
                $resolved = trim((string) $value);
                return in_array($resolved, $allowed, true) ? $resolved : $fallback;
            };
            $headingScaleMap = ['sm' => '0.94', 'md' => '1', 'lg' => '1.08'];
            $bodyScaleMap = ['sm' => '0.95', 'md' => '1', 'lg' => '1.05'];

            $headingColor = $resolveHexColor(site_setting('typography.heading_color', '#1d4ed8'), '#1d4ed8');
            $subheadingColor = $resolveHexColor(site_setting('typography.subheading_color', '#2563eb'), '#2563eb');
            $bodyColor = $resolveHexColor(site_setting('typography.body_color', '#334155'), '#334155');
            $headingWeight = $resolveIn(site_setting('typography.heading_weight', '800'), ['600', '700', '800', '900'], '800');
            $bodyWeight = $resolveIn(site_setting('typography.body_weight', '400'), ['400', '500', '600'], '400');
            $headingStyle = $resolveIn(site_setting('typography.heading_style', 'normal'), ['normal', 'italic'], 'normal');
            $bodyStyle = $resolveIn(site_setting('typography.body_style', 'normal'), ['normal', 'italic'], 'normal');
            $headingScaleKey = $resolveIn(site_setting('typography.heading_scale', 'md'), ['sm', 'md', 'lg'], 'md');
            $bodyScaleKey = $resolveIn(site_setting('typography.body_scale', 'md'), ['sm', 'md', 'lg'], 'md');
        @endphp
        <style>
            [x-cloak] { display: none !important; }
            body {
                font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, sans-serif;
                color: var(--manake-body-color) !important;
                font-weight: var(--manake-body-weight);
                font-style: var(--manake-body-style);
                font-size: calc(1rem * var(--manake-body-scale));
            }
            :root {
                --manake-heading-h1: {{ $headingColor }};
                --manake-heading-h2: {{ $subheadingColor }};
                --manake-heading-h3: {{ $subheadingColor }};
                --manake-heading-h4: {{ $headingColor }};
                --manake-body-color: {{ $bodyColor }};
                --manake-heading-weight: {{ $headingWeight }};
                --manake-body-weight: {{ $bodyWeight }};
                --manake-heading-style: {{ $headingStyle }};
                --manake-body-style: {{ $bodyStyle }};
                --manake-heading-scale: {{ $headingScaleMap[$headingScaleKey] ?? '1' }};
                --manake-body-scale: {{ $bodyScaleMap[$bodyScaleKey] ?? '1' }};
            }
            h1 {
                color: var(--manake-heading-h1);
                letter-spacing: -0.015em;
                font-weight: var(--manake-heading-weight) !important;
                font-style: var(--manake-heading-style) !important;
                font-size: calc(2rem * var(--manake-heading-scale)) !important;
            }
            h2 {
                color: var(--manake-heading-h2);
                letter-spacing: -0.012em;
                font-weight: var(--manake-heading-weight) !important;
                font-style: var(--manake-heading-style) !important;
                font-size: calc(1.5rem * var(--manake-heading-scale)) !important;
            }
            h3 {
                color: var(--manake-heading-h3);
                font-weight: var(--manake-heading-weight) !important;
                font-style: var(--manake-heading-style) !important;
                font-size: calc(1.125rem * var(--manake-heading-scale)) !important;
            }
            :is(h4, h5, h6) {
                color: var(--manake-heading-h4);
                font-weight: var(--manake-heading-weight) !important;
                font-style: var(--manake-heading-style) !important;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8 sm:px-6 sm:py-10">
            <div>
                <a href="/">
                    <img src="{{ $logoUrlLight }}" alt="Manake" class="brand-logo-light h-12 w-auto rounded-xl bg-white p-2 dark:hidden">
                    <img src="{{ $logoUrlDark }}" alt="Manake" class="brand-logo-dark hidden h-12 w-auto rounded-xl bg-white p-2 dark:block">
                </a>
            </div>

            <div class="card mt-6 w-full overflow-hidden rounded-2xl px-6 py-5 shadow-md sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
