<x-filament-panels::page>
@php
    $createUrl = \App\Filament\Resources\ProgressReportResource::getUrl('create');
@endphp

<style>
    /* ── Stitch Dark Theme Progress Reports Directory ── */
    .pr-card {
        background-color: #131b2e;
        border: 1.5px solid #222a3d;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    }

    .pr-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .pr-report-card {
        background-color: #131b2e;
        border: 1.5px solid #222a3d;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 16px;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .pr-report-card:hover {
        transform: translateY(-3px);
        border-color: #3b82f6;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), 0 0 16px rgba(59, 130, 246, 0.2);
    }

    .pr-report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #1e40af, #3b82f6);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .pr-report-card:hover::before {
        opacity: 1;
    }

    .pr-search-input {
        width: 100%;
        background-color: #0b1326;
        border: 1px solid #222a3d;
        border-radius: 10px;
        padding: 8px 14px 8px 36px;
        font-size: 13px;
        color: #dae2fd;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-sizing: border-box;
    }

    .pr-search-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }

    .pr-filter-btn {
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .pr-btn-secondary {
        background-color: #172238;
        color: #93c5fd;
        border: 1px solid #25334d;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 13px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .pr-btn-secondary:hover {
        background-color: #1e2d4a;
        border-color: #3b82f6;
        color: #ffffff;
    }

    .pr-btn-primary {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: #ffffff;
        border: none;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 15px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.4);
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .pr-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.55);
    }

    .pr-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #222a3d;
        background-color: #0b1326;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .pr-icon-btn:hover {
        border-color: #3b82f6;
        color: #ffffff;
        background-color: #172238;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd; display: flex; flex-direction: column; gap: 20px;">

    {{-- ── 1. Top Hero Context & Action Banner ── --}}
    <div class="pr-card" style="padding: 22px 26px; position: relative; overflow: hidden;">
        <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; position: relative; z-index: 1;">
            {{-- Left Title & Info --}}
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                    <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                            Progress Reports
                        </h1>
                        <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35);">
                            {{ $stats['total_reports'] }} Total Created
                        </span>
                    </div>
                    <p style="font-size: 12.5px; color: #8e909f; margin: 4px 0 0 0;">
                        Click on any created report card to open its full evaluation workspace, review daily work logs, and write weekly remarks.
                    </p>
                </div>
            </div>

            {{-- Right Quick Actions --}}
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ $createUrl }}"
                   style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: #ffffff; border: none; font-size: 13px; font-weight: 700; padding: 10px 20px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 16px rgba(217, 119, 6, 0.4); transition: transform 0.15s ease;"
                   onmouseover="this.style.transform='scale(1.02)';"
                   onmouseout="this.style.transform='scale(1)';"
                   title="Generate a new progress report">
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Create Progress Report</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Top 4 KPI Statistics Cards ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        {{-- Card 1: Total Reports --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(99, 102, 241, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Reports</div>
                <div style="font-size: 26px; font-weight: 800; color: #ffffff; line-height: 1.1; margin-top: 2px;">{{ $stats['total_reports'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Weekly Cohort Evaluations</div>
            </div>
        </div>

        {{-- Card 2: Completed Evaluations --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(16, 185, 129, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Fully Completed</div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; line-height: 1.1; margin-top: 2px;">{{ $stats['completed_reports'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">All interns evaluated</div>
            </div>
        </div>

        {{-- Card 3: In Progress Reviews --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(245, 158, 11, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">In Progress</div>
                <div style="font-size: 26px; font-weight: 800; color: #fbbf24; line-height: 1.1; margin-top: 2px;">{{ $stats['in_progress_reports'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Pending supervisor review</div>
            </div>
        </div>

        {{-- Card 4: Interns Evaluated Rate --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(59, 130, 246, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div style="flex-grow: 1;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Evaluations</div>
                    <span style="font-size: 11px; font-weight: 700; color: #60a5fa;">{{ $stats['eval_pct'] }}%</span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #dae2fd; line-height: 1.1; margin-top: 2px;">{{ $stats['total_evaluated'] }} / {{ $stats['total_enrolled'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Total intern reviews logged</div>
            </div>
        </div>
    </div>

    {{-- ── 3. Search, Filter Tabs & Display Toolbar ── --}}
    <div class="pr-card" style="padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        
        {{-- Status Filter Tabs --}}
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <div style="display: inline-flex; background: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 3px; gap: 2px;">
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'all')"
                    class="pr-filter-btn"
                    style="{{ $statusFilter === 'all' ? 'background-color: #1e40af; color: #ffffff;' : 'background-color: transparent; color: #8e909f;' }}">
                    All Reports ({{ $stats['total_reports'] }})
                </button>
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'in_progress')"
                    class="pr-filter-btn"
                    style="{{ $statusFilter === 'in_progress' ? 'background-color: #d97706; color: #ffffff;' : 'background-color: transparent; color: #8e909f;' }}">
                    ⏳ In Progress ({{ $stats['in_progress_reports'] }})
                </button>
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'completed')"
                    class="pr-filter-btn"
                    style="{{ $statusFilter === 'completed' ? 'background-color: #059669; color: #ffffff;' : 'background-color: transparent; color: #8e909f;' }}">
                    ✓ Completed ({{ $stats['completed_reports'] }})
                </button>
            </div>

            {{-- Target Mode Filter Dropdown --}}
            <select
                wire:model.live="selectionModeFilter"
                style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 7px 12px; font-size: 12.5px; font-weight: 600; color: #dae2fd; outline: none; cursor: pointer;">
                <option value="all">All Target Types</option>
                <option value="batch">📦 Batch Cohorts</option>
                <option value="team">🚀 Project Teams</option>
                <option value="intern">👥 Direct Interns</option>
            </select>
        </div>

        {{-- Search & Per Page --}}
        <div style="display: flex; align-items: center; gap: 12px; flex-grow: 1; max-width: 420px; min-width: 260px;">
            <div style="position: relative; flex-grow: 1;">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search report title, batch, or squad..."
                    class="pr-search-input"
                />
                <svg style="position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <select
                wire:model.live="perPage"
                style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 7px 10px; font-size: 12px; font-weight: 600; color: #8e909f; outline: none; cursor: pointer;">
                <option value="6">6 / page</option>
                <option value="12">12 / page</option>
                <option value="24">24 / page</option>
            </select>
        </div>

    </div>

    {{-- ── 4. Created Reports Displayed in CARD STYLE (Grid) ── --}}
    @if ($reports->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px;">
            @foreach ($reports as $report)
                @php
                    $viewUrl = \App\Filament\Resources\ProgressReportResource::getUrl('view', ['record' => $report]);
                    $editUrl = \App\Filament\Resources\ProgressReportResource::getUrl('edit', ['record' => $report]);

                    $totalInterns = $report->total_interns_count;
                    $evalCount = $report->evaluated_count;
                    $isDone = ($totalInterns > 0 && $evalCount === $totalInterns);
                    $pct = $totalInterns > 0 ? round(($evalCount / $totalInterns) * 100) : 0;

                    // Target title display
                    $targetLabel = match ($report->selection_mode) {
                        'batch' => '📦 Batch: ' . ($report->batch?->batch_name ?? 'Cohort'),
                        'team'  => '🚀 Squad: ' . ($report->team?->team_name ?? 'Team'),
                        default => '👥 Direct: ' . $totalInterns . ' Interns',
                    };
                @endphp

                <div
                    class="pr-report-card"
                    onclick="window.location.href='{{ $viewUrl }}'"
                    title="Click to open {{ $report->title }} evaluation workspace">
                    
                    {{-- ── Card Top Header Row ── --}}
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px;">
                            {{-- Target Cohort Tag & Report Type Badge --}}
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                @if ($report->isMonthly())
                                    <span class="pr-badge" style="background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35);">
                                        🟣 Monthly Review
                                    </span>
                                @else
                                    <span class="pr-badge" style="background: rgba(59, 130, 246, 0.12); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);">
                                        🔵 Weekly Report
                                    </span>
                                @endif
                                <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25);">
                                    {{ $targetLabel }}
                                </span>
                            </div>

                            {{-- Status Tag --}}
                            @if ($isDone)
                                <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                    ✓ All Evaluated
                                </span>
                            @else
                                <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">
                                    ⏳ {{ $evalCount }}/{{ $totalInterns }} Evaluated
                                </span>
                            @endif
                        </div>

                        {{-- Report Title & Date Duration --}}
                        <h2 style="font-size: 16.5px; font-weight: 800; color: #ffffff; margin: 0 0 6px 0; line-height: 1.35; letter-spacing: -0.01em;">
                            {{ $report->title }}
                        </h2>
                        
                        <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #94a3b8; font-family: ui-monospace, monospace; background: #0b1326; padding: 4px 9px; border-radius: 7px; border: 1px solid #1c2638;">
                            <span>🗓️</span>
                            <span>{{ $report->formatted_date_range }}</span>
                        </div>
                    </div>

                    {{-- ── Card Middle: Evaluation Progress Bar & Indicators ── --}}
                    <div style="background-color: #0b1326; border: 1px solid #1c2638; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 10px;">
                        {{-- Progress Percentage --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                            <span style="color: #8e909f; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Evaluation Progress</span>
                            <span style="font-weight: 800; color: {{ $isDone ? '#34d399' : '#60a5fa' }};">{{ $pct }}%</span>
                        </div>

                        {{-- Progress Track --}}
                        <div style="width: 100%; height: 6px; background-color: #172238; border-radius: 9999px; overflow: hidden;">
                            <div style="height: 100%; width: {{ $pct }}%; background: {{ $isDone ? 'linear-gradient(90deg, #059669 0%, #10b981 100%)' : 'linear-gradient(90deg, #1e40af 0%, #3b82f6 100%)' }}; border-radius: 9999px; transition: width 0.3s ease;"></div>
                        </div>

                        {{-- Metadata Metrics --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: #94a3b8; padding-top: 4px; border-top: 1px solid #172238;">
                            <span>👥 <strong style="color: #dae2fd;">{{ $totalInterns }}</strong> Enrolled</span>
                            <span>📝 <strong style="color: #60a5fa;">{{ $report->total_daily_logs_count }}</strong> Logs</span>
                            <span style="color: #64748b;">{{ $report->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    {{-- ── Card Bottom Action Buttons (With Edit Button) ── --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 10px; border-top: 1px solid #1c2638;">
                        
                        {{-- Left Actions: Edit Button & Delete Button --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            {{-- EDIT BUTTON (User specifically requested on the card) --}}
                            <a
                                href="{{ $editUrl }}"
                                onclick="event.stopPropagation();"
                                class="pr-btn-secondary"
                                title="Edit Report Parameters">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Edit</span>
                            </a>

                            {{-- Delete Report Button --}}
                            <button
                                type="button"
                                wire:click.stop="confirmDelete({{ $report->id }}, '{{ addslashes($report->title) }}')"
                                class="pr-icon-btn"
                                style="color: #ef4444;"
                                title="Delete Report">
                                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        {{-- Right Actions: Download PDF & View / Evaluate --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            {{-- Quick PDF Trigger --}}
                            <button
                                type="button"
                                wire:click.stop="openPdfModal({{ $report->id }})"
                                class="pr-icon-btn"
                                style="color: #10b981;"
                                title="Export PDF">
                                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </button>

                            {{-- View & Evaluate Button --}}
                            <a
                                href="{{ $viewUrl }}"
                                onclick="event.stopPropagation();"
                                class="pr-btn-primary"
                                title="Open View & Evaluate Report Workspace">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>View & Evaluate</span>
                            </a>
                        </div>

                    </div>

                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 10px;">
            {{ $reports->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="pr-card" style="padding: 48px 24px; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 16px; background-color: rgba(59, 130, 246, 0.1); color: #60a5fa; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; border: 1px solid rgba(59, 130, 246, 0.25);">
                <svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0 0 6px 0;">
                No Progress Reports Found
            </h3>
            <p style="font-size: 13px; color: #8e909f; max-width: 440px; margin: 0 auto 20px auto;">
                @if (filled($search) || $statusFilter !== 'all' || $selectionModeFilter !== 'all')
                    No reports match your selected search or filter criteria. Try resetting filters.
                @else
                    Start by generating your first weekly progress report to evaluate interns on daily tasks and attendance.
                @endif
            </p>
            <a href="{{ $createUrl }}"
               style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; padding: 10px 22px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4);">
                <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create Progress Report</span>
            </a>
        </div>
    @endif

    {{-- ── 5. PDF Export Modal ── --}}
    @if ($showPdfModal)
        <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="pr-card" style="width: 100%; max-width: 480px; padding: 26px; border: 1.5px solid #222a3d; box-shadow: 0 20px 40px rgba(0,0,0,0.6);" wire:click.outside="closePdfModal">
                
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center;">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Export Progress Report</h3>
                    </div>
                    <button type="button" wire:click="closePdfModal" style="background: none; border: none; color: #64748b; font-size: 20px; cursor: pointer;">&times;</button>
                </div>

                <p style="font-size: 13px; color: #8e909f; margin: 0 0 18px 0;">
                    Choose your export layout format:
                </p>

                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 22px;">
                    <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #0b1326; border: 1.5px solid {{ $pdfExportMode === 'single_pdf' ? '#3b82f6' : '#1c2638' }}; border-radius: 10px; cursor: pointer;">
                        <input type="radio" wire:model.live="pdfExportMode" value="single_pdf" style="margin-top: 3px; accent-color: #3b82f6;" />
                        <div>
                            <div style="font-size: 13.5px; font-weight: 700; color: #ffffff;">Single PDF (All Interns Combined)</div>
                            <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">Compiles all enrolled interns into a single structured multi-page document.</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #0b1326; border: 1.5px solid {{ $pdfExportMode === 'separate_pdfs' ? '#3b82f6' : '#1c2638' }}; border-radius: 10px; cursor: pointer;">
                        <input type="radio" wire:model.live="pdfExportMode" value="separate_pdfs" style="margin-top: 3px; accent-color: #3b82f6;" />
                        <div>
                            <div style="font-size: 13.5px; font-weight: 700; color: #ffffff;">Separate PDFs in ZIP Archive</div>
                            <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">Generates individual PDF files for each intern packed into a downloadable ZIP.</div>
                        </div>
                    </label>
                </div>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="closePdfModal" class="pr-btn-secondary">
                        Cancel
                    </button>
                    <button type="button" wire:click="executePdfDownload" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #ffffff; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 9px; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);">
                        Download PDF Now
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ── 6. Delete Confirmation Modal ── --}}
    @if ($showDeleteModal)
        <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="pr-card" style="width: 100%; max-width: 440px; padding: 26px; border: 1.5px solid rgba(239, 68, 68, 0.5); box-shadow: 0 20px 40px rgba(0,0,0,0.6);" wire:click.outside="closeDeleteModal">
                
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(239, 68, 68, 0.15); color: #f87171; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Delete Progress Report</h3>
                        <div style="font-size: 12px; color: #f87171; margin-top: 2px;">This action cannot be undone.</div>
                    </div>
                </div>

                <p style="font-size: 13px; color: #cbd5e1; margin: 0 0 20px 0; line-height: 1.5;">
                    Are you sure you want to delete <strong style="color: #ffffff;">{{ $reportTitleToDelete }}</strong>? All evaluations and weekly summaries associated with this report will be deleted.
                </p>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="closeDeleteModal" class="pr-btn-secondary">
                        Cancel
                    </button>
                    <button type="button" wire:click="executeDelete" style="background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%); color: #ffffff; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 9px; cursor: pointer; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);">
                        Confirm Delete
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
