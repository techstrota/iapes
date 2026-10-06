<x-filament-panels::page>
@php
    $name = $record->name ?: ($record->offerletter?->name ?? $record->application?->name ?? 'Unnamed Intern');
    $internCode = $record->intern_code ?? ('INT-' . str_pad($record->id, 3, '0', STR_PAD_LEFT));
    $appCode = $record->application?->application_code;
    $role = $record->internship_role ?: ($record->offerletter?->internship_role ?? $record->application?->domain ?? $record->domain ?? 'Intern');
    $institution = $record->university ?: ($record->college ?: ($record->offerletter?->university ?? $record->offerletter?->college ?? $record->application?->college ?? 'Institution Not Recorded'));

    // Status mapping for today's live attendance
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
    $attRate = $totDays > 0 ? round(($presDays / $totDays) * 100) : 0;
    $attRateString = "{$attRate}% ({$presDays}/{$totDays}d)";

    // Task stats
    $taskStats = $record->task_stats;
    $totalAssigned = $taskStats['total'];
    $completedCount = $taskStats['completed'];
    $pendingTasksCount = $taskStats['pending'];
    $overdueCount = $taskStats['overdue'];

    // Tenure dates
    $startDate = $record->joining_date ?: $record->offerletter?->joining_date;
    $endDate = $record->completion_date ?: $record->offerletter?->completion_date;
    $tenureMonths = 0;
    if ($startDate && $endDate) {
        $tenureMonths = (int) round(\Carbon\Carbon::parse($startDate)->floatDiffInMonths(\Carbon\Carbon::parse($endDate)));
    } elseif ($record->application?->duration) {
        $tenureMonths = (int) $record->application->duration;
    }
    $isTenurePast = $endDate ? \Carbon\Carbon::parse($endDate)->isPast() : false;

    // Project & Grade
    $projectDisplay = $record->team?->team_name ?: ($record->project_name ?: 'Project not assigned');
    $mentorName = $record->team?->mentor_name ?: 'David Kim';
    $grade = $record->completionCertificate?->grade ?: ($record->completionLetter?->grade ?: ($record->grade ?: '—'));

    // Batch
    $batchDisplay = $record->batch?->batch_name ?? $record->batch?->name ?? ($record->offerletter?->batch_name ?? 'Not Assigned');

    // Initials fallback
    $words = explode(' ', trim($name));
    $initials = strtoupper(substr($words[0] ?? 'I', 0, 1) . substr($words[1] ?? '', 0, 1));
    if (empty($initials)) $initials = 'IN';

    // Credentials
    $userId = $record->username ?: $record->email;
    $plainPassword = $record->plain_password ?: str($record->username)->before('@user.com')->toString();

    // Documents
    $cert = $record->completionCertificate;
    $letter = $record->completionLetter;
    $hasCert = filled($record->cert_ref_id) || $cert || filled($record->cert_token);
    $hasLetter = filled($record->letter_ref_id) || $letter;
    $certRef = $record->cert_ref_id ?: ($cert?->cert_ref_id ?: 'Generated');
    $letterRef = $record->letter_ref_id ?: ($letter?->letter_ref_id ?: 'Generated');

    // Teammates in squad
    $teammates = $record->teammates()->with(['offerletter', 'application'])->get();

    // Skills array
    $skillsArray = [];
    $rawSkills = $record->skills ?: ($record->application?->skills ?? '');
    if (filled($rawSkills)) {
        if (is_array($rawSkills)) {
            $skillsArray = $rawSkills;
        } else {
            $skillsArray = array_filter(array_map('trim', explode(',', $rawSkills)));
        }
    }

    $indexUrl = \App\Filament\Resources\InternManagement\InternResource::getUrl('index');
    $editUrl = \App\Filament\Resources\InternManagement\InternResource::getUrl('edit', ['record' => $record]);
@endphp

<style>
    /* ── Modal & Form Controls Styling for Stitch Theme ── */
    .fi-modal-window {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5) !important;
    }
    .fi-modal-header {
        border-bottom: 1px solid #222a3d !important;
    }
    .fi-modal-footer {
        border-top: 1px solid #222a3d !important;
    }
    .fi-input-wrp {
        background-color: #090e1c !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 10px !important;
        color: #dae2fd !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4) !important;
        --tw-ring-color: transparent !important;
    }
    .fi-input-wrp:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25), inset 0 2px 4px rgba(0, 0, 0, 0.4) !important;
    }
    .fi-input-wrp input,
    .fi-input-wrp select,
    .fi-input-wrp textarea {
        color: #dae2fd !important;
        font-size: 13.5px !important;
        background-color: transparent !important;
    }
    .fi-fo-field-wrp-label label,
    .fi-fo-field-wrp-label span {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #8e909f !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd; display: flex; flex-direction: column; gap: 22px;"
     x-data="{
         activeTab: 'overview',
         copiedCode: false,
         copiedApp: false,
         copyText(text, type) {
             navigator.clipboard.writeText(text);
             if (type === 'code') { this.copiedCode = true; setTimeout(() => this.copiedCode = false, 2000); }
             if (type === 'app') { this.copiedApp = true; setTimeout(() => this.copiedApp = false, 2000); }
         }
     }">

    {{-- ── 1. Top Sub-Navigation / Breadcrumb Bar ── --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <a href="{{ $indexUrl }}"
           style="display: inline-flex; align-items: center; gap: 8px; color: #8e909f; text-decoration: none; font-size: 13px; font-weight: 600; padding: 6px 14px; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 10px; transition: all 0.2s ease;"
           onmouseover="this.style.color='#dae2fd'; this.style.borderColor='#3b82f6';"
           onmouseout="this.style.color='#8e909f'; this.style.borderColor='#222a3d';">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Interns Directory</span>
        </a>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            {{-- Archival cycle badge if present --}}
            @if ($record->cohort_archive_name)
                <span style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 9999px;">
                    📁 Cycle: {{ $record->cohort_archive_name }}
                </span>
            @endif

            {{-- Account Status --}}
            <span style="font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 6px; {{ $record->is_active ? 'background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3);' : 'background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);' }}">
                <span style="width: 6px; height: 6px; border-radius: 50%; background-color: {{ $record->is_active ? '#4edea3' : '#f87171' }};"></span>
                {{ $record->is_active ? 'Account Active' : 'Archived / Inactive' }}
            </span>

            {{-- Tenure Pill --}}
            <span style="font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 9999px; {{ $isTenurePast ? 'background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);' : 'background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);' }}">
                {{ $isTenurePast ? 'Tenure Completed' : 'On-going Tenure' }}
            </span>
        </div>
    </div>

    {{-- ── 2. Hero Identity & Profile Card (Stitch Dark Theme) ── --}}
    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 18px; padding: 24px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35); position: relative; overflow: hidden;">
        {{-- Background ambient glow --}}
        <div style="position: absolute; right: -60px; top: -60px; width: 220px; height: 220px; border-radius: 50%; background: radial-gradient(circle, rgba(30, 64, 175, 0.18) 0%, transparent 70%); pointer-events: none;"></div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Top Info Row --}}
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                
                {{-- Left: Profile Avatar + Identity Details --}}
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; flex: 1; min-width: 280px;">
                    {{-- Avatar with hover upload trigger --}}
                    <div style="position: relative; flex-shrink: 0; group">
                        <div style="width: 88px; height: 88px; border-radius: 50%; overflow: hidden; background-color: #0b1326; border: 3px solid #1e40af; box-shadow: 0 4px 16px rgba(30,64,175,0.4); display: flex; align-items: center; justify-content: center; color: #60a5fa; font-size: 28px; font-weight: 800;">
                            @if ($record->intern_image)
                                <img src="{{ asset('storage/' . $record->intern_image) }}" alt="{{ $name }}" style="width: 100%; height: 100%; object-fit: cover;" />
                            @else
                                {{ $initials }}
                            @endif
                        </div>

                        {{-- Hover / Quick Camera Button --}}
                        <button type="button"
                                wire:click="mountAction('uploadPhoto')"
                                title="Change Profile Picture"
                                style="position: absolute; bottom: 0; right: 0; width: 28px; height: 28px; border-radius: 50%; background-color: #1e40af; border: 2px solid #131b2e; color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s ease;"
                                onmouseover="this.style.transform='scale(1.15)';"
                                onmouseout="this.style.transform='scale(1)';">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>

                    {{-- Identity Details --}}
                    <div style="display: flex; flex-direction: column; gap: 6px; min-width: 240px; flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h1 style="font-size: 24px; font-weight: 800; color: #ffffff; line-height: 1.2; margin: 0;">
                                {{ $name }}
                            </h1>

                            {{-- Intern Code Badge with Copy --}}
                            <div style="display: inline-flex; align-items: center; background-color: #0b1326; border: 1px solid #1d4ed8; border-radius: 8px; padding: 3px 8px; gap: 6px;">
                                <span style="font-family: ui-monospace, monospace; font-size: 13px; font-weight: 800; color: #60a5fa; letter-spacing: -0.01em;">
                                    {{ $internCode }}
                                </span>
                                <button type="button"
                                        @click="copyText('{{ $internCode }}', 'code')"
                                        style="background: transparent; border: none; padding: 0; color: #8e909f; cursor: pointer; display: flex; align-items: center;"
                                        title="Copy Intern ID">
                                    <template x-if="!copiedCode">
                                        <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </template>
                                    <template x-if="copiedCode">
                                        <svg style="width: 13px; height: 13px; color: #4edea3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                </button>
                            </div>

                            @if ($appCode)
                                <div style="display: inline-flex; align-items: center; background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 3px 8px; gap: 6px;">
                                    <span style="font-family: ui-monospace, monospace; font-size: 11.5px; font-weight: 600; color: #8e909f;">
                                        App: {{ $appCode }}
                                    </span>
                                    <button type="button"
                                            @click="copyText('{{ $appCode }}', 'app')"
                                            style="background: transparent; border: none; padding: 0; color: #8e909f; cursor: pointer; display: flex; align-items: center;"
                                            title="Copy Application ID">
                                        <template x-if="!copiedApp">
                                            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </template>
                                        <template x-if="copiedApp">
                                            <svg style="width: 12px; height: 12px; color: #4edea3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </template>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Role and Institution Lines --}}
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #60a5fa; font-weight: 600;">
                            <svg style="width: 16px; height: 16px; color: #60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>{{ $role }}</span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #94a3b8; font-weight: 500;">
                            <svg style="width: 16px; height: 16px; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>{{ $institution }}</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Live Today Attendance Pill & Quick Context --}}
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f;">Today's Attendance:</span>
                        <div style="background-color: {{ $statusInfo['bg'] }}; color: {{ $statusInfo['text'] }}; border: 1px solid {{ $statusInfo['border'] }}; font-size: 13px; font-weight: 700; padding: 4px 14px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: {{ $statusInfo['dot'] }}; display: inline-block;"></span>
                            {{ $statusInfo['label'] }}
                        </div>
                    </div>

                    {{-- Badges Row: Batch & Project --}}
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <div style="background-color: #0c1c38; color: #60a5fa; border: 1px solid #1d4ed8; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>🗓️</span>
                            <span>Batch: {{ $batchDisplay }}</span>
                        </div>

                        <div style="background-color: #210f36; color: #c084fc; border: 1px solid #7c3aed; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>🚀</span>
                            <span>Project: {{ $projectDisplay }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Row: Action Toolbar (matching every functionality) --}}
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding-top: 18px; border-top: 1px solid #222a3d;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    {{-- Edit Profile --}}
                    <a href="{{ $editUrl }}"
                       style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 9px; font-size: 13px; font-weight: 700; background-color: #1e40af; color: #ffffff; text-decoration: none; border: 1px solid #2563eb; box-shadow: 0 2px 8px rgba(30,64,175,0.4); transition: all 0.15s ease;"
                       onmouseover="this.style.backgroundColor='#1d4ed8';"
                       onmouseout="this.style.backgroundColor='#1e40af';">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Details
                    </a>

                    {{-- Completion Certificate Unified Modal --}}
                    <button type="button"
                            wire:click="mountAction('completion_certificate')"
                            style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 9px; font-size: 13px; font-weight: 700; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); cursor: pointer; transition: all 0.15s ease;"
                            onmouseover="this.style.backgroundColor='rgba(245, 158, 11, 0.25)';"
                            onmouseout="this.style.backgroundColor='rgba(245, 158, 11, 0.15)';">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        Completion Certificate
                    </button>

                    {{-- Change Password Modal --}}
                    <button type="button"
                            wire:click="mountAction('changePassword')"
                            style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 9px; font-size: 13px; font-weight: 600; background-color: #171f33; color: #dae2fd; border: 1px solid #222a3d; cursor: pointer; transition: all 0.15s ease;"
                            onmouseover="this.style.backgroundColor='#1e293b'; this.style.borderColor='#3b82f6';"
                            onmouseout="this.style.backgroundColor='#171f33'; this.style.borderColor='#222a3d';">
                        <svg style="width: 15px; height: 15px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Change Password
                    </button>

                    {{-- Upload Photo Modal --}}
                    <button type="button"
                            wire:click="mountAction('uploadPhoto')"
                            style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 9px; font-size: 13px; font-weight: 600; background-color: #171f33; color: #dae2fd; border: 1px solid #222a3d; cursor: pointer; transition: all 0.15s ease;"
                            onmouseover="this.style.backgroundColor='#1e293b'; this.style.borderColor='#3b82f6';"
                            onmouseout="this.style.backgroundColor='#171f33'; this.style.borderColor='#222a3d';">
                        <svg style="width: 15px; height: 15px; color: #38bdf8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Upload Photo
                    </button>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    {{-- I-Card Link --}}
                    <a href="{{ route('print-id-card', ['id' => $record->id]) }}" target="_blank"
                       style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 9px; font-size: 13px; font-weight: 600; background-color: #171f33; color: #dae2fd; border: 1px solid #222a3d; text-decoration: none; transition: all 0.15s ease;"
                       onmouseover="this.style.backgroundColor='#1e293b'; this.style.borderColor='#3b82f6';"
                       onmouseout="this.style.backgroundColor='#171f33'; this.style.borderColor='#222a3d';">
                        <svg style="width: 15px; height: 15px; color: #a78bfa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                        Print I-Card ↗
                    </a>

                    {{-- View Offer Letter if exists --}}
                    @if (filled($record->offer_letter_id))
                        <a href="{{ route('view-offer-pdf', ['id' => $record->offer_letter_id]) }}" target="_blank"
                           style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 9px; font-size: 13px; font-weight: 600; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); text-decoration: none; transition: all 0.15s ease;"
                           onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.25)';"
                           onmouseout="this.style.backgroundColor='rgba(59, 130, 246, 0.15)';">
                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Offer Letter PDF ↗
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Top 4 KPI Statistics Row (Matches Interns Directory) ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        {{-- KPI 1: Attendance Rate --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(78, 222, 163, 0.3);">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Attendance Rate</div>
                <div style="font-size: 26px; font-weight: 700; color: #4edea3; line-height: 1.1; margin-top: 2px;">{{ $attRate }}%</div>
                <div style="font-size: 11.5px; color: #8e909f; margin-top: 4px;">{{ $presDays }}/{{ $totDays }} Days Logged</div>
            </div>
        </div>

        {{-- KPI 2: Tasks & Deliverables --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(147, 51, 234, 0.15); color: #d8b4fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(147, 51, 234, 0.3);">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Tasks Progress</div>
                <div style="font-size: 26px; font-weight: 700; color: #dae2fd; line-height: 1.1; margin-top: 2px;">{{ $completedCount }} / {{ $totalAssigned }}</div>
                <div style="font-size: 11.5px; color: {{ $overdueCount > 0 ? '#f87171' : '#8e909f' }}; margin-top: 4px;">
                    {{ $pendingTasksCount }} Pending {{ $overdueCount > 0 ? '• ⚠️ ' . $overdueCount . ' Overdue' : '' }}
                </div>
            </div>
        </div>

        {{-- KPI 3: Tenure & Timeline --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(59, 130, 246, 0.3);">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Internship Tenure</div>
                <div style="font-size: 22px; font-weight: 700; color: #dae2fd; line-height: 1.1; margin-top: 4px;">
                    {{ $tenureMonths > 0 ? $tenureMonths . ' Months' : 'Active' }}
                </div>
                <div style="font-size: 11.5px; color: #8e909f; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M y') : 'TBD' }} → {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M y') : 'TBD' }}
                </div>
            </div>
        </div>

        {{-- KPI 4: Project & Grade --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(245, 158, 11, 0.3);">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Awarded Grade</div>
                <div style="font-size: 26px; font-weight: 700; color: #fbbf24; line-height: 1.1; margin-top: 2px;">
                    {{ filled($grade) && $grade !== '—' ? $grade : '—' }}
                </div>
                <div style="font-size: 11.5px; color: #8e909f; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    Mentor: {{ $mentorName }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4. Modern Enterprise Tab Navigation Bar ── --}}
    <style>
        .intern-tab-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            color: #94a3b8;
            background-color: transparent;
            text-decoration: none;
            user-select: none;
            white-space: nowrap;
        }
        .intern-tab-item:hover {
            background-color: #17223b;
            color: #dae2fd;
            border-color: #222a3d;
        }
        .intern-tab-item.is-active {
            background-color: #1e40af !important;
            color: #ffffff !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
            font-weight: 700 !important;
        }
        .intern-tab-item svg {
            width: 16px;
            height: 16px;
            color: #8e909f;
            flex-shrink: 0;
            transition: color 0.18s ease;
        }
        .intern-tab-item:hover svg {
            color: #60a5fa;
        }
        .intern-tab-item.is-active svg {
            color: #ffffff !important;
        }
    </style>

    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 6px; box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
        {{-- Inner Segmented Track --}}
        <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 11px; padding: 3px; display: inline-flex; align-items: center; gap: 3px; flex-wrap: wrap;">
            
            {{-- Tab 1: Credentials & Overview --}}
            <button type="button"
                    @click="activeTab = 'overview'"
                    :class="{ 'is-active': activeTab === 'overview' }"
                    class="intern-tab-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                <span>Credentials & Overview</span>
            </button>

            {{-- Tab 2: Academic & Personal --}}
            <button type="button"
                    @click="activeTab = 'academic'"
                    :class="{ 'is-active': activeTab === 'academic' }"
                    class="intern-tab-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
                <span>Academic & Personal</span>
            </button>

            {{-- Tab 3: Offer & Terms --}}
            <button type="button"
                    @click="activeTab = 'offer'"
                    :class="{ 'is-active': activeTab === 'offer' }"
                    class="intern-tab-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Offer & Terms</span>
                @if (filled($record->offer_letter_id))
                    <span style="font-size: 10px; font-weight: 700; color: #4edea3; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 1px 6px; border-radius: 4px;">PDF</span>
                @endif
            </button>

            {{-- Tab 4: Squad & Teammates --}}
            <button type="button"
                    @click="activeTab = 'squad'"
                    :class="{ 'is-active': activeTab === 'squad' }"
                    class="intern-tab-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Squad & Teammates</span>
                @if ($teammates->count() > 0)
                    <span style="font-size: 10px; font-weight: 700; color: #b8c4ff; background: rgba(99, 102, 241, 0.2); border: 1px solid rgba(99, 102, 241, 0.35); padding: 1px 6px; border-radius: 9999px;">
                        {{ $teammates->count() }}
                    </span>
                @endif
            </button>

            {{-- Tab 5: Completion Documents --}}
            <button type="button"
                    @click="activeTab = 'documents'"
                    :class="{ 'is-active': activeTab === 'documents' }"
                    class="intern-tab-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                <span>Completion Documents</span>
                @if ($hasCert || $hasLetter)
                    <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #4edea3; box-shadow: 0 0 6px #4edea3;"></span>
                @endif
            </button>
        </div>

        {{-- Right Side Context Label --}}
        <div style="display: flex; align-items: center; gap: 8px; padding-right: 10px; font-size: 12px; color: #8e909f;">
            <span style="font-weight: 500;">Active View:</span>
            <span style="font-weight: 700; color: #60a5fa;"
                  x-text="activeTab === 'overview' ? 'Login Credentials & Profile' : (activeTab === 'academic' ? 'Education & Academic Details' : (activeTab === 'offer' ? 'Internship Agreement' : (activeTab === 'squad' ? 'Squad Placement' : 'Certificates & Letters')))">
            </span>
        </div>
    </div>

    {{-- ── 5. TAB PANELS ── --}}

    {{-- ─── TAB 1: CREDENTIALS & OVERVIEW ─── --}}
    <div x-show="activeTab === 'overview'" style="display: flex; flex-direction: column; gap: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
            
            {{-- Left: Login Credentials Card (Full Functionality) --}}
            <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(245, 158, 11, 0.3);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <span style="font-size: 15px; font-weight: 700; color: #ffffff;">Intern Portal Credentials</span>
                    </div>

                    <span style="font-size: 11px; font-weight: 600; color: #4edea3; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 3px 8px; border-radius: 6px;">
                        Portal Sync Ready
                    </span>
                </div>

                {{-- Interactive Credentials Widget --}}
                <div style="display: flex; flex-direction: column; gap: 14px; width: 100%;"
                     wire:key="intern-view-cred-{{ $record->id }}-{{ md5($plainPassword) }}"
                     x-data="{
                         showPass: false,
                         copiedU: false,
                         copiedP: false,
                         uId: @js($userId),
                         pPass: @js($plainPassword),
                         copyU() { navigator.clipboard.writeText(this.uId); this.copiedU = true; setTimeout(() => this.copiedU = false, 2000); },
                         copyP() { navigator.clipboard.writeText(this.pPass); this.copiedP = true; setTimeout(() => this.copiedP = false, 2000); }
                     }">
                    
                    {{-- User ID Row --}}
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #fbbf24;">
                                Login User ID
                            </span>
                            <button type="button" 
                                    @click="copyU()" 
                                    style="background: transparent; border: none; font-size: 11px; font-weight: 600; color: #fbbf24; cursor: pointer; padding: 2px 6px; border-radius: 4px;">
                                <span x-show="!copiedU">Copy ID</span>
                                <span x-show="copiedU" x-cloak style="color: #4edea3; font-weight: 700;">✓ Copied!</span>
                            </button>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; background-color: #090e1c; border: 1.5px solid #222a3d; border-radius: 10px; padding: 10px 14px; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);">
                            <span style="font-family: ui-monospace, SFMono-Regular, monospace; font-size: 14px; font-weight: 600; color: #dae2fd; user-select: all; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $userId }}
                            </span>
                            <button type="button" 
                                    @click="copyU()" 
                                    style="background: #17223b; border: 1px solid #222a3d; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #b8c4ff; cursor: pointer; flex-shrink: 0; margin-left: 8px;"
                                    title="Copy User ID">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Password Row --}}
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">
                                Password
                            </span>
                            <span style="font-size: 10.5px; font-weight: 600; padding: 2px 8px; border-radius: 4px; border: 1px solid #222a3d; background-color: #171f33;"
                                  :style="showPass ? 'color: #fbbf24; border-color: rgba(245, 158, 11, 0.35); background-color: rgba(245, 158, 11, 0.12);' : 'color: #94a3b8; border-color: #222a3d; background-color: #171f33;'"
                                  x-text="showPass ? 'Plaintext Revealed' : 'Masked'">
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; background-color: #090e1c; border: 1.5px solid #222a3d; border-radius: 10px; padding: 10px 14px; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);">
                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;">
                                <template x-if="!showPass">
                                    <span style="font-family: ui-monospace, monospace; font-size: 16px; letter-spacing: 0.22em; color: #94a3b8; user-select: none;">
                                        ••••••••••••
                                    </span>
                                </template>
                                <template x-if="showPass">
                                    <span style="font-family: ui-monospace, monospace; font-size: 14px; font-weight: 700; color: #4edea3; user-select: all;" 
                                          x-text="pPass">
                                    </span>
                                </template>
                            </div>

                            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; margin-left: 8px;">
                                {{-- Eye toggle --}}
                                <button type="button" 
                                        @click="showPass = !showPass" 
                                        style="background: #17223b; border: 1px solid #222a3d; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #b8c4ff; cursor: pointer;"
                                        :title="showPass ? 'Hide Plain Password' : 'Show Plain Password'">
                                    <template x-if="!showPass">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </template>
                                    <template x-if="showPass">
                                        <svg style="width: 14px; height: 14px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </template>
                                </button>

                                {{-- Copy password --}}
                                <button type="button" 
                                        @click="copyP()" 
                                        style="background: #17223b; border: 1px solid #222a3d; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #b8c4ff; cursor: pointer;"
                                        title="Copy Password">
                                    <template x-if="!copiedP">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </template>
                                    <template x-if="copiedP">
                                        <svg style="width: 14px; height: 14px; color: #4edea3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                </button>

                                {{-- Change Password Modal Trigger --}}
                                <button type="button" 
                                        wire:click="mountAction('changePassword')" 
                                        style="background: #17223b; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #fbbf24; cursor: pointer;"
                                        title="Change Password">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Action inside Credentials --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 6px; border-top: 1px dashed rgba(255, 255, 255, 0.08);">
                        <button type="button" 
                                wire:click="mountAction('changePassword')"
                                style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font-size: 12px; font-weight: 700; color: #fbbf24; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; cursor: pointer;">
                            <svg style="width: 14px; height: 14px; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Update Password
                        </button>

                        <span style="font-size: 11px; color: #8e909f;">
                            Used for Intern Portal login
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right: Contact & Quick Profile Info --}}
            <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #222a3d; padding-bottom: 12px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span style="font-size: 15px; font-weight: 700; color: #ffffff;">Contact & Profile Information</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
                    {{-- Email --}}
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Email Address</span>
                        <div style="font-size: 13px; font-weight: 600; color: #dae2fd; word-break: break-all;">
                            {{ $record->email }}
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Phone Number</span>
                        <div style="font-size: 13px; font-weight: 600; color: #dae2fd;">
                            {{ $record->phone ?: ($record->offerletter?->phone ?? $record->application?->phone ?? '—') }}
                        </div>
                    </div>

                    {{-- Domain --}}
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Domain</span>
                        <div>
                            <span style="background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 12px; font-weight: 600; padding: 3px 8px; border-radius: 6px;">
                                {{ $record->domain ?: ($record->application?->domain ?? 'General') }}
                            </span>
                        </div>
                    </div>

                    {{-- Academic Year --}}
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Academic Year</span>
                        <div style="font-size: 13px; font-weight: 600; color: #dae2fd;">
                            {{ $record->academic_year ?: ($record->application?->year ?? '—') }}
                        </div>
                    </div>

                    {{-- Batch Timing --}}
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Batch Timing</span>
                        <div style="font-size: 13px; font-weight: 600; color: #dae2fd;">
                            {{ $record->batch?->batch_timing ?: 'Not Scheduled' }}
                        </div>
                    </div>

                    {{-- Working Hours --}}
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Weekly Schedule</span>
                        <div style="font-size: 13px; font-weight: 600; color: #dae2fd;">
                            {{ $record->working_hours ?: ($record->offerletter?->working_hours ?? '42 hours per week') }}
                        </div>
                    </div>
                </div>

                {{-- Skills Chips --}}
                @if (count($skillsArray) > 0)
                    <div style="display: flex; flex-direction: column; gap: 6px; padding-top: 10px; border-top: 1px solid #222a3d;">
                        <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Skills & Competencies</span>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            @foreach ($skillsArray as $skill)
                                <span style="background-color: #17223b; color: #b8c4ff; border: 1px solid #222a3d; font-size: 11.5px; font-weight: 600; padding: 3px 9px; border-radius: 6px;">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── TAB 2: ACADEMIC & PERSONAL DETAILS ─── --}}
    <div x-show="activeTab === 'academic'" style="display: flex; flex-direction: column; gap: 20px;">
        <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(59, 130, 246, 0.3);">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                    <span style="font-size: 16px; font-weight: 700; color: #ffffff;">Academic Background & Education</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Degree / Course</div>
                    <div style="font-size: 15px; font-weight: 700; color: #dae2fd; margin-top: 4px;">
                        {{ $record->degree ?: ($record->offerletter?->degree ?? $record->application?->degree ?? 'Not Specified') }}
                    </div>
                </div>

                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">College / Institution</div>
                    <div style="font-size: 15px; font-weight: 700; color: #dae2fd; margin-top: 4px;">
                        {{ $record->college ?: ($record->offerletter?->college ?? $record->application?->college ?? 'Not Specified') }}
                    </div>
                </div>

                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Affiliated University</div>
                    <div style="font-size: 15px; font-weight: 700; color: #dae2fd; margin-top: 4px;">
                        {{ $record->university ?: ($record->offerletter?->university ?? $record->college ?? 'Not Specified') }}
                    </div>
                </div>

                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">CGPA / Percentage</div>
                    <div style="font-size: 15px; font-weight: 700; color: #4edea3; margin-top: 4px;">
                        {{ $record->cgpa ? $record->cgpa : ($record->application?->cgpa ? $record->application->cgpa : 'Not Recorded') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── TAB 3: OFFER LETTER & TENURE TERMS ─── --}}
    <div x-show="activeTab === 'offer'" style="display: flex; flex-direction: column; gap: 20px;">
        <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(59, 130, 246, 0.3);">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span style="font-size: 16px; font-weight: 700; color: #ffffff;">Internship & Offer Letter Agreement</span>
                </div>

                @if (filled($record->offer_letter_id))
                    <a href="{{ route('view-offer-pdf', ['id' => $record->offer_letter_id]) }}" target="_blank"
                       style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); text-decoration: none;">
                        📄 View Signed Offer Letter PDF ↗
                    </a>
                @endif
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Internship Role</div>
                    <div style="font-size: 14px; font-weight: 700; color: #60a5fa; margin-top: 4px;">{{ $role }}</div>
                </div>

                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Position Title</div>
                    <div style="font-size: 14px; font-weight: 600; color: #dae2fd; margin-top: 4px;">
                        {{ $record->internship_position ?: ($record->offerletter?->internship_position ?? 'Intern') }}
                    </div>
                </div>

                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Joining Date</div>
                    <div style="font-size: 14px; font-weight: 600; color: #dae2fd; margin-top: 4px;">
                        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'TBD' }}
                    </div>
                </div>

                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Completion Date</div>
                    <div style="font-size: 14px; font-weight: 600; color: #dae2fd; margin-top: 4px;">
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'TBD' }}
                    </div>
                </div>

                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Offer Issue Date</div>
                    <div style="font-size: 14px; font-weight: 600; color: #dae2fd; margin-top: 4px;">
                        {{ $record->offerletter?->offer_issue_date ? \Carbon\Carbon::parse($record->offerletter->offer_issue_date)->format('d M Y') : '—' }}
                    </div>
                </div>

                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Offer Status</div>
                    <div style="margin-top: 4px;">
                        @php
                            $offerStatus = $record->offerletter?->offer_status ?? 'draft';
                            $statusColors = [
                                'accepted' => ['bg' => 'rgba(16, 185, 129, 0.15)', 'text' => '#4edea3', 'border' => 'rgba(16, 185, 129, 0.3)'],
                                'draft'    => ['bg' => 'rgba(245, 158, 11, 0.15)', 'text' => '#fbbf24', 'border' => 'rgba(245, 158, 11, 0.3)'],
                                'rejected' => ['bg' => 'rgba(239, 68, 68, 0.15)', 'text' => '#f87171', 'border' => 'rgba(239, 68, 68, 0.3)'],
                            ];
                            $stCol = $statusColors[strtolower($offerStatus)] ?? ['bg' => '#17223b', 'text' => '#dae2fd', 'border' => '#222a3d'];
                        @endphp
                        <span style="background-color: {{ $stCol['bg'] }}; color: {{ $stCol['text'] }}; border: 1px solid {{ $stCol['border'] }}; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 6px;">
                            {{ ucfirst($offerStatus) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── TAB 4: SQUAD & TEAMMATES ─── --}}
    <div x-show="activeTab === 'squad'" style="display: flex; flex-direction: column; gap: 20px;">
        <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222a3d; padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(147, 51, 234, 0.15); color: #d8b4fe; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(147, 51, 234, 0.3);">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <span style="font-size: 16px; font-weight: 700; color: #ffffff;">Squad: {{ $projectDisplay }}</span>
                        <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">
                            Track: {{ $record->team?->track ?: 'General' }} • Mentor: {{ $mentorName }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Teammates List --}}
            @php
                $teammates = $record->teammates()->with(['offerletter', 'application'])->get();
            @endphp

            @if ($teammates->count() > 0)
                <div style="font-size: 13px; font-weight: 700; color: #b8c4ff; text-transform: uppercase; letter-spacing: 0.05em;">
                    Squad Members ({{ $teammates->count() }} Co-Interns)
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                    @foreach ($teammates as $teammate)
                        @php
                            $tmName = $teammate->name ?: ($teammate->offerletter?->name ?? $teammate->application?->name ?? 'Teammate');
                            $tmRole = $teammate->internship_role ?: ($teammate->offerletter?->internship_role ?? 'Intern');
                            $tmCode = $teammate->intern_code ?: ('INT-' . str_pad($teammate->id, 3, '0', STR_PAD_LEFT));
                            $tmUrl = \App\Filament\Resources\InternManagement\InternResource::getUrl('view', ['record' => $teammate]);
                            $tmWords = explode(' ', trim($tmName));
                            $tmInitials = strtoupper(substr($tmWords[0] ?? 'T', 0, 1) . substr($tmWords[1] ?? '', 0, 1));
                        @endphp

                        <a href="{{ $tmUrl }}"
                           style="background-color: #090e1c; border: 1.5px solid #222a3d; border-radius: 12px; padding: 12px 16px; display: flex; align-items: center; gap: 14px; text-decoration: none; color: inherit; transition: all 0.2s ease;"
                           onmouseover="this.style.borderColor='#3b82f6'; this.style.transform='translateY(-2px)';"
                           onmouseout="this.style.borderColor='#222a3d'; this.style.transform='translateY(0)';">
                            <div style="width: 42px; height: 42px; border-radius: 50%; overflow: hidden; background-color: #17223b; color: #60a5fa; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; border: 1px solid #1d4ed8; flex-shrink: 0;">
                                @if ($teammate->intern_image)
                                    <img src="{{ asset('storage/' . $teammate->intern_image) }}" alt="{{ $tmName }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                @else
                                    {{ $tmInitials }}
                                @endif
                            </div>

                            <div style="min-width: 0; flex: 1;">
                                <div style="font-size: 14px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tmName }}
                                </div>
                                <div style="font-size: 12px; color: #60a5fa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                                    {{ $tmRole }}
                                </div>
                            </div>

                            <span style="font-family: ui-monospace, monospace; font-size: 11px; font-weight: 700; color: #8e909f; background: #171f33; padding: 2px 6px; border-radius: 4px; border: 1px solid #222a3d;">
                                {{ $tmCode }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div style="background-color: #090e1c; border: 1px dashed #222a3d; border-radius: 12px; padding: 28px; text-align: center; color: #8e909f;">
                    <div style="font-size: 24px; margin-bottom: 8px;">👥</div>
                    <div style="font-size: 14px; font-weight: 600; color: #dae2fd;">No other teammates in this squad</div>
                    <div style="font-size: 12px; margin-top: 4px;">Assign other interns to Squad "{{ $projectDisplay }}" to view them here.</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── TAB 5: COMPLETION DOCUMENTS & ARCHIVAL ─── --}}
    <div x-show="activeTab === 'documents'" style="display: flex; flex-direction: column; gap: 20px;">
        
        {{-- Unified Action Notice Banner --}}
        <div style="background: #131b2e; border: 1.5px solid rgba(245, 158, 11, 0.4); border-radius: 14px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div>
                <div style="font-size: 14px; font-weight: 700; color: #fbbf24; display: flex; align-items: center; gap: 8px;">
                    <span>⚡</span>
                    <span>Completion Documents Center</span>
                </div>
                <div style="font-size: 12px; color: #c4c5d5; margin-top: 3px;">
                    Verify details, assign grades, and generate both the Completion Certificate and Letter at the same time.
                </div>
            </div>

            <button type="button"
                    wire:click="mountAction('completion_certificate')"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 9px; font-size: 13px; font-weight: 700; background-color: #fbbf24; color: #0b1326; border: none; cursor: pointer; box-shadow: 0 2px 10px rgba(245, 158, 11, 0.4); transition: transform 0.15s ease;"
                    onmouseover="this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.transform='translateY(0)';">
                <span>🎓</span>
                <span>{{ $hasCert || $hasLetter ? 'Update / Re-generate Both Documents' : 'Generate Completion Documents' }}</span>
            </button>
        </div>

        {{-- Dual Document Panels --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
            {{-- Certificate Card --}}
            <div style="background-color: #131b2e; border: 1.5px solid {{ $hasCert ? 'rgba(245, 158, 11, 0.4)' : '#222a3d' }}; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0,0,0,0.25); display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 20px;">🏅</span>
                        <span style="font-size: 14px; font-weight: 700; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.05em;">Completion Certificate</span>
                    </div>

                    @if ($hasCert)
                        <span style="font-size: 11px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 3px 10px; border-radius: 9999px;">
                            Issued
                        </span>
                    @else
                        <span style="font-size: 11px; font-weight: 600; color: #8e909f; background-color: #171f33; border: 1px dashed #2d3449; padding: 3px 10px; border-radius: 9999px;">
                            Not Generated
                        </span>
                    @endif
                </div>

                @if ($hasCert)
                    <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #8e909f;">Reference ID:</span>
                            <span style="font-family: ui-monospace, monospace; font-weight: 700; color: #fbbf24;">{{ $certRef }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #8e909f;">Project:</span>
                            <span style="font-weight: 600; color: #dae2fd;">{{ $cert?->project_name ?: $projectDisplay }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #8e909f;">Grade Awarded:</span>
                            <span style="font-weight: 700; color: #4edea3;">{{ $cert?->grade ?: ($grade ?: 'A') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #8e909f;">Issuing Date:</span>
                            <span style="color: #dae2fd;">{{ $cert?->issuing_date?->format('d M Y') ?: ($record->issuing_date?->format('d M Y') ?? '—') }}</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: auto;">
                        <a href="{{ route('intern.certificate.view', ['id' => $record->id]) }}" target="_blank"
                           style="flex: 1; text-align: center; font-size: 12px; font-weight: 700; color: #dae2fd; background-color: #171f33; border: 1px solid #222a3d; padding: 9px 12px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease;">
                            👁️ View Certificate ↗
                        </a>
                        <a href="{{ route('intern.certificate.download', ['id' => $record->id]) }}" target="_blank"
                           style="flex: 1; text-align: center; font-size: 12px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); padding: 9px 12px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease;">
                            ⬇ Download PDF
                        </a>
                    </div>
                @else
                    <div style="background-color: #090e1c; border: 1px dashed #222a3d; border-radius: 12px; padding: 24px; text-align: center; color: #8e909f; font-size: 13px;">
                        No completion certificate generated yet. Click "Generate Completion Documents" above.
                    </div>
                @endif
            </div>

            {{-- Letter Card --}}
            <div style="background-color: #131b2e; border: 1.5px solid {{ $hasLetter ? 'rgba(16, 185, 129, 0.4)' : '#222a3d' }}; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0,0,0,0.25); display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 20px;">📄</span>
                        <span style="font-size: 14px; font-weight: 700; color: #4edea3; text-transform: uppercase; letter-spacing: 0.05em;">Completion Letter</span>
                    </div>

                    @if ($hasLetter)
                        <span style="font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 3px 10px; border-radius: 9999px;">
                            Issued
                        </span>
                    @else
                        <span style="font-size: 11px; font-weight: 600; color: #8e909f; background-color: #171f33; border: 1px dashed #2d3449; padding: 3px 10px; border-radius: 9999px;">
                            Not Generated
                        </span>
                    @endif
                </div>

                @if ($hasLetter)
                    <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #8e909f;">Reference ID:</span>
                            <span style="font-family: ui-monospace, monospace; font-weight: 700; color: #4edea3;">{{ $letterRef }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #8e909f;">Template:</span>
                            <span style="font-weight: 600; color: #dae2fd;">{{ ucfirst($letter?->template ?: ($record->completion_letter_template ?: 'bachelors')) }} Degree</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #8e909f;">Project:</span>
                            <span style="font-weight: 600; color: #dae2fd;">{{ $letter?->project_name ?: $projectDisplay }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #8e909f;">Grade Awarded:</span>
                            <span style="font-weight: 700; color: #4edea3;">{{ $letter?->grade ?: ($grade ?: 'A') }}</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: auto;">
                        <a href="{{ route('intern.completion_letter.view', ['id' => $record->id]) }}" target="_blank"
                           style="flex: 1; text-align: center; font-size: 12px; font-weight: 700; color: #dae2fd; background-color: #171f33; border: 1px solid #222a3d; padding: 9px 12px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease;">
                            👁️ View Letter ↗
                        </a>
                        <a href="{{ route('intern.completion_letter.download', ['id' => $record->id]) }}" target="_blank"
                           style="flex: 1; text-align: center; font-size: 12px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); padding: 9px 12px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease;">
                            ⬇ Download PDF
                        </a>
                    </div>
                @else
                    <div style="background-color: #090e1c; border: 1px dashed #222a3d; border-radius: 12px; padding: 24px; text-align: center; color: #8e909f; font-size: 13px;">
                        No completion letter generated yet. Click "Generate Completion Documents" above.
                    </div>
                @endif
            </div>
        </div>

        {{-- Project Description & Summary --}}
        @php
            $projDesc = $record->team?->project_description ?: $record->project_description;
        @endphp
        @if (filled($projDesc))
            <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0,0,0,0.25); display: flex; flex-direction: column; gap: 12px;">
                <span style="font-size: 14px; font-weight: 700; color: #ffffff;">Project Description & Overview</span>
                <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 16px; font-size: 13.5px; color: #dae2fd; line-height: 1.6;">
                    {!! $projDesc !!}
                </div>
            </div>
        @endif

        {{-- Archival Cycle Card if archived --}}
        @if ($record->cohort_archive_name || $record->archived_at)
            <div style="background-color: #131b2e; border: 1.5px solid rgba(245, 158, 11, 0.35); border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0,0,0,0.25); display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;">📁</span>
                    <span style="font-size: 14px; font-weight: 700; color: #fbbf24; text-transform: uppercase;">Cohort Archival Record</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
                    <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 10px; padding: 12px 16px;">
                        <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Archived Cohort Cycle</div>
                        <div style="font-size: 14px; font-weight: 700; color: #fbbf24; margin-top: 3px;">
                            {{ $record->cohort_archive_name ?: 'Completed Cohort' }}
                        </div>
                    </div>

                    <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 10px; padding: 12px 16px;">
                        <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Archived On</div>
                        <div style="font-size: 14px; font-weight: 600; color: #dae2fd; margin-top: 3px;">
                            {{ $record->archived_at ? $record->archived_at->format('d M Y, h:i A') : '—' }}
                        </div>
                    </div>

                    @if ($record->archive_note)
                        <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 10px; padding: 12px 16px; grid-column: 1 / -1;">
                            <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase;">Archival Action Note</div>
                            <div style="font-size: 13px; color: #dae2fd; margin-top: 3px;">
                                {{ $record->archive_note }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

</div>
</x-filament-panels::page>
