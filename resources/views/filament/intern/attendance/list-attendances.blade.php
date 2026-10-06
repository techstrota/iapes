<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
@php
    $stats = $this->getAttendanceStats();
@endphp

<style>
    /* ── Stitch Tabs Toolbar Styling ── */
    .fi-resource-list-records-page .fi-tabs {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 14px !important;
        padding: 6px !important;
        gap: 6px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
        display: inline-flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
    }
    .fi-resource-list-records-page .fi-tabs-item {
        border-radius: 10px !important;
        padding: 8px 16px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #8e909f !important;
        transition: all 0.15s ease !important;
        border: none !important;
        background-color: transparent !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
    }
    .fi-resource-list-records-page .fi-tabs-item:hover {
        background-color: #171f33 !important;
        color: #dae2fd !important;
    }
    .fi-resource-list-records-page .fi-tabs-item.fi-active,
    .fi-resource-list-records-page .fi-tabs-item[aria-selected="true"] {
        background-color: #1e40af !important;
        color: #ffffff !important;
        border: 1px solid #3b82f6 !important;
        box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
        font-weight: 700 !important;
    }
    .fi-resource-list-records-page .fi-tabs-item.fi-active svg,
    .fi-resource-list-records-page .fi-tabs-item[aria-selected="true"] svg,
    .fi-resource-list-records-page .fi-tabs-item.fi-active .fi-tabs-item-icon,
    .fi-resource-list-records-page .fi-tabs-item[aria-selected="true"] .fi-tabs-item-icon {
        color: #ffffff !important;
    }
    .fi-resource-list-records-page .fi-tabs-item .fi-badge {
        font-size: 11px !important;
        font-weight: 700 !important;
        border-radius: 9999px !important;
        padding: 2px 7px !important;
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #dae2fd !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
    }
    .fi-resource-list-records-page .fi-tabs-item.fi-active .fi-badge,
    .fi-resource-list-records-page .fi-tabs-item[aria-selected="true"] .fi-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
    }

    /* ── Stitch Table Container & Header ── */
    .fi-resource-list-records-page .fi-ta-ctn {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
        overflow: hidden !important;
    }
    .fi-resource-list-records-page .fi-ta-header-ctn,
    .fi-resource-list-records-page .fi-ta-header {
        background-color: #131b2e !important;
        border-bottom: 1.5px solid #1c2638 !important;
        padding: 14px 20px !important;
    }

    /* Search Field */
    .fi-resource-list-records-page .fi-ta-search-field .fi-input-wrp {
        background-color: #090e1c !important;
        border: 1px solid #222a3d !important;
        border-radius: 10px !important;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4) !important;
    }
    .fi-resource-list-records-page .fi-ta-search-field input {
        color: #dae2fd !important;
        font-size: 13px !important;
    }
    .fi-resource-list-records-page .fi-ta-search-field input::placeholder {
        color: #8e909f !important;
    }
    .fi-resource-list-records-page .fi-ta-search-field .fi-input-wrp:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
    }

    /* Filters trigger button */
    .fi-resource-list-records-page .fi-ta-filters-trigger-btn,
    .fi-resource-list-records-page .fi-ta-filter-trigger-action button,
    .fi-resource-list-records-page .fi-ta-filter-trigger button {
        background-color: #090e1c !important;
        border: 1px solid #222a3d !important;
        border-radius: 10px !important;
        color: #dae2fd !important;
        transition: all 0.15s ease !important;
    }
    .fi-resource-list-records-page .fi-ta-filters-trigger-btn:hover,
    .fi-resource-list-records-page .fi-ta-filter-trigger-action button:hover {
        border-color: #3b82f6 !important;
        color: #ffffff !important;
    }

    /* Content & Background */
    .fi-resource-list-records-page .fi-ta-content {
        background-color: #0b1326 !important;
        padding: 16px !important;
    }

    /* Hide duplicate Filament row actions in content grid */
    .fi-resource-list-records-page .fi-ta-content-grid .fi-ta-actions,
    .fi-resource-list-records-page .fi-ta-content-grid .fi-ta-actions-cell {
        display: none !important;
    }

    /* Empty State */
    .fi-resource-list-records-page .fi-ta-empty-state {
        background-color: #131b2e !important;
        border-radius: 16px !important;
        padding: 50px 24px !important;
    }
    .fi-resource-list-records-page .fi-ta-empty-state-icon-ctn {
        background-color: rgba(99, 102, 241, 0.15) !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
        color: #b8c4ff !important;
        width: 58px !important;
        height: 58px !important;
        border-radius: 16px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto 16px auto !important;
    }
    .fi-resource-list-records-page .fi-ta-empty-state-icon-ctn svg {
        width: 28px !important;
        height: 28px !important;
        color: #b8c4ff !important;
    }
    .fi-resource-list-records-page .fi-ta-empty-state-heading {
        color: #ffffff !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        margin-bottom: 6px !important;
    }
    .fi-resource-list-records-page .fi-ta-empty-state-description {
        color: #8e909f !important;
        font-size: 13px !important;
        max-width: 440px !important;
        margin: 0 auto 18px auto !important;
        line-height: 1.5 !important;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

    {{-- 1. Hero Context & Action Banner --}}
    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px 26px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px; position: relative; z-index: 1;">
            {{-- Left: Identity & Info --}}
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                    <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2;">
                            My Attendance & Roll-Call Tracker
                        </h1>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; padding: 3px 9px; border-radius: 9999px; border: 1px solid rgba(78, 222, 163, 0.3);">
                            {{ $stats['total_days'] }} Total Logged Days
                        </span>
                    </div>
                    <p style="font-size: 12.5px; color: #8e909f; margin: 4px 0 0 0;">
                        Monitor your daily check-in roll-call records, shift compliance, and approved absence history.
                    </p>
                </div>
            </div>

            {{-- Right: Today's Roll-Call Status Badge --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                @if ($stats['today_status'] === 'present')
                    <div style="display: inline-flex; align-items: center; gap: 7px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.35); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981; box-shadow: 0 0 6px #10b981;"></span>
                        <span>✓ Checked-In Present Today</span>
                    </div>
                @elseif ($stats['today_status'] === 'leave')
                    <div style="display: inline-flex; align-items: center; gap: 7px; background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;">
                        <span>🏖 Approved Leave Today</span>
                    </div>
                @elseif ($stats['today_status'] === 'absent')
                    <div style="display: inline-flex; align-items: center; gap: 7px; background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;">
                        <span>✕ Absent Today</span>
                    </div>
                @else
                    <div style="display: inline-flex; align-items: center; gap: 7px; background-color: rgba(148, 163, 184, 0.12); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #64748b;"></span>
                        <span>⏳ Today's Roll-Call Pending</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @php
        $cal = $this->getCalendarData();
    @endphp

    {{-- 2. Calendar View vs Table View Toolbar --}}
    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
        {{-- Left: View Mode Switcher --}}
        <div style="display: flex; align-items: center; gap: 8px;">
            <button
                type="button"
                wire:click="setViewMode('calendar')"
                style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; border: {{ $viewMode === 'calendar' ? '1px solid #3b82f6' : '1px solid #222a3d' }}; background-color: {{ $viewMode === 'calendar' ? '#1e40af' : '#090e1c' }}; color: {{ $viewMode === 'calendar' ? '#ffffff' : '#dae2fd' }}; box-shadow: {{ $viewMode === 'calendar' ? '0 2px 10px rgba(30, 64, 175, 0.45)' : 'none' }};">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Monthly Timesheet Calendar</span>
            </button>
            <button
                type="button"
                wire:click="setViewMode('list')"
                style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; border: {{ $viewMode === 'list' ? '1px solid #3b82f6' : '1px solid #222a3d' }}; background-color: {{ $viewMode === 'list' ? '#1e40af' : '#090e1c' }}; color: {{ $viewMode === 'list' ? '#ffffff' : '#dae2fd' }}; box-shadow: {{ $viewMode === 'list' ? '0 2px 10px rgba(30, 64, 175, 0.45)' : 'none' }};">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>Attendance Log List</span>
            </button>
        </div>

        {{-- Right: Month Navigator & Direct Month Picker --}}
        @if ($viewMode === 'calendar')
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                {{-- Quick Previous Month Button --}}
                <button
                    type="button"
                    wire:click="previousMonth"
                    title="Previous Month"
                    style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #222a3d; background-color: #090e1c; color: #dae2fd; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s ease;"
                    onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff';"
                    onmouseout="this.style.borderColor='#222a3d'; this.style.color='#dae2fd';">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                {{-- Direct Month Picker Input --}}
                <div style="display: flex; align-items: center; gap: 8px; background-color: #090e1c; border: 1px solid #222a3d; border-radius: 8px; padding: 4px 10px;">
                    <span style="font-size: 13px; font-weight: 800; color: #dae2fd; min-width: 120px; text-align: center;">
                        {{ $cal['monthTitle'] }}
                    </span>
                    <input
                        type="month"
                        wire:model.live="calendarMonth"
                        style="background: transparent; border: none; color: #8e909f; font-size: 12px; cursor: pointer; outline: none; padding: 2px;"
                        title="Change Month and Year"
                    />
                </div>

                {{-- Quick Next Month Button --}}
                <button
                    type="button"
                    wire:click="nextMonth"
                    title="Next Month"
                    style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #222a3d; background-color: #090e1c; color: #dae2fd; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s ease;"
                    onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff';"
                    onmouseout="this.style.borderColor='#222a3d'; this.style.color='#dae2fd';">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                {{-- Current Month / Today Button --}}
                <button
                    type="button"
                    wire:click="goToCurrentMonth"
                    style="padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.4); background-color: rgba(30, 64, 175, 0.2); color: #b8c4ff; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.15s ease;"
                    onmouseover="this.style.backgroundColor='#1e40af'; this.style.color='#ffffff';"
                    onmouseout="this.style.backgroundColor='rgba(30, 64, 175, 0.2)'; this.style.color='#b8c4ff';">
                    Current Month
                </button>
            </div>
        @endif
    </div>

    {{-- 3. Single 4-Card KPI Strip (No Repetition, No Late Mark) --}}
    @php
        $cardCompliance = $viewMode === 'calendar' ? $cal['monthCompliance'] : $stats['attendance_rate'];
        $cardPresent = $viewMode === 'calendar' ? $cal['monthPresent'] : $stats['present_days'];
        $cardLeave = $viewMode === 'calendar' ? $cal['monthLeave'] : $stats['leave_days'];
        $cardAbsent = $viewMode === 'calendar' ? $cal['monthAbsent'] : $stats['absent_days'];
        $cardTotalMarked = $viewMode === 'calendar' ? $cal['monthTotalMarked'] : $stats['total_days'];
        $periodLabel = $viewMode === 'calendar' ? $cal['monthTitle'] : 'Overall';
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        {{-- Card 1: Attendance Compliance Rate --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">{{ $periodLabel }} Compliance</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: {{ $cardCompliance >= 85 ? 'rgba(16, 185, 129, 0.15)' : ($cardCompliance >= 75 ? 'rgba(245, 158, 11, 0.15)' : 'rgba(239, 68, 68, 0.15)') }}; color: {{ $cardCompliance >= 85 ? '#4edea3' : ($cardCompliance >= 75 ? '#fbbf24' : '#f87171') }}; display: flex; align-items: center; justify-content: center; border: 1px solid {{ $cardCompliance >= 85 ? 'rgba(78, 222, 163, 0.3)' : ($cardCompliance >= 75 ? 'rgba(245, 158, 11, 0.3)' : 'rgba(239, 68, 68, 0.3)') }};">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: {{ $cardCompliance >= 85 ? '#4edea3' : ($cardCompliance >= 75 ? '#fbbf24' : '#f87171') }}; line-height: 1.1;">
                {{ $cardCompliance }}%
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #4edea3; font-weight: 700;">{{ $cardPresent }} of {{ $cardTotalMarked }} Marked Days</span>
                <span>{{ $cardCompliance >= 85 ? 'Excellent' : ($cardCompliance >= 75 ? 'Standard' : 'Low Compliance') }}</span>
            </div>
        </div>

        {{-- Card 2: Days Present --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Days Present</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(78, 222, 163, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                {{ $cardPresent }} <span style="font-size: 16px; font-weight: 500; color: #64748b;">Days</span>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #93c5fd; font-weight: 600;">Full Duty Check-Ins</span>
                <span>On-Site / Virtual</span>
            </div>
        </div>

        {{-- Card 3: Approved Leaves --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Approved Leaves</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #b8c4ff; line-height: 1.1;">
                {{ $cardLeave }} <span style="font-size: 16px; font-weight: 500; color: #64748b;">Days</span>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #60a5fa; font-weight: 600;">Excused Absences</span>
                <span>Approved Status</span>
            </div>
        </div>

        {{-- Card 4: Unexcused Absences --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Unexcused Absences</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: {{ $cardAbsent > 0 ? 'rgba(239, 68, 68, 0.15)' : 'rgba(100, 116, 139, 0.15)' }}; color: {{ $cardAbsent > 0 ? '#f87171' : '#94a3b8' }}; display: flex; align-items: center; justify-content: center; border: 1px solid {{ $cardAbsent > 0 ? 'rgba(239, 68, 68, 0.3)' : 'rgba(100, 116, 139, 0.3)' }};">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: {{ $cardAbsent > 0 ? '#f87171' : '#ffffff' }}; line-height: 1.1;">
                {{ $cardAbsent }} <span style="font-size: 16px; font-weight: 500; color: #64748b;">Days</span>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="{{ $cardAbsent > 0 ? 'color: #f87171; font-weight: 700;' : 'color: #8e909f;' }}">Missed Roll-Calls</span>
                <span>No Record</span>
            </div>
        </div>
    </div>

    @if ($viewMode === 'calendar')
        {{-- 4. Monthly Timesheet Calendar Grid Container --}}
        <div style="background-color: #131b2e; border-radius: 16px; border: 1.5px solid #222a3d; padding: 22px; box-shadow: 0 4px 20px rgba(0,0,0,0.25); margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h3 style="font-size: 17px; font-weight: 800; color: #dae2fd; margin: 0;">
                        {{ $cal['monthTitle'] }} Timesheet Ledger
                    </h3>
                    <span style="font-size: 11px; color: #8e909f; background-color: #090e1c; padding: 3px 8px; border-radius: 6px; border: 1px solid #222a3d;">
                        Click any date to view detailed record
                    </span>
                </div>

                {{-- Status Legend (No Late) --}}
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 11px; color: #8e909f;">
                    <span style="display: inline-flex; align-items: center; gap: 4px;"><span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981;"></span> Present</span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;"><span style="width: 7px; height: 7px; border-radius: 50%; background-color: #6366f1;"></span> Leave</span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;"><span style="width: 7px; height: 7px; border-radius: 50%; background-color: #ef4444;"></span> Absent</span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;"><span style="width: 7px; height: 7px; border-radius: 50%; background-color: #64748b;"></span> Sun (Off)</span>
                </div>
            </div>

            {{-- 7-Column Day Headers --}}
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; text-align: center; margin-bottom: 8px;">
                <div style="font-size: 11px; font-weight: 800; color: #a5b4fc; text-transform: uppercase; padding: 8px 0; background-color: rgba(99, 102, 241, 0.15); border-radius: 8px; border: 1px solid rgba(99, 102, 241, 0.3);">Sun (Week-Off)</div>
                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 8px 0; background-color: #090e1c; border-radius: 8px; border: 1px solid #1c2638;">Mon</div>
                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 8px 0; background-color: #090e1c; border-radius: 8px; border: 1px solid #1c2638;">Tue</div>
                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 8px 0; background-color: #090e1c; border-radius: 8px; border: 1px solid #1c2638;">Wed</div>
                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 8px 0; background-color: #090e1c; border-radius: 8px; border: 1px solid #1c2638;">Thu</div>
                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 8px 0; background-color: #090e1c; border-radius: 8px; border: 1px solid #1c2638;">Fri</div>
                <div style="font-size: 11px; font-weight: 800; color: #4edea3; text-transform: uppercase; padding: 8px 0; background-color: #090e1c; border-radius: 8px; border: 1px solid #1c2638;">Sat</div>
            </div>

            {{-- 7-Column Days Grid --}}
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;">
                @foreach ($cal['calendarDays'] as $cDay)
                    @php
                        $cStatus = $cDay['status'];
                        $isCur = $cDay['isCurrentMonth'];
                        $isDaySelected = $cDay['isSelected'] ?? false;
                        $isSunOff = $cDay['isWeekend'];
                    @endphp
                    <div
                        wire:click="selectDay('{{ $cDay['date'] }}')"
                        style="min-height: 78px; border-radius: 10px; cursor: pointer; transition: all 0.15s ease; border: {{ $isDaySelected ? '2px solid #3b82f6' : ($cDay['isToday'] ? '1.5px solid #1E40AF' : '1px solid #222a3d') }}; background-color: {{ $isDaySelected ? 'rgba(30, 64, 175, 0.28)' : ($isSunOff ? '#0b1326' : '#171f33') }}; padding: 8px; display: flex; flex-direction: column; justify-content: space-between; {{ $isDaySelected ? 'box-shadow: 0 0 14px rgba(59,130,246,0.35);' : '' }} {{ !$isCur ? 'opacity: 0.32;' : '' }}"
                    >
                        {{-- Top: Day Number and Selected/Today Tags --}}
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 12px; font-weight: {{ $isDaySelected || $cDay['isToday'] ? '800' : '600' }}; color: {{ $isDaySelected ? '#ffffff' : ($cDay['isToday'] ? '#b8c4ff' : ($isSunOff ? '#a5b4fc' : '#dae2fd')) }};">
                                {{ $cDay['day'] }}
                            </span>
                            <div style="display: flex; align-items: center; gap: 3px;">
                                @if ($isDaySelected)
                                    <span style="font-size: 8px; font-weight: 800; background-color: #1E40AF; color: #FFFFFF; padding: 1px 4px; border-radius: 4px; letter-spacing: 0.05em;">SELECTED</span>
                                @elseif ($cDay['isToday'])
                                    <span style="font-size: 8px; font-weight: 800; background-color: rgba(30, 64, 175, 0.35); color: #b8c4ff; padding: 1px 4px; border-radius: 4px; border: 1px solid rgba(59, 130, 246, 0.3);">TODAY</span>
                                @endif
                            </div>
                        </div>

                        {{-- Bottom: Status Pill (No Late) --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 2px; margin-top: 6px;">
                            <div>
                                @if ($cDay['isWeekend'])
                                    @if ($cStatus === 'present')
                                        <span style="font-size: 9.5px; font-weight: 700; color: #10B981; background-color: rgba(16, 185, 129, 0.15); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(78, 222, 163, 0.3);">✓ Sun Present</span>
                                    @else
                                        <span style="font-size: 9.5px; font-weight: 600; color: #a5b4fc; background-color: #0f172a; padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(99, 102, 241, 0.25);">Week-Off</span>
                                    @endif
                                @elseif ($cStatus === 'present')
                                    <span style="font-size: 9.5px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.18); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(78, 222, 163, 0.35); display: inline-flex; align-items: center; gap: 3px;">
                                        <span>✓</span> Present
                                    </span>
                                @elseif ($cStatus === 'leave')
                                    <span style="font-size: 9.5px; font-weight: 700; color: #c7d2fe; background-color: rgba(99, 102, 241, 0.18); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(99, 102, 241, 0.35); display: inline-flex; align-items: center; gap: 3px;">
                                        <span>🏖</span> Leave
                                    </span>
                                @elseif ($cStatus === 'absent')
                                    <span style="font-size: 9.5px; font-weight: 700; color: #f87171; background-color: rgba(239, 68, 68, 0.18); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(239, 68, 68, 0.35); display: inline-flex; align-items: center; gap: 3px;">
                                        <span>✕</span> Absent
                                    </span>
                                @elseif ($cStatus === 'unmarked')
                                    <span style="font-size: 9px; font-weight: 500; color: #64748b; background-color: rgba(100, 116, 139, 0.1); padding: 2px 5px; border-radius: 4px; border: 1px dashed rgba(100, 116, 139, 0.3);">
                                        Unmarked
                                    </span>
                                @else
                                    <span style="font-size: 9px; font-weight: 500; color: #475569;">
                                        —
                                    </span>
                                @endif
                            </div>

                            @if (!empty($cDay['note']))
                                <span title="{{ $cDay['note'] }}" style="color: #93c5fd; font-size: 10px;">
                                    💬
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 5. Selected Day Inspection Box (No Late) --}}
        @if (!empty($cal['selectedDayInfo']))
            @php $selDay = $cal['selectedDayInfo']; @endphp
            <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 20px 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); margin-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    {{-- Left: Date & Status --}}
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 46px; height: 46px; border-radius: 12px; background-color: #171f33; border: 1px solid #222a3d; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0;">
                            <span style="font-size: 9px; font-weight: 700; color: #8e909f; text-transform: uppercase;">{{ \Carbon\Carbon::parse($selDay['date'])->format('M') }}</span>
                            <span style="font-size: 16px; font-weight: 800; color: #dae2fd; line-height: 1;">{{ \Carbon\Carbon::parse($selDay['date'])->format('d') }}</span>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <h4 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0;">
                                    {{ $selDay['formatted'] }}
                                </h4>
                                @if ($selDay['isToday'])
                                    <span style="font-size: 10px; font-weight: 700; background-color: rgba(30, 64, 175, 0.3); color: #b8c4ff; padding: 2px 7px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.3);">
                                        TODAY
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 12px; color: #8e909f; margin-top: 3px;">
                                Shift Timing: <strong style="color: #dae2fd;">{{ $cal['batchTiming'] ?? 'Standard Intern Shift (10:00 AM - 06:00 PM)' }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Status Chip & Remarks (No Late) --}}
                    <div style="display: flex; align-items: center; gap: 12px;">
                        @if ($selDay['status'] === 'present')
                            <div style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.35); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981;"></span>
                                <span>Checked-in: Full Duty Present</span>
                            </div>
                        @elseif ($selDay['status'] === 'leave')
                            <div style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(99, 102, 241, 0.15); color: #c7d2fe; border: 1px solid rgba(99, 102, 241, 0.35); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;">
                                <span>🏖 Approved Leave</span>
                            </div>
                        @elseif ($selDay['status'] === 'absent')
                            <div style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;">
                                <span>✕ Unexcused Absence</span>
                            </div>
                        @elseif ($selDay['isWeekend'])
                            <div style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(99, 102, 241, 0.12); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.25); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600;">
                                <span>☕ Scheduled Sunday Week-Off</span>
                            </div>
                        @else
                            <div style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(148, 163, 184, 0.12); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600;">
                                <span>⏳ No Roll-Call Marked</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if (!empty($selDay['note']))
                    <div style="margin-top: 14px; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; font-size: 12.5px; color: #dae2fd;">
                        <span style="color: #8e909f; font-weight: 600;">Supervisor / Roll-Call Note: </span> {{ $selDay['note'] }}
                    </div>
                @endif
            </div>
        @endif
    @else
        {{-- When in Table List View --}}
        <div class="flex flex-col gap-y-4">
            <x-filament-panels::resources.tabs />

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

            {{ $this->table }}

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
        </div>
    @endif

</div>
</x-filament-panels::page>
