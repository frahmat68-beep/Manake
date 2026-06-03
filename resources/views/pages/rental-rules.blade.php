@extends('layouts.landing')

@section('title', __('ui.rental_rules.page_title'))
@section('meta_description', __('ui.rental_rules.meta_description'))

@push('head')
    <style>
        .rental-rules-page {
            --rules-accent: #D4A843;
            --rules-accent-hover: #E0BA5D;
            --rules-accent-text: #0A0A0B;
            --rules-accent-soft: rgba(212, 168, 67, 0.12);
            --rules-accent-border: rgba(212, 168, 67, 0.28);
            --rules-accent-glow: rgba(212, 168, 67, 0.10);
            --rules-text: #F5F5F5;
            --rules-muted: rgba(255, 255, 255, 0.78);
            --rules-surface: rgba(17, 17, 19, 0.70);
            --rules-surface-soft: rgba(17, 17, 19, 0.50);
            --rules-border: rgba(212, 168, 67, 0.28);
        }

        html[data-theme-resolved="light"] .rental-rules-page {
            --rules-accent: #2563EB;
            --rules-accent-hover: #1D4ED8;
            --rules-accent-text: #FFFFFF;
            --rules-accent-soft: rgba(37, 99, 235, 0.08);
            --rules-accent-border: rgba(37, 99, 235, 0.24);
            --rules-accent-glow: rgba(37, 99, 235, 0.08);
            --rules-text: #0F172A;
            --rules-muted: #4B5563;
            --rules-surface: rgba(255, 255, 255, 0.92);
            --rules-surface-soft: rgba(255, 255, 255, 0.82);
            --rules-border: #CBD5E1;
        }

        /* Spacing & Typography Overrides */
        .rental-rules-page h1 {
            font-size: clamp(2.2rem, 5vw, 3.4rem) !important;
            line-height: 1.15 !important;
        }
        .rental-rules-page p {
            font-size: 15px !important;
            line-height: 1.65 !important;
        }
        .rental-rules-page .rules-card {
            padding: 1.5rem !important;
        }
        .rental-rules-page .rules-card h3 {
            font-size: 15px !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
        }
        .rental-rules-page .rules-card p {
            font-size: 14px !important;
            line-height: 1.65 !important;
        }
        .rental-rules-page .rules-card-strong h3 {
            font-size: 17px !important;
            font-weight: 700 !important;
        }
        .rental-rules-page .rules-card-strong ul li {
            font-size: 15px !important;
            line-height: 1.7 !important;
        }
        .rental-rules-page a,
        .rental-rules-page button {
            font-size: 14.5px !important;
            font-weight: 600 !important;
        }

        .rules-page-bg {
            background-color: #0A0A0B !important;
            color: var(--rules-text) !important;
        }

        html[data-theme-resolved="light"] .rental-rules-page.rules-page-bg {
            background-color: #F8FAFC !important;
        }

        .rules-card {
            background: var(--rules-surface-soft) !important;
            border-color: var(--rules-border) !important;
            color: var(--rules-text) !important;
        }

        .rules-card-strong {
            background: var(--rules-surface) !important;
            border-color: var(--rules-border) !important;
            color: var(--rules-text) !important;
        }

        .rules-title {
            color: var(--rules-text) !important;
        }

        .rules-muted {
            color: var(--rules-muted) !important;
        }

        .rules-accent-text {
            color: var(--rules-accent) !important;
        }

        .rules-accent-bg {
            background-color: var(--rules-accent) !important;
            color: var(--rules-accent-text) !important;
            border-color: var(--rules-accent) !important;
        }

        .rules-accent-bg:hover {
            background-color: var(--rules-accent-hover) !important;
        }

        .rules-accent-soft {
            background-color: var(--rules-accent-soft) !important;
            border-color: var(--rules-accent-border) !important;
            color: var(--rules-accent) !important;
        }

        .rules-accent-dot,
        .rules-accent-bullet {
            color: var(--rules-accent) !important;
        }

        .rules-accent-glow {
            background-color: var(--rules-accent-glow) !important;
        }

        .rules-accent-border-hover:hover {
            border-color: var(--rules-accent-border) !important;
        }

        html[data-theme-resolved="light"] .rental-rules-page .rules-card,
        html[data-theme-resolved="light"] .rental-rules-page .rules-card-strong {
            box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.28);
        }
    </style>
@endpush

@php
    $rulesTitle = setting('copy.rules_page.title', __('ui.rental_rules.title'));
    $rulesSubtitle = setting('copy.rules_page.subtitle', __('ui.rental_rules.subtitle'));
    $rulesOperationalTitle = setting('copy.rules_page.operational_title', __('ui.rental_rules.operational_title'));
    $rulesPrimaryCta = setting('copy.rules_page.cta_primary', __('ui.rental_rules.cta_primary'));
    $rulesSecondaryCta = setting('copy.rules_page.cta_secondary', __('ui.rental_rules.cta_secondary'));

    $contactAddress = setting('footer.address', setting('footer_address', site_content('footer.address', __('app.footer.address_body'))));
    $contactWhatsapp = setting('footer.whatsapp', setting('social_whatsapp', site_content('footer.whatsapp', setting('footer_phone', '+62 812-3456-7890'))));
    $contactEmail = setting('contact.email', setting('footer_email', site_content('contact.email', 'hello@manakerental.id')));
    $contactInstagram = setting('footer.instagram', setting('social_instagram', site_content('footer.instagram', '@manakerental')));
    $contactAddressLines = collect(preg_split('/\R+/', trim((string) $contactAddress)))
        ->map(static fn ($line) => trim((string) $line))
        ->filter()
        ->values();
    $contactAddressTitle = $contactAddressLines->first();
    $contactAddressRest = $contactAddressLines->slice(1);
    $defaultContactMapEmbed = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.1428282443417!2d106.78129727614359!3d-6.3755559936147135!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ef01a88d499d%3A0x15293a04b517553a!2sManake%20-%20Sewa%20HT%2C%20Alat%20Event%20dan%20Film!5e0!3m2!1sen!2sid!4v1771911840986!5m2!1sen!2sid" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
    $contactMapEmbed = trusted_map_embed_iframe($defaultContactMapEmbed, $contactAddress);

    $contactWhatsappEntries = collect(preg_split('/\s*(?:\/|\||,)\s*/', (string) $contactWhatsapp))
        ->map(static fn ($item) => trim((string) $item))
        ->filter()
        ->values();

    $buildWhatsappHref = static function (string $value): ?string {
        $digits = preg_replace('/\D+/', '', $value);
        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . ltrim(substr($digits, 1), '0');
        }

        return 'https://wa.me/' . $digits;
    };

    $instagramHandle = ltrim((string) $contactInstagram, '@');
    $instagramUrl = str_starts_with((string) $contactInstagram, 'http://') || str_starts_with((string) $contactInstagram, 'https://')
        ? (string) $contactInstagram
        : ($instagramHandle ? ('https://instagram.com/' . $instagramHandle) : null);
@endphp

@section('content')
    <section x-data="{ contactModalOpen: false }" class="rental-rules-page rules-page-bg min-h-screen py-12 md:py-16 selection:bg-amber-500/10 selection:text-amber-500">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-12">
            
            <!-- 1. Elegant Header Hero -->
            <div class="rules-card-strong relative overflow-hidden rounded-3xl border p-8 sm:p-12 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.35)] backdrop-blur-md">
                <div class="rules-accent-glow absolute -right-24 -top-24 h-96 w-96 rounded-full blur-3xl" aria-hidden="true"></div>
                <div class="relative z-10 max-w-3xl space-y-4">
                    <h1 class="rules-title text-[clamp(2.2rem,5vw,3.6rem)] font-extrabold leading-[1.05] tracking-tight">
                        {{ $rulesTitle }}
                    </h1>
                    <p class="rules-muted text-sm sm:text-base leading-relaxed max-w-2xl">
                        {{ $rulesSubtitle }}
                    </p>
                </div>
            </div>

            <!-- 2. Step-by-Step Interactive Guide: Alur Penggunaan Web -->
            <div class="space-y-6">
                <div class="text-center md:text-left">
                    <span class="rules-accent-text text-[10px] font-bold uppercase tracking-[0.2em]">{{ __('ui.rental_rules.user_flow_title') }}</span>
                    <h2 class="text-2xl font-bold text-[#E8E8EC] mt-1">{{ __('ui.rental_rules.user_flow_intro') }}</h2>
                </div>
 
                <div class="grid gap-4 sm:grid-cols-4 lg:grid-cols-7">
                    @foreach ([
                        ['num' => '01', 'title' => __('ui.rental_rules.sections.user_flow.step_1_title'), 'desc' => __('ui.rental_rules.sections.user_flow.point_1')],
                        ['num' => '02', 'title' => __('ui.rental_rules.sections.user_flow.step_2_title'), 'desc' => __('ui.rental_rules.sections.user_flow.point_2')],
                        ['num' => '03', 'title' => __('ui.rental_rules.sections.user_flow.step_3_title'), 'desc' => __('ui.rental_rules.sections.user_flow.point_3')],
                        ['num' => '04', 'title' => __('ui.rental_rules.sections.user_flow.step_4_title'), 'desc' => __('ui.rental_rules.sections.user_flow.point_4')],
                        ['num' => '05', 'title' => __('ui.rental_rules.sections.user_flow.step_5_title'), 'desc' => __('ui.rental_rules.sections.user_flow.point_5')],
                        ['num' => '06', 'title' => __('ui.rental_rules.sections.user_flow.step_6_title'), 'desc' => __('ui.rental_rules.sections.user_flow.point_6')],
                        ['num' => '07', 'title' => __('ui.rental_rules.sections.user_flow.step_7_title'), 'desc' => __('ui.rental_rules.sections.user_flow.point_7')],
                    ] as $step)
                        <div class="rules-card relative rounded-2xl border p-5 space-y-3 transition duration-300 rules-accent-border-hover">
                            <div class="rules-accent-text text-lg font-black">{{ $step['num'] }}</div>
                            <h3 class="rules-title text-xs font-bold uppercase tracking-wider">{{ $step['title'] }}</h3>
                            <p class="rules-muted text-[11px] leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. Policy Details Cards Grid (2x2) -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Card 1: Booking & Pembayaran -->
                <article class="rules-card-strong rounded-3xl border p-6 md:p-8 flex items-start gap-4">
                    <div class="rules-accent-soft flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="rules-title text-base sm:text-lg font-bold">{{ __('ui.rental_rules.sections.booking_payment.title') }}</h3>
                        <ul class="rules-muted space-y-1.5 text-xs leading-relaxed">
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.booking_payment.point_1') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.booking_payment.point_2') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.booking_payment.point_3') }}</span></li>
                        </ul>
                    </div>
                </article>

                <!-- Card 2: Ketersediaan Alat -->
                <article class="rules-card-strong rounded-3xl border p-6 md:p-8 flex items-start gap-4">
                    <div class="rules-accent-soft flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="rules-title text-base sm:text-lg font-bold">{{ __('ui.rental_rules.sections.availability.title') }}</h3>
                        <ul class="rules-muted space-y-1.5 text-xs leading-relaxed">
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.availability.point_1') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.availability.point_2') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.availability.point_3') }}</span></li>
                        </ul>
                    </div>
                </article>

                <!-- Card 3: Ubah Jadwal (Reschedule) -->
                <article class="rules-card-strong rounded-3xl border p-6 md:p-8 flex items-start gap-4">
                    <div class="rules-accent-soft flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="rules-title text-base sm:text-lg font-bold">{{ __('ui.rental_rules.sections.reschedule.title') }}</h3>
                        <ul class="rules-muted space-y-1.5 text-xs leading-relaxed">
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.reschedule.point_1') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.reschedule.point_2') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.reschedule.point_3') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.reschedule.point_4') }}</span></li>
                        </ul>
                    </div>
                </article>

                <!-- Card 4: Denda & Tanggung Jawab -->
                <article class="rules-card-strong rounded-3xl border p-6 md:p-8 flex items-start gap-4">
                    <div class="rules-accent-soft flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="rules-title text-base sm:text-lg font-bold">{{ __('ui.rental_rules.sections.penalty.title') }}</h3>
                        <ul class="rules-muted space-y-1.5 text-xs leading-relaxed">
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_1') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_2') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_3') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_4') }}</span></li>
                            <li class="flex items-start gap-2"><span class="rules-accent-bullet font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_5') }}</span></li>
                        </ul>
                    </div>
                </article>
            </div>

            <!-- 4. Operational Footer CTA Section -->
            <article class="rules-card-strong rounded-3xl border p-6 sm:p-8 space-y-4">
                <h2 class="rules-title text-base sm:text-lg font-bold">{{ $rulesOperationalTitle }}</h2>
                <p class="rules-muted text-xs sm:text-sm leading-relaxed max-w-4xl">
                    {{ __('ui.rental_rules.operational_note') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('catalog') }}" class="rules-accent-bg inline-flex rounded-xl px-6 py-3.5 text-sm font-bold transition">
                        {{ $rulesPrimaryCta }}
                    </a>
                    <button
                        type="button"
                        @click="contactModalOpen = true"
                        class="rules-title rules-accent-border-hover inline-flex rounded-xl border px-6 py-3.5 text-sm font-bold transition"
                    >
                        {{ $rulesSecondaryCta }}
                    </button>
                </div>
            </article>

            <!-- Reusable Contact Modal (Kept exactly as original) -->
            <div
                x-cloak
                x-show="contactModalOpen"
                x-transition.opacity
                class="fixed inset-0 z-[80] flex items-center justify-center bg-black/70 p-4"
                @click.self="contactModalOpen = false"
                @keydown.escape.window="contactModalOpen = false"
            >
                <div class="rules-card-strong w-full max-w-3xl rounded-3xl border p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-3 border-b border-white/5 pb-4">
                        <div>
                            <span class="rules-accent-text text-[9px] font-bold uppercase tracking-wider">{{ __('ui.contact.title') }}</span>
                            <h2 class="rules-title text-lg font-bold mt-0.5">{{ __('ui.contact.info_title') }}</h2>
                            <p class="rules-muted text-xs mt-1">{{ __('ui.contact.subtitle') }}</p>
                        </div>
                        <button
                            type="button"
                            @click="contactModalOpen = false"
                            class="rules-title rules-accent-border-hover inline-flex h-8 w-8 items-center justify-center rounded-xl border transition"
                            aria-label="{{ __('ui.actions.close') }}"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                        <div class="rules-card rounded-2xl border p-5 space-y-4">
                            <div class="rules-muted space-y-1.5 text-xs sm:text-sm leading-relaxed">
                                @if ($contactAddressTitle)
                                    <p class="rules-title font-bold">{{ $contactAddressTitle }}</p>
                                @endif
                                @foreach ($contactAddressRest as $addressLine)
                                    <p>{{ $addressLine }}</p>
                                @endforeach
                            </div>

                            <div>
                                <p class="rules-muted text-[10px] font-bold uppercase tracking-wider">{{ __('ui.contact.labels.whatsapp') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse ($contactWhatsappEntries as $whatsappNumber)
                                        @php $whatsappHref = $buildWhatsappHref($whatsappNumber); @endphp
                                        @if ($whatsappHref)
                                            <a
                                                href="{{ $whatsappHref }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="rules-accent-bg inline-flex items-center rounded-xl px-3.5 py-2 text-xs font-bold transition"
                                            >
                                                {{ $whatsappNumber }}
                                            </a>
                                        @else
                                            <span class="rules-card inline-flex items-center rounded-xl px-3.5 py-2 text-xs font-semibold">
                                                {{ $whatsappNumber }}
                                            </span>
                                        @endif
                                    @empty
                                        <span class="rules-muted text-xs">{{ $contactWhatsapp }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rules-muted space-y-2 text-xs pt-2 border-t border-white/5">
                                <p>
                                    <span class="rules-title font-semibold">{{ __('ui.contact.labels.email') }}:</span>
                                    <a href="mailto:{{ $contactEmail }}" class="rules-accent-text break-all font-semibold hover:underline">{{ $contactEmail }}</a>
                                </p>
                                <p>
                                    <span class="rules-title font-semibold">{{ __('ui.contact.labels.instagram') }}:</span>
                                    @if ($instagramUrl)
                                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="rules-accent-text break-all font-semibold hover:underline">{{ $contactInstagram }}</a>
                                    @else
                                        {{ $contactInstagram }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="rules-card rounded-2xl border p-4 flex flex-col justify-between">
                            <p class="rules-title text-xs font-bold">{{ __('ui.contact.map_title') }}</p>
                            <div class="mt-2 flex-1 min-h-[200px] overflow-hidden rounded-xl border border-white/5">
                                @if ($contactMapEmbed)
                                    <div class="h-full w-full [&>iframe]:h-full [&>iframe]:w-full [&>iframe]:border-0">
                                        {!! $contactMapEmbed !!}
                                    </div>
                                @else
                                    <div class="flex h-[200px] items-center justify-center px-3 text-center text-xs rules-muted">
                                        {{ __('ui.contact.map_empty') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
