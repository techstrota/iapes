@php
    $record = isset($getRecord) && is_callable($getRecord) ? $getRecord() : ($record ?? null);
    $name = $record->name ?? 'Unnamed Candidate';
    $appCode = $record->application_code ?? ('APP-' . str_pad($record->id, 4, '0', STR_PAD_LEFT));
    $domain = $record->domain ?? 'Internship Candidate';
    $status = strtolower($record->status ?? 'applied');
    $isArchived = (bool) ($record->is_archived ?? false);
    $archiveCycle = $record->cohort_archive_name ?? '';

    $statusMap = [
        'applied'             => ['bg' => 'rgba(148, 163, 184, 0.12)', 'text' => '#94a3b8', 'dot' => '#94a3b8', 'border' => 'rgba(148, 163, 184, 0.3)', 'label' => 'Applied'],
        'interview_scheduled' => ['bg' => 'rgba(59, 130, 246, 0.12)', 'text' => '#60a5fa', 'dot' => '#60a5fa', 'border' => 'rgba(59, 130, 246, 0.4)', 'label' => 'Scheduled'],
        'interviewed'         => ['bg' => 'rgba(245, 158, 11, 0.12)', 'text' => '#fbbf24', 'dot' => '#fbbf24', 'border' => 'rgba(245, 158, 11, 0.35)', 'label' => 'Interviewed'],
        'shortlisted'         => ['bg' => 'rgba(16, 185, 129, 0.12)', 'text' => '#34d399', 'dot' => '#34d399', 'border' => 'rgba(16, 185, 129, 0.4)', 'label' => 'Shortlisted'],
        'rejected'            => ['bg' => 'rgba(239, 68, 68, 0.12)', 'text' => '#f87171', 'dot' => '#f87171', 'border' => 'rgba(239, 68, 68, 0.4)', 'label' => 'Rejected'],
    ];
    $statusInfo = $statusMap[$status] ?? $statusMap['applied'];

    // Initials for avatar
    $words = preg_split('/\s+/', trim($name));
    $initials = strtoupper(substr($words[0] ?? 'C', 0, 1) . substr($words[1] ?? '', 0, 1));
    if (empty($initials)) $initials = 'CD';

    // Detail view URL
    $detailUrl = \App\Filament\Resources\CandidateManagement\CandidateResource::getUrl('view', ['record' => $record]);

    // Batch assignment
    $assignment = $record->interviewAssignments?->sortByDesc('id')->first();
    $batch = $assignment?->batch;
    $batchName = $batch?->interview_batch_name;
    $batchDate = $batch?->interview_date ? \Carbon\Carbon::parse($batch->interview_date)->format('d M') : '';
    $batchTime = $batch?->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('h:i A') : '';
    $batchMeta = trim("{$batchDate} {$batchTime}");

    // Education & College
    $college = $record->college ?: '';
    $degree = $record->degree ?: '';
    $educationDisplay = $degree ? "{$degree} • {$college}" : $college;

    // Contact
    $email = $record->email ?: '';
    $phone = $record->phone ?: '';

    // CGPA & Applied Date
    $cgpa = $record->cgpa;
    $appliedDate = $record->created_at ? $record->created_at->format('d M, Y') : '';

    // Resume
    $resumeUrl = $record->resume_path ? asset('storage/' . $record->resume_path) : null;

    // Scores
    $attendance = $assignment?->attendance;
    $ps = $assignment?->problem_solving;
    $comm = $assignment?->communication;
    $totalScore = $assignment?->overall_score;
    $hasPs = !is_null($ps) && $ps !== '';
    $hasComm = !is_null($comm) && $comm !== '';
@endphp

<div style="display: flex; flex-direction: column; gap: 12px; width: 100%; box-sizing: border-box;">

    {{-- Top Row: Application Code (Left) | Badges & Status Pill (Right) --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
        <span style="font-size: 14.5px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">
            {{ $appCode }}
        </span>

        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            @if ($isArchived)
                <span style="background-color: rgba(148, 163, 184, 0.12); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.3); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;" title="Archived Cycle: {{ $archiveCycle }}">
                    📦 {{ $archiveCycle ? \Illuminate\Support\Str::limit($archiveCycle, 16) : 'Archived' }}
                </span>
            @endif

            <div style="background-color: {{ $statusInfo['bg'] }}; color: {{ $statusInfo['text'] }}; border: 1px solid {{ $statusInfo['border'] }}; font-size: 12.5px; font-weight: 600; padding: 2.5px 11px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 6px;">
                <span style="width: 7px; height: 7px; border-radius: 50%; background-color: {{ $statusInfo['dot'] }};"></span>
                {{ $statusInfo['label'] }}
            </div>
        </div>
    </div>

    {{-- Middle Profile Row: Circular Avatar + Name & Domain --}}
    <a
        href="{{ $detailUrl }}"
        style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px; cursor: pointer;">
        <div style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background-color: #172235; color: #38bdf8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14.5px; border: 1.5px solid #233149;">
            {{ $initials }}
        </div>

        <div style="min-width: 0; flex-grow: 1;">
            <div style="font-size: 15.5px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;">
                {{ $name }}
            </div>
            <div style="font-size: 13px; color: #60a5fa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; font-weight: 500;">
                {{ $domain }}
            </div>
        </div>
    </a>

    {{-- Badges & Education Row --}}
    <div style="display: flex; flex-direction: column; gap: 6px; width: 100%;">
        @if ($batchName && in_array($status, ['interview_scheduled', 'interviewed', 'shortlisted']))
            <div style="background-color: #0c1c38; color: #60a5fa; border: 1px solid #1d4ed8; font-size: 11.5px; font-weight: 600; padding: 3px 9px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px; width: fit-content; max-width: 100%;" title="Batch: {{ $batchName }}{{ $batchMeta ? " ({$batchMeta})" : '' }}">
                <span>🗓️</span>
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $batchName }} @if($batchMeta)<span style="opacity: 0.8; font-size: 11px;">({{ $batchMeta }})</span>@endif</span>
            </div>
        @endif

        @if ($degree || $college)
            <div style="background-color: #210f36; color: #c084fc; border: 1px solid #7c3aed; border-radius: 8px; padding: 6px 10px; display: flex; align-items: flex-start; gap: 8px; width: 100%; box-sizing: border-box;" title="{{ $educationDisplay }}">
                <span style="font-size: 13.5px; line-height: 1.25; flex-shrink: 0; margin-top: 1px;">🎓</span>
                <div style="min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 2px;">
                    @if ($degree)
                        <div style="font-size: 12px; font-weight: 700; color: #ffffff; line-height: 1.35; word-break: break-word;">
                            {{ $degree }}
                        </div>
                    @endif
                    @if ($college)
                        <div style="font-size: 11.5px; font-weight: {{ $degree ? '500' : '700' }}; color: {{ $degree ? '#c084fc' : '#ffffff' }}; line-height: 1.35; word-break: break-word;">
                            {{ $college }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Contact Row: Email & Phone --}}
    <div style="display: flex; flex-direction: column; gap: 4px;">
        @if ($email)
            <div style="display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $email }}">
                <svg style="width: 14px; height: 14px; flex-shrink: 0; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span style="overflow: hidden; text-overflow: ellipsis;">{{ $email }}</span>
            </div>
        @endif

        @if ($phone)
            <div style="display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <svg style="width: 14px; height: 14px; flex-shrink: 0; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>{{ $phone }}</span>
            </div>
        @endif
    </div>

    {{-- Meta & Resume Row: CGPA, Applied Date & Resume Link --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            @if ($cgpa)
                <span style="background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                    ⭐ CGPA: {{ $cgpa }}
                </span>
            @endif

            @if ($appliedDate)
                <span style="font-size: 12px; color: #64748b;">
                    {{ $appliedDate }}
                </span>
            @endif
        </div>

        @if ($resumeUrl)
            <a
                href="{{ $resumeUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                style="display: inline-flex; align-items: center; gap: 4px; background-color: rgba(59, 130, 246, 0.12); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 11.5px; font-weight: 600; padding: 2.5px 8px; border-radius: 6px; text-decoration: none; transition: all 0.15s ease;"
                title="View Resume (PDF)">
                <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Resume
            </a>
        @endif
    </div>

    {{-- Bottom Section: Interview Evaluation Card or Pending Note --}}
    @if (in_array($status, ['interview_scheduled', 'interviewed', 'shortlisted', 'rejected']))
        @if ($assignment)
            @if ($attendance === 'absent')
                <div style="width: 100%; background-color: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Interview Attendance
                    </span>
                    <span style="font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; background-color: rgba(239, 68, 68, 0.2); padding: 2px 7px; border-radius: 4px;">
                        Absent
                    </span>
                </div>
            @elseif ($hasPs || $hasComm)
                <div style="background-color: rgba(0, 0, 0, 0.2); border: 1px solid #1c2638; border-radius: 8px; padding: 8px 10px; display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.06); padding-bottom: 5px;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; display: flex; align-items: center; gap: 5px;">
                            <span style="color: #fbbf24;">🎯</span> Interview Marks
                        </span>
                        @if ($totalScore !== null)
                            <span style="background-color: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 11px; font-weight: 800; padding: 1px 7px; border-radius: 9999px;">
                                {{ (float)$totalScore }} / 50
                            </span>
                        @endif
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                        <div style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 6px; padding: 5px 7px;">
                            <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: #8e909f; letter-spacing: 0.04em;">Technical</div>
                            <div style="font-size: 12.5px; font-weight: 700; color: #ffffff; margin-top: 2px;">
                                {{ $hasPs ? (float)$ps : '-' }} <span style="font-size: 10px; color: #64748b; font-weight: normal;">/ 25</span>
                            </div>
                        </div>
                        <div style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 6px; padding: 5px 7px;">
                            <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: #8e909f; letter-spacing: 0.04em;">Communication</div>
                            <div style="font-size: 12.5px; font-weight: 700; color: #ffffff; margin-top: 2px;">
                                {{ $hasComm ? (float)$comm : '-' }} <span style="font-size: 10px; color: #64748b; font-weight: normal;">/ 25</span>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($status === 'interview_scheduled')
                <div style="width: 100%; background-color: rgba(59, 130, 246, 0.08); color: #60a5fa; border: 1px dashed rgba(59, 130, 246, 0.3); font-size: 11.5px; font-weight: 600; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 5px; text-align: center;">
                    <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Interview scheduled • Pending evaluation</span>
                </div>
            @endif
        @elseif ($status === 'interview_scheduled')
            <div style="width: 100%; background-color: rgba(59, 130, 246, 0.08); color: #60a5fa; border: 1px dashed rgba(59, 130, 246, 0.3); font-size: 11.5px; font-weight: 600; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 5px; text-align: center;">
                <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Interview scheduled • Pending evaluation</span>
            </div>
        @endif
    @endif

</div>
