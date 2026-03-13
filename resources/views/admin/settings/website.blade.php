@extends('layouts.admin', ['activePage' => 'website'])

@section('title', __('ui.admin.website_settings'))
@section('page_title', __('ui.admin.website_settings'))

@php
    $logoPath = $settings['brand.logo_path'] ?? null;
    $logoUrl = $logoPath ? site_media_url($logoPath) : site_asset('manake-logo-blue.png');
    $faviconPath = $settings['brand.favicon_path'] ?? null;
    $faviconUrl = $faviconPath ? site_media_url($faviconPath) : site_asset('MANAKE-FAV-M.png');
    $settingsAction = request()->routeIs('admin.settings.*')
        ? route('admin.settings.update')
        : route('admin.website.update');
@endphp

@section('content')
    <div class="mx-auto max-w-6xl space-y-5">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ $settingsAction }}" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_20rem]">
            @csrf

            <div class="space-y-5">
                <section class="card rounded-[2rem] p-6 shadow-sm sm:p-7">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('ui.admin.settings_brand') }}</h2>
                        <p class="text-sm text-slate-500">{{ __('ui.admin.settings_identity') }}</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Nama Brand') }}</label>
                            <input type="text" name="brand_name" value="{{ old('brand_name', $settings['brand.name'] ?? '') }}" class="input mt-2 w-full rounded-2xl px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Tagline Brand') }}</label>
                            <input type="text" name="brand_tagline" value="{{ old('brand_tagline', $settings['brand.tagline'] ?? '') }}" class="input mt-2 w-full rounded-2xl px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Logo Utama') }}</label>
                            <input type="file" name="brand_logo" accept="image/*" class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Favicon Browser') }}</label>
                            <input type="file" name="brand_favicon" accept="image/*" class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700">
                        </div>
                    </div>
                </section>

                <section class="card rounded-[2rem] p-6 shadow-sm sm:p-7">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('ui.admin.settings_seo') }}</h2>
                        <p class="text-sm text-slate-500">{{ __('ui.admin.settings_search') }}</p>
                    </div>

                    <div class="mt-5 grid gap-4">
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('SEO Meta Title') }}</label>
                            <input type="text" name="seo_meta_title" value="{{ old('seo_meta_title', $settings['seo.meta_title'] ?? '') }}" class="input mt-2 w-full rounded-2xl px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('SEO Meta Description') }}</label>
                            <textarea name="seo_meta_description" rows="4" class="input mt-2 w-full rounded-2xl px-4 py-3 text-sm">{{ old('seo_meta_description', $settings['seo.meta_description'] ?? '') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="card rounded-[2rem] p-6 shadow-sm sm:p-7">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('ui.admin.settings_contact_social') }}</h2>
                        <p class="text-sm text-slate-500">{{ __('ui.admin.settings_contact') }}</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Nomor WhatsApp') }}</label>
                            <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact.whatsapp'] ?? '') }}" class="input mt-2 w-full rounded-2xl px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Instagram') }}</label>
                            <input type="text" name="social_instagram" value="{{ old('social_instagram', $settings['social.instagram'] ?? '') }}" class="input mt-2 w-full rounded-2xl px-4 py-3 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('TikTok') }}</label>
                            <input type="text" name="social_tiktok" value="{{ old('social_tiktok', $settings['social.tiktok'] ?? '') }}" class="input mt-2 w-full rounded-2xl px-4 py-3 text-sm">
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
                <section class="card rounded-[2rem] p-5 shadow-sm">
                    <div class="space-y-1">
                        <h2 class="text-sm font-semibold text-slate-900">{{ __('ui.admin.settings_preview') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('ui.admin.website_settings') }}</p>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Logo') }}</p>
                            <div class="mt-3 flex min-h-[4.5rem] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4">
                                <img src="{{ $logoUrl }}" alt="{{ __('Pratinjau logo') }}" class="h-10 w-auto max-w-full object-contain">
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Favicon') }}</p>
                            <div class="mt-3 flex min-h-[4.5rem] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4">
                                <img src="{{ $faviconUrl }}" alt="{{ __('Pratinjau favicon') }}" class="h-10 w-10 rounded-xl object-contain">
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card rounded-[2rem] p-5 shadow-sm">
                    <div class="space-y-1">
                        <h2 class="text-sm font-semibold text-slate-900">{{ __('ui.admin.settings_maintenance') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Mode website') }}</p>
                    </div>

                    <label class="mt-4 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm text-slate-700">
                        <input type="hidden" name="maintenance_enabled" value="0">
                        <input type="checkbox" name="maintenance_enabled" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300" {{ old('maintenance_enabled', $settings['site.maintenance_enabled'] ?? '') ? 'checked' : '' }}>
                        <span>{{ __('Aktifkan mode maintenance') }}</span>
                    </label>
                </section>

                <button class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold">
                    {{ __('ui.admin.settings_save') }}
                </button>
            </aside>
        </form>
    </div>
@endsection
