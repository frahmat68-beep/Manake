<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $values = [
            'brand_name' => $data['brand_name'] ?? null,
            'brand_tagline' => $data['brand_tagline'] ?? null,
            'seo_meta_title' => $data['seo_meta_title'] ?? null,
            'seo_meta_description' => $data['seo_meta_description'] ?? null,
            'contact_whatsapp' => $data['contact_whatsapp'] ?? null,
            'social_instagram' => $data['social_instagram'] ?? null,
            'social_tiktok' => $data['social_tiktok'] ?? null,
            'maintenance_enabled' => $request->boolean('maintenance_enabled') ? '1' : '0',
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
                adminId: $adminId
            );
        }

        if (! empty($values['brand_favicon'])) {
            site_media_upsert(
                key: $mapping['brand_favicon'],
                path: $values['brand_favicon'],
                group: 'branding',
                altText: 'Brand favicon',
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
            Storage::disk('public')->delete($current);
        }

        return $file->store($directory, 'public');
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
