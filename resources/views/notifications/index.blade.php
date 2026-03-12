@extends('layouts.app')

@section('title', __('ui.placeholders.notifications_title'))
@section('page_title', __('ui.placeholders.notifications_title'))

@php
    $unreadCount = $notifications->whereNull('read_at')->count();
@endphp

@section('content')
    <div class="space-y-4">
        <div class="flex flex-col gap-2.5 md:flex-row md:items-end md:justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold text-blue-700">{{ __('ui.placeholders.notifications_title') }}</h1>
                <p class="text-sm text-slate-500">{{ __('ui.placeholders.notifications_message') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="status-chip status-chip-info">{{ $notifications->total() }} {{ __('ui.notifications_page.total') }}</span>
                <span class="status-chip {{ $unreadCount > 0 ? 'status-chip-warning' : 'status-chip-success' }}">
                    {{ $unreadCount > 0 ? __('ui.notifications_page.unread', ['count' => $unreadCount]) : __('ui.notifications_page.all_read') }}
                </span>
            </div>
        </div>

        <section class="grid gap-3">
            @forelse ($notifications as $notification)
                @php
                    $targetUrl = $notification->order
                        ? route('account.orders.show', $notification->order)
                        : route('booking.history');
                    $isUnread = $notification->read_at === null;
                @endphp

                @if ($isUnread)
                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $targetUrl }}">
                        <button
                            type="submit"
                            class="card flex w-full items-start gap-4 rounded-2xl border p-4 text-left transition hover:border-blue-200 hover:shadow-md"
                        >
                            <span class="mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500"></span>
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-semibold text-slate-900">{{ $notification->title }}</p>
                                    <p class="text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                </div>
                                <p class="text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                                <p class="text-xs font-semibold text-blue-600">{{ __('ui.notifications_page.open_detail') }}</p>
                            </div>
                        </button>
                    </form>
                @else
                    <a
                        href="{{ $targetUrl }}"
                        class="card flex items-start gap-4 rounded-2xl border p-4 transition hover:border-blue-200 hover:shadow-md"
                    >
                        <span class="mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-slate-200"></span>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm font-semibold text-slate-900">{{ $notification->title }}</p>
                                <p class="text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>
                            <p class="text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                            <p class="text-xs font-semibold text-slate-500">{{ __('ui.notifications_page.read') }}</p>
                        </div>
                    </a>
                @endif
            @empty
                <div class="card rounded-2xl border border-dashed p-8 text-center">
                    <p class="text-sm font-semibold text-slate-700">{{ __('app.notifications.empty') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('ui.notifications_page.empty_note') }}</p>
                </div>
            @endforelse
        </section>

        <div>
            {{ $notifications->links() }}
        </div>
    </div>
@endsection
