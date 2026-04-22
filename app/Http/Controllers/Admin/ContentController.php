<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function index()
    {
        $schema = $this->schema();
        $settingKeys = collect($schema)
            ->flatMap(function ($meta) {
                if (($meta['type'] ?? 'text') === 'image') {
                    return [$meta['key'], $meta['key'] . '_alt'];
                }

                return [$meta['key']];
            })
            ->values()
            ->all();

        $settings = SiteSetting::query()
            ->whereIn('key', $settingKeys)
            ->pluck('value', 'key')
            ->toArray();

        return view('admin.content.index', [
            'schema' => $schema,
            'settings' => $settings,
            'activePage' => 'content',
        ]);
    }

    public function update(Request $request)
    {
        $schema = $this->schema();
        $rules = [];
        foreach ($schema as $field => $meta) {
            $rules[$field] = $meta['validation'];
            if (($meta['type'] ?? 'text') === 'image') {
                $rules[$field . '_alt'] = ['nullable', 'string', 'max:255'];
            }
        }

        $data = $request->validate($rules);
        $adminId = auth('admin')->id();
        $changedKeys = [];

        foreach ($schema as $field => $meta) {
            $settingKey = $meta['key'];
            $value = $data[$field] ?? null;
            $imageAltValue = $data[$field . '_alt'] ?? null;

            if (($meta['type'] ?? 'text') === 'image') {
                $current = SiteSetting::query()->where('key', $settingKey)->value('value');

                if ($request->hasFile($field)) {
                    if ($current && ! str_starts_with((string) $current, 'http')) {
                        site_media_delete($current);
                    }

                    $storedPath = site_media_store_uploaded_file($request->file($field), 'site/' . $meta['group']);
                    $value = $storedPath;
                    site_media_upsert(
                        key: $settingKey,
                        path: $storedPath,
                        group: $meta['group'],
                        altText: $imageAltValue,
                        disk: site_media_disk(),
                        adminId: $adminId
                    );
                } else {
                    $value = $current;
                }
            }

            SiteSetting::updateOrCreate(
                ['key' => $settingKey],
                [
                    'value' => $value,
                    'type' => $meta['type'],
                    'group' => $meta['group'],
                    'updated_by_admin_id' => $adminId,
                ]
            );

            $changedKeys[] = $settingKey;
            if (($meta['type'] ?? 'text') === 'image') {
                $changedKeys[] = $settingKey . '_alt';
                SiteSetting::updateOrCreate(
                    ['key' => $settingKey . '_alt'],
                    [
                        'value' => $imageAltValue,
                        'type' => 'text',
                        'group' => $meta['group'],
                        'updated_by_admin_id' => $adminId,
                    ]
                );
            }
        }

        site_setting_forget($changedKeys);
        admin_audit('content.update', 'site_settings', null, [
            'keys' => $changedKeys,
        ], $adminId);

        return back()->with('success', __('Konten berhasil disimpan.'));
    }

    private function schema(): array
    {
        return [
            'home_hero_title' => [
                'key' => 'home.hero_title',
                'label' => 'Hero Title',
                'type' => 'text',
                'group' => 'home',
                'validation' => ['nullable', 'string', 'max:255'],
            ],
            'home_hero_subtitle' => [
                'key' => 'home.hero_subtitle',
                'label' => 'Hero Subtitle',
                'type' => 'textarea',
                'group' => 'home',
                'validation' => ['nullable', 'string', 'max:1000'],
            ],
            'home_hero_image_path' => [
                'key' => 'home.hero_image_path',
                'label' => 'Hero Image',
                'type' => 'image',
                'group' => 'home',
                'validation' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            ],
            'home_overview_headline' => [
                'key' => 'home.overview_headline',
                'label' => 'Overview Headline',
                'type' => 'text',
                'group' => 'home',
                'validation' => ['nullable', 'string', 'max:255'],
            ],
            'footer_about' => [
                'key' => 'footer.about',
                'label' => 'Footer About',
                'type' => 'textarea',
                'group' => 'footer',
                'validation' => ['nullable', 'string', 'max:1500'],
            ],
            'footer_address' => [
                'key' => 'footer.address',
                'label' => 'Footer Address',
                'type' => 'textarea',
                'group' => 'footer',
                'validation' => ['nullable', 'string', 'max:1200'],
            ],
            'footer_whatsapp' => [
                'key' => 'footer.whatsapp',
                'label' => 'Footer WhatsApp',
                'type' => 'text',
                'group' => 'footer',
                'validation' => ['nullable', 'string', 'max:255'],
            ],
            'footer_instagram' => [
                'key' => 'footer.instagram',
                'label' => 'Footer Instagram',
                'type' => 'text',
                'group' => 'footer',
                'validation' => ['nullable', 'string', 'max:255'],
            ],
            'contact_email' => [
                'key' => 'contact.email',
                'label' => 'Contact Email',
                'type' => 'text',
                'group' => 'contact',
                'validation' => ['nullable', 'email', 'max:255'],
            ],
            'contact_phone' => [
                'key' => 'contact.phone',
                'label' => 'Contact Phone',
                'type' => 'text',
                'group' => 'contact',
                'validation' => ['nullable', 'string', 'max:255'],
            ],
            'contact_map_embed' => [
                'key' => 'contact.map_embed',
                'label' => 'Contact Map Embed',
                'type' => 'textarea',
                'group' => 'contact',
                'validation' => [
                    'nullable',
                    'string',
                    'max:5000',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! is_string($value) || trim($value) === '') {
                            return;
                        }

                        if (trusted_map_embed_url($value) === null) {
                            $fail(__('Embed peta harus menggunakan Google Maps yang valid.'));
                        }
                    },
                ],
            ],
        ];
    }
}
