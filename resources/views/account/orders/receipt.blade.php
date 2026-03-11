<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.invoice.title') }} {{ $order->order_number ?? ('ORD-' . $order->id) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    <script>
        if ((window.location.hash || '').toLowerCase().includes('embedded')) {
            document.documentElement.classList.add('invoice-embedded');
        }
    </script>
    <style>
        :root {
            --invoice-bg: #f6f8fc;
            --invoice-surface: #ffffff;
            --invoice-surface-soft: #f9fbff;
            --invoice-border: #d8e2f0;
            --invoice-text: #0f172a;
            --invoice-muted: #64748b;
            --invoice-heading: #0b1530;
            --invoice-primary: #2f5ff5;
            --invoice-primary-strong: #2248ca;
            --invoice-primary-soft: #eaf0ff;
            --invoice-success-bg: #dcfce7;
            --invoice-success-text: #15803d;
            --invoice-warning-bg: #fef3c7;
            --invoice-warning-text: #b45309;
            --invoice-danger-bg: #fee2e2;
            --invoice-danger-text: #b91c1c;
            --invoice-shadow: 0 20px 40px rgba(15, 23, 42, 0.08), 0 8px 18px rgba(15, 23, 42, 0.05);
            --invoice-radius-xl: 18px;
            --invoice-radius-lg: 14px;
            --invoice-brand-gradient: linear-gradient(135deg, #1f409f 0%, #345fd5 54%, #1b367f 100%);
        }

        html[data-theme-resolved='dark'],
        html.dark {
            --invoice-bg: #0b1220;
            --invoice-surface: #101a2d;
            --invoice-surface-soft: #14203a;
            --invoice-border: #2a3a58;
            --invoice-text: #e7eefb;
            --invoice-muted: #9eb0ce;
            --invoice-heading: #f8fbff;
            --invoice-primary: #4f7dff;
            --invoice-primary-strong: #3a64de;
            --invoice-primary-soft: rgba(79, 125, 255, 0.16);
            --invoice-success-bg: rgba(22, 163, 74, 0.22);
            --invoice-success-text: #86efac;
            --invoice-warning-bg: rgba(217, 119, 6, 0.2);
            --invoice-warning-text: #fcd34d;
            --invoice-danger-bg: rgba(220, 38, 38, 0.22);
            --invoice-danger-text: #fda4af;
            --invoice-shadow: 0 22px 48px rgba(2, 6, 23, 0.45), 0 12px 24px rgba(2, 6, 23, 0.32);
            --invoice-brand-gradient: linear-gradient(135deg, #0f245f 0%, #2546a9 54%, #0f2d73 100%);
        }

        * {
            box-sizing: border-box;
        }

        body.invoice-page {
            margin: 0;
            padding: 24px;
            background: radial-gradient(980px 420px at -15% -10%, rgba(47, 95, 245, 0.14), transparent 60%), var(--invoice-bg);
            color: var(--invoice-text);
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-shell {
            width: min(100%, 980px);
            margin: 0 auto;
        }

        .invoice-header {
            border: 1px solid var(--invoice-border);
            border-radius: var(--invoice-radius-xl);
            padding: 24px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(220px, 280px);
            gap: 16px;
            background: var(--invoice-brand-gradient);
            color: #f8fbff;
            box-shadow: var(--invoice-shadow);
        }

        .brand-logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: 1px solid #bfd0ff;
            background: #ffffff;
            padding: 10px 14px;
            margin-bottom: 10px;
            box-shadow: 0 8px 18px rgba(3, 10, 32, 0.24);
        }

        .brand-logo {
            display: block;
            width: auto;
            height: 34px;
            filter: none;
        }

        .invoice-title {
            margin: 6px 0 0;
            font-size: clamp(30px, 4.2vw, 38px);
            line-height: 1.06;
            color: #fff;
        }

        .invoice-subtitle {
            margin: 8px 0 0;
            font-size: 14px;
            line-height: 1.55;
            color: #dbe7ff;
        }

        .header-right {
            border: 1px solid #4363c8;
            border-radius: 14px;
            padding: 14px;
            background: #1a2f74;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-self: flex-start;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .status-paid {
            background: var(--invoice-success-bg);
            color: var(--invoice-success-text);
        }

        .status-pending {
            background: var(--invoice-warning-bg);
            color: var(--invoice-warning-text);
        }

        .status-danger {
            background: var(--invoice-danger-bg);
            color: var(--invoice-danger-text);
        }

        .status-damage {
            background: #fce7f3;
            color: #9d174d;
        }

        .header-total-label {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #dbe7ff;
        }

        .header-total-amount {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
            font-weight: 700;
            color: #fff;
            font-variant-numeric: tabular-nums;
        }

        .invoice-grid {
            margin-top: 16px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 14px;
        }

        .stack {
            display: grid;
            gap: 14px;
        }

        .invoice-card {
            border: 1px solid var(--invoice-border);
            border-radius: var(--invoice-radius-lg);
            background: var(--invoice-surface);
            padding: 16px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .card-title {
            margin: 0;
            color: var(--invoice-heading);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .meta-list {
            margin-top: 10px;
            display: grid;
            gap: 8px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
        }

        .meta-row span:first-child {
            color: var(--invoice-muted);
        }

        .meta-row span:last-child {
            color: var(--invoice-text);
            font-weight: 600;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .person-name {
            margin-top: 10px;
            font-size: 20px;
            line-height: 1.2;
            font-weight: 700;
            color: var(--invoice-heading);
        }

        .person-lines {
            margin-top: 8px;
            display: grid;
            gap: 4px;
            color: var(--invoice-muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .rental-summary-value {
            margin-top: 9px;
            font-size: 15px;
            line-height: 1.4;
            color: var(--invoice-text);
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .more-details {
            margin-top: 10px;
            border-top: 1px dashed var(--invoice-border);
            padding-top: 10px;
        }

        .more-details > summary {
            cursor: pointer;
            color: var(--invoice-primary);
            font-size: 12px;
            font-weight: 600;
            list-style: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .more-details > summary::-webkit-details-marker {
            display: none;
        }

        .more-details > summary::before {
            content: '+';
            font-weight: 700;
        }

        .more-details[open] > summary::before {
            content: '−';
        }

        .items-card {
            margin-top: 16px;
        }

        .section-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 8px;
            align-items: baseline;
        }

        .section-note {
            color: var(--invoice-muted);
            font-size: 12px;
        }

        .table-wrap {
            margin-top: 12px;
            border: 1px solid var(--invoice-border);
            border-radius: 12px;
            overflow: auto;
            background: var(--invoice-surface-soft);
        }

        .items-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }

        .items-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: var(--invoice-primary-soft);
            border-bottom: 1px solid var(--invoice-border);
            color: var(--invoice-heading);
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            font-weight: 700;
            padding: 10px 12px;
            text-align: left;
        }

        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--invoice-border);
            vertical-align: top;
            color: var(--invoice-text);
            font-size: 13px;
        }

        .items-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.5);
        }

        html[data-theme-resolved='dark'] .items-table tbody tr:nth-child(even),
        html.dark .items-table tbody tr:nth-child(even) {
            background: rgba(7, 14, 30, 0.22);
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .item-title {
            margin: 0;
            color: var(--invoice-heading);
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-sub {
            margin-top: 3px;
            color: var(--invoice-muted);
            font-size: 12px;
        }

        .period-main {
            color: var(--invoice-text);
            font-size: 13px;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .period-sub {
            margin-top: 2px;
            color: var(--invoice-muted);
            font-size: 12px;
        }

        .num {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .totals-card {
            margin: 12px 0 0 auto;
            width: min(100%, 360px);
            border: 1px solid var(--invoice-border);
            border-radius: 12px;
            background: var(--invoice-surface);
            overflow: hidden;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--invoice-border);
            font-size: 13px;
            color: var(--invoice-text);
            font-variant-numeric: tabular-nums;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .totals-table td:first-child {
            color: var(--invoice-muted);
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .totals-table .grand-row td {
            background: var(--invoice-primary-soft);
            color: var(--invoice-primary-strong);
            font-size: 15px;
            font-weight: 700;
        }

        .terms-card {
            margin-top: 14px;
        }

        .terms-details > summary {
            cursor: pointer;
            list-style: none;
            color: var(--invoice-heading);
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .terms-details > summary::-webkit-details-marker {
            display: none;
        }

        .terms-details > summary::before {
            content: '▸';
            color: var(--invoice-primary);
            transition: transform .2s ease;
        }

        .terms-details[open] > summary::before {
            transform: rotate(90deg);
        }

        .terms-body {
            margin-top: 8px;
            color: var(--invoice-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .terms-body ul {
            margin: 0;
            padding-left: 18px;
        }

        .invoice-foot {
            margin-top: 12px;
            border-top: 1px solid var(--invoice-border);
            padding-top: 12px;
            color: var(--invoice-muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .actions {
            width: min(100%, 980px);
            margin: 14px auto 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        html.invoice-embedded .actions {
            display: none;
        }

        html.invoice-embedded .pickup-guide-overlay,
        html.invoice-embedded .toast {
            display: none !important;
        }

        .action-btn {
            border: 1px solid var(--invoice-border);
            background: var(--invoice-surface);
            color: var(--invoice-text);
            border-radius: 12px;
            padding: 9px 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
        }

        .action-btn.primary {
            border-color: transparent;
            background: var(--invoice-primary);
            color: #fff;
        }

        .action-btn:hover {
            border-color: color-mix(in oklab, var(--invoice-primary) 42%, var(--invoice-border));
            color: var(--invoice-primary-strong);
        }

        .action-btn.primary:hover {
            background: var(--invoice-primary-strong);
            color: #fff;
        }

        .action-btn[aria-disabled='true'] {
            opacity: .55;
            pointer-events: none;
            cursor: not-allowed;
        }

        .toast {
            position: fixed;
            right: 18px;
            bottom: 18px;
            border-radius: 10px;
            background: #0f172a;
            color: #fff;
            font-size: 12px;
            padding: 10px 14px;
            opacity: 0;
            transform: translateY(8px);
            pointer-events: none;
            transition: .2s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .pickup-guide-overlay {
            position: fixed;
            inset: 0;
            z-index: 120;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 14px;
            background: rgba(2, 6, 23, 0.62);
        }

        .pickup-guide-overlay.is-visible {
            display: flex;
        }

        .pickup-guide-card {
            width: min(100%, 620px);
            border: 1px solid var(--invoice-border);
            border-radius: 16px;
            background: var(--invoice-surface);
            box-shadow: var(--invoice-shadow);
            overflow: hidden;
        }

        .pickup-guide-head {
            padding: 16px 18px;
            background: var(--invoice-primary-soft);
            border-bottom: 1px solid var(--invoice-border);
        }

        .pickup-guide-title {
            margin: 0;
            color: var(--invoice-heading);
            font-size: 18px;
            font-weight: 700;
        }

        .pickup-guide-subtitle {
            margin: 6px 0 0;
            color: var(--invoice-muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .pickup-guide-body {
            padding: 16px 18px;
            display: grid;
            gap: 10px;
        }

        .pickup-guide-item {
            border: 1px solid var(--invoice-border);
            border-radius: 12px;
            background: var(--invoice-surface-soft);
            padding: 11px 12px;
        }

        .pickup-guide-item strong {
            display: block;
            color: var(--invoice-heading);
            font-size: 13px;
            margin-bottom: 4px;
        }

        .pickup-guide-item p {
            margin: 0;
            color: var(--invoice-text);
            font-size: 13px;
            line-height: 1.55;
        }

        .pickup-guide-item .receipt-code {
            margin-top: 6px;
            font-weight: 700;
            color: var(--invoice-primary-strong);
            font-variant-numeric: tabular-nums;
        }

        .pickup-guide-item .pickup-name {
            margin-top: 4px;
            color: var(--invoice-muted);
            font-size: 12px;
        }

        .pickup-guide-actions {
            padding: 0 18px 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .pickup-guide-close {
            border: 0;
            border-radius: 10px;
            background: var(--invoice-primary);
            color: #fff;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        .pickup-guide-close:hover {
            background: var(--invoice-primary-strong);
        }

        @media (max-width: 920px) {
            body.invoice-page {
                padding: 14px;
            }

            .invoice-header {
                grid-template-columns: 1fr;
            }

            .invoice-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .invoice-shell {
                width: 100%;
            }

            .invoice-header,
            .invoice-card {
                padding: 14px;
            }

            .header-total-amount {
                font-size: 24px;
            }

            .actions {
                justify-content: stretch;
            }

            .action-btn {
                flex: 1 1 calc(50% - 8px);
                text-align: center;
            }

            .totals-card {
                width: 100%;
            }
        }

        @media print {
            @page {
                size: auto;
                margin: 10mm;
            }

            :root,
            html.dark,
            html[data-theme-resolved='dark'] {
                --invoice-bg: #ffffff;
                --invoice-surface: #ffffff;
                --invoice-surface-soft: #ffffff;
                --invoice-border: #d6deed;
                --invoice-text: #0f172a;
                --invoice-muted: #64748b;
                --invoice-heading: #0f172a;
                --invoice-primary: #1d4ed8;
                --invoice-primary-strong: #1d4ed8;
                --invoice-primary-soft: #eef3ff;
                --invoice-shadow: none;
            }

            body.invoice-page {
                background: #fff !important;
                padding: 0;
            }

            .invoice-shell {
                width: 100%;
            }

            .invoice-header,
            .invoice-card,
            .totals-card {
                box-shadow: none;
            }

            .actions,
            .toast,
            .pickup-guide-overlay {
                display: none !important;
            }

            .table-wrap {
                overflow: visible;
            }

            .items-table th {
                position: static;
            }

            .terms-details > summary::before {
                display: none;
            }

            .terms-details > summary {
                cursor: default;
            }

            .terms-body {
                display: block !important;
            }
        }
    </style>
</head>
<body class="invoice-page">
    @php
        $locale = app()->getLocale();
        $isIndonesia = $locale === 'id';

        $formatCurrency = static function ($value) use ($isIndonesia) {
            $amount = (int) $value;

            if ($isIndonesia) {
                return 'Rp ' . number_format($amount, 0, ',', '.');
            }

            return 'IDR ' . number_format($amount, 0, '.', ',');
        };

        $formatDate = static function ($value, bool $withTime = true) use ($locale, $isIndonesia) {
            if (! $value) {
                return '-';
            }

            $date = $value instanceof \Carbon\CarbonInterface
                ? $value->copy()
                : \Carbon\Carbon::parse($value);
            $date->locale($locale);

            if ($withTime) {
                return $isIndonesia ? $date->translatedFormat('d M Y H:i') : $date->translatedFormat('M d, Y H:i');
            }

            return $isIndonesia ? $date->translatedFormat('d M Y') : $date->translatedFormat('M d, Y');
        };

        $profile = $order->user->profile;
        $invoiceId = $order->order_number ?: ('ORD-' . $order->id);
        $orderRouteKey = (string) ($order->order_number ?: $order->midtrans_order_id ?: '');
        $signedPdfUrl = $orderRouteKey !== ''
            ? \Illuminate\Support\Facades\URL::temporarySignedRoute('account.orders.receipt.pdf', now()->addMinutes(30), ['order' => $orderRouteKey])
            : null;
        $orderReference = trim((string) ($order->midtrans_order_id ?: $invoiceId));
        $showSeparateOrderReference = $orderReference !== '' && $orderReference !== $invoiceId;

        $issuedAt = $order->paid_at ?: $order->updated_at;
        $printedAt = $generatedAt ?? now();

        $paymentPayload = [];
        if (! empty($order->payment?->payload_json)) {
            $decoded = json_decode((string) $order->payment->payload_json, true);
            if (is_array($decoded)) {
                $paymentPayload = $decoded;
            }
        }

        $paymentType = (string) ($order->payment?->payment_type ?: data_get($paymentPayload, 'payment_type', 'snap'));
        $referenceNumber = data_get($paymentPayload, 'va_numbers.0.va_number')
            ?: data_get($paymentPayload, 'permata_va_number')
            ?: data_get($paymentPayload, 'bill_key')
            ?: data_get($paymentPayload, 'transaction_id');
        $bankName = data_get($paymentPayload, 'va_numbers.0.bank')
            ?: data_get($paymentPayload, 'bank')
            ?: 'BCA';

        $paymentMethodLabel = match ($paymentType) {
            'bank_transfer' => __('ui.invoice.methods.virtual_account'),
            'qris' => __('ui.invoice.methods.qris'),
            'gopay' => __('ui.invoice.methods.gopay'),
            'shopeepay' => __('ui.invoice.methods.shopeepay'),
            'credit_card' => __('ui.invoice.methods.credit_card'),
            default => strtoupper($paymentType),
        };

        $subtotal = (int) ($order->total_amount ?? 0);
        $penalty = $order->resolvePenaltyAmount();
        $shipping = max((int) ($order->shipping_amount ?? 0), 0);
        $discount = max((int) ($order->discount_amount ?? data_get($paymentPayload, 'discount_amount', 0)), 0);
        $tax = (int) round($subtotal * 0.11);
        $grandTotal = max($subtotal + $tax + $shipping + $penalty - $discount, 0);

        $statusRaw = strtolower((string) ($order->status_pembayaran ?? 'pending'));
        $statusConfig = match ($statusRaw) {
            'paid' => ['label' => __('ui.invoice.status.paid'), 'class' => 'status-paid'],
            'pending' => ['label' => __('ui.invoice.status.pending'), 'class' => 'status-pending'],
            'expired' => ['label' => __('ui.invoice.status.expired'), 'class' => 'status-danger'],
            'failed', 'deny', 'cancel' => ['label' => __('ui.invoice.status.failed'), 'class' => 'status-danger'],
            'refunded' => ['label' => __('ui.invoice.status.refunded'), 'class' => 'status-danger'],
            default => ['label' => strtoupper($statusRaw), 'class' => 'status-pending'],
        };

        if ($penalty > 0 && $statusRaw === 'paid') {
            $statusConfig = ['label' => __('ui.invoice.status.damage_invoice'), 'class' => 'status-damage'];
        }

        $rentalStart = $order->rental_start_date;
        $rentalEnd = $order->rental_end_date;
        $rentalDays = ($rentalStart && $rentalEnd)
            ? max($rentalStart->diffInDays($rentalEnd) + 1, 1)
            : 0;

        $totalsRows = [
            ['label' => __('ui.invoice.totals.subtotal'), 'amount' => $subtotal, 'always' => true],
            ['label' => __('ui.invoice.totals.tax'), 'amount' => $tax, 'always' => true],
            ['label' => __('ui.invoice.totals.shipping'), 'amount' => $shipping, 'always' => false],
            ['label' => __('ui.invoice.totals.penalty'), 'amount' => $penalty, 'always' => false],
            ['label' => __('ui.invoice.totals.discount'), 'amount' => $discount, 'always' => false, 'negative' => true],
        ];

        $visibleTotalsRows = collect($totalsRows)
            ->filter(fn ($row) => ! empty($row['always']) || ((int) ($row['amount'] ?? 0) > 0))
            ->values();

        $terms = [
            __('ui.invoice.terms.late'),
            __('ui.invoice.terms.damage'),
            __('ui.invoice.terms.loss'),
            __('ui.invoice.terms.final'),
        ];

        $contactPhone = (string) setting('contact_whatsapp', '+62 812-3456-7890');
        $contactEmail = (string) setting('contact_email', 'hello@manakerental.id');
        $footerAddressRaw = (string) setting('footer_address', 'Jl. Sutera Vision No. 12, Jakarta Selatan');
        $footerAddress = \Illuminate\Support\Str::of(strip_tags($footerAddressRaw))
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->limit(100)
            ->value();
        $pickupAddressRaw = (string) setting('footer.address', $footerAddressRaw);
        $pickupAddressLines = collect(preg_split('/\R+/', trim((string) strip_tags($pickupAddressRaw))))
            ->map(static fn ($line) => trim((string) $line))
            ->filter()
            ->values();
        if ($pickupAddressLines->isEmpty() && $footerAddress !== '') {
            $pickupAddressLines = collect([$footerAddress]);
        }
        $pickupRecipientName = (string) ($profile?->full_name ?? $order->user->name ?? '-');
        $pickupGuideStorageKey = 'manake.invoice_pickup_guide_seen.' . md5((string) $invoiceId);
    @endphp

    <main class="invoice-shell">
        <header class="invoice-header" aria-label="Invoice Header">
            <div>
                <span class="brand-logo-wrap">
                    <img src="{{ site_asset('manake-logo-blue.png') }}" alt="Manake" class="brand-logo">
                </span>
                <h1 class="invoice-title">{{ __('ui.invoice.title') }}</h1>
                <p class="invoice-subtitle">{{ __('ui.invoice.subtitle') }}</p>
            </div>
            <aside class="header-right" aria-label="Invoice Summary Header">
                <span class="status-badge {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                <p class="header-total-label">{{ __('ui.invoice.header_total') }}</p>
                <p class="header-total-amount">{{ $formatCurrency($grandTotal) }}</p>
            </aside>
        </header>

        <section class="invoice-grid" aria-label="Invoice Details">
            <div class="stack">
                <article class="invoice-card">
                    <h2 class="card-title">{{ __('ui.invoice.sections.billed_to') }}</h2>
                    <p class="person-name">{{ $profile?->full_name ?? $order->user->name ?? '-' }}</p>
                    <div class="person-lines">
                        <span>{{ $order->user->email ?? '-' }}</span>
                        <span>{{ $profile?->phone ?: ($order->user->phone ?? '-') }}</span>
                        <span>{{ $profile?->address_text ?: ($order->user->address ?? '-') }}</span>
                    </div>
                </article>

                <article class="invoice-card">
                    <h2 class="card-title">{{ __('ui.invoice.sections.rental_summary') }}</h2>
                    <div class="meta-list">
                        <div class="meta-row">
                            <span>{{ __('ui.invoice.meta.rental_period') }}</span>
                            <span>
                                @if ($rentalStart && $rentalEnd)
                                    {{ $formatDate($rentalStart, false) }} - {{ $formatDate($rentalEnd, false) }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="meta-row">
                            <span>{{ __('ui.invoice.meta.rental_days') }}</span>
                            <span>{{ $rentalDays > 0 ? __('ui.invoice.days_count', ['count' => $rentalDays]) : '-' }}</span>
                        </div>
                    </div>
                </article>
            </div>

            <div class="stack">
                <article class="invoice-card">
                    <h2 class="card-title">{{ __('ui.invoice.sections.invoice_meta') }}</h2>
                    <div class="meta-list">
                        @if ($showSeparateOrderReference)
                            <div class="meta-row">
                                <span>{{ __('ui.invoice.meta.invoice_id') }}</span>
                                <span>{{ $invoiceId }}</span>
                            </div>
                            <div class="meta-row">
                                <span>{{ __('ui.invoice.meta.order_id') }}</span>
                                <span>{{ $orderReference }}</span>
                            </div>
                        @else
                            <div class="meta-row">
                                <span>{{ __('ui.invoice.meta.invoice_order_id') }}</span>
                                <span>{{ $invoiceId }}</span>
                            </div>
                        @endif
                        <div class="meta-row">
                            <span>{{ __('ui.invoice.meta.issued_at') }}</span>
                            <span>{{ $formatDate($issuedAt) }}</span>
                        </div>
                        @if ($order->paid_at)
                            <div class="meta-row">
                                <span>{{ __('ui.invoice.meta.paid_at') }}</span>
                                <span>{{ $formatDate($order->paid_at) }}</span>
                            </div>
                        @endif
                    </div>

                    <details class="more-details">
                        <summary>{{ __('ui.invoice.sections.more_details') }}</summary>
                        <div class="meta-list" style="margin-top: 8px;">
                            <div class="meta-row">
                                <span>{{ __('ui.invoice.meta.printed_at') }}</span>
                                <span>{{ $formatDate($printedAt) }}</span>
                            </div>
                        </div>
                    </details>
                </article>

                <article class="invoice-card">
                    <h2 class="card-title">{{ __('ui.invoice.sections.payment_details') }}</h2>
                    <div class="meta-list">
                        <div class="meta-row">
                            <span>{{ __('ui.invoice.meta.method') }}</span>
                            <span>{{ $paymentMethodLabel }}</span>
                        </div>
                        <div class="meta-row">
                            <span>{{ __('ui.invoice.meta.bank') }}</span>
                            <span>{{ strtoupper((string) $bankName) }}</span>
                        </div>
                        <div class="meta-row">
                            <span>{{ __('ui.invoice.meta.reference') }}</span>
                            <span>{{ $referenceNumber ?: '-' }}</span>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section class="invoice-card items-card" aria-label="{{ __('ui.invoice.sections.items') }}">
            <div class="section-header">
                <h2 class="card-title">{{ __('ui.invoice.sections.items') }}</h2>
                <span class="section-note">{{ $order->items->count() }} item</span>
            </div>

            <div class="table-wrap" role="region" aria-label="{{ __('ui.invoice.sections.items') }}" tabindex="0">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.invoice.table.item') }}</th>
                            <th>{{ __('ui.invoice.table.period') }}</th>
                            <th class="num">{{ __('ui.invoice.table.qty') }}</th>
                            <th class="num">{{ __('ui.invoice.table.rate') }}</th>
                            <th class="num">{{ __('ui.invoice.table.line_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            @php
                                $itemStart = $item->rental_start_date ? \Carbon\Carbon::parse($item->rental_start_date) : $rentalStart;
                                $itemEnd = $item->rental_end_date ? \Carbon\Carbon::parse($item->rental_end_date) : $rentalEnd;
                                $itemDays = max((int) ($item->rental_days ?? (($itemStart && $itemEnd) ? $itemStart->diffInDays($itemEnd) + 1 : 1)), 1);
                                $itemPeriod = ($itemStart && $itemEnd)
                                    ? ($formatDate($itemStart, false) . ' - ' . $formatDate($itemEnd, false))
                                    : '-';
                                $lineTotal = (int) ($item->subtotal ?? ((int) ($item->price ?? 0) * (int) ($item->qty ?? 1) * $itemDays));
                            @endphp
                            <tr>
                                <td>
                                    <p class="item-title">{{ $item->equipment->name ?? 'Alat' }}</p>
                                    <p class="item-sub">{{ $item->equipment->category->name ?? __('ui.cart.gear_generic') }}</p>
                                </td>
                                <td>
                                    <p class="period-main">{{ $itemPeriod }}</p>
                                    <p class="period-sub">{{ __('ui.invoice.days_count', ['count' => $itemDays]) }}</p>
                                </td>
                                <td class="num">{{ (int) ($item->qty ?? 1) }}</td>
                                <td class="num">{{ $formatCurrency((int) ($item->price ?? 0)) }} {{ __('ui.invoice.per_day_short') }}</td>
                                <td class="num">{{ $formatCurrency($lineTotal) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; color: var(--invoice-muted);">{{ __('ui.invoice.table.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="totals-card" aria-label="{{ __('ui.invoice.sections.totals') }}">
                <table class="totals-table">
                    @include('account.orders.partials.totals-rows', ['visibleTotalsRows' => $visibleTotalsRows, 'formatCurrency' => $formatCurrency, 'grandTotal' => $grandTotal])
                </table>
            </div>
        </section>

        <section class="invoice-card terms-card">
            <details class="terms-details">
                <summary>{{ __('ui.invoice.sections.terms') }}</summary>
                <div class="terms-body">
                    @include('account.orders.partials.terms-list', ['terms' => $terms])
                </div>
            </details>

            <div class="invoice-foot">
                <strong>Manake</strong> • {{ $contactPhone }} • {{ $contactEmail }} • {{ $footerAddress }}<br>
                {{ __('ui.invoice.auto_note') }}
            </div>
        </section>
    </main>

    <div
        id="pickup-guide-modal"
        class="pickup-guide-overlay"
        data-storage-key="{{ $pickupGuideStorageKey }}"
        data-enabled="{{ $statusRaw === 'paid' ? '1' : '0' }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="pickup-guide-title"
    >
        <div class="pickup-guide-card">
            <div class="pickup-guide-head">
                <h2 id="pickup-guide-title" class="pickup-guide-title">{{ __('ui.invoice.pickup_popup.title') }}</h2>
                <p class="pickup-guide-subtitle">{{ __('ui.invoice.pickup_popup.subtitle') }}</p>
            </div>
            <div class="pickup-guide-body">
                <div class="pickup-guide-item">
                    <strong>{{ __('ui.invoice.pickup_popup.where_title') }}</strong>
                    <p>
                        @foreach ($pickupAddressLines as $line)
                            {{ $line }}@if (! $loop->last)<br>@endif
                        @endforeach
                    </p>
                </div>
                <div class="pickup-guide-item">
                    <strong>{{ __('ui.invoice.pickup_popup.receipt_title') }}</strong>
                    <p>{{ __('ui.invoice.pickup_popup.receipt_body') }}</p>
                    <p class="receipt-code">{{ __('ui.invoice.pickup_popup.receipt_label') }}: {{ $invoiceId }}</p>
                    <p class="pickup-name">{{ $pickupRecipientName }}</p>
                </div>
                <div class="pickup-guide-item">
                    <strong>{{ __('ui.invoice.pickup_popup.delegate_title') }}</strong>
                    <p>{{ __('ui.invoice.pickup_popup.delegate_body') }}</p>
                </div>
            </div>
            <div class="pickup-guide-actions">
                <button type="button" class="pickup-guide-close" data-close-pickup-guide>
                    {{ __('ui.invoice.pickup_popup.close_button') }}
                </button>
            </div>
        </div>
    </div>

    <div class="actions" aria-label="Invoice Actions">
        <a href="{{ route('booking.history') }}" class="action-btn">{{ __('ui.orders.back_to_history') }}</a>
        <a href="{{ $signedPdfUrl ?: '#' }}" class="action-btn" @if (! $signedPdfUrl) aria-disabled="true" @endif>{{ __('ui.invoice.actions.download_pdf') }}</a>
        <button type="button" class="action-btn" id="share-invoice-btn">{{ __('ui.invoice.actions.share') }}</button>
    </div>

    <div class="toast" id="invoice-toast"></div>

    <script>
        (function () {
            const toast = document.getElementById('invoice-toast');
            const shareButton = document.getElementById('share-invoice-btn');
            const pickupGuideModal = document.getElementById('pickup-guide-modal');
            const invoiceId = @json($invoiceId);

            const showToast = (message) => {
                if (!toast) return;
                toast.textContent = message;
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 1600);
            };

            shareButton?.addEventListener('click', async () => {
                const shareData = {
                    title: `${@json(__('ui.invoice.title'))} ${invoiceId}`,
                    text: `${@json(__('ui.invoice.title'))} Manake ${invoiceId}`,
                    url: window.location.href,
                };

                try {
                    if (navigator.share) {
                        await navigator.share(shareData);
                        showToast(@json(__('ui.invoice.toast.shared')));
                        return;
                    }

                    await navigator.clipboard.writeText(window.location.href);
                    showToast(@json(__('ui.invoice.toast.copied')));
                } catch (error) {
                    showToast(@json(__('ui.invoice.toast.failed')));
                }
            });

            if (pickupGuideModal) {
                const isEnabled = pickupGuideModal.dataset.enabled === '1';
                const storageKey = pickupGuideModal.dataset.storageKey || '';
                const closeButtons = pickupGuideModal.querySelectorAll('[data-close-pickup-guide]');

                const closeGuide = () => {
                    pickupGuideModal.classList.remove('is-visible');
                    document.body.style.overflow = '';

                    if (storageKey) {
                        try {
                            localStorage.setItem(storageKey, '1');
                        } catch (error) {
                            // ignore storage failures
                        }
                    }
                };

                if (isEnabled) {
                    let shouldOpen = true;

                    if (storageKey) {
                        try {
                            shouldOpen = localStorage.getItem(storageKey) !== '1';
                        } catch (error) {
                            shouldOpen = true;
                        }
                    }

                    if (shouldOpen) {
                        window.setTimeout(() => {
                            pickupGuideModal.classList.add('is-visible');
                            document.body.style.overflow = 'hidden';
                        }, 350);
                    }
                }

                closeButtons.forEach((button) => {
                    button.addEventListener('click', closeGuide);
                });

                pickupGuideModal.addEventListener('click', (event) => {
                    if (event.target === pickupGuideModal) {
                        closeGuide();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && pickupGuideModal.classList.contains('is-visible')) {
                        closeGuide();
                    }
                });
            }

        })();
    </script>
</body>
</html>
