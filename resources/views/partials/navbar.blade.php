@php
    $brandName = site_setting('brand.name', 'Manake');
    $homeUrl = route('home');
    $notificationItems = collect($notificationItems ?? []);
    $currentUser = auth('web')->user();
    $displayName = $currentUser?->display_name ?: ($currentUser?->name ?: __('app.user.generic'));
    $userInitial = strtoupper(substr((string) $displayName, 0, 1));
@endphp

<nav
    class="sticky top-0 z-50 border-b border-[#1A1A1E] bg-[#0A0A0B]/95 text-[#E8E8EC] shadow-[0_16px_60px_rgba(0,0,0,0.18)] backdrop-blur-xl"
    x-data="{ mobileOpen: false, userOpen: false, notifOpen: false }"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ $homeUrl }}" class="inline-flex shrink-0 items-center" aria-label="{{ $brandName }}">
            <x-brand.image light="manake-logo-white.png" dark="manake-logo-white.png" :alt="$brandName" img-class="h-8 w-auto" />
        </a>

        <div class="hidden flex-1 items-center justify-center gap-8 text-sm font-semibold text-[#A0A0A8] lg:flex">
            <a href="{{ route('catalog') }}" class="transition hover:text-[#E8E8EC]">{{ __('Equipment') }}</a>
            <a href="{{ route('availability.board') }}" class="transition hover:text-[#E8E8EC]">{{ __('Cek Alat') }}</a>
            <a href="{{ route('about') }}" class="transition hover:text-[#E8E8EC]">{{ __('Tentang Kami') }}</a>
            <a href="{{ route('rental.rules') }}" class="transition hover:text-[#E8E8EC]">{{ __('Cara Sewa') }}</a>
            <a href="{{ route('contact') }}" class="transition hover:text-[#E8E8EC]">{{ __('Contact') }}</a>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @auth('web')
                <div class="relative" @click.outside="notifOpen = false">
                    <button
                        type="button"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]"
                        @click="notifOpen = !notifOpen; userOpen = false"
                        :aria-expanded="notifOpen.toString()"
                        aria-label="{{ __('ui.nav.notifications') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                            <path d="M9 17a3 3 0 0 0 6 0" />
                        </svg>
                        @if (($notificationCount ?? 0) > 0)
                            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#D4A843] px-1 text-[10px] font-bold text-[#0A0A0B]">
                                {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                            </span>
                        @endif
                    </button>

                    <div x-cloak x-show="notifOpen" x-transition.origin.top.right class="absolute right-0 mt-3 w-80 max-w-[calc(100vw-2rem)] rounded-2xl border border-[#1A1A1E] bg-[#111113] p-3 shadow-2xl">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#D4A843]">{{ __('ui.nav.notifications') }}</p>
                            <a href="{{ route('notifications') }}" class="text-xs font-semibold text-[#A0A0A8] transition hover:text-[#D4A843]">{{ __('ui.nav.view_all') }}</a>
                        </div>
                        <div class="mt-3 max-h-72 space-y-2 overflow-y-auto">
                            @forelse ($notificationItems as $notification)
                                <a href="{{ $notification['url'] ?? route('notifications') }}" class="block rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-2 transition hover:border-[#D4A843]/40">
                                    <p class="line-clamp-1 text-xs font-bold text-[#E8E8EC]">{{ $notification['title'] }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-[#A0A0A8]">{{ $notification['body'] }}</p>
                                </a>
                            @empty
                                <p class="rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-4 text-center text-xs text-[#A0A0A8]">{{ __('app.notifications.empty') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <a
                    href="{{ route('cart') }}"
                    class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]"
                    aria-label="{{ __('ui.nav.cart') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                    </svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#D4A843] px-1 text-[10px] font-bold text-[#0A0A0B]">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    @endif
                </a>

                <div class="relative hidden sm:block" @click.outside="userOpen = false">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 py-1.5 pl-1.5 pr-3 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40"
                        @click="userOpen = !userOpen; notifOpen = false"
                        :aria-expanded="userOpen.toString()"
                    >
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#D4A843] text-xs font-bold text-[#0A0A0B]">{{ $userInitial }}</span>
                        <span class="max-w-[9rem] truncate">{{ $displayName }}</span>
                    </button>
                    <div x-cloak x-show="userOpen" x-transition.origin.top.right class="absolute right-0 mt-3 w-56 rounded-2xl border border-[#1A1A1E] bg-[#111113] p-2 shadow-2xl">
                        <a href="{{ route('booking.history') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold text-[#E8E8EC] transition hover:bg-white/5 hover:text-[#D4A843]">{{ __('ui.nav.my_orders') }}</a>
                        <a href="{{ route('profile') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold text-[#E8E8EC] transition hover:bg-white/5 hover:text-[#D4A843]">{{ __('ui.nav.my_profile') }}</a>
                        <a href="{{ route('settings.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold text-[#E8E8EC] transition hover:bg-white/5 hover:text-[#D4A843]">{{ __('ui.nav.settings') }}</a>
                        <div class="my-1 h-px bg-[#1A1A1E]"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-[#E8E8EC] transition hover:bg-white/5 hover:text-[#D4A843]">{{ __('ui.nav.logout') }}</button>
                        </form>
                    </div>
                </div>
            @endauth

            @guest('web')
                <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-[#A0A0A8] transition hover:text-[#E8E8EC] sm:inline-flex">{{ __('ui.nav.login') }}</a>
            @endguest

            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-[#E8E8EC] transition hover:border-[#D4A843]/40 lg:hidden"
                @click="mobileOpen = !mobileOpen; userOpen = false; notifOpen = false"
                :aria-expanded="mobileOpen.toString()"
                aria-label="{{ __('ui.nav.toggle_menu') }}"
            >
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

    <div x-cloak x-show="mobileOpen" x-transition class="border-t border-[#1A1A1E] bg-[#0A0A0B]/98 lg:hidden">
        <div class="mx-auto max-w-7xl space-y-3 px-4 py-4 sm:px-6">
            <div class="grid gap-2">
                <a href="{{ route('catalog') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-[#E8E8EC] hover:bg-white/5" @click="mobileOpen = false">Equipment</a>
                <a href="{{ route('availability.board') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-[#E8E8EC] hover:bg-white/5" @click="mobileOpen = false">Cek Alat</a>
                <a href="{{ route('about') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-[#E8E8EC] hover:bg-white/5" @click="mobileOpen = false">Tentang Kami</a>
                <a href="{{ route('rental.rules') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-[#E8E8EC] hover:bg-white/5" @click="mobileOpen = false">Cara Sewa</a>
                <a href="{{ route('contact') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-[#E8E8EC] hover:bg-white/5" @click="mobileOpen = false">Contact</a>
            </div>

            @auth('web')
                <div class="grid grid-cols-2 gap-2 border-t border-[#1A1A1E] pt-3">
                    <a href="{{ route('cart') }}" class="rounded-xl border border-white/10 px-3 py-2 text-center text-sm font-semibold text-[#E8E8EC]">{{ __('ui.nav.cart') }}</a>
                    <a href="{{ route('booking.history') }}" class="rounded-xl border border-white/10 px-3 py-2 text-center text-sm font-semibold text-[#E8E8EC]">{{ __('ui.nav.my_orders') }}</a>
                    <a href="{{ route('profile') }}" class="rounded-xl border border-white/10 px-3 py-2 text-center text-sm font-semibold text-[#E8E8EC]">{{ __('ui.nav.my_profile') }}</a>
                    <a href="{{ route('settings.index') }}" class="rounded-xl border border-white/10 px-3 py-2 text-center text-sm font-semibold text-[#E8E8EC]">{{ __('ui.nav.settings') }}</a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-white/10 px-3 py-2 text-sm font-semibold text-[#E8E8EC]">{{ __('ui.nav.logout') }}</button>
                </form>
            @endauth

            @guest('web')
                <a href="{{ route('login') }}" class="block rounded-xl border border-white/10 px-3 py-2 text-center text-sm font-semibold text-[#E8E8EC]">{{ __('ui.nav.login') }}</a>
            @endguest
        </div>
    </div>
</nav>
