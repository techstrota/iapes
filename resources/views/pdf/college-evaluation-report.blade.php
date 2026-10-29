<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $evaluation->college_name }} - Internship Performance & Evaluation Report</title>
@php
    $activeColumns = $evaluation->getActiveColumns();
    $colCount = count($activeColumns);

    // Read saved PDF layout settings from database, allowing runtime query options as override
    $savedSettings = $evaluation->columns_config['pdf_settings'] ?? [];
    $pdfSettings   = array_merge($savedSettings, array_filter($pdfOptions ?? []));

    $fontSizeChoice = $pdfSettings['font_size'] ?? ($savedSettings['font_size'] ?? 'normal');
    $spacingChoice  = $pdfSettings['spacing'] ?? ($savedSettings['spacing'] ?? 'normal');

    // Base font sizes: standard is now 12px (previously was cramped 9px)
    $baseFontSize = match ($fontSizeChoice) {
        'compact' => 10.5,
        'large'   => 13.0,
        'xlarge'  => 14.0,
        default   => 12.0, // Standard / Normal
    };

    // Auto-scale slightly if there are many columns (e.g. >= 8)
    if ($colCount >= 9) {
        $baseFontSize = max(9.5, $baseFontSize - 2.5);
    } elseif ($colCount >= 8) {
        $baseFontSize = max(10.5, $baseFontSize - 1.5);
    }

    $tableFontSize = "{$baseFontSize}px";
    $thFontSize    = round($baseFontSize * 0.95, 1) . 'px';
    $subFontSize   = round($baseFontSize * 0.78, 1) . 'px';

    $cellPadding = match ($spacingChoice) {
        'compact'  => '5px 4px',
        'spacious' => '10px 8px',
        default    => '8px 6px', // Comfortable executive standard
    };

    // Check custom column widths from columns_config
    $normCols = $evaluation->getNormalizedColumns();
    $customWidthMap = [];
    foreach ($normCols as $nc) {
        $key = $nc['key'] ?? \Illuminate\Support\Str::slug($nc['label'] ?? '', '_');
        if (!empty($nc['width']) && is_numeric($nc['width']) && (float)$nc['width'] > 0) {
            $customWidthMap[$key] = (float)$nc['width'];
        }
    }

    // Calculate percentage column widths that always total 100% on A4 Portrait
    $weights = [];
    foreach ($activeColumns as $col) {
        $t = $col['type'] ?? 'custom_text';
        $k = $col['key'];

        if (isset($pdfSettings['col_widths'][$k]) && is_numeric($pdfSettings['col_widths'][$k])) {
            $w = (float)$pdfSettings['col_widths'][$k];
        } elseif (isset($savedSettings['col_widths'][$k]) && is_numeric($savedSettings['col_widths'][$k])) {
            $w = (float)$savedSettings['col_widths'][$k];
        } elseif (isset($customWidthMap[$k])) {
            $w = $customWidthMap[$k];
        } else {
            $w = match ($t) {
                'sr_no'        => 6.0,
                'student_id'   => 17.0,
                'student_name' => 24.0,
                'grade'        => 10.0,
                'direct_marks', 'total_marks', 'marks' => 10.5,
                'criteria_attendance', 'criteria_task', 'custom_criterion' => 10.0,
                default        => 32.5,
            };
        }
        $weights[$k] = $w;
    }
    $totalWeight = max(1.0, array_sum($weights));
    $colWidths = [];
    foreach ($weights as $key => $w) {
        $colWidths[$key] = round(($w / $totalWeight) * 100, 2);
    }
@endphp
<style>
    @page {
        size: a4 portrait;
        margin: 92px 12mm 72px 12mm;
    }

    html, body {
        width: 100%;
        max-width: 100%;
    }

    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 11.5px;
        line-height: 1.35;
        color: #000;
        margin: 0;
        padding: 0;
        position: relative;
        background: transparent;
    }

    /* ── Horizontal Blurred Watermark ── */
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.1;
        z-index: -1000;
        width: 100%;
        text-align: center;
    }

    .watermark img {
        width: 460px;
        height: auto;
    }

    /* ── Header (Same to same Offer Letter & Progress Report) ── */
    header {
        position: fixed;
        top: -72px;
        left: 0;
        right: 0;
        border-bottom: 2px solid #f39200;
        padding-bottom: 8px;
        height: 52px;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-logo {
        height: 48px;
        width: auto;
    }

    .header-contact {
        font-size: 11.5px;
        font-weight: bold;
        color: #000;
        vertical-align: bottom;
    }

    /* ── Footer ── */
    footer {
        position: fixed;
        bottom: -60px;
        left: 0;
        right: 0;
        text-align: center;
        border-top: 2px solid #f39200;
        padding-top: 4px;
        color: #000;
        background: transparent;
        height: 52px;
    }

    .footer-brand {
        font-size: 11.5px;
        font-weight: 800;
        color: #000;
        letter-spacing: 0.3px;
    }

    .footer-tagline {
        font-size: 11px;
        font-weight: 700;
        font-style: italic;
        color: #f39200;
        margin-top: 2px;
        letter-spacing: 0.4px;
    }

    .footer-services {
        font-size: 10.5px;
        font-weight: 600;
        color: #334155;
        letter-spacing: 0.8px;
        margin-top: 2px;
        text-transform: uppercase;
    }

    .system-remark {
        font-size: 9.5px;
        color: maroon;
        font-style: italic;
        margin-top: 2px;
        display: block;
        text-align: right;
    }


    /* ── Report Title Banner ── */
    .report-header-box {
        text-align: center;
        margin-top: 4px;
        margin-bottom: 12px;
    }

    .report-main-title {
        font-size: 20px;
        font-weight: 800;
        margin-top: 0;
        color: #000;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-decoration: underline;
    }

    .report-sub-title {
        font-size: 12px;
        font-weight: bold;
        color: #333;
        margin-top: 5px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* ── Institutional Details Box (Progress Report Style) ── */
    .details-container {
        padding: 4px 6px;
        margin-bottom: 14px;
        background: transparent;
    }

    .details-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .details-table td {
        padding: 3px 0;
        vertical-align: top;
        color: #000;
    }

    .label {
        font-weight: bold;
        color: #000;
        width: 24%;
    }

    .val {
        color: #000;
        width: 76%;
    }

    /* ── Master Evaluation Table (Strict 100% Width & Auto-Wrapping) ── */
    .eval-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        font-size: {{ $tableFontSize }};
        margin-bottom: 14px;
        background: transparent;
        page-break-inside: auto;
    }

    .eval-table th {
        background: transparent;
        color: #000;
        font-size: {{ $thFontSize }};
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.01em;
        padding: {{ $cellPadding }};
        border: 1px solid #000;
        text-align: center;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.15;
    }

    .eval-table td {
        padding: {{ $cellPadding }};
        border: 1px solid #000;
        vertical-align: middle;
        font-size: {{ $tableFontSize }};
        font-weight: normal;
        color: #000;
        background: transparent;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.35;
    }

    .eval-table tr {
        page-break-inside: avoid;
    }


    .text-center {
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .font-bold {
        font-weight: bold;
    }

    /* ── Summary & Remarks Evaluation Boxes ── */
    .eval-box {
        background: transparent;
        padding: 2px 0;
        margin-top: 10px;
        margin-bottom: 14px;
        page-break-inside: auto;
    }

    .eval-title {
        font-size: 14px;
        font-weight: bold;
        color: #000;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 4px;
        page-break-after: avoid;
        page-break-inside: avoid;
    }

    .eval-content {
        font-size: 13px;
        color: #000;
        line-height: 1.45;
        white-space: pre-line;
    }

    /* ── Main Content Container ── */
    .main-content {
        position: relative;
        padding-bottom: 40px;
    }

    /* ── Signature Section (Fixed above Footer) ── */
    .signature-container {
        position: absolute;
        bottom: 40px;
        left: 0;
        right: 0;
        width: 100%;
        page-break-inside: avoid;
    }

    .mentor-name-row {
        font-size: 14px;
        font-weight: bold;
        color: #000;
        margin-bottom: 35px;
    }
</style>

</head>
<body>

@php
    $logoSrc = ($logoBase64 ?? null) ?: (file_exists(public_path('images/TsLogo.png')) ? public_path('images/TsLogo.png') : null);
    $watermarkSrc = $watermarkBase64 ?? (file_exists(public_path('images/TsLogo_watermark_blurred.png')) ? public_path('images/TsLogo_watermark_blurred.png') : $logoSrc);

    $collegeTitle = $evaluation->college?->name ?? $evaluation->college_name;
    $univTitle = $evaluation->college?->university_name ?? $evaluation->university_name;

    $attMaxFormatted = rtrim(rtrim(number_format((float)$evaluation->max_attendance_marks, 2), '0'), '.');
    $taskMaxFormatted = rtrim(rtrim(number_format((float)$evaluation->max_task_marks, 2), '0'), '.');
    $totalMaxFormatted = rtrim(rtrim(number_format((float)$evaluation->total_marks, 2), '0'), '.');
@endphp


{{-- ── Background Watermark (Blurred & Soft Faint) ── --}}
@if ($watermarkSrc)
    <div class="watermark">
        <img src="{{ $watermarkSrc }}" alt="Techstrota Watermark">
    </div>
@endif

{{-- ── Header (Same to same Offer Letter & Progress Report) ── --}}
<header>
    <table class="header-table">
        <tr>
            <td class="header-contact" style="text-align: left; font-size: 11px; font-weight: bold; vertical-align: bottom;">Email: info@techstrota.com</td>
            <td style="text-align: center;">
                @if ($logoSrc)
                    <img src="{{ $logoSrc }}" class="header-logo" alt="Techstrota Logo">
                @else
                    <strong style="font-size: 16px; color: #f39200;">TECHSTROTA</strong>
                @endif
            </td>
            <td class="header-contact" style="text-align: right; font-size: 11px; font-weight: bold; vertical-align: bottom;">Tel: +91 81288 40055</td>
        </tr>
    </table>
</header>

{{-- ── Footer ── --}}
<footer>
    <div class="footer-brand">
        <span style="color: #000;">Techstrota</span> 
        <span style="color: #f39200; margin: 0 4px;">|</span> 
        <span style="color: #1e40af;">www.techstrota.com</span>
    </div>
    <div class="footer-tagline">
        &ldquo;You think, we Build.&rdquo;
    </div>
    <div class="footer-services">
        Websites &bull; Mobile Apps &bull; Software &bull; Digital Marketing
    </div>
    <span class="system-remark">This is a system-generated document.</span>
</footer>

{{-- ── Main Content ── --}}
<main class="main-content">

    {{-- Title Banner --}}
    <div class="report-header-box">
        <div class="report-main-title">Internship Performance &amp; Evaluation Report</div>
    </div>

    {{-- Institutional Details Section (Progress Report Clean Key-Value Structure) ── --}}
    <div class="details-container">
        <table class="details-table">
            <tr>
                <td class="label">College / Institution:</td>
                <td class="val" style="font-weight: bold; font-size: 14px;">{{ $collegeTitle }}</td>
            </tr>
            <tr>
                <td class="label">Affiliated University:</td>
                <td class="val">{{ $univTitle ?: 'Autonomous / Direct Campus' }}</td>
            </tr>
            <tr>
                <td class="label">Course / Degree:</td>
                <td class="val">{{ $evaluation->course_degree ?: 'Information Technology / Computer Engineering' }} &bull; Year: {{ $evaluation->academic_year ?: '2025–2026' }}</td>
            </tr>
            @if ($evaluation->evaluation_period_start || $evaluation->evaluation_period_end)
            <tr>
                <td class="label">Evaluation Period:</td>
                <td class="val">
                    {{ $evaluation->evaluation_period_start ? $evaluation->evaluation_period_start->format('d/m/Y') : 'Start' }} to {{ $evaluation->evaluation_period_end ? $evaluation->evaluation_period_end->format('d/m/Y') : 'Completion' }}
                    &bull; Date of Evaluation: {{ $evaluation->evaluation_date ? $evaluation->evaluation_date->format('d/m/Y') : now()->format('d/m/Y') }}
                </td>
            </tr>
            @else
            <tr>
                <td class="label">Date of Evaluation:</td>
                <td class="val">{{ $evaluation->evaluation_date ? $evaluation->evaluation_date->format('d/m/Y') : now()->format('d/m/Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Master Evaluation Marks Table (Progress Report Style) ── --}}
    <table class="eval-table">
        <thead>
            <tr>
                @foreach ($activeColumns as $col)
                    @php
                        $w = $colWidths[$col['key']] ?? round(100 / max(1, $colCount), 2);
                        $align = $col['align'] ?? 'center';
                    @endphp
                    <th style="width: {{ $w }}%; text-align: {{ $align }};">
                        {{ $col['label'] }}
                        @if (!empty($col['sub_label']))
                            <br><span style="font-size: {{ $subFontSize }}; font-weight: normal;">{{ $col['sub_label'] }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse ($evaluationInterns as $index => $item)
                <tr>
                    @foreach ($activeColumns as $col)
                        @php
                            $cType = $col['type'] ?? 'custom_text';
                            $align = $col['align'] ?? 'center';
                        @endphp
                        <td style="text-align: {{ $align }};">
                            @if ($cType === 'sr_no')
                                {{ $index + 1 }}
                            @elseif ($cType === 'student_id')
                                {{ $item->student_id }}
                            @elseif ($cType === 'student_name')
                                {{ $item->student_name }}
                            @elseif ($cType === 'grade')
                                {{ strtoupper($item->grade ?: 'PASS') }}
                            @elseif (in_array($cType, ['direct_marks', 'total_marks', 'marks']))
                                {{ rtrim(rtrim(number_format((float)$item->total_obtained_marks, 2), '0'), '.') }}
                            @elseif ($cType === 'criteria_attendance')
                                {{ rtrim(rtrim(number_format((float)$item->attendance_marks, 2), '0'), '.') }}
                            @elseif ($cType === 'criteria_task')
                                {{ rtrim(rtrim(number_format((float)$item->task_marks, 2), '0'), '.') }}
                            @elseif ($cType === 'custom_criterion')
                                @php
                                    $critMark = $item->getCustomMarkByName($col['name'] ?? '');
                                @endphp
                                {{ $critMark !== null ? rtrim(rtrim(number_format((float)$critMark, 2), '0'), '.') : '0' }}
                            @else
                                {{ $item->getCellValue($col['key'], $col) }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($activeColumns) }}" class="text-center" style="padding: 14px;">
                        No students evaluated for this batch report.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Methodology & Rubrics Notes ── --}}
    <div class="eval-box">
        <div class="eval-title">Evaluation Methodology &amp; Marking Instructions</div>
        <div class="eval-content">{{ $evaluation->instruction_notes ?: $evaluation->generateDefaultInstructions() }}</div>
    </div>

    {{-- Signatures Section (Matching Progress Report Exactly) ── --}}
    <div class="signature-container">
        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
            <tr>
                <td style="width: 50%; text-align: left; vertical-align: bottom; border: none !important; padding: 0;">
                    <table style="border-collapse: collapse; border: none; margin: 0; padding: 0;">
                        <tr>
                            <td style="font-size: 13px; font-weight: bold; color: #000; padding: 0 6px 0 0; vertical-align: bottom; border: none !important; white-space: nowrap;">Signature:</td>
                            <td style="font-size: 13px; color: #000; vertical-align: bottom; border: none !important; white-space: nowrap;">________________________</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; text-align: right; vertical-align: bottom; border: none !important; padding: 0;">
                    <table style="margin-left: auto; border-collapse: collapse; border: none; margin-right: 0; padding: 0;">
                        <tr>
                            <td style="font-size: 13px; font-weight: bold; color: #000; padding: 0 6px 0 0; vertical-align: bottom; border: none !important; white-space: nowrap;">Date:</td>
                            <td style="font-size: 13px; color: #000; vertical-align: bottom; border: none !important; white-space: nowrap;">________________________</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</main>

</body>
</html>
