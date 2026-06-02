<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToCanonicalDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalUrl = rtrim((string) config('app.canonical_url'), '/');
        $canonicalHost = parse_url($canonicalUrl, PHP_URL_HOST);

        if (! $canonicalUrl || ! $canonicalHost) {
            return $next($request);
        }

        $redirectHosts = collect(explode(',', (string) config('app.canonical_redirect_hosts')))
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
