<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ResolveRuntimeUrls
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->runningInConsole()) {
            return $next($request);
        }

        $runtimeRoot = rtrim($request->getSchemeAndHttpHost(), '/');

        if ($runtimeRoot !== '') {
            URL::forceRootUrl($runtimeRoot);
            URL::forceScheme($request->getScheme());

            config([
                'app.url' => $runtimeRoot,
                'filesystems.disks.public.url' => $runtimeRoot . '/storage',
            ]);
        }

        return $next($request);
    }
}
