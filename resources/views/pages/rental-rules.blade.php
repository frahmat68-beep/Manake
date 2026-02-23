@extends('layouts.app')

@section('title', __('ui.rental_rules.page_title'))
@section('meta_description', __('ui.rental_rules.meta_description'))

@php
    $rulesKicker = setting('copy.rules_page.kicker', __('ui.rental_rules.kicker'));
    $rulesTitle = setting('copy.rules_page.title', __('ui.rental_rules.title'));
    $rulesSubtitle = setting('copy.rules_page.subtitle', __('ui.rental_rules.subtitle'));
    $rulesOperationalTitle = setting('copy.rules_page.operational_title', __('ui.rental_rules.operational_title'));
    $rulesPrimaryCta = setting('copy.rules_page.cta_primary', __('ui.rental_rules.cta_primary'));
    $rulesSecondaryCta = setting('copy.rules_page.cta_secondary', __('ui.rental_rules.cta_secondary'));
@endphp

@section('content')
    <section class="mx-auto max-w-6xl space-y-6">
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

        <article class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <h2 class="text-lg font-semibold">{{ $rulesOperationalTitle }}</h2>
            <p class="mt-2 text-sm text-slate-600">
                {{ __('ui.rental_rules.operational_note') }}
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    {{ $rulesPrimaryCta }}
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">
                    {{ $rulesSecondaryCta }}
                </a>
            </div>
        </article>
    </section>
@endsection
