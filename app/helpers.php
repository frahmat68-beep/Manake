<?php

use App\Models\AuditLog;
use App\Models\SiteContent;
use App\Models\SiteMedia;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

if (! function_exists('site_public_media_candidates')) {
    function site_public_media_candidates(string $path): array
    {
        $normalizedPath = ltrim($path, '/');
        $candidates = [
            [public_path('storage/' . $normalizedPath), public_path('storage')],
            [base_path('storage/app/public/' . $normalizedPath), base_path('storage/app/public')],
        ];

        try {
            $publicDisk = Storage::disk('public');

            if (method_exists($publicDisk, 'exists') && $publicDisk->exists($normalizedPath)) {
                $diskPath = $publicDisk->path($normalizedPath);

                if (is_file($diskPath)) {
                    $candidates[] = [$diskPath, dirname($diskPath)];
                }
            }
        } catch (\Throwable $exception) {
            // Fall through to bundled/public path checks.
        }

        return $candidates;
    }
}

if (! function_exists('schema_table_exists_cached')) {
    function schema_table_exists_cached(string $table): bool
    {
        static $tableExistsCache = [];

        if (app()->runningUnitTests()) {
            try {
                return Schema::hasTable($table);
            } catch (\Throwable $exception) {
                return false;
            }
        }

        $cacheKey = schema_exists_cache_key('table', $table);

        if (array_key_exists($cacheKey, $tableExistsCache)) {
            return $tableExistsCache[$cacheKey];
        }

        try {
            $tableExistsCache[$cacheKey] = (bool) Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                fn () => Schema::hasTable($table)
            );
        } catch (\Throwable $exception) {
            $tableExistsCache[$cacheKey] = false;
        }

        return $tableExistsCache[$cacheKey];
    }
}

if (! function_exists('schema_column_exists_cached')) {
    function schema_column_exists_cached(string $table, string $column): bool
    {
        static $columnExistsCache = [];

        if (! schema_table_exists_cached($table)) {
            return false;
        }

        if (app()->runningUnitTests()) {
            try {
                return Schema::hasColumn($table, $column);
            } catch (\Throwable $exception) {
                return false;
            }
        }

        $cacheKey = schema_exists_cache_key('column', $table, $column);

        if (array_key_exists($cacheKey, $columnExistsCache)) {
            return $columnExistsCache[$cacheKey];
        }

        try {
            $columnExistsCache[$cacheKey] = (bool) Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                fn () => Schema::hasColumn($table, $column)
            );
        } catch (\Throwable $exception) {
            $columnExistsCache[$cacheKey] = false;
        }

        return $columnExistsCache[$cacheKey];
    }
}

if (! function_exists('schema_exists_cache_key')) {
    function schema_exists_cache_key(string $type, string $table, ?string $column = null): string
    {
        $connection = (string) config('database.default', 'default');
        $database = (string) config("database.connections.{$connection}.database", 'default');
        $signature = $connection . '|' . $database . '|' . $table . '|' . ((string) $column);

        return 'schema_exists:' . $type . ':' . sha1($signature);
    }
}

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return site_setting($key, $default);
    }
}

if (! function_exists('site_setting')) {
    function site_setting(string $key, $default = null)
    {
        if (! schema_table_exists_cached('site_settings')) {
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
        if (! schema_table_exists_cached('site_settings')) {
            return null;
        }

        $cached = Cache::remember("site_setting:{$key}", 3600, function () use ($key) {
            return [
                'resolved' => true,
                'value' => SiteSetting::query()->where('key', $key)->value('value'),
            ];
        });

        if (is_array($cached) && array_key_exists('resolved', $cached)) {
            return $cached['value'] ?? null;
        }

        return $cached;
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
        if (schema_table_exists_cached('site_settings')) {
            $fromSetting = site_setting($key);
            if ($fromSetting !== null) {
                return $fromSetting;
            }
        }

        // Backward compatibility source: site_contents.
        if (! schema_table_exists_cached('site_contents')) {
            return $default;
        }

        return Cache::remember("site_content:{$key}", 3600, function () use ($key, $default) {
            return SiteContent::query()->where('key', $key)->value('value') ?? $default;
        });
    }
}

if (! function_exists('trusted_map_embed_url')) {
    function trusted_map_embed_url(?string $rawEmbed, ?string $fallbackAddress = null): ?string
    {
        $value = trim((string) $rawEmbed);

        if ($value !== '') {
            if (preg_match('/<iframe[^>]*\s+src=["\']([^"\']+)["\']/i', $value, $matches) === 1) {
                $value = html_entity_decode((string) ($matches[1] ?? ''), ENT_QUOTES, 'UTF-8');
            }

            $trustedUrl = trusted_map_normalize_url($value);
            if ($trustedUrl !== null) {
                return $trustedUrl;
            }
        }

        $fallback = trim((string) $fallbackAddress);
        if ($fallback !== '') {
            return 'https://www.google.com/maps?q=' . rawurlencode($fallback) . '&output=embed';
        }

        return null;
    }
}

if (! function_exists('trusted_map_embed_iframe')) {
    function trusted_map_embed_iframe(?string $rawEmbed, ?string $fallbackAddress = null): ?string
    {
        $embedUrl = trusted_map_embed_url($rawEmbed, $fallbackAddress);

        if ($embedUrl === null) {
            return null;
        }

        return '<iframe src="' . e($embedUrl) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';
    }
}

if (! function_exists('trusted_map_normalize_url')) {
    function trusted_map_normalize_url(?string $value): ?string
    {
        $candidate = trim((string) $value);
        if ($candidate === '') {
            return null;
        }

        $parts = parse_url($candidate);
        if ($parts === false) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = strtolower((string) ($parts['path'] ?? ''));
        $query = (string) ($parts['query'] ?? '');

        if ($host === '' || ! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $isGoogleHost = str_ends_with($host, 'google.com')
            || str_ends_with($host, 'google.co.id')
            || in_array($host, ['maps.google.com', 'maps.app.goo.gl', 'goo.gl'], true);

        if ($isGoogleHost) {
            if (str_contains($path, '/maps/embed') || str_contains($query, 'output=embed')) {
                return preg_replace('/^http:/i', 'https:', $candidate);
            }

            return 'https://www.google.com/maps?q=' . rawurlencode($candidate) . '&output=embed';
        }

        return null;
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

if (! function_exists('site_public_media_path')) {
    function site_public_media_path(string $path): ?string
    {
        foreach (site_public_media_candidates($path) as [$candidate]) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

if (! function_exists('site_asset')) {
    function site_asset(string $path, bool $withVersion = true): string
    {
        $normalizedPath = ltrim($path, '/');
        $version = '1';

        if ($withVersion) {
            $publicPath = public_path($normalizedPath);
            $version = file_exists($publicPath) ? (string) filemtime($publicPath) : '1';
        }

        return route('assets.public', [
            'path' => $normalizedPath,
            'v' => $version,
        ], false);
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
        $normalizedPath = ltrim($path, '/');

        try {
            if ($resolvedDisk === 'public') {
                $absolutePath = site_public_media_path($normalizedPath);

                if (! $absolutePath) {
                    return null;
                }

                $version = '1';
                if (is_file($absolutePath)) {
                    $version = (string) filemtime($absolutePath);
                }

                return route('assets.media', [
                    'path' => $normalizedPath,
                    'v' => $version,
                ], false);
            }

            $resolvedStorage = Storage::disk($resolvedDisk);

            if (method_exists($resolvedStorage, 'exists') && ! $resolvedStorage->exists($normalizedPath)) {
                return null;
            }

            return $resolvedStorage->url($normalizedPath);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}

if (! function_exists('admin_audit')) {
    function admin_audit(string $action, ?string $tableName = null, string|int|null $recordId = null, array $payload = [], ?int $adminId = null): void
    {
        if (! schema_table_exists_cached('audit_logs')) {
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
