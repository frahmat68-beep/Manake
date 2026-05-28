@extends('layouts.landing')

@section('title', __('app.footer.quick_about'))

@php
    $aboutText = setting('footer.about', setting('footer_description', site_content('footer.about', __('app.footer.about_body'))));
    $contactWhatsapp = setting('footer.whatsapp', setting('social_whatsapp', site_content('footer.whatsapp', setting('footer_phone', '+62 812-3456-7890'))));
    $contactEmail = setting('contact.email', setting('footer_email', site_content('contact.email', 'hello@manakerental.id')));
@endphp

@push('head')
    <style>
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
    <div class="min-h-screen bg-[#0A0A0B] text-[#E8E8EC] py-6 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 space-y-12">
            
            <!-- 1. Hero Section -->
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-[1.3fr,0.7fr] items-stretch about-enter">
                <!-- Left Content Card -->
                <div class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8 lg:p-10 shadow-2xl flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-bold tracking-widest uppercase text-[#D4A843] mb-3">TENTANG MANAKE</p>
                        <h1 class="text-3xl font-extrabold tracking-tight text-[#E8E8EC] sm:text-4xl leading-tight">
                            Rental alat produksi yang dibuat lebih rapi.
                        </h1>
                        <p class="mt-4 text-sm text-[#A0A0A8] leading-relaxed max-w-2xl">
                            {{ $aboutText }}
                        </p>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('catalog') }}" class="mk-button-primary px-6 py-3 text-sm font-bold flex items-center gap-2">
                            Lihat Katalog
                        </a>
                        <a href="{{ route('availability.board') }}" class="mk-button-secondary px-6 py-3 text-sm font-bold">
                            Cek Ketersediaan
                        </a>
                    </div>
                </div>

                <!-- Right Visual Summary Card -->
                <div class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 sm:p-8 shadow-xl flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-[#E8E8EC]">Untuk produksi, event, dan kreator.</h2>
                        <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">Penyediaan kamera, audio, pencahayaan, dan support gear cinematic terkurasi.</p>
                    </div>
                    <div class="space-y-3 mt-6">
                        <div class="flex items-center gap-3 p-3 bg-[#0A0A0B]/40 rounded-2xl border border-white/5 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-[#D4A843]"></span>
                            <span class="text-xs font-semibold text-[#E8E8EC]">Kamera & lensa</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-[#0A0A0B]/40 rounded-2xl border border-white/5 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-[#D4A843]"></span>
                            <span class="text-xs font-semibold text-[#E8E8EC]">Lighting & audio</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-[#0A0A0B]/40 rounded-2xl border border-white/5 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-[#D4A843]"></span>
                            <span class="text-xs font-semibold text-[#E8E8EC]">HT, drone, dan support gear</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. What Manake Does Section -->
            <section class="space-y-6">
                <div class="border-b border-[#1A1A1E] pb-3">
                    <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">Apa yang Manake bantu?</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <article class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 sm:p-8 shadow-xl flex flex-col justify-between about-card-in">
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#D4A843]/10 text-[#D4A843]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-.547.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#E8E8EC]">Sewa alat produksi</h3>
                                <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">
                                    Pilih kamera, lensa, lighting, audio, dan support gear sesuai kebutuhan produksi.
                                </p>
                            </div>
                        </div>
                    </article>
                    <article class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 sm:p-8 shadow-xl flex flex-col justify-between about-card-in">
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#D4A843]/10 text-[#D4A843]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#E8E8EC]">Cek jadwal alat</h3>
                                <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">
                                    Lihat ketersediaan alat berdasarkan tanggal secara real-time sebelum mulai booking.
                                </p>
                            </div>
                        </div>
                    </article>
                    <article class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 sm:p-8 shadow-xl flex flex-col justify-between about-card-in">
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#D4A843]/10 text-[#D4A843]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#E8E8EC]">Booking lebih terstruktur</h3>
                                <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">
                                    Keranjang, checkout, pembayaran otomatis, dan pencatatan invoice dalam satu alur rapi.
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- 3. Why Choose Us Section -->
            <section class="space-y-6">
                <div class="border-b border-[#1A1A1E] pb-3">
                    <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">Kenapa Manake?</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl about-card-in">
                        <h3 class="text-sm font-bold text-[#E8E8EC]">Alat terawat</h3>
                        <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">Seluruh kamera, lensa, dan lighting dirawat secara profesional agar performa terbaik saat shooting.</p>
                    </div>
                    <div class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl about-card-in">
                        <h3 class="text-sm font-bold text-[#E8E8EC]">Stok dan jadwal jelas</h3>
                        <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">Papan ketersediaan live memberikan kepastian unit tanpa harus menunggu konfirmasi manual.</p>
                    </div>
                    <div class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl about-card-in">
                        <h3 class="text-sm font-bold text-[#E8E8EC]">Cocok untuk event & shooting</h3>
                        <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">Pilihan perlengkapan terstruktur, praktis untuk kru film pendek, mahasiswa, hingga event besar.</p>
                    </div>
                    <div class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl about-card-in">
                        <h3 class="text-sm font-bold text-[#E8E8EC]">Pembayaran online</h3>
                        <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">Proses checkout terintegrasi gerbang pembayaran otomatis yang aman dan cepat.</p>
                    </div>
                    <div class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl about-card-in md:col-span-2 lg:col-span-1">
                        <h3 class="text-sm font-bold text-[#E8E8EC]">Dukungan admin</h3>
                        <p class="mt-2 text-xs text-[#A0A0A8] leading-relaxed">Tim bantuan siap melayani serah terima alat dan koordinasi jadwal operasional.</p>
                    </div>
                </div>
            </section>

            <!-- 4. Rental Flow Preview Section -->
            <section class="space-y-6">
                <div class="border-b border-[#1A1A1E] pb-3 flex flex-col md:flex-row md:items-baseline md:justify-between gap-2">
                    <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">Alur sewa singkat</h2>
                    <p class="text-[10px] font-bold text-[#A0A0A8] uppercase tracking-wider">
                        Katalog bisa dilihat tanpa login. Login hanya dibutuhkan saat menambahkan alat ke keranjang dan checkout.
                    </p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/40 p-5 shadow-sm flex flex-col items-start gap-4">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-[#D4A843]/10 text-xs font-black text-[#D4A843]">1</span>
                        <div>
                            <h4 class="text-xs font-bold text-[#E8E8EC]">Cek katalog</h4>
                            <p class="mt-1 text-[10px] text-[#A0A0A8] leading-normal">Cari kamera, lighting, drone, atau audio gear.</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/40 p-5 shadow-sm flex flex-col items-start gap-4">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-[#D4A843]/10 text-xs font-black text-[#D4A843]">2</span>
                        <div>
                            <h4 class="text-xs font-bold text-[#E8E8EC]">Pilih tanggal</h4>
                            <p class="mt-1 text-[10px] text-[#A0A0A8] leading-normal">Tentukan durasi sewa di form ketersediaan.</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/40 p-5 shadow-sm flex flex-col items-start gap-4">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-[#D4A843]/10 text-xs font-black text-[#D4A843]">3</span>
                        <div>
                            <h4 class="text-xs font-bold text-[#E8E8EC]">Login saat booking</h4>
                            <p class="mt-1 text-[10px] text-[#A0A0A8] leading-normal">Masuk ke akun untuk verifikasi sewa.</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/40 p-5 shadow-sm flex flex-col items-start gap-4">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-[#D4A843]/10 text-xs font-black text-[#D4A843]">4</span>
                        <div>
                            <h4 class="text-xs font-bold text-[#E8E8EC]">Checkout</h4>
                            <p class="mt-1 text-[10px] text-[#A0A0A8] leading-normal">Selesaikan pembayaran aman secara online.</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-[#111113]/40 p-5 shadow-sm flex flex-col items-start gap-4 col-span-2 md:col-span-1">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-[#D4A843]/10 text-xs font-black text-[#D4A843]">5</span>
                        <div>
                            <h4 class="text-xs font-bold text-[#E8E8EC]">Ambil alat</h4>
                            <p class="mt-1 text-[10px] text-[#A0A0A8] leading-normal">Kunjungi studio kami untuk serah terima unit.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. Who It's For Section -->
            <section class="space-y-6">
                <div class="border-b border-[#1A1A1E] pb-3">
                    <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">Cocok untuk siapa?</h2>
                </div>
                <div class="flex flex-wrap gap-2.5">
                    <span class="rounded-full border border-white/5 bg-[#111113]/40 px-4 py-2 text-xs font-bold text-[#A0A0A8]">Kru film pendek</span>
                    <span class="rounded-full border border-white/5 bg-[#111113]/40 px-4 py-2 text-xs font-bold text-[#A0A0A8]">Event kampus</span>
                    <span class="rounded-full border border-white/5 bg-[#111113]/40 px-4 py-2 text-xs font-bold text-[#A0A0A8]">Content creator</span>
                    <span class="rounded-full border border-white/5 bg-[#111113]/40 px-4 py-2 text-xs font-bold text-[#A0A0A8]">Production house</span>
                    <span class="rounded-full border border-white/5 bg-[#111113]/40 px-4 py-2 text-xs font-bold text-[#A0A0A8]">Dokumenter & interview</span>
                    <span class="rounded-full border border-white/5 bg-[#111113]/40 px-4 py-2 text-xs font-bold text-[#A0A0A8]">Event organizer</span>
                </div>
            </section>

            <!-- 6. Brand Final CTA Card -->
            <section class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8 lg:p-10 shadow-2xl relative overflow-hidden text-center max-w-4xl mx-auto">
                <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-[#D4A843]/10 blur-[60px]"></div>
                <div class="relative space-y-6">
                    <h2 class="text-2xl font-extrabold text-[#E8E8EC]">Punya kebutuhan produksi khusus?</h2>
                    <p class="text-sm text-[#A0A0A8] leading-relaxed max-w-2xl mx-auto">
                        Kirim tanggal sewa, jenis alat, dan jumlah unit. Tim Manake bisa bantu arahkan pilihan alat yang sesuai.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                        <a href="{{ route('contact') }}" class="mk-button-primary px-6 py-3.5 text-sm font-bold">
                            Hubungi Tim
                        </a>
                        <a href="{{ Route::has('rental.rules') ? route('rental.rules') : url('/rental-rules') }}" class="mk-button-secondary px-6 py-3.5 text-sm font-bold">
                            Lihat Cara Sewa
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
