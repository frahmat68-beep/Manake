@extends('layouts.app')

@section('title', __('ui.settings.title'))
@section('page_title', __('ui.settings.title'))

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-semibold text-blue-700">{{ __('ui.settings.title') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('ui.settings.subtitle') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1.2fr,0.8fr]">
        <div class="space-y-5">
            @if (session('status') === 'settings-updated')
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ __('ui.settings.saved') }}
                </div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
                @csrf

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('ui.settings.section_language') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('ui.settings.section_language_hint') }}</p>

                    <div class="mt-4 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                        @foreach (['id' => __('ui.languages.id'), 'en' => __('ui.languages.en')] as $value => $label)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                                <input
                                    type="radio"
                                    name="locale"
                                    value="{{ $value }}"
                                    class="h-4 w-4 text-blue-600"
                                    {{ $locale === $value ? 'checked' : '' }}
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('locale')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('ui.settings.section_theme') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('ui.settings.section_theme_hint') }}</p>

                    <div class="mt-4 grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                        @foreach (['system' => __('ui.settings.theme_system'), 'dark' => __('ui.settings.theme_dark'), 'light' => __('ui.settings.theme_light')] as $value => $label)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                                <input
                                    type="radio"
                                    name="theme"
                                    value="{{ $value }}"
                                    class="h-4 w-4 text-blue-600"
                                    {{ $theme === $value ? 'checked' : '' }}
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('theme')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button class="btn-primary inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold">
                    {{ __('ui.settings.save') }}
                </button>
            </form>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('ui.settings.tips_title') }}</h3>
                <p class="mt-2 text-xs text-slate-500">{{ __('ui.settings.tips_body') }}</p>
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500">
                    {{ __('ui.settings.tips_note') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('form[action="{{ route('settings.update') }}"]');
            if (!form) {
                return;
            }

            form.addEventListener('submit', () => {
                const selectedTheme = form.querySelector('input[name="theme"]:checked')?.value;
                const selectedLocale = form.querySelector('input[name="locale"]:checked')?.value;

                if (selectedTheme && window.ManakePreferences?.rememberTheme) {
                    window.ManakePreferences.rememberTheme(selectedTheme);
                }

                if (selectedLocale && window.ManakePreferences?.rememberLocale) {
                    window.ManakePreferences.rememberLocale(selectedLocale);
                }
            });
        })();
    </script>
@endpush
