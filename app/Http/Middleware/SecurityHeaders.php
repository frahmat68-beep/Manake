<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.midtrans.com https://cdn.jsdelivr.net https://unpkg.com https://accounts.google.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com data:; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "connect-src 'self' https://*.midtrans.com https://cdn.jsdelivr.net https://unpkg.com; ";
        $csp .= "frame-src 'self' https://*.midtrans.com https://accounts.google.com; ";

        $response->headers->set('Content-Security-Policy', $csp);

        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
