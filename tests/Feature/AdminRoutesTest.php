<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Route Admin',
            'email' => 'route-admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_pages_are_accessible_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();

        $routes = [
            'admin.dashboard',
            'admin.categories.index',
            'admin.equipments.index',
            'admin.content.index',
            'admin.settings.index',
            'admin.website.edit',
            'admin.orders.index',
            'admin.users.index',
        ];

        foreach ($routes as $name) {
            $response = $this->actingAs($admin, 'admin')->get(route($name));
            $response->assertSuccessful();
        }

        $this->actingAs($admin, 'admin')
            ->get(route('admin.copy.edit', 'landing'))
            ->assertSuccessful();
    }

    public function test_admin_pages_redirect_guest_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_order_and_user_detail_pages_are_accessible(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => 'Fikri Rachmat']);
        $equipment = Equipment::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-TEST-100',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 250000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->addDay()->toDateString(),
        ]);

        $order->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'price' => 250000,
            'subtotal' => 250000,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order))
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.show', $user))
            ->assertOk();
    }

    public function test_legacy_admin_booking_routes_redirect_to_orders(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bookings.index'))
            ->assertRedirect(route('admin.orders.index'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bookings.show', 1))
            ->assertRedirect(route('admin.orders.show', 1));
    }

    public function test_admin_dashboard_shows_financial_summary_cards(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-FIN-001',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 300000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->addDay()->toDateString(),
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Uang Masuk');
        $response->assertSee('Pendapatan Sewa');
        $response->assertSee('Pajak Terkumpul');
        $response->assertSee('Rp 333.000');
        $response->assertSee('Rp 300.000');
        $response->assertSee('Rp 33.000');
    }

    public function test_admin_orders_page_shows_monthly_archive_and_logs(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-ARCHIVE-001',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'selesai',
            'status' => 'completed',
            'total_amount' => 275000,
            'rental_start_date' => now()->subDays(10)->toDateString(),
            'rental_end_date' => now()->subDays(8)->toDateString(),
            'paid_at' => now()->subDays(11),
            'returned_at' => now()->subDays(7),
        ]);

        $order->items()->create([
            'equipment_id' => Equipment::factory()->create()->id,
            'qty' => 2,
            'price' => 137500,
            'subtotal' => 275000,
        ]);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'order.update_status',
            'table_name' => 'orders',
            'record_id' => (string) $order->id,
            'payload_json' => json_encode(['status_pembayaran' => 'paid']),
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertSee('Arsip Bulanan');
        $response->assertSee('Log Pesanan');
        $response->assertSee('MNK-ARCHIVE-001');
    }
}
