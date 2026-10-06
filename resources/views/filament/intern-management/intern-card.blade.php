@php
    $record = isset($getRecord) && is_callable($getRecord) ? $getRecord() : ($record ?? null);
    $name = $record->offerletter?->name ?? $record->application?->name ?? $record->name ?? 'Unnamed Intern';
    $internCode = $record->intern_code ?? ('INT-' . str_pad($record->id, 3, '0', STR_PAD_LEFT));
    $role = $record->offerletter?->internship_role ?? $record->application?->domain ?? $record->domain ?? 'Intern';
    
    $rawBatch = $record->batch?->batch_name ?? $record->batch?->name ?? $record->offerletter?->batch_name;
    $hasBatch = filled($rawBatch);
    $batchDisplay = $hasBatch ? $rawBatch : 'Batch';

    $rawProject = $record->project_name ?? $record->team?->team_name ?? $record->team?->name;
    $hasProject = filled($rawProject);
    $projectDisplay = $hasProject ? $rawProject : 'Project';

    // Mentor name
    $mentorName = $record->team?->mentor_name ?: 'David Kim';

    // Attendance status resolution for TODAY
    $status = $record->today_status;

    $statusMap = [
        'present'   => ['bg' => 'rgba(16, 185, 129, 0.12)', 'text' => '#34d399', 'dot' => '#34d399', 'border' => 'rgba(16, 185, 129, 0.4)', 'label' => 'Present'],
        'wfh'       => ['bg' => 'rgba(59, 130, 246, 0.12)', 'text' => '#60a5fa', 'dot' => '#60a5fa', 'border' => 'rgba(59, 130, 246, 0.4)', 'label' => 'WFH'],
        'leave'     => ['bg' => 'rgba(168, 85, 247, 0.12)', 'text' => '#c084fc', 'dot' => '#c084fc', 'border' => 'rgba(168, 85, 247, 0.4)', 'label' => 'Leave'],
        'absent'    => ['bg' => 'rgba(239, 68, 68, 0.12)', 'text' => '#f87171', 'dot' => '#f87171', 'border' => 'rgba(239, 68, 68, 0.4)', 'label' => 'Absent'],
        'unmarked'  => ['bg' => 'rgba(148, 163, 184, 0.12)', 'text' => '#94a3b8', 'dot' => '#94a3b8', 'border' => 'rgba(148, 163, 184, 0.3)', 'label' => 'Unmarked'],
        'week-off'  => ['bg' => 'rgba(139, 92, 246, 0.12)', 'text' => '#a78bfa', 'dot' => '#a78bfa', 'border' => 'rgba(139, 92, 246, 0.3)', 'label' => 'Week-Off'],
        'completed' => ['bg' => 'rgba(59, 130, 246, 0.12)', 'text' => '#60a5fa', 'dot' => '#60a5fa', 'border' => 'rgba(59, 130, 246, 0.4)', 'label' => 'Completed'],
    ];
    $statusInfo = $statusMap[strtolower($status)] ?? $statusMap['unmarked'];

    // Attendance stats
    $totDays = \App\Models\Attendance::where('intern_id', $record->id)->count();
    $presDays = \App\Models\Attendance::where('intern_id', $record->id)->whereIn('status', ['present', 'wfh'])->count();
    if ($totDays > 0) {
        $attRate = round(($presDays / $totDays) * 100);
        $attRateString = "{$attRate}% ({$presDays}/{$totDays}d)";
    } else {
        $attRateString = "0% (0d)";
    }

    // Task / Progress Resolution
    $taskStats = $record->task_stats;
    $totalAssigned = $taskStats['total'];
    $completedCount = $taskStats['completed'];
    $pendingTasksCount = $taskStats['pending'];
    $overdueCount = $taskStats['overdue'];

    // Initials for avatar fallback
    $words = explode(' ', trim($name));
    $initials = strtoupper(substr($words[0] ?? 'I', 0, 1) . substr($words[1] ?? '', 0, 1));
    if (empty($initials)) $initials = 'SD';

    $detailUrl = \App\Filament\Resources\InternManagement\InternResource::getUrl('view', ['record' => $record]);
@endphp

<div
    style="background-color: #0b1326; border-radius: 16px; border: 1.5px solid #1c2638; box-shadow: 0 4px 16px rgba(0,0,0,0.25); padding: 18px; display: flex; flex-direction: column; gap: 14px; transition: all 0.2s ease; width: 100%; box-sizing: border-box;">

    {{-- Top Row: Code (Left) | Status Pill (Right) --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
        <span style="font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">
            {{ $internCode }}
        </span>

        <div style="background-color: {{ $statusInfo['bg'] }}; color: {{ $statusInfo['text'] }}; border: 1px solid {{ $statusInfo['border'] }}; font-size: 13px; font-weight: 600; padding: 3px 12px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 6px;">
            <span style="width: 7px; height: 7px; border-radius: 50%; background-color: {{ $statusInfo['dot'] }};"></span>
            {{ $statusInfo['label'] }}
        </div>
    </div>

    {{-- Middle Profile Row: Circular Avatar + Name & Role --}}
    <a
        href="{{ $detailUrl }}"
        style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px; cursor: pointer;">
        <div style="width: 46px; height: 46px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background-color: #172235; color: #38bdf8; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; border: 1px solid #233149;">
            @if ($record->intern_image)
                <img src="{{ asset('storage/' . $record->intern_image) }}" alt="{{ $name }}" style="width: 100%; height: 100%; object-fit: cover;" />
            @else
                {{ $initials }}
            @endif
        </div>

        <div style="min-width: 0; flex-grow: 1;">
            <div style="font-size: 16px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;">
                {{ $name }}
            </div>
            <div style="font-size: 13px; color: #60a5fa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 3px; font-weight: 500;">
                {{ $role }}
            </div>
        </div>
    </a>

    {{-- Badges Row: Batch Pill + Project Pill --}}
    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; overflow: hidden;">
        <div style="background-color: #0c1c38; color: #60a5fa; border: 1px solid #1d4ed8; font-size: 12px; font-weight: 600; padding: 5px 11px; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px; flex-shrink: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            <span style="font-size: 13px;">🗓️</span>
            <span style="overflow: hidden; text-overflow: ellipsis;">Batch: {{ $batchDisplay }}</span>
        </div>

        <div style="background-color: #210f36; color: #c084fc; border: 1px solid #7c3aed; font-size: 12px; font-weight: 600; padding: 5px 11px; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px; flex-shrink: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            <span style="font-size: 13px;">🚀</span>
            <span style="overflow: hidden; text-overflow: ellipsis;">Project: {{ $projectDisplay }}</span>
        </div>
    </div>

    {{-- Optional Certificates / Letters Badges --}}
    @if ($record->cert_ref_id || $record->letter_ref_id)
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            @if ($record->cert_ref_id)
                <span style="background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; font-family: ui-monospace, monospace;">
                    🏅 {{ $record->cert_ref_id }}
                </span>
            @endif
            @if ($record->letter_ref_id)
                <span style="background-color: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; font-family: ui-monospace, monospace;">
                    📄 {{ $record->letter_ref_id }}
                </span>
            @endif
        </div>
    @endif

    {{-- Mentor & Attendance Stats Row --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
        <div style="font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            <span style="color: #ffffff; font-weight: 700;">Mentor:</span>
            <span style="color: #94a3b8; font-weight: 500; margin-left: 3px;">{{ $mentorName }}</span>
        </div>
        <div style="display: inline-flex; align-items: center; gap: 5px; color: #34d399; font-size: 13px; font-weight: 700; white-space: nowrap; flex-shrink: 0;">
            <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #34d399;"></span>
            <span>{{ $attRateString }}</span>
        </div>
    </div>

    {{-- Bottom Banner: Tasks status --}}
    <div>
        @if ($overdueCount > 0)
            <div style="width: 100%; background-color: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center;">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $pendingTasksCount }} {{ \Illuminate\Support\Str::plural('task', $pendingTasksCount) }} assigned ({{ $overdueCount }} overdue)</span>
            </div>
        @elseif ($pendingTasksCount > 0)
            <div style="width: 100%; background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center;">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>{{ $pendingTasksCount }} {{ \Illuminate\Support\Str::plural('task', $pendingTasksCount) }} assigned</span>
            </div>
        @elseif ($totalAssigned > 0)
            <div style="width: 100%; background-color: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center;">
                <span style="font-size: 14px; font-weight: 800;">✓</span>
                <span>All tasks completed</span>
            </div>
        @else
            <div style="width: 100%; background-color: rgba(148, 163, 184, 0.08); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center;">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
                <span>No tasks assigned</span>
            </div>
        @endif
    </div>

</div>
