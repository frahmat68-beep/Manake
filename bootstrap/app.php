<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureOtpVerified;
use App\Http\Middleware\EnsureProfileCompleted;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetTheme;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\DisableAuthenticatedCache;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ResolveRuntimeUrls;
use App\Http\Middleware\AdminSuper;
use App\Http\Middleware\EnsureAuthenticatedForAccountFeature;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

$isValidAppKey = static function (?string $value): bool {
    $key = trim((string) $value);
    if ($key === '') {
        return false;
    }

    if (str_starts_with($key, 'base64:')) {
        $decoded = base64_decode(substr($key, 7), true);

        return is_string($decoded) && strlen($decoded) === 32;
    }

    return strlen($key) === 32;
};

$setRuntimeEnv = static function (string $key, string $value): void {
    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
};

$isVercelRuntime = getenv('VERCEL') !== false;
$vercelStoragePath = null;

if ($isVercelRuntime) {
    $appUrl = getenv('APP_URL');
    $vercelUrl = getenv('VERCEL_URL');
    if ((! is_string($appUrl) || trim($appUrl) === '') && is_string($vercelUrl) && trim($vercelUrl) !== '') {
        $setRuntimeEnv('APP_URL', 'https://' . trim($vercelUrl));
    }

    $appKey = getenv('APP_KEY');
    if (! $isValidAppKey(is_string($appKey) ? $appKey : null)) {
        $seed = implode('|', array_filter([
            getenv('VERCEL_PROJECT_ID') ?: null,
            getenv('VERCEL_ENV') ?: null,
            getenv('VERCEL_URL') ?: null,
            'manake',
        ]));

        $fallbackAppKey = 'base64:' . base64_encode(hash('sha256', 'manake-app-key|' . $seed, true));
        $setRuntimeEnv('APP_KEY', $fallbackAppKey);
    }

    $vercelStoragePath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'manake-storage';

    foreach ([
        $vercelStoragePath,
        $vercelStoragePath . '/framework',
        $vercelStoragePath . '/framework/cache',
        $vercelStoragePath . '/framework/cache/data',
        $vercelStoragePath . '/framework/sessions',
        $vercelStoragePath . '/framework/views',
        $vercelStoragePath . '/logs',
    ] as $directory) {
        if (! is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }
    }

    // Ensure storage-dependent config values resolve to writable runtime paths on Vercel.
    $setRuntimeEnv('LARAVEL_STORAGE_PATH', $vercelStoragePath);
    $setRuntimeEnv('VIEW_COMPILED_PATH', $vercelStoragePath . '/framework/views');
    $setRuntimeEnv('APP_SERVICES_CACHE', $vercelStoragePath . '/framework/cache/services.php');
    $setRuntimeEnv('APP_PACKAGES_CACHE', $vercelStoragePath . '/framework/cache/packages.php');
    $setRuntimeEnv('APP_CONFIG_CACHE', $vercelStoragePath . '/framework/cache/config.php');
    $setRuntimeEnv('APP_ROUTES_CACHE', $vercelStoragePath . '/framework/cache/routes-v7.php');
    $setRuntimeEnv('APP_EVENTS_CACHE', $vercelStoragePath . '/framework/cache/events.php');
}

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) use ($isVercelRuntime): void {
        if ($isVercelRuntime) {
            $middleware->trustProxies(
                at: '*',
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO
            );
        }

        $middleware->alias([
            'otp' => EnsureOtpVerified::class,
            'ensure.profile.completed' => EnsureProfileCompleted::class,
            'auth.feature' => EnsureAuthenticatedForAccountFeature::class,
            'role' => RoleMiddleware::class,
            'admin.auth' => AdminAuthenticate::class,
            'admin.super' => AdminSuper::class,
        ]);

        // Laravel 11/12 CSRF exceptions are configured here (not in app/Http/Middleware/VerifyCsrfToken.php).
        $middleware->validateCsrfTokens(except: [
            'payment/callback',
            'midtrans/callback',
            'api/midtrans/callback',
        ]);

        $middleware->web(append: [
            ResolveRuntimeUrls::class,
            ForceHttps::class,
            SecurityHeaders::class,
            DisableAuthenticatedCache::class,
            SetTheme::class,
            SetLocale::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('ui.auth.session_expired'),
                ], 419);
            }

            $message = __('ui.auth.session_expired');
            $redirectTo = url()->previous() ?: route('home');

            if ($request->is('logout') || $request->is('admin/logout')) {
                $message = __('ui.auth.session_expired_logout');
                $redirectTo = $request->is('admin/*') ? route('admin.login') : route('home');
            } elseif ($request->is('login') || $request->is('register') || $request->is('forgot-password') || $request->is('reset-password') || $request->is('password/*')) {
                $redirectTo = route('login');
            } elseif ($request->is('admin/login')) {
                $redirectTo = route('admin.login');
            }

            return redirect()
                ->to($redirectTo)
                ->withInput($request->except(['_token', 'password', 'password_confirmation', 'current_password']))
                ->with('error', $message);
        });
    })->create();

return $app;
