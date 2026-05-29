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
        'calm' => 'border-[#1A1A1E] bg-[#111113] text-[#E8E8EC]',
        'busy' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'critical' => 'border-rose-500/20 bg-rose-500/10 text-rose-400',
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

    $availabilityKicker = setting('copy.availability.kicker', __('ui.availability_board.kicker'));
    $availabilityMonthFilterAria = __('ui.availability_board.month_filter_aria');
    $availabilityDateFilterAria = __('ui.availability_board.date_filter_aria');
    $availabilityPreviousMonthAria = __('ui.availability_board.previous_month_aria');
    $availabilityNextMonthAria = __('ui.availability_board.next_month_aria');
    $availabilityDisplayRangeLabel = __('ui.availability_board.display_range_label');
    $availabilityMobileScrollHint = __('ui.availability_board.mobile_scroll_hint');
    $availabilitySearchButton = __('ui.availability_board.search_button');
    $availabilityUnitSuffix = __('ui.availability_board.unit_suffix');
    $availabilityUnitsSuffix = __('ui.availability_board.units_suffix');

    $resolveAvailabilityCategoryName = static function (string $name): string {
        $rawName = trim($name);
        if (! app()->isLocale('en')) {
            return $rawName;
        }

        $normalized = strtolower($rawName);

        return match ($normalized) {
            'aksesoris', 'aksesori' => 'Accessories',
            'kamera' => 'Camera',
            'lensa' => 'Lens',
            'lampu', 'lighting' => 'Lighting',
            default => $rawName,
        };
    };

    $equipmentClientRows = $equipmentRows
        ->map(function (array $row) use ($resolveAvailabilityCategoryName) {
            $dayCells = collect($row['day_cells'] ?? [])->map(function ($cell) {
                return [
                    'reserved' => (int) data_get($cell, 'reserved', 0),
                    'available' => (int) data_get($cell, 'available', 0),
                    'is_blocked' => (bool) data_get($cell, 'is_blocked', false),
                ];
            })->all();

            $rawCategory = (string) data_get($row, 'category', app()->isLocale('en') ? 'Other' : 'Lainnya');

            return [
                'id' => (int) data_get($row, 'id', 0),
                'name' => (string) data_get($row, 'name', __('app.product.generic')),
                'slug' => (string) data_get($row, 'slug', ''),
                'category' => $resolveAvailabilityCategoryName($rawCategory),
                'category_id' => (int) data_get($row, 'category_id', 0),
                'price_per_day' => (int) data_get($row, 'price_per_day', 0),
                'image_url' => (string) data_get($row, 'image_url', site_asset('MANAKE-FAV-M.png')),
                'status' => (string) data_get($row, 'status', 'ready'),
                'stock' => (int) data_get($row, 'stock', 0),
                'day_cells' => $dayCells,
            ];
        })
        ->values();

    $resolveOrderStatusName = static function (string $status): string {
        $normalized = strtolower(trim($status));
        if (! app()->isLocale('en')) {
            return match ($normalized) {
                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                'diproses' => 'Diproses',
                'lunas' => 'Lunas',
                'barang_dipinjam', 'barang_diambil' => 'Sedang Disewa',
                'dikembalikan' => 'Dikembalikan',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
                'barang_rusak' => 'Klaim Kerusakan',
                default => $status,
            };
        }
        return match ($normalized) {
            'menunggu_pembayaran' => 'Waiting for Payment',
            'diproses' => 'Processed',
            'lunas' => 'Paid',
            'barang_dipinjam', 'barang_diambil' => 'Rented',
            'dikembalikan' => 'Returned',
            'selesai' => 'Completed',
            'dibatalkan' => 'Canceled',
            'barang_rusak' => 'Damaged',
            default => $status,
        };
    };

    $resolveSourceLabelName = static function (string $label): string {
        $normalized = strtolower(trim($label));
        if (! app()->isLocale('en')) {
            return match ($normalized) {
                'dipakai' => 'Dipakai',
                'buffer sebelum' => 'Buffer Sebelum',
                'buffer sesudah' => 'Buffer Sesudah',
                'maintenance' => 'Maintenance',
                default => $label,
            };
        }
        return match ($normalized) {
            'dipakai' => 'In Use',
            'buffer sebelum' => 'Buffer Before',
            'buffer sesudah' => 'Buffer After',
            'maintenance' => 'Maintenance',
            default => $label,
        };
    };

    $localizedDailySchedulesByDate = [];
    foreach (($dailySchedulesByDate ?? []) as $date => $schedules) {
        $localizedDailySchedulesByDate[$date] = array_map(function ($schedule) use ($resolveOrderStatusName) {
            $status = $schedule['status_pesanan'] ?? '';
            $schedule['status_label'] = $resolveOrderStatusName($status);
            return $schedule;
        }, $schedules);
    }

    $localizedSelectedBusyRows = $selectedBusyRows->map(function ($row) use ($resolveSourceLabelName, $resolveAvailabilityCategoryName) {
        if (isset($row['source_labels'])) {
            $row['source_labels'] = collect($row['source_labels'])->map($resolveSourceLabelName);
        }
        if (isset($row['category'])) {
            $row['category'] = $resolveAvailabilityCategoryName($row['category']);
        }
        return $row;
    });

    $localizedSelectedFreeRows = $selectedFreeRows->map(function ($row) use ($resolveAvailabilityCategoryName) {
        if (isset($row['category'])) {
            $row['category'] = $resolveAvailabilityCategoryName($row['category']);
        }
        return $row;
    });
@endphp

@push('head')
    <style>
        /* Custom Entrance Animations */
        .availability-enter {
            animation: availability-enter 520ms ease-out both;
        }

        .availability-card-in {
            animation: availability-card-in 520ms ease-out both;
        }

        @keyframes availability-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes availability-card-in {
            from {
                opacity: 0;
                transform: translateY(14px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .availability-enter,
            .availability-card-in {
                animation: none !important;
            }
        }

        /* Scoped CSS variables for availability board theme */
        .availability-page {
            --availability-accent: #D4A843;
            --availability-accent-hover: #E0BA5D;
            --availability-accent-text: #0A0A0B;
            --availability-accent-soft: rgba(212, 168, 67, 0.14);
            --availability-accent-border: rgba(212, 168, 67, 0.34);
            --availability-accent-shadow: rgba(212, 168, 67, 0.45);
            --availability-surface: #111113;
            --availability-surface-strong: #0A0A0B;
            --availability-text: #E8E8EC;
            --availability-muted: #A0A0A8;
        }

        html[data-theme-resolved="light"] .availability-page {
            --availability-accent: #2563EB;
            --availability-accent-hover: #1D4ED8;
            --availability-accent-text: #FFFFFF;
            --availability-accent-soft: rgba(37, 99, 235, 0.10);
            --availability-accent-border: rgba(37, 99, 235, 0.24);
            --availability-accent-shadow: rgba(37, 99, 235, 0.25);
            --availability-surface: #FFFFFF;
            --availability-surface-strong: #F8FAFC;
            --availability-text: #111827;
            --availability-muted: #4B5563;
        }

        .availability-accent-text {
            color: var(--availability-accent) !important;
        }

        .availability-accent-bg {
            background-color: var(--availability-accent) !important;
            color: var(--availability-accent-text) !important;
            border-color: var(--availability-accent) !important;
        }

        .availability-accent-bg:hover {
            background-color: var(--availability-accent-hover) !important;
        }

        .availability-accent-border {
            border-color: var(--availability-accent-border) !important;
        }

        .availability-accent-dot {
            background-color: var(--availability-accent) !important;
        }

        .availability-strong-text {
            color: var(--availability-text) !important;
        }

        .availability-muted-text {
            color: var(--availability-muted) !important;
        }

        .availability-accent-focus:focus {
            border-color: var(--availability-accent) !important;
            box-shadow: 0 0 0 2px var(--availability-accent-soft) !important;
        }

        .availability-selected-ring {
            box-shadow: 0 0 0 2px var(--availability-accent), 0 8px 20px -12px var(--availability-accent) !important;
        }

        .availability-page .availability-board-input {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #1A1A1E;
            background-color: #0A0A0B;
            color: #E8E8EC;
            outline: none;
            transition:
                background-color 160ms ease,
                border-color 160ms ease,
                box-shadow 160ms ease,
                color 160ms ease;
        }

        .availability-page .availability-board-input::placeholder {
            color: #66666C;
        }

        .availability-page .availability-board-input-icon {
            color: #6A6A78;
            pointer-events: none;
        }

        .availability-page .availability-board-input:focus {
            border-color: var(--availability-accent, #D4A843);
            box-shadow: 0 0 0 2px var(--availability-accent-soft, rgba(212, 168, 67, 0.18));
        }

        .availability-suggestions-dropdown {
            border-color: rgba(255, 255, 255, 0.10);
            background: rgba(17, 17, 19, 0.96);
        }

        .availability-suggestion-link:hover,
        .availability-suggestion-link:focus {
            background: rgba(255, 255, 255, 0.05);
        }

        .availability-suggestion-image-box {
            background: #0A0A0B;
            border-color: #1A1A1E;
        }

        .availability-suggestion-name {
            color: #E8E8EC;
        }

        .availability-suggestion-meta {
            color: #A0A0A8;
        }

        html[data-theme-resolved="light"] .availability-page .availability-board-input {
            background-color: #FFFFFF !important;
            color: #111827 !important;
            border-color: #DADDE3 !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
        }

        html[data-theme-resolved="light"] .availability-page .availability-board-input::placeholder {
            color: #6B7280 !important;
        }

        html[data-theme-resolved="light"] .availability-page .availability-board-input-icon {
            color: #6B7280 !important;
        }

        html[data-theme-resolved="light"] .availability-page .availability-board-input:focus {
            border-color: #2563EB !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.14) !important;
        }

        html[data-theme-resolved="light"] .availability-page .availability-suggestions-dropdown {
            border-color: #E5E7EB;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 24px 60px -30px rgba(15, 23, 42, 0.25);
        }

        html[data-theme-resolved="light"] .availability-page .availability-suggestion-link:hover,
        html[data-theme-resolved="light"] .availability-page .availability-suggestion-link:focus {
            background: rgba(37, 99, 235, 0.06);
        }

        html[data-theme-resolved="light"] .availability-page .availability-suggestion-image-box {
            background: #F8FAFC;
            border-color: #E5E7EB;
        }

        html[data-theme-resolved="light"] .availability-page .availability-suggestion-name {
            color: #111827;
        }

        html[data-theme-resolved="light"] .availability-page .availability-suggestion-meta {
            color: #4B5563;
        }

        html[data-theme-resolved="light"] .availability-page input[type="month"].availability-board-input,
        html[data-theme-resolved="light"] .availability-page input[type="date"].availability-board-input {
            color-scheme: light !important;
            background-color: #FFFFFF !important;
            color: #111827 !important;
        }

        html[data-theme-resolved="dark"] .availability-page input[type="month"].availability-board-input,
        html[data-theme-resolved="dark"] .availability-page input[type="date"].availability-board-input {
            color-scheme: dark;
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
                box-shadow: 0 0 0 0 var(--availability-accent-border);
            }
            70% {
                box-shadow: 0 0 0 9px transparent;
            }
            100% {
                box-shadow: 0 0 0 0 transparent;
            }
        }

        .board-cell--range {
            border-color: var(--availability-accent) !important;
            background: linear-gradient(160deg, var(--availability-accent-soft) 0%, color-mix(in oklab, var(--availability-accent) 24%, transparent) 100%) !important;
            color: var(--availability-text) !important;
            box-shadow: 0 0 0 1px var(--availability-accent-border), 0 14px 28px -24px var(--availability-accent) !important;
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
    </style>
@endpush

@section('content')
    <section class="mk-section availability-page">
        <div
            class="mk-container space-y-6"
        x-data="{
            schedulesByDate: @js($localizedDailySchedulesByDate ?? []),
            equipmentRows: @js($equipmentClientRows),
            productUrlTemplate: @js(route('product.show', ['slug' => '__slug__'])),
            catalogUrl: @js(route('catalog')),
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
    >
        <section class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8 lg:p-10 shadow-2xl availability-enter">
            <div class="relative flex flex-col gap-6">
                <div>
                    <p class="section-kicker availability-accent-text font-bold tracking-widest uppercase">{{ $availabilityKicker }}</p>
                    <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-[#E8E8EC] sm:text-3xl leading-tight">
                        {{ $availabilityTitle }}
                    </h1>
                    <p class="mt-1 text-sm text-[#A0A0A8] leading-relaxed max-w-3xl">
                        {{ $availabilitySubtitle }}
                    </p>
                </div>

                <form method="GET" action="{{ route('availability.board') }}" class="w-full space-y-3">
                    <div class="grid w-full gap-3 grid-cols-1 sm:grid-cols-[minmax(0,1.4fr)_180px_180px_auto]">
                        <div class="board-input-group">
                            <svg class="availability-board-input-icon absolute left-4 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input
                                id="availability-search-input"
                                type="text"
                                name="q"
                                value="{{ $search }}"
                                autocomplete="off"
                                placeholder="{{ $availabilitySearchPlaceholder }}"
                                aria-label="{{ $availabilitySearchPlaceholder }}"
                                class="availability-board-input w-full pl-11 pr-12 py-3.5 text-sm"
                            >
                            <div id="availability-search-loading-spinner" class="absolute right-4 top-1/2 -translate-y-1/2 hidden">
                                <svg class="availability-accent-text h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div id="availability-search-suggestions-dropdown" class="availability-suggestions-dropdown absolute left-0 right-0 top-[calc(100%+0.5rem)] z-[80] hidden max-h-80 overflow-y-auto rounded-2xl border p-2 shadow-2xl backdrop-blur-xl space-y-1"></div>
                        </div>
                        <div class="board-input-group">
                            <svg class="availability-board-input-icon absolute left-4 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <input
                                type="month"
                                name="month"
                                value="{{ $monthValue }}"
                                min="{{ $windowStartMonthValue }}"
                                max="{{ $windowEndMonthValue }}"
                                aria-label="{{ $availabilityMonthFilterAria }}"
                                class="availability-board-input w-full pl-11 py-3.5 text-sm"
                            >
                        </div>
                        <div class="board-input-group">
                            <svg class="availability-board-input-icon absolute left-4 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <input
                                type="date"
                                name="date"
                                value="{{ $selectedDateValue }}"
                                min="{{ $windowStartValue }}"
                                max="{{ $windowEndValue }}"
                                aria-label="{{ $availabilityDateFilterAria }}"
                                class="availability-board-input w-full pl-11 py-3.5 text-sm"
                            >
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="availability-accent-bg py-3.5 px-6 text-sm flex items-center justify-center gap-2 w-full rounded-xl font-bold transition sm:w-auto">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>{{ $availabilitySearchButton }}</span>
                            </button>
                            @if ($search !== '')
                                <a href="{{ route('availability.board', ['month' => $monthValue, 'date' => $selectedDateValue]) }}" class="mk-button-secondary py-3.5 px-5 text-sm flex items-center justify-center">
                                    {{ $availabilityResetButton }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center justify-end">
                        <p class="text-[10px] font-bold text-[#A0A0A8] uppercase tracking-wider">
                            {{ $availabilityDisplayRangeLabel }} <span class="availability-strong-text">{{ \Carbon\Carbon::parse($windowStartValue)->translatedFormat('d M') }} — {{ \Carbon\Carbon::parse($windowEndValue)->translatedFormat('d M Y') }}</span>
                        </p>
                    </div>
                </form>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(360px,0.95fr)] overflow-hidden">
            <article class="rounded-3xl border border-white/5 bg-[#111113]/40 overflow-hidden shadow-xl availability-card-in">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#1A1A1E] bg-[#0A0A0B] px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#A0A0A8]">{{ $availabilityCalendarTitle }}</p>
                        <h2 class="mt-1 text-xl font-semibold text-[#E8E8EC]">{{ $monthLabel }}</h2>
                        <p class="mt-1 text-[11px] text-[#A0A0A8] sm:hidden">{{ $availabilityDragHint }}</p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-xl border border-[#1A1A1E] bg-[#111113] px-2 py-1">
                        @if ($canGoPrev)
                            <a
                                href="{{ route('availability.board', ['month' => $prevMonth, 'date' => $monthDate->copy()->subMonth()->startOfMonth()->toDateString(), 'q' => $search ?: null]) }}"
                                data-ui-icon-button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#0A0A0B] hover:bg-[#1A1A1E] text-[#E8E8EC] font-bold transition-all"
                                aria-label="{{ $availabilityPreviousMonthAria }}"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                        @else
                            <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg text-[#1A1A1E]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        @endif
                        <span class="min-w-[7.5rem] text-center text-sm font-semibold text-[#E8E8EC] sm:min-w-[9rem]">{{ $monthLabel }}</span>
                        @if ($canGoNext)
                            <a
                                href="{{ route('availability.board', ['month' => $nextMonth, 'date' => $monthDate->copy()->addMonth()->startOfMonth()->toDateString(), 'q' => $search ?: null]) }}"
                                data-ui-icon-button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#0A0A0B] hover:bg-[#1A1A1E] text-[#E8E8EC] font-bold transition-all"
                                aria-label="{{ $availabilityNextMonthAria }}"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg text-[#1A1A1E]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="px-3 py-3 sm:px-5 sm:py-4">
                    <!-- Elegant horizontal scroll indicator for mobile -->
                    <div class="flex items-center gap-1.5 mb-3 px-1 text-[10px] font-semibold text-[#A0A0A8] sm:hidden">
                        <svg class="h-3.5 w-3.5 animate-pulse availability-accent-text" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span>{{ $availabilityMobileScrollHint }}</span>
                    </div>

                    <div class="-mx-1 overflow-x-auto pb-2 px-1 sm:mx-0 sm:overflow-visible sm:px-0 scrollbar-thin">
                        <div class="min-w-[32rem] sm:min-w-0">
                            <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                                @foreach ($weekdayLabels as $weekday)
                                    <p class="text-center text-[10px] font-semibold uppercase tracking-[0.16em] text-[#6A6A78] sm:text-[11px]">{{ $weekday }}</p>
                                @endforeach
                            </div>
                            <div class="mt-2 grid grid-cols-7 gap-1.5 sm:gap-2">
                                @foreach ($calendarDays as $day)
                                    @php
                                        $toneClass = $toneClasses[$day['tone']] ?? $toneClasses['calm'];
                                        $selectedClass = $day['is_selected'] ? 'availability-selected-ring' : '';
                                        $todayClass = $day['is_today'] ? 'availability-accent-text font-bold' : '';
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
                                        aria-label="{{ $day['day'] }}, {{ __('ui.actions.detail') }} {{ \Carbon\Carbon::parse($day['date'])->translatedFormat('d F Y') }}"
                                    >
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-[11px] font-semibold sm:text-xs {{ $todayClass }}">{{ $day['day'] }}</p>
                                            @if ($day['is_selected'])
                                                <span class="inline-flex h-2.5 w-2.5 rounded-full availability-accent-dot"></span>
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
                <article class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl availability-card-in">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#A0A0A8]">{{ $availabilitySelectedTitle }}</p>
                    <h2 class="mt-1 text-2xl font-semibold text-[#E8E8EC]">{{ $selectedDateLabel }}</h2>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/5 bg-[#0A0A0B]/40 px-3 py-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-[#A0A0A8]">{{ $availabilityMetricTotal }}</p>
                            <p class="mt-1 text-2xl font-bold text-[#E8E8EC]">{{ $summary['total_equipments'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-3 py-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-500">{{ $availabilityMetricBusy }}</p>
                            <p class="mt-1 text-2xl font-bold text-rose-400">{{ $summary['busy_equipments'] ?? 0 }}</p>
                            @php
                                $reservedUnitCount = (int) ($summary['reserved_units'] ?? 0);
                            @endphp
                            <p class="mt-1.5 text-[9px] font-bold text-rose-400/80 uppercase tracking-tight">
                                {{ $reservedUnitCount }} {{ $reservedUnitCount === 1 ? $availabilityUnitSuffix : $availabilityUnitsSuffix }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-3 py-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-500">{{ $availabilityMetricAvailable }}</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-400">{{ $summary['available_equipments'] ?? 0 }}</p>
                        </div>
                    </div>
                </article>

                <article class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl availability-card-in">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <h3 class="text-base font-semibold text-[#E8E8EC]">{{ $availabilityReadyTitle }}</h3>
                        <span class="mk-badge mk-badge-success shrink-0">
                            {{ $localizedSelectedFreeRows->count() }} {{ $availabilityCountEmptySuffix }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @forelse ($localizedSelectedFreeRows->take(6) as $row)
                            <article class="board-item rounded-xl border border-white/5 bg-[#0A0A0B]/40 px-3.5 py-2.5">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-[#E8E8EC]">{{ $row['name'] }}</p>
                                    <span class="mk-badge mk-badge-success shrink-0">
                                        {{ strtr($availabilityMinLeftTemplate, [':qty' => (string) $row['selected_available']]) }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-[#A0A0A8]">{{ $row['category'] }}</p>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-white/5 bg-[#0A0A0B]/40 px-3 py-3 text-xs text-[#A0A0A8]">
                                {{ $availabilityReadyEmpty }}
                            </p>
                        @endforelse
                    </div>
                </article>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <article class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl availability-card-in">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-base font-semibold text-[#E8E8EC]">{{ $availabilityBusyTitle }} {{ app()->isLocale('en') ? 'on' : 'di' }} {{ $selectedDateLabel }}</h2>
                    <span class="mk-badge mk-badge-danger shrink-0">{{ $localizedSelectedBusyRows->count() }} {{ $availabilityCountToolsSuffix }}</span>
                </div>
                <div class="space-y-2">
                    @forelse ($localizedSelectedBusyRows->take(10) as $row)
                        <article class="board-item rounded-xl border border-white/5 bg-[#0A0A0B]/40 px-3.5 py-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-[#E8E8EC]">{{ $row['name'] }}</p>
                                <p class="text-xs font-semibold text-rose-400">{{ strtr($availabilityInUseTemplate, [':qty' => (string) $row['selected_reserved']]) }}</p>
                            </div>
                            @if ($row['source_labels']->isNotEmpty())
                                <p class="mt-0.5 text-xs text-[#A0A0A8]">{{ $row['source_labels']->implode(', ') }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-white/5 bg-[#0A0A0B]/40 px-3 py-3 text-xs text-[#A0A0A8]">
                            {{ $availabilityBusyEmpty }}
                        </p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-3xl border border-white/5 bg-[#111113]/40 p-6 shadow-xl availability-card-in">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-base font-semibold text-[#E8E8EC]">{{ $availabilityMonthlyTitle }}</h2>
                    <span class="mk-badge mk-badge-info shrink-0">{{ $monthlySchedules->count() }} {{ $availabilityCountSchedulesSuffix }}</span>
                </div>
                <div class="max-h-[29rem] space-y-2 overflow-y-auto pr-1 scrollbar-thin">
                    @forelse ($monthlySchedules as $schedule)
                        <article class="board-item rounded-xl border border-white/5 bg-[#0A0A0B]/40 px-3.5 py-2.5">
                            <p class="text-sm font-semibold text-[#E8E8EC]">{{ $schedule['equipment_name'] }}</p>
                            <p class="mt-0.5 text-xs text-[#A0A0A8]">
                                {{ \Carbon\Carbon::parse($schedule['start_date'])->translatedFormat('d M Y') }}
                                -
                                {{ \Carbon\Carbon::parse($schedule['end_date'])->translatedFormat('d M Y') }}
                                • Qty {{ $schedule['qty'] }}
                            </p>
                            <p class="mt-1 text-[10px] font-bold text-[#A0A0A8] uppercase tracking-wider">
                                {{ $resolveOrderStatusName($schedule['status_pesanan']) }}
                            </p>
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-white/5 bg-[#0A0A0B]/40 px-3 py-3 text-xs text-[#A0A0A8]">
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

            <div class="mk-card relative z-10 w-full max-w-3xl max-h-[90vh] overflow-hidden shadow-2xl border border-[#1A1A1E] bg-[#111113] animate-fade-in">
                <div class="flex items-center justify-between gap-3 border-b border-[#1A1A1E] bg-[#0A0A0B] px-4 py-3 sm:px-5 sm:py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#A0A0A8]">{{ $availabilityModalDateTitle }}</p>
                        <h2 class="mt-1 text-xl font-bold text-[#E8E8EC]" x-text="modalDateLabel"></h2>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#1A1A1E] bg-[#111113] hover:bg-[#1A1A1E] text-[#A0A0A8] hover:text-[var(--availability-accent)] transition-all font-semibold"
                        @click="closeScheduleModal()"
                        aria-label="{{ $availabilityModalClose }}"
                    >
                        ✕
                    </button>
                </div>

                <div class="px-4 py-4 sm:px-5 overflow-y-auto max-h-[calc(90vh-5rem)]">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-3.5 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-500">{{ $availabilityMetricBusy }}</p>
                            <p class="mt-1 text-xl font-bold text-rose-400" x-text="modalBusyEquipments"></p>
                        </div>
                        <div class="rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3.5 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-[#A0A0A8]">{{ $availabilityMetricUnits }}</p>
                            <p class="mt-1 text-xl font-bold text-[#E8E8EC]" x-text="modalReservedUnits"></p>
                        </div>
                        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-3.5 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-500">{{ $availabilityMetricAvailable }}</p>
                            <p class="mt-1 text-xl font-bold text-emerald-400" x-text="modalAvailableEquipments"></p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2.5">
                        <template x-if="modalSchedules.length === 0">
                            <p class="rounded-xl border border-dashed border-[#1A1A1E] bg-[#0A0A0B] px-4 py-4 text-xs font-medium text-[#A0A0A8]">
                                {{ $availabilityModalEmpty }}
                            </p>
                        </template>

                        <template x-for="(item, index) in modalSchedules" :key="`${item.equipment_name}-${index}`">
                            <article class="board-item rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3.5 py-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-[#E8E8EC]" x-text="item.equipment_name"></p>
                                        <p class="mt-0.5 text-xs text-[#A0A0A8]" x-text="item.status_label"></p>
                                    </div>
                                    <span class="rounded-full bg-[#111113] border border-[#1A1A1E] px-2 py-0.5 text-[10px] font-bold availability-accent-text" x-text="`x${item.qty}`"></span>
                                </div>
                                <p class="mt-2 text-xs text-[#A0A0A8]">
                                    {{ $availabilityPeriodLabel }}: <span class="font-semibold text-[#E8E8EC]" x-text="`${formatDateLabel(item.start_date)} - ${formatDateLabel(item.end_date)}`"></span>
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

            <div class="mk-card relative z-10 w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl border border-[#1A1A1E] bg-[#111113] animate-fade-in">
                <div class="flex items-center justify-between gap-3 border-b border-[#1A1A1E] bg-[#0A0A0B] px-4 py-3 sm:px-5 sm:py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#A0A0A8]">{{ $availabilityRangeKicker }}</p>
                        <h2 class="mt-1 text-lg font-bold text-[#E8E8EC]">{{ $availabilityRangeTitle }}</h2>
                        <p class="mt-1 text-xs text-[#A0A0A8]">
                            <span class="font-semibold text-[#E8E8EC]" x-text="formatDateLabel(getRangeStartDate())"></span>
                            -
                            <span class="font-semibold text-[#E8E8EC]" x-text="formatDateLabel(getRangeEndDate())"></span>
                            •
                            <span class="font-semibold availability-accent-text" x-text="getRangeDurationLabel()"></span>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#1A1A1E] bg-[#111113] hover:bg-[#1A1A1E] text-[#A0A0A8] hover:text-[var(--availability-accent)] transition-all font-semibold"
                        @click="closeRangeSelectionModal()"
                        aria-label="{{ $availabilityModalClose }}"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto px-4 py-4 sm:px-5 max-h-[calc(90vh-6rem)]">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-end">
                        <div>
                            <label class="text-[11px] font-bold uppercase tracking-wide text-[#A0A0A8]">{{ $availabilityRangeFilterLabel }}</label>
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
                        <div class="rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3.5 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-[#A0A0A8]">{{ $availabilityRangeAvailableLabel }}</p>
                            <p class="text-base font-bold text-emerald-400" x-text="getFilteredRangeRows().length"></p>
                        </div>
                        <a
                            :href="catalogUrl"
                            class="availability-accent-bg rounded-xl font-bold transition py-2.5 px-5 text-sm"
                        >
                            {{ $availabilityRangeContinue }}
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        <template x-if="getFilteredRangeRows().length === 0">
                            <p class="rounded-xl border border-dashed border-[#1A1A1E] bg-[#0A0A0B] px-4 py-4 text-xs font-medium text-[#A0A0A8]">
                                {{ $availabilityRangeEmpty }}
                            </p>
                        </template>

                        <template x-for="item in getFilteredRangeRows()" :key="`range-item-${item.id}`">
                            <article class="board-item rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3.5 py-3">
                                <div class="flex items-start gap-3.5">
                                    <img
                                        :src="item.image_url"
                                        :alt="item.name"
                                        class="h-14 w-14 rounded-lg border border-[#1A1A1E] bg-[#0A0A0B] object-cover"
                                        loading="lazy"
                                    >
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-[#E8E8EC]" x-text="item.name"></p>
                                        <p class="mt-0.5 text-xs italic text-[#A0A0A8]" x-text="item.category"></p>
                                        <p class="mt-1 text-[11px] font-bold availability-accent-text">
                                            {{ $availabilityFromPriceLabel }} <span x-text="`{{ $currencyPrefix }} ${Number(item.price_per_day || 0).toLocaleString(@js($intlLocale))}`"></span> {{ __('app.product.per_day') }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-150 dark:border-emerald-900 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400" x-text="@js($availabilityMinLeftTemplate).replace(':qty', item.min_available)"></span>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-3 border-t border-[#1A1A1E]/50 dark:border-slate-800/50 pt-2.5">
                                    <a
                                        :href="buildProductUrl(item.slug)"
                                        class="availability-accent-bg rounded-lg font-bold transition py-1.5 px-3 text-[11px]"
                                    >
                                        {{ $availabilityRangePick }}
                                    </a>
                                    <span class="text-[10px] text-[#A0A0A8]" x-text="`* ${@js($availabilityRangePrefillNote)}`"></span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('availability-search-input');
    const spinner = document.getElementById('availability-search-loading-spinner');
    const dropdown = document.getElementById('availability-search-suggestions-dropdown');

    if (!searchInput || !dropdown) return;

    let debounceTimeout = null;
    let currentFocusIndex = -1;
    let suggestionsData = [];

    const showDropdown = () => {
        dropdown.classList.remove('hidden');
    };

    const hideDropdown = () => {
        dropdown.classList.add('hidden');
        currentFocusIndex = -1;
    };

    const renderSuggestions = (items) => {
        suggestionsData = items || [];
        const noSuggestionsLabel = @js(app()->getLocale() === 'en' ? 'No suggestions' : 'Tidak ada saran');

        if (!items || items.length === 0) {
            dropdown.innerHTML = `
                <div class="p-3 text-center text-xs availability-suggestion-meta">
                    ${noSuggestionsLabel}
                </div>
            `;
            showDropdown();
            return;
        }

        const locale = @js($intlLocale);
        const currency = @js($currencyPrefix);
        const recommendationLabel = @js(app()->getLocale() === 'en' ? 'Recommended' : 'Rekomendasi');
        const perDayLabel = @js(app()->getLocale() === 'en' ? '/day' : '/hari');
        const availableLabel = @js(app()->getLocale() === 'en' ? 'Available' : 'Tersedia');
        const unitLabel = @js(app()->getLocale() === 'en' ? 'units' : 'unit');

        dropdown.innerHTML = items.map((item, index) => {
            const priceFormatted = new Intl.NumberFormat(locale).format(item.price_per_day || 0);

            return `
                <a href="${item.detail_url}"
                   data-availability-suggestion-index="${index}"
                   class="availability-suggestion-link flex items-center gap-3 rounded-xl border border-transparent p-2 transition active:scale-[0.99] focus:outline-none">
                    <div class="availability-suggestion-image-box flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg border p-1">
                        <img src="${item.image_url}" alt="${item.name}" class="h-full w-full object-contain">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="availability-suggestion-name truncate text-xs font-bold transition-colors">${item.name}</span>
                            ${item.is_recommended ? `<span class="rounded px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider availability-accent-bg">${recommendationLabel}</span>` : ''}
                        </div>
                        <div class="availability-suggestion-meta mt-1 flex flex-wrap items-center gap-2 text-[10px]">
                            <span class="font-semibold">${item.category_name || ''}</span>
                            <span class="h-1 w-1 rounded-full bg-current opacity-25"></span>
                            <span>${currency} ${priceFormatted}${perDayLabel}</span>
                            <span class="h-1 w-1 rounded-full bg-current opacity-25"></span>
                            <span>${availableLabel}: ${item.available_units ?? 0} ${unitLabel}</span>
                        </div>
                    </div>
                </a>
            `;
        }).join('');

        showDropdown();
    };

    const fetchSuggestions = async (query) => {
        if (spinner) spinner.classList.remove('hidden');

        try {
            const response = await fetch(`/search/suggestions?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const json = await response.json();
                renderSuggestions(json.data || []);
            }
        } catch (error) {
            console.error('Error fetching availability suggestions:', error);
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    };

    searchInput.addEventListener('input', () => {
        const value = searchInput.value.trim();

        if (debounceTimeout) {
            clearTimeout(debounceTimeout);
        }

        if (value.length < 2) {
            hideDropdown();
            return;
        }

        debounceTimeout = setTimeout(() => {
            fetchSuggestions(value);
        }, 300);
    });

    const updateFocus = () => {
        const items = dropdown.querySelectorAll('a[data-availability-suggestion-index]');

        items.forEach((item, index) => {
            if (index === currentFocusIndex) {
                item.classList.add('availability-accent-border');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('availability-accent-border');
            }
        });
    };

    searchInput.addEventListener('keydown', (event) => {
        const items = dropdown.querySelectorAll('a[data-availability-suggestion-index]');

        if (dropdown.classList.contains('hidden')) {
            if (event.key === 'ArrowDown' && searchInput.value.trim().length >= 2) {
                fetchSuggestions(searchInput.value.trim());
            }
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            if (items.length > 0) {
                currentFocusIndex = (currentFocusIndex + 1) % items.length;
                updateFocus();
            }
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            if (items.length > 0) {
                currentFocusIndex = (currentFocusIndex - 1 + items.length) % items.length;
                updateFocus();
            }
        } else if (event.key === 'Enter') {
            if (currentFocusIndex >= 0 && suggestionsData[currentFocusIndex]) {
                event.preventDefault();
                window.location.href = suggestionsData[currentFocusIndex].detail_url;
            }
        } else if (event.key === 'Escape') {
            event.preventDefault();
            hideDropdown();
            searchInput.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('#availability-search-input') && !event.target.closest('#availability-search-suggestions-dropdown')) {
            hideDropdown();
        }
    });

    searchInput.addEventListener('focus', () => {
        const value = searchInput.value.trim();

        if (value.length >= 2) {
            if (dropdown.children.length > 0) {
                showDropdown();
            } else {
                fetchSuggestions(value);
            }
        }
    });
});
</script>
@endpush
