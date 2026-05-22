@extends('layouts.landing')

@section('title', setting('meta_title', 'Manake.Id'))

@section('content')
    <section class="min-h-screen bg-black text-white">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-4 py-16 sm:px-6 lg:px-10">
            <div class="max-w-2xl">
                <p class="text-[11px] font-black uppercase tracking-[0.42em] text-amber-400">
                    MANAKE UI RESET
                </p>

                <h1 class="mt-6 font-serif text-[clamp(3.2rem,8vw,6.5rem)] font-semibold leading-[0.92] tracking-[-0.05em] text-white" style="font-family: 'DM Serif Display', Georgia, serif;">
                    Ready for v0 rebuild
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-8 text-white/70 sm:text-xl">
                    The public homepage has been reset so the next rebuild can start from a clean, verifiable baseline.
                </p>

                <div class="mt-10">
                    <a href="{{ route('catalog') }}" class="inline-flex items-center rounded-md bg-amber-400 px-6 py-3.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                        Browse Catalog
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
