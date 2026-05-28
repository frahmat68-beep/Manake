<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('ui.invoice.title') }} {{ $order->order_number ?? ('ORD-' . $order->id) }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #171717;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.8px;
            line-height: 1.45;
            background: #FFFFFF;
        }

        .sheet {
            width: 100%;
            margin: 0 auto;
        }

        .invoice-header {
            padding: 16px 18px;
            border: 1px solid #111113;
            border-bottom: 4px solid #D4A843;
            border-radius: 12px;
            background-color: #111113;
            color: #FFFFFF;
        }

        .layout {
            width: 100%;
            border-collapse: collapse;
        }

        .layout td {
            vertical-align: top;
        }

        .brand-logo-wrap {
            display: inline-block;
            border: 1px solid #D4A843;
            border-radius: 8px;
            background: #111113;
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        .brand-logo {
            display: block;
            height: 24px;
            width: auto;
            margin: 0;
        }

        .invoice-title {
            margin: 4px 0 0;
            font-size: 26px;
            color: #FFFFFF;
            line-height: 1.1;
            font-weight: 700;
        }

        .invoice-subtitle {
            margin: 6px 0 0;
            color: #A0A0A8;
            font-size: 10px;
        }

        .header-right {
            border: 1px solid #D4A843/30;
            border-radius: 8px;
            background: #0A0A0B;
            padding: 10px 12px;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .status-paid {
            background: #D4A843;
            color: #0A0A0B;
        }

        .status-pending {
            background: #F8F7F2;
            color: #D4A843;
            border: 1px solid #D4A843;
        }

        .status-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-damage {
            background: #fce7f3;
            color: #9d174d;
        }

        .summary-label {
            margin: 8px 0 2px;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #A0A0A8;
            font-weight: 700;
        }

        .summary-amount {
            margin: 0;
            color: #D4A843;
            font-size: 22px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .cards {
            margin-top: 12px;
        }

        .cards-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin: -10px;
        }

        .cards-grid td {
            width: 50%;
            border: 1px solid #E5E2DA;
            border-radius: 12px;
            padding: 12px;
            vertical-align: top;
            background: #F8F7F2;
        }

        .card-title {
            margin: 0 0 7px;
            color: #111113;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
            border-bottom: 1px solid #E5E2DA;
            padding-bottom: 4px;
        }

        .person-name {
            margin: 4px 0 0;
            color: #111113;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }

        .line {
            margin: 3px 0 0;
            color: #555558;
            font-size: 9.5px;
            line-height: 1.35;
        }

        .meta-row {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }

        .meta-row td {
            border: none;
            padding: 2px 0;
            font-size: 9.5px;
            vertical-align: top;
        }

        .meta-row td:first-child {
            color: #555558;
            width: 45%;
        }

        .meta-row td:last-child {
            color: #111113;
            font-weight: 700;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .items {
            margin-top: 14px;
            border: 1px solid #E5E2DA;
            border-radius: 12px;
            background: #FFFFFF;
            padding: 14px 16px;
        }

        .section-head {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .section-head td {
            vertical-align: bottom;
        }

        .section-note {
            text-align: right;
            color: #555558;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .section-title {
            margin: 0;
            color: #111113;
            font-size: 15px;
            font-weight: 700;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            table-layout: fixed;
            font-size: 9.5px;
            page-break-inside: auto;
            border: 1px solid #E5E2DA;
            border-radius: 8px;
            overflow: hidden;
            background: #FFFFFF;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table tfoot {
            display: table-row-group;
        }

        .items-table thead th {
            padding: 7px 8px;
            background: #111113;
            color: #D4A843;
            border-bottom: 2px solid #D4A843;
            text-transform: uppercase;
            letter-spacing: .07em;
            font-size: 8.5px;
            text-align: left;
        }

        .items-table tbody td {
            padding: 7px 8px;
            border-bottom: 1px solid #E5E2DA;
            vertical-align: top;
            color: #111113;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .items-table tbody tr:nth-child(even) {
            background: #F8F7F2;
        }

        .items-table th:nth-child(1),
        .items-table td:nth-child(1) { width: 32%; }
        .items-table th:nth-child(2),
        .items-table td:nth-child(2) { width: 26%; }
        .items-table th:nth-child(3),
        .items-table td:nth-child(3) { width: 10%; }
        .items-table th:nth-child(4),
        .items-table td:nth-child(4) { width: 14%; }
        .items-table th:nth-child(5),
        .items-table td:nth-child(5) { width: 18%; }

        .item-title {
            margin: 0;
            font-size: 10px;
            font-weight: 700;
            color: #111113;
            line-height: 1.35;
        }

        .item-sub {
            margin-top: 2px;
            font-size: 8.5px;
            color: #555558;
        }

        .period-sub {
            margin-top: 2px;
            font-size: 8.5px;
            color: #555558;
        }

        .num {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .totals-wrap {
            width: 46%;
            margin: 9px 0 0 auto;
            border: 1px solid #E5E2DA;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
            background: #FFFFFF;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .totals-table td {
            padding: 7px 9px;
            border-bottom: 1px solid #E5E2DA;
            font-variant-numeric: tabular-nums;
        }

        .totals-table td:first-child {
            color: #555558;
        }

        .totals-table td:last-child {
            text-align: right;
            color: #111113;
            font-weight: 700;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .totals-table .grand-row td {
            background: #111113;
            color: #D4A843;
            font-size: 11px;
            font-weight: 700;
        }

        .terms {
            margin-top: 14px;
            border: 1px solid #E5E2DA;
            border-radius: 12px;
            padding: 12px 16px;
            background: #F8F7F2;
        }

        .terms-title {
            margin: 0;
            color: #111113;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
        }

        .terms ul {
            margin: 6px 0 0;
            padding-left: 16px;
            color: #555558;
            font-size: 9px;
        }

        .terms li {
            margin-bottom: 3px;
        }

        .footer-mini {
            margin-top: 10px;
            border-top: 1px solid #E5E2DA;
            padding-top: 8px;
            color: #555558;
            font-size: 9px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
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
    $orderReference = trim((string) ($order->midtrans_order_id ?: $invoiceId));
    $showSeparateOrderReference = $orderReference !== '' && $orderReference !== $invoiceId;

    $issuedAt = $order->paid_at ?: $order->updated_at;
    $printedAt = $generatedAt ?? now();

    $logoPath = public_path('manake-logo-white.png');
    $logoUrl = file_exists($logoPath)
        ? ('data:image/png;base64,' . base64_encode(file_get_contents($logoPath)))
        : site_asset('manake-logo-white.png');

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
@endphp

<main class="sheet">
    <header class="invoice-header">
        <table class="layout">
            <tr>
                <td style="width: 64%; padding-right: 10px;">
                    <span class="brand-logo-wrap">
                        <img src="{{ $logoUrl }}" alt="Manake" class="brand-logo">
                    </span>
                    <h1 class="invoice-title">{{ __('ui.invoice.title') }}</h1>
                    <p class="invoice-subtitle">{{ __('ui.invoice.subtitle') }}</p>
                </td>
                <td style="width: 36%;">
                    <div class="header-right">
                        <span class="status-badge {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                        <p class="summary-label">{{ __('ui.invoice.header_total') }}</p>
                        <p class="summary-amount">{{ $formatCurrency($grandTotal) }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <div class="cards">
        <table class="cards-grid">
            <tr>
                <td>
                    <p class="card-title">{{ __('ui.invoice.sections.billed_to') }}</p>
                    <p class="person-name">{{ $profile?->full_name ?? $order->user->name ?? '-' }}</p>
                    <p class="line">{{ $order->user->email ?? '-' }}</p>
                    <p class="line">{{ $profile?->phone ?: ($order->user->phone ?? '-') }}</p>
                    <p class="line">{{ $profile?->address_text ?: ($order->user->address ?? '-') }}</p>
                </td>
                <td>
                    <p class="card-title">{{ __('ui.invoice.sections.rental_summary') }}</p>
                    <table class="meta-row">
                        <tr>
                            <td>{{ __('ui.invoice.meta.rental_period') }}</td>
                            <td>
                                @if ($rentalStart && $rentalEnd)
                                    {{ $formatDate($rentalStart, false) }} - {{ $formatDate($rentalEnd, false) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('ui.invoice.meta.rental_days') }}</td>
                            <td>{{ $rentalDays > 0 ? __('ui.invoice.days_count', ['count' => $rentalDays]) : '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="card-title">{{ __('ui.invoice.sections.invoice_meta') }}</p>
                    <table class="meta-row">
                        @if ($showSeparateOrderReference)
                            <tr>
                                <td>{{ __('ui.invoice.meta.invoice_id') }}</td>
                                <td>{{ $invoiceId }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('ui.invoice.meta.order_id') }}</td>
                                <td>{{ $orderReference }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>{{ __('ui.invoice.meta.invoice_order_id') }}</td>
                                <td>{{ $invoiceId }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>{{ __('ui.invoice.meta.issued_at') }}</td>
                            <td>{{ $formatDate($issuedAt) }}</td>
                        </tr>
                        @if ($order->paid_at)
                            <tr>
                                <td>{{ __('ui.invoice.meta.paid_at') }}</td>
                                <td>{{ $formatDate($order->paid_at) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>{{ __('ui.invoice.meta.printed_at') }}</td>
                            <td>{{ $formatDate($printedAt) }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <p class="card-title">{{ __('ui.invoice.sections.payment_details') }}</p>
                    <table class="meta-row">
                        <tr>
                            <td>{{ __('ui.invoice.meta.method') }}</td>
                            <td>{{ $paymentMethodLabel }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('ui.invoice.meta.bank') }}</td>
                            <td>{{ strtoupper((string) $bankName) }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('ui.invoice.meta.reference') }}</td>
                            <td>{{ $referenceNumber ?: '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="items">
        <table class="section-head">
            <tr>
                <td><h2 class="section-title">{{ __('ui.invoice.sections.items') }}</h2></td>
                <td class="section-note">{{ $order->items->count() }} item</td>
            </tr>
        </table>

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
                            <div>{{ $itemPeriod }}</div>
                            <div class="period-sub">{{ __('ui.invoice.days_count', ['count' => $itemDays]) }}</div>
                        </td>
                        <td class="num">{{ (int) ($item->qty ?? 1) }}</td>
                        <td class="num">{{ $formatCurrency((int) ($item->price ?? 0)) }} {{ __('ui.invoice.per_day_short') }}</td>
                        <td class="num">{{ $formatCurrency($lineTotal) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#555558;">{{ __('ui.invoice.table.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals-wrap">
            <table class="totals-table">
                @include('account.orders.partials.totals-rows', ['visibleTotalsRows' => $visibleTotalsRows, 'formatCurrency' => $formatCurrency, 'grandTotal' => $grandTotal])
            </table>
        </div>
    </div>

    <div class="terms">
        <p class="terms-title">{{ __('ui.invoice.sections.terms') }}</p>
        @include('account.orders.partials.terms-list', ['terms' => $terms])
        <p class="footer-mini">
            <strong>Manake</strong> • {{ $contactPhone }} • {{ $contactEmail }} • {{ $footerAddress }}<br>
            {{ __('ui.invoice.auto_note') }}
        </p>
    </div>
</main>
</body>
</html>
