<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // global middleware
    ];

    protected $middlewareGroups = [
        'web' => [
            // ...
        ],

        'api' => [
            // ...
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.feature' => \App\Http\Middleware\EnsureAuthenticatedForAccountFeature::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'ensure.profile.completed' => \App\Http\Middleware\EnsureProfileCompleted::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'admin.auth' => \App\Http\Middleware\AdminAuthenticate::class,
        'admin.super' => \App\Http\Middleware\AdminSuper::class,
    ];
}
