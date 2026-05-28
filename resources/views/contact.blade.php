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
    @endphp

    <section class="min-h-screen bg-[#0A0A0B] py-16 md:py-24 text-[#E8E8EC]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-12">
            <!-- Premium Header Card -->
            <div class="relative overflow-hidden rounded-[2.5rem] border border-[#1A1A1E] bg-[#111113]/80 p-8 sm:p-12 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.8)] backdrop-blur-md">
                <!-- Decorative Subtle Background Glow -->
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-[#D4A843]/5 blur-3xl"></div>
                <div class="absolute -left-24 -bottom-24 h-96 w-96 rounded-full bg-blue-500/5 blur-3xl"></div>

                <div class="relative z-10 max-w-3xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#D4A843]/20 bg-[#D4A843]/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4A843]"></span>
                        Support Hub
                    </span>
                    <h1 class="mt-4 text-[clamp(2.2rem,5vw,3.8rem)] font-extrabold leading-[1.05] tracking-tight text-[#E8E8EC]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Kontak & Studio <span class="text-[#D4A843]">Manake</span>
                    </h1>
                    <p class="mt-4 text-base md:text-lg leading-relaxed text-[#A0A0A8] max-w-2xl">
                        Punya pertanyaan seputar ketersediaan kamera, butuh penawaran custom untuk kru produksi, atau ingin berkunjung ke studio kami? Hubungi kami langsung di bawah ini.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        @if ($contactWhatsappEntries->isNotEmpty() && $buildWhatsappHref((string) $contactWhatsappEntries->first()))
                            <a href="{{ $buildWhatsappHref((string) $contactWhatsappEntries->first()) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#D4A843] px-6 py-3.5 text-sm font-bold text-[#0A0A0B] shadow-[0_16px_30px_-15px_rgba(212,168,67,0.4)] transition duration-300 hover:-translate-y-0.5 hover:bg-[#e0ba5d] hover:shadow-[0_20px_35px_-12px_rgba(212,168,67,0.5)] active:translate-y-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.62.962 3.22 1.463 4.832 1.464 5.489 0 9.954-4.468 9.957-9.96.002-2.66-1.023-5.158-2.883-7.02C16.69 1.777 14.2 1.752 11.998 1.752c-5.49 0-9.954 4.469-9.957 9.961-.002 1.814.49 3.586 1.42 5.178l-.99 3.616 3.73-.978zM17.06 14.54c-.274-.138-1.62-.8-1.874-.892-.252-.093-.437-.138-.62.138-.184.276-.713.892-.873 1.077-.16.184-.32.207-.593.07-.273-.138-1.155-.426-2.2-1.358-.813-.725-1.36-1.62-1.52-1.896-.16-.276-.017-.425.12-.562.123-.123.273-.32.41-.482.137-.16.183-.275.273-.458.09-.184.046-.344-.022-.482-.068-.138-.62-1.492-.85-2.043-.224-.54-.45-.466-.62-.475-.16-.008-.344-.01-.527-.01-.184 0-.482.07-.733.344-.252.276-.96.938-.96 2.29 0 1.35 1.054 2.656 1.202 2.84.148.184 2.075 3.17 5.027 4.444.702.302 1.25.483 1.677.62.705.224 1.346.192 1.854.116.565-.084 1.62-.662 1.848-1.298.227-.636.227-1.182.16-1.297-.07-.115-.25-.184-.523-.322z"/></svg>
                                Chat WhatsApp
                            </a>
                        @endif
                        <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-6 py-3.5 text-sm font-semibold text-[#E8E8EC] transition duration-300 hover:border-white/20 hover:bg-[#111113] active:scale-98">
                            Lihat Katalog
                        </a>
                    </div>
                </div>
            </div>

            <!-- Two Column Grid for Info & Map -->
            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                
                <!-- Left Column: Contact Detail Cards -->
                <div class="space-y-6">
                    
                    <!-- Address Card -->
                    <div class="rounded-3xl border border-[#1A1A1E] bg-[#111113]/50 p-6 md:p-8 shadow-sm transition duration-300 hover:border-white/10">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="space-y-3">
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">Studio & Lokasi</p>
                                <h3 class="text-xl font-bold text-[#E8E8EC]">
                                    {{ $contactAddressTitle ?: 'Manake Studio' }}
                                </h3>
                                <div class="text-sm leading-relaxed text-[#A0A0A8]">
                                    @foreach ($contactAddressRest as $addressLine)
                                        <p>{{ $addressLine }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Channels Card -->
                    <div class="rounded-3xl border border-[#1A1A1E] bg-[#111113]/50 p-6 md:p-8 shadow-sm transition duration-300 hover:border-white/10">
                        <div class="space-y-6">
                            
                            <!-- WhatsApp Section -->
                            <div class="space-y-3">
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">WhatsApp Hotline</p>
                                <div class="flex flex-wrap gap-3">
                                    @forelse ($contactWhatsappEntries as $whatsappNumber)
                                        @php $whatsappHref = $buildWhatsappHref($whatsappNumber); @endphp
                                        @if ($whatsappHref)
                                            <a
                                                href="{{ $whatsappHref }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-2 rounded-2xl border border-[#D4A843]/20 bg-[#D4A843]/5 px-5 py-3 text-sm font-semibold text-[#D4A843] transition duration-300 hover:bg-[#D4A843]/10 hover:border-[#D4A843]/40"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.62.962 3.22 1.463 4.832 1.464 5.489 0 9.954-4.468 9.957-9.96.002-2.66-1.023-5.158-2.883-7.02C16.69 1.777 14.2 1.752 11.998 1.752c-5.49 0-9.954 4.469-9.957 9.961-.002 1.814.49 3.586 1.42 5.178l-.99 3.616 3.73-.978zM17.06 14.54c-.274-.138-1.62-.8-1.874-.892-.252-.093-.437-.138-.62.138-.184.276-.713.892-.873 1.077-.16.184-.32.207-.593.07-.273-.138-1.155-.426-2.2-1.358-.813-.725-1.36-1.62-1.52-1.896-.16-.276-.017-.425.12-.562.123-.123.273-.32.41-.482.137-.16.183-.275.273-.458.09-.184.046-.344-.022-.482-.068-.138-.62-1.492-.85-2.043-.224-.54-.45-.466-.62-.475-.16-.008-.344-.01-.527-.01-.184 0-.482.07-.733.344-.252.276-.96.938-.96 2.29 0 1.35 1.054 2.656 1.202 2.84.148.184 2.075 3.17 5.027 4.444.702.302 1.25.483 1.677.62.705.224 1.346.192 1.854.116.565-.084 1.62-.662 1.848-1.298.227-.636.227-1.182.16-1.297-.07-.115-.25-.184-.523-.322z"/></svg>
                                                {{ $whatsappNumber }}
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-2xl border border-[#1A1A1E] bg-[#111113] px-5 py-3 text-sm font-semibold text-[#E8E8EC]">
                                                {{ $whatsappNumber }}
                                            </span>
                                        @endif
                                    @empty
                                        <span class="text-sm text-[#A0A0A8]">{{ $contactWhatsapp }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Email & Instagram Columns -->
                            <div class="grid gap-4 sm:grid-cols-2 pt-2">
                                <!-- Email Block -->
                                <div class="rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B] p-5 transition duration-300 hover:border-[#D4A843]/20">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">{{ __('ui.contact.labels.email') }}</p>
                                    <a href="mailto:{{ $contactEmail }}" class="mt-2.5 inline-flex items-center gap-2 text-sm font-bold text-[#E8E8EC] transition hover:text-[#D4A843] break-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        {{ $contactEmail }}
                                    </a>
                                </div>

                                <!-- Instagram Block -->
                                <div class="rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B] p-5 transition duration-300 hover:border-[#D4A843]/20">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">{{ __('ui.contact.labels.instagram') }}</p>
                                    @if ($instagramUrl)
                                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="mt-2.5 inline-flex items-center gap-2 text-sm font-bold text-[#E8E8EC] transition hover:text-[#D4A843]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $contactInstagram }}
                                        </a>
                                    @else
                                        <span class="mt-2.5 inline-flex items-center gap-2 text-sm font-bold text-[#E8E8EC]">
                                            {{ $contactInstagram }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Right Column: Premium Map Card -->
                <div class="rounded-[2rem] border border-[#1A1A1E] bg-[#111113]/50 p-6 md:p-8 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A843]">
                            Interactive Map
                        </span>
                        <h2 class="text-xl font-bold text-[#E8E8EC] mt-2">{{ __('ui.contact.map_title') }}</h2>
                        <p class="text-xs text-[#A0A0A8] mt-1">Gunakan peta interaktif di bawah untuk menavigasi ke studio kami dengan mudah.</p>
                    </div>

                    <div class="mt-6 flex-1 min-h-[300px] overflow-hidden rounded-2xl border border-[#1A1A1E] shadow-inner bg-[#0A0A0B] relative">
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
        </div>
    </section>
@endsection
