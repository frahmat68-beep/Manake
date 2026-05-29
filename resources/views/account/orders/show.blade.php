@extends('layouts.app')

@section('title', __('ui.orders.detail_title'))

@push('head')
    <style>
        .order-detail-page {
            --order-accent: #D4A843;
            --order-accent-hover: #E0BA5D;
            --order-accent-text: #0A0A0B;
            --order-accent-soft: rgba(212, 168, 67, 0.12);
            --order-accent-border: rgba(212, 168, 67, 0.32);
            --order-accent-glow: rgba(212, 168, 67, 0.16);

            --order-bg: #0A0A0B;
            --order-surface: #111113;
            --order-surface-soft: rgba(17, 17, 19, 0.72);
            --order-surface-muted: #0A0A0B;
            --order-border: #1A1A1E;
            --order-text: #E8E8EC;
            --order-muted: #A0A0A8;
            --order-subtle: #7C7C84;
        }

        html[data-theme-resolved="light"] .order-detail-page {
            --order-accent: #2563EB;
            --order-accent-hover: #1D4ED8;
            --order-accent-text: #FFFFFF;
            --order-accent-soft: rgba(37, 99, 235, 0.10);
            --order-accent-border: rgba(37, 99, 235, 0.30);
            --order-accent-glow: rgba(37, 99, 235, 0.12);

            --order-bg: #F8FAFC;
            --order-surface: #FFFFFF;
            --order-surface-soft: rgba(255, 255, 255, 0.94);
            --order-surface-muted: #F8FAFC;
            --order-border: #E5E7EB;
            --order-text: #111827;
            --order-muted: #4B5563;
            --order-subtle: #6B7280;
        }

        .order-page-bg {
            background-color: var(--order-bg) !important;
            color: var(--order-text) !important;
        }

        .order-card {
            background: var(--order-surface-soft) !important;
            border-color: var(--order-border) !important;
            color: var(--order-text) !important;
        }

        .order-card-solid {
            background: var(--order-surface) !important;
            border-color: var(--order-border) !important;
            color: var(--order-text) !important;
        }

        .order-inner {
            background: var(--order-surface-muted) !important;
            border-color: var(--order-border) !important;
            color: var(--order-text) !important;
        }

        .order-title {
            color: var(--order-text) !important;
        }

        .order-muted {
            color: var(--order-muted) !important;
        }

        .order-subtle {
            color: var(--order-subtle) !important;
        }

        .order-border {
            border-color: var(--order-border) !important;
        }

        .order-accent-text {
            color: var(--order-accent) !important;
        }

        .order-accent-bg {
            background: var(--order-accent) !important;
            background-color: var(--order-accent) !important;
            color: var(--order-accent-text) !important;
            border-color: var(--order-accent) !important;
        }

        .order-accent-bg:hover {
            background: var(--order-accent-hover) !important;
            background-color: var(--order-accent-hover) !important;
        }

        .order-accent-soft {
            background: var(--order-accent-soft) !important;
            border-color: var(--order-accent-border) !important;
            color: var(--order-accent) !important;
        }

        .order-accent-dot {
            background-color: var(--order-accent) !important;
        }

        .order-accent-glow {
            background-color: var(--order-accent-glow) !important;
        }

        .order-accent-border-left {
            border-left: 5px solid var(--order-accent) !important;
        }

        .order-accent-ring {
            box-shadow: 0 0 0 3px var(--order-accent-soft) !important;
        }

        .order-secondary-button {
            background: var(--order-surface) !important;
            border: 1px solid var(--order-border) !important;
            color: var(--order-text) !important;
        }

        .order-secondary-button:hover {
            border-color: var(--order-accent-border) !important;
            color: var(--order-accent) !important;
        }

        .order-primary-outline {
            background: var(--order-accent-soft) !important;
            border: 1px solid var(--order-accent-border) !important;
            color: var(--order-accent) !important;
        }

        .order-primary-outline:hover {
            background: var(--order-accent) !important;
            color: var(--order-accent-text) !important;
        }

        .order-input {
            background: var(--order-surface) !important;
            border: 1px solid var(--order-border) !important;
            color: var(--order-text) !important;
            border-radius: 0.75rem !important;
            outline: none !important;
        }

        .order-input:focus {
            border-color: var(--order-accent) !important;
            box-shadow: 0 0 0 3px var(--order-accent-soft) !important;
        }

        html[data-theme-resolved="light"] .order-input {
            color-scheme: light !important;
        }

        html[data-theme-resolved="dark"] .order-input {
            color-scheme: dark !important;
        }

        html[data-theme-resolved="light"] .order-detail-page .order-card,
        html[data-theme-resolved="light"] .order-detail-page .order-card-solid {
            box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.22);
        }

        .order-status-paid {
            border-color: rgba(16, 185, 129, 0.28) !important;
            background: #ECFDF5 !important;
            color: #047857 !important;
        }

        .order-status-warning {
            border-color: rgba(245, 158, 11, 0.28) !important;
            background: #FFFBEB !important;
            color: #B45309 !important;
        }

        .order-status-danger {
            border-color: rgba(244, 63, 94, 0.28) !important;
            background: #FFF1F2 !important;
            color: #BE123C !important;
        }

        .order-status-neutral {
            border-color: var(--order-border) !important;
            background: var(--order-surface-muted) !important;
            color: var(--order-muted) !important;
        }

        html[data-theme-resolved="dark"] .order-status-paid {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #A7F3D0 !important;
        }

        html[data-theme-resolved="dark"] .order-status-warning {
            background: rgba(245, 158, 11, 0.12) !important;
            color: #FDE68A !important;
        }

        html[data-theme-resolved="dark"] .order-status-danger {
            background: rgba(244, 63, 94, 0.12) !important;
            color: #FDA4AF !important;
        }

        .order-alert-info {
            border-color: #CBD5E1 !important;
            background: #F8FAFC !important;
            color: #334155 !important;
        }

        .order-alert-success {
            border-color: rgba(16, 185, 129, 0.28) !important;
            background: #ECFDF5 !important;
            color: #047857 !important;
        }

        .order-alert-error {
            border-color: rgba(244, 63, 94, 0.28) !important;
            background: #FFF1F2 !important;
            color: #BE123C !important;
        }

        html[data-theme-resolved="dark"] .order-alert-info {
            background: rgba(15, 23, 42, 0.40) !important;
            color: #CBD5E1 !important;
        }

        html[data-theme-resolved="dark"] .order-alert-success {
            background: rgba(6, 78, 59, 0.38) !important;
            color: #A7F3D0 !important;
        }

        html[data-theme-resolved="dark"] .order-alert-error {
            background: rgba(136, 19, 55, 0.38) !important;
            color: #FDA4AF !important;
        }
    </style>
@endpush

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
        'paid' => ['label' => __('ui.invoice.status.paid'), 'badge' => 'order-status-paid'],
        'failed' => ['label' => __('ui.invoice.status.failed'), 'badge' => 'order-status-danger'],
        'expired' => ['label' => __('ui.invoice.status.expired'), 'badge' => 'order-status-neutral'],
        'refunded' => ['label' => __('ui.invoice.status.refunded'), 'badge' => 'order-status-warning'],
        default => ['label' => __('ui.invoice.status.pending'), 'badge' => 'order-status-warning'],
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
    $orderDetailTitle = __('ui.orders.detail_title');
    $orderDetailSubtitle = __('ui.orders.page_subtitle');
    $orderDetailBackLabel = __('ui.orders.back_to_history');
    $orderNumberLabel = __('ui.orders.order_number');
    $orderProgressTitle = __('ui.orders.progress_title');
    $orderItemsTitle = __('ui.orders.items_title');
    $orderPaymentTitle = __('ui.orders.payment_title');
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
    $orderCancelButton = __('ui.orders.cancel_order_button');
    $orderCancelConfirm = __('ui.orders.cancel_order_confirm');
    $orderDateMaxWindowError = __('ui.orders.date_max_window_error');
    $orderInvoiceModalTitleTemplate = __('ui.orders.invoice_modal_title_template');
@endphp

@section('content')
    <div class="order-detail-page order-page-bg min-h-screen">
        <div class="mx-auto w-full max-w-[1280px] px-4 py-8 pb-24 sm:px-6 lg:px-8 lg:py-10 space-y-6">
            <header class="order-card relative overflow-hidden rounded-3xl border p-6 shadow-[0_24px_70px_-50px_rgba(0,0,0,0.35)] animate-fade-up sm:p-7">
                <span class="order-accent-bg absolute left-0 top-0 h-1 w-full"></span>
                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div class="max-w-3xl">
                        <p class="order-accent-text text-xs font-black uppercase tracking-[0.2em]">
                            {{ $orderOrderIdLabel }}
                        </p>
                        <h1 class="order-title mt-3 text-[clamp(2rem,4vw,3.1rem)] font-black leading-[0.98] tracking-tight">
                            {{ $orderDetailTitle }}
                        </h1>
                        <p class="order-muted mt-3 max-w-2xl text-sm leading-6 sm:text-base">
                            {{ $orderDetailSubtitle }}
                        </p>
                    </div>

                    <a href="{{ route('booking.history') }}" class="order-secondary-button inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition md:w-auto">
                        ← {{ $orderDetailBackLabel }}
                    </a>
                </div>
            </header>

            <div id="payment-alert" class="hidden rounded-xl border px-4 py-3 text-sm font-semibold"></div>

            @if (session('error'))
                <div class="rounded-xl border border-rose-500/20 bg-rose-950/70 px-4 py-3 text-sm text-rose-300">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-950/70 px-4 py-3 text-sm text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if ($rescheduleConflictPopupMessage)
                <div id="reschedule-conflict-popup" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 px-4">
                    <div class="w-full max-w-md rounded-3xl border order-card-solid p-5 shadow-2xl">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-600">{{ $orderScheduleUnavailableTitle }}</p>
                                <p class="mt-2 text-sm font-semibold order-title">{{ $orderScheduleUnavailableSubtitle }}</p>
                            </div>
                            <button
                                type="button"
                                data-close-reschedule-popup
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border order-border text-current transition hover:border-rose-500/20 hover:text-rose-300"
                                aria-label="{{ $orderPopupCloseAria }}"
                            >
                                ×
                            </button>
                        </div>
                        <p class="mt-3 rounded-xl border border-rose-500/20 bg-rose-950/70 px-3 py-2 text-sm text-rose-300">
                            {{ $rescheduleConflictPopupMessage }}
                        </p>
                        <div class="mt-4 flex justify-end">
                            <button
                                type="button"
                                data-close-reschedule-popup
                                class="order-secondary-button inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition"
                            >
                                {{ $orderPopupCloseButton }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($hasDamageFeeOutstanding)
                <div class="rounded-3xl border border-rose-500/20 bg-rose-950/70 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-300">{{ $orderAdditionalFeeRequiredTitle }}</p>
                    <p class="mt-1 text-lg font-semibold text-rose-200">{{ strtr($orderAdditionalFeeRequiredDesc, [':fee' => $formatIdr($additionalFee)]) }}</p>
                    <p class="mt-1 text-sm text-rose-300">{{ $orderAdditionalFeeRequiredTaxNote }}</p>
                    @if ($order->additional_fee_note)
                        <p class="mt-2 rounded-xl border border-rose-500/20 bg-[#0A0A0B] px-3 py-2 text-xs text-rose-300">{{ $order->additional_fee_note }}</p>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start">
                <div class="space-y-6">
                    <article class="order-card rounded-3xl border p-6 shadow-2xl sm:p-7 animate-fade-up">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="order-accent-text text-xs font-black uppercase tracking-[0.18em]">
                                    {{ $orderNumberLabel }}
                                </p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <h2 id="order-number-text" class="order-title break-all text-xl font-black">
                                        {{ $order->order_number ?? ('ORD-' . $order->id) }}
                                    </h2>
                                    <button
                                        type="button"
                                        id="copy-order-number"
                                        class="order-primary-outline rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                                    >
                                        {{ $orderCopyReceipt }}
                                    </button>
                                </div>
                            </div>
                            <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-semibold {{ $statusMeta['badge'] }}">
                                {{ $statusMeta['label'] }}
                            </span>
                        </div>
 
                        <div class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                            <div class="order-inner rounded-2xl border px-4 py-3 transition hover:border-[var(--order-accent-border)]">
                                <p class="text-xs order-muted">{{ $orderOrderIdLabel }}</p>
                                <p class="mt-1 font-semibold order-title">#{{ $order->id }}</p>
                            </div>
                            <div class="order-inner rounded-2xl border px-4 py-3 transition hover:border-[var(--order-accent-border)]">
                                <p class="text-xs order-muted">{{ $orderRentalPeriodLabel }}</p>
                                <p class="mt-1 font-semibold order-title">
                                    {{ optional($order->rental_start_date)->format('d M Y') }} - {{ optional($order->rental_end_date)->format('d M Y') }}
                                </p>
                            </div>
                            <div class="order-inner rounded-2xl border px-4 py-3 transition hover:border-[var(--order-accent-border)]">
                                <p class="text-xs order-muted">{{ $orderRentalStatusLabel }}</p>
                                <p class="mt-1 font-semibold order-title">{{ $statusLabel($orderStatus) }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="order-card rounded-3xl border p-6 shadow-2xl sm:p-7 animate-fade-up">
                        <h2 class="order-title flex items-center gap-3 text-xl font-black">
                            <span class="order-accent-dot h-3 w-3 rounded-full"></span>
                            {{ $orderProgressTitle }}
                        </h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($timeline as $step)
                                @php
                                    $stepClass = $step['done'] || $step['active']
                                        ? 'order-accent-soft'
                                        : 'order-inner';
                                    $dotClass = ($step['done'] || $step['active'])
                                        ? 'order-accent-dot'
                                        : 'bg-slate-300 dark:bg-[#2A2A2E]';
                                @endphp
                                <div class="flex items-center justify-between rounded-2xl border px-4 py-3 {{ $stepClass }}">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $dotClass }}"></span>
                                        <p class="text-sm font-semibold order-title">{{ $step['title'] }}</p>
                                    </div>
                                    @if ($step['time'])
                                        <p class="text-xs order-muted">{{ $step['time']->format('d M Y H:i') }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($orderStatus === 'barang_rusak')
                            <p class="mt-3 rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                {{ __('ui.orders.damage_note_intro') }}
                                @if ($hasDamageFeeOutstanding)
                                    {{ __('ui.orders.damage_note_with_fee') }}
                                @else
                                    {{ __('ui.orders.damage_note_without_fee') }}
                                @endif
                            </p>
                        @endif
                    </article>

                    <article class="order-card rounded-3xl border p-6 shadow-2xl sm:p-7 animate-fade-up">
                        <h2 class="order-title flex items-center gap-3 text-xl font-black">
                            <span class="order-accent-dot h-3 w-3 rounded-full"></span>
                            {{ $orderItemsTitle }}
                        </h2>
                        <div class="mt-4 space-y-3">
                            @forelse ($order->items as $item)
                                <div class="order-inner rounded-2xl border p-4">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-4">
                                            @if ($item->equipment)
                                                <div class="order-card-solid h-14 w-14 flex-shrink-0 overflow-hidden rounded-xl border p-1 shadow-sm bg-white">
                                                    <img
                                                        src="{{ site_media_url($item->equipment->image_path ?? $item->equipment->image ?? null) ?: config('placeholders.equipment') }}"
                                                        alt="{{ $item->equipment->name }}"
                                                        class="h-full w-full object-contain"
                                                        onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';"
                                                    >
                                                </div>
                                            @endif
                                            <div>
                                                <p class="text-sm font-semibold order-title">{{ $item->equipment->name ?? __('app.product.generic') }}</p>
                                                <p class="text-xs order-muted">
                                                    {{ strtr($orderItemLineTemplate, [
                                                        ':qty' => (string) $item->qty,
                                                        ':price' => $formatIdr($item->price),
                                                    ]) }}
                                                </p>
                                            </div>
                                        </div>
                                        <p class="text-sm font-semibold order-title">{{ $formatIdr($item->subtotal) }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="order-muted text-sm">{{ $orderItemsEmpty }}</p>
                            @endforelse
                        </div>
                    </article>

                    @if (\Illuminate\Support\Facades\Schema::hasTable('order_notifications') && $order->relationLoaded('notifications') && $order->notifications->isNotEmpty())
                        <article class="order-card rounded-3xl border p-6 shadow-2xl sm:p-7">
                            <h2 class="order-title text-xl font-black">{{ $orderNotificationsTitle }}</h2>
                            <div class="mt-4 space-y-3">
                                @foreach ($order->notifications as $notification)
                                    <div class="order-inner rounded-2xl border px-4 py-3">
                                        <p class="text-sm font-semibold order-title">{{ $notification->title }}</p>
                                        <p class="mt-1 text-xs order-muted">{{ $notification->message }}</p>
                                        <p class="mt-1 text-[11px] order-muted">{{ $notification->created_at?->format('d M Y H:i') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endif
                </div>

                <aside class="space-y-5 lg:sticky lg:top-28">
                    <article class="order-card-solid relative overflow-hidden rounded-3xl border p-6 shadow-2xl">
                        <span class="order-accent-glow pointer-events-none absolute right-0 top-0 h-36 w-36 translate-x-1/3 -translate-y-1/3 rounded-full blur-3xl"></span>
                        <div class="relative z-10">
                            <h2 class="order-title mb-6 flex items-center gap-3 text-2xl font-black">
                                <span class="order-accent-dot h-3 w-3 rounded-full"></span>
                                {{ $orderPaymentTitle }}
                            </h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-start justify-between gap-4 order-muted">
                                    <span class="shrink-0">{{ $orderMidtransOrderIdLabel }}</span>
                                    <span class="min-w-0 max-w-[58%] break-all text-right font-semibold order-title">{{ $order->midtrans_order_id ?? '-' }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-4 order-muted">
                                    <span class="shrink-0">{{ $orderStatusPesananLabel }}</span>
                                    <span class="min-w-0 max-w-[58%] break-words text-right font-semibold order-title">{{ $statusLabel($orderStatus) }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-4 order-muted">
                                    <span class="shrink-0">{{ $orderSubtotalLabel }}</span>
                                    <span class="min-w-0 max-w-[58%] break-words text-right font-semibold order-title">{{ $formatIdr($baseTotal) }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-4 order-muted">
                                    <span class="shrink-0">{{ $orderTaxLabel }}</span>
                                    <span class="min-w-0 max-w-[58%] break-words text-right font-semibold order-title">{{ $formatIdr($taxAmount) }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-4 border-t order-border pt-3">
                                    <span class="shrink-0 font-semibold order-title">{{ $orderTotalRentalLabel }}</span>
                                    <span class="min-w-0 max-w-[58%] break-words text-right font-black order-title">{{ $formatIdr($rentalGrandTotal) }}</span>
                                </div>
                                @if ($additionalFee > 0)
                                    <div class="mt-2 rounded-2xl border border-amber-500/20 bg-amber-950/20 p-3.5">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-400">{{ $orderAdditionalFeeSectionTitle }}</p>
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $isDamageFeePaid ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                                {{ $isDamageFeePaid ? $orderAdditionalFeePaidLabel : $orderAdditionalFeeUnpaidLabel }}
                                            </span>
                                        </div>
                                        <div class="mt-1 flex items-start justify-between gap-4 text-sm text-amber-300">
                                            <span class="shrink-0">{{ $orderAdditionalFeeLabel }}</span>
                                            <span class="min-w-0 max-w-[58%] break-words text-right font-semibold">{{ $formatIdr($additionalFee) }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-amber-400/80">{{ $orderAdditionalFeeNoTaxLabel }}</p>
                                    </div>
                                    @if ($order->additional_fee_note)
                                        <p class="rounded-2xl border border-amber-500/20 bg-[#0A0A0B] px-3 py-2 text-xs text-amber-300">{{ $order->additional_fee_note }}</p>
                                    @endif
                                @endif
                                @if ($isDamageFeePaid && $additionalFee > 0)
                                    <div class="flex items-start justify-between gap-4 border-t order-border pt-3 order-muted">
                                        <span class="shrink-0 font-semibold">{{ $orderFinalTotalLabel }}</span>
                                        <span class="min-w-0 max-w-[58%] break-words text-right font-semibold order-title">{{ $formatIdr($grandTotal) }}</span>
                                    </div>
                                @endif
                                @if ($order->admin_note)
                                    <div class="rounded-2xl border order-accent-soft px-3 py-2 text-xs">
                                        <p class="font-semibold">{{ $orderAdminNoteLabel }}</p>
                                        <p class="mt-1">{{ $order->admin_note }}</p>
                                    </div>
                                @endif
                                @if ($order->paid_at)
                                    <div class="flex items-start justify-between gap-4 order-muted">
                                        <span class="shrink-0">{{ $orderPaidAtLabel }}</span>
                                        <span class="min-w-0 max-w-[58%] break-words text-right font-semibold order-title">{{ $order->paid_at->format('d M Y H:i') }}</span>
                                    </div>
                                @endif
                            </div>

                            @if ($canReschedule)
                                <div class="order-inner rounded-2xl border p-4 mt-5">
                                    <h3 class="text-sm font-semibold order-title">{{ $orderRescheduleTitle }}</h3>
                                    <p class="mt-1 text-xs order-muted">{{ strtr($orderRescheduleDescTemplate, [':days' => (string) $rescheduleDurationDays]) }}</p>
                                    <form method="POST" action="{{ route('account.orders.reschedule', $order) }}" class="mt-4 space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="text-[11px] font-semibold uppercase tracking-wide order-muted">{{ $orderRescheduleStartLabel }}</label>
                                            <input
                                                type="date"
                                                name="rental_start_date"
                                                data-reschedule-start
                                                min="{{ now()->toDateString() }}"
                                                max="{{ $rescheduleMaxDate }}"
                                                value="{{ old('rental_start_date', optional($order->rental_start_date)->format('Y-m-d')) }}"
                                                required
                                                class="order-input mt-1 w-full px-3 py-2 text-xs"
                                            >
                                        </div>
                                        <div>
                                            <label class="text-[11px] font-semibold uppercase tracking-wide order-muted">{{ $orderRescheduleEndLabel }}</label>
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
                                                class="order-inner mt-1 w-full px-3 py-2 text-xs order-title"
                                            >
                                            <p class="mt-1 text-[11px] order-muted">{{ strtr($orderRescheduleEndNoteTemplate, [':days' => (string) $rescheduleDurationDays]) }}</p>
                                        </div>
                                        <button type="submit" class="order-secondary-button order-accent-link inline-flex w-full items-center justify-center rounded-xl px-3 py-2 text-xs font-semibold transition">
                                            {{ $orderRescheduleSaveButton }}
                                        </button>
                                    </form>
                                </div>
                            @elseif ($hasPickedUp)
                                <p class="order-accent-soft rounded-2xl border px-3 py-2 text-xs font-medium mt-5">
                                    {{ $orderRescheduleLocked }}
                                </p>
                            @endif

                            @if ($isPrimaryPayable)
                                <button id="pay-now-button" class="order-accent-bg mt-4 inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                    {{ $orderPayNowButton }}
                                </button>
                                <button id="refresh-status-button" class="order-secondary-button mt-2 inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                    {{ $orderRefreshPaymentButton }}
                                </button>
                                @if ($orderStatus === 'menunggu_pembayaran')
                                    <form action="{{ route('account.orders.cancel', $order) }}" method="POST" class="mt-2" onsubmit="return confirm('{{ $orderCancelConfirm }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-rose-500/25 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 dark:bg-rose-950/20 dark:text-rose-300 dark:hover:bg-rose-950/40">
                                            {{ $orderCancelButton }}
                                        </button>
                                    </form>
                                @endif
                                <p class="mt-2 text-xs order-muted text-center">{{ $orderPaymentNote }}</p>
                            @endif

                            @if ($hasDamageFeeOutstanding)
                                <button id="pay-damage-fee-button" class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                                    {{ $orderPayAdditionalButton }}
                                </button>
                                <button id="refresh-damage-status-button" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-rose-500/20 bg-[#0A0A0B] px-4 py-2.5 text-sm font-semibold text-rose-500 transition hover:bg-rose-500/10">
                                    {{ $orderRefreshAdditionalButton }}
                                </button>
                            @endif

                            @if ($canAccessInvoice && $signedInvoiceUrl)
                                <div class="space-y-2 mt-4">
                                    <button
                                        type="button"
                                        data-open-invoice-modal
                                        data-invoice-url="{{ $signedInvoiceUrl }}"
                                        data-invoice-pdf-url="{{ $signedInvoicePdfUrl }}"
                                        data-invoice-preview-url="{{ $signedInvoicePdfPreviewUrl }}"
                                        data-order-number="{{ $order->order_number ?? ('ORD-' . $order->id) }}"
                                        class="order-secondary-button inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                                    >
                                        {{ $orderViewInvoiceButton }}
                                    </button>
                                    <a href="{{ $signedInvoicePdfUrl }}" data-skip-loader="true" class="order-accent-bg inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                        {{ $orderDownloadPdfButton }}
                                    </a>
                                </div>
                            @elseif ($isPaid && $hasDamageFeeOutstanding)
                                <p class="rounded-2xl border border-amber-500/20 bg-amber-950/20 px-3 py-2 text-xs text-amber-300 mt-4">
                                    {{ $orderInvoiceLockedNote }}
                                </p>
                            @endif
                        </div>
                    </article>
                </aside>
            </div>
        </div>
    </div>

    <div
        id="order-detail-invoice-modal"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/70 p-3 sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="order-detail-invoice-title"
    >
        <div class="absolute inset-0" data-close-invoice-modal></div>

        <div class="order-detail-page relative z-10 flex h-[min(88vh,860px)] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border order-card-solid shadow-2xl">
            <div class="order-accent-bg flex shrink-0 items-center justify-between gap-4 border-b px-4 py-3 sm:px-5">
                <div>
                    <h3 id="order-detail-invoice-title" class="text-base font-semibold sm:text-lg">
                        {{ __('ui.overview.invoice_detail_title') }}
                    </h3>
                    <p class="text-xs opacity-70">{{ __('ui.invoice.title') }}</p>
                </div>
                <button
                    type="button"
                    data-close-invoice-modal
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-black/20 bg-black/10 p-0 text-current transition hover:bg-black/20"
                    aria-label="{{ __('ui.overview.close_modal') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="min-h-0 flex-1 bg-slate-100 p-2 sm:p-3 dark:bg-[#0A0A0B]">
                <iframe
                    id="order-detail-invoice-frame"
                    title="{{ __('ui.invoice.title') }}"
                    loading="lazy"
                    class="h-full w-full rounded-xl border order-border bg-white"
                ></iframe>
            </div>

            <div class="order-card-solid shrink-0 border-t order-border px-4 py-3">
                <div class="flex justify-end">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <a
                            id="order-detail-invoice-download"
                            href="{{ $signedInvoicePdfUrl }}"
                            data-skip-loader="true"
                            class="order-secondary-button inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition"
                        >
                            {{ $orderDownloadPdfButton }}
                        </a>
                        <button
                            type="button"
                            data-close-invoice-modal
                            class="order-secondary-button inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition"
                        >
                            {{ __('ui.overview.close_modal') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }

                let finalPreviewUrl = resolvedPreviewUrl;
                try {
                    const urlObj = new URL(resolvedPreviewUrl, window.location.origin);
                    urlObj.hash = 'toolbar=0&navpanes=0&scrollbar=1';
                    finalPreviewUrl = urlObj.toString();
                } catch (e) {
                    // Fallback if URL constructor fails (e.g. invalid url)
                }

                frame.src = finalPreviewUrl;
                activePdfBaseUrl = typeof downloadUrl === 'string' ? downloadUrl : '';
                syncDownloadUrl();
                const invoiceTitleTemplate = @json($orderInvoiceModalTitleTemplate);
                title.textContent = orderNumber
                    ? invoiceTitleTemplate.replace(':order', orderNumber)
                    : defaultTitle;
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
                        info: 'border-slate-200 bg-[#111113] text-slate-700',
                        success: 'border-[#1A1A1E] bg-[#111113] text-[#D4A843]',
                        error: 'border-rose-200 bg-rose-50 text-rose-700',
                    };
                    alertBox.className = `rounded-xl border px-4 py-3 text-sm ${styles[type] || styles.info}`;
                    alertBox.textContent = message;
                    alertBox.classList.remove('hidden');
                };

                const syncRentalPaymentStatus = async (redirectWhenPaid = false) => {
                    const response = await window.fetchWithCsrf(@json(route('payments.refresh-status', $order)), {
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
                    const response = await window.fetchWithCsrf(@json(route('payments.damage-fee.refresh-status', $order)), {
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
                        const response = await window.fetchWithCsrf(config.tokenEndpoint, {
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
