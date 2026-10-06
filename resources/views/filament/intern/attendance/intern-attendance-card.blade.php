@php
    $record = $getRecord();
    $date = $record->date ? \Carbon\Carbon::parse($record->date) : null;
    $status = strtolower($record->status ?? 'present');
    $note = $record->note;

    // Status styling
    if ($status === 'present') {
        $badgeLabel = '✓ Present';
        $badgeBg = 'rgba(16, 185, 129, 0.15)';
        $badgeColor = '#4edea3';
        $badgeBorder = 'rgba(78, 222, 163, 0.35)';
        $dotColor = '#10b981';
    } elseif ($status === 'leave') {
        $badgeLabel = '🏖 Approved Leave';
        $badgeBg = 'rgba(59, 130, 246, 0.15)';
        $badgeColor = '#60a5fa';
        $badgeBorder = 'rgba(59, 130, 246, 0.35)';
        $dotColor = '#3b82f6';
    } else {
        $badgeLabel = '✕ Absent';
        $badgeBg = 'rgba(239, 68, 68, 0.15)';
        $badgeColor = '#f87171';
        $badgeBorder = 'rgba(239, 68, 68, 0.35)';
        $dotColor = '#ef4444';
    }

    $isToday = $date ? $date->isToday() : false;
    $dayNum = $date ? $date->format('d') : '—';
    $monthYear = $date ? $date->format('M Y') : '—';
    $dayOfWeek = $date ? $date->format('l') : '—';

    // Batch timing from intern
    $batchTiming = $record->intern?->batch?->batch_timing;
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
    .fi-ta-content-grid .fi-ta-actions,
    .fi-ta-content-grid .fi-ta-actions-cell {
        display: none !important;
    }
</style>

<div style="background-color: #131b2e; border: 1.5px solid {{ $isToday ? 'rgba(59, 130, 246, 0.65)' : '#222a3d' }}; border-radius: 16px; padding: 18px 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: {{ $isToday ? '0 4px 20px rgba(30, 64, 175, 0.3)' : '0 4px 20px rgba(0, 0, 0, 0.25)' }}; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); width: 100%; box-sizing: border-box; height: 100%; position: relative; overflow: hidden;"
     onmouseover="this.style.borderColor='rgba(59, 130, 246, 0.6)'; this.style.boxShadow='0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 0 15px rgba(59, 130, 246, 0.1)';"
     onmouseout="this.style.borderColor='{{ $isToday ? 'rgba(59, 130, 246, 0.65)' : '#222a3d' }}'; this.style.boxShadow='{{ $isToday ? '0 4px 20px rgba(30, 64, 175, 0.3)' : '0 4px 20px rgba(0, 0, 0, 0.25)' }}';">

    <div>
        {{-- Top Row: Calendar Block on Left & Status Badge on Right --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px;">
            {{-- Calendar Date Pill --}}
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #171f33 0%, #1e293b 100%); border: 1px solid #2a354d; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.05);">
                    <span style="font-size: 16px; font-weight: 800; color: #ffffff; line-height: 1;">{{ $dayNum }}</span>
                    <span style="font-size: 9.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 1px;">{{ $date ? $date->format('M') : '' }}</span>
                </div>
                <div>
                    <div style="font-size: 14px; font-weight: 700; color: #ffffff; line-height: 1.2;">
                        {{ $dayOfWeek }}
                        @if ($isToday)
                            <span style="margin-left: 4px; font-size: 10px; font-weight: 700; color: #60a5fa; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); padding: 1px 6px; border-radius: 4px;">TODAY</span>
                        @endif
                    </div>
                    <div style="font-size: 11.5px; color: #8e909f; margin-top: 2px;">
                        {{ $date ? $date->format('d M, Y') : 'No Date' }}
                    </div>
                </div>
            </div>

            {{-- Status Badge --}}
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background-color: {{ $badgeBg }}; color: {{ $badgeColor }}; border: 1px solid {{ $badgeBorder }}; font-size: 11px; font-weight: 700; letter-spacing: 0.02em; white-space: nowrap;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background-color: {{ $dotColor }}; box-shadow: 0 0 6px {{ $dotColor }};"></span>
                {{ $badgeLabel }}
            </span>
        </div>

        {{-- Recessed Telemetry Box --}}
        <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;">
            @if ($batchTiming)
                <div style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: #93c5fd;">
                    <svg style="width: 13px; height: 13px; flex-shrink: 0; color: #60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Shift: <strong>{{ $batchTiming }}</strong></span>
                </div>
            @endif

            @if ($note)
                <div style="display: flex; align-items: flex-start; gap: 6px; font-size: 11.5px; color: #cbd5e1; line-height: 1.4; background: rgba(255, 255, 255, 0.03); border-radius: 6px; padding: 6px 8px; border-left: 3px solid {{ $badgeColor }};">
                    <svg style="width: 13px; height: 13px; flex-shrink: 0; color: {{ $badgeColor }}; margin-top: 1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    <span style="font-style: italic; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                        "{{ $note }}"
                    </span>
                </div>
            @else
                <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b;">
                    <svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Standard attendance record logged</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer Strip: Relative Timestamp --}}
    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid #1c2638; font-size: 11px; color: #64748b;">
        <span>{{ $date ? $date->diffForHumans() : '' }}</span>
        <span style="color: #475569;">#{{ $record->id }}</span>
    </div>

</div>
