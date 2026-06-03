<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\AdminSuper;
use App\Http\Middleware\CheckWebsiteMaintenance;
use App\Http\Middleware\DisableAuthenticatedCache;
use App\Http\Middleware\EnsureAuthenticatedForAccountFeature;

use App\Http\Middleware\EnsureProfileCompleted;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\RedirectToCanonicalDomain;
use App\Http\Middleware\ResolveRuntimeUrls;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetTheme;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
};

$isVercelRuntime = getenv('VERCEL') !== false;
$vercelStoragePath = null;

if ($isVercelRuntime) {
    $sanitizeEnv = static function (?string $value): string {
        return trim((string) $value, " \t\n\r\0\x0B\"'");
    };

    $appUrl = $sanitizeEnv(getenv('APP_URL'));
    $vercelUrl = $sanitizeEnv(getenv('VERCEL_URL'));
    if ($appUrl === '' && $vercelUrl !== '') {
        $setRuntimeEnv('APP_URL', 'https://'.$vercelUrl);
    }

    $appKey = getenv('APP_KEY');
    if (! $isValidAppKey(is_string($appKey) ? $appKey : null)) {
        throw new RuntimeException('APP_KEY must be configured with a valid 32-byte key before running Manake on Vercel.');
    }

    $vercelStoragePath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'manake-storage';

    foreach ([
        $vercelStoragePath,
        $vercelStoragePath.'/framework',
        $vercelStoragePath.'/framework/cache',
        $vercelStoragePath.'/framework/cache/data',
        $vercelStoragePath.'/framework/sessions',
        $vercelStoragePath.'/framework/views',
        $vercelStoragePath.'/logs',
    ] as $directory) {
        if (! is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }
    }

    $setRuntimeEnv('LARAVEL_STORAGE_PATH', $vercelStoragePath);
    $setRuntimeEnv('VIEW_COMPILED_PATH', $vercelStoragePath.'/framework/views');
    $setRuntimeEnv('APP_SERVICES_CACHE', $vercelStoragePath.'/framework/cache/services.php');
    $setRuntimeEnv('APP_PACKAGES_CACHE', $vercelStoragePath.'/framework/cache/packages.php');
    $setRuntimeEnv('APP_CONFIG_CACHE', $vercelStoragePath.'/framework/cache/config.php');
    $setRuntimeEnv('APP_ROUTES_CACHE', $vercelStoragePath.'/framework/cache/routes-v7.php');
    $setRuntimeEnv('APP_EVENTS_CACHE', $vercelStoragePath.'/framework/cache/events.php');
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

            'ensure.profile.completed' => EnsureProfileCompleted::class,
            'auth.feature' => EnsureAuthenticatedForAccountFeature::class,
            'role' => RoleMiddleware::class,
            'admin.auth' => AdminAuthenticate::class,
            'admin.super' => AdminSuper::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/midtrans/callback',
        ]);

        $middleware->web(append: [
            RedirectToCanonicalDomain::class,
            ResolveRuntimeUrls::class,
            ForceHttps::class,
            SecurityHeaders::class,
            DisableAuthenticatedCache::class,
            SetTheme::class,
            SetLocale::class,
            CheckWebsiteMaintenance::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (getenv('VERCEL') !== false) {
                $isProduction = config('app.env') === 'production';
                $allowProdDebug = filter_var(env('MANAKE_ALLOW_PRODUCTION_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
                $debugKey = env('DEBUG_KEY');
                $hasValidDebugKey = ! empty($debugKey) && (
                    $request->query('_debug') === $debugKey ||
                    $request->header('X-Manake-Debug') === $debugKey
                );

                $shouldDebug = false;
                if (! $isProduction) {
                    $shouldDebug = config('app.debug') || $hasValidDebugKey;
                } else {
                    $shouldDebug = $allowProdDebug && $hasValidDebugKey;
                }

                if ($shouldDebug) {
                    return response()->json([
                        'error_message' => $exception->getMessage(),
                        'error_class' => get_class($exception),
                        'error_file' => $exception->getFile(),
                        'error_line' => $exception->getLine(),
                        'error_trace' => collect($exception->getTrace())->map(fn ($t) => [
                            'file' => $t['file'] ?? null,
                            'line' => $t['line'] ?? null,
                            'function' => $t['function'] ?? null,
                            'class' => $t['class'] ?? null,
                        ])->take(20),
                    ], 500);
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Internal Server Error',
                    ], 500);
                }

                return null;
            }

            return null;
        });

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
