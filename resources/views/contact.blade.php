@extends('layouts.landing')

@section('title', __('ui.contact.page_title'))

@section('content')
    @php
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

        $firstWhatsappHref = $contactWhatsappEntries->isNotEmpty() ? $buildWhatsappHref((string) $contactWhatsappEntries->first()) : '#';
    @endphp

    <section class="min-h-screen bg-[#0A0A0B] py-16 md:py-24 text-[#E8E8EC] selection:bg-amber-500/10 selection:text-amber-500">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-20">
            
            <!-- 1. Hero Section -->
            <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] items-center">
                <!-- Hero Left Info -->
                <div class="relative overflow-hidden rounded-[2.5rem] border border-[#1A1A1E] bg-[#111113]/80 p-8 sm:p-12 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.8)] backdrop-blur-md">
                    <!-- Subtle Glow -->
                    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-[#D4A843]/5 blur-3xl"></div>
                    
                    <div class="relative z-10 space-y-6">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#D4A843]/20 bg-[#D4A843]/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#D4A843] animate-pulse"></span>
                            Production Support Hub
                        </span>
                        
                        <h1 class="text-[clamp(2.2rem,5vw,3.6rem)] font-extrabold leading-[1.05] tracking-tight text-[#E8E8EC]">
                            Hubungi Tim <span class="text-[#D4A843]">Manake</span>
                        </h1>
                        
                        <p class="text-base md:text-lg leading-relaxed text-[#A0A0A8] max-w-2xl">
                            Mau cek ketersediaan alat, minta penawaran event, atau konsultasi kebutuhan produksi? Tim kami siap bantu dari pemilihan gear sampai jadwal pengambilan.
                        </p>

                        <!-- Small Trust/Status Pills -->
                        <div class="flex flex-wrap gap-2.5 pt-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/[0.03] border border-white/5 px-3.5 py-1.5 text-xs text-[#A0A0A8]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#D4A843]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Respon cepat via WhatsApp
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/[0.03] border border-white/5 px-3.5 py-1.5 text-xs text-[#A0A0A8]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#D4A843]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Support kebutuhan event & shooting
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/[0.03] border border-white/5 px-3.5 py-1.5 text-xs text-[#A0A0A8]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#D4A843]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Pickup studio tersedia
                            </span>
                        </div>
                        
                        <div class="mt-8 flex flex-wrap gap-4 pt-2">
                            @if ($contactWhatsappEntries->isNotEmpty() && $firstWhatsappHref !== '#')
                                <a href="{{ $firstWhatsappHref }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#D4A843] px-6 py-3.5 text-sm font-bold text-[#0A0A0B] shadow-[0_16px_30px_-15px_rgba(212,168,67,0.4)] transition duration-300 hover:-translate-y-0.5 hover:bg-[#e0ba5d] hover:shadow-[0_20px_35px_-12px_rgba(212,168,67,0.5)] active:translate-y-0" aria-label="Hubungi WhatsApp Resmi Manake">
                                    Chat WhatsApp
                                </a>
                            @endif
                            <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0A0A0B] px-6 py-3.5 text-sm font-semibold text-[#E8E8EC] transition duration-300 hover:border-white/20 hover:bg-[#111113] active:scale-98">
                                Lihat Katalog
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Hero Right: Quick Consult Card -->
                <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-[#111113]/70 p-6 sm:p-8 shadow-[0_20px_50px_-20px_rgba(0,0,0,0.7)] backdrop-blur-sm">
                    <div class="absolute -right-12 -bottom-12 h-48 w-48 rounded-full bg-blue-500/5 blur-2xl"></div>
                    <div class="relative z-10 space-y-5">
                        <div class="border-b border-white/5 pb-4">
                            <h3 class="text-lg font-bold text-[#E8E8EC]">Butuh alat untuk kapan?</h3>
                            <p class="text-xs text-[#A0A0A8] mt-1">Konsultasikan kebutuhan produksi Anda secara instan.</p>
                        </div>

                        <!-- Checklist -->
                        <ul class="space-y-3 text-sm text-[#A0A0A8]">
                            <li class="flex items-center gap-3">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#D4A843]/10 text-[#D4A843] border border-[#D4A843]/20">✓</span>
                                <span>Shooting / Produksi Film</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#D4A843]/10 text-[#D4A843] border border-[#D4A843]/20">✓</span>
                                <span>Event / Kampus / Dokumentasi</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#D4A843]/10 text-[#D4A843] border border-[#D4A843]/20">✓</span>
                                <span>HT Crew & Handy Talky Ringan</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#D4A843]/10 text-[#D4A843] border border-[#D4A843]/20">✓</span>
                                <span>Kamera, Lens & Lighting Pro</span>
                            </li>
                        </ul>

                        <a href="{{ $firstWhatsappHref }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 hover:border-[#D4A843]/40 hover:bg-[#D4A843]/5 hover:text-[#D4A843] py-3.5 text-xs font-bold text-[#E8E8EC] transition duration-300" aria-label="Konsultasi Kebutuhan Sewa Sekarang">
                            Konsultasi Sekarang →
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. Contact Channels Section -->
            <div class="space-y-6">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">Hubungi Langsung</span>
                    <h2 class="text-2xl font-bold text-[#E8E8EC] mt-1">Saluran Komunikasi Resmi</h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Card A: WhatsApp -->
                    <div class="rounded-3xl border border-[#D4A843]/20 bg-[#D4A843]/5 p-6 shadow-sm flex flex-col justify-between transition duration-300 hover:border-[#D4A843]/40">
                        <div class="space-y-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.62.962 3.22 1.463 4.832 1.464 5.489 0 9.954-4.468 9.957-9.96.002-2.66-1.023-5.158-2.883-7.02C16.69 1.777 14.2 1.752 11.998 1.752c-5.49 0-9.954 4.469-9.957 9.961-.002 1.814.49 3.586 1.42 5.178l-.99 3.616 3.73-.978zM17.06 14.54c-.274-.138-1.62-.8-1.874-.892-.252-.093-.437-.138-.62.138-.184.276-.713.892-.873 1.077-.16.184-.32.207-.593.07-.273-.138-1.155-.426-2.2-1.358-.813-.725-1.36-1.62-1.52-1.896-.16-.276-.017-.425.12-.562.123-.123.273-.32.41-.482.137-.16.183-.275.273-.458.09-.184.046-.344-.022-.482-.068-.138-.62-1.492-.85-2.043-.224-.54-.45-.466-.62-.475-.16-.008-.344-.01-.527-.01-.184 0-.482.07-.733.344-.252.276-.96.938-.96 2.29 0 1.35 1.054 2.656 1.202 2.84.148.184 2.075 3.17 5.027 4.444.702.302 1.25.483 1.677.62.705.224 1.346.192 1.854.116.565-.084 1.62-.662 1.848-1.298.227-.636.227-1.182.16-1.297-.07-.115-.25-.184-.523-.322z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-[#E8E8EC]">WhatsApp Hotline</h3>
                                <p class="text-xs text-[#A0A0A8] mt-1">Terbaik untuk cek cepat ketersediaan alat dan panduan booking instan.</p>
                            </div>
                            <div class="flex flex-col gap-2 pt-2">
                                @forelse ($contactWhatsappEntries as $whatsappNumber)
                                    @php $whatsappHref = $buildWhatsappHref($whatsappNumber); @endphp
                                    @if ($whatsappHref)
                                        <a href="{{ $whatsappHref }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-xl bg-[#D4A843] py-2.5 text-xs font-bold text-[#0A0A0B] shadow-sm hover:bg-[#e0ba5d] transition duration-300">
                                            {{ $whatsappNumber }}
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center rounded-xl bg-[#111113] py-2.5 text-xs font-semibold text-[#E8E8EC]">
                                            {{ $whatsappNumber }}
                                        </span>
                                    @endif
                                @empty
                                    <span class="text-xs text-[#A0A0A8]">{{ $contactWhatsapp }}</span>
                                @endforelse
                            </div>
                        </div>
                        <span class="mt-6 block text-[10px] uppercase tracking-wider text-[#D4A843] font-bold">Fast Response</span>
                    </div>

                    <!-- Card B: Email -->
                    <div class="rounded-3xl border border-white/10 bg-[#111113]/50 p-6 shadow-sm flex flex-col justify-between transition duration-300 hover:border-white/20">
                        <div class="space-y-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/10 text-[#A0A0A8]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-[#E8E8EC]">Email Resmi</h3>
                                <p class="text-xs text-[#A0A0A8] mt-1">Terbaik untuk penawaran formal, tagihan invoice, dan kolaborasi project.</p>
                            </div>
                            <div class="pt-2">
                                <a href="mailto:{{ $contactEmail }}" class="inline-flex w-full items-center justify-center rounded-xl bg-white/[0.03] border border-white/10 py-2.5 text-xs font-semibold text-[#E8E8EC] hover:bg-[#111113] hover:text-[#D4A843] transition duration-300 break-all px-2 text-center">
                                    {{ $contactEmail }}
                                </a>
                            </div>
                        </div>
                        <span class="mt-6 block text-[10px] uppercase tracking-wider text-[#A0A0A8] font-bold">Formal Support</span>
                    </div>

                    <!-- Card C: Instagram -->
                    <div class="rounded-3xl border border-white/10 bg-[#111113]/50 p-6 shadow-sm flex flex-col justify-between transition duration-300 hover:border-white/20">
                        <div class="space-y-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/10 text-[#A0A0A8]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-[#E8E8EC]">Instagram Portofolio</h3>
                                <p class="text-xs text-[#A0A0A8] mt-1">Terbaik untuk melihat portofolio kami, update alat, dan konsultasi via DM.</p>
                            </div>
                            <div class="pt-2">
                                @if ($instagramUrl)
                                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-xl bg-white/[0.03] border border-white/10 py-2.5 text-xs font-semibold text-[#E8E8EC] hover:bg-[#111113] hover:text-[#D4A843] transition duration-300 text-center px-2">
                                        {{ $contactInstagram }}
                                    </a>
                                @else
                                    <span class="inline-flex w-full items-center justify-center rounded-xl bg-[#111113] py-2.5 text-xs font-semibold text-[#E8E8EC] text-center px-2">
                                        {{ $contactInstagram }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="mt-6 block text-[10px] uppercase tracking-wider text-[#A0A0A8] font-bold">Social DM</span>
                    </div>
                </div>
            </div>

            <!-- 3. Studio Visit Section & 4. Map Section -->
            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <!-- Left: Studio Address Visit Card -->
                <div class="rounded-[2rem] border border-white/10 bg-[#111113]/50 p-6 md:p-8 shadow-sm space-y-6">
                    <div class="flex items-start gap-4 border-b border-white/5 pb-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">Titik Pengambilan</span>
                            <h3 class="text-xl font-bold text-[#E8E8EC] mt-0.5">Studio & Pickup Point</h3>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-bold text-[#E8E8EC]">{{ $contactAddressTitle ?: 'Manake Studio & Rental' }}</h4>
                            <div class="mt-2 text-sm leading-relaxed text-[#A0A0A8]">
                                @foreach ($contactAddressRest as $addressLine)
                                    <p>{{ $addressLine }}</p>
                                @endforeach
                            </div>
                        </div>

                        <!-- Practical Notes -->
                        <div class="border-t border-white/5 pt-4 space-y-3">
                            <h5 class="text-xs font-bold uppercase tracking-wider text-[#D4A843]">Catatan Praktis Pengambilan:</h5>
                            <ul class="space-y-2 text-xs text-[#A0A0A8]">
                                <li class="flex items-start gap-2">
                                    <span class="text-[#D4A843]">•</span>
                                    <span>Konfirmasi jadwal sebelum datang agar alat dipersiapkan terlebih dahulu.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#D4A843]">•</span>
                                    <span>Bawa identitas resmi (KTP/SIM/KTM) saat proses serah terima alat.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#D4A843]">•</span>
                                    <span>Pastikan order sewa Anda sudah terverifikasi dan berstatus lunas.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Right: Map Card -->
                <div class="rounded-[2rem] border border-white/10 bg-[#111113]/50 p-6 md:p-8 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">
                            Rute Peta
                        </span>
                        <h2 class="text-xl font-bold text-[#E8E8EC] mt-1">{{ __('ui.contact.map_title') }}</h2>
                        <p class="text-xs text-[#A0A0A8] mt-1">Ikuti panduan Google Maps di bawah untuk navigasi akurat ke studio.</p>
                    </div>

                    <div class="mt-6 flex-1 min-h-[260px] overflow-hidden rounded-2xl border border-white/10 bg-[#0A0A0B] relative">
                        @if ($contactMapEmbed)
                            <div class="absolute inset-0 [&>iframe]:h-full [&>iframe]:w-full [&>iframe]:border-0">
                                {!! $contactMapEmbed !!}
                            </div>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-sm text-[#A0A0A8]">
                                {{ __('ui.contact.map_empty') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 5. Rental Help Section -->
            <div class="rounded-[2rem] border border-white/10 bg-[#111113]/40 p-8 sm:p-10 shadow-sm space-y-6">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">Panduan Chat</span>
                    <h2 class="text-2xl font-bold text-[#E8E8EC] mt-1">Biar chat kamu langsung cepat diproses</h2>
                    <p class="text-sm text-[#A0A0A8] mt-1.5">Siapkan informasi ini sebelum menghubungi tim kami agar kami dapat memberikan estimasi ketersediaan secara instan:</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-4 pt-2">
                    <div class="rounded-2xl border border-white/5 bg-[#0A0A0B]/70 p-5 space-y-2">
                        <div class="text-lg font-bold text-[#D4A843]">01</div>
                        <h3 class="text-sm font-semibold text-[#E8E8EC]">Tanggal Sewa</h3>
                        <p class="text-xs text-[#A0A0A8]">Tentukan tanggal pengambilan dan pengembalian yang Anda inginkan.</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#0A0A0B]/70 p-5 space-y-2">
                        <div class="text-lg font-bold text-[#D4A843]">02</div>
                        <h3 class="text-sm font-semibold text-[#E8E8EC]">Jenis Alat</h3>
                        <p class="text-xs text-[#A0A0A8]">Sebutkan tipe kamera, lensa, lighting, atau audio spesifik yang ingin disewa.</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#0A0A0B]/70 p-5 space-y-2">
                        <div class="text-lg font-bold text-[#D4A843]">03</div>
                        <h3 class="text-sm font-semibold text-[#E8E8EC]">Jumlah Unit</h3>
                        <p class="text-xs text-[#A0A0A8]">Kuantitas tiap-tiap item yang Anda perlukan untuk lancarnya proses produksi.</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#0A0A0B]/70 p-5 space-y-2">
                        <div class="text-lg font-bold text-[#D4A843]">04</div>
                        <h3 class="text-sm font-semibold text-[#E8E8EC]">Lokasi Produksi</h3>
                        <p class="text-xs text-[#A0A0A8]">Info kota/wilayah shooting guna penyesuaian logistik/koordinasi pengambilan.</p>
                    </div>
                </div>
            </div>

            <!-- 6. FAQ Mini Section -->
            <div class="space-y-6">
                <div class="text-center">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">FAQ Singkat</span>
                    <h2 class="text-2xl font-bold text-[#E8E8EC] mt-1">Pertanyaan Umum</h2>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/60 p-5 space-y-2">
                        <h4 class="text-sm font-bold text-[#E8E8EC]">Bisa cek ketersediaan tanpa login?</h4>
                        <p class="text-xs leading-relaxed text-[#A0A0A8]">Bisa. Kamu bisa lihat katalog dan cek jadwal alat di Papan Ketersediaan secara bebas tanpa perlu melakukan login.</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/60 p-5 space-y-2">
                        <h4 class="text-sm font-bold text-[#E8E8EC]">Kapan saya perlu melakukan login?</h4>
                        <p class="text-xs leading-relaxed text-[#A0A0A8]">Login hanya diwajibkan saat Anda ingin menambahkan peralatan ke dalam keranjang belanja dan melakukan checkout transaksi sewa.</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/60 p-5 space-y-2">
                        <h4 class="text-sm font-bold text-[#E8E8EC]">Bisa minta penawaran tertulis untuk event?</h4>
                        <p class="text-xs leading-relaxed text-[#A0A0A8]">Bisa sekali. Silakan kirimkan detail tanggal, jenis acara, serta daftar kebutuhan alat Anda melalui WhatsApp atau email resmi kami.</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/60 p-5 space-y-2">
                        <h4 class="text-sm font-bold text-[#E8E8EC]">Apakah bisa ambil alat langsung di studio?</h4>
                        <p class="text-xs leading-relaxed text-[#A0A0A8]">Bisa. Namun jadwal pengambilan wajib dikonfirmasi terlebih dahulu kepada admin kami untuk memastikan barang sudah melalui QC & siap diambil.</p>
                    </div>
                </div>
            </div>

            <!-- 7. Final CTA Section -->
            <div class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-[#111113] p-10 sm:p-12 text-center shadow-[0_20px_50px_-20px_rgba(0,0,0,0.8)]">
                <div class="absolute -right-24 -bottom-24 h-96 w-96 rounded-full bg-[#D4A843]/5 blur-3xl"></div>
                <div class="relative z-10 space-y-5 max-w-2xl mx-auto">
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-[#E8E8EC]">Siap mulai sewa alat produksi?</h3>
                    <p class="text-sm text-[#A0A0A8]">Mulai cari kamera sinema, lighting, drone, atau HT terbaik untuk project Anda sekarang.</p>
                    
                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        @if ($contactWhatsappEntries->isNotEmpty() && $firstWhatsappHref !== '#')
                            <a href="{{ $firstWhatsappHref }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#D4A843] px-6 py-3.5 text-xs font-bold text-[#0A0A0B] shadow-md hover:bg-[#e0ba5d] transition duration-300">
                                Chat WhatsApp
                            </a>
                        @endif
                        <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0A0A0B] px-6 py-3.5 text-xs font-bold text-[#E8E8EC] hover:bg-[#111113] hover:border-white/20 transition duration-300">
                            Cek Katalog Alat
                        </a>
                        <a href="{{ route('rental.rules') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0A0A0B] px-6 py-3.5 text-xs font-bold text-[#E8E8EC] hover:bg-[#111113] hover:border-white/20 transition duration-300">
                            Lihat Cara Sewa
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
