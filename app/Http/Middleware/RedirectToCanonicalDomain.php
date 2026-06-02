<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToCanonicalDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalUrl = rtrim((string) (
            config('app.canonical_url')
            ?: env('APP_CANONICAL_URL')
            ?: 'https://www.manake.app'
        ), '/');
        $canonicalHost = parse_url($canonicalUrl, PHP_URL_HOST);

        if (! $canonicalUrl || ! $canonicalHost) {
            return $next($request);
        }

        $configuredRedirectHosts = config('app.canonical_redirect_hosts')
            ?: env('APP_CANONICAL_REDIRECT_HOSTS')
            ?: 'manake.app,manake.vercel.app';

        $redirectHosts = collect(explode(',', (string) $configuredRedirectHosts))
            ->map(fn (string $host): string => strtolower(trim($host)))
            ->filter()
            ->all();

        $currentHost = strtolower($request->getHost());

        if ($currentHost !== strtolower($canonicalHost) && in_array($currentHost, $redirectHosts, true)) {
            $target = $canonicalUrl.'/'.$request->getRequestUri();
            $target = str_replace($canonicalUrl.'//', $canonicalUrl.'/', $target);
            $status = in_array($request->getMethod(), ['GET', 'HEAD'], true) ? 301 : 308;

            return redirect()->away($target, $status);
        }

        return $next($request);
    }
}
