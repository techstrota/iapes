<x-filament-panels::page>
    @php
        $data = $this->getViewData();
        $paginator = $data['paginator'];
        $totalAssignmentsCount = $data['totalAssignmentsCount'];
        $onTimeCount = $data['onTimeCount'];
        $lateCount = $data['lateCount'];
        $overdueCount = $data['overdueCount'];
        $pendingCount = $data['pendingCount'];
        $totalSubmittedCount = $data['totalSubmittedCount'];
        $overallOnTimeRate = $data['overallOnTimeRate'];
        $overallCompletionRate = $data['overallCompletionRate'];
        $batches = $data['batches'];
        $teams = $data['teams'];
        $archiveCycles = $data['archiveCycles'];
        $colDefs = $data['columnDefinitions'];
        $detailInternData = $data['detailInternData'];
        $detailTaskData = $data['detailTaskData'];
    @endphp

    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', Helvetica, Arial, sans-serif; color: #dae2fd; max-width: 100%; margin: 0 auto;">

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- Premium Executive Styling & Design Tokens                         --}}
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
                    <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(147,51,234,0.4) 0%, rgba(59,130,246,0.15) 100%); border: 1px solid rgba(147,51,234,0.4); display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 4px 18px rgba(147,51,234,0.3);">
                        🎯
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                                Task Performance & Deadline Intelligence Report
                            </h1>
                            <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3);">
                                🟢 Deadline Tracker
                            </span>
                        </div>
                        <p style="font-size: 13px; color: #8e909f; margin: 4px 0 0;">
                            Track task allocations, on-time submissions, delayed completions, pending deliverables, and historical cohort performance.
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
                            <option value="due_date" style="background: #0f172a; color: #ffffff;">Due Date</option>
                            <option value="submitted_at" style="background: #0f172a; color: #ffffff;">Submission Date</option>
                            <option value="created_at" style="background: #0f172a; color: #ffffff;">Created Date</option>
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
            {{-- Top Archival Scope Switcher Tabs                             --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div style="margin-top: 20px; padding-top: 18px; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 12px; padding: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    <button
                        type="button"
                        wire:click="$set('activeScope', 'all')"
                        style="padding: 7px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeScope === 'all' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        🌐 All Lifetime Tasks
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeScope', 'active')"
                        style="padding: 7px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeScope === 'active' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        ⚡ Active Interns Tasks
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeScope', 'archived')"
                        style="padding: 7px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeScope === 'archived' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        📁 Completed / Archived Cohorts
                    </button>
                </div>

                {{-- Archive Cohort Cycle Filter --}}
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
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 22px;">
            {{-- KPI 1: Total Tasks Assigned --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        Total Assignments
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59,130,246,0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        📋
                    </span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                    {{ $totalAssignmentsCount }}
                </div>
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #4edea3; font-weight: 700;">{{ $totalSubmittedCount }} Submitted</span>
                    <span>•</span>
                    <span style="color: #fbbf24; font-weight: 700;">{{ $pendingCount }} Pending</span>
                </div>
            </div>

            {{-- KPI 2: Completed On-Time --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        Completed On-Time
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16,185,129,0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        ✅
                    </span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #4edea3; line-height: 1.1;">
                    {{ $onTimeCount }}
                </div>
                <div style="margin-top: 8px; font-size: 11.5px; color: #4edea3; font-weight: 600;">
                    Submitted on or before deadline
                </div>
            </div>

            {{-- KPI 3: Completed Late --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        Completed Late
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245,158,11,0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        ⚠️
                    </span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #fbbf24; line-height: 1.1;">
                    {{ $lateCount }}
                </div>
                <div style="margin-top: 8px; font-size: 11.5px; color: #fde68a; font-weight: 600;">
                    Submitted past due date
                </div>
            </div>

            {{-- KPI 4: Overdue / Missed --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        Overdue / Missed
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(239,68,68,0.15); color: #ffb4ab; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        🚨
                    </span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #ffb4ab; line-height: 1.1;">
                    {{ $overdueCount }}
                </div>
                <div style="margin-top: 8px; font-size: 11.5px; color: #ffb4ab; font-weight: 600;">
                    Unsubmitted past due date
                </div>
            </div>

            {{-- KPI 5: Overall On-Time Rate --}}
            <div class="exec-card" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">
                        On-Time Submission Rate
                    </span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(147,51,234,0.15); color: #d8b4fe; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        📈
                    </span>
                </div>
                <div style="display: flex; align-items: baseline; gap: 8px;">
                    <div style="font-size: 28px; font-weight: 800; color: {{ $overallOnTimeRate >= 80 ? '#4edea3' : ($overallOnTimeRate >= 60 ? '#fbbf24' : '#ffb4ab') }}; line-height: 1.1;">
                        {{ $overallOnTimeRate }}%
                    </div>
                    <span style="font-size: 11px; font-weight: 600; color: #8e909f;">Compliance</span>
                </div>
                <div style="margin-top: 10px;">
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: {{ min(100, $overallOnTimeRate) }}%; background: {{ $overallOnTimeRate >= 80 ? '#10b981' : ($overallOnTimeRate >= 60 ? '#f59e0b' : '#ef4444') }};"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 3. Perspective View Mode Tabs (Intern / Task / Matrix)            --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 16px;">
            <div style="background-color: #0b1326; border: 1.5px solid #222a3d; border-radius: 12px; padding: 4px; display: inline-flex; align-items: center; gap: 4px;">
                <button
                    type="button"
                    wire:click="setPerspective('intern')"
                    style="padding: 8px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'intern' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                    👤 By Intern Performance
                </button>
                <button
                    type="button"
                    wire:click="setPerspective('task')"
                    style="padding: 8px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'task' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                    📋 By Task Overview
                </button>
                <button
                    type="button"
                    wire:click="setPerspective('matrix')"
                    style="padding: 8px 18px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'matrix' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                    📑 Granular Submissions Matrix
                </button>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 4. Comprehensive Filter Toolbar                                   --}}
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
                            placeholder="Search task title, intern, code..."
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

                    {{-- Timeliness Status Filter --}}
                    <div>
                        <select wire:model.live="timelinessFilter" class="exec-input" style="cursor: pointer;">
                            <option value="all">Timeliness: All Statuses</option>
                            <option value="on_time">🟢 Completed On-Time</option>
                            <option value="late">🟡 Completed Late</option>
                            <option value="overdue">🔴 Overdue (Missed)</option>
                            <option value="pending">⏳ Pending / In Progress</option>
                        </select>
                    </div>

                    {{-- Priority Filter --}}
                    <div>
                        <select wire:model.live="priorityFilter" class="exec-input" style="cursor: pointer;">
                            <option value="all">Priority: All</option>
                            <option value="urgent">🔥 Urgent</option>
                            <option value="high">⚡ High</option>
                            <option value="medium">📌 Medium</option>
                            <option value="low">☕ Low</option>
                        </select>
                    </div>

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
        {{-- 5. Perspective Master Tables                                      --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="exec-card" style="padding: 0; overflow: hidden; margin-bottom: 22px;">
            <div style="overflow-x: auto;">

                {{-- ── 5A. VIEW 1: BY INTERN PERFORMANCE ── --}}
                @if ($viewMode === 'intern')
                    <table class="exec-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th wire:click="toggleSort('name')" style="cursor: pointer;">
                                    Intern Profile & Academic
                                    @if ($sortBy === 'name')
                                        <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th>Batch & Squad Allocation</th>
                                <th>Status</th>
                                <th wire:click="toggleSort('total_assigned')" style="cursor: pointer; text-align: center;">
                                    Tasks Assigned
                                    @if ($sortBy === 'total_assigned')
                                        <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th>Execution Breakdown</th>
                                <th wire:click="toggleSort('on_time_pct')" style="cursor: pointer; min-width: 160px;">
                                    On-Time Compliance
                                    @if ($sortBy === 'on_time_pct')
                                        <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th style="text-align: center;">Avg Score</th>
                                <th style="text-align: right; width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paginator as $index => $row)
                                @php
                                    $intern = $row['intern'];
                                    $srNo = ($paginator->currentPage() - 1) * $paginator->perPage() + $index + 1;
                                    $onTimeRate = $row['on_time_pct'];
                                    $rateColor = $onTimeRate >= 80 ? '#4edea3' : ($onTimeRate >= 60 ? '#fbbf24' : '#ffb4ab');
                                @endphp
                                <tr>
                                    <td style="text-align: center; color: #8e909f; font-weight: 600;">
                                        {{ $srNo }}
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            @if ($intern->intern_image)
                                                <img
                                                    src="{{ asset('storage/' . $intern->intern_image) }}"
                                                    alt="{{ $intern->name }}"
                                                    style="width: 38px; height: 38px; border-radius: 10px; object-fit: cover; border: 1.5px solid #222a3d; flex-shrink: 0;"
                                                />
                                            @else
                                                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">
                                                    {{ strtoupper(substr($intern->name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div style="font-weight: 700; color: #ffffff; font-size: 13.5px;">{{ $intern->name }}</div>
                                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                                    <span class="pill-badge" style="background: rgba(30,64,175,0.25); color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); font-size: 11px;">
                                                        {{ $intern->intern_code ?: 'TS-INTERN' }}
                                                    </span>
                                                    <span style="font-size: 11px; color: #8e909f;">{{ $intern->college ?: 'College TBD' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; color: #cbd5e1; font-weight: 600;">{{ $intern->batch?->batch_name ?: 'Batch Unassigned' }}</div>
                                        <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">
                                            🚀 {{ $intern->team?->team_name ?: 'Project Unassigned' }}
                                            @if ($intern->team?->mentor_name)
                                                • Mentor: {{ $intern->team->mentor_name }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if ($row['is_archived'])
                                            <span class="pill-badge" style="background: rgba(147, 51, 234, 0.2); color: #d8b4fe; border: 1px solid rgba(147, 51, 234, 0.4);">
                                                🎓 Completed & Archived
                                            </span>
                                            @if ($intern->cohort_archive_name)
                                                <div style="font-size: 10.5px; color: #c084fc; margin-top: 2px;">{{ $intern->cohort_archive_name }}</div>
                                            @endif
                                        @else
                                            <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3);">
                                                🟢 Active
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: center; font-weight: 800; font-size: 15px; color: #ffffff;">
                                        {{ $row['total_assigned'] }}
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <span class="pill-badge" style="background: rgba(16,185,129,0.15); color: #4edea3; border: 1px solid rgba(16,185,129,0.3); font-size: 11px;">
                                                🟢 {{ $row['on_time'] }} On-time
                                            </span>
                                            @if ($row['late'] > 0)
                                                <span class="pill-badge" style="background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); font-size: 11px;">
                                                    🟡 {{ $row['late'] }} Late
                                                </span>
                                            @endif
                                            @if ($row['overdue'] > 0)
                                                <span class="pill-badge" style="background: rgba(239,68,68,0.15); color: #ffb4ab; border: 1px solid rgba(239,68,68,0.3); font-size: 11px;">
                                                    🔴 {{ $row['overdue'] }} Overdue
                                                </span>
                                            @endif
                                            @if ($row['pending'] > 0)
                                                <span class="pill-badge" style="background: rgba(30,41,59,0.8); color: #cbd5e1; border: 1px solid #334155; font-size: 11px;">
                                                    ⏳ {{ $row['pending'] }} Pending
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                                <span style="font-weight: 800; font-size: 13px; color: {{ $rateColor }};">
                                                    {{ $onTimeRate }}%
                                                </span>
                                                <span style="font-size: 11px; color: #8e909f;">
                                                    {{ $row['on_time'] }}/{{ max(1, $row['submitted']) }} On-Time
                                                </span>
                                            </div>
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill" style="width: {{ min(100, $onTimeRate) }}%; background-color: {{ $rateColor }};"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @if ($row['avg_marks'] !== null)
                                            <span style="font-size: 13px; font-weight: 800; color: #93c5fd;">{{ $row['avg_marks'] }}</span>
                                        @else
                                            <span style="color: #64748b; font-size: 12px;">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <button
                                            type="button"
                                            wire:click="openInternDossier({{ $intern->id }})"
                                            class="exec-btn-secondary"
                                            style="padding: 6px 11px; font-size: 11.5px;">
                                            <span>Task Dossier</span>
                                            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="padding: 40px 20px; text-align: center; color: #8e909f;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                                        <p style="font-size: 14px; font-weight: 600; color: #dae2fd; margin: 0 0 4px;">No intern task records found.</p>
                                        <p style="font-size: 12px; margin: 0;">Adjust filters or search parameters above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                {{-- ── 5B. VIEW 2: BY TASK OVERVIEW ── --}}
                @elseif ($viewMode === 'task')
                    <table class="exec-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th wire:click="toggleSort('title')" style="cursor: pointer;">
                                    Task Details & Priority
                                    @if ($sortBy === 'title')
                                        <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th wire:click="toggleSort('due_date')" style="cursor: pointer;">
                                    Due Date / Deadline
                                    @if ($sortBy === 'due_date')
                                        <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th style="text-align: center;">Assigned Roster</th>
                                <th wire:click="toggleSort('completion_pct')" style="cursor: pointer; min-width: 160px;">
                                    Completion Progress
                                    @if ($sortBy === 'completion_pct')
                                        <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th>Timeliness Roster Breakdown</th>
                                <th style="text-align: right; width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paginator as $index => $row)
                                @php
                                    $task = $row['task'];
                                    $srNo = ($paginator->currentPage() - 1) * $paginator->perPage() + $index + 1;
                                    $compPct = $row['completion_pct'];
                                    $prio = strtolower($task->priority ?: 'medium');
                                    $prioBg = match ($prio) {
                                        'urgent' => 'rgba(239, 68, 68, 0.2); color: #ffb4ab; border: 1px solid rgba(239, 68, 68, 0.4);',
                                        'high'   => 'rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4);',
                                        'low'    => 'rgba(100, 116, 139, 0.2); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.3);',
                                        default  => 'rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);',
                                    };
                                @endphp
                                <tr>
                                    <td style="text-align: center; color: #8e909f; font-weight: 600;">
                                        {{ $srNo }}
                                    </td>
                                    <td>
                                        <div style="font-size: 14px; font-weight: 700; color: #ffffff;">{{ $task->title }}</div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                            <span class="pill-badge" style="{{ $prioBg }}">
                                                {{ ucfirst($prio) }} Priority
                                            </span>
                                            <span style="font-size: 11.5px; color: #8e909f;">Task #{{ $task->task_id }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($task->due_date)
                                            <div style="font-weight: 700; color: {{ $task->due_date->isPast() ? '#ffb4ab' : '#4edea3' }}; font-size: 13px;">
                                                📅 {{ $task->due_date->format('d/m/Y') }}
                                            </div>
                                            <div style="font-size: 11px; color: #8e909f; margin-top: 2px;">
                                                {{ $task->due_date->diffForHumans() }}
                                            </div>
                                        @else
                                            <span style="color: #64748b; font-size: 12px;">No Deadline</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="pill-badge" style="background: rgba(30,64,175,0.25); color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); font-size: 13px; font-weight: 800;">
                                            👥 {{ $row['total_assigned'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                                <span style="font-weight: 800; font-size: 13px; color: {{ $compPct >= 80 ? '#4edea3' : ($compPct >= 50 ? '#93c5fd' : '#fde68a') }};">
                                                    {{ $compPct }}%
                                                </span>
                                                <span style="font-size: 11px; color: #8e909f;">
                                                    {{ $row['submitted'] }}/{{ $row['total_assigned'] }} Done
                                                </span>
                                            </div>
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill" style="width: {{ min(100, $compPct) }}%; background-color: #9333ea;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <span class="pill-badge" style="background: rgba(16,185,129,0.15); color: #4edea3; border: 1px solid rgba(16,185,129,0.3); font-size: 11px;">
                                                🟢 {{ $row['on_time'] }} On-time
                                            </span>
                                            @if ($row['late'] > 0)
                                                <span class="pill-badge" style="background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); font-size: 11px;">
                                                    🟡 {{ $row['late'] }} Late
                                                </span>
                                            @endif
                                            @if ($row['overdue'] > 0)
                                                <span class="pill-badge" style="background: rgba(239,68,68,0.15); color: #ffb4ab; border: 1px solid rgba(239,68,68,0.3); font-size: 11px;">
                                                    🔴 {{ $row['overdue'] }} Overdue
                                                </span>
                                            @endif
                                            @if ($row['pending'] > 0)
                                                <span class="pill-badge" style="background: rgba(30,41,59,0.8); color: #cbd5e1; border: 1px solid #334155; font-size: 11px;">
                                                    ⏳ {{ $row['pending'] }} Pending
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <button
                                            type="button"
                                            wire:click="openTaskDossier({{ $task->task_id }})"
                                            class="exec-btn-secondary"
                                            style="padding: 6px 11px; font-size: 11.5px;">
                                            <span>Roster</span>
                                            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding: 40px 20px; text-align: center; color: #8e909f;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                                        <p style="font-size: 14px; font-weight: 600; color: #dae2fd; margin: 0 0 4px;">No tasks found.</p>
                                        <p style="font-size: 12px; margin: 0;">Adjust search or filter parameters above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                {{-- ── 5C. VIEW 3: GRANULAR SUBMISSIONS MATRIX ── --}}
                @else
                    <table class="exec-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th>Task & Priority</th>
                                <th>Assigned Intern</th>
                                <th>Batch & Squad</th>
                                <th>Due Date</th>
                                <th>Submitted At</th>
                                <th>Timeliness Status</th>
                                <th style="text-align: center;">Marks / Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paginator as $index => $item)
                                @php
                                    $task = $item['task'];
                                    $intern = $item['intern'];
                                    $sub = $item['submission'];
                                    $srNo = ($paginator->currentPage() - 1) * $paginator->perPage() + $index + 1;
                                    $t = $item['timeliness'];
                                    $badgeStyle = match ($t) {
                                        'on_time' => 'background: rgba(16,185,129,0.15); color: #4edea3; border: 1px solid rgba(16,185,129,0.3);',
                                        'late'    => 'background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3);',
                                        'overdue' => 'background: rgba(239,68,68,0.15); color: #ffb4ab; border: 1px solid rgba(239,68,68,0.3);',
                                        default   => 'background: rgba(30,41,59,0.8); color: #cbd5e1; border: 1px solid #334155;',
                                    };
                                    $badgeIcon = match ($t) {
                                        'on_time' => '🟢 On Time',
                                        'late'    => '🟡 ' . $item['delay_text'],
                                        'overdue' => '🔴 ' . $item['delay_text'],
                                        default   => '⏳ In Progress',
                                    };
                                @endphp
                                <tr>
                                    <td style="text-align: center; color: #8e909f; font-weight: 600;">
                                        {{ $srNo }}
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #ffffff; font-size: 13.5px;">{{ $task->title }}</div>
                                        <span class="pill-badge" style="background: rgba(59,130,246,0.15); color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); font-size: 10.5px; margin-top: 3px;">
                                            {{ ucfirst($task->priority ?: 'Normal') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #ffffff;">{{ $intern->name }}</div>
                                        <span class="pill-badge" style="background: rgba(30,64,175,0.25); color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); font-size: 11px; margin-top: 2px;">
                                            {{ $intern->intern_code ?: 'TS-INTERN' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; color: #cbd5e1;">{{ $intern->batch?->batch_name ?: 'Batch Unassigned' }}</div>
                                        <div style="font-size: 11px; color: #8e909f; margin-top: 2px;">{{ $intern->team?->team_name ?: 'Squad Unassigned' }}</div>
                                    </td>
                                    <td>
                                        @if ($task->due_date)
                                            <div style="font-size: 12.5px; font-weight: 600; color: #dae2fd;">{{ $task->due_date->format('d/m/Y') }}</div>
                                        @else
                                            <span style="color: #64748b; font-size: 12px;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($sub && $sub->submitted_at)
                                            <div style="font-size: 12.5px; font-weight: 600; color: #cbd5e1;">
                                                {{ \Carbon\Carbon::parse($sub->submitted_at)->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span style="color: #64748b; font-size: 12px;">Unsubmitted</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="pill-badge" style="{{ $badgeStyle }}">
                                            {{ $badgeIcon }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        @if ($sub && $sub->marks !== null)
                                            <span style="font-size: 13px; font-weight: 800; color: #4edea3;">{{ $sub->marks }}</span>
                                            @if ($sub->grade)
                                                <span style="font-size: 11px; color: #93c5fd; font-weight: 700;">({{ $sub->grade }})</span>
                                            @endif
                                        @else
                                            <span style="color: #64748b; font-size: 12px;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="padding: 40px 20px; text-align: center; color: #8e909f;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                                        <p style="font-size: 14px; font-weight: 600; color: #dae2fd; margin: 0 0 4px;">No matching task submissions in this scope.</p>
                                        <p style="font-size: 12px; margin: 0;">Try adjusting your filters above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif

            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- 6. Standardized Pagination Footer (11 per page default)       --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div style="padding: 14px 20px; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background-color: #0d1527;">
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <div style="color: #8e909f; font-size: 13px; font-weight: 500;">
                        Showing <span style="font-weight: 700; color: #dae2fd;">{{ $paginator->total() }}</span> records in current view
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

                {{-- Page Navigation Buttons --}}
                <div style="display: flex; align-items: center; gap: 4px;">
                    @if ($paginator->onFirstPage())
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

                    @foreach (range(1, max(1, $paginator->lastPage())) as $page)
                        @if ($page == $paginator->currentPage())
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

                    @if ($paginator->hasMorePages())
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
        {{-- 7. POP-UP MODAL: Column Selector & CSV Export                     --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($showExportModal)
            <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div class="exec-card" style="width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; background: #0f172a; border: 1.5px solid #222a3d; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
                    {{-- Header --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(30,64,175,0.25); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                📥
                            </div>
                            <div>
                                <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Export Task Report CSV</h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0;">Select fields to include in your task execution CSV.</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeExportModal"
                            style="background: transparent; border: none; color: #8e909f; cursor: pointer; padding: 4px;">
                            ✕
                        </button>
                    </div>

                    {{-- Body: Checkbox Grid --}}
                    <div style="padding: 20px 24px;">
                        {{-- Custom Date Selection Filter for Export --}}
                        <div style="margin-bottom: 20px; padding: 14px 16px; background: #131b2e; border: 1px solid #222a3d; border-radius: 12px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">📅</span>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 700; color: #ffffff;">Custom Date Selection (Filter Downloaded Data)</div>
                                        <div style="font-size: 11px; color: #8e909f;">Choose start and end dates to export only task records within this timeframe. Leave empty for all records.</div>
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
                                        <option value="due_date">Due Date</option>
                                        <option value="submitted_at">Submission Date</option>
                                        <option value="created_at">Created Date</option>
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
                                Data Fields ({{ count($visibleColumns) }} Selected)
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
                                <label style="display: flex; align-items: center; gap: 8px; padding: 7px 10px; background: #171f33; border: 1px solid #222a3d; border-radius: 8px; font-size: 12px; color: #dae2fd; cursor: pointer;">
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

                    {{-- Footer --}}
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
                            <span>Download Export</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 8. POP-UP MODAL: Intern Task Dossier Modal                        --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($showDetailModal && $detailInternData)
            @php
                $dIntern = $detailInternData['intern'];
            @endphp
            <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div class="exec-card" style="width: 100%; max-width: 820px; max-height: 90vh; overflow-y: auto; background: #0f172a; border: 1.5px solid #222a3d; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;">
                                {{ strtoupper(substr($dIntern->name, 0, 2)) }}
                            </div>
                            <div>
                                <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">{{ $dIntern->name }} — Task Execution Dossier</h3>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">
                                    {{ $dIntern->intern_code ?: 'TS-INTERN' }} • {{ $dIntern->batch?->batch_name ?: 'Batch Not Assigned' }}
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

                    <div style="padding: 22px 24px;">
                        {{-- Stats Bar --}}
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-bottom: 20px;">
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #ffffff;">{{ $detailInternData['total'] }}</div>
                                <div style="font-size: 11px; color: #8e909f; font-weight: 600;">Total Assigned</div>
                            </div>
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #4edea3;">{{ $detailInternData['on_time'] }}</div>
                                <div style="font-size: 11px; color: #4edea3; font-weight: 600;">On-Time (✓)</div>
                            </div>
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #fbbf24;">{{ $detailInternData['late'] }}</div>
                                <div style="font-size: 11px; color: #fbbf24; font-weight: 600;">Late Submissions</div>
                            </div>
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #ffb4ab;">{{ $detailInternData['overdue'] }}</div>
                                <div style="font-size: 11px; color: #ffb4ab; font-weight: 600;">Overdue (Missed)</div>
                            </div>
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #cbd5e1;">{{ $detailInternData['pending'] }}</div>
                                <div style="font-size: 11px; color: #8e909f; font-weight: 600;">Pending</div>
                            </div>
                        </div>

                        {{-- Tasks List --}}
                        <div style="font-size: 13px; font-weight: 700; color: #ffffff; margin-bottom: 10px;">Assigned Tasks Record</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse ($detailInternData['assignments'] as $assign)
                                @php
                                    $tTask = $assign['task'];
                                    $tSub = $assign['submission'];
                                    $statusColor = match ($assign['timeliness']) {
                                        'on_time' => '#4edea3',
                                        'late'    => '#fbbf24',
                                        'overdue' => '#ffb4ab',
                                        default   => '#cbd5e1',
                                    };
                                @endphp
                                <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <div>
                                        <div style="font-weight: 700; color: #ffffff; font-size: 13.5px;">{{ $tTask->title }}</div>
                                        <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">
                                            Due: {{ $tTask->due_date ? $tTask->due_date->format('d/m/Y') : 'No deadline' }}
                                            @if ($tSub && $tSub->submitted_at)
                                                • Submitted: {{ \Carbon\Carbon::parse($tSub->submitted_at)->format('d/m/Y H:i') }}
                                            @endif
                                            @if ($tSub && $tSub->marks !== null)
                                                • Marks: <strong style="color: #4edea3;">{{ $tSub->marks }}</strong>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <span class="pill-badge" style="background: rgba(13,21,39,0.8); color: {{ $statusColor }}; border: 1px solid {{ $statusColor }};">
                                            {{ $assign['delay_text'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div style="color: #8e909f; font-size: 12px;">No task assignments recorded for this intern.</div>
                            @endforelse
                        </div>
                    </div>

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

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 9. POP-UP MODAL: Task Details & Assigned Roster Modal             --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($showDetailModal && $detailTaskData)
            @php
                $dTask = $detailTaskData['task'];
            @endphp
            <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div class="exec-card" style="width: 100%; max-width: 820px; max-height: 90vh; overflow-y: auto; background: #0f172a; border: 1.5px solid #222a3d; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">{{ $dTask->title }}</h3>
                            <div style="font-size: 12px; color: #8e909f; margin-top: 3px;">
                                Task #{{ $dTask->task_id }} • Due Date: {{ $dTask->due_date ? $dTask->due_date->format('d/m/Y') : 'No deadline' }}
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeDetailModal"
                            style="background: transparent; border: none; color: #8e909f; cursor: pointer; padding: 4px; font-size: 18px;">
                            ✕
                        </button>
                    </div>

                    <div style="padding: 22px 24px;">
                        {{-- Task Roster Summary --}}
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-bottom: 20px;">
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #ffffff;">{{ $detailTaskData['total'] }}</div>
                                <div style="font-size: 11px; color: #8e909f; font-weight: 600;">Assigned Interns</div>
                            </div>
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #4edea3;">{{ $detailTaskData['on_time'] }}</div>
                                <div style="font-size: 11px; color: #4edea3; font-weight: 600;">On-Time Submissions</div>
                            </div>
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #fbbf24;">{{ $detailTaskData['late'] }}</div>
                                <div style="font-size: 11px; color: #fbbf24; font-weight: 600;">Late Submissions</div>
                            </div>
                            <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 20px; font-weight: 800; color: #ffb4ab;">{{ $detailTaskData['overdue'] }}</div>
                                <div style="font-size: 11px; color: #ffb4ab; font-weight: 600;">Overdue (Missed)</div>
                            </div>
                        </div>

                        {{-- Roster List --}}
                        <div style="font-size: 13px; font-weight: 700; color: #ffffff; margin-bottom: 10px;">Assigned Interns & Submission Status</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse ($detailTaskData['assignments'] as $assign)
                                @php
                                    $rIntern = $assign['intern'];
                                    $rSub = $assign['submission'];
                                    $rColor = match ($assign['timeliness']) {
                                        'on_time' => '#4edea3',
                                        'late'    => '#fbbf24',
                                        'overdue' => '#ffb4ab',
                                        default   => '#cbd5e1',
                                    };
                                @endphp
                                <div style="background: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <div>
                                        <div style="font-weight: 700; color: #ffffff; font-size: 13.5px;">{{ $rIntern->name }}</div>
                                        <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">
                                            {{ $rIntern->intern_code ?: 'TS-INTERN' }} • {{ $rIntern->batch?->batch_name ?: 'Batch Not Assigned' }}
                                            @if ($rSub && $rSub->submitted_at)
                                                • Submitted: {{ \Carbon\Carbon::parse($rSub->submitted_at)->format('d/m/Y H:i') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <span class="pill-badge" style="background: rgba(13,21,39,0.8); color: {{ $rColor }}; border: 1px solid {{ $rColor }};">
                                            {{ $assign['delay_text'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div style="color: #8e909f; font-size: 12px;">No interns assigned to this task.</div>
                            @endforelse
                        </div>
                    </div>

                    <div style="padding: 16px 24px; border-top: 1px solid #222a3d; background: #0b1326; display: flex; align-items: center; justify-content: flex-end;">
                        <button
                            type="button"
                            wire:click="closeDetailModal"
                            class="exec-btn-primary">
                            Close Roster
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
