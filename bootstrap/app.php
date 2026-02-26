<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use App\Http\Middleware\EnsureOtpVerified;
use App\Http\Middleware\EnsureProfileCompleted;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetTheme;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\AdminSuper;
use App\Http\Middleware\EnsureAuthenticatedForAccountFeature;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
            'logout',
            'admin/logout',
            'payment/callback',
            'midtrans/callback',
            'api/midtrans/callback',
        ]);

        $middleware->web(append: [
            ForceHttps::class,
            SetTheme::class,
            SetLocale::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('ui.auth.session_expired'),
                ], 419);
            }

            if ($request->is('logout')) {
                return redirect()
                    ->route('home')
                    ->with('error', __('ui.auth.session_expired_logout'));
            }

            return redirect()
                ->back()
                ->withInput($request->except('_token'))
                ->with('error', __('ui.auth.session_expired'));
        });
    })->create();
