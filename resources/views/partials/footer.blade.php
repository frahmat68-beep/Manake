@php
    // ======================================================================
    // APA YANG SAYA LIHAT?
    // -> Pembungkus utama dan konfigurasi visual untuk FOOTER (Bagian bawah website).
    //
    // 🎓 KEMUNGKINAN PERTANYAAN/PERMINTAAN DOSEN SAAT SIDANG:
    // 1. "Ganti teks Hak Cipta (Copyright) di bagian paling bawah!"
    // 2. "Ubah nomor WhatsApp, Email, atau Instagram untuk hubungi rental!"
    // 3. "Ganti alamat fisik studio rental alat!"
    //
    // 🟢 APA YANG BISA SAYA UBAH? (Sangat Aman & Mudah)
    // - Tentang Rental (`footer.about`): Ganti teks default di baris 4.
    // - Kontak WhatsApp: Ganti nomor default di baris 8.
    // - Kontak Email: Ganti email default di baris 9.
    // - Kontak Instagram: Ganti instagram default di baris 10.
    // - Alamat Fisik: Ganti teks alamat default di baris 7.
    // - Teks Copyright: Pada bagian "Bottom Bar" (baris 147), ubah/tambahkan teks setelah tanda `&copy;`.
    //
    // 🟡 APA RISIKONYA? (Perlu Hati-hati)
    // - Variabel `$buildWhatsappHref` dan `$buildTelHref`: Ini adalah fungsi pembantu untuk mendeteksi digit nomor telepon secara otomatis untuk dijadikan link klik. Jangan merusak regex di dalamnya agar tombol hubungi WA tidak mati.
    //
    // 🔴 JANGAN DIUBAH! (Bisa Merusak Fitur)
    // - Tag pembungkus `<footer id="contact" ...>` -> ID `contact` digunakan sebagai anchor link dari Navbar. Jika ID ini diubah atau dihapus, klik menu "Kontak" di navbar atas tidak akan bisa scroll otomatis ke bawah.
    //
    // 📝 BAGAIMANA CARA MENGUBAHNYA? (Contoh Konkret)
    // - Mengubah email: Ganti `'hello@manakerental.id'` menjadi `'kontak@manake.id'`.
    // - Mengubah Instagram: Ganti `'@manakerental'` menjadi `'@rental_manake'`.
    // ======================================================================

    $footerAbout = app()->getLocale() === 'en'
        ? __('app.footer.about_body')
        : setting('footer.about', setting('footer_description', site_content('footer.about', __('app.footer.about_body'))));
    $footerAddress = app()->getLocale() === 'en'
        ? __('app.footer.address_body')
        : setting('footer.address', setting('footer_address', site_content('footer.address', __('app.footer.address_body'))));
    $footerWhatsapp = setting('footer.whatsapp', setting('social_whatsapp', site_content('footer.whatsapp', setting('footer_phone', '+62 812-3456-7890'))));
    $footerEmail = setting('contact.email', setting('footer_email', site_content('contact.email', 'hello@manakerental.id')));
    $footerInstagram = setting('footer.instagram', setting('social_instagram', site_content('footer.instagram', '@manakerental')));
    $footerAddressLines = collect(preg_split('/\R+/', trim((string) $footerAddress)))
        ->map(static fn ($line) => trim((string) $line))
        ->filter()
        ->values();
    $footerAddressTitle = $footerAddressLines->first();
    $footerAddressRest = $footerAddressLines->slice(1);
    $footerWhatsappEntries = collect(preg_split('/\s*(?:\/|\||,)\s*/', (string) $footerWhatsapp))
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
    $buildTelHref = static function (string $value): ?string {
        $sanitized = preg_replace('/[^\d+]/', '', $value);
        if (! $sanitized) {
            return null;
        }

        if (str_starts_with($sanitized, '00')) {
            $sanitized = '+' . ltrim(substr($sanitized, 2), '0');
        }

        return 'tel:' . $sanitized;
    };
    $footerMapUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($footerAddress ?: 'Manake Studio & Rental');
@endphp

<footer id="contact" class="border-t manake-shell manake-border mt-auto">
    <div class="mx-auto grid max-w-7xl gap-8 px-6 py-10 sm:px-8 lg:grid-cols-[1.1fr,0.9fr,1fr] lg:gap-10 lg:py-12 items-start">
        {{-- Column 1: Brand Logo & Guidelines --}}
        <div class="space-y-4">
            <x-brand.image light="manake-logo-blue.png" dark="manake-logo-white.png" alt="Manake" class="manake-footer-logo" img-class="manake-footer-logo__image" />
            <p class="max-w-sm text-xs leading-relaxed manake-text-muted">{{ $footerAbout }}</p>

            <div class="space-y-2 pt-1">
                <p class="manake-kicker">{{ __('app.footer.rules_title') }}</p>
                <div class="flex">
                    <a href="{{ route('rental.rules') }}" class="manake-secondary-button px-3 py-1.5 text-xs">
                        {{ app()->getLocale() === 'en' ? __('app.footer.rules_link') : setting('footer.rules_link', __('app.footer.rules_link')) }}
                        <span aria-hidden="true" class="ml-1">→</span>
                    </a>
                </div>
                <p class="max-w-sm text-[11px] leading-relaxed manake-text-muted">{{ app()->getLocale() === 'en' ? __('app.footer.rules_note') : setting('footer.rules_note', __('app.footer.rules_note')) }}</p>
            </div>
        </div>

        {{-- Column 2: Contact & Address --}}
        <div class="space-y-4">
            <div>
                <h3 class="manake-kicker mb-3">{{ __('app.footer.contact_title') }}</h3>
                <div class="space-y-2 text-xs manake-text-muted">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[10px] font-bold manake-text uppercase tracking-wider">{{ __('WhatsApp') }}</span>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($footerWhatsappEntries as $whatsappNumber)
                                @php
                                    $whatsappHref = $buildWhatsappHref($whatsappNumber);
                                    $telHref = $buildTelHref($whatsappNumber);
                                @endphp
                                @if ($whatsappHref)
                                    <a
                                        href="{{ $whatsappHref }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="px-2.5 py-1 text-xs manake-secondary-button"
                                    >
                                        {{ $whatsappNumber }}
                                    </a>
                                @elseif ($telHref)
                                    <a href="{{ $telHref }}" class="px-2.5 py-1 text-xs manake-secondary-button">
                                        {{ $whatsappNumber }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold manake-border text-current">
                                        {{ $whatsappNumber }}
                                    </span>
                                @endif
                            @empty
                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold manake-border text-current">
                                    {{ $footerWhatsapp }}
                                </span>
                            @endforelse
                        </div>
                    </div>
                    <p class="break-all pt-1">Email: <span class="manake-text font-semibold">{{ $footerEmail }}</span></p>
                    <p>Instagram: <span class="manake-text font-semibold">{{ $footerInstagram }}</span></p>
                </div>
            </div>

            <div class="pt-1">
                <h3 class="manake-kicker mb-2">{{ __('app.footer.address_title') }}</h3>
                <div class="space-y-1 text-xs leading-relaxed manake-text-muted">
                    @if ($footerAddressTitle)
                        <p class="font-bold manake-text">{{ $footerAddressTitle }}</p>
                    @endif
                    @foreach ($footerAddressRest as $addressLine)
                        <p>{{ $addressLine }}</p>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Column 3: Location / Map Card --}}
        <div class="space-y-4">
            <h3 class="manake-kicker">{{ __('app.footer.location_title') }}</h3>
            <div class="relative overflow-hidden rounded-2xl p-4 manake-surface">
                <div class="flex flex-col justify-between min-h-[120px] gap-3">
                    <div>
                        <p class="text-[8px] font-black uppercase tracking-[0.22em] manake-accent-text">{{ __('app.footer.coordinates') }}</p>
                        <p class="mt-1.5 text-xs font-semibold manake-text">{{ $footerAddressTitle ?: __('Manake Studio & Rental') }}</p>
                        <p class="mt-1 text-[11px] leading-relaxed manake-text-muted">{{ $footerAddressRest->implode(' ') }}</p>
                    </div>
                    <div class="flex">
                        <a href="{{ $footerMapUrl }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 text-xs manake-secondary-button">
                            {{ __('app.footer.location_open_map') }}
                            <span aria-hidden="true" class="ml-1">↗</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="border-t manake-border bg-[var(--manake-bg-soft)]">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-4 text-[11px] manake-text-muted sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <span>&copy; {{ app()->getLocale() === 'en' ? __('app.footer.copyright') : setting('footer_copyright', __('app.footer.copyright')) }}</span>
            <span class="font-medium opacity-80">{{ app()->getLocale() === 'en' ? __('app.footer.tagline') : setting('site_tagline', __('app.footer.tagline')) }}</span>
        </div>
    </div>
</footer>
