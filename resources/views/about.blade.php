@extends('layouts.landing')

@section('title', __('app.footer.quick_about'))

@php
    $aboutText = setting('footer.about', setting('footer_description', site_content('footer.about', __('app.footer.about_body'))));
    $contactWhatsapp = setting('footer.whatsapp', setting('social_whatsapp', site_content('footer.whatsapp', setting('footer_phone', '+62 812-3456-7890'))));
    $contactEmail = setting('contact.email', setting('footer_email', site_content('contact.email', 'hello@manakerental.id')));
@endphp

@section('content')
    <section class="mk-section">
        <div class="mk-container space-y-6">
            <header class="mk-card p-6 sm:p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">{{ __('app.footer.quick_about') }}</p>
                <h1 class="mk-title-section mt-3">{{ setting('brand.name', 'Manake') }}</h1>
                <p class="mk-copy mt-4 leading-relaxed">{{ $aboutText }}</p>
            </header>

            <div class="grid gap-6 lg:grid-cols-2">
                <article class="mk-card p-6">
                    <h2 class="text-lg font-bold text-slate-900">{{ __('Yang Perlu Diisi di CMS') }}</h2>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li>{{ __('1. Profil singkat bisnis: fokus layanan dan area cakupan.') }}</li>
                        <li>{{ __('2. Kontak aktif: WhatsApp, email, dan jam operasional.') }}</li>
                        <li>{{ __('3. Alamat lengkap + embed maps agar titik lokasi jelas.') }}</li>
                        <li>{{ __('4. Aturan sewa yang wajib dibaca sebelum checkout.') }}</li>
                        <li>{{ __('5. Call-to-action utama: cara booking, pembayaran, dan pengembalian.') }}</li>
                    </ul>
                </article>

                <article class="mk-card p-6">
                    <h2 class="text-lg font-bold text-slate-900">{{ __('Kontak Cepat') }}</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <p><span class="font-bold text-slate-900">WhatsApp:</span> {{ $contactWhatsapp }}</p>
                        <p><span class="font-bold text-slate-900">Email:</span> {{ $contactEmail }}</p>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('contact') }}" class="mk-button-secondary py-2 px-4 text-sm font-bold">
                            {{ __('app.footer.quick_contact') }}
                        </a>
                        <a href="{{ route('catalog') }}" class="mk-button-primary py-2 px-4 text-sm font-bold">
                            {{ __('app.footer.quick_catalog') }}
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>
@endsection

