<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = null;

        $user = Auth::guard('web')->user();
        if ($user && Schema::hasColumn('users', 'preferred_locale')) {
            $userLocale = $user->preferred_locale;
            if (in_array($userLocale, ['id', 'en'], true)) {
                $locale = $userLocale;
            }
        }

        if (! $locale) {
            $locale = $request->session()->get('locale');
        }

        if (! $locale) {
            $locale = $request->cookie('locale', config('app.locale', 'id'));
        }

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);

        return $next($request);
    }
}
