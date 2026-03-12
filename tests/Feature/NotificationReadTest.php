<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_notification_as_read_and_get_updated_unread_count(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-NOTIF-READ-1',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 100000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-NOTIF-READ-1',
        ]);

        $notification = OrderNotification::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => 'order_update',
            'title' => 'Update pesanan',
            'message' => 'Pesanan diproses.',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('notifications.read', $notification));

        $response
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'unread_count' => 0,
            ]);

        $this->assertDatabaseHas('order_notifications', [
            'id' => $notification->id,
        ]);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $order = Order::create([
            'user_id' => $owner->id,
            'order_number' => 'ORD-NOTIF-READ-2',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 100000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-NOTIF-READ-2',
        ]);

        $notification = OrderNotification::query()->create([
            'user_id' => $owner->id,
            'order_id' => $order->id,
            'type' => 'order_update',
            'title' => 'Update pesanan',
            'message' => 'Pesanan diproses.',
        ]);

        $this->actingAs($otherUser)
            ->postJson(route('notifications.read', $notification))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_notifications_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-NOTIF-PAGE-1',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 100000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-NOTIF-PAGE-1',
        ]);

        OrderNotification::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => 'order_update',
            'title' => 'Update pesanan',
            'message' => 'Pesanan siap diproses.',
        ]);

        $this->actingAs($user)
            ->get(route('notifications'))
            ->assertOk()
            ->assertSee('Update pesanan');
    }

    public function test_user_can_mark_notification_as_read_and_redirect_to_target_page(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-NOTIF-READ-REDIRECT',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 100000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-NOTIF-READ-REDIRECT',
        ]);

        $notification = OrderNotification::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => 'order_update',
            'title' => 'Update pesanan',
            'message' => 'Pesanan diproses.',
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notification), [
            'redirect' => route('account.orders.show', $order),
        ]);

        $response->assertRedirect(route('account.orders.show', $order));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_opening_order_detail_marks_related_notifications_as_read(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-NOTIF-SHOW-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 100000,
            'rental_start_date' => now()->subDay()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-NOTIF-SHOW-1',
            'paid_at' => now()->subHour(),
        ]);

        $notification = OrderNotification::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => 'order_update',
            'title' => 'Update pesanan',
            'message' => 'Pesanan siap diambil.',
        ]);

        $this->actingAs($user)
            ->get(route('account.orders.show', $order))
            ->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
