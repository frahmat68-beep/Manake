<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\Category;
use App\Models\OrderNotification;
use App\Models\SiteSetting;
use App\Models\User;
use App\Observers\UserObserver;
use App\Services\CartService;
use App\Services\OrderReminderService;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRuntimeTranslationOverrides();
        $this->registerVerifyEmailMailTemplate();

        User::observe(UserObserver::class);

        View::composer(['partials.navbar', 'layouts.app', 'layouts.user', 'layouts.app-dashboard', 'welcome'], function ($view) {
            $cartCount = app(CartService::class)->totalItems();
            static $navCategoriesCache;
            $notificationCount = 0;
            $notificationItems = collect();

            if ($navCategoriesCache === null) {
                $navCategoriesCache = collect();

                if (schema_table_exists_cached('categories')) {
                    $navCategoriesCache = Category::query()
                        ->select(['name', 'slug'])
                        ->orderBy('name')
                        ->limit(8)
                        ->get();
                }
            }

            if (Auth::guard('web')->check() && schema_table_exists_cached('order_notifications')) {
                $webUserId = (int) Auth::guard('web')->id();
                $reminderSyncKey = 'order_reminder_sync:' . $webUserId . ':' . now()->format('YmdH') . floor(now()->minute / 15);
                if (Cache::add($reminderSyncKey, 1, now()->addMinutes(15))) {
                    app(OrderReminderService::class)->dispatchDueReturnReminders($webUserId);
                }

                $notificationItems = OrderNotification::query()
                    ->with('order')
                    ->where('user_id', $webUserId)
                    ->latest()
                    ->limit(8)
                    ->get()
                    ->map(function (OrderNotification $notification) {
                        $targetUrl = route('notifications');
                        if ($notification->order) {
                            $targetUrl = route('account.orders.show', $notification->order);
                        }

                        return [
                            'id' => (int) $notification->id,
                            'title' => $notification->title,
                            'body' => $notification->message,
                            'url' => $targetUrl,
                            'mark_read_url' => route('notifications.read', $notification),
                            'time' => $notification->created_at?->diffForHumans(),
                            'is_new' => $notification->read_at === null,
                        ];
                    });

                $notificationCount = (int) OrderNotification::query()
                    ->where('user_id', $webUserId)
                    ->whereNull('read_at')
                    ->count();
            }

            $view->with([
                'cartCount' => $cartCount,
                'notificationCount' => $notificationCount,
                'notificationItems' => $notificationItems,
                'navCategories' => $navCategoriesCache,
            ]);
        });
    }

    private function registerVerifyEmailMailTemplate(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $verificationUrl) {
            $recipientName = trim((string) ($notifiable->display_name ?? $notifiable->name ?? ''));
            if ($recipientName === '') {
                $recipientName = (string) __('app.user.generic');
            }

            $expireMinutes = max((int) config('auth.verification.expire', 60), 1);
            $fromAddress = (string) config('mail.from.address', 'no-reply@manake.id');

            return (new MailMessage)
                ->subject(__('app.auth.verify_email_mail_subject'))
                ->from($fromAddress, 'Manake No-Reply')
                ->view('emails.verify-email', [
                    'recipientName' => $recipientName,
                    'verificationUrl' => $verificationUrl,
                    'expireMinutes' => $expireMinutes,
                    'subjectText' => __('app.auth.verify_email_mail_subject'),
                    'subtitleText' => __('app.auth.verify_email_mail_subtitle'),
                    'headingText' => __('app.auth.verify_email_mail_greeting', ['name' => $recipientName]),
                    'introText' => __('app.auth.verify_email_mail_intro'),
                    'buttonText' => __('app.auth.verify_email_mail_button'),
                    'expireText' => __('app.auth.verify_email_mail_expire', ['minutes' => $expireMinutes]),
                    'ignoreText' => __('app.auth.verify_email_mail_ignore'),
                    'noReplyText' => __('app.auth.verify_email_mail_no_reply'),
                ]);
        });
    }

    private function loadRuntimeTranslationOverrides(): void
    {
        if (! schema_table_exists_cached('site_settings')) {
            return;
        }

        $overridesByLocale = Cache::remember('copy.translation_overrides.scoped', 600, function () {
            $lines = [
                '*' => [],
                'id' => [],
                'en' => [],
            ];

            $parseLines = static function (?string $rawMap, array &$bucket): void {
                if (! is_string($rawMap) || trim($rawMap) === '') {
                    return;
                }

                foreach (preg_split('/\r\n|\r|\n/', $rawMap) as $line) {
                    $rawLine = trim((string) $line);
                    if ($rawLine === '' || str_starts_with($rawLine, '#') || str_starts_with($rawLine, '//')) {
                        continue;
                    }

                    $separator = str_contains($rawLine, '=') ? '=' : (str_contains($rawLine, ':') ? ':' : null);
                    if (! $separator) {
                        continue;
                    }

                    [$rawKey, $rawValue] = array_pad(explode($separator, $rawLine, 2), 2, '');
                    $translationKey = trim((string) $rawKey);
                    $translationValue = trim((string) $rawValue);

                    if ($translationKey === '' || $translationValue === '' || ! str_contains($translationKey, '.')) {
                        continue;
                    }

                    $bucket[$translationKey] = $translationValue;
                }
            };

            $explicitOverrides = SiteSetting::query()
                ->where('key', 'like', 'copy.trans.%')
                ->whereNotNull('value')
                ->pluck('value', 'key')
                ->all();

            foreach ($explicitOverrides as $settingKey => $value) {
                $settingKey = trim((string) $settingKey);
                $translationValue = trim((string) $value);

                if ($settingKey === '' || $translationValue === '') {
                    continue;
                }

                if (preg_match('/^copy\.trans\.(.+)\.(id|en)$/', $settingKey, $matches) === 1) {
                    $translationKey = trim((string) ($matches[1] ?? ''));
                    $locale = trim((string) ($matches[2] ?? ''));
                    if ($translationKey !== '' && str_contains($translationKey, '.') && isset($lines[$locale])) {
                        $lines[$locale][$translationKey] = $translationValue;
                    }
                    continue;
                }

                $translationKey = trim(Str::after($settingKey, 'copy.trans.'));
                if ($translationKey === '' || ! str_contains($translationKey, '.')) {
                    continue;
                }

                $lines['*'][$translationKey] = $translationValue;
            }

            $parseLines(SiteSetting::query()->where('key', 'copy.translation_overrides')->value('value'), $lines['*']);
            $parseLines(SiteSetting::query()->where('key', 'copy.translation_overrides.id')->value('value'), $lines['id']);
            $parseLines(SiteSetting::query()->where('key', 'copy.translation_overrides.en')->value('value'), $lines['en']);

            return $lines;
        });

        if (! is_array($overridesByLocale)) {
            return;
        }

        $fallbackLocale = (string) config('app.fallback_locale', 'id');
        $locales = array_values(array_unique(array_filter([
            app()->getLocale(),
            (string) config('app.locale'),
            $fallbackLocale,
            'id',
            'en',
        ])));

        foreach ($locales as $locale) {
            $localeOverrides = [];

            if ($locale === $fallbackLocale && isset($overridesByLocale['*']) && is_array($overridesByLocale['*'])) {
                $localeOverrides = $overridesByLocale['*'];
            }

            if (isset($overridesByLocale[$locale]) && is_array($overridesByLocale[$locale])) {
                $localeOverrides = array_merge($localeOverrides, $overridesByLocale[$locale]);
            }

            if ($localeOverrides !== []) {
                $translator = app('translator');
                $groups = collect(array_keys($localeOverrides))
                    ->map(static fn ($key) => explode('.', (string) $key, 2)[0] ?? '')
                    ->filter(static fn ($group) => $group !== '')
                    ->unique()
                    ->values();

                foreach ($groups as $group) {
                    $translator->load('*', (string) $group, $locale);
                }

                $translator->addLines($localeOverrides, $locale);
            }
        }
    }
}
