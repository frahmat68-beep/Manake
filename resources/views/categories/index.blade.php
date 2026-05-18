@extends('layouts.landing')

@section('title', __('app.category.title'))

@section('content')
    <section class="mk-section bg-slate-50/50 dark:bg-slate-950/20">
        <div class="mk-container">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="mk-title-section text-blue-600 dark:text-blue-400">{{ __('ui.categories.title') }}</h1>
                    <p class="mk-copy mt-2">{{ __('ui.categories.subtitle') }}</p>
                </div>
                <a href="{{ route('catalog') }}" class="mk-button-secondary py-2.5 px-5">
                    {{ __('app.actions.see_catalog') }}
                </a>
            </div>

            @if ($categories->isEmpty())
                <div class="mk-card mt-8 p-10 text-center flex flex-col items-center justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-950/55 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7h18" />
                            <path d="M5 7v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7" />
                            <path d="M9 11h6" />
                        </svg>
                    </div>
                    <p class="mt-6 text-lg font-bold text-slate-900 dark:text-slate-100">{{ __('ui.categories.empty_title') }}</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-md">{{ __('ui.categories.empty_subtitle') }}</p>
                    <a href="{{ route('catalog') }}" class="mk-button-primary mt-6">
                        {{ __('ui.categories.empty_cta') }}
                    </a>
                </div>
            @else
                <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <article class="mk-card group flex flex-col justify-between p-6">
                            <div>
                                <h2 class="mk-title-card group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $category->name }}</h2>
                                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400 line-clamp-3 leading-relaxed">{{ $category->description ?: __('app.category.all_subtitle') }}</p>
                            </div>
                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 pt-4">
                                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ $category->equipments_count }} {{ __('ui.categories.count_suffix') }}</span>
                                <a href="{{ route('category.show', $category->slug) }}" class="inline-flex items-center text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                    {{ __('ui.categories.view_category_cta') }} →
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
