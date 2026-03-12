@props([
    'logoUrl' => null,
    'brandName' => 'Manake',
    'activePage' => '',
    'isSuperAdmin' => false,
    'adminName' => 'Admin',
    'adminRole' => 'admin',
])

@php
    $primaryItems = [
        [
            'key' => 'dashboard',
            'label' => __('ui.admin.dashboard'),
            'url' => route('admin.dashboard'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="8" rx="1.5" /><rect x="13" y="3" width="8" height="5" rx="1.5" /><rect x="13" y="10" width="8" height="11" rx="1.5" /><rect x="3" y="13" width="8" height="8" rx="1.5" /></svg>',
        ],
        [
            'key' => 'orders',
            'label' => __('ui.admin.orders'),
            'url' => route('admin.orders.index'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" /><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" /></svg>',
        ],
        [
            'key' => 'equipments',
            'label' => __('ui.admin.equipments'),
            'url' => route('admin.equipments.index'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2" /><path d="M8 21h8" /><path d="M12 18v3" /></svg>',
        ],
        [
            'key' => 'categories',
            'label' => __('ui.admin.categories'),
            'url' => route('admin.categories.index'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18" /><path d="M3 12h18" /><path d="M3 17h18" /></svg>',
        ],
        [
            'key' => 'users',
            'label' => __('ui.admin.users'),
            'url' => route('admin.users.index'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="8.5" cy="7" r="4" /><path d="M20 8v6" /><path d="M23 11h-6" /></svg>',
        ],
    ];

    $settingsItems = [
        [
            'key' => 'copy',
            'label' => __('ui.admin.copywriting'),
            'url' => route('admin.copy.edit', 'landing'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><polyline points="14 2 14 8 20 8" /></svg>',
        ],
        [
            'key' => 'website',
            'label' => __('ui.admin.website_settings'),
            'url' => route('admin.website.edit'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="2" y1="12" x2="22" y2="12" /><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z" /></svg>',
        ],
    ];

    if ($isSuperAdmin) {
        $settingsItems[] = [
            'key' => 'db',
            'label' => __('ui.admin.db_explorer'),
            'url' => route('admin.db.index'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5" /><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3" /></svg>',
        ];
    }

    $assetWithVersion = static function (string $file): string {
        return site_asset($file);
    };
    $expandedLogoFallbackUrl = $assetWithVersion('manake-logo-blue.png');
    $expandedLogoUrl = $logoUrl ?: $expandedLogoFallbackUrl;
    $adminInitial = strtoupper(substr((string) ($adminName ?: 'A'), 0, 1));
@endphp

<aside
    data-manake-sidebar="admin"
    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    <div class="flex h-20 items-center justify-between border-b border-slate-200 px-4">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <img
                src="{{ $expandedLogoUrl }}"
                alt="{{ $brandName }}"
                onerror="this.onerror=null;this.src='{{ $expandedLogoFallbackUrl }}';"
                class="h-auto w-40 object-contain object-left"
            >
        </a>
        <button type="button" class="rounded-lg border border-slate-200 p-1.5 text-slate-500 lg:hidden" @click="sidebarOpen = false" aria-label="{{ __('ui.actions.close') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
        <p class="px-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-600">{{ __('ui.admin.sidebar_operational') }}</p>
        <nav class="mt-2 space-y-1">
            @foreach ($primaryItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $activePage === $item['key'] ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-300/40' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-900 hover:ring-1 hover:ring-blue-200' }}"
                >
                    <span class="inline-flex h-8 w-8 items-center justify-center">{!! $item['icon'] !!}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <p class="mt-6 px-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-600">{{ __('ui.admin.sidebar_settings') }}</p>
        <nav class="mt-2 space-y-1">
            @foreach ($settingsItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $activePage === $item['key'] ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-300/40' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-900 hover:ring-1 hover:ring-blue-200' }}"
                >
                    <span class="inline-flex h-8 w-8 items-center justify-center">{!! $item['icon'] !!}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="border-t border-slate-200 px-4 py-4">
        <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5 text-slate-700">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-semibold text-white">{{ $adminInitial }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ $adminName }}</p>
                <p class="truncate text-[11px] uppercase tracking-wide text-slate-400">{{ $adminRole }}</p>
            </div>
        </div>
    </div>
</aside>
