<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran</title>
    @php
        $snapSrc = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $snapSrc }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
</head>
<body>

<h2>Pembayaran</h2>
<p>Referensi Pesanan: {{ $booking->reference }}</p>
<p>Total: Rp {{ number_format($amount) }}</p>

<button id="pay-button">Bayar Sekarang</button>

<script>
document.getElementById('pay-button').onclick = function () {
    snap.pay('{{ $snapToken }}');
};
</script>

</body>
</html>
