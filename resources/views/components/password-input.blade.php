@props([
    'id' => null,
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'required' => false,
    'inputClass' => '',
    'wrapperClass' => '',
    'buttonClass' => 'text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-100',
])

@php
    $resolvedId = $id ?? ($attributes->get('id') ?: 'password-' . str()->random(6));
    $resolvedName = $name ?? $attributes->get('name');
    $baseInputClass = $inputClass !== ''
        ? $inputClass
        : 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none';
@endphp

<div x-data="{ show: false }" class="relative {{ $wrapperClass }}">
    <input
        {{ $attributes->except(['class', 'type', 'name', 'id', 'value']) }}
        id="{{ $resolvedId }}"
        @if ($resolvedName) name="{{ $resolvedName }}" @endif
        :type="show ? 'text' : 'password'"
        class="{{ trim($baseInputClass . ' pr-12') }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $required ? 'required' : '' }}
        data-password-input
        @if (! is_null($value)) value="{{ $value }}" @endif
    >

    <button
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 {{ $buttonClass }}"
        @click="show = !show"
        :aria-label="show ? 'Hide password' : 'Show password'"
        data-password-toggle
    >
        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <svg x-cloak x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.4 0-10-7-10-7a20.77 20.77 0 0 1 5.06-6.94" />
            <path d="M1 1l22 22" />
            <path d="M9.88 9.88a3 3 0 0 0 4.24 4.24" />
        </svg>
    </button>
    {{ $slot }}
</div>
