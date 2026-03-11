<nav class="sticky top-0 z-50 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <!-- LEFT -->
        <div class="flex items-center gap-8">
            <!-- LOGO -->
            <a href="/" class="flex items-center gap-3">
                <img src="{{ site_asset('manake-logo-blue.png') }}" alt="Manake" class="h-8">
            </a>

            <!-- MENU -->
            <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="/equipments" class="hover:text-blue-600 transition">
                    {{ __('ui.nav.catalog') }}
                </a>
                <a href="/overview" class="hover:text-blue-600 transition">
                    {{ __('ui.nav.my_orders') }}
                </a>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="flex items-center gap-4">

            <!-- SEARCH (OPTIONAL) -->
            <div class="hidden md:block relative">
                <input
                    type="text"
                    placeholder="{{ __('ui.nav.search_placeholder') }}"
                    class="pl-10 pr-4 py-2 rounded-xl border border-slate-300
                           focus:ring-2 focus:ring-blue-500 focus:outline-none
                           text-sm w-64">
                <span class="absolute left-3 top-2.5 text-slate-400 text-sm">
                    🔍
                </span>
            </div>

            <!-- AUTH BUTTON (DUMMY DULU) -->
            <a href="/login"
               class="px-5 py-2 rounded-xl text-sm font-semibold
                      bg-blue-600 text-white hover:bg-blue-700 transition">
                {{ __('ui.nav.login') }}
            </a>
        </div>

    </div>
</nav>
