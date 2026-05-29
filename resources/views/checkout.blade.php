@extends('layouts.app')

@section('title', setting('copy.checkout.title', __('ui.checkout.title')))

@push('head')
    <style>
        .checkout-page {
            --checkout-accent: #D4A843;
            --checkout-accent-hover: #E0BA5D;
            --checkout-accent-text: #0A0A0B;
            --checkout-accent-soft: rgba(212, 168, 67, 0.12);
            --checkout-accent-border: rgba(212, 168, 67, 0.28);
            --checkout-accent-glow: rgba(212, 168, 67, 0.18);

            --checkout-bg: #0A0A0B;
            --checkout-surface: #111113;
            --checkout-surface-soft: rgba(17, 17, 19, 0.72);
            --checkout-surface-muted: #0A0A0B;
            --checkout-border: #1A1A1E;
            --checkout-text: #E8E8EC;
            --checkout-muted: #A0A0A8;
            --checkout-placeholder: #66666C;
        }

        html[data-theme-resolved="light"] .checkout-page {
            --checkout-accent: #2563EB;
            --checkout-accent-hover: #1D4ED8;
            --checkout-accent-text: #FFFFFF;
            --checkout-accent-soft: rgba(37, 99, 235, 0.10);
            --checkout-accent-border: rgba(37, 99, 235, 0.24);
            --checkout-accent-glow: rgba(37, 99, 235, 0.12);

            --checkout-bg: #F8FAFC;
            --checkout-surface: #FFFFFF;
            --checkout-surface-soft: rgba(255, 255, 255, 0.92);
            --checkout-surface-muted: #F8FAFC;
            --checkout-border: #E5E7EB;
            --checkout-text: #111827;
            --checkout-muted: #4B5563;
            --checkout-placeholder: #6B7280;
        }

        .checkout-page-bg {
            background-color: var(--checkout-bg) !important;
            color: var(--checkout-text) !important;
        }

        .checkout-card {
            background: var(--checkout-surface-soft) !important;
            border-color: var(--checkout-border) !important;
            color: var(--checkout-text) !important;
        }

        .checkout-card-solid {
            background: var(--checkout-surface) !important;
            border-color: var(--checkout-border) !important;
            color: var(--checkout-text) !important;
        }

        .checkout-inner {
            background: var(--checkout-surface-muted) !important;
            border-color: var(--checkout-border) !important;
            color: var(--checkout-text) !important;
        }

        .checkout-title {
            color: var(--checkout-text) !important;
        }

        .checkout-muted {
            color: var(--checkout-muted) !important;
        }

        .checkout-border {
            border-color: var(--checkout-border) !important;
        }

        .checkout-accent-text {
            color: var(--checkout-accent) !important;
        }

        .checkout-accent-bg {
            background: var(--checkout-accent) !important;
            background-color: var(--checkout-accent) !important;
            color: var(--checkout-accent-text) !important;
            border-color: var(--checkout-accent) !important;
        }

        .checkout-accent-bg:hover {
            background: var(--checkout-accent-hover) !important;
            background-color: var(--checkout-accent-hover) !important;
        }

        .checkout-accent-soft {
            background: var(--checkout-accent-soft) !important;
            border-color: var(--checkout-accent-border) !important;
            color: var(--checkout-accent) !important;
        }

        .checkout-accent-glow {
            background-color: var(--checkout-accent-glow) !important;
        }

        .checkout-accent-dot {
            background-color: var(--checkout-accent) !important;
        }

        .checkout-secondary-button {
            background: var(--checkout-surface) !important;
            border: 1px solid var(--checkout-border) !important;
            color: var(--checkout-text) !important;
        }

        .checkout-secondary-button:hover {
            border-color: var(--checkout-accent-border) !important;
            color: var(--checkout-accent) !important;
        }

        .checkout-input {
            background: var(--checkout-surface-muted) !important;
            border: 1px solid var(--checkout-border) !important;
            color: var(--checkout-text) !important;
            border-radius: 0.75rem !important;
            outline: none !important;
        }

        .checkout-input:disabled {
            opacity: 1 !important;
            -webkit-text-fill-color: var(--checkout-text) !important;
        }

        .checkout-textarea {
            background: var(--checkout-surface-muted) !important;
            border: 1px solid var(--checkout-border) !important;
            color: var(--checkout-text) !important;
            border-radius: 0.75rem !important;
            outline: none !important;
            resize: none;
        }

        .checkout-textarea:disabled {
            opacity: 1 !important;
            -webkit-text-fill-color: var(--checkout-text) !important;
        }

        .checkout-checkbox {
            border-color: var(--checkout-border) !important;
            color: var(--checkout-accent) !important;
        }

        .checkout-checkbox:focus {
            box-shadow: 0 0 0 3px var(--checkout-accent-soft) !important;
        }

        .checkout-accent-link:hover {
            color: var(--checkout-accent-hover) !important;
        }

        html[data-theme-resolved="light"] .checkout-page .checkout-card,
        html[data-theme-resolved="light"] .checkout-page .checkout-card-solid {
            box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.22);
        }

        .checkout-alert-info {
            border-color: #CBD5E1 !important;
            background: #F8FAFC !important;
            color: #334155 !important;
        }

        .checkout-alert-success {
            border-color: rgba(16, 185, 129, 0.28) !important;
            background: #ECFDF5 !important;
            color: #047857 !important;
        }

        .checkout-alert-error {
            border-color: rgba(244, 63, 94, 0.28) !important;
            background: #FFF1F2 !important;
            color: #BE123C !important;
        }

        html[data-theme-resolved="dark"] .checkout-alert-info {
            border-color: rgba(148, 163, 184, 0.28) !important;
            background: rgba(15, 23, 42, 0.40) !important;
            color: #CBD5E1 !important;
        }

        html[data-theme-resolved="dark"] .checkout-alert-success {
            background: rgba(6, 78, 59, 0.38) !important;
            color: #A7F3D0 !important;
        }

        html[data-theme-resolved="dark"] .checkout-alert-error {
            background: rgba(136, 19, 55, 0.38) !important;
            color: #FDA4AF !important;
        }
    </style>
@endpush

@section('content')
    @php
        $formatIdr = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
        $isCartEmpty = empty($cartItems);
        $estimatedSubtotal = (int) ($estimatedSubtotal ?? 0);
        $taxAmount = (int) ($taxAmount ?? 0);
        $estimatedTotal = (int) ($estimatedTotal ?? ($estimatedSubtotal + $taxAmount));
        $checkoutTitle = setting('copy.checkout.title', __('ui.checkout.title'));
        $checkoutSubtitle = setting('copy.checkout.subtitle', __('ui.checkout.subtitle'));
        $checkoutBackToCart = setting('copy.checkout.back_to_cart', __('ui.checkout.back_to_cart'));
        $checkoutDetailTitle = setting('copy.checkout.detail_title', __('ui.checkout.detail_title'));
        $checkoutEmptyCart = setting('copy.checkout.empty_cart', __('ui.checkout.empty_cart'));
        $checkoutProfileTitle = setting('copy.checkout.profile_title', __('ui.checkout.profile_title'));
        $checkoutProfileHint = setting('copy.checkout.profile_hint', __('ui.checkout.profile_hint'));
        $checkoutConfirmProfile = setting('copy.checkout.confirm_profile', __('ui.checkout.confirm_profile'));
        $checkoutSubmitButton = setting('copy.checkout.submit_button', __('ui.checkout.submit_button'));
        $checkoutSubmitProcessing = setting('copy.checkout.submit_processing', __('ui.checkout.submit_processing'));
        $checkoutPaymentTitle = setting('copy.checkout.payment_title', __('ui.checkout.payment_title'));
        $checkoutPaymentNote = setting('copy.checkout.payment_note', __('ui.checkout.payment_note'));
        $checkoutSummaryTitle = setting('copy.checkout.summary_title', __('ui.checkout.summary_title'));
        $checkoutSummarySubtotal = setting('copy.checkout.summary_subtotal', __('ui.checkout.summary_subtotal'));
        $checkoutSummaryEstimate = setting('copy.checkout.summary_estimate', __('ui.checkout.summary_estimate'));
        $checkoutSummaryTax = setting('copy.checkout.summary_tax', __('ui.checkout.summary_tax'));
        $checkoutSummaryTotal = setting('copy.checkout.summary_total', __('ui.checkout.summary_total'));
        $checkoutQtyTemplate = __('ui.checkout.qty_template');
        $checkoutInvalidDateNote = __('ui.checkout.invalid_date_note');
        $checkoutProfileNameLabel = __('ui.checkout.profile_name_label');
        $checkoutProfilePhoneLabel = __('ui.checkout.profile_phone_label');
        $checkoutProfileAddressLabel = __('ui.checkout.profile_address_label');
        $checkoutProfileUpdateLinkLabel = __('ui.checkout.profile_update_link_label');
        $checkoutMsgSyncFailed = __('ui.checkout.messages.sync_failed');
        $checkoutMsgCheckoutFailed = __('ui.checkout.messages.checkout_failed');
        $checkoutMsgCreated = __('ui.checkout.messages.created');
        $checkoutMsgSnapNotReady = __('ui.checkout.messages.snap_not_ready');
        $checkoutMsgPaySuccess = __('ui.checkout.messages.pay_success');
        $checkoutMsgPayPending = __('ui.checkout.messages.pay_pending');
        $checkoutMsgPayFailed = __('ui.checkout.messages.pay_failed');
        $checkoutMsgPopupClosed = __('ui.checkout.messages.popup_closed');
        $checkoutMsgGenericError = __('ui.checkout.messages.generic_error');

        $checkoutKicker = __('ui.checkout.kicker');
        $checkoutSecurityVerified = __('ui.checkout.security_verified');
        $checkoutUnitSingular = __('ui.checkout.unit_singular');
        $checkoutUnitPlural = __('ui.checkout.unit_plural');
        $checkoutEmptyCatalogCta = __('ui.checkout.empty_catalog_cta');
    @endphp

    <div class="checkout-page checkout-page-bg manake-page">
        <div class="manake-page-frame space-y-8">
            <header class="checkout-card rounded-3xl border p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.30)] animate-fade-up sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="checkout-accent-text text-xs font-black uppercase tracking-[0.2em]">{{ $checkoutKicker }}</p>
                        <h1 class="checkout-title mt-3 font-serif text-4xl font-black sm:text-5xl">
                            {{ $checkoutTitle }}
                        </h1>
                        <p class="checkout-muted mt-3 max-w-2xl text-sm leading-7 sm:text-base">
                            {{ $checkoutSubtitle }}
                        </p>
                    </div>
                    <a href="{{ route('cart') }}" class="checkout-secondary-button inline-flex w-full items-center justify-center rounded-xl px-5 py-3 font-bold transition lg:w-auto">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ $checkoutBackToCart }}
                    </a>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-start">
                <div class="lg:col-span-8 space-y-8">
                    <div id="checkout-alert" class="hidden rounded-md border px-5 py-4 text-sm font-semibold"></div>

                    <article class="checkout-card rounded-3xl border p-6 shadow-2xl animate-fade-up sm:p-8">
                        <h2 class="checkout-title flex items-center gap-3 text-2xl font-black">
                            <span class="checkout-accent-dot h-8 w-1.5 rounded-full"></span>
                            {{ $checkoutDetailTitle }}
                        </h2>

                        @if ($isCartEmpty)
                            <div class="checkout-border mt-8 rounded-md border border-dashed px-6 py-12 text-center">
                                <p class="checkout-muted text-lg font-semibold">{{ $checkoutEmptyCart }}</p>
                                <a href="{{ route('catalog') }}" class="checkout-accent-bg mt-6 inline-flex rounded-xl px-5 py-3 font-bold">
                                    {{ $checkoutEmptyCatalogCta }}
                                </a>
                            </div>
                        @else
                            <div class="mt-8 space-y-4">
                                @foreach ($cartItems as $item)
                                    @php
                                        $startDate = ! empty($item['rental_start_date']) ? \Carbon\Carbon::parse($item['rental_start_date']) : null;
                                        $endDate = ! empty($item['rental_end_date']) ? \Carbon\Carbon::parse($item['rental_end_date']) : null;
                                        $lineEstimate = (int) ($item['estimated_total'] ?? ((int) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 1)));
                                    @endphp
                                    <div class="checkout-inner flex flex-col gap-4 rounded-2xl border p-5 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="checkout-card-solid h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border p-2 shadow-sm">
                                                <img src="{{ site_media_url($item['image_path'] ?? $item['image'] ?? null) ?: config('placeholders.equipment') }}" alt="{{ $item['name'] }}" class="h-full w-full object-contain" onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';">
                                            </div>
                                            <div>
                                                <p class="checkout-title text-lg font-black">{{ $item['name'] }}</p>
                                                <p class="checkout-muted mt-1 text-xs font-semibold uppercase tracking-[0.18em]">
                                                    {{ strtr($checkoutQtyTemplate, [
                                                        ':qty' => (string) ($item['qty'] ?? 0),
                                                        ':price' => $formatIdr((int) ($item['price'] ?? 0)),
                                                    ]) }}
                                                </p>
                                                @if ($startDate && $endDate)
                                                    <span class="checkout-accent-soft mt-3 inline-flex rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-widest">
                                                        {{ $startDate->translatedFormat('d M') }} - {{ $endDate->translatedFormat('d M Y') }}
                                                    </span>
                                                @else
                                                    <p class="mt-2 text-xs font-semibold text-rose-400">{{ $checkoutInvalidDateNote }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="checkout-title text-xl font-black tracking-tight">{{ $formatIdr($lineEstimate) }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <form id="checkout-form" class="mt-10 space-y-8">
                                @csrf
                                <div class="checkout-inner rounded-2xl border p-6">
                                    <h3 class="checkout-title mb-5 flex items-center gap-3 text-xl font-black">
                                        <svg class="checkout-accent-text h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $checkoutProfileTitle }}
                                    </h3>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="checkout-muted mb-2 block text-xs font-bold uppercase tracking-[0.2em]">{{ $checkoutProfileNameLabel }}</label>
                                            <input type="text" value="{{ $profile?->full_name ?? '-' }}" class="checkout-input w-full px-4 py-3 text-sm" disabled>
                                        </div>
                                        <div>
                                            <label class="checkout-muted mb-2 block text-xs font-bold uppercase tracking-[0.2em]">{{ $checkoutProfilePhoneLabel }}</label>
                                            <input type="text" value="{{ $profile?->phone ?? '-' }}" class="checkout-input w-full px-4 py-3 text-sm" disabled>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="checkout-muted mb-2 block text-xs font-bold uppercase tracking-[0.2em]">{{ $checkoutProfileAddressLabel }}</label>
                                            <textarea rows="3" class="checkout-textarea w-full px-4 py-3 text-sm" disabled>{{ $profile?->address_text ?? $profile?->address ?? '-' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="checkout-card-solid mt-5 flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="checkout-muted text-xs font-semibold">{{ $checkoutProfileHint }}</p>
                                        <a href="{{ route('profile') }}" class="checkout-accent-text checkout-accent-link text-xs font-black uppercase tracking-[0.18em] transition">
                                            {{ $checkoutProfileUpdateLinkLabel }}
                                        </a>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <label class="checkout-card-solid flex cursor-pointer items-start gap-4 rounded-2xl border p-5">
                                        <input type="checkbox" name="confirm_profile" class="checkout-checkbox mt-1 h-5 w-5 rounded" required>
                                        <span class="checkout-muted text-sm font-semibold leading-7">{{ $checkoutConfirmProfile }}</span>
                                    </label>

                                    <button type="submit" id="checkout-submit" class="checkout-accent-bg flex w-full items-center justify-center gap-2 rounded-xl py-4 text-base font-black transition disabled:cursor-not-allowed disabled:opacity-60">
                                        {{ $checkoutSubmitButton }}
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </article>

                    <article class="checkout-card-solid flex items-center gap-5 rounded-2xl border p-5">
                        <div class="checkout-accent-bg flex h-14 w-14 items-center justify-center rounded-2xl">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.041 11.955 11.955 0 013 12c0 5.391 3.991 9.928 9 10.822 5.009-.894 9-5.43 9-10.822 0-2.08-.528-4.047-1.455-5.764z"></path></svg>
                        </div>
                        <div>
                            <h3 class="checkout-title text-xl font-black">{{ $checkoutPaymentTitle }}</h3>
                            <p class="checkout-muted mt-1 text-sm leading-7">{{ $checkoutPaymentNote }}</p>
                        </div>
                    </article>
                </div>

                <aside class="lg:col-span-4">
                    <div class="sticky top-6 space-y-6 animate-fade-up" style="animation-delay: 200ms">
                        <article class="checkout-card-solid relative overflow-hidden rounded-3xl border p-6 shadow-2xl">
                            <div class="checkout-accent-glow absolute right-0 top-0 h-40 w-40 translate-x-1/3 -translate-y-1/3 rounded-full blur-3xl"></div>
                            <div class="relative z-10">
                                <h2 class="checkout-title flex items-center gap-3 text-2xl font-black">
                                    <svg class="checkout-accent-text h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    {{ $checkoutSummaryTitle }}
                                </h2>

                                <div class="checkout-border mt-6 space-y-3 border-t pt-6">
                                    <div class="flex items-center justify-between text-sm checkout-muted">
                                        <span>{{ $checkoutSummarySubtotal }}</span>
                                        <span class="font-bold checkout-title">{{ $formatIdr($subtotalPerDay ?? 0) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm checkout-muted">
                                        <span>{{ $checkoutSummaryEstimate }}</span>
                                        <span class="font-bold checkout-title">{{ $formatIdr($estimatedSubtotal) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm checkout-muted">
                                        <span>{{ $checkoutSummaryTax }}</span>
                                        <span class="font-bold checkout-title">{{ $formatIdr($taxAmount) }}</span>
                                    </div>
                                    <div class="checkout-border mt-4 border-t pt-4">
                                        <p class="checkout-accent-text text-xs font-bold uppercase tracking-[0.2em]">{{ $checkoutSummaryTotal }}</p>
                                        <p class="checkout-title mt-2 text-4xl font-black tracking-tight" id="summary-total-side">{{ $formatIdr($estimatedTotal) }}</p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    @foreach ($cartItems as $item)
                                        <div class="checkout-inner flex items-center gap-3 rounded-2xl border p-3">
                                            <div class="checkout-card-solid h-10 w-10 flex-shrink-0 rounded-md p-1.5">
                                                <img src="{{ site_media_url($item['image_path'] ?? $item['image'] ?? null) ?: config('placeholders.equipment') }}" alt="" class="h-full w-full object-contain" onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';">
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-[11px] font-black uppercase tracking-wider">{{ $item['name'] }}</p>
                                                @php
                                                    $sidebarQty = (int) ($item['qty'] ?? 0);
                                                    $sidebarUnitLabel = $sidebarQty === 1 ? $checkoutUnitSingular : $checkoutUnitPlural;
                                                @endphp
                                                <p class="checkout-muted text-[10px] font-semibold">{{ $sidebarQty }} {{ $sidebarUnitLabel }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </article>

                        <div class="checkout-card-solid rounded-2xl border p-5 text-center">
                            <p class="checkout-muted text-xs font-bold uppercase tracking-[0.2em]">{{ $checkoutSecurityVerified }}</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $snapSrc = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $snapSrc }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        (function () {
            const form = document.getElementById('checkout-form');
            if (!form) return;

            const alertBox = document.getElementById('checkout-alert');
            const submitButton = document.getElementById('checkout-submit');

            const showAlert = (message, type = 'info') => {
                if (!alertBox) return;
                if (!message) {
                    alertBox.classList.add('hidden');
                    return;
                }
                const baseClass = 'rounded-xl border px-4 py-3 text-sm font-semibold';
                const styles = {
                    info: 'checkout-alert-info',
                    success: 'checkout-alert-success',
                    error: 'checkout-alert-error',
                };
                alertBox.className = `${baseClass} ${styles[type] || styles.info}`;
                alertBox.textContent = message;
                alertBox.classList.remove('hidden');
            };

            const refreshPaymentStatus = async (refreshUrl) => {
                if (!refreshUrl) {
                    return null;
                }

                const response = await window.fetchWithCsrf(refreshUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || @json($checkoutMsgSyncFailed));
                }

                return data;
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                showAlert('', 'info');

                submitButton.disabled = true;
                submitButton.textContent = @json($checkoutSubmitProcessing);

                try {
                    const response = await fetch("{{ route('checkout.store') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const message = data.message || @json($checkoutMsgCheckoutFailed);
                        showAlert(message, 'error');
                        return;
                    }

                    const shouldFallbackToOrderDetail = Boolean(data.fallback_to_order_detail) || !data.snap_token;
                    if (shouldFallbackToOrderDetail) {
                        showAlert(data.message || @json($checkoutMsgCreated), 'info');
                        window.location.href = data.redirect_url_to_order_detail;
                        return;
                    }

                    if (!window.snap) {
                        showAlert(@json($checkoutMsgSnapNotReady), 'info');
                        window.location.href = data.redirect_url_to_order_detail;
                        return;
                    }

                    window.snap.pay(data.snap_token, {
                        onSuccess: async function () {
                            showAlert(@json($checkoutMsgPaySuccess), 'success');

                            try {
                                const status = await refreshPaymentStatus(data.refresh_status_url);
                                if (status?.is_paid && status?.invoice_url) {
                                    window.location.href = status.invoice_url;
                                    return;
                                }
                            } catch (error) {
                                // Lanjutkan fallback ke detail pesanan jika sinkronisasi gagal.
                            }

                            window.location.href = data.redirect_url_to_order_detail;
                        },
                        onPending: async function () {
                            showAlert(@json($checkoutMsgPayPending), 'info');

                            try {
                                const status = await refreshPaymentStatus(data.refresh_status_url);
                                if (status?.is_paid && status?.invoice_url) {
                                    window.location.href = status.invoice_url;
                                    return;
                                }
                            } catch (error) {
                                // Fallback tetap ke detail pesanan.
                            }

                            window.location.href = data.redirect_url_to_order_detail;
                        },
                        onError: function () {
                            showAlert(@json($checkoutMsgPayFailed), 'error');
                        },
                        onClose: function () {
                            showAlert(@json($checkoutMsgPopupClosed), 'info');
                        },
                    });
                } catch (error) {
                    showAlert(@json($checkoutMsgGenericError), 'error');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = @json($checkoutSubmitButton);
                }
            });
        })();
    </script>
@endpush
