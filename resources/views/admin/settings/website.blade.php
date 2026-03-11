@extends('layouts.admin', ['activePage' => 'website'])

@section('title', __('Pengaturan Situs'))
@section('page_title', __('Pengaturan Situs'))

@php
    $logoPath = $settings['brand.logo_path'] ?? null;
    $logoUrl = $logoPath ? site_media_url($logoPath) : site_asset('manake-logo-blue.png');
    $faviconPath = $settings['brand.favicon_path'] ?? null;
    $faviconUrl = $faviconPath ? site_media_url($faviconPath) : null;
@endphp

@section('content')
    <div class="max-w-5xl space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.website.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="card rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Identitas Website') }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ __('Isi hanya data paling krusial yang muncul di website.') }}</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Nama Brand') }}</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $settings['brand.name'] ?? '') }}" class="input mt-2 w-full rounded-xl px-4 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Tagline Brand') }}</label>
                        <input type="text" name="brand_tagline" value="{{ old('brand_tagline', $settings['brand.tagline'] ?? '') }}" class="input mt-2 w-full rounded-xl px-4 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Logo Utama') }}</label>
                        <div class="mt-2 flex items-center gap-4">
                            <img src="{{ $logoUrl }}" alt="{{ __('Pratinjau logo') }}" class="h-12 w-12 rounded-xl border border-slate-200 object-cover bg-white">
                            <input type="file" name="brand_logo" accept="image/*" class="text-sm text-slate-600">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Favicon Browser') }}</label>
                        <div class="mt-2 flex items-center gap-4">
                            @if ($faviconUrl)
                                <img src="{{ $faviconUrl }}" alt="{{ __('Pratinjau favicon') }}" class="h-10 w-10 rounded-lg border border-slate-200 object-cover bg-white">
                            @else
                                <div class="h-10 w-10 rounded-lg border border-dashed border-slate-200 bg-slate-50"></div>
                            @endif
                            <input type="file" name="brand_favicon" accept="image/*" class="text-sm text-slate-600">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Teks Website Penting') }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ __('Bagian ini untuk teks yang mempengaruhi branding dan pencarian.') }}</p>
                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('SEO Meta Title') }}</label>
                        <input type="text" name="seo_meta_title" value="{{ old('seo_meta_title', $settings['seo.meta_title'] ?? '') }}" class="input mt-2 w-full rounded-xl px-4 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('SEO Meta Description') }}</label>
                        <textarea name="seo_meta_description" rows="3" class="input mt-2 w-full rounded-xl px-4 py-2 text-sm">{{ old('seo_meta_description', $settings['seo.meta_description'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Kontak Publik') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Nomor WhatsApp') }}</label>
                        <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact.whatsapp'] ?? '') }}" class="input mt-2 w-full rounded-xl px-4 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Instagram') }}</label>
                        <input type="text" name="social_instagram" value="{{ old('social_instagram', $settings['social.instagram'] ?? '') }}" class="input mt-2 w-full rounded-xl px-4 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('TikTok') }}</label>
                        <input type="text" name="social_tiktok" value="{{ old('social_tiktok', $settings['social.tiktok'] ?? '') }}" class="input mt-2 w-full rounded-xl px-4 py-2 text-sm">
                    </div>
                </div>
            </div>

            <button class="btn-primary inline-flex items-center justify-center rounded-xl px-6 py-2.5 text-sm font-semibold transition">
                {{ __('Simpan Teks & Identitas Website') }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.website.update') }}" class="card rounded-2xl p-6 shadow-sm">
            @csrf
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Mode Maintenance') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ __('Kontrol ini berdiri sendiri dan diletakkan terpisah dari pengaturan teks website.') }}</p>
            <input type="hidden" name="maintenance_enabled" value="0">
            <label class="mt-4 flex items-center gap-3 text-sm text-slate-700">
                <input type="checkbox" name="maintenance_enabled" value="1" class="h-4 w-4 rounded border-slate-300" {{ old('maintenance_enabled', $settings['site.maintenance_enabled'] ?? '') ? 'checked' : '' }}>
                {{ __('Aktifkan mode maintenance') }}
            </label>
            <button class="mt-4 inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                {{ __('Update Maintenance') }}
            </button>
        </form>
    </div>
@endsection
