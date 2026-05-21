@php
    $inlineAppCss = null;
    $inlineThemeCss = null;
    $manifestPath = public_path('build/manifest.json');
    $useViteFallback = false;

    if (is_file($manifestPath)) {
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $appCssPath = is_array($manifest) ? ($manifest['resources/css/app.css']['file'] ?? null) : null;
        $themeCssPath = is_array($manifest) ? ($manifest['resources/css/theme.css']['file'] ?? null) : null;

        if (is_string($appCssPath) && $appCssPath !== '') {
            $fullAppCssPath = public_path('build/' . ltrim($appCssPath, '/'));
            if (is_file($fullAppCssPath)) {
                $inlineAppCss = file_get_contents($fullAppCssPath);
            }
        }

        if (is_string($themeCssPath) && $themeCssPath !== '') {
            $fullThemeCssPath = public_path('build/' . ltrim($themeCssPath, '/'));
            if (is_file($fullThemeCssPath)) {
                $inlineThemeCss = file_get_contents($fullThemeCssPath);
            }
        }
    }

    if ((! is_string($inlineAppCss) || $inlineAppCss === '') || (! is_string($inlineThemeCss) || $inlineThemeCss === '')) {
        $useViteFallback = is_file(public_path('hot'));
    }

    $isPublicHome = request()->routeIs('home') && ! auth('web')->check();
@endphp

@if (is_string($inlineAppCss) && $inlineAppCss !== '')
    <style id="manake-inline-app-css">{!! $inlineAppCss !!}</style>
@endif

@if (is_string($inlineThemeCss) && $inlineThemeCss !== '')
    <style id="manake-inline-theme-css">{!! $inlineThemeCss !!}</style>
@elseif ($useViteFallback)
    @vite(['resources/css/app.css', 'resources/css/theme.css'])
@else
    <style id="manake-inline-app-css"></style>
    <style id="manake-inline-theme-css"></style>
@endif

@if ($isPublicHome)
    <style id="manake-public-home-shell-overrides">
        body[data-manake-shell="app"] [data-manake-sidebar="app"] {
            display: none !important;
        }
        @media (min-width: 1024px) {
            body[data-manake-shell="app"] .lg\:pl-24 {
                padding-left: 0 !important;
            }
        }
        body[data-manake-shell="app"] .manake-main-stage {
            padding: 0 !important;
            background: #ffffff !important;
        }
        body[data-manake-shell="app"] .manake-main-stage > div {
            width: 100% !important;
            max-width: none !important;
        }
        body[data-manake-shell="app"] [data-manake-topbar="app"] {
            background: rgba(255, 255, 255, 0.94) !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.72) !important;
            box-shadow: 0 12px 36px rgba(15, 23, 42, 0.055) !important;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
        body[data-manake-shell="app"] [data-manake-topbar="app"] > div {
            max-width: 1280px !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
        body[data-manake-shell="app"] .manake-main-stage section {
            scroll-margin-top: 5rem;
        }
        body[data-manake-shell="app"] .manake-main-stage .manake-hero,
        body[data-manake-shell="app"] .manake-main-stage .noise-overlay:first-child {
            margin-top: 0 !important;
        }
        body[data-manake-shell="app"] .manake-main-stage :is(.shadow-2xl, .shadow-xl) {
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.10) !important;
        }
        body[data-manake-shell="app"] .manake-main-stage :is(section) + section {
            margin-top: 0 !important;
        }
    </style>
@endif

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
