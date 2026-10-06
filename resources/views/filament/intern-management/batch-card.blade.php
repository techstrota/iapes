@php
    $record = $getRecord();
    $batchName = $record->batch_name ?? 'Unnamed Batch';
    $isArchived = $record->is_archived ?? false;

    // Timing string resolution & shift calculation
    $timingRaw = $record->batch_timing ?: '';
    $rawHour = 9;
    $timeLine1 = '09:30 AM - 01:30';
    $timeLine2 = 'PM';

    if (!empty($timingRaw)) {
        // Split timing string on " To " or " to " or " - "
        $parts = preg_split('/(\s+to\s+|\s*-\s*)/i', trim($timingRaw));
        if (count($parts) >= 2) {
            $startStr = trim($parts[0]);
            $endStr = trim($parts[1]);

            try {
                $startCarbon = \Carbon\Carbon::parse($startStr);
                $rawHour = (int) $startCarbon->format('H');
                $startFormatted = $startCarbon->format('g:i A');
            } catch (\Exception $e) {
                $rawHour = 9;
                $startFormatted = $startStr;
            }

            try {
                $endCarbon = \Carbon\Carbon::parse($endStr);
                $endFormatted = $endCarbon->format('g:i A');
            } catch (\Exception $e) {
                $endFormatted = $endStr;
            }

            // Split endFormatted into time and AM/PM
            $endWords = explode(' ', $endFormatted);
            if (count($endWords) >= 2) {
                $timeLine1 = "{$startFormatted} - {$endWords[0]}";
                $timeLine2 = $endWords[1];
            } else {
                $timeLine1 = "{$startFormatted} -";
                $timeLine2 = $endFormatted;
            }
        } else {
            $timeLine1 = $timingRaw;
            $timeLine2 = '';
        }
    }

    // Shift resolution based on parsed hour
    if ($rawHour < 12) {
        $shiftLabel = 'Morning';
        $shiftBg = '#172442';
        $shiftColor = '#93c5fd';
        $shiftBorder = 'rgba(59, 130, 246, 0.35)';
    } elseif ($rawHour < 17) {
        $shiftLabel = 'Afternoon';
        $shiftBg = 'rgba(88, 28, 135, 0.4)';
        $shiftColor = '#d8b4fe';
        $shiftBorder = 'rgba(168, 85, 247, 0.35)';
    } else {
        $shiftLabel = 'Evening';
        $shiftBg = 'rgba(120, 53, 15, 0.4)';
        $shiftColor = '#fde68a';
        $shiftBorder = 'rgba(245, 158, 11, 0.35)';
    }

    // Interns collection & count
    $interns = $record->interns ?? collect();
    $internCount = $interns->count() ?: ($record->no_of_interns ?? 0);

    // Teams / Squads resolution
    $teams = $record->teams ?? collect();
    if ($teams->isEmpty() && $record->team) {
        $teams = collect([$record->team]);
    }
    $squadsCount = $teams->count();
    $squadsText = $squadsCount . ' ' . \Illuminate\Support\Str::plural('Squad', $squadsCount);

    $editUrl = \App\Filament\Resources\InternManagement\InternshipBatchResource::getUrl('edit', ['record' => $record]);
    $internsFilterUrl = \App\Filament\Resources\InternManagement\InternResource::getUrl('index') . '?selectedBatchId=' . $record->id;
@endphp

{{-- CSS Resets: Strip Filament's outer card box, outer padding, and outer border --}}
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

<div style="background-color: #0c1424; border: 1.5px solid #1a2438; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.28); transition: all 0.2s ease; width: 100%; box-sizing: border-box;"
     onmouseover="this.style.borderColor='rgba(59, 130, 246, 0.45)'; this.style.boxShadow='0 10px 25px -5px rgba(0, 0, 0, 0.5)';"
     onmouseout="this.style.borderColor='#1a2438'; this.style.boxShadow='0 4px 20px rgba(0, 0, 0, 0.28)';">

    <div>
        {{-- Top Row: Symmetrical Status Pill (Left) & Timing Pill (Right) --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
            {{-- Status Pill --}}
            @if (!$isArchived)
                <div style="display: inline-flex; align-items: center; gap: 9px; padding: 7px 16px; border-radius: 9999px; background-color: rgba(6, 78, 59, 0.4); border: 1px solid rgba(16, 185, 129, 0.4); flex-shrink: 0;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #34d399; flex-shrink: 0; box-shadow: 0 0 8px rgba(52, 211, 153, 0.6);"></span>
                    <div style="display: flex; flex-direction: column; font-size: 13px; font-weight: 700; color: #34d399; line-height: 1.15;">
                        <span>Active</span>
                        <span>Cohort</span>
                    </div>
                </div>
            @else
                <div style="display: inline-flex; align-items: center; gap: 9px; padding: 7px 16px; border-radius: 9999px; background-color: rgba(120, 53, 15, 0.4); border: 1px solid rgba(245, 158, 11, 0.4); flex-shrink: 0;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #fbbf24; flex-shrink: 0;"></span>
                    <div style="display: flex; flex-direction: column; font-size: 13px; font-weight: 700; color: #fde68a; line-height: 1.15;">
                        <span>Archived</span>
                        <span>Batch</span>
                    </div>
                </div>
            @endif

            {{-- Timing Pill --}}
            <div style="display: inline-flex; align-items: center; gap: 9px; padding: 7px 16px; border-radius: 9999px; background-color: rgba(69, 26, 3, 0.35); border: 1px solid rgba(245, 158, 11, 0.45); flex-shrink: 0;">
                <svg style="width: 16px; height: 16px; color: #fbbf24; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"></path>
                </svg>
                <div style="display: flex; flex-direction: column; font-size: 13px; font-weight: 700; color: #fbbf24; line-height: 1.15;">
                    <span>{{ $timeLine1 }}</span>
                    @if ($timeLine2)
                        <span>{{ $timeLine2 }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Batch Title & Shift Badge --}}
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-top: 20px;">
            <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.35; letter-spacing: -0.01em; word-break: break-word;">
                    {{ $batchName }}
                </h3>
                @if ($isArchived && !empty($record->cohort_archive_name))
                    <div style="display: inline-flex; align-items: center; gap: 5px; margin-top: 2px;">
                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); font-size: 11px; font-weight: 700; color: #fde68a;">
                            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            {{ $record->cohort_archive_name }}
                        </span>
                    </div>
                @endif
            </div>
            <span style="font-size: 12px; font-weight: 600; padding: 3px 11px; border-radius: 7px; background-color: {{ $shiftBg }}; color: {{ $shiftColor }}; border: 1px solid {{ $shiftBorder }}; flex-shrink: 0; margin-top: 2px;">
                {{ $shiftLabel }}
            </span>
        </div>

        {{-- Middle Recessed Box (Avatars, Intern Count, Status, Squads) --}}
        <div style="margin-top: 18px; background-color: #090e1c; border: 1px solid #1a2335; border-radius: 14px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
            {{-- Left: Avatars + Intern Count & Status --}}
            <div style="display: flex; align-items: center; min-width: 0; gap: 12px;">
                {{-- Avatar Stack --}}
                <div style="display: flex; align-items: center; flex-shrink: 0; margin-left: 8px;">
                    @php
                        $avatarColors = ['#2563eb', '#059669', '#7c3aed', '#d97706'];
                        $firstInterns = $interns->take(3);
                        $remaining = $internCount - $firstInterns->count();
                    @endphp

                    @forelse ($firstInterns as $idx => $internItem)
                        @php
                            $iWords = explode(' ', trim($internItem->name));
                            $iInitials = strtoupper(substr($iWords[0] ?? 'I', 0, 1) . substr($iWords[1] ?? '', 0, 1));
                            $bgCol = $avatarColors[$idx % count($avatarColors)];
                        @endphp
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: {{ $bgCol }}; color: #ffffff; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid #090e1c; margin-left: -8px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);" title="{{ $internItem->name }}">
                            {{ $iInitials ?: 'IN' }}
                        </div>
                    @empty
                        {{-- Clean placeholder initials matching reference mockup --}}
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: #2563eb; color: #ffffff; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid #090e1c; margin-left: -8px;">
                            SD
                        </div>
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: #059669; color: #ffffff; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid #090e1c; margin-left: -8px;">
                            MD
                        </div>
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: #7c3aed; color: #ffffff; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid #090e1c; margin-left: -8px;">
                            NP
                        </div>
                    @endforelse

                    @if ($remaining > 0)
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: #1e293b; color: #ffffff; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid #090e1c; margin-left: -8px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);" title="{{ $remaining }} more interns">
                            +{{ $remaining }}
                        </div>
                    @endif
                </div>

                {{-- Interns Count and Active / Archived Label (No truncation) --}}
                <div style="display: flex; flex-direction: column; flex-shrink: 0;">
                    <span style="font-size: 14px; font-weight: 800; color: #ffffff; line-height: 1.2; white-space: nowrap;">
                        {{ $internCount }} Interns
                    </span>
                    @if ($isArchived)
                        <span style="font-size: 11.5px; font-weight: 600; color: #fbbf24; line-height: 1.2; margin-top: 2px; white-space: nowrap;">
                            ✓ Completed & Archived
                        </span>
                    @else
                        @php
                            $activeCount = $interns->where('is_active', true)->count();
                        @endphp
                        @if ($activeCount === $internCount && $internCount > 0)
                            <span style="font-size: 11.5px; font-weight: 600; color: #34d399; line-height: 1.2; margin-top: 2px; white-space: nowrap;">
                                Enrolled & Active
                            </span>
                        @elseif ($internCount > 0)
                            <span style="font-size: 11.5px; font-weight: 600; color: #60a5fa; line-height: 1.2; margin-top: 2px; white-space: nowrap;">
                                {{ $activeCount }} Active / {{ $internCount }} Total
                            </span>
                        @else
                            <span style="font-size: 11.5px; font-weight: 600; color: #94a3b8; line-height: 1.2; margin-top: 2px; white-space: nowrap;">
                                Awaiting Interns
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Right: Squads Badge --}}
            <div style="flex-shrink: 0; background-color: #141d2e; border: 1px solid #233149; border-radius: 8px; padding: 5px 11px; font-size: 11.5px; font-weight: 600; color: #cbd5e1; white-space: nowrap;">
                {{ $squadsText }}
            </div>
        </div>
    </div>

    {{-- Card Footer Actions: Edit Button & View Interns Button --}}
    <div style="margin-top: 20px; display: flex; align-items: center; gap: 10px;">
        <a
            href="{{ $editUrl }}"
            style="display: inline-flex; align-items: center; gap: 7px; padding: 8px 15px; border-radius: 10px; font-size: 13px; font-weight: 600; background-color: #111a2c; color: #dae2fd; border: 1.5px solid #1f2c42; text-decoration: none; transition: all 0.15s ease; flex-shrink: 0;"
            onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff'; this.style.backgroundColor='#162238';"
            onmouseout="this.style.borderColor='#1f2c42'; this.style.color='#dae2fd'; this.style.backgroundColor='#111a2c';">
            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Edit</span>
        </a>

        <a
            href="{{ $internsFilterUrl }}"
            style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; background-color: #152442; color: #93c5fd; border: 1.5px solid #204582; text-decoration: none; transition: all 0.15s ease;"
            onmouseover="this.style.backgroundColor='#1d4ed8'; this.style.color='#ffffff'; this.style.borderColor='#3b82f6';"
            onmouseout="this.style.backgroundColor='#152442'; this.style.color='#93c5fd'; this.style.borderColor='#204582';">
            <span>View Interns</span>
            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

</div>
