<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_summary_renders_saved_badge(): void
    {
        $user = User::factory()->create();

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => 'Dewi Lestari',
                'phone' => '081299988877',
                'address' => 'Jl. Mawar No. 10',
                'city' => 'Jakarta',
                'notes' => 'Catatan penting',
                'identity_number' => '3173020202020002',
                'emergency_contact' => 'Bima 081200000000',
                'is_completed' => true,
            ]
        );

        $this->actingAs($user);

        $response = $this->get(route('profile.complete'));

        $response->assertOk();
        $response->assertSee(__('ui.profile_complete.saved_badge'));
        $response->assertSee('Dewi Lestari');
        $response->assertSee('Jakarta');
    }
}
