<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentCallbackAndOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_callback_marks_order_paid_with_valid_signature(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-1',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 250000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-TEST-123',
        ]);

        $payload = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '250000',
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-1',
            'payment_type' => 'bank_transfer',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . 'test-server-key');

        $response = $this->postJson('/api/midtrans/callback', $payload);

        $response->assertOk()->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'transaction_id' => 'trx-1',
            'transaction_status' => 'settlement',
            'status' => 'paid',
        ]);
    }

    public function test_midtrans_callback_is_idempotent_for_duplicate_payload(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-IDEMPOTENT-1',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 350000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-IDEMPOTENT-1',
        ]);

        $payload = [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '350000',
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-idempotent-1',
            'payment_type' => 'bank_transfer',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . 'test-server-key');

        $first = $this->postJson('/api/midtrans/callback', $payload);
        $first->assertOk()->assertJson(['status' => 'ok']);

        $second = $this->postJson('/api/midtrans/callback', $payload);
        $second->assertOk()->assertJson([
            'status' => 'ok',
            'message' => 'duplicate',
        ]);

        $this->assertDatabaseCount('payment_webhook_events', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
        ]);
    }

    public function test_midtrans_callback_rejects_invalid_signature(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-2',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 100000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-TEST-456',
        ]);

        $response = $this->postJson('/api/midtrans/callback', [
            'order_id' => $order->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '100000',
            'transaction_status' => 'settlement',
            'signature_key' => 'invalid-signature',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id, 'provider' => 'midtrans']);
    }

    public function test_midtrans_callback_marks_damage_fee_paid_without_changing_primary_paid_status(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-DAMAGE-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'barang_rusak',
            'status' => 'paid',
            'total_amount' => 500000,
            'additional_fee' => 150000,
            'rental_start_date' => now()->subDays(5)->toDateString(),
            'rental_end_date' => now()->subDays(2)->toDateString(),
            'midtrans_order_id' => 'MNK-RENTAL-PAID-1',
            'paid_at' => now()->subDays(6),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_MIDTRANS_DAMAGE,
            'midtrans_order_id' => 'MNK-DAMAGE-FEE-1',
            'status' => 'pending',
            'transaction_status' => 'pending',
            'gross_amount' => 150000,
            'snap_token' => 'snap-damage',
        ]);

        $payload = [
            'order_id' => 'MNK-DAMAGE-FEE-1',
            'status_code' => '200',
            'gross_amount' => '150000',
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-damage-1',
            'payment_type' => 'bank_transfer',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . 'test-server-key');

        $response = $this->postJson('/api/midtrans/callback', $payload);
        $response->assertOk()->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_MIDTRANS_DAMAGE,
            'midtrans_order_id' => 'MNK-DAMAGE-FEE-1',
            'status' => 'paid',
            'transaction_status' => 'settlement',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'selesai',
        ]);
    }

    public function test_otp_middleware_redirects_unverified_user_when_enabled(): void
    {
        config(['security.otp_required' => true]);

        $user = User::factory()->create([
            'is_otp_verified' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('otp.form'));
    }

    public function test_otp_resend_sends_email(): void
    {
        config(['security.otp_required' => true]);
        Mail::fake();

        $user = User::factory()->create([
            'is_otp_verified' => false,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('otp.resend'));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertNotNull($user->otp_code);
        $this->assertNotNull($user->otp_expires_at);

        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use ($user) {
            return $mail->hasTo($user->email) && Hash::check((string) $mail->otp, (string) $user->fresh()->otp_code);
        });
    }

    public function test_otp_verify_marks_user_as_verified(): void
    {
        config(['security.otp_required' => true]);

        $user = User::factory()->create([
            'is_otp_verified' => false,
            'otp_code' => Hash::make('123456'),
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->actingAs($user)->post(route('otp.verify'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('profile'));

        $user->refresh();
        $this->assertTrue((bool) $user->is_otp_verified);
        $this->assertNull($user->otp_code);
        $this->assertNull($user->otp_expires_at);
    }
}
