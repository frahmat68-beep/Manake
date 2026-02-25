<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $locale = app()->getLocale();
        $theme = $request->attributes->get('theme_preference', 'dark');

        if ($request->user() && schema_column_exists_cached('users', 'preferred_locale')) {
            $userLocale = $request->user()->preferred_locale;
            if (in_array($userLocale, ['id', 'en'], true)) {
                $locale = $userLocale;
            }
        }

        if ($request->user() && schema_column_exists_cached('users', 'preferred_theme')) {
            $userTheme = $request->user()->preferred_theme;
            if (in_array($userTheme, ['system', 'dark', 'light'], true)) {
                $theme = $userTheme;
            }
        }

        return view('settings.index', [
            'locale' => $locale,
            'theme' => $theme,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'in:id,en'],
            'theme' => ['required', 'in:system,dark,light'],
        ]);

        $request->session()->put('locale', $data['locale']);
        $request->session()->put('theme', $data['theme']);

        if ($request->user()) {
            $update = [];
            if (schema_column_exists_cached('users', 'preferred_locale')) {
                $update['preferred_locale'] = $data['locale'];
            }
            if (schema_column_exists_cached('users', 'preferred_theme')) {
                $update['preferred_theme'] = $data['theme'];
            }
            if ($update !== []) {
                $request->user()->forceFill($update)->save();
            }
        }

        return back()
            ->with('status', 'settings-updated')
            ->withCookie(cookie('locale', $data['locale'], 60 * 24 * 30))
            ->withCookie(cookie('theme', $data['theme'], 60 * 24 * 30));
    }
}
