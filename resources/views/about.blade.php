@extends('layouts.landing')

@section('title', __('app.footer.quick_about'))

@php
    $aboutPage = __('ui.about_page');
    $aboutText = setting('footer.about', setting('footer_description', site_content('footer.about', __('ui.about_page.description_fallback'))));
    $contactWhatsapp = setting('footer.whatsapp', setting('social_whatsapp', site_content('footer.whatsapp', setting('footer_phone', '+62 812-3456-7890'))));
    $contactEmail = setting('contact.email', setting('footer_email', site_content('contact.email', 'hello@manakerental.id')));
@endphp

@push('head')
    <style>
        .about-page {
            --about-accent: #D4A843;
            --about-accent-hover: #E0BA5D;
            --about-accent-text: #0A0A0B;
            --about-accent-soft: rgba(212, 168, 67, 0.12);
            --about-accent-border: rgba(212, 168, 67, 0.30);
            --about-text: #E8E8EC;
            --about-muted: #A0A0A8;
            --about-surface: rgba(17, 17, 19, 0.70);
            --about-surface-soft: rgba(17, 17, 19, 0.40);
            --about-border: rgba(255, 255, 255, 0.10);
        }

        html[data-theme-resolved="light"] .about-page {
            --about-accent: #2563EB;
            --about-accent-hover: #1D4ED8;
            --about-accent-text: #FFFFFF;
            --about-accent-soft: rgba(37, 99, 235, 0.08);
            --about-accent-border: rgba(37, 99, 235, 0.22);
            --about-text: #111827;
            --about-muted: #4B5563;
            --about-surface: rgba(255, 255, 255, 0.90);
            --about-surface-soft: rgba(255, 255, 255, 0.78);
            --about-border: #E5E7EB;
        }

        .about-card {
            border-color: var(--about-border) !important;
            background: var(--about-surface) !important;
            color: var(--about-text) !important;
        }

        .about-card-soft {
            border-color: var(--about-border) !important;
            background: var(--about-surface-soft) !important;
            color: var(--about-text) !important;
        }

        .about-title {
            color: var(--about-text) !important;
        }

        .about-muted {
            color: var(--about-muted) !important;
        }

        .about-accent-text {
            color: var(--about-accent) !important;
        }

        .about-accent-bg {
            background-color: var(--about-accent) !important;
            color: var(--about-accent-text) !important;
            border-color: var(--about-accent) !important;
        }

        .about-accent-bg:hover {
            background-color: var(--about-accent-hover) !important;
        }

        .about-accent-soft {
            background-color: var(--about-accent-soft) !important;
            color: var(--about-accent) !important;
        }

        .about-accent-dot {
            background-color: var(--about-accent) !important;
        }

        .about-accent-border-hover {
            border-color: var(--about-border) !important;
        }

        .about-accent-border-hover:hover {
            border-color: var(--about-accent) !important;
            color: var(--about-accent) !important;
        }

        html[data-theme-resolved="light"] .about-page {
            background-color: #F8FAFC !important;
        }

        /* Custom entrance transitions */
        .about-enter {
            animation: about-enter 520ms ease-out both;
        }

        .about-card-in {
            animation: about-card-in 520ms ease-out both;
        }

        @keyframes about-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes about-card-in {
            from {
                opacity: 0;
                transform: translateY(14px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .about-enter,
            .about-card-in {
                animation: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="about-page min-h-screen bg-[#0A0A0B] text-[#E8E8EC] py-6 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 space-y-12">
            
            <!-- 1. Hero Section -->
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-[1.3fr,0.7fr] items-stretch about-enter">
                <!-- Left Content Card -->
                <div class="about-card rounded-3xl border p-6 sm:p-8 lg:p-10 shadow-2xl flex flex-col justify-between">
                    <div>
                        <p class="about-accent-text text-sm font-bold tracking-widest uppercase mb-3">{{ $aboutPage['kicker'] }}</p>
                        <h1 class="about-title text-4xl font-extrabold tracking-tight sm:text-5xl leading-tight">
                            {{ $aboutPage['title'] }}
                        </h1>
                        <p class="about-muted mt-4 text-base sm:text-lg leading-relaxed max-w-2xl">
                            {{ $aboutText }}
                        </p>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('catalog') }}" class="about-accent-bg rounded-xl px-6 py-3.5 text-base font-bold flex items-center gap-2 transition">
                            {{ $aboutPage['catalog_button'] }}
                        </a>
                        <a href="{{ route('availability.board') }}" class="rounded-xl border px-6 py-3.5 text-base font-bold transition about-accent-border-hover about-title">
                            {{ $aboutPage['availability_button'] }}
                        </a>
                    </div>
                </div>

                <!-- Right Visual Summary Card -->
                <div class="about-card-soft rounded-3xl border p-6 sm:p-8 shadow-xl flex flex-col justify-between">
                    <div>
                        <h2 class="about-title text-xl font-bold">{{ $aboutPage['summary_title'] }}</h2>
                        <p class="about-muted mt-2 text-sm leading-relaxed">{{ $aboutPage['summary_desc'] }}</p>
                    </div>
                    <div class="space-y-3 mt-6">
                        <div class="flex items-center gap-3 p-3 bg-[#0A0A0B]/40 rounded-2xl border border-white/5 shadow-sm">
                            <span class="about-accent-dot h-2.5 w-2.5 rounded-full"></span>
                            <span class="about-title text-sm font-semibold">{{ $aboutPage['summary_item_1'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-[#0A0A0B]/40 rounded-2xl border border-white/5 shadow-sm">
                            <span class="about-accent-dot h-2.5 w-2.5 rounded-full"></span>
                            <span class="about-title text-sm font-semibold">{{ $aboutPage['summary_item_2'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-[#0A0A0B]/40 rounded-2xl border border-white/5 shadow-sm">
                            <span class="about-accent-dot h-2.5 w-2.5 rounded-full"></span>
                            <span class="about-title text-sm font-semibold">{{ $aboutPage['summary_item_3'] }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. What Manake Does Section -->
            <section class="space-y-6">
                <div class="border-b border-[#1A1A1E] pb-3">
                    <h2 class="about-title text-2xl font-bold tracking-tight">{{ $aboutPage['what_title'] }}</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <article class="about-card-soft rounded-3xl border p-6 sm:p-8 shadow-xl flex flex-col justify-between about-card-in">
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl about-accent-soft">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-.547.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="about-title text-lg font-bold">{{ $aboutPage['what_card_1_title'] }}</h3>
                                <p class="about-muted mt-2 text-sm leading-relaxed">
                                    {{ $aboutPage['what_card_1_desc'] }}
                                </p>
                            </div>
                        </div>
                    </article>
                    <article class="about-card-soft rounded-3xl border p-6 sm:p-8 shadow-xl flex flex-col justify-between about-card-in">
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl about-accent-soft">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="about-title text-lg font-bold">{{ $aboutPage['what_card_2_title'] }}</h3>
                                <p class="about-muted mt-2 text-sm leading-relaxed">
                                    {{ $aboutPage['what_card_2_desc'] }}
                                </p>
                            </div>
                        </div>
                    </article>
                    <article class="about-card-soft rounded-3xl border p-6 sm:p-8 shadow-xl flex flex-col justify-between about-card-in">
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl about-accent-soft">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="about-title text-lg font-bold">{{ $aboutPage['what_card_3_title'] }}</h3>
                                <p class="about-muted mt-2 text-sm leading-relaxed">
                                    {{ $aboutPage['what_card_3_desc'] }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- 3. Why Choose Us Section -->
            <section class="space-y-6">
                <div class="border-b border-[#1A1A1E] pb-3">
                    <h2 class="about-title text-2xl font-bold tracking-tight">{{ $aboutPage['why_title'] }}</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="about-card-soft rounded-3xl border p-6 shadow-xl about-card-in">
                        <h3 class="about-title text-base font-bold">{{ $aboutPage['why_card_1_title'] }}</h3>
                        <p class="about-muted mt-2 text-sm leading-relaxed">{{ $aboutPage['why_card_1_desc'] }}</p>
                    </div>
                    <div class="about-card-soft rounded-3xl border p-6 shadow-xl about-card-in">
                        <h3 class="about-title text-base font-bold">{{ $aboutPage['why_card_2_title'] }}</h3>
                        <p class="about-muted mt-2 text-sm leading-relaxed">{{ $aboutPage['why_card_2_desc'] }}</p>
                    </div>
                    <div class="about-card-soft rounded-3xl border p-6 shadow-xl about-card-in">
                        <h3 class="about-title text-base font-bold">{{ $aboutPage['why_card_3_title'] }}</h3>
                        <p class="about-muted mt-2 text-sm leading-relaxed">{{ $aboutPage['why_card_3_desc'] }}</p>
                    </div>
                    <div class="about-card-soft rounded-3xl border p-6 shadow-xl about-card-in">
                        <h3 class="about-title text-base font-bold">{{ $aboutPage['why_card_4_title'] }}</h3>
                        <p class="about-muted mt-2 text-sm leading-relaxed">{{ $aboutPage['why_card_4_desc'] }}</p>
                    </div>
                    <div class="about-card-soft rounded-3xl border p-6 shadow-xl about-card-in md:col-span-2 lg:col-span-1">
                        <h3 class="about-title text-base font-bold">{{ $aboutPage['why_card_5_title'] }}</h3>
                        <p class="about-muted mt-2 text-sm leading-relaxed">{{ $aboutPage['why_card_5_desc'] }}</p>
                    </div>
                </div>
            </section>

            <!-- 4. Rental Flow Preview Section -->
            <section class="space-y-6">
                <div class="border-b border-[#1A1A1E] pb-3 flex flex-col md:flex-row md:items-baseline md:justify-between gap-2">
                    <h2 class="about-title text-2xl font-bold tracking-tight">{{ $aboutPage['flow_title'] }}</h2>
                    <p class="about-muted text-xs font-bold uppercase tracking-wider">
                        {{ $aboutPage['flow_note'] }}
                    </p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="about-card-soft rounded-2xl border p-5 shadow-sm flex flex-col items-start gap-4">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl about-accent-soft text-sm font-black">1</span>
                        <div>
                            <h4 class="about-title text-sm font-bold">{{ $aboutPage['flow_step_1_title'] }}</h4>
                            <p class="about-muted mt-1.5 text-xs leading-normal">{{ $aboutPage['flow_step_1_desc'] }}</p>
                        </div>
                    </div>
                    <div class="about-card-soft rounded-2xl border p-5 shadow-sm flex flex-col items-start gap-4">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl about-accent-soft text-sm font-black">2</span>
                        <div>
                            <h4 class="about-title text-sm font-bold">{{ $aboutPage['flow_step_2_title'] }}</h4>
                            <p class="about-muted mt-1.5 text-xs leading-normal">{{ $aboutPage['flow_step_2_desc'] }}</p>
                        </div>
                    </div>
                    <div class="about-card-soft rounded-2xl border p-5 shadow-sm flex flex-col items-start gap-4">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl about-accent-soft text-sm font-black">3</span>
                        <div>
                            <h4 class="about-title text-sm font-bold">{{ $aboutPage['flow_step_3_title'] }}</h4>
                            <p class="about-muted mt-1.5 text-xs leading-normal">{{ $aboutPage['flow_step_3_desc'] }}</p>
                        </div>
                    </div>
                    <div class="about-card-soft rounded-2xl border p-5 shadow-sm flex flex-col items-start gap-4">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl about-accent-soft text-sm font-black">4</span>
                        <div>
                            <h4 class="about-title text-sm font-bold">{{ $aboutPage['flow_step_4_title'] }}</h4>
                            <p class="about-muted mt-1.5 text-xs leading-normal">{{ $aboutPage['flow_step_4_desc'] }}</p>
                        </div>
                    </div>
                    <div class="about-card-soft rounded-2xl border p-5 shadow-sm flex flex-col items-start gap-4 col-span-2 md:col-span-1">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl about-accent-soft text-sm font-black">5</span>
                        <div>
                            <h4 class="about-title text-sm font-bold">{{ $aboutPage['flow_step_5_title'] }}</h4>
                            <p class="about-muted mt-1.5 text-xs leading-normal">{{ $aboutPage['flow_step_5_desc'] }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. Who It's For Section -->
            <section class="space-y-6">
                <div class="border-b border-[#1A1A1E] pb-3">
                    <h2 class="about-title text-2xl font-bold tracking-tight">{{ $aboutPage['audience_title'] }}</h2>
                </div>
                <div class="flex flex-wrap gap-2.5">
                    <span class="rounded-full border px-4 py-2.5 text-sm font-bold about-card-soft about-muted">{{ $aboutPage['audience_1'] }}</span>
                    <span class="rounded-full border px-4 py-2.5 text-sm font-bold about-card-soft about-muted">{{ $aboutPage['audience_2'] }}</span>
                    <span class="rounded-full border px-4 py-2.5 text-sm font-bold about-card-soft about-muted">{{ $aboutPage['audience_3'] }}</span>
                    <span class="rounded-full border px-4 py-2.5 text-sm font-bold about-card-soft about-muted">{{ $aboutPage['audience_4'] }}</span>
                    <span class="rounded-full border px-4 py-2.5 text-sm font-bold about-card-soft about-muted">{{ $aboutPage['audience_5'] }}</span>
                    <span class="rounded-full border px-4 py-2.5 text-sm font-bold about-card-soft about-muted">{{ $aboutPage['audience_6'] }}</span>
                </div>
            </section>

            <!-- 6. Brand Final CTA Card -->
            <section class="about-card rounded-3xl border p-6 sm:p-8 lg:p-10 shadow-2xl relative overflow-hidden text-center max-w-4xl mx-auto">
                <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-current opacity-10 blur-[60px] about-accent-text"></div>
                <div class="relative space-y-6">
                    <h2 class="about-title text-3xl font-extrabold">{{ $aboutPage['cta_title'] }}</h2>
                    <p class="about-muted text-base leading-relaxed max-w-2xl mx-auto">
                        {{ $aboutPage['cta_desc'] }}
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                        <a href="{{ route('contact') }}" class="about-accent-bg rounded-xl px-6 py-3.5 text-base font-bold flex items-center justify-center gap-2 transition">
                            {{ $aboutPage['cta_contact'] }}
                        </a>
                        <a href="{{ Route::has('rental.rules') ? route('rental.rules') : url('/rental-rules') }}" class="about-accent-border-hover about-title border rounded-xl px-6 py-3.5 text-base font-bold flex items-center justify-center gap-2 transition">
                            {{ $aboutPage['cta_rules'] }}
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
