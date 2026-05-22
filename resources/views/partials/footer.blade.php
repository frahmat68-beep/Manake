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
    $defaultFooterMapEmbed = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.142828244342!2d106.78129727579194!3d-6.375555993614689!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ef01a88d499d%3A0x15293a04b517553a!2sManake%20-%20Sewa%20HT%2C%20Alat%20Event%20dan%20Film!5e0!3m2!1sen!2sid!4v1776856962246!5m2!1sen!2sid" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
    $footerMapRaw = $defaultFooterMapEmbed;
    $footerMapEmbed = trusted_map_embed_iframe($footerMapRaw, $footerAddress);
@endphp

<footer id="contact" class="border-t border-[#1A1A1E] bg-[#0A0A0B] text-[#E8E8EC]">
    <div class="mx-auto grid max-w-7xl gap-8 px-6 py-12 lg:grid-cols-[1.1fr,1fr,1.2fr]">
        <div>
            <x-brand.image light="manake-logo-blue.png" dark="manake-logo-blue.png" alt="Manake" img-class="h-12 w-auto" class="mb-5 inline-flex" />
            <p class="mt-3 max-w-md text-sm leading-relaxed text-[#A0A0A8]">{{ $footerAbout }}</p>
            <div class="mt-5">
                <p class="text-sm font-semibold text-[#E8E8EC]">{{ setting('footer.rules_title', __('app.footer.rules_title')) }}</p>
                <a href="{{ route('rental.rules') }}" class="mt-2 inline-flex items-center gap-1 rounded-md border border-[#1A1A1E] bg-[#111113] px-3 py-1.5 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                    {{ setting('footer.rules_link', __('app.footer.rules_link')) }}
                    <span aria-hidden="true">→</span>
                </a>
                <p class="mt-2 text-sm leading-relaxed text-[#A0A0A8]">{{ setting('footer.rules_note', __('app.footer.rules_note')) }}</p>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-[#E8E8EC]">{{ __('app.footer.contact_title') }}</h3>
            <div class="mt-3 space-y-2 text-sm text-[#A0A0A8]">
                <p class="text-sm font-semibold text-[#E8E8EC]">WhatsApp</p>
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
                                class="inline-flex items-center gap-1 rounded-md border border-[#1A1A1E] bg-[#111113] px-2.5 py-1 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]"
                            >
                                {{ $whatsappNumber }}
                            </a>
                        @elseif ($telHref)
                            <a href="{{ $telHref }}" class="inline-flex items-center gap-1 rounded-md border border-[#1A1A1E] bg-[#111113] px-2.5 py-1 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                {{ $whatsappNumber }}
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-md border border-[#1A1A1E] bg-[#111113] px-2.5 py-1 text-xs font-semibold text-[#E8E8EC]">
                                {{ $whatsappNumber }}
                            </span>
                        @endif
                    @empty
                        <span class="inline-flex items-center gap-1 rounded-md border border-[#1A1A1E] bg-[#111113] px-2.5 py-1 text-xs font-semibold text-[#E8E8EC]">
                            {{ $footerWhatsapp }}
                        </span>
                    @endforelse
                </div>
                <p class="break-all">Email: <span class="text-[#E8E8EC]">{{ $footerEmail }}</span></p>
                <p>Instagram: <span class="text-[#E8E8EC]">{{ $footerInstagram }}</span></p>
            </div>
            <h3 class="mt-5 text-sm font-semibold text-[#E8E8EC]">{{ __('app.footer.address_title') }}</h3>
            <div class="mt-2 space-y-1 text-sm leading-relaxed text-[#A0A0A8]">
                @if ($footerAddressTitle)
                    <p class="font-semibold text-[#E8E8EC]">{{ $footerAddressTitle }}</p>
                @endif
                @foreach ($footerAddressRest as $addressLine)
                    <p>{{ $addressLine }}</p>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-[#E8E8EC]">{{ __('app.footer.location_title') }}</h3>
            <div class="mt-3 overflow-hidden rounded-lg border border-[#1A1A1E] bg-[#111113]">
                <div class="border-b border-[#1A1A1E] px-4 py-3">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#D4A843]">{{ __('app.footer.location_title') }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-[#A0A0A8]">{{ $footerAddressTitle ?: __('Manake Studio & Rental') }}</p>
                </div>
                <div class="relative min-h-[180px] bg-[radial-gradient(circle_at_top_left,_rgba(212,168,67,0.12),_transparent_26%),linear-gradient(135deg,rgba(10,10,11,0.98),rgba(17,17,19,0.96))]">
                    @if (! empty($footerMapEmbed))
                        <div class="relative min-h-[220px] [&>iframe]:h-[220px] [&>iframe]:w-full [&>iframe]:border-0">
                            {!! $footerMapEmbed !!}
                        </div>
                    @endif
                    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_top,rgba(15,23,42,0.24),transparent_40%)]"></div>
                    <div class="pointer-events-none absolute bottom-3 left-3 right-3 flex items-center justify-between gap-3 rounded-md border border-[#1A1A1E] bg-[#0A0A0B]/75 px-3 py-2 text-xs text-[#E8E8EC] backdrop-blur">
                        <span class="font-semibold">{{ $footerAddressTitle ?: __('Manake Studio & Rental') }}</span>
                        <span class="rounded-full border border-[#1A1A1E] bg-[#111113] px-2 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#D4A843]">{{ __('Open in maps') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-[#1A1A1E]">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-4 text-xs text-[#A0A0A8] sm:flex-row sm:items-center sm:justify-between">
            <span>&copy; {{ setting('footer_copyright', __('app.footer.copyright')) }}</span>
            <span>{{ setting('site_tagline', __('app.footer.tagline')) }}</span>
        </div>
    </div>
</footer>
