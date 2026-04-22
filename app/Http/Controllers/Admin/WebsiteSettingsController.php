<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
class WebsiteSettingsController extends Controller
{
    public function edit()
    {
        $keys = $this->settingsKeys();
        $settings = SiteSetting::query()
            ->whereIn('key', array_values($keys))
            ->pluck('value', 'key')
            ->toArray();

        return view('admin.settings.website', [
            'settings' => $settings,
            'activePage' => 'website',
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'brand_name' => ['nullable', 'string', 'max:150'],
            'brand_tagline' => ['nullable', 'string', 'max:255'],
            'seo_meta_title' => ['nullable', 'string', 'max:255'],
            'seo_meta_description' => ['nullable', 'string', 'max:500'],
            'contact_whatsapp' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_tiktok' => ['nullable', 'string', 'max:255'],
            'brand_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'brand_favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,ico', 'max:2048'],
            'maintenance_enabled' => ['nullable', 'boolean'],
        ]);

        $adminId = auth('admin')->id();
        $mapping = $this->settingsKeys();
        $existingValues = SiteSetting::query()
            ->whereIn('key', array_values($mapping))
            ->pluck('value', 'key');

        $values = [
            'brand_name' => array_key_exists('brand_name', $data)
                ? $data['brand_name']
                : $existingValues->get($mapping['brand_name']),
            'brand_tagline' => array_key_exists('brand_tagline', $data)
                ? $data['brand_tagline']
                : $existingValues->get($mapping['brand_tagline']),
            'seo_meta_title' => array_key_exists('seo_meta_title', $data)
                ? $data['seo_meta_title']
                : $existingValues->get($mapping['seo_meta_title']),
            'seo_meta_description' => array_key_exists('seo_meta_description', $data)
                ? $data['seo_meta_description']
                : $existingValues->get($mapping['seo_meta_description']),
            'contact_whatsapp' => array_key_exists('contact_whatsapp', $data)
                ? $data['contact_whatsapp']
                : $existingValues->get($mapping['contact_whatsapp']),
            'social_instagram' => array_key_exists('social_instagram', $data)
                ? $data['social_instagram']
                : $existingValues->get($mapping['social_instagram']),
            'social_tiktok' => array_key_exists('social_tiktok', $data)
                ? $data['social_tiktok']
                : $existingValues->get($mapping['social_tiktok']),
            'maintenance_enabled' => array_key_exists('maintenance_enabled', $data)
                ? ($request->boolean('maintenance_enabled') ? '1' : '0')
                : ($existingValues->get($mapping['maintenance_enabled']) ?? '0'),
        ];

        if ($request->hasFile('brand_logo')) {
            $values['brand_logo'] = $this->storeFile($request->file('brand_logo'), 'branding', $mapping['brand_logo']);
        }

        if ($request->hasFile('brand_favicon')) {
            $values['brand_favicon'] = $this->storeFile($request->file('brand_favicon'), 'branding', $mapping['brand_favicon']);
        }

        foreach ($mapping as $field => $key) {
            $value = $values[$field] ?? null;

            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => $this->fieldType($field),
                    'group' => $this->fieldGroup($field),
                    'updated_by_admin_id' => $adminId,
                ]
            );

            site_setting_forget($key);
        }

        if (! empty($values['brand_logo'])) {
            site_media_upsert(
                key: $mapping['brand_logo'],
                path: $values['brand_logo'],
                group: 'branding',
                altText: 'Brand logo',
                disk: site_media_disk(),
                adminId: $adminId
            );
        }

        if (! empty($values['brand_favicon'])) {
            site_media_upsert(
                key: $mapping['brand_favicon'],
                path: $values['brand_favicon'],
                group: 'branding',
                altText: 'Brand favicon',
                disk: site_media_disk(),
                adminId: $adminId
            );
        }

        admin_audit('website_settings.update', 'site_settings', null, [
            'keys' => array_values($mapping),
        ], $adminId);

        return back()->with('success', __('Website settings berhasil disimpan.'));
    }

    private function storeFile($file, string $directory, string $settingKey): string
    {
        $current = SiteSetting::query()->where('key', $settingKey)->value('value');
        if ($current) {
            site_media_delete($current);
        }

        return site_media_store_uploaded_file($file, $directory);
    }

    private function settingsKeys(): array
    {
        return [
            'brand_name' => 'brand.name',
            'brand_tagline' => 'brand.tagline',
            'brand_logo' => 'brand.logo_path',
            'brand_favicon' => 'brand.favicon_path',
            'seo_meta_title' => 'seo.meta_title',
            'seo_meta_description' => 'seo.meta_description',
            'contact_whatsapp' => 'contact.whatsapp',
            'social_instagram' => 'social.instagram',
            'social_tiktok' => 'social.tiktok',
            'maintenance_enabled' => 'site.maintenance_enabled',
        ];
    }

    private function fieldType(string $field): string
    {
        return match ($field) {
            'maintenance_enabled' => 'boolean',
            'brand_logo', 'brand_favicon' => 'file',
            'seo_meta_description' => 'textarea',
            default => 'text',
        };
    }

    private function fieldGroup(string $field): string
    {
        return match ($field) {
            'brand_name', 'brand_tagline', 'brand_logo', 'brand_favicon' => 'branding',
            'seo_meta_title', 'seo_meta_description' => 'seo',
            'contact_whatsapp' => 'contact',
            'social_instagram', 'social_tiktok' => 'social',
            default => 'website',
        };
    }
}
