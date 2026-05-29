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
        --rules-text: #E8E8EC;
        --rules-muted: #A0A0A8;
        --rules-surface: rgba(17, 17, 19, 0.70);
        --rules-surface-soft: rgba(17, 17, 19, 0.50);
        --rules-border: #1A1A1E;
    }

    html[data-theme-resolved="light"] .rental-rules-page {
        --rules-accent: #2563EB;
        --rules-accent-hover: #1D4ED8;
        --rules-accent-text: #FFFFFF;
        --rules-accent-soft: rgba(37, 99, 235, 0.08);
        --rules-accent-border: rgba(37, 99, 235, 0.24);
        --rules-text: #111827;
        --rules-muted: #4B5563;
        --rules-surface: rgba(255, 255, 255, 0.90);
        --rules-surface-soft: rgba(255, 255, 255, 0.78);
        --rules-border: #E5E7EB;
    }

    .rules-accent-text {
        color: var(--rules-accent) !important;
    }

    .rules-accent-bg {
        background-color: var(--rules-accent) !important;
        color: var(--rules-accent-text) !important;
        border-color: var(--rules-accent) !important;
    }

    .rules-accent-soft {
        background-color: var(--rules-accent-soft) !important;
        color: var(--rules-accent) !important;
        border-color: var(--rules-accent-border) !important;
    }

    .rules-accent-dot {
        background-color: var(--rules-accent) !important;
    }

    .rules-card {
        background: var(--rules-surface-soft) !important;
        border-color: var(--rules-border) !important;
    }

    .rules-title {
        color: var(--rules-text) !important;
    }

    .rules-muted {
        color: var(--rules-muted) !important;
    }

    .rules-accent-border-hover:hover {
        border-color: var(--rules-accent-border) !important;
        color: var(--rules-accent) !important;
    }

    html[data-theme-resolved="light"] .rental-rules-page {
        background-color: #F8FAFC !important;
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
    <section x-data="{ contactModalOpen: false }" class="rental-rules-page min-h-screen bg-[#0A0A0B] py-12 md:py-16 text-[#E8E8EC] selection:bg-amber-500/10 selection:text-amber-500">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-12">
            
            <!-- 1. Elegant Header Hero -->
            <div class="relative overflow-hidden rounded-3xl border border-[#1A1A1E] bg-[#111113]/70 p-8 sm:p-12 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.8)] backdrop-blur-md">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-[#D4A843]/5 blur-3xl" aria-hidden="true"></div>
                <div class="relative z-10 max-w-3xl space-y-4">
                    <h1 class="text-[clamp(2.2rem,5vw,3.6rem)] font-extrabold leading-[1.05] tracking-tight text-[#E8E8EC]">
                        {{ $rulesTitle }}
                    </h1>
                    <p class="text-sm sm:text-base leading-relaxed text-[#A0A0A8] max-w-2xl">
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
                <article class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 md:p-8 flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-base sm:text-lg font-bold text-[#E8E8EC]">{{ __('ui.rental_rules.sections.booking_payment.title') }}</h3>
                        <ul class="space-y-1.5 text-xs text-[#A0A0A8] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.booking_payment.point_1') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.booking_payment.point_2') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.booking_payment.point_3') }}</span></li>
                        </ul>
                    </div>
                </article>

                <!-- Card 2: Ketersediaan Alat -->
                <article class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 md:p-8 flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-base sm:text-lg font-bold text-[#E8E8EC]">{{ __('ui.rental_rules.sections.availability.title') }}</h3>
                        <ul class="space-y-1.5 text-xs text-[#A0A0A8] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.availability.point_1') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.availability.point_2') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.availability.point_3') }}</span></li>
                        </ul>
                    </div>
                </article>

                <!-- Card 3: Ubah Jadwal (Reschedule) -->
                <article class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 md:p-8 flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-base sm:text-lg font-bold text-[#E8E8EC]">{{ __('ui.rental_rules.sections.reschedule.title') }}</h3>
                        <ul class="space-y-1.5 text-xs text-[#A0A0A8] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.reschedule.point_1') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.reschedule.point_2') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.reschedule.point_3') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.reschedule.point_4') }}</span></li>
                        </ul>
                    </div>
                </article>

                <!-- Card 4: Denda & Tanggung Jawab -->
                <article class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 md:p-8 flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-base sm:text-lg font-bold text-[#E8E8EC]">{{ __('ui.rental_rules.sections.penalty.title') }}</h3>
                        <ul class="space-y-1.5 text-xs text-[#A0A0A8] leading-relaxed">
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_1') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_2') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_3') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_4') }}</span></li>
                            <li class="flex items-start gap-2"><span class="text-[#D4A843] font-bold">•</span><span>{{ __('ui.rental_rules.sections.penalty.point_5') }}</span></li>
                        </ul>
                    </div>
                </article>
            </div>

            <!-- 4. Operational Footer CTA Section -->
            <article class="rounded-3xl border border-white/10 bg-[#111113]/60 p-6 sm:p-8 space-y-4">
                <h2 class="text-base sm:text-lg font-bold text-[#E8E8EC]">{{ $rulesOperationalTitle }}</h2>
                <p class="text-xs sm:text-sm text-[#A0A0A8] leading-relaxed max-w-4xl">
                    {{ __('ui.rental_rules.operational_note') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-xl bg-[#D4A843] px-5 py-3 text-xs font-bold text-[#0A0A0B] shadow-md hover:bg-[#e0ba5d] transition duration-300">
                        {{ $rulesPrimaryCta }}
                    </a>
                    <button
                        type="button"
                        @click="contactModalOpen = true"
                        class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-[#0A0A0B] px-5 py-3 text-xs font-bold text-[#E8E8EC] hover:bg-[#111113] transition duration-300"
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
                <div class="w-full max-w-3xl rounded-3xl border border-[#1A1A1E] bg-[#111113] p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-3 border-b border-white/5 pb-4">
                        <div>
                            <span class="text-[9px] font-bold uppercase tracking-wider text-[#D4A843]">{{ __('ui.contact.title') }}</span>
                            <h2 class="text-lg font-bold text-[#E8E8EC] mt-0.5">{{ __('ui.contact.info_title') }}</h2>
                            <p class="text-xs text-[#A0A0A8] mt-1">{{ __('ui.contact.subtitle') }}</p>
                        </div>
                        <button
                            type="button"
                            @click="contactModalOpen = false"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-[#1A1A1E] text-[#A0A0A8] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]"
                            aria-label="{{ __('ui.actions.close') }}"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                        <div class="rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B] p-5 space-y-4">
                            <div class="space-y-1.5 text-xs sm:text-sm leading-relaxed text-[#A0A0A8]">
                                @if ($contactAddressTitle)
                                    <p class="font-bold text-[#E8E8EC]">{{ $contactAddressTitle }}</p>
                                @endif
                                @foreach ($contactAddressRest as $addressLine)
                                    <p>{{ $addressLine }}</p>
                                @endforeach
                            </div>

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#A0A0A8]">{{ __('ui.contact.labels.whatsapp') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse ($contactWhatsappEntries as $whatsappNumber)
                                        @php $whatsappHref = $buildWhatsappHref($whatsappNumber); @endphp
                                        @if ($whatsappHref)
                                            <a
                                                href="{{ $whatsappHref }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center rounded-xl bg-[#D4A843] px-3.5 py-2 text-xs font-bold text-[#0A0A0B] transition hover:bg-[#e0ba5d]"
                                            >
                                                {{ $whatsappNumber }}
                                            </a>
                                        @else
                                            <span class="inline-flex items-center rounded-xl bg-[#111113] px-3.5 py-2 text-xs font-semibold text-[#E8E8EC]">
                                                {{ $whatsappNumber }}
                                            </span>
                                        @endif
                                    @empty
                                        <span class="text-xs text-[#A0A0A8]">{{ $contactWhatsapp }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="space-y-2 text-xs text-[#A0A0A8] pt-2 border-t border-white/5">
                                <p>
                                    <span class="font-semibold text-[#E8E8EC]">{{ __('ui.contact.labels.email') }}:</span>
                                    <a href="mailto:{{ $contactEmail }}" class="break-all text-[#D4A843] hover:text-[#e0ba5d] font-semibold">{{ $contactEmail }}</a>
                                </p>
                                <p>
                                    <span class="font-semibold text-[#E8E8EC]">{{ __('ui.contact.labels.instagram') }}:</span>
                                    @if ($instagramUrl)
                                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="text-[#D4A843] hover:text-[#e0ba5d] font-semibold">{{ $contactInstagram }}</a>
                                    @else
                                        {{ $contactInstagram }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-[#1A1A1E] bg-[#111113] p-4 flex flex-col justify-between">
                            <p class="text-xs font-bold text-[#E8E8EC]">{{ __('ui.contact.map_title') }}</p>
                            <div class="mt-2 flex-1 min-h-[200px] overflow-hidden rounded-xl border border-[#1A1A1E]">
                                @if ($contactMapEmbed)
                                    <div class="h-full w-full [&>iframe]:h-full [&>iframe]:w-full [&>iframe]:border-0">
                                        {!! $contactMapEmbed !!}
                                    </div>
                                @else
                                    <div class="flex h-[200px] items-center justify-center px-3 text-center text-xs text-[#A0A0A8]">
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
