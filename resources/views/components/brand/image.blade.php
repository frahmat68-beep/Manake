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
            $detectedWidth = 640;
            $detectedHeight = 154;
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
    /* Prevent layout shifting, establish consistent logo containment for navbar logo */
    .manake-navbar-logo {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        height: 44px !important;
        width: 180px !important;
        overflow: visible !important;
    }
    .manake-navbar-logo__image {
        height: 44px !important;
        max-height: 44px !important;
        width: auto !important;
        object-fit: contain !important;
        transition: transform 0.15s ease-in-out;
        transform-origin: left center !important;
    }
    /* Dynamic visual scale compensation to align light/dark logos visually by eye */
    html[data-theme-resolved="light"] .manake-navbar-logo__image {
        transform: scale(1.65) !important;
    }
    html[data-theme-resolved="dark"] .manake-navbar-logo__image {
        transform: scale(1.0) !important;
    }
</style>
