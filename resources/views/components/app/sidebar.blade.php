@props([
    'logoUrl' => null,
    'brandName' => 'Manake',
    'categories' => collect(),
    'displayName' => null,
    'userInitial' => 'U',
    'isAuthenticated' => false,
])

@php
    $categories = collect($categories ?? []);
    $displayName = $displayName ?: __('app.user.generic');
    $locale = app()->getLocale();
    $currentTheme = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    if (! in_array($currentTheme, ['system', 'dark', 'light'], true)) {
        $currentTheme = 'light';
    }

    $isCatalogRoute = request()->routeIs('home')
        || request()->routeIs('catalog')
        || request()->routeIs('categories.*')
        || request()->routeIs('category.show')
        || request()->routeIs('product.show');
    $isAvailabilityRoute = request()->routeIs('availability.board');
    $items = [
        [
            'key' => 'catalog',
            'label' => __('ui.nav.catalog'),
            'url' => route('catalog'),
            'active' => $isCatalogRoute,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" /><rect x="3" y="14" width="7" height="7" rx="1.5" /></svg>',
        ],
        [
            'key' => 'availability',
            'label' => __('ui.nav.check_availability'),
            'url' => route('availability.board'),
            'active' => $isAvailabilityRoute,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2" /><path d="M8 2v4" /><path d="M16 2v4" /><path d="M3 10h18" /></svg>',
        ],
    ];

    if ($isAuthenticated) {
        $items[] = [
            'key' => 'orders',
            'label' => __('ui.nav.my_orders'),
            'url' => route('booking.history'),
            'active' => request()->routeIs('booking.*') || request()->routeIs('overview') || request()->routeIs('dashboard') || request()->routeIs('account.orders.*'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3" /><circle cx="12" cy="12" r="9" /></svg>',
        ];
    }

    $activeCategorySlug = (string) request()->query('category', request()->route('slug', ''));
    $submenuEnabledRaw = strtolower(trim((string) setting('catalog.sidebar_submenu_enabled', '1')));
    $submenuEnabled = ! in_array($submenuEnabledRaw, ['0', 'false', 'off', 'no', 'tidak'], true);
    $submenuDurationMs = max(120, min(1200, (int) setting('catalog.sidebar_submenu_duration_ms', 220)));
@endphp

<aside
    x-data="{
        catalogSubmenuOpen: false,
        catalogSubmenuEnabled: {{ $submenuEnabled ? 'true' : 'false' }},
        catalogToggle() {
            if (!this.catalogSubmenuEnabled) return;
            this.catalogSubmenuOpen = !this.catalogSubmenuOpen;
        }
    }"
    data-manake-sidebar="app"
    class="group/sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-visible border-r border-slate-200 bg-white/92 px-2 py-4 shadow-sm backdrop-blur transition-[width,transform,box-shadow] duration-200 ease-out lg:w-[5.35rem] lg:translate-x-0 lg:hover:w-[17.5rem] lg:focus-within:w-[17.5rem] lg:hover:shadow-2xl lg:focus-within:shadow-2xl"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    <div class="flex h-16 items-center justify-between px-3">
        <a
            href="{{ route('home') }}"
            title="{{ $brandName }}"
            aria-label="{{ $brandName }}"
            class="manake-sidebar-brand-link flex w-full items-center justify-center overflow-hidden rounded-xl px-2 py-2 text-slate-900 lg:px-2"
        >
            <span class="manake-sidebar-brand__mark" aria-hidden="true">
                <x-brand.image
                    light="MANAKE-FAV-M.png"
                    dark="MANAKE-FAV-M-white.png"
                    :alt="$brandName"
                    img-class="manake-brand-mark-image"
                />
            </span>
            <span class="manake-sidebar-brand__wordmark">
                <x-brand.image
                    light="manake-logo-blue.png"
                    dark="manake-logo-white.png"
                    :alt="$brandName"
                    img-class="manake-brand-wordmark-image"
                />
            </span>
        </a>
        <button data-ui-icon-button class="rounded-lg p-1.5 lg:hidden" type="button" @click="sidebarOpen = false; shellPrefsOpen = false" aria-label="{{ __('ui.actions.close') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <nav class="mt-3 space-y-1 px-1">
        @foreach ($items as $item)
            @php
                $isCatalogMenu = ($item['key'] ?? '') === 'catalog';
            @endphp
            @if ($isCatalogMenu && $categories->isNotEmpty())
                <div class="space-y-1" @click.outside="catalogSubmenuOpen = false">
                    <div class="flex items-center gap-1">
                        <a
                            href="{{ $item['url'] }}"
                            aria-label="{{ $item['label'] }}"
                            data-nav-item
                            data-nav-active="{{ $item['active'] ? 'true' : 'false' }}"
                            class="flex h-11 min-w-0 flex-1 items-center rounded-2xl px-3 transition lg:justify-center lg:px-0 lg:group-hover/sidebar:justify-start lg:group-hover/sidebar:px-3 lg:group-focus-within/sidebar:justify-start lg:group-focus-within/sidebar:px-3 {{ $item['active'] ? '!text-white' : '!text-slate-700' }}"
                        >
                            <span data-nav-icon>{!! $item['icon'] !!}</span>
                            <span class="truncate text-sm font-semibold transition-all duration-200 lg:ml-0 lg:pointer-events-none lg:max-w-0 lg:overflow-hidden lg:whitespace-nowrap lg:opacity-0 lg:-translate-x-2 lg:group-hover/sidebar:ml-3 lg:group-hover/sidebar:pointer-events-auto lg:group-hover/sidebar:max-w-[12rem] lg:group-hover/sidebar:opacity-100 lg:group-hover/sidebar:translate-x-0 lg:group-focus-within/sidebar:ml-3 lg:group-focus-within/sidebar:pointer-events-auto lg:group-focus-within/sidebar:max-w-[12rem] lg:group-focus-within/sidebar:opacity-100 lg:group-focus-within/sidebar:translate-x-0">{{ $item['label'] }}</span>
                        </a>
                        <button
                            type="button"
                            x-cloak
                            x-show="catalogSubmenuEnabled"
                            data-ui-icon-button
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl !text-slate-700 lg:hidden lg:group-hover/sidebar:inline-flex lg:group-focus-within/sidebar:inline-flex"
                            @click.prevent="catalogToggle()"
                            :aria-expanded="catalogSubmenuOpen.toString()"
                            aria-label="{{ __('ui.nav.category') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="catalogSubmenuOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <div
                        x-cloak
                        x-show="catalogSubmenuEnabled && catalogSubmenuOpen"
                        x-transition:enter="transition ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-1 max-h-0"
                        x-transition:enter-end="opacity-100 translate-y-0 max-h-80"
                        x-transition:leave="transition ease-in"
                        x-transition:leave-start="opacity-100 translate-y-0 max-h-80"
                        x-transition:leave-end="opacity-0 -translate-y-1 max-h-0"
                        data-nav-panel
                        class="overflow-hidden rounded-xl border border-slate-200/80 bg-slate-50/90 p-2 lg:ml-10"
                        style="transition-duration: {{ $submenuDurationMs }}ms;"
                    >
                        <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('ui.nav.category') }}</p>
                        <div class="space-y-1">
                            @foreach ($categories as $category)
                                @php
                                    $isCategoryActive = $activeCategorySlug !== '' && $activeCategorySlug === (string) $category->slug;
                                @endphp
                                <a
                                    href="{{ route('catalog', ['category' => $category->slug]) }}"
                                    data-ui-chip-option
                                    data-ui-active="{{ $isCategoryActive ? 'true' : 'false' }}"
                                    class="flex items-center justify-between rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition {{ $isCategoryActive ? '!text-slate-900' : '!text-slate-700' }}"
                                >
                                    <span class="truncate">{{ $category->name }}</span>
                                    <span class="ml-2 inline-flex h-1.5 w-1.5 rounded-full {{ $isCategoryActive ? 'bg-blue-600' : 'bg-slate-300' }}"></span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <a
                    href="{{ $item['url'] }}"
                    aria-label="{{ $item['label'] }}"
                    data-nav-item
                    data-nav-active="{{ $item['active'] ? 'true' : 'false' }}"
                    @click="shellPrefsOpen = false"
                    @if (isset($item['modal']))
                        @click.prevent="openAuthModal('{{ $item['modal'] }}')"
                    @endif
                    class="flex h-11 items-center rounded-2xl px-3 transition lg:justify-center lg:px-0 lg:group-hover/sidebar:justify-start lg:group-hover/sidebar:px-3 lg:group-focus-within/sidebar:justify-start lg:group-focus-within/sidebar:px-3 {{ $item['active'] ? '!text-white' : '!text-slate-700' }}"
                >
                    <span data-nav-icon>{!! $item['icon'] !!}</span>
                    <span class="text-sm font-semibold transition-all duration-200 lg:ml-0 lg:pointer-events-none lg:max-w-0 lg:overflow-hidden lg:whitespace-nowrap lg:opacity-0 lg:-translate-x-2 lg:group-hover/sidebar:ml-3 lg:group-hover/sidebar:pointer-events-auto lg:group-hover/sidebar:max-w-[12rem] lg:group-hover/sidebar:opacity-100 lg:group-hover/sidebar:translate-x-0 lg:group-focus-within/sidebar:ml-3 lg:group-focus-within/sidebar:pointer-events-auto lg:group-focus-within/sidebar:max-w-[12rem] lg:group-focus-within/sidebar:opacity-100 lg:group-focus-within/sidebar:translate-x-0">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach

        <div class="relative pt-2" @click.outside="shellPrefsOpen = false">
            <button
                type="button"
                data-nav-item
                :data-nav-active="shellPrefsOpen ? 'true' : 'false'"
                data-settings-toggle
                class="flex h-11 w-full items-center rounded-2xl px-3 !text-slate-700 transition lg:justify-center lg:px-0 lg:group-hover/sidebar:justify-start lg:group-hover/sidebar:px-3 lg:group-focus-within/sidebar:justify-start lg:group-focus-within/sidebar:px-3"
                @click="shellPrefsOpen = !shellPrefsOpen"
                :aria-expanded="shellPrefsOpen.toString()"
                aria-label="{{ __('ui.nav.settings') }}"
            >
                <span data-nav-icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3 1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8 1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" /></svg>
                </span>
                <span class="text-sm font-semibold transition-all duration-200 lg:ml-0 lg:pointer-events-none lg:max-w-0 lg:overflow-hidden lg:whitespace-nowrap lg:opacity-0 lg:-translate-x-2 lg:group-hover/sidebar:ml-3 lg:group-hover/sidebar:pointer-events-auto lg:group-hover/sidebar:max-w-[12rem] lg:group-hover/sidebar:opacity-100 lg:group-hover/sidebar:translate-x-0 lg:group-focus-within/sidebar:ml-3 lg:group-focus-within/sidebar:pointer-events-auto lg:group-focus-within/sidebar:max-w-[12rem] lg:group-focus-within/sidebar:opacity-100 lg:group-focus-within/sidebar:translate-x-0">{{ __('ui.nav.settings') }}</span>
            </button>

            <div
                x-cloak
                x-show="shellPrefsOpen"
                x-transition.origin.left.top
                @click.stop
                data-settings-popout
                class="fixed inset-x-4 bottom-4 z-[90] w-auto max-w-[22rem] overflow-y-auto pointer-events-auto lg:absolute lg:inset-auto lg:bottom-auto lg:left-[calc(100%+1rem)] lg:top-1/2 lg:z-[120] lg:mt-0 lg:w-[19rem] lg:max-w-[calc(100vw-8rem)] lg:-translate-y-1/2"
                style="max-height: calc(100vh - 2rem);"
            >
                <x-preferences.popover
                    :locale="$locale"
                    :current-theme="$currentTheme"
                    :redirect="url()->full()"
                />
            </div>
        </div>
    </nav>

    <div class="mt-auto border-t border-slate-200 pt-4">
    @if ($isAuthenticated)
        <div class="space-y-2">
            <a
                href="{{ route('profile.complete') }}"
                title="{{ __('ui.nav.my_profile') }}"
                aria-label="{{ __('ui.nav.my_profile') }}"
                data-nav-item
                data-nav-active="{{ request()->routeIs('profile.*') ? 'true' : 'false' }}"
                class="flex items-center gap-3 rounded-xl px-2 py-2 !text-slate-700 transition lg:justify-center lg:gap-0 lg:group-hover/sidebar:justify-start lg:group-hover/sidebar:gap-3 lg:group-focus-within/sidebar:justify-start lg:group-focus-within/sidebar:gap-3"
            >
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-semibold text-white shadow-sm">{{ $userInitial }}</span>
                <span class="text-sm font-semibold transition-all duration-200 lg:ml-0 lg:pointer-events-none lg:max-w-0 lg:overflow-hidden lg:whitespace-nowrap lg:opacity-0 lg:-translate-x-2 lg:group-hover/sidebar:ml-3 lg:group-hover/sidebar:pointer-events-auto lg:group-hover/sidebar:max-w-[10rem] lg:group-hover/sidebar:opacity-100 lg:group-hover/sidebar:translate-x-0 lg:group-focus-within/sidebar:ml-3 lg:group-focus-within/sidebar:pointer-events-auto lg:group-focus-within/sidebar:max-w-[10rem] lg:group-focus-within/sidebar:opacity-100 lg:group-focus-within/sidebar:translate-x-0">{{ $displayName }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    title="{{ __('ui.nav.logout') }}"
                    data-nav-item
                    data-nav-active="false"
                    class="flex h-11 w-full items-center rounded-xl px-3 !text-slate-700 transition lg:justify-center lg:px-0 lg:group-hover/sidebar:justify-start lg:group-hover/sidebar:px-3 lg:group-focus-within/sidebar:justify-start lg:group-focus-within/sidebar:px-3"
                >
                    <span data-nav-icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                    </span>
                    <span class="text-sm font-semibold transition-all duration-200 lg:ml-0 lg:pointer-events-none lg:max-w-0 lg:overflow-hidden lg:whitespace-nowrap lg:opacity-0 lg:-translate-x-2 lg:group-hover/sidebar:ml-3 lg:group-hover/sidebar:pointer-events-auto lg:group-hover/sidebar:max-w-[10rem] lg:group-hover/sidebar:opacity-100 lg:group-hover/sidebar:translate-x-0 lg:group-focus-within/sidebar:ml-3 lg:group-focus-within/sidebar:pointer-events-auto lg:group-focus-within/sidebar:max-w-[10rem] lg:group-focus-within/sidebar:opacity-100 lg:group-focus-within/sidebar:translate-x-0">{{ __('ui.nav.logout') }}</span>
                </button>
            </form>
        </div>
    @endif
    </div>

    <span class="absolute -right-3 top-0 hidden h-full w-3 lg:block" aria-hidden="true"></span>
</aside>
