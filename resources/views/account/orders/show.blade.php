@extends('layouts.app')

@section('title', __('ui.orders.detail_title'))

@php
    $formatIdr = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
    $paymentStatus = $order->status_pembayaran ?? 'pending';
    $orderStatus = $order->status_pesanan ?? 'menunggu_pembayaran';
    $isPaid = $paymentStatus === 'paid';
    $isPrimaryPayable = in_array($paymentStatus, ['pending', 'failed'], true);
    $additionalFee = $order->resolvePenaltyAmount();
    $damagePayment = $order->damagePayment;
    $damagePaymentStatus = (string) ($damagePayment?->status ?? '');
    $damageFeeStatuses = ['barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda'];
    $hasDamageFeeOutstanding = in_array($orderStatus, $damageFeeStatuses, true) && $additionalFee > 0 && $damagePaymentStatus !== 'paid';
    $canAccessInvoice = $isPaid && ! $hasDamageFeeOutstanding;
    $orderRouteKey = (string) ($order->order_number ?: $order->midtrans_order_id ?: '');
    $signedInvoiceUrl = ($canAccessInvoice && $orderRouteKey !== '')
        ? \Illuminate\Support\Facades\URL::temporarySignedRoute('account.orders.receipt', now()->addMinutes(30), ['order' => $orderRouteKey])
        : null;
    $signedInvoicePdfUrl = ($canAccessInvoice && $orderRouteKey !== '')
        ? \Illuminate\Support\Facades\URL::temporarySignedRoute('account.orders.receipt.pdf', now()->addMinutes(30), ['order' => $orderRouteKey])
        : null;
    $signedInvoicePdfPreviewUrl = ($canAccessInvoice && $orderRouteKey !== '')
        ? \Illuminate\Support\Facades\URL::temporarySignedRoute('account.orders.receipt.pdf', now()->addMinutes(30), ['order' => $orderRouteKey, 'inline' => 1])
        : null;
    $isDamageFeePaid = $additionalFee > 0 && $damagePaymentStatus === 'paid';
    $loadSnapPaymentScript = $isPrimaryPayable || $hasDamageFeeOutstanding;
    $baseTotal = (int) ($order->total_amount ?? 0);
    $taxAmount = (int) round($baseTotal * 0.11);
    $rentalGrandTotal = $baseTotal + $taxAmount;
    $grandTotal = $rentalGrandTotal + ($isDamageFeePaid ? $additionalFee : 0);

    $statusMeta = match ($paymentStatus) {
        'paid' => ['label' => __('ui.invoice.status.paid'), 'badge' => 'bg-blue-100 text-blue-700'],
        'failed' => ['label' => __('ui.invoice.status.failed'), 'badge' => 'bg-rose-100 text-rose-700'],
        'expired' => ['label' => __('ui.invoice.status.expired'), 'badge' => 'bg-slate-200 text-slate-700'],
        'refunded' => ['label' => __('ui.invoice.status.refunded'), 'badge' => 'bg-indigo-100 text-indigo-700'],
        default => ['label' => __('ui.invoice.status.pending'), 'badge' => 'bg-amber-100 text-amber-700'],
    };

    $statusLabel = fn ($status) => match ($status) {
        'menunggu_pembayaran' => __('ui.orders.statuses.waiting_payment'),
        'diproses' => __('ui.orders.statuses.processed'),
        'lunas' => __('ui.orders.statuses.ready_pickup'),
        'barang_diambil' => __('ui.orders.statuses.picked_up'),
        'barang_kembali' => __('ui.orders.statuses.returned'),
        'barang_rusak' => __('ui.orders.statuses.damaged'),
        'barang_hilang' => __('ui.orders.statuses.lost'),
        'overdue_denda' => __('ui.orders.statuses.overdue'),
        'selesai' => __('ui.orders.statuses.completed'),
        'expired' => __('ui.orders.statuses.expired'),
        'dibatalkan' => __('ui.orders.statuses.canceled'),
        'refund' => __('ui.orders.statuses.refunded'),
        default => strtoupper((string) $status),
    };

    $timeline = [
        [
            'title' => __('ui.orders.timeline.waiting_payment'),
            'done' => $paymentStatus !== 'pending',
            'active' => $paymentStatus === 'pending',
            'time' => null,
        ],
        [
            'title' => __('ui.orders.timeline.payment_confirmed'),
            'done' => $paymentStatus === 'paid',
            'active' => $paymentStatus === 'paid' && $orderStatus === 'lunas',
            'time' => $order->paid_at,
        ],
        [
            'title' => __('ui.orders.timeline.order_processed'),
            'done' => in_array($orderStatus, ['diproses', 'lunas', 'barang_diambil', 'barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda', 'selesai'], true),
            'active' => in_array($orderStatus, ['diproses', 'lunas'], true),
            'time' => null,
        ],
        [
            'title' => __('ui.orders.timeline.picked_up'),
            'done' => in_array($orderStatus, ['barang_diambil', 'barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda', 'selesai'], true),
            'active' => $orderStatus === 'barang_diambil',
            'time' => $order->picked_up_at,
        ],
        [
            'title' => __('ui.orders.timeline.returned'),
            'done' => in_array($orderStatus, ['barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda', 'selesai'], true),
            'active' => in_array($orderStatus, ['barang_kembali', 'barang_rusak', 'barang_hilang'], true),
            'time' => $order->returned_at,
        ],
    ];
    $canReschedule = in_array($orderStatus, ['menunggu_pembayaran', 'diproses', 'lunas'], true);
    $hasPickedUp = in_array($orderStatus, ['barang_diambil', 'barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda', 'selesai'], true);
    $rescheduleStartDate = $order->rental_start_date;
    $rescheduleEndDate = $order->rental_end_date;
    $rescheduleMaxDate = now()->addMonthsNoOverflow(3)->toDateString();
    $rescheduleDurationDays = 1;
    if ($rescheduleStartDate && $rescheduleEndDate && $rescheduleEndDate->gte($rescheduleStartDate)) {
        $rescheduleDurationDays = $rescheduleStartDate->diffInDays($rescheduleEndDate) + 1;
    }
    $rescheduleConflictPopupMessage = (session('error') && (old('rental_start_date') || old('rental_end_date')))
        ? session('error')
        : null;
    $orderDetailTitle = setting('copy.order_detail.title', __('ui.orders.detail_title'));
    $orderDetailSubtitle = setting('copy.order_detail.subtitle', __('ui.orders.page_subtitle'));
    $orderDetailBackLabel = setting('copy.order_detail.back_label', __('ui.orders.back_to_history'));
    $orderNumberLabel = setting('copy.order_detail.order_number_label', __('ui.orders.order_number'));
    $orderProgressTitle = setting('copy.order_detail.progress_title', __('ui.orders.progress_title'));
    $orderItemsTitle = setting('copy.order_detail.items_title', __('ui.orders.items_title'));
    $orderPaymentTitle = setting('copy.order_detail.payment_title', __('ui.orders.payment_title'));
    $orderScheduleUnavailableTitle = __('ui.orders.schedule_unavailable_title');
    $orderScheduleUnavailableSubtitle = __('ui.orders.schedule_unavailable_subtitle');
    $orderPopupCloseAria = __('ui.actions.close');
    $orderPopupCloseButton = __('ui.actions.close');
    $orderAdditionalFeeRequiredTitle = __('ui.orders.additional_fee_required_title');
    $orderAdditionalFeeRequiredDesc = __('ui.orders.additional_fee_required_desc');
    $orderAdditionalFeeRequiredTaxNote = __('ui.orders.additional_fee_required_tax_note');
    $orderCopyReceipt = __('ui.orders.copy_receipt_button');
    $orderCopySuccess = __('ui.orders.copy_receipt_success');
    $orderCopyFailed = __('ui.orders.copy_receipt_failed');
    $orderOrderIdLabel = __('ui.orders.order_id');
    $orderRentalPeriodLabel = __('ui.orders.rental_period');
    $orderRentalStatusLabel = __('ui.orders.rental_status');
    $orderItemLineTemplate = __('ui.orders.item_line_template');
    $orderItemsEmpty = __('ui.orders.items_empty');
    $orderNotificationsTitle = __('ui.orders.notifications_title');
    $orderMidtransOrderIdLabel = __('ui.orders.midtrans_order_id');
    $orderStatusPesananLabel = __('ui.orders.order_status');
    $orderSubtotalLabel = __('ui.orders.subtotal_rental');
    $orderTaxLabel = __('ui.orders.tax_label');
    $orderTotalRentalLabel = __('ui.orders.total_rental');
    $orderAdditionalFeeSectionTitle = __('ui.orders.additional_fee_section_title');
    $orderAdditionalFeePaidLabel = __('ui.orders.additional_fee_paid_label');
    $orderAdditionalFeeUnpaidLabel = __('ui.orders.additional_fee_unpaid_label');
    $orderAdditionalFeeLabel = __('ui.orders.additional_fee_label');
    $orderAdditionalFeeNoTaxLabel = __('ui.orders.additional_fee_no_tax_label');
    $orderFinalTotalLabel = __('ui.orders.final_total');
    $orderAdminNoteLabel = __('ui.orders.admin_note');
    $orderPaidAtLabel = __('ui.orders.paid_at');
    $orderRescheduleTitle = __('ui.orders.reschedule_title');
    $orderRescheduleDescTemplate = __('ui.orders.reschedule_desc_template');
    $orderRescheduleStartLabel = __('ui.orders.reschedule_start_label');
    $orderRescheduleEndLabel = __('ui.orders.reschedule_end_label');
    $orderRescheduleEndNoteTemplate = __('ui.orders.reschedule_end_note_template');
    $orderRescheduleSaveButton = __('ui.orders.reschedule_save_button');
    $orderRescheduleLocked = __('ui.orders.reschedule_locked');
    $orderPayNowButton = __('ui.orders.pay_now_button');
    $orderRefreshPaymentButton = __('ui.orders.refresh_payment_button');
    $orderPaymentNote = __('ui.orders.payment_note');
    $orderPayAdditionalButton = __('ui.orders.pay_additional_button');
    $orderRefreshAdditionalButton = __('ui.orders.refresh_additional_button');
    $orderViewInvoiceButton = __('ui.orders.view_invoice_button');
    $orderDownloadPdfButton = __('ui.orders.download_pdf_button');
    $orderInvoiceLockedNote = __('ui.orders.invoice_locked_note');
    $orderPaymentSyncFailed = __('ui.orders.messages.payment_sync_failed');
    $orderPaymentConfirmedInvoice = __('ui.orders.messages.payment_confirmed_invoice');
    $orderPaymentNeedAdditional = __('ui.orders.messages.payment_need_additional');
    $orderAdditionalSyncFailed = __('ui.orders.messages.additional_sync_failed');
    $orderAdditionalPaid = __('ui.orders.messages.additional_paid');
    $orderProcessing = __('ui.orders.messages.processing');
    $orderCreatingSession = __('ui.orders.messages.creating_session');
    $orderSessionFailed = __('ui.orders.messages.session_failed');
    $orderSnapNotReady = __('ui.orders.messages.snap_not_ready');
    $orderPaymentSuccessSync = __('ui.orders.messages.payment_success_sync');
    $orderPaymentSuccessSyncFailed = __('ui.orders.messages.payment_success_sync_failed');
    $orderPaymentPending = __('ui.orders.messages.payment_pending');
    $orderPaymentFailed = __('ui.orders.messages.payment_failed');
    $orderPopupClosed = __('ui.orders.messages.popup_closed');
    $orderPaymentOpenError = __('ui.orders.messages.payment_open_error');
    $orderChecking = __('ui.orders.messages.checking');
    $orderStatusUnpaid = __('ui.orders.messages.status_unpaid');
    $orderStatusCheckFailed = __('ui.orders.messages.status_check_failed');
    $orderAdditionalUnpaid = __('ui.orders.messages.additional_unpaid');
    $orderAdditionalCheckFailed = __('ui.orders.messages.additional_check_failed');
@endphp

@section('content')
    <section class="bg-slate-50">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-blue-700 sm:text-3xl">{{ $orderDetailTitle }}</h1>
                    <p class="text-sm text-slate-500">{{ $orderDetailSubtitle }}</p>
                </div>
                <a href="{{ route('booking.history') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600">← {{ $orderDetailBackLabel }}</a>
            </div>
        </div>
    </section>

    <section class="bg-slate-100">
        <div class="mx-auto max-w-6xl px-4 pb-12 sm:px-6">
            <div id="payment-alert" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

            @if (session('error'))
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($rescheduleConflictPopupMessage)
                <div id="reschedule-conflict-popup" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 px-4">
                    <div class="w-full max-w-md rounded-2xl border border-rose-200 bg-white p-5 shadow-2xl">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-600">{{ $orderScheduleUnavailableTitle }}</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $orderScheduleUnavailableSubtitle }}</p>
                            </div>
                            <button
                                type="button"
                                data-close-reschedule-popup
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-rose-200 hover:text-rose-600"
                                aria-label="{{ $orderPopupCloseAria }}"
                            >
                                ×
                            </button>
                        </div>
                        <p class="mt-3 rounded-xl border border-rose-100 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                            {{ $rescheduleConflictPopupMessage }}
                        </p>
                        <div class="mt-4 flex justify-end">
                            <button
                                type="button"
                                data-close-reschedule-popup
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600"
                            >
                                {{ $orderPopupCloseButton }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($hasDamageFeeOutstanding)
                <div class="mt-4 rounded-2xl border-2 border-rose-300 bg-rose-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">{{ $orderAdditionalFeeRequiredTitle }}</p>
                    <p class="mt-1 text-lg font-semibold text-rose-800">{{ strtr($orderAdditionalFeeRequiredDesc, [':fee' => $formatIdr($additionalFee)]) }}</p>
                    <p class="mt-1 text-sm text-rose-700">{{ $orderAdditionalFeeRequiredTaxNote }}</p>
                    @if ($order->additional_fee_note)
                        <p class="mt-2 rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs text-rose-700">{{ $order->additional_fee_note }}</p>
                    @endif
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[1fr,340px]">
                <div class="space-y-6">
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-blue-500">{{ $orderNumberLabel }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <p id="order-number-text" class="text-lg font-semibold text-blue-700">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                    <button
                                        type="button"
                                        id="copy-order-number"
                                        class="inline-flex items-center rounded-lg border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600"
                                    >
                                        {{ $orderCopyReceipt }}
                                    </button>
                                </div>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['badge'] }}">
                                {{ $statusMeta['label'] }}
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-500">{{ $orderOrderIdLabel }}</p>
                                <p class="mt-1 font-semibold text-slate-800">#{{ $order->id }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-500">{{ $orderRentalPeriodLabel }}</p>
                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ optional($order->rental_start_date)->format('d M Y') }} - {{ optional($order->rental_end_date)->format('d M Y') }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-500">{{ $orderRentalStatusLabel }}</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $statusLabel($orderStatus) }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-blue-700">{{ $orderProgressTitle }}</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($timeline as $step)
                                @php
                                    $stepClass = $step['done']
                                        ? 'border-blue-200 bg-blue-50'
                                        : ($step['active'] ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-slate-50');
                                    $dotClass = $step['done']
                                        ? 'bg-blue-600'
                                        : ($step['active'] ? 'bg-amber-500' : 'bg-slate-300');
                                @endphp
                                <div class="flex items-center justify-between rounded-xl border px-3 py-2 {{ $stepClass }}">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $dotClass }}"></span>
                                        <p class="text-sm font-semibold text-slate-800">{{ $step['title'] }}</p>
                                    </div>
                                    @if ($step['time'])
                                        <p class="text-xs text-slate-500">{{ $step['time']->format('d M Y H:i') }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($orderStatus === 'barang_rusak')
                            <p class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                {{ __('ui.orders.damage_note_intro') }}
                                @if ($hasDamageFeeOutstanding)
                                    {{ __('ui.orders.damage_note_with_fee') }}
                                @else
                                    {{ __('ui.orders.damage_note_without_fee') }}
                                @endif
                            </p>
                        @endif
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-blue-700">{{ $orderItemsTitle }}</h2>
                        <div class="mt-4 space-y-3">
                            @forelse ($order->items as $item)
                                <div class="rounded-xl border border-slate-100 p-4">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-blue-700">{{ $item->equipment->name ?? __('app.product.generic') }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ strtr($orderItemLineTemplate, [
                                                    ':qty' => (string) $item->qty,
                                                    ':price' => $formatIdr($item->price),
                                                ]) }}
                                            </p>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-800">{{ $formatIdr($item->subtotal) }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">{{ $orderItemsEmpty }}</p>
                            @endforelse
                        </div>
                    </article>

                    @if (\Illuminate\Support\Facades\Schema::hasTable('order_notifications') && $order->relationLoaded('notifications') && $order->notifications->isNotEmpty())
                        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $orderNotificationsTitle }}</h2>
                            <div class="mt-4 space-y-3">
                                @foreach ($order->notifications as $notification)
                                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                        <p class="text-sm font-semibold text-slate-800">{{ $notification->title }}</p>
                                        <p class="mt-1 text-xs text-slate-600">{{ $notification->message }}</p>
                                        <p class="mt-1 text-[11px] text-slate-400">{{ $notification->created_at?->format('d M Y H:i') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endif
                </div>

                <aside class="h-fit space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-blue-700">{{ $orderPaymentTitle }}</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>{{ $orderMidtransOrderIdLabel }}</span>
                            <span class="font-semibold text-slate-800">{{ $order->midtrans_order_id ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>{{ $orderStatusPesananLabel }}</span>
                            <span class="font-semibold text-slate-800">{{ strtoupper($order->status_pesanan ?? 'pending') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>{{ $orderSubtotalLabel }}</span>
                            <span class="font-semibold text-slate-800">{{ $formatIdr($baseTotal) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>{{ $orderTaxLabel }}</span>
                            <span class="font-semibold text-slate-800">{{ $formatIdr($taxAmount) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 text-slate-700">
                            <span class="font-semibold">{{ $orderTotalRentalLabel }}</span>
                            <span class="font-semibold text-slate-900">{{ $formatIdr($rentalGrandTotal) }}</span>
                        </div>
                        @if ($additionalFee > 0)
                            <div class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">{{ $orderAdditionalFeeSectionTitle }}</p>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $isDamageFeePaid ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $isDamageFeePaid ? $orderAdditionalFeePaidLabel : $orderAdditionalFeeUnpaidLabel }}
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-sm text-amber-700">
                                    <span>{{ $orderAdditionalFeeLabel }}</span>
                                    <span class="font-semibold">{{ $formatIdr($additionalFee) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-amber-700">{{ $orderAdditionalFeeNoTaxLabel }}</p>
                            </div>
                            @if ($order->additional_fee_note)
                                <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">{{ $order->additional_fee_note }}</p>
                            @endif
                        @endif
                        @if ($isDamageFeePaid && $additionalFee > 0)
                            <div class="flex justify-between border-t border-slate-200 pt-2 text-slate-700">
                                <span class="font-semibold">{{ $orderFinalTotalLabel }}</span>
                                <span class="font-semibold text-slate-900">{{ $formatIdr($grandTotal) }}</span>
                            </div>
                        @endif
                        @if ($order->admin_note)
                            <div class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-700">
                                <p class="font-semibold">{{ $orderAdminNoteLabel }}</p>
                                <p class="mt-1">{{ $order->admin_note }}</p>
                            </div>
                        @endif
                        @if ($order->paid_at)
                            <div class="flex justify-between text-slate-600">
                                <span>{{ $orderPaidAtLabel }}</span>
                                <span class="font-semibold text-slate-800">{{ $order->paid_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    @if ($canReschedule)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <h3 class="text-sm font-semibold text-slate-900">{{ $orderRescheduleTitle }}</h3>
                            <p class="mt-1 text-xs text-slate-600">{{ strtr($orderRescheduleDescTemplate, [':days' => (string) $rescheduleDurationDays]) }}</p>
                            <form method="POST" action="{{ route('account.orders.reschedule', $order) }}" class="mt-3 space-y-2">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $orderRescheduleStartLabel }}</label>
                                    <input
                                        type="date"
                                        name="rental_start_date"
                                        data-reschedule-start
                                        min="{{ now()->toDateString() }}"
                                        max="{{ $rescheduleMaxDate }}"
                                        value="{{ old('rental_start_date', optional($order->rental_start_date)->format('Y-m-d')) }}"
                                        required
                                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                    >
                                </div>
                                <div>
                                    <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $orderRescheduleEndLabel }}</label>
                                    <input
                                        type="date"
                                        name="rental_end_date"
                                        data-reschedule-end
                                        data-duration-days="{{ $rescheduleDurationDays }}"
                                        min="{{ now()->toDateString() }}"
                                        max="{{ $rescheduleMaxDate }}"
                                        value="{{ old('rental_end_date', optional($order->rental_end_date)->format('Y-m-d')) }}"
                                        readonly
                                        required
                                        class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                    >
                                    <p class="mt-1 text-[11px] text-slate-500">{{ strtr($orderRescheduleEndNoteTemplate, [':days' => (string) $rescheduleDurationDays]) }}</p>
                                </div>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">
                                    {{ $orderRescheduleSaveButton }}
                                </button>
                            </form>
                        </div>
                    @elseif ($hasPickedUp)
                        <p class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                            {{ $orderRescheduleLocked }}
                        </p>
                    @endif

                    @if ($isPrimaryPayable)
                        <button id="pay-now-button" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            {{ $orderPayNowButton }}
                        </button>
                        <button id="refresh-status-button" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:border-blue-200 hover:text-blue-600 transition">
                            {{ $orderRefreshPaymentButton }}
                        </button>
                        <p class="text-xs text-slate-500">{{ $orderPaymentNote }}</p>
                    @endif

                    @if ($hasDamageFeeOutstanding)
                        <button id="pay-damage-fee-button" class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                            {{ $orderPayAdditionalButton }}
                        </button>
                        <button id="refresh-damage-status-button" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                            {{ $orderRefreshAdditionalButton }}
                        </button>
                    @endif

                    @if ($canAccessInvoice && $signedInvoiceUrl)
                        <div class="space-y-2">
                            <button
                                type="button"
                                data-open-invoice-modal
                                data-invoice-url="{{ $signedInvoiceUrl }}"
                                data-invoice-pdf-url="{{ $signedInvoicePdfUrl }}"
                                data-invoice-preview-url="{{ $signedInvoicePdfPreviewUrl }}"
                                data-order-number="{{ $order->order_number ?? ('ORD-' . $order->id) }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:border-blue-200 hover:text-blue-600"
                            >
                                {{ $orderViewInvoiceButton }}
                            </button>
                            <a href="{{ $signedInvoicePdfUrl }}" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                {{ $orderDownloadPdfButton }}
                            </a>
                        </div>
                    @elseif ($isPaid && $hasDamageFeeOutstanding)
                        <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                            {{ $orderInvoiceLockedNote }}
                        </p>
                    @endif
                </aside>
            </div>
        </div>

        <div
            id="order-detail-invoice-modal"
            class="fixed inset-0 z-[95] hidden items-center justify-center p-3 sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="order-detail-invoice-title"
        >
            <div class="absolute inset-0 bg-slate-950/55" data-close-invoice-modal></div>

            <div class="relative z-10 flex h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-blue-600 px-4 py-3 text-white sm:px-5">
                    <div>
                        <h3 id="order-detail-invoice-title" class="text-base font-semibold sm:text-lg">
                            {{ __('ui.overview.invoice_detail_title') }}
                        </h3>
                        <p class="text-xs text-blue-100">{{ __('ui.invoice.title') }}</p>
                    </div>
                    <button
                        type="button"
                        data-close-invoice-modal
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-blue-300/80 bg-blue-500/60 p-0 text-white transition hover:bg-blue-500"
                        aria-label="{{ __('ui.overview.close_modal') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 bg-slate-100 p-2 sm:p-3">
                    <iframe
                        id="order-detail-invoice-frame"
                        title="{{ __('ui.invoice.title') }}"
                        loading="lazy"
                        class="h-full w-full rounded-xl border border-slate-200 bg-white"
                    ></iframe>
                </div>

                <div class="border-t border-slate-200 bg-white px-4 py-3">
                    <div class="flex justify-end">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a
                                id="order-detail-invoice-download"
                                href="{{ $signedInvoicePdfUrl }}"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600"
                            >
                                {{ $orderDownloadPdfButton }}
                            </a>
                            <button
                                type="button"
                                data-close-invoice-modal
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600"
                            >
                                {{ __('ui.overview.close_modal') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const copyButton = document.getElementById('copy-order-number');
            const valueNode = document.getElementById('order-number-text');

            if (!copyButton || !valueNode) return;

            copyButton.addEventListener('click', async () => {
                const orderNumber = valueNode.textContent?.trim();
                if (!orderNumber) return;

                try {
                    await navigator.clipboard.writeText(orderNumber);
                    copyButton.textContent = @json($orderCopySuccess);
                    setTimeout(() => {
                        copyButton.textContent = @json($orderCopyReceipt);
                    }, 1500);
                } catch (error) {
                    copyButton.textContent = @json($orderCopyFailed);
                    setTimeout(() => {
                        copyButton.textContent = @json($orderCopyReceipt);
                    }, 1500);
                }
            });
        })();

        (function () {
            const startInput = document.querySelector('[data-reschedule-start]');
            const endInput = document.querySelector('[data-reschedule-end]');

            if (!startInput || !endInput) return;

            const durationDays = Number(endInput.dataset.durationDays || 1);
            if (!Number.isFinite(durationDays) || durationDays < 1) return;

            const formatDate = (dateValue) => {
                const year = dateValue.getFullYear();
                const month = String(dateValue.getMonth() + 1).padStart(2, '0');
                const day = String(dateValue.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };

            const maxAllowed = startInput.getAttribute('max') || '';
            if (maxAllowed) {
                endInput.setAttribute('max', maxAllowed);
            }

            const syncEndDate = () => {
                if (!startInput.value) return;

                const startDate = new Date(`${startInput.value}T00:00:00`);
                if (Number.isNaN(startDate.getTime())) return;

                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + (durationDays - 1));
                const computedEnd = formatDate(endDate);
                endInput.value = computedEnd;

                if (maxAllowed && computedEnd > maxAllowed) {
                    startInput.setCustomValidity(@json(__('Tanggal sewa maksimal 3 bulan dari hari ini.')));
                } else {
                    startInput.setCustomValidity('');
                }
            };

            startInput.addEventListener('change', syncEndDate);
            startInput.addEventListener('input', syncEndDate);
            syncEndDate();
        })();

        (function () {
            const popup = document.getElementById('reschedule-conflict-popup');
            if (!popup) return;

            const closePopup = () => {
                popup.remove();
            };

            popup.querySelectorAll('[data-close-reschedule-popup]').forEach((button) => {
                button.addEventListener('click', closePopup);
            });

            popup.addEventListener('click', (event) => {
                if (event.target === popup) {
                    closePopup();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closePopup();
                }
            });
        })();

        (function () {
            const modal = document.getElementById('order-detail-invoice-modal');
            const frame = document.getElementById('order-detail-invoice-frame');
            const title = document.getElementById('order-detail-invoice-title');
            const openButtons = document.querySelectorAll('[data-open-invoice-modal]');
            const closeButtons = modal?.querySelectorAll('[data-close-invoice-modal]');
            const downloadButton = document.getElementById('order-detail-invoice-download');
            const defaultTitle = @json(__('ui.overview.invoice_detail_title'));
            let activePdfBaseUrl = '';

            if (!modal || !frame || !title || !openButtons.length) {
                return;
            }

            const buildPdfUrl = () => {
                if (!activePdfBaseUrl) {
                    return '';
                }
                return activePdfBaseUrl;
            };

            const syncDownloadUrl = () => {
                if (!downloadButton) {
                    return;
                }
                const nextUrl = buildPdfUrl();
                if (nextUrl) {
                    downloadButton.href = nextUrl;
                    downloadButton.classList.remove('pointer-events-none', 'opacity-50');
                } else {
                    downloadButton.href = '#';
                    downloadButton.classList.add('pointer-events-none', 'opacity-50');
                }
            };

            const openModal = (invoiceUrl, previewUrl, downloadUrl, orderNumber = '') => {
                const resolvedPreviewUrl = typeof previewUrl === 'string' && previewUrl !== ''
                    ? previewUrl
                    : (typeof invoiceUrl === 'string' && invoiceUrl !== ''
                        ? `${invoiceUrl}#embedded`
                        : '');

                if (!resolvedPreviewUrl) {
                    return;
                }

                frame.src = resolvedPreviewUrl;
                activePdfBaseUrl = typeof downloadUrl === 'string' ? downloadUrl : '';
                syncDownloadUrl();
                title.textContent = orderNumber ? `Invoice ${orderNumber}` : defaultTitle;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                title.textContent = defaultTitle;
                document.body.classList.remove('overflow-hidden');
                frame.src = 'about:blank';
                activePdfBaseUrl = '';
                syncDownloadUrl();
            };

            window.openOrderInvoiceModal = ({ invoiceUrl = '', previewUrl = '', pdfUrl = '', orderNumber = '' } = {}) => {
                openModal(invoiceUrl, previewUrl, pdfUrl, orderNumber);
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    openModal(
                        button.dataset.invoiceUrl,
                        button.dataset.invoicePreviewUrl || '',
                        button.dataset.invoicePdfUrl || '',
                        button.dataset.orderNumber || ''
                    );
                });
            });

            closeButtons?.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('flex')) {
                    closeModal();
                }
            });
        })();
    </script>

    @if ($loadSnapPaymentScript)
        @php
            $snapSrc = config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js';
        @endphp
        <script src="{{ $snapSrc }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script>
            (function () {
                const payButton = document.getElementById('pay-now-button');
                const refreshButton = document.getElementById('refresh-status-button');
                const damagePayButton = document.getElementById('pay-damage-fee-button');
                const refreshDamageButton = document.getElementById('refresh-damage-status-button');
                const alertBox = document.getElementById('payment-alert');
                if (!payButton && !damagePayButton) return;

                const showAlert = (message, type = 'info') => {
                    if (!alertBox) return;
                    const styles = {
                        info: 'border-slate-200 bg-white text-slate-700',
                        success: 'border-blue-200 bg-blue-50 text-blue-700',
                        error: 'border-rose-200 bg-rose-50 text-rose-700',
                    };
                    alertBox.className = `rounded-xl border px-4 py-3 text-sm ${styles[type] || styles.info}`;
                    alertBox.textContent = message;
                    alertBox.classList.remove('hidden');
                };

                const syncRentalPaymentStatus = async (redirectWhenPaid = false) => {
                    const response = await fetchWithCsrf(@json(route('payments.refresh-status', $order)), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || @json($orderPaymentSyncFailed));
                    }

                    if (data.is_paid && data.invoice_url) {
                        showAlert(@json($orderPaymentConfirmedInvoice), 'success');
                        if (redirectWhenPaid) {
                            if (typeof window.openOrderInvoiceModal === 'function') {
                                window.openOrderInvoiceModal({
                                    invoiceUrl: data.invoice_url,
                                    previewUrl: data.invoice_pdf_preview_url || '',
                                    pdfUrl: data.invoice_pdf_url || '',
                                    orderNumber: data.receipt_number || '',
                                });
                            } else {
                                window.location.href = data.invoice_url;
                            }
                        }
                    } else if (data.is_paid && data.has_damage_fee_outstanding) {
                        showAlert(@json($orderPaymentNeedAdditional), 'info');
                    }

                    return data;
                };

                const syncDamagePaymentStatus = async () => {
                    const response = await fetchWithCsrf(@json(route('payments.damage-fee.refresh-status', $order)), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || @json($orderAdditionalSyncFailed));
                    }

                    if (data.is_paid) {
                        showAlert(@json($orderAdditionalPaid), 'success');
                    }

                    return data;
                };

                const processPayment = async (config) => {
                    if (!config.button) return;
                    config.button.disabled = true;
                    config.button.textContent = @json($orderProcessing);
                    showAlert(@json($orderCreatingSession), 'info');

                    try {
                        const response = await fetchWithCsrf(config.tokenEndpoint, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json();

                        if (!response.ok || !data.snap_token) {
                            throw new Error(data.message || @json($orderSessionFailed));
                        }

                        if (!window.snap) {
                            throw new Error(@json($orderSnapNotReady));
                        }

                        window.snap.pay(data.snap_token, {
                            onSuccess: async function () {
                                showAlert(@json($orderPaymentSuccessSync), 'success');
                                try {
                                    const status = await config.refreshStatus();
                                    if (!status.is_paid || config.forceReloadAfterPaid) {
                                        setTimeout(() => window.location.reload(), 900);
                                    }
                                } catch (error) {
                                    showAlert(error.message || @json($orderPaymentSuccessSyncFailed), 'info');
                                    setTimeout(() => window.location.reload(), 900);
                                }
                            },
                            onPending: async function () {
                                showAlert(@json($orderPaymentPending), 'info');
                                try {
                                    await config.refreshStatus();
                                } catch (error) {
                                    // fallback ke reload normal
                                }
                                setTimeout(() => window.location.reload(), 900);
                            },
                            onError: function () {
                                showAlert(@json($orderPaymentFailed), 'error');
                            },
                            onClose: function () {
                                showAlert(@json($orderPopupClosed), 'info');
                            },
                        });
                    } catch (error) {
                        showAlert(error.message || @json($orderPaymentOpenError), 'error');
                    } finally {
                        config.button.disabled = false;
                        config.button.textContent = config.defaultLabel;
                    }
                };

                payButton?.addEventListener('click', () => processPayment({
                    button: payButton,
                    tokenEndpoint: @json(route('payments.snap-token', $order)),
                    refreshStatus: () => syncRentalPaymentStatus(true),
                    defaultLabel: @json($orderPayNowButton),
                    forceReloadAfterPaid: false,
                }));

                damagePayButton?.addEventListener('click', () => processPayment({
                    button: damagePayButton,
                    tokenEndpoint: @json(route('payments.damage-fee.snap-token', $order)),
                    refreshStatus: () => syncDamagePaymentStatus(),
                    defaultLabel: @json($orderPayAdditionalButton),
                    forceReloadAfterPaid: true,
                }));

                refreshButton?.addEventListener('click', async () => {
                    refreshButton.disabled = true;
                    const defaultText = refreshButton.textContent;
                    refreshButton.textContent = @json($orderChecking);

                    try {
                        const status = await syncRentalPaymentStatus(true);
                        if (!status.is_paid) {
                            showAlert(@json($orderStatusUnpaid), 'info');
                        }
                    } catch (error) {
                        showAlert(error.message || @json($orderStatusCheckFailed), 'error');
                    } finally {
                        refreshButton.disabled = false;
                        refreshButton.textContent = defaultText;
                    }
                });

                refreshDamageButton?.addEventListener('click', async () => {
                    refreshDamageButton.disabled = true;
                    const defaultText = refreshDamageButton.textContent;
                    refreshDamageButton.textContent = @json($orderChecking);

                    try {
                        const status = await syncDamagePaymentStatus();
                        if (!status.is_paid) {
                            showAlert(@json($orderAdditionalUnpaid), 'info');
                        } else {
                            setTimeout(() => window.location.reload(), 800);
                        }
                    } catch (error) {
                        showAlert(error.message || @json($orderAdditionalCheckFailed), 'error');
                    } finally {
                        refreshDamageButton.disabled = false;
                        refreshDamageButton.textContent = defaultText;
                    }
                });

                let pollAttempts = 0;
                const maxPollAttempts = 15;
                const pollStatus = async () => {
                    if (pollAttempts >= maxPollAttempts) {
                        return;
                    }

                    pollAttempts += 1;

                    try {
                        const status = await syncRentalPaymentStatus(true);
                        if (!status.is_paid) {
                            setTimeout(pollStatus, 12000);
                        }
                    } catch (error) {
                        setTimeout(pollStatus, 12000);
                    }
                };

                if (payButton) {
                    setTimeout(pollStatus, 7000);
                }

                @if (session('pay_now'))
                    payButton?.click();
                @endif
            })();
        </script>
    @endif
@endpush
