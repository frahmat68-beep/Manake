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

    <section class="min-h-screen bg-[#0A0A0B] py-12 md:py-16 text-[#E8E8EC] selection:bg-amber-500/10 selection:text-amber-500">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-10">
            
            <!-- 1. Hero Section: Clean 2-Column Card -->
            <div class="relative overflow-hidden rounded-3xl border border-[#1A1A1E] bg-[#111113]/70 p-6 sm:p-8 lg:p-10 shadow-[0_30px_80px_-40px_rgba(0,0,0,0.8)] backdrop-blur-md">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-[#D4A843]/5 blur-3xl" aria-hidden="true"></div>
                
                <div class="relative z-10 grid gap-8 lg:grid-cols-[1.15fr_0.85fr] items-center">
                    <!-- Left Side Info -->
                    <div class="space-y-4">
                        <h1 class="text-[clamp(2rem,4.5vw,3rem)] font-extrabold leading-[1.1] tracking-tight text-[#E8E8EC]">
                            Hubungi Tim <span class="text-[#D4A843]">Manake</span>
                        </h1>
                        
                        <p class="text-sm sm:text-base leading-relaxed text-[#A0A0A8] max-w-2xl">
                            Cek ketersediaan alat, konsultasi kebutuhan produksi, atau minta penawaran event langsung ke tim kami.
                        </p>
                        
                        <div class="flex flex-wrap gap-3 pt-2">
                            @if ($contactWhatsappEntries->isNotEmpty() && $firstWhatsappHref !== '#')
                                <a href="{{ $firstWhatsappHref }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#D4A843] px-5 py-3 text-xs font-bold text-[#0A0A0B] shadow-[0_16px_30px_-15px_rgba(212,168,67,0.4)] transition duration-300 hover:-translate-y-0.5 hover:bg-[#e0ba5d]" aria-label="Chat WhatsApp Resmi Manake">
                                    Chat WhatsApp
                                </a>
                            @endif
                            <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0A0A0B] px-5 py-3 text-xs font-semibold text-[#E8E8EC] transition duration-300 hover:border-white/20 hover:bg-[#111113]">
                                Lihat Katalog
                            </a>
                        </div>
                    </div>

                    <!-- Right Side: Siapkan Detail Sewa Checklist -->
                    <div class="rounded-2xl border border-white/5 bg-[#0A0A0B]/60 p-5 sm:p-6 space-y-3.5">
                        <h3 class="text-sm font-bold text-[#E8E8EC] uppercase tracking-wider">Siapkan detail sewa</h3>
                        
                        <ul class="grid gap-2 text-xs text-[#A0A0A8]">
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full bg-[#D4A843]/10 text-[#D4A843] border border-[#D4A843]/20" aria-hidden="true">✓</span>
                                <span>Tanggal sewa yang diinginkan</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full bg-[#D4A843]/10 text-[#D4A843] border border-[#D4A843]/20" aria-hidden="true">✓</span>
                                <span>Jenis alat (kamera, lighting, dll)</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full bg-[#D4A843]/10 text-[#D4A843] border border-[#D4A843]/20" aria-hidden="true">✓</span>
                                <span>Jumlah unit yang dibutuhkan</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full bg-[#D4A843]/10 text-[#D4A843] border border-[#D4A843]/20" aria-hidden="true">✓</span>
                                <span>Lokasi & kebutuhan produksi</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 2. Contact Channel Cards: Clean 3-Column Grid -->
            <div class="grid gap-5 md:grid-cols-3">
                <!-- Card A: WhatsApp -->
                <div class="rounded-3xl border border-[#D4A843]/20 bg-[#D4A843]/5 p-6 flex flex-col justify-between h-full transition duration-300 hover:border-[#D4A843]/40">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.62.962 3.22 1.463 4.832 1.464 5.489 0 9.954-4.468 9.957-9.96.002-2.66-1.023-5.158-2.883-7.02C16.69 1.777 14.2 1.752 11.998 1.752c-5.49 0-9.954 4.469-9.957 9.961-.002 1.814.49 3.586 1.42 5.178l-.99 3.616 3.73-.978zM17.06 14.54c-.274-.138-1.62-.8-1.874-.892-.252-.093-.437-.138-.62.138-.184.276-.713.892-.873 1.077-.16.184-.32.207-.593.07-.273-.138-1.155-.426-2.2-1.358-.813-.725-1.36-1.62-1.52-1.896-.16-.276-.017-.425.12-.562.123-.123.273-.32.41-.482.137-.16.183-.275.273-.458.09-.184.046-.344-.022-.482-.068-.138-.62-1.492-.85-2.043-.224-.54-.45-.466-.62-.475-.16-.008-.344-.01-.527-.01-.184 0-.482.07-.733.344-.252.276-.96.938-.96 2.29 0 1.35 1.054 2.656 1.202 2.84.148.184 2.075 3.17 5.027 4.444.702.302 1.25.483 1.677.62.705.224 1.346.192 1.854.116.565-.084 1.62-.662 1.848-1.298.227-.636.227-1.182.16-1.297-.07-.115-.25-.184-.523-.322z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base sm:text-lg font-bold text-[#E8E8EC]">WhatsApp</h3>
                            <p class="text-xs text-[#A0A0A8]">Cek ketersediaan alat langsung via chat.</p>
                            <div class="flex flex-col gap-2 pt-2">
                                @forelse ($contactWhatsappEntries as $whatsappNumber)
                                    @php $whatsappHref = $buildWhatsappHref($whatsappNumber); @endphp
                                    @if ($whatsappHref)
                                        <a href="{{ $whatsappHref }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-xl bg-[#D4A843] py-2 text-xs font-bold text-[#0A0A0B] hover:bg-[#e0ba5d] transition duration-300" aria-label="Hubungi WhatsApp {{ $whatsappNumber }}">
                                            {{ $whatsappNumber }}
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center rounded-xl bg-[#111113] py-2 text-xs font-semibold text-[#E8E8EC]">
                                            {{ $whatsappNumber }}
                                        </span>
                                    @endif
                                @empty
                                    <span class="text-xs text-[#A0A0A8]">{{ $contactWhatsapp }}</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card B: Email -->
                <div class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 flex flex-col justify-between h-full transition duration-300 hover:border-white/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/[0.03] border border-white/10 text-[#A0A0A8]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base sm:text-lg font-bold text-[#E8E8EC]">Email</h3>
                            <p class="text-xs text-[#A0A0A8]">Proposal penawaran, invoice kantor, & kolaborasi.</p>
                            <div class="pt-2">
                                <a href="mailto:{{ $contactEmail }}" class="inline-flex w-full items-center justify-center rounded-xl bg-white/[0.03] border border-white/10 py-2.5 text-xs font-semibold text-[#E8E8EC] hover:bg-[#111113] hover:text-[#D4A843] transition duration-300 break-all px-2 text-center" aria-label="Kirim Email ke {{ $contactEmail }}">
                                    {{ $contactEmail }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card C: Instagram -->
                <div class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 flex flex-col justify-between h-full transition duration-300 hover:border-white/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/[0.03] border border-white/10 text-[#A0A0A8]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base sm:text-lg font-bold text-[#E8E8EC]">Instagram</h3>
                            <p class="text-xs text-[#A0A0A8]">Portofolio kru, pembaruan katalog unit, & DM cepat.</p>
                            <div class="pt-2">
                                @if ($instagramUrl)
                                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-xl bg-white/[0.03] border border-white/10 py-2.5 text-xs font-semibold text-[#E8E8EC] hover:bg-[#111113] hover:text-[#D4A843] transition duration-300 text-center px-2" aria-label="Buka Instagram Manake">
                                        {{ $contactInstagram }}
                                    </a>
                                @else
                                    <span class="inline-flex w-full items-center justify-center rounded-xl bg-[#111113] py-2.5 text-xs font-semibold text-[#E8E8EC] text-center px-2">
                                        {{ $contactInstagram }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Studio Visit Section & 4. Map Section: Symmetrical 12-Column Grid -->
            <div class="grid gap-6 lg:grid-cols-12 items-stretch">
                <!-- Left: Studio & Pickup Point (lg:col-span-5) -->
                <div class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 md:p-8 flex flex-col justify-between h-full lg:col-span-5">
                    <div class="space-y-6">
                        <div class="flex items-center gap-4 border-b border-white/5 pb-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843]" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-[#E8E8EC]">Titik Pengambilan</h3>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-bold text-[#E8E8EC]">{{ $contactAddressTitle ?: 'Manake Studio & Rental' }}</h4>
                                <div class="mt-2 text-xs sm:text-sm leading-relaxed text-[#A0A0A8]">
                                    @foreach ($contactAddressRest as $addressLine)
                                        <p>{{ $addressLine }}</p>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Practical Notes -->
                            <div class="border-t border-white/5 pt-4 space-y-2.5">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-[#D4A843]">Catatan Praktis:</h5>
                                <ol class="space-y-1.5 text-xs text-[#A0A0A8]">
                                    <li class="flex items-start gap-2">
                                        <span class="text-[#D4A843] font-bold">1.</span>
                                        <span>Konfirmasi jadwal sebelum datang agar alat siap.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-[#D4A843] font-bold">2.</span>
                                        <span>Bawa identitas resmi saat pengambilan alat.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-[#D4A843] font-bold">3.</span>
                                        <span>Pastikan order sewa sudah terverifikasi.</span>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Interactive Google Map (lg:col-span-7) -->
                <div class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 md:p-8 flex flex-col justify-between h-full min-h-[420px] lg:col-span-7">
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-[#E8E8EC]">{{ __('ui.contact.map_title') }}</h3>
                        <p class="text-xs text-[#A0A0A8]">Gunakan rute peta di bawah untuk menavigasi langsung ke studio kami.</p>
                    </div>

                    <div class="mt-4 flex-1 min-h-[300px] overflow-hidden rounded-2xl border border-white/10 bg-[#0A0A0B] relative">
                        @if ($contactMapEmbed)
                            <div class="absolute inset-0 [&>iframe]:h-full [&>iframe]:w-full [&>iframe]:border-0">
                                {!! $contactMapEmbed !!}
                            </div>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-xs text-[#A0A0A8]">
                                {{ __('ui.contact.map_empty') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 5. Rental Help & 6. FAQ Section: 2-Column Desktop Grid -->
            <div class="grid gap-6 md:grid-cols-2 items-stretch">
                <!-- Left Panel: Biar chat cepat diproses -->
                <div class="rounded-3xl border border-white/10 bg-[#111113]/40 p-6 md:p-8 flex flex-col justify-between h-full">
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-[#E8E8EC]">Biar chat kamu cepat diproses</h3>
                        
                        <div class="grid gap-3 pt-2">
                            <div class="flex gap-3 items-start rounded-xl border border-white/5 bg-[#0A0A0B]/50 p-4">
                                <div class="text-xs font-bold text-[#D4A843] shrink-0 mt-0.5">01</div>
                                <div>
                                    <h4 class="text-xs font-bold text-[#E8E8EC]">Tanggal sewa</h4>
                                    <p class="text-[11px] text-[#A0A0A8] mt-0.5">Tentukan tanggal pengambilan & kembali.</p>
                                </div>
                            </div>
                            <div class="flex gap-3 items-start rounded-xl border border-white/5 bg-[#0A0A0B]/50 p-4">
                                <div class="text-xs font-bold text-[#D4A843] shrink-0 mt-0.5">02</div>
                                <div>
                                    <h4 class="text-xs font-bold text-[#E8E8EC]">Jenis alat</h4>
                                    <p class="text-[11px] text-[#A0A0A8] mt-0.5">Tulis daftar tipe kamera/audio yang dicari.</p>
                                </div>
                            </div>
                            <div class="flex gap-3 items-start rounded-xl border border-white/5 bg-[#0A0A0B]/50 p-4">
                                <div class="text-xs font-bold text-[#D4A843] shrink-0 mt-0.5">03</div>
                                <div>
                                    <h4 class="text-xs font-bold text-[#E8E8EC]">Jumlah unit</h4>
                                    <p class="text-[11px] text-[#A0A0A8] mt-0.5">Kuantitas pasti untuk mengecek alokasi stok.</p>
                                </div>
                            </div>
                            <div class="flex gap-3 items-start rounded-xl border border-white/5 bg-[#0A0A0B]/50 p-4">
                                <div class="text-xs font-bold text-[#D4A843] shrink-0 mt-0.5">04</div>
                                <div>
                                    <h4 class="text-xs font-bold text-[#E8E8EC]">Kebutuhan produksi</h4>
                                    <p class="text-[11px] text-[#A0A0A8] mt-0.5">Lokasi kota & peruntukan shooting/event.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Pertanyaan singkat FAQs -->
                <div class="rounded-3xl border border-white/10 bg-[#111113]/40 p-6 md:p-8 flex flex-col justify-between h-full">
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-[#E8E8EC]">Pertanyaan singkat</h3>

                        <div class="divide-y divide-white/5 space-y-4">
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-[#E8E8EC]">Bisa cek ketersediaan tanpa login?</h4>
                                <p class="text-[11px] leading-relaxed text-[#A0A0A8]">Bisa. Kamu bisa lihat katalog dan cek jadwal alat tanpa login.</p>
                            </div>
                            <div class="space-y-1 pt-3">
                                <h4 class="text-xs font-bold text-[#E8E8EC]">Kapan perlu login?</h4>
                                <p class="text-[11px] leading-relaxed text-[#A0A0A8]">Login diperlukan saat menambahkan alat ke keranjang dan checkout.</p>
                            </div>
                            <div class="space-y-1 pt-3">
                                <h4 class="text-xs font-bold text-[#E8E8EC]">Bisa minta penawaran untuk event?</h4>
                                <p class="text-[11px] leading-relaxed text-[#A0A0A8]">Bisa. Kirim tanggal, jenis acara, dan kebutuhan alat via WhatsApp atau email.</p>
                            </div>
                            <div class="space-y-1 pt-3">
                                <h4 class="text-xs font-bold text-[#E8E8EC]">Apakah bisa ambil alat langsung?</h4>
                                <p class="text-[11px] leading-relaxed text-[#A0A0A8]">Bisa, tetapi jadwal pickup perlu dikonfirmasi dulu.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Final CTA: Full Width Compact Closing Strip -->
            <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-[#111113] p-8 sm:p-10 text-center shadow-[0_20px_50px_-20px_rgba(0,0,0,0.8)]">
                <div class="absolute -right-24 -bottom-24 h-96 w-96 rounded-full bg-[#D4A843]/5 blur-3xl" aria-hidden="true"></div>
                <div class="relative z-10 space-y-4 max-w-2xl mx-auto">
                    <h3 class="text-xl sm:text-2xl font-extrabold text-[#E8E8EC]">Siap mulai sewa alat produksi?</h3>
                    <p class="text-xs text-[#A0A0A8]">Mulai dari katalog, cek jadwal alat, atau langsung hubungi tim Manake untuk kebutuhan khusus.</p>
                    
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        @if ($contactWhatsappEntries->isNotEmpty() && $firstWhatsappHref !== '#')
                            <a href="{{ $firstWhatsappHref }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#D4A843] px-5 py-3 text-xs font-bold text-[#0A0A0B] hover:bg-[#e0ba5d] transition duration-300" aria-label="Hubungi WhatsApp">
                                Chat WhatsApp
                            </a>
                        @endif
                        <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0A0A0B] px-5 py-3 text-xs font-bold text-[#E8E8EC] hover:bg-[#111113] transition duration-300">
                            Cek Katalog Alat
                        </a>
                        <a href="{{ route('rental.rules') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0A0A0B] px-5 py-3 text-xs font-bold text-[#E8E8EC] hover:bg-[#111113] transition duration-300">
                            Lihat Cara Sewa
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
