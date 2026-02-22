@extends('layouts.admin', ['activePage' => 'copy'])

@section('title', 'Editor Teks Website')
@section('page_title', 'Editor Teks Website')

@php
    $fallbacks = [
        'home.hero_title' => __('app.landing.hero_title') . ' ' . __('app.landing.hero_highlight') . ' ' . __('app.landing.hero_suffix'),
        'home.hero_subtitle' => __('app.landing.hero_desc'),
        'hero_cta_text' => __('app.actions.see_catalog'),
        'copy.landing.ready_panel_title' => 'Siap Disewa',
        'copy.landing.ready_panel_subtitle' => 'Item live dari inventory yang tersedia hari ini.',
        'copy.landing.flow_kicker' => 'Alur Rental',
        'copy.landing.flow_title' => 'Biar proses sewa tidak ribet',
        'copy.landing.flow_catalog_link' => 'Lihat semua alat',
        'copy.landing.step_1_title' => 'Pilih Alat',
        'copy.landing.step_1_desc' => 'Filter berdasarkan kategori, status siap, dan budget harian.',
        'copy.landing.step_2_title' => 'Isi Profil',
        'copy.landing.step_2_desc' => 'Data identitas dan kontak disimpan agar transaksi berikutnya lebih cepat.',
        'copy.landing.step_3_title' => 'Bayar via Midtrans',
        'copy.landing.step_3_desc' => 'Pilih metode pembayaran favorit tanpa pindah halaman berulang.',
        'copy.landing.step_4_title' => 'Buat Resi',
        'copy.landing.step_4_desc' => 'Setelah lunas, resi bisa dibuka dan dicetak langsung dari detail pesanan.',
        'copy.landing.quick_category_kicker' => 'Kategori Cepat',
        'copy.landing.quick_category_title' => 'Akses langsung ke kebutuhan produksi',
        'copy.landing.quick_category_empty' => 'Belum ada kategori tersedia.',
        'copy.trans.ui.nav.catalog' => __('ui.nav.catalog'),
        'copy.trans.ui.nav.check_availability' => __('ui.nav.check_availability'),
        'copy.trans.ui.nav.my_orders' => __('ui.nav.my_orders'),
        'copy.trans.ui.nav.settings' => __('ui.nav.settings'),
        'copy.trans.ui.nav.search_placeholder' => __('ui.nav.search_placeholder'),
        'copy.trans.ui.nav.notifications' => __('ui.nav.notifications'),
        'copy.trans.ui.nav.cart' => __('ui.nav.cart'),
        'copy.trans.ui.nav.login' => __('ui.nav.login'),
        'copy.trans.ui.nav.register' => __('ui.nav.register'),
        'copy.trans.ui.nav.category' => __('ui.nav.category'),
        'copy.trans.ui.actions.explore_catalog' => __('ui.actions.explore_catalog'),
        'copy.trans.ui.admin.dashboard' => __('ui.admin.dashboard'),
        'copy.trans.ui.admin.orders' => __('ui.admin.orders'),
        'copy.trans.ui.admin.equipments' => __('ui.admin.equipments'),
        'copy.trans.ui.admin.categories' => __('ui.admin.categories'),
        'copy.trans.ui.admin.users' => __('ui.admin.users'),
        'copy.trans.ui.admin.copywriting' => __('ui.admin.copywriting'),
        'copy.trans.ui.admin.website_settings' => __('ui.admin.website_settings'),
        'copy.trans.ui.admin.legacy_content' => __('ui.admin.legacy_content'),
        'copy.trans.ui.admin.db_explorer' => __('ui.admin.db_explorer'),
        'copy.trans.ui.admin.sidebar_operational' => __('ui.admin.sidebar_operational'),
        'copy.trans.ui.admin.sidebar_settings' => __('ui.admin.sidebar_settings'),
        'copy.trans.ui.admin.view_website' => __('ui.admin.view_website'),
        'copy.trans.ui.admin.panel_title' => __('ui.admin.panel_title'),
        'copy.trans.ui.admin.login_heading' => __('ui.admin.login_heading'),
        'copy.trans.ui.admin.login_subheading' => __('ui.admin.login_subheading'),
        'copy.trans.ui.admin.login_intro' => __('ui.admin.login_intro'),
        'copy.trans.ui.admin.login_hint' => __('ui.admin.login_hint'),
        'copy.trans.ui.admin.login_button' => __('ui.admin.login_button'),
        'copy.trans.ui.admin.back_home' => __('ui.admin.back_home'),
        'copy.catalog.kicker' => __('app.catalog.kicker'),
        'copy.catalog.title' => __('app.catalog.title'),
        'copy.catalog.subtitle' => __('app.catalog.subtitle'),
        'copy.catalog.category_label' => __('app.catalog.filter_category'),
        'catalog.sidebar_submenu_enabled' => '1',
        'catalog.sidebar_submenu_duration_ms' => '220',
        'catalog.idle_hamburger_enabled' => '1',
        'catalog.idle_hamburger_delay_ms' => '2200',
        'catalog.idle_hamburger_step_ms' => '900',
        'copy.catalog.empty_title' => 'Belum ada alat tersedia.',
        'copy.catalog.empty_subtitle' => 'Tambahkan alat baru dari admin agar kategori ini terisi.',
        'copy.trans.ui.catalog.reset_search_label' => __('ui.catalog.reset_search_label'),
        'copy.trans.ui.catalog.all_categories_label' => __('ui.catalog.all_categories_label'),
        'copy.trans.ui.catalog.search_result_prefix' => __('ui.catalog.search_result_prefix'),
        'copy.trans.ui.catalog.item_suffix' => __('ui.catalog.item_suffix'),
        'copy.trans.ui.catalog.quick_order_button' => __('ui.catalog.quick_order_button'),
        'copy.trans.ui.catalog.login_to_order_button' => __('ui.catalog.login_to_order_button'),
        'copy.trans.ui.catalog.out_of_stock_button' => __('ui.catalog.out_of_stock_button'),
        'copy.trans.ui.catalog.quick_order_title' => __('ui.catalog.quick_order_title'),
        'copy.trans.ui.catalog.quick_order_hint' => __('ui.catalog.quick_order_hint'),
        'copy.category.kicker' => __('app.category.title'),
        'copy.category.subtitle' => 'Daftar alat pada kategori ini.',
        'copy.category.total_label' => 'Total alat',
        'copy.category.empty_title' => 'Belum ada alat di kategori ini.',
        'copy.category.empty_subtitle' => 'Silakan cek kategori lain atau hubungi admin.',
        'copy.trans.ui.category.ready_label' => __('ui.category.ready_label'),
        'copy.trans.ui.category.available_line' => __('ui.category.available_line'),
        'copy.trans.ui.category.availability_note' => __('ui.category.availability_note'),
        'copy.trans.ui.categories.title' => __('ui.categories.title'),
        'copy.trans.ui.categories.subtitle' => __('ui.categories.subtitle'),
        'copy.trans.ui.categories.empty_title' => __('ui.categories.empty_title'),
        'copy.trans.ui.categories.empty_subtitle' => __('ui.categories.empty_subtitle'),
        'copy.trans.ui.categories.empty_cta' => __('ui.categories.empty_cta'),
        'copy.trans.ui.categories.count_suffix' => __('ui.categories.count_suffix'),
        'copy.trans.ui.categories.view_category_cta' => __('ui.categories.view_category_cta'),
        'copy.trans.ui.contact.title' => __('ui.contact.title'),
        'copy.trans.ui.contact.subtitle' => __('ui.contact.subtitle'),
        'copy.trans.ui.contact.info_title' => __('ui.contact.info_title'),
        'copy.trans.ui.contact.map_title' => __('ui.contact.map_title'),
        'copy.trans.ui.contact.map_empty' => __('ui.contact.map_empty'),
        'copy.booking.title' => __('ui.nav.my_orders'),
        'copy.booking.subtitle' => 'Semua pesanan dan status pembayaran kamu.',
        'copy.booking.active_title' => 'Rental Aktif',
        'copy.booking.recent_title' => 'Riwayat Terbaru',
        'copy.booking.cta_text' => __('ui.actions.explore_catalog'),
        'copy.order_detail.title' => 'Detail Pesanan',
        'copy.order_detail.subtitle' => 'Pantau status pembayaran dan progres rental di sini.',
        'copy.order_detail.back_label' => 'Kembali ke Riwayat',
        'copy.order_detail.order_number_label' => 'Nomor Pesanan',
        'copy.order_detail.progress_title' => 'Progres Pesanan',
        'copy.order_detail.items_title' => 'Item Disewa',
        'copy.order_detail.payment_title' => 'Pembayaran',
        'copy.trans.ui.orders.copy_receipt_button' => __('ui.orders.copy_receipt_button'),
        'copy.trans.ui.orders.schedule_unavailable_title' => __('ui.orders.schedule_unavailable_title'),
        'copy.trans.ui.orders.schedule_unavailable_subtitle' => __('ui.orders.schedule_unavailable_subtitle'),
        'copy.trans.ui.orders.reschedule_title' => __('ui.orders.reschedule_title'),
        'copy.trans.ui.orders.reschedule_save_button' => __('ui.orders.reschedule_save_button'),
        'copy.trans.ui.orders.pay_now_button' => __('ui.orders.pay_now_button'),
        'copy.trans.ui.orders.refresh_payment_button' => __('ui.orders.refresh_payment_button'),
        'copy.trans.ui.orders.view_invoice_button' => __('ui.orders.view_invoice_button'),
        'copy.trans.ui.orders.download_pdf_button' => __('ui.orders.download_pdf_button'),
        'copy.availability.title' => 'Pusat Cek Ketersediaan Alat',
        'copy.availability.subtitle' => 'Klik tanggal di kalender untuk melihat pesanan aktif, atau drag beberapa hari sekaligus untuk cek rentang sewa.',
        'copy.availability.calendar_title' => 'Kalender Pemakaian',
        'copy.availability.selected_title' => 'Tanggal Dipilih',
        'copy.availability.ready_title' => 'Alat Paling Siap Dipakai',
        'copy.availability.busy_title' => 'Alat Terpakai',
        'copy.availability.monthly_title' => 'Jadwal Aktif Bulan Ini',
        'copy.availability.search_placeholder' => 'Cari nama alat...',
        'copy.availability.show_button' => 'Tampilkan',
        'copy.availability.reset_button' => 'Atur Ulang Pencarian',
        'copy.availability.drag_hint' => 'Tap tanggal untuk detail, drag untuk cek sewa rentang hari.',
        'copy.availability.metric_total' => 'Total Alat',
        'copy.availability.metric_busy' => 'Sedang Disewa',
        'copy.availability.metric_available' => 'Masih Kosong',
        'copy.availability.metric_units' => 'Unit Dipakai',
        'copy.availability.ready_empty' => 'Tidak ada alat yang kosong pada tanggal ini.',
        'copy.availability.busy_empty' => 'Tidak ada alat yang sedang dipakai pada tanggal ini.',
        'copy.availability.monthly_empty' => 'Belum ada jadwal aktif pada rentang bulan ini.',
        'copy.availability.modal_date_title' => 'Detail Tanggal',
        'copy.availability.modal_close' => 'Tutup',
        'copy.availability.modal_empty' => 'Tidak ada pesanan aktif pada tanggal ini.',
        'copy.availability.range_kicker' => 'Cek Rentang Sewa',
        'copy.availability.range_title' => 'Mau sewa di tanggal ini?',
        'copy.availability.range_filter_label' => 'Pilih Kategori Alat',
        'copy.availability.range_all_categories' => 'Semua kategori',
        'copy.availability.range_available_label' => 'Alat Tersedia',
        'copy.availability.range_continue' => 'Lanjut ke Keranjang',
        'copy.availability.range_empty' => 'Tidak ada alat tersedia penuh di rentang tanggal ini. Coba kategori lain atau ubah rentang drag.',
        'copy.availability.range_pick' => 'Pilih & Sewa',
        'copy.availability.range_prefill_note' => 'Tanggal sewa akan terisi otomatis di detail alat.',
        'copy.trans.ui.availability_board.count_empty_suffix' => __('ui.availability_board.count_empty_suffix'),
        'copy.trans.ui.availability_board.count_tools_suffix' => __('ui.availability_board.count_tools_suffix'),
        'copy.trans.ui.availability_board.count_schedules_suffix' => __('ui.availability_board.count_schedules_suffix'),
        'copy.trans.ui.availability_board.in_use_template' => __('ui.availability_board.in_use_template'),
        'copy.checkout.title' => 'Konfirmasi Sewa',
        'copy.checkout.subtitle' => 'Tinjau jadwal tiap alat sebelum pembayaran.',
        'copy.checkout.back_to_cart' => 'Kembali ke Keranjang',
        'copy.checkout.detail_title' => 'Detail Sewa',
        'copy.checkout.empty_cart' => 'Keranjang kosong. Silakan tambahkan item sebelum pembayaran.',
        'copy.checkout.profile_title' => 'Data Diri',
        'copy.checkout.profile_hint' => 'Data diambil dari profil. Perbarui profil bila perlu.',
        'copy.checkout.confirm_profile' => 'Saya memastikan data diri di atas sudah benar.',
        'copy.checkout.submit_button' => 'Konfirmasi & Bayar',
        'copy.checkout.submit_processing' => 'Memproses...',
        'copy.checkout.payment_title' => 'Metode Pembayaran',
        'copy.checkout.payment_note' => 'Pembayaran akan diproses melalui Midtrans Snap (sandbox) tanpa memuat ulang halaman.',
        'copy.checkout.summary_title' => 'Ringkasan',
        'copy.checkout.summary_subtotal' => 'Subtotal / hari',
        'copy.checkout.summary_estimate' => 'Total (estimasi)',
        'copy.checkout.summary_tax' => 'PPN 11%',
        'copy.checkout.summary_total' => 'Total Bayar',
        'copy.checkout.no_items' => 'Tidak ada item di keranjang.',
        'copy.trans.ui.checkout.profile_update_link_label' => __('ui.checkout.profile_update_link_label'),
        'copy.trans.ui.checkout.invalid_date_note' => __('ui.checkout.invalid_date_note'),
        'copy.trans.ui.checkout.messages.checkout_failed' => __('ui.checkout.messages.checkout_failed'),
        'copy.trans.ui.checkout.messages.pay_failed' => __('ui.checkout.messages.pay_failed'),
        'copy.translation_overrides' => "ui.nav.my_orders = Riwayat Sewa\nui.cart.title = Keranjang Utama\napp.product.rental_date = Pilih Jadwal Sewa",
        'copy.rules_page.kicker' => 'Aturan Sewa',
        'copy.rules_page.title' => 'Panduan Sewa Manake Rental',
        'copy.rules_page.subtitle' => 'Halaman ini merangkum aturan utama supaya proses sewa aman, jelas, dan adil untuk semua pengguna. Aturan ini berlaku untuk pemesanan website, reschedule, dan pengelolaan unit.',
        'copy.rules_page.operational_title' => 'Catatan Operasional',
        'copy.rules_page.cta_primary' => 'Mulai Sewa dari Katalog',
        'copy.rules_page.cta_secondary' => 'Hubungi Tim Manake',
        'footer.about' => __('app.footer.about_body'),
        'footer.address' => __('app.footer.address_body'),
        'footer.whatsapp' => '+62 812-3456-7890',
        'contact.email' => 'hello@manakerental.id',
        'footer.instagram' => '@manakerental',
        'footer_copyright' => __('app.footer.copyright'),
        'site_tagline' => __('app.footer.tagline'),
        'footer.rules_title' => __('app.footer.rules_title'),
        'footer.rules_link' => __('app.footer.rules_link'),
        'footer.rules_note' => __('app.footer.rules_note'),
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="card rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">{{ $sectionMeta['label'] }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $sectionMeta['description'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Semua field di halaman ini khusus edit teks yang tampil ke pengguna. Field sudah dirapikan per kontainer tampilan supaya lebih mudah dikelola.</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn-secondary inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[260px,1fr]">
            <aside class="card h-fit rounded-2xl p-4 shadow-sm">
                <p class="px-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Halaman Pengguna</p>
                <nav class="mt-3 space-y-1">
                    @foreach ($sections as $sectionKey => $meta)
                        <a
                            href="{{ route('admin.copy.edit', $sectionKey) }}"
                            class="block rounded-xl px-3 py-2 text-sm font-semibold transition {{ $currentSection === $sectionKey ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                        >
                            {{ $meta['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <section class="card rounded-2xl p-6 shadow-sm">
                @php
                    $fieldsByContainer = collect($sectionMeta['fields'])
                        ->map(function ($meta, $fieldName) {
                            $location = trim((string) ($meta['location'] ?? ''));
                            $locationParts = $location !== '' ? array_values(array_filter(array_map('trim', explode('>', $location)))) : [];
                            $container = trim((string) ($meta['container'] ?? ($locationParts[1] ?? ($locationParts[0] ?? 'Umum'))));

                            return [
                                'field' => $fieldName,
                                'meta' => $meta,
                                'container' => $container !== '' ? $container : 'Umum',
                            ];
                        })
                        ->groupBy('container');
                    $containerAnchors = $fieldsByContainer->keys()->mapWithKeys(function ($container) {
                        $slug = 'container-' . \Illuminate\Support\Str::slug((string) $container);
                        return [$container => $slug];
                    });
                @endphp

                <form method="POST" action="{{ route('admin.copy.update', $currentSection) }}" class="space-y-6">
                    @csrf

                    @if ($fieldsByContainer->count() > 1)
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Navigasi Kontainer</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($containerAnchors as $container => $anchorId)
                                    <a href="#{{ $anchorId }}" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                        {{ $container }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @foreach ($fieldsByContainer as $container => $containerFields)
                        <article id="{{ $containerAnchors[$container] }}" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5 scroll-mt-28">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold text-blue-700">{{ $container }}</h3>
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700">{{ $containerFields->count() }} isian</span>
                            </div>

                            <div class="space-y-5">
                                @foreach ($containerFields as $fieldData)
                                    @php
                                        $fieldName = $fieldData['field'];
                                        $meta = $fieldData['meta'];
                                        $currentValue = old($fieldName, $settings[$meta['key']] ?? $fallbacks[$meta['key']] ?? '');
                                        $usingFallback = ! isset($settings[$meta['key']]) || $settings[$meta['key']] === null;
                                        $isTranslationMap = ($meta['key'] ?? '') === 'copy.translation_overrides';
                                    @endphp

                                    <div>
                                        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <label for="{{ $fieldName }}" class="text-sm font-semibold text-slate-900">{{ $meta['label'] }}</label>
                                                @if (!empty($meta['location']))
                                                    <p class="text-xs text-slate-500">Lokasi tampil: {{ $meta['location'] }}</p>
                                                @endif
                                            </div>
                                            @if ($usingFallback)
                                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                                                    Sedang pakai teks default
                                                </span>
                                            @endif
                                        </div>

                                        @if (($meta['type'] ?? 'text') === 'textarea')
                                            <textarea
                                                id="{{ $fieldName }}"
                                                name="{{ $fieldName }}"
                                                rows="{{ $isTranslationMap ? 12 : 4 }}"
                                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none {{ $isTranslationMap ? 'font-mono leading-relaxed' : '' }}"
                                            >{{ $currentValue }}</textarea>
                                        @else
                                            <input
                                                id="{{ $fieldName }}"
                                                name="{{ $fieldName }}"
                                                type="text"
                                                value="{{ $currentValue }}"
                                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                            >
                                        @endif

                                        @if ($isTranslationMap)
                                            <p class="mt-1 text-[11px] text-slate-500">
                                                Format: <code>translation.key = Teks Baru</code>. Satu baris satu key. Contoh: <code>ui.nav.my_orders = Riwayat Sewa</code>.
                                            </p>
                                        @else
                                            <p class="mt-1 text-[11px] text-slate-400">Kosongkan field jika mau balik ke teks default bawaan sistem.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach

                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-500">Perubahan langsung tampil di halaman pengguna setelah disimpan.</p>
                        <button type="submit" class="btn-primary inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold transition">
                            Simpan Teks Halaman
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
