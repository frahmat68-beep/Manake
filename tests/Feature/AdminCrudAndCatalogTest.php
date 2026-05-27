<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
            'name' => 'Camera',
            'slug' => 'camera',
            'description' => 'Kategori kamera.',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Camera',
            'slug' => 'camera',
        ]);
    }

    public function test_admin_can_create_equipment_with_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $category = Category::create([
            'name' => 'Camera',
            'slug' => 'camera',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.equipments.store'), [
            'name' => 'Sony A7 III',
            'slug' => 'sony-a7-iii',
            'category_id' => $category->id,
            'price_per_day' => 350000,
            'stock' => 6,
            'status' => 'ready',
            'description' => 'Kamera mirrorless.',
            'image' => UploadedFile::fake()->image('sony.jpg', 1200, 800),
        ]);

        $response->assertRedirect(route('admin.equipments.index'));

        $equipment = Equipment::query()->where('slug', 'sony-a7-iii')->first();
        $this->assertNotNull($equipment);
        $this->assertSame(6, $equipment->stock);
        $this->assertNotEmpty($equipment->image_path);
        Storage::disk('public')->assertExists($equipment->image_path);
    }

    public function test_catalog_shows_equipment(): void
    {
        $category = Category::create([
            'name' => 'Camera',
            'slug' => 'camera',
        ]);

        Equipment::create([
            'name' => 'Sony A7 III',
            'slug' => 'sony-a7-iii',
            'category_id' => $category->id,
            'price_per_day' => 350000,
            'status' => 'ready',
        ]);

        $response = $this->get(route('catalog'));
        $response->assertOk();
        $response->assertSee('Sony A7 III');
    }

    public function test_product_detail_shows_equipment(): void
    {
        $category = Category::create([
            'name' => 'Camera',
            'slug' => 'camera',
        ]);

        Equipment::create([
            'name' => 'Sony A7 III',
            'slug' => 'sony-a7-iii',
            'category_id' => $category->id,
            'price_per_day' => 350000,
            'status' => 'ready',
        ]);

        $response = $this->get(route('product.show', 'sony-a7-iii'));
        $response->assertOk();
        $response->assertSee('Sony A7 III');
    }

    public function test_product_detail_shows_specifications_from_admin_input(): void
    {
        $category = Category::create([
            'name' => 'Lens',
            'slug' => 'lens',
        ]);

        Equipment::create([
            'name' => 'Sony FE 35mm',
            'slug' => 'sony-fe-35mm',
            'category_id' => $category->id,
            'price_per_day' => 250000,
            'status' => 'ready',
            'specifications' => "Mount: Sony E\nFocal: 35mm\nIsi box: Lens + pouch",
            'description' => 'Lensa prime untuk portrait.',
        ]);

        $response = $this->get(route('product.show', 'sony-fe-35mm'));
        $response->assertOk();
        $response->assertSee('Mount: Sony E');
        $response->assertSee('Focal: 35mm');
        $response->assertSee('Isi box: Lens + pouch');
    }

    public function test_admin_can_delete_equipment_that_has_order_history(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'name' => 'Temporary Audio Kit',
            'slug' => 'temporary-audio-kit',
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status_pembayaran' => Order::PAYMENT_PAID,
            'status_pesanan' => Order::STATUS_COMPLETED,
            'total_amount' => 100000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->addDay()->toDateString(),
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000,
            'rental_start_date' => now()->toDateString(),
            'rental_end_date' => now()->addDay()->toDateString(),
            'rental_days' => 2,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->delete(route('admin.equipments.destroy', $equipment->slug));

        $response->assertRedirect(route('admin.equipments.index'));
        $this->assertDatabaseMissing('equipments', ['id' => $equipment->id]);
        $this->assertNull($item->fresh()->equipment_id);
    }
}
