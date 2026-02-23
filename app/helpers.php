<?php

use App\Models\AuditLog;
use App\Models\SiteContent;
use App\Models\SiteMedia;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return site_setting($key, $default);
    }
}

if (! function_exists('site_setting')) {
    function site_setting(string $key, $default = null)
    {
        if (! Schema::hasTable('site_settings')) {
            return $default;
        }

        $locale = app()->bound('translator')
            ? (string) app()->getLocale()
            : (string) config('app.locale', 'id');
        $fallbackLocale = (string) config('app.fallback_locale', 'id');

        if ($locale !== '') {
            $localizedValue = site_setting_raw("{$key}.{$locale}");
            if ($localizedValue !== null) {
                return $localizedValue;
            }
        }

        $baseValue = site_setting_raw($key);
        if ($baseValue !== null) {
            if ($locale !== '' && $locale !== $fallbackLocale && site_setting_is_translatable_key($key)) {
                return $default;
            }

            return $baseValue;
        }

        if ($locale !== '' && $locale !== $fallbackLocale) {
            $fallbackLocalizedValue = site_setting_raw("{$key}.{$fallbackLocale}");
            if ($fallbackLocalizedValue !== null && ! site_setting_is_translatable_key($key)) {
                return $fallbackLocalizedValue;
            }
        }

        return $default;
    }
}

if (! function_exists('site_setting_raw')) {
    function site_setting_raw(string $key)
    {
        if (! Schema::hasTable('site_settings')) {
            return null;
        }

        return Cache::remember("site_setting:{$key}", 3600, function () use ($key) {
            return SiteSetting::query()->where('key', $key)->value('value');
        });
    }
}

if (! function_exists('site_setting_is_translatable_key')) {
    function site_setting_is_translatable_key(string $key): bool
    {
        if (str_starts_with($key, 'copy.')) {
            return true;
        }

        $exactKeys = [
            'hero_title',
            'hero_subtitle',
            'hero_cta_text',
            'home.hero_title',
            'home.hero_subtitle',
            'home.overview_headline',
            'site_name',
            'site_tagline',
            'meta_title',
            'meta_description',
            'seo.meta_title',
            'seo.meta_description',
            'footer_description',
            'footer.about',
            'footer.rules_title',
            'footer.rules_link',
            'footer.rules_note',
            'footer_copyright',
            'contact.title',
            'contact.subtitle',
            'contact.info_title',
            'contact.map_title',
            'contact.map_empty',
            'contact.form_receiver_email',
            'brand.tagline',
        ];

        if (in_array($key, $exactKeys, true)) {
            return true;
        }

        $prefixes = [
            'home.',
            'footer.',
            'contact.',
            'seo.',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('site_content')) {
    function site_content(string $key, $default = null)
    {
        // Preferred source: site_settings.
        if (Schema::hasTable('site_settings')) {
            $fromSetting = site_setting($key);
            if ($fromSetting !== null) {
                return $fromSetting;
            }
        }

        // Backward compatibility source: site_contents.
        if (! Schema::hasTable('site_contents')) {
            return $default;
        }

        return Cache::remember("site_content:{$key}", 3600, function () use ($key, $default) {
            return SiteContent::query()->where('key', $key)->value('value') ?? $default;
        });
    }
}

if (! function_exists('site_setting_forget')) {
    function site_setting_forget(string|array $keys): void
    {
        foreach ((array) $keys as $key) {
            Cache::forget("site_setting:{$key}");
            Cache::forget("site_content:{$key}");
        }
    }
}

if (! function_exists('site_media_url')) {
    function site_media_url(?string $path, ?string $disk = 'public'): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $resolvedDisk = $disk ?: 'public';
        if ($resolvedDisk === 'public') {
            return asset('storage/' . ltrim($path, '/'));
        }

        return Storage::disk($resolvedDisk)->url($path);
    }
}

if (! function_exists('admin_audit')) {
    function admin_audit(string $action, ?string $tableName = null, string|int|null $recordId = null, array $payload = [], ?int $adminId = null): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        $resolvedAdminId = $adminId;
        if (! $resolvedAdminId) {
            $resolvedAdminId = Auth::guard('admin')->id();
        }

        AuditLog::create([
            'admin_id' => $resolvedAdminId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId ? (string) $recordId : null,
            'payload_json' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}

if (! function_exists('site_media_upsert')) {
    function site_media_upsert(string $key, string $path, string $group, ?string $altText = null, string $disk = 'public', ?int $adminId = null): SiteMedia
    {
        return SiteMedia::query()->updateOrCreate(
            ['key' => $key],
            [
                'path' => $path,
                'disk' => $disk,
                'group' => $group,
                'alt_text' => $altText,
                'uploaded_by_admin_id' => $adminId ?? Auth::guard('admin')->id(),
            ]
        );
    }
}
