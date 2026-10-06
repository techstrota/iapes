@php
    $record = $getRecord();
    $task = $record->task;
    $submission = $record->task_submission;
    $title = $task?->title ?? 'Untitled Task';
    $description = $task?->description ? strip_tags($task->description) : 'Milestone guidelines and project deliverables.';
    $priority = strtolower($task?->priority ?? 'medium');
    $dueDate = $task?->due_date ? \Carbon\Carbon::parse($task->due_date) : null;
    $subStatus = $submission?->status;
    $isOverdue = $dueDate && $dueDate->isPast() && (!$submission || in_array($subStatus, ['rejected']));

    // Priority styles
    if ($priority === 'high') {
        $prioLabel = '🔥 High Priority';
        $prioBg = 'rgba(147, 0, 10, 0.35)';
        $prioColor = '#ffb4ab';
        $prioBorder = 'rgba(255, 180, 171, 0.3)';
    } elseif ($priority === 'low') {
        $prioLabel = '🟢 Low Priority';
        $prioBg = 'rgba(0, 49, 31, 0.4)';
        $prioColor = '#4edea3';
        $prioBorder = 'rgba(78, 222, 163, 0.3)';
    } else {
        $prioLabel = '⚡ Medium Priority';
        $prioBg = 'rgba(52, 51, 195, 0.25)';
        $prioColor = '#c0c1ff';
        $prioBorder = 'rgba(192, 193, 255, 0.3)';
    }

    // Due Date Display
    if (!$dueDate) {
        $dueText = 'No Due Date';
        $dueBg = '#171f33';
        $dueColor = '#94a3b8';
        $dueBorder = '#222a3d';
    } elseif ($isOverdue) {
        $diff = $dueDate->diffForHumans(null, true);
        $dueText = "⚠️ Overdue {$diff}";
        $dueBg = 'rgba(220, 38, 38, 0.2)';
        $dueColor = '#fca5a5';
        $dueBorder = 'rgba(239, 68, 68, 0.4)';
    } elseif ($dueDate->isToday()) {
        $dueText = '🕒 Due Today';
        $dueBg = 'rgba(245, 158, 11, 0.2)';
        $dueColor = '#fde68a';
        $dueBorder = 'rgba(245, 158, 11, 0.4)';
    } else {
        $dueText = '📅 Due ' . $dueDate->format('M d');
        $dueBg = '#171f33';
        $dueColor = '#b8c4ff';
        $dueBorder = '#222a3d';
    }

    // Assigned scope badge: Only show for Team or Batch assignments (Solo task badge removed per user request)
    $assignedType = $record->assigned_type;
    if ($assignedType === 'team' && $record->team) {
        $scopeBadge = '👥 Team: ' . $record->team->team_name;
    } elseif ($assignedType === 'batch' && $record->batch) {
        $scopeBadge = '🎓 Batch: ' . $record->batch->batch_name;
    } else {
        $scopeBadge = null;
    }

    // Telemetry & Evaluation details
    $subMarks = $submission?->marks;
    $subGrade = $submission?->grade;
    $subFeedback = $submission?->admin_feedback;
    $subFile = $submission?->submission_file;
    $subText = $submission?->submission_text;
    $submittedAt = $submission?->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at) : null;
    $evaluatedAt = $submission?->evaluated_at ? \Carbon\Carbon::parse($submission->evaluated_at) : null;
    $recordKey = $record->getKey();

    // Submission state parameters
    if ($subStatus === 'approved') {
        $stateBadge = '✓ Approved & Graded';
        $stateBg = 'rgba(16, 185, 129, 0.15)';
        $stateColor = '#4edea3';
        $stateBorder = 'rgba(78, 222, 163, 0.35)';
        $progressPct = 100;
        $progressBarColor = '#4edea3';
    } elseif ($subStatus === 'rejected') {
        $stateBadge = '⚠️ Revision Needed';
        $stateBg = 'rgba(239, 68, 68, 0.15)';
        $stateColor = '#f87171';
        $stateBorder = 'rgba(239, 68, 68, 0.35)';
        $progressPct = 35;
        $progressBarColor = '#f87171';
    } elseif ($subStatus === 'submitted' || $subStatus === 'reviewed') {
        $stateBadge = '🕒 In Review';
        $stateBg = 'rgba(245, 158, 11, 0.15)';
        $stateColor = '#fbbf24';
        $stateBorder = 'rgba(245, 158, 11, 0.35)';
        $progressPct = 65;
        $progressBarColor = '#fbbf24';
    } else {
        $stateBadge = '○ Pending Submission';
        $stateBg = 'rgba(148, 163, 184, 0.12)';
        $stateColor = '#94a3b8';
        $stateBorder = 'rgba(148, 163, 184, 0.25)';
        $progressPct = 0;
        $progressBarColor = '#1e293b';
    }
@endphp

{{-- Scoped CSS Resets for Filament Grid Wrapper --}}
<style>
    .fi-ta-content-grid > div,
    .fi-ta-content-grid .fi-ta-record {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        ring: 0 !important;
        padding: 0 !important;
        outline: none !important;
    }
    .fi-ta-content-grid .fi-ta-record > div {
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .fi-ta-content-grid .fi-ta-record-checkbox {
        display: none !important;
    }
    /* Hide duplicate row actions below the card */
    .fi-ta-content-grid .fi-ta-actions,
    .fi-ta-content-grid .fi-ta-actions-cell {
        display: none !important;
    }
</style>

<div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); width: 100%; box-sizing: border-box; min-height: 280px; height: 100%; position: relative;"
     onmouseover="this.style.borderColor='rgba(59, 130, 246, 0.55)'; this.style.boxShadow='0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 0 15px rgba(59, 130, 246, 0.1)';"
     onmouseout="this.style.borderColor='#222a3d'; this.style.boxShadow='0 4px 20px rgba(0, 0, 0, 0.25)';">

    <div>
        {{-- Top Tag Bar: Priority & Due Date Chip (Single clean line) --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; background-color: {{ $prioBg }}; color: {{ $prioColor }}; border: 1px solid {{ $prioBorder }}; font-size: 11px; font-weight: 700; letter-spacing: 0.02em;">
                    {{ $prioLabel }}
                </span>
                <span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; background-color: {{ $dueBg }}; color: {{ $dueColor }}; border: 1px solid {{ $dueBorder }}; font-size: 11px; font-weight: 600;">
                    {{ $dueText }}
                </span>
            </div>

            @if ($scopeBadge)
                <span style="padding: 3px 9px; border-radius: 6px; background-color: #171f33; border: 1px solid #222a3d; color: #b8c4ff; font-size: 11px; font-weight: 600; white-space: nowrap; max-width: 180px; overflow: hidden; text-overflow: ellipsis;">
                    {{ $scopeBadge }}
                </span>
            @endif
        </div>

        {{-- Task Title --}}
        <h3 style="font-size: 18px; font-weight: 700; color: #ffffff; margin: 0 0 6px 0; line-height: 1.35; letter-spacing: -0.01em;">
            {{ $title }}
        </h3>

        {{-- Deliverable Summary (clamped 2 lines) --}}
        <p style="font-size: 12.5px; color: #94a3b8; line-height: 1.5; margin: 0 0 14px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
            {{ $description }}
        </p>

        {{-- Attached Spec / Guidelines Chip (if present) --}}
        @if ($task?->attachment)
            <a href="{{ asset('storage/' . $task->attachment) }}" target="_blank"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; background-color: #171f33; border: 1px solid #222a3d; color: #b8c4ff; font-size: 11.5px; font-weight: 500; text-decoration: none; margin-bottom: 14px; transition: all 0.15s ease;"
               onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff';"
               onmouseout="this.style.borderColor='#222a3d'; this.style.color='#b8c4ff';">
                <svg style="width: 13px; height: 13px; color: #60a5fa; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                <span style="max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    Download Brief: {{ basename($task->attachment) }}
                </span>
            </a>
        @endif

        {{-- Recessed Telemetry Box (#090e1c) --}}
        <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; display: flex; flex-direction: column; gap: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; flex-wrap: wrap; gap: 6px;">
                <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px; background-color: {{ $stateBg }}; color: {{ $stateColor }}; border: 1px solid {{ $stateBorder }}; font-size: 11px; font-weight: 700;">
                    {{ $stateBadge }}
                </span>

                @if ($subStatus === 'approved')
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if ($subMarks !== null)
                            <span style="font-weight: 800; color: #4edea3; font-size: 13px;">
                                {{ $subMarks }}<span style="font-weight: 400; color: #64748b; font-size: 11px;">/100</span>
                            </span>
                        @endif
                        @if ($subGrade)
                            <span style="font-size: 11px; font-weight: 800; padding: 1px 7px; border-radius: 4px; background: rgba(78, 222, 163, 0.2); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.4);">
                                Grade {{ $subGrade }}
                            </span>
                        @endif
                    </div>
                @elseif ($subStatus === 'submitted' || $subStatus === 'reviewed')
                    <span style="font-size: 11px; color: #94a3b8;">
                        Submitted {{ $submittedAt?->diffForHumans() ?? 'recently' }}
                    </span>
                @elseif ($subStatus === 'rejected')
                    <span style="font-size: 11px; color: #f87171; font-weight: 600;">
                        Action Required
                    </span>
                @else
                    <span style="font-size: 11px; color: #64748b;">
                        Deliverable Awaited
                    </span>
                @endif
            </div>

            {{-- Progress Indicator Bar --}}
            <div style="width: 100%; height: 6px; background-color: #1e293b; border-radius: 9999px; overflow: hidden;">
                <div style="height: 100%; width: {{ $progressPct }}%; background-color: {{ $progressBarColor }}; border-radius: 9999px; transition: width 0.3s ease;"></div>
            </div>

            {{-- Detailed Submission Information / Feedback snippet --}}
            @if ($subFeedback)
                <div style="padding: 7px 10px; border-radius: 8px; background: rgba(255, 255, 255, 0.03); border-left: 3px solid {{ $subStatus === 'rejected' ? '#f87171' : '#4edea3' }}; font-size: 11.5px; color: #cbd5e1; line-height: 1.4;">
                    <span style="font-weight: 700; color: {{ $subStatus === 'rejected' ? '#f87171' : '#4edea3' }}; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.03em; display: block; margin-bottom: 2px;">
                        Mentor Feedback:
                    </span>
                    <span style="font-style: italic; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        "{{ $subFeedback }}"
                    </span>
                </div>
            @endif

            {{-- Submitted Links/Files preview --}}
            @if ($subText || $subFile)
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding-top: 2px; font-size: 11px;">
                    @if ($subText && filter_var($subText, FILTER_VALIDATE_URL))
                        <a href="{{ $subText }}" target="_blank"
                           style="color: #60a5fa; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            <svg style="width: 11px; height: 11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            <span>Live Work / Repo</span>
                        </a>
                    @endif

                    @if ($subFile)
                        <a href="{{ asset('storage/' . $subFile) }}" target="_blank"
                           style="color: #4edea3; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            <svg style="width: 11px; height: 11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Uploaded File</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Card Action Footer (Single action button pair inside the card) --}}
    <div style="display: flex; align-items: center; gap: 8px; padding-top: 14px; border-top: 1px solid #222a3d; margin-top: auto;">
        {{-- View Details Modal Trigger --}}
        <button
            type="button"
            wire:click="mountTableAction('view', '{{ $recordKey }}')"
            style="flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; background-color: #171f33; color: #cbd5e1; border: 1px solid #222a3d; cursor: pointer; transition: all 0.15s ease;"
            onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff';"
            onmouseout="this.style.borderColor='#222a3d'; this.style.color='#cbd5e1';">
            <svg style="width: 14px; height: 14px; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>View Details</span>
        </button>

        {{-- Submit or Status Action Button --}}
        @if ($subStatus === 'approved')
            <button
                type="button"
                wire:click="mountTableAction('view', '{{ $recordKey }}')"
                style="flex: 1 1 auto; min-width: 0; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.4); cursor: pointer; transition: all 0.15s ease;"
                onmouseover="this.style.backgroundColor='rgba(16, 185, 129, 0.25)';"
                onmouseout="this.style.backgroundColor='rgba(16, 185, 129, 0.15)';">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>Task Completed</span>
            </button>
        @elseif ($subStatus === 'rejected')
            <button
                type="button"
                wire:click="mountTableAction('submit_task', '{{ $recordKey }}')"
                style="flex: 1 1 auto; min-width: 0; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; background-color: #dc2626; color: #ffffff; border: 1px solid #ef4444; border-radius: 8px; cursor: pointer; box-shadow: 0 1px 4px rgba(220, 38, 38, 0.35); transition: background-color 0.15s ease;"
                onmouseover="this.style.backgroundColor='#b91c1c';"
                onmouseout="this.style.backgroundColor='#dc2626';">
                <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Resubmit Work</span>
            </button>
        @elseif ($subStatus === 'submitted' || $subStatus === 'reviewed')
            <button
                type="button"
                wire:click="mountTableAction('submit_task', '{{ $recordKey }}')"
                style="flex: 1 1 auto; min-width: 0; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; background-color: #171f33; color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); cursor: pointer; transition: all 0.15s ease;"
                onmouseover="this.style.backgroundColor='#1e293b'; this.style.borderColor='#fbbf24';"
                onmouseout="this.style.backgroundColor='#171f33'; this.style.borderColor='rgba(245, 158, 11, 0.4)';">
                <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Update Submission</span>
            </button>
        @else
            <button
                type="button"
                wire:click="mountTableAction('submit_task', '{{ $recordKey }}')"
                style="flex: 1 1 auto; min-width: 0; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; background-color: #1e40af; color: #ffffff; border: 1px solid #3b82f6; cursor: pointer; box-shadow: 0 1px 4px rgba(30, 64, 175, 0.35); transition: background-color 0.15s ease;"
                onmouseover="this.style.backgroundColor='#1d4ed8';"
                onmouseout="this.style.backgroundColor='#1e40af';">
                <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                <span>Submit Work</span>
            </button>
        @endif
    </div>

</div>
