<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckWebsiteMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            app()->environment('testing')
            || $request->is('admin')
            || $request->is('admin/*')
            || $request->is('up')
            || $request->is('assets/*')
            || $request->is('build/*')
            || $request->is('storage/*')
            || $request->is('favicon.ico')
        ) {
            return $next($request);
        }

        try {
            $enabled = filter_var(site_setting('site.maintenance_enabled', '0'), FILTER_VALIDATE_BOOL);
        } catch (Throwable) {
            $enabled = false;
        }

        if (! $enabled) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Manake sedang maintenance. Silakan cek kembali beberapa saat lagi.'),
            ], 503);
        }

        return response()
            ->view('errors.503', [], 503)
            ->header('Retry-After', '900');
    }
}
