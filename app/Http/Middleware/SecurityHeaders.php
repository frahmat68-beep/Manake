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
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $vercelEnv = (string) ($_SERVER['VERCEL_ENV'] ?? $_ENV['VERCEL_ENV'] ?? '');
        $allowVercelPreviewFeedback = $vercelEnv !== '' && $vercelEnv !== 'production';
        $previewFeedbackSource = $allowVercelPreviewFeedback ? ' https://vercel.live' : '';

        $csp = "default-src 'self'; ";
        $csp .= "base-uri 'self'; ";
        $csp .= "object-src 'none'; ";
        $csp .= "form-action 'self' https://*.midtrans.com; ";
        $csp .= "frame-ancestors 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.midtrans.com https://cdn.jsdelivr.net https://unpkg.com https://accounts.google.com{$previewFeedbackSource}; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com data:; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "media-src 'self'; ";
        $csp .= "connect-src 'self' https://*.midtrans.com https://cdn.jsdelivr.net https://unpkg.com{$previewFeedbackSource}; ";
        $csp .= "frame-src 'self' https://*.midtrans.com https://accounts.google.com https://www.google.com https://maps.google.com{$previewFeedbackSource}; ";
        $csp .= "manifest-src 'self'; ";

        if (app()->environment('production')) {
            $csp .= 'upgrade-insecure-requests; ';
        }

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
