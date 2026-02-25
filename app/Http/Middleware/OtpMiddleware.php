<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OtpMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! config('security.otp_required')) {
            return $next($request);
        }

        if (! schema_column_exists_cached('users', 'is_otp_verified')) {
            return $next($request);
        }

        if (! (bool) auth()->user()->is_otp_verified) {
            return redirect()->route('otp.form');
        }

        return $next($request);
    }
}
