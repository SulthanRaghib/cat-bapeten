@php
    $statePath = $getStatePath();
    $stateValue = $getState() ?? '';
@endphp

<style>
    /* ═══ LIGHT — default ════════════════════════════════════════════════ */
    .cdtp-root {
        --cdtp-bg: #ffffff;
        --cdtp-bg-alt: #f9fafb;
        --cdtp-bg-hover: #f3f4f6;
        --cdtp-border: #e5e7eb;
        --cdtp-border-input: #d1d5db;
        --cdtp-text: #1f2937;
        --cdtp-text-sec: #374151;
        --cdtp-text-dim: #6b7280;
        --cdtp-text-faint: #9ca3af;
        --cdtp-inset: inset 0 1px 2px rgba(0, 0, 0, .05);
        --cdtp-panel-shadow: 0 20px 50px -12px rgba(0, 0, 0, .18), 0 4px 16px rgba(0, 0, 0, .06);
    }

    /* ═══ DARK — Filament palette (gray-900/800/700) ═════════════════════ */
    .dark .cdtp-root {
        --cdtp-bg: #242427;
        --cdtp-bg-alt: #18181b;
        --cdtp-bg-hover: #374151;
        --cdtp-border: #4b5563;
        --cdtp-border-input: #6b7280;
        --cdtp-text: #f9fafb;
        --cdtp-text-sec: #e5e7eb;
        --cdtp-text-dim: #d1d5db;
        --cdtp-text-faint: #9ca3af;
        --cdtp-inset: inset 0 1px 2px rgba(0, 0, 0, .2);
        --cdtp-panel-shadow: 0 20px 50px -12px rgba(0, 0, 0, .55), 0 4px 16px rgba(0, 0, 0, .35);
    }

    /* ── Trigger button ────────────────────────────────────────────────── */
    .cdtp-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        border-radius: 8px;
        border: 1px solid var(--cdtp-border-input);
        background: var(--cdtp-bg);
        color: var(--cdtp-text);
        text-align: left;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s;
        outline: none;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.5;
    }

    .cdtp-trigger:hover {
        border-color: var(--color-primary-400, #fb923c);
    }

    .cdtp-trigger.open {
        border-color: var(--color-primary-500, #f97316);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary-500, #f97316) 15%, transparent);
    }

    /* ── Pop-over panel ────────────────────────────────────────────────── */
    .cdtp-panel {
        position: absolute;
        z-index: 9999;
        left: 0;
        border-radius: 14px;
        background: var(--cdtp-bg) !important;
        box-shadow: var(--cdtp-panel-shadow);
        border: 1px solid var(--cdtp-border);
        overflow: hidden;
        user-select: none;
    }

    .cdtp-panel.drop-down {
        top: calc(100% + 6px);
        bottom: auto;
    }

    .cdtp-panel.drop-up {
        bottom: calc(100% + 6px);
        top: auto;
    }

    /* ── Header gradient ───────────────────────────────────────────────── */
    .cdtp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: linear-gradient(135deg,
                var(--color-primary-600, #ea580c),
                var(--color-primary-800, #9a3412));
        text-shadow: 0 1px 3px rgba(0, 0, 0, .2);
    }

    /* ── Nav arrows ────────────────────────────────────────────────────── */
    .cdtp-nav {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        background: transparent;
        border: none;
        cursor: pointer;
        color: rgba(255, 255, 255, .7);
        transition: background .12s, color .12s;
    }

    .cdtp-nav:hover {
        background: rgba(255, 255, 255, .2);
        color: #fff;
    }

    /* ── Month/Year header buttons ─────────────────────────────────────── */
    .cdtp-hdr-btn {
        padding: 3px 8px;
        border-radius: 6px;
        background: transparent;
        border: none;
        cursor: pointer;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        font-family: inherit;
        letter-spacing: .01em;
        transition: background .12s;
        text-shadow: 0 1px 3px rgba(0, 0, 0, .2);
    }

    .cdtp-hdr-btn:hover {
        background: rgba(255, 255, 255, .2);
    }

    /* ── Day cell button ───────────────────────────────────────────────── */
    .cdtp-day {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-family: inherit;
        font-weight: 400;
        color: var(--cdtp-text-sec);
        background: transparent;
        transition: background .1s, color .1s, transform .08s, box-shadow .1s;
    }

    .cdtp-day:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 15%, transparent) !important;
        color: var(--color-primary-600, #ea580c) !important;
    }

    .cdtp-day.selected {
        background: var(--color-primary-500, #f97316) !important;
        color: #fff !important;
        font-weight: 700 !important;
        box-shadow: 0 3px 10px color-mix(in srgb, var(--color-primary-500, #f97316) 45%, transparent) !important;
    }

    .cdtp-day.today:not(.selected) {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 15%, transparent) !important;
        color: var(--color-primary-600, #ea580c) !important;
        font-weight: 700 !important;
    }

    /* ── Grid button (month / year) ────────────────────────────────────── */
    .cdtp-grid {
        padding: 8px 4px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 12.5px;
        font-weight: 500;
        font-family: inherit;
        color: var(--cdtp-text-sec);
        background: var(--cdtp-bg-hover);
        transition: all .12s;
    }

    .cdtp-grid:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 15%, transparent) !important;
        color: var(--color-primary-600, #ea580c) !important;
    }

    .cdtp-grid.active {
        background: var(--color-primary-500, #f97316) !important;
        color: #fff !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 8px color-mix(in srgb, var(--color-primary-500, #f97316) 40%, transparent);
    }

    /* ── Time spinner arrows ───────────────────────────────────────────── */
    .cdtp-spin {
        width: 28px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid var(--cdtp-border);
        background: var(--cdtp-bg-alt);
        color: var(--cdtp-text-dim);
        cursor: pointer;
        transition: background .1s;
    }

    .cdtp-spin:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 10%, transparent);
        border-color: var(--color-primary-400, #fb923c);
        color: var(--color-primary-600, #ea580c);
    }

    /* ── Time number input ─────────────────────────────────────────────── */
    .cdtp-time {
        width: 52px;
        height: 52px;
        text-align: center;
        font-size: 22px;
        font-weight: 700;
        font-family: inherit;
        color: var(--cdtp-text);
        background: var(--cdtp-bg);
        border: 2px solid var(--cdtp-border);
        border-radius: 10px;
        outline: none;
        -moz-appearance: textfield;
        box-shadow: var(--cdtp-inset);
        transition: border-color .15s;
    }

    .cdtp-time:focus {
        border-color: var(--color-primary-500, #f97316);
    }

    .cdtp-time::-webkit-inner-spin-button,
    .cdtp-time::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }

    /* ── Footer buttons ────────────────────────────────────────────────── */
    .cdtp-btn-now {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        border: 1.5px solid var(--color-primary-400, #fb923c);
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 8%, transparent);
        color: var(--color-primary-700, #c2410c);
        transition: background .15s, border-color .15s, color .15s;
    }

    .cdtp-btn-now:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 16%, transparent);
        border-color: var(--color-primary-500, #f97316);
        color: var(--color-primary-800, #9a3412);
    }

    .cdtp-btn-clear {
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        font-family: inherit;
        cursor: pointer;
        border: 1px solid var(--cdtp-border);
        background: transparent;
        color: var(--cdtp-text-dim);
        transition: background .12s, color .12s;
    }

    .cdtp-btn-clear:hover {
        background: var(--cdtp-bg-hover);
        color: var(--cdtp-text-sec);
    }

    .cdtp-btn-ok {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 7px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        border: none;
        color: #fff;
        background: var(--color-primary-500, #f97316);
        box-shadow: 0 2px 6px color-mix(in srgb, var(--color-primary-500, #f97316) 30%, transparent);
        transition: background .12s, box-shadow .12s;
    }

    .cdtp-btn-ok:hover {
        background: var(--color-primary-600, #ea580c);
    }

    .cdtp-btn-ok:disabled {
        background: var(--cdtp-bg-hover);
        color: var(--cdtp-text-faint);
        box-shadow: none;
        cursor: not-allowed;
    }

    /* ═══ DARK — interactive element overrides ═══════════════════════════ */
    .dark .cdtp-header {
        background: linear-gradient(135deg,
                var(--color-primary-700, #c2410c),
                var(--color-primary-900, #7c2d12));
    }

    .dark .cdtp-spin:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 15%, transparent);
        border-color: var(--color-primary-400, #fb923c);
        color: var(--color-primary-300, #fdba74);
    }

    .dark .cdtp-time:focus {
        border-color: var(--color-primary-400, #fb923c);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary-500, #f97316) 20%, transparent);
    }

    .dark .cdtp-btn-now {
        border-color: color-mix(in srgb, var(--color-primary-500, #f97316) 40%, transparent);
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 10%, transparent);
        color: var(--color-primary-400, #fb923c);
    }

    .dark .cdtp-btn-now:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 20%, transparent);
        border-color: var(--color-primary-500, #f97316);
        color: var(--color-primary-300, #fdba74);
    }

    .dark .cdtp-btn-ok {
        box-shadow: 0 2px 8px color-mix(in srgb, var(--color-primary-500, #f97316) 40%, transparent);
    }

    .dark .cdtp-btn-clear:hover {
        background: var(--cdtp-bg-hover);
        color: var(--cdtp-text);
    }

    .dark .cdtp-day:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 20%, transparent) !important;
        color: var(--color-primary-300, #fdba74) !important;
    }

    .dark .cdtp-day.today:not(.selected) {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 20%, transparent) !important;
        color: var(--color-primary-300, #fdba74) !important;
    }

    .dark .cdtp-grid:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f97316) 20%, transparent) !important;
        color: var(--color-primary-300, #fdba74) !important;
    }

    .dark .cdtp-grid.active {
        background: var(--color-primary-600, #ea580c) !important;
        box-shadow: 0 2px 8px color-mix(in srgb, var(--color-primary-500, #f97316) 50%, transparent);
    }

    .dark .cdtp-trigger:hover {
        border-color: var(--color-primary-400, #fb923c);
    }

    .dark .cdtp-trigger.open {
        border-color: var(--color-primary-400, #fb923c);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary-500, #f97316) 20%, transparent);
    }
</style>

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="cdtp-root" x-data="customDateTimePicker({ statePath: @js($statePath), initialValue: @js($stateValue), placeholder: @js(__('Pick date and time...')), locale: @js(app()->getLocale()) })" x-init="init()" @click.outside="isOpen = false"
        style="position:relative">

        {{-- ── TRIGGER ──────────────────────────────────────────────── --}}
        <button type="button" class="cdtp-trigger" :class="isOpen ? 'open' : ''" @click="isOpen = !isOpen">
            <svg style="width:16px;height:16px;flex-shrink:0"
                :style="{ color: displayValue ? 'var(--color-primary-500, #f97316)' : 'var(--cdtp-text-faint)' }"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <span style="flex:1;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                :style="{
                    color: displayValue ? 'var(--cdtp-text)' : 'var(--cdtp-text-faint)',
                    fontWeight: displayValue ?
                        '500' : '400'
                }"
                x-text="displayValue || placeholder"></span>
            <svg style="width:13px;height:13px;flex-shrink:0;transition:transform .2s"
                :style="{
                    transform: isOpen ? 'rotate(180deg)' : '',
                    color: isOpen ? 'var(--color-primary-500, #f97316)' : 'var(--cdtp-text-faint)'
                }"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        {{-- ── PICKER PANEL ─────────────────────────────────────────── --}}
        <div x-show="isOpen" x-cloak x-transition class="cdtp-panel" x-ref="panel" :class="dropDirection"
            x-effect="if (isOpen) { $nextTick(() => { const r = $el.parentElement.getBoundingClientRect(); dropDirection = (window.innerHeight - r.bottom) < 420 ? 'drop-up' : 'drop-down'; }); }">
            <div style="display:flex;align-items:stretch">

                {{-- ── LEFT: CALENDAR ───────────────────────────────── --}}
                <div style="width:272px;flex-shrink:0">

                    {{-- Gradient header --}}
                    <div class="cdtp-header">
                        <button type="button" class="cdtp-nav" @click="prevNav()">
                            <svg style="width:13px;height:13px" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>

                        <div style="display:flex;align-items:center;gap:2px">
                            <template x-if="pickerView === 'days'">
                                <div style="display:flex;align-items:center;gap:2px">
                                    <button type="button" class="cdtp-hdr-btn" @click="showMonthPicker()"
                                        x-text="monthNames[viewMonth]"></button>
                                    <button type="button" class="cdtp-hdr-btn" @click="showYearPicker()"
                                        x-text="viewYear"></button>
                                </div>
                            </template>
                            <template x-if="pickerView === 'months'">
                                <button type="button" class="cdtp-hdr-btn" @click="showYearPicker()"
                                    x-text="viewYear"></button>
                            </template>
                            <template x-if="pickerView === 'years'">
                                <span style="color:#fff;font-weight:700;font-size:14px"
                                    x-text="`${yearRange[0]} – ${yearRange[11]}`"></span>
                            </template>
                        </div>

                        <button type="button" class="cdtp-nav" @click="nextNav()">
                            <svg style="width:13px;height:13px" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>

                    {{-- ── DAY VIEW ─────────────────────────────────── --}}
                    <div x-show="pickerView === 'days'" style="padding:8px 10px 10px">
                        {{-- Weekday headers --}}
                        <div style="display:grid;grid-template-columns:repeat(7,1fr);margin-bottom:2px">
                            <template x-for="name in dayNamesShort" :key="name">
                                <div style="display:flex;align-items:center;justify-content:center;height:22px;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--cdtp-text-faint)"
                                    x-text="name"></div>
                            </template>
                        </div>
                        {{-- Day cells --}}
                        <div style="display:grid;grid-template-columns:repeat(7,1fr)">
                            <template x-for="(cell, i) in calendarDays" :key="i">
                                <div style="display:flex;align-items:center;justify-content:center;height:34px">
                                    <button x-show="cell.day !== null" type="button" class="cdtp-day"
                                        :class="{ 'selected': cell.isSelected, 'today': cell.isToday && !cell.isSelected }"
                                        @click="selectDay(cell.day)" x-text="cell.day"></button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ── MONTH VIEW ───────────────────────────────── --}}
                    <div x-show="pickerView === 'months'" style="padding:14px 10px">
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px">
                            <template x-for="(m, idx) in monthShort" :key="idx">
                                <button type="button" class="cdtp-grid" :class="{ 'active': viewMonth === idx }"
                                    @click="selectMonth(idx)" x-text="m"></button>
                            </template>
                        </div>
                    </div>

                    {{-- ── YEAR VIEW ────────────────────────────────── --}}
                    <div x-show="pickerView === 'years'" style="padding:14px 10px">
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px">
                            <template x-for="y in yearRange" :key="y">
                                <button type="button" class="cdtp-grid" :class="{ 'active': viewYear === y }"
                                    @click="selectYear(y)" x-text="y"></button>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- ── DIVIDER ──────────────────────────────────────── --}}
                <div style="width:1px;background:var(--cdtp-border)"></div>

                {{-- ── RIGHT: TIME ──────────────────────────────────── --}}
                <div
                    style="width:154px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:18px 12px;background:var(--cdtp-bg-alt)">
                    <div
                        style="font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--cdtp-text-faint);margin-bottom:14px">
                        {{ __('Time (WIB)') }}
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">

                        {{-- Hour --}}
                        <div style="display:flex;flex-direction:column;align-items:center;gap:5px">
                            <button type="button" class="cdtp-spin" @click="hour = (hour + 1) % 24">
                                <svg style="width:11px;height:11px" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                </svg>
                            </button>
                            <input type="number" min="0" max="23" class="cdtp-time"
                                :value="pad(hour)" @change="clampHour($event.target.value)"
                                @wheel.prevent="hour = ($event.deltaY < 0 ? (hour + 1) % 24 : (hour - 1 + 24) % 24)" />
                            <button type="button" class="cdtp-spin" @click="hour = (hour - 1 + 24) % 24">
                                <svg style="width:11px;height:11px" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <span
                                style="font-size:10px;color:var(--cdtp-text-faint);font-weight:500;margin-top:2px">{{ __('Hour') }}</span>
                        </div>

                        <div style="font-size:26px;font-weight:700;color:var(--cdtp-text-dim);padding-bottom:22px">:
                        </div>

                        {{-- Minute --}}
                        <div style="display:flex;flex-direction:column;align-items:center;gap:5px">
                            <button type="button" class="cdtp-spin" @click="minute = (minute + 1) % 60">
                                <svg style="width:11px;height:11px" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                </svg>
                            </button>
                            <input type="number" min="0" max="59" class="cdtp-time"
                                :value="pad(minute)" @change="clampMinute($event.target.value)"
                                @wheel.prevent="minute = ($event.deltaY < 0 ? (minute + 1) % 60 : (minute - 1 + 60) % 60)" />
                            <button type="button" class="cdtp-spin" @click="minute = (minute - 1 + 60) % 60">
                                <svg style="width:11px;height:11px" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <span
                                style="font-size:10px;color:var(--cdtp-text-faint);font-weight:500;margin-top:2px">{{ __('Minute') }}</span>
                        </div>

                    </div>
                </div>

            </div>

            {{-- ── FOOTER ───────────────────────────────────────────── --}}
            <div
                style="display:flex;align-items:center;gap:8px;padding:10px 12px;border-top:1px solid var(--cdtp-border);background:var(--cdtp-bg-alt) !important">
                <button type="button" class="cdtp-btn-now" @click="setNow(); confirm();">
                    <svg style="width:13px;height:13px" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('Now') }}
                </button>
                <button type="button" class="cdtp-btn-clear" @click="clear()">{{ __('Clear') }}</button>
                <button type="button" class="cdtp-btn-ok" style="margin-left:auto" @click="confirm()"
                    :disabled="selectedDay === null">
                    <svg style="width:13px;height:13px" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    {{ __('Confirm') }}
                </button>
            </div>

        </div>
    </div>
</x-dynamic-component>
