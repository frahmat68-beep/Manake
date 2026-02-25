<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ThemeController extends Controller
{
    public function switch(string $theme, Request $request): RedirectResponse
    {
        if (! in_array($theme, ['system', 'dark', 'light'], true)) {
            $theme = 'light';
        }

        $request->session()->put('theme', $theme);

        if ($request->user() && schema_column_exists_cached('users', 'preferred_theme')) {
            $request->user()->forceFill([
                'preferred_theme' => $theme,
            ])->save();
        }

        $redirectTarget = $request->query('redirect');
        $fallback = url()->previous() ?: route('home');
        $target = $fallback;

        if (is_string($redirectTarget) && trim($redirectTarget) !== '') {
            $candidate = trim($redirectTarget);
            $appUrl = rtrim(config('app.url') ?: $request->getSchemeAndHttpHost(), '/');
            $sameHostAbsolute = Str::startsWith($candidate, $appUrl . '/');
            $relativePath = Str::startsWith($candidate, '/');

            if ($sameHostAbsolute || $relativePath) {
                $target = $candidate;
            }
        }

        return redirect()->to($target)->withCookie(cookie('theme', $theme, 60 * 24 * 30));
    }
}
