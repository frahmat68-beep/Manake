<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutOrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_missing_equipment(): void
    {
        $user = User::factory()->create();
        $this->seedCompletedVerifiedProfile($user);
        $otherUser = User::factory()->create();

        $this->withSession([
            'cart.items' => [
                'equipment:999' => [
                    'equipment_id' => 999,
                    'name' => 'Missing Gear',
                    'price' => 10000,
                    'qty' => 1,
                    'key' => 'equipment:999',
                ],
            ],
        ]);
        $this->actingAs($user);
        $this->assertTrue(auth()->check());
        $this->assertSame($user->id, auth()->id());
        $this->assertDatabaseHas('profiles', ['user_id' => $user->id, 'is_completed' => 1]);
        $this->assertTrue((bool) auth()->user()->profile?->is_completed);

        $response = $this->postJson(route('checkout.store'), [
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'confirm_profile' => 'on',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Beberapa alat tidak tersedia. Silakan perbarui cart.',
        ]);
    }

    public function test_checkout_creates_order_items_with_equipment_id(): void
    {
        $user = User::factory()->create();
        $this->seedCompletedVerifiedProfile($user);

        $category = Category::create(['name' => 'Camera']);
        $equipment = Equipment::create([
            'category_id' => $category->id,
            'name' => 'Sony A7',
            'description' => 'Camera',
            'price_per_day' => 250000,
            'stock' => 5,
            'image' => null,
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createSnapToken')->once()->andReturn('snap-test-token');
        });

        $this->withSession([
            'cart.items' => [
                'equipment:' . $equipment->id => [
                    'equipment_id' => $equipment->id,
                    'name' => $equipment->name,
                    'price' => 1,
                    'qty' => 2,
                    'key' => 'equipment:' . $equipment->id,
                ],
            ],
        ]);
        $this->actingAs($user);
        $this->assertTrue(auth()->check());
        $this->assertSame($user->id, auth()->id());
        $this->assertDatabaseHas('profiles', ['user_id' => $user->id, 'is_completed' => 1]);
        $this->assertTrue((bool) auth()->user()->profile?->is_completed);

        $response = $this->postJson(route('checkout.store'), [
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'confirm_profile' => 'on',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['order_id', 'midtrans_order_id', 'snap_token', 'redirect_url_to_order_detail']);

        $this->assertDatabaseHas('order_items', [
            'equipment_id' => $equipment->id,
            'qty' => 2,
            'price' => 250000,
            'subtotal' => 500000,
        ]);
    }

    public function test_checkout_rejects_when_requested_qty_exceeds_available_stock(): void
    {
        $user = User::factory()->create();
        $this->seedCompletedVerifiedProfile($user);
        $otherUser = User::factory()->create();

        $category = Category::create(['name' => 'Lighting']);
        $equipment = Equipment::create([
            'category_id' => $category->id,
            'name' => 'Aputure 600D',
            'description' => 'Light',
            'price_per_day' => 300000,
            'stock' => 1,
            'status' => 'ready',
            'image' => null,
        ]);

        $existingOrder = Order::create([
            'user_id' => $otherUser->id,
            'order_number' => 'MNK-EXIST-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 300000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'midtrans_order_id' => 'MNK-EXIST-1',
            'paid_at' => now(),
        ]);

        $existingOrder->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'price' => 300000,
            'subtotal' => 300000,
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldNotReceive('createSnapToken');
        });

        $this->withSession([
            'cart.items' => [
                'equipment:' . $equipment->id => [
                    'equipment_id' => $equipment->id,
                    'name' => $equipment->name,
                    'price' => 300000,
                    'qty' => 1,
                    'key' => 'equipment:' . $equipment->id,
                ],
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->toDateString(),
            'confirm_profile' => 'on',
        ]);

        $response->assertStatus(422);
        $message = (string) $response->json('message');
        $this->assertStringContainsString('Aputure 600D tidak tersedia pada tanggal:', $message);
    }

    public function test_checkout_rejects_overlap_even_when_conflict_from_same_user_order(): void
    {
        $user = User::factory()->create();
        $this->seedCompletedVerifiedProfile($user);

        $category = Category::create(['name' => 'Lighting']);
        $equipment = Equipment::create([
            'category_id' => $category->id,
            'name' => 'Nanlite Forza 300',
            'description' => 'Light',
            'price_per_day' => 280000,
            'stock' => 1,
            'status' => 'ready',
            'image' => null,
        ]);

        $rentalDate = now()->addDays(2)->toDateString();
        $existingOrder = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-SELF-ALLOW-1',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 280000,
            'rental_start_date' => $rentalDate,
            'rental_end_date' => $rentalDate,
            'midtrans_order_id' => 'MNK-SELF-ALLOW-1',
            'paid_at' => now(),
        ]);

        $existingOrder->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'price' => 280000,
            'subtotal' => 280000,
            'rental_start_date' => $rentalDate,
            'rental_end_date' => $rentalDate,
            'rental_days' => 1,
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldNotReceive('createSnapToken');
        });

        $this->withSession([
            'cart.items' => [
                'equipment:' . $equipment->id => [
                    'equipment_id' => $equipment->id,
                    'name' => $equipment->name,
                    'price' => 280000,
                    'qty' => 1,
                    'key' => 'equipment:' . $equipment->id,
                ],
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'rental_start_date' => $rentalDate,
            'rental_end_date' => $rentalDate,
            'confirm_profile' => 'on',
        ]);

        $response->assertStatus(422);
        $message = (string) $response->json('message');
        $this->assertStringContainsString('Nanlite Forza 300 tidak tersedia pada tanggal:', $message);
    }

    public function test_checkout_allows_when_existing_rental_does_not_overlap_dates(): void
    {
        $user = User::factory()->create();
        $this->seedCompletedVerifiedProfile($user);

        $category = Category::create(['name' => 'Camera']);
        $equipment = Equipment::create([
            'category_id' => $category->id,
            'name' => 'Lumix S1H',
            'description' => 'Camera',
            'price_per_day' => 650000,
            'stock' => 1,
            'status' => 'ready',
            'image' => null,
        ]);

        $existingOrder = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-EXIST-2',
            'status_pembayaran' => 'pending',
            'status_pesanan' => 'menunggu_pembayaran',
            'status' => 'pending',
            'total_amount' => 650000,
            'rental_start_date' => now()->addDays(10)->toDateString(),
            'rental_end_date' => now()->addDays(11)->toDateString(),
            'midtrans_order_id' => 'MNK-EXIST-2',
        ]);

        $existingOrder->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'price' => 650000,
            'subtotal' => 650000,
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createSnapToken')->once()->andReturn('snap-test-token');
        });

        $this->withSession([
            'cart.items' => [
                'equipment:' . $equipment->id => [
                    'equipment_id' => $equipment->id,
                    'name' => $equipment->name,
                    'price' => 650000,
                    'qty' => 1,
                    'key' => 'equipment:' . $equipment->id,
                ],
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'rental_start_date' => now()->addDays(1)->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
            'confirm_profile' => 'on',
        ]);

        $response->assertOk();
        $response->assertJsonPath('snap_token', 'snap-test-token');
    }

    public function test_checkout_rejects_on_buffer_day_after_existing_booking(): void
    {
        $user = User::factory()->create();
        $this->seedCompletedVerifiedProfile($user);
        $otherUser = User::factory()->create();

        $category = Category::create(['name' => 'Audio']);
        $equipment = Equipment::create([
            'category_id' => $category->id,
            'name' => 'Zoom F8n',
            'description' => 'Recorder',
            'price_per_day' => 400000,
            'stock' => 1,
            'status' => 'ready',
            'image' => null,
        ]);

        $existingStart = now()->addDays(5)->toDateString();
        $existingEnd = now()->addDays(5)->toDateString();
        $bufferDay = now()->addDays(6)->toDateString();

        $existingOrder = Order::create([
            'user_id' => $otherUser->id,
            'order_number' => 'MNK-EXIST-BUFFER',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 400000,
            'rental_start_date' => $existingStart,
            'rental_end_date' => $existingEnd,
            'midtrans_order_id' => 'MNK-EXIST-BUFFER',
            'paid_at' => now(),
        ]);

        $existingOrder->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'price' => 400000,
            'subtotal' => 400000,
            'rental_start_date' => $existingStart,
            'rental_end_date' => $existingEnd,
            'rental_days' => 1,
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldNotReceive('createSnapToken');
        });

        $this->withSession([
            'cart.items' => [
                'equipment:' . $equipment->id => [
                    'equipment_id' => $equipment->id,
                    'name' => $equipment->name,
                    'price' => 400000,
                    'qty' => 1,
                    'key' => 'equipment:' . $equipment->id,
                ],
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'rental_start_date' => $bufferDay,
            'rental_end_date' => $bufferDay,
            'confirm_profile' => 'on',
        ]);

        $response->assertStatus(422);
        $message = (string) $response->json('message');
        $this->assertStringContainsString('Zoom F8n tidak tersedia pada tanggal:', $message);
    }

    public function test_checkout_rejects_on_buffer_day_before_existing_booking(): void
    {
        $user = User::factory()->create();
        $this->seedCompletedVerifiedProfile($user);
        $otherUser = User::factory()->create();

        $category = Category::create(['name' => 'Audio']);
        $equipment = Equipment::create([
            'category_id' => $category->id,
            'name' => 'Sennheiser EW100',
            'description' => 'Wireless Mic',
            'price_per_day' => 180000,
            'stock' => 1,
            'status' => 'ready',
            'image' => null,
        ]);

        $existingStart = now()->addDays(8)->toDateString();
        $existingEnd = now()->addDays(8)->toDateString();
        $bufferBeforeDay = now()->addDays(7)->toDateString();

        $existingOrder = Order::create([
            'user_id' => $otherUser->id,
            'order_number' => 'MNK-EXIST-BUFFER-BEFORE',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 180000,
            'rental_start_date' => $existingStart,
            'rental_end_date' => $existingEnd,
            'midtrans_order_id' => 'MNK-EXIST-BUFFER-BEFORE',
            'paid_at' => now(),
        ]);

        $existingOrder->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'price' => 180000,
            'subtotal' => 180000,
            'rental_start_date' => $existingStart,
            'rental_end_date' => $existingEnd,
            'rental_days' => 1,
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldNotReceive('createSnapToken');
        });

        $this->withSession([
            'cart.items' => [
                'equipment:' . $equipment->id => [
                    'equipment_id' => $equipment->id,
                    'name' => $equipment->name,
                    'price' => 180000,
                    'qty' => 1,
                    'key' => 'equipment:' . $equipment->id,
                ],
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'rental_start_date' => $bufferBeforeDay,
            'rental_end_date' => $bufferBeforeDay,
            'confirm_profile' => 'on',
        ]);

        $response->assertStatus(422);
        $message = (string) $response->json('message');
        $this->assertStringContainsString('Sennheiser EW100 tidak tersedia pada tanggal:', $message);
    }

    private function seedCompletedVerifiedProfile(User $user): void
    {
        $nik = str_pad((string) (3276000000000000 + $user->id), 16, '0', STR_PAD_LEFT);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => 'Tester',
                'nik' => $nik,
                'date_of_birth' => '1998-01-01',
                'gender' => 'male',
                'phone' => '08123456789',
                'address_line' => 'Jl. Test No. 1 RT 01/02',
                'kelurahan' => 'Sukarasa',
                'kecamatan' => 'Sukasari',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'postal_code' => '40151',
                'maps_url' => 'https://maps.google.com/?q=bandung',
                'emergency_name' => 'Kontak Darurat',
                'emergency_relation' => 'Saudara',
                'emergency_phone' => '081299999999',
                'address' => 'Jl. Test No. 1 RT 01/02',
                'is_completed' => true,
                'completed_at' => now(),
                'rental_consent_accepted_at' => now(),
            ]
        );
    }
}
