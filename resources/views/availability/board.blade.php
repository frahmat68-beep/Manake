@extends('layouts.landing')

@section('title', setting('copy.availability.title', __('ui.availability_board.title')))
@section('meta_description', setting('copy.availability.subtitle', __('ui.availability_board.subtitle')))

@php
    $monthValue = $monthDate->format('Y-m');
    $selectedDateValue = $selectedDate->toDateString();
    $monthLabel = $monthDate->translatedFormat('F Y');
    $selectedDateLabel = $selectedDate->translatedFormat('d M Y');
    $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
    $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
    $windowStartValue = $windowStartDate->toDateString();
    $windowEndValue = $windowEndDate->toDateString();
    $windowStartMonthValue = $windowStartDate->format('Y-m');
    $windowEndMonthValue = $windowEndDate->format('Y-m');
    $canGoPrev = $prevMonth >= $windowStartMonthValue;
    $canGoNext = $nextMonth <= $windowEndMonthValue;
    $weekdayLabels = trans('ui.availability_board.weekdays');
    if (! is_array($weekdayLabels) || count($weekdayLabels) !== 7) {
        $weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    }
    $intlLocale = app()->getLocale() === 'en' ? 'en-US' : 'id-ID';
    $toneClasses = [
        'calm' => 'border-slate-200 bg-white text-slate-700',
        'busy' => 'border-amber-200 bg-amber-50 text-amber-800',
        'critical' => 'border-rose-200 bg-rose-50 text-rose-800',
    ];
    $availabilityTitle = setting('copy.availability.title', __('ui.availability_board.title'));
    $availabilitySubtitle = setting('copy.availability.subtitle', __('ui.availability_board.subtitle'));
    $availabilityCalendarTitle = setting('copy.availability.calendar_title', __('ui.availability_board.calendar_title'));
    $availabilitySelectedTitle = setting('copy.availability.selected_title', __('ui.availability_board.selected_title'));
    $availabilityReadyTitle = setting('copy.availability.ready_title', __('ui.availability_board.ready_title'));
    $availabilityBusyTitle = setting('copy.availability.busy_title', __('ui.availability_board.busy_title'));
    $availabilityMonthlyTitle = setting('copy.availability.monthly_title', __('ui.availability_board.monthly_title'));
    $availabilitySearchPlaceholder = setting('copy.availability.search_placeholder', __('ui.availability_board.search_placeholder'));
    $availabilityShowButton = setting('copy.availability.show_button', __('ui.availability_board.show_button'));
    $availabilityResetButton = setting('copy.availability.reset_button', __('ui.availability_board.reset_button'));
    $availabilityDragHint = setting('copy.availability.drag_hint', __('ui.availability_board.drag_hint'));
    $availabilityMetricTotal = setting('copy.availability.metric_total', __('ui.availability_board.metric_total'));
    $availabilityMetricBusy = setting('copy.availability.metric_busy', __('ui.availability_board.metric_busy'));
    $availabilityMetricAvailable = setting('copy.availability.metric_available', __('ui.availability_board.metric_available'));
    $availabilityMetricUnits = setting('copy.availability.metric_units', __('ui.availability_board.metric_units'));
    $availabilityReadyEmpty = setting('copy.availability.ready_empty', __('ui.availability_board.ready_empty'));
    $availabilityBusyEmpty = setting('copy.availability.busy_empty', __('ui.availability_board.busy_empty'));
    $availabilityMonthlyEmpty = setting('copy.availability.monthly_empty', __('ui.availability_board.monthly_empty'));
    $availabilityModalDateTitle = setting('copy.availability.modal_date_title', __('ui.availability_board.modal_date_title'));
    $availabilityModalClose = setting('copy.availability.modal_close', __('ui.availability_board.modal_close'));
    $availabilityModalEmpty = setting('copy.availability.modal_empty', __('ui.availability_board.modal_empty'));
    $availabilityRangeKicker = setting('copy.availability.range_kicker', __('ui.availability_board.range_kicker'));
    $availabilityRangeTitle = setting('copy.availability.range_title', __('ui.availability_board.range_title'));
    $availabilityRangeFilterLabel = setting('copy.availability.range_filter_label', __('ui.availability_board.range_filter_label'));
    $availabilityRangeAllCategories = setting('copy.availability.range_all_categories', __('ui.availability_board.range_all_categories'));
    $availabilityRangeAvailableLabel = setting('copy.availability.range_available_label', __('ui.availability_board.range_available_label'));
    $availabilityRangeContinue = setting('copy.availability.range_continue', __('ui.availability_board.range_continue'));
    $availabilityRangeEmpty = setting('copy.availability.range_empty', __('ui.availability_board.range_empty'));
    $availabilityRangePick = setting('copy.availability.range_pick', __('ui.availability_board.range_pick'));
    $availabilityRangePrefillNote = setting('copy.availability.range_prefill_note', __('ui.availability_board.range_prefill_note'));
    $availabilityCountEmptySuffix = __('ui.availability_board.count_empty_suffix');
    $availabilityCountToolsSuffix = __('ui.availability_board.count_tools_suffix');
    $availabilityCountSchedulesSuffix = __('ui.availability_board.count_schedules_suffix');
    $availabilityInUseTemplate = __('ui.availability_board.in_use_template');
    $availabilityPeriodLabel = __('ui.availability_board.period_label');
    $availabilityFromPriceLabel = __('ui.availability_board.from_price_label');
    $availabilityMinLeftTemplate = __('ui.availability_board.min_left_template');
    $availabilityDayLabel = __('app.product.day_label');
    $currencyPrefix = app()->getLocale() === 'en' ? 'IDR' : 'Rp';

    $equipmentClientRows = $equipmentRows
        ->map(function (array $row) {
            $dayCells = collect($row['day_cells'] ?? [])->map(function ($cell) {
                return [
                    'reserved' => (int) data_get($cell, 'reserved', 0),
                    'available' => (int) data_get($cell, 'available', 0),
                    'is_blocked' => (bool) data_get($cell, 'is_blocked', false),
                ];
            })->all();

            return [
                'id' => (int) data_get($row, 'id', 0),
                'name' => (string) data_get($row, 'name', __('app.product.generic')),
                'slug' => (string) data_get($row, 'slug', ''),
                'category' => (string) data_get($row, 'category', app()->isLocale('en') ? 'Other' : 'Lainnya'),
                'category_id' => (int) data_get($row, 'category_id', 0),
                'price_per_day' => (int) data_get($row, 'price_per_day', 0),
                'image_url' => (string) data_get($row, 'image_url', site_asset('MANAKE-FAV-M.png')),
                'status' => (string) data_get($row, 'status', 'ready'),
                'stock' => (int) data_get($row, 'stock', 0),
                'day_cells' => $dayCells,
            ];
        })
        ->values();
@endphp

@push('head')
    <style>
        @keyframes availabilitySurfaceIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .board-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .board-input-group svg {
            position: absolute;
            left: 1rem;
            color: var(--text-soft);
            pointer-events: none;
            font-size: 0.875rem;
        }

        .board-cell {
            transition: all 0.2s ease;
            user-select: none;
        }

        .board-cell:hover:not(:disabled) {
            transform: translateY(-1px);
            border-color: var(--primary-soft);
        }

        @keyframes boardRangePulse {
            0% {
                box-shadow: 0 0 0 0 rgba(212, 168, 67, 0.22);
            }
            70% {
                box-shadow: 0 0 0 9px rgba(212, 168, 67, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(212, 168, 67, 0);
            }
        }

        .board-cell--range {
            border-color: #D4A843 !important;
            background: linear-gradient(160deg, rgba(212, 168, 67, 0.16) 0%, rgba(212, 168, 67, 0.26) 100%) !important;
            color: #E8E8EC !important;
            box-shadow: 0 0 0 1px rgba(212, 168, 67, 0.2), 0 14px 28px -24px rgba(212, 168, 67, 0.55) !important;
        }

        .board-cell--range-dragging {
            animation: boardRangePulse 0.8s ease-out infinite;
            transform: translateY(-1px) scale(1.01);
        }

        .board-cell--locked {
            opacity: 0.45;
            cursor: not-allowed !important;
            border-style: dashed !important;
            box-shadow: none !important;
            transform: none !important;
            background: #f1f5f9 !important;
            color: #64748b !important;
        }

        html[data-theme-resolved='dark'] .board-cell--range {
            border-color: rgba(212, 168, 67, 0.72) !important;
            background: linear-gradient(160deg, rgba(33, 28, 12, 0.95) 0%, rgba(53, 41, 12, 0.92) 100%) !important;
            color: #E8E8EC !important;
            box-shadow: 0 0 0 1px rgba(212, 168, 67, 0.18), 0 16px 28px -24px rgba(0, 0, 0, 0.8) !important;
        }

        html[data-theme-resolved='dark'] .board-cell--locked {
            background: color-mix(in oklab, var(--surface-2) 90%, #0d1524) !important;
            color: var(--text-soft) !important;
        }

        .board-item {
            transition: all 0.2s ease;
        }

        .board-item:hover {
            transform: translateX(2px);
        }

        @media (prefers-reduced-motion: reduce) {
            .board-cell,
            .board-item {
                animation: none;
                transition: none;
                transform: none;
            }
        }
    </style>
@endpush

@section('content')
    <section class="mk-section">
        <div
            class="mk-container space-y-6"
        x-data="{
            schedulesByDate: @js($dailySchedulesByDate ?? []),
            equipmentRows: @js($equipmentClientRows),
            productUrlTemplate: @js(route('product.show', ['slug' => '__slug__'])),
            cartUrl: @js(route('cart')),
            scheduleModalOpen: false,
            rangeModalOpen: false,
            modalDate: '',
            modalDateLabel: '',
            modalBusyEquipments: 0,
            modalReservedUnits: 0,
            modalAvailableEquipments: 0,
            modalSchedules: [],
            isSelectingRange: false,
            selectionAnchorDate: '',
            rangeDragged: false,
            skipNextClick: false,
            selectedRangeDates: [],
            rangeCategoryOptions: [],
            selectedRangeCategoryId: 'all',
            rangeAvailableRows: [],
            dayLabel: @js($availabilityDayLabel),
            initBoard() {
                this.selectedRangeDates = [];
            },
            parseDate(value) {
                const parsed = new Date(`${value}T00:00:00`);
                if (Number.isNaN(parsed.getTime())) {
                    return null;
                }
                return parsed;
            },
            formatDateLabel(value) {
                const parsed = this.parseDate(value);
                if (!parsed) {
                    return value || '-';
                }
                return new Intl.DateTimeFormat(@js($intlLocale), {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                }).format(parsed);
            },
            buildDateRange(startDate, endDate) {
                const start = this.parseDate(startDate);
                const end = this.parseDate(endDate);
                if (!start || !end) {
                    return [];
                }

                const min = start <= end ? start : end;
                const max = start <= end ? end : start;
                const output = [];

                const cursor = new Date(min);
                while (cursor <= max) {
                    const year = cursor.getFullYear();
                    const month = String(cursor.getMonth() + 1).padStart(2, '0');
                    const day = String(cursor.getDate()).padStart(2, '0');
                    output.push(`${year}-${month}-${day}`);
                    cursor.setDate(cursor.getDate() + 1);
                }

                return output;
            },
            isDateInSelection(date) {
                return this.selectedRangeDates.includes(date);
            },
            beginDateSelection(date, isSelectable = true) {
                if (!isSelectable) {
                    return;
                }
                this.isSelectingRange = true;
                this.selectionAnchorDate = date;
                this.rangeDragged = false;
                this.selectedRangeDates = [date];
            },
            hoverDateSelection(date, isSelectable = true) {
                if (!this.isSelectingRange) {
                    return;
                }
                if (!isSelectable) {
                    return;
                }

                const nextRange = this.buildDateRange(this.selectionAnchorDate, date);
                if (nextRange.length > 1) {
                    this.rangeDragged = true;
                }
                this.selectedRangeDates = nextRange;
            },
            finishDateSelection(date, isSelectable = true) {
                if (!this.isSelectingRange) {
                    return;
                }
                if (!isSelectable) {
                    this.isSelectingRange = false;
                    this.selectionAnchorDate = '';
                    this.selectedRangeDates = [];
                    return;
                }

                this.selectedRangeDates = this.buildDateRange(this.selectionAnchorDate, date);
                const shouldOpenRangeModal = this.rangeDragged || this.selectedRangeDates.length > 1;

                this.isSelectingRange = false;
                this.selectionAnchorDate = '';

                if (shouldOpenRangeModal) {
                    this.skipNextClick = true;
                    this.openRangeSelectionModal();
                }
            },
            cancelDanglingSelection() {
                if (!this.isSelectingRange) {
                    return;
                }

                const shouldOpenRangeModal = this.rangeDragged || this.selectedRangeDates.length > 1;
                this.isSelectingRange = false;
                this.selectionAnchorDate = '';

                if (shouldOpenRangeModal) {
                    this.skipNextClick = true;
                    this.openRangeSelectionModal();
                }
            },
            handleDayClick(date, busyEquipments, reservedUnits, availableEquipments, isSelectable = true) {
                if (!isSelectable) {
                    return;
                }
                if (this.skipNextClick) {
                    this.skipNextClick = false;
                    return;
                }

                this.selectedRangeDates = [date];
                this.openScheduleModal(date, busyEquipments, reservedUnits, availableEquipments);
            },
            openScheduleModal(date, busyEquipments, reservedUnits, availableEquipments) {
                this.modalDate = date;
                this.modalDateLabel = this.formatDateLabel(date);
                this.modalBusyEquipments = busyEquipments;
                this.modalReservedUnits = reservedUnits;
                this.modalAvailableEquipments = availableEquipments;
                this.modalSchedules = Array.isArray(this.schedulesByDate[date]) ? this.schedulesByDate[date] : [];
                this.scheduleModalOpen = true;
                this.syncBodyLock();
            },
            closeScheduleModal() {
                this.scheduleModalOpen = false;
                this.syncBodyLock();
            },
            getRangeStartDate() {
                return this.selectedRangeDates.length ? this.selectedRangeDates[0] : '';
            },
            getRangeEndDate() {
                return this.selectedRangeDates.length ? this.selectedRangeDates[this.selectedRangeDates.length - 1] : '';
            },
            getRangeDurationLabel() {
                const days = this.selectedRangeDates.length;
                if (days <= 0) {
                    return '-';
                }
                return `${days} ${this.dayLabel}`;
            },
            computeRangeAvailability() {
                if (!this.selectedRangeDates.length) {
                    this.rangeAvailableRows = [];
                    this.rangeCategoryOptions = [];
                    return;
                }

                const rows = [];

                this.equipmentRows.forEach((row) => {
                    if (!row || String(row.status || '') !== 'ready') {
                        return;
                    }

                    let minAvailable = Number.MAX_SAFE_INTEGER;

                    this.selectedRangeDates.forEach((date) => {
                        const cell = row.day_cells && row.day_cells[date] ? row.day_cells[date] : null;
                        const available = cell ? Number(cell.available || 0) : Number(row.stock || 0);
                        minAvailable = Math.min(minAvailable, Number.isFinite(available) ? available : 0);
                    });

                    if (!Number.isFinite(minAvailable)) {
                        minAvailable = 0;
                    }

                    if (minAvailable <= 0) {
                        return;
                    }

                    rows.push({
                        ...row,
                        min_available: minAvailable,
                    });
                });

                rows.sort((left, right) => {
                    if (right.min_available !== left.min_available) {
                        return right.min_available - left.min_available;
                    }
                    return String(left.name || '').localeCompare(String(right.name || ''), 'id');
                });

                this.rangeAvailableRows = rows;

                const categoryMap = new Map();
                rows.forEach((row) => {
                    const id = String(row.category_id || 0);
                    if (!categoryMap.has(id)) {
                        categoryMap.set(id, row.category || 'Lainnya');
                    }
                });

                this.rangeCategoryOptions = Array.from(categoryMap, ([id, name]) => ({ id, name }))
                    .sort((a, b) => String(a.name).localeCompare(String(b.name), 'id'));
            },
            openRangeSelectionModal() {
                this.computeRangeAvailability();
                this.selectedRangeCategoryId = 'all';
                this.rangeModalOpen = true;
                this.syncBodyLock();
            },
            closeRangeSelectionModal() {
                this.rangeModalOpen = false;
                this.selectedRangeDates = [];
                this.rangeAvailableRows = [];
                this.rangeCategoryOptions = [];
                this.selectedRangeCategoryId = 'all';
                this.syncBodyLock();
            },
            getFilteredRangeRows() {
                if (this.selectedRangeCategoryId === 'all') {
                    return this.rangeAvailableRows;
                }

                return this.rangeAvailableRows.filter((row) => String(row.category_id || 0) === this.selectedRangeCategoryId);
            },
            buildProductUrl(slug) {
                const safeSlug = encodeURIComponent(String(slug || '').trim());
                const baseUrl = this.productUrlTemplate.replace('__slug__', safeSlug);
                const params = new URLSearchParams();

                const startDate = this.getRangeStartDate();
                const endDate = this.getRangeEndDate();
                if (startDate) {
                    params.set('rental_start_date', startDate);
                }
                if (endDate) {
                    params.set('rental_end_date', endDate);
                }

                if (!params.toString()) {
                    return baseUrl;
                }

                return `${baseUrl}?${params.toString()}`;
            },
            syncBodyLock() {
                if (this.scheduleModalOpen || this.rangeModalOpen) {
                    document.body.classList.add('overflow-hidden');
                } else {
                    document.body.classList.remove('overflow-hidden');
                }
            },
            handleEscape() {
                if (this.rangeModalOpen) {
                    this.closeRangeSelectionModal();
                    return;
                }
                if (this.scheduleModalOpen) {
                    this.closeScheduleModal();
                }
            },
        }"
        x-init="initBoard()"
        x-on:keydown.escape.window="handleEscape()"
        x-on:pointerup.window="cancelDanglingSelection()"
    >
        <section class="mk-card p-4 sm:p-6 animate-fade-up">
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <p class="section-kicker font-bold tracking-widest uppercase text-[#D4A843]/80">{{ __('ui.nav.availability_board') ?: 'PAPAN KETERSEDIAAN' }}</p>
                    <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl leading-tight">
                        {{ $availabilityTitle }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        {{ $availabilitySubtitle }}
                    </p>
                </div>

                <form method="GET" action="{{ route('availability.board') }}" class="grid w-full gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-4 sm:grid-cols-3 lg:max-w-3xl">
                    <div class="board-input-group sm:col-span-3 lg:col-span-1">
                        <svg class="absolute left-4 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="{{ $availabilitySearchPlaceholder }}"
                            class="mk-input pl-11 py-3 text-sm"
                        >
                    </div>
                    <div class="board-input-group">
                        <svg class="absolute left-4 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <input
                            type="month"
                            name="month"
                            value="{{ $monthValue }}"
                            min="{{ $windowStartMonthValue }}"
                            max="{{ $windowEndMonthValue }}"
                            class="mk-input pl-11 py-3 text-sm"
                        >
                    </div>
                    <div class="board-input-group">
                        <svg class="absolute left-4 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <input
                            type="date"
                            name="date"
                            value="{{ $selectedDateValue }}"
                            min="{{ $windowStartValue }}"
                            max="{{ $windowEndValue }}"
                            class="mk-input pl-11 py-3 text-sm"
                        >
                    </div>
                    <div class="sm:col-span-3 flex flex-wrap items-center justify-between gap-3 pt-1">
                        <div class="flex gap-2">
                             <button type="submit" class="mk-button-primary py-2.5 px-5 text-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                {{ $availabilityShowButton }}
                            </button>
                            @if ($search !== '')
                                <a href="{{ route('availability.board', ['month' => $monthValue, 'date' => $selectedDateValue]) }}" class="mk-button-secondary py-2.5 px-5 text-sm">
                                    {{ $availabilityResetButton }}
                                </a>
                            @endif
                        </div>
                        <p class="text-[11px] font-medium text-slate-500">
                            {{ __('Showing range:') }} <span class="text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($windowStartValue)->translatedFormat('d M') }} — {{ \Carbon\Carbon::parse($windowEndValue)->translatedFormat('d M Y') }}</span>
                        </p>
                    </div>
                </form>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] overflow-hidden">
            <article class="mk-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $availabilityCalendarTitle }}</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $monthLabel }}</h2>
                        <p class="mt-1 text-[11px] text-slate-500 sm:hidden">{{ $availabilityDragHint }}</p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-2 py-1">
                        @if ($canGoPrev)
                            <a
                                href="{{ route('availability.board', ['month' => $prevMonth, 'date' => $monthDate->copy()->subMonth()->startOfMonth()->toDateString(), 'q' => $search ?: null]) }}"
                                data-ui-icon-button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold transition-all"
                                aria-label="Bulan sebelumnya"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                        @else
                            <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg text-slate-300 dark:text-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        @endif
                        <span class="min-w-[7.5rem] text-center text-sm font-semibold text-slate-700 dark:text-slate-300 sm:min-w-[9rem]">{{ $monthLabel }}</span>
                        @if ($canGoNext)
                            <a
                                href="{{ route('availability.board', ['month' => $nextMonth, 'date' => $monthDate->copy()->addMonth()->startOfMonth()->toDateString(), 'q' => $search ?: null]) }}"
                                data-ui-icon-button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold transition-all"
                                aria-label="Bulan berikutnya"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg text-slate-300 dark:text-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="px-3 py-3 sm:px-5 sm:py-4">
                    <!-- Elegant horizontal scroll indicator for mobile -->
                    <div class="flex items-center gap-1.5 mb-3 px-1 text-[10px] font-semibold text-slate-400 dark:text-slate-500 sm:hidden">
                        <svg class="h-3.5 w-3.5 animate-pulse text-[#D4A843]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span>Geser mendatar untuk melihat kalender penuh / Swipe horizontally</span>
                    </div>

                    <div class="-mx-1 overflow-x-auto pb-2 px-1 sm:mx-0 sm:overflow-visible sm:px-0 scrollbar-thin">
                        <div class="min-w-[32rem] sm:min-w-0">
                            <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                                @foreach ($weekdayLabels as $weekday)
                                    <p class="text-center text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400 sm:text-[11px]">{{ $weekday }}</p>
                                @endforeach
                            </div>
                            <div class="mt-2 grid grid-cols-7 gap-1.5 sm:gap-2">
                                @foreach ($calendarDays as $day)
                                    @php
                                        $toneClass = $toneClasses[$day['tone']] ?? $toneClasses['calm'];
                                        $selectedClass = $day['is_selected'] ? 'ring-2 ring-[#D4A843] shadow-md shadow-[#D4A843]/20' : '';
                                        $todayClass = $day['is_today'] ? 'text-[#D4A843] font-bold' : '';
                                        $isSelectable = (bool) ($day['is_selectable'] ?? false);
                                        $lockedClass = $isSelectable ? '' : 'board-cell--locked';
                                        $hasUsage = (int) $day['busy_equipments'] > 0 || (int) $day['reserved_units'] > 0;
                                    @endphp
                                    <button
                                        type="button"
                                        class="board-cell group flex h-[4.5rem] w-full flex-col rounded-lg border px-1.5 py-2 text-left sm:h-[8.75rem] sm:rounded-xl sm:px-2 sm:py-2.5 {{ $toneClass }} {{ $selectedClass }} {{ $lockedClass }} {{ $day['in_month'] ? '' : 'opacity-55' }}"
                                        @if ($isSelectable)
                                            @pointerdown.prevent="beginDateSelection('{{ $day['date'] }}', true)"
                                            @pointerenter="hoverDateSelection('{{ $day['date'] }}', true)"
                                            @pointerup.prevent="finishDateSelection('{{ $day['date'] }}', true)"
                                            @click.prevent="handleDayClick('{{ $day['date'] }}', {{ (int) $day['busy_equipments'] }}, {{ (int) $day['reserved_units'] }}, {{ (int) $day['available_equipments'] }}, true)"
                                        @endif
                                        @unless ($isSelectable)
                                            disabled
                                            aria-disabled="true"
                                        @endunless
                                        x-bind:class="{
                                            'board-cell--range': isDateInSelection('{{ $day['date'] }}'),
                                            'board-cell--range-dragging': isSelectingRange && isDateInSelection('{{ $day['date'] }}')
                                        }"
                                        aria-haspopup="dialog"
                                        aria-label="{{ __('ui.actions.detail') }} {{ \Carbon\Carbon::parse($day['date'])->translatedFormat('d F Y') }}"
                                    >
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-[11px] font-semibold sm:text-xs {{ $todayClass }}">{{ $day['day'] }}</p>
                                            @if ($day['is_selected'])
                                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-[#D4A843]"></span>
                                            @endif
                                        </div>

                                        @if ($hasUsage)
                                            <div class="mt-auto">
                                                <p class="text-[10px] font-semibold leading-tight sm:hidden">
                                                    {{ $day['busy_equipments'] }} {{ $availabilityCountToolsSuffix }}
                                                </p>
                                                <p class="hidden text-[11px] font-semibold leading-tight sm:block">
                                                    {{ $day['busy_equipments'] }} {{ $availabilityCountToolsSuffix }}
                                                </p>
                                                <p class="mt-0.5 hidden text-[10px] leading-tight sm:block">
                                                    {{ strtr($availabilityInUseTemplate, [':qty' => (string) $day['reserved_units']]) }}
                                                </p>
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <div class="space-y-4">
                <article class="mk-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $availabilitySelectedTitle }}</p>
                    <h2 class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $selectedDateLabel }}</h2>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 px-3 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $availabilityMetricTotal }}</p>
                            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $summary['total_equipments'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-rose-100 dark:border-rose-950/20 bg-rose-50/50 dark:bg-rose-950/10 px-3 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-500 dark:text-rose-450">{{ $availabilityMetricBusy }}</p>
                            <p class="mt-1 text-2xl font-semibold text-rose-700 dark:text-rose-400">{{ $summary['busy_equipments'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-emerald-100 dark:border-emerald-950/20 bg-emerald-50/50 dark:bg-emerald-950/10 px-3 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">{{ $availabilityMetricAvailable }}</p>
                            <p class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-450">{{ $summary['available_equipments'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 px-3 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $availabilityMetricUnits }}</p>
                            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $summary['reserved_units'] ?? 0 }}</p>
                        </div>
                    </div>
                </article>

                <article class="mk-card p-6">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $availabilityReadyTitle }}</h3>
                        <span class="mk-badge mk-badge-success shrink-0">
                            {{ $selectedFreeRows->count() }} {{ $availabilityCountEmptySuffix }}
                        </span>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse ($selectedFreeRows->take(6) as $row)
                            <article class="board-item rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 px-3.5 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $row['name'] }}</p>
                                    <span class="mk-badge mk-badge-success shrink-0">
                                        {{ strtr($availabilityMinLeftTemplate, [':qty' => (string) $row['selected_available']]) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs italic text-slate-500 dark:text-slate-400">{{ $row['category'] }}</p>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/30 px-3 py-3 text-xs text-slate-500 dark:text-slate-400">
                                {{ $availabilityReadyEmpty }}
                            </p>
                        @endforelse
                    </div>
                </article>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <article class="mk-card p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $availabilityBusyTitle }} di {{ $selectedDateLabel }}</h2>
                    <span class="mk-badge mk-badge-danger shrink-0">{{ $selectedBusyRows->count() }} {{ $availabilityCountToolsSuffix }}</span>
                </div>
                <div class="mt-3 space-y-2">
                    @forelse ($selectedBusyRows->take(10) as $row)
                        <article class="board-item rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 px-3.5 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $row['name'] }}</p>
                                <p class="text-xs font-semibold text-rose-600 dark:text-rose-400">{{ strtr($availabilityInUseTemplate, [':qty' => (string) $row['selected_reserved']]) }}</p>
                            </div>
                            @if ($row['source_labels']->isNotEmpty())
                                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ $row['source_labels']->implode(', ') }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/30 px-3 py-3 text-xs text-slate-500 dark:text-slate-400">
                            {{ $availabilityBusyEmpty }}
                        </p>
                    @endforelse
                </div>
            </article>

            <article class="mk-card p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $availabilityMonthlyTitle }}</h2>
                    <span class="mk-badge mk-badge-info shrink-0">{{ $monthlySchedules->count() }} {{ $availabilityCountSchedulesSuffix }}</span>
                </div>
                <div class="mt-3 max-h-[29rem] space-y-2 overflow-y-auto pr-1">
                    @forelse ($monthlySchedules as $schedule)
                        <article class="board-item rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 px-3.5 py-3">
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $schedule['equipment_name'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ \Carbon\Carbon::parse($schedule['start_date'])->translatedFormat('d M Y') }}
                                -
                                {{ \Carbon\Carbon::parse($schedule['end_date'])->translatedFormat('d M Y') }}
                                • Qty {{ $schedule['qty'] }}
                            </p>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                {{ strtoupper($schedule['status_pesanan']) }}
                            </p>
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/30 px-3 py-3 text-xs text-slate-500 dark:text-slate-400">
                            {{ $availabilityMonthlyEmpty }}
                        </p>
                    @endforelse
                </div>
            </article>
        </section>

        <div
            x-cloak
            x-show="scheduleModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-[95] flex items-end justify-center p-2 sm:items-center sm:p-6"
            role="dialog"
            aria-modal="true"
            @click.self="closeScheduleModal()"
        >
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[1.5px]"></div>

            <div class="mk-card relative z-10 w-full max-w-3xl max-h-[90vh] overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 animate-fade-in">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 px-4 py-3 sm:px-5 sm:py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $availabilityModalDateTitle }}</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-slate-100" x-text="modalDateLabel"></h2>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-850 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-all font-semibold"
                        @click="closeScheduleModal()"
                        aria-label="{{ $availabilityModalClose }}"
                    >
                        ✕
                    </button>
                </div>

                <div class="px-4 py-4 sm:px-5 overflow-y-auto max-h-[calc(90vh-5rem)]">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-rose-100 dark:border-rose-950/20 bg-rose-50/50 dark:bg-rose-950/10 px-3.5 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-500 dark:text-rose-450">{{ $availabilityMetricBusy }}</p>
                            <p class="mt-1.5 text-xl font-bold text-rose-700 dark:text-rose-400" x-text="modalBusyEquipments"></p>
                        </div>
                        <div class="rounded-xl border border-amber-100 dark:border-amber-950/20 bg-amber-50/50 dark:bg-amber-950/10 px-3.5 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-450">{{ $availabilityMetricUnits }}</p>
                            <p class="mt-1.5 text-xl font-bold text-amber-700 dark:text-amber-400" x-text="modalReservedUnits"></p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 dark:border-emerald-950/20 bg-emerald-50/50 dark:bg-emerald-950/10 px-3.5 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-450">{{ $availabilityMetricAvailable }}</p>
                            <p class="mt-1.5 text-xl font-bold text-emerald-700 dark:text-emerald-400" x-text="modalAvailableEquipments"></p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2.5">
                        <template x-if="modalSchedules.length === 0">
                            <p class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/30 px-4 py-4 text-xs font-medium text-slate-500 dark:text-slate-400">
                                {{ $availabilityModalEmpty }}
                            </p>
                        </template>

                        <template x-for="(item, index) in modalSchedules" :key="`${item.equipment_name}-${index}`">
                            <article class="board-item rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 px-3.5 py-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100" x-text="item.equipment_name"></p>
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400" x-text="item.status_label"></p>
                                    </div>
                                    <span class="rounded-full bg-[#111113] border border-[#1A1A1E] px-2 py-0.5 text-[10px] font-bold text-[#D4A843]" x-text="`x${item.qty}`"></span>
                                </div>
                                <p class="mt-2 text-xs text-slate-650 dark:text-slate-350">
                                    {{ $availabilityPeriodLabel }}: <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="`${formatDateLabel(item.start_date)} - ${formatDateLabel(item.end_date)}`"></span>
                                </p>
                            </article>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div
            x-cloak
            x-show="rangeModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-[96] flex items-end justify-center p-2 sm:items-center sm:p-6"
            role="dialog"
            aria-modal="true"
            @click.self="closeRangeSelectionModal()"
        >
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[1.5px]"></div>

            <div class="mk-card relative z-10 w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 animate-fade-in">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 px-4 py-3 sm:px-5 sm:py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $availabilityRangeKicker }}</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-slate-100">{{ $availabilityRangeTitle }}</h2>
                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="formatDateLabel(getRangeStartDate())"></span>
                            -
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="formatDateLabel(getRangeEndDate())"></span>
                            •
                            <span class="font-semibold text-[#D4A843]" x-text="getRangeDurationLabel()"></span>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-850 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-all font-semibold"
                        @click="closeRangeSelectionModal()"
                        aria-label="{{ $availabilityModalClose }}"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto px-4 py-4 sm:px-5 max-h-[calc(90vh-6rem)]">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-end">
                        <div>
                            <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $availabilityRangeFilterLabel }}</label>
                            <select
                                x-model="selectedRangeCategoryId"
                                class="mk-input mt-1 w-full py-2 text-sm"
                            >
                                <option value="all">{{ $availabilityRangeAllCategories }}</option>
                                <template x-for="category in rangeCategoryOptions" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 px-3.5 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-450">{{ $availabilityRangeAvailableLabel }}</p>
                            <p class="text-base font-bold text-emerald-700 dark:text-emerald-400" x-text="getFilteredRangeRows().length"></p>
                        </div>
                        <a
                            :href="cartUrl"
                            class="mk-button-primary py-2.5 px-5 text-sm"
                        >
                            {{ $availabilityRangeContinue }}
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        <template x-if="getFilteredRangeRows().length === 0">
                            <p class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/30 px-4 py-4 text-xs font-medium text-slate-500 dark:text-slate-400">
                                {{ $availabilityRangeEmpty }}
                            </p>
                        </template>

                        <template x-for="item in getFilteredRangeRows()" :key="`range-item-${item.id}`">
                            <article class="board-item rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 px-3.5 py-3">
                                <div class="flex items-start gap-3.5">
                                    <img
                                        :src="item.image_url"
                                        :alt="item.name"
                                        class="h-14 w-14 rounded-lg border border-slate-200 dark:border-slate-750 bg-white dark:bg-slate-800 object-cover"
                                        loading="lazy"
                                    >
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100" x-text="item.name"></p>
                                        <p class="mt-0.5 text-xs italic text-slate-500 dark:text-slate-400" x-text="item.category"></p>
                                        <p class="mt-1 text-[11px] font-bold text-[#D4A843]">
                                            {{ $availabilityFromPriceLabel }} <span x-text="`{{ $currencyPrefix }} ${Number(item.price_per_day || 0).toLocaleString(@js($intlLocale))}`"></span> {{ __('app.product.per_day') }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-150 dark:border-emerald-900 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400" x-text="@js($availabilityMinLeftTemplate).replace(':qty', item.min_available)"></span>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-3 border-t border-slate-200/50 dark:border-slate-800/50 pt-2.5">
                                    <a
                                        :href="buildProductUrl(item.slug)"
                                        class="mk-button-primary py-1.5 px-3 text-[11px]"
                                    >
                                        {{ $availabilityRangePick }}
                                    </a>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400" x-text="`* ${@js($availabilityRangePrefillNote)}`"></span>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
@endsection
