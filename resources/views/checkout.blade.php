@extends('layouts.app')

@section('title', setting('copy.checkout.title', __('ui.checkout.title')))

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
    @endphp

    <div class="manake-page bg-[#0A0A0B] text-[#E8E8EC]">
        <div class="manake-page-frame space-y-8">
            <header class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-6 shadow-2xl animate-fade-up sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#D4A843]">{{ __('Checkout') }}</p>
                        <h1 class="mt-3 font-serif text-4xl font-black text-[#E8E8EC] sm:text-5xl">
                            {{ $checkoutTitle }}
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-[#A0A0A8] sm:text-base">
                            {{ $checkoutSubtitle }}
                        </p>
                    </div>
                    <a href="{{ route('cart') }}" class="inline-flex w-full items-center justify-center rounded-md border border-[#1A1A1E] bg-[#0A0A0B] px-5 py-3 font-bold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843] lg:w-auto">
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

                    <article class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-6 shadow-2xl animate-fade-up sm:p-8">
                        <h2 class="flex items-center gap-3 text-2xl font-black text-[#E8E8EC]">
                            <span class="h-8 w-1.5 rounded-full bg-[#D4A843]"></span>
                            {{ $checkoutDetailTitle }}
                        </h2>

                        @if ($isCartEmpty)
                            <div class="mt-8 rounded-md border border-dashed border-[#1A1A1E] px-6 py-12 text-center">
                                <p class="text-lg font-semibold text-[#A0A0A8]">{{ $checkoutEmptyCart }}</p>
                                <a href="{{ route('catalog') }}" class="mt-6 inline-flex rounded-md bg-[#D4A843] px-5 py-3 font-bold text-[#0A0A0B]">
                                    {{ __('app.actions.back_to_catalog') }}
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
                                    <div class="flex flex-col gap-4 rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-5 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-[#1A1A1E] bg-[#111113] p-2 shadow-sm">
                                                <img src="{{ site_media_url($item['image_path'] ?? $item['image'] ?? null) ?: config('placeholders.equipment') }}" alt="{{ $item['name'] }}" class="h-full w-full object-contain" onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';">
                                            </div>
                                            <div>
                                                <p class="text-lg font-black text-[#E8E8EC]">{{ $item['name'] }}</p>
                                                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#A0A0A8]">
                                                    {{ strtr($checkoutQtyTemplate, [
                                                        ':qty' => (string) ($item['qty'] ?? 0),
                                                        ':price' => $formatIdr((int) ($item['price'] ?? 0)),
                                                    ]) }}
                                                </p>
                                                @if ($startDate && $endDate)
                                                    <span class="mt-3 inline-flex rounded-full border border-[#1A1A1E] bg-[#111113] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-[#D4A843]">
                                                        {{ $startDate->translatedFormat('d M') }} - {{ $endDate->translatedFormat('d M Y') }}
                                                    </span>
                                                @else
                                                    <p class="mt-2 text-xs font-semibold text-rose-400">{{ $checkoutInvalidDateNote }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xl font-black tracking-tight text-[#E8E8EC]">{{ $formatIdr($lineEstimate) }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <form id="checkout-form" class="mt-10 space-y-8">
                                @csrf
                                <div class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-6">
                                    <h3 class="mb-5 flex items-center gap-3 text-xl font-black text-[#E8E8EC]">
                                        <svg class="h-6 w-6 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $checkoutProfileTitle }}
                                    </h3>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-[#A0A0A8]">{{ $checkoutProfileNameLabel }}</label>
                                            <input type="text" value="{{ $profile?->full_name ?? '-' }}" class="manake-input" disabled>
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-[#A0A0A8]">{{ $checkoutProfilePhoneLabel }}</label>
                                            <input type="text" value="{{ $profile?->phone ?? '-' }}" class="manake-input" disabled>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-[#A0A0A8]">{{ $checkoutProfileAddressLabel }}</label>
                                            <textarea rows="3" class="manake-textarea" disabled>{{ $profile?->address_text ?? $profile?->address ?? '-' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="mt-5 flex flex-col gap-3 rounded-md border border-[#1A1A1E] bg-[#111113] p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-xs font-semibold text-[#A0A0A8]">{{ $checkoutProfileHint }}</p>
                                        <a href="{{ route('profile') }}" class="text-xs font-black uppercase tracking-[0.18em] text-[#D4A843] hover:text-[#e0ba5d]">
                                            {{ $checkoutProfileUpdateLinkLabel }}
                                        </a>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <label class="flex cursor-pointer items-start gap-4 rounded-md border border-[#1A1A1E] bg-[#111113] p-5">
                                        <input type="checkbox" name="confirm_profile" class="mt-1 h-5 w-5 rounded border-[#1A1A1E] text-[#D4A843] focus:ring-[#D4A843]" required>
                                        <span class="text-sm font-semibold leading-7 text-[#A0A0A8]">{{ $checkoutConfirmProfile }}</span>
                                    </label>

                                    <button type="submit" id="checkout-submit" class="w-full rounded-md bg-[#D4A843] py-4 text-base font-black text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                                        {{ $checkoutSubmitButton }}
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </article>

                    <article class="flex items-center gap-5 rounded-md border border-[#1A1A1E] bg-[#111113] p-5">
                        <div class="flex h-14 w-14 items-center justify-center rounded-md bg-[#D4A843] text-[#0A0A0B]">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.041 11.955 11.955 0 013 12c0 5.391 3.991 9.928 9 10.822 5.009-.894 9-5.43 9-10.822 0-2.08-.528-4.047-1.455-5.764z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-[#E8E8EC]">{{ $checkoutPaymentTitle }}</h3>
                            <p class="mt-1 text-sm leading-7 text-[#A0A0A8]">{{ $checkoutPaymentNote }}</p>
                        </div>
                    </article>
                </div>

                <aside class="lg:col-span-4">
                    <div class="sticky top-6 space-y-6 animate-fade-up" style="animation-delay: 200ms">
                        <article class="relative overflow-hidden rounded-lg border border-[#1A1A1E] bg-[#111113] p-6 text-[#E8E8EC] shadow-2xl">
                            <div class="absolute right-0 top-0 h-40 w-40 translate-x-1/3 -translate-y-1/3 rounded-full bg-[#D4A843]/20 blur-3xl"></div>
                            <div class="relative z-10">
                                <h2 class="manake-heading flex items-center gap-3 text-2xl font-black">
                                    <svg class="h-6 w-6 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    {{ $checkoutSummaryTitle }}
                                </h2>

                                <div class="mt-6 space-y-3 border-t border-white/10 pt-6">
                                    <div class="flex items-center justify-between text-sm text-[#A0A0A8]">
                                        <span>{{ $checkoutSummarySubtotal }}</span>
                                        <span class="font-bold text-white">{{ $formatIdr($subtotalPerDay ?? 0) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm text-[#A0A0A8]">
                                        <span>{{ $checkoutSummaryEstimate }}</span>
                                        <span class="font-bold text-white">{{ $formatIdr($estimatedSubtotal) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm text-[#A0A0A8]">
                                        <span>{{ $checkoutSummaryTax }}</span>
                                        <span class="font-bold text-white">{{ $formatIdr($taxAmount) }}</span>
                                    </div>
                                    <div class="mt-4 border-t border-white/10 pt-4">
                                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#D4A843]">{{ $checkoutSummaryTotal }}</p>
                                        <p class="mt-2 text-4xl font-black tracking-tight text-[#E8E8EC]" id="summary-total-side">{{ $formatIdr($estimatedTotal) }}</p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    @foreach ($cartItems as $item)
                                        <div class="flex items-center gap-3 rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-3">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-md bg-[#111113] p-1.5">
                                                <img src="{{ site_media_url($item['image_path'] ?? $item['image'] ?? null) ?: config('placeholders.equipment') }}" alt="" class="h-full w-full object-contain" onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';">
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-[11px] font-black uppercase tracking-wider">{{ $item['name'] }}</p>
                                                <p class="text-[10px] font-semibold text-[#A0A0A8]">{{ (int) ($item['qty'] ?? 0) }} Unit</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </article>

                        <div class="manake-card text-center">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#A0A0A8]">{{ __('Security Verified by Midtrans Snap') }}</p>
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
                const baseClass = 'rounded-xl border px-4 py-3 text-sm';
                const styles = {
                    info: 'border-slate-200 bg-slate-50 text-slate-700',
                    success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    error: 'border-rose-200 bg-rose-50 text-rose-700',
                };
                alertBox.className = `${baseClass} ${styles[type] || styles.info}`;
                alertBox.textContent = message;
                alertBox.classList.remove('hidden');
            };

            const refreshPaymentStatus = async (refreshUrl) => {
                if (!refreshUrl) {
                    return null;
                }

                const response = await fetchWithCsrf(refreshUrl, {
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
