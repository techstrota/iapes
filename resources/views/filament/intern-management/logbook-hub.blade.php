<x-filament-panels::page>
    <div class="logbook-hub-container" style="font-family: 'Plus Jakarta Sans', Inter, -apple-system, BlinkMacSystemFont, sans-serif; color: #dae2fd;">

        <style>
            .logbook-hub-container * { box-sizing: border-box; }
            .hub-tab-btn { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: none; }
            .hub-tab-btn.active { background-color: #1E40AF !important; color: #FFFFFF !important; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.45); }
            .hub-tab-btn:not(.active):hover { background-color: #222a3d; color: #dae2fd; }
            
            .subtab-btn { transition: all 0.15s ease; cursor: pointer; border: none; }
            .subtab-btn.active-sub { background-color: #171f33 !important; border-bottom: 2px solid #3b82f6 !important; color: #dae2fd !important; }

            .stat-card { transition: transform 0.15s ease, box-shadow 0.15s ease; }
            .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.35); }

            .calendar-cell-btn { transition: all 0.15s ease; cursor: pointer; }
            .calendar-cell-btn:hover { border-color: #3b82f6 !important; transform: translateY(-1px); }

            .intern-item-card { transition: all 0.15s ease; cursor: pointer; }
            .intern-item-card:hover { background-color: #1a233a !important; border-color: #334155 !important; }
            .intern-item-card.selected-intern { background-color: rgba(30, 64, 175, 0.2) !important; border-color: #3b82f6 !important; box-shadow: inset 3px 0 0 #3b82f6; }

            .log-card { transition: all 0.15s ease; }
            .log-card:hover { border-color: #334155 !important; }

            .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #0c1222; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #222a3d; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #3b82f6; }

            @media (max-width: 1024px) {
                .kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
                .split-ledger-grid { grid-template-columns: 1fr !important; }
                .inspection-grid { grid-template-columns: 1fr !important; }
            }
            @media (max-width: 640px) {
                .kpi-grid { grid-template-columns: 1fr !important; }
                .header-flex { flex-direction: column !important; align-items: stretch !important; }
                .tab-bar-wrap { flex-direction: column !important; align-items: stretch !important; }
                .calendar-grid-7 { font-size: 11px !important; gap: 4px !important; }
            }
        </style>

        {{-- 1. TOP HEADER & ACTION RIBBON --}}
        <div class="header-flex" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 22px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; border: 1px solid rgba(59, 130, 246, 0.3);">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #3b82f6; box-shadow: 0 0 6px #3b82f6;"></span>
                    Internship Operations
                </div>
                <h1 style="font-size: 26px; font-weight: 800; color: #dae2fd; margin: 0; line-height: 1.2; letter-spacing: -0.02em;">
                    Intern Daily Logbook Command Center
                </h1>
                <p style="font-size: 13px; color: #8e909f; margin: 4px 0 0; font-weight: 500;">
                    Monitor daily activity submissions, inspect task notes, track compliance, and identify missing records.
                </p>
            </div>

            {{-- Quick Create Action Button --}}
            <div style="display: flex; align-items: center; gap: 12px;">
                <button
                    type="button"
                    wire:click="openCreateLogModal"
                    style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #1E40AF 0%, #2563eb 100%); color: #ffffff; font-size: 13px; font-weight: 700; padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.15); cursor: pointer; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35); transition: all 0.15s ease;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>+ New Logbook Entry</span>
                </button>
            </div>
        </div>

        {{-- 2. EXECUTIVE KPI CARDS --}}
        <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px;">
            {{-- Total Active Interns --}}
            <div class="stat-card" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">Active Interns</span>
                    <div style="width: 34px; height: 34px; border-radius: 8px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                </div>
                <div style="margin-top: 12px;">
                    <div style="font-size: 30px; font-weight: 800; color: #dae2fd; line-height: 1;">{{ $stats['totalActive'] }}</div>
                    <p style="font-size: 12px; color: #8e909f; font-weight: 500; margin: 4px 0 0;">Total Enrolled in Cohort</p>
                </div>
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 12px; overflow: hidden;">
                    <div style="width: 100%; height: 100%; background-color: #3b82f6; border-radius: 9999px;"></div>
                </div>
            </div>

            {{-- Logged on Selected Date --}}
            <div class="stat-card" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11px; font-weight: 700; color: #4edea3; text-transform: uppercase; letter-spacing: 0.05em;">Submitted Logs</span>
                    <div style="width: 34px; height: 34px; border-radius: 8px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(78, 222, 163, 0.3);">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div style="margin-top: 12px;">
                    <div style="display: flex; align-items: baseline; gap: 6px;">
                        <span style="font-size: 30px; font-weight: 800; color: #4edea3; line-height: 1;">{{ $stats['loggedToday'] }}</span>
                        <span style="font-size: 13px; font-weight: 700; color: #8e909f;">/ {{ $stats['totalActive'] }}</span>
                    </div>
                    <p style="font-size: 12px; color: #4edea3; font-weight: 600; margin: 4px 0 0;">
                        {{ $stats['rateToday'] }}% submission rate
                    </p>
                </div>
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 12px; overflow: hidden;">
                    <div style="width: {{ $stats['rateToday'] }}%; height: 100%; background-color: #10B981; border-radius: 9999px;"></div>
                </div>
            </div>

            {{-- Missing Logs --}}
            <div class="stat-card" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11px; font-weight: 700; color: #ffb4ab; text-transform: uppercase; letter-spacing: 0.05em;">Missing Submissions</span>
                    <div style="width: 34px; height: 34px; border-radius: 8px; background-color: rgba(239, 68, 68, 0.15); color: #ffb4ab; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                </div>
                <div style="margin-top: 12px;">
                    <div style="display: flex; align-items: baseline; gap: 6px;">
                        <span style="font-size: 30px; font-weight: 800; color: {{ $stats['missingToday'] > 0 ? '#ffb4ab' : '#8e909f' }}; line-height: 1;">{{ $stats['missingToday'] }}</span>
                        <span style="font-size: 13px; font-weight: 700; color: #8e909f;">pending</span>
                    </div>
                    <p style="font-size: 12px; color: #ffb4ab; font-weight: 600; margin: 4px 0 0;">
                        @if ($stats['isSunday'])
                            <span style="color: #a5b4fc;">Sunday (Week-Off)</span>
                        @elseif ($stats['isHoliday'])
                            <span style="color: #c084fc;">Scheduled Holiday</span>
                        @else
                            Awaiting daily log note
                        @endif
                    </p>
                </div>
                @php $missingPct = $stats['totalActive'] > 0 ? round(($stats['missingToday'] / $stats['totalActive']) * 100) : 0; @endphp
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 12px; overflow: hidden;">
                    <div style="width: {{ $missingPct }}%; height: 100%; background-color: #EF4444; border-radius: 9999px;"></div>
                </div>
            </div>

            {{-- Monthly Log Volume --}}
            <div class="stat-card" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11px; font-weight: 700; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.05em;">Monthly Volume</span>
                    <div style="width: 34px; height: 34px; border-radius: 8px; background-color: rgba(2, 132, 199, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(147, 197, 253, 0.3);">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                </div>
                <div style="margin-top: 12px;">
                    <div style="font-size: 30px; font-weight: 800; color: #93c5fd; line-height: 1;">{{ $stats['monthTotalLogs'] }}</div>
                    <p style="font-size: 12px; color: #93c5fd; font-weight: 600; margin: 4px 0 0;">Entries Logged in {{ Carbon\Carbon::parse($calendarMonth . '-01')->format('M Y') }}</p>
                </div>
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 12px; overflow: hidden;">
                    <div style="width: 100%; height: 100%; background-color: #0284C7; border-radius: 9999px;"></div>
                </div>
            </div>
        </div>

        {{-- 3. NAVIGATION TABS & MONTH CONTROL BAR --}}
        <div class="tab-bar-wrap" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 22px;">
            {{-- Tabs --}}
            <div style="display: flex; align-items: center; gap: 6px; background-color: #0c1222; padding: 4px; border-radius: 10px; border: 1px solid #222a3d;">
                <button
                    type="button"
                    wire:click="setActiveTab('datewise')"
                    class="hub-tab-btn {{ $activeTab === 'datewise' ? 'active' : '' }}"
                    style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; color: #8e909f; background: transparent;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>Date-Wise Cohort Overview</span>
                </button>

                <button
                    type="button"
                    wire:click="setActiveTab('individual')"
                    class="hub-tab-btn {{ $activeTab === 'individual' ? 'active' : '' }}"
                    style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; color: #8e909f; background: transparent;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span>Intern-Wise Individual Ledger</span>
                </button>

                <button
                    type="button"
                    wire:click="setActiveTab('register')"
                    class="hub-tab-btn {{ $activeTab === 'register' ? 'active' : '' }}"
                    style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; color: #8e909f; background: transparent;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    <span>All Logs Register</span>
                </button>
            </div>

            {{-- Month Navigator --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <button
                    type="button"
                    wire:click="previousMonth"
                    title="Previous Month"
                    style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #222a3d; background-color: #171f33; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #dae2fd;">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                </button>

                <div style="background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 6px 14px; font-size: 13px; font-weight: 800; color: #dae2fd; min-width: 125px; text-align: center;">
                    {{ Carbon\Carbon::parse($calendarMonth . '-01')->format('F Y') }}
                </div>

                <button
                    type="button"
                    wire:click="nextMonth"
                    title="Next Month"
                    style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #222a3d; background-color: #171f33; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #dae2fd;">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                </button>

                <button
                    type="button"
                    wire:click="goToCurrentMonth"
                    style="padding: 6px 12px; font-size: 12px; font-weight: 700; color: #b8c4ff; background-color: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 8px; cursor: pointer;">
                    Today
                </button>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 1: DATE-WISE COHORT OVERVIEW & DAY INSPECTION DECK   --}}
        {{-- ======================================================== --}}
        @if ($activeTab === 'datewise')
            {{-- Cohort Monthly Progress Calendar Grid --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 20px; margin-bottom: 22px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <h2 style="font-size: 16px; font-weight: 800; color: #dae2fd; margin: 0;">
                            Cohort Logbook Submission Calendar — {{ $datewiseCalendar['monthTitle'] }}
                        </h2>
                        <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0;">
                            Click on any calendar day to inspect submitted work notes and review missing interns for that specific date.
                        </p>
                    </div>

                    {{-- Calendar Legend --}}
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 11px; font-weight: 700; color: #8e909f; flex-wrap: wrap;">
                        <span style="display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10B981;"></span>
                            Logged
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #EF4444;"></span>
                            Missing
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #8b5cf6;"></span>
                            Holiday
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #6366f1;"></span>
                            Sunday (Off)
                        </span>
                    </div>
                </div>

                {{-- 7-Day Header --}}
                <div class="calendar-grid-7" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; text-align: center; margin-bottom: 8px;">
                    <div style="font-size: 11px; font-weight: 800; color: #a5b4fc; text-transform: uppercase; padding: 6px 0; background-color: rgba(99, 102, 241, 0.15); border-radius: 6px; border: 1px solid rgba(99, 102, 241, 0.3);">Sun (Off)</div>
                    <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Mon</div>
                    <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Tue</div>
                    <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Wed</div>
                    <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Thu</div>
                    <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Fri</div>
                    <div style="font-size: 11px; font-weight: 800; color: #4edea3; text-transform: uppercase; padding: 6px 0;">Sat</div>
                </div>

                {{-- Calendar Tiles Grid --}}
                <div class="calendar-grid-7" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;">
                    @foreach ($datewiseCalendar['calendarDays'] as $day)
                        @php
                            $isSelected = $day['isSelected'];
                            $isCurMonth = $day['isCurrentMonth'];
                            $isToday = $day['isToday'];
                            $isSunday = $day['isSunday'];
                            $isHoliday = $day['isHoliday'];
                            $logged = $day['loggedCount'];
                            $missing = $day['missingCount'];
                            $total = $day['totalActive'];
                            $rate = $day['rate'];
                        @endphp
                        <div
                            wire:click="selectDate('{{ $day['date'] }}')"
                            class="calendar-cell-btn"
                            style="min-height: 80px; border-radius: 10px; padding: 8px; display: flex; flex-direction: column; justify-content: space-between; background-color: {{ $isSelected ? '#1c2847' : ($isSunday ? '#0c1222' : '#171f33') }}; border: 2px solid {{ $isSelected ? '#3b82f6' : ($isToday ? '#60a5fa' : '#222a3d') }}; opacity: {{ $isCurMonth ? '1' : '0.35' }}; box-shadow: {{ $isSelected ? '0 0 12px rgba(59,130,246,0.3)' : 'none' }};">
                            
                            {{-- Day Header --}}
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 13px; font-weight: {{ $isSelected || $isToday ? '800' : '700' }}; color: {{ $isSelected ? '#60a5fa' : ($isToday ? '#93c5fd' : ($isSunday ? '#a5b4fc' : '#dae2fd')) }};">
                                    {{ $day['day'] }}
                                </span>
                                @if ($isToday)
                                    <span style="font-size: 9px; font-weight: 800; background-color: rgba(59, 130, 246, 0.25); color: #93c5fd; padding: 1px 6px; border-radius: 4px; text-transform: uppercase;">Today</span>
                                @endif
                            </div>

                            {{-- Tile Body Content --}}
                            <div style="margin: 4px 0;">
                                @if ($isSunday)
                                    <div style="font-size: 10px; font-weight: 700; color: #a5b4fc; background: rgba(99, 102, 241, 0.12); padding: 2px 4px; border-radius: 4px; text-align: center; border: 1px dashed rgba(99, 102, 241, 0.3);">
                                        Sunday (Off)
                                    </div>
                                @elseif ($isHoliday)
                                    <div style="font-size: 10px; font-weight: 700; color: #c084fc; background: rgba(139, 92, 246, 0.15); padding: 2px 4px; border-radius: 4px; text-align: center; border: 1px solid rgba(139, 92, 246, 0.3);">
                                        Holiday
                                    </div>
                                @else
                                    {{-- Working Day Status --}}
                                    <div style="display: flex; flex-direction: column; gap: 3px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 10px; font-weight: 700;">
                                            <span style="color: #4edea3;">✓ {{ $logged }} Logged</span>
                                            @if ($missing > 0)
                                                <span style="color: #ffb4ab;">✕ {{ $missing }}</span>
                                            @endif
                                        </div>
                                        <div style="width: 100%; height: 3px; background-color: #0c1222; border-radius: 9999px; overflow: hidden;">
                                            <div style="width: {{ $rate }}%; height: 100%; background-color: {{ $rate >= 80 ? '#10B981' : ($rate >= 40 ? '#f59e0b' : '#EF4444') }};"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Bottom Indicator Bar if Selected --}}
                            @if ($isSelected)
                                <div style="width: 100%; height: 2px; background-color: #3b82f6; border-radius: 2px;"></div>
                            @else
                                <div style="height: 2px;"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Day Inspection Deck for Selected Date --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 22px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                {{-- Inspection Header & Filter Controls --}}
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; border-bottom: 1px solid #222a3d; padding-bottom: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(59, 130, 246, 0.3);">
                            <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h3 style="font-size: 18px; font-weight: 800; color: #dae2fd; margin: 0;">
                                    Daily Breakdown: {{ $datewiseDayDetails['dateFormatted'] }}
                                </h3>
                                @if ($datewiseDayDetails['isToday'])
                                    <span style="font-size: 10px; font-weight: 800; background-color: rgba(59, 130, 246, 0.2); color: #93c5fd; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Today</span>
                                @endif
                                @if ($datewiseDayDetails['isSunday'])
                                    <span style="font-size: 10px; font-weight: 800; background-color: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 2px 8px; border-radius: 9999px;">Sunday Week-Off</span>
                                @elseif ($datewiseDayDetails['isHoliday'])
                                    <span style="font-size: 10px; font-weight: 800; background-color: rgba(139, 92, 246, 0.2); color: #c084fc; padding: 2px 8px; border-radius: 9999px;">{{ $datewiseDayDetails['holidayName'] }}</span>
                                @endif
                            </div>
                            <p style="font-size: 12px; color: #8e909f; margin: 3px 0 0;">
                                Tracking compliance for <strong style="color: #dae2fd;">{{ $datewiseDayDetails['totalCount'] }}</strong> active interns on this date.
                            </p>
                        </div>
                    </div>

                    {{-- Search & Batch Filters --}}
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <div style="position: relative; min-width: 200px;">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Filter intern by name/code..."
                                style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 12px 7px 32px; font-size: 12px; color: #dae2fd; outline: none;"
                            />
                            <svg style="position: absolute; left: 10px; top: 9px; width: 14px; height: 14px; color: #8e909f;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>

                        <select
                            wire:model.live="selectedBatchId"
                            style="background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 12px; font-size: 12px; color: #dae2fd; outline: none;">
                            <option value="">All Internship Batches</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Non-Working Day Notice Banner --}}
                @if ($datewiseDayDetails['isSunday'] || $datewiseDayDetails['isHoliday'])
                    <div style="background-color: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                        <svg style="width: 20px; height: 20px; color: #a5b4fc; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div style="font-size: 12px; color: #b8c4ff;">
                            <strong>Scheduled Rest Day / Holiday:</strong> Daily logbook submissions are disabled for official holidays and Sundays according to company policy. Interns are not flagged as missing on this date.
                        </div>
                    </div>
                @endif

                {{-- Sub-Tabs: Submitted Logs vs Missing Interns --}}
                <div style="display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #222a3d; margin-bottom: 20px;">
                    <button
                        type="button"
                        wire:click="setDatewiseSubTab('submitted')"
                        class="subtab-btn {{ $datewiseSubTab === 'submitted' ? 'active-sub' : '' }}"
                        style="padding: 10px 18px; font-size: 13px; font-weight: 700; color: {{ $datewiseSubTab === 'submitted' ? '#4edea3' : '#8e909f' }}; background: transparent; display: inline-flex; align-items: center; gap: 8px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10B981;"></span>
                        Submitted Logs ({{ $datewiseDayDetails['submittedCount'] }})
                    </button>

                    <button
                        type="button"
                        wire:click="setDatewiseSubTab('missing')"
                        class="subtab-btn {{ $datewiseSubTab === 'missing' ? 'active-sub' : '' }}"
                        style="padding: 10px 18px; font-size: 13px; font-weight: 700; color: {{ $datewiseSubTab === 'missing' ? '#ffb4ab' : '#8e909f' }}; background: transparent; display: inline-flex; align-items: center; gap: 8px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #EF4444;"></span>
                        Missing Interns ({{ $datewiseDayDetails['missingCount'] }})
                    </button>
                </div>

                {{-- Sub-Tab Content: Submitted Logs List --}}
                @if ($datewiseSubTab === 'submitted')
                    @if (empty($datewiseDayDetails['submitted']))
                        <div style="text-align: center; padding: 48px 20px; background-color: #0c1222; border-radius: 12px; border: 1px dashed #222a3d;">
                            <svg style="width: 42px; height: 42px; color: #8e909f; margin: 0 auto 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <h4 style="font-size: 15px; font-weight: 700; color: #dae2fd; margin: 0;">No Log Entries Recorded For This Date</h4>
                            <p style="font-size: 12px; color: #8e909f; margin: 6px 0 16px;">No interns have submitted daily work logs for {{ $datewiseDayDetails['dateFormatted'] }}.</p>
                            @if (!$datewiseDayDetails['isSunday'] && !$datewiseDayDetails['isHoliday'])
                                <button
                                    type="button"
                                    wire:click="setDatewiseSubTab('missing')"
                                    style="padding: 8px 16px; font-size: 12px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; cursor: pointer;">
                                    View Missing Interns List
                                </button>
                            @endif
                        </div>
                    @else
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 16px;">
                            @foreach ($datewiseDayDetails['submitted'] as $item)
                                @php
                                    $intern = $item['intern'];
                                    $log = $item['log'];
                                    $initials = strtoupper(substr($intern->name, 0, 2));
                                @endphp
                                <div class="log-card" style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        {{-- Intern Identity Header --}}
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #1e40af, #3b82f6); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
                                                    {{ $initials }}
                                                </div>
                                                <div>
                                                    <div style="font-size: 14px; font-weight: 800; color: #dae2fd;">{{ $intern->name }}</div>
                                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #8e909f; margin-top: 1px;">
                                                        <span style="font-weight: 700; color: #93c5fd;">{{ $intern->intern_code }}</span>
                                                        <span>•</span>
                                                        <span>{{ $intern->batch?->batch_name ?? 'No Batch' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Status Pill --}}
                                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(78, 222, 163, 0.3); padding: 3px 8px; border-radius: 9999px;">
                                                <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                Submitted
                                            </span>
                                        </div>

                                        {{-- Today's Work Note Block --}}
                                        <div style="background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                                            <div style="font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                                                Today's Work Note
                                            </div>
                                            <div style="font-size: 13px; color: #dae2fd; line-height: 1.5; white-space: pre-wrap; font-family: inherit;">
                                                {{ $log->tasks_completed }}
                                            </div>
                                        </div>

                                        {{-- Supervisor Remarks (if present) --}}
                                        @if (filled($log->supervisor_remarks))
                                            <div style="background-color: rgba(245, 158, 11, 0.08); border-left: 3px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 8px 12px; margin-bottom: 12px;">
                                                <div style="font-size: 10px; font-weight: 800; color: #fbbf24; text-transform: uppercase; margin-bottom: 3px;">
                                                    Supervisor Remarks
                                                </div>
                                                <div style="font-size: 12px; color: #fef3c7; line-height: 1.4;">
                                                    {{ $log->supervisor_remarks }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Actions: Edit & Delete --}}
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; border-top: 1px solid #222a3d; padding-top: 12px; margin-top: 6px;">
                                        <button
                                            type="button"
                                            wire:click="openEditLogModal({{ $log->id }})"
                                            style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; font-size: 12px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 6px; cursor: pointer;">
                                            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            Edit Note
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="deleteLog({{ $log->id }})"
                                            wire:confirm="Are you sure you want to delete this logbook entry? This action cannot be undone."
                                            style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; font-size: 12px; font-weight: 700; color: #ffb4ab; background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;">
                                            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

                {{-- Sub-Tab Content: Missing Interns List --}}
                @if ($datewiseSubTab === 'missing')
                    @if (empty($datewiseDayDetails['missing']))
                        <div style="text-align: center; padding: 48px 20px; background-color: #0c1222; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.3);">
                            <div style="width: 46px; height: 46px; border-radius: 50%; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <h4 style="font-size: 16px; font-weight: 800; color: #4edea3; margin: 0;">100% Submission Compliance!</h4>
                            <p style="font-size: 12px; color: #8e909f; margin: 6px 0 0;">All enrolled active interns have completed their daily log notes for this date.</p>
                        </div>
                    @else
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px;">
                            @foreach ($datewiseDayDetails['missing'] as $item)
                                @php
                                    $intern = $item['intern'];
                                    $initials = strtoupper(substr($intern->name, 0, 2));
                                @endphp
                                <div style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #222a3d; color: #ffb4ab; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; border: 1px solid rgba(239, 68, 68, 0.3);">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div style="font-size: 13px; font-weight: 800; color: #dae2fd;">{{ $intern->name }}</div>
                                            <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                                <span style="color: #93c5fd; font-weight: 600;">{{ $intern->intern_code }}</span>
                                                • {{ $intern->batch?->batch_name ?? 'No Batch' }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 1-Click Add Log Action --}}
                                    <button
                                        type="button"
                                        wire:click="openCreateLogModal({{ $intern->id }}, '{{ $selectedDate }}')"
                                        style="display: inline-flex; align-items: center; gap: 5px; padding: 7px 12px; font-size: 12px; font-weight: 700; color: #ffffff; background-color: #1E40AF; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 6px rgba(30,64,175,0.4); white-space: nowrap;">
                                        <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                        + Add Log
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- TAB 2: INTERN-WISE INDIVIDUAL LEDGER                     --}}
        {{-- ======================================================== --}}
        @if ($activeTab === 'individual')
            <div class="split-ledger-grid" style="display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: start;">
                
                {{-- LEFT: Intern Directory & Search --}}
                <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <h3 style="font-size: 15px; font-weight: 800; color: #dae2fd; margin: 0;">Intern Directory</h3>
                        <span style="font-size: 11px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); padding: 2px 8px; border-radius: 9999px;">
                            {{ count($individualData['interns']) }} Interns
                        </span>
                    </div>

                    {{-- Search Input --}}
                    <div style="position: relative; margin-bottom: 10px;">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search intern name or ID..."
                            style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px 8px 32px; font-size: 12px; color: #dae2fd; outline: none;"
                        />
                        <svg style="position: absolute; left: 10px; top: 10px; width: 14px; height: 14px; color: #8e909f;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>

                    {{-- Batch Selector --}}
                    <div style="margin-bottom: 14px;">
                        <select
                            wire:model.live="selectedBatchId"
                            style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 10px; font-size: 12px; color: #dae2fd; outline: none;">
                            <option value="">All Batches</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Scrollable List of Interns --}}
                    <div class="custom-scrollbar" style="max-height: 560px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; padding-right: 4px;">
                        @forelse ($individualData['interns'] as $intern)
                            @php
                                $isSelected = $selectedInternId === $intern->id;
                                $initials = strtoupper(substr($intern->name, 0, 2));
                            @endphp
                            <div
                                wire:click="selectIntern({{ $intern->id }})"
                                class="intern-item-card {{ $isSelected ? 'selected-intern' : '' }}"
                                style="background-color: {{ $isSelected ? '#1c2847' : '#171f33' }}; border: 1px solid {{ $isSelected ? '#3b82f6' : '#222a3d' }}; border-radius: 10px; padding: 10px 12px; display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: {{ $isSelected ? '#3b82f6' : '#222a3d' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">
                                        {{ $initials }}
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-size: 13px; font-weight: 800; color: #dae2fd; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $intern->name }}
                                        </div>
                                        <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                            <span style="color: #93c5fd; font-weight: 600;">{{ $intern->intern_code }}</span>
                                        </div>
                                    </div>
                                </div>

                                <svg style="width: 14px; height: 14px; color: {{ $isSelected ? '#3b82f6' : '#8e909f' }}; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 24px 10px; color: #8e909f; font-size: 12px;">
                                No interns match the filter.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- RIGHT: Individual Showcase & Timesheet Calendar --}}
                <div>
                    @if ($individualData['selectedIntern'])
                        @php
                            $intern = $individualData['selectedIntern'];
                            $initials = strtoupper(substr($intern->name, 0, 2));
                        @endphp
                        {{-- Intern Profile Summary Card --}}
                        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <div style="width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #1e40af, #3b82f6); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; box-shadow: 0 4px 12px rgba(30,64,175,0.4);">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <h2 style="font-size: 20px; font-weight: 800; color: #dae2fd; margin: 0;">{{ $intern->name }}</h2>
                                            <span style="font-size: 11px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.3);">
                                                {{ $intern->intern_code }}
                                            </span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: #8e909f; margin-top: 4px; flex-wrap: wrap;">
                                            <span><strong>Batch:</strong> {{ $intern->batch?->batch_name ?? 'N/A' }}</span>
                                            <span>•</span>
                                            <span><strong>Team:</strong> {{ $intern->team?->name ?? 'None' }}</span>
                                            <span>•</span>
                                            <span><strong>Email:</strong> {{ $intern->email }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Add Log for this Intern --}}
                                <button
                                    type="button"
                                    wire:click="openCreateLogModal({{ $intern->id }}, '{{ $selectedDate }}')"
                                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 12px; font-weight: 700; color: #ffffff; background-color: #1E40AF; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 8px rgba(30,64,175,0.4);">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                    + Add Log Entry
                                </button>
                            </div>

                            {{-- Individual Monthly Compliance KPI Badges --}}
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 18px; border-top: 1px solid #222a3d; padding-top: 16px;">
                                <div style="background-color: #0c1222; border-radius: 10px; padding: 12px 14px; border: 1px solid #222a3d;">
                                    <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Compliance Rate</span>
                                    <div style="font-size: 22px; font-weight: 800; color: #4edea3; margin-top: 4px;">
                                        {{ $individualData['complianceRate'] }}%
                                    </div>
                                    <div style="width: 100%; height: 3px; background-color: #171f33; border-radius: 9999px; margin-top: 6px; overflow: hidden;">
                                        <div style="width: {{ $individualData['complianceRate'] }}%; height: 100%; background-color: #10B981;"></div>
                                    </div>
                                </div>

                                <div style="background-color: #0c1222; border-radius: 10px; padding: 12px 14px; border: 1px solid #222a3d;">
                                    <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Logged Working Days</span>
                                    <div style="font-size: 22px; font-weight: 800; color: #93c5fd; margin-top: 4px;">
                                        {{ $individualData['monthLoggedCount'] }} <span style="font-size: 12px; font-weight: 600; color: #8e909f;">/ {{ $individualData['monthWorkingDays'] }} days</span>
                                    </div>
                                </div>

                                <div style="background-color: #0c1222; border-radius: 10px; padding: 12px 14px; border: 1px solid #222a3d;">
                                    <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Missing Log Entries</span>
                                    <div style="font-size: 22px; font-weight: 800; color: {{ $individualData['missingDaysCount'] > 0 ? '#ffb4ab' : '#8e909f' }}; margin-top: 4px;">
                                        {{ $individualData['missingDaysCount'] }} <span style="font-size: 12px; font-weight: 600; color: #8e909f;">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Individual Monthly Timesheet Calendar Grid --}}
                        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                                <h3 style="font-size: 16px; font-weight: 800; color: #dae2fd; margin: 0;">
                                    {{ $individualData['monthTitle'] }} Timesheet Ledger
                                </h3>
                                <div style="font-size: 11px; color: #8e909f;">Click any day to view or edit details below</div>
                            </div>

                            {{-- 7-Day Header --}}
                            <div class="calendar-grid-7" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; margin-bottom: 8px;">
                                <div style="font-size: 10px; font-weight: 800; color: #a5b4fc; text-transform: uppercase; padding: 4px 0; background-color: rgba(99, 102, 241, 0.15); border-radius: 4px;">Sun</div>
                                <div style="font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 4px 0;">Mon</div>
                                <div style="font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 4px 0;">Tue</div>
                                <div style="font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 4px 0;">Wed</div>
                                <div style="font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 4px 0;">Thu</div>
                                <div style="font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 4px 0;">Fri</div>
                                <div style="font-size: 10px; font-weight: 800; color: #4edea3; text-transform: uppercase; padding: 4px 0;">Sat</div>
                            </div>

                            {{-- Calendar Days Grid --}}
                            <div class="calendar-grid-7" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
                                @foreach ($individualData['calendarDays'] as $cell)
                                    @php
                                        $status = $cell['status'];
                                        $isSelected = $cell['isSelected'];
                                        $isCurMonth = $cell['isCurrentMonth'];
                                        $isToday = $cell['isToday'];
                                    @endphp
                                    <div
                                        wire:click="selectDate('{{ $cell['date'] }}')"
                                        class="calendar-cell-btn"
                                        style="min-height: 64px; border-radius: 8px; padding: 6px; display: flex; flex-direction: column; justify-content: space-between; background-color: {{ $isSelected ? '#1c2847' : '#171f33' }}; border: 2px solid {{ $isSelected ? '#3b82f6' : ($isToday ? '#60a5fa' : '#222a3d') }}; opacity: {{ $isCurMonth ? '1' : '0.35' }};">
                                        
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <span style="font-size: 12px; font-weight: 700; color: {{ $isSelected ? '#60a5fa' : '#dae2fd' }};">
                                                {{ $cell['day'] }}
                                            </span>
                                            @if ($isToday)
                                                <span style="font-size: 8px; font-weight: 800; background-color: rgba(59, 130, 246, 0.25); color: #93c5fd; padding: 1px 4px; border-radius: 3px;">NOW</span>
                                            @endif
                                        </div>

                                        {{-- Status Pill --}}
                                        <div>
                                            @if ($status === 'logged')
                                                <span style="font-size: 9px; font-weight: 800; color: #4edea3; background-color: rgba(16, 185, 129, 0.2); padding: 2px 4px; border-radius: 4px; display: block; text-align: center;">
                                                    📝 Logged
                                                </span>
                                            @elseif ($status === 'missing')
                                                <span style="font-size: 9px; font-weight: 800; color: #ffb4ab; background-color: rgba(239, 68, 68, 0.2); padding: 2px 4px; border-radius: 4px; display: block; text-align: center;">
                                                    ⚠️ Missing
                                                </span>
                                            @elseif ($status === 'weekend')
                                                <span style="font-size: 9px; font-weight: 700; color: #a5b4fc; background: rgba(99, 102, 241, 0.1); padding: 2px 4px; border-radius: 4px; display: block; text-align: center;">
                                                    Week-Off
                                                </span>
                                            @elseif ($status === 'holiday')
                                                <span style="font-size: 9px; font-weight: 700; color: #c084fc; background: rgba(139, 92, 246, 0.15); padding: 2px 4px; border-radius: 4px; display: block; text-align: center;">
                                                    Holiday
                                                </span>
                                            @else
                                                <span style="font-size: 9px; color: #8e909f; display: block; text-align: center;">-</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Selected Day Drawer for this Intern --}}
                        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 14px; margin-bottom: 16px;">
                                <div>
                                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Selected Date Inspection</div>
                                    <h4 style="font-size: 16px; font-weight: 800; color: #dae2fd; margin: 2px 0 0;">
                                        {{ Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
                                    </h4>
                                </div>

                                @if ($individualData['selectedDayLog'])
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <button
                                            type="button"
                                            wire:click="openEditLogModal({{ $individualData['selectedDayLog']->id }})"
                                            style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; font-size: 12px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 6px; cursor: pointer;">
                                            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            Edit Entry
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="deleteLog({{ $individualData['selectedDayLog']->id }})"
                                            wire:confirm="Are you sure you want to delete this logbook entry?"
                                            style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; font-size: 12px; font-weight: 700; color: #ffb4ab; background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;">
                                            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            Delete
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if ($individualData['selectedDayLog'])
                                {{-- Log Details --}}
                                <div style="background-color: #0c1222; border: 1px solid #222a3d; border-radius: 10px; padding: 16px; margin-bottom: 12px;">
                                    <div style="font-size: 11px; font-weight: 800; color: #4edea3; text-transform: uppercase; margin-bottom: 6px;">
                                        ✓ Daily Work Note
                                    </div>
                                    <div style="font-size: 14px; color: #dae2fd; line-height: 1.6; white-space: pre-wrap;">
                                        {{ $individualData['selectedDayLog']->tasks_completed }}
                                    </div>
                                </div>

                                @if (filled($individualData['selectedDayLog']->supervisor_remarks))
                                    <div style="background-color: rgba(245, 158, 11, 0.08); border-left: 3px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 10px 14px;">
                                        <div style="font-size: 11px; font-weight: 800; color: #fbbf24; text-transform: uppercase; margin-bottom: 4px;">Supervisor Remarks</div>
                                        <div style="font-size: 13px; color: #fef3c7; line-height: 1.5;">
                                            {{ $individualData['selectedDayLog']->supervisor_remarks }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                {{-- Empty Log for Selected Day --}}
                                @php
                                    $cSelected = Carbon\Carbon::parse($selectedDate);
                                    $isSelSun = $cSelected->isSunday();
                                    $isSelHol = \App\Models\Attendance::whereDate('date', $selectedDate)->where('status', 'holiday')->exists();
                                @endphp
                                <div style="text-align: center; padding: 32px 16px; background-color: #0c1222; border-radius: 10px; border: 1px dashed #222a3d;">
                                    @if ($isSelSun)
                                        <p style="font-size: 13px; color: #a5b4fc; font-weight: 600; margin: 0;">
                                            Sunday is a scheduled week-off. Daily logbook is not required.
                                        </p>
                                    @elseif ($isSelHol)
                                        <p style="font-size: 13px; color: #c084fc; font-weight: 600; margin: 0;">
                                            Official Holiday. Daily logbook is not required.
                                        </p>
                                    @else
                                        <svg style="width: 36px; height: 36px; color: #ffb4ab; margin: 0 auto 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <h5 style="font-size: 14px; font-weight: 700; color: #dae2fd; margin: 0;">No Log Entry Found</h5>
                                        <p style="font-size: 12px; color: #8e909f; margin: 4px 0 14px;">This intern has not logged their activity for {{ $cSelected->format('d M Y') }}.</p>
                                        <button
                                            type="button"
                                            wire:click="openCreateLogModal({{ $intern->id }}, '{{ $selectedDate }}')"
                                            style="padding: 8px 16px; font-size: 12px; font-weight: 700; color: #ffffff; background-color: #1E40AF; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 8px rgba(30,64,175,0.4);">
                                            + Create Log Entry for This Day
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div style="text-align: center; padding: 60px 20px; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px;">
                            <p style="font-size: 14px; color: #8e909f;">Select an intern from the directory to view their activity ledger.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- TAB 3: ALL LOGS REGISTER (GLOBAL COMPREHENSIVE TABLE)    --}}
        {{-- ======================================================== --}}
        @if ($activeTab === 'register')
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                {{-- Table Filters Row --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #8e909f; margin-bottom: 4px;">SEARCH</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="registerSearch"
                            placeholder="Search note or intern..."
                            style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; font-size: 12px; color: #dae2fd; outline: none;"
                        />
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #8e909f; margin-bottom: 4px;">BATCH</label>
                        <select
                            wire:model.live="registerBatchId"
                            style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; font-size: 12px; color: #dae2fd; outline: none;">
                            <option value="">All Batches</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #8e909f; margin-bottom: 4px;">INTERN</label>
                        <select
                            wire:model.live="registerInternId"
                            style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; font-size: 12px; color: #dae2fd; outline: none;">
                            <option value="">All Interns</option>
                            @foreach ($allActiveInterns as $ai)
                                <option value="{{ $ai->id }}">{{ $ai->name }} ({{ $ai->intern_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #8e909f; margin-bottom: 4px;">DATE FROM</label>
                        <input
                            type="date"
                            wire:model.live="registerDateFrom"
                            style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; font-size: 12px; color: #dae2fd; outline: none;"
                        />
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #8e909f; margin-bottom: 4px;">DATE UNTIL</label>
                        <input
                            type="date"
                            wire:model.live="registerDateUntil"
                            style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; font-size: 12px; color: #dae2fd; outline: none;"
                        />
                    </div>
                </div>

                {{-- Table --}}
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 1px solid #222a3d; background-color: #0c1222; color: #8e909f; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                <th style="padding: 12px 14px;">Date</th>
                                <th style="padding: 12px 14px;">Intern</th>
                                <th style="padding: 12px 14px; width: 45%;">Work Note (Today's Tasks)</th>
                                <th style="padding: 12px 14px;">Remarks</th>
                                <th style="padding: 12px 14px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($registerData as $row)
                                @php
                                    $dObj = Carbon\Carbon::parse($row->date);
                                    $initials = strtoupper(substr($row->intern?->name ?? 'NA', 0, 2));
                                @endphp
                                <tr style="border-bottom: 1px solid #1a233a;" class="intern-muster-row">
                                    <td style="padding: 14px; vertical-align: top; white-space: nowrap;">
                                        <div style="font-weight: 800; color: #dae2fd;">{{ $dObj->format('d M Y') }}</div>
                                        <div style="font-size: 11px; color: #8e909f;">{{ $dObj->format('l') }}</div>
                                    </td>

                                    <td style="padding: 14px; vertical-align: top;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 30px; height: 30px; border-radius: 50%; background: #1e40af; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; flex-shrink: 0;">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: #dae2fd;">{{ $row->intern?->name ?? 'Unknown' }}</div>
                                                <div style="font-size: 11px; color: #8e909f;">
                                                    <span style="color: #93c5fd;">{{ $row->intern?->intern_code }}</span>
                                                    • {{ $row->intern?->batch?->batch_name ?? 'No Batch' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td style="padding: 14px; vertical-align: top; line-height: 1.5; color: #dae2fd;">
                                        <div style="white-space: pre-wrap; font-family: inherit;">{{ $row->tasks_completed }}</div>
                                    </td>

                                    <td style="padding: 14px; vertical-align: top; color: #8e909f; font-size: 12px;">
                                        @if (filled($row->supervisor_remarks))
                                            <span style="color: #fef3c7; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                                {{ $row->supervisor_remarks }}
                                            </span>
                                        @else
                                            <span style="color: #64748b;">—</span>
                                        @endif
                                    </td>

                                    <td style="padding: 14px; vertical-align: top; text-align: right; white-space: nowrap;">
                                        <div style="display: inline-flex; align-items: center; gap: 6px;">
                                            <button
                                                type="button"
                                                wire:click="openEditLogModal({{ $row->id }})"
                                                title="Edit Entry"
                                                style="padding: 5px 8px; border-radius: 6px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); cursor: pointer;">
                                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="deleteLog({{ $row->id }})"
                                                wire:confirm="Are you sure you want to delete this log entry?"
                                                title="Delete Entry"
                                                style="padding: 5px 8px; border-radius: 6px; background-color: rgba(239, 68, 68, 0.15); color: #ffb4ab; border: 1px solid rgba(239, 68, 68, 0.3); cursor: pointer;">
                                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #8e909f;">
                                        No logbook entries found matching your query.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                <div style="margin-top: 20px;">
                    {{ $registerData->links() }}
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- POP-UP MODAL FORM FOR CREATE / EDIT DAILY LOG            --}}
        {{-- ======================================================== --}}
        @if ($showLogModal)
            <div style="position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px;">
                <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 16px; width: 100%; max-width: 580px; box-shadow: 0 20px 40px rgba(0,0,0,0.6); overflow: hidden; animation: fadeIn 0.15s ease;">
                    
                    {{-- Modal Header --}}
                    <div style="padding: 18px 24px; background-color: #171f33; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background-color: rgba(59, 130, 246, 0.2); color: #93c5fd; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 800; color: #dae2fd; margin: 0;">
                                    {{ $editingLogId ? 'Edit Daily Log Entry' : 'Create Daily Log Entry' }}
                                </h3>
                                <p style="font-size: 11px; color: #8e909f; margin: 2px 0 0;">
                                    Record daily accomplishments and optional supervisor remarks.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="closeLogModal"
                            style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #222a3d; background-color: #0c1222; color: #8e909f; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Modal Body Form --}}
                    <div style="padding: 22px 24px; display: flex; flex-direction: column; gap: 16px;">
                        
                        {{-- Intern Selector --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Intern <span style="color: #ef4444;">*</span>
                            </label>
                            <select
                                wire:model="modalInternId"
                                style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #dae2fd; outline: none;">
                                <option value="">Select an Intern</option>
                                @foreach ($allActiveInterns as $ai)
                                    <option value="{{ $ai->id }}">{{ $ai->name }} ({{ $ai->intern_code }})</option>
                                @endforeach
                            </select>
                            @error('modalInternId')
                                <span style="font-size: 11px; color: #ffb4ab; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Date Picker --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Log Date <span style="color: #ef4444;">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="modalDate"
                                style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #dae2fd; outline: none;"
                            />
                            @error('modalDate')
                                <span style="font-size: 11px; color: #ffb4ab; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                            <div style="font-size: 11px; color: #8e909f; margin-top: 4px;">
                                Note: Sundays and scheduled holidays are not allowed for daily activity entries.
                            </div>
                        </div>

                        {{-- Today's Work Note (tasks_completed) --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Today's Work Note (Tasks Completed) <span style="color: #ef4444;">*</span>
                            </label>
                            <textarea
                                wire:model="modalTasks"
                                rows="5"
                                placeholder="Describe what tasks were performed, completed, or progressed today..."
                                style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #dae2fd; outline: none; line-height: 1.5; resize: vertical;"></textarea>
                            @error('modalTasks')
                                <span style="font-size: 11px; color: #ffb4ab; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Supervisor Remarks (Optional) --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Supervisor Remarks / Feedback (Optional)
                            </label>
                            <textarea
                                wire:model="modalRemarks"
                                rows="3"
                                placeholder="Add guidance, review comments, or feedback for this entry..."
                                style="width: 100%; background-color: #0c1222; border: 1px solid #222a3d; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #dae2fd; outline: none; line-height: 1.5; resize: vertical;"></textarea>
                            @error('modalRemarks')
                                <span style="font-size: 11px; color: #ffb4ab; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 16px 24px; background-color: #171f33; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                        <button
                            type="button"
                            wire:click="closeLogModal"
                            style="padding: 9px 18px; font-size: 13px; font-weight: 600; color: #c4c5d5; background: transparent; border: 1px solid #222a3d; border-radius: 8px; cursor: pointer;">
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="saveLogModal"
                            wire:loading.attr="disabled"
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 22px; font-size: 13px; font-weight: 700; color: #ffffff; background: linear-gradient(135deg, #1E40AF 0%, #2563eb 100%); border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 10px rgba(30,64,175,0.4);">
                            <span wire:loading.remove wire:target="saveLogModal">
                                {{ $editingLogId ? 'Update Entry' : 'Save Log Entry' }}
                            </span>
                            <span wire:loading wire:target="saveLogModal">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
