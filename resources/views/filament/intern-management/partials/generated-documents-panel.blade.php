@php
    $record = $getRecord();
    $cert = $record->completionCertificate;
    $letter = $record->completionLetter;
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
    {{-- Certificate Card --}}
    <div style="background-color: #131b2e; border: 1px solid {{ $cert || $record->cert_ref_id ? 'rgba(245, 158, 11, 0.3)' : '#222a3d' }}; border-radius: 12px; padding: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🏅</span>
                <span style="font-size: 13px; font-weight: 700; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.05em;">Completion Certificate</span>
            </div>
            @if($cert || $record->cert_ref_id)
                <span style="font-size: 11px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 2px 8px; border-radius: 9999px;">
                    Issued
                </span>
            @else
                <span style="font-size: 11px; font-weight: 600; color: #8e909f; background-color: #171f33; border: 1px dashed #2d3449; padding: 2px 8px; border-radius: 9999px;">
                    Not Generated
                </span>
            @endif
        </div>

        @if($cert || $record->cert_ref_id)
            <div style="display: flex; flex-direction: column; gap: 6px; font-size: 12px; color: #c4c5d5; margin-bottom: 14px;">
                <div><strong style="color: #8e909f;">Reference ID:</strong> <span style="font-family: ui-monospace, monospace; font-weight: 700; color: #fbbf24;">{{ $cert?->cert_ref_id ?: $record->cert_ref_id }}</span></div>
                <div><strong style="color: #8e909f;">Project:</strong> <span style="color: #dae2fd;">{{ $cert?->project_name ?: ($record->project_name ?: 'Internship Project') }}</span></div>
                <div><strong style="color: #8e909f;">Grade:</strong> <span style="font-weight: 700; color: #4edea3;">{{ $cert?->grade ?: ($record->grade ?: 'A') }}</span></div>
                <div><strong style="color: #8e909f;">Issuing Date:</strong> <span style="color: #dae2fd;">{{ $cert?->issuing_date?->format('d M Y') ?: ($record->issuing_date?->format('d M Y') ?? '—') }}</span></div>
                @if($cert?->generated_at)
                    <div style="font-size: 11px; color: #8e909f;">Generated on {{ $cert->generated_at->format('d M Y, h:i A') }} {{ $cert->generated_by ? 'by ' . $cert->generated_by : '' }}</div>
                @endif
            </div>

            <div style="display: flex; gap: 8px;">
                <a href="{{ route('intern.certificate.view', ['id' => $record->id]) }}" target="_blank"
                   style="flex: 1; text-align: center; font-size: 12px; font-weight: 700; color: #dae2fd; background-color: #171f33; border: 1px solid #222a3d; padding: 7px 12px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease;">
                    👁️ View Certificate
                </a>
                <a href="{{ route('intern.certificate.download', ['id' => $record->id]) }}" target="_blank"
                   style="flex: 1; text-align: center; font-size: 12px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 7px 12px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease;">
                    📥 Download PDF
                </a>
            </div>
        @else
            <div style="font-size: 12px; color: #8e909f; margin-bottom: 12px;">
                No completion certificate has been generated for this intern yet.
            </div>
        @endif
    </div>

    {{-- Letter Card --}}
    <div style="background-color: #131b2e; border: 1px solid {{ $letter || $record->letter_ref_id ? 'rgba(16, 185, 129, 0.3)' : '#222a3d' }}; border-radius: 12px; padding: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">📄</span>
                <span style="font-size: 13px; font-weight: 700; color: #4edea3; text-transform: uppercase; letter-spacing: 0.05em;">Completion Letter</span>
            </div>
            @if($letter || $record->letter_ref_id)
                <span style="font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(78, 222, 163, 0.3); padding: 2px 8px; border-radius: 9999px;">
                    Issued
                </span>
            @else
                <span style="font-size: 11px; font-weight: 600; color: #8e909f; background-color: #171f33; border: 1px dashed #2d3449; padding: 2px 8px; border-radius: 9999px;">
                    Not Generated
                </span>
            @endif
        </div>

        @if($letter || $record->letter_ref_id)
            <div style="display: flex; flex-direction: column; gap: 6px; font-size: 12px; color: #c4c5d5; margin-bottom: 14px;">
                <div><strong style="color: #8e909f;">Reference ID:</strong> <span style="font-family: ui-monospace, monospace; font-weight: 700; color: #4edea3;">{{ $letter?->letter_ref_id ?: $record->letter_ref_id }}</span></div>
                <div><strong style="color: #8e909f;">Template:</strong> <span style="color: #dae2fd;">{{ ucfirst($letter?->template ?: ($record->completion_letter_template ?: 'bachelors')) }} Degree</span></div>
                <div><strong style="color: #8e909f;">Project:</strong> <span style="color: #dae2fd;">{{ $letter?->project_name ?: ($record->project_name ?: 'Internship Project') }}</span></div>
                <div><strong style="color: #8e909f;">Grade:</strong> <span style="font-weight: 700; color: #4edea3;">{{ $letter?->grade ?: ($record->grade ?: 'A') }}</span></div>
                <div><strong style="color: #8e909f;">Issuing Date:</strong> <span style="color: #dae2fd;">{{ $letter?->issuing_date?->format('d M Y') ?: ($record->issuing_date?->format('d M Y') ?? '—') }}</span></div>
                @if($letter?->generated_at)
                    <div style="font-size: 11px; color: #8e909f;">Generated on {{ $letter->generated_at->format('d M Y, h:i A') }} {{ $letter->generated_by ? 'by ' . $letter->generated_by : '' }}</div>
                @endif
            </div>

            <div style="display: flex; gap: 8px;">
                <a href="{{ route('intern.completion_letter.view', ['id' => $record->id]) }}" target="_blank"
                   style="flex: 1; text-align: center; font-size: 12px; font-weight: 700; color: #dae2fd; background-color: #171f33; border: 1px solid #222a3d; padding: 7px 12px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease;">
                    👁️ View Letter
                </a>
                <a href="{{ route('intern.completion_letter.download', ['id' => $record->id]) }}" target="_blank"
                   style="flex: 1; text-align: center; font-size: 12px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(78, 222, 163, 0.3); padding: 7px 12px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease;">
                    📥 Download PDF
                </a>
            </div>
        @else
            <div style="font-size: 12px; color: #8e909f; margin-bottom: 12px;">
                No completion letter has been generated for this intern yet.
            </div>
        @endif
    </div>
</div>
