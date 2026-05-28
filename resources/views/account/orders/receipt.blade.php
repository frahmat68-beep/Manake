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
        * {
            box-sizing: border-box;
        }

        body.invoice-page {
            margin: 0;
            padding: 24px;
            background: #F7F6F0;
            color: #171717;
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-shell {
            width: min(100%, 920px);
            margin: 0 auto;
            background: #FFFFFF;
            border: 1px solid #3300FF;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(17, 17, 19, 0.05);
        }

        /* Top Header Horizontal Bar */
        .invoice-bar {
            width: 100%;
            border: 1.5px solid #3300FF;
            background: #FFFFFF;
            border-radius: 4px;
            margin-bottom: 24px;
        }

        .invoice-bar table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-bar td {
            padding: 8px 16px;
            vertical-align: middle;
            font-size: 13px;
            color: #3300FF;
            font-weight: bold;
        }

        .invoice-bar .title {
            font-style: italic;
            font-size: 15px;
        }

        .invoice-bar .number {
            text-align: right;
        }

        /* Layout Grid */
        .invoice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        /* Text Styles */
        .section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #555558;
            margin: 0 0 4px;
            font-weight: bold;
        }

        .section-value {
            font-size: 13px;
            font-weight: bold;
            color: #111113;
            margin: 0 0 12px;
        }

        .meta-list {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-list td {
            padding: 4px 0;
            font-size: 12px;
        }

        .meta-list td.label {
            color: #555558;
            width: 40%;
        }

        .meta-list td.value {
            font-weight: bold;
            color: #111113;
            text-align: right;
        }

        /* Items Table */
        .table-wrap {
            margin-bottom: 24px;
            border: 1px solid #3300FF;
            border-radius: 6px;
            overflow: hidden;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            background: #3300FF;
            color: #FFFFFF;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 10px 12px;
            text-align: left;
        }

        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #3300FF;
            background: #FFFFFF;
            font-size: 13px;
            color: #111113;
            vertical-align: top;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table tbody tr:nth-child(even) td {
            background: #F8F7F2;
        }

        .item-name {
            font-weight: bold;
            margin: 0;
        }

        .item-category {
            font-size: 10px;
            color: #555558;
            margin: 2px 0 0;
        }

        .items-table th.num,
        .items-table td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        /* Bottom Grid */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        /* Penalty block */
        .penalty-box {
            border: 1px solid #3300FF;
            background: #FFFFFF;
            padding: 14px 16px;
            border-radius: 6px;
            height: fit-content;
        }

        .penalty-title {
            color: #3300FF;
            font-weight: bold;
            font-size: 13px;
            margin: 0 0 8px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .penalty-list {
            margin: 0;
            padding-left: 18px;
            font-size: 12px;
            color: #111113;
            line-height: 1.5;
        }

        .penalty-list li {
            margin-bottom: 6px;
        }

        /* Totals Box */
        .totals-box {
            border: 1px solid #3300FF;
            background: #FFFFFF;
            border-radius: 6px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #F7F6F0;
            font-size: 12px;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .totals-table .label {
            color: #555558;
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
            font-size: 13px;
        }

        /* Payment details box */
        .payment-box {
            border: 1.5px solid #3300FF;
            background: #FFFFFF;
            border-radius: 6px;
            padding: 12px 14px;
        }

        .payment-title {
            font-size: 11px;
            font-weight: bold;
            color: #3300FF;
            text-transform: uppercase;
            margin: 0 0 8px;
            border-bottom: 1px solid #3300FF;
            padding-bottom: 4px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table td {
            padding: 4px 0;
            font-size: 12px;
        }

        .payment-table .label {
            color: #555558;
            width: 40%;
        }

        .payment-table .value {
            font-weight: bold;
            color: #111113;
            text-align: right;
        }

        /* Footer Section */
        .footer-section {
            margin-top: 36px;
            border-top: 1.5px solid #3300FF;
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-logo {
            display: block;
            height: 52px;
            width: auto;
        }

        .footer-info {
            text-align: right;
            font-size: 11px;
            color: #555558;
            line-height: 1.4;
        }

        .status-paid-text {
            color: #3300FF;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending-text {
            color: #D4A843;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Actions panel */
        .actions {
            width: min(100%, 920px);
            margin: 16px auto 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .action-btn {
            border: 1px solid #3300FF;
            background: #FFFFFF;
            color: #3300FF;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: #3300FF;
            color: #FFFFFF;
        }

        /* Embedded overrides */
        html.invoice-embedded body.invoice-page {
            padding: 0;
            background: #FFFFFF;
        }

        html.invoice-embedded .invoice-shell {
            border: none;
            box-shadow: none;
            padding: 10px;
        }

        html.invoice-embedded .actions {
            display: none !important;
        }

        /* Responsive Layouts */
        @media (max-width: 768px) {
            .invoice-shell {
                padding: 16px;
            }

            .invoice-grid,
            .bottom-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .footer-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .footer-info {
                text-align: left;
            }
        }

        /* Toast styling */
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
            z-index: 99999;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
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

        // Visual helper for Event Name / Film Title fallback
        $eventTitle = $order->notes ?: ($order->order_number ?: '-');
    @endphp

    <main class="invoice-shell">
        <!-- Top Horizontal Bar -->
        <header class="invoice-bar" aria-label="Invoice Header">
            <table>
                <tr>
                    <td class="title">Invoice</td>
                    <td class="number">No. {{ $invoiceId }}</td>
                </tr>
            </table>
        </header>

        <!-- Customer / Date Block -->
        <section class="invoice-grid" aria-label="Invoice Details">
            <div>
                <p class="section-label">Issued to:</p>
                <p class="section-value">{{ $profile?->full_name ?? $order->user->name ?? '-' }}</p>
                
                <p class="section-label">Event Name/Film Title:</p>
                <p class="section-value">{{ $eventTitle }}</p>

                <p class="section-label">Address / Phone:</p>
                <div class="line" style="font-size: 11px; color: #171717;">
                    {{ $profile?->address_text ?: ($order->user->address ?? '-') }}<br>
                    {{ $profile?->phone ?: ($order->user->phone ?? '-') }}
                </div>
            </div>
            <div>
                <p class="section-label" style="text-align: right;">Date & Details:</p>
                <table class="meta-list">
                    <tr>
                        <td class="label">Date:</td>
                        <td class="value">{{ $formatDate($issuedAt, false) }}</td>
                    </tr>
                    @if ($showSeparateOrderReference)
                        <tr>
                            <td class="label">Order Ref:</td>
                            <td class="value">{{ $orderReference }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value">
                            <span class="{{ $statusRaw === 'paid' ? 'status-paid-text' : 'status-pending-text' }}">
                                {{ $statusLabelText }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Rental Period:</td>
                        <td class="value">
                            @if ($rentalStart && $rentalEnd)
                                {{ $formatDate($rentalStart, false) }} - {{ $formatDate($rentalEnd, false) }} ({{ $rentalDays }} days)
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </section>

        <!-- Item Table -->
        <div class="table-wrap">
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
        </div>

        <!-- Lower Section Layout -->
        <section class="bottom-grid">
            <!-- Penalty Section -->
            <div class="penalty-box">
                <p class="penalty-title">Penalty</p>
                <ol class="penalty-list">
                    <li>Late returns
                        <ul style="margin: 2px 0; padding-left: 14px; list-style-type: lower-alpha;">
                            <li>3 hours (30%)</li>
                            <li>6 hours (50%)</li>
                            <li>9 hours (100%)</li>
                        </ul>
                    </li>
                    <li>Unit damaged per item (50%)</li>
                    <li>Unit lost per item (100%)</li>
                </ol>
            </div>

            <div>
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
                            <td style="border-bottom-left-radius: 4px;">Total</td>
                            <td class="value" style="color: #FFFFFF; border-bottom-right-radius: 4px; text-align: right;">{{ $formatCurrency($grandTotal) }}</td>
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
                            <td class="value">{{ $referenceNumber ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label" style="border-top: 1px dashed #3300FF; padding-top: 4px; margin-top: 2px;">Account:</td>
                            <td class="value" style="border-top: 1px dashed #3300FF; padding-top: 4px; margin-top: 2px;">
                                Fikri Mulya Rachmat<br>
                                0851 5664 9015
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </section>

        <!-- Footer Section -->
        <footer class="footer-section">
            <img src="{{ site_asset('manake-logo-blue.png') }}" alt="Manake Logo" class="footer-logo">
            <div class="footer-info">
                <strong>Manake Rental</strong><br>
                WhatsApp: {{ $contactPhone }}<br>
                Email: {{ $contactEmail }}<br>
                Address: {{ $footerAddress }}
            </div>
        </footer>
    </main>

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
        })();
    </script>
</body>
</html>
