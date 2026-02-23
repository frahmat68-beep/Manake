<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CopywritingController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.copy.edit', 'landing');
    }

    public function edit(string $section): View
    {
        $sections = $this->sections();
        abort_unless(isset($sections[$section]), 404);

        $sectionMeta = $sections[$section];
        $locale = (string) app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', 'id');
        $settingKeys = collect($sectionMeta['fields'] ?? [])->pluck('key')->all();
        $localizedSettingKeys = collect($settingKeys)
            ->map(fn (string $key) => $this->storedSettingKey($key, $locale, $fallbackLocale))
            ->all();
        $rawSettings = SiteSetting::query()
            ->whereIn('key', array_values(array_unique(array_merge($settingKeys, $localizedSettingKeys))))
            ->pluck('value', 'key')
            ->toArray();
        $settings = [];

        foreach ($settingKeys as $baseKey) {
            $storedKey = $this->storedSettingKey($baseKey, $locale, $fallbackLocale);
            if (array_key_exists($storedKey, $rawSettings)) {
                $settings[$baseKey] = $rawSettings[$storedKey];
                continue;
            }

            $settings[$baseKey] = $locale === $fallbackLocale
                ? ($rawSettings[$baseKey] ?? null)
                : null;
        }

        return view('admin.copy.edit', [
            'sections' => $sections,
            'currentSection' => $section,
            'sectionMeta' => $sectionMeta,
            'settings' => $settings,
            'activePage' => 'copy',
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $sections = $this->sections();
        abort_unless(isset($sections[$section]), 404);

        $sectionMeta = $sections[$section];
        $locale = (string) app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', 'id');
        $rules = [];

        foreach ($sectionMeta['fields'] as $fieldName => $meta) {
            $max = (int) ($meta['max'] ?? ($meta['type'] === 'textarea' ? 4000 : 255));
            $rules[$fieldName] = ['nullable', 'string', 'max:' . $max];
        }

        $validated = $request->validate($rules);
        $adminId = auth('admin')->id();
        $changedKeys = [];

        foreach ($sectionMeta['fields'] as $fieldName => $meta) {
            $value = trim((string) ($validated[$fieldName] ?? ''));
            $storedValue = $value !== '' ? $value : null;
            $storedKey = $this->storedSettingKey((string) $meta['key'], $locale, $fallbackLocale);

            SiteSetting::updateOrCreate(
                ['key' => $storedKey],
                [
                    'value' => $storedValue,
                    'type' => $meta['type'] ?? 'text',
                    'group' => $sectionMeta['group'] ?? 'copy',
                    'updated_by_admin_id' => $adminId,
                ]
            );

            $changedKeys[] = $storedKey;
            site_setting_forget([
                $storedKey,
                (string) $meta['key'],
                (string) $meta['key'] . '.id',
                (string) $meta['key'] . '.en',
            ]);
        }

        admin_audit('copy.update', 'site_settings', null, [
            'section' => $section,
            'keys' => $changedKeys,
        ], $adminId);

        Cache::forget('copy.translation_overrides.scoped');

        return redirect()
            ->route('admin.copy.edit', $section)
            ->with('success', __('Teks untuk halaman ini berhasil disimpan.'));
    }

    private function storedSettingKey(string $baseKey, string $locale, string $fallbackLocale): string
    {
        if ($locale === '' || $locale === $fallbackLocale) {
            return $baseKey;
        }

        return "{$baseKey}.{$locale}";
    }

    private function sections(): array
    {
        return [
            'landing' => [
                'label' => 'Landing Page',
                'group' => 'landing',
                'description' => 'Teks untuk halaman utama user (/).',
                'fields' => [
                    'hero_title' => [
                        'key' => 'home.hero_title',
                        'label' => 'Judul Hero',
                        'type' => 'text',
                        'max' => 255,
                        'location' => 'Landing > Hero > Judul utama',
                    ],
                    'hero_subtitle' => [
                        'key' => 'home.hero_subtitle',
                        'label' => 'Subjudul Hero',
                        'type' => 'textarea',
                        'max' => 1000,
                        'location' => 'Landing > Hero > Paragraf deskripsi',
                    ],
                    'hero_cta_text' => [
                        'key' => 'hero_cta_text',
                        'label' => 'Teks Tombol Hero',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Landing > Hero > Tombol utama',
                    ],
                    'ready_panel_title' => [
                        'key' => 'copy.landing.ready_panel_title',
                        'label' => 'Judul Panel Ready Item',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Landing > Kartu carousel kanan',
                    ],
                    'ready_panel_subtitle' => [
                        'key' => 'copy.landing.ready_panel_subtitle',
                        'label' => 'Subjudul Panel Ready Item',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Landing > Kartu carousel kanan',
                    ],
                    'flow_kicker' => [
                        'key' => 'copy.landing.flow_kicker',
                        'label' => 'Label Kecil Alur Rental',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Landing > Section alur',
                    ],
                    'flow_title' => [
                        'key' => 'copy.landing.flow_title',
                        'label' => 'Judul Alur Rental',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Landing > Section alur',
                    ],
                    'flow_catalog_link' => [
                        'key' => 'copy.landing.flow_catalog_link',
                        'label' => 'Teks Link ke Katalog',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Landing > Section alur > kanan atas',
                    ],
                    'step_1_title' => [
                        'key' => 'copy.landing.step_1_title',
                        'label' => 'Judul Step 1',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Landing > Alur rental > kartu step 1',
                    ],
                    'step_1_desc' => [
                        'key' => 'copy.landing.step_1_desc',
                        'label' => 'Deskripsi Step 1',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Landing > Alur rental > kartu step 1',
                    ],
                    'step_2_title' => [
                        'key' => 'copy.landing.step_2_title',
                        'label' => 'Judul Step 2',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Landing > Alur rental > kartu step 2',
                    ],
                    'step_2_desc' => [
                        'key' => 'copy.landing.step_2_desc',
                        'label' => 'Deskripsi Step 2',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Landing > Alur rental > kartu step 2',
                    ],
                    'step_3_title' => [
                        'key' => 'copy.landing.step_3_title',
                        'label' => 'Judul Step 3',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Landing > Alur rental > kartu step 3',
                    ],
                    'step_3_desc' => [
                        'key' => 'copy.landing.step_3_desc',
                        'label' => 'Deskripsi Step 3',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Landing > Alur rental > kartu step 3',
                    ],
                    'step_4_title' => [
                        'key' => 'copy.landing.step_4_title',
                        'label' => 'Judul Step 4',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Landing > Alur rental > kartu step 4',
                    ],
                    'step_4_desc' => [
                        'key' => 'copy.landing.step_4_desc',
                        'label' => 'Deskripsi Step 4',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Landing > Alur rental > kartu step 4',
                    ],
                    'quick_category_kicker' => [
                        'key' => 'copy.landing.quick_category_kicker',
                        'label' => 'Label Kecil Kategori Cepat',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Landing > Section kategori cepat',
                    ],
                    'quick_category_title' => [
                        'key' => 'copy.landing.quick_category_title',
                        'label' => 'Judul Kategori Cepat',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Landing > Section kategori cepat',
                    ],
                    'quick_category_empty' => [
                        'key' => 'copy.landing.quick_category_empty',
                        'label' => 'Teks Saat Kategori Kosong',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Landing > Section kategori cepat',
                    ],
                ],
            ],
            'user_navigation' => [
                'label' => 'Navigasi User',
                'group' => 'navigation',
                'description' => 'Teks umum untuk sidebar, navbar, dan aksi utama user.',
                'fields' => [
                    'nav_catalog' => [
                        'key' => 'copy.trans.ui.nav.catalog',
                        'label' => 'Menu Katalog',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Sidebar / Navbar',
                    ],
                    'nav_check_availability' => [
                        'key' => 'copy.trans.ui.nav.check_availability',
                        'label' => 'Menu Cek Alat',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Sidebar / Navbar',
                    ],
                    'nav_my_orders' => [
                        'key' => 'copy.trans.ui.nav.my_orders',
                        'label' => 'Menu Riwayat',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Sidebar / Navbar',
                    ],
                    'nav_settings' => [
                        'key' => 'copy.trans.ui.nav.settings',
                        'label' => 'Menu Pengaturan',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Sidebar / Navbar',
                    ],
                    'nav_search_placeholder' => [
                        'key' => 'copy.trans.ui.nav.search_placeholder',
                        'label' => 'Placeholder Search Global',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Navigasi User > Header search',
                    ],
                    'nav_notifications' => [
                        'key' => 'copy.trans.ui.nav.notifications',
                        'label' => 'Label Notifikasi',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Header',
                    ],
                    'nav_cart' => [
                        'key' => 'copy.trans.ui.nav.cart',
                        'label' => 'Label Keranjang',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Header',
                    ],
                    'nav_login' => [
                        'key' => 'copy.trans.ui.nav.login',
                        'label' => 'Label Login',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Header (guest)',
                    ],
                    'nav_register' => [
                        'key' => 'copy.trans.ui.nav.register',
                        'label' => 'Label Daftar',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Header (guest)',
                    ],
                    'nav_sidebar_category_title' => [
                        'key' => 'copy.trans.ui.nav.category',
                        'label' => 'Judul Submenu Kategori',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Navigasi User > Sidebar katalog',
                    ],
                    'action_explore_catalog' => [
                        'key' => 'copy.trans.ui.actions.explore_catalog',
                        'label' => 'Teks Aksi Jelajahi Katalog',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Navigasi User > CTA',
                    ],
                ],
            ],
            'admin_panel' => [
                'label' => 'Admin Panel',
                'group' => 'admin',
                'description' => 'Teks area admin: sidebar, header panel, dan login admin.',
                'fields' => [
                    'admin_menu_dashboard' => [
                        'key' => 'copy.trans.ui.admin.dashboard',
                        'label' => 'Menu Dashboard',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin > Sidebar operasional',
                    ],
                    'admin_menu_orders' => [
                        'key' => 'copy.trans.ui.admin.orders',
                        'label' => 'Menu Pesanan',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin > Sidebar operasional',
                    ],
                    'admin_menu_equipments' => [
                        'key' => 'copy.trans.ui.admin.equipments',
                        'label' => 'Menu Peralatan',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin > Sidebar operasional',
                    ],
                    'admin_menu_categories' => [
                        'key' => 'copy.trans.ui.admin.categories',
                        'label' => 'Menu Kategori',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin > Sidebar operasional',
                    ],
                    'admin_menu_users' => [
                        'key' => 'copy.trans.ui.admin.users',
                        'label' => 'Menu Pengguna',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin > Sidebar operasional',
                    ],
                    'admin_menu_copywriting' => [
                        'key' => 'copy.trans.ui.admin.copywriting',
                        'label' => 'Menu Teks Website',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin > Sidebar pengaturan',
                    ],
                    'admin_menu_website_settings' => [
                        'key' => 'copy.trans.ui.admin.website_settings',
                        'label' => 'Menu Pengaturan Website',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Admin > Sidebar pengaturan',
                    ],
                    'admin_menu_legacy_content' => [
                        'key' => 'copy.trans.ui.admin.legacy_content',
                        'label' => 'Menu Konten Legacy',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Admin > Sidebar pengaturan',
                    ],
                    'admin_menu_db' => [
                        'key' => 'copy.trans.ui.admin.db_explorer',
                        'label' => 'Menu Data Database',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Admin > Sidebar pengaturan',
                    ],
                    'admin_sidebar_operational' => [
                        'key' => 'copy.trans.ui.admin.sidebar_operational',
                        'label' => 'Judul Grup Operasional',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin > Sidebar',
                    ],
                    'admin_sidebar_settings' => [
                        'key' => 'copy.trans.ui.admin.sidebar_settings',
                        'label' => 'Judul Grup Pengaturan',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin > Sidebar',
                    ],
                    'admin_view_website' => [
                        'key' => 'copy.trans.ui.admin.view_website',
                        'label' => 'Label Tombol Lihat Website',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Admin > Header kanan',
                    ],
                    'admin_panel_title' => [
                        'key' => 'copy.trans.ui.admin.panel_title',
                        'label' => 'Subjudul Header Admin',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Admin > Header kiri',
                    ],
                    'admin_login_heading' => [
                        'key' => 'copy.trans.ui.admin.login_heading',
                        'label' => 'Judul Halaman Login Admin',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Admin Login > Panel kanan',
                    ],
                    'admin_login_subheading' => [
                        'key' => 'copy.trans.ui.admin.login_subheading',
                        'label' => 'Subjudul Halaman Login Admin',
                        'type' => 'textarea',
                        'max' => 320,
                        'location' => 'Admin Login > Panel kanan',
                    ],
                    'admin_login_intro' => [
                        'key' => 'copy.trans.ui.admin.login_intro',
                        'label' => 'Intro Login Admin (Panel kiri)',
                        'type' => 'text',
                        'max' => 180,
                        'location' => 'Admin Login > Panel kiri',
                    ],
                    'admin_login_hint' => [
                        'key' => 'copy.trans.ui.admin.login_hint',
                        'label' => 'Hint Login Admin',
                        'type' => 'textarea',
                        'max' => 220,
                        'location' => 'Admin Login > Panel kanan',
                    ],
                    'admin_login_button' => [
                        'key' => 'copy.trans.ui.admin.login_button',
                        'label' => 'Teks Tombol Login Admin',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Admin Login > Tombol submit',
                    ],
                    'admin_back_home' => [
                        'key' => 'copy.trans.ui.admin.back_home',
                        'label' => 'Teks Tombol Kembali ke Home',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Admin Login > Panel kanan',
                    ],
                ],
            ],
            'catalog' => [
                'label' => 'Katalog',
                'group' => 'catalog',
                'description' => 'Teks halaman katalog (/catalog).',
                'fields' => [
                    'catalog_kicker' => [
                        'key' => 'copy.catalog.kicker',
                        'label' => 'Label Kecil Katalog',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Catalog > Header',
                    ],
                    'catalog_title' => [
                        'key' => 'copy.catalog.title',
                        'label' => 'Judul Katalog',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Catalog > Header',
                    ],
                    'catalog_subtitle' => [
                        'key' => 'copy.catalog.subtitle',
                        'label' => 'Subjudul Katalog',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Catalog > Header',
                    ],
                    'catalog_category_label' => [
                        'key' => 'copy.catalog.category_label',
                        'label' => 'Label Filter Kategori',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Catalog > Filter kategori',
                    ],
                    'catalog_sidebar_submenu_enabled' => [
                        'key' => 'catalog.sidebar_submenu_enabled',
                        'label' => 'Aktifkan Submenu Kategori Sidebar (1/0)',
                        'type' => 'text',
                        'max' => 5,
                        'location' => 'Catalog > Sidebar interaktif',
                    ],
                    'catalog_sidebar_submenu_duration_ms' => [
                        'key' => 'catalog.sidebar_submenu_duration_ms',
                        'label' => 'Durasi Animasi Submenu Sidebar (ms)',
                        'type' => 'text',
                        'max' => 6,
                        'location' => 'Catalog > Sidebar interaktif',
                    ],
                    'catalog_idle_hamburger_enabled' => [
                        'key' => 'catalog.idle_hamburger_enabled',
                        'label' => 'Aktifkan Animasi Hamburger Idle (1/0)',
                        'type' => 'text',
                        'max' => 5,
                        'location' => 'Catalog > Interaksi idle kategori',
                    ],
                    'catalog_idle_hamburger_delay_ms' => [
                        'key' => 'catalog.idle_hamburger_delay_ms',
                        'label' => 'Delay Idle Sebelum Animasi (ms)',
                        'type' => 'text',
                        'max' => 6,
                        'location' => 'Catalog > Interaksi idle kategori',
                    ],
                    'catalog_idle_hamburger_step_ms' => [
                        'key' => 'catalog.idle_hamburger_step_ms',
                        'label' => 'Kecepatan Pindah Animasi per Kategori (ms)',
                        'type' => 'text',
                        'max' => 6,
                        'location' => 'Catalog > Interaksi idle kategori',
                    ],
                    'catalog_empty_title' => [
                        'key' => 'copy.catalog.empty_title',
                        'label' => 'Judul Saat Katalog Kosong',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Catalog > Empty state',
                    ],
                    'catalog_empty_subtitle' => [
                        'key' => 'copy.catalog.empty_subtitle',
                        'label' => 'Subjudul Saat Katalog Kosong',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Catalog > Empty state',
                    ],
                    'catalog_reset_search_label' => [
                        'key' => 'copy.trans.ui.catalog.reset_search_label',
                        'label' => 'Teks Tombol Reset Search',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Catalog > Filter kategori',
                    ],
                    'catalog_all_categories_label' => [
                        'key' => 'copy.trans.ui.catalog.all_categories_label',
                        'label' => 'Label Semua Kategori',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Catalog > Filter kategori',
                    ],
                    'catalog_search_result_prefix' => [
                        'key' => 'copy.trans.ui.catalog.search_result_prefix',
                        'label' => 'Prefix Hasil Pencarian',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Catalog > Header hasil pencarian',
                    ],
                    'catalog_item_suffix' => [
                        'key' => 'copy.trans.ui.catalog.item_suffix',
                        'label' => 'Sufiks Jumlah Item',
                        'type' => 'text',
                        'max' => 40,
                        'location' => 'Catalog > Badge jumlah item',
                    ],
                    'catalog_quick_order_button' => [
                        'key' => 'copy.trans.ui.catalog.quick_order_button',
                        'label' => 'Teks Tombol Pesan Cepat',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Catalog > Kartu item',
                    ],
                    'catalog_login_to_order_button' => [
                        'key' => 'copy.trans.ui.catalog.login_to_order_button',
                        'label' => 'Teks Tombol Login untuk Pesan',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Catalog > Kartu item',
                    ],
                    'catalog_out_of_stock_button' => [
                        'key' => 'copy.trans.ui.catalog.out_of_stock_button',
                        'label' => 'Teks Tombol Stok Habis',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Catalog > Kartu item',
                    ],
                    'catalog_quick_order_title' => [
                        'key' => 'copy.trans.ui.catalog.quick_order_title',
                        'label' => 'Judul Modal Pesan Cepat',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Catalog > Modal pesan cepat',
                    ],
                    'catalog_quick_order_hint' => [
                        'key' => 'copy.trans.ui.catalog.quick_order_hint',
                        'label' => 'Hint Modal Pesan Cepat',
                        'type' => 'textarea',
                        'max' => 240,
                        'location' => 'Catalog > Modal pesan cepat',
                    ],
                ],
            ],
            'category' => [
                'label' => 'Halaman Kategori',
                'group' => 'category',
                'description' => 'Teks halaman detail kategori (/category/{slug}).',
                'fields' => [
                    'category_kicker' => [
                        'key' => 'copy.category.kicker',
                        'label' => 'Label Kecil Kategori',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Category detail > Header',
                    ],
                    'category_subtitle' => [
                        'key' => 'copy.category.subtitle',
                        'label' => 'Subjudul Kategori',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Category detail > Header',
                    ],
                    'category_total_label' => [
                        'key' => 'copy.category.total_label',
                        'label' => 'Label Total Item',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Category detail > Ringkasan',
                    ],
                    'category_empty_title' => [
                        'key' => 'copy.category.empty_title',
                        'label' => 'Judul Saat Kategori Kosong',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Category detail > Empty state',
                    ],
                    'category_empty_subtitle' => [
                        'key' => 'copy.category.empty_subtitle',
                        'label' => 'Subjudul Saat Kategori Kosong',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Category detail > Empty state',
                    ],
                    'category_ready_label' => [
                        'key' => 'copy.trans.ui.category.ready_label',
                        'label' => 'Label Badge Ready',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Category detail > Ringkasan',
                    ],
                    'category_available_line' => [
                        'key' => 'copy.trans.ui.category.available_line',
                        'label' => 'Template Teks Ketersediaan',
                        'type' => 'text',
                        'max' => 180,
                        'location' => 'Category detail > Kartu item',
                    ],
                    'category_availability_note' => [
                        'key' => 'copy.trans.ui.category.availability_note',
                        'label' => 'Catatan Ketersediaan',
                        'type' => 'textarea',
                        'max' => 220,
                        'location' => 'Category detail > Kartu item',
                    ],
                ],
            ],
            'categories' => [
                'label' => 'Daftar Kategori',
                'group' => 'category',
                'description' => 'Teks halaman /categories.',
                'fields' => [
                    'categories_title' => [
                        'key' => 'copy.trans.ui.categories.title',
                        'label' => 'Judul Halaman Daftar Kategori',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Categories > Header',
                    ],
                    'categories_subtitle' => [
                        'key' => 'copy.trans.ui.categories.subtitle',
                        'label' => 'Subjudul Halaman Daftar Kategori',
                        'type' => 'textarea',
                        'max' => 260,
                        'location' => 'Categories > Header',
                    ],
                    'categories_empty_title' => [
                        'key' => 'copy.trans.ui.categories.empty_title',
                        'label' => 'Judul Empty State',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Categories > Empty state',
                    ],
                    'categories_empty_subtitle' => [
                        'key' => 'copy.trans.ui.categories.empty_subtitle',
                        'label' => 'Subjudul Empty State',
                        'type' => 'textarea',
                        'max' => 260,
                        'location' => 'Categories > Empty state',
                    ],
                    'categories_empty_cta' => [
                        'key' => 'copy.trans.ui.categories.empty_cta',
                        'label' => 'Teks Tombol Empty State',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Categories > Empty state',
                    ],
                    'categories_count_suffix' => [
                        'key' => 'copy.trans.ui.categories.count_suffix',
                        'label' => 'Sufiks Jumlah Item',
                        'type' => 'text',
                        'max' => 40,
                        'location' => 'Categories > Kartu kategori',
                    ],
                    'categories_view_category_cta' => [
                        'key' => 'copy.trans.ui.categories.view_category_cta',
                        'label' => 'Teks Link Lihat Kategori',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Categories > Kartu kategori',
                    ],
                ],
            ],
            'contact' => [
                'label' => 'Halaman Kontak',
                'group' => 'contact',
                'description' => 'Teks halaman /contact.',
                'fields' => [
                    'contact_title' => [
                        'key' => 'copy.trans.ui.contact.title',
                        'label' => 'Judul Halaman Kontak',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Contact > Header',
                    ],
                    'contact_subtitle' => [
                        'key' => 'copy.trans.ui.contact.subtitle',
                        'label' => 'Subjudul Halaman Kontak',
                        'type' => 'textarea',
                        'max' => 260,
                        'location' => 'Contact > Header',
                    ],
                    'contact_info_title' => [
                        'key' => 'copy.trans.ui.contact.info_title',
                        'label' => 'Judul Kontainer Informasi',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Contact > Kontainer info',
                    ],
                    'contact_map_title' => [
                        'key' => 'copy.trans.ui.contact.map_title',
                        'label' => 'Judul Kontainer Peta',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Contact > Kontainer peta',
                    ],
                    'contact_map_empty' => [
                        'key' => 'copy.trans.ui.contact.map_empty',
                        'label' => 'Teks Saat Peta Belum Diatur',
                        'type' => 'text',
                        'max' => 200,
                        'location' => 'Contact > Kontainer peta',
                    ],
                ],
            ],
            'booking' => [
                'label' => 'Riwayat & Detail Pesanan',
                'group' => 'booking',
                'description' => 'Teks halaman riwayat booking dan detail pesanan user.',
                'fields' => [
                    'booking_title' => [
                        'key' => 'copy.booking.title',
                        'label' => 'Judul Halaman Riwayat',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Riwayat > Header utama',
                    ],
                    'booking_subtitle' => [
                        'key' => 'copy.booking.subtitle',
                        'label' => 'Subjudul Halaman Riwayat',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Riwayat > Header deskripsi',
                    ],
                    'booking_active_title' => [
                        'key' => 'copy.booking.active_title',
                        'label' => 'Judul Kolom Rental Aktif',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Riwayat > Kartu kiri',
                    ],
                    'booking_recent_title' => [
                        'key' => 'copy.booking.recent_title',
                        'label' => 'Judul Kolom Riwayat Terbaru',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Riwayat > Kartu kanan',
                    ],
                    'booking_cta_text' => [
                        'key' => 'copy.booking.cta_text',
                        'label' => 'Teks Tombol Header Riwayat',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Riwayat > Tombol kanan atas',
                    ],
                    'order_detail_title' => [
                        'key' => 'copy.order_detail.title',
                        'label' => 'Judul Halaman Detail Pesanan',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Header utama',
                    ],
                    'order_detail_subtitle' => [
                        'key' => 'copy.order_detail.subtitle',
                        'label' => 'Subjudul Halaman Detail Pesanan',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Detail Pesanan > Header deskripsi',
                    ],
                    'order_detail_back_label' => [
                        'key' => 'copy.order_detail.back_label',
                        'label' => 'Label Tombol Kembali Detail Pesanan',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Link kembali',
                    ],
                    'order_number_label' => [
                        'key' => 'copy.order_detail.order_number_label',
                        'label' => 'Label Nomor Order',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Kartu ringkasan',
                    ],
                    'order_progress_title' => [
                        'key' => 'copy.order_detail.progress_title',
                        'label' => 'Judul Progress Pesanan',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Kartu progress',
                    ],
                    'order_items_title' => [
                        'key' => 'copy.order_detail.items_title',
                        'label' => 'Judul Item Disewa',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Kartu item',
                    ],
                    'order_payment_title' => [
                        'key' => 'copy.order_detail.payment_title',
                        'label' => 'Judul Kartu Pembayaran',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Sidebar kanan',
                    ],
                    'order_copy_receipt_button' => [
                        'key' => 'copy.trans.ui.orders.copy_receipt_button',
                        'label' => 'Teks Tombol Copy Resi',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Header kartu order',
                    ],
                    'order_schedule_unavailable_title' => [
                        'key' => 'copy.trans.ui.orders.schedule_unavailable_title',
                        'label' => 'Judul Popup Jadwal Tidak Tersedia',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Detail Pesanan > Popup reschedule',
                    ],
                    'order_schedule_unavailable_subtitle' => [
                        'key' => 'copy.trans.ui.orders.schedule_unavailable_subtitle',
                        'label' => 'Subjudul Popup Jadwal Tidak Tersedia',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Detail Pesanan > Popup reschedule',
                    ],
                    'order_reschedule_title' => [
                        'key' => 'copy.trans.ui.orders.reschedule_title',
                        'label' => 'Judul Kontainer Reschedule',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Kontainer reschedule',
                    ],
                    'order_reschedule_save_button' => [
                        'key' => 'copy.trans.ui.orders.reschedule_save_button',
                        'label' => 'Teks Tombol Simpan Reschedule',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Detail Pesanan > Kontainer reschedule',
                    ],
                    'order_pay_now_button' => [
                        'key' => 'copy.trans.ui.orders.pay_now_button',
                        'label' => 'Teks Tombol Bayar Sekarang',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Sidebar pembayaran',
                    ],
                    'order_refresh_payment_button' => [
                        'key' => 'copy.trans.ui.orders.refresh_payment_button',
                        'label' => 'Teks Tombol Cek Status Pembayaran',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Detail Pesanan > Sidebar pembayaran',
                    ],
                    'order_view_invoice_button' => [
                        'key' => 'copy.trans.ui.orders.view_invoice_button',
                        'label' => 'Teks Tombol Lihat Invoice',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Sidebar invoice',
                    ],
                    'order_download_pdf_button' => [
                        'key' => 'copy.trans.ui.orders.download_pdf_button',
                        'label' => 'Teks Tombol Download PDF',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Detail Pesanan > Sidebar invoice',
                    ],
                ],
            ],
            'availability' => [
                'label' => 'Cek Ketersediaan',
                'group' => 'availability',
                'description' => 'Teks halaman availability board (/availability-board).',
                'fields' => [
                    'availability_title' => [
                        'key' => 'copy.availability.title',
                        'label' => 'Judul Halaman Cek Ketersediaan',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Availability Board > Hero',
                    ],
                    'availability_subtitle' => [
                        'key' => 'copy.availability.subtitle',
                        'label' => 'Subjudul Halaman Cek Ketersediaan',
                        'type' => 'textarea',
                        'max' => 500,
                        'location' => 'Availability Board > Hero',
                    ],
                    'availability_calendar_title' => [
                        'key' => 'copy.availability.calendar_title',
                        'label' => 'Judul Kartu Kalender',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Kartu kalender',
                    ],
                    'availability_selected_title' => [
                        'key' => 'copy.availability.selected_title',
                        'label' => 'Judul Kartu Tanggal Dipilih',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Ringkasan kanan',
                    ],
                    'availability_ready_title' => [
                        'key' => 'copy.availability.ready_title',
                        'label' => 'Judul Kartu Alat Siap Dipakai',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Ringkasan kanan',
                    ],
                    'availability_busy_title' => [
                        'key' => 'copy.availability.busy_title',
                        'label' => 'Judul Kartu Alat Terpakai',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Bagian bawah kiri',
                    ],
                    'availability_monthly_title' => [
                        'key' => 'copy.availability.monthly_title',
                        'label' => 'Judul Kartu Jadwal Aktif Bulan Ini',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Bagian bawah kanan',
                    ],
                    'availability_search_placeholder' => [
                        'key' => 'copy.availability.search_placeholder',
                        'label' => 'Placeholder Input Cari Alat',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Availability Board > Filter atas',
                    ],
                    'availability_show_button' => [
                        'key' => 'copy.availability.show_button',
                        'label' => 'Teks Tombol Tampilkan',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Availability Board > Filter atas',
                    ],
                    'availability_reset_button' => [
                        'key' => 'copy.availability.reset_button',
                        'label' => 'Teks Tombol Reset',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Filter atas',
                    ],
                    'availability_drag_hint' => [
                        'key' => 'copy.availability.drag_hint',
                        'label' => 'Hint Drag Kalender (Mobile)',
                        'type' => 'text',
                        'max' => 180,
                        'location' => 'Availability Board > Header kalender',
                    ],
                    'availability_metric_total' => [
                        'key' => 'copy.availability.metric_total',
                        'label' => 'Label Metric Total Alat',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Kartu ringkasan tanggal',
                    ],
                    'availability_metric_busy' => [
                        'key' => 'copy.availability.metric_busy',
                        'label' => 'Label Metric Sedang Disewa',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Kartu ringkasan tanggal',
                    ],
                    'availability_metric_available' => [
                        'key' => 'copy.availability.metric_available',
                        'label' => 'Label Metric Masih Kosong',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Kartu ringkasan tanggal',
                    ],
                    'availability_metric_units' => [
                        'key' => 'copy.availability.metric_units',
                        'label' => 'Label Metric Unit Dipakai',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Kartu ringkasan tanggal',
                    ],
                    'availability_ready_empty' => [
                        'key' => 'copy.availability.ready_empty',
                        'label' => 'Teks Empty Alat Siap Dipakai',
                        'type' => 'textarea',
                        'max' => 280,
                        'location' => 'Availability Board > Kartu alat siap dipakai',
                    ],
                    'availability_busy_empty' => [
                        'key' => 'copy.availability.busy_empty',
                        'label' => 'Teks Empty Alat Terpakai',
                        'type' => 'textarea',
                        'max' => 280,
                        'location' => 'Availability Board > Kartu alat terpakai',
                    ],
                    'availability_monthly_empty' => [
                        'key' => 'copy.availability.monthly_empty',
                        'label' => 'Teks Empty Jadwal Bulanan',
                        'type' => 'textarea',
                        'max' => 280,
                        'location' => 'Availability Board > Kartu jadwal bulan',
                    ],
                    'availability_modal_date_title' => [
                        'key' => 'copy.availability.modal_date_title',
                        'label' => 'Judul Modal Detail Tanggal',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Modal detail tanggal',
                    ],
                    'availability_modal_close' => [
                        'key' => 'copy.availability.modal_close',
                        'label' => 'Label Tombol Tutup Modal',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Availability Board > Modal detail tanggal',
                    ],
                    'availability_modal_empty' => [
                        'key' => 'copy.availability.modal_empty',
                        'label' => 'Teks Empty Modal Detail Tanggal',
                        'type' => 'textarea',
                        'max' => 220,
                        'location' => 'Availability Board > Modal detail tanggal',
                    ],
                    'availability_range_kicker' => [
                        'key' => 'copy.availability.range_kicker',
                        'label' => 'Kicker Modal Drag Rentang',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_range_title' => [
                        'key' => 'copy.availability.range_title',
                        'label' => 'Judul Modal Drag Rentang',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_range_filter_label' => [
                        'key' => 'copy.availability.range_filter_label',
                        'label' => 'Label Filter Kategori Modal Drag',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_range_all_categories' => [
                        'key' => 'copy.availability.range_all_categories',
                        'label' => 'Label Semua Kategori Modal Drag',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_range_available_label' => [
                        'key' => 'copy.availability.range_available_label',
                        'label' => 'Label Counter Alat Tersedia',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_range_continue' => [
                        'key' => 'copy.availability.range_continue',
                        'label' => 'Teks Tombol Lanjut ke Keranjang',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_range_empty' => [
                        'key' => 'copy.availability.range_empty',
                        'label' => 'Teks Empty Modal Drag',
                        'type' => 'textarea',
                        'max' => 280,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_range_pick' => [
                        'key' => 'copy.availability.range_pick',
                        'label' => 'Teks Tombol Pilih & Sewa',
                        'type' => 'text',
                        'max' => 100,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_range_prefill_note' => [
                        'key' => 'copy.availability.range_prefill_note',
                        'label' => 'Catatan Prefill Tanggal',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Availability Board > Modal drag rentang',
                    ],
                    'availability_count_empty_suffix' => [
                        'key' => 'copy.trans.ui.availability_board.count_empty_suffix',
                        'label' => 'Sufiks Hitung Alat Kosong',
                        'type' => 'text',
                        'max' => 60,
                        'location' => 'Availability Board > Badge jumlah',
                    ],
                    'availability_count_tools_suffix' => [
                        'key' => 'copy.trans.ui.availability_board.count_tools_suffix',
                        'label' => 'Sufiks Hitung Alat Terpakai',
                        'type' => 'text',
                        'max' => 60,
                        'location' => 'Availability Board > Badge jumlah',
                    ],
                    'availability_count_schedules_suffix' => [
                        'key' => 'copy.trans.ui.availability_board.count_schedules_suffix',
                        'label' => 'Sufiks Hitung Jadwal',
                        'type' => 'text',
                        'max' => 60,
                        'location' => 'Availability Board > Badge jumlah',
                    ],
                    'availability_in_use_template' => [
                        'key' => 'copy.trans.ui.availability_board.in_use_template',
                        'label' => 'Template Unit Dipakai',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Availability Board > Kartu alat terpakai',
                    ],
                ],
            ],
            'checkout' => [
                'label' => 'Checkout',
                'group' => 'checkout',
                'description' => 'Teks halaman checkout (/checkout).',
                'fields' => [
                    'checkout_title' => [
                        'key' => 'copy.checkout.title',
                        'label' => 'Judul Halaman Checkout',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Checkout > Header',
                    ],
                    'checkout_subtitle' => [
                        'key' => 'copy.checkout.subtitle',
                        'label' => 'Subjudul Halaman Checkout',
                        'type' => 'textarea',
                        'max' => 320,
                        'location' => 'Checkout > Header',
                    ],
                    'checkout_back_to_cart' => [
                        'key' => 'copy.checkout.back_to_cart',
                        'label' => 'Label Link Kembali ke Cart',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Header',
                    ],
                    'checkout_detail_title' => [
                        'key' => 'copy.checkout.detail_title',
                        'label' => 'Judul Kartu Detail Sewa',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Kontainer detail',
                    ],
                    'checkout_empty_cart' => [
                        'key' => 'copy.checkout.empty_cart',
                        'label' => 'Teks Saat Cart Kosong',
                        'type' => 'textarea',
                        'max' => 200,
                        'location' => 'Checkout > Kontainer detail',
                    ],
                    'checkout_profile_title' => [
                        'key' => 'copy.checkout.profile_title',
                        'label' => 'Judul Kartu Data Diri',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Kontainer data diri',
                    ],
                    'checkout_profile_hint' => [
                        'key' => 'copy.checkout.profile_hint',
                        'label' => 'Hint Data Diri',
                        'type' => 'textarea',
                        'max' => 220,
                        'location' => 'Checkout > Kontainer data diri',
                    ],
                    'checkout_confirm_profile' => [
                        'key' => 'copy.checkout.confirm_profile',
                        'label' => 'Teks Checklist Konfirmasi Data',
                        'type' => 'text',
                        'max' => 180,
                        'location' => 'Checkout > Kontainer data diri',
                    ],
                    'checkout_submit_button' => [
                        'key' => 'copy.checkout.submit_button',
                        'label' => 'Teks Tombol Konfirmasi & Bayar',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Kontainer data diri',
                    ],
                    'checkout_submit_processing' => [
                        'key' => 'copy.checkout.submit_processing',
                        'label' => 'Teks Tombol Saat Memproses',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Kontainer data diri',
                    ],
                    'checkout_payment_title' => [
                        'key' => 'copy.checkout.payment_title',
                        'label' => 'Judul Kartu Metode Pembayaran',
                        'type' => 'text',
                        'max' => 140,
                        'location' => 'Checkout > Kontainer metode pembayaran',
                    ],
                    'checkout_payment_note' => [
                        'key' => 'copy.checkout.payment_note',
                        'label' => 'Deskripsi Metode Pembayaran',
                        'type' => 'textarea',
                        'max' => 240,
                        'location' => 'Checkout > Kontainer metode pembayaran',
                    ],
                    'checkout_summary_title' => [
                        'key' => 'copy.checkout.summary_title',
                        'label' => 'Judul Kartu Ringkasan',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Sidebar ringkasan',
                    ],
                    'checkout_summary_subtotal' => [
                        'key' => 'copy.checkout.summary_subtotal',
                        'label' => 'Label Subtotal / Hari',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Sidebar ringkasan',
                    ],
                    'checkout_summary_estimate' => [
                        'key' => 'copy.checkout.summary_estimate',
                        'label' => 'Label Total Estimasi',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Sidebar ringkasan',
                    ],
                    'checkout_summary_tax' => [
                        'key' => 'copy.checkout.summary_tax',
                        'label' => 'Label PPN',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Sidebar ringkasan',
                    ],
                    'checkout_summary_total' => [
                        'key' => 'copy.checkout.summary_total',
                        'label' => 'Label Total Bayar',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Sidebar ringkasan',
                    ],
                    'checkout_no_items' => [
                        'key' => 'copy.checkout.no_items',
                        'label' => 'Teks Sidebar Saat Tidak Ada Item',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Checkout > Sidebar ringkasan',
                    ],
                    'checkout_profile_update_link_label' => [
                        'key' => 'copy.trans.ui.checkout.profile_update_link_label',
                        'label' => 'Teks Link Update Profil',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Checkout > Kontainer data diri',
                    ],
                    'checkout_invalid_date_note' => [
                        'key' => 'copy.trans.ui.checkout.invalid_date_note',
                        'label' => 'Teks Saat Tanggal Item Tidak Valid',
                        'type' => 'textarea',
                        'max' => 240,
                        'location' => 'Checkout > Kontainer detail',
                    ],
                    'checkout_message_checkout_failed' => [
                        'key' => 'copy.trans.ui.checkout.messages.checkout_failed',
                        'label' => 'Pesan Error Checkout',
                        'type' => 'text',
                        'max' => 220,
                        'location' => 'Checkout > Alert/popup',
                    ],
                    'checkout_message_pay_failed' => [
                        'key' => 'copy.trans.ui.checkout.messages.pay_failed',
                        'label' => 'Pesan Gagal Bayar',
                        'type' => 'text',
                        'max' => 220,
                        'location' => 'Checkout > Alert/popup',
                    ],
                ],
            ],
            'global_labels' => [
                'label' => 'Global Text Override',
                'group' => 'copy',
                'description' => "Override semua teks berbasis translation key (__('ui.xxx')) di web user. Format: satu baris satu key, contoh ui.nav.my_orders = Riwayat Sewa.",
                'fields' => [
                    'translation_overrides' => [
                        'key' => 'copy.translation_overrides',
                        'label' => 'Daftar Override Translation Key',
                        'type' => 'textarea',
                        'max' => 20000,
                        'location' => 'Global > Translation key map',
                    ],
                ],
            ],
            'rules' => [
                'label' => 'Rules Sewa',
                'group' => 'rules',
                'description' => 'Teks halaman rules sewa (/rental-rules).',
                'fields' => [
                    'rules_kicker' => [
                        'key' => 'copy.rules_page.kicker',
                        'label' => 'Kicker Rules Sewa',
                        'type' => 'text',
                        'max' => 80,
                        'location' => 'Rules Sewa > Header',
                    ],
                    'rules_title' => [
                        'key' => 'copy.rules_page.title',
                        'label' => 'Judul Rules Sewa',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Rules Sewa > Header',
                    ],
                    'rules_subtitle' => [
                        'key' => 'copy.rules_page.subtitle',
                        'label' => 'Subjudul Rules Sewa',
                        'type' => 'textarea',
                        'max' => 600,
                        'location' => 'Rules Sewa > Header',
                    ],
                    'rules_operational_title' => [
                        'key' => 'copy.rules_page.operational_title',
                        'label' => 'Judul Catatan Operasional',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Rules Sewa > Kartu bawah',
                    ],
                    'rules_cta_primary' => [
                        'key' => 'copy.rules_page.cta_primary',
                        'label' => 'Teks Tombol Utama Rules',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Rules Sewa > Tombol utama',
                    ],
                    'rules_cta_secondary' => [
                        'key' => 'copy.rules_page.cta_secondary',
                        'label' => 'Teks Tombol Kedua Rules',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Rules Sewa > Tombol sekunder',
                    ],
                ],
            ],
            'footer' => [
                'label' => 'Footer Website',
                'group' => 'footer',
                'description' => 'Teks footer yang tampil di semua halaman user.',
                'fields' => [
                    'footer_about' => [
                        'key' => 'footer.about',
                        'label' => 'Tentang Kami',
                        'type' => 'textarea',
                        'max' => 1500,
                        'location' => 'Footer kolom kiri',
                    ],
                    'footer_address' => [
                        'key' => 'footer.address',
                        'label' => 'Alamat',
                        'type' => 'textarea',
                        'max' => 1200,
                        'location' => 'Footer kolom kanan',
                    ],
                    'footer_whatsapp' => [
                        'key' => 'footer.whatsapp',
                        'label' => 'WhatsApp',
                        'type' => 'text',
                        'max' => 255,
                        'location' => 'Footer kolom kontak',
                    ],
                    'footer_email' => [
                        'key' => 'contact.email',
                        'label' => 'Email Kontak',
                        'type' => 'text',
                        'max' => 255,
                        'location' => 'Footer kolom kontak',
                    ],
                    'footer_instagram' => [
                        'key' => 'footer.instagram',
                        'label' => 'Instagram',
                        'type' => 'text',
                        'max' => 255,
                        'location' => 'Footer kolom kontak',
                    ],
                    'footer_copyright' => [
                        'key' => 'footer_copyright',
                        'label' => 'Copyright',
                        'type' => 'text',
                        'max' => 255,
                        'location' => 'Footer baris bawah',
                    ],
                    'footer_tagline' => [
                        'key' => 'site_tagline',
                        'label' => 'Tagline Bawah Footer',
                        'type' => 'text',
                        'max' => 255,
                        'location' => 'Footer baris bawah',
                    ],
                    'footer_rules_title' => [
                        'key' => 'footer.rules_title',
                        'label' => 'Judul Kartu Rules Footer',
                        'type' => 'text',
                        'max' => 120,
                        'location' => 'Footer > Kartu panduan sewa',
                    ],
                    'footer_rules_link' => [
                        'key' => 'footer.rules_link',
                        'label' => 'Teks Link Rules Footer',
                        'type' => 'text',
                        'max' => 160,
                        'location' => 'Footer > Kartu panduan sewa',
                    ],
                    'footer_rules_note' => [
                        'key' => 'footer.rules_note',
                        'label' => 'Catatan Rules Footer',
                        'type' => 'textarea',
                        'max' => 400,
                        'location' => 'Footer > Kartu panduan sewa',
                    ],
                ],
            ],
        ];
    }
}
