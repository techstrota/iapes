@php
    $record = $getRecord();
    $title = $record->title ?? 'Untitled Task';
    $description = $record->description ? strip_tags($record->description) : 'Deliverable checkpoints, acceptance criteria, and task milestones.';
    $priority = strtolower($record->priority ?? 'medium');
    $status = $record->status ?? 'active';
    $dueDate = $record->due_date ? \Carbon\Carbon::parse($record->due_date) : null;
    $isOverdue = $dueDate && $dueDate->isPast() && $status !== 'completed';

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
        $dueBg = 'rgba(147, 0, 10, 0.35)';
        $dueColor = '#ffb4ab';
        $dueBorder = 'rgba(255, 180, 171, 0.35)';
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

    // Target scope resolution
    $assignedInterns = $record->assigned_interns;
    $internCount = $assignedInterns->count();

    if ($internCount === 0) {
        $scopeBadge = '⚪ Unassigned';
    } elseif ($internCount === 1) {
        $scopeBadge = '👤 Solo: ' . ($assignedInterns->first()->name ?? 'Intern');
    } else {
        $scopeBadge = "👥 {$internCount} Interns Assigned";
    }

    // Telemetry counts
    $totalAssigned = max(1, $internCount);
    $submittedCount = $record->submitted_count;
    $evaluatedCount = $record->evaluated_count;
    $pendingEvalCount = max(0, $submittedCount - $evaluatedCount);
    $notSubmittedCount = max(0, $totalAssigned - $submittedCount);
    $pctSubmitted = min(100, round(($submittedCount / $totalAssigned) * 100));

    // Two-tone bar percentages
    $pctGraded = min(100, round(($evaluatedCount / $totalAssigned) * 100));
    $pctPending = min(100 - $pctGraded, round(($pendingEvalCount / $totalAssigned) * 100));

    // Interns for avatar stack (show submitted interns if any, otherwise assigned interns)
    $submissions = $record->submissions()->with('intern')->get();
    $displayInterns = $submissions->isNotEmpty()
        ? $submissions->map(fn($s) => $s->intern)->filter()->values()
        : $record->assigned_interns;
    $memberColors = ['#1e40af', '#005236', '#3433c3', '#00a572', '#7c3aed'];

    $editUrl = \App\Filament\Resources\TaskManagement\TaskResource::getUrl('edit', ['record' => $record]);
    $evalUrl = \App\Filament\Resources\TaskManagement\TaskResource::getUrl('view', ['record' => $record]);
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
    .fi-ta-content-grid .fi-ta-actions-cell {
        display: none !important;
    }
</style>

<div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: all 0.2s ease; width: 100%; box-sizing: border-box; min-height: 380px; position: relative;"
     onmouseover="this.style.borderColor='rgba(59, 130, 246, 0.5)'; this.style.boxShadow='0 10px 25px -5px rgba(0, 0, 0, 0.4)';"
     onmouseout="this.style.borderColor='#222a3d'; this.style.boxShadow='0 4px 20px rgba(0, 0, 0, 0.25)';">

    <div>
        {{-- Top Tag Bar: Priority & Due Date Chip --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                <span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; background-color: {{ $prioBg }}; color: {{ $prioColor }}; border: 1px solid {{ $prioBorder }}; font-size: 11px; font-weight: 700; letter-spacing: 0.02em;">
                    {{ $prioLabel }}
                </span>
                <span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; background-color: {{ $dueBg }}; color: {{ $dueColor }}; border: 1px solid {{ $dueBorder }}; font-size: 11px; font-weight: 600;">
                    {{ $dueText }}
                </span>
            </div>

            <span style="padding: 3px 8px; border-radius: 6px; background-color: #171f33; border: 1px solid #222a3d; color: #b8c4ff; font-size: 11px; font-weight: 600; white-space: nowrap;">
                {{ $scopeBadge }}
            </span>
        </div>

        {{-- Task Title --}}
        <h3 style="font-size: 17.5px; font-weight: 700; color: #ffffff; margin: 0 0 6px 0; line-height: 1.35; letter-spacing: -0.01em;">
            {{ $title }}
        </h3>

        {{-- Deliverable Summary --}}
        <p style="font-size: 12.5px; color: #94a3b8; line-height: 1.45; margin: 0 0 12px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 36px;">
            {{ $description }}
        </p>

        {{-- Attached Spec Chip (if present) --}}
        @if ($record->attachment)
            <a href="{{ asset('storage/' . $record->attachment) }}" target="_blank"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; background-color: #171f33; border: 1px solid #222a3d; color: #b8c4ff; font-size: 11.5px; font-weight: 500; text-decoration: none; margin-bottom: 12px;"
               onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff';"
               onmouseout="this.style.borderColor='#222a3d'; this.style.color='#b8c4ff';">
                <svg style="width: 13px; height: 13px; color: #60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                <span style="max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ basename($record->attachment) }}
                </span>
            </a>
        @endif

        {{-- Recessed Telemetry Progress Box (#090e1c) --}}
        <div style="background-color: #090e1c; border: 1px solid #1a2335; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; display: flex; flex-direction: column; gap: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px;">
                <span style="color: #ffffff; font-weight: 600;">Submissions Progress</span>
                <span style="font-weight: 800; color: #ffffff;">
                    {{ $submittedCount }} <span style="font-weight: 400; color: #64748b;">/ {{ $totalAssigned }} Submitted</span>
                    <span style="color: #60a5fa; font-weight: 700; margin-left: 3px;">({{ $pctSubmitted }}%)</span>
                </span>
            </div>

            {{-- Two-Tone Progress Bar --}}
            <div style="width: 100%; height: 7px; background-color: #1e293b; border-radius: 9999px; overflow: hidden; display: flex;">
                <div style="height: 100%; background-color: #4edea3; width: {{ $pctGraded }}%;" title="{{ $evaluatedCount }} Graded"></div>
                <div style="height: 100%; background-color: #fbbf24; width: {{ $pctPending }}%;" title="{{ $pendingEvalCount }} Pending Review"></div>
            </div>

            {{-- Telemetry Breakdown Chips --}}
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; padding-top: 2px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: #4edea3; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #4edea3;"></span>
                        {{ $evaluatedCount }} Graded
                    </span>
                    <span style="color: #fbbf24; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #fbbf24;"></span>
                        {{ $pendingEvalCount }} Awaiting Review
                    </span>
                </div>
                <span style="color: #64748b;">
                    {{ $notSubmittedCount }} Not Submitted
                </span>
            </div>
        </div>

        {{-- Overlapping Intern Avatars & Pending Badge --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; padding: 0 2px;">
            <div style="display: flex; align-items: center; margin-left: 6px;">
                @php
                    $firstAvatars = $displayInterns->take(4);
                    $remAvatars = $displayInterns->count() - $firstAvatars->count();
                @endphp

                @forelse ($firstAvatars as $idx => $internObj)
                    @php
                        $iName = is_object($internObj) ? ($internObj->name ?? 'Intern') : 'Intern';
                        $words = explode(' ', trim($iName));
                        $iInit = strtoupper(substr($words[0] ?? 'I', 0, 1) . substr($words[1] ?? '', 0, 1));
                        $bgCol = $memberColors[$idx % count($memberColors)];
                    @endphp
                    <div style="width: 28px; height: 28px; border-radius: 50%; background-color: {{ $bgCol }}; color: #ffffff; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid #131b2e; margin-left: -6px; box-shadow: 0 1px 3px rgba(0,0,0,0.3);" title="{{ $iName }}">
                        {{ $iInit ?: 'IN' }}
                    </div>
                @empty
                    <span style="font-size: 11px; color: #64748b; font-style: italic;">No recipients</span>
                @endforelse

                @if ($remAvatars > 0)
                    <div style="width: 28px; height: 28px; border-radius: 50%; background-color: #222a3d; color: #b8c4ff; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid #131b2e; margin-left: -6px;">
                        +{{ $remAvatars }}
                    </div>
                @endif
            </div>

            @if ($pendingEvalCount > 0)
                <span style="font-size: 11.5px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 2px 8px; border-radius: 6px;">
                    +{{ $pendingEvalCount }} Pending Review
                </span>
            @else
                <span style="font-size: 11px; font-weight: 600; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); padding: 2px 8px; border-radius: 6px;">
                    ✓ All Evaluated
                </span>
            @endif
        </div>
    </div>

    {{-- Card Action Footer --}}
    <div style="display: flex; align-items: center; gap: 8px; padding-top: 14px; border-top: 1px solid #222a3d;">
        {{-- Manage Button (Left) --}}
        <a
            href="{{ $editUrl }}"
            style="flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; background-color: #171f33; color: #cbd5e1; border: 1px solid #222a3d; text-decoration: none; white-space: nowrap !important; transition: all 0.15s ease;"
            onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff';"
            onmouseout="this.style.borderColor='#222a3d'; this.style.color='#cbd5e1';">
            <span style="white-space: nowrap !important;">Manage</span>
        </a>

        {{-- Evaluate Button (Right) --}}
        <a
            href="{{ $evalUrl }}"
            style="flex: 1 1 auto; min-width: 0; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; background-color: #1e40af; color: #ffffff; text-decoration: none; white-space: nowrap !important; box-shadow: 0 1px 4px rgba(30, 64, 175, 0.35); transition: background-color 0.15s ease;"
            onmouseover="this.style.backgroundColor='#1d4ed8';"
            onmouseout="this.style.backgroundColor='#1e40af';">
            <span style="white-space: nowrap !important; overflow: hidden; text-overflow: ellipsis;">Evaluate ({{ $pendingEvalCount }} Pending)</span>
            <svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

</div>
