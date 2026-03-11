@extends('layouts.app')

@section('title', __('ui.cart.title'))
@section('page_title', __('ui.cart.title'))

@section('content')
    @php
        $formatIdr = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
        $subtotalPerDay = (int) ($subtotal ?? 0);
        $estimatedSubtotal = (int) ($estimatedSubtotal ?? 0);
        $taxAmount = (int) ($taxAmount ?? 0);
        $estimatedTotal = (int) ($grandTotal ?? ($estimatedSubtotal + $taxAmount));
        $suggestedEquipments = collect($suggestedEquipments ?? []);
        $cartSuggestedStartDate = is_string($cartSuggestedStartDate ?? null) ? $cartSuggestedStartDate : null;
        $cartSuggestedEndDate = is_string($cartSuggestedEndDate ?? null) ? $cartSuggestedEndDate : null;
        $hasMissingRentalDate = collect($cartItems ?? [])->contains(function ($item) {
            return empty($item['rental_start_date']) || empty($item['rental_end_date']);
        });
    @endphp

    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-blue-700">{{ __('ui.cart.title') }}</h2>
                <p class="text-sm text-slate-500">{{ __('ui.cart.subtitle') }}</p>
            </div>
            <a href="{{ route('catalog') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600">← {{ __('ui.actions.back_to_catalog') }}</a>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-4">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-relaxed whitespace-pre-line text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (empty($cartItems))
                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <p class="text-sm text-slate-600">{{ __('ui.cart.empty') }}</p>
                    <a href="{{ route('catalog') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        {{ __('ui.actions.back_to_catalog') }}
                    </a>
                </div>
            @else
                @foreach ($cartItems as $item)
                    @php
                        $key = $item['key'] ?? ($item['equipment_id'] ?? $item['product_id'] ?? $item['slug'] ?? $item['name']);
                        $startDate = null;
                        $endDate = null;
                        $durationDays = null;
                        try {
                            if (! empty($item['rental_start_date']) && ! empty($item['rental_end_date'])) {
                                $startDate = \Carbon\Carbon::parse($item['rental_start_date'])->startOfDay();
                                $endDate = \Carbon\Carbon::parse($item['rental_end_date'])->startOfDay();
                                if ($endDate->gte($startDate)) {
                                    $durationDays = $startDate->diffInDays($endDate) + 1;
                                } else {
                                    $startDate = null;
                                    $endDate = null;
                                }
                            }
                        } catch (\Throwable $exception) {
                            $startDate = null;
                            $endDate = null;
                            $durationDays = null;
                        }
                        $lineEstimate = ((int) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1)) * max((int) ($durationDays ?? 1), 1);
                    @endphp
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <img
                                src="{{ $item['image'] ?? 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=600&q=80' }}"
                                alt="{{ $item['name'] }}"
                                class="h-24 w-24 rounded-xl object-cover bg-slate-100"
                            >
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-slate-900">{{ $item['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $item['category'] ?? '-' }}</p>
                                @if ($startDate && $endDate)
                                    <p class="mt-1 text-xs text-blue-700">
                                        {{ __('Sewa:') }} {{ $startDate->translatedFormat('d M Y') }} - {{ $endDate->translatedFormat('d M Y') }}
                                        ({{ $durationDays }} {{ __('hari') }})
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-amber-600">{{ __('ui.cart.missing_dates_note') }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-600">
                                        <form method="POST" action="{{ route('cart.decrement', $key) }}" class="js-cart-decrement-form" data-item-qty="{{ (int) ($item['qty'] ?? 1) }}" data-confirm-message="{{ __('ui.dialog.remove_cart_zero_qty') }}" data-confirm-title="{{ __('ui.dialog.title') }}" data-confirm-button="{{ __('ui.dialog.remove_cart_item_confirm') }}" data-cancel-button="{{ __('ui.dialog.cancel') }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="h-6 w-6 rounded-full border border-slate-200 text-xs font-semibold text-slate-600 hover:text-blue-600">-</button>
                                        </form>
                                        <span class="min-w-[20px] text-center font-semibold">{{ $item['qty'] }}</span>
                                        <form method="POST" action="{{ route('cart.increment', $key) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="h-6 w-6 rounded-full border border-slate-200 text-xs font-semibold text-slate-600 hover:text-blue-600">+</button>
                                        </form>
                                    </div>
                                    <form method="POST" action="{{ route('cart.remove', $key) }}" data-confirm="{{ __('ui.dialog.remove_cart_item') }}" data-confirm-title="{{ __('ui.dialog.title') }}" data-confirm-button="{{ __('ui.dialog.remove_cart_item_confirm') }}" data-cancel-button="{{ __('ui.dialog.cancel') }}" data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-600">
                                            {{ __('ui.actions.remove') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-xs text-slate-500">{{ __('ui.cart.subtotal_per_day') }}</p>
                                <p class="text-lg font-semibold text-slate-900">
                                    {{ $formatIdr(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ __('Estimasi item:') }} {{ $formatIdr($lineEstimate) }}</p>
                                @if (! empty($item['slug']))
                                    <a href="{{ route('product.show', $item['slug']) }}" class="mt-2 inline-block text-xs text-blue-600 hover:text-blue-700">{{ __('ui.actions.detail') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if (! empty($cartItems) && $suggestedEquipments->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                    <h3 class="text-base font-semibold text-blue-700">{{ __('ui.cart.suggestions_title') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('ui.cart.suggestions_subtitle') }}</p>
                        </div>
                        <a href="{{ route('catalog') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                            {{ __('ui.cart.view_catalog') }}
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($suggestedEquipments as $suggestion)
                            @php
                                $suggestionImagePath = $suggestion->image_path ?? $suggestion->image;
                                $suggestionFallbackImage = 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=900&q=80';
                                $suggestionImage = site_media_url($suggestionImagePath) ?: $suggestionFallbackImage;
                                $suggestionAvailable = (int) ($suggestion->available_units ?? $suggestion->stock);
                                $suggestionUrl = ! empty($suggestion->slug) ? route('product.show', $suggestion->slug) : route('catalog');
                                if ($cartSuggestedStartDate && $cartSuggestedEndDate && ! empty($suggestion->slug)) {
                                    $suggestionUrl = route('product.show', $suggestion->slug) . '?' . http_build_query([
                                        'rental_start_date' => $cartSuggestedStartDate,
                                        'rental_end_date' => $cartSuggestedEndDate,
                                        'lock_dates' => 1,
                                    ]);
                                }
                            @endphp
                            <article class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <img src="{{ $suggestionImage }}" alt="{{ $suggestion->name }}" class="h-16 w-16 rounded-lg bg-white object-cover" onerror="this.onerror=null;this.src='{{ $suggestionFallbackImage }}';">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $suggestion->name }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $suggestion->category?->name ?? __('ui.cart.gear_generic') }} • {{ __('ui.cart.available_units', ['count' => $suggestionAvailable]) }}</p>
                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <p class="text-xs font-semibold text-slate-800">{{ $formatIdr((int) $suggestion->price_per_day) }}/hari</p>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $suggestionUrl }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600">
                                                {{ __('ui.actions.detail') }}
                                            </a>
                                            <button
                                                type="button"
                                                class="js-open-suggestion-add-modal inline-flex items-center rounded-lg bg-blue-600 px-2.5 py-1 text-[11px] font-semibold text-white transition hover:bg-blue-700"
                                                data-equipment-id="{{ (int) $suggestion->id }}"
                                                data-name="{{ $suggestion->name }}"
                                                data-category="{{ $suggestion->category?->name ?? __('ui.cart.gear_generic') }}"
                                                data-image="{{ $suggestionImage }}"
                                                data-price="{{ $formatIdr((int) $suggestion->price_per_day) }}/hari"
                                                data-max-qty="{{ max(1, min($suggestionAvailable, 99)) }}"
                                                data-start-date="{{ $cartSuggestedStartDate ?? '' }}"
                                                data-end-date="{{ $cartSuggestedEndDate ?? '' }}"
                                                data-start-date-label="{{ $cartSuggestedStartDate ? \Carbon\Carbon::parse($cartSuggestedStartDate)->translatedFormat('d M Y') : '' }}"
                                                data-end-date-label="{{ $cartSuggestedEndDate ? \Carbon\Carbon::parse($cartSuggestedEndDate)->translatedFormat('d M Y') : '' }}"
                                            >
                                                {{ __('ui.cart.suggestions_add_button') }}
                                            </button>
                                        </div>
                                    </div>
                                    @if ($cartSuggestedStartDate && $cartSuggestedEndDate)
                                        <p class="mt-1 text-[11px] text-blue-700">
                                            {{ __('ui.cart.suggestions_locked_dates_note', ['start' => \Carbon\Carbon::parse($cartSuggestedStartDate)->translatedFormat('d M Y'), 'end' => \Carbon\Carbon::parse($cartSuggestedEndDate)->translatedFormat('d M Y')]) }}
                                        </p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (! empty($cartItems))
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-blue-700">{{ __('ui.cart.rules_title') }}</h3>
                    <div class="mt-3 space-y-2 text-sm">
                        <details class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <summary class="cursor-pointer font-semibold text-slate-800">{{ __('ui.cart.rules_late_title') }}</summary>
                            <p class="mt-2 text-slate-600">{{ __('ui.cart.rules_late_body') }}</p>
                        </details>
                        <details class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <summary class="cursor-pointer font-semibold text-slate-800">{{ __('ui.cart.rules_damage_title') }}</summary>
                            <p class="mt-2 text-slate-600">{{ __('ui.cart.rules_damage_body') }}</p>
                        </details>
                        <details class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <summary class="cursor-pointer font-semibold text-slate-800">{{ __('ui.cart.rules_loss_title') }}</summary>
                            <p class="mt-2 text-slate-600">{{ __('ui.cart.rules_loss_body') }}</p>
                        </details>
                    </div>
                </section>
            @endif
            </div>

            <div class="h-fit self-start rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                <h3 class="text-lg font-semibold text-blue-700">{{ __('ui.cart.summary') }}</h3>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>{{ __('ui.cart.subtotal_per_day') }}</span>
                        <span>{{ $formatIdr($subtotalPerDay) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>{{ __('ui.cart.estimated_total') }}</span>
                        <span>{{ $formatIdr($estimatedSubtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>{{ __('ui.cart.tax') }}</span>
                        <span>{{ $formatIdr($taxAmount) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-3 font-semibold text-slate-900">
                        <span>{{ __('ui.cart.total') }}</span>
                        <span>{{ $formatIdr($estimatedTotal) }}</span>
                    </div>
                </div>
                @if ($hasMissingRentalDate)
                    <p class="mt-3 text-xs text-amber-600">
                        {{ __('ui.cart.missing_dates_warning') }}
                    </p>
                @endif
                <a
                    href="{{ route('checkout') }}"
                    class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition {{ empty($cartItems) || $hasMissingRentalDate ? 'pointer-events-none opacity-50' : '' }}"
                >
                    {{ __('ui.actions.continue_checkout') }}
                </a>
            </div>
        </div>

        @if (! empty($cartItems) && $suggestedEquipments->isNotEmpty())
            <div id="suggestion-add-modal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/55 p-4" role="dialog" aria-modal="true" aria-labelledby="suggestion-add-modal-title">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 id="suggestion-add-modal-title" class="text-base font-semibold text-slate-900">{{ __('ui.cart.suggestions_add_title') }}</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ __('ui.cart.suggestions_subtitle') }}</p>
                        </div>
                        <button type="button" class="rounded-lg border border-slate-200 p-1.5 text-slate-500 transition hover:border-blue-200 hover:text-blue-600" data-modal-close aria-label="{{ __('ui.actions.close') }}">
                            ✕
                        </button>
                    </div>

                    <div class="mt-4 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <img id="suggestion-modal-image" src="" alt="Suggestion" class="h-14 w-14 rounded-lg bg-white object-cover">
                        <div class="min-w-0 flex-1">
                            <p id="suggestion-modal-name" class="truncate text-sm font-semibold text-slate-900"></p>
                            <p id="suggestion-modal-category" class="text-xs text-slate-500"></p>
                            <p id="suggestion-modal-price" class="mt-1 text-xs font-semibold text-slate-800"></p>
                        </div>
                    </div>

                    <p id="suggestion-modal-dates" class="mt-3 hidden text-xs text-blue-700"></p>

                    <form id="suggestion-add-form" method="POST" action="{{ route('cart.add') }}" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="equipment_id" id="suggestion-form-equipment-id">
                        <input type="hidden" name="qty" id="suggestion-form-qty" value="1">
                        <input type="hidden" name="rental_start_date" id="suggestion-form-start-date">
                        <input type="hidden" name="rental_end_date" id="suggestion-form-end-date">

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.cart.suggestions_qty_label') }}</p>
                            <div class="mt-2 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <button type="button" class="h-7 w-7 rounded-full border border-slate-300 text-sm font-semibold text-slate-600 transition hover:border-blue-300 hover:text-blue-700" data-qty-action="decrement">-</button>
                                <span id="suggestion-qty-display" class="min-w-[28px] text-center text-sm font-semibold text-slate-900">1</span>
                                <button type="button" class="h-7 w-7 rounded-full border border-slate-300 text-sm font-semibold text-slate-600 transition hover:border-blue-300 hover:text-blue-700" data-qty-action="increment">+</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-800" data-modal-close>
                                {{ __('ui.catalog.quick_cancel_button') }}
                            </button>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                {{ __('ui.cart.suggestions_confirm_add') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const forms = document.querySelectorAll('.js-cart-decrement-form');
            if (!forms.length) {
                return;
            }

            forms.forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (form.dataset.zeroConfirmDone === '1') {
                        form.dataset.zeroConfirmDone = '';
                        return;
                    }

                    const currentQty = Number(form.dataset.itemQty || '1');
                    if (currentQty > 1) {
                        return;
                    }

                    event.preventDefault();

                    if (typeof window.manakeConfirm !== 'function') {
                        form.submit();
                        return;
                    }

                    window.manakeConfirm({
                        title: form.dataset.confirmTitle || '{{ __('ui.dialog.title') }}',
                        message: form.dataset.confirmMessage || '{{ __('ui.dialog.remove_cart_zero_qty') }}',
                        confirmText: form.dataset.confirmButton || '{{ __('ui.dialog.remove_cart_item_confirm') }}',
                        cancelText: form.dataset.cancelButton || '{{ __('ui.dialog.cancel') }}',
                        variant: 'danger',
                        onConfirm: () => {
                            form.dataset.zeroConfirmDone = '1';
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                            } else {
                                form.submit();
                            }
                        },
                    });
                });
            });
        })();

        (() => {
            const modal = document.getElementById('suggestion-add-modal');
            if (!modal) {
                return;
            }

            const openButtons = document.querySelectorAll('.js-open-suggestion-add-modal');
            if (!openButtons.length) {
                return;
            }

            const modalName = document.getElementById('suggestion-modal-name');
            const modalCategory = document.getElementById('suggestion-modal-category');
            const modalPrice = document.getElementById('suggestion-modal-price');
            const modalImage = document.getElementById('suggestion-modal-image');
            const modalDates = document.getElementById('suggestion-modal-dates');
            const lockedDatesTemplate = @json(__('ui.cart.suggestions_locked_dates_note', ['start' => ':start', 'end' => ':end']));

            const equipmentIdInput = document.getElementById('suggestion-form-equipment-id');
            const qtyInput = document.getElementById('suggestion-form-qty');
            const startDateInput = document.getElementById('suggestion-form-start-date');
            const endDateInput = document.getElementById('suggestion-form-end-date');
            const qtyDisplay = document.getElementById('suggestion-qty-display');

            const decrementButton = modal.querySelector('[data-qty-action="decrement"]');
            const incrementButton = modal.querySelector('[data-qty-action="increment"]');
            const closeButtons = modal.querySelectorAll('[data-modal-close]');

            let maxQty = 99;

            const openModal = () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            const syncQty = (nextQty) => {
                const clamped = Math.max(1, Math.min(Number(nextQty || 1), maxQty));
                qtyInput.value = String(clamped);
                qtyDisplay.textContent = String(clamped);
                decrementButton?.toggleAttribute('disabled', clamped <= 1);
                incrementButton?.toggleAttribute('disabled', clamped >= maxQty);
                decrementButton?.classList.toggle('opacity-40', clamped <= 1);
                incrementButton?.classList.toggle('opacity-40', clamped >= maxQty);
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const {
                        equipmentId = '',
                        name = '',
                        category = '',
                        image = '',
                        price = '',
                        maxQty: buttonMaxQty = '99',
                        startDate = '',
                        endDate = '',
                        startDateLabel = '',
                        endDateLabel = '',
                    } = button.dataset;

                    maxQty = Math.max(1, Math.min(Number(buttonMaxQty || '99'), 99));
                    equipmentIdInput.value = equipmentId;
                    startDateInput.value = startDate;
                    endDateInput.value = endDate;

                    modalName.textContent = name;
                    modalCategory.textContent = category;
                    modalPrice.textContent = price;
                    modalImage.src = image || '{{ site_asset('MANAKE-FAV-M.png') }}';

                    if (startDate && endDate) {
                        modalDates.textContent = lockedDatesTemplate
                            .replace(':start', startDateLabel || startDate)
                            .replace(':end', endDateLabel || endDate);
                        modalDates.classList.remove('hidden');
                    } else {
                        modalDates.textContent = '';
                        modalDates.classList.add('hidden');
                    }

                    syncQty(1);
                    openModal();
                });
            });

            decrementButton?.addEventListener('click', () => {
                syncQty(Number(qtyInput.value || '1') - 1);
            });

            incrementButton?.addEventListener('click', () => {
                syncQty(Number(qtyInput.value || '1') + 1);
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('flex')) {
                    closeModal();
                }
            });
        })();
    </script>
@endpush
