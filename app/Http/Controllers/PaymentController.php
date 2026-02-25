<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\MidtransService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request, Order $order, MidtransService $midtrans, PricingService $pricing)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $order->midtrans_order_id) {
            $order->midtrans_order_id = $this->generateOrderNumber($order->id);
            $order->order_number = $order->midtrans_order_id;
            $order->save();
        }

        $order->loadMissing(['items.equipment', 'user.profile']);
        $pricingSummary = $pricing->calculateOrderTotals([], (int) ($order->total_amount ?? 0));

        $payment = Payment::firstOrNew([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_MIDTRANS_RENTAL,
        ]);

        $requiresNewToken = ! $payment->snap_token
            || in_array($payment->status, ['cancel', 'deny', 'expire', 'failure', 'failed', 'refunded', 'expired'], true);

        if ($requiresNewToken) {
            $snapToken = $midtrans->createSnapToken($order);
            $payment->snap_token = $snapToken;
            $payment->status = Order::PAYMENT_PENDING;
            $payment->transaction_status = 'pending';
            $payment->gross_amount = (int) ($pricingSummary['total'] ?? 0);
            $payment->midtrans_order_id = $order->midtrans_order_id;
            $payment->expired_at = null;
            $payment->payload_json = json_encode([
                'generated_at' => now()->toDateTimeString(),
                'subtotal' => (int) ($pricingSummary['subtotal'] ?? 0),
                'tax' => (int) ($pricingSummary['tax'] ?? 0),
                'total' => (int) ($pricingSummary['total'] ?? 0),
            ], JSON_UNESCAPED_UNICODE);
            $payment->save();

            $order->snap_token = $snapToken;
            $order->save();
        } elseif (! $order->snap_token && $payment->snap_token) {
            $order->snap_token = $payment->snap_token;
            $order->save();
        }

        return response()->json([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->midtrans_order_id,
            'snap_token' => $payment->snap_token,
            'payment_status' => $payment->status,
        ]);
    }

    public function createDamageFeeSnapToken(Request $request, Order $order, MidtransService $midtrans)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $damageFeeAmount = $order->resolvePenaltyAmount();
        if ($damageFeeAmount <= 0) {
            return response()->json([
                'message' => __('Tagihan tambahan belum tersedia.'),
            ], 422);
        }

        $damageEligibleStatuses = [
            Order::STATUS_RETURNED_OK,
            Order::STATUS_RETURNED_DAMAGED,
            Order::STATUS_RETURNED_LOST,
            Order::STATUS_OVERDUE_DAMAGE_INVOICE,
        ];

        if (! in_array((string) $order->status_pesanan, $damageEligibleStatuses, true)) {
            return response()->json([
                'message' => __('Tagihan tambahan belum dapat diproses pada status order saat ini.'),
            ], 422);
        }

        if (($order->status_pembayaran ?? Order::PAYMENT_PENDING) !== Order::PAYMENT_PAID) {
            return response()->json([
                'message' => __('Tagihan tambahan baru bisa dibayar setelah pembayaran sewa lunas.'),
            ], 422);
        }

        $latestDamagePayment = Payment::query()
            ->where('order_id', $order->id)
            ->where('provider', Payment::PROVIDER_MIDTRANS_DAMAGE)
            ->latest('id')
            ->first();

        if (($latestDamagePayment->status ?? null) === Order::PAYMENT_PAID) {
            return response()->json([
                'message' => __('Tagihan tambahan sudah lunas.'),
            ], 422);
        }

        $requiresNewToken = ! $latestDamagePayment
            || ! $latestDamagePayment->snap_token
            || ! $latestDamagePayment->midtrans_order_id
            || (int) ($latestDamagePayment->gross_amount ?? 0) !== $damageFeeAmount
            || in_array((string) $latestDamagePayment->status, ['cancel', 'deny', 'expire', 'failure', 'failed', 'refunded', 'expired'], true);

        $payment = $latestDamagePayment ?? new Payment([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_MIDTRANS_DAMAGE,
        ]);

        if ($requiresNewToken) {
            $externalOrderId = $this->generateDamageFeeOrderNumber($order);
            $snapToken = $midtrans->createDamageFeeSnapToken($order, $damageFeeAmount, $externalOrderId);

            $payment->midtrans_order_id = $externalOrderId;
            $payment->snap_token = $snapToken;
            $payment->status = Order::PAYMENT_PENDING;
            $payment->transaction_status = 'pending';
            $payment->gross_amount = $damageFeeAmount;
            $payment->paid_at = null;
            $payment->expired_at = now()->addDays(3);
            $payment->payload_json = json_encode([
                'scope' => 'damage_fee',
                'generated_at' => now()->toDateTimeString(),
                'subtotal' => $damageFeeAmount,
                'tax' => 0,
                'total' => $damageFeeAmount,
                'note' => (string) ($order->additional_fee_note ?? ''),
            ], JSON_UNESCAPED_UNICODE);
            $payment->save();
        }

        return response()->json([
            'order_id' => $order->id,
            'midtrans_order_id' => $payment->midtrans_order_id,
            'snap_token' => $payment->snap_token,
            'payment_status' => $payment->status,
        ]);
    }

    public function handleNotification(Request $request)
    {
        $payload = $request->all();

        if (! $this->isValidNotificationPayload($payload)) {
            return response()->json(['message' => __('Invalid payload')], 400);
        }

        if (! $this->isValidNotificationSignature($payload)) {
            return response()->json(['message' => __('Invalid signature')], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $targetPayment = $this->resolvePaymentByExternalId($orderId);
        $order = $targetPayment?->order ?: $this->resolveOrderByExternalId($orderId);

        if (! $order) {
            Log::warning('Midtrans callback order not found', [
                'order_id' => $orderId,
                'payload' => $payload,
            ]);

            return response()->json(['message' => __('Order not found')], 404);
        }

        if (! $this->reserveWebhookEvent($payload, $order->id)) {
            return response()->json(['status' => 'ok', 'message' => __('duplicate')]);
        }

        $this->syncOrderPaymentStatus($order, $payload, $targetPayment);

        return response()->json(['status' => 'ok']);
    }

    public function refreshStatus(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $order->midtrans_order_id) {
            return response()->json([
                'message' => __('Order belum memiliki Midtrans ID.'),
            ], 422);
        }

        try {
            $this->configureMidtrans();
            $transaction = Transaction::status($order->midtrans_order_id);
            $payload = json_decode(json_encode($transaction), true) ?: [];

            if (! isset($payload['order_id'])) {
                $payload['order_id'] = $order->midtrans_order_id;
            }

            $this->syncOrderPaymentStatus($order, $payload);
            $order->refresh();
            $order->loadMissing('damagePayment');

            $isPaid = ($order->status_pembayaran ?? 'pending') === 'paid';
            $hasOutstandingDamageFee = $order->hasOutstandingDamageFee();
            $canViewInvoice = $order->canAccessInvoice();
            if (! $order->order_number && $order->midtrans_order_id) {
                $order->order_number = $order->midtrans_order_id;
                $order->save();
            }

            $orderRouteKey = (string) ($order->order_number ?: $order->midtrans_order_id);
            $signedInvoiceUrl = $canViewInvoice
                ? URL::temporarySignedRoute('account.orders.receipt', now()->addMinutes(30), ['order' => $orderRouteKey])
                : null;
            $signedInvoicePdfUrl = $canViewInvoice
                ? URL::temporarySignedRoute('account.orders.receipt.pdf', now()->addMinutes(30), ['order' => $orderRouteKey])
                : null;
            $signedInvoicePdfPreviewUrl = $canViewInvoice
                ? URL::temporarySignedRoute('account.orders.receipt.pdf', now()->addMinutes(30), ['order' => $orderRouteKey, 'inline' => 1])
                : null;

            return response()->json([
                'status' => 'ok',
                'is_paid' => $isPaid,
                'payment_status' => $order->status_pembayaran,
                'order_status' => $order->status_pesanan,
                'paid_at' => $order->paid_at?->toIso8601String(),
                'detail_url' => route('account.orders.show', $order),
                'can_view_invoice' => $canViewInvoice,
                'has_damage_fee_outstanding' => $hasOutstandingDamageFee,
                'invoice_url' => $signedInvoiceUrl,
                'invoice_pdf_url' => $signedInvoicePdfUrl,
                'invoice_pdf_preview_url' => $signedInvoicePdfPreviewUrl,
                'receipt_number' => $order->order_number,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => __('Gagal menyinkronkan status pembayaran. Coba lagi beberapa saat.'),
            ], 500);
        }
    }

    public function refreshDamageFeeStatus(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('provider', Payment::PROVIDER_MIDTRANS_DAMAGE)
            ->latest('id')
            ->first();

        if (! $payment || ! $payment->midtrans_order_id) {
            return response()->json([
                'message' => __('Tagihan tambahan belum memiliki sesi pembayaran Midtrans.'),
            ], 422);
        }

        try {
            $this->configureMidtrans();
            $transaction = Transaction::status($payment->midtrans_order_id);
            $payload = json_decode(json_encode($transaction), true) ?: [];

            if (! isset($payload['order_id'])) {
                $payload['order_id'] = $payment->midtrans_order_id;
            }

            $this->syncOrderPaymentStatus($order, $payload, $payment);
            $order->refresh();
            $payment->refresh();

            return response()->json([
                'status' => 'ok',
                'is_paid' => (string) $payment->status === Order::PAYMENT_PAID,
                'payment_status' => (string) $payment->status,
                'order_status' => (string) ($order->status_pesanan ?? ''),
                'detail_url' => route('account.orders.show', $order),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => __('Gagal menyinkronkan status tagihan tambahan.'),
            ], 500);
        }
    }

    private function syncOrderPaymentStatus(Order $order, array $payload, ?Payment $targetPayment = null): void
    {
        $beforePaymentStatus = $order->status_pembayaran;
        $beforeOrderStatus = $order->status_pesanan;

        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? 'pending'));
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $grossAmount = $this->normalizeGrossAmount($payload['gross_amount'] ?? $order->total_amount ?? 0);
        $mappedStatus = $this->mapOrderStatus($transactionStatus, $fraudStatus);
        $externalOrderId = (string) ($payload['order_id'] ?? '');

        $payment = $targetPayment;
        if (! $payment && $externalOrderId !== '') {
            $payment = $this->resolvePaymentByExternalId($externalOrderId);
        }

        if (! $payment) {
            $payment = Payment::firstOrNew([
                'order_id' => $order->id,
                'provider' => Payment::PROVIDER_MIDTRANS_RENTAL,
            ]);
        }

        $provider = (string) ($payment->provider ?: Payment::PROVIDER_MIDTRANS_RENTAL);
        $previousPaymentStatus = (string) ($payment->status ?? '');

        if (! $order->order_number) {
            $order->order_number = $order->midtrans_order_id ?: $this->generateOrderNumber($order->id);
        }

        if ($provider === Payment::PROVIDER_MIDTRANS_RENTAL && ! $order->midtrans_order_id && $externalOrderId !== '') {
            $order->midtrans_order_id = $externalOrderId;
            if (! $order->order_number) {
                $order->order_number = $order->midtrans_order_id;
            }
        }

        $payment->order_id = $order->id;
        $payment->provider = $provider;
        $payment->transaction_id = $payload['transaction_id'] ?? $payment->transaction_id;
        $payment->payment_type = $paymentType;
        $payment->transaction_status = $transactionStatus;
        if ($externalOrderId !== '') {
            $payment->midtrans_order_id = $externalOrderId;
        }
        $payment->gross_amount = $grossAmount;
        $payment->status = $mappedStatus;
        $payment->paid_at = $mappedStatus === Order::PAYMENT_PAID ? now() : null;
        $payment->expired_at = $mappedStatus === Order::PAYMENT_EXPIRED ? now() : $payment->expired_at;
        $payment->payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $payment->save();

        if (! $order->snap_token && $provider === Payment::PROVIDER_MIDTRANS_RENTAL && $payment->snap_token) {
            $order->snap_token = $payment->snap_token;
        }

        if ($provider === Payment::PROVIDER_MIDTRANS_DAMAGE) {
            $this->applyDamageFeeOrderStatus($order, $mappedStatus);
            if ($previousPaymentStatus !== $mappedStatus) {
                $this->sendDamageFeeNotification($order, $mappedStatus, $payment);
            }
        } else {
            $this->applyOrderStatus($order, $mappedStatus);
            $this->sendPaymentSyncNotification($order, $beforePaymentStatus, $beforeOrderStatus);
        }

        $order->save();
    }

    private function sendPaymentSyncNotification(Order $order, ?string $beforePaymentStatus, ?string $beforeOrderStatus): void
    {
        if (! schema_table_exists_cached('order_notifications')) {
            return;
        }

        if ($order->user_id <= 0) {
            return;
        }

        if ($beforePaymentStatus === $order->status_pembayaran && $beforeOrderStatus === $order->status_pesanan) {
            return;
        }

        $title = __('Update pembayaran') . ' ' . ($order->order_number ?: ('ORD-' . $order->id));
        $message = __('Status pembayaran kamu sekarang: :status.', [
            'status' => $this->paymentStatusLabel($order->status_pembayaran),
        ]);

        if ($order->status_pembayaran === 'paid') {
            $message .= ' ' . __('Invoice sudah tersedia.');
        }

        OrderNotification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => 'payment_update',
            'title' => $title,
            'message' => $message,
        ]);
    }

    private function sendDamageFeeNotification(Order $order, string $paymentStatus, Payment $payment): void
    {
        if (! schema_table_exists_cached('order_notifications')) {
            return;
        }

        if ($order->user_id <= 0) {
            return;
        }

        $damageFeeAmount = max((int) ($payment->gross_amount ?? $order->resolvePenaltyAmount()), 0);

        $message = match ($paymentStatus) {
            Order::PAYMENT_PAID => __('Tagihan tambahan kerusakan sebesar Rp :amount sudah lunas.', [
                'amount' => number_format($damageFeeAmount, 0, ',', '.'),
            ]),
            Order::PAYMENT_EXPIRED => __('Tagihan tambahan kerusakan kadaluwarsa. Silakan buat sesi pembayaran baru dari detail order.'),
            Order::PAYMENT_FAILED => __('Pembayaran tagihan tambahan gagal. Silakan coba lagi.'),
            default => __('Tagihan tambahan kerusakan berstatus :status.', [
                'status' => $this->paymentStatusLabel($paymentStatus),
            ]),
        };

        OrderNotification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => 'damage_fee_update',
            'title' => __('Tagihan tambahan') . ' ' . ($order->order_number ?: ('ORD-' . $order->id)),
            'message' => $message,
        ]);
    }

    private function applyOrderStatus(Order $order, string $orderStatus): void
    {
        if ($orderStatus === 'paid') {
            $order->status_pembayaran = Order::PAYMENT_PAID;
            if ($order->canTransitionToOrderStatus(Order::STATUS_READY_PICKUP)) {
                $order->status_pesanan = Order::STATUS_READY_PICKUP;
            }
            $order->status = 'paid';
            if (! $order->paid_at) {
                $order->paid_at = now();
            }

            return;
        }

        if ($orderStatus === 'failed') {
            $order->status_pembayaran = Order::PAYMENT_FAILED;
            if ($order->canTransitionToOrderStatus(Order::STATUS_CANCELLED)) {
                $order->status_pesanan = Order::STATUS_CANCELLED;
            }
            $order->status = 'failed';
            $order->paid_at = null;

            return;
        }

        if ($orderStatus === 'expired') {
            $order->status_pembayaran = Order::PAYMENT_EXPIRED;
            if ($order->canTransitionToOrderStatus(Order::STATUS_EXPIRED)) {
                $order->status_pesanan = Order::STATUS_EXPIRED;
            }
            $order->status = 'expired';
            $order->paid_at = null;

            return;
        }

        if ($orderStatus === 'refunded') {
            $order->status_pembayaran = Order::PAYMENT_REFUNDED;
            if ($order->canTransitionToOrderStatus(Order::STATUS_REFUNDED)) {
                $order->status_pesanan = Order::STATUS_REFUNDED;
            }
            $order->status = 'refunded';
            $order->paid_at = null;

            return;
        }

        if ($order->status_pembayaran !== Order::PAYMENT_PAID) {
            $order->status_pembayaran = Order::PAYMENT_PENDING;
            if ($order->canTransitionToOrderStatus(Order::STATUS_PENDING_PAYMENT)) {
                $order->status_pesanan = Order::STATUS_PENDING_PAYMENT;
            }
            $order->status = 'pending';
            $order->paid_at = null;
        }
    }

    private function applyDamageFeeOrderStatus(Order $order, string $paymentStatus): void
    {
        if ($paymentStatus === Order::PAYMENT_PAID) {
            if ($order->canTransitionToOrderStatus(Order::STATUS_COMPLETED)) {
                $order->status_pesanan = Order::STATUS_COMPLETED;
            }
            $order->status = 'paid';

            return;
        }

        if ($paymentStatus === Order::PAYMENT_EXPIRED) {
            if (in_array((string) $order->status_pesanan, [Order::STATUS_RETURNED_DAMAGED, Order::STATUS_RETURNED_LOST], true)
                && $order->canTransitionToOrderStatus(Order::STATUS_OVERDUE_DAMAGE_INVOICE)) {
                $order->status_pesanan = Order::STATUS_OVERDUE_DAMAGE_INVOICE;
                $order->status = 'pending';
            }
        }
    }

    private function resolveOrderByExternalId(string $orderId): ?Order
    {
        return Order::query()
            ->where('midtrans_order_id', $orderId)
            ->orWhere('order_number', $orderId)
            ->first();
    }

    private function resolvePaymentByExternalId(string $externalOrderId): ?Payment
    {
        if ($externalOrderId === '') {
            return null;
        }

        return Payment::query()
            ->where('midtrans_order_id', $externalOrderId)
            ->latest('id')
            ->first();
    }

    private function isValidNotificationPayload(array $payload): bool
    {
        return isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'], $payload['signature_key']);
    }

    private function isValidNotificationSignature(array $payload): bool
    {
        $serverKey = config('services.midtrans.server_key') ?: config('midtrans.server_key');

        if (! $serverKey) {
            return false;
        }

        $expectedSignature = hash(
            'sha512',
            (string) $payload['order_id'] . (string) $payload['status_code'] . (string) $payload['gross_amount'] . $serverKey
        );

        return hash_equals($expectedSignature, (string) $payload['signature_key']);
    }

    private function configureMidtrans(): void
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    private function generateOrderNumber(int $orderId): string
    {
        return 'ORDER-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }

    private function generateDamageFeeOrderNumber(Order $order): string
    {
        $orderNumber = $order->order_number ?: ('ORD' . $order->id);
        $normalized = Str::upper(Str::of($orderNumber)->replaceMatches('/[^A-Z0-9]/', '')->value());

        return $normalized . '-DMG-' . Str::upper(Str::random(5));
    }

    private function mapOrderStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if (in_array($transactionStatus, ['capture', 'settlement'], true) && $fraudStatus !== 'challenge') {
            return 'paid';
        }

        if ($transactionStatus === 'expire') {
            return 'expired';
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'failure'], true)) {
            return 'failed';
        }

        if (in_array($transactionStatus, ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'], true)) {
            return 'refunded';
        }

        return 'pending';
    }

    private function paymentStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => __('Lunas'),
            'failed' => __('Gagal'),
            'expired' => __('Kedaluwarsa'),
            'refunded' => __('Refund'),
            default => __('Menunggu'),
        };
    }

    private function reserveWebhookEvent(array $payload, ?int $orderId = null): bool
    {
        if (! schema_table_exists_cached('payment_webhook_events')) {
            return true;
        }

        $eventKey = $this->buildWebhookEventKey($payload);
        $event = PaymentWebhookEvent::query()->firstOrCreate(
            [
                'provider' => 'midtrans',
                'event_key' => $eventKey,
            ],
            [
                'order_id' => $orderId,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'processed_at' => now(),
            ]
        );

        if (! $event->wasRecentlyCreated) {
            return false;
        }

        return true;
    }

    private function buildWebhookEventKey(array $payload): string
    {
        $transactionId = trim((string) ($payload['transaction_id'] ?? ''));
        if ($transactionId !== '') {
            return $transactionId;
        }

        return sha1(json_encode([
            'order_id' => (string) ($payload['order_id'] ?? ''),
            'transaction_status' => (string) ($payload['transaction_status'] ?? ''),
            'status_code' => (string) ($payload['status_code'] ?? ''),
            'gross_amount' => (string) ($payload['gross_amount'] ?? ''),
            'fraud_status' => (string) ($payload['fraud_status'] ?? ''),
            'settlement_time' => (string) ($payload['settlement_time'] ?? ''),
        ], JSON_UNESCAPED_UNICODE));
    }

    private function normalizeGrossAmount(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        return 0;
    }
}
