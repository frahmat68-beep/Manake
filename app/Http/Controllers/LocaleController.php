<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class LocaleController extends Controller
{
    public function switch(string $locale, Request $request): RedirectResponse
    {
        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        $request->session()->put('locale', $locale);

        if ($request->user() && schema_column_exists_cached('users', 'preferred_locale')) {
            $request->user()->forceFill([
                'preferred_locale' => $locale,
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

        return redirect()->to($target)->withCookie(cookie('locale', $locale, 60 * 24 * 30));
    }
}
