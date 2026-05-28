@php
    $brandName = site_setting('brand.name', 'Manake');
    $homeUrl = route('home');
    $notificationItems = collect($notificationItems ?? []);
    $currentUser = auth('web')->user();
    $displayName = $currentUser?->display_name ?: ($currentUser?->name ?: __('app.user.generic'));
    $userInitial = strtoupper(substr((string) $displayName, 0, 1));
    $resolvedTheme = $themeResolved ?? request()->attributes->get('theme_resolved', 'light');
    $isLightShell = $resolvedTheme === 'light';
    $navShellClass = $isLightShell
        ? 'border-[#E5E2DA] bg-[#F7F7F4]/95 text-[#171717] shadow-[0_16px_50px_rgba(15,23,42,0.08)]'
        : 'border-[#1A1A1E] bg-[#0A0A0B]/95 text-[#E8E8EC] shadow-[0_16px_60px_rgba(0,0,0,0.18)]';
    $navLinkClass = $isLightShell ? 'text-[#666666] hover:text-blue-600' : 'text-[#A0A0A8] hover:text-[#E8E8EC]';
    $iconButtonClass = $isLightShell
        ? 'border-[#E5E2DA] bg-white/80 text-[#171717] hover:border-blue-600/45 hover:text-blue-600'
        : 'border-white/10 bg-white/5 text-[#E8E8EC] hover:border-[#D4A843]/40 hover:text-[#D4A843]';
    $dropdownClass = $isLightShell
        ? 'border-[#E5E2DA] bg-white text-[#171717] shadow-[0_22px_60px_rgba(15,23,42,0.12)]'
        : 'border-[#1A1A1E] bg-[#111113] text-[#E8E8EC] shadow-2xl';
    $dropdownItemClass = $isLightShell
        ? 'text-[#171717] hover:bg-[#F7F7F4] hover:text-blue-600'
        : 'text-[#E8E8EC] hover:bg-white/5 hover:text-[#D4A843]';
    $mobilePanelClass = $isLightShell ? 'border-[#E5E2DA] bg-[#F7F7F4]/98' : 'border-[#1A1A1E] bg-[#0A0A0B]/98';
    $mobileButtonClass = $isLightShell ? 'border-[#E5E2DA] text-[#171717]' : 'border-white/10 text-[#E8E8EC]';
    $settingsActiveClass = request()->routeIs('settings.*')
        ? ($isLightShell ? 'border-blue-600/60 text-blue-600 bg-blue-50/50' : 'border-[#D4A843]/50 text-[#D4A843] bg-[#D4A843]/5')
        : $iconButtonClass;
@endphp

<nav
    class="sticky top-0 z-50 border-b {{ $navShellClass }} backdrop-blur-xl"
    x-data="{ mobileOpen: false, userOpen: false, notifOpen: false }"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ $homeUrl }}" class="inline-flex shrink-0 items-center" aria-label="{{ $brandName }}">
            <x-brand.image light="manake-logo-blue.png" dark="manake-logo-white.png" :alt="$brandName" img-class="h-9 md:h-10 w-auto" />
        </a>

        <div class="hidden flex-1 items-center justify-center gap-8 text-sm font-semibold lg:flex">
            <a href="{{ route('catalog') }}" class="transition {{ $navLinkClass }}">{{ __('ui.nav.equipment') }}</a>
            <a href="{{ route('availability.board') }}" class="transition {{ $navLinkClass }}">{{ __('ui.nav.check_availability') }}</a>
            <a href="{{ route('about') }}" class="transition {{ $navLinkClass }}">{{ __('ui.nav.about') }}</a>
            <a href="{{ route('rental.rules') }}" class="transition {{ $navLinkClass }}">{{ __('ui.nav.rental_guide') }}</a>
            <a href="{{ route('contact') }}" class="transition {{ $navLinkClass }}">{{ __('ui.nav.contact') }}</a>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @auth('web')
                <div class="relative" @click.outside="notifOpen = false">
                    <button
                        type="button"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border transition {{ $iconButtonClass }}"
                        @click="notifOpen = !notifOpen; userOpen = false"
                        :aria-expanded="notifOpen.toString()"
                        aria-label="{{ __('ui.nav.notifications') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                            <path d="M9 17a3 3 0 0 0 6 0" />
                        </svg>
                        @if (($notificationCount ?? 0) > 0)
                            <span id="notification-badge" class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#D4A843] px-1 text-[10px] font-bold text-[#0A0A0B]">
                                {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                            </span>
                        @endif
                    </button>

                    <div x-cloak x-show="notifOpen" x-transition.origin.top.right class="absolute right-0 mt-3 w-80 max-w-[calc(100vw-2rem)] rounded-2xl border p-3 {{ $dropdownClass }}">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#D4A843]">{{ __('ui.nav.notifications') }}</p>
                            <a href="{{ route('notifications') }}" class="text-xs font-semibold transition {{ $navLinkClass }} hover:!text-[#D4A843]">{{ __('ui.nav.view_all') }}</a>
                        </div>
                        <div class="mt-3 max-h-72 space-y-2 overflow-y-auto">
                            @forelse ($notificationItems as $notification)
                                <a
                                    href="{{ $notification['url'] ?? route('notifications') }}"
                                    data-notification-link="true"
                                    data-mark-read-url="{{ $notification['mark_read_url'] }}"
                                    data-target-url="{{ $notification['url'] ?? route('notifications') }}"
                                    data-is-new="{{ $notification['is_new'] ? 'true' : 'false' }}"
                                    data-skip-loader="true"
                                    class="block rounded-xl border px-3 py-2 transition {{ $notification['is_new'] ? ($isLightShell ? 'border-[#D4A843]/30 bg-[#F7F7F4]' : 'border-[#D4A843]/30 bg-[#0A0A0B]') : ($isLightShell ? 'border-[#E5E2DA] bg-[#F7F7F4] opacity-60' : 'border-[#1A1A1E] bg-[#0A0A0B] opacity-60') }} hover:border-[#D4A843]/40 hover:opacity-100"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="line-clamp-1 text-xs font-bold {{ $isLightShell ? 'text-[#171717]' : 'text-[#E8E8EC]' }}">{{ $notification['title'] }}</p>
                                        @if ($notification['is_new'])
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#D4A843] notif-dot"></span>
                                        @endif
                                    </div>
                                    <p class="mt-1 line-clamp-2 text-xs leading-relaxed {{ $isLightShell ? 'text-[#666666]' : 'text-[#A0A0A8]' }}">{{ $notification['body'] }}</p>
                                </a>
                            @empty
                                <p class="rounded-xl border px-3 py-4 text-center text-xs {{ $isLightShell ? 'border-[#E5E2DA] bg-[#F7F7F4] text-[#666666]' : 'border-[#1A1A1E] bg-[#0A0A0B] text-[#A0A0A8]' }}">{{ __('app.notifications.empty') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <a
                    href="{{ route('cart') }}"
                    class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border transition {{ $iconButtonClass }}"
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
                        class="inline-flex items-center gap-2 rounded-full border py-1.5 pl-1.5 pr-3 text-sm font-semibold transition {{ $isLightShell ? 'border-[#E5E2DA] bg-white/80 text-[#171717] hover:border-[#D4A843]/45' : 'border-white/10 bg-white/5 text-[#E8E8EC] hover:border-[#D4A843]/40' }}"
                        @click="userOpen = !userOpen; notifOpen = false"
                        :aria-expanded="userOpen.toString()"
                    >
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#D4A843] text-xs font-bold text-[#0A0A0B]">{{ $userInitial }}</span>
                        <span class="max-w-[9rem] truncate">{{ $displayName }}</span>
                    </button>
                    <div x-cloak x-show="userOpen" x-transition.origin.top.right class="absolute right-0 mt-3 w-56 rounded-2xl border p-2 {{ $dropdownClass }}">
                        <a href="{{ route('profile') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold transition {{ $dropdownItemClass }}">{{ __('ui.nav.my_profile') }}</a>
                        <a href="{{ route('booking.history') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold transition {{ $dropdownItemClass }}">{{ __('ui.nav.my_orders') }}</a>
                        <div class="my-1 h-px {{ $isLightShell ? 'bg-[#E5E2DA]' : 'bg-[#1A1A1E]' }}"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-xl px-3 py-2 text-left text-sm font-semibold transition {{ $dropdownItemClass }}">{{ __('ui.nav.logout') }}</button>
                        </form>
                    </div>
                </div>

                {{-- Settings: at the far right as a utility control for auth users --}}
                <a
                    href="{{ route('settings.index') }}"
                    class="relative hidden h-10 w-10 items-center justify-center rounded-xl border transition sm:inline-flex {{ $settingsActiveClass }}"
                    aria-label="{{ __('ui.nav.settings') }}"
                    title="{{ __('ui.nav.settings') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                    </svg>
                </a>
            @endauth

            @guest('web')
                <a href="{{ route('login') }}" class="hidden text-sm font-semibold transition {{ $navLinkClass }} sm:inline-flex">{{ __('ui.nav.login') }}</a>
                {{-- Settings: after Masuk for guest users --}}
                <a
                    href="{{ route('settings.index') }}"
                    class="relative hidden h-10 w-10 items-center justify-center rounded-xl border transition sm:inline-flex {{ $settingsActiveClass }}"
                    aria-label="{{ __('ui.nav.settings') }}"
                    title="{{ __('ui.nav.settings') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                    </svg>
                </a>
            @endguest

            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition {{ $iconButtonClass }} lg:hidden"
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

    <div x-cloak x-show="mobileOpen" x-transition class="border-t lg:hidden {{ $mobilePanelClass }}">
        <div class="mx-auto max-w-7xl space-y-3 px-4 py-4 sm:px-6">
            <div class="grid gap-2">
                <a href="{{ route('catalog') }}" class="rounded-xl px-3 py-2 text-sm font-semibold {{ $isLightShell ? 'text-[#171717] hover:bg-white' : 'text-[#E8E8EC] hover:bg-white/5' }}" @click="mobileOpen = false">{{ __('ui.nav.equipment') }}</a>
                <a href="{{ route('availability.board') }}" class="rounded-xl px-3 py-2 text-sm font-semibold {{ $isLightShell ? 'text-[#171717] hover:bg-white' : 'text-[#E8E8EC] hover:bg-white/5' }}" @click="mobileOpen = false">{{ __('ui.nav.check_availability') }}</a>
                <a href="{{ route('about') }}" class="rounded-xl px-3 py-2 text-sm font-semibold {{ $isLightShell ? 'text-[#171717] hover:bg-white' : 'text-[#E8E8EC] hover:bg-white/5' }}" @click="mobileOpen = false">{{ __('ui.nav.about') }}</a>
                <a href="{{ route('rental.rules') }}" class="rounded-xl px-3 py-2 text-sm font-semibold {{ $isLightShell ? 'text-[#171717] hover:bg-white' : 'text-[#E8E8EC] hover:bg-white/5' }}" @click="mobileOpen = false">{{ __('ui.nav.rental_guide') }}</a>
                <a href="{{ route('contact') }}" class="rounded-xl px-3 py-2 text-sm font-semibold {{ $isLightShell ? 'text-[#171717] hover:bg-white' : 'text-[#E8E8EC] hover:bg-white/5' }}" @click="mobileOpen = false">{{ __('ui.nav.contact') }}</a>
                <a href="{{ route('settings.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('settings.*') ? ($isLightShell ? 'text-blue-600' : 'text-[#D4A843]') : ($isLightShell ? 'text-[#171717] hover:bg-white' : 'text-[#E8E8EC] hover:bg-white/5') }}" @click="mobileOpen = false">{{ __('ui.nav.settings') }}</a>
            </div>

            @auth('web')
                <div class="grid grid-cols-3 gap-2 border-t pt-3 {{ $isLightShell ? 'border-[#E5E2DA]' : 'border-[#1A1A1E]' }}">
                    <a href="{{ route('cart') }}" class="rounded-xl border px-3 py-2 text-center text-sm font-semibold {{ $mobileButtonClass }}">{{ __('ui.nav.cart') }}</a>
                    <a href="{{ route('booking.history') }}" class="rounded-xl border px-3 py-2 text-center text-sm font-semibold {{ $mobileButtonClass }}">{{ __('ui.nav.my_orders') }}</a>
                    <a href="{{ route('profile') }}" class="rounded-xl border px-3 py-2 text-center text-sm font-semibold {{ $mobileButtonClass }}">{{ __('ui.nav.my_profile') }}</a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border px-3 py-2 text-sm font-semibold {{ $mobileButtonClass }}">{{ __('ui.nav.logout') }}</button>
                </form>
            @endauth

            @guest('web')
                <a href="{{ route('login') }}" class="block rounded-xl border px-3 py-2 text-center text-sm font-semibold {{ $mobileButtonClass }}">{{ __('ui.nav.login') }}</a>
            @endguest
        </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const notifLinks = document.querySelectorAll('[data-notification-link="true"]');
    const badge = document.getElementById('notification-badge');

    notifLinks.forEach(link => {
        link.addEventListener('click', async (e) => {
            const isNew = link.getAttribute('data-is-new') === 'true';
            const markReadUrl = link.getAttribute('data-mark-read-url');
            const targetUrl = link.getAttribute('data-target-url');

            if (!isNew) {
                if (!(e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1)) {
                    e.preventDefault();
                    window.location.href = targetUrl;
                }
                return;
            }

            const performVisualMark = () => {
                link.setAttribute('data-is-new', 'false');
                link.classList.add('opacity-60');
                link.classList.remove('border-[#D4A843]/30');
                const isLight = {{ $isLightShell ? 'true' : 'false' }};
                if (isLight) {
                    link.classList.add('border-[#E5E2DA]');
                } else {
                    link.classList.add('border-[#1A1A1E]');
                }
                const dot = link.querySelector('.notif-dot');
                if (dot) dot.remove();
            };

            if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) {
                performVisualMark();
                fetch(markReadUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).catch(() => {});
                return;
            }

            e.preventDefault();
            performVisualMark();

            const navigate = () => {
                window.location.href = targetUrl;
            };

            try {
                const response = await fetch(markReadUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data && typeof data.unread_count !== 'undefined') {
                        const count = parseInt(data.unread_count, 10);
                        if (count > 0) {
                            if (badge) {
                                badge.textContent = count > 99 ? '99+' : count;
                            }
                        } else {
                            if (badge) {
                                badge.remove();
                            }
                        }
                    }
                }
            } catch (err) {
                console.error('Error marking notification as read:', err);
            } finally {
                navigate();
            }
        });
    });
});
</script>

