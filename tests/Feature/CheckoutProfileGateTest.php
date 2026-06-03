<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutProfileGateTest extends TestCase
{
    use RefreshDatabase;

    private function createEquipment(): Equipment
    {
        $category = Category::create([
            'name' => 'Profile Gate Category',
            'slug' => 'profile-gate-category',
        ]);

        return Equipment::create([
            'category_id' => $category->id,
            'name' => 'Profile Gate Gear',
            'slug' => 'profile-gate-gear',
            'description' => 'Gear for profile gate test',
            'price_per_day' => 150000,
            'stock' => 4,
            'status' => 'ready',
        ]);
    }

    private function completeProfilePayload(): array
    {
        return [
            'full_name' => 'Fikri Rahmat',
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
            'rental_responsibility_consent' => 1,
        ];
    }

    public function test_guest_is_redirected_from_checkout_to_login(): void
    {
        $response = $this->get(route('checkout'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_profile_complete_page(): void
    {
        $response = $this->get(route('profile'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_completed_profile_is_redirected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            ['is_completed' => false]
        );

        $this->assertFalse($profile->is_completed);

        $response = $this->get(route('checkout'));

        $response->assertRedirect(route('profile.complete'));
    }

    public function test_incomplete_profile_cannot_add_item_to_cart(): void
    {
        $user = User::factory()->create();
        $equipment = $this->createEquipment();

        $this->actingAs($user);

        $response = $this->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertRedirect(route('profile.complete'));
        $response->assertSessionHas('warning', __('Lengkapi profil penyewaan sebelum memesan alat.'));
    }

    public function test_profile_completion_persists_and_allows_checkout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $payload = $this->completeProfilePayload();

        $response = $this->post(route('profile.complete.store'), $payload);

        $response->assertRedirect(route('profile.complete'));

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'full_name' => 'Fikri Rahmat',
            'nik' => '3276020202020001',
            'phone' => '081234567890',
            'address_line' => 'Jl. Contoh No. 123 RT 01/03',
            'is_completed' => true,
        ]);

        $profile = Profile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertTrue((bool) $profile->is_completed);
        $this->assertNotNull($profile->rental_consent_accepted_at);

        $checkoutResponse = $this->get(route('checkout'));

        $checkoutResponse->assertOk();
    }

    public function test_authenticated_user_without_rental_consent_cannot_checkout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($this->completeProfilePayload(), [
                'is_completed' => true,
                'completed_at' => now(),
                'rental_consent_accepted_at' => null,
            ])
        );

        $checkoutResponse = $this->get(route('checkout'));
        $checkoutResponse->assertRedirect(route('profile.complete'));
    }

    public function test_authenticated_user_without_rental_consent_cannot_add_item_to_cart(): void
    {
        $user = User::factory()->create();
        $equipment = $this->createEquipment();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($this->completeProfilePayload(), [
                'is_completed' => true,
                'completed_at' => now(),
                'rental_consent_accepted_at' => null,
            ])
        );

        $this->actingAs($user);

        $response = $this->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertRedirect(route('profile.complete'));
        $response->assertSessionHas('warning', __('Setujui pernyataan tanggung jawab sewa sebelum melanjutkan pemesanan.'));
    }

    public function test_authenticated_user_with_unverified_email_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($this->completeProfilePayload(), [
                'is_completed' => true,
                'completed_at' => now(),
                'rental_consent_accepted_at' => now(),
            ])
        );

        $this->actingAs($user);
        $response = $this->get(route('checkout'));

        $response->assertRedirect(route('profile.complete'));
    }

    public function test_authenticated_user_with_unverified_email_cannot_add_item_to_cart(): void
    {
        $user = User::factory()->unverified()->create();
        $equipment = $this->createEquipment();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($this->completeProfilePayload(), [
                'is_completed' => true,
                'completed_at' => now(),
                'rental_consent_accepted_at' => now(),
            ])
        );

        $this->actingAs($user);

        $response = $this->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertRedirect(route('profile.complete'));
        $response->assertSessionHas('warning', __('Verifikasi email dan lengkapi profil sebelum melanjutkan pemesanan.'));
    }

    public function test_complete_profile_with_verified_email_and_consent_can_add_item_to_cart(): void
    {
        $user = User::factory()->create();
        $equipment = $this->createEquipment();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($this->completeProfilePayload(), [
                'is_completed' => true,
                'completed_at' => now(),
                'rental_consent_accepted_at' => now(),
            ])
        );

        $this->actingAs($user);

        $response = $this->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'qty' => 1,
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('success');
    }

    public function test_catalog_and_product_browsing_still_work_without_profile_completion(): void
    {
        $equipment = $this->createEquipment();

        $this->get(route('catalog'))->assertOk();
        $this->get(route('product.show', $equipment->slug))->assertOk();
    }
}
