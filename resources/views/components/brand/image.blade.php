@props([
    'light' => 'manake-logo-blue.png',
    'dark' => 'manake-logo-white.png',
    'alt' => 'Manake',
    'imgClass' => '',
    'swapInDark' => true,
    'width' => null,
    'height' => null,
])

@php
    // Handle both imgClass and img-class attribute smoothly
    $resolvedImgClass = $imgClass ?: ($attributes->get('img-class') ?: '');
    $attributes = $attributes->except(['img-class']);

    $managedLogoPath = site_setting('brand.logo_path');
    $managedLogoUrl = site_media_url($managedLogoPath);
    $lightUrl = $managedLogoUrl ?: site_asset($light);
    $darkUrl = $managedLogoUrl ?: ($dark ? site_asset($dark) : $lightUrl);
    $resolvedTheme = $themeResolved ?? request()->attributes->get('theme_resolved', 'light');
    $initialSrc = $swapInDark && $resolvedTheme === 'dark' ? $darkUrl : $lightUrl;

    // Detect image dimensions to avoid CLS / unsized-images warning
    $detectedWidth = $width;
    $detectedHeight = $height;
    if (!$detectedWidth && !$detectedHeight) {
        if ($light === 'manake-logo-white.png' || $light === 'manake-logo-blue.png') {
            $detectedWidth = 640;
            $detectedHeight = 154;
        } elseif ($light === 'MANAKE-FAV-M.png') {
            $detectedWidth = 493;
            $detectedHeight = 512;
        }
    }
@endphp

<span {{ $attributes->class(['manake-themed-asset', 'manake-themed-asset--swap' => $swapInDark, 'inline-flex', 'items-center', 'justify-center']) }}>
    <img
        src="{{ $initialSrc }}"
        alt="{{ $alt }}"
        @if ($detectedWidth) width="{{ $detectedWidth }}" @endif
        @if ($detectedHeight) height="{{ $detectedHeight }}" @endif
        @if ($managedLogoUrl)
            name="manake-cms-logo"
        @endif
        data-manake-themed-image
        data-light-src="{{ $lightUrl }}"
        data-dark-src="{{ $darkUrl }}"
        data-swap-dark="{{ $swapInDark ? 'true' : 'false' }}"
        class="manake-themed-asset__image object-contain max-h-full max-w-full {{ $resolvedImgClass }}"
    >
</span>

<style>
    /* Premium visual scaling correction to ensure the light-theme logo matches the dark-theme logo exactly */
    html[data-theme-resolved='light'] img[src*='manake-logo-blue.png'],
    html[data-theme-resolved='light'] .manake-themed-asset__image[src*='manake-logo-blue.png'] {
        transform: scale(1.08) !important;
        transform-origin: left center !important;
    }
</style>
