<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartQuantityValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_add_rejects_quantity_above_stock(): void
    {
        $user = User::factory()->create();
        $this->seedVerifiedRentalProfile($user);
        $equipment = $this->createEquipment([
            'stock' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'qty' => 2,
        ]);

        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('error', function ($message) use ($equipment) {
            return str_contains((string) $message, "Stok {$equipment->name} tersedia 1 unit");
        });
        $this->assertSame([], session()->get('cart.items', []));
    }

    public function test_cart_increment_rejects_when_quantity_exceeds_stock(): void
    {
        $user = User::factory()->create();
        $this->seedVerifiedRentalProfile($user);
        $equipment = $this->createEquipment([
            'stock' => 1,
        ]);
        $key = 'equipment:' . $equipment->id;

        $response = $this
            ->actingAs($user)
            ->withSession([
                'cart.items' => [
                    $key => [
                        'key' => $key,
                        'equipment_id' => $equipment->id,
                        'name' => $equipment->name,
                        'slug' => $equipment->slug,
                        'price' => $equipment->price_per_day,
                        'qty' => 1,
                    ],
                ],
            ])
            ->patch(route('cart.increment', $key));

        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('error');

        $items = session()->get('cart.items', []);
        $this->assertSame(1, (int) data_get($items, $key . '.qty'));
    }

    public function test_cart_update_rejects_when_quantity_exceeds_stock(): void
    {
        $user = User::factory()->create();
        $this->seedVerifiedRentalProfile($user);
        $equipment = $this->createEquipment([
            'stock' => 1,
        ]);
        $key = 'equipment:' . $equipment->id;

        $response = $this
            ->actingAs($user)
            ->withSession([
                'cart.items' => [
                    $key => [
                        'key' => $key,
                        'equipment_id' => $equipment->id,
                        'name' => $equipment->name,
                        'slug' => $equipment->slug,
                        'price' => $equipment->price_per_day,
                        'qty' => 1,
                    ],
                ],
            ])
            ->patch(route('cart.update', $key), [
                'qty' => 3,
            ]);

        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('error');

        $items = session()->get('cart.items', []);
        $this->assertSame(1, (int) data_get($items, $key . '.qty'));
    }

    public function test_cart_add_rejects_when_same_user_existing_booking_exceeds_remaining_stock(): void
    {
        $user = User::factory()->create();
        $this->seedVerifiedRentalProfile($user);
        $equipment = $this->createEquipment([
            'stock' => 20,
        ]);

        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(7)->toDateString();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'MNK-SELF-STOCK-LOCK',
            'status_pembayaran' => 'paid',
            'status_pesanan' => 'lunas',
            'status' => 'paid',
            'total_amount' => 1500000,
            'rental_start_date' => $startDate,
            'rental_end_date' => $endDate,
            'midtrans_order_id' => 'MNK-SELF-STOCK-LOCK',
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'equipment_id' => $equipment->id,
            'qty' => 15,
            'price' => $equipment->price_per_day,
            'subtotal' => 15 * (int) $equipment->price_per_day * 3,
            'rental_start_date' => $startDate,
            'rental_end_date' => $endDate,
            'rental_days' => 3,
        ]);

        $response = $this->actingAs($user)->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'qty' => 7,
            'rental_start_date' => $startDate,
            'rental_end_date' => $endDate,
        ]);

        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('error', function ($message) use ($equipment) {
            $text = (string) $message;

            return str_contains($text, $equipment->name) && str_contains($text, 'Tanggal bentrok:');
        });
        $this->assertSame([], session()->get('cart.items', []));
    }

    private function createEquipment(array $overrides = []): Equipment
    {
        $category = Category::create([
            'name' => 'Cart Validation',
            'slug' => 'cart-validation-' . strtolower((string) str()->random(6)),
        ]);

        return Equipment::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Stock Limited Gear',
            'slug' => 'stock-limited-' . strtolower((string) str()->random(6)),
            'description' => 'Testing stock lock',
            'price_per_day' => 100000,
            'stock' => 1,
            'status' => 'ready',
            'image' => null,
        ], $overrides));
    }

    private function seedVerifiedRentalProfile(User $user): void
    {
        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => 'Valid Rental User',
                'nik' => '3276020202020001',
                'date_of_birth' => '1997-02-02',
                'gender' => 'male',
                'phone' => '081234567890',
                'address_line' => 'Jl. Contoh No. 123 RT 01/03',
                'kelurahan' => 'Cijerah',
                'kecamatan' => 'Bandung Kulon',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'postal_code' => '40213',
                'maps_url' => 'https://maps.google.com/?q=bandung',
                'emergency_name' => 'Alya',
                'emergency_relation' => 'Saudara',
                'emergency_phone' => '081234000000',
                'identity_number' => '3276020202020001',
                'address' => 'Jl. Contoh No. 123 RT 01/03',
                'emergency_contact' => 'Alya (Saudara) - 081234000000',
                'is_completed' => true,
                'completed_at' => now(),
                'rental_consent_accepted_at' => now(),
            ]
        );
    }
}
