<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OrderReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_route_redirects_when_order_not_paid(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-UNPAID-1',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 100000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-UNPAID-1',
        ]);

        $response = $this->actingAs($user)->get($this->signedReceiptUrl($order));

        $response->assertRedirect(route('account.orders.show', $order));
        $response->assertSessionHas('error');
    }

    public function test_receipt_route_renders_when_order_is_paid(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PAID-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 500000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->addDay()->toDateString(),
            'midtrans_order_id' => 'MNK-PAID-1',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get($this->signedReceiptUrl($order));

        $response->assertOk();
        $response->assertSee('Invoice');
        $response->assertSee('ORD-PAID-1');
    }

    public function test_receipt_route_redirects_when_damage_fee_is_unpaid(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-DAMAGE-UNPAID-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'barang_rusak',
            'status' => 'paid',
            'total_amount' => 500000,
            'additional_fee' => 100000,
            'rental_start_date' => now()->subDays(5)->toDateString(),
            'rental_end_date' => now()->subDays(2)->toDateString(),
            'midtrans_order_id' => 'MNK-DAMAGE-UNPAID-1',
            'paid_at' => now()->subDays(6),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_MIDTRANS_DAMAGE,
            'midtrans_order_id' => 'MNK-DAMAGE-PAYMENT-UNPAID-1',
            'status' => 'pending',
            'transaction_status' => 'pending',
            'gross_amount' => 100000,
            'snap_token' => 'snap-damage-pending',
        ]);

        $response = $this->actingAs($user)->get($this->signedReceiptUrl($order));

        $response->assertRedirect(route('account.orders.show', $order));
        $response->assertSessionHas('error', 'Invoice tersedia setelah tagihan tambahan lunas.');
    }

    public function test_receipt_route_renders_when_damage_fee_is_paid(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-DAMAGE-PAID-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'selesai',
            'status' => 'paid',
            'total_amount' => 500000,
            'additional_fee' => 100000,
            'rental_start_date' => now()->subDays(5)->toDateString(),
            'rental_end_date' => now()->subDays(2)->toDateString(),
            'midtrans_order_id' => 'MNK-DAMAGE-PAID-1',
            'paid_at' => now()->subDays(6),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_MIDTRANS_DAMAGE,
            'midtrans_order_id' => 'MNK-DAMAGE-PAYMENT-PAID-1',
            'status' => 'paid',
            'transaction_status' => 'settlement',
            'gross_amount' => 100000,
            'paid_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get($this->signedReceiptUrl($order));

        $response->assertOk();
        $response->assertSee('Invoice');
        $response->assertSee('ORD-DAMAGE-PAID-1');
    }

    public function test_receipt_pdf_route_redirects_when_damage_fee_is_unpaid(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PDF-DAMAGE-UNPAID-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'barang_rusak',
            'status' => 'paid',
            'total_amount' => 400000,
            'additional_fee' => 120000,
            'rental_start_date' => now()->subDays(4)->toDateString(),
            'rental_end_date' => now()->subDay()->toDateString(),
            'midtrans_order_id' => 'MNK-PDF-DAMAGE-UNPAID-1',
            'paid_at' => now()->subDays(3),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_MIDTRANS_DAMAGE,
            'midtrans_order_id' => 'MNK-PDF-DAMAGE-PAYMENT-UNPAID-1',
            'status' => 'pending',
            'transaction_status' => 'pending',
            'gross_amount' => 120000,
            'snap_token' => 'snap-pdf-damage-pending',
        ]);

        $response = $this->actingAs($user)->get($this->signedReceiptPdfUrl($order));

        $response->assertRedirect(route('account.orders.show', $order));
        $response->assertSessionHas('error', 'Invoice PDF tersedia setelah tagihan tambahan lunas.');
    }

    public function test_receipt_pdf_route_downloads_when_invoice_is_accessible(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PDF-PAID-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'selesai',
            'status' => 'paid',
            'total_amount' => 500000,
            'rental_start_date' => now()->subDays(3)->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-PDF-PAID-1',
            'paid_at' => now()->subHours(3),
        ]);

        $response = $this->actingAs($user)->get($this->signedReceiptPdfUrl($order));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_receipt_pdf_route_streams_inline_when_requested(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PDF-INLINE-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'selesai',
            'status' => 'paid',
            'total_amount' => 420000,
            'rental_start_date' => now()->subDays(2)->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-PDF-INLINE-1',
            'paid_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($user)->get($this->signedReceiptPdfUrl($order, ['inline' => 1]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $contentDisposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('inline', strtolower($contentDisposition));
        $this->assertStringContainsString('Invoice-ORD-PDF-INLINE-1.pdf', $contentDisposition);
    }

    public function test_receipt_view_deduplicates_order_id_and_hides_zero_optional_totals(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-SAME-ID-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'selesai',
            'status' => 'paid',
            'total_amount' => 350000,
            'rental_start_date' => now()->subDays(2)->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'ORD-SAME-ID-1',
            'paid_at' => now()->subHours(4),
        ]);

        $response = $this->actingAs($user)->get($this->signedReceiptUrl($order));

        $response->assertOk();
        $response->assertSee(__('ui.invoice.meta.invoice_order_id'));
        $response->assertDontSee('<span>' . __('ui.invoice.meta.invoice_id') . '</span>', false);
        $response->assertDontSee('<span>' . __('ui.invoice.meta.order_id') . '</span>', false);
        $response->assertDontSee(__('ui.invoice.totals.shipping'));
        $response->assertDontSee(__('ui.invoice.totals.penalty'));
    }

    private function signedReceiptUrl(Order $order): string
    {
        $orderRouteKey = (string) ($order->order_number ?: $order->midtrans_order_id ?: $order->id);

        return URL::temporarySignedRoute('account.orders.receipt', now()->addMinutes(30), [
            'order' => $orderRouteKey,
        ]);
    }

    private function signedReceiptPdfUrl(Order $order, array $query = []): string
    {
        $orderRouteKey = (string) ($order->order_number ?: $order->midtrans_order_id ?: $order->id);

        return URL::temporarySignedRoute('account.orders.receipt.pdf', now()->addMinutes(30), [
            'order' => $orderRouteKey,
            ...$query,
        ]);
    }
}
