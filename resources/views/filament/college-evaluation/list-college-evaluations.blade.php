<x-filament-panels::page>
@php
    $createUrl = \App\Filament\Resources\CollegeEvaluationResource::getUrl('create');
    $collegesUrl = \App\Filament\Resources\CollegeResource::getUrl('index');
@endphp

<style>
    /* ── Stitch Dark Theme College Evaluations Directory ── */
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                            College & University Evaluations
                        </h1>
                        <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35);">
                            {{ $stats['total_evaluations'] }} Batches Evaluated
                        </span>
                    </div>
                    <p style="font-size: 12.5px; color: #8e909f; margin: 4px 0 0 0;">
                        Continuous academic 2020 evaluations, internship batch cohorts, student scorecards, and official PDF certificates.
                    </p>
                </div>
            </div>

            {{-- Right Quick Actions --}}
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ $collegesUrl }}" class="pr-btn-secondary" title="View Colleges Directory">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Colleges Directory</span>
                </a>

                <a href="{{ $createUrl }}"
                   style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: #ffffff; border: none; font-size: 13px; font-weight: 700; padding: 10px 20px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 16px rgba(217, 119, 6, 0.4); transition: transform 0.15s ease;"
                   onmouseover="this.style.transform='scale(1.02)';"
                   onmouseout="this.style.transform='scale(1)';"
                   title="Create new college evaluation batch">
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New College Evaluation</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Top 4 KPI Statistics Cards ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        {{-- Card 1: Total Batches --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(99, 102, 241, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Batches</div>
                <div style="font-size: 26px; font-weight: 800; color: #ffffff; line-height: 1.1; margin-top: 2px;">{{ $stats['total_evaluations'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Cohorts Evaluated</div>
            </div>
        </div>

        {{-- Card 2: Total Graded Interns --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(16, 185, 129, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Interns Graded</div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; line-height: 1.1; margin-top: 2px;">{{ $stats['total_interns_evaluated'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Students Assessed</div>
            </div>
        </div>

        {{-- Card 3: Institutions Represented --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(245, 158, 11, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Institutions</div>
                <div style="font-size: 26px; font-weight: 800; color: #fbbf24; line-height: 1.1; margin-top: 2px;">{{ $stats['unique_colleges'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Colleges & Universities</div>
            </div>
        </div>

        {{-- Card 4: Average Batch Size --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(59, 130, 246, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Avg Batch Size</div>
                <div style="font-size: 26px; font-weight: 800; color: #dae2fd; line-height: 1.1; margin-top: 2px;">{{ $stats['avg_batch_size'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Students per Batch</div>
            </div>
        </div>
    </div>

    {{-- ── 3. Search & Filter Toolbar ── --}}
    <div class="pr-card" style="padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        {{-- Left Filters --}}
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            {{-- College Filter --}}
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">College:</span>
                <select
                    wire:model.live="collegeFilter"
                    style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 7px 12px; font-size: 12.5px; font-weight: 600; color: #dae2fd; outline: none; cursor: pointer;">
                    <option value="all">🏛️ All Colleges</option>
                    @foreach ($colleges as $cName)
                        <option value="{{ $cName }}">{{ $cName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Duration Filter --}}
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">Duration:</span>
                <select
                    wire:model.live="durationFilter"
                    style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 7px 12px; font-size: 12.5px; font-weight: 600; color: #dae2fd; outline: none; cursor: pointer;">
                    <option value="all">⏳ All Durations</option>
                    @foreach ($durations as $dName)
                        <option value="{{ $dName }}">{{ $dName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Right Search & Per Page --}}
        <div style="display: flex; align-items: center; gap: 12px; flex-grow: 1; max-width: 440px; min-width: 260px;">
            <div style="position: relative; flex-grow: 1;">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search batch, college, course, or session..."
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

    {{-- ── 4. Evaluations Displayed in CARD STYLE (Grid) ── --}}
    @if ($evaluations->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px;">
            @foreach ($evaluations as $eval)
                @php
                    $evalViewUrl = \App\Filament\Resources\CollegeEvaluationResource::getUrl('view', ['record' => $eval]);
                    $evalEditUrl = \App\Filament\Resources\CollegeEvaluationResource::getUrl('edit', ['record' => $eval]);
                    $collegeViewUrl = $eval->college_id ? \App\Filament\Resources\CollegeResource::getUrl('view', ['record' => $eval->college_id]) : null;

                    $enrolledCount = $eval->evaluationInterns->count();
                    $collegeDisplayName = $eval->college?->name ?? $eval->college_name;
                    $univDisplayName = $eval->college?->university_name ?? $eval->university_name;

                    $tot = rtrim(rtrim(number_format((float)$eval->total_marks, 2), '0'), '.');
                    $att = rtrim(rtrim(number_format((float)$eval->max_attendance_marks, 2), '0'), '.');
                    $task = rtrim(rtrim(number_format((float)$eval->max_task_marks, 2), '0'), '.');
                    $customCriteriaList = $eval->getCustomCriteriaList();
                @endphp

                <div
                    class="pr-report-card"
                    onclick="window.location.href='{{ $evalViewUrl }}'"
                    title="Click to open {{ $eval->batch_title ?: 'Evaluation Batch' }} scorecard workspace">

                    {{-- Card Top Header Row --}}
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
                            </div>

                            <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                👥 {{ $enrolledCount }} {{ \Illuminate\Support\Str::plural('Student', $enrolledCount) }}
                            </span>
                        </div>

                        {{-- Batch Title & College Name --}}
                        <h2 style="font-size: 16.5px; font-weight: 800; color: #ffffff; margin: 0 0 4px 0; line-height: 1.35; letter-spacing: -0.01em;">
                            {{ $eval->batch_title ?: 'General Evaluation Batch' }}
                        </h2>

                        <div style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: #8e909f; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <span>🏛️</span>
                            @if ($collegeViewUrl)
                                <a href="{{ $collegeViewUrl }}" onclick="event.stopPropagation();" style="color: #60a5fa; text-decoration: none; font-weight: 600;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
                                    {{ $collegeDisplayName }}
                                </a>
                            @else
                                <span style="color: #dae2fd; font-weight: 600;">{{ $collegeDisplayName }}</span>
                            @endif

                            @if ($univDisplayName)
                                <span style="color: #64748b;">• {{ $univDisplayName }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Middle: Nested Scheme Panel (#0b1326) --}}
                    <div style="background-color: #0b1326; border: 1px solid #1c2638; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px;">
                            <span style="color: #8e909f; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Scheme</span>
                            <span style="font-weight: 800; color: #ffffff;">Total: {{ $tot }} Marks</span>
                        </div>

                        {{-- Score Pill Boxes --}}
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
                                <div style="font-size: 9.5px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Task Work</div>
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

                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: #94a3b8; padding-top: 4px; border-top: 1px solid #172238;">
                            <span>🗓️ {{ $eval->evaluation_date ? $eval->evaluation_date->format('d M Y') : 'Date TBD' }}</span>
                            <span style="color: #64748b;">Created {{ $eval->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    {{-- Card Bottom Actions --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 10px; border-top: 1px solid #1c2638;">
                        {{-- Left: Edit & Delete --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a
                                href="{{ $evalEditUrl }}"
                                onclick="event.stopPropagation();"
                                class="pr-btn-secondary"
                                title="Edit Batch Parameters & Marking Scheme">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Edit</span>
                            </a>

                            <button
                                type="button"
                                wire:click.stop="confirmDelete({{ $eval->id }}, '{{ addslashes($eval->batch_title ?: 'Batch') }}')"
                                class="pr-icon-btn"
                                style="color: #ef4444;"
                                title="Delete Evaluation Batch">
                                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        {{-- Right: PDF Download & Open Evaluation Workspace --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button
                                type="button"
                                wire:click.stop="downloadPdf({{ $eval->id }})"
                                class="pr-btn-secondary"
                                style="color: #10b981; border-color: rgba(16, 185, 129, 0.35); background: rgba(16, 185, 129, 0.08);"
                                title="Download PDF Evaluation Report">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>PDF</span>
                            </button>

                            <a
                                href="{{ $evalViewUrl }}"
                                onclick="event.stopPropagation();"
                                class="pr-btn-primary"
                                title="Open Batch Evaluation Workspace">
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

        {{-- Pagination --}}
        <div style="margin-top: 10px;">
            {{ $evaluations->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="pr-card" style="padding: 48px 24px; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 16px; background-color: rgba(59, 130, 246, 0.1); color: #60a5fa; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; border: 1px solid rgba(59, 130, 246, 0.25);">
                <svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0 0 6px 0;">
                No College Evaluations Found
            </h3>
            <p style="font-size: 13px; color: #8e909f; max-width: 440px; margin: 0 auto 20px auto;">
                @if (filled($search) || $collegeFilter !== 'all' || $durationFilter !== 'all')
                    No batches match your selected search or filter criteria. Try resetting filters.
                @else
                    Start by generating your first college evaluation batch to assess enrolled interns on attendance and tasks.
                @endif
            </p>
            <a href="{{ $createUrl }}"
               style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; padding: 10px 22px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4);">
                <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create Evaluation Batch</span>
            </a>
        </div>
    @endif

    {{-- ── 5. Delete Confirmation Modal ── --}}
    @if ($showDeleteModal)
        <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="pr-card" style="width: 100%; max-width: 440px; padding: 24px; border: 1.5px solid #222a3d; box-shadow: 0 20px 40px rgba(0,0,0,0.6);" wire:click.outside="closeDeleteModal">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background-color: rgba(239, 68, 68, 0.15); color: #f87171; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Delete Evaluation Batch?</h3>
                        <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">This action cannot be undone.</p>
                    </div>
                </div>

                <p style="font-size: 13px; color: #cbd5e1; line-height: 1.5; margin: 0 0 20px 0;">
                    Are you sure you want to remove <strong style="color: #ffffff;">"{{ $batchTitleToDelete }}"</strong>? All calculated intern marks and evaluation records for this batch will be permanently deleted.
                </p>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="pr-btn-secondary"
                        style="color: #94a3b8;">
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="executeDelete"
                        style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: #ffffff; border: none; font-size: 12.5px; font-weight: 700; padding: 8px 18px; border-radius: 9px; cursor: pointer; box-shadow: 0 2px 10px rgba(220, 38, 38, 0.4);">
                        Delete Batch
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
