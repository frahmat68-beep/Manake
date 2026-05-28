@php
    $footerAbout = setting('footer.about', setting('footer_description', site_content('footer.about', __('app.footer.about_body'))));
    $footerAddress = setting('footer.address', setting('footer_address', site_content('footer.address', __('app.footer.address_body'))));
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

<footer id="contact" class="border-t border-white/5 bg-[#0A0A0B] text-[#E8E8EC]">
    <div class="mx-auto grid max-w-7xl gap-10 px-6 py-16 sm:px-8 lg:grid-cols-[1.1fr,0.9fr,1fr] lg:gap-12 lg:py-20 items-start">
        {{-- Column 1: Brand Logo & Guidelines --}}
        <div class="space-y-6">
            <x-brand.image light="manake-logo-white.png" dark="manake-logo-white.png" alt="Manake" img-class="h-8 md:h-9 w-auto" class="inline-flex" />
            <p class="max-w-sm text-sm leading-relaxed text-[#A0A0A8]">{{ $footerAbout }}</p>
            
            <div class="space-y-3 pt-2">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#D4A843]">{{ __('PANDUAN SEWA') }}</p>
                <a href="{{ route('rental.rules') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843] hover:bg-[#D4A843]/5">
                    {{ setting('footer.rules_link', __('app.footer.rules_link')) }}
                    <span aria-hidden="true">→</span>
                </a>
                <p class="max-w-sm text-xs leading-relaxed text-[#A0A0A8]">{{ setting('footer.rules_note', __('app.footer.rules_note')) }}</p>
            </div>
        </div>

        {{-- Column 2: Contact & Address --}}
        <div class="space-y-6">
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-[0.24em] text-[#D4A843] mb-4">{{ __('HUBUNGI KAMI') }}</h3>
                <div class="space-y-3 text-sm text-[#A0A0A8]">
                    <div class="flex flex-col gap-2">
                        <span class="text-xs font-bold text-[#E8E8EC]">{{ __('WhatsApp') }}</span>
                        <div class="flex flex-wrap gap-2">
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
                                        class="inline-flex items-center gap-1.5 rounded-full border border-white/8 bg-white/5 px-3 py-1.5 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/45 hover:text-[#D4A843] hover:bg-[#D4A843]/5"
                                    >
                                        {{ $whatsappNumber }}
                                    </a>
                                @elseif ($telHref)
                                    <a href="{{ $telHref }}" class="inline-flex items-center gap-1.5 rounded-full border border-white/8 bg-white/5 px-3 py-1.5 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/45 hover:text-[#D4A843] hover:bg-[#D4A843]/5">
                                        {{ $whatsappNumber }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/8 bg-white/5 px-3 py-1.5 text-xs font-semibold text-[#E8E8EC]">
                                        {{ $whatsappNumber }}
                                    </span>
                                @endif
                            @empty
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/8 bg-white/5 px-3 py-1.5 text-xs font-semibold text-[#E8E8EC]">
                                    {{ $footerWhatsapp }}
                                </span>
                            @endforelse
                        </div>
                    </div>
                    <p class="break-all">Email: <span class="text-[#E8E8EC] font-semibold">{{ $footerEmail }}</span></p>
                    <p>Instagram: <span class="text-[#E8E8EC] font-semibold">{{ $footerInstagram }}</span></p>
                </div>
            </div>

            <div>
                <h3 class="text-[10px] font-black uppercase tracking-[0.24em] text-[#D4A843] mb-4">{{ __('ALAMAT') }}</h3>
                <div class="space-y-1.5 text-sm leading-relaxed text-[#A0A0A8]">
                    @if ($footerAddressTitle)
                        <p class="font-bold text-[#E8E8EC]">{{ $footerAddressTitle }}</p>
                    @endif
                    @foreach ($footerAddressRest as $addressLine)
                        <p>{{ $addressLine }}</p>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Column 3: Location / Map Card --}}
        <div>
            <h3 class="text-[10px] font-black uppercase tracking-[0.24em] text-[#D4A843] mb-4">{{ __('LOKASI') }}</h3>
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#111113]/60 backdrop-blur-md p-5 shadow-lg">
                <div class="flex flex-col justify-between min-h-[160px] gap-4">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.22em] text-[#D4A843]">{{ __('KOORDINAT') }}</p>
                        <p class="mt-2 text-sm font-semibold text-[#E8E8EC]">{{ $footerAddressTitle ?: __('Manake Studio & Rental') }}</p>
                        <p class="mt-1.5 text-xs leading-relaxed text-[#A0A0A8]">{{ $footerAddressRest->implode(' ') }}</p>
                    </div>
                    <a href="{{ $footerMapUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843] hover:bg-[#D4A843]/5">
                        {{ __('Open in maps') }}
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="border-t border-white/5 bg-[#070708]">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-6 py-5 text-xs text-[#A0A0A8] sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <span>&copy; {{ setting('footer_copyright', __('app.footer.copyright')) }}</span>
            <span class="font-medium text-[#707078]">{{ setting('site_tagline', __('app.footer.tagline')) }}</span>
        </div>
    </div>
</footer>
