<x-filament-panels::page>
@php
    $college = $record;
    $collegeName = $college->name;
    $univName = $college->university_name;
    $address = $college->address ?? '';

    $allBatches = $college->evaluations()->with('evaluationInterns')->get();
    $activeBatches = $allBatches->where('is_archived', false);
    $archivedBatches = $allBatches->where('is_archived', true);

    $currentTab = $activeBatchTab ?? 'active';
    $displayedBatches = $currentTab === 'archived' ? $archivedBatches : $activeBatches;

    $activeBatchesCount = $activeBatches->count();
    $archivedBatchesCount = $archivedBatches->count();
    $batchesCount = $activeBatchesCount;
    $internsCount = $college->interns_count ?? 0;
    $activeInternsCount = $college->active_interns_count ?? 0;

    $words = preg_split('/\s+/', trim($collegeName));
    $initials = strtoupper(substr($words[0] ?? 'C', 0, 1) . substr($words[1] ?? '', 0, 1));
    if (empty($initials)) $initials = 'CL';

    $editCollegeUrl = \App\Filament\Resources\CollegeResource::getUrl('edit', ['record' => $college]);
    $createBatchUrl = \App\Filament\Resources\CollegeEvaluationResource::getUrl('create', ['college_id' => $college->id]);
    $collegesListUrl = \App\Filament\Resources\CollegeResource::getUrl('index');
@endphp

<style>
    /* ── Stitch Dark Theme View College Page ── */
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
        white-space: nowrap;
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

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd; display: flex; flex-direction: column; gap: 24px;">

    {{-- ── 1. Top Hero College Profile Card ── --}}
    <div class="pr-card" style="padding: 24px 28px; position: relative; overflow: hidden;">
        <div style="position: absolute; right: -50px; top: -50px; width: 240px; height: 240px; background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

        {{-- Top Identity & Action Header --}}
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; position: relative; z-index: 1;">
            
            {{-- Left Emblem & Name --}}
            <div style="display: flex; align-items: center; gap: 18px; min-width: 0; flex: 1;">
                <div style="width: 58px; height: 58px; border-radius: 16px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 800; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); border: 1.5px solid rgba(255, 255, 255, 0.15);">
                    {{ $initials }}
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
                        @if ($univName)
                            <span class="pr-badge" style="background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35);">
                                🏛️ {{ $univName }}
                            </span>
                        @else
                            <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25);">
                                🏢 Autonomous Institution
                            </span>
                        @endif

                        @if ($address)
                            <span class="pr-badge" style="background: rgba(59, 130, 246, 0.12); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);">
                                📍 {{ $address }}
                            </span>
                        @endif

                        <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                            📊 {{ $activeBatchesCount }} Active {{ \Illuminate\Support\Str::plural('Batch', $activeBatchesCount) }}
                        </span>

                        @if ($archivedBatchesCount > 0)
                            <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">
                                📁 {{ $archivedBatchesCount }} Archived
                            </span>
                        @endif

                        <span class="pr-badge" style="background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.35);">
                            👥 {{ $internsCount }} {{ \Illuminate\Support\Str::plural('Intern', $internsCount) }}
                        </span>
                    </div>

                    <h1 style="font-size: 24px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.3; letter-spacing: -0.01em;">
                        {{ $collegeName }}
                    </h1>
                    <p style="font-size: 13px; color: #8e909f; margin: 4px 0 0 0;">
                        {{ $univName ? 'Affiliated with ' . $univName : 'Autonomous / Direct Campus' }}
                    </p>
                </div>
            </div>

            {{-- Right Actions --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
                <a href="{{ $collegesListUrl }}" class="pr-btn-secondary" title="Return to College Directory">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>All Colleges</span>
                </a>

                <a href="{{ $editCollegeUrl }}" class="pr-btn-secondary" title="Edit College Profile">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit College</span>
                </a>

                <a href="{{ $createBatchUrl }}"
                   style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: #ffffff; border: none; font-size: 13px; font-weight: 700; padding: 8px 18px; border-radius: 9px; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.4); transition: transform 0.15s ease;"
                   onmouseover="this.style.transform='scale(1.02)';"
                   onmouseout="this.style.transform='scale(1)';"
                   title="Create new internship batch for this college">
                    <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New Evaluation Batch</span>
                </a>
            </div>
        </div>

        {{-- Nested Metadata Panel (#0b1326) --}}
        <div style="margin-top: 20px; background-color: #0b1326; border: 1px solid #1c2638; border-radius: 12px; padding: 16px 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">Affiliated University</div>
                    <div style="font-size: 14px; font-weight: 600; color: #dae2fd; margin-top: 3px;">
                        {{ $univName ?: 'Autonomous / Direct Campus' }}
                    </div>
                </div>

                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">Campus Address</div>
                    <div style="font-size: 14px; font-weight: 600; color: #dae2fd; margin-top: 3px;">
                        {{ $address ?: 'No address specified' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Internship Batches & Academic Evaluations Section ── --}}
    <div>
        {{-- Section Header --}}
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <h2 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">
                        Internship Batches & Academic Evaluations
                    </h2>
                    <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">
                        Manage active and archived evaluation batches, marking rubrics, student scorecards, and PDF reports.
                    </p>
                </div>
            </div>

            <a href="{{ $createBatchUrl }}"
               class="pr-btn-primary"
               title="Create New Evaluation Batch for this College">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>New Evaluation Batch</span>
            </a>
        </div>

        {{-- Tabs for Active vs Archived Batches --}}
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; border-bottom: 1px solid #1c2638; padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <button
                    type="button"
                    wire:click="setBatchTab('active')"
                    style="padding: 7px 16px; border-radius: 9px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 8px; border: 1px solid {{ $currentTab === 'active' ? '#3b82f6' : '#222a3d' }}; background: {{ $currentTab === 'active' ? 'rgba(59, 130, 246, 0.15)' : '#0b1326' }}; color: {{ $currentTab === 'active' ? '#60a5fa' : '#94a3b8' }};">
                    <span>🟢 Active Batches</span>
                    <span style="background: {{ $currentTab === 'active' ? '#2563eb' : '#1e293b' }}; color: #ffffff; padding: 1px 7px; border-radius: 9999px; font-size: 11px;">
                        {{ $activeBatchesCount }}
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="setBatchTab('archived')"
                    style="padding: 7px 16px; border-radius: 9px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 8px; border: 1px solid {{ $currentTab === 'archived' ? '#f59e0b' : '#222a3d' }}; background: {{ $currentTab === 'archived' ? 'rgba(245, 158, 11, 0.15)' : '#0b1326' }}; color: {{ $currentTab === 'archived' ? '#fbbf24' : '#94a3b8' }};">
                    <span>📁 Archived Batches</span>
                    <span style="background: {{ $currentTab === 'archived' ? '#d97706' : '#1e293b' }}; color: #ffffff; padding: 1px 7px; border-radius: 9999px; font-size: 11px;">
                        {{ $archivedBatchesCount }}
                    </span>
                </button>
            </div>

            <div style="font-size: 12px; color: #64748b;">
                Showing {{ $displayedBatches->count() }} {{ $currentTab === 'archived' ? 'archived' : 'active' }} {{ \Illuminate\Support\Str::plural('batch', $displayedBatches->count()) }}
            </div>
        </div>

        {{-- Batches Card Grid --}}
        @if ($displayedBatches->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px;">
                @foreach ($displayedBatches as $eval)
                    @php
                        $evalViewUrl = \App\Filament\Resources\CollegeEvaluationResource::getUrl('view', ['record' => $eval]);
                        $evalEditUrl = \App\Filament\Resources\CollegeEvaluationResource::getUrl('edit', ['record' => $eval]);
                        $enrolledCount = $eval->evaluationInterns->count();

                        $tot = rtrim(rtrim(number_format((float)$eval->total_marks, 2), '0'), '.');
                        $att = rtrim(rtrim(number_format((float)$eval->max_attendance_marks, 2), '0'), '.');
                        $task = rtrim(rtrim(number_format((float)$eval->max_task_marks, 2), '0'), '.');
                        $customCriteriaList = $eval->getCustomCriteriaList();
                    @endphp

                    <div
                        class="pr-report-card"
                        onclick="window.location.href='{{ $evalViewUrl }}'"
                        title="Click to open {{ $eval->batch_title ?: 'Evaluation Batch' }} marks scorecard">

                        {{-- Top Header Row --}}
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35);">
                                        ⏳ {{ $eval->duration ?: '1 Month' }}
                                    </span>

                                    <span class="pr-badge" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35);">
                                        🗓️ {{ $eval->academic_year ?: 'Session TBD' }}
                                    </span>

                                    @if ($eval->course_degree)
                                        <span class="pr-badge" style="background: rgba(245, 158, 11, 0.12); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.25);">
                                            🎓 {{ $eval->course_degree }}
                                        </span>
                                    @endif

                                    @if ($eval->is_archived)
                                        <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">
                                            📁 Archived
                                        </span>
                                    @endif
                                </div>

                                <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                    👥 {{ $enrolledCount }} {{ \Illuminate\Support\Str::plural('Student', $enrolledCount) }}
                                </span>
                            </div>

                            <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 4px 0; line-height: 1.35; letter-spacing: -0.01em;">
                                {{ $eval->batch_title ?: 'General Evaluation Batch' }}
                            </h3>

                            <div style="font-size: 12px; color: #8e909f;">
                                Evaluation Date: <strong style="color: #dae2fd;">{{ $eval->evaluation_date ? $eval->evaluation_date->format('d M Y') : 'Date TBD' }}</strong>
                            </div>
                        </div>

                        {{-- Middle Nested Stats Box (#0b1326) --}}
                        <div style="background-color: #0b1326; border: 1px solid #1c2638; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 10px;">
                            <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">
                                Marking Scheme (Total: {{ $tot }} Marks)
                            </div>

                            @php
                                $pillCount = 2 + count($customCriteriaList);
                                $gridCols = min(4, $pillCount);
                            @endphp
                            <div style="display: grid; grid-template-columns: repeat({{ $gridCols }}, 1fr); gap: 8px;">
                                <div style="background-color: #131b2e; border: 1px solid #1f293d; border-radius: 8px; padding: 7px 6px; text-align: center;">
                                    <div style="font-size: 9.5px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Attendance</div>
                                    <div style="font-size: 16px; font-weight: 800; color: #34d399; margin-top: 2px;">{{ $att }} M</div>
                                </div>

                                <div style="background-color: #131b2e; border: 1px solid #1f293d; border-radius: 8px; padding: 7px 6px; text-align: center;">
                                    <div style="font-size: 9.5px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Task Deliverables</div>
                                    <div style="font-size: 16px; font-weight: 800; color: #60a5fa; margin-top: 2px;">{{ $task }} M</div>
                                </div>

                                @foreach ($customCriteriaList as $crit)
                                    <div style="background-color: #131b2e; border: 1px solid #1f293d; border-radius: 8px; padding: 7px 6px; text-align: center;">
                                        <div style="font-size: 9.5px; font-weight: 700; color: #8e909f; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $crit['name'] }}">
                                            {{ \Illuminate\Support\Str::limit($crit['name'], 12) }}
                                        </div>
                                        <div style="font-size: 16px; font-weight: 800; color: #fcd34d; margin-top: 2px;">{{ rtrim(rtrim(number_format((float)$crit['max_marks'], 2), '0'), '.') }} M</div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($eval->evaluation_period_start || $eval->evaluation_period_end)
                                <div style="font-size: 11px; color: #64748b; padding-top: 4px; border-top: 1px solid #172238;">
                                    🗓️ Period: {{ $eval->evaluation_period_start ? $eval->evaluation_period_start->format('d M') : 'Start' }} to {{ $eval->evaluation_period_end ? $eval->evaluation_period_end->format('d M Y') : 'End' }}
                                </div>
                            @endif
                        </div>

                        {{-- Card Bottom Actions (Edit Batch, Archive/Restore, Delete, PDF, Open Workspace) --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 10px; border-top: 1px solid #1c2638;">
                            {{-- Left Actions: Edit Batch, Archive / Restore & Delete Batch --}}
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <a href="{{ $evalEditUrl }}"
                                   onclick="event.stopPropagation();"
                                   class="pr-btn-secondary"
                                   title="Edit Batch Parameters & Marking Scheme">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span>Edit</span>
                                </a>

                                @if ($eval->is_archived)
                                    <button
                                        type="button"
                                        wire:click.stop="unarchiveBatch({{ $eval->id }})"
                                        onclick="event.stopPropagation();"
                                        class="pr-btn-secondary"
                                        style="color: #34d399; border-color: rgba(16, 185, 129, 0.4); padding: 5px 11px;"
                                        title="Restore this Batch to Active Batches">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4" />
                                        </svg>
                                        <span>Restore</span>
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click.stop="confirmArchiveBatch({{ $eval->id }}, '{{ addslashes($eval->batch_title ?: 'Batch') }}')"
                                        onclick="event.stopPropagation();"
                                        class="pr-icon-btn"
                                        style="color: #f59e0b;"
                                        title="Archive this Evaluation Batch">
                                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                    </button>
                                @endif

                                <button
                                    type="button"
                                    wire:click.stop="confirmDeleteBatch({{ $eval->id }}, '{{ addslashes($eval->batch_title ?: 'Batch') }}')"
                                    onclick="event.stopPropagation();"
                                    class="pr-icon-btn"
                                    style="color: #ef4444;"
                                    title="Delete Evaluation Batch">
                                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Right Actions: Download PDF & Open Evaluation Workspace --}}
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button
                                    type="button"
                                    wire:click.stop="downloadBatchPdf({{ $eval->id }})"
                                    onclick="event.stopPropagation();"
                                    class="pr-icon-btn"
                                    style="color: #10b981;"
                                    title="Download PDF Evaluation Report">
                                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </button>

                                <a href="{{ $evalViewUrl }}"
                                   onclick="event.stopPropagation();"
                                   class="pr-btn-primary"
                                   title="Open Batch Workspace & Student Marks">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Open Batch</span>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="pr-card" style="padding: 48px 24px; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 16px; background-color: {{ $currentTab === 'archived' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(99, 102, 241, 0.1)' }}; color: {{ $currentTab === 'archived' ? '#fbbf24' : '#818cf8' }}; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; border: 1px solid {{ $currentTab === 'archived' ? 'rgba(245, 158, 11, 0.25)' : 'rgba(99, 102, 241, 0.25)' }};">
                    @if ($currentTab === 'archived')
                        <svg style="width: 30px; height: 30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    @else
                        <svg style="width: 30px; height: 30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    @endif
                </div>
                <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 6px 0;">
                    @if ($currentTab === 'archived')
                        No Archived Batches
                    @else
                        No Active Internship Batches Created Yet
                    @endif
                </h3>
                <p style="font-size: 13px; color: #8e909f; max-width: 440px; margin: 0 auto 20px auto;">
                    @if ($currentTab === 'archived')
                        Completed or archived evaluation cohorts for {{ $collegeName }} will be preserved and accessible here.
                    @else
                        Create an internship batch for {{ $collegeName }} to allocate students, configure the grading scheme, and generate evaluation reports.
                    @endif
                </p>
                @if ($currentTab === 'active')
                    <a href="{{ $createBatchUrl }}"
                       style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; padding: 10px 22px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4);">
                        <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Create First Evaluation Batch</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- ── 3. Archive Batch Confirmation Modal ── --}}
    @if (!empty($showArchiveBatchModal))
        <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="pr-card" style="width: 100%; max-width: 440px; padding: 24px; border: 1.5px solid #222a3d; box-shadow: 0 20px 40px rgba(0,0,0,0.6);" wire:click.outside="closeArchiveBatchModal">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Archive Batch?</h3>
                        <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">Move this batch to archived cohorts.</p>
                    </div>
                </div>

                <p style="font-size: 13px; color: #cbd5e1; line-height: 1.5; margin: 0 0 20px 0;">
                    Are you sure you want to archive batch <strong style="color: #ffffff;">"{{ $batchTitleToArchive }}"</strong>? It will be moved to the Archived Batches tab and can be restored at any time.
                </p>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <button
                        type="button"
                        wire:click="closeArchiveBatchModal"
                        class="pr-btn-secondary"
                        style="color: #94a3b8;">
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="executeArchiveBatch"
                        style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: #ffffff; border: none; font-size: 12.5px; font-weight: 700; padding: 8px 18px; border-radius: 9px; cursor: pointer; box-shadow: 0 2px 10px rgba(245, 158, 11, 0.4);">
                        Archive Batch
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── 4. Delete Batch Confirmation Modal ── --}}
    @if (!empty($showDeleteBatchModal))
        <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="pr-card" style="width: 100%; max-width: 440px; padding: 24px; border: 1.5px solid #222a3d; box-shadow: 0 20px 40px rgba(0,0,0,0.6);" wire:click.outside="closeDeleteBatchModal">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background-color: rgba(239, 68, 68, 0.15); color: #f87171; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Delete Batch?</h3>
                        <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">This action cannot be undone.</p>
                    </div>
                </div>

                <p style="font-size: 13px; color: #cbd5e1; line-height: 1.5; margin: 0 0 20px 0;">
                    Are you sure you want to delete batch <strong style="color: #ffffff;">"{{ $batchTitleToDelete }}"</strong>? All calculated marks for enrolled interns in this batch will be removed.
                </p>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <button
                        type="button"
                        wire:click="closeDeleteBatchModal"
                        class="pr-btn-secondary"
                        style="color: #94a3b8;">
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="executeDeleteBatch"
                        style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: #ffffff; border: none; font-size: 12.5px; font-weight: 700; padding: 8px 18px; border-radius: 9px; cursor: pointer; box-shadow: 0 2px 10px rgba(220, 38, 38, 0.4);">
                        Delete Batch
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
