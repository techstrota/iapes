<x-filament-panels::page>
    @php
        $internsList = $this->getViewData()['interns'];
        $data = $this->getViewData();
        $totalRosterCount = $data['totalRosterCount'];
        $activeRosterCount = $data['activeRosterCount'];
        $archivedRosterCount = $data['archivedRosterCount'];
        $filteredCount = $data['filteredCount'];
        $avgAttendance = $data['avgAttendance'];
        $totalAssignedTasks = $data['totalAssignedTasks'];
        $totalCompletedTasks = $data['totalCompletedTasks'];
        $overallTaskRate = $data['overallTaskRate'];
        $totalLogbooks = $data['totalLogbooksSubmitted'];
        $archiveCycles = $data['archiveCycles'];
        $batches = $data['batches'];
        $teams = $data['teams'];
        $domains = $data['domains'];
        $colDefs = $data['columnDefinitions'];
        $detailIntern = $data['detailIntern'];
    @endphp

    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', Helvetica, Arial, sans-serif; color: #dae2fd; max-width: 100%; margin: 0 auto;">

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- Premium Executive Styling & Micro-Interactions                    --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <style>
            .exec-card {
                background: linear-gradient(145deg, rgba(19, 27, 46, 0.95) 0%, rgba(11, 19, 38, 0.98) 100%);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 16px;
                box-shadow: 0 10px 30px -6px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.06);
                transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            }
            .exec-card:hover {
                border-color: rgba(59, 130, 246, 0.3);
                box-shadow: 0 12px 36px -6px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            }
            .exec-input {
                background: #171f33 !important;
                border: 1px solid #222a3d !important;
                border-radius: 10px !important;
                padding: 8px 12px !important;
                font-size: 13px !important;
                color: #dae2fd !important;
                outline: none !important;
                transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
            }
            .exec-input:focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
            }
            .exec-btn-primary {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
                color: #ffffff;
                padding: 9px 18px;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 700;
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow: 0 4px 16px rgba(30, 64, 175, 0.4);
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .exec-btn-primary:hover {
                background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
                transform: translateY(-1px);
                box-shadow: 0 6px 20px rgba(30, 64, 175, 0.6);
            }
            .exec-btn-secondary {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #171f33;
                color: #cbd5e1;
                padding: 8px 14px;
                border-radius: 9px;
                font-size: 12px;
                font-weight: 600;
                border: 1px solid #222a3d;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .exec-btn-secondary:hover {
                background: #1f2b47;
                color: #ffffff;
                border-color: #3b82f6;
            }
            .exec-table th {
                background-color: #0d1527;
                color: #8e909f;
                font-size: 11.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 12px 14px;
                border-bottom: 1px solid #222a3d;
                white-space: nowrap;
            }
            .exec-table td {
                padding: 12px 14px;
                border-bottom: 1px solid #1a2337;
                font-size: 13px;
                vertical-align: middle;
            }
            .exec-table tr:hover td {
                background-color: rgba(30, 64, 175, 0.08);
            }
            .pill-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 3px 9px;
                border-radius: 6px;
                font-size: 11.5px;
                font-weight: 700;
                line-height: 1.3;
            }
            .progress-bar-bg {
                background: #171f33;
                border-radius: 9999px;
                overflow: hidden;
                height: 6px;
                width: 100%;
                border: 1px solid #222a3d;
            }
            .progress-bar-fill {
                height: 100%;
                border-radius: 9999px;
                transition: width 0.4s ease;
            }
        </style>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 1. Report Header & Action Bar                                     --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="exec-card" style="padding: 22px 26px; margin-bottom: 22px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(30,64,175,0.4) 0%, rgba(59,130,246,0.15) 100%); border: 1px solid rgba(59,130,246,0.4); display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 4px 18px rgba(30,64,175,0.3);">
                        📊
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                                Comprehensive Interns Intelligence & Analytics Report
                            </h1>
                            <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3);">
                                🟢 Live Telemetry
                            </span>
                        </div>
                        <p style="font-size: 13px; color: #8e909f; margin: 4px 0 0;">
                            360° holistic view of intern completion, attendance metrics, task submissions, academic background, and cohort archives.
                        </p>
                    </div>
                </div>

                {{-- Action Buttons & Custom Date Selection --}}
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    {{-- Custom Date Selection Filter --}}
                    <div style="display: flex; align-items: center; gap: 6px; background: #0b1326; border: 1.5px solid #222a3d; border-radius: 10px; padding: 4px 10px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; display: flex; align-items: center; gap: 4px;">
                            📅 Date:
                        </span>
                        <select wire:model.live="dateField" style="background: transparent; border: none; color: #60a5fa; font-size: 12px; font-weight: 700; cursor: pointer; outline: none; padding: 2px 4px;">
                            <option value="joining_date" style="background: #0f172a; color: #ffffff;">Joining Date</option>
                            <option value="completion_date" style="background: #0f172a; color: #ffffff;">Completion Date</option>
                            <option value="created_at" style="background: #0f172a; color: #ffffff;">Enrolled Date</option>
                        </select>
                        <input
                            type="date"
                            wire:model.live="customStartDate"
                            class="exec-input"
                            style="padding: 4px 8px; font-size: 12px; width: 130px; height: 34px;"
                            title="Start Date"
                        />
                        <span style="color: #8e909f; font-size: 11px; font-weight: 600;">to</span>
                        <input
                            type="date"
                            wire:model.live="customEndDate"
                            class="exec-input"
                            style="padding: 4px 8px; font-size: 12px; width: 130px; height: 34px;"
                            title="End Date"
                        />
                        @if ($customStartDate || $customEndDate)
                            <button
                                type="button"
                                wire:click="$set('customStartDate', null); $set('customEndDate', null);"
                                style="background: transparent; border: none; color: #ffb4ab; cursor: pointer; font-size: 13px; padding: 0 4px;"
                                title="Clear date filter">
                                ✕
                            </button>
                        @endif
                    </div>

                    <button
                        type="button"
                        wire:click="openExportModal"
                        class="exec-btn-primary">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Download CSV Report</span>
                    </button>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- Top Archival & Scope Switcher Tabs                           --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div style="margin-top: 20px; padding-top: 18px; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 12px; padding: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'all')"
                        style="padding: 7px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeTab === 'all' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        🌐 All Lifetime Interns ({{ $totalRosterCount }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'active')"
                        style="padding: 7px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeTab === 'active' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        ⚡ Current Active ({{ $activeRosterCount }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'archived')"
                        style="padding: 7px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeTab === 'archived' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        📁 Completed / Archived ({{ $archivedRosterCount }})
                    </button>
                </div>

                {{-- Archive Cycle Filter if available --}}
                @if (!empty($archiveCycles))
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #8e909f;">Archive Cohort Cycle:</span>
                        <select
                            wire:model.live="selectedCycle"
                            class="exec-input"
                            style="cursor: pointer; padding: 6px 12px !important; font-size: 12px !important;">
                            <option value="">All Cycles (Lifetime)</option>
                            @foreach ($archiveCycles as $cycle)
                                <option value="{{ $cycle }}">{{ $cycle }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 2. Executive KPI Metrics Cards (Top Dashboard Overview)           --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 22px;">
            {{-- KPI 1: Roster Scope --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        Interns In Scope
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59,130,246,0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        👥
                    </span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                    {{ $filteredCount }}
                </div>
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #4edea3; font-weight: 700;">🟢 {{ $data['filteredActiveCount'] }} Active</span>
                    <span>•</span>
                    <span style="color: #c084fc; font-weight: 700;">🎓 {{ $data['filteredArchivedCount'] }} Completed</span>
                </div>
            </div>

            {{-- KPI 2: Overall Attendance Rate --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        Avg. Attendance Rate
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16,185,129,0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        📅
                    </span>
                </div>
                <div style="display: flex; align-items: baseline; gap: 8px;">
                    <div style="font-size: 28px; font-weight: 800; color: {{ $avgAttendance >= 90 ? '#4edea3' : ($avgAttendance >= 75 ? '#fbbf24' : '#ffb4ab') }}; line-height: 1.1;">
                        {{ $avgAttendance }}%
                    </div>
                    <span style="font-size: 11px; font-weight: 600; color: #8e909f;">Overall</span>
                </div>
                <div style="margin-top: 10px;">
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: {{ min(100, $avgAttendance) }}%; background: {{ $avgAttendance >= 90 ? '#10b981' : ($avgAttendance >= 75 ? '#f59e0b' : '#ef4444') }};"></div>
                    </div>
                </div>
            </div>

            {{-- KPI 3: Task Completion Rate --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        Task Completion Rate
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(147,51,234,0.15); color: #d8b4fe; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        🎯
                    </span>
                </div>
                <div style="display: flex; align-items: baseline; gap: 8px;">
                    <div style="font-size: 28px; font-weight: 800; color: {{ $overallTaskRate >= 80 ? '#4edea3' : ($overallTaskRate >= 50 ? '#93c5fd' : '#fde68a') }}; line-height: 1.1;">
                        {{ $overallTaskRate }}%
                    </div>
                    <span style="font-size: 11px; font-weight: 600; color: #8e909f;">
                        ({{ $totalCompletedTasks }} / {{ $totalAssignedTasks }} Done)
                    </span>
                </div>
                <div style="margin-top: 10px;">
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: {{ min(100, $overallTaskRate) }}%; background: #9333ea;"></div>
                    </div>
                </div>
            </div>

            {{-- KPI 4: Daily Logbook Entries --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        Daily Logbook Activity
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245,158,11,0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        📖
                    </span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                    {{ number_format($totalLogbooks) }}
                </div>
                <div style="margin-top: 8px; font-size: 11.5px; color: #fbbf24; font-weight: 600;">
                    Total daily tasks recorded
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 3. Comprehensive Filter Toolbar                                   --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="exec-card" style="padding: 16px 20px; margin-bottom: 22px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px; flex-grow: 1;">
                    {{-- Search Input --}}
                    <div style="position: relative; width: 260px; max-width: 100%;">
                        <div style="position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #8e909f; pointer-events: none; display: flex; align-items: center;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search name, TS code, college..."
                            class="exec-input"
                            style="width: 100%; padding-left: 36px !important;"
                        />
                    </div>

                    {{-- Batch Dropdown --}}
                    <div>
                        <select wire:model.live="selectedBatchId" class="exec-input" style="cursor: pointer;">
                            <option value="">Batch: All Batches</option>
                            @foreach ($batches as $b)
                                <option value="{{ $b->id }}">{{ $b->batch_name }} ({{ $b->batch_timing ?: 'General' }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Project Squad Dropdown --}}
                    <div>
                        <select wire:model.live="selectedTeamId" class="exec-input" style="cursor: pointer;">
                            <option value="">Project: All Squads</option>
                            @foreach ($teams as $t)
                                <option value="{{ $t->id }}">{{ $t->team_name }} ({{ $t->mentor_name ?: 'Mentor TBD' }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Attendance Threshold Filter --}}
                    <div>
                        <select wire:model.live="attendanceFilter" class="exec-input" style="cursor: pointer;">
                            <option value="all">Attendance: All Rates</option>
                            <option value="high">🟢 ≥ 90% (Excellent)</option>
                            <option value="medium">🟡 75% - 89% (Normal)</option>
                            <option value="low">🔴 &lt; 75% (Attention Needed)</option>
                        </select>
                    </div>

                    {{-- Task Completion Filter --}}
                    <div>
                        <select wire:model.live="taskFilter" class="exec-input" style="cursor: pointer;">
                            <option value="all">Tasks: All Statuses</option>
                            <option value="completed">✅ 100% Completed</option>
                            <option value="in_progress">⏳ In Progress / Pending</option>
                            <option value="no_tasks">⚪ No Tasks Assigned</option>
                        </select>
                    </div>

                    {{-- Domain Filter --}}
                    @if (!empty($domains))
                        <div>
                            <select wire:model.live="domainFilter" class="exec-input" style="cursor: pointer;">
                                <option value="">Domain: All</option>
                                @foreach ($domains as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                {{-- Reset Button --}}
                <div>
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="exec-btn-secondary"
                        style="color: #ffb4ab;"
                        title="Clear all filters">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Reset Filters</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 4. Master Data Table                                              --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="exec-card" style="padding: 0; overflow: hidden; margin-bottom: 22px;">
            <div style="overflow-x: auto;">
                <table class="exec-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th wire:click="toggleSort('name')" style="cursor: pointer;">
                                Intern & Academic Profile
                                @if ($sortBy === 'name')
                                    <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th>Batch & Project Squad</th>
                            <th>Status & Lifecycle</th>
                            <th wire:click="toggleSort('attendance_pct')" style="cursor: pointer; min-width: 170px;">
                                Overall Attendance
                                @if ($sortBy === 'attendance_pct')
                                    <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th wire:click="toggleSort('task_pct')" style="cursor: pointer; min-width: 160px;">
                                Task Progress
                                @if ($sortBy === 'task_pct')
                                    <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th style="text-align: center;">Logbooks</th>
                            <th style="text-align: right; width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($internsList as $index => $intern)
                            @php
                                $tel = $intern->telemetry;
                                $srNo = ($internsList->currentPage() - 1) * $internsList->perPage() + $index + 1;
                                $attPct = $tel['attendance_pct'];
                                $attColor = $attPct >= 90 ? '#10b981' : ($attPct >= 75 ? '#f59e0b' : '#ef4444');
                                $taskPct = $tel['task_pct'];
                            @endphp
                            <tr>
                                {{-- Sr. No. --}}
                                <td style="text-align: center; color: #8e909f; font-weight: 600;">
                                    {{ $srNo }}
                                </td>

                                {{-- Intern Profile --}}
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        @if ($intern->intern_image)
                                            <img
                                                src="{{ asset('storage/' . $intern->intern_image) }}"
                                                alt="{{ $intern->name }}"
                                                style="width: 40px; height: 40px; border-radius: 10px; object-fit: cover; border: 1.5px solid #222a3d; flex-shrink: 0;"
                                            />
                                        @else
                                            <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(30,64,175,0.3);">
                                                {{ strtoupper(substr($intern->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight: 700; color: #ffffff; font-size: 14px;">
                                                {{ $intern->name }}
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                                <span class="pill-badge" style="background: rgba(30,64,175,0.25); color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); font-size: 11px;">
                                                    {{ $intern->intern_code ?: 'TS-INTERN' }}
                                                </span>
                                                @if ($intern->domain)
                                                    <span style="font-size: 11px; color: #8e909f;">• {{ $intern->domain }}</span>
                                                @endif
                                            </div>
                                            <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">
                                                🎓 {{ $intern->college ?: 'College not specified' }}
                                                @if ($intern->degree)
                                                    ({{ $intern->degree }})
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Batch & Project Squad --}}
                                <td>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <span class="pill-badge" style="background: rgba(14, 165, 233, 0.15); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.3);">
                                                📅 {{ $intern->batch?->batch_name ?: 'Batch Unassigned' }}
                                            </span>
                                            @if ($intern->batch?->batch_timing)
                                                <span style="font-size: 11px; color: #8e909f;">({{ $intern->batch->batch_timing }})</span>
                                            @endif
                                        </div>
                                        <div style="margin-top: 4px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <span class="pill-badge" style="background: rgba(168, 85, 247, 0.15); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.3);">
                                                🚀 {{ $intern->team?->team_name ?: ($intern->project_name ?: 'Project Unassigned') }}
                                            </span>
                                        </div>
                                        <div style="font-size: 11.5px; color: #8e909f; margin-top: 3px;">
                                            👤 Mentor: <strong style="color: #cbd5e1;">{{ $intern->team?->mentor_name ?: 'Not Assigned' }}</strong>
                                        </div>
                                    </div>
                                </td>

                                {{-- Status & Lifecycle --}}
                                <td>
                                    @if ($tel['is_archived'])
                                        <div>
                                            <span class="pill-badge" style="background: rgba(147, 51, 234, 0.2); color: #d8b4fe; border: 1px solid rgba(147, 51, 234, 0.4);">
                                                🎓 Completed & Archived
                                            </span>
                                            @if ($intern->cohort_archive_name)
                                                <div style="font-size: 11px; color: #c084fc; font-weight: 600; margin-top: 3px;">
                                                    📁 {{ $intern->cohort_archive_name }}
                                                </div>
                                            @endif
                                            @if ($intern->completion_date)
                                                <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                                    Completed: {{ $intern->completion_date->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div>
                                            <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3);">
                                                🟢 Active (In Progress)
                                            </span>
                                            @if ($intern->joining_date)
                                                <div style="font-size: 11px; color: #8e909f; margin-top: 3px;">
                                                    Joined: {{ $intern->joining_date->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Attendance Telemetry --}}
                                <td>
                                    <div>
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="font-weight: 800; font-size: 13px; color: {{ $attColor }};">
                                                {{ $attPct }}%
                                            </span>
                                            <span style="font-size: 11px; color: #8e909f;">
                                                {{ $tel['attended_days'] }}/{{ $tel['working_days'] }} Days
                                            </span>
                                        </div>
                                        <div class="progress-bar-bg" style="margin-bottom: 5px;">
                                            <div class="progress-bar-fill" style="width: {{ min(100, $attPct) }}%; background-color: {{ $attColor }};"></div>
                                        </div>
                                        <div style="font-size: 10.5px; color: #8e909f; white-space: nowrap;">
                                            <span style="color: #4edea3; font-weight: 600;">P:{{ $tel['present'] }}</span> |
                                            <span style="color: #60a5fa; font-weight: 600;">WFH:{{ $tel['wfh'] }}</span> |
                                            <span style="color: #fbbf24; font-weight: 600;">L:{{ $tel['late'] }}</span> |
                                            <span style="color: #ffb4ab; font-weight: 600;">A:{{ $tel['absent'] }}</span> |
                                            <span style="color: #d8b4fe; font-weight: 600;">Lev:{{ $tel['leave'] }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Task Progress --}}
                                <td>
                                    <div>
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="font-weight: 800; font-size: 13px; color: {{ $taskPct >= 80 ? '#4edea3' : ($taskPct >= 50 ? '#93c5fd' : '#fde68a') }};">
                                                {{ $taskPct }}%
                                            </span>
                                            <span style="font-size: 11px; color: #8e909f;">
                                                {{ $tel['completed_tasks'] }}/{{ $tel['total_tasks'] }} Tasks
                                            </span>
                                        </div>
                                        <div class="progress-bar-bg" style="margin-bottom: 5px;">
                                            <div class="progress-bar-fill" style="width: {{ min(100, $taskPct) }}%; background-color: #9333ea;"></div>
                                        </div>
                                        @if ($tel['pending_tasks'] > 0)
                                            <span class="pill-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); font-size: 10.5px;">
                                                ⏳ {{ $tel['pending_tasks'] }} Pending
                                            </span>
                                        @else
                                            <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 10.5px;">
                                                ✅ All Done
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Logbooks --}}
                                <td style="text-align: center;">
                                    <span class="pill-badge" style="background: rgba(30, 41, 59, 0.8); color: #cbd5e1; border: 1px solid #334155; font-size: 12px; font-weight: 700;">
                                        📖 {{ $tel['logbooks_count'] }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td style="text-align: right;">
                                    <button
                                        type="button"
                                        wire:click="openDetailModal({{ $intern->id }})"
                                        class="exec-btn-secondary"
                                        style="padding: 6px 11px; font-size: 11.5px;"
                                        title="View Full Intern Dossier">
                                        <span>View Details</span>
                                        <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 40px 20px; text-align: center; color: #8e909f;">
                                    <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                                    <p style="font-size: 14px; font-weight: 600; color: #dae2fd; margin: 0 0 4px;">No intern records found matching the current filters.</p>
                                    <p style="font-size: 12px; margin: 0;">Try adjusting your search criteria, clearing filters, or switching tabs.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- 5. Standardized Pagination Footer (11 per page default)       --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div style="padding: 14px 20px; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background-color: #0d1527;">
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <div style="color: #8e909f; font-size: 13px; font-weight: 500;">
                        Showing <span style="font-weight: 700; color: #dae2fd;">{{ $internsList->total() }}</span> of <span style="font-weight: 700; color: #dae2fd;">{{ max($totalRosterCount, $internsList->total()) }}</span> interns
                    </div>

                    {{-- Per Page Selector --}}
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 12px; color: #8e909f; font-weight: 600;">Per page:</span>
                        <select
                            wire:model.live="perPage"
                            class="exec-input"
                            style="padding: 3px 8px !important; font-size: 12px !important; font-weight: 700 !important; cursor: pointer;">
                            <option value="11">11</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="-1">All</option>
                        </select>
                    </div>
                </div>

                {{-- Page Buttons --}}
                <div style="display: flex; align-items: center; gap: 4px;">
                    @if ($internsList->onFirstPage())
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

                    @foreach (range(1, max(1, $internsList->lastPage())) as $page)
                        @if ($page == $internsList->currentPage())
                            <span style="background-color: #1e40af; color: #ffffff; font-weight: 700; border-radius: 6px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 12px; box-shadow: 0 2px 6px rgba(30,64,175,0.4);">
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

                    @if ($internsList->hasMorePages())
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

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 6. POP-UP MODAL: Column Selector & CSV Export                     --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($showExportModal)
            <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div class="exec-card" style="width: 100%; max-width: 720px; max-height: 90vh; overflow-y: auto; background: #0f172a; border: 1.5px solid #222a3d; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
                    {{-- Modal Header --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(30,64,175,0.25); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                📥
                            </div>
                            <div>
                                <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Export CSV Report</h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0;">Select which telemetry data points and columns to include in your CSV.</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeExportModal"
                            style="background: transparent; border: none; color: #8e909f; cursor: pointer; padding: 4px;">
                            ✕
                        </button>
                    </div>

                    {{-- Modal Body: Column Checkboxes --}}
                    <div style="padding: 20px 24px;">
                        {{-- Custom Date Selection Filter for Export --}}
                        <div style="margin-bottom: 20px; padding: 14px 16px; background: #131b2e; border: 1px solid #222a3d; border-radius: 12px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">📅</span>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 700; color: #ffffff;">Custom Date Selection (Filter Downloaded Data)</div>
                                        <div style="font-size: 11px; color: #8e909f;">Choose start and end dates to export only interns within this timeframe. Leave empty for all records.</div>
                                    </div>
                                </div>
                                @if ($customStartDate || $customEndDate)
                                    <span class="pill-badge" style="background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); font-size: 11px;">
                                        Range: {{ $customStartDate ?: 'Start' }} → {{ $customEndDate ?: 'End' }}
                                    </span>
                                @else
                                    <span class="pill-badge" style="background: rgba(148,163,184,0.1); color: #94a3b8; border: 1px solid rgba(148,163,184,0.2); font-size: 11px;">
                                        All Dates
                                    </span>
                                @endif
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <label style="font-size: 11.5px; color: #8e909f; font-weight: 600;">Date Basis:</label>
                                    <select wire:model.live="dateField" class="exec-input" style="padding: 6px 10px; font-size: 12px; cursor: pointer;">
                                        <option value="joining_date">Joining Date</option>
                                        <option value="completion_date">Completion Date</option>
                                        <option value="created_at">Enrolled Date</option>
                                    </select>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <label style="font-size: 11.5px; color: #8e909f; font-weight: 600;">From:</label>
                                    <input
                                        type="date"
                                        wire:model.live="customStartDate"
                                        class="exec-input"
                                        style="padding: 6px 10px; font-size: 12px;"
                                    />
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <label style="font-size: 11.5px; color: #8e909f; font-weight: 600;">To:</label>
                                    <input
                                        type="date"
                                        wire:model.live="customEndDate"
                                        class="exec-input"
                                        style="padding: 6px 10px; font-size: 12px;"
                                    />
                                </div>
                                @if ($customStartDate || $customEndDate)
                                    <button
                                        type="button"
                                        wire:click="$set('customStartDate', null); $set('customEndDate', null);"
                                        style="font-size: 11.5px; font-weight: 700; color: #ffb4ab; background: transparent; border: none; cursor: pointer; text-decoration: underline;">
                                        Clear Dates
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                            <span style="font-size: 12px; font-weight: 700; color: #8e909f; text-transform: uppercase;">
                                Available Data Fields ({{ count($visibleColumns) }} Selected)
                            </span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button
                                    type="button"
                                    wire:click="selectAllExportColumns"
                                    style="font-size: 11.5px; font-weight: 700; color: #60a5fa; background: transparent; border: none; cursor: pointer;">
                                    Select All
                                </button>
                                <span style="color: #475569;">•</span>
                                <button
                                    type="button"
                                    wire:click="deselectAllExportColumns"
                                    style="font-size: 11.5px; font-weight: 700; color: #8e909f; background: transparent; border: none; cursor: pointer;">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                            @foreach ($colDefs as $colKey => $colLabel)
                                <label style="display: flex; align-items: center; gap: 8px; padding: 7px 10px; background: #171f33; border: 1px solid #222a3d; border-radius: 8px; font-size: 12px; color: #dae2fd; cursor: pointer; transition: background 0.15s ease;">
                                    <input
                                        type="checkbox"
                                        wire:model="visibleColumns"
                                        value="{{ $colKey }}"
                                        style="width: 15px; height: 15px; accent-color: #1e40af; border-radius: 4px; cursor: pointer;"
                                    />
                                    <span>{{ $colLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 16px 24px; border-top: 1px solid #222a3d; background: #0b1326; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                        <button
                            type="button"
                            wire:click="closeExportModal"
                            class="exec-btn-secondary">
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="exportCsv"
                            class="exec-btn-primary">
                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download Export ({{ $filteredCount }} Records)</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 7. POP-UP MODAL: Comprehensive Intern Dossier                     --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($showDetailModal && $detailIntern)
            @php
                $dt = $detailIntern->telemetry;
            @endphp
            <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div class="exec-card" style="width: 100%; max-width: 840px; max-height: 90vh; overflow-y: auto; background: #0f172a; border: 1.5px solid #222a3d; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
                    {{-- Header --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            @if ($detailIntern->intern_image)
                                <img
                                    src="{{ asset('storage/' . $detailIntern->intern_image) }}"
                                    alt="{{ $detailIntern->name }}"
                                    style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 2px solid #222a3d;"
                                />
                            @else
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;">
                                    {{ strtoupper(substr($detailIntern->name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">{{ $detailIntern->name }}</h3>
                                    <span class="pill-badge" style="background: rgba(30,64,175,0.25); color: #93c5fd; border: 1px solid rgba(59,130,246,0.3);">
                                        {{ $detailIntern->intern_code ?: 'TS-INTERN' }}
                                    </span>
                                </div>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 3px;">
                                    {{ $detailIntern->email }} • {{ $detailIntern->phone ?: 'No phone recorded' }}
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeDetailModal"
                            style="background: transparent; border: none; color: #8e909f; cursor: pointer; padding: 4px; font-size: 18px;">
                            ✕
                        </button>
                    </div>

                    {{-- Body --}}
                    <div style="padding: 22px 24px; display: flex; flex-direction: column; gap: 20px;">
                        {{-- Top Grid: Academic & Allocation --}}
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 14px;">
                                <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Academic Profile</div>
                                <div style="font-size: 13px; font-weight: 700; color: #ffffff; margin-top: 4px;">{{ $detailIntern->college ?: 'College TBD' }}</div>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">{{ $detailIntern->degree ?: 'Program TBD' }} ({{ $detailIntern->academic_year ?: 'Year TBD' }})</div>
                                <div style="font-size: 11.5px; color: #4edea3; font-weight: 600; margin-top: 4px;">Score / CGPA: {{ $detailIntern->cgpa ?: '—' }}</div>
                            </div>

                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 14px;">
                                <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Batch & Squad Allocation</div>
                                <div style="font-size: 13px; font-weight: 700; color: #ffffff; margin-top: 4px;">{{ $detailIntern->batch?->batch_name ?: 'Batch Not Assigned' }}</div>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">{{ $detailIntern->team?->team_name ?: 'Project Not Assigned' }}</div>
                                <div style="font-size: 11.5px; color: #93c5fd; font-weight: 600; margin-top: 4px;">Mentor: {{ $detailIntern->team?->mentor_name ?: 'Mentor Not Assigned' }}</div>
                            </div>

                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 14px;">
                                <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Lifecycle Status</div>
                                <div style="margin-top: 6px;">
                                    @if ($dt['is_archived'])
                                        <span class="pill-badge" style="background: rgba(147, 51, 234, 0.2); color: #d8b4fe; border: 1px solid rgba(147, 51, 234, 0.4);">
                                            🎓 Completed & Archived
                                        </span>
                                        <div style="font-size: 11.5px; color: #c084fc; font-weight: 600; margin-top: 4px;">Cycle: {{ $detailIntern->cohort_archive_name }}</div>
                                    @else
                                        <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3);">
                                            🟢 Active (In Progress)
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 11.5px; color: #8e909f; margin-top: 4px;">
                                    Joined: {{ $detailIntern->joining_date ? $detailIntern->joining_date->format('d/m/Y') : '—' }}
                                    @if ($detailIntern->completion_date)
                                        • Completed: {{ $detailIntern->completion_date->format('d/m/Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Attendance Telemetry Card --}}
                        <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <div style="font-size: 13px; font-weight: 700; color: #ffffff;">Overall Attendance Statistics</div>
                                <div style="font-size: 15px; font-weight: 800; color: {{ $dt['attendance_pct'] >= 90 ? '#4edea3' : ($dt['attendance_pct'] >= 75 ? '#fbbf24' : '#ffb4ab') }};">
                                    {{ $dt['attendance_pct'] }}% Attendance Rate
                                </div>
                            </div>
                            <div class="progress-bar-bg" style="height: 8px; margin-bottom: 12px;">
                                <div class="progress-bar-fill" style="width: {{ min(100, $dt['attendance_pct']) }}%; background-color: {{ $dt['attendance_pct'] >= 90 ? '#10b981' : ($dt['attendance_pct'] >= 75 ? '#f59e0b' : '#ef4444') }};"></div>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 8px; text-align: center;">
                                <div style="background: #0d1527; padding: 8px; border-radius: 8px; border: 1px solid #222a3d;">
                                    <div style="font-size: 16px; font-weight: 800; color: #4edea3;">{{ $dt['present'] }}</div>
                                    <div style="font-size: 10.5px; color: #8e909f; font-weight: 600;">Present</div>
                                </div>
                                <div style="background: #0d1527; padding: 8px; border-radius: 8px; border: 1px solid #222a3d;">
                                    <div style="font-size: 16px; font-weight: 800; color: #60a5fa;">{{ $dt['wfh'] }}</div>
                                    <div style="font-size: 10.5px; color: #8e909f; font-weight: 600;">WFH</div>
                                </div>
                                <div style="background: #0d1527; padding: 8px; border-radius: 8px; border: 1px solid #222a3d;">
                                    <div style="font-size: 16px; font-weight: 800; color: #fbbf24;">{{ $dt['late'] }}</div>
                                    <div style="font-size: 10.5px; color: #8e909f; font-weight: 600;">Late</div>
                                </div>
                                <div style="background: #0d1527; padding: 8px; border-radius: 8px; border: 1px solid #222a3d;">
                                    <div style="font-size: 16px; font-weight: 800; color: #ffb4ab;">{{ $dt['absent'] }}</div>
                                    <div style="font-size: 10.5px; color: #8e909f; font-weight: 600;">Absent</div>
                                </div>
                                <div style="background: #0d1527; padding: 8px; border-radius: 8px; border: 1px solid #222a3d;">
                                    <div style="font-size: 16px; font-weight: 800; color: #d8b4fe;">{{ $dt['leave'] }}</div>
                                    <div style="font-size: 10.5px; color: #8e909f; font-weight: 600;">Leave</div>
                                </div>
                                <div style="background: #0d1527; padding: 8px; border-radius: 8px; border: 1px solid #222a3d;">
                                    <div style="font-size: 16px; font-weight: 800; color: #dae2fd;">{{ $dt['working_days'] }}</div>
                                    <div style="font-size: 10.5px; color: #8e909f; font-weight: 600;">Total Evaluated</div>
                                </div>
                            </div>
                        </div>

                        {{-- Task Performance Card --}}
                        <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <div style="font-size: 13px; font-weight: 700; color: #ffffff;">Task Assignment & Submissions</div>
                                <div style="font-size: 15px; font-weight: 800; color: #93c5fd;">
                                    {{ $dt['completed_tasks'] }} / {{ $dt['total_tasks'] }} ({{ $dt['task_pct'] }}%)
                                </div>
                            </div>
                            <div class="progress-bar-bg" style="height: 8px; margin-bottom: 12px;">
                                <div class="progress-bar-fill" style="width: {{ min(100, $dt['task_pct']) }}%; background-color: #9333ea;"></div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3);">
                                    ✅ {{ $dt['completed_tasks'] }} Completed
                                </span>
                                <span class="pill-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                                    ⏳ {{ $dt['pending_tasks'] }} Pending
                                </span>
                                <span class="pill-badge" style="background: rgba(30, 41, 59, 0.8); color: #cbd5e1; border: 1px solid #334155;">
                                    📖 {{ $dt['logbooks_count'] }} Daily Logbooks Submitted
                                </span>
                            </div>
                        </div>

                        {{-- Issued Documents & Credentials --}}
                        <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 14px;">
                            <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; margin-bottom: 8px;">Documentation & Credentials</div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span class="pill-badge" style="background: {{ $detailIntern->offer_letter_id ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; color: {{ $detailIntern->offer_letter_id ? '#4edea3' : '#ffb4ab' }}; border: 1px solid {{ $detailIntern->offer_letter_id ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)' }};">
                                    {{ $detailIntern->offer_letter_id ? '✓ Offer Letter Issued' : '✕ No Offer Letter' }}
                                </span>
                                <span class="pill-badge" style="background: {{ $detailIntern->completionCertificate ? 'rgba(16, 185, 129, 0.15)' : 'rgba(100, 116, 139, 0.2)' }}; color: {{ $detailIntern->completionCertificate ? '#4edea3' : '#94a3b8' }}; border: 1px solid {{ $detailIntern->completionCertificate ? 'rgba(16, 185, 129, 0.3)' : 'rgba(100, 116, 139, 0.3)' }};">
                                    {{ $detailIntern->completionCertificate ? '✓ Certificate Issued' : '○ Certificate Pending' }}
                                </span>
                                <span class="pill-badge" style="background: {{ $detailIntern->completionLetter ? 'rgba(16, 185, 129, 0.15)' : 'rgba(100, 116, 139, 0.2)' }}; color: {{ $detailIntern->completionLetter ? '#4edea3' : '#94a3b8' }}; border: 1px solid {{ $detailIntern->completionLetter ? 'rgba(16, 185, 129, 0.3)' : 'rgba(100, 116, 139, 0.3)' }};">
                                    {{ $detailIntern->completionLetter ? '✓ Completion Letter Issued' : '○ Completion Letter Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div style="padding: 16px 24px; border-top: 1px solid #222a3d; background: #0b1326; display: flex; align-items: center; justify-content: flex-end;">
                        <button
                            type="button"
                            wire:click="closeDetailModal"
                            class="exec-btn-primary">
                            Close Dossier
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
