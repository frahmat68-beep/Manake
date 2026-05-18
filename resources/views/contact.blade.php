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
    <section class="mk-section">
        <div class="mk-container space-y-6">
            <div class="mk-card p-6 sm:p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">{{ __('ui.contact.title') }}</p>
                <h1 class="mk-title-section mt-3">{{ __('ui.contact.info_title') }}</h1>
                <p class="mk-copy mt-4 leading-relaxed">{{ __('ui.contact.subtitle') }}</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
                <div class="mk-card p-6">
                    <h2 class="text-lg font-bold text-slate-900">{{ __('ui.contact.info_title') }}</h2>
                    <div class="mt-4 space-y-1 text-sm leading-relaxed text-slate-600">
                        @if ($contactAddressTitle)
                            <p class="font-bold text-slate-900">{{ $contactAddressTitle }}</p>
                        @endif
                        @foreach ($contactAddressRest as $addressLine)
                            <p>{{ $addressLine }}</p>
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.contact.labels.whatsapp') }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($contactWhatsappEntries as $whatsappNumber)
                                @php $whatsappHref = $buildWhatsappHref($whatsappNumber); @endphp
                                @if ($whatsappHref)
                                    <a
                                        href="{{ $whatsappHref }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mk-button-primary py-2 px-4 text-sm font-bold"
                                    >
                                        {{ $whatsappNumber }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white">
                                        {{ $whatsappNumber }}
                                    </span>
                                @endif
                            @empty
                                <span class="text-sm text-slate-600">{{ $contactWhatsapp }}</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.contact.labels.email') }}</p>
                            <a href="mailto:{{ $contactEmail }}" class="mt-1 block break-all text-sm font-bold text-slate-800 hover:text-blue-700">
                                {{ $contactEmail }}
                            </a>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.contact.labels.instagram') }}</p>
                            @if ($instagramUrl)
                                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="mt-1 block text-sm font-bold text-slate-800 hover:text-blue-700">
                                    {{ $contactInstagram }}
                                </a>
                            @else
                                <p class="mt-1 text-sm font-bold text-slate-800">{{ $contactInstagram }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mk-card p-6">
                    <h2 class="text-lg font-bold text-slate-900">{{ __('ui.contact.map_title') }}</h2>
                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                        @if ($contactMapEmbed)
                            <div class="[&>iframe]:h-[280px] [&>iframe]:w-full [&>iframe]:border-0">
                                {!! $contactMapEmbed !!}
                            </div>
                        @else
                            <div class="flex h-48 items-center justify-center text-sm text-slate-500">
                                {{ __('ui.contact.map_empty') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

