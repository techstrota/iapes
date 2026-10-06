<x-filament-panels::page>
    @php
        $analytics = $this->analyticsData;
    @endphp

    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- Custom Styles matching IAPES System UI (Dashboard/Attendance/Intern) --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <style>
            .sys-card {
                background-color: #131b2e;
                border: 1px solid #222a3d;
                border-radius: 14px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
                transition: transform 0.15s ease, border-color 0.15s ease;
            }
            .sys-card-inner {
                background-color: #171f33;
                border: 1px solid #222a3d;
                border-radius: 10px;
            }
            .sys-tab-btn {
                padding: 8px 16px;
                font-size: 13px;
                border-radius: 9px;
                border: none;
                cursor: pointer;
                transition: all 0.15s ease;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                white-space: nowrap;
            }
            .sys-tab-active {
                background-color: #1e40af !important;
                color: #ffffff !important;
                font-weight: 700 !important;
                box-shadow: 0 2px 8px rgba(30, 64, 175, 0.4) !important;
            }
            .sys-tab-inactive {
                background-color: transparent !important;
                color: #8e909f !important;
                font-weight: 500 !important;
            }
            .sys-tab-inactive:hover {
                background-color: #171f33 !important;
                color: #dae2fd !important;
            }
            .sys-input {
                background-color: #171f33 !important;
                border: 1px solid #222a3d !important;
                border-radius: 10px !important;
                padding: 8px 12px !important;
                font-size: 13px !important;
                color: #dae2fd !important;
                outline: none !important;
                transition: border-color 0.15s ease !important;
            }
            .sys-input:focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 1px #3b82f6 !important;
            }
            .sys-btn-primary {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                background-color: #1e40af;
                color: #ffffff;
                padding: 8px 16px;
                border-radius: 10px;
                font-size: 12.5px;
                font-weight: 700;
                border: 1px solid #3b82f6;
                box-shadow: 0 2px 10px rgba(30, 64, 175, 0.4);
                cursor: pointer;
                transition: all 0.15s ease;
                text-decoration: none;
            }
            .sys-btn-primary:hover {
                background-color: #1d4ed8;
                transform: translateY(-1px);
            }
            .sys-btn-secondary {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                background-color: #171f33;
                color: #dae2fd;
                padding: 8px 14px;
                border-radius: 10px;
                font-size: 12.5px;
                font-weight: 600;
                border: 1px solid #222a3d;
                cursor: pointer;
                transition: all 0.15s ease;
                text-decoration: none;
            }
            .sys-btn-secondary:hover {
                background-color: #222a3d;
                color: #ffffff;
            }
            .sys-btn-amber {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                background-color: #d97706;
                color: #ffffff;
                padding: 8px 16px;
                border-radius: 10px;
                font-size: 12.5px;
                font-weight: 700;
                border: 1px solid #f59e0b;
                box-shadow: 0 2px 10px rgba(217, 119, 6, 0.35);
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .sys-btn-amber:hover {
                background-color: #b45309;
                transform: translateY(-1px);
            }
            .sys-table-row:hover {
                background-color: #171f33 !important;
            }
            .sys-scroll::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            .sys-scroll::-webkit-scrollbar-track {
                background: #0b1326;
            }
            .sys-scroll::-webkit-scrollbar-thumb {
                background: #222a3d;
                border-radius: 3px;
            }
            .sys-scroll::-webkit-scrollbar-thumb:hover {
                background: #3b82f6;
            }
        </style>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 1. Hero Reports Header & Quick Actions Banner                    --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px 26px; margin-bottom: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;">
            {{-- Ambient Decorative Glow --}}
            <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(30, 64, 175, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; position: relative; z-index: 1;">
                {{-- Left: Reports Identity & Status --}}
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 54px; height: 54px; border-radius: 14px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                        <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2;">
                                Reports & Executive Analytics Hub
                            </h1>
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; padding: 3px 9px; border-radius: 9999px; border: 1px solid rgba(59, 130, 246, 0.3);">
                                System Intelligence
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-top: 5px; flex-wrap: wrap;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #4edea3; background-color: rgba(16, 185, 129, 0.12); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.25);">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981; display: inline-block; box-shadow: 0 0 6px #10b981;"></span>
                                Live System Telemetry
                            </span>
                            <span style="font-size: 12px; color: #8e909f; display: inline-flex; align-items: center; gap: 4px;">
                                <svg style="width: 14px; height: 14px; color: #8e909f;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ now()->format('l, d F Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right: Time Range Selector & Action Launchpad --}}
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    {{-- Time Range Segmented Bar --}}
                    <div style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 3px; display: inline-flex; align-items: center; gap: 2px;">
                        <button type="button" wire:click="setTimeRange('all')"
                            style="padding: 6px 12px; font-size: 12px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $timeRange === 'all' ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: transparent; color: #8e909f; font-weight: 500;' }}">
                            All Time
                        </button>
                        <button type="button" wire:click="setTimeRange('7_days')"
                            style="padding: 6px 12px; font-size: 12px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $timeRange === '7_days' ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: transparent; color: #8e909f; font-weight: 500;' }}">
                            7 Days
                        </button>
                        <button type="button" wire:click="setTimeRange('30_days')"
                            style="padding: 6px 12px; font-size: 12px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $timeRange === '30_days' ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: transparent; color: #8e909f; font-weight: 500;' }}">
                            30 Days
                        </button>
                        <button type="button" wire:click="setTimeRange('this_month')"
                            style="padding: 6px 12px; font-size: 12px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $timeRange === 'this_month' ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: transparent; color: #8e909f; font-weight: 500;' }}">
                            This Month
                        </button>
                        <button type="button" wire:click="setTimeRange('custom')"
                            style="padding: 6px 12px; font-size: 12px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $timeRange === 'custom' ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: transparent; color: #8e909f; font-weight: 500;' }}">
                            Custom
                        </button>
                    </div>

                    {{-- PDF Summary Button --}}
                    <a href="{{ route('report.download', ['type' => 'full']) }}" target="_blank" class="sys-btn-secondary">
                        <svg style="width: 16px; height: 16px; color: #60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        PDF Summary
                    </a>

                    {{-- CSV Export Center Trigger Button --}}
                    <button type="button" wire:click="openExportModal('{{ $activeTab === 'analytics' ? 'interns' : $activeTab }}')" class="sys-btn-primary">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        CSV Export Center
                    </button>
                </div>
            </div>

            {{-- Custom Date Range Picker Ribbon --}}
            @if ($timeRange === 'custom')
                <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #222a3d; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="font-size: 12px; font-weight: 700; color: #8e909f;">Custom Interval:</span>
                    <input type="date" wire:model.live="customStartDate" class="sys-input" style="color-scheme: dark;" />
                    <span style="font-size: 12px; color: #8e909f;">to</span>
                    <input type="date" wire:model.live="customEndDate" class="sys-input" style="color-scheme: dark;" />
                </div>
            @endif
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 2. Top 5 System KPI Cards Row (Matches Dashboard & Intern View)  --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 22px;">
            {{-- KPI 1: Active Interns --}}
            <div class="sys-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Active Interns</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                    {{ number_format($analytics['activeInterns']) }}
                    <span style="font-size: 16px; color: #8e909f; font-weight: 600;">/ {{ number_format($analytics['totalInterns']) }}</span>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #4edea3; font-weight: 700;">
                        {{ $analytics['totalInterns'] > 0 ? round(($analytics['activeInterns'] / $analytics['totalInterns']) * 100, 1) : 0 }}% Active
                    </span>
                    <span>{{ $analytics['archivedInterns'] }} Archived</span>
                </div>
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                    <div style="width: {{ $analytics['totalInterns'] > 0 ? ($analytics['activeInterns'] / $analytics['totalInterns']) * 100 : 0 }}%; height: 100%; background-color: #3b82f6; border-radius: 9999px;"></div>
                </div>
            </div>

            {{-- KPI 2: Candidate Pipeline --}}
            <div class="sys-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Recruitment Pool</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                    {{ number_format($analytics['totalCandidates']) }}
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #fbbf24; font-weight: 700;">{{ $analytics['conversionRate'] }}% Conversion</span>
                    <span>{{ $analytics['shortlistedCandidates'] }} Shortlisted</span>
                </div>
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                    <div style="width: {{ min(100, $analytics['conversionRate']) }}%; height: 100%; background-color: #f59e0b; border-radius: 9999px;"></div>
                </div>
            </div>

            {{-- KPI 3: Task Velocity --}}
            <div class="sys-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Task Submissions</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(147, 51, 234, 0.15); color: #d8b4fe; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(147, 51, 234, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                    {{ number_format($analytics['totalSubmissions']) }}
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #d8b4fe; font-weight: 700;">{{ $analytics['taskCompletionRate'] }}% Velocity</span>
                    <span>{{ $analytics['evaluatedSubmissions'] }} Evaluated</span>
                </div>
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                    <div style="width: {{ min(100, $analytics['taskCompletionRate']) }}%; height: 100%; background-color: #a855f7; border-radius: 9999px;"></div>
                </div>
            </div>

            {{-- KPI 4: Attendance Rate --}}
            <div class="sys-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Cohort Attendance</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(78, 222, 163, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #4edea3; line-height: 1.1;">
                    {{ $analytics['attendancePct'] }}%
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #4edea3; font-weight: 700;">{{ number_format($analytics['presentCount']) }} Present</span>
                    <span style="color: #f87171;">{{ number_format($analytics['absentCount']) }} Absent</span>
                </div>
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                    <div style="width: {{ min(100, $analytics['attendancePct']) }}%; height: 100%; background-color: #10b981; border-radius: 9999px;"></div>
                </div>
            </div>

            {{-- KPI 5: Audit Log Events --}}
            <div class="sys-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">System Audit Trail</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(6, 182, 212, 0.15); color: #67e8f9; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(6, 182, 212, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                    {{ number_format($analytics['totalLogs']) }}
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #67e8f9; font-weight: 700;">100% Audited</span>
                    <span>Zero Breaches</span>
                </div>
                <div style="width: 100%; height: 4px; background-color: #171f33; border-radius: 9999px; margin-top: 10px; overflow: hidden;">
                    <div style="width: 100%; height: 100%; background-color: #06b6d4; border-radius: 9999px;"></div>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 3. Segmented Navigation Bar (Matches Intern & Attendance Tabs)   --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap;">
            <div style="display: inline-flex; background-color: #0b1326; border: 1px solid #222a3d; padding: 4px; border-radius: 12px; gap: 4px; flex-wrap: wrap;">
                <button type="button" wire:click="setActiveTab('analytics')"
                    class="sys-tab-btn {{ $activeTab === 'analytics' ? 'sys-tab-active' : 'sys-tab-inactive' }}">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                    <span>Executive Analytics</span>
                </button>

                <button type="button" wire:click="setActiveTab('interns')"
                    class="sys-tab-btn {{ $activeTab === 'interns' ? 'sys-tab-active' : 'sys-tab-inactive' }}">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Interns Report</span>
                    <span style="padding: 2px 7px; border-radius: 9999px; font-size: 11px; background-color: #171f33; color: #b8c4ff; border: 1px solid #222a3d;">
                        {{ $analytics['totalInterns'] }}
                    </span>
                </button>

                <button type="button" wire:click="setActiveTab('candidates')"
                    class="sys-tab-btn {{ $activeTab === 'candidates' ? 'sys-tab-active' : 'sys-tab-inactive' }}">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Candidates & Funnel</span>
                    <span style="padding: 2px 7px; border-radius: 9999px; font-size: 11px; background-color: #171f33; color: #fbbf24; border: 1px solid #222a3d;">
                        {{ $analytics['totalCandidates'] }}
                    </span>
                </button>

                <button type="button" wire:click="setActiveTab('tasks')"
                    class="sys-tab-btn {{ $activeTab === 'tasks' ? 'sys-tab-active' : 'sys-tab-inactive' }}">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Tasks & Submissions</span>
                    <span style="padding: 2px 7px; border-radius: 9999px; font-size: 11px; background-color: #171f33; color: #d8b4fe; border: 1px solid #222a3d;">
                        {{ $analytics['totalSubmissions'] }}
                    </span>
                </button>

                <button type="button" wire:click="setActiveTab('attendance')"
                    class="sys-tab-btn {{ $activeTab === 'attendance' ? 'sys-tab-active' : 'sys-tab-inactive' }}">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Muster Roll Register</span>
                    <span style="padding: 2px 7px; border-radius: 9999px; font-size: 11px; background-color: #171f33; color: #4edea3; border: 1px solid #222a3d;">
                        {{ $analytics['totalAttendances'] }}
                    </span>
                </button>

                <button type="button" wire:click="setActiveTab('logs')"
                    class="sys-tab-btn {{ $activeTab === 'logs' ? 'sys-tab-active' : 'sys-tab-inactive' }}">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Audit Logs</span>
                    <span style="padding: 2px 7px; border-radius: 9999px; font-size: 11px; background-color: #171f33; color: #67e8f9; border: 1px solid #222a3d;">
                        {{ $analytics['totalLogs'] }}
                    </span>
                </button>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- WORKSPACE 1: EXECUTIVE ANALYTICS                                --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($activeTab === 'analytics')
            <div style="display: flex; flex-direction: column; gap: 20px;">
                {{-- Trajectory & Funnel Section --}}
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 18px;">
                    {{-- 6-Month Trajectory Card --}}
                    <div class="sys-card" style="padding: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 12px; margin-bottom: 16px;">
                            <div>
                                <h3 style="font-size: 14px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                                    6-Month Growth & Activity Trajectory
                                </h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 3px 0 0;">Monthly velocity across Interns, Candidates, and Tasks</p>
                            </div>
                            <div style="display: flex; align-items: center; gap: 14px; font-size: 11.5px;">
                                <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background-color: #3b82f6;"></span> Interns</span>
                                <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background-color: #f59e0b;"></span> Candidates</span>
                                <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background-color: #a855f7;"></span> Tasks</span>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            @foreach ($analytics['monthlyTrajectory'] as $item)
                                @php
                                    $maxVal = max(1, $item['interns'] + $item['candidates'] + $item['tasks']);
                                @endphp
                                <div>
                                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                                        <span style="font-weight: 700; color: #ffffff;">{{ $item['month'] }}</span>
                                        <span style="font-size: 11.5px; color: #8e909f;">
                                            <strong style="color: #60a5fa;">{{ $item['interns'] }}</strong> interns •
                                            <strong style="color: #fbbf24;">{{ $item['candidates'] }}</strong> candidates •
                                            <strong style="color: #c084fc;">{{ $item['tasks'] }}</strong> submissions
                                        </span>
                                    </div>
                                    <div style="width: 100%; height: 8px; border-radius: 9999px; background-color: #0b1326; overflow: hidden; display: flex;">
                                        <div style="width: {{ ($item['interns'] / $maxVal) * 100 }}%; height: 100%; background-color: #3b82f6;" title="Interns: {{ $item['interns'] }}"></div>
                                        <div style="width: {{ ($item['candidates'] / $maxVal) * 100 }}%; height: 100%; background-color: #f59e0b;" title="Candidates: {{ $item['candidates'] }}"></div>
                                        <div style="width: {{ ($item['tasks'] / $maxVal) * 100 }}%; height: 100%; background-color: #a855f7;" title="Tasks: {{ $item['tasks'] }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Talent Acquisition Funnel --}}
                    <div class="sys-card" style="padding: 20px;">
                        <div style="border-bottom: 1px solid #222a3d; padding-bottom: 12px; margin-bottom: 16px;">
                            <h3 style="font-size: 14px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                                Recruitment Pipeline Funnel
                            </h3>
                            <p style="font-size: 12px; color: #8e909f; margin: 3px 0 0;">Conversion stages from initial apply to offer accept</p>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @php
                                $f = $analytics['funnel'];
                                $base = max(1, $f['applied']);
                            @endphp
                            {{-- Step 1 --}}
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <span style="color: #dae2fd;">1. Applications</span>
                                    <span style="font-weight: 800; color: #ffffff;">{{ $f['applied'] }} (100%)</span>
                                </div>
                                <div style="width: 100%; height: 6px; border-radius: 9999px; background-color: #0b1326; overflow: hidden;">
                                    <div style="width: 100%; height: 100%; background-color: #64748b; border-radius: 9999px;"></div>
                                </div>
                            </div>

                            {{-- Step 2 --}}
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <span style="color: #dae2fd;">2. Interview Scheduled</span>
                                    <span style="font-weight: 800; color: #67e8f9;">{{ $f['scheduled'] }} ({{ round(($f['scheduled'] / $base) * 100, 1) }}%)</span>
                                </div>
                                <div style="width: 100%; height: 6px; border-radius: 9999px; background-color: #0b1326; overflow: hidden;">
                                    <div style="width: {{ min(100, ($f['scheduled'] / $base) * 100) }}%; height: 100%; background-color: #06b6d4; border-radius: 9999px;"></div>
                                </div>
                            </div>

                            {{-- Step 3 --}}
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <span style="color: #dae2fd;">3. Interview Conducted</span>
                                    <span style="font-weight: 800; color: #60a5fa;">{{ $f['interviewed'] }} ({{ round(($f['interviewed'] / $base) * 100, 1) }}%)</span>
                                </div>
                                <div style="width: 100%; height: 6px; border-radius: 9999px; background-color: #0b1326; overflow: hidden;">
                                    <div style="width: {{ min(100, ($f['interviewed'] / $base) * 100) }}%; height: 100%; background-color: #3b82f6; border-radius: 9999px;"></div>
                                </div>
                            </div>

                            {{-- Step 4 --}}
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <span style="color: #dae2fd;">4. Shortlisted / Selected</span>
                                    <span style="font-weight: 800; color: #fbbf24;">{{ $f['selected'] }} ({{ round(($f['selected'] / $base) * 100, 1) }}%)</span>
                                </div>
                                <div style="width: 100%; height: 6px; border-radius: 9999px; background-color: #0b1326; overflow: hidden;">
                                    <div style="width: {{ min(100, ($f['selected'] / $base) * 100) }}%; height: 100%; background-color: #f59e0b; border-radius: 9999px;"></div>
                                </div>
                            </div>

                            {{-- Step 5 --}}
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <span style="color: #dae2fd;">5. Offers Issued</span>
                                    <span style="font-weight: 800; color: #34d399;">{{ $f['offers_issued'] }} ({{ round(($f['offers_issued'] / $base) * 100, 1) }}%)</span>
                                </div>
                                <div style="width: 100%; height: 6px; border-radius: 9999px; background-color: #0b1326; overflow: hidden;">
                                    <div style="width: {{ min(100, ($f['offers_issued'] / $base) * 100) }}%; height: 100%; background-color: #10b981; border-radius: 9999px;"></div>
                                </div>
                            </div>

                            {{-- Step 6 --}}
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <span style="color: #34d399; font-weight: 700;">6. Offers Accepted</span>
                                    <span style="font-weight: 800; color: #4edea3;">{{ $f['offers_accepted'] }} ({{ round(($f['offers_accepted'] / $base) * 100, 1) }}%)</span>
                                </div>
                                <div style="width: 100%; height: 6px; border-radius: 9999px; background-color: #0b1326; overflow: hidden;">
                                    <div style="width: {{ min(100, ($f['offers_accepted'] / $base) * 100) }}%; height: 100%; background-color: #4edea3; border-radius: 9999px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Domains, Batches & Real-Time Audit Feed --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px;">
                    {{-- Domain Distribution --}}
                    <div class="sys-card" style="padding: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 12px; margin-bottom: 14px;">
                            <h3 style="font-size: 14px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Domain Distribution</h3>
                            <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Headcount</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse ($analytics['domainBreakdown'] as $dom)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background-color: #171f33; border: 1px solid #222a3d;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #3b82f6;"></span>
                                        <span style="font-size: 13px; font-weight: 600; color: #ffffff;">{{ $dom->domain }}</span>
                                    </div>
                                    <span style="font-size: 12px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); padding: 3px 10px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.25);">
                                        {{ $dom->count }} candidates
                                    </span>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 24px 0; font-size: 12px; color: #8e909f;">No domain records available.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Active Cohorts & Batches --}}
                    <div class="sys-card" style="padding: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 12px; margin-bottom: 14px;">
                            <h3 style="font-size: 14px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Active Cohorts & Batches</h3>
                            <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Interns</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse ($analytics['batches'] as $b)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background-color: #171f33; border: 1px solid #222a3d;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span>
                                        <span style="font-size: 13px; font-weight: 600; color: #ffffff;">{{ $b->batch_name }}</span>
                                    </div>
                                    <span style="font-size: 12px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); padding: 3px 10px; border-radius: 6px; border: 1px solid rgba(78, 222, 163, 0.25);">
                                        {{ $b->interns_count }} interns
                                    </span>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 24px 0; font-size: 12px; color: #8e909f;">No active batches recorded.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Live Audit Feed --}}
                    <div class="sys-card" style="padding: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 12px; margin-bottom: 14px;">
                            <h3 style="font-size: 14px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Recent Audit Trail</h3>
                            <button type="button" wire:click="setActiveTab('logs')" style="font-size: 12px; font-weight: 700; color: #60a5fa; background: none; border: none; cursor: pointer;">View All</button>
                        </div>
                        <div class="sys-scroll" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto; padding-right: 4px;">
                            @forelse ($analytics['recentLogs'] as $log)
                                <div style="padding: 10px 12px; border-radius: 8px; background-color: #171f33; border: 1px solid #222a3d; font-size: 12px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                        <span style="font-weight: 700; color: #93c5fd;">{{ ucfirst($log->action) }}</span>
                                        <span style="font-size: 11px; color: #8e909f;">{{ $log->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <p style="margin: 0; color: #dae2fd; font-size: 12px; line-height: 1.4;">{{ $log->description ?: 'No detail' }}</p>
                                    <div style="margin-top: 4px; font-size: 11px; color: #8e909f;">
                                        Actor: {{ $log->user?->name ?: ($log->user?->email ?: 'System') }}
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 24px 0; font-size: 12px; color: #8e909f;">No audit events recorded.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- WORKSPACE 2: INTERNS COMPREHENSIVE REPORT                       --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($activeTab === 'interns')
            <div style="display: flex; flex-direction: column; gap: 16px;">
                {{-- Search & Multi-Parameter Filter Toolbar --}}
                <div class="sys-card" style="padding: 16px 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: flex-end;">
                        {{-- Search Intern --}}
                        <div style="grid-column: span 2;">
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">
                                Search Interns
                            </label>
                            <input type="text" wire:model.live.debounce.300ms="internSearch"
                                placeholder="Search by name, code, email, college, domain..."
                                class="sys-input" style="width: 100%;" />
                        </div>

                        {{-- Batch Filter --}}
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">
                                Batch
                            </label>
                            <select wire:model.live="internBatchId" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="">All Batches</option>
                                @foreach ($this->batchesList as $batch)
                                    <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Squad Filter --}}
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">
                                Project Squad
                            </label>
                            <select wire:model.live="internTeamId" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="">All Squads</option>
                                @foreach ($this->teamsList as $team)
                                    <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Domain Filter --}}
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">
                                Tech Domain
                            </label>
                            <select wire:model.live="internDomain" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="">All Domains</option>
                                @foreach ($this->domainsList as $dom)
                                    <option value="{{ $dom }}">{{ $dom }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Active Status Filter --}}
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">
                                Status
                            </label>
                            <select wire:model.live="internStatus" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="all">All Interns</option>
                                <option value="active">Active Only</option>
                                <option value="archived">Archived / Completed</option>
                            </select>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px; padding-top: 14px; border-top: 1px solid #222a3d; flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 12px; color: #8e909f;">
                            Showing filtered roster with aggregated attendance and performance telemetry.
                        </span>
                        <button type="button" wire:click="openExportModal('interns')" class="sys-btn-primary">
                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Filtered Interns (CSV)
                        </button>
                    </div>
                </div>

                {{-- Interns Table --}}
                @php
                    $interns = $this->internsReport;
                @endphp
                <div class="sys-card" style="overflow: hidden;">
                    <div class="sys-scroll" style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background-color: #171f33; border-bottom: 1px solid #222a3d;">
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Intern ID & Name</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Contact & College</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Domain & Role</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Batch & Squad</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Attendance</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Tasks Done</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Status</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($interns as $intern)
                                    @php
                                        $tot = $intern->total_attendance_count ?: 0;
                                        $pres = $intern->present_attendance_count ?: 0;
                                        $attRate = $tot > 0 ? round(($pres / $tot) * 100, 1) : 0;
                                    @endphp
                                    <tr class="sys-table-row" style="border-bottom: 1px solid #1c2638; transition: background-color 0.15s ease;">
                                        <td style="padding: 13px 16px;">
                                            <div style="font-weight: 700; color: #ffffff; font-size: 13.5px;">{{ $intern->name }}</div>
                                            <div style="font-size: 11.5px; color: #60a5fa; font-family: monospace; margin-top: 2px;">
                                                {{ $intern->intern_code ?: 'INT-' . str_pad($intern->id, 4, '0', STR_PAD_LEFT) }}
                                            </div>
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <div style="color: #dae2fd; font-size: 12.5px;">{{ $intern->email }}</div>
                                            <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">{{ $intern->college ?: 'College unrecorded' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <div style="font-weight: 600; color: #ffffff; font-size: 12.5px;">{{ $intern->domain ?: 'General' }}</div>
                                            <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">{{ $intern->internship_role ?: 'Intern' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <span style="font-size: 11.5px; font-weight: 700; color: #b8c4ff; background-color: rgba(99, 102, 241, 0.15); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(99, 102, 241, 0.25);">
                                                {{ $intern->batch?->batch_name ?: 'No Batch' }}
                                            </span>
                                            <div style="font-size: 11px; color: #8e909f; margin-top: 3px;">{{ $intern->team?->team_name ?: 'No Squad' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center;">
                                            <div style="font-weight: 800; color: #4edea3; font-size: 14px;">{{ $attRate }}%</div>
                                            <div style="font-size: 11px; color: #8e909f;">{{ $pres }} / {{ $tot }} days</div>
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center;">
                                            <div style="font-weight: 800; color: #ffffff; font-size: 14px;">{{ $intern->total_submissions_count ?: 0 }}</div>
                                            <div style="font-size: 11px; color: #fbbf24; font-weight: 700;">Grade: {{ $intern->grade ?: 'N/A' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center;">
                                            @if ($intern->is_active)
                                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.12); padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.25);">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #10b981;"></span>
                                                    Active
                                                </span>
                                            @else
                                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #8e909f; background-color: #171f33; padding: 3px 8px; border-radius: 6px; border: 1px solid #222a3d;">
                                                    Archived
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding: 13px 16px; text-align: right;">
                                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                                <button type="button" wire:click="viewInternDossier({{ $intern->id }})"
                                                    style="padding: 6px 12px; font-size: 12px; font-weight: 700; background-color: #1e40af; color: #ffffff; border: 1px solid #3b82f6; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 6px rgba(30, 64, 175, 0.3);">
                                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Dossier
                                                </button>
                                                <button type="button" wire:click="exportSingleInternCsv({{ $intern->id }})"
                                                    title="Download Single Intern CSV Dossier"
                                                    style="padding: 6px 9px; font-size: 12px; background-color: #171f33; color: #fbbf24; border: 1px solid #222a3d; border-radius: 8px; cursor: pointer;">
                                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 36px 0; color: #8e909f; font-size: 13px;">
                                            No interns match your current filter and search criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($interns->hasPages())
                        <div style="padding: 12px 18px; border-top: 1px solid #222a3d;">
                            {{ $interns->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- WORKSPACE 3: CANDIDATES & RECRUITMENT REPORT                     --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($activeTab === 'candidates')
            <div style="display: flex; flex-direction: column; gap: 16px;">
                {{-- Filter Toolbar --}}
                <div class="sys-card" style="padding: 16px 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: flex-end;">
                        <div style="grid-column: span 2;">
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Search Candidate</label>
                            <input type="text" wire:model.live.debounce.300ms="candidateSearch"
                                placeholder="Search by name, code, email, college..."
                                class="sys-input" style="width: 100%;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Application Status</label>
                            <select wire:model.live="candidateStatus" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="all">All Statuses</option>
                                <option value="applied">Applied</option>
                                <option value="interview_scheduled">Interview Scheduled</option>
                                <option value="interviewed">Interviewed</option>
                                <option value="shortlisted">Shortlisted</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Domain</label>
                            <select wire:model.live="candidateDomain" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="">All Domains</option>
                                @foreach ($this->domainsList as $dom)
                                    <option value="{{ $dom }}">{{ $dom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Min CGPA</label>
                            <input type="number" step="0.1" min="0" max="10" wire:model.live="candidateMinCgpa"
                                placeholder="e.g. 7.5" class="sys-input" style="width: 100%;" />
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px; padding-top: 14px; border-top: 1px solid #222a3d; flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 12px; color: #8e909f;">Detailed recruitment funnel telemetry and applicant interview scores.</span>
                        <button type="button" wire:click="openExportModal('candidates')" class="sys-btn-primary">
                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Candidates (CSV)
                        </button>
                    </div>
                </div>

                {{-- Candidates Table --}}
                @php
                    $candidates = $this->candidatesReport;
                @endphp
                <div class="sys-card" style="overflow: hidden;">
                    <div class="sys-scroll" style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background-color: #171f33; border-bottom: 1px solid #222a3d;">
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Code & Name</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Contact</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">College & Degree</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Domain</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">CGPA</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Duration</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Status</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: right;">Applied Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($candidates as $cand)
                                    <tr class="sys-table-row" style="border-bottom: 1px solid #1c2638; transition: background-color 0.15s ease;">
                                        <td style="padding: 13px 16px;">
                                            <div style="font-weight: 700; color: #ffffff; font-size: 13.5px;">{{ $cand->name }}</div>
                                            <div style="font-size: 11.5px; color: #fbbf24; font-family: monospace; margin-top: 2px;">{{ $cand->application_code }}</div>
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <div style="color: #dae2fd; font-size: 12.5px;">{{ $cand->email }}</div>
                                            <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">{{ $cand->phone ?: 'No phone' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <div style="color: #ffffff; font-size: 12.5px;">{{ $cand->college }}</div>
                                            <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">{{ $cand->degree ?: 'N/A' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px; font-weight: 600; color: #ffffff; font-size: 12.5px;">
                                            {{ $cand->domain ?: 'General' }}
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center; font-weight: 800; color: #ffffff; font-size: 13.5px;">
                                            {{ $cand->cgpa ? number_format($cand->cgpa, 2) : 'N/A' }}
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center; color: #8e909f; font-size: 12px;">
                                            {{ $cand->duration }} {{ $cand->duration_unit }}
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center;">
                                            @php
                                                $candBadge = match($cand->status) {
                                                    'shortlisted' => 'background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3);',
                                                    'rejected' => 'background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);',
                                                    'interview_scheduled', 'interviewed' => 'background-color: rgba(6, 182, 212, 0.15); color: #67e8f9; border: 1px solid rgba(6, 182, 212, 0.3);',
                                                    default => 'background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);',
                                                };
                                            @endphp
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; {{ $candBadge }}">
                                                {{ ucfirst(str_replace('_', ' ', $cand->status)) }}
                                            </span>
                                        </td>
                                        <td style="padding: 13px 16px; text-align: right; color: #8e909f; font-size: 12px;">
                                            {{ $cand->created_at?->format('d M Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 36px 0; color: #8e909f; font-size: 13px;">
                                            No candidates found matching the criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($candidates->hasPages())
                        <div style="padding: 12px 18px; border-top: 1px solid #222a3d;">
                            {{ $candidates->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- WORKSPACE 4: TASKS & PERFORMANCE REPORT                         --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($activeTab === 'tasks')
            <div style="display: flex; flex-direction: column; gap: 16px;">
                {{-- Filter Toolbar --}}
                <div class="sys-card" style="padding: 16px 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; align-items: flex-end;">
                        <div style="grid-column: span 2;">
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Search Task or Intern</label>
                            <input type="text" wire:model.live.debounce.300ms="taskSearch"
                                placeholder="Search by task title, intern name, intern code..."
                                class="sys-input" style="width: 100%;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Priority</label>
                            <select wire:model.live="taskPriority" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="all">All Priorities</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Submission Status</label>
                            <select wire:model.live="submissionStatus" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="all">All Statuses</option>
                                <option value="submitted">Submitted</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="revision">Revision</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px; padding-top: 14px; border-top: 1px solid #222a3d; flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 12px; color: #8e909f;">Task delivery timeline, awarded marks, grades, and turnaround records.</span>
                        <button type="button" wire:click="openExportModal('tasks')" class="sys-btn-primary">
                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Tasks (CSV)
                        </button>
                    </div>
                </div>

                {{-- Tasks Submissions Table --}}
                @php
                    $tasks = $this->tasksReport;
                @endphp
                <div class="sys-card" style="overflow: hidden;">
                    <div class="sys-scroll" style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background-color: #171f33; border-bottom: 1px solid #222a3d;">
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Task Title & Priority</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Intern & Batch</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Due Date</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Submitted At</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Status</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Marks & Grade</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tasks as $sub)
                                    <tr class="sys-table-row" style="border-bottom: 1px solid #1c2638; transition: background-color 0.15s ease;">
                                        <td style="padding: 13px 16px;">
                                            <div style="font-weight: 700; color: #ffffff; font-size: 13.5px;">
                                                {{ $sub->task?->title ?: 'Task #' . $sub->task_id }}
                                            </div>
                                            <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; {{ $sub->task?->priority === 'high' ? 'background-color: rgba(239, 68, 68, 0.15); color: #f87171;' : 'background-color: #171f33; color: #8e909f;' }}">
                                                {{ $sub->task?->priority ?: 'Normal' }} Priority
                                            </span>
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <div style="font-weight: 600; color: #ffffff; font-size: 12.5px;">{{ $sub->intern?->name ?: 'N/A' }}</div>
                                            <div style="font-size: 11.5px; color: #60a5fa; font-family: monospace; margin-top: 2px;">{{ $sub->intern?->intern_code }}</div>
                                        </td>
                                        <td style="padding: 13px 16px; color: #dae2fd; font-size: 12px;">
                                            {{ $sub->task?->due_date?->format('d M Y') ?: 'No due date' }}
                                        </td>
                                        <td style="padding: 13px 16px; color: #8e909f; font-size: 12px;">
                                            {{ $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y, h:i A') : 'N/A' }}
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center;">
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.25);">
                                                {{ ucfirst($sub->status) }}
                                            </span>
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center;">
                                            <div style="font-weight: 800; color: #ffffff; font-size: 14px;">{{ $sub->marks !== null ? $sub->marks : 'Pending' }}</div>
                                            <div style="font-size: 11px; color: #fbbf24; font-weight: 700;">{{ $sub->grade ?: '-' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px; color: #8e909f; font-size: 12px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $sub->admin_feedback ?: 'No feedback given' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 36px 0; color: #8e909f; font-size: 13px;">
                                            No task submissions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($tasks->hasPages())
                        <div style="padding: 12px 18px; border-top: 1px solid #222a3d;">
                            {{ $tasks->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- WORKSPACE 5: ATTENDANCE & MUSTER ROLL REPORT                    --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($activeTab === 'attendance')
            <div style="display: flex; flex-direction: column; gap: 16px;">
                {{-- Filter Toolbar --}}
                <div class="sys-card" style="padding: 16px 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: flex-end;">
                        <div style="grid-column: span 2;">
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Search Intern</label>
                            <input type="text" wire:model.live.debounce.300ms="attendanceSearch"
                                placeholder="Search by intern name, intern code..."
                                class="sys-input" style="width: 100%;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Batch</label>
                            <select wire:model.live="attendanceBatchId" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="">All Batches</option>
                                @foreach ($this->batchesList as $batch)
                                    <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Status</label>
                            <select wire:model.live="attendanceStatus" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="all">All Statuses</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="leave">Leave / Excused</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Date Filter</label>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <input type="date" wire:model.live="attendanceStartDate" class="sys-input" style="width: 100%; color-scheme: dark;" />
                                <span style="font-size: 12px; color: #8e909f;">-</span>
                                <input type="date" wire:model.live="attendanceEndDate" class="sys-input" style="width: 100%; color-scheme: dark;" />
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px; padding-top: 14px; border-top: 1px solid #222a3d; flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 12px; color: #8e909f;">Muster roll register with daily check-ins, punch records, and statuses.</span>
                        <button type="button" wire:click="openExportModal('attendance')" class="sys-btn-primary">
                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Attendance (CSV)
                        </button>
                    </div>
                </div>

                {{-- Attendance Table --}}
                @php
                    $attendances = $this->attendanceReport;
                @endphp
                <div class="sys-card" style="overflow: hidden;">
                    <div class="sys-scroll" style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background-color: #171f33; border-bottom: 1px solid #222a3d;">
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Date</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Intern Code & Name</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Batch & Squad</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Status</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Note / Remarks</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: right;">Logged At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($attendances as $att)
                                    <tr class="sys-table-row" style="border-bottom: 1px solid #1c2638; transition: background-color 0.15s ease;">
                                        <td style="padding: 13px 16px; font-family: monospace; font-weight: 700; color: #ffffff; font-size: 13px;">
                                            {{ $att->date }}
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <div style="font-weight: 700; color: #ffffff; font-size: 13.5px;">{{ $att->intern?->name ?: 'N/A' }}</div>
                                            <div style="font-size: 11.5px; color: #60a5fa; font-family: monospace; margin-top: 2px;">{{ $att->intern?->intern_code }}</div>
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <div style="color: #dae2fd; font-size: 12px;">{{ $att->intern?->batch?->batch_name ?: 'No Batch' }}</div>
                                            <div style="font-size: 11px; color: #8e909f; margin-top: 2px;">{{ $att->intern?->team?->team_name ?: 'No Squad' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center;">
                                            @php
                                                $attBadge = match($att->status) {
                                                    'present' => 'background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3);',
                                                    'absent' => 'background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);',
                                                    'late' => 'background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);',
                                                    'leave' => 'background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);',
                                                    default => 'background-color: #171f33; color: #8e909f; border: 1px solid #222a3d;',
                                                };
                                            @endphp
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; {{ $attBadge }}">
                                                {{ ucfirst($att->status) }}
                                            </span>
                                        </td>
                                        <td style="padding: 13px 16px; color: #8e909f; font-size: 12px;">
                                            {{ $att->note ?: '-' }}
                                        </td>
                                        <td style="padding: 13px 16px; text-align: right; color: #8e909f; font-size: 12px;">
                                            {{ $att->created_at?->format('d M Y, h:i A') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 36px 0; color: #8e909f; font-size: 13px;">
                                            No attendance entries match your filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($attendances->hasPages())
                        <div style="padding: 12px 18px; border-top: 1px solid #222a3d;">
                            {{ $attendances->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- WORKSPACE 6: SYSTEM ACTIVITY LOGS & AUDIT TRAIL                 --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($activeTab === 'logs')
            <div style="display: flex; flex-direction: column; gap: 16px;">
                {{-- Filter Toolbar --}}
                <div class="sys-card" style="padding: 16px 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; align-items: flex-end;">
                        <div style="grid-column: span 2;">
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Search Logs</label>
                            <input type="text" wire:model.live.debounce.300ms="logSearch"
                                placeholder="Search by description, action, user email..."
                                class="sys-input" style="width: 100%;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Action</label>
                            <select wire:model.live="logAction" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="all">All Actions</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                                <option value="login">Login / Auth</option>
                                <option value="evaluated">Evaluated</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Actor / Admin</label>
                            <select wire:model.live="logUserId" class="sys-input" style="width: 100%; cursor: pointer;">
                                <option value="">All Users / System</option>
                                @foreach ($this->usersList as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px; padding-top: 14px; border-top: 1px solid #222a3d; flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 12px; color: #8e909f;">Chronological immutable audit log of all system changes and administrative operations.</span>
                        <button type="button" wire:click="openExportModal('logs')" class="sys-btn-primary">
                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Audit Logs (CSV)
                        </button>
                    </div>
                </div>

                {{-- Activity Logs Table --}}
                @php
                    $logs = $this->logsReport;
                @endphp
                <div class="sys-card" style="overflow: hidden;">
                    <div class="sys-scroll" style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background-color: #171f33; border-bottom: 1px solid #222a3d;">
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Timestamp</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Actor / Admin</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; text-align: center;">Action</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Entity Type & ID</th>
                                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr class="sys-table-row" style="border-bottom: 1px solid #1c2638; transition: background-color 0.15s ease;">
                                        <td style="padding: 13px 16px; color: #dae2fd; font-family: monospace; font-size: 12px;">
                                            {{ $log->created_at?->format('d M Y, h:i:s A') }}
                                        </td>
                                        <td style="padding: 13px 16px;">
                                            <div style="font-weight: 700; color: #ffffff; font-size: 13px;">{{ $log->user?->name ?: 'System Engine' }}</div>
                                            <div style="font-size: 11px; color: #8e909f;">{{ $log->user?->email ?: 'Internal Process' }}</div>
                                        </td>
                                        <td style="padding: 13px 16px; text-align: center;">
                                            @php
                                                $actionBadge = match(strtolower($log->action)) {
                                                    'created' => 'background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3);',
                                                    'updated' => 'background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);',
                                                    'deleted' => 'background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);',
                                                    default => 'background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);',
                                                };
                                            @endphp
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; {{ $actionBadge }}">
                                                {{ ucfirst($log->action) }}
                                            </span>
                                        </td>
                                        <td style="padding: 13px 16px; font-size: 12px; color: #dae2fd;">
                                            <span style="font-weight: 700; color: #ffffff;">{{ class_basename($log->subject_type ?? 'N/A') }}</span>
                                            @if ($log->subject_id)
                                                <span style="font-family: monospace; color: #60a5fa; margin-left: 4px;">#{{ $log->subject_id }}</span>
                                            @endif
                                        </td>
                                        <td style="padding: 13px 16px; font-size: 12px; color: #dae2fd;">
                                            {{ $log->description ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 36px 0; color: #8e909f; font-size: 13px;">
                                            No activity logs recorded.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($logs->hasPages())
                        <div style="padding: 12px 18px; border-top: 1px solid #222a3d;">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- MODAL 1: CUSTOMIZABLE CSV EXPORT BUILDER                         --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($showExportModal)
            <div style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px; background-color: rgba(11, 19, 38, 0.85); backdrop-filter: blur(6px);">
                <div class="sys-card" style="width: 100%; max-width: 680px; max-height: 90vh; border: 1.5px solid #222a3d; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5); display: flex; flex-direction: column; overflow: hidden;">
                    {{-- Modal Header --}}
                    <div style="padding: 18px 22px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; background-color: #171f33;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background-color: rgba(30, 64, 175, 0.2); color: #60a5fa; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(59, 130, 246, 0.3);">
                                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0;">
                                    Custom CSV Export Builder
                                </h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0;">
                                    Select the exact columns to export for <strong style="color: #60a5fa;">{{ ucfirst($exportDataset) }}</strong>.
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeExportModal"
                            style="background: none; border: none; color: #8e909f; cursor: pointer; padding: 4px; border-radius: 6px;">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Target Dataset Switcher --}}
                    <div style="padding: 12px 22px; background-color: #131b2e; border-bottom: 1px solid #222a3d; display: flex; align-items: center; gap: 6px; overflow-x: auto;">
                        @foreach (['interns' => 'Interns Roster', 'candidates' => 'Candidates', 'tasks' => 'Tasks', 'attendance' => 'Attendance', 'logs' => 'Audit Logs'] as $key => $lbl)
                            <button type="button" wire:click="openExportModal('{{ $key }}')"
                                style="padding: 6px 12px; font-size: 12px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $exportDataset === $key ? 'background-color: #1e40af; color: #ffffff;' : 'background-color: #171f33; color: #8e909f;' }}">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Quick Action Ribbon --}}
                    <div style="padding: 10px 22px; background-color: #0b1326; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; font-size: 12px;">
                        <span style="color: #8e909f;">
                            Columns Selected: <strong style="color: #60a5fa;">{{ count($selectedColumns) }}</strong>
                        </span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" wire:click="selectAllColumns" style="color: #60a5fa; font-weight: 700; background: none; border: none; cursor: pointer;">Select All</button>
                            <span style="color: #4b5563;">•</span>
                            <button type="button" wire:click="deselectAllColumns" style="color: #8e909f; background: none; border: none; cursor: pointer;">Deselect All</button>
                        </div>
                    </div>

                    {{-- Checkboxes Body --}}
                    <div class="sys-scroll" style="padding: 18px 22px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 14px;">
                        @php
                            $categories = $this->availableColumns[$exportDataset] ?? [];
                        @endphp
                        @foreach ($categories as $catName => $columns)
                            <div class="sys-card-inner" style="padding: 14px;">
                                <h4 style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 10px; display: flex; align-items: center; gap: 6px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #3b82f6;"></span>
                                    {{ $catName }}
                                </h4>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                    @foreach ($columns as $colKey => $colLabel)
                                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #dae2fd; cursor: pointer; user-select: none;">
                                            <input type="checkbox" value="{{ $colKey }}" wire:model.live="selectedColumns"
                                                style="accent-color: #1e40af; width: 16px; height: 16px; cursor: pointer;" />
                                            <span>{{ $colLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 14px 22px; border-top: 1px solid #222a3d; background-color: #171f33; display: flex; align-items: center; justify-content: space-between;">
                        <button type="button" wire:click="closeExportModal" class="sys-btn-secondary">
                            Cancel
                        </button>
                        <button type="button" wire:click="exportCsv" class="sys-btn-primary">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download CSV Report
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- MODAL 2: INDIVIDUAL INTERN DOSSIER INSPECTOR                     --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($showDossierModal && $this->selectedIntern)
            @php
                $intern = $this->selectedIntern;
                $metrics = $this->selectedInternMetrics;
            @endphp
            <div style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px; background-color: rgba(11, 19, 38, 0.85); backdrop-filter: blur(6px);">
                <div class="sys-card" style="width: 100%; max-width: 820px; max-height: 92vh; border: 1.5px solid #222a3d; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5); display: flex; flex-direction: column; overflow: hidden;">
                    {{-- Dossier Header --}}
                    <div style="padding: 18px 22px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; background-color: #171f33;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 800; font-size: 18px; border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 4px 12px rgba(30, 64, 175, 0.4);">
                                {{ strtoupper(substr($intern->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <h2 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">{{ $intern->name }}</h2>
                                    @if ($intern->is_active)
                                        <span style="font-size: 10.5px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); padding: 2px 7px; border-radius: 6px; border: 1px solid rgba(78, 222, 163, 0.25);">Active</span>
                                    @else
                                        <span style="font-size: 10.5px; font-weight: 700; color: #8e909f; background-color: #131b2e; padding: 2px 7px; border-radius: 6px; border: 1px solid #222a3d;">Archived</span>
                                    @endif
                                </div>
                                <div style="font-size: 12px; color: #60a5fa; font-family: monospace; margin-top: 2px;">
                                    {{ $intern->intern_code ?: 'INT-' . str_pad($intern->id, 4, '0', STR_PAD_LEFT) }} • {{ $intern->email }}
                                </div>
                                <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">
                                    Batch: {{ $intern->batch?->batch_name ?: 'No Batch' }} • Squad: {{ $intern->team?->team_name ?: 'No Squad' }}
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" wire:click="exportSingleInternCsv({{ $intern->id }})" class="sys-btn-primary">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export CSV
                            </button>
                            <button type="button" wire:click="closeInternDossier"
                                style="background: none; border: none; color: #8e909f; cursor: pointer; padding: 6px; border-radius: 6px;">
                                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Quick Dossier KPI Strip --}}
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; padding: 14px 22px; background-color: #0b1326; border-bottom: 1px solid #222a3d;">
                        <div class="sys-card-inner" style="padding: 10px 14px;">
                            <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Attendance Rate</span>
                            <div style="font-size: 18px; font-weight: 800; color: #4edea3; margin-top: 2px;">{{ $metrics['attendancePct'] }}%</div>
                            <span style="font-size: 11px; color: #8e909f;">{{ $metrics['presentAttendance'] }}/{{ $metrics['totalAttendance'] }} days</span>
                        </div>
                        <div class="sys-card-inner" style="padding: 10px 14px;">
                            <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Tasks Submitted</span>
                            <div style="font-size: 18px; font-weight: 800; color: #ffffff; margin-top: 2px;">{{ $metrics['totalSubmissions'] }}</div>
                            <span style="font-size: 11px; color: #b8c4ff;">{{ $metrics['evaluatedSubmissions'] }} evaluated</span>
                        </div>
                        <div class="sys-card-inner" style="padding: 10px 14px;">
                            <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Average Score</span>
                            <div style="font-size: 18px; font-weight: 800; color: #ffffff; margin-top: 2px;">{{ $metrics['avgScore'] }}</div>
                            <span style="font-size: 11px; color: #fbbf24; font-weight: 700;">Grade: {{ $intern->grade ?: 'N/A' }}</span>
                        </div>
                        <div class="sys-card-inner" style="padding: 10px 14px;">
                            <span style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Academic CGPA</span>
                            <div style="font-size: 18px; font-weight: 800; color: #ffffff; margin-top: 2px;">{{ $intern->cgpa ? number_format($intern->cgpa, 2) : 'N/A' }}</div>
                            <span style="font-size: 11px; color: #8e909f;">{{ $intern->degree ?: 'Degree' }}</span>
                        </div>
                    </div>

                    {{-- Dossier Sub-Tabs Bar --}}
                    <div style="padding: 10px 22px 0; background-color: #131b2e; border-bottom: 1px solid #222a3d; display: flex; align-items: center; gap: 8px;">
                        <button type="button" wire:click="setDossierTab('overview')"
                            style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 8px 8px 0 0; border: none; cursor: pointer; transition: all 0.15s ease; {{ $dossierTab === 'overview' ? 'background-color: #171f33; color: #60a5fa; border-top: 2px solid #3b82f6;' : 'background-color: transparent; color: #8e909f;' }}">
                            Overview & Profile
                        </button>
                        <button type="button" wire:click="setDossierTab('attendance')"
                            style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 8px 8px 0 0; border: none; cursor: pointer; transition: all 0.15s ease; {{ $dossierTab === 'attendance' ? 'background-color: #171f33; color: #60a5fa; border-top: 2px solid #3b82f6;' : 'background-color: transparent; color: #8e909f;' }}">
                            Attendance History ({{ $metrics['totalAttendance'] }})
                        </button>
                        <button type="button" wire:click="setDossierTab('tasks')"
                            style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 8px 8px 0 0; border: none; cursor: pointer; transition: all 0.15s ease; {{ $dossierTab === 'tasks' ? 'background-color: #171f33; color: #60a5fa; border-top: 2px solid #3b82f6;' : 'background-color: transparent; color: #8e909f;' }}">
                            Tasks & Submissions ({{ $metrics['totalSubmissions'] }})
                        </button>
                        <button type="button" wire:click="setDossierTab('credentials')"
                            style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 8px 8px 0 0; border: none; cursor: pointer; transition: all 0.15s ease; {{ $dossierTab === 'credentials' ? 'background-color: #171f33; color: #60a5fa; border-top: 2px solid #3b82f6;' : 'background-color: transparent; color: #8e909f;' }}">
                            Credentials & Documents
                        </button>
                    </div>

                    {{-- Dossier Content Body --}}
                    <div class="sys-scroll" style="padding: 18px 22px; overflow-y: auto; flex: 1;">
                        {{-- Overview Tab --}}
                        @if ($dossierTab === 'overview')
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                {{-- Personal & Academic Card --}}
                                <div class="sys-card-inner" style="padding: 16px;">
                                    <h4 style="font-size: 12px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px; padding-bottom: 8px; border-bottom: 1px solid #222a3d;">
                                        Personal & Academic Details
                                    </h4>
                                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12.5px;">
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Full Name</span>
                                            <strong style="color: #ffffff;">{{ $intern->name }}</strong>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Phone</span>
                                            <span style="color: #ffffff;">{{ $intern->phone ?: 'N/A' }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">System Username</span>
                                            <span style="color: #60a5fa; font-family: monospace;">{{ $intern->username }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Plain Password</span>
                                            <span style="color: #ffffff; font-family: monospace;">{{ $intern->plain_password }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">College</span>
                                            <span style="color: #ffffff;">{{ $intern->college ?: 'N/A' }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">University</span>
                                            <span style="color: #ffffff;">{{ $intern->university ?: 'N/A' }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Degree & Year</span>
                                            <span style="color: #ffffff;">{{ $intern->degree ?: 'N/A' }} ({{ $intern->academic_year ?: 'N/A' }})</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span style="color: #8e909f;">Skills</span>
                                            <span style="color: #ffffff; max-width: 200px; text-align: right;">{{ $intern->skills ?: 'None listed' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Internship Squad & Tenure Card --}}
                                <div class="sys-card-inner" style="padding: 16px;">
                                    <h4 style="font-size: 12px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px; padding-bottom: 8px; border-bottom: 1px solid #222a3d;">
                                        Internship Assignment & Squad
                                    </h4>
                                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12.5px;">
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Domain</span>
                                            <strong style="color: #ffffff;">{{ $intern->domain ?: 'General' }}</strong>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Assigned Role</span>
                                            <span style="color: #ffffff;">{{ $intern->internship_role ?: 'Intern' }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Batch</span>
                                            <span style="color: #ffffff;">{{ $intern->batch?->batch_name ?: 'Unassigned' }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Project Squad</span>
                                            <span style="color: #ffffff;">{{ $intern->team?->team_name ?: 'Unassigned' }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Assigned Project</span>
                                            <span style="color: #ffffff;">{{ $intern->assigned_project_name }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 6px;">
                                            <span style="color: #8e909f;">Joining Date</span>
                                            <span style="color: #ffffff;">{{ $intern->joining_date?->format('d M Y') ?: 'N/A' }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span style="color: #8e909f;">Completion Date</span>
                                            <span style="color: #ffffff;">{{ $intern->completion_date?->format('d M Y') ?: 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Attendance History Tab --}}
                        @if ($dossierTab === 'attendance')
                            <div class="sys-card-inner" style="overflow: hidden;">
                                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12.5px;">
                                    <thead>
                                        <tr style="background-color: #131b2e; border-bottom: 1px solid #222a3d;">
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f;">Date</th>
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f; text-align: center;">Status</th>
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f;">Note / Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($intern->attendances as $att)
                                            <tr style="border-bottom: 1px solid #1c2638;">
                                                <td style="padding: 10px 14px; font-family: monospace; font-weight: 700; color: #ffffff;">
                                                    {{ $att->date }}
                                                </td>
                                                <td style="padding: 10px 14px; text-align: center;">
                                                    @php
                                                        $attBadge = match($att->status) {
                                                            'present' => 'background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3);',
                                                            'absent' => 'background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);',
                                                            'late' => 'background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);',
                                                            'leave' => 'background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);',
                                                            default => 'background-color: #171f33; color: #8e909f;',
                                                        };
                                                    @endphp
                                                    <span style="display: inline-block; padding: 2px 7px; border-radius: 5px; font-size: 11px; font-weight: 700; {{ $attBadge }}">
                                                        {{ ucfirst($att->status) }}
                                                    </span>
                                                </td>
                                                <td style="padding: 10px 14px; color: #8e909f;">{{ $att->note ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" style="text-align: center; padding: 24px; color: #8e909f;">No attendance records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Tasks Tab --}}
                        @if ($dossierTab === 'tasks')
                            <div class="sys-card-inner" style="overflow: hidden;">
                                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12.5px;">
                                    <thead>
                                        <tr style="background-color: #131b2e; border-bottom: 1px solid #222a3d;">
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f;">Task Title</th>
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f;">Due Date</th>
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f;">Submitted</th>
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f; text-align: center;">Marks</th>
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f; text-align: center;">Grade</th>
                                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8e909f;">Feedback</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($intern->submissions as $sub)
                                            <tr style="border-bottom: 1px solid #1c2638;">
                                                <td style="padding: 10px 14px; font-weight: 700; color: #ffffff;">{{ $sub->task?->title ?: 'Task #' . $sub->task_id }}</td>
                                                <td style="padding: 10px 14px; color: #8e909f;">{{ $sub->task?->due_date?->format('d M Y') ?: '-' }}</td>
                                                <td style="padding: 10px 14px; color: #8e909f;">{{ $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y') : '-' }}</td>
                                                <td style="padding: 10px 14px; text-align: center; font-weight: 800; color: #ffffff;">{{ $sub->marks !== null ? $sub->marks : '-' }}</td>
                                                <td style="padding: 10px 14px; text-align: center; font-weight: 800; color: #fbbf24;">{{ $sub->grade ?: '-' }}</td>
                                                <td style="padding: 10px 14px; color: #8e909f; font-size: 11.5px;">{{ $sub->admin_feedback ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="text-align: center; padding: 24px; color: #8e909f;">No task submissions found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Credentials Tab --}}
                        @if ($dossierTab === 'credentials')
                            <div class="sys-card-inner" style="padding: 18px;">
                                <h4 style="font-size: 12px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px; padding-bottom: 8px; border-bottom: 1px solid #222a3d;">
                                    Official Credentials & Verification
                                </h4>
                                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 8px;">
                                        <span style="color: #8e909f;">Certificate Reference ID</span>
                                        <span style="font-family: monospace; font-weight: 700; color: #60a5fa;">{{ $intern->cert_ref_id ?: 'Not yet issued' }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 8px;">
                                        <span style="color: #8e909f;">Completion Letter Reference ID</span>
                                        <span style="font-family: monospace; font-weight: 700; color: #60a5fa;">{{ $intern->letter_ref_id ?: 'Not yet issued' }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #1c2638; padding-bottom: 8px;">
                                        <span style="color: #8e909f;">Public Verification Hash Token</span>
                                        <span style="font-family: monospace; color: #dae2fd; font-size: 12px;">{{ $intern->cert_token ?: 'None generated' }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between;">
                                        <span style="color: #8e909f;">Offer Letter Status</span>
                                        <span style="color: #ffffff;">
                                            {{ $intern->offerletter?->offer_status ? ucfirst($intern->offerletter->offer_status) : 'N/A' }} 
                                            ({{ $intern->offerletter?->is_accepted ? 'Accepted' : 'Pending' }})
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
