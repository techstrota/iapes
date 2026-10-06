<x-filament-panels::page>
    <div class="attendance-hub-container" style="font-family: 'Plus Jakarta Sans', Inter, -apple-system, BlinkMacSystemFont, sans-serif; color: #dae2fd;">

        <style>
            .attendance-hub-container * { box-sizing: border-box; }
            .tab-btn { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: none; }
            .tab-btn.active { background-color: #1E40AF !important; color: #FFFFFF !important; box-shadow: 0 2px 8px rgba(30, 64, 175, 0.4); }
            .tab-btn:not(.active):hover { background-color: #222a3d; color: #dae2fd; }
            
            .stat-card { transition: transform 0.15s ease, box-shadow 0.15s ease; }
            .stat-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.3); }

            .intern-muster-row:hover { background-color: #171f33 !important; }
            .calendar-day-btn { transition: all 0.15s ease; }
            .calendar-day-btn:hover { border-color: #3b82f6 !important; transform: translateY(-1px); }

            .status-radio-card { transition: all 0.15s ease; cursor: pointer; }
            .status-radio-card:hover { transform: translateY(-1px); }

            @media (max-width: 1024px) {
                .grid-kpi-row { grid-template-columns: 1fr !important; }
                .muster-top-grid { grid-template-columns: 1fr !important; }
                .master-detail-grid { grid-template-columns: 1fr !important; }
                .pod-two-col { grid-template-columns: 1fr !important; }
            }
            @media (max-width: 768px) {
                .header-flex { flex-direction: column !important; align-items: stretch !important; }
                .tab-bar-wrap { flex-direction: column !important; align-items: stretch !important; }
                .calendar-grid-7 { font-size: 11px !important; gap: 4px !important; }
                .calendar-cell { min-height: 52px !important; padding: 4px !important; }
            }
        </style>

        {{-- 1. TOP CONTEXT RIBBON & DATE NAVIGATOR --}}
        <div class="header-flex" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; border: 1px solid rgba(78, 222, 163, 0.3);">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10B981;"></span>
                    Enterprise Attendance Hub
                </div>
                <h1 style="font-size: 24px; font-weight: 800; color: #dae2fd; margin: 0; line-height: 1.2; letter-spacing: -0.02em;">
                    Attendance Roll-Call & Muster Register
                </h1>
            </div>

            {{-- Controls & Date Picker --}}
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                {{-- Date Period Picker with Explicit Calendar Input --}}
                <div style="display: inline-flex; align-items: center; background-color: #131b2e; border: 2px solid {{ $isToday ? '#1E40AF' : '#2563EB' }}; border-radius: 10px; padding: 4px 8px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25); transition: all 0.2s ease;">
                    <button
                        type="button"
                        wire:click="previousDay"
                        title="Previous Day"
                        style="width: 28px; height: 28px; border: none; background: #171f33; border-radius: 6px; color: #dae2fd; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color 0.15s ease;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div style="padding: 0 8px; display: flex; align-items: center; gap: 8px;">
                        <svg style="width: 17px; height: 17px; color: #b8c4ff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{-- Interactive HTML5 Datepicker allowing immediate selection of any date --}}
                        <input
                            type="date"
                            wire:model.live="selectedDate"
                            title="Click to select any date"
                            style="border: none; background: transparent; font-size: 13px; font-weight: 800; color: #dae2fd; cursor: pointer; outline: none; padding: 2px 4px; color-scheme: dark;"
                        />
                        <span style="background-color: #171f33; color: #b8c4ff; border: 1px solid #222a3d; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px; white-space: nowrap;">
                            {{ Carbon\Carbon::parse($selectedDate)->format('D, d M Y') }}
                        </span>
                        @if ($isToday)
                            <span style="background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3); font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">TODAY</span>
                        @endif
                        @if (Carbon\Carbon::parse($selectedDate)->isSunday())
                            <span style="background-color: rgba(99, 102, 241, 0.2); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.4); font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; letter-spacing: 0.05em;">WEEK-OFF</span>
                        @endif
                    </div>

                    <button
                        type="button"
                        wire:click="nextDay"
                        title="Next Day"
                        style="width: 28px; height: 28px; border: none; background: #171f33; border-radius: 6px; color: #dae2fd; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color 0.15s ease;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                @if (!$isToday)
                    <button
                        type="button"
                        wire:click="setToday"
                        style="padding: 7px 12px; font-size: 12px; font-weight: 700; background-color: #171f33; color: #b8c4ff; border: 1px solid #222a3d; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                        Return to Today
                    </button>
                @endif

                {{-- Primary "+ Mark Attendance" Button (Opens Modal with Present, Absent, WFH, Leave) --}}
                <button
                    type="button"
                    wire:click="openMarkModal()"
                    style="background-color: #1E40AF; color: #FFFFFF; font-weight: 700; font-size: 13px; border-radius: 10px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(30,64,175,0.4);">
                    <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Mark Attendance</span>
                </button>

                {{-- Export CSV --}}
                <button
                    type="button"
                    wire:click="exportAttendance"
                    style="background-color: #171f33; border: 1px solid #222a3d; color: #dae2fd; font-weight: 600; font-size: 13px; border-radius: 10px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    <svg style="width: 16px; height: 16px; color: #8e909f;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>

        {{-- 2. TOP ATTENDANCE OVERVIEW & VISUAL CHARTS (STITCH SCREEN c4b2048e058f492f82d5ff55a76b5762) --}}
        <div class="muster-top-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 22px;">
            {{-- Left Side: 4 KPI Summary Cards --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                {{-- Total Interns --}}
                <div class="stat-card" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">Total Interns</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <div style="font-size: 28px; font-weight: 800; color: #dae2fd; line-height: 1;">{{ $stats['total'] }}</div>
                        <p style="font-size: 11px; color: #8e909f; font-weight: 500; margin: 4px 0 0;">Active Enrolled Interns</p>
                    </div>
                    <div style="width: 100%; height: 5px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                        <div style="width: 100%; height: 100%; background-color: #1E40AF; border-radius: 9999px;"></div>
                    </div>
                </div>

                {{-- Present Today --}}
                <div class="stat-card" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 11px; font-weight: 700; color: #4edea3; text-transform: uppercase; letter-spacing: 0.05em;">Present Today</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(78, 222, 163, 0.3);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <div style="display: flex; align-items: baseline; gap: 6px;">
                            <span style="font-size: 28px; font-weight: 800; color: #4edea3; line-height: 1;">{{ $stats['present'] }}</span>
                            <span style="font-size: 12px; font-weight: 700; color: #4edea3;">/ {{ $stats['total'] }}</span>
                        </div>
                        <p style="font-size: 11px; color: #4edea3; font-weight: 600; margin: 4px 0 0;">
                            @if ($stats['isAnyMarked'])
                                {{ $stats['rate'] }}% attendance rate
                            @else
                                <span style="color: #8e909f;">Not yet marked for {{ Carbon\Carbon::parse($selectedDate)->format('d M') }}</span>
                            @endif
                        </p>
                    </div>
                    <div style="width: 100%; height: 5px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                        <div style="width: {{ $stats['rate'] }}%; height: 100%; background-color: #10B981; border-radius: 9999px;"></div>
                    </div>
                </div>

                {{-- Absent --}}
                <div class="stat-card" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 11px; font-weight: 700; color: #ffb4ab; text-transform: uppercase; letter-spacing: 0.05em;">Absent</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(239, 68, 68, 0.15); color: #ffb4ab; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(239, 68, 68, 0.3);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <div style="display: flex; align-items: baseline; gap: 6px;">
                            <span style="font-size: 28px; font-weight: 800; color: #ffb4ab; line-height: 1;">{{ $stats['absent'] }}</span>
                            <span style="font-size: 12px; font-weight: 700; color: #ffb4ab;">/ {{ $stats['total'] }}</span>
                        </div>
                        <p style="font-size: 11px; color: #ffb4ab; font-weight: 600; margin: 4px 0 0;">Unexcused Absences</p>
                    </div>
                    @php $absPct = $stats['total'] > 0 ? round(($stats['absent'] / $stats['total']) * 100) : 0; @endphp
                    <div style="width: 100%; height: 5px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                        <div style="width: {{ $absPct }}%; height: 100%; background-color: #EF4444; border-radius: 9999px;"></div>
                    </div>
                </div>

                {{-- WFH / Remote --}}
                <div class="stat-card" style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 11px; font-weight: 700; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.05em;">Remote / WFH</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(2, 132, 199, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(147, 197, 253, 0.3);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <div style="display: flex; align-items: baseline; gap: 6px;">
                            <span style="font-size: 28px; font-weight: 800; color: #93c5fd; line-height: 1;">{{ $stats['wfh'] }}</span>
                            <span style="font-size: 12px; font-weight: 700; color: #93c5fd;">/ {{ $stats['total'] }}</span>
                        </div>
                        <p style="font-size: 11px; color: #93c5fd; font-weight: 600; margin: 4px 0 0;">{{ $stats['leave'] }} on Approved Leave • {{ $stats['unmarked'] }} Unmarked</p>
                    </div>
                    @php $wfhPct = $stats['total'] > 0 ? round(($stats['wfh'] / $stats['total']) * 100) : 0; @endphp
                    <div style="width: 100%; height: 5px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                        <div style="width: {{ $wfhPct }}%; height: 100%; background-color: #0284C7; border-radius: 9999px;"></div>
                    </div>
                </div>
            </div>

            {{-- Right Side: Weekly Attendance Distribution Chart Card (Stitch Exact) --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div>
                        <h2 style="font-size: 14px; font-weight: 800; color: #dae2fd; margin: 0;">Weekly Attendance Distribution</h2>
                        <p style="font-size: 11px; color: #8e909f; margin: 2px 0 0;">Daily roll-call composition across current working week</p>
                    </div>
                    {{-- Mini Donut Health Score --}}
                    <div style="display: flex; align-items: center; gap: 8px; background-color: #171f33; padding: 5px 10px; border-radius: 10px; border: 1px solid #222a3d;">
                        <div style="position: relative; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <svg style="width: 32px; height: 32px; transform: rotate(-90deg);" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#222a3d" stroke-width="3.5" />
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10B981" stroke-width="3.5" stroke-dasharray="{{ $stats['rate'] }}, 100" stroke-linecap="round" />
                            </svg>
                            <span style="position: absolute; font-size: 9px; font-weight: 800; color: #4edea3;">{{ round($stats['rate']) }}%</span>
                        </div>
                        <div style="text-align: left;">
                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #4edea3;">Cohort Health</div>
                            <div style="font-size: 11px; font-weight: 700; color: #dae2fd;">
                                @if (!$stats['isAnyMarked'])
                                    Unmarked Today
                                @elseif ($stats['rate'] >= 85)
                                    Good Standing
                                @else
                                    Needs Review
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stacked Bar Chart (7 Days: Mon - Sun) --}}
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; height: 110px; align-items: end; border-bottom: 1px solid #222a3d; padding-bottom: 8px;">
                    @foreach ($chartDistribution as $bar)
                        @php
                            $isBarSelected = ($bar['isSelected'] ?? false);
                            $isSun = ($bar['isSunday'] ?? false) || ($bar['isWeekOff'] ?? false);
                            $hasAttendance = ($bar['present'] + $bar['wfh'] + $bar['leave'] + $bar['absent']) > 0;
                        @endphp
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 4px;">
                            <div style="width: 100%; max-width: 40px; height: 100%; background-color: {{ $isBarSelected ? 'rgba(30, 64, 175, 0.25)' : ($isSun ? '#0c1222' : '#171f33') }}; border-radius: 6px 6px 0 0; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end; {{ $isBarSelected ? 'outline: 2.5px solid #3b82f6; outline-offset: 1px; box-shadow: 0 2px 6px rgba(59,130,246,0.3);' : ($bar['isToday'] ? 'outline: 1.5px solid #222a3d; outline-offset: 1px;' : '') }} {{ $isSun && !$hasAttendance ? 'border: 1px dashed rgba(99, 102, 241, 0.35);' : '' }}">
                                @if ($bar['present'] > 0)
                                    <div style="background-color: #10B981; width: 100%; height: {{ $bar['pPct'] }}%;" title="{{ $bar['present'] }} Present"></div>
                                @endif
                                @if ($bar['wfh'] > 0)
                                    <div style="background-color: #0284C7; width: 100%; height: {{ $bar['wfh'] }}%;" title="{{ $bar['wfh'] }} WFH"></div>
                                @endif
                                @if ($bar['leave'] > 0)
                                    <div style="background-color: #9333EA; width: 100%; height: {{ $bar['leave'] }}%;" title="{{ $bar['leave'] }} Leave"></div>
                                @endif
                                @if ($bar['absent'] > 0)
                                    <div style="background-color: #EF4444; width: 100%; height: {{ $bar['absent'] }}%;" title="{{ $bar['absent'] }} Absent"></div>
                                @endif
                                @if ($isSun && !$hasAttendance)
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: repeating-linear-gradient(45deg, rgba(99, 102, 241, 0.08), rgba(99, 102, 241, 0.08) 4px, rgba(15, 23, 42, 0.6) 4px, rgba(15, 23, 42, 0.6) 8px);" title="Sunday - Scheduled Week-Off">
                                        <span style="font-size: 8px; font-weight: 800; color: #a5b4fc; transform: rotate(-90deg); letter-spacing: 0.05em;">OFF</span>
                                    </div>
                                @endif
                            </div>
                            <span style="font-size: 10px; font-weight: {{ $isBarSelected ? '800' : ($bar['isToday'] ? '700' : '600') }}; color: {{ $isBarSelected ? '#b8c4ff' : ($isSun ? '#a5b4fc' : ($bar['isToday'] ? '#dae2fd' : '#8e909f')) }}; white-space: nowrap;">
                                {{ $bar['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Chart Legend --}}
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; font-size: 11px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="width: 9px; height: 9px; border-radius: 2px; background-color: #10B981;"></span>
                        <span style="color: #8e909f; font-weight: 500;">Present (Office)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="width: 9px; height: 9px; border-radius: 2px; background-color: #0284C7;"></span>
                        <span style="color: #8e909f; font-weight: 500;">WFH (Remote)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="width: 9px; height: 9px; border-radius: 2px; background-color: #9333EA;"></span>
                        <span style="color: #8e909f; font-weight: 500;">Leave</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="width: 9px; height: 9px; border-radius: 2px; background-color: #EF4444;"></span>
                        <span style="color: #8e909f; font-weight: 500;">Absent</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="width: 9px; height: 9px; border-radius: 2px; background-color: #0c1222; border: 1px dashed #a5b4fc; display: inline-flex; align-items: center; justify-content: center;"></span>
                        <span style="color: #a5b4fc; font-weight: 600;">Week-Off (Sun)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. 4-WAY SEGMENTED VIEW TABS & FILTER TOOLBELT (STITCH CORE REQUIREMENT) --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 12px 18px; display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
            <div class="tab-bar-wrap" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                {{-- 4 Segmented Tab Buttons --}}
                <div style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    {{-- Tab 1: Muster Roll --}}
                    <button
                        type="button"
                        wire:click="setActiveTab('muster')"
                        class="tab-btn {{ $activeTab === 'muster' ? 'active' : '' }}"
                        style="padding: 7px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; background: transparent; color: #8e909f;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>1. All Interns (Muster Roll)</span>
                        <span style="background-color: rgba(255,255,255,0.15); font-size: 10px; font-weight: 800; padding: 1px 5px; border-radius: 9999px;">{{ $stats['total'] }}</span>
                    </button>

                    {{-- Tab 2: Batchwise --}}
                    <button
                        type="button"
                        wire:click="setActiveTab('batch')"
                        class="tab-btn {{ $activeTab === 'batch' ? 'active' : '' }}"
                        style="padding: 7px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; background: transparent; color: #8e909f;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        <span>2. Batchwise</span>
                    </button>

                    {{-- Tab 3: Groupwise / Pods --}}
                    <button
                        type="button"
                        wire:click="setActiveTab('group')"
                        class="tab-btn {{ $activeTab === 'group' ? 'active' : '' }}"
                        style="padding: 7px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; background: transparent; color: #8e909f;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <span>3. Groupwise / Pods</span>
                    </button>

                    {{-- Tab 4: Individual --}}
                    <button
                        type="button"
                        wire:click="setActiveTab('individual')"
                        class="tab-btn {{ $activeTab === 'individual' ? 'active' : '' }}"
                        style="padding: 7px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; background: transparent; color: #8e909f;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        <span>4. Individual Ledger</span>
                    </button>
                </div>

                {{-- Controls: Search, Batch Dropdown, Squad Dropdown --}}
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                    {{-- Search Input --}}
                    <div style="position: relative; width: 220px; max-width: 100%;">
                        <div style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #8e909f; pointer-events: none;">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search intern name, code..."
                            style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 10px 7px 30px; font-size: 12px; color: #dae2fd; outline: none;"
                        />
                    </div>

                    {{-- Batch Select --}}
                    <select
                        wire:model.live="selectedBatchId"
                        style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 12px; font-size: 12px; color: #dae2fd; font-weight: 600; cursor: pointer; outline: none;">
                        <option value="">All Batches</option>
                        @foreach ($batches as $b)
                            <option value="{{ $b->id }}">{{ $b->batch_name }}</option>
                        @endforeach
                    </select>

                    {{-- Team / Squad Select --}}
                    <select
                        wire:model.live="selectedTeamId"
                        style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 12px; font-size: 12px; color: #dae2fd; font-weight: 600; cursor: pointer; outline: none;">
                        <option value="">All Squads</option>
                        @foreach ($teams as $t)
                            <option value="{{ $t->id }}">{{ $t->team_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($activeTab === 'muster')
                {{-- Status Pills Row & Roll-Call All in Muster Tab --}}
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; border-top: 1px solid #222a3d; padding-top: 8px;">
                    <div style="display: flex; align-items: center; gap: 4px; overflow-x: auto;">
                        @foreach (['all' => 'All Interns', 'present' => 'Present', 'wfh' => 'WFH', 'absent' => 'Absent', 'leave' => 'Leave', 'unmarked' => 'Unmarked'] as $val => $label)
                            <button
                                type="button"
                                wire:click="$set('statusFilter', '{{ $val }}')"
                                style="padding: 4px 10px; font-size: 11px; border-radius: 6px; border: none; cursor: pointer; {{ $statusFilter === $val ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: #171f33; color: #8e909f; border: 1px solid #222a3d; font-weight: 500;' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Roll-Call All Quick Trigger Bar --}}
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f;">Roll-Call for {{ Carbon\Carbon::parse($selectedDate)->format('d M') }}:</span>
                        <button
                            type="button"
                            wire:click="openMarkModal()"
                            style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #1E40AF; color: #FFFFFF; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span>Roll-Call All Active</span>
                        </button>
                        <button
                            type="button"
                            wire:click="markAllPresent"
                            style="padding: 4px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3); cursor: pointer;">
                            All Present
                        </button>
                        <button
                            type="button"
                            wire:click="markAllWfh"
                            style="padding: 4px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: rgba(2, 132, 199, 0.15); color: #93c5fd; border: 1px solid rgba(147, 197, 253, 0.3); cursor: pointer;">
                            All WFH
                        </button>
                        <button
                            type="button"
                            wire:click="markAllAbsent"
                            style="padding: 4px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: rgba(239, 68, 68, 0.15); color: #ffb4ab; border: 1px solid rgba(239, 68, 68, 0.3); cursor: pointer;">
                            All Absent
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 1: ALL INTERNS (CLASSIC MUSTER ROLL REGISTER - STITCH c4b2048e058f492f) --}}
        {{-- ========================================================================= --}}
        @if ($activeTab === 'muster')
            <div style="background-color: #131b2e; border-radius: 14px; border: 1px solid #222a3d; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                {{-- Muster Legend Strip --}}
                <div style="background-color: #171f33; border-bottom: 1px solid #222a3d; padding: 10px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; font-size: 12px;">
                    <div style="display: flex; align-items: center; gap: 6px; color: #8e909f;">
                        <svg style="width: 16px; height: 16px; color: #b8c4ff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Muster roll weekly register. Use the status buttons on each row to log <strong>Present [P]</strong>, <strong>Absent [A]</strong>, <strong>WFH [W]</strong>, or <strong>Leave [L]</strong>.</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 11px; font-weight: 700;">
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 18px; height: 18px; border-radius: 4px; background-color: #10B981; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 10px;">P</span> Present
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 18px; height: 18px; border-radius: 4px; background-color: #0284C7; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 10px;">W</span> WFH
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 18px; height: 18px; border-radius: 4px; background-color: #EF4444; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 10px;">A</span> Absent
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 18px; height: 18px; border-radius: 4px; background-color: #9333EA; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 10px;">L</span> Leave
                        </span>
                    </div>
                </div>

                {{-- Muster Roll Table --}}
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                        <thead>
                            <tr style="background-color: #171f33; border-bottom: 1px solid #222a3d; font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">
                                <th style="padding: 12px 18px; min-width: 220px;">Intern Profile</th>
                                <th style="padding: 12px 14px;">Batch & Squad</th>
                                @foreach ($weekDays as $wd)
                                    @php
                                        $isWdSelected = $wd['isSelected'] ?? false;
                                        $isSunOff = ($wd['isSunday'] ?? false) || ($wd['isWeekOff'] ?? false);
                                    @endphp
                                    <th style="padding: 10px 8px; text-align: center; width: 64px; {{ $isWdSelected ? 'background-color: rgba(30, 64, 175, 0.35); color: #b8c4ff; border-bottom: 3px solid #3b82f6;' : ($isSunOff ? 'background-color: rgba(99, 102, 241, 0.08); color: #a5b4fc;' : ($wd['isToday'] ? 'background-color: rgba(255, 255, 255, 0.05); color: #dae2fd;' : '')) }}">
                                        <div style="font-weight: {{ $isWdSelected ? '900' : '700' }};">{{ $wd['dayName'] }}</div>
                                        <div style="font-size: 10px; font-weight: {{ $isWdSelected ? '900' : ($wd['isToday'] ? '800' : '500') }};">
                                            @if ($isSunOff)
                                                <span style="display: inline-block; font-size: 9px; font-weight: 800; color: #a5b4fc; background-color: rgba(99, 102, 241, 0.18); padding: 1px 4px; border-radius: 3px; border: 1px solid rgba(99, 102, 241, 0.3);">OFF</span>
                                            @else
                                                {{ $wd['dayNum'] }}{{ $wd['isToday'] ? ' (Today)' : '' }}
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                                <th style="padding: 12px 10px; text-align: center;">P / Tot</th>
                                <th style="padding: 12px 10px; text-align: center;">Absent</th>
                                <th style="padding: 12px 14px; text-align: center;">Rate</th>
                                <th style="padding: 12px 18px; text-align: right; min-width: 200px;">Actions for {{ Carbon\Carbon::parse($selectedDate)->format('d M') }}</th>
                            </tr>
                        </thead>
                        <tbody style="border-top: 1px solid #222a3d;">
                            @forelse ($interns as $intern)
                                @php
                                    $displayName = $intern->name ?: ($intern->offerletter?->name ?? 'Unknown');
                                    $internCode = $intern->intern_code ?: ('INT-' . str_pad($intern->id, 3, '0', STR_PAD_LEFT));
                                    $role = $intern->internship_role ?: ($intern->offerletter?->internship_role ?? 'Intern');

                                    $rawBatch = $intern->batch?->batch_name ?? $intern->offerletter?->batch_name;
                                    $batchDisplay = filled($rawBatch) ? $rawBatch : 'Batch not assigned';

                                    $rawProject = $intern->project_name ?? $intern->team?->team_name;
                                    $projectDisplay = filled($rawProject) ? $rawProject : 'Project not assigned';

                                    $allAtt = $intern->attendances->whereIn('status', ['present', 'wfh', 'leave', 'absent']);
                                    $totMarked = $allAtt->count();
                                    $presentCount = $allAtt->where('status', 'present')->count();
                                    $wfhCount = $allAtt->where('status', 'wfh')->count();
                                    $absentCount = $allAtt->where('status', 'absent')->count();
                                    $rate = $totMarked > 0 ? round((($presentCount + $wfhCount) / $totMarked) * 100, 1) : 0.0;

                                    $words = explode(' ', trim($displayName));
                                    $initials = strtoupper(substr($words[0] ?? 'I', 0, 1) . substr($words[1] ?? '', 0, 1));

                                    $todayAtt = $intern->attendances->firstWhere('date', $selectedDate);
                                    $currentStatus = $todayAtt?->status ?? 'unmarked';
                                @endphp
                                <tr class="intern-muster-row" style="border-bottom: 1px solid #222a3d; transition: background-color 0.15s ease;">
                                    {{-- Intern Profile --}}
                                    <td style="padding: 12px 18px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background-color: #171f33; color: #b8c4ff; border: 1px solid #222a3d; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                                @if ($intern->intern_image)
                                                    <img src="{{ asset('storage/' . $intern->intern_image) }}" alt="{{ $displayName }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                                @else
                                                    {{ $initials }}
                                                @endif
                                            </div>
                                            <div style="min-width: 0;">
                                                <div style="font-weight: 700; color: #dae2fd; display: flex; align-items: center; gap: 4px;">
                                                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $displayName }}</span>
                                                    <svg style="width: 13px; height: 13px; color: #4edea3; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                </div>
                                                <div style="font-size: 11px; color: #8e909f;">
                                                    <span style="font-weight: 600; color: #b8c4ff;">{{ $internCode }}</span> • {{ $role }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Batch & Squad --}}
                                    <td style="padding: 12px 14px;">
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <span style="display: inline-flex; align-items: center; font-size: 11px; font-weight: 600; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); padding: 2px 7px; border-radius: 6px; width: fit-content; border: 1px solid rgba(59, 130, 246, 0.3);">
                                                {{ $batchDisplay }}
                                            </span>
                                            <span style="display: inline-flex; align-items: center; font-size: 11px; font-weight: 500; color: #c4c5d5; background-color: #171f33; padding: 2px 7px; border-radius: 6px; width: fit-content; border: 1px solid #222a3d;">
                                                {{ $projectDisplay }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- 7 Days Columns (Mon - Sun) --}}
                                    @foreach ($weekDays as $wd)
                                        @php
                                            $dayAtt = $intern->attendances->firstWhere('date', $wd['date']);
                                            $st = $dayAtt?->status ?? 'unmarked';
                                            $isWdSelected = $wd['isSelected'] ?? false;
                                            $isSunOff = ($wd['isSunday'] ?? false) || ($wd['isWeekOff'] ?? false);
                                        @endphp
                                        <td style="padding: 10px 4px; text-align: center; {{ $isWdSelected ? 'background-color: rgba(30, 64, 175, 0.2); outline: 1px solid rgba(59, 130, 246, 0.4); outline-offset: -1px;' : ($isSunOff ? 'background-color: rgba(99, 102, 241, 0.04);' : ($wd['isToday'] ? 'background-color: rgba(255, 255, 255, 0.03);' : '')) }}">
                                            @if ($isSunOff && $st === 'unmarked')
                                                <span
                                                    title="Sunday - Scheduled Week-Off"
                                                    style="width: 26px; height: 26px; border-radius: 6px; background-color: #0f172a; color: #a5b4fc; font-weight: 800; font-size: 9px; border: 1px solid rgba(99, 102, 241, 0.3); display: inline-flex; align-items: center; justify-content: center; cursor: default;">
                                                    OFF
                                                </span>
                                            @elseif ($st === 'present')
                                                <button
                                                    type="button"
                                                    wire:click="openMarkModal({{ $intern->id }})"
                                                    title="Present (Click to edit log)"
                                                    style="width: 26px; height: 26px; border-radius: 6px; background-color: #10B981; color: #FFFFFF; font-weight: 800; font-size: 11px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                                                    P
                                                </button>
                                            @elseif ($st === 'wfh')
                                                <button
                                                    type="button"
                                                    wire:click="openMarkModal({{ $intern->id }})"
                                                    title="WFH (Click to edit log)"
                                                    style="width: 26px; height: 26px; border-radius: 6px; background-color: #0284C7; color: #FFFFFF; font-weight: 800; font-size: 11px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                                                    W
                                                </button>
                                            @elseif ($st === 'absent')
                                                <button
                                                    type="button"
                                                    wire:click="openMarkModal({{ $intern->id }})"
                                                    title="Absent (Click to edit log)"
                                                    style="width: 26px; height: 26px; border-radius: 6px; background-color: #EF4444; color: #FFFFFF; font-weight: 800; font-size: 11px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                                                    A
                                                </button>
                                            @elseif ($st === 'leave')
                                                <button
                                                    type="button"
                                                    wire:click="openMarkModal({{ $intern->id }})"
                                                    title="Leave (Click to edit log)"
                                                    style="width: 26px; height: 26px; border-radius: 6px; background-color: #9333EA; color: #FFFFFF; font-weight: 800; font-size: 11px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                                                    L
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="openMarkModal({{ $intern->id }})"
                                                    title="Unmarked (Click to mark)"
                                                    style="width: 26px; height: 26px; border-radius: 6px; background-color: #171f33; color: #8e909f; font-weight: 700; font-size: 11px; border: 1px dashed #2d3449; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                                                    -
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach

                                    {{-- P / Tot --}}
                                    <td style="padding: 12px 10px; text-align: center; font-weight: 700; color: #4edea3;">
                                        {{ $presentCount }}/{{ $totMarked }}
                                    </td>

                                    {{-- Absent --}}
                                    <td style="padding: 12px 10px; text-align: center; font-weight: 600; color: {{ $absentCount > 0 ? '#ffb4ab' : '#8e909f' }};">
                                        {{ $absentCount }}
                                    </td>

                                    {{-- Rate --}}
                                    <td style="padding: 12px 14px; text-align: center;">
                                        @if ($totMarked === 0)
                                            <span style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; background-color: #171f33; color: #8e909f; border: 1px solid #222a3d;">
                                                Unmarked
                                            </span>
                                        @else
                                            <span style="font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 9999px; {{ $rate >= 85 ? 'background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3);' : ($rate >= 70 ? 'background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);' : 'background-color: rgba(239, 68, 68, 0.15); color: #ffb4ab; border: 1px solid rgba(239, 68, 68, 0.3);') }}">
                                                {{ $rate }}%
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions: Quick Status Buttons for Selected Date & Modal / Calendar --}}
                                    <td style="padding: 12px 18px; text-align: right;">
                                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                                            {{-- Present Button --}}
                                            <button
                                                type="button"
                                                wire:click="setAttendanceStatus({{ $intern->id }}, 'present')"
                                                title="Mark Present"
                                                style="width: 24px; height: 24px; border-radius: 5px; font-size: 10px; font-weight: 800; cursor: pointer; border: 1px solid #222a3d; {{ $currentStatus === 'present' ? 'background-color: #10B981; color: #FFF;' : 'background-color: #171f33; color: #4edea3;' }}">
                                                P
                                            </button>

                                            {{-- Absent Button --}}
                                            <button
                                                type="button"
                                                wire:click="setAttendanceStatus({{ $intern->id }}, 'absent')"
                                                title="Mark Absent"
                                                style="width: 24px; height: 24px; border-radius: 5px; font-size: 10px; font-weight: 800; cursor: pointer; border: 1px solid #222a3d; {{ $currentStatus === 'absent' ? 'background-color: #EF4444; color: #FFF;' : 'background-color: #171f33; color: #ffb4ab;' }}">
                                                A
                                            </button>

                                            {{-- WFH Button --}}
                                            <button
                                                type="button"
                                                wire:click="setAttendanceStatus({{ $intern->id }}, 'wfh')"
                                                title="Mark WFH"
                                                style="width: 24px; height: 24px; border-radius: 5px; font-size: 10px; font-weight: 800; cursor: pointer; border: 1px solid #222a3d; {{ $currentStatus === 'wfh' ? 'background-color: #0284C7; color: #FFF;' : 'background-color: #171f33; color: #93c5fd;' }}">
                                                W
                                            </button>

                                            {{-- Leave Button --}}
                                            <button
                                                type="button"
                                                wire:click="setAttendanceStatus({{ $intern->id }}, 'leave')"
                                                title="Mark Leave"
                                                style="width: 24px; height: 24px; border-radius: 5px; font-size: 10px; font-weight: 800; cursor: pointer; border: 1px solid #222a3d; {{ $currentStatus === 'leave' ? 'background-color: #9333EA; color: #FFF;' : 'background-color: #171f33; color: #d8b4fe;' }}">
                                                L
                                            </button>

                                            {{-- Mark Modal Trigger --}}
                                            <button
                                                type="button"
                                                wire:click="openMarkModal({{ $intern->id }})"
                                                title="Open Detailed Mark Popup"
                                                style="padding: 4px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: #171f33; color: #dae2fd; border: 1px solid #222a3d; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
                                                <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                <span>Mark</span>
                                            </button>

                                            {{-- Calendar Button --}}
                                            <button
                                                type="button"
                                                wire:click="openInternCalendar({{ $intern->id }})"
                                                title="View Monthly Calendar"
                                                style="padding: 4px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; background-color: rgba(30, 64, 175, 0.2); color: #b8c4ff; border: 1px solid rgba(59, 130, 246, 0.3); cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
                                                <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                <span>Calendar</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" style="padding: 36px 18px; text-align: center; color: #8e909f;">
                                        <p style="font-size: 14px; font-weight: 600; margin: 0 0 4px; color: #dae2fd;">No intern records found</p>
                                        <p style="font-size: 12px; margin: 0;">Try adjusting your search terms or filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Controls with Filament-style Per-Page Selector --}}
                <div style="padding: 14px 18px; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background-color: #131b2e;">
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <div style="color: #8e909f; font-size: 12px; font-weight: 500;">
                            Showing <span style="font-weight: 700; color: #dae2fd;">{{ $interns->count() }}</span> of <span style="font-weight: 700; color: #dae2fd;">{{ $interns->total() }}</span> interns
                        </div>

                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 12px; color: #8e909f; font-weight: 600;">Per page:</span>
                            <select
                                wire:model.live="perPage"
                                style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 6px; padding: 3px 8px; font-size: 12px; font-weight: 700; color: #dae2fd; cursor: pointer; outline: none;">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 4px;">
                        @if ($interns->onFirstPage())
                            <span style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; color: #475569; cursor: not-allowed;">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </span>
                        @else
                            <button
                                type="button"
                                wire:click="previousPage"
                                style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; color: #8e909f; cursor: pointer; border: none; background: transparent; border-radius: 6px;">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </button>
                        @endif

                        @foreach (range(1, $interns->lastPage()) as $page)
                            @if ($page == $interns->currentPage())
                                <span style="background-color: #1E40AF; color: #FFFFFF; font-weight: 700; border-radius: 6px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 12px; box-shadow: 0 2px 6px rgba(30,64,175,0.4);">
                                    {{ $page }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="gotoPage({{ $page }})"
                                    style="color: #c4c5d5; font-weight: 600; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; background: transparent; font-size: 12px; border-radius: 6px;">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach

                        @if ($interns->hasMorePages())
                            <button
                                type="button"
                                wire:click="nextPage"
                                style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; color: #8e909f; cursor: pointer; border: none; background: transparent; border-radius: 6px;">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        @else
                            <span style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; color: #475569; cursor: not-allowed;">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- TAB 2: BATCHWISE OVERVIEW (STITCH SCREEN c26ab599acf54ca69888217ba25c2e65) --}}
        {{-- ========================================================================= --}}
        @if ($activeTab === 'batch')
            <div style="display: flex; flex-direction: column; gap: 20px;">
                {{-- 4 Batch Metric Cards --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Active Batches</span>
                        <div style="font-size: 26px; font-weight: 800; color: #dae2fd; margin-top: 6px;">{{ count($batchesOverview) }}</div>
                        <p style="font-size: 11px; color: #8e909f; margin: 4px 0 0;">Synchronized shifts</p>
                    </div>
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #4edea3; text-transform: uppercase;">Highest Batch Attendance</span>
                        @php $maxRate = collect($batchesOverview)->max('rate') ?? 0; @endphp
                        <div style="font-size: 26px; font-weight: 800; color: #4edea3; margin-top: 6px;">{{ $maxRate }}%</div>
                        <p style="font-size: 11px; color: #4edea3; font-weight: 600; margin: 4px 0 0;">Top performing batch</p>
                    </div>
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #ffb4ab; text-transform: uppercase;">Attention Needed</span>
                        <div style="font-size: 26px; font-weight: 800; color: #ffb4ab; margin-top: 6px;">{{ $stats['absent'] }} Absent</div>
                        <p style="font-size: 11px; color: #ffb4ab; font-weight: 600; margin: 4px 0 0;">Unexcused across all batches</p>
                    </div>
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #b8c4ff; text-transform: uppercase;">Cohort Average</span>
                        <div style="font-size: 26px; font-weight: 800; color: #b8c4ff; margin-top: 6px;">{{ $stats['rate'] }}%</div>
                        <p style="font-size: 11px; color: #8e909f; margin: 4px 0 0;">{{ $stats['present'] }} Onsite • {{ $stats['wfh'] }} WFH</p>
                    </div>
                </div>

                {{-- Detailed Batch Cards Showcase --}}
                @forelse ($batchesOverview as $bData)
                    @php
                        $b = $bData['batch'];
                        $isUnassigned = $bData['is_unassigned'] ?? false;
                        $bInitials = $isUnassigned ? '⚠️' : strtoupper(substr($b->batch_name, 0, 3));
                        $isHigh = $bData['rate'] >= 85;
                    @endphp
                    <div style="background-color: {{ $isUnassigned ? 'rgba(245, 158, 11, 0.05)' : '#131b2e' }}; border-radius: 14px; border: {{ $isUnassigned ? '1.5px dashed rgba(245, 158, 11, 0.35)' : '1px solid #222a3d' }}; box-shadow: 0 4px 20px rgba(0,0,0,0.25); position: relative; overflow: hidden; padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                        {{-- Left Accent Strip --}}
                        <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background-color: {{ $isUnassigned ? '#f59e0b' : ($isHigh ? '#10B981' : '#1E40AF') }};"></div>

                        {{-- Batch Card Header --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: #171f33; color: {{ $isUnassigned ? '#fbbf24' : '#b8c4ff' }}; border: 1px solid #222a3d; font-weight: 800; font-size: 15px; display: flex; align-items: center; justify-content: center;">
                                    {{ $bInitials }}
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h3 style="font-size: 17px; font-weight: 800; color: #dae2fd; margin: 0;">{{ $b->batch_name }}</h3>
                                        <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; {{ $isUnassigned ? 'background-color: rgba(239, 68, 68, 0.15); color: #ffb4ab; border: 1px solid rgba(239, 68, 68, 0.3);' : ($isHigh ? 'background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3);' : 'background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);') }}">
                                            {{ $isUnassigned ? '⚠️ Unassigned Pool' : ($isHigh ? 'Compliant' : 'Active Batch') }}
                                        </span>
                                    </div>
                                    <div style="font-size: 12px; color: #8e909f; margin-top: 3px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        <span>Timing: <strong style="color: #dae2fd;">{{ $b->batch_timing ?: 'Standard Shift' }}</strong></span>
                                        <span>•</span>
                                        <span>Capacity: <strong style="color: #dae2fd;">{{ $bData['total'] }} / {{ $b->no_of_interns ?: $bData['total'] }} Interns</strong></span>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="text-align: right;">
                                    <div style="font-size: 22px; font-weight: 800; color: {{ $isHigh ? '#4edea3' : '#b8c4ff' }}; line-height: 1;">{{ $bData['rate'] }}%</div>
                                    <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Batch Rate</span>
                                </div>
                                <button
                                    type="button"
                                    wire:click="markBatchPresent({{ $b->id }})"
                                    style="background-color: #1E40AF; color: #FFFFFF; font-weight: 700; font-size: 12px; border-radius: 8px; padding: 8px 14px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(30,64,175,0.4);">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                    <span>Batch Roll-Call All</span>
                                </button>
                            </div>
                        </div>

                        {{-- 3-Stat Ribbon for Batch --}}
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; background-color: #171f33; border: 1px solid #222a3d; padding: 10px 14px; border-radius: 10px;">
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Present Today</span>
                                <span style="font-size: 14px; font-weight: 700; color: #4edea3; margin-top: 2px;">{{ $bData['presentToday'] }} Interns</span>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Remote / WFH</span>
                                <span style="font-size: 14px; font-weight: 700; color: #93c5fd; margin-top: 2px;">{{ $bData['wfhToday'] }} Approved</span>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Absent / Leave</span>
                                <span style="font-size: 14px; font-weight: 700; color: {{ $bData['absentToday'] > 0 ? '#ffb4ab' : '#8e909f' }}; margin-top: 2px;">
                                    {{ $bData['absentToday'] }} Absent • {{ $bData['leaveToday'] }} Leave
                                </span>
                            </div>
                        </div>

                        {{-- Batch Interns Table --}}
                        <div style="overflow-x: auto; border: 1px solid #222a3d; border-radius: 10px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead>
                                    <tr style="background-color: #171f33; border-bottom: 1px solid #222a3d; font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase;">
                                        <th style="padding: 9px 14px;">Intern Name & ID</th>
                                        <th style="padding: 9px 12px;">Assigned Project</th>
                                        @foreach ($weekDays as $wd)
                                            @php
                                                $isWdSelected = $wd['isSelected'] ?? false;
                                                $isSunOff = ($wd['isSunday'] ?? false) || ($wd['isWeekOff'] ?? false);
                                            @endphp
                                            <th style="padding: 8px 4px; text-align: center; width: 48px; {{ $isWdSelected ? 'background-color: rgba(30, 64, 175, 0.35); color: #b8c4ff; border-bottom: 2px solid #3b82f6;' : ($isSunOff ? 'background-color: rgba(99, 102, 241, 0.08); color: #a5b4fc;' : ($wd['isToday'] ? 'background-color: rgba(255, 255, 255, 0.05); color: #dae2fd;' : '')) }}">
                                                <div>{{ $wd['dayName'] }}</div>
                                                <div style="font-size: 9px; {{ $isSunOff ? 'color: #a5b4fc; font-weight: 800;' : '' }}">{{ $isSunOff ? 'OFF' : $wd['dayNum'] }}</div>
                                            </th>
                                        @endforeach
                                        <th style="padding: 9px 10px; text-align: center;">Weekly %</th>
                                        <th style="padding: 9px 14px; text-align: right;">Action for {{ Carbon\Carbon::parse($selectedDate)->format('d M') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bData['members'] as $m)
                                        @php
                                            $intern = $m['intern'];
                                            $name = $intern->name ?: ($intern->offerletter?->name ?? 'Intern');
                                            $code = $intern->intern_code ?: ('INT-' . str_pad($intern->id, 3, '0', STR_PAD_LEFT));
                                            $proj = $intern->project_name ?? $intern->team?->team_name ?? 'Project not assigned';
                                        @endphp
                                        <tr style="border-bottom: 1px solid #222a3d;">
                                            <td style="padding: 10px 14px;">
                                                <div style="font-weight: 700; color: #dae2fd;">{{ $name }}</div>
                                                <div style="font-size: 10px; color: #8e909f;">{{ $code }}</div>
                                            </td>
                                            <td style="padding: 10px 12px; color: #c4c5d5;">
                                                {{ $proj }}
                                            </td>
                                            @foreach ($weekDays as $wd)
                                                @php
                                                    $daySt = $m['weekStatus'][$wd['date']] ?? 'unmarked';
                                                    $isWdSelected = $wd['isSelected'] ?? false;
                                                    $isSunOff = ($wd['isSunday'] ?? false) || ($wd['isWeekOff'] ?? false);
                                                @endphp
                                                <td style="padding: 8px 4px; text-align: center; {{ $isWdSelected ? 'background-color: rgba(30, 64, 175, 0.2);' : ($isSunOff ? 'background-color: rgba(99, 102, 241, 0.04);' : '') }}">
                                                    @if ($isSunOff && $daySt === 'unmarked')
                                                        <span title="Sunday - Scheduled Week-Off" style="display: inline-block; width: 22px; height: 22px; line-height: 20px; border-radius: 4px; background-color: #0f172a; border: 1px solid rgba(99, 102, 241, 0.3); color: #a5b4fc; font-size: 8px; font-weight: 800;">OFF</span>
                                                    @elseif ($daySt === 'present')
                                                        <span style="display: inline-block; width: 22px; height: 22px; line-height: 22px; border-radius: 4px; background-color: #10B981; color: #FFF; font-weight: 800; font-size: 10px;">P</span>
                                                    @elseif ($daySt === 'wfh')
                                                        <span style="display: inline-block; width: 22px; height: 22px; line-height: 22px; border-radius: 4px; background-color: #0284C7; color: #FFF; font-weight: 800; font-size: 10px;">W</span>
                                                    @elseif ($daySt === 'absent')
                                                        <span style="display: inline-block; width: 22px; height: 22px; line-height: 22px; border-radius: 4px; background-color: #EF4444; color: #FFF; font-weight: 800; font-size: 10px;">A</span>
                                                    @elseif ($daySt === 'leave')
                                                        <span style="display: inline-block; width: 22px; height: 22px; line-height: 22px; border-radius: 4px; background-color: #9333EA; color: #FFF; font-weight: 800; font-size: 10px;">L</span>
                                                    @else
                                                        <span style="display: inline-block; width: 22px; height: 22px; line-height: 22px; border-radius: 4px; background-color: #171f33; border: 1px dashed #2d3449; color: #8e909f; font-size: 10px;">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td style="padding: 10px 10px; text-align: center; font-weight: 800; color: #4edea3;">
                                                {{ $m['rate'] }}%
                                            </td>
                                            <td style="padding: 10px 14px; text-align: right;">
                                                <div style="display: inline-flex; align-items: center; gap: 3px;">
                                                    <button
                                                        type="button"
                                                        wire:click="setAttendanceStatus({{ $intern->id }}, 'present')"
                                                        title="Mark Present"
                                                        style="width: 22px; height: 22px; border-radius: 4px; font-size: 9px; font-weight: 800; cursor: pointer; border: 1px solid #222a3d; {{ $m['statusToday'] === 'present' ? 'background-color: #10B981; color: #FFF;' : 'background-color: #171f33; color: #4edea3;' }}">
                                                        P
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="setAttendanceStatus({{ $intern->id }}, 'absent')"
                                                        title="Mark Absent"
                                                        style="width: 22px; height: 22px; border-radius: 4px; font-size: 9px; font-weight: 800; cursor: pointer; border: 1px solid #222a3d; {{ $m['statusToday'] === 'absent' ? 'background-color: #EF4444; color: #FFF;' : 'background-color: #171f33; color: #ffb4ab;' }}">
                                                        A
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="setAttendanceStatus({{ $intern->id }}, 'wfh')"
                                                        title="Mark WFH"
                                                        style="width: 22px; height: 22px; border-radius: 4px; font-size: 9px; font-weight: 800; cursor: pointer; border: 1px solid #222a3d; {{ $m['statusToday'] === 'wfh' ? 'background-color: #0284C7; color: #FFF;' : 'background-color: #171f33; color: #93c5fd;' }}">
                                                        W
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="setAttendanceStatus({{ $intern->id }}, 'leave')"
                                                        title="Mark Leave"
                                                        style="width: 22px; height: 22px; border-radius: 4px; font-size: 9px; font-weight: 800; cursor: pointer; border: 1px solid #222a3d; {{ $m['statusToday'] === 'leave' ? 'background-color: #9333EA; color: #FFF;' : 'background-color: #171f33; color: #d8b4fe;' }}">
                                                        L
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="openMarkModal({{ $intern->id }})"
                                                        title="Detailed Log"
                                                        style="padding: 2px 6px; font-size: 10px; font-weight: 700; border-radius: 4px; background-color: #171f33; color: #dae2fd; border: 1px solid #222a3d; cursor: pointer;">
                                                        Mark
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" style="padding: 18px; text-align: center; color: #8e909f;">No interns assigned to this batch.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 36px; text-align: center;">
                        <p style="font-weight: 700; color: #dae2fd; margin: 0;">No active batches found</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- TAB 3: GROUPWISE & PODS VIEW (STITCH SCREEN 235605e0d6e043da8571c89497ef) --}}
        {{-- ========================================================================= --}}
        @if ($activeTab === 'group')
            <div style="display: flex; flex-direction: column; gap: 20px;">
                {{-- 4 Squad Metric Cards --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Total Squads</span>
                        <div style="font-size: 26px; font-weight: 800; color: #dae2fd; margin-top: 6px;">{{ count($groupsOverview) }}</div>
                        <p style="font-size: 11px; color: #8e909f; margin: 4px 0 0;">Active Project Squads</p>
                    </div>
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #4edea3; text-transform: uppercase;">Top Squad Rate</span>
                        @php $maxSquadRate = collect($groupsOverview)->max('rate') ?? 0; @endphp
                        <div style="font-size: 26px; font-weight: 800; color: #4edea3; margin-top: 6px;">{{ $maxSquadRate }}%</div>
                        <p style="font-size: 11px; color: #4edea3; font-weight: 600; margin: 4px 0 0;">Leading Squad</p>
                    </div>
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #93c5fd; text-transform: uppercase;">Remote Protocol</span>
                        <div style="font-size: 26px; font-weight: 800; color: #93c5fd; margin-top: 6px;">{{ $stats['wfh'] }} Interns</div>
                        <p style="font-size: 11px; color: #93c5fd; font-weight: 600; margin: 4px 0 0;">Active WFH today</p>
                    </div>
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #b8c4ff; text-transform: uppercase;">Squad Avg Standing</span>
                        <div style="font-size: 26px; font-weight: 800; color: #b8c4ff; margin-top: 6px;">{{ $stats['rate'] }}%</div>
                        <p style="font-size: 11px; color: #8e909f; margin: 4px 0 0;">{{ $stats['present'] }} active onsite</p>
                    </div>
                </div>

                {{-- Pods Responsive Grid (2 columns on wide screens) --}}
                <div class="pod-two-col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                    @forelse ($groupsOverview as $gData)
                        @php
                            $team = $gData['team'];
                            $isUnassigned = $gData['is_unassigned'] ?? false;
                            $squadNum = $isUnassigned ? '⚠️' : str_pad($team->id, 2, '0', STR_PAD_LEFT);
                            $mentor = $team->mentor_name ?: 'Faculty Mentor Assigned';
                            $title = $team->mentor_title ?: ($team->track ?: 'Engineering');
                        @endphp
                        <div style="background-color: {{ $isUnassigned ? 'rgba(245, 158, 11, 0.05)' : '#131b2e' }}; border-radius: 14px; border: {{ $isUnassigned ? '1.5px dashed rgba(245, 158, 11, 0.35)' : '1px solid #222a3d' }}; box-shadow: 0 4px 20px rgba(0,0,0,0.25); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px;">
                            {{-- Pod Header --}}
                            <div>
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 8px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 42px; height: 42px; border-radius: 10px; background-color: #171f33; color: {{ $isUnassigned ? '#fbbf24' : '#b8c4ff' }}; border: 1px solid #222a3d; font-weight: 800; font-size: 16px; display: flex; align-items: center; justify-content: center;">
                                            {{ $squadNum }}
                                        </div>
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <h3 style="font-size: 16px; font-weight: 800; color: #dae2fd; margin: 0;">{{ $team->team_name }}</h3>
                                                <span style="font-size: 10px; font-weight: 700; background-color: {{ $isUnassigned ? 'rgba(239, 68, 68, 0.15)' : 'rgba(16, 185, 129, 0.15)' }}; color: {{ $isUnassigned ? '#ffb4ab' : '#4edea3' }}; border: 1px solid {{ $isUnassigned ? 'rgba(239, 68, 68, 0.3)' : 'rgba(78, 222, 163, 0.3)' }}; padding: 2px 6px; border-radius: 9999px;">
                                                    {{ $isUnassigned ? '⚠️ Unassigned Pool' : "Squad #{$squadNum}" }}
                                                </span>
                                            </div>
                                            <p style="font-size: 11px; color: #8e909f; margin: 2px 0 0;">
                                                Track: <strong style="color: #b8c4ff;">{{ $title }}</strong> • Mentor: <strong style="color: #dae2fd;">{{ $mentor }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 20px; font-weight: 800; color: {{ $isUnassigned ? '#fbbf24' : '#4edea3' }}; line-height: 1;">{{ $gData['rate'] }}%</div>
                                        <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Squad Rate</span>
                                    </div>
                                </div>

                                {{-- Week Muster Snapshot Spark-bar --}}
                                <div style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <span style="font-size: 11px; font-weight: 700; color: #8e909f;">Roll-Call Snapshot:</span>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        @foreach ($weekDays as $wd)
                                            @php
                                                $isWdSelected = $wd['isSelected'] ?? false;
                                                $isSunOff = ($wd['isSunday'] ?? false) || ($wd['isWeekOff'] ?? false);
                                            @endphp
                                            <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; {{ $isWdSelected ? 'background-color: #1E40AF; color: #FFF; box-shadow: 0 1px 3px rgba(30,64,175,0.4);' : ($isSunOff ? 'background-color: #0b1326; color: #a5b4fc; border: 1px dashed rgba(99, 102, 241, 0.4);' : ($wd['isToday'] ? 'background-color: #2563EB; color: #FFF;' : 'background-color: #131b2e; color: #8e909f; border: 1px solid #222a3d;')) }}">
                                                {{ $wd['dayName'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="markTeamPresent({{ $team->id }})"
                                        style="font-size: 11px; font-weight: 700; background-color: #10B981; color: #FFFFFF; border: none; border-radius: 6px; padding: 4px 10px; cursor: pointer;">
                                        Pod Roll-Call
                                    </button>
                                </div>

                                {{-- Squad Members Table --}}
                                <div style="overflow-x: auto; border: 1px solid #222a3d; border-radius: 8px;">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                                        <thead>
                                            <tr style="background-color: #171f33; border-bottom: 1px solid #222a3d; font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase;">
                                                <th style="padding: 8px 12px;">Member</th>
                                                <th style="padding: 8px 8px;">Role</th>
                                                @foreach ($weekDays as $wd)
                                                    @php
                                                        $isWdSelected = $wd['isSelected'] ?? false;
                                                        $isSunOff = ($wd['isSunday'] ?? false) || ($wd['isWeekOff'] ?? false);
                                                    @endphp
                                                    <th style="padding: 6px 2px; text-align: center; width: 28px; {{ $isWdSelected ? 'background-color: rgba(30, 64, 175, 0.35); color: #b8c4ff;' : ($isSunOff ? 'color: #a5b4fc;' : '') }}">{{ substr($wd['dayName'], 0, 1) }}</th>
                                                @endforeach
                                                <th style="padding: 8px 12px; text-align: right;">Action for {{ Carbon\Carbon::parse($selectedDate)->format('d M') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($gData['members'] as $m)
                                                @php
                                                    $intern = $m['intern'];
                                                    $name = $intern->name ?: ($intern->offerletter?->name ?? 'Intern');
                                                    $role = $intern->internship_role ?: 'Developer';
                                                @endphp
                                                <tr style="border-bottom: 1px solid #222a3d;">
                                                    <td style="padding: 8px 12px; font-weight: 700; color: #dae2fd;">
                                                        {{ $name }}
                                                    </td>
                                                    <td style="padding: 8px 8px; color: #8e909f;">
                                                        {{ $role }}
                                                    </td>
                                                    @foreach ($weekDays as $wd)
                                                        @php
                                                            $daySt = $m['weekStatus'][$wd['date']] ?? 'unmarked';
                                                            $isWdSelected = $wd['isSelected'] ?? false;
                                                            $isSunOff = ($wd['isSunday'] ?? false) || ($wd['isWeekOff'] ?? false);
                                                        @endphp
                                                        <td style="padding: 6px 2px; text-align: center; {{ $isWdSelected ? 'background-color: rgba(30, 64, 175, 0.2);' : ($isSunOff ? 'background-color: rgba(99, 102, 241, 0.04);' : '') }}">
                                                            @if ($isSunOff && $daySt === 'unmarked')
                                                                <span title="Sunday - Scheduled Week-Off" style="color: #a5b4fc; font-weight: 800; font-size: 8px;">OFF</span>
                                                            @elseif ($daySt === 'present')
                                                                <span style="color: #4edea3; font-weight: 800;">P</span>
                                                            @elseif ($daySt === 'wfh')
                                                                <span style="color: #93c5fd; font-weight: 800;">W</span>
                                                            @elseif ($daySt === 'absent')
                                                                <span style="color: #ffb4ab; font-weight: 800;">A</span>
                                                            @elseif ($daySt === 'leave')
                                                                <span style="color: #d8b4fe; font-weight: 800;">L</span>
                                                            @else
                                                                <span style="color: #475569;">-</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td style="padding: 8px 12px; text-align: right;">
                                                        <div style="display: inline-flex; align-items: center; gap: 3px;">
                                                            <button
                                                                type="button"
                                                                wire:click="setAttendanceStatus({{ $intern->id }}, 'present')"
                                                                title="Mark Present"
                                                                style="padding: 2px 5px; font-size: 9px; font-weight: 800; border-radius: 4px; cursor: pointer; border: 1px solid #222a3d; {{ $m['statusToday'] === 'present' ? 'background-color: #10B981; color: #FFF;' : 'background-color: #171f33; color: #4edea3;' }}">
                                                                P
                                                            </button>
                                                            <button
                                                                type="button"
                                                                wire:click="setAttendanceStatus({{ $intern->id }}, 'absent')"
                                                                title="Mark Absent"
                                                                style="padding: 2px 5px; font-size: 9px; font-weight: 800; border-radius: 4px; cursor: pointer; border: 1px solid #222a3d; {{ $m['statusToday'] === 'absent' ? 'background-color: #EF4444; color: #FFF;' : 'background-color: #171f33; color: #ffb4ab;' }}">
                                                                A
                                                            </button>
                                                            <button
                                                                type="button"
                                                                wire:click="setAttendanceStatus({{ $intern->id }}, 'wfh')"
                                                                title="Mark WFH"
                                                                style="padding: 2px 5px; font-size: 9px; font-weight: 800; border-radius: 4px; cursor: pointer; border: 1px solid #222a3d; {{ $m['statusToday'] === 'wfh' ? 'background-color: #0284C7; color: #FFF;' : 'background-color: #171f33; color: #93c5fd;' }}">
                                                                W
                                                            </button>
                                                            <button
                                                                type="button"
                                                                wire:click="setAttendanceStatus({{ $intern->id }}, 'leave')"
                                                                title="Mark Leave"
                                                                style="padding: 2px 5px; font-size: 9px; font-weight: 800; border-radius: 4px; cursor: pointer; border: 1px solid #222a3d; {{ $m['statusToday'] === 'leave' ? 'background-color: #9333EA; color: #FFF;' : 'background-color: #171f33; color: #d8b4fe;' }}">
                                                                L
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" style="padding: 14px; text-align: center; color: #8e909f;">No members assigned.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: span 2; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 36px; text-align: center;">
                            <p style="font-weight: 700; color: #dae2fd; margin: 0;">No active project squads found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- TAB 4: INDIVIDUAL INTERNS & CALENDAR (STITCH SCREEN 40fada927fba49a28cc9) --}}
        {{-- ========================================================================= --}}
        @if ($activeTab === 'individual')
            @php
                $indiv = $individualData;
                $sel = $indiv['selectedIntern'];
                $iStats = $indiv['internStats'];
            @endphp
            <div class="master-detail-grid" style="display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: start;">
                {{-- LEFT COLUMN: Intern Search & Roster List --}}
                <div style="background-color: #131b2e; border-radius: 14px; border: 1px solid #222a3d; padding: 16px; display: flex; flex-direction: column; gap: 12px; max-height: calc(100vh - 180px); overflow-y: auto;">
                    <div style="font-size: 13px; font-weight: 800; color: #dae2fd; text-transform: uppercase; letter-spacing: 0.05em;">
                        Interns Directory ({{ count($indiv['interns']) }})
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach ($indiv['interns'] as $it)
                            @php
                                $isSelected = $selectedInternId === $it->id;
                                $name = $it->name ?: ($it->offerletter?->name ?? 'Intern');
                                $code = $it->intern_code ?: ('INT-' . str_pad($it->id, 3, '0', STR_PAD_LEFT));
                                $squadName = $it->project_name ?? $it->team?->team_name ?? 'Project not assigned';

                                $attList = $it->attendances->whereIn('status', ['present', 'wfh', 'leave', 'absent']);
                                $tot = $attList->count();
                                $pCount = $attList->where('status', 'present')->count();
                                $wCount = $attList->where('status', 'wfh')->count();
                                $rate = $tot > 0 ? round((($pCount + $wCount) / $tot) * 100, 1) : 0.0;

                                $words = explode(' ', trim($name));
                                $inits = strtoupper(substr($words[0] ?? 'I', 0, 1) . substr($words[1] ?? '', 0, 1));
                            @endphp
                            <div
                                wire:click="selectIntern({{ $it->id }})"
                                style="padding: 12px; border-radius: 10px; cursor: pointer; transition: all 0.15s ease; border: 1.5px solid {{ $isSelected ? '#1E40AF' : '#222a3d' }}; background-color: {{ $isSelected ? 'rgba(30, 64, 175, 0.25)' : '#171f33' }};">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 34px; height: 34px; border-radius: 50%; overflow: hidden; background-color: {{ $isSelected ? '#1E40AF' : '#222a3d' }}; color: {{ $isSelected ? '#FFFFFF' : '#b8c4ff' }}; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; flex-shrink: 0; border: 1px solid #222a3d;">
                                            @if ($it->intern_image)
                                                <img src="{{ asset('storage/' . $it->intern_image) }}" alt="{{ $name }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                            @else
                                                {{ $inits }}
                                            @endif
                                        </div>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 13px; font-weight: 700; color: {{ $isSelected ? '#b8c4ff' : '#dae2fd' }}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $name }}
                                            </div>
                                            <div style="font-size: 10px; color: #8e909f;">{{ $code }} • {{ $squadName }}</div>
                                        </div>
                                    </div>
                                    @if ($tot === 0)
                                        <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 9999px; background-color: #222a3d; color: #8e909f; flex-shrink: 0;">
                                            Unmarked
                                        </span>
                                    @else
                                        <span style="font-size: 11px; font-weight: 800; padding: 2px 6px; border-radius: 9999px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3); flex-shrink: 0;">
                                            {{ $rate }}%
                                        </span>
                                    @endif
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 10px; color: #8e909f; margin-top: 6px; padding-top: 6px; border-top: 1px solid {{ $isSelected ? 'rgba(30, 64, 175, 0.3)' : '#222a3d' }};">
                                    <span style="color: #4edea3; font-weight: 700;">{{ $pCount }} Present</span>
                                    <span>•</span>
                                    <span style="color: #93c5fd; font-weight: 700;">{{ $wCount }} WFH</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- RIGHT COLUMN: Selected Intern Showcase, Stats Ribbon & Monthly Calendar --}}
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    @if ($sel)
                        @php
                            $selName = $sel->name ?: ($sel->offerletter?->name ?? 'Intern');
                            $selCode = $sel->intern_code ?: ('INT-' . str_pad($sel->id, 3, '0', STR_PAD_LEFT));
                            $selRole = $sel->internship_role ?: ($sel->offerletter?->internship_role ?? 'Product & Engineering Intern');
                            $selSquad = $sel->project_name ?? $sel->team?->team_name ?? 'Project not assigned';
                            $selMentor = $sel->team?->mentor_name ?? 'Senior Engineering Mentor';
                            $selBatch = $sel->batch?->batch_name ?? 'Batch not assigned';

                            $words = explode(' ', trim($selName));
                            $selInits = strtoupper(substr($words[0] ?? 'I', 0, 1) . substr($words[1] ?? '', 0, 1));
                        @endphp

                        {{-- Intern Showcase Header Banner --}}
                        <div style="background-color: #131b2e; border-radius: 14px; border: 1px solid #222a3d; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="width: 56px; height: 56px; border-radius: 14px; overflow: hidden; background-color: #171f33; color: #b8c4ff; border: 1px solid #222a3d; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                                    @if ($sel->intern_image)
                                        <img src="{{ asset('storage/' . $sel->intern_image) }}" alt="{{ $selName }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                    @else
                                        {{ $selInits }}
                                    @endif
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <h2 style="font-size: 20px; font-weight: 800; color: #dae2fd; margin: 0;">{{ $selName }}</h2>
                                        <span style="background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; border: 1px solid rgba(99, 102, 241, 0.3); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">{{ $selCode }}</span>
                                        <span style="background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            <svg style="width: 12px; height: 12px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            Verified Intern
                                        </span>
                                    </div>
                                    <div style="font-size: 12px; color: #8e909f; margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <strong style="color: #b8c4ff;">{{ $selRole }}</strong>
                                        <span>•</span>
                                        <span>{{ $selSquad }}</span>
                                        <span>•</span>
                                        <span>Mentor: {{ $selMentor }}</span>
                                        <span>•</span>
                                        <span>{{ $selBatch }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Direct Actions: Mark Button & View Profile --}}
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button
                                    type="button"
                                    wire:click="openMarkModal({{ $sel->id }})"
                                    style="background-color: #1E40AF; color: #FFFFFF; font-size: 12px; font-weight: 700; padding: 8px 14px; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(30,64,175,0.4);">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    <span>Mark Attendance</span>
                                </button>
                                <a
                                    href="{{ route('filament.admin.resources.intern-management.interns.view', ['record' => $sel->id]) }}"
                                    target="_blank"
                                    style="background-color: #171f33; border: 1px solid #222a3d; color: #dae2fd; font-size: 12px; font-weight: 700; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>Open Profile</span>
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            </div>
                        </div>

                        {{-- 5-Metric Quick Stats Ribbon (Stitch Exact) --}}
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px;">
                            {{-- Days Present --}}
                            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 14px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Days Present</span>
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10B981;"></span>
                                </div>
                                <div style="font-size: 24px; font-weight: 800; color: #4edea3; margin-top: 6px; line-height: 1;">
                                    {{ $iStats['present'] }} <span style="font-size: 11px; font-weight: 500; color: #8e909f;">days</span>
                                </div>
                            </div>

                            {{-- Remote WFH --}}
                            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 14px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Remote (WFH)</span>
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #0284C7;"></span>
                                </div>
                                <div style="font-size: 24px; font-weight: 800; color: #93c5fd; margin-top: 6px; line-height: 1;">
                                    {{ $iStats['wfh'] }} <span style="font-size: 11px; font-weight: 500; color: #8e909f;">days</span>
                                </div>
                            </div>

                            {{-- Approved Leave --}}
                            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 14px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Approved Leave</span>
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #9333EA;"></span>
                                </div>
                                <div style="font-size: 24px; font-weight: 800; color: #d8b4fe; margin-top: 6px; line-height: 1;">
                                    {{ $iStats['leave'] }} <span style="font-size: 11px; font-weight: 500; color: #8e909f;">day</span>
                                </div>
                            </div>

                            {{-- Absent --}}
                            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 14px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Unexcused Absent</span>
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #EF4444;"></span>
                                </div>
                                <div style="font-size: 24px; font-weight: 800; color: {{ $iStats['absent'] > 0 ? '#ffb4ab' : '#8e909f' }}; margin-top: 6px; line-height: 1;">
                                    {{ $iStats['absent'] }} <span style="font-size: 11px; font-weight: 500; color: #8e909f;">days</span>
                                </div>
                            </div>

                            {{-- Compliance Rate % --}}
                            <div style="background-color: rgba(30, 64, 175, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 14px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 10px; font-weight: 800; color: #b8c4ff; text-transform: uppercase;">Compliance</span>
                                    <span style="font-size: 10px; font-weight: 800; color: #4edea3;">Rate</span>
                                </div>
                                <div style="font-size: 24px; font-weight: 800; color: #b8c4ff; margin-top: 6px; line-height: 1;">
                                    {{ $iStats['compliance'] }}%
                                </div>
                                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 6px; overflow: hidden;">
                                    <div style="width: {{ $iStats['compliance'] }}%; height: 100%; background-color: #1E40AF; border-radius: 9999px;"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Monthly Timesheet Calendar Grid (Stitch 40fada927fba49a28cc9) --}}
                        <div style="background-color: #131b2e; border-radius: 14px; border: 1px solid #222a3d; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <h3 style="font-size: 16px; font-weight: 800; color: #dae2fd; margin: 0;">{{ $indiv['monthTitle'] }} Timesheet Calendar</h3>
                                    <span style="font-size: 11px; color: #8e909f;">(Click any working date to mark/change status)</span>
                                </div>

                                {{-- Month Navigation & Legend --}}
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <button
                                        type="button"
                                        wire:click="previousMonth"
                                        style="width: 30px; height: 30px; border-radius: 6px; border: 1px solid #222a3d; background-color: #171f33; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #dae2fd;">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                    </button>
                                    <span style="font-size: 13px; font-weight: 800; color: #dae2fd; min-width: 110px; text-align: center;">{{ $indiv['monthTitle'] }}</span>
                                    <button
                                        type="button"
                                        wire:click="nextMonth"
                                        style="width: 30px; height: 30px; border-radius: 6px; border: 1px solid #222a3d; background-color: #171f33; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #dae2fd;">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Calendar Days Grid Header (Sun - Sat) --}}
                            <div class="calendar-grid-7" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; margin-bottom: 8px;">
                                <div style="font-size: 11px; font-weight: 800; color: #a5b4fc; text-transform: uppercase; padding: 6px 0; background-color: rgba(99, 102, 241, 0.15); border-radius: 6px; border: 1px solid rgba(99, 102, 241, 0.3);">Sun (Week-Off)</div>
                                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Mon</div>
                                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Tue</div>
                                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Wed</div>
                                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Thu</div>
                                <div style="font-size: 11px; font-weight: 800; color: #8e909f; text-transform: uppercase; padding: 6px 0;">Fri</div>
                                <div style="font-size: 11px; font-weight: 800; color: #4edea3; text-transform: uppercase; padding: 6px 0;">Sat</div>
                            </div>

                            {{-- Calendar Days Grid Cells --}}
                            <div class="calendar-grid-7" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
                                @foreach ($indiv['calendarDays'] as $cDay)
                                    @php
                                        $cStatus = $cDay['status'];
                                        $isCur = $cDay['isCurrentMonth'];
                                        $isDaySelected = $cDay['isSelected'] ?? false;
                                        $isSunOff = $cDay['isWeekend'];
                                    @endphp
                                    <div
                                        class="calendar-cell calendar-day-btn"
                                        style="min-height: 64px; border-radius: 8px; border: {{ $isDaySelected ? '2px solid #3b82f6' : ($cDay['isToday'] ? '1.5px solid #1E40AF' : '1px solid #222a3d') }}; background-color: {{ $isDaySelected ? 'rgba(30, 64, 175, 0.25)' : ($isSunOff ? '#0b1326' : '#171f33') }}; padding: 6px; display: flex; flex-direction: column; justify-content: space-between; {{ $isDaySelected ? 'box-shadow: 0 2px 8px rgba(59,130,246,0.3);' : '' }} {{ !$isCur ? 'opacity: 0.35;' : '' }}">
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <span style="font-size: 11px; font-weight: {{ $isDaySelected || $cDay['isToday'] ? '800' : '600' }}; color: {{ $isDaySelected ? '#b8c4ff' : ($cDay['isToday'] ? '#b8c4ff' : ($isSunOff ? '#a5b4fc' : '#dae2fd')) }};">
                                                {{ $cDay['day'] }}
                                            </span>
                                            <div style="display: flex; align-items: center; gap: 3px;">
                                                @if ($isDaySelected)
                                                    <span style="font-size: 8px; font-weight: 800; background-color: #1E40AF; color: #FFFFFF; padding: 1px 4px; border-radius: 3px;">SELECTED</span>
                                                @elseif ($cDay['isToday'])
                                                    <span style="font-size: 8px; font-weight: 800; background-color: rgba(30, 64, 175, 0.3); color: #b8c4ff; padding: 1px 4px; border-radius: 3px;">TODAY</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div style="display: flex; justify-content: flex-end; gap: 2px; margin-top: 4px;">
                                            @if ($cDay['isWeekend'])
                                                @if ($cStatus === 'present')
                                                    <span style="font-size: 9px; font-weight: 700; color: #10B981; background-color: rgba(16, 185, 129, 0.15); padding: 2px 5px; border-radius: 4px; border: 1px solid rgba(78, 222, 163, 0.3);">P (Sun)</span>
                                                @elseif ($cStatus === 'wfh')
                                                    <span style="font-size: 9px; font-weight: 700; color: #38bdf8; background-color: rgba(2, 132, 199, 0.15); padding: 2px 5px; border-radius: 4px; border: 1px solid rgba(56, 189, 248, 0.3);">W (Sun)</span>
                                                @else
                                                    <span style="font-size: 9px; font-weight: 700; color: #a5b4fc; background-color: #0f172a; padding: 2px 5px; border-radius: 4px; border: 1px solid rgba(99, 102, 241, 0.3);">Week-Off</span>
                                                @endif
                                            @elseif ($cStatus === 'present')
                                                <button
                                                    type="button"
                                                    wire:click="setAttendanceStatus({{ $sel->id }}, 'absent', '{{ $cDay['date'] }}')"
                                                    title="Present (Click to mark Absent)"
                                                    style="border: none; cursor: pointer; background-color: #10B981; color: #FFFFFF; font-weight: 800; font-size: 10px; padding: 2px 7px; border-radius: 4px;">
                                                    P
                                                </button>
                                            @elseif ($cStatus === 'absent')
                                                <button
                                                    type="button"
                                                    wire:click="setAttendanceStatus({{ $sel->id }}, 'wfh', '{{ $cDay['date'] }}')"
                                                    title="Absent (Click to mark WFH)"
                                                    style="border: none; cursor: pointer; background-color: #EF4444; color: #FFFFFF; font-weight: 800; font-size: 10px; padding: 2px 7px; border-radius: 4px;">
                                                    A
                                                </button>
                                            @elseif ($cStatus === 'wfh')
                                                <button
                                                    type="button"
                                                    wire:click="setAttendanceStatus({{ $sel->id }}, 'leave', '{{ $cDay['date'] }}')"
                                                    title="WFH (Click to mark Leave)"
                                                    style="border: none; cursor: pointer; background-color: #0284C7; color: #FFFFFF; font-weight: 800; font-size: 10px; padding: 2px 7px; border-radius: 4px;">
                                                    W
                                                </button>
                                            @elseif ($cStatus === 'leave')
                                                <button
                                                    type="button"
                                                    wire:click="setAttendanceStatus({{ $sel->id }}, 'present', '{{ $cDay['date'] }}')"
                                                    title="Leave (Click to mark Present)"
                                                    style="border: none; cursor: pointer; background-color: #9333EA; color: #FFFFFF; font-weight: 800; font-size: 10px; padding: 2px 7px; border-radius: 4px;">
                                                    L
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="setAttendanceStatus({{ $sel->id }}, 'present', '{{ $cDay['date'] }}')"
                                                    title="Unmarked (Click to Mark Present)"
                                                    style="border: 1px dashed #2d3449; cursor: pointer; background-color: #131b2e; color: #8e909f; font-weight: 600; font-size: 10px; padding: 2px 6px; border-radius: 4px;">
                                                    +
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 48px; text-align: center;">
                            <p style="font-weight: 700; color: #dae2fd; margin: 0;">Select an intern from the left roster to view their monthly calendar ledger.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- 4. MARK ATTENDANCE POPUP MODAL (PRESENT, ABSENT, WFH, LEAVE) --}}
        {{-- ========================================================================= --}}
        @if ($showMarkModal)
            <div style="position: fixed; inset: 0; background-color: rgba(11, 19, 38, 0.75); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px;">
                <div style="background-color: #131b2e; border-radius: 16px; border: 1px solid #222a3d; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden;">
                    {{-- Modal Header --}}
                    <div style="padding: 18px 24px; border-bottom: 1px solid #222a3d; background-color: #171f33; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; border: 1px solid rgba(99, 102, 241, 0.3); display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 800; color: #dae2fd; margin: 0;">Mark Attendance Roll-Call</h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0;">Select target intern, date, and verification status.</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="closeMarkModal"
                            style="background: transparent; border: none; color: #8e909f; cursor: pointer; padding: 4px; border-radius: 6px;">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 16px;">
                        {{-- Intern Target Selection --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Target Intern(s)
                            </label>
                            <select
                                wire:model="markInternId"
                                style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600; color: #dae2fd; outline: none;">
                                <option value="">⭐ Roll-Call All Active Interns (Bulk)</option>
                                @foreach ($allActiveInterns as $internOption)
                                    <option value="{{ $internOption->id }}">
                                        {{ $internOption->name }} ({{ $internOption->intern_code ?: 'INT-' . str_pad($internOption->id, 3, '0', STR_PAD_LEFT) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date Selection --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Attendance Date
                            </label>
                            <input
                                type="date"
                                wire:model="markDate"
                                style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600; color: #dae2fd; outline: none; color-scheme: dark;"
                            />
                        </div>

                        {{-- 4 Big Status Radio Cards --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #dae2fd; margin-bottom: 8px;">
                                Select Attendance Status
                            </label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                {{-- Present --}}
                                <div
                                    wire:click="setMarkStatus('present')"
                                    class="status-radio-card"
                                    style="border-radius: 10px; padding: 12px; border: 2px solid {{ $markStatus === 'present' ? '#10B981' : '#222a3d' }}; background-color: {{ $markStatus === 'present' ? 'rgba(16, 185, 129, 0.15)' : '#171f33' }};">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span style="font-weight: 800; font-size: 13px; color: #4edea3;">Present</span>
                                        <span style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid {{ $markStatus === 'present' ? '#10B981' : '#2d3449' }}; display: flex; align-items: center; justify-content: center;">
                                            @if ($markStatus === 'present')
                                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #10B981;"></span>
                                            @endif
                                        </span>
                                    </div>
                                    <p style="font-size: 11px; color: #4edea3; margin: 4px 0 0;">Physical / Onsite Presence</p>
                                </div>

                                {{-- Absent --}}
                                <div
                                    wire:click="setMarkStatus('absent')"
                                    class="status-radio-card"
                                    style="border-radius: 10px; padding: 12px; border: 2px solid {{ $markStatus === 'absent' ? '#EF4444' : '#222a3d' }}; background-color: {{ $markStatus === 'absent' ? 'rgba(239, 68, 68, 0.15)' : '#171f33' }};">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span style="font-weight: 800; font-size: 13px; color: #ffb4ab;">Absent</span>
                                        <span style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid {{ $markStatus === 'absent' ? '#EF4444' : '#2d3449' }}; display: flex; align-items: center; justify-content: center;">
                                            @if ($markStatus === 'absent')
                                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #EF4444;"></span>
                                            @endif
                                        </span>
                                    </div>
                                    <p style="font-size: 11px; color: #ffb4ab; margin: 4px 0 0;">Unexcused Absence</p>
                                </div>

                                {{-- WFH --}}
                                <div
                                    wire:click="setMarkStatus('wfh')"
                                    class="status-radio-card"
                                    style="border-radius: 10px; padding: 12px; border: 2px solid {{ $markStatus === 'wfh' ? '#0284C7' : '#222a3d' }}; background-color: {{ $markStatus === 'wfh' ? 'rgba(2, 132, 199, 0.15)' : '#171f33' }};">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span style="font-weight: 800; font-size: 13px; color: #93c5fd;">WFH</span>
                                        <span style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid {{ $markStatus === 'wfh' ? '#0284C7' : '#2d3449' }}; display: flex; align-items: center; justify-content: center;">
                                            @if ($markStatus === 'wfh')
                                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #0284C7;"></span>
                                            @endif
                                        </span>
                                    </div>
                                    <p style="font-size: 11px; color: #93c5fd; margin: 4px 0 0;">Approved Remote Working</p>
                                </div>

                                {{-- Leave --}}
                                <div
                                    wire:click="setMarkStatus('leave')"
                                    class="status-radio-card"
                                    style="border-radius: 10px; padding: 12px; border: 2px solid {{ $markStatus === 'leave' ? '#9333EA' : '#222a3d' }}; background-color: {{ $markStatus === 'leave' ? 'rgba(147, 51, 234, 0.15)' : '#171f33' }};">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span style="font-weight: 800; font-size: 13px; color: #d8b4fe;">Leave</span>
                                        <span style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid {{ $markStatus === 'leave' ? '#9333EA' : '#2d3449' }}; display: flex; align-items: center; justify-content: center;">
                                            @if ($markStatus === 'leave')
                                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #9333EA;"></span>
                                            @endif
                                        </span>
                                    </div>
                                    <p style="font-size: 11px; color: #d8b4fe; margin: 4px 0 0;">Approved Vacation / Sick</p>
                                </div>
                            </div>
                        </div>

                        {{-- Optional Note --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Notes / Remarks (Optional)
                            </label>
                            <input
                                type="text"
                                wire:model="markNote"
                                placeholder="e.g. Approved medical leave, remote presentation..."
                                style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 12px; font-size: 12px; color: #dae2fd; outline: none;"
                            />
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 16px 24px; background-color: #171f33; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                        <button
                            type="button"
                            wire:click="closeMarkModal"
                            style="padding: 8px 16px; font-size: 13px; font-weight: 600; color: #c4c5d5; background: transparent; border: 1px solid #222a3d; border-radius: 8px; cursor: pointer;">
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="submitMarkModal"
                            style="padding: 8px 20px; font-size: 13px; font-weight: 700; color: #FFFFFF; background-color: #1E40AF; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 8px rgba(30,64,175,0.4);">
                            Confirm & Save
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
