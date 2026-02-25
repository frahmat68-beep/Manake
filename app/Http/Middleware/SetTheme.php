<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class SetTheme
{
    public function handle(Request $request, Closure $next)
    {
        $allowed = ['system', 'dark', 'light'];
        $theme = null;

        $user = Auth::guard('web')->user();
        if ($user && schema_column_exists_cached('users', 'preferred_theme')) {
            $userTheme = $user->preferred_theme;
            if (in_array($userTheme, $allowed, true)) {
                $theme = $userTheme;
            }
        }

        if (! $theme) {
            $fromSession = $request->session()->get('theme');
            if (in_array($fromSession, $allowed, true)) {
                $theme = $fromSession;
            }
        }

        if (! $theme) {
            $fromCookie = $request->cookie('theme');
            if (in_array($fromCookie, $allowed, true)) {
                $theme = $fromCookie;
            }
        }

        $theme = $theme ?: 'light';

        $request->attributes->set('theme_preference', $theme);
        View::share('themePreference', $theme);

        return $next($request);
    }
}
