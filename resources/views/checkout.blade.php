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

    <div class="bg-slate-50 min-h-screen">
        <div class="mx-auto max-w-7xl px-4 py-12 pb-24 sm:px-6 lg:px-8">
            {{-- Header Section --}}
            <header class="mb-12 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between animate-fade-up">
                <div class="glass-lg noise-overlay spotlight-shell rounded-[2.5rem] p-8 sm:p-10 border border-white/20 shadow-2xl flex-1">
                    <h1 class="text-4xl font-black tracking-tight text-slate-950 sm:text-5xl leading-tight">
                        {{ $checkoutTitle }}
                    </h1>
                    <p class="mt-4 text-lg text-slate-600 font-medium max-w-2xl leading-relaxed">
                        {{ $checkoutSubtitle }}
                    </p>
                </div>
                <div class="flex items-center gap-4 px-4 sm:px-0">
                    <a href="{{ route('cart') }}" class="group flex items-center gap-3 text-sm font-black uppercase tracking-widest text-slate-500 transition-colors hover:text-blue-600">
                        <svg class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ $checkoutBackToCart }}
                    </a>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:items-start lg:gap-16">
                {{-- Left Content: Order Details & Form --}}
                <div class="lg:col-span-8 space-y-8">
                    <div id="checkout-alert" class="hidden rounded-[2rem] border-2 border-dashed px-8 py-6 text-sm font-bold shadow-sm animate-fade-in-down"></div>

                    {{-- Order Items Card --}}
                    <article class="premium-card noise-overlay spotlight-shell relative overflow-hidden rounded-[2.5rem] border border-white/20 bg-white/40 p-8 shadow-2xl sm:p-10 animate-fade-up">
                        <h2 class="text-2xl font-black text-slate-950 flex items-center gap-3">
                            <span class="h-8 w-1.5 rounded-full bg-blue-600"></span>
                            {{ $checkoutDetailTitle }}
                        </h2>

                        @if ($isCartEmpty)
                            <div class="mt-8 text-center py-12">
                                <p class="text-lg text-slate-500 font-semibold">{{ $checkoutEmptyCart }}</p>
                                <a href="{{ route('catalog') }}" class="btn-primary mt-8 inline-flex items-center rounded-2xl px-10 py-4 font-black tracking-widest uppercase text-xs">
                                    {{ __('app.actions.back_to_catalog') }}
                                </a>
                            </div>
                        @else
                            <div class="mt-10 space-y-6">
                                @foreach ($cartItems as $item)
                                    @php
                                        $startDate = ! empty($item['rental_start_date']) ? \Carbon\Carbon::parse($item['rental_start_date']) : null;
                                        $endDate = ! empty($item['rental_end_date']) ? \Carbon\Carbon::parse($item['rental_end_date']) : null;
                                        $lineEstimate = (int) ($item['estimated_total'] ?? ((int) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 1)));
                                    @endphp
                                    <div class="group relative flex items-center justify-between gap-6 rounded-3xl border border-slate-200/60 bg-white/60 p-6 transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-600/5">
                                        <div class="flex items-center gap-6">
                                            <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm p-2">
                                                <img src="{{ $item['image'] ?? config('placeholders.equipment') }}" alt="{{ $item['name'] }}" class="h-full w-full object-contain">
                                            </div>
                                            <div>
                                                <p class="text-lg font-black text-slate-950">{{ $item['name'] }}</p>
                                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                                    {{ strtr($checkoutQtyTemplate, [
                                                        ':qty' => (string) ($item['qty'] ?? 0),
                                                        ':price' => $formatIdr((int) ($item['price'] ?? 0)),
                                                    ]) }}
                                                </p>
                                                @if ($startDate && $endDate)
                                                    <div class="mt-3 flex items-center gap-2 text-[12px] font-bold text-blue-600 bg-blue-50 w-fit px-3 py-1 rounded-lg">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        {{ $startDate->translatedFormat('d M') }} — {{ $endDate->translatedFormat('d M Y') }}
                                                    </div>
                                                @else
                                                    <p class="mt-2 text-xs font-bold text-rose-600 italic">{{ $checkoutInvalidDateNote }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xl font-black text-slate-950 tracking-tighter">{{ $formatIdr($lineEstimate) }}</p>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Checkout Form --}}
                            <form id="checkout-form" class="mt-12 space-y-10">
                                @csrf
                                <div class="rounded-[2rem] border border-blue-100 bg-blue-50/40 p-8 shadow-inner">
                                    <h3 class="text-xl font-black text-slate-950 mb-6 flex items-center gap-3">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $checkoutProfileTitle }}
                                    </h3>
                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ $checkoutProfileNameLabel }}</label>
                                            <div class="relative">
                                                <input type="text" value="{{ $profile?->full_name ?? '-' }}" class="w-full rounded-2xl border border-slate-200 bg-white/80 px-5 py-4 text-sm font-bold text-slate-700 shadow-sm transition focus:border-blue-500 focus:ring-0 disabled:opacity-60" disabled>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ $checkoutProfilePhoneLabel }}</label>
                                            <div class="relative">
                                                <input type="text" value="{{ $profile?->phone ?? '-' }}" class="w-full rounded-2xl border border-slate-200 bg-white/80 px-5 py-4 text-sm font-bold text-slate-700 shadow-sm transition focus:border-blue-500 focus:ring-0 disabled:opacity-60" disabled>
                                            </div>
                                        </div>
                                        <div class="space-y-2 md:col-span-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ $checkoutProfileAddressLabel }}</label>
                                            <div class="relative">
                                                <textarea rows="3" class="w-full rounded-2xl border border-slate-200 bg-white/80 px-5 py-4 text-sm font-bold text-slate-700 shadow-sm transition focus:border-blue-500 focus:ring-0 disabled:opacity-60" disabled>{{ $profile?->address_text ?? $profile?->address ?? '-' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6 flex items-center justify-between rounded-2xl bg-white/50 p-4 border border-blue-100">
                                        <p class="text-xs font-bold text-slate-500">
                                            {{ $checkoutProfileHint }}
                                        </p>
                                        <a href="{{ route('profile.complete') }}" class="text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-700 underline decoration-2 underline-offset-4">
                                            {{ $checkoutProfileUpdateLinkLabel }}
                                        </a>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <label class="group relative flex cursor-pointer items-start gap-4 rounded-3xl border border-slate-200 bg-white/60 p-6 transition-all hover:bg-white hover:shadow-lg">
                                        <div class="flex h-6 w-6 items-center justify-center">
                                            <input type="checkbox" name="confirm_profile" class="h-6 w-6 rounded-lg border-2 border-slate-200 text-blue-600 transition focus:ring-blue-500 focus:ring-offset-0" required>
                                        </div>
                                        <span class="text-sm font-bold text-slate-600 leading-relaxed">{{ $checkoutConfirmProfile }}</span>
                                    </label>

                                    <button type="submit" id="checkout-submit" class="btn-primary group/submit relative overflow-hidden flex w-full items-center justify-center rounded-[1.75rem] py-6 text-xl font-black shadow-2xl shadow-blue-600/40 transition-all hover:scale-[1.01] active:scale-[0.98]">
                                        <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover/submit:opacity-100 transition-opacity"></div>
                                        {{ $checkoutSubmitButton }}
                                        <svg class="ml-4 h-6 w-6 transition-transform group-hover/submit:translate-x-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </article>

                    {{-- Info Card --}}
                    <article class="rounded-[2.5rem] border border-blue-100 bg-blue-50/30 p-8 flex items-center gap-6">
                        <div class="h-16 w-16 flex-shrink-0 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-xl shadow-blue-600/20">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.041 11.955 11.955 0 013 12c0 5.391 3.991 9.928 9 10.822 5.009-.894 9-5.43 9-10.822 0-2.08-.528-4.047-1.455-5.764z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-950">{{ $checkoutPaymentTitle }}</h3>
                            <p class="mt-1 text-sm font-bold text-slate-500 leading-relaxed">{{ $checkoutPaymentNote }}</p>
                        </div>
                    </article>
                </div>

                {{-- Right Content: Sidebar Summary --}}
                <aside class="lg:col-span-4">
                    <div class="sticky top-12 space-y-8 animate-fade-up" style="animation-delay: 200ms">
                        <article class="premium-card noise-overlay spotlight-shell relative overflow-hidden rounded-[3rem] border border-slate-800 bg-slate-950 p-10 shadow-2xl text-white">
                            <div class="absolute top-0 right-0 p-10 opacity-5 blur-3xl">
                                <div class="h-64 w-64 rounded-full bg-blue-500"></div>
                            </div>

                            <div class="relative z-10">
                                <h2 class="text-2xl font-black tracking-tight flex items-center gap-3">
                                    <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    {{ $checkoutSummaryTitle }}
                                </h2>

                                <div class="mt-8 space-y-4 border-t border-slate-800/50 pt-8">
                                    <div class="flex justify-between text-sm font-bold text-slate-400">
                                        <span>{{ $checkoutSummarySubtotal }}</span>
                                        <span class="text-white">{{ $formatIdr($subtotalPerDay ?? 0) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm font-bold text-slate-400">
                                        <span>{{ $checkoutSummaryEstimate }}</span>
                                        <span class="text-white">{{ $formatIdr($estimatedSubtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm font-bold text-slate-400 pb-6">
                                        <span>{{ $checkoutSummaryTax }}</span>
                                        <span class="text-white">{{ $formatIdr($taxAmount) }}</span>
                                    </div>

                                    <div class="flex flex-col border-t border-slate-800/50 pt-8">
                                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-500 mb-2">{{ $checkoutSummaryTotal }}</span>
                                        <p class="text-4xl font-black tracking-tighter" id="summary-total-side">{{ $formatIdr($estimatedTotal) }}</p>
                                    </div>
                                </div>

                                {{-- Mini Item List in Sidebar --}}
                                <div class="mt-10 space-y-3">
                                    @foreach ($cartItems as $item)
                                        <div class="flex items-center gap-3 rounded-2xl bg-white/5 p-3 border border-white/5">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-white/10 p-1.5">
                                                <img src="{{ $item['image'] ?? config('placeholders.equipment') }}" alt="" class="h-full w-full object-contain">
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-[11px] font-black uppercase tracking-wider">{{ $item['name'] }}</p>
                                                <p class="text-[10px] font-bold text-slate-500">{{ (int)($item['qty'] ?? 0) }} Unit</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </article>

                        <div class="rounded-[2rem] bg-slate-200/50 p-6 border border-slate-200">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Security Verified by Midtrans Snap</p>
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
