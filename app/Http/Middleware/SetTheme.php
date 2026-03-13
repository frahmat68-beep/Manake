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
        $themeSourceExplicit = false;

        $user = Auth::guard('web')->user();
        if ($user && schema_column_exists_cached('users', 'preferred_theme')) {
            $userTheme = $user->preferred_theme;
            if (in_array($userTheme, $allowed, true)) {
                $theme = $userTheme;
                $themeSourceExplicit = true;
            }
        }

        if (! $theme) {
            $fromSession = $request->session()->get('theme');
            if (in_array($fromSession, $allowed, true)) {
                $theme = $fromSession;
                $themeSourceExplicit = true;
            }
        }

        if (! $theme) {
            $fromCookie = $request->cookie('theme');
            if (in_array($fromCookie, $allowed, true)) {
                $theme = $fromCookie;
                $themeSourceExplicit = true;
            }
        }

        $theme = $theme ?: 'light';
        $resolvedTheme = match ($theme) {
            'dark' => 'dark',
            'light' => 'light',
            default => in_array($request->cookie('theme_resolved'), ['dark', 'light'], true)
                ? $request->cookie('theme_resolved')
                : 'light',
        };

        $request->attributes->set('theme_preference', $theme);
        $request->attributes->set('theme_preference_explicit', $themeSourceExplicit);
        $request->attributes->set('theme_resolved', $resolvedTheme);
        View::share('themePreference', $theme);
        View::share('themePreferenceExplicit', $themeSourceExplicit);
        View::share('themeResolved', $resolvedTheme);

        return $next($request);
    }
}
