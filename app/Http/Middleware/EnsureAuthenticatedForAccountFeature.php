<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAuthenticatedForAccountFeature
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        return redirect()
            ->guest(route('login'))
            ->with('error', __('Login dulu untuk akses fitur ini.'));
    }
}
