@extends('layouts.landing')

@section('title', __('app.category.title'))

@section('content')
    <section class="mk-section bg-slate-100">
        <div class="mk-container">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-blue-700 sm:text-3xl">{{ __('ui.categories.title') }}</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ __('ui.categories.subtitle') }}</p>
                </div>
                <a href="{{ route('catalog') }}" class="btn-secondary inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition">
                    {{ __('app.actions.see_catalog') }}
                </a>
            </div>

            @if ($categories->isEmpty())
                <div class="card mt-8 rounded-2xl p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7h18" />
                            <path d="M5 7v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7" />
                            <path d="M9 11h6" />
                        </svg>
                    </div>
                    <p class="mt-4 text-base font-semibold text-slate-900">{{ __('ui.categories.empty_title') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('ui.categories.empty_subtitle') }}</p>
                    <a href="{{ route('catalog') }}" class="btn-primary mt-5 inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition">
                        {{ __('ui.categories.empty_cta') }}
                    </a>
                </div>
            @else
                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <article class="card rounded-2xl p-5 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $category->name }}</h2>
                            <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ $category->description ?: __('app.category.all_subtitle') }}</p>
                            <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                                <span>{{ $category->equipments_count }} {{ __('ui.categories.count_suffix') }}</span>
                                <a href="{{ route('category.show', $category->slug) }}" class="text-blue-600 hover:text-blue-700 font-semibold">
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
