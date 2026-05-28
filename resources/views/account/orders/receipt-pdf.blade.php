<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('ui.invoice.title') }} {{ $order->order_number ?? ('ORD-' . $order->id) }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            color: #171717;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            background: #F5F4ED; /* premium warm ivory background */
        }

        .sheet {
            width: 100%;
            height: 100%;
        }

        /* DomPDF safe full-page layout */
        .page-table {
            width: 100%;
            min-height: 270mm;
            border-collapse: collapse;
        }

        .page-content {
            vertical-align: top;
            padding-bottom: 15px;
        }

        .page-footer {
            vertical-align: bottom;
            height: 24mm;
        }

        /* Top Header Horizontal Bar */
        .invoice-bar {
            width: 100%;
            border: 1px solid #3300FF;
            background: #FFFFFF;
            border-radius: 4px;
            margin-bottom: 12px;
        }

        .invoice-bar table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-bar td {
            padding: 5px 10px;
            vertical-align: middle;
            font-size: 10px;
            color: #3300FF;
            font-weight: bold;
            letter-spacing: 0.02em;
        }

        .invoice-bar .title {
            font-style: italic;
            font-size: 12px;
        }

        .invoice-bar .number {
            text-align: right;
        }

        /* Layout Tables */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .layout-table td {
            vertical-align: top;
        }

        .col-left {
            width: 55%;
            padding-right: 15px;
        }

        .col-right {
            width: 45%;
        }

        /* Text Styles */
        .section-label {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #555558;
            margin: 0 0 1px;
            font-weight: normal;
        }

        .section-value {
            font-size: 10px;
            font-weight: bold;
            color: #111113;
            margin: 0 0 8px;
        }

        .meta-list {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-list td {
            padding: 2.5px 0;
            font-size: 9px;
            vertical-align: middle;
        }

        .meta-list td.label {
            color: #555558;
            width: 38%;
            font-weight: normal;
        }

        .meta-list td.value {
            font-weight: bold;
            color: #111113;
            text-align: right;
        }

        /* Status Badge Pill */
        .status-badge {
            display: inline-block;
            padding: 1.5px 6px;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 99px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-paid {
            background: rgba(51, 0, 255, 0.1);
            color: #3300FF;
        }

        .badge-pending {
            background: rgba(212, 168, 67, 0.15);
            color: #D4A843;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #3300FF;
        }

        .items-table th {
            background: #3300FF;
            color: #FFFFFF;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .03em;
            padding: 5px 8px;
            text-align: left;
            border: 1px solid #3300FF;
        }

        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #D8D2FF; /* thin soft border */
            border-left: 1px solid #D8D2FF;
            border-right: 1px solid #D8D2FF;
            background: #FFFFFF;
            font-size: 9.5px;
            color: #111113;
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) td {
            background: #FCFAF5;
        }

        .item-name {
            font-weight: bold;
            margin: 0;
            font-size: 9.5px;
        }

        .item-category {
            font-size: 7.5px;
            color: #555558;
            margin: 1px 0 0;
        }

        .items-table th.num,
        .items-table td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        /* Bottom Section Grid */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .bottom-table td {
            vertical-align: top;
        }

        .bottom-left {
            width: 48%;
            padding-right: 12px;
        }

        .bottom-right {
            width: 52%;
        }

        /* Penalty block */
        .penalty-box {
            border: 1px solid rgba(51, 0, 255, 0.2);
            background: #FFFFFF;
            padding: 8px 10px;
            border-radius: 4px;
        }

        .penalty-title {
            color: #3300FF;
            font-weight: bold;
            font-size: 9px;
            margin: 0 0 4px;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .penalty-list {
            margin: 0;
            padding-left: 12px;
            font-size: 8.5px;
            color: #111113;
            line-height: 1.35;
        }

        .penalty-list li {
            margin-bottom: 3px;
        }

        /* Totals Box */
        .totals-box {
            border: 1px solid rgba(51, 0, 255, 0.2);
            background: #FFFFFF;
            border-radius: 4px;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #FCFAF5;
            font-size: 9px;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .totals-table .label {
            color: #555558;
            font-weight: normal;
        }

        .totals-table .value {
            text-align: right;
            font-weight: bold;
            color: #111113;
        }

        .totals-table .grand-row td {
            background: #3300FF;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 9.5px;
            padding: 5px 8px;
        }

        /* Payment details box */
        .payment-box {
            border: 1px solid rgba(51, 0, 255, 0.2);
            background: #FFFFFF;
            border-radius: 4px;
            padding: 6px 8px;
        }

        .payment-title {
            font-size: 8px;
            font-weight: bold;
            color: #3300FF;
            text-transform: uppercase;
            margin: 0 0 4px;
            border-bottom: 1px solid rgba(51, 0, 255, 0.15);
            padding-bottom: 2px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table td {
            padding: 2px 0;
            font-size: 8.5px;
            vertical-align: top;
        }

        .payment-table .label {
            color: #555558;
            width: 32%;
            font-weight: normal;
        }

        .payment-table .value {
            font-weight: bold;
            color: #111113;
            text-align: right;
        }

        /* Footer Section */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #3300FF;
            padding-top: 6px;
        }

        .footer-table td {
            vertical-align: middle;
        }

        .footer-logo-col {
            width: 45%;
        }

        .footer-logo {
            display: block;
            height: 54px;
            width: auto;
        }

        .footer-info-col {
            width: 55%;
            text-align: right;
            font-size: 7.5px;
            color: #555558;
            line-height: 1.3;
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

    // DomPDF Safe Multi-candidate Logo Scanning Block
    $logoCandidates = [
        public_path('manake-logo-blue.png'),
        public_path('Manake.png'),
        public_path('manake-logo.png'),
        public_path('MANAKE-FAV-M.png'),
    ];
    $logoPath = null;
    foreach ($logoCandidates as $cand) {
        if (file_exists($cand)) {
            $logoPath = $cand;
            break;
        }
    }
    $logoUrl = $logoPath
        ? ('data:image/png;base64,' . base64_encode(file_get_contents($logoPath)))
        : site_asset('manake-logo-blue.png');

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
    $statusLabelText = match ($statusRaw) {
        'paid' => __('ui.invoice.status.paid'),
        'pending' => __('ui.invoice.status.pending'),
        'expired' => __('ui.invoice.status.expired'),
        'failed', 'deny', 'cancel' => __('ui.invoice.status.failed'),
        'refunded' => __('ui.invoice.status.refunded'),
        default => strtoupper($statusRaw),
    };

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

    $contactPhone = (string) setting('contact_whatsapp', '+62 812-3456-7890');
    $contactEmail = (string) setting('contact_email', 'hello@manakerental.id');
    $footerAddressRaw = (string) setting('footer_address', 'Jl. Sutera Vision No. 12, Jakarta Selatan');
    $footerAddress = \Illuminate\Support\Str::of(strip_tags($footerAddressRaw))
        ->replaceMatches('/\s+/', ' ')
        ->trim()
        ->limit(100)
        ->value();

    $businessName = (string) setting('business_name', setting('site_name', 'Manake Rental'));
    $businessPhone = $contactPhone ?: '-';
    $businessEmail = $contactEmail ?: '-';
    $businessAddress = $footerAddress;

    $shortRef = $referenceNumber
        ? (strlen($referenceNumber) > 22 ? substr($referenceNumber, 0, 18) . '...' : $referenceNumber)
        : '-';

    // Visual helper for Event Name / Film Title fallback
    $eventTitle = $order->notes ?: ($order->order_number ?: '-');
@endphp

<main class="sheet">
    <table class="page-table">
        <tr>
            <td class="page-content">
                <!-- Top Horizontal Bar -->
                <div class="invoice-bar">
                    <table>
                        <tr>
                            <td class="title">Invoice</td>
                            <td class="number">No. {{ $invoiceId }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Customer / Date Block -->
                <table class="layout-table">
                    <tr>
                        <td class="col-left">
                            <p class="section-label">Issued to:</p>
                            <p class="section-value">{{ $profile?->full_name ?? $order->user->name ?? '-' }}</p>
                            
                            <p class="section-label">Event Name/Film Title:</p>
                            <p class="section-value">{{ $eventTitle }}</p>

                            <p class="section-label">Address / Phone:</p>
                            <div class="line" style="font-size: 8.5px; color: #171717;">
                                {{ $profile?->address_text ?: ($order->user->address ?? '-') }}<br>
                                {{ $profile?->phone ?: ($order->user->phone ?? '-') }}
                            </div>
                        </td>
                        <td class="col-right">
                            <p class="section-label" style="text-align: right;">Date & Details:</p>
                            <table class="meta-list">
                                <tr>
                                    <td class="label">Date:</td>
                                    <td class="value">{{ $formatDate($issuedAt, false) }}</td>
                                </tr>
                                @if ($showSeparateOrderReference)
                                    <tr>
                                        <td class="label"><span>{{ __('ui.invoice.meta.invoice_id') }}</span>:</td>
                                        <td class="value">{{ $invoiceId }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label"><span>{{ __('ui.invoice.meta.order_id') }}</span>:</td>
                                        <td class="value">{{ $orderReference }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="label"><span>{{ __('ui.invoice.meta.invoice_order_id') }}</span>:</td>
                                        <td class="value">{{ $invoiceId }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="label">Status:</td>
                                    <td class="value">
                                        <span class="status-badge {{ $statusRaw === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                                            {{ $statusLabelText }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Rental Period:</td>
                                    <td class="value">
                                        @if ($rentalStart && $rentalEnd)
                                            <div style="margin-bottom: 2px;">{{ $formatDate($rentalStart, false) }} - {{ $formatDate($rentalEnd, false) }}</div>
                                            <span style="font-weight: normal; color: #555558; font-size: 8px;">{{ $rentalDays }} {{ $isIndonesia ? 'hari' : ($rentalDays > 1 ? 'days' : 'day') }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Item Table -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="num">Unit Price</th>
                            <th class="num">Qty</th>
                            <th class="num">Days</th>
                            <th class="num">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            @php
                                $itemStart = $item->rental_start_date ? \Carbon\Carbon::parse($item->rental_start_date) : $rentalStart;
                                $itemEnd = $item->rental_end_date ? \Carbon\Carbon::parse($item->rental_end_date) : $rentalEnd;
                                $itemDays = max((int) ($item->rental_days ?? (($itemStart && $itemEnd) ? $itemStart->diffInDays($itemEnd) + 1 : 1)), 1);
                                $lineTotal = (int) ($item->subtotal ?? ((int) ($item->price ?? 0) * (int) ($item->qty ?? 1) * $itemDays));
                            @endphp
                            <tr>
                                <td>
                                    <p class="item-name">{{ $item->equipment->name ?? 'Alat' }}</p>
                                    <p class="item-category">{{ $item->equipment->category->name ?? __('ui.cart.gear_generic') }}</p>
                                </td>
                                <td class="num">{{ $formatCurrency((int) ($item->price ?? 0)) }}</td>
                                <td class="num">{{ (int) ($item->qty ?? 1) }}</td>
                                <td class="num">{{ $itemDays }}</td>
                                <td class="num">{{ $formatCurrency($lineTotal) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; color:#555558;">{{ __('ui.invoice.table.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Lower Section Layout -->
                <table class="bottom-table">
                    <tr>
                        <td class="bottom-left">
                            <!-- Penalty Section -->
                            <div class="penalty-box">
                                <p class="penalty-title">Penalty</p>
                                <ol class="penalty-list">
                                    <li>Late returns
                                        <ul style="margin: 2px 0; padding-left: 12px; list-style-type: lower-alpha;">
                                            <li>3 hours (30%)</li>
                                            <li>6 hours (50%)</li>
                                            <li>9 hours (100%)</li>
                                        </ul>
                                    </li>
                                    <li>Unit damaged per item (50%)</li>
                                    <li>Unit lost per item (100%)</li>
                                </ol>
                            </div>
                        </td>
                        <td class="bottom-right">
                            <!-- Totals Table -->
                            <div class="totals-box">
                                <table class="totals-table">
                                    @foreach ($visibleTotalsRows as $row)
                                        <tr>
                                            <td class="label">{{ $row['label'] }}</td>
                                            <td class="value">
                                                {{ !empty($row['negative']) ? '-' : '' }}{{ $formatCurrency($row['amount']) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="grand-row">
                                        <td style="border-bottom-left-radius: 3px;">Total</td>
                                        <td class="value" style="color: #FFFFFF; border-bottom-right-radius: 3px; text-align: right;">{{ $formatCurrency($grandTotal) }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Payment Details Box -->
                            <div class="payment-box">
                                <p class="payment-title">Payment details</p>
                                <table class="payment-table">
                                    <tr>
                                        <td class="label">Method:</td>
                                        <td class="value">{{ $paymentMethodLabel }} ({{ strtoupper((string) $bankName) }})</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Ref. Number:</td>
                                        <td class="value">{{ $shortRef }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label" style="border-top: 1px dashed rgba(51, 0, 255, 0.2); padding-top: 4px; margin-top: 2px;">Account:</td>
                                        <td class="value" style="border-top: 1px dashed rgba(51, 0, 255, 0.2); padding-top: 4px; margin-top: 2px;">
                                            {{ $businessName }}<br>
                                            <span style="font-weight: normal; color: #555558;">{{ $businessPhone }}</span>
                                            @if ($businessEmail && $businessEmail !== '-')
                                                <br><span style="font-weight: normal; color: #555558; font-size: 8px;">{{ $businessEmail }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="page-footer">
                <!-- Footer Section -->
                <table class="footer-table">
                    <tr>
                        <td class="footer-logo-col">
                            <img src="{{ $logoUrl }}" alt="Manake Logo" class="footer-logo">
                        </td>
                        <td class="footer-info-col">
                            <strong>Manake Rental</strong><br>
                            WhatsApp: {{ $contactPhone }}<br>
                            Email: {{ $contactEmail }}<br>
                            Address: {{ $footerAddress }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</main>
</body>
</html>
