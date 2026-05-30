@extends('layouts.admin', ['activePage' => 'website'])

@section('title', __('ui.admin_website_settings.title'))
@section('page_title', __('ui.admin_website_settings.page_title'))

@php
    $logoPath = $settings['brand.logo_path'] ?? null;
    $logoUrl = $logoPath ? site_media_url($logoPath) : site_asset('manake-logo-white.png');
    $faviconPath = $settings['brand.favicon_path'] ?? null;
    $faviconUrl = $faviconPath ? site_media_url($faviconPath) : site_asset('MANAKE-FAV-M.png');
    $settingsAction = request()->routeIs('admin.settings.*')
        ? route('admin.settings.update')
        : route('admin.website.update');

    $websiteSettingsCopy = __('ui.admin_website_settings');

    if (! is_array($websiteSettingsCopy)) {
        $websiteSettingsCopy = [
            'title' => 'Website Settings',
            'page_title' => 'Website Settings',
            'brand' => [
                'title' => 'Brand',
                'subtitle' => 'Site identity',
                'name' => 'Brand Name',
                'tagline' => 'Brand Tagline',
                'logo' => 'Primary Logo',
                'favicon' => 'Browser Favicon',
                'logo_help' => 'Recommended: transparent PNG or WebP.',
                'favicon_help' => 'Recommended: square PNG, WebP, or ICO.',
            ],
            'seo' => [
                'title' => 'SEO',
                'subtitle' => 'Search appearance',
                'meta_title' => 'SEO Meta Title',
                'meta_description' => 'SEO Meta Description',
            ],
            'contact' => [
                'title' => 'Contact & Social',
                'subtitle' => 'Public contact channels shown on the website.',
                'whatsapp' => 'WhatsApp Number',
                'instagram' => 'Instagram',
                'tiktok' => 'TikTok',
            ],
            'preview' => [
                'title' => 'Preview',
                'subtitle' => 'Website Settings',
                'logos' => 'Logos',
                'logo_alt' => 'Logo preview',
                'favicon' => 'Favicon',
                'favicon_alt' => 'Favicon preview',
            ],
            'maintenance' => [
                'title' => 'Maintenance',
                'subtitle' => 'Website mode',
                'enabled' => 'Enable maintenance mode',
                'hint' => 'When enabled, users may see a maintenance state depending on the site configuration.',
            ],
            'save' => 'Save Settings',
        ];
    }
@endphp

@push('head')

<style>
    .admin-website-page {
        color: var(--admin-text);
    }

    .admin-website-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-website-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-website-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-website-title {
        color: var(--admin-text);
    }

    .admin-website-muted {
        color: var(--admin-muted);
    }

    .admin-website-subtle {
        color: var(--admin-subtle);
    }

    .admin-website-label {
        display: block;
        color: var(--admin-subtle);
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.18em;
        text-transform: uppercase;
    }

    .admin-website-input {
        width: 100%;
        min-height: 3rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 1rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .admin-website-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-website-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-website-input::placeholder {
        color: #9CA3AF !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-website-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-website-input::placeholder {
        color: #6B7280 !important;
    }

    .admin-website-file {
        width: 100%;
        min-height: 3rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-muted);
        border-radius: 1rem;
        padding: 0.45rem;
        font-size: 0.875rem;
    }

    .admin-website-file::file-selector-button {
        margin-right: 0.75rem;
        border: 0;
        border-radius: 0.8rem;
        padding: 0.55rem 0.85rem;
        font-weight: 800;
        cursor: pointer;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-website-file {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #4B5563 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-website-file::file-selector-button {
        background: #EFF6FF !important;
        color: #2563EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-website-file {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #A0A0A8 !important;
        color-scheme: dark;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-website-file::file-selector-button {
        background: rgba(217, 173, 63, 0.16) !important;
        color: #D9AD3F !important;
    }

    .admin-website-preview-box {
        border: 1px solid var(--admin-border);
        background: var(--admin-surface-raised);
        border-radius: 1.1rem;
        padding: 1rem;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-website-preview-box {
        background: #F8FAFC !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-website-preview-box {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
    }

    .admin-website-preview-frame {
        min-height: 4.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        border-radius: 1rem;
        padding: 1rem;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-website-preview-frame {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-website-preview-frame {
        background: #111113 !important;
        border-color: #1A1A1E !important;
    }

    .admin-website-checkbox-row {
        border: 1px solid var(--admin-border);
        background: var(--admin-surface-raised);
        color: var(--admin-text);
        border-radius: 1rem;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-website-checkbox-row {
        background: #F8FAFC !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-website-checkbox-row {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
    }

    .admin-website-checkbox {
        height: 1rem;
        width: 1rem;
        border-radius: 0.3rem;
        accent-color: var(--admin-accent);
    }
</style>

@endpush

@section('content')
    <div class="admin-website-page mx-auto max-w-7xl space-y-5 sm:space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ $settingsAction }}" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf

            <div class="space-y-5">
                <section class="admin-website-card p-5 sm:p-6">
                    <div class="space-y-1">
                        <h2 class="admin-website-title text-lg font-black">{{ $websiteSettingsCopy['brand']['title'] }}</h2>
                        <p class="admin-website-muted text-sm">{{ $websiteSettingsCopy['brand']['subtitle'] }}</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="admin-website-label">{{ $websiteSettingsCopy['brand']['name'] }}</label>
                            <input type="text" name="brand_name" value="{{ old('brand_name', $settings['brand.name'] ?? '') }}" class="admin-website-input mt-2">
                        </div>
                        <div>
                            <label class="admin-website-label">{{ $websiteSettingsCopy['brand']['tagline'] }}</label>
                            <input type="text" name="brand_tagline" value="{{ old('brand_tagline', $settings['brand.tagline'] ?? '') }}" class="admin-website-input mt-2">
                        </div>
                        <div>
                            <label class="admin-website-label">{{ $websiteSettingsCopy['brand']['logo'] }}</label>
                            <input type="file" name="brand_logo" accept="image/*" class="admin-website-file mt-2">
                            <p class="admin-website-subtle mt-2 text-xs">{{ $websiteSettingsCopy['brand']['logo_help'] }}</p>
                        </div>
                        <div>
                            <label class="admin-website-label">{{ $websiteSettingsCopy['brand']['favicon'] }}</label>
                            <input type="file" name="brand_favicon" accept="image/*" class="admin-website-file mt-2">
                            <p class="admin-website-subtle mt-2 text-xs">{{ $websiteSettingsCopy['brand']['favicon_help'] }}</p>
                        </div>
                    </div>
                </section>

                <section class="admin-website-card p-5 sm:p-6">
                    <div class="space-y-1">
                        <h2 class="admin-website-title text-lg font-black">{{ $websiteSettingsCopy['seo']['title'] }}</h2>
                        <p class="admin-website-muted text-sm">{{ $websiteSettingsCopy['seo']['subtitle'] }}</p>
                    </div>

                    <div class="mt-5 grid gap-4">
                        <div>
                            <label class="admin-website-label">{{ $websiteSettingsCopy['seo']['meta_title'] }}</label>
                            <input type="text" name="seo_meta_title" value="{{ old('seo_meta_title', $settings['seo.meta_title'] ?? '') }}" class="admin-website-input mt-2">
                        </div>
                        <div>
                            <label class="admin-website-label">{{ $websiteSettingsCopy['seo']['meta_description'] }}</label>
                            <textarea name="seo_meta_description" rows="4" class="admin-website-input mt-2 resize-y">{{ old('seo_meta_description', $settings['seo.meta_description'] ?? '') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="admin-website-card p-5 sm:p-6">
                    <div class="space-y-1">
                        <h2 class="admin-website-title text-lg font-black">{{ $websiteSettingsCopy['contact']['title'] }}</h2>
                        <p class="admin-website-muted text-sm">{{ $websiteSettingsCopy['contact']['subtitle'] }}</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="admin-website-label">{{ $websiteSettingsCopy['contact']['whatsapp'] }}</label>
                            <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact.whatsapp'] ?? '') }}" class="admin-website-input mt-2">
                        </div>
                        <div>
                            <label class="admin-website-label">{{ $websiteSettingsCopy['contact']['instagram'] }}</label>
                            <input type="text" name="social_instagram" value="{{ old('social_instagram', $settings['social.instagram'] ?? '') }}" class="admin-website-input mt-2">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="admin-website-label">{{ $websiteSettingsCopy['contact']['tiktok'] }}</label>
                            <input type="text" name="social_tiktok" value="{{ old('social_tiktok', $settings['social.tiktok'] ?? '') }}" class="admin-website-input mt-2">
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
                <section class="admin-website-card p-5">
                    <div class="space-y-1">
                        <h2 class="admin-website-title text-sm font-black">{{ $websiteSettingsCopy['preview']['title'] }}</h2>
                        <p class="admin-website-muted text-xs">{{ $websiteSettingsCopy['preview']['subtitle'] }}</p>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="admin-website-preview-box">
                            <p class="admin-website-label">{{ $websiteSettingsCopy['preview']['logos'] }}</p>
                            <div class="admin-website-preview-frame mt-3">
                                <img src="{{ $logoUrl }}" alt="{{ $websiteSettingsCopy['preview']['logo_alt'] }}" class="h-10 w-auto max-w-full object-contain">
                            </div>
                        </div>
                        <div class="admin-website-preview-box">
                            <p class="admin-website-label">{{ $websiteSettingsCopy['preview']['favicon'] }}</p>
                            <div class="admin-website-preview-frame mt-3">
                                <img src="{{ $faviconUrl }}" alt="{{ $websiteSettingsCopy['preview']['favicon_alt'] }}" class="h-10 w-10 rounded-xl object-contain">
                            </div>
                        </div>
                    </div>
                </section>

                <section class="admin-website-card p-5">
                    <div class="space-y-1">
                        <h2 class="admin-website-title text-sm font-black">{{ $websiteSettingsCopy['maintenance']['title'] }}</h2>
                        <p class="admin-website-muted text-xs">{{ $websiteSettingsCopy['maintenance']['subtitle'] }}</p>
                    </div>

                    <label class="admin-website-checkbox-row mt-4 flex items-start gap-3 px-4 py-3 text-sm">
                        <input type="hidden" name="maintenance_enabled" value="0">
                        <input type="checkbox" name="maintenance_enabled" value="1" class="admin-website-checkbox mt-0.5" {{ old('maintenance_enabled', $settings['site.maintenance_enabled'] ?? '') ? 'checked' : '' }}>
                        <span>
                            <span class="admin-website-title block font-bold">{{ $websiteSettingsCopy['maintenance']['enabled'] }}</span>
                            <span class="admin-website-muted mt-1 block text-xs">{{ $websiteSettingsCopy['maintenance']['hint'] }}</span>
                        </span>
                    </label>
                </section>

                <button type="submit" class="admin-accent-bg inline-flex min-h-11 w-full items-center justify-center rounded-xl px-6 text-sm font-bold transition">
                    {{ $websiteSettingsCopy['save'] }}
                </button>
            </aside>
        </form>
    </div>
@endsection
