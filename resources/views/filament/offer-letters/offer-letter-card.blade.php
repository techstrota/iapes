@php
    $record = isset($getRecord) && is_callable($getRecord) ? $getRecord() : ($record ?? null);
    $app = $record?->application;

    $name = $record?->getRawOriginal('name') ?: ($app?->name ?? 'Unnamed Candidate');
    $offerCode = $record?->offer_letter_code ?: ('OFFER/' . now()->year . '/' . str_pad($record?->id ?? 1, 3, '0', STR_PAD_LEFT));
    $role = $record?->internship_role ?: 'Internship Role Not Set';
    $position = $record?->internship_position ?: '';
    $roleDisplay = $position ? "{$role} • {$position}" : $role;

    $status = strtolower($record?->offer_status ?? 'draft');
    $statusMap = [
        'draft'    => ['bg' => 'rgba(245, 158, 11, 0.12)', 'text' => '#fbbf24', 'dot' => '#fbbf24', 'border' => 'rgba(245, 158, 11, 0.35)', 'label' => 'Draft'],
        'accepted' => ['bg' => 'rgba(16, 185, 129, 0.12)', 'text' => '#34d399', 'dot' => '#34d399', 'border' => 'rgba(16, 185, 129, 0.4)',  'label' => 'Accepted'],
        'rejected' => ['bg' => 'rgba(239, 68, 68, 0.12)',  'text' => '#f87171', 'dot' => '#f87171', 'border' => 'rgba(239, 68, 68, 0.4)',  'label' => 'Rejected'],
    ];
    $statusInfo = $statusMap[$status] ?? $statusMap['draft'];

    // Initials for avatar
    $words = preg_split('/\s+/', trim($name));
    $initials = strtoupper(substr($words[0] ?? 'C', 0, 1) . substr($words[1] ?? '', 0, 1));
    if (empty($initials)) {
        $initials = 'OL';
    }

    // Edit URL
    $editUrl = $record ? \App\Filament\Resources\CandidateManagement\OfferLetterResource::getUrl('edit', ['record' => $record]) : '#';

    // Template Layout & Duration & Preset
    $templateKey = $record?->template ?: 'single_page';
    $layoutLabel = $record?->layout_label ?: 'Single Page Offer Letter';
    $durationLabel = $record?->duration_label ?: '3 Months';
    $presetName = $record?->templatePreset?->name;

    // Education & College
    $college = $record?->getRawOriginal('college') ?: ($app?->college ?? '');
    $university = $record?->getRawOriginal('university') ?: '';
    $degree = $record?->getRawOriginal('degree') ?: ($app?->degree ?? '');
    $educationDisplay = trim("{$degree} • {$college}", ' •');

    // Dates & Working Hours
    $joiningDate = $record?->joining_date ? \Carbon\Carbon::parse($record->joining_date)->format('d M Y') : 'Pending';
    $completionDate = $record?->completion_date ? \Carbon\Carbon::parse($record->completion_date)->format('d M Y') : 'Pending';
    $issueDate = $record?->offer_issue_date ? \Carbon\Carbon::parse($record->offer_issue_date)->format('d M Y') : 'Not Set';
    $workingHours = $record?->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday';

    // Contact
    $email = $record?->getRawOriginal('email') ?: ($app?->email ?? '');
    $phone = $record?->getRawOriginal('phone') ?: ($app?->phone ?? '');
@endphp

<div style="display: flex; flex-direction: column; gap: 11px; width: 100%; box-sizing: border-box;">

    {{-- Top Row: Offer Letter Code (Left) | Status Pill (Right) --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
        <span style="font-size: 14px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">
            {{ $offerCode }}
        </span>

        <div style="background-color: {{ $statusInfo['bg'] }}; color: {{ $statusInfo['text'] }}; border: 1px solid {{ $statusInfo['border'] }}; font-size: 12px; font-weight: 600; padding: 2.5px 11px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 6px;">
            <span style="width: 7px; height: 7px; border-radius: 50%; background-color: {{ $statusInfo['dot'] }};"></span>
            {{ $statusInfo['label'] }}
        </div>
    </div>

    {{-- Profile Row: Circular Avatar + Candidate Name & Internship Role/Position --}}
    <a
        href="{{ $editUrl }}"
        style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px; cursor: pointer;">
        <div style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background-color: #172235; color: #38bdf8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14.5px; border: 1.5px solid #233149;">
            {{ $initials }}
        </div>

        <div style="min-width: 0; flex-grow: 1;">
            <div style="font-size: 15.5px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;">
                {{ $name }}
            </div>
            <div style="font-size: 12.5px; color: #60a5fa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; font-weight: 600;" title="{{ $roleDisplay }}">
                {{ $roleDisplay }}
            </div>
        </div>
    </a>

    {{-- Template Badge, Duration & Education Box --}}
    <div style="display: flex; flex-direction: column; gap: 6px; width: 100%;">
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <div style="background-color: #0c1c38; color: #60a5fa; border: 1px solid #1d4ed8; font-size: 11.5px; font-weight: 600; padding: 3px 9px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px; max-width: 100%;" title="Layout: {{ $layoutLabel }}">
                <span>📄</span>
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $layoutLabel }}
                </span>
            </div>

            <div style="background-color: rgba(37, 99, 235, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" title="Duration: {{ $durationLabel }}">
                <span>⏱️</span>
                <span>{{ $durationLabel }}</span>
            </div>

            @if ($presetName)
                <div style="background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" title="Preset: {{ $presetName }}">
                    <span>🔖</span>
                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 140px;">{{ $presetName }}</span>
                </div>
            @endif
        </div>

        @if ($degree || $college)
            <div style="background-color: #210f36; color: #c084fc; border: 1px solid #7c3aed; border-radius: 8px; padding: 6px 10px; display: flex; align-items: flex-start; gap: 8px; width: 100%; box-sizing: border-box;" title="{{ $educationDisplay }}">
                <span style="font-size: 13.5px; line-height: 1.25; flex-shrink: 0; margin-top: 1px;">🎓</span>
                <div style="min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 1px;">
                    @if ($degree)
                        <div style="font-size: 12px; font-weight: 700; color: #ffffff; line-height: 1.3; word-break: break-word;">
                            {{ $degree }}
                        </div>
                    @endif
                    @if ($college)
                        <div style="font-size: 11.5px; font-weight: {{ $degree ? '500' : '700' }}; color: {{ $degree ? '#c084fc' : '#ffffff' }}; line-height: 1.3; word-break: break-word;">
                            {{ $college }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Schedule & Working Hours Box --}}
    <div style="background-color: rgba(0, 0, 0, 0.22); border: 1px solid #1c2638; border-radius: 8px; padding: 8px 10px; display: flex; flex-direction: column; gap: 6px;">
        <div style="display: grid; grid-template-columns: 1.35fr 0.85fr; gap: 6px;">
            <div style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 6px; padding: 5px 7px;">
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #8e909f; letter-spacing: 0.04em;">Internship Tenure</div>
                <div style="font-size: 11.5px; font-weight: 700; color: #ffffff; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $joiningDate }} → {{ $completionDate }}">
                    {{ $joiningDate }} <span style="color: #60a5fa;">→</span> {{ $completionDate }}
                </div>
            </div>
            <div style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 6px; padding: 5px 7px;">
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #8e909f; letter-spacing: 0.04em;">Issued On</div>
                <div style="font-size: 11.5px; font-weight: 700; color: #ffffff; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $issueDate }}
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: #cbd5e1; padding-top: 2px;" title="Working Hours: {{ $workingHours }}">
            <svg style="width: 13.5px; height: 13.5px; flex-shrink: 0; color: #60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <strong style="color: #94a3b8; font-weight: 600;">Hours:</strong> {{ $workingHours }}
            </span>
        </div>
    </div>

    {{-- Contact Row: Email & Phone --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
        @if ($email)
            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; flex: 1;" title="{{ $email }}">
                <svg style="width: 13.5px; height: 13.5px; flex-shrink: 0; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span style="overflow: hidden; text-overflow: ellipsis;">{{ $email }}</span>
            </div>
        @endif

        @if ($phone)
            <div style="display: flex; align-items: center; gap: 5px; font-size: 12px; color: #94a3b8; white-space: nowrap; flex-shrink: 0;">
                <svg style="width: 13.5px; height: 13.5px; flex-shrink: 0; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>{{ $phone }}</span>
            </div>
        @endif
    </div>

</div>
