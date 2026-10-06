@php
    $record = $getRecord();
    $isArchived = (bool) ($record->is_archived ?? false);
    $status = $record->status ?? 'on_track';
    $statusMap = [
        'on_track'  => ['bg' => 'rgba(16, 185, 129, 0.15)', 'text' => '#4edea3', 'border' => 'rgba(78, 222, 163, 0.3)', 'label' => 'On Track'],
        'attention' => ['bg' => 'rgba(239, 68, 68, 0.15)', 'text' => '#ffb4ab', 'border' => 'rgba(239, 68, 68, 0.3)', 'label' => 'Attention Needed'],
        'completed' => ['bg' => 'rgba(59, 130, 246, 0.15)', 'text' => '#93c5fd', 'border' => 'rgba(59, 130, 246, 0.3)', 'label' => 'Completed'],
    ];
    $statusInfo = $statusMap[$status] ?? $statusMap['on_track'];
    $track = $record->track ?? 'Full Stack';
    $interns = $record->interns ?? collect();
    $internCount = $interns->count();

    // Squad size label matching Stitch design: Solo (1 Intern), Pair (2 Interns), Trio (3 Interns), Squad (4 Interns)
    if ($internCount === 1) {
        $squadLabel = 'Solo (1 Intern)';
    } elseif ($internCount === 2) {
        $squadLabel = 'Pair (2 Interns)';
    } elseif ($internCount === 3) {
        $squadLabel = 'Trio (3 Interns)';
    } elseif ($internCount >= 4) {
        $squadLabel = "Squad ({$internCount} Interns)";
    } else {
        $squadLabel = 'Unassigned (0 Interns)';
    }

    $archiveName = $record->cohort_archive_name;
    $mentorName = $record->mentor_name ?: 'John Doe';
    $mentorTitle = $record->mentor_title ?: 'Project Supervisor';
    $description = $record->project_description ? strip_tags($record->project_description) : 'Multi-rail project deliverables, sprint checkpoints, and feature implementation.';

    // Supervisor avatar initials & color palette
    $mWords = explode(' ', trim($mentorName));
    $mInitials = strtoupper(substr($mWords[0] ?? 'M', 0, 1) . substr($mWords[1] ?? '', 0, 1));
    $supervisorColors = ['#1e40af', '#005236', '#3433c3', '#00a572', '#b45309'];
    $supervisorBg = $supervisorColors[abs(crc32($mentorName)) % count($supervisorColors)];

    $editUrl = \App\Filament\Resources\InternManagement\InternTeamResource::getUrl('edit', ['record' => $record]);
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

<div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: all 0.2s ease; width: 100%; box-sizing: border-box; min-height: 380px;"
     onmouseover="this.style.borderColor='rgba(59, 130, 246, 0.5)'; this.style.boxShadow='0 10px 25px -5px rgba(0, 0, 0, 0.4)';"
     onmouseout="this.style.borderColor='#222a3d'; this.style.boxShadow='0 4px 20px rgba(0, 0, 0, 0.25)';">

    <div>
        {{-- Top Lifecycle & Status Row --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
            @if (!$isArchived)
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background-color: #00311f; color: #4edea3; font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; border: 1px solid rgba(78, 222, 163, 0.3);">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #4edea3; box-shadow: 0 0 6px rgba(78, 222, 163, 0.6);"></span>
                    Current Active Project
                </span>
            @else
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background-color: rgba(120, 53, 15, 0.4); color: #fde68a; font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; border: 1px solid rgba(245, 158, 11, 0.4);">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #fbbf24;"></span>
                    Archived Project
                </span>
            @endif

            <span style="background-color: {{ $statusInfo['bg'] }}; color: {{ $statusInfo['text'] }}; border: 1px solid {{ $statusInfo['border'] }}; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 5px; height: 5px; border-radius: 50%; background-color: {{ $statusInfo['text'] }};"></span>
                {{ $statusInfo['label'] }}
            </span>
        </div>

        {{-- Meta Tags Row (Track & Squad Size) --}}
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
            <span style="padding: 3px 10px; border-radius: 6px; background-color: #171f33; border: 1px solid #222a3d; color: #c0c1ff; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                {{ $track }}
            </span>
            <span style="padding: 3px 10px; border-radius: 6px; background-color: #171f33; border: 1px solid #222a3d; color: #94a3b8; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                <svg style="width: 13px; height: 13px; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ $squadLabel }}
            </span>
        </div>

        {{-- Project Name / Title --}}
        <h3 style="font-size: 17px; font-weight: 700; color: #ffffff; margin: 0 0 8px 0; line-height: 1.35; letter-spacing: -0.01em;">
            {{ $record->team_name }}
        </h3>

        {{-- Project Deliverables / Description --}}
        <p style="font-size: 12.5px; color: #94a3b8; line-height: 1.45; margin: 0 0 14px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 36px;">
            {{ $description }}
        </p>

        {{-- Lead Mentor / Supervisor Info Box --}}
        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; background-color: #171f33; border: 1px solid #222a3d; margin-bottom: 14px;">
            <div style="width: 34px; height: 34px; border-radius: 50%; background-color: {{ $supervisorBg }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                {{ $mInitials }}
            </div>
            <div style="display: flex; flex-direction: column; min-width: 0; flex: 1;">
                <span style="font-size: 13px; font-weight: 600; color: #ffffff; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $mentorName }}
                </span>
                <span style="font-size: 11px; color: #94a3b8; line-height: 1.2; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $mentorTitle }}
                </span>
            </div>
        </div>

        {{-- Team Members Row --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 14px; padding: 0 2px;">
            <div style="display: flex; align-items: center; margin-left: 6px;">
                @php
                    $memberColors = ['#1e40af', '#005236', '#3433c3', '#00a572', '#7c3aed'];
                    $firstInterns = $interns->take(4);
                    $remaining = $internCount - $firstInterns->count();
                @endphp

                @forelse ($firstInterns as $idx => $internItem)
                    @php
                        $iWords = explode(' ', trim($internItem->name));
                        $iInit = strtoupper(substr($iWords[0] ?? 'I', 0, 1) . substr($iWords[1] ?? '', 0, 1));
                        $bgCol = $memberColors[$idx % count($memberColors)];
                    @endphp
                    <div style="width: 28px; height: 28px; border-radius: 50%; background-color: {{ $bgCol }}; color: #ffffff; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid #131b2e; margin-left: -6px; box-shadow: 0 1px 3px rgba(0,0,0,0.3);" title="{{ $internItem->name }}">
                        {{ $iInit ?: 'IN' }}
                    </div>
                @empty
                    <span style="font-size: 11px; color: #64748b; font-style: italic;">No members assigned yet</span>
                @endforelse

                @if ($remaining > 0)
                    <div style="width: 28px; height: 28px; border-radius: 50%; background-color: #222a3d; color: #b8c4ff; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid #131b2e; margin-left: -6px;">
                        +{{ $remaining }}
                    </div>
                @endif
            </div>

            <span style="font-size: 12px; font-weight: 500; color: #94a3b8;">
                {{ $internCount }} {{ \Illuminate\Support\Str::plural('Member', $internCount) }} Assigned
            </span>
        </div>
    </div>

    {{-- Card Action Footer --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 14px; border-top: 1px solid #222a3d;">
        {{-- Manage Team / Actions button (Amber style matching Stitch) --}}
        <a
            href="{{ $editUrl }}"
            style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 15px; border-radius: 8px; font-size: 12px; font-weight: 600; background-color: #d97706; color: #ffffff; text-decoration: none; box-shadow: 0 1px 4px rgba(217, 119, 6, 0.25); transition: background-color 0.15s ease;"
            onmouseover="this.style.backgroundColor='#b45309';"
            onmouseout="this.style.backgroundColor='#d97706';">
            <span>Manage Team</span>
            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
        </a>

        {{-- View Team link matching Stitch --}}
        <a
            href="{{ $editUrl }}"
            style="display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: #b8c4ff; text-decoration: none; transition: color 0.15s ease;"
            onmouseover="this.style.color='#ffffff';"
            onmouseout="this.style.color='#b8c4ff';">
            <span>View Team</span>
            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

</div>
