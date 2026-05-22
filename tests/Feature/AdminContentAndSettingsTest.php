<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentAndSettingsTest extends TestCase
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

    public function test_admin_can_update_content_text_and_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.content.update'), [
            'home_hero_title' => 'Hero Title',
            'home_hero_subtitle' => 'Hero subtitle',
            'home_hero_image_path' => UploadedFile::fake()->image('hero.jpg', 1200, 800),
            'home_hero_image_path_alt' => 'Hero Banner',
            'footer_about' => 'About footer',
            'footer_address' => 'Jl. Contoh 1',
            'footer_whatsapp' => 'wa.me/123',
            'footer_instagram' => '@insta',
            'contact_email' => 'hello@example.com',
            'contact_phone' => '080000000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('site_settings', [
            'key' => 'footer.about',
            'value' => 'About footer',
        ]);

        $heroImagePath = SiteSetting::query()->where('key', 'home.hero_image_path')->value('value');
        $this->assertNotNull($heroImagePath);
        Storage::disk('public')->assertExists($heroImagePath);
        $this->assertDatabaseHas('site_media', [
            'key' => 'home.hero_image_path',
            'path' => $heroImagePath,
            'disk' => 'public',
        ]);
    }

    public function test_admin_can_update_website_settings_with_logo(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.website.update'), [
            'brand_name' => 'Manake Test',
            'brand_tagline' => 'Tagline',
            'seo_meta_title' => 'Meta Title',
            'seo_meta_description' => 'Meta Description',
            'contact_whatsapp' => 'wa.me/123',
            'social_instagram' => 'instagram',
            'social_tiktok' => 'tiktok',
            'maintenance_enabled' => true,
            'brand_logo' => UploadedFile::fake()->image('logo.jpg', 200, 200),
        ]);

        $response->assertRedirect();
        $setting = SiteSetting::query()->where('key', 'brand.logo_path')->first();
        $this->assertNotNull($setting);
        Storage::disk('public')->assertExists($setting->value);

        $this->assertNotNull(site_media_url($setting->value));
        $this->get('/assets/media/'.$setting->value)->assertOk();
    }

    public function test_user_page_renders_dynamic_content(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'home.hero_title.id'],
            ['value' => 'Dynamic Hero']
        );

        SiteSetting::updateOrCreate(
            ['key' => 'footer.about.id'],
            ['value' => 'Footer Dynamic']
        );

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Sewa');
        $response->assertSee('Sewa');
        $response->assertSee('terbaik, kapan saja.');
        $response->assertDontSee('Dynamic Hero');
        $response->assertSee('Footer Dynamic');
    }

    public function test_admin_can_update_copywriting_section(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.copy.update', 'footer'), [
            'footer_about' => 'Kami menyewakan alat produksi untuk tim kecil sampai besar.',
            'footer_address' => 'Jl. Baru No. 123, Jakarta',
            'footer_whatsapp' => '+62 811-1111-1111',
            'footer_email' => 'halo@manake.id',
            'footer_instagram' => '@manakeid',
            'footer_copyright' => '2026 Manake.',
            'footer_tagline' => 'Rental alat produksi tanpa ribet.',
        ]);

        $response->assertRedirect(route('admin.copy.edit', 'footer'));
        $this->assertDatabaseHas('site_settings', [
            'key' => 'footer.about.id',
            'value' => 'Kami menyewakan alat produksi untuk tim kecil sampai besar.',
        ]);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'site_tagline.id',
            'value' => 'Rental alat produksi tanpa ribet.',
        ]);
    }
}
