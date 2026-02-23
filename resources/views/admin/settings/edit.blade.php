@extends('layouts.admin')

@section('title', __('ui.admin.website_settings'))
@section('page_title', __('ui.admin.website_settings'))

@section('content')
    <div class="max-w-5xl space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('ui.admin.settings_general') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Nama Situs') }}</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Slogan') }}</label>
                        <input type="text" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Judul Meta') }}</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Deskripsi Meta') }}</label>
                        <textarea name="meta_description" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('ui.admin.settings_homepage') }}</h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Judul Hero') }}</label>
                        <input type="text" name="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Subjudul Hero') }}</label>
                        <textarea name="hero_subtitle" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('hero_subtitle', $settings['hero_subtitle'] ?? '') }}</textarea>
                    </div>
                    <div class="sm:max-w-xs">
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Teks CTA Hero') }}</label>
                        <input type="text" name="hero_cta_text" value="{{ old('hero_cta_text', $settings['hero_cta_text'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('ui.admin.settings_footer') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Deskripsi Footer') }}</label>
                        <textarea name="footer_description" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('footer_description', $settings['footer_description'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Alamat Footer') }}</label>
                        <textarea name="footer_address" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('footer_address', $settings['footer_address'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Email Footer') }}</label>
                        <input type="email" name="footer_email" value="{{ old('footer_email', $settings['footer_email'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <label class="mt-4 text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Telepon Footer') }}</label>
                        <input type="text" name="footer_phone" value="{{ old('footer_phone', $settings['footer_phone'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <label class="mt-4 text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Hak Cipta Footer') }}</label>
                        <input type="text" name="footer_copyright" value="{{ old('footer_copyright', $settings['footer_copyright'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('ui.admin.settings_social') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Instagram') }}</label>
                        <input type="text" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('WhatsApp') }}</label>
                        <input type="text" name="social_whatsapp" value="{{ old('social_whatsapp', $settings['social_whatsapp'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('YouTube') }}</label>
                        <input type="text" name="social_youtube" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('TikTok') }}</label>
                        <input type="text" name="social_tiktok" value="{{ old('social_tiktok', $settings['social_tiktok'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('ui.admin.settings_contact') }}</h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Embed Peta Kontak') }}</label>
                        <textarea name="contact_map_embed" rows="4" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">{{ old('contact_map_embed', $settings['contact_map_embed'] ?? '') }}</textarea>
                    </div>
                    <div class="sm:max-w-sm">
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Email Penerima Form') }}</label>
                        <input type="email" name="contact_form_receiver_email" value="{{ old('contact_form_receiver_email', $settings['contact_form_receiver_email'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Kontrol Tipografi & Gaya') }}</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-300">
                    {{ __('Kontrol ini akan mempengaruhi gaya teks utama di web pengguna/admin: warna heading, ketebalan font, miring, dan skala ukuran.') }}
                </p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Warna Heading Utama (H1)') }}</label>
                        <input
                            type="color"
                            name="typography_heading_color"
                            value="{{ old('typography_heading_color', $settings['typography.heading_color'] ?? '#1d4ed8') }}"
                            class="mt-2 h-10 w-full rounded-xl border border-slate-200 bg-white px-1 py-1"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Warna Subheading (H2-H3)') }}</label>
                        <input
                            type="color"
                            name="typography_subheading_color"
                            value="{{ old('typography_subheading_color', $settings['typography.subheading_color'] ?? '#2563eb') }}"
                            class="mt-2 h-10 w-full rounded-xl border border-slate-200 bg-white px-1 py-1"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Warna Teks Isi') }}</label>
                        <input
                            type="color"
                            name="typography_body_color"
                            value="{{ old('typography_body_color', $settings['typography.body_color'] ?? '#334155') }}"
                            class="mt-2 h-10 w-full rounded-xl border border-slate-200 bg-white px-1 py-1"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Bobot Heading') }}</label>
                        <select name="typography_heading_weight" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach (['600', '700', '800', '900'] as $value)
                                <option value="{{ $value }}" @selected(old('typography_heading_weight', $settings['typography.heading_weight'] ?? '800') === $value)>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Bobot Teks Isi') }}</label>
                        <select name="typography_body_weight" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach (['400', '500', '600'] as $value)
                                <option value="{{ $value }}" @selected(old('typography_body_weight', $settings['typography.body_weight'] ?? '400') === $value)>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Gaya Heading') }}</label>
                        <select name="typography_heading_style" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach (['normal' => __('Normal'), 'italic' => __('Miring')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('typography_heading_style', $settings['typography.heading_style'] ?? 'normal') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Gaya Teks Isi') }}</label>
                        <select name="typography_body_style" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach (['normal' => __('Normal'), 'italic' => __('Miring')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('typography_body_style', $settings['typography.body_style'] ?? 'normal') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Skala Ukuran Heading') }}</label>
                        <select name="typography_heading_scale" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach (['sm' => __('Kecil'), 'md' => __('Normal'), 'lg' => __('Besar')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('typography_heading_scale', $settings['typography.heading_scale'] ?? 'md') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ __('Skala Ukuran Teks Isi') }}</label>
                        <select name="typography_body_scale" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach (['sm' => __('Kecil'), 'md' => __('Normal'), 'lg' => __('Besar')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('typography_body_scale', $settings['typography.body_scale'] ?? 'md') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                {{ __('ui.admin.settings_save') }}
            </button>
        </form>
    </div>
@endsection
