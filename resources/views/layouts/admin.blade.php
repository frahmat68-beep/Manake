@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get(
        'theme_resolved',
        $initialThemePreference === 'dark' ? 'dark' : 'light'
    );
@endphp
<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    class="scroll-smooth {{ $initialThemeResolved === 'dark' ? 'dark' : '' }}"
    data-theme="manake-brand"
    data-theme-preference="{{ $initialThemePreference }}"
    data-theme-resolved="{{ $initialThemeResolved }}"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.admin.panel_title') . ' | Manake')</title>
    @php
        $assetWithVersion = static function (string $file): string {
            return site_asset($file);
        };
        $faviconUrl = $assetWithVersion('MANAKE-FAV-M.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @include('partials.runtime-ui-assets')
    @stack('head')
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
            color: var(--manake-body-color-resolved, var(--text)) !important;
            font-weight: var(--manake-body-weight);
            font-style: var(--manake-body-style);
            font-size: calc(1rem * var(--manake-body-scale));
        }
        :root {
            --manake-heading-h1-light: {{ $headingColor }};
            --manake-heading-h2-light: {{ $subheadingColor }};
            --manake-heading-h3-light: {{ $subheadingColor }};
            --manake-heading-h4-light: {{ $headingColor }};
            --manake-body-color-light: {{ $bodyColor }};
            --manake-heading-h1-dark: color-mix(in oklab, {{ $headingColor }} 72%, white 28%);
            --manake-heading-h2-dark: color-mix(in oklab, {{ $subheadingColor }} 70%, white 30%);
            --manake-heading-h3-dark: color-mix(in oklab, {{ $subheadingColor }} 70%, white 30%);
            --manake-heading-h4-dark: color-mix(in oklab, {{ $headingColor }} 74%, white 26%);
            --manake-body-color-dark: color-mix(in oklab, var(--text) 90%, white 10%);
            --manake-heading-h1-resolved: var(--manake-heading-h1-light);
            --manake-heading-h2-resolved: var(--manake-heading-h2-light);
            --manake-heading-h3-resolved: var(--manake-heading-h3-light);
            --manake-heading-h4-resolved: var(--manake-heading-h4-light);
            --manake-body-color-resolved: var(--manake-body-color-light);
            --manake-heading-weight: {{ $headingWeight }};
            --manake-body-weight: {{ $bodyWeight }};
            --manake-heading-style: {{ $headingStyle }};
            --manake-body-style: {{ $bodyStyle }};
            --manake-heading-scale: {{ $headingScaleMap[$headingScaleKey] ?? '1' }};
            --manake-body-scale: {{ $bodyScaleMap[$bodyScaleKey] ?? '1' }};
        }
        html[data-theme-resolved='dark'] {
            --manake-heading-h1-resolved: var(--manake-heading-h1-dark);
            --manake-heading-h2-resolved: var(--manake-heading-h2-dark);
            --manake-heading-h3-resolved: var(--manake-heading-h3-dark);
            --manake-heading-h4-resolved: var(--manake-heading-h4-dark);
            --manake-body-color-resolved: var(--manake-body-color-dark);
        }
        body[data-manake-shell="admin"] {
            --admin-accent: #D4A843;
            --admin-accent-hover: #E0BA5D;
            --admin-accent-text: #0A0A0B;
            --admin-accent-soft: rgba(212, 168, 67, 0.12);
            --admin-accent-border: rgba(212, 168, 67, 0.30);

            --admin-bg: #05070C;
            --admin-bg-soft: #0A0A0B;
            --admin-surface: #111113;
            --admin-surface-raised: #151519;
            --admin-border: #1A1A1E;
            --admin-text: #E8E8EC;
            --admin-muted: #A0A0A8;
            --admin-subtle: #66666C;

            --manake-heading-h1-resolved: var(--admin-text);
            --manake-heading-h2-resolved: var(--admin-text);
            --manake-heading-h3-resolved: var(--admin-accent);
            --manake-heading-h4-resolved: var(--admin-text);
            --manake-body-color-resolved: var(--admin-muted);
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] {
            --admin-accent: #2563EB;
            --admin-accent-hover: #1D4ED8;
            --admin-accent-text: #FFFFFF;
            --admin-accent-soft: rgba(37, 99, 235, 0.10);
            --admin-accent-border: rgba(37, 99, 235, 0.28);

            --admin-bg: #F8FAFC;
            --admin-bg-soft: #EEF2F7;
            --admin-surface: #FFFFFF;
            --admin-surface-raised: #F1F5F9;
            --admin-border: #E5E7EB;
            --admin-text: #111827;
            --admin-muted: #4B5563;
            --admin-subtle: #6B7280;

            --manake-heading-h1-resolved: var(--admin-text);
            --manake-heading-h2-resolved: var(--admin-text);
            --manake-heading-h3-resolved: var(--admin-accent);
            --manake-heading-h4-resolved: var(--admin-text);
            --manake-body-color-resolved: var(--admin-muted);
        }

        .admin-shell-bg {
            background: var(--admin-bg) !important;
            color: var(--admin-text) !important;
        }

        .admin-card {
            background: var(--admin-surface) !important;
            border-color: var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        .admin-card-raised {
            background: var(--admin-surface-raised) !important;
            border-color: var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        .admin-title {
            color: var(--admin-text) !important;
        }

        .admin-muted {
            color: var(--admin-muted) !important;
        }

        .admin-subtle {
            color: var(--admin-subtle) !important;
        }

        .admin-border {
            border-color: var(--admin-border) !important;
        }

        .admin-accent-text {
            color: var(--admin-accent) !important;
        }

        .admin-accent-bg {
            background: var(--admin-accent) !important;
            color: var(--admin-accent-text) !important;
            border-color: var(--admin-accent) !important;
        }

        .admin-accent-bg:hover {
            background: var(--admin-accent-hover) !important;
            border-color: var(--admin-accent-hover) !important;
        }

        .admin-accent-soft {
            background: var(--admin-accent-soft) !important;
            border-color: var(--admin-accent-border) !important;
            color: var(--admin-accent) !important;
        }

        .admin-secondary-button {
            background: var(--admin-surface) !important;
            border: 1px solid var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        .admin-secondary-button:hover {
            border-color: var(--admin-accent-border) !important;
            color: var(--admin-accent) !important;
        }

        table tbody tr:hover td {
            background-color: var(--admin-surface-raised) !important;
            color: var(--admin-text) !important;
        }
        table tbody tr:focus-within td {
            background-color: var(--admin-surface-raised) !important;
            color: var(--admin-text) !important;
        }

        /* ── Admin Settings Trigger ────────────────────────────────── */
        body[data-manake-shell="admin"] .admin-settings-trigger {
            background: transparent !important;
            border: 1px solid transparent !important;
            color: var(--admin-muted) !important;
            transition: background 0.18s, border-color 0.18s, color 0.18s !important;
        }

        body[data-manake-shell="admin"] .admin-settings-trigger:hover,
        body[data-manake-shell="admin"] .admin-settings-trigger.is-open {
            background: var(--admin-accent-soft) !important;
            border-color: var(--admin-accent-border) !important;
            color: var(--admin-accent) !important;
        }

        /* ── Admin Preferences Popover ─────────────────────────────── */
        body[data-manake-shell="admin"] .admin-preferences-popover {
            width: 100% !important;
            overflow: hidden !important;
            border-radius: 1.25rem !important;
            background: var(--admin-surface) !important;
            border: 1px solid var(--admin-border) !important;
            color: var(--admin-text) !important;
            box-shadow: 0 28px 80px -35px rgba(0,0,0,0.65),
                        0 8px 30px -12px rgba(0,0,0,0.35) !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-preferences-popover {
            box-shadow: 0 28px 70px -40px rgba(15,23,42,0.28),
                        0 8px 28px -10px rgba(15,23,42,0.12) !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-popover__title {
            color: var(--admin-text) !important;
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-popover__label {
            color: var(--admin-muted) !important;
            font-size: 0.68rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.16em !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-divider {
            background: var(--admin-border) !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-grid {
            display: grid !important;
            gap: 0.4rem !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-grid--two {
            grid-template-columns: repeat(2, minmax(0,1fr)) !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-grid--stacked {
            grid-template-columns: 1fr !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice {
            display: flex !important;
            align-items: center !important;
            gap: 0.65rem !important;
            width: 100% !important;
            background: var(--admin-surface-raised) !important;
            border: 1px solid var(--admin-border) !important;
            color: var(--admin-text) !important;
            padding: 0.7rem 0.875rem !important;
            text-align: left !important;
            border-radius: 0.85rem !important;
            box-shadow: none !important;
            transition: background 0.15s, border-color 0.15s, color 0.15s !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice:hover {
            background: var(--admin-accent-soft) !important;
            border-color: var(--admin-accent-border) !important;
            color: var(--admin-accent) !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice.is-active,
        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice[data-ui-active="true"] {
            background: var(--admin-accent-soft) !important;
            border-color: var(--admin-accent-border) !important;
            color: var(--admin-accent) !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice__body {
            min-width: 0 !important;
            flex: 1 1 auto !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice__title {
            color: inherit !important;
            display: block !important;
            font-size: 0.83rem !important;
            font-weight: 700 !important;
            line-height: 1.3 !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice__meta {
            color: var(--admin-muted) !important;
            display: block !important;
            font-size: 0.7rem !important;
            line-height: 1.35 !important;
            margin-top: 0.1rem !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice.is-active .manake-preferences-choice__meta,
        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice[data-ui-active="true"] .manake-preferences-choice__meta {
            color: var(--admin-accent) !important;
            opacity: 0.75 !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice__icon,
        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice__dot {
            flex: 0 0 auto !important;
            color: inherit !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice__dot {
            display: inline-block !important;
            width: 0.55rem !important;
            height: 0.55rem !important;
            border-radius: 50% !important;
            background: currentColor !important;
            opacity: 0.45 !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice.is-active .manake-preferences-choice__dot,
        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice[data-ui-active="true"] .manake-preferences-choice__dot {
            opacity: 1 !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice__check {
            flex: 0 0 auto !important;
            color: var(--admin-accent) !important;
            opacity: 0 !important;
            transition: opacity 0.15s !important;
        }

        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice.is-active .manake-preferences-choice__check,
        body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-choice[data-ui-active="true"] .manake-preferences-choice__check {
            opacity: 1 !important;
        }

        @media (max-width: 480px) {
            body[data-manake-shell="admin"] .admin-preferences-popover .manake-preferences-grid--two {
                grid-template-columns: 1fr !important;
            }
        }

        /* ── Admin Topbar ───────────────────────────────────────────── */
        body[data-manake-shell="admin"] .admin-topbar {
            background: rgba(17, 17, 19, 0.96) !important;
            color: var(--admin-text) !important;
            border-color: var(--admin-border) !important;
            box-shadow: 0 14px 50px rgba(0, 0, 0, 0.18) !important;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-topbar {
            background: rgba(255, 255, 255, 0.96) !important;
            box-shadow: 0 14px 45px rgba(15, 23, 42, 0.08) !important;
        }

        body[data-manake-shell="admin"] .admin-topbar-title {
            color: var(--admin-text) !important;
        }

        body[data-manake-shell="admin"] .admin-topbar-subtitle {
            color: var(--admin-muted) !important;
        }

        body[data-manake-shell="admin"] .admin-topbar-link {
            color: var(--admin-muted) !important;
            transition: color 0.18s !important;
        }

        body[data-manake-shell="admin"] .admin-topbar-link:hover {
            color: var(--admin-accent) !important;
        }

        /* ── Admin Action Buttons ───────────────────────────────────── */
        .admin-action-disabled {
            background: var(--admin-surface-raised) !important;
            border: 1px solid var(--admin-border) !important;
            color: var(--admin-subtle) !important;
        }

        .admin-action-primary {
            background: var(--admin-accent-soft) !important;
            border: 1px solid var(--admin-accent-border) !important;
            color: var(--admin-accent) !important;
            transition: background 0.15s, color 0.15s !important;
        }

        .admin-action-primary:hover {
            background: var(--admin-accent) !important;
            color: var(--admin-accent-text) !important;
        }

        .admin-action-success {
            background: rgba(16, 185, 129, 0.10) !important;
            border: 1px solid rgba(16, 185, 129, 0.28) !important;
            color: #059669 !important;
            transition: background 0.15s, color 0.15s !important;
        }

        html[data-theme-resolved="dark"] .admin-action-success {
            color: #6EE7B7 !important;
        }

        .admin-action-success:hover {
            background: #10B981 !important;
            color: #ffffff !important;
        }

        .admin-action-danger {
            background: rgba(244, 63, 94, 0.10) !important;
            border: 1px solid rgba(244, 63, 94, 0.28) !important;
            color: #E11D48 !important;
            transition: background 0.15s, color 0.15s !important;
        }

        html[data-theme-resolved="dark"] .admin-action-danger {
            color: #FDA4AF !important;
        }

        .admin-action-danger:hover {
            background: #E11D48 !important;
            color: #ffffff !important;
        }

        /* ── Admin Sidebar ──────────────────────────────────────────── */
        body[data-manake-shell="admin"] .admin-sidebar {
            background: var(--admin-bg) !important;
            border-color: var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar {
            background: #FFFFFF !important;
            border-color: #E5E7EB !important;
            color: #111827 !important;
            box-shadow: 10px 0 35px -30px rgba(15, 23, 42, 0.22);
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-sidebar {
            background: #05070C !important;
            border-color: #1A1A1E !important;
            color: #E8E8EC !important;
        }

        body[data-manake-shell="admin"] .admin-sidebar-header {
            border-color: var(--admin-border) !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-header {
            background: #FFFFFF !important;
            border-color: #E5E7EB !important;
        }

        body[data-manake-shell="admin"] .admin-sidebar-footer {
            border-color: var(--admin-border) !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-footer {
            background: #FFFFFF !important;
            border-color: #E5E7EB !important;
        }

        /* Nav items */
        body[data-manake-shell="admin"] .admin-sidebar-nav-active {
            background: var(--admin-accent) !important;
            color: var(--admin-accent-text) !important;
            box-shadow: 0 12px 30px -22px var(--admin-accent) !important;
        }

        body[data-manake-shell="admin"] .admin-sidebar-nav-inactive {
            color: var(--admin-muted) !important;
        }

        body[data-manake-shell="admin"] .admin-sidebar-nav-inactive:hover {
            background: var(--admin-surface) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-nav-active {
            background: #2563EB !important;
            color: #FFFFFF !important;
            box-shadow: 0 12px 26px -20px rgba(37, 99, 235, 0.55) !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-nav-inactive {
            color: #4B5563 !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-nav-inactive:hover {
            background: #F1F5F9 !important;
            color: #111827 !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-sidebar-nav-active {
            background: #D4A843 !important;
            color: #0A0A0B !important;
            box-shadow: 0 12px 30px -22px rgba(212, 168, 67, 0.50) !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-sidebar-nav-inactive {
            color: #A0A0A8 !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-sidebar-nav-inactive:hover {
            background: #111113 !important;
            color: #E8E8EC !important;
        }

        /* Section labels */
        body[data-manake-shell="admin"] .admin-sidebar-section-label {
            color: var(--admin-subtle) !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-section-label {
            color: #6B7280 !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-sidebar-section-label {
            color: #66666C !important;
        }

        /* Profile card at the bottom */
        body[data-manake-shell="admin"] .admin-sidebar-profile-card {
            background: var(--admin-surface-raised) !important;
            border: 1px solid var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-profile-card {
            background: #F1F5F9 !important;
            border-color: #E5E7EB !important;
            color: #111827 !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-sidebar-profile-card {
            background: #151519 !important;
            border-color: #1A1A1E !important;
            color: #E8E8EC !important;
        }

        /* ── Sidebar Logo Sizing & Placement ──────────────────────── */
        body[data-manake-shell="admin"] .admin-sidebar-logo-link {
            height: 44px !important;
            min-width: 0 !important;
            overflow: visible !important;
        }

        body[data-manake-shell="admin"] .admin-sidebar-logo-frame {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            width: 164px !important;
            height: 44px !important;
            overflow: visible !important;
        }

        body[data-manake-shell="admin"] .admin-sidebar-logo-img {
            width: auto !important;
            height: 38px !important;
            max-height: 38px !important;
            max-width: none !important;
            object-fit: contain !important;
            transform-origin: left center !important;
        }

        /* Dual explicit images display logic */
        body[data-manake-shell="admin"] .admin-sidebar-logo-img--light,
        body[data-manake-shell="admin"] .admin-sidebar-logo-img--dark {
            display: none !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-logo-img--light {
            display: block !important;
            transform: scale(1.36) !important; /* Visual optical size balance */
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-sidebar-logo-img--dark {
            display: block !important;
            transform: scale(1.00) !important;
        }

        /* Collapsed Sidebar Logo constraints */
        body[data-manake-shell="admin"] .admin-sidebar-logo-link.is-collapsed {
            overflow: hidden !important;
        }

        body[data-manake-shell="admin"] .admin-sidebar-logo-link.is-collapsed .admin-sidebar-logo-frame {
            width: 40px !important;
            height: 44px !important;
            justify-content: center !important;
            overflow: hidden !important;
        }

        body[data-manake-shell="admin"] .admin-sidebar-logo-link.is-collapsed .admin-sidebar-logo-img {
            height: 32px !important;
            max-height: 32px !important;
            transform-origin: center center !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-sidebar-logo-link.is-collapsed .admin-sidebar-logo-img--light {
            display: block !important;
            transform: scale(1.20) !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-sidebar-logo-link.is-collapsed .admin-sidebar-logo-img--dark {
            display: block !important;
            transform: scale(1.00) !important;
        }

        /* ── Admin Categories Page Styles ──────────────────────────── */
        body[data-manake-shell="admin"] .admin-categories-page {
            color: var(--admin-text);
        }

        body[data-manake-shell="admin"] .admin-categories-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            color: var(--admin-text);
            border-radius: 1.35rem;
            box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-card {
            background: #FFFFFF !important;
            border-color: #E5E7EB !important;
            box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-card {
            background: #111113 !important;
            border-color: #1A1A1E !important;
            box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
        }

        body[data-manake-shell="admin"] .admin-categories-title {
            color: var(--admin-text);
        }

        body[data-manake-shell="admin"] .admin-categories-muted {
            color: var(--admin-muted);
        }

        body[data-manake-shell="admin"] .admin-categories-subtle {
            color: var(--admin-subtle);
        }

        body[data-manake-shell="admin"] .admin-categories-kicker {
            color: var(--admin-accent);
            font-size: 0.72rem;
            font-weight: 900;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }

        body[data-manake-shell="admin"] .admin-categories-input,
        body[data-manake-shell="admin"] .admin-categories-textarea {
            width: 100%;
            border: 1px solid var(--admin-border);
            background: var(--admin-surface);
            color: var(--admin-text);
            border-radius: 0.95rem;
            padding: 0.85rem 1rem;
            font-size: 0.875rem;
            outline: none;
            transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
        }

        body[data-manake-shell="admin"] .admin-categories-input {
            min-height: 3.05rem;
        }

        body[data-manake-shell="admin"] .admin-categories-textarea {
            min-height: 7.5rem;
            resize: vertical;
        }

        body[data-manake-shell="admin"] .admin-categories-input:focus,
        body[data-manake-shell="admin"] .admin-categories-textarea:focus {
            border-color: var(--admin-accent);
            box-shadow: 0 0 0 3px var(--admin-accent-soft);
        }

        body[data-manake-shell="admin"] .admin-categories-input::placeholder,
        body[data-manake-shell="admin"] .admin-categories-textarea::placeholder {
            color: var(--admin-subtle);
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-input,
        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-textarea {
            background: #FFFFFF !important;
            border-color: #E5E7EB !important;
            color: #111827 !important;
            color-scheme: light;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-input,
        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-textarea {
            background: #0A0A0B !important;
            border-color: #1A1A1E !important;
            color: #E8E8EC !important;
            color-scheme: dark;
        }

        body[data-manake-shell="admin"] .admin-categories-table thead {
            background: var(--admin-surface-raised);
            color: var(--admin-muted);
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table thead {
            background: #F8FAFC !important;
            color: #4B5563 !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table thead {
            background: #0A0A0B !important;
            color: #A0A0A8 !important;
        }

        body[data-manake-shell="admin"] .admin-categories-table tbody tr {
            background: transparent !important;
            color: var(--admin-text) !important;
            border-bottom: 1px solid var(--admin-border);
            transition: background-color 160ms ease, color 160ms ease;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr {
            border-bottom-color: #E5E7EB !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr {
            border-bottom-color: #1A1A1E !important;
        }

        body[data-manake-shell="admin"] .admin-categories-table tbody tr:last-child {
            border-bottom: 0 !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover {
            background: #F8FAFC !important;
            color: #111827 !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover {
            background: #151519 !important;
            color: #E8E8EC !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover .admin-categories-title {
            color: #111827 !important;
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover .admin-categories-muted {
            color: #4B5563 !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover .admin-categories-title {
            color: #E8E8EC !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-categories-table tbody tr:hover .admin-categories-muted {
            color: #A0A0A8 !important;
        }

        body[data-manake-shell="admin"] .admin-category-locked-button {
            border: 1px solid var(--admin-border);
            background: var(--admin-surface-raised);
            color: var(--admin-subtle);
        }

        html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-category-locked-button {
            background: #F8FAFC !important;
            border-color: #E5E7EB !important;
            color: #6B7280 !important;
        }

        html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-category-locked-button {
            background: #0A0A0B !important;
            border-color: #1A1A1E !important;
            color: #66666C !important;
        }

        body[data-manake-shell="admin"] .admin-category-label {
            display: block;
            color: var(--admin-muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        body[data-manake-shell="admin"] .admin-category-help {
            margin-top: 0.45rem;
            color: var(--admin-subtle);
            font-size: 0.75rem;
            line-height: 1.45;
        }
    </style>
</head>
<body class="manake-shell" data-admin-panel="true" data-manake-shell="admin">
    @include('partials.page-loader')
    @php
        $activePage = $activePage ?? '';
        $brandName = site_setting('brand.name', 'Manake');
        $logoUrl = $assetWithVersion('manake-logo-white.png');
        $adminName = auth('admin')->user()->name ?? __('Admin');
        $adminRole = auth('admin')->user()->role ?? 'admin';
        $isSuperAdmin = auth('admin')->check() && $adminRole === 'super_admin';
        $locale = app()->getLocale();
        $currentTheme = $themePreference ?? request()->attributes->get('theme_preference', 'light');
        if (! in_array($currentTheme, ['system', 'dark', 'light'], true)) {
            $currentTheme = 'light';
        }
    @endphp

    <div x-data="{ sidebarOpen: false, sidebarCollapsed: false, adminSettingsOpen: false }" class="admin-shell-bg min-h-screen">
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false"></div>

        <x-admin.sidebar
            :logo-url="$logoUrl"
            :brand-name="$brandName"
            :active-page="$activePage"
            :is-super-admin="$isSuperAdmin"
            :admin-name="$adminName"
            :admin-role="$adminRole"
        />

        <div class="transition-all duration-300" :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'">
            <header class="admin-topbar manake-topbar-shell sticky top-0 z-50 border-b" data-manake-topbar="admin">
                <div class="mx-auto flex h-16 w-full max-w-[1320px] items-center justify-between gap-3 px-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="admin-secondary-button inline-flex h-9 w-9 items-center justify-center rounded-xl lg:hidden" @click="sidebarOpen = true" aria-label="{{ __('ui.actions.close') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="4" y1="7" x2="20" y2="7"></line>
                                <line x1="4" y1="12" x2="20" y2="12"></line>
                                <line x1="4" y1="17" x2="20" y2="17"></line>
                            </svg>
                        </button>

                        {{-- Desktop Collapse Toggle --}}
                        <button type="button" class="admin-secondary-button hidden h-9 w-9 items-center justify-center rounded-xl transition lg:flex" @click="sidebarCollapsed = !sidebarCollapsed" aria-label="{{ __('ui.admin.toggle_sidebar', [], null, app()->getLocale()) ?: 'Toggle sidebar' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m15 18-6-6 6-6"/>
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <h1 class="admin-topbar-title truncate text-lg font-semibold">@yield('page_title', __('ui.admin.dashboard'))</h1>
                            <p class="admin-topbar-subtitle text-xs">{{ __('ui.admin.panel_title') }}</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <a href="/" class="admin-topbar-link hidden text-sm font-semibold sm:inline">{{ __('ui.admin.view_website') }}</a>
                        <div class="relative" @click.outside="adminSettingsOpen = false">
                            <button
                                type="button"
                                class="admin-settings-trigger inline-flex h-9 w-9 items-center justify-center rounded-xl"
                                :class="{ 'is-open': adminSettingsOpen }"
                                @click="adminSettingsOpen = !adminSettingsOpen"
                                :aria-expanded="adminSettingsOpen.toString()"
                                aria-label="{{ __('ui.nav.settings') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3 1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8 1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" />
                                </svg>
                            </button>
                            <div
                                x-cloak
                                x-show="adminSettingsOpen"
                                x-transition.origin.top.right
                                class="absolute right-0 z-[100] mt-3 w-[20rem] max-w-[calc(100vw-2rem)] origin-top-right"
                            >
                                <x-preferences.popover
                                    :locale="$locale"
                                    :current-theme="$currentTheme"
                                    :redirect="url()->full()"
                                    class="admin-preferences-popover"
                                />
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="admin-secondary-button rounded-xl px-3 py-2 text-xs font-semibold transition">
                                {{ __('ui.nav.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="admin-shell-bg px-4 py-5 sm:px-6 sm:py-6">
                <div class="mx-auto w-full max-w-[1320px]">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        window.fetchWithCsrf = async function (url, options = {}) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                ...(options.headers || {}),
            };

            return fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers,
            });
        };
        window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (window.axios && window.csrfToken) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken;
        }
    </script>
    @include('partials.ui-feedback')
    @stack('scripts')
    @include('partials.theme-toggle')
</body>
</html>
