@props([
    'light' => 'manake-logo-blue.png',
    'dark' => 'manake-logo-white.png',
    'alt' => 'Manake',
    'imgClass' => '',
    'swapInDark' => true,
])

@php
    $lightUrl = site_asset($light);
    $darkUrl = $dark ? site_asset($dark) : $lightUrl;
    $resolvedTheme = request()->attributes->get('theme_resolved', 'light');
    $initialSrc = $swapInDark && $resolvedTheme === 'dark' ? $darkUrl : $lightUrl;
@endphp

<span {{ $attributes->class(['manake-themed-asset', 'manake-themed-asset--swap' => $swapInDark]) }}>
    <img
        src="{{ $initialSrc }}"
        alt="{{ $alt }}"
        data-manake-themed-image
        data-light-src="{{ $lightUrl }}"
        data-dark-src="{{ $darkUrl }}"
        data-swap-dark="{{ $swapInDark ? 'true' : 'false' }}"
        class="manake-themed-asset__image {{ $imgClass }}"
    >
</span>
