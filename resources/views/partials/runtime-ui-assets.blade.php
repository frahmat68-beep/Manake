@php
    $inlineAppCss = null;
    $manifestPath = public_path('build/manifest.json');
    $useViteFallback = false;

    if (is_file($manifestPath)) {
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $appCssPath = is_array($manifest) ? ($manifest['resources/css/app.css']['file'] ?? null) : null;

        if (is_string($appCssPath) && $appCssPath !== '') {
            $fullAppCssPath = public_path('build/' . ltrim($appCssPath, '/'));
            if (is_file($fullAppCssPath)) {
                $inlineAppCss = file_get_contents($fullAppCssPath);
            }
        }
    }

    if (! is_string($inlineAppCss) || $inlineAppCss === '') {
        $useViteFallback = is_file(public_path('hot'));
    }
@endphp

@if (is_string($inlineAppCss) && $inlineAppCss !== '')
    <style id="manake-inline-app-css">{!! $inlineAppCss !!}</style>
@elseif ($useViteFallback)
    @vite(['resources/css/app.css'])
@else
    <style id="manake-inline-app-css"></style>
@endif

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
