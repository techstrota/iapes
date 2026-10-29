<x-filament-panels::page>
    @php
        $eval = $this->record;
        $evalInterns = $eval->evaluationInterns()->with(['intern.batch', 'intern.team'])->orderBy('sort_order')->orderBy('id')->get();
        $activeColumns = $eval->getActiveColumns();
        $isDirectMode = $eval->isDirectMode();

        $totalMaxFormatted = rtrim(rtrim(number_format((float)$eval->total_marks, 2), '0'), '.');
        $attMaxFormatted = rtrim(rtrim(number_format((float)$eval->max_attendance_marks, 2), '0'), '.');
        $taskMaxFormatted = rtrim(rtrim(number_format((float)$eval->max_task_marks, 2), '0'), '.');

        $totalCount = $evalInterns->count();
        $avgScore = $totalCount > 0 ? round($evalInterns->avg('total_obtained_marks'), 1) : 0;
        $topScore = $totalCount > 0 ? round($evalInterns->max('total_obtained_marks'), 1) : 0;
        $outstandingCount = $evalInterns->whereIn('grade', ['O', 'A+'])->count();
    @endphp

    <style>
        .exec-card {
            background-color: #131b2e;
            border: 1px solid #222a3d;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }
        .exec-table th {
            background-color: #0b1326;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 14px;
            border-bottom: 1px solid #222a3d;
        }
        .exec-table td {
            padding: 12px 14px;
            font-size: 13px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            vertical-align: middle;
        }
        .exec-table tr:hover {
            background-color: rgba(30, 41, 59, 0.4);
        }
        .badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
        }
        .btn-exec-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff !important;
            font-size: 12.5px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.35);
            text-decoration: none !important;
        }
        .btn-exec-primary:hover {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            transform: translateY(-1px);
            color: #ffffff !important;
            text-decoration: none !important;
        }
        .btn-exec-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0b1326;
            color: #94a3b8;
            font-size: 12.5px;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #222a3d;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }
        .btn-exec-secondary:hover {
            background: #1e293b;
            color: #ffffff !important;
            border-color: #3b82f6;
            text-decoration: none !important;
        }
        .exec-input {
            width: 100%;
            padding: 8px 12px;
            background: #0b1326;
            border: 1.5px solid #222a3d;
            border-radius: 8px;
            color: #ffffff;
            font-size: 13px;
        }
        .exec-input:focus {
            border-color: #3b82f6;
            outline: none;
        }
        .exec-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-color: #0b1326 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 8px center !important;
            background-size: 13px 13px !important;
            padding: 4px 28px 4px 10px !important;
            border: 1.5px solid #222a3d !important;
            border-radius: 8px !important;
            color: #ffffff !important;
            font-size: 11.5px !important;
            font-weight: 600 !important;
            height: 28px !important;
            line-height: 1.2 !important;
            cursor: pointer;
        }
        .exec-select:focus {
            border-color: #3b82f6 !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3b82f6 !important;
        }
        .exec-select option {
            background-color: #0b1326;
            color: #ffffff;
        }
    </style>

    <div style="display: flex; flex-direction: column; gap: 20px;">

        {{-- ── 1. Top Executive Banner ── --}}
        <div class="exec-card" style="padding: 22px 24px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(59, 130, 246, 0.15); border: 1.5px solid rgba(59, 130, 246, 0.3); display: flex; align-items: center; justify-content: center; font-size: 26px; color: #60a5fa;">
                        🏛️
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                                {{ $eval->batch_title ?: 'Internship Evaluation Batch' }}
                            </h1>
                            <span class="badge-pill" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3);">
                                ⏱️ {{ $eval->duration ?: '1 Month' }}
                            </span>
                            <span class="badge-pill" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                                {{ $eval->academic_year ?: 'Session 2025–2026' }}
                            </span>
                            <span class="badge-pill" style="{{ $isDirectMode ? 'background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);' : 'background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);' }}">
                                {{ $isDirectMode ? '🎯 Direct Marks Mode' : '📊 Criteria Breakdown Mode' }}
                            </span>
                            @if ($eval->is_archived)
                                <span class="badge-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">
                                    📁 Archived
                                </span>
                            @endif
                        </div>
                        <p style="font-size: 13px; color: #94a3b8; margin: 4px 0 0;">
                            <strong>Institution:</strong> {{ $eval->college?->name ?? $eval->college_name }}
                            @if ($eval->college?->university_name || $eval->university_name)
                                &bull; <strong>University:</strong> {{ $eval->college?->university_name ?? $eval->university_name }}
                            @endif
                            @if ($eval->course_degree) &bull; <strong>Course:</strong> {{ $eval->course_degree }} @endif
                            @if ($eval->evaluation_date) &bull; <strong>Evaluation Date:</strong> {{ $eval->evaluation_date->format('d M Y') }} @endif
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <button type="button" wire:click="openAddStudentModal" class="btn-exec-primary" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 2px 10px rgba(16, 185, 129, 0.35);" title="Add a custom student row directly to this evaluation roster">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Student Row</span>
                    </button>

                    @if (!$isDirectMode)
                        <button type="button" wire:click="recalculateAll" class="btn-exec-secondary" title="Recalculate all marks from live attendance and task records">
                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Recalculate Marks</span>
                        </button>
                    @endif

                    <button type="button" wire:click="openPdfPreviewModal" class="btn-exec-primary" style="background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 2px 10px rgba(99, 102, 241, 0.35);" title="Live Preview PDF & Adjust Table Layout">
                        <span>👁️ Preview &amp; Adjust PDF</span>
                    </button>

                    <a href="{{ route('college-evaluations.download-pdf', $eval->id) }}" class="btn-exec-primary" style="text-decoration: none;" title="Download Official Evaluation PDF Report">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download Official PDF Report</span>
                    </a>

                    <a href="{{ \App\Filament\Resources\CollegeEvaluationResource::getUrl('edit', ['record' => $eval]) }}" class="btn-exec-secondary" style="text-decoration: none;" title="Edit Batch Parameters & Marking Scheme">
                        <svg style="width: 15px; height: 15px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Batch &amp; Columns</span>
                    </a>
                </div>
            </div>

            {{-- Scheme Summary Strip --}}
            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #222a3d; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span style="font-size: 11.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">
                    Evaluation Scheme:
                </span>
                <span class="badge-pill" style="background: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                    Total: {{ $totalMaxFormatted }} Marks
                </span>
                @if ($isDirectMode)
                    <span class="badge-pill" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                        Direct Evaluation (No Sub-criteria)
                    </span>
                @else
                    <span class="badge-pill" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                        Attendance: {{ $attMaxFormatted }} M
                    </span>
                    <span class="badge-pill" style="background: rgba(59, 130, 246, 0.12); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                        Tasks: {{ $taskMaxFormatted }} M
                    </span>
                    @foreach ($eval->getCustomCriteriaList() as $crit)
                        <span class="badge-pill" style="background: rgba(168, 85, 247, 0.12); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3);">
                            {{ $crit['name'] }}: {{ rtrim(rtrim(number_format((float)$crit['max_marks'], 2), '0'), '.') }} M
                        </span>
                    @endforeach
                @endif
                @foreach ($eval->getCustomColumnsList() as $col)
                    <span class="badge-pill" style="background: rgba(14, 165, 233, 0.12); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3);">
                        Custom Column: {{ $col['label'] }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- ── 2. Metric KPI Cards ── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">
            <div class="exec-card" style="padding: 16px 18px; border-left: 3px solid #3b82f6;">
                <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Total Evaluated Students</div>
                <div style="font-size: 26px; font-weight: 800; color: #ffffff; margin-top: 6px;">{{ $totalCount }}</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Enrolled under this college</div>
            </div>

            <div class="exec-card" style="padding: 16px 18px; border-left: 3px solid #10b981;">
                <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Average Batch Score</div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; margin-top: 6px;">{{ $avgScore }} <span style="font-size: 14px; color: #64748b;">/ {{ $totalMaxFormatted }}</span></div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Combined batch performance</div>
            </div>

            <div class="exec-card" style="padding: 16px 18px; border-left: 3px solid #f59e0b;">
                <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Highest Score</div>
                <div style="font-size: 26px; font-weight: 800; color: #fbbf24; margin-top: 6px;">{{ $topScore }} <span style="font-size: 14px; color: #64748b;">/ {{ $totalMaxFormatted }}</span></div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Top marks achieved</div>
            </div>

            <div class="exec-card" style="padding: 16px 18px; border-left: 3px solid #8b5cf6;">
                <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Top Grades (O &amp; A+)</div>
                <div style="font-size: 26px; font-weight: 800; color: #c084fc; margin-top: 6px;">{{ $outstandingCount }}</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Distinction &amp; high honors</div>
            </div>
        </div>

        {{-- ── 3. Evaluated Interns Table ── --}}
        <div class="exec-card" style="overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0;">
                        Students Evaluation &amp; Marks Roster
                    </h2>
                    <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0;">
                        Showing all evaluated students. Click "Adjust" to enter marks and custom fields, or "+ Add Student Row" above to append new rows.
                    </p>
                </div>
                <span class="badge-pill" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                    {{ $totalCount }} Students Roster
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table class="exec-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr>
                            @foreach ($activeColumns as $col)
                                @php
                                    $cKey = $col['key'];
                                    $w = $columnWidths[$cKey] ?? null;
                                @endphp
                                <th style="text-align: {{ $col['align'] ?? 'center' }}; {{ $w ? 'width: ' . $w . '%;' : '' }}">
                                    {{ $col['label'] }}
                                    @if (!empty($col['sub_label']))
                                        <br><span style="font-size: 10px; color: #64748b; font-weight: 500;">{{ $col['sub_label'] }}</span>
                                    @endif
                                </th>
                            @endforeach
                            <th style="text-align: right; width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($evalInterns as $index => $item)
                            @php
                                $gradeStr = strtoupper($item->grade ?: 'PASS');
                                $gradeColor = match($gradeStr) {
                                    'O'     => 'background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4);',
                                    'A+', 'A' => 'background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4);',
                                    'B+', 'B' => 'background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4);',
                                    'C'     => 'background: rgba(249, 115, 22, 0.2); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.4);',
                                    default => 'background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3);',
                                };
                            @endphp
                            <tr>
                                @foreach ($activeColumns as $col)
                                    @php $cType = $col['type'] ?? 'custom_text'; @endphp
                                    <td style="text-align: {{ $col['align'] ?? 'center' }};">
                                        @if ($cType === 'sr_no')
                                            <span style="color: #64748b; font-weight: 600;">{{ $index + 1 }}</span>
                                        @elseif ($cType === 'student_id')
                                            <span style="font-family: monospace; font-weight: 700; color: #60a5fa;">
                                                {{ $item->student_id }}
                                            </span>
                                        @elseif ($cType === 'student_name')
                                            <div style="font-weight: 700; color: #ffffff;">
                                                {{ $item->student_name }}
                                            </div>
                                            @if ($item->remarks)
                                                <div style="font-size: 11px; color: #f59e0b; margin-top: 3px; font-style: italic;">
                                                    &ldquo;{{ $item->remarks }}&rdquo;
                                                </div>
                                            @endif
                                        @elseif ($cType === 'grade')
                                            <span class="badge-pill" style="{{ $gradeColor }}">
                                                {{ $gradeStr }}
                                            </span>
                                        @elseif (in_array($cType, ['direct_marks', 'total_marks', 'marks']))
                                            <span style="font-weight: 800; font-size: 14px; color: #38bdf8;">
                                                {{ rtrim(rtrim(number_format((float)$item->total_obtained_marks, 2), '0'), '.') }}
                                            </span>
                                        @elseif ($cType === 'criteria_attendance')
                                            <span style="color: #34d399; font-weight: 700;">
                                                {{ rtrim(rtrim(number_format((float)$item->attendance_marks, 2), '0'), '.') }}
                                            </span>
                                            <div style="font-size: 10.5px; color: #64748b;">
                                                {{ $item->attendance_pct !== null ? $item->attendance_pct . '%' : '—' }}
                                            </div>
                                        @elseif ($cType === 'criteria_task')
                                            <span style="color: #60a5fa; font-weight: 700;">
                                                {{ rtrim(rtrim(number_format((float)$item->task_marks, 2), '0'), '.') }}
                                            </span>
                                            <div style="font-size: 10.5px; color: #64748b;">
                                                {{ $item->task_completion_pct !== null ? $item->task_completion_pct . '%' : '—' }}
                                            </div>
                                        @elseif ($cType === 'custom_criterion')
                                            @php
                                                $critMark = $item->getCustomMarkByName($col['name'] ?? '');
                                            @endphp
                                            <span style="color: #c084fc; font-weight: 700;">
                                                {{ $critMark !== null ? rtrim(rtrim(number_format((float)$critMark, 2), '0'), '.') : '0' }}
                                            </span>
                                        @else
                                            <span style="color: #e2e8f0; font-weight: 500;">
                                                {{ $item->getCellValue($col['key'], $col) ?: '—' }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                <td style="text-align: right; white-space: nowrap;">
                                    <button type="button" wire:click="openEditInternModal({{ $item->id }})" class="btn-exec-secondary" style="padding: 4px 8px; font-size: 11.5px;" title="Edit marks and custom columns">
                                        ✏️ Adjust
                                    </button>
                                    <button type="button" wire:click="deleteInternRow({{ $item->id }})" wire:confirm="Are you sure you want to remove this student row?" class="btn-exec-secondary" style="padding: 4px 8px; font-size: 11.5px; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" title="Remove row">
                                        ✕
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($activeColumns) + 1 }}" style="text-align: center; padding: 28px; color: #94a3b8;">
                                    No students enrolled in this evaluation yet. Click <strong>+ Add Student Row</strong> above or <strong>Edit Batch &amp; Columns</strong> to enroll students.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 4. Marking Methodology & Legend Preview Card ── --}}
        <div class="exec-card" style="padding: 20px 22px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <h3 style="font-size: 14px; font-weight: 800; color: #ffffff; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                    📋 PDF Marking &amp; Calculation Legend Preview
                </h3>
                <span class="badge-pill" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8;">
                    Printed below PDF Table
                </span>
            </div>
            <div style="background: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 14px 16px; font-size: 12.5px; color: #cbd5e1; line-height: 1.6; white-space: pre-line;">
                {{ $eval->instruction_notes ?: $eval->generateDefaultInstructions() }}
            </div>
        </div>

    </div>

    {{-- ── 5. Quick Adjust / Edit Student Marks Modal ── --}}
    @if ($showEditModal)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 16px;">
            <div class="exec-card" style="width: 100%; max-width: 580px; max-height: 90vh; overflow-y: auto; padding: 24px; position: relative;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid #222a3d; padding-bottom: 12px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0;">
                            Edit Student Marks &amp; Data
                        </h3>
                        <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0;">Update student identifiers, custom column values, and scores.</p>
                    </div>
                    <button type="button" wire:click="closeEditModal" style="background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">
                        ✕
                    </button>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                Student ID / Code
                            </label>
                            <input type="text" wire:model="editStudentId" class="exec-input" placeholder="e.g. TS25/WD/01">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                Student Full Name
                            </label>
                            <input type="text" wire:model="editStudentName" class="exec-input" placeholder="e.g. Kajalben Bharatbhai">
                        </div>
                    </div>

                    {{-- Custom Text Columns (e.g. Course Name/ Module Name) --}}
                    @foreach ($eval->getCustomColumnsList() as $col)
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #38bdf8; margin-bottom: 4px;">
                                {{ $col['label'] }}
                            </label>
                            <input type="text" wire:model="editCustomFields.{{ $col['key'] }}" class="exec-input" placeholder="Enter {{ strtolower($col['label']) }}">
                        </div>
                    @endforeach

                    @if ($isDirectMode)
                        {{-- Direct Marks --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #34d399; margin-bottom: 4px;">
                                    Marks Obtained (Max: {{ $totalMaxFormatted }})
                                </label>
                                <input type="number" step="0.5" wire:model.live.debounce.250ms="editTotalMarks" class="exec-input" placeholder="e.g. 41">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                    Grade (Auto-Calculated)
                                </label>
                                <input type="text" wire:model="editGrade" class="exec-input" placeholder="e.g. O, A, B">
                                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                    Auto: <strong>O</strong> (90%+), <strong>A</strong> (70–89%), <strong>B+</strong> (60–69%), <strong>B</strong> (50–59%), <strong>C</strong> (40–49%)
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Criteria Mode --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                    Attendance Marks (Max: {{ $attMaxFormatted }})
                                </label>
                                <input type="number" step="0.5" wire:model="editAttendanceMarks" class="exec-input">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                    Task Marks (Max: {{ $taskMaxFormatted }})
                                </label>
                                <input type="number" step="0.5" wire:model="editTaskMarks" class="exec-input">
                            </div>
                        </div>

                        @foreach ($eval->getCustomCriteriaList() as $crit)
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c084fc; margin-bottom: 4px;">
                                    {{ $crit['name'] }} Marks (Max: {{ rtrim(rtrim(number_format((float)$crit['max_marks'], 2), '0'), '.') }})
                                </label>
                                <input type="number" step="0.5" wire:model="editCustomMarks.{{ $crit['name'] }}" class="exec-input">
                            </div>
                        @endforeach

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                Override Grade (Optional)
                            </label>
                            <input type="text" wire:model="editGrade" class="exec-input" placeholder="Leave blank for auto grade">
                        </div>
                    @endif

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                            Remarks / Module Comments
                        </label>
                        <textarea rows="2" wire:model="editRemarks" placeholder="Optional remark (e.g. Excellent frontend work)" class="exec-input"></textarea>
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; align-items: center; justify-content: flex-end; gap: 10px; border-top: 1px solid #222a3d; padding-top: 14px;">
                    <button type="button" wire:click="closeEditModal" class="btn-exec-secondary">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveInternMarks" class="btn-exec-primary">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── 6. Add Brand New Student Row Modal ── --}}
    @if ($showAddModal)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 16px;">
            <div class="exec-card" style="width: 100%; max-width: 580px; max-height: 90vh; overflow-y: auto; padding: 24px; position: relative;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid #222a3d; padding-bottom: 12px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0;">
                            Add New Student Row
                        </h3>
                        <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0;">Enter details to append a student directly to this evaluation roster.</p>
                    </div>
                    <button type="button" wire:click="closeAddModal" style="background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">
                        ✕
                    </button>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                Student ID / Code *
                            </label>
                            <input type="text" wire:model="newStudentId" class="exec-input" placeholder="e.g. TS25/WD/01">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                Student Full Name *
                            </label>
                            <input type="text" wire:model="newStudentName" class="exec-input" placeholder="e.g. Kajalben Bharatbhai">
                        </div>
                    </div>

                    {{-- Custom Text Columns (e.g. Course Name/ Module Name) --}}
                    @foreach ($eval->getCustomColumnsList() as $col)
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #38bdf8; margin-bottom: 4px;">
                                {{ $col['label'] }}
                            </label>
                            <input type="text" wire:model="newCustomFields.{{ $col['key'] }}" class="exec-input" placeholder="Enter {{ strtolower($col['label']) }}">
                        </div>
                    @endforeach

                    @if ($isDirectMode)
                        {{-- Direct Marks --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #34d399; margin-bottom: 4px;">
                                    Marks (Max: {{ $totalMaxFormatted }}) *
                                </label>
                                <input type="number" step="0.5" wire:model.live.debounce.250ms="newTotalMarks" class="exec-input" placeholder="e.g. 41">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                    Grade (Auto-Calculated)
                                </label>
                                <input type="text" wire:model="newGrade" class="exec-input" placeholder="e.g. O, A, B">
                                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                    Auto: <strong>O</strong> (90%+), <strong>A</strong> (70–89%), <strong>B+</strong> (60–69%), <strong>B</strong> (50–59%), <strong>C</strong> (40–49%)
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Criteria Mode --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                    Attendance Marks (Max: {{ $attMaxFormatted }})
                                </label>
                                <input type="number" step="0.5" wire:model="newAttendanceMarks" class="exec-input">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                    Task Marks (Max: {{ $taskMaxFormatted }})
                                </label>
                                <input type="number" step="0.5" wire:model="newTaskMarks" class="exec-input">
                            </div>
                        </div>

                        @foreach ($eval->getCustomCriteriaList() as $crit)
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c084fc; margin-bottom: 4px;">
                                    {{ $crit['name'] }} Marks (Max: {{ rtrim(rtrim(number_format((float)$crit['max_marks'], 2), '0'), '.') }})
                                </label>
                                <input type="number" step="0.5" wire:model="newCustomMarks.{{ $crit['name'] }}" class="exec-input">
                            </div>
                        @endforeach

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                Grade (Optional)
                            </label>
                            <input type="text" wire:model="newGrade" class="exec-input" placeholder="Leave blank for auto grade">
                        </div>
                    @endif

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                            Remarks / Module Comments
                        </label>
                        <textarea rows="2" wire:model="newRemarks" placeholder="Optional remark" class="exec-input"></textarea>
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; align-items: center; justify-content: flex-end; gap: 10px; border-top: 1px solid #222a3d; padding-top: 14px;">
                    <button type="button" wire:click="closeAddModal" class="btn-exec-secondary">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveNewStudentRow" class="btn-exec-primary" style="background: linear-gradient(135deg, #10b981, #059669);">
                        Add Student to Roster
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── 7. Live Interactive PDF Preview & Table Adjustments Modal ── --}}
    @if ($showPdfPreviewModal)
        @php
            $fontSizes = [
                'compact' => ['table' => '10.5px', 'th' => '10px', 'sub' => '8.5px'],
                'normal'  => ['table' => '12px',   'th' => '11px', 'sub' => '9.5px'],
                'large'   => ['table' => '13px',   'th' => '12px', 'sub' => '10.5px'],
                'xlarge'  => ['table' => '14px',   'th' => '13px', 'sub' => '11px'],
            ];
            $currentFont = $fontSizes[$previewFontSize] ?? $fontSizes['normal'];

            $paddings = [
                'compact'  => '5px 4px',
                'normal'   => '8px 6px',
                'spacious' => '10px 8px',
            ];
            $currentPadding = $paddings[$previewSpacing] ?? $paddings['normal'];

            $collegeTitle = $eval->college?->name ?? $eval->college_name;
            $univTitle = $eval->college?->university_name ?? $eval->university_name;

            $totalWidthSum = array_sum($columnWidths);
        @endphp

        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 12px;">
            <div class="exec-card" style="width: 98%; max-width: 1300px; height: 96vh; display: flex; flex-direction: column; overflow: hidden; padding: 0; position: relative; border: 1.5px solid #334155; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.75);">

                {{-- ── Modal Header & Controls ── --}}
                <div style="padding: 10px 18px; border-bottom: 1px solid #222a3d; background: #0c1222; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 22px;">📐</span>
                        <div>
                            <h3 style="font-size: 15px; font-weight: 800; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                                Interactive PDF Table Designer &amp; Preview
                                <span class="badge-pill" style="font-size: 10.5px; background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35);">
                                    A4 Portrait Ready
                                </span>
                            </h3>
                            <p style="font-size: 11px; color: #94a3b8; margin: 2px 0 0;">
                                Drag column divider handles (⠿) or use [-] [+] to shrink/expand any column. Click cells to edit text.
                            </p>
                        </div>
                    </div>

                    {{-- Right-side Action Buttons --}}
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        {{-- Mode Switcher Tabs --}}
                        <div style="display: flex; align-items: center; background: #1e293b; border-radius: 8px; padding: 2px; border: 1px solid #334155;">
                            <button type="button" wire:click="setPreviewViewMode('interactive')" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700; border-radius: 6px; border: none; cursor: pointer; {{ $previewViewMode === 'interactive' ? 'background: #3b82f6; color: #ffffff;' : 'background: transparent; color: #94a3b8;' }}">
                                📐 Interactive Sheet
                            </button>
                            <button type="button" wire:click="setPreviewViewMode('compiled_pdf')" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700; border-radius: 6px; border: none; cursor: pointer; {{ $previewViewMode === 'compiled_pdf' ? 'background: #3b82f6; color: #ffffff;' : 'background: transparent; color: #94a3b8;' }}">
                                📄 Compiled PDF
                            </button>
                        </div>

                        <button type="button" wire:click="autoFitColumnWidths" class="btn-exec-secondary" style="padding: 5px 10px; font-size: 11.5px;" title="Automatically balance widths based on content lengths in each column">
                            ✨ Auto-Fit Content
                        </button>

                        <button type="button" wire:click="resetColumnWidths" class="btn-exec-secondary" style="padding: 5px 10px; font-size: 11.5px;" title="Reset columns to default balanced widths">
                            🔄 Reset
                        </button>

                        <button type="button" wire:click="savePdfSettings" class="btn-exec-secondary" style="padding: 5px 12px; font-size: 11.5px; color: #34d399; border-color: rgba(16, 185, 129, 0.4);" title="Save column widths, font size & spacing as batch default in database">
                            💾 Save Layout
                        </button>

                        <a href="{{ route('college-evaluations.download-pdf', array_merge(['record' => $eval->id, 'font_size' => $previewFontSize, 'spacing' => $previewSpacing], ['col_widths' => $columnWidths])) }}" onclick="collectAndSaveCellEdits()" class="btn-exec-primary" style="padding: 5px 12px; font-size: 11.5px; text-decoration: none;" title="Download PDF with current adjustments">
                            ⬇️ Download PDF
                        </a>

                        <button type="button" onclick="printInteractiveA4Sheet()" class="btn-exec-secondary" style="padding: 5px 10px; font-size: 11.5px;" title="Print directly or save as PDF via browser print">
                            🖨️ Print
                        </button>

                        <button type="button" wire:click="closePdfPreviewModal" style="background: transparent; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; padding: 0 4px; line-height: 1;" title="Close Modal">
                            ✕
                        </button>
                    </div>
                </div>

                {{-- ── Quick Adjust Bar (Font Size, Spacing & Column Width Controls) ── --}}
                <div style="padding: 8px 18px; background: #0f172a; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    {{-- Typography & Spacing selectors --}}
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Font Size:</label>
                            <select wire:model.live="previewFontSize" class="exec-select">
                                <option value="compact">Compact (10.5px)</option>
                                <option value="normal">Standard (12px) - Recommended</option>
                                <option value="large">Large (13px)</option>
                                <option value="xlarge">Extra Large (14px)</option>
                            </select>
                        </div>

                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Row Spacing:</label>
                            <select wire:model.live="previewSpacing" class="exec-select">
                                <option value="compact">Compact (5px)</option>
                                <option value="normal">Standard (8px) - Recommended</option>
                                <option value="spacious">Spacious (10px)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Column Width % badges with Nudge Buttons --}}
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 10.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Widths:</span>
                        @foreach ($activeColumns as $col)
                            @php
                                $cKey = $col['key'];
                                $cWidth = $columnWidths[$cKey] ?? 10;
                            @endphp
                            <div style="display: inline-flex; align-items: center; background: #1e293b; border: 1px solid #334155; border-radius: 6px; padding: 2px 4px; gap: 3px;" title="{{ $col['label'] }}">
                                <span style="font-size: 10.5px; font-weight: 600; color: #e2e8f0; max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $col['label'] }}:
                                </span>
                                <button type="button" wire:click="adjustColumnWidth('{{ $cKey }}', -1)" style="border: none; background: #334155; color: #ffffff; border-radius: 3px; width: 16px; height: 16px; font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;" title="Shrink column by 1%">-</button>
                                <span style="font-family: monospace; font-size: 10.5px; font-weight: 700; color: #38bdf8; min-width: 32px; text-align: center;">
                                    {{ $cWidth }}%
                                </span>
                                <button type="button" wire:click="adjustColumnWidth('{{ $cKey }}', 1)" style="border: none; background: #334155; color: #ffffff; border-radius: 3px; width: 16px; height: 16px; font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;" title="Expand column by 1%">+</button>
                            </div>
                        @endforeach

                        <span class="badge-pill" style="font-size: 10px; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                            Total: {{ round($totalWidthSum) }}%
                        </span>
                    </div>
                </div>

                {{-- ── Main Body: Interactive Sheet OR Compiled PDF ── --}}
                <div style="flex: 1; overflow-y: auto; background: #334155; padding: 20px 10px; display: flex; justify-content: center;">

                    @if ($previewViewMode === 'compiled_pdf')
                        {{-- Compiled DomPDF View --}}
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                            <iframe
                                src="{{ route('college-evaluations.preview-pdf', array_merge(['record' => $eval->id, 'font_size' => $previewFontSize, 'spacing' => $previewSpacing, 't' => time()], ['col_widths' => $columnWidths])) }}"
                                style="width: 100%; height: 100%; border: none; background: #ffffff;"
                                title="Compiled PDF Live Preview"
                            ></iframe>
                        </div>
                    @else
                        {{-- Interactive A4 Sheet with Draggable Column Divider Handles & Contenteditable Cells --}}
                        <div id="interactive-a4-sheet" style="width: 100%; max-width: 860px; min-height: 1100px; background: #ffffff; color: #000000; padding: 40px 42px; box-shadow: 0 10px 35px rgba(0,0,0,0.5); font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; position: relative;">

                            {{-- Letterhead Watermark (Blurred & Faint like PDF) --}}
                            <div style="position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%); opacity: 0.09; pointer-events: none; z-index: 1;">
                                <img src="{{ file_exists(public_path('images/TsLogo_watermark_blurred.png')) ? asset('images/TsLogo_watermark_blurred.png') : asset('images/TsLogo.png') }}" style="width: 460px;" alt="Watermark">
                            </div>

                            <div style="position: relative; z-index: 2;">
                                {{-- ── Header (Exact Match to PDF: #f39200 Orange Border) ── --}}
                                <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #f39200; padding-bottom: 8px; margin-bottom: 12px;">
                                    <tr>
                                        <td style="font-size: 11px; font-weight: bold; color: #000; vertical-align: bottom; width: 33%;">
                                            Email: info@techstrota.com
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; width: 34%;">
                                            <img src="{{ asset('images/TsLogo.png') }}" style="height: 48px; width: auto;" alt="Techstrota Logo">
                                        </td>
                                        <td style="font-size: 11px; font-weight: bold; color: #000; text-align: right; vertical-align: bottom; width: 33%;">
                                            Tel: +91 81288 40055
                                        </td>
                                    </tr>
                                </table>

                                {{-- ── Report Title (Exact Match to PDF) ── --}}
                                <div style="text-align: center; margin-top: 4px; margin-bottom: 12px;">
                                    <div style="font-size: 20px; font-weight: 800; color: #000; text-transform: uppercase; letter-spacing: 0.03em; text-decoration: underline;">
                                        Internship Performance &amp; Evaluation Report
                                    </div>
                                </div>

                                {{-- ── Institutional Details (Exact Match to PDF Key-Value Layout) ── --}}
                                <div style="padding: 4px 6px; margin-bottom: 14px;">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;">
                                        <tr>
                                            <td style="font-weight: bold; width: 24%; padding: 3px 0; color: #000;">College / Institution:</td>
                                            <td style="font-weight: bold; font-size: 14px; width: 76%; padding: 3px 0; color: #000;">{{ $collegeTitle }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: bold; padding: 3px 0; color: #000;">Affiliated University:</td>
                                            <td style="padding: 3px 0; color: #000;">{{ $univTitle ?: 'Autonomous / Direct Campus' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: bold; padding: 3px 0; color: #000;">Course / Degree:</td>
                                            <td style="padding: 3px 0; color: #000;">{{ $eval->course_degree ?: 'Information Technology / Computer Engineering' }} &bull; Year: {{ $eval->academic_year ?: '2025–2026' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: bold; padding: 3px 0; color: #000;">Evaluation Period:</td>
                                            <td style="padding: 3px 0; color: #000;">
                                                {{ $eval->evaluation_period_start ? $eval->evaluation_period_start->format('d/m/Y') : 'Start' }} to {{ $eval->evaluation_period_end ? $eval->evaluation_period_end->format('d/m/Y') : 'Completion' }}
                                                &bull; Date of Evaluation: {{ $eval->evaluation_date ? $eval->evaluation_date->format('d/m/Y') : now()->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                {{-- ── Master Resizable Evaluation Table (1px solid #000 grid matching PDF) ── --}}
                                <div style="position: relative; margin-bottom: 14px;">
                                    <table id="interactive-eval-table" style="width: 100%; table-layout: fixed; border-collapse: collapse; font-size: {{ $currentFont['table'] }}; color: #000; background: transparent;">
                                        <thead>
                                            <tr>
                                                @foreach ($activeColumns as $cIndex => $col)
                                                    @php
                                                        $cKey = $col['key'];
                                                        $w = $columnWidths[$cKey] ?? round(100 / max(1, count($activeColumns)), 1);
                                                    @endphp
                                                    <th
                                                        data-col-key="{{ $cKey }}"
                                                        data-col-index="{{ $cIndex }}"
                                                        style="width: {{ $w }}%; border: 1px solid #000; padding: {{ $currentPadding }}; font-size: {{ $currentFont['th'] }}; font-weight: bold; text-transform: uppercase; text-align: {{ $col['align'] ?? 'center' }}; position: relative; word-wrap: break-word; vertical-align: middle; background: transparent; line-height: 1.15; color: #000;"
                                                    >
                                                        <div>{{ $col['label'] }}</div>
                                                        @if (!empty($col['sub_label']))
                                                            <div style="font-size: {{ $currentFont['sub'] }}; font-weight: normal; margin-top: 1px;">{{ $col['sub_label'] }}</div>
                                                        @endif
                                                        <div class="col-width-tag" style="font-size: 8.5px; color: #2563eb; font-weight: bold; margin-top: 2px;">
                                                            {{ $w }}%
                                                        </div>

                                                        {{-- Draggable Border Handle --}}
                                                        @if ($cIndex < count($activeColumns) - 1)
                                                            <div
                                                                class="col-resizer-handle"
                                                                title="Drag left/right to shrink or expand column"
                                                                style="position: absolute; right: -4px; top: 0; bottom: 0; width: 8px; cursor: col-resize; z-index: 20; background: transparent;"
                                                            ></div>
                                                        @endif
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($evalInterns as $index => $item)
                                                <tr>
                                                    @foreach ($activeColumns as $col)
                                                        @php
                                                            $cType = $col['type'] ?? 'custom_text';
                                                            $align = $col['align'] ?? 'center';
                                                            $cKey = $col['key'];
                                                        @endphp
                                                        <td
                                                            style="border: 1px solid #000; padding: {{ $currentPadding }}; font-size: {{ $currentFont['table'] }}; font-weight: normal; text-align: {{ $align }}; vertical-align: middle; word-wrap: break-word; line-height: 1.35; color: #000; background: transparent;"
                                                            contenteditable="true"
                                                            data-intern-id="{{ $item->id }}"
                                                            data-col-key="{{ $cKey }}"
                                                            onblur="saveCellOnBlur(this)"
                                                            title="Click to edit cell text inline (auto-saves on blur)"
                                                        >
                                                            @if ($cType === 'sr_no')
                                                                {{ $index + 1 }}
                                                            @elseif ($cType === 'student_id')
                                                                {{ $item->student_id }}
                                                            @elseif ($cType === 'student_name')
                                                                {{ $item->student_name }}
                                                            @elseif ($cType === 'grade')
                                                                {{ strtoupper($item->grade ?: 'PASS') }}
                                                            @elseif (in_array($cType, ['direct_marks', 'total_marks', 'marks']))
                                                                {{ rtrim(rtrim(number_format((float)$item->total_obtained_marks, 2), '0'), '.') }}
                                                            @elseif ($cType === 'criteria_attendance')
                                                                {{ rtrim(rtrim(number_format((float)$item->attendance_marks, 2), '0'), '.') }}
                                                            @elseif ($cType === 'criteria_task')
                                                                {{ rtrim(rtrim(number_format((float)$item->task_marks, 2), '0'), '.') }}
                                                            @elseif ($cType === 'custom_criterion')
                                                                @php
                                                                    $critMark = $item->getCustomMarkByName($col['name'] ?? '');
                                                                @endphp
                                                                {{ $critMark !== null ? rtrim(rtrim(number_format((float)$critMark, 2), '0'), '.') : '0' }}
                                                            @else
                                                                {{ $item->getCellValue($col['key'], $col) }}
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($activeColumns) }}" style="text-align: center; padding: 14px; border: 1px solid #000; color: #000;">
                                                        No students enrolled in this batch report.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Inline Cell Edits Saver Notice --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding: 6px 12px; background: rgba(59, 130, 246, 0.08); border: 1px dashed rgba(59, 130, 246, 0.3); border-radius: 6px;">
                                    <span style="font-size: 11px; color: #1e40af;">
                                        ✏️ <em>Tip: Click inside any cell to edit text directly. Edits auto-save immediately on click outside (blur).</em>
                                    </span>
                                    <button type="button" onclick="collectAndSaveCellEdits()" style="background: #2563eb; color: #ffffff; border: none; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 4px; cursor: pointer;">
                                        💾 Save In-Table Edits
                                    </button>
                                </div>

                                {{-- ── Evaluation Methodology & Rubrics Notes (Exact Match to PDF) ── --}}
                                <div style="margin-top: 10px; margin-bottom: 14px; padding: 2px 0;">
                                    <div style="font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.02em; margin-bottom: 4px; color: #000;">
                                        Evaluation Methodology &amp; Marking Instructions
                                    </div>
                                    <div style="font-size: 13px; line-height: 1.45; white-space: pre-line; color: #000;">
                                        {{ $eval->instruction_notes ?: $eval->generateDefaultInstructions() }}
                                    </div>
                                </div>

                                {{-- ── Signatures Section (Exact Match to PDF) ── --}}
                                <div style="margin-top: 40px; margin-bottom: 25px; width: 100%;">
                                    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
                                        <tr>
                                            <td style="width: 50%; text-align: left; vertical-align: bottom; border: none !important; padding: 0;">
                                                <table style="border-collapse: collapse; border: none; margin: 0; padding: 0;">
                                                    <tr>
                                                        <td style="font-size: 13px; font-weight: bold; color: #000; padding: 0 6px 0 0; vertical-align: bottom; border: none !important; white-space: nowrap;">Signature:</td>
                                                        <td style="font-size: 13px; color: #000; vertical-align: bottom; border: none !important; white-space: nowrap;">________________________</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td style="width: 50%; text-align: right; vertical-align: bottom; border: none !important; padding: 0;">
                                                <table style="margin-left: auto; border-collapse: collapse; border: none; margin-right: 0; padding: 0;">
                                                    <tr>
                                                        <td style="font-size: 13px; font-weight: bold; color: #000; padding: 0 6px 0 0; vertical-align: bottom; border: none !important; white-space: nowrap;">Date:</td>
                                                        <td style="font-size: 13px; color: #000; vertical-align: bottom; border: none !important; white-space: nowrap;">________________________</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                {{-- ── Footer (Exact Match to PDF: #f39200 Orange Border) ── --}}
                                <div style="border-top: 2px solid #f39200; padding-top: 6px; text-align: center; color: #000; margin-top: 30px;">
                                    <div style="font-size: 11.5px; font-weight: 800; color: #000; letter-spacing: 0.3px;">
                                        <span style="color: #000;">Techstrota</span> 
                                        <span style="color: #f39200; margin: 0 4px;">|</span> 
                                        <span style="color: #1e40af;">www.techstrota.com</span>
                                    </div>
                                    <div style="font-size: 11px; font-weight: 700; font-style: italic; color: #f39200; margin-top: 2px; letter-spacing: 0.4px;">
                                        &ldquo;You think, we Build.&rdquo;
                                    </div>
                                    <div style="font-size: 10.5px; font-weight: 600; color: #334155; letter-spacing: 0.8px; margin-top: 2px; text-transform: uppercase;">
                                        Websites &bull; Mobile Apps &bull; Software &bull; Digital Marketing
                                    </div>
                                    <div style="font-size: 9.5px; color: maroon; font-style: italic; margin-top: 3px; text-align: right;">
                                        This is a system-generated document.
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ── Client-side Interactive Column Resizer & Cell Saver JavaScript ── --}}
        <script>
            (function () {
                window.initInteractiveSheetResize = function () {
                    const table = document.getElementById('interactive-eval-table');
                    if (!table) return;

                    const headers = table.querySelectorAll('th[data-col-key]');
                    headers.forEach((th) => {
                        const resizer = th.querySelector('.col-resizer-handle');
                        if (!resizer) return;

                        let startX, startWidth, tableWidth, colKey;
                        colKey = th.getAttribute('data-col-key');

                        resizer.onmousedown = function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            startX = e.clientX;
                            startWidth = th.offsetWidth;
                            tableWidth = table.offsetWidth;

                            document.body.style.cursor = 'col-resize';
                            document.body.style.userSelect = 'none';

                            function onMouseMove(ev) {
                                const diff = ev.clientX - startX;
                                const newWidthPx = Math.max(30, startWidth + diff);
                                const pct = Math.max(4.0, Math.min(80.0, parseFloat(((newWidthPx / tableWidth) * 100).toFixed(1))));

                                th.style.width = pct + '%';
                                const tag = th.querySelector('.col-width-tag');
                                if (tag) tag.textContent = pct + '%';
                            }

                            function onMouseUp() {
                                document.removeEventListener('mousemove', onMouseMove);
                                document.removeEventListener('mouseup', onMouseUp);
                                document.body.style.cursor = '';
                                document.body.style.userSelect = '';

                                // Compute all column percentages & rebalance to 100%
                                const allTh = table.querySelectorAll('th[data-col-key]');
                                const currentWidths = {};
                                let sum = 0;
                                allTh.forEach(t => {
                                    const k = t.getAttribute('data-col-key');
                                    const w = parseFloat(t.style.width) || parseFloat(((t.offsetWidth / table.offsetWidth) * 100).toFixed(1));
                                    currentWidths[k] = w;
                                    sum += w;
                                });

                                if (sum > 0) {
                                    const factor = 100 / sum;
                                    for (const k in currentWidths) {
                                        currentWidths[k] = parseFloat((currentWidths[k] * factor).toFixed(1));
                                        const targetTh = table.querySelector('th[data-col-key="' + k + '"]');
                                        if (targetTh) {
                                            targetTh.style.width = currentWidths[k] + '%';
                                            const tag = targetTh.querySelector('.col-width-tag');
                                            if (tag) tag.textContent = currentWidths[k] + '%';
                                        }
                                    }
                                }

                                @this.call('updateColumnWidths', currentWidths);
                            }

                            document.addEventListener('mousemove', onMouseMove);
                            document.addEventListener('mouseup', onMouseUp);
                        };
                    });
                };

                window.saveCellOnBlur = function (td) {
                    const internId = td.getAttribute('data-intern-id');
                    const colKey = td.getAttribute('data-col-key');
                    const val = td.innerText.trim();
                    if (internId && colKey) {
                        @this.call('saveSingleCellEdit', parseInt(internId), colKey, val);
                    }
                };

                window.collectAndSaveCellEdits = function () {
                    const table = document.getElementById('interactive-eval-table');
                    if (!table) return;

                    const cells = table.querySelectorAll('td[contenteditable="true"]');
                    const edits = {};

                    cells.forEach(td => {
                        const internId = td.getAttribute('data-intern-id');
                        const colKey = td.getAttribute('data-col-key');
                        if (internId && colKey) {
                            if (!edits[internId]) edits[internId] = {};
                            edits[internId][colKey] = td.innerText.trim();
                        }
                    });

                    @this.call('saveInlineCellEdits', edits);
                };

                window.printInteractiveA4Sheet = function () {
                    const sheet = document.getElementById('interactive-a4-sheet');
                    if (!sheet) return;

                    const printWindow = window.open('', '_blank');
                    printWindow.document.write('<!DOCTYPE html><html><head><title>Print Report</title>');
                    printWindow.document.write('<style>@page { size: a4 portrait; margin: 12mm; } body { margin: 0; font-family: sans-serif; } table { border-collapse: collapse; } th, td { border: 1px solid #000; } .col-resizer-handle, .col-width-tag { display: none !important; } [contenteditable] { outline: none; }</style>');
                    printWindow.document.write('</head><body>');
                    printWindow.document.write(sheet.innerHTML);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.focus();
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 350);
                };

                // Initialize on load and re-initialize after Livewire DOM morphs
                document.addEventListener('DOMContentLoaded', window.initInteractiveSheetResize);
                document.addEventListener('livewire:initialized', () => {
                    Livewire.hook('morph.updated', () => {
                        window.initInteractiveSheetResize();
                    });
                });
                setTimeout(window.initInteractiveSheetResize, 400);
            })();
        </script>
    @endif

</x-filament-panels::page>
