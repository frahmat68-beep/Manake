@php
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

    $resolvedTheme = $themeResolved ?? request()->attributes->get('theme_resolved', 'light');
    $isLightShell = $resolvedTheme === 'light';

    $footerShellClass = $isLightShell
        ? 'border-t border-[#E5E2DA] bg-[#F7F7F4] text-[#171717] shadow-[inset_0_1px_0_rgba(255,255,255,0.8)]'
        : 'border-t border-white/5 bg-[#0A0A0B] text-[#E8E8EC]';

    $kickerClass = $isLightShell ? 'text-blue-600' : 'text-[#D4A843]';

    $linkClass = $isLightShell
        ? 'text-[#171717] hover:text-blue-600 border-[#E5E2DA] bg-white hover:border-blue-600/50 hover:bg-blue-50/20'
        : 'text-[#E8E8EC] hover:text-[#D4A843] border-white/10 bg-white/5 hover:border-[#D4A843]/45 hover:bg-[#D4A843]/5';

    $mapCardClass = $isLightShell
        ? 'border-[#E5E2DA] bg-white shadow-[0_10px_30px_rgba(0,0,0,0.03)]'
        : 'border-white/10 bg-[#111113]/60 backdrop-blur-md shadow-lg';

    $bottomBarClass = $isLightShell ? 'border-t border-[#E5E2DA] bg-[#F2F0EA]' : 'border-t border-white/5 bg-[#070708]';
    $textMutedClass = $isLightShell ? 'text-[#666666]' : 'text-[#A0A0A8]';
    $textPrimaryClass = $isLightShell ? 'text-[#171717]' : 'text-[#E8E8EC]';
@endphp

<footer id="contact" class="{{ $footerShellClass }}">
    <div class="mx-auto grid max-w-7xl gap-10 px-6 py-16 sm:px-8 lg:grid-cols-[1.1fr,0.9fr,1fr] lg:gap-12 lg:py-20 items-start">
        {{-- Column 1: Brand Logo & Guidelines --}}
        <div class="space-y-6">
            <x-brand.image light="manake-logo-blue.png" dark="manake-logo-white.png" alt="Manake" img-class="h-8 md:h-9 w-auto" class="inline-flex" />
            <p class="max-w-sm text-sm leading-relaxed {{ $textMutedClass }}">{{ $footerAbout }}</p>

            <div class="space-y-3 pt-2">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] {{ $kickerClass }}">{{ __('app.footer.rules_title') }}</p>
                <a href="{{ route('rental.rules') }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-xs font-semibold transition {{ $linkClass }}">
                    {{ app()->getLocale() === 'en' ? __('app.footer.rules_link') : setting('footer.rules_link', __('app.footer.rules_link')) }}
                    <span aria-hidden="true">→</span>
                </a>
                <p class="max-w-sm text-xs leading-relaxed {{ $textMutedClass }}">{{ app()->getLocale() === 'en' ? __('app.footer.rules_note') : setting('footer.rules_note', __('app.footer.rules_note')) }}</p>
            </div>
        </div>

        {{-- Column 2: Contact & Address --}}
        <div class="space-y-6">
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-[0.24em] {{ $kickerClass }} mb-4">{{ __('app.footer.contact_title') }}</h3>
                <div class="space-y-3 text-sm {{ $textMutedClass }}">
                    <div class="flex flex-col gap-2">
                        <span class="text-xs font-bold {{ $textPrimaryClass }}">{{ __('WhatsApp') }}</span>
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
                                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $linkClass }}"
                                    >
                                        {{ $whatsappNumber }}
                                    </a>
                                @elseif ($telHref)
                                    <a href="{{ $telHref }}" class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $linkClass }}">
                                        {{ $whatsappNumber }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold text-current">
                                        {{ $whatsappNumber }}
                                    </span>
                                @endif
                            @empty
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold text-current">
                                    {{ $footerWhatsapp }}
                                </span>
                            @endforelse
                        </div>
                    </div>
                    <p class="break-all">Email: <span class="{{ $textPrimaryClass }} font-semibold">{{ $footerEmail }}</span></p>
                    <p>Instagram: <span class="{{ $textPrimaryClass }} font-semibold">{{ $footerInstagram }}</span></p>
                </div>
            </div>

            <div>
                <h3 class="text-[10px] font-black uppercase tracking-[0.24em] {{ $kickerClass }} mb-4">{{ __('app.footer.address_title') }}</h3>
                <div class="space-y-1.5 text-sm leading-relaxed {{ $textMutedClass }}">
                    @if ($footerAddressTitle)
                        <p class="font-bold {{ $textPrimaryClass }}">{{ $footerAddressTitle }}</p>
                    @endif
                    @foreach ($footerAddressRest as $addressLine)
                        <p>{{ $addressLine }}</p>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Column 3: Location / Map Card --}}
        <div>
            <h3 class="text-[10px] font-black uppercase tracking-[0.24em] {{ $kickerClass }} mb-4">{{ __('app.footer.location_title') }}</h3>
            <div class="relative overflow-hidden rounded-2xl border p-5 {{ $mapCardClass }}">
                <div class="flex flex-col justify-between min-h-[160px] gap-4">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.22em] {{ $kickerClass }}">{{ app()->getLocale() === 'id' ? 'KOORDINAT' : 'COORDINATES' }}</p>
                        <p class="mt-2 text-sm font-semibold {{ $textPrimaryClass }}">{{ $footerAddressTitle ?: __('Manake Studio & Rental') }}</p>
                        <p class="mt-1.5 text-xs leading-relaxed {{ $textMutedClass }}">{{ $footerAddressRest->implode(' ') }}</p>
                    </div>
                    <a href="{{ $footerMapUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit items-center gap-2 rounded-xl border px-4 py-2.5 text-xs font-semibold transition {{ $linkClass }}">
                        {{ __('app.footer.location_open_map') }}
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="{{ $bottomBarClass }}">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-6 py-5 text-xs {{ $textMutedClass }} sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <span>&copy; {{ app()->getLocale() === 'en' ? __('app.footer.copyright') : setting('footer_copyright', __('app.footer.copyright')) }}</span>
            <span class="font-medium opacity-80">{{ app()->getLocale() === 'en' ? __('app.footer.tagline') : setting('site_tagline', __('app.footer.tagline')) }}</span>
        </div>
    </div>
</footer>
