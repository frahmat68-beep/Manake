@php
    $brandName = site_setting('brand.name', 'Manake');
    $assetWithVersion = static function (string $file): string {
        return site_asset($file);
    };
    $locale = app()->getLocale();
    $currentTheme = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    if (! in_array($currentTheme, ['system', 'dark', 'light'], true)) {
        $currentTheme = 'light';
    }
    $searchQuery = request('q', '');
    $categories = collect($navCategories ?? [])
        ->filter(fn ($category) => ! empty($category->slug ?? null))
        ->map(fn ($category) => [
            'label' => $category->name,
            'url' => route('category.show', $category->slug),
        ])
        ->values();
    $notificationItems = collect([
        [
            'title' => __('app.notifications.ready_title'),
            'body' => __('app.notifications.ready_body'),
        ],
        [
            'title' => __('app.notifications.payment_title'),
            'body' => __('app.notifications.payment_body'),
        ],
        [
            'title' => __('app.notifications.reminder_title'),
            'body' => __('app.notifications.reminder_body'),
        ],
    ]);
@endphp

@php
    $isHome = request()->routeIs('home');
    $homeUrl = route('home');
    $toHomeSection = static function (string $hash) use ($isHome, $homeUrl): string {
        return $isHome ? "#{$hash}" : "{$homeUrl}#{$hash}";
    };
@endphp

<nav class="sticky top-0 z-50 border-b border-[#1A1A1E] bg-[#0A0A0B]/95 backdrop-blur-xl" x-data="{ mobileOpen: false, categoryOpen: false, userOpen: false, prefOpen: false, notifOpen: false }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="flex h-16 items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-6">
                <a href="{{ $homeUrl }}" class="inline-flex shrink-0 items-center">
                    <x-brand.image light="manake-logo-white.png" dark="manake-logo-white.png" :alt="$brandName" img-class="h-8 w-auto" />
                </a>

                <div class="hidden items-center gap-5 text-sm font-semibold text-[#A0A0A8] lg:flex">
                    <div class="relative" @mouseenter="categoryOpen = true" @mouseleave="categoryOpen = false" @click.outside="categoryOpen = false">
                        <div class="inline-flex items-center gap-1">
                            <a href="{{ $toHomeSection('categories') }}" data-ui-text-button class="transition">{{ __('ui.nav.category') }}</a>
                            <button
                                type="button"
                                data-ui-text-button
                                class="inline-flex items-center rounded-full p-0.5 transition"
                                @click="categoryOpen = !categoryOpen"
                                :aria-expanded="categoryOpen.toString()"
                                aria-label="{{ __('ui.nav.category') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="categoryOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div x-cloak x-show="categoryOpen" x-transition.origin.top.left class="card absolute left-0 mt-2 w-56 rounded-xl p-2 shadow-lg">
                            @forelse ($categories as $cat)
                                <a href="{{ $cat['url'] }}" data-ui-menu-item class="block rounded-lg px-3 py-2 text-sm transition">
                                    {{ $cat['label'] }}
                                </a>
                            @empty
                                <a href="{{ route('categories.index') }}" data-ui-menu-item class="block rounded-lg px-3 py-2 text-sm transition">
                                    {{ __('app.category.title') }}
                                </a>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden w-full max-w-md flex-1 xl:block">
                <form method="GET" action="{{ route('catalog') }}" class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#66666C]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.5 3.5a5 5 0 1 0 0 10 5 5 0 0 0 0-10ZM2 8.5a6.5 6.5 0 1 1 11.158 4.157l3.092 3.093a1 1 0 0 1-1.414 1.414l-3.093-3.092A6.5 6.5 0 0 1 2 8.5Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="{{ __('ui.nav.search_placeholder') }}"
                        class="input w-full rounded-xl pl-10 pr-4 py-2.5 text-sm focus:border-[#D4A843] focus:ring-2 focus:ring-[#D4A843]/30 focus:outline-none"
                    >
                </form>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative hidden sm:block" @click.outside="prefOpen = false">
                    <button
                        type="button"
                        data-ui-icon-button
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl transition"
                        @click="prefOpen = !prefOpen"
                        :aria-expanded="prefOpen.toString()"
                        aria-label="{{ __('ui.nav.settings') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3 1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8 1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" />
                        </svg>
                    </button>
                    <div x-cloak x-show="prefOpen" x-transition.origin.top.right class="absolute right-0 mt-2 w-[18.5rem]">
                        <x-preferences.popover :locale="$locale" :current-theme="$currentTheme" :redirect="url()->full()" />
                    </div>
                </div>

                @auth
                    <div class="relative" @click.outside="notifOpen = false">
                        <button
                            type="button"
                            data-ui-icon-button
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl transition"
                            @click="notifOpen = !notifOpen"
                            :aria-expanded="notifOpen.toString()"
                            aria-label="{{ __('ui.nav.notifications') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                <path d="M9 17a3 3 0 0 0 6 0" />
                            </svg>
                            @if (($notificationCount ?? 0) > 0)
                                <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#D4A843] px-1 text-[10px] font-semibold text-[#0A0A0B]">
                                    {{ $notificationCount }}
                                </span>
                            @endif
                        </button>
                        <div x-cloak x-show="notifOpen" x-transition.origin.top.right class="card absolute right-0 mt-2 w-80 rounded-xl p-3 shadow-lg">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#A0A0A8]">{{ __('ui.nav.notifications') }}</p>
                            <div class="mt-2 max-h-72 space-y-2 overflow-y-auto">
                                @forelse ($notificationItems as $notification)
                                    <article class="rounded-xl border border-[#1A1A1E] bg-[#111113] px-3 py-2">
                                        <p class="text-xs font-semibold text-[#E8E8EC]">{{ $notification['title'] }}</p>
                                        <p class="mt-1 text-xs text-[#A0A0A8]">{{ $notification['body'] }}</p>
                                    </article>
                                @empty
                                    <p class="rounded-xl border border-[#1A1A1E] bg-[#111113] px-3 py-2 text-xs text-[#A0A0A8]">{{ __('app.notifications.empty') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endauth

                <a
                    href="{{ route('cart') }}"
                    data-ui-icon-button
                    class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl transition"
                    aria-label="{{ __('ui.nav.cart') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                    </svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#D4A843] px-1 text-[10px] font-semibold text-[#0A0A0B]">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                @guest
                    <a href="{{ route('login') }}" data-ui-text-button class="hidden text-sm font-semibold transition sm:inline">{{ __('ui.nav.login') }}</a>
                    <a href="{{ route('register') }}" class="btn-primary hidden rounded-xl px-3 py-2 text-sm font-semibold transition sm:inline">{{ __('ui.nav.register') }}</a>
                @endguest

                @auth
                    <div class="relative hidden sm:block" @click.outside="userOpen = false">
                        <button
                            type="button"
                            class="btn-secondary inline-flex items-center gap-2 rounded-full px-2 py-1.5 transition"
                            @click="userOpen = !userOpen"
                            :aria-expanded="userOpen.toString()"
                        >
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#D4A843] text-xs font-semibold text-[#0A0A0B]">
                                {{ strtoupper(substr(auth()->user()->display_name ?? 'U', 0, 1)) }}
                            </span>
                            <span class="max-w-[120px] truncate text-sm font-semibold text-slate-700">{{ auth()->user()->display_name ?? __('app.user.generic') }}</span>
                        </button>
                        <div x-cloak x-show="userOpen" x-transition.origin.top.right class="absolute right-0 mt-2 w-52 rounded-xl border border-[#1A1A1E] bg-[#111113] py-2 shadow-lg">
                            <a href="{{ route('cart') }}" data-ui-menu-item class="block px-4 py-2 text-sm">{{ __('ui.nav.cart') }}</a>
                            <a href="{{ route('booking.history') }}" data-ui-menu-item class="block px-4 py-2 text-sm">{{ __('ui.nav.my_orders') }}</a>
                            <a href="{{ route('profile.complete') }}" data-ui-menu-item class="block px-4 py-2 text-sm">{{ __('ui.nav.my_profile') }}</a>
                            <div class="my-1 h-px bg-slate-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" data-ui-menu-item class="w-full px-4 py-2 text-left text-sm">{{ __('ui.nav.logout') }}</button>
                            </form>
                        </div>
                    </div>
                @endauth

                <button type="button" data-ui-icon-button class="inline-flex h-10 w-10 items-center justify-center rounded-xl lg:hidden" @click="mobileOpen = !mobileOpen" aria-label="{{ __('ui.nav.toggle_menu') }}">
                    <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="7" x2="20" y2="7" />
                        <line x1="4" y1="12" x2="20" y2="12" />
                        <line x1="4" y1="17" x2="20" y2="17" />
                    </svg>
                    <svg x-cloak x-show="mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="pb-3 xl:hidden">
            <form method="GET" action="{{ route('catalog') }}" class="relative">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#66666C]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.5 3.5a5 5 0 1 0 0 10 5 5 0 0 0 0-10ZM2 8.5a6.5 6.5 0 1 1 11.158 4.157l3.092 3.093a1 1 0 0 1-1.414 1.414l-3.093-3.092A6.5 6.5 0 0 1 2 8.5Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="q"
                    value="{{ $searchQuery }}"
                    placeholder="{{ __('ui.nav.search_placeholder') }}"
                    class="input w-full rounded-xl pl-10 pr-4 py-2.5 text-sm focus:border-[#D4A843] focus:ring-2 focus:ring-[#D4A843]/30 focus:outline-none"
                >
            </form>
        </div>

        <div x-cloak x-show="mobileOpen" x-transition class="pb-4 lg:hidden">
            <div class="card space-y-3 rounded-2xl p-3">
                    <div class="rounded-xl bg-[#111113] p-3">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">{{ __('ui.nav.category') }}</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @forelse ($categories as $cat)
                            <a href="{{ $cat['url'] }}" class="btn-secondary rounded-xl px-3 py-2 text-center text-sm font-semibold transition">
                                {{ $cat['label'] }}
                            </a>
                        @empty
                            <a href="{{ route('categories.index') }}" class="btn-secondary col-span-2 rounded-xl px-3 py-2 text-center text-sm font-semibold transition">
                                {{ __('app.category.title') }}
                            </a>
                        @endforelse
                    </div>
                </div>

                @guest
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="btn-secondary rounded-xl px-3 py-2 text-center text-sm font-semibold">{{ __('ui.nav.login') }}</a>
                        <a href="{{ route('register') }}" class="btn-primary rounded-xl px-3 py-2 text-center text-sm font-semibold">{{ __('ui.nav.register') }}</a>
                    </div>
                @endguest

                @auth
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('cart') }}" class="btn-secondary rounded-xl px-3 py-2 text-center text-sm font-semibold">{{ __('ui.nav.cart') }}</a>
                        <a href="{{ route('booking.history') }}" class="btn-secondary rounded-xl px-3 py-2 text-center text-sm font-semibold">{{ __('ui.nav.my_orders') }}</a>
                        <a href="{{ route('profile.complete') }}" class="btn-secondary rounded-xl px-3 py-2 text-center text-sm font-semibold">{{ __('ui.nav.my_profile') }}</a>
                        <button type="button" class="btn-secondary rounded-xl px-3 py-2 text-center text-sm font-semibold" @click="notifOpen = !notifOpen; mobileOpen = true">
                            {{ __('ui.nav.notifications') }}
                        </button>
                    </div>
                    <div x-cloak x-show="notifOpen" x-transition class="space-y-2 rounded-xl border border-slate-200 bg-white p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">{{ __('ui.nav.notifications') }}</p>
                        @foreach ($notificationItems as $notification)
                            <article class="rounded-xl border border-slate-200 px-3 py-2">
                                <p class="text-xs font-semibold text-slate-800">{{ $notification['title'] }}</p>
                                <p class="mt-1 text-xs text-slate-600">{{ $notification['body'] }}</p>
                            </article>
                        @endforeach
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary w-full rounded-xl px-3 py-2 text-sm font-semibold">
                            {{ __('ui.nav.logout') }}
                        </button>
                    </form>
                @endauth

                <x-preferences.popover :locale="$locale" :current-theme="$currentTheme" :redirect="url()->full()" class="mt-1" />
            </div>
        </div>
    </div>
</nav>
