<nav class="sticky top-0 z-50 border-b border-[#1A1A1E] bg-[#0A0A0B]/95 backdrop-blur-xl text-[#E8E8EC]">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">

        <!-- LEFT -->
        <div class="flex items-center gap-8">
            <!-- LOGO -->
            <a href="/" class="flex items-center gap-3">
                <x-brand.image light="manake-logo-white.png" dark="manake-logo-white.png" alt="Manake" img-class="h-8 w-auto" />
            </a>

            <!-- MENU -->
            <div class="hidden items-center gap-6 text-sm font-medium text-[#A0A0A8] md:flex">
                <a href="/equipments" class="transition hover:text-[#D4A843]">
                    {{ __('ui.nav.catalog') }}
                </a>
                <a href="/overview" class="transition hover:text-[#D4A843]">
                    {{ __('ui.nav.my_orders') }}
                </a>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="flex items-center gap-4">

            <!-- SEARCH (OPTIONAL) -->
            <div class="relative hidden md:block">
                <input
                    type="text"
                    placeholder="{{ __('ui.nav.search_placeholder') }}"
                    class="input w-64 rounded-xl border-[#1A1A1E] pl-10 pr-4 py-2 text-sm focus:border-[#D4A843] focus:ring-2 focus:ring-[#D4A843]/30 focus:outline-none">
                <span class="absolute left-3 top-2.5 text-[#66666C] text-sm">
                    🔍
                </span>
            </div>

            <!-- AUTH BUTTON (DUMMY DULU) -->
            <a href="/login"
               class="btn-primary rounded-xl px-5 py-2 text-sm font-semibold transition">
                {{ __('ui.nav.login') }}
            </a>
        </div>

    </div>
</nav>
