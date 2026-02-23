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
        $checkoutNoItems = setting('copy.checkout.no_items', __('ui.checkout.no_items'));
        $checkoutQtyTemplate = __('ui.checkout.qty_template');
        $checkoutScheduleLabel = __('ui.checkout.schedule_label');
        $checkoutInvalidDateNote = __('ui.checkout.invalid_date_note');
        $checkoutProfileNameLabel = __('ui.checkout.profile_name_label');
        $checkoutProfilePhoneLabel = __('ui.checkout.profile_phone_label');
        $checkoutProfileAddressLabel = __('ui.checkout.profile_address_label');
        $checkoutProfileUpdateLinkLabel = __('ui.checkout.profile_update_link_label');
        $checkoutSidebarQtyTemplate = __('ui.checkout.sidebar_qty_template');
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

    <section class="bg-slate-50">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-slate-900">{{ $checkoutTitle }}</h1>
                    <p class="text-sm text-slate-600">{{ $checkoutSubtitle }}</p>
                </div>
                <a href="{{ route('cart') }}" class="text-sm text-slate-600 hover:text-blue-600">← {{ $checkoutBackToCart }}</a>
            </div>
        </div>
    </section>

    <section class="bg-slate-100">
        <div class="mx-auto grid max-w-6xl grid-cols-1 gap-6 px-4 pb-14 sm:px-6 lg:grid-cols-[1fr,320px]">
            <div class="space-y-4">
                <div id="checkout-alert" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $checkoutDetailTitle }}</h2>

                    @if ($isCartEmpty)
                        <p class="mt-3 text-sm text-slate-500">{{ $checkoutEmptyCart }}</p>
                        <a href="{{ route('catalog') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            {{ __('app.actions.back_to_catalog') }}
                        </a>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($cartItems as $item)
                                @php
                                    $startDate = ! empty($item['rental_start_date']) ? \Carbon\Carbon::parse($item['rental_start_date']) : null;
                                    $endDate = ! empty($item['rental_end_date']) ? \Carbon\Carbon::parse($item['rental_end_date']) : null;
                                    $lineEstimate = (int) ($item['estimated_total'] ?? ((int) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 1)));
                                @endphp
                                <div class="rounded-xl border border-slate-200 p-3 text-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $item['name'] }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ strtr($checkoutQtyTemplate, [
                                                    ':qty' => (string) ($item['qty'] ?? 0),
                                                    ':price' => $formatIdr((int) ($item['price'] ?? 0)),
                                                ]) }}
                                            </p>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $formatIdr($lineEstimate) }}</p>
                                    </div>
                                    @if ($startDate && $endDate)
                                        <p class="mt-2 text-xs text-blue-700">
                                            {{ $checkoutScheduleLabel }}: {{ $startDate->translatedFormat('d M Y') }} - {{ $endDate->translatedFormat('d M Y') }}
                                            ({{ max((int) ($item['rental_days'] ?? 1), 1) }} {{ __('app.product.day_label') }})
                                        </p>
                                    @else
                                        <p class="mt-2 text-xs text-rose-600">{{ $checkoutInvalidDateNote }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <form id="checkout-form" class="mt-4 space-y-4">
                            @csrf
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                <h3 class="text-sm font-semibold text-slate-900">{{ $checkoutProfileTitle }}</h3>
                                <div class="mt-3 grid grid-cols-1 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-500">{{ $checkoutProfileNameLabel }}</label>
                                        <input
                                            type="text"
                                            value="{{ $profile?->full_name ?? '-' }}"
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                                            disabled
                                        >
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-500">{{ $checkoutProfilePhoneLabel }}</label>
                                        <input
                                            type="text"
                                            value="{{ $profile?->phone ?? '-' }}"
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                                            disabled
                                        >
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-500">{{ $checkoutProfileAddressLabel }}</label>
                                        <textarea
                                            rows="2"
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                                            disabled
                                        >{{ $profile?->address_text ?? $profile?->address ?? '-' }}</textarea>
                                    </div>
                                </div>
                                <p class="mt-3 text-xs text-slate-400">
                                    {{ $checkoutProfileHint }}
                                    <a href="{{ route('profile.complete') }}" class="text-blue-600 hover:text-blue-700">{{ $checkoutProfileUpdateLinkLabel }}</a>
                                </p>
                            </div>

                            <label class="flex items-start gap-3 text-sm text-slate-600">
                                <input type="checkbox" name="confirm_profile" class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500" required>
                                <span>{{ $checkoutConfirmProfile }}</span>
                            </label>

                            <button
                                type="submit"
                                id="checkout-submit"
                                class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition"
                            >
                                {{ $checkoutSubmitButton }}
                            </button>
                        </form>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $checkoutPaymentTitle }}</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ $checkoutPaymentNote }}
                    </p>
                </div>
            </div>

            <div class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ $checkoutSummaryTitle }}</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>{{ $checkoutSummarySubtotal }}</span>
                        <span id="summary-subtotal">{{ $formatIdr($subtotalPerDay ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>{{ $checkoutSummaryEstimate }}</span>
                        <span>{{ $formatIdr($estimatedSubtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>{{ $checkoutSummaryTax }}</span>
                        <span>{{ $formatIdr($taxAmount) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-2 font-semibold text-slate-900">
                        <span>{{ $checkoutSummaryTotal }}</span>
                        <span id="summary-total-side">{{ $formatIdr($estimatedTotal) }}</span>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($cartItems as $item)
                        @php
                            $startDate = ! empty($item['rental_start_date']) ? \Carbon\Carbon::parse($item['rental_start_date']) : null;
                            $endDate = ! empty($item['rental_end_date']) ? \Carbon\Carbon::parse($item['rental_end_date']) : null;
                        @endphp
                        <div class="rounded-xl border border-slate-200 p-3 text-sm text-slate-600">
                            <p class="font-semibold text-slate-900">{{ $item['name'] }}</p>
                            <div class="mt-1 flex justify-between text-xs">
                                <span>{{ strtr($checkoutSidebarQtyTemplate, [':qty' => (string) ($item['qty'] ?? 0)]) }}</span>
                                <span>{{ $formatIdr((int) ($item['estimated_total'] ?? 0)) }}</span>
                            </div>
                            @if ($startDate && $endDate)
                                <p class="mt-1 text-xs text-blue-700">
                                    {{ $startDate->translatedFormat('d M Y') }} - {{ $endDate->translatedFormat('d M Y') }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ $checkoutNoItems }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
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
