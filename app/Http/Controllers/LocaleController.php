<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocaleController extends Controller
{
    public function switch(string $locale, Request $request): RedirectResponse|JsonResponse
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

        $target = $this->resolveRedirectTarget($request, $request->query('redirect'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()
                ->json([
                    'locale' => $locale,
                    'redirect' => $target,
                ])
                ->withCookie(cookie('locale', $locale, 60 * 24 * 30));
        }

        return redirect()->to($target)->withCookie(cookie('locale', $locale, 60 * 24 * 30));
    }

    private function resolveRedirectTarget(Request $request, mixed $redirectTarget): string
    {
        $fallback = url()->previous() ?: route('home');

        if (! is_string($redirectTarget) || trim($redirectTarget) === '') {
            return $fallback;
        }

        $candidate = trim($redirectTarget);
        if (Str::startsWith($candidate, '/')) {
            return $candidate;
        }

        $candidateParts = parse_url($candidate);
        if (! is_array($candidateParts) || empty($candidateParts['host'])) {
            return $fallback;
        }

        $allowedHosts = collect([
            parse_url($request->getSchemeAndHttpHost(), PHP_URL_HOST),
            parse_url((string) config('app.url'), PHP_URL_HOST),
        ])->filter()->values()->all();

        return in_array($candidateParts['host'], $allowedHosts, true) ? $candidate : $fallback;
    }
}
