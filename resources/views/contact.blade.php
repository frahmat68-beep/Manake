@extends('layouts.landing')

@section('title', __('ui.contact.page_title'))

@push('head')
    <style>
        .contact-page {
            --contact-accent: #D4A843;
            --contact-accent-hover: #E0BA5D;
            --contact-accent-text: #0A0A0B;
            --contact-accent-soft: rgba(212, 168, 67, 0.10);
            --contact-accent-border: rgba(212, 168, 67, 0.24);
            --contact-accent-glow: rgba(212, 168, 67, 0.08);
            --contact-text: #F5F5F5;
            --contact-muted: rgba(255, 255, 255, 0.78);
            --contact-surface: rgba(17, 17, 19, 0.70);
            --contact-surface-soft: rgba(17, 17, 19, 0.44);
            --contact-surface-strong: #0A0A0B;
            --contact-border: rgba(212, 168, 67, 0.28);
        }

        html[data-theme-resolved="light"] .contact-page {
            --contact-accent: #2563EB;
            --contact-accent-hover: #1D4ED8;
            --contact-accent-text: #FFFFFF;
            --contact-accent-soft: rgba(37, 99, 235, 0.08);
            --contact-accent-border: rgba(37, 99, 235, 0.22);
            --contact-accent-glow: rgba(37, 99, 235, 0.08);
            --contact-text: #0F172A;
            --contact-muted: #4B5563;
            --contact-surface: rgba(255, 255, 255, 0.92);
            --contact-surface-soft: rgba(255, 255, 255, 0.80);
            --contact-surface-strong: #FFFFFF;
            --contact-border: #CBD5E1;
        }

        /* Spacing & Typography Overrides */
        .contact-page h1 {
            font-size: clamp(2.2rem, 5vw, 3.2rem) !important;
            line-height: 1.15 !important;
        }
        .contact-page p {
            font-size: 15px !important;
            line-height: 1.65 !important;
        }
        .contact-page ul {
            font-size: 15px !important;
        }
        .contact-page ul li span {
            font-size: 15px !important;
            line-height: 1.7 !important;
        }
        .contact-page a, .contact-page button {
            font-size: 14.5px !important;
            font-weight: 600 !important;
        }
        .contact-page .contact-card h3,
        .contact-page .contact-card-soft h3 {
            font-size: 17px !important;
            font-weight: 700 !important;
        }
        .contact-page .contact-card p,
        .contact-page .contact-card-soft p {
            font-size: 14.5px !important;
            line-height: 1.65 !important;
        }
        .contact-page .contact-wa-card a,
        .contact-page .contact-card a {
            font-size: 15px !important;
            font-weight: 600 !important;
        }
        .contact-page .contact-card h4 {
            font-size: 16px !important;
            font-weight: 700 !important;
        }
        .contact-page .contact-card .contact-muted p {
            font-size: 15px !important;
            line-height: 1.65 !important;
        }
        .contact-page .contact-card ol li {
            font-size: 14.5px !important;
            line-height: 1.7 !important;
        }
        .contact-page .contact-card-soft h4 {
            font-size: 14.5px !important;
            font-weight: 700 !important;
        }
        .contact-page .contact-card-soft p {
            font-size: 14.5px !important;
            line-height: 1.65 !important;
        }

        .contact-page-bg {
            background-color: #0A0A0B !important;
            color: var(--contact-text) !important;
        }

        html[data-theme-resolved="light"] .contact-page.contact-page-bg {
            background-color: #F8FAFC !important;
        }

        .contact-card {
            background: var(--contact-surface) !important;
            border-color: var(--contact-border) !important;
            color: var(--contact-text) !important;
        }

        .contact-card-soft {
            background: var(--contact-surface-soft) !important;
            border-color: var(--contact-border) !important;
            color: var(--contact-text) !important;
        }

        .contact-inner-surface {
            background: var(--contact-surface-strong) !important;
            border-color: var(--contact-border) !important;
        }

        .contact-title {
            color: var(--contact-text) !important;
        }

        .contact-muted {
            color: var(--contact-muted) !important;
        }

        .contact-accent-text {
            color: var(--contact-accent) !important;
        }

        .contact-accent-bg {
            background-color: var(--contact-accent) !important;
            color: var(--contact-accent-text) !important;
            border-color: var(--contact-accent) !important;
        }

        .contact-accent-bg:hover {
            background-color: var(--contact-accent-hover) !important;
        }

        .contact-accent-soft {
            background-color: var(--contact-accent-soft) !important;
            border-color: var(--contact-accent-border) !important;
            color: var(--contact-accent) !important;
        }

        .contact-accent-glow {
            background-color: var(--contact-accent-glow) !important;
        }

        .contact-accent-border-hover:hover {
            border-color: var(--contact-accent-border) !important;
            color: var(--contact-accent) !important;
        }

        .contact-accent-number {
            color: var(--contact-accent) !important;
        }

        .contact-icon-box {
            background-color: var(--contact-accent-soft) !important;
            border-color: var(--contact-accent-border) !important;
            color: var(--contact-accent) !important;
        }

        .contact-wa-btn {
            background-color: var(--contact-accent) !important;
            color: var(--contact-accent-text) !important;
        }

        .contact-wa-btn:hover {
            background-color: var(--contact-accent-hover) !important;
        }

        .contact-wa-card {
            background-color: var(--contact-accent-soft) !important;
            border-color: var(--contact-accent-border) !important;
        }

        .contact-wa-card:hover {
            border-color: var(--contact-accent-border) !important;
            filter: brightness(1.05);
        }

        .contact-cta-strip {
            background: var(--contact-surface) !important;
            border-color: var(--contact-border) !important;
        }

        html[data-theme-resolved="light"] .contact-page .contact-card,
        html[data-theme-resolved="light"] .contact-page .contact-card-soft {
            box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.28);
        }

        html[data-theme-resolved="light"] .contact-page .contact-cta-strip {
            box-shadow: 0 10px 40px -20px rgba(15, 23, 42, 0.18);
        }
    </style>
@endpush

@section('content')
    @php
        $contactCopy = __('ui.contact');
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
        $rawMapEmbed = setting('contact.map_embed') ?: $defaultContactMapEmbed;
        $contactMapEmbed = trusted_map_embed_iframe($rawMapEmbed, $contactAddress);

        $contactWhatsappEntries = collect(preg_split('/\s*(?:\/|\|,)\s*/', (string) $contactWhatsapp))
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

        $firstWhatsappHref = $contactWhatsappEntries->isNotEmpty() ? $buildWhatsappHref((string) $contactWhatsappEntries->first()) : '#';
    @endphp

    <section class="contact-page contact-page-bg min-h-screen py-12 md:py-16 selection:bg-amber-500/10 selection:text-amber-500">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-10">
            
            <!-- 1. Hero Section -->
            <div class="contact-card relative overflow-hidden rounded-3xl border p-6 sm:p-8 lg:p-10 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.35)] backdrop-blur-md">
                <div class="contact-accent-glow absolute -right-24 -top-24 h-96 w-96 rounded-full blur-3xl" aria-hidden="true"></div>
                
                <div class="relative z-10 grid gap-8 lg:grid-cols-[1.15fr_0.85fr] items-center">
                    <!-- Left Side Info -->
                    <div class="space-y-4">
                        <h1 class="contact-title text-[clamp(2rem,4.5vw,3rem)] font-extrabold leading-[1.1] tracking-tight">
                            {{ $contactCopy['hero_title_prefix'] }} <span class="contact-accent-text">{{ $contactCopy['hero_title_highlight'] }}</span>
                        </h1>
                        
                        <p class="contact-muted text-sm sm:text-base leading-relaxed max-w-2xl">
                            {{ $contactCopy['hero_subtitle'] }}
                        </p>
                        
                        <div class="flex flex-wrap gap-3 pt-2">
                            @if ($contactWhatsappEntries->isNotEmpty() && $firstWhatsappHref !== '#')
                                <a href="{{ $firstWhatsappHref }}" target="_blank" rel="noopener noreferrer"
                                   class="contact-accent-bg inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-xs font-bold shadow-[0_16px_30px_-15px_rgba(0,0,0,0.25)] transition duration-300 hover:-translate-y-0.5"
                                   aria-label="{{ $contactCopy['whatsapp_aria'] }}">
                                    {{ $contactCopy['whatsapp_button'] }}
                                </a>
                            @endif
                            <a href="{{ route('catalog') }}"
                               class="contact-title contact-inner-surface contact-accent-border-hover inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-3 text-xs font-semibold transition duration-300">
                                {{ $contactCopy['catalog_button'] }}
                            </a>
                        </div>
                    </div>
 
                    <!-- Right Side: Prepare Rental Details Checklist -->
                    <div class="contact-inner-surface rounded-2xl border p-5 sm:p-6 space-y-3.5">
                        <h3 class="contact-title text-sm font-bold uppercase tracking-wider">{{ $contactCopy['prepare_title'] }}</h3>
                        
                        <ul class="grid gap-2 text-xs">
                            <li class="flex items-center gap-2.5">
                                <span class="contact-accent-soft flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full border" aria-hidden="true">✓</span>
                                <span class="contact-muted">{{ $contactCopy['prepare_item_1'] }}</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="contact-accent-soft flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full border" aria-hidden="true">✓</span>
                                <span class="contact-muted">{{ $contactCopy['prepare_item_2'] }}</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="contact-accent-soft flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full border" aria-hidden="true">✓</span>
                                <span class="contact-muted">{{ $contactCopy['prepare_item_3'] }}</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="contact-accent-soft flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full border" aria-hidden="true">✓</span>
                                <span class="contact-muted">{{ $contactCopy['prepare_item_4'] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 2. Contact Channel Cards -->
            <div class="grid gap-5 md:grid-cols-3">
                <!-- Card A: WhatsApp -->
                <div class="contact-wa-card rounded-3xl border p-6 flex flex-col justify-between h-full transition duration-300">
                    <div class="flex items-start gap-4">
                        <div class="contact-icon-box flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.62.962 3.22 1.463 4.832 1.464 5.489 0 9.954-4.468 9.957-9.96.002-2.66-1.023-5.158-2.883-7.02C16.69 1.777 14.2 1.752 11.998 1.752c-5.49 0-9.954 4.469-9.957 9.961-.002 1.814.49 3.586 1.42 5.178l-.99 3.616 3.73-.978zM17.06 14.54c-.274-.138-1.62-.8-1.874-.892-.252-.093-.437-.138-.62.138-.184.276-.713.892-.873 1.077-.16.184-.32.207-.593.07-.273-.138-1.155-.426-2.2-1.358-.813-.725-1.36-1.62-1.52-1.896-.16-.276-.017-.425.12-.562.123-.123.273-.32.41-.482.137-.16.183-.275.273-.458.09-.184.046-.344-.022-.482-.068-.138-.62-1.492-.85-2.043-.224-.54-.45-.466-.62-.475-.16-.008-.344-.01-.527-.01-.184 0-.482.07-.733.344-.252.276-.96.938-.96 2.29 0 1.35 1.054 2.656 1.202 2.84.148.184 2.075 3.17 5.027 4.444.702.302 1.25.483 1.677.62.705.224 1.346.192 1.854.116.565-.084 1.62-.662 1.848-1.298.227-.636.227-1.182.16-1.297-.07-.115-.25-.184-.523-.322z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="contact-title text-base sm:text-lg font-bold">{{ $contactCopy['whatsapp_title'] }}</h3>
                            <p class="contact-muted text-xs">{{ $contactCopy['whatsapp_desc'] }}</p>
                            <div class="flex flex-col gap-2 pt-2">
                                @forelse ($contactWhatsappEntries as $whatsappNumber)
                                    @php $whatsappHref = $buildWhatsappHref($whatsappNumber); @endphp
                                    @if ($whatsappHref)
                                        <a href="{{ $whatsappHref }}" target="_blank" rel="noopener noreferrer"
                                           class="contact-wa-btn inline-flex w-full items-center justify-center rounded-xl py-2 text-xs font-bold transition duration-300"
                                           aria-label="{{ __('ui.contact.whatsapp_aria') }}">
                                            {{ $whatsappNumber }}
                                        </a>
                                    @else
                                        <span class="contact-card inline-flex items-center justify-center rounded-xl py-2 text-xs font-semibold">
                                            {{ $whatsappNumber }}
                                        </span>
                                    @endif
                                @empty
                                    <span class="contact-muted text-xs">{{ $contactWhatsapp }}</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card B: Email -->
                <div class="contact-card rounded-3xl border p-6 flex flex-col justify-between h-full transition duration-300 contact-accent-border-hover">
                    <div class="flex items-start gap-4">
                        <div class="contact-card flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border contact-muted" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="contact-title text-base sm:text-lg font-bold">{{ $contactCopy['email_title'] }}</h3>
                            <p class="contact-muted text-xs">{{ $contactCopy['email_desc'] }}</p>
                            <div class="pt-2">
                                <a href="mailto:{{ $contactEmail }}"
                                   class="contact-card-soft contact-accent-border-hover inline-flex w-full items-center justify-center rounded-xl border py-2.5 text-xs font-semibold transition duration-300 break-all px-2 text-center"
                                   aria-label="{{ __('ui.contact.email_aria', ['email' => $contactEmail]) }}">
                                    {{ $contactEmail }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card C: Instagram -->
                <div class="contact-card rounded-3xl border p-6 flex flex-col justify-between h-full transition duration-300 contact-accent-border-hover">
                    <div class="flex items-start gap-4">
                        <div class="contact-card flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border contact-muted" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="contact-title text-base sm:text-lg font-bold">{{ $contactCopy['instagram_title'] }}</h3>
                            <p class="contact-muted text-xs">{{ $contactCopy['instagram_desc'] }}</p>
                            <div class="pt-2">
                                @if ($instagramUrl)
                                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer"
                                       class="contact-card-soft contact-accent-border-hover inline-flex w-full items-center justify-center rounded-xl border py-2.5 text-xs font-semibold transition duration-300 text-center px-2"
                                       aria-label="{{ $contactCopy['instagram_aria'] }}">
                                        {{ $contactInstagram }}
                                    </a>
                                @else
                                    <span class="contact-card-soft inline-flex w-full items-center justify-center rounded-xl border py-2.5 text-xs font-semibold text-center px-2">
                                        {{ $contactInstagram }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Studio Visit & 4. Map Section -->
            <div class="grid gap-6 lg:grid-cols-12 items-stretch">
                <!-- Left: Pickup Point (lg:col-span-5) -->
                <div class="contact-card rounded-3xl border p-6 md:p-8 flex flex-col justify-between h-full lg:col-span-5">
                    <div class="space-y-6">
                        <div class="flex items-center gap-4 border-b pb-4" style="border-color: var(--contact-border)">
                            <div class="contact-icon-box flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="contact-title text-base sm:text-lg font-bold">{{ $contactCopy['pickup_title'] }}</h3>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h4 class="contact-title text-sm font-bold">{{ $contactAddressTitle ?: $contactCopy['pickup_default_title'] }}</h4>
                                <div class="mt-2 text-xs sm:text-sm leading-relaxed contact-muted">
                                    @foreach ($contactAddressRest as $addressLine)
                                        <p>{{ $addressLine }}</p>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Practical Notes -->
                            <div class="border-t pt-4 space-y-2.5" style="border-color: var(--contact-border)">
                                <h5 class="contact-accent-text text-xs font-bold uppercase tracking-wider">{{ $contactCopy['practical_notes_title'] }}</h5>
                                <ol class="space-y-1.5 text-xs contact-muted">
                                    <li class="flex items-start gap-2">
                                        <span class="contact-accent-number font-bold">1.</span>
                                        <span>{{ $contactCopy['practical_note_1'] }}</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="contact-accent-number font-bold">2.</span>
                                        <span>{{ $contactCopy['practical_note_2'] }}</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="contact-accent-number font-bold">3.</span>
                                        <span>{{ $contactCopy['practical_note_3'] }}</span>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Interactive Google Map (lg:col-span-7) -->
                <div class="contact-card rounded-3xl border p-6 md:p-8 flex flex-col justify-between h-full min-h-[420px] lg:col-span-7">
                    <div class="space-y-1">
                        <h3 class="contact-title text-lg font-bold">{{ $contactCopy['map_title'] }}</h3>
                        <p class="contact-muted text-xs">{{ $contactCopy['map_desc'] }}</p>
                    </div>

                    <div class="mt-4 flex-1 min-h-[300px] overflow-hidden rounded-2xl border" style="background: var(--contact-surface-strong); border-color: var(--contact-border); position: relative;">
                        @if ($contactMapEmbed)
                            <div class="absolute inset-0 [&>iframe]:h-full [&>iframe]:w-full [&>iframe]:border-0">
                                {!! $contactMapEmbed !!}
                            </div>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-xs contact-muted">
                                {{ $contactCopy['map_empty'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 5. Fast Chat Help & 6. FAQ Section -->
            <div class="grid gap-6 md:grid-cols-2 items-stretch">
                <!-- Left Panel: Fast Chat Tips -->
                <div class="contact-card-soft rounded-3xl border p-6 md:p-8 flex flex-col justify-between h-full">
                    <div class="space-y-4">
                        <h3 class="contact-title text-lg font-bold">{{ $contactCopy['fast_chat_title'] }}</h3>
                        
                        <div class="grid gap-3 pt-2">
                            <div class="contact-inner-surface flex gap-3 items-start rounded-xl border p-4" style="border-color: var(--contact-border)">
                                <div class="contact-accent-number text-xs font-bold shrink-0 mt-0.5">01</div>
                                <div>
                                    <h4 class="contact-title text-xs font-bold">{{ $contactCopy['fast_chat_step_1_title'] }}</h4>
                                    <p class="contact-muted text-[11px] mt-0.5">{{ $contactCopy['fast_chat_step_1_desc'] }}</p>
                                </div>
                            </div>
                            <div class="contact-inner-surface flex gap-3 items-start rounded-xl border p-4" style="border-color: var(--contact-border)">
                                <div class="contact-accent-number text-xs font-bold shrink-0 mt-0.5">02</div>
                                <div>
                                    <h4 class="contact-title text-xs font-bold">{{ $contactCopy['fast_chat_step_2_title'] }}</h4>
                                    <p class="contact-muted text-[11px] mt-0.5">{{ $contactCopy['fast_chat_step_2_desc'] }}</p>
                                </div>
                            </div>
                            <div class="contact-inner-surface flex gap-3 items-start rounded-xl border p-4" style="border-color: var(--contact-border)">
                                <div class="contact-accent-number text-xs font-bold shrink-0 mt-0.5">03</div>
                                <div>
                                    <h4 class="contact-title text-xs font-bold">{{ $contactCopy['fast_chat_step_3_title'] }}</h4>
                                    <p class="contact-muted text-[11px] mt-0.5">{{ $contactCopy['fast_chat_step_3_desc'] }}</p>
                                </div>
                            </div>
                            <div class="contact-inner-surface flex gap-3 items-start rounded-xl border p-4" style="border-color: var(--contact-border)">
                                <div class="contact-accent-number text-xs font-bold shrink-0 mt-0.5">04</div>
                                <div>
                                    <h4 class="contact-title text-xs font-bold">{{ $contactCopy['fast_chat_step_4_title'] }}</h4>
                                    <p class="contact-muted text-[11px] mt-0.5">{{ $contactCopy['fast_chat_step_4_desc'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: FAQ -->
                <div class="contact-card-soft rounded-3xl border p-6 md:p-8 flex flex-col justify-between h-full">
                    <div class="space-y-4">
                        <h3 class="contact-title text-lg font-bold">{{ $contactCopy['faq_title'] }}</h3>

                        <div class="space-y-4" style="border-color: var(--contact-border)">
                            <div class="space-y-1">
                                <h4 class="contact-title text-xs font-bold">{{ $contactCopy['faq_1_q'] }}</h4>
                                <p class="contact-muted text-[11px] leading-relaxed">{{ $contactCopy['faq_1_a'] }}</p>
                            </div>
                            <div class="space-y-1 pt-3 border-t" style="border-color: var(--contact-border)">
                                <h4 class="contact-title text-xs font-bold">{{ $contactCopy['faq_2_q'] }}</h4>
                                <p class="contact-muted text-[11px] leading-relaxed">{{ $contactCopy['faq_2_a'] }}</p>
                            </div>
                            <div class="space-y-1 pt-3 border-t" style="border-color: var(--contact-border)">
                                <h4 class="contact-title text-xs font-bold">{{ $contactCopy['faq_3_q'] }}</h4>
                                <p class="contact-muted text-[11px] leading-relaxed">{{ $contactCopy['faq_3_a'] }}</p>
                            </div>
                            <div class="space-y-1 pt-3 border-t" style="border-color: var(--contact-border)">
                                <h4 class="contact-title text-xs font-bold">{{ $contactCopy['faq_4_q'] }}</h4>
                                <p class="contact-muted text-[11px] leading-relaxed">{{ $contactCopy['faq_4_a'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Final CTA: Full Width Closing Strip -->
            <div class="contact-cta-strip relative overflow-hidden rounded-3xl border p-8 sm:p-10 text-center">
                <div class="contact-accent-glow absolute -right-24 -bottom-24 h-96 w-96 rounded-full blur-3xl" aria-hidden="true"></div>
                <div class="relative z-10 space-y-4 max-w-2xl mx-auto">
                    <h3 class="contact-title text-xl sm:text-2xl font-extrabold">{{ $contactCopy['cta_title'] }}</h3>
                    <p class="contact-muted text-xs">{{ $contactCopy['cta_subtitle'] }}</p>
                    
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        @if ($contactWhatsappEntries->isNotEmpty() && $firstWhatsappHref !== '#')
                            <a href="{{ $firstWhatsappHref }}" target="_blank" rel="noopener noreferrer"
                               class="contact-accent-bg inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-xs font-bold transition duration-300 hover:-translate-y-0.5"
                               aria-label="{{ $contactCopy['whatsapp_aria'] }}">
                                {{ $contactCopy['cta_chat_button'] }}
                            </a>
                        @endif
                        <a href="{{ route('catalog') }}"
                           class="contact-card contact-accent-border-hover inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-3 text-xs font-bold transition duration-300">
                            {{ $contactCopy['cta_catalog_button'] }}
                        </a>
                        <a href="{{ route('rental.rules') }}"
                           class="contact-card contact-accent-border-hover inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-3 text-xs font-bold transition duration-300">
                            {{ $contactCopy['cta_rules_button'] }}
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
