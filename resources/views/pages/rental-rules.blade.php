@extends('layouts.landing')

@section('title', __('ui.rental_rules.page_title'))
@section('meta_description', __('ui.rental_rules.meta_description'))

@php
    $rulesKicker = setting('copy.rules_page.kicker', __('ui.rental_rules.kicker'));
    $rulesTitle = setting('copy.rules_page.title', __('ui.rental_rules.title'));
    $rulesSubtitle = setting('copy.rules_page.subtitle', __('ui.rental_rules.subtitle'));
    $rulesOperationalTitle = setting('copy.rules_page.operational_title', __('ui.rental_rules.operational_title'));
    $rulesPrimaryCta = setting('copy.rules_page.cta_primary', __('ui.rental_rules.cta_primary'));
    $rulesSecondaryCta = setting('copy.rules_page.cta_secondary', __('ui.rental_rules.cta_secondary'));

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

@section('content')
    <section x-data="{ contactModalOpen: false }" class="mk-section">
        <div class="mk-container space-y-6">
            <div class="rounded-3xl border border-blue-100 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">{{ $rulesKicker }}</p>
            <h1 class="mt-2 text-3xl font-extrabold text-blue-700">{{ $rulesTitle }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600">
                {{ $rulesSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">{{ __('ui.rental_rules.sections.booking_payment.title') }}</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>{{ __('ui.rental_rules.sections.booking_payment.point_1') }}</li>
                    <li>{{ __('ui.rental_rules.sections.booking_payment.point_2') }}</li>
                    <li>{{ __('ui.rental_rules.sections.booking_payment.point_3') }}</li>
                </ul>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">{{ __('ui.rental_rules.sections.availability.title') }}</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>{{ __('ui.rental_rules.sections.availability.point_1') }}</li>
                    <li>{{ __('ui.rental_rules.sections.availability.point_2') }}</li>
                    <li>{{ __('ui.rental_rules.sections.availability.point_3') }}</li>
                </ul>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">{{ __('ui.rental_rules.sections.reschedule.title') }}</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>{{ __('ui.rental_rules.sections.reschedule.point_1') }}</li>
                    <li>{{ __('ui.rental_rules.sections.reschedule.point_2') }}</li>
                    <li>{{ __('ui.rental_rules.sections.reschedule.point_3') }}</li>
                    <li>{{ __('ui.rental_rules.sections.reschedule.point_4') }}</li>
                </ul>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">{{ __('ui.rental_rules.sections.penalty.title') }}</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>{{ __('ui.rental_rules.sections.penalty.point_1') }}</li>
                    <li>{{ __('ui.rental_rules.sections.penalty.point_2') }}</li>
                    <li>{{ __('ui.rental_rules.sections.penalty.point_3') }}</li>
                    <li>{{ __('ui.rental_rules.sections.penalty.point_4') }}</li>
                    <li>{{ __('ui.rental_rules.sections.penalty.point_5') }}</li>
                </ul>
            </article>
        </div>

        <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-blue-700">{{ __('ui.rental_rules.user_flow_title') }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ __('ui.rental_rules.user_flow_intro') }}</p>
            <ol class="mt-4 space-y-2 text-sm text-slate-700">
                <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ __('ui.rental_rules.sections.user_flow.point_1') }}</li>
                <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ __('ui.rental_rules.sections.user_flow.point_2') }}</li>
                <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ __('ui.rental_rules.sections.user_flow.point_3') }}</li>
                <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ __('ui.rental_rules.sections.user_flow.point_4') }}</li>
                <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ __('ui.rental_rules.sections.user_flow.point_5') }}</li>
                <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ __('ui.rental_rules.sections.user_flow.point_6') }}</li>
                <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ __('ui.rental_rules.sections.user_flow.point_7') }}</li>
            </ol>
        </article>

        <article class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <h2 class="text-lg font-semibold">{{ $rulesOperationalTitle }}</h2>
            <p class="mt-2 text-sm text-slate-600">
                {{ __('ui.rental_rules.operational_note') }}
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    {{ $rulesPrimaryCta }}
                </a>
                <button
                    type="button"
                    @click="contactModalOpen = true"
                    class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50"
                >
                    {{ $rulesSecondaryCta }}
                </button>
            </div>
        </article>

        <div
            x-cloak
            x-show="contactModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-900/60 p-4"
            @click.self="contactModalOpen = false"
            @keydown.escape.window="contactModalOpen = false"
        >
            <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">{{ __('ui.contact.title') }}</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('ui.contact.info_title') }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ __('ui.contact.subtitle') }}</p>
                    </div>
                    <button
                        type="button"
                        @click="contactModalOpen = false"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-blue-200 hover:text-blue-600"
                        aria-label="{{ __('ui.actions.close') }}"
                    >
                        ✕
                    </button>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="space-y-1 text-sm leading-relaxed text-slate-700">
                            @if ($contactAddressTitle)
                                <p class="font-semibold text-slate-900">{{ $contactAddressTitle }}</p>
                            @endif
                            @foreach ($contactAddressRest as $addressLine)
                                <p>{{ $addressLine }}</p>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.contact.labels.whatsapp') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse ($contactWhatsappEntries as $whatsappNumber)
                                    @php $whatsappHref = $buildWhatsappHref($whatsappNumber); @endphp
                                    @if ($whatsappHref)
                                        <a
                                            href="{{ $whatsappHref }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                                        >
                                            {{ $whatsappNumber }}
                                        </a>
                                    @else
                                        <span class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white">
                                            {{ $whatsappNumber }}
                                        </span>
                                    @endif
                                @empty
                                    <span class="text-sm text-slate-700">{{ $contactWhatsapp }}</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-4 space-y-2 text-sm text-slate-700">
                            <p>
                                <span class="font-semibold text-slate-900">{{ __('ui.contact.labels.email') }}:</span>
                                <a href="mailto:{{ $contactEmail }}" class="break-all text-blue-700 hover:text-blue-800">{{ $contactEmail }}</a>
                            </p>
                            <p>
                                <span class="font-semibold text-slate-900">{{ __('ui.contact.labels.instagram') }}:</span>
                                @if ($instagramUrl)
                                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:text-blue-800">{{ $contactInstagram }}</a>
                                @else
                                    {{ $contactInstagram }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                        <p class="text-sm font-semibold text-slate-900">{{ __('ui.contact.map_title') }}</p>
                        <div class="mt-2 overflow-hidden rounded-lg border border-slate-200">
                            @if ($contactMapEmbed)
                                <div class="[&>iframe]:h-[220px] [&>iframe]:w-full [&>iframe]:border-0">
                                    {!! $contactMapEmbed !!}
                                </div>
                            @else
                                <div class="flex h-[220px] items-center justify-center px-3 text-center text-sm text-slate-500">
                                    {{ __('ui.contact.map_empty') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
