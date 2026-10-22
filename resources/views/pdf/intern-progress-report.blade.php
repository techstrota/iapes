<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $report->title }} - Weekly Progress Report</title>
<style>
    @page {
        margin: 92px 12mm 72px 12mm;
    }

    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 8.5px;
        line-height: 1.25;
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
        width: 600px;
        height: auto;
    }

    /* ── Header (Same to same Offer Letter) ── */
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
        font-size: 11px;
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
        font-size: 11px;
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
        font-size: 10px;
        font-weight: 600;
        color: #334155;
        letter-spacing: 0.8px;
        margin-top: 2px;
        text-transform: uppercase;
    }

    .system-remark {
        font-size: 9px;
        color: maroon;
        font-style: italic;
        margin-top: 2px;
        display: block;
        text-align: right;
    }

    /* ── Main Content Container ── */
    .main-content {
        position: relative;
    }

    /* ── Report Title Banner ── */
    .report-header-box {
        text-align: center;
        margin-top: 6px;
        margin-bottom: 4px;
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
        font-size: 10px;
        font-weight: bold;
        color: #333;
        margin-top: 6px;
        margin-bottom: 14px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    /* ── Internship Details Box (Single Part / 1 Column List, No Background Color) ── */
    .details-container {
        padding: 4px 6px;
        margin-bottom: 14px;
        background: transparent;
    }

    /* ── Weekly Report Overview Narrative ── */
    .weekly-intro-box {
        margin-top: 6px;
        margin-bottom: 14px;
        font-size: 13.5px;
        color: #000;
        line-height: 1.5;
        text-align: justify;
    }
    .weekly-intro-box p {
        margin: 0;
    }

    .details-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .details-table td {
        padding: 1.2px 0;
        vertical-align: top;
        color: #000;
    }

    .label {
        font-weight: bold;
        color: #000;
        width: 25%;
    }

    .val {
        color: #000;
        width: 75%;
    }

    /* ── Section Title ── */
    .section-title {
        font-size: 14px;
        font-weight: 800;
        color: #000;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-top: 14px;
        margin-bottom: 5px;
        page-break-after: avoid;
        page-break-inside: avoid;
    }

    /* ── Simple Logbook Table (Clean, No Colors, Empty Sign Column) ── */
    .log-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-bottom: 14px;
        background: transparent;
        page-break-inside: auto;
    }

    .log-table td.module-topic-cell {
        padding: 2px 3px;
        vertical-align: middle;
        line-height: 1.15;
        white-space: pre-line;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .log-table th {
        background: transparent;
        color: #000;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 3px 3px;
        border: 1px solid #000;
        text-align: center;
    }

    .log-table td {
        padding: 2.5px 3px;
        border: 1px solid #000;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.15;
        color: #000;
        background: transparent;
    }

    .log-table tr {
        page-break-inside: avoid;
    }

    .eval-tick {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 15px;
        font-weight: bold;
        color: #000;
        display: inline-block;
        line-height: 1;
    }

    /* ── Summary & Remarks Evaluation Boxes (Simple, No Colored Background) ── */
    .eval-box {
        background: transparent;
        padding: 2px 0;
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
        font-size: 13.5px;
        color: #000;
        line-height: 1.35;
        white-space: pre-line;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* ── Signature Section (Left Side Only) ── */
    .signature-container {
        margin-top: 14px;
        text-align: left;
        page-break-inside: avoid;
    }

    .mentor-name-row {
        font-size: 14px;
        color: #000;
        margin-bottom: 35px;
    }

    @if ($report->isMonthly())
    .report-header-box {
        margin-top: 4px;
        margin-bottom: 3px;
    }
    .report-sub-title {
        margin-top: 4px;
        margin-bottom: 8px;
    }
    .details-container {
        padding: 2px 4px;
        margin-bottom: 8px;
    }
    .details-table {
        font-size: 13px;
    }
    .details-table td {
        padding: 1px 0;
    }
    .section-title {
        font-size: 13.5px;
        margin-top: 8px;
        margin-bottom: 4px;
        page-break-after: avoid;
        page-break-inside: avoid;
    }
    .log-table {
        font-size: 13px;
        margin-bottom: 8px;
        page-break-inside: auto;
    }
    .log-table th {
        font-size: 12.5px;
        padding: 4px 6px;
    }
    .log-table td {
        font-size: 12.5px;
        padding: 3.5px 6px;
    }
    .eval-box {
        padding: 2px 0;
        margin-top: 8px;
        margin-bottom: 8px;
        page-break-inside: auto;
    }
    .eval-title {
        font-size: 13px;
        margin-bottom: 3px;
        page-break-after: avoid;
        page-break-inside: avoid;
    }
    .eval-content {
        font-size: 12.5px;
        line-height: 1.35;
    }
    .signature-container {
        margin-top: 14px;
        page-break-inside: avoid;
    }
    .mentor-name-row {
        font-size: 13px;
        margin-bottom: 25px;
    }
    .sig-label {
        font-size: 13px;
    }
    @endif

    .sig-table {
        border-collapse: collapse;
        border: none;
        margin: 0;
    }

    .sig-table td {
        border: none !important;
        padding: 0;
        vertical-align: bottom;
        background: transparent !important;
    }

    .sig-label {
        font-size: 14px;
        font-weight: bold;
        color: #000;
        padding-right: 8px !important;
        white-space: nowrap;
    }

    .sig-line-beside {
        border-bottom: 1px solid #000 !important;
        width: 220px;
        height: 14px;
    }

    .page-break {
        page-break-after: always;
    }
</style>
</head>
<body>

@php
    $logoSrc = ($logoBase64 ?? null) ?: (file_exists(public_path('images/TsLogo.png')) ? public_path('images/TsLogo.png') : null);
    $watermarkSrc = $watermarkBase64 ?? (file_exists(public_path('images/TsLogo_watermark_blurred.png')) ? public_path('images/TsLogo_watermark_blurred.png') : $logoSrc);
@endphp

{{-- ── Background Watermark of Techstrota Logo (Blurred & Soft Faint) ── --}}
@if ($watermarkSrc)
    <div class="watermark">
        <img src="{{ $watermarkSrc }}" alt="Techstrota Watermark">
    </div>
@endif

{{-- ── Header (Same to same Offer Letter) ── --}}
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

{{-- ── Main Content: One Page Per Intern ── --}}
<main class="main-content">
@foreach ($reportInterns as $index => $repIntern)
    @php
        $intern = $repIntern->intern;
        $dailyLogs = $repIntern->daily_logbooks;

        // Intern metadata
        $internName = $intern?->name ?: ($intern?->offerletter?->name ?? 'Intern');
        $internDisplayName = filled($internName) ? ucwords(strtolower($internName)) : 'the intern';
        $internCode = $intern?->intern_code ?: ('INT-' . str_pad($intern?->id ?? 0, 3, '0', STR_PAD_LEFT));
        $collegeName = $intern?->college ?: ($intern?->university ?: ($intern?->offerletter?->college ?? 'N/A'));
        $internshipDuration = $intern?->offerletter?->duration_label ?? ($intern?->batch?->duration ?? '3 Months');
        $internshipType = $intern?->offerletter?->internship_role ?? ($intern?->internship_role ?? ($intern?->domain ?? 'Web Development'));
        $internRole = $intern?->internship_role ?: ($intern?->offerletter?->internship_role ?: ($intern?->domain ?: ($internshipType !== 'On-site' ? $internshipType : 'Full Stack Development')));
        $departmentCourse = $intern?->degree ?: ($intern?->offerletter?->degree ?: ($intern?->internship_role ?: ($intern?->domain ?: 'Information Technology')));

        // Mentor name fetched from Project Team (intern_team table mentor_name field)
        $mentorName = $intern?->team?->mentor_name
            ?: ($report->team?->mentor_name
            ?: ($repIntern->evaluator?->name
            ?: ($report->creator?->name ?: 'Badal Jamod')));

        // Internship Period
        if ($intern?->joining_date && $intern?->completion_date) {
            $internshipPeriod = \Carbon\Carbon::parse($intern->joining_date)->format('d/m/Y') . ' to ' . \Carbon\Carbon::parse($intern->completion_date)->format('d/m/Y');
        } elseif ($intern?->offerletter?->joining_date && $intern?->offerletter?->completion_date) {
            $internshipPeriod = \Carbon\Carbon::parse($intern->offerletter->joining_date)->format('d/m/Y') . ' to ' . \Carbon\Carbon::parse($intern->offerletter->completion_date)->format('d/m/Y');
        } else {
            $internshipPeriod = $report->formatted_date_range;
        }

        // Date iteration across report duration
        $weekStart = \Carbon\Carbon::parse($report->week_start_date);
        $weekEnd   = \Carbon\Carbon::parse($report->week_end_date);

        $internAttendances = \App\Models\Attendance::where('intern_id', $intern?->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->keyBy(fn ($a) => \Carbon\Carbon::parse($a->date)->toDateString());

        $globalHolidays = \App\Models\Attendance::whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->where('status', 'holiday')
            ->where(function ($q) use ($intern) {
                $q->where('intern_id', $intern?->id)
                  ->orWhereNull('intern_id');
            })
            ->get()
            ->keyBy(fn ($a) => \Carbon\Carbon::parse($a->date)->toDateString());

        $curr = $weekStart->copy();
        $dates = [];
        while ($curr->lte($weekEnd)) {
            $dates[] = $curr->copy();
            $curr->addDay();
        }

        // Resolve Batch & Scheduled Start/End Timing
        $batch = $intern?->batch ?? $report->batch;
        $batchStartTime = '11:00 AM';
        $batchEndTime   = '04:00 PM';

        if ($batch && filled($batch->batch_timing)) {
            $timingParts = preg_split('/\s+(?:to|-)\s+/i', trim($batch->batch_timing));
            if (count($timingParts) >= 2) {
                try {
                    $batchStartTime = \Carbon\Carbon::parse(trim($timingParts[0]))->format('g:i A');
                    $batchEndTime   = \Carbon\Carbon::parse(trim($timingParts[1]))->format('g:i A');
                } catch (\Throwable $e) {
                    $batchStartTime = trim($timingParts[0]);
                    $batchEndTime   = trim($timingParts[1]);
                }
            }
        }
    @endphp

    <div>
        {{-- ── 1. Report Title Section ── --}}
        <div class="report-header-box">
            <div class="report-main-title">{{ strtoupper($report->title) }}</div>
            @if ($report->isMonthly())
                <div class="report-sub-title">MONTHLY / FINAL INTERNSHIP EVALUATION & REVIEW REPORT &bull; EVALUATION PERIOD: {{ $report->formatted_date_range }}</div>
            @else
                <div class="report-sub-title">WEEKLY INTERNSHIP PROGRESS EVALUATION REPORT &bull; EVALUATION PERIOD: {{ $report->formatted_date_range }}</div>
            @endif
        </div>

        @if ($report->isMonthly())
            {{-- ── 2. Internship Details Box (Monthly Evaluation Review Mode) ── --}}
            <div class="details-container">
                <table class="details-table">
                    <tr>
                        <td class="label">1) Company Name:</td>
                        <td class="val">Techstrota</td>
                    </tr>
                    <tr>
                        <td class="label">2) Internship Duration:</td>
                        <td class="val">{{ $internshipDuration }} (Working Days)</td>
                    </tr>
                    <tr>
                        <td class="label">3) Internship Type:</td>
                        <td class="val">{{ $internshipType }}</td>
                    </tr>
                    <tr>
                        <td class="label">4) Intern's Name:</td>
                        <td class="val"><strong>{{ strtoupper($internName) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">5) College Name:</td>
                        <td class="val">{{ $collegeName }}</td>
                    </tr>
                    <tr>
                        <td class="label">6) Internship ID:</td>
                        <td class="val" style="font-weight: bold;">{{ $internCode }}</td>
                    </tr>
                    <tr>
                        <td class="label">7) Department/ Course:</td>
                        <td class="val">{{ $departmentCourse }}</td>
                    </tr>
                    <tr>
                        <td class="label">8) Internship Period:</td>
                        <td class="val">{{ $internshipPeriod }}</td>
                    </tr>
                </table>
            </div>
        @else
            {{-- ── 2. Weekly Report Overview Narrative (Replaces numbered details table in weekly mode) ── --}}
            <div class="weekly-intro-box">
                <p>This weekly report presents the work and learning progress of <strong>{{ $internDisplayName }}</strong>, a <strong>{{ $internRole }}</strong> intern at TechStrota, during the reporting period from <strong>{{ $weekStart->format('d F Y') }}</strong> to <strong>{{ $weekEnd->format('d F Y') }}</strong>. The intern, registered under ID <strong>{{ $internCode }}</strong> and enrolled in <strong>{{ $departmentCourse }}</strong> at <strong>{{ $collegeName }}</strong>, has been assigned tasks related to the internship program. This report documents the activities completed, skills developed, challenges encountered, and objectives for the upcoming week.</p>
            </div>
        @endif

        @if ($report->isMonthly())
            {{-- ── 3. Performance Evaluation Criteria & Rating Matrix (Monthly Mode - Matching Physical Review Sheet) ── --}}
            <div class="section-title">
                Final Internship Review
            </div>

            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width: 30%; text-align: left; padding-left: 6px;">Evaluation Criteria</th>
                        <th style="width: 13%; text-align: center;">Excellent</th>
                        <th style="width: 13%; text-align: center;">Good</th>
                        <th style="width: 13%; text-align: center;">Average</th>
                        <th style="width: 31%; text-align: left; padding-left: 6px;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $criteriaList = $report->evaluation_criteria ?: \App\Models\ReportManagement\ProgressReport::getDefaultEvaluationCriteria();
                        $ratingsData = $repIntern->criteria_ratings ?? [];
                    @endphp
                    @foreach ($criteriaList as $cIdx => $crit)
                        @php
                            $cKey = $crit['id'] ?? ('crit_' . $cIdx);
                            $evalRow = $ratingsData[$cKey] ?? [];
                            $rateVal = strtolower(trim($evalRow['rating'] ?? 'Good'));
                            $rateRemarks = $evalRow['remarks'] ?? '';

                            $isExcellent = ($rateVal === 'excellent');
                            $isGood = ($rateVal === 'good');
                            $isAverage = in_array($rateVal, ['average', 'needs improvement', 'satisfactory', 'fair', 'poor']);
                        @endphp
                        <tr>
                            <td style="font-weight: bold; padding-left: 6px;">
                                {{ $crit['name'] ?? ucfirst(str_replace('_', ' ', $cKey)) }}
                            </td>
                            <td style="text-align: center;">
                                @if ($isExcellent)
                                    <span class="eval-tick">&#10003;</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($isGood)
                                    <span class="eval-tick">&#10003;</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($isAverage)
                                    <span class="eval-tick">&#10003;</span>
                                @endif
                            </td>
                            <td style="padding-left: 6px; font-size: 13px;">
                                {{ $rateRemarks ?: '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- ── 4. Final Comments by Mentor (Matching Physical Sheet) ── --}}
            <div class="eval-box" style="margin-top: 10px; margin-bottom: 12px; page-break-inside: avoid;">
                <div class="eval-title">Final Comments by Mentor:</div>
                <div class="eval-content" style="white-space: pre-line; line-height: 1.35;">{{ $repIntern->mentor_final_comments ?: ($repIntern->supervisor_remarks ?: 'Candidate demonstrated steady performance and met expectations.') }}</div>
            </div>
        @else
            {{-- ── 3. Daily Logbook Entries Section (Simple Table, No Heavy Colors, Empty Sign Column) ── --}}
            <div class="section-title">
                Daily Logbook Entries
            </div>

            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width: 7%;">Day</th>
                        <th style="width: 11%;">Date</th>
                        <th style="width: 44%;">Module/Topic</th>
                        <th style="width: 14%;">Nature of Work</th>
                        <th style="width: 8%;">Entry time</th>
                        <th style="width: 8%;">Exit time</th>
                        <th style="width: 8%;">Supervisor's sign</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dates as $dayIdx => $d)
                        @php
                            $dStr = $d->toDateString();
                            $att = $internAttendances->get($dStr);
                            $hol = $globalHolidays->get($dStr);
                            $log = $dailyLogs->first(fn ($l) => \Carbon\Carbon::parse($l->date)->toDateString() === $dStr);

                            $status = 'unmarked';
                            $holidayNote = null;
                            if ($att) {
                                $status = strtolower($att->status);
                                $holidayNote = $att->note;
                            } elseif ($hol) {
                                $status = 'holiday';
                                $holidayNote = $hol->note;
                            } elseif ($d->isSunday()) {
                                $status = 'week_off';
                            }

                            // When marked absent, strictly display Absent regardless of whether a daily log was filled
                            if ($status === 'absent') {
                                $moduleTopic  = 'Absent';
                                $natureOfWork = 'Absent';
                                $entryTime    = '—';
                                $exitTime     = '—';
                            } elseif ($log) {
                                $tasksText = filled($log->tasks_completed) ? trim(strip_tags((string) $log->tasks_completed)) : '';
                                $titleText = (filled($log->title) && trim($log->title) !== 'Tasks & Development') ? trim($log->title) : '';

                                if ($tasksText !== '' && $titleText !== '') {
                                    $moduleTopic = $titleText . "\n" . $tasksText;
                                } elseif ($tasksText !== '') {
                                    $moduleTopic = $tasksText;
                                } elseif ($titleText !== '') {
                                    $moduleTopic = $titleText;
                                } else {
                                    $moduleTopic = 'Log entry submitted';
                                }

                                $moduleTopic = trim($moduleTopic);

                                if ($status === 'late') {
                                    $dur = ($att && $att->late_minutes > 0) ? " (Late by {$att->formatted_late_duration})" : '';
                                    $rem = ($att && $att->note) ? " — {$att->note}" : '';
                                    $moduleTopic .= "\n• [Late Arrival{$dur}{$rem}]";
                                }

                                // Nature of Work (Good, supervisor remarks, etc.)
                                if (filled($log->supervisor_remarks)) {
                                    $natureOfWork = $log->supervisor_remarks;
                                } elseif ($status === 'late') {
                                    $natureOfWork = 'Late' . (($att && $att->late_minutes > 0) ? ' (' . $att->formatted_late_duration . ')' : '');
                                } else {
                                    $natureOfWork = 'Good';
                                }

                                // Entry & Exit Time fetched from Attendance (if recorded) or Batch Schedule
                                $entryTime = ($att && filled($att->start_time)) ? $att->start_time : $batchStartTime;
                                $exitTime  = ($att && filled($att->end_time)) ? $att->end_time : $batchEndTime;
                            } else {
                                if ($status === 'holiday') {
                                    $moduleTopic  = 'Holiday' . ($holidayNote ? " ({$holidayNote})" : '');
                                    $natureOfWork = 'Holiday';
                                } elseif ($status === 'week_off') {
                                    $moduleTopic  = 'Scheduled Sunday Week-Off';
                                    $natureOfWork = 'Week-Off';
                                } elseif ($status === 'leave') {
                                    $moduleTopic  = 'Intern on Approved Leave' . ($holidayNote ? " ({$holidayNote})" : '');
                                    $natureOfWork = 'Leave';
                                } elseif ($status === 'late') {
                                    $dur = ($att && $att->late_minutes > 0) ? " (Late by {$att->formatted_late_duration})" : '';
                                    $moduleTopic  = 'Late Arrival' . $dur . ($att?->note ? " — {$att->note}" : '');
                                    $natureOfWork = 'Late' . (($att && $att->late_minutes > 0) ? ' (' . $att->formatted_late_duration . ')' : '');
                                } else {
                                    $moduleTopic  = 'No log entry submitted';
                                    $natureOfWork = 'Pending';
                                }

                                $isExplicitNonWorking = in_array($status, ['holiday', 'week_off', 'leave']);
                                if ($isExplicitNonWorking) {
                                    $entryTime = '—';
                                    $exitTime  = '—';
                                } else {
                                    $entryTime = ($att && filled($att->start_time)) ? $att->start_time : $batchStartTime;
                                    $exitTime  = ($att && filled($att->end_time)) ? $att->end_time : $batchEndTime;
                                }
                            }
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold;">
                                Day {{ $dayIdx + 1 }}
                            </td>
                            <td style="text-align: center;">
                                {{ $d->format('d/m/Y') }}
                            </td>
                            <td class="module-topic-cell">{{ trim($moduleTopic) }}</td>
                            <td style="text-align: center;">
                                {{ $natureOfWork }}
                            </td>
                            <td style="text-align: center;">
                                {{ $entryTime }}
                            </td>
                            <td style="text-align: center;">
                                {{ $exitTime }}
                            </td>
                            {{-- Supervisor's sign cell remains empty as requested --}}
                            <td style="text-align: center;">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- ── 4. Summary of Work Done Section ── --}}
            <div class="eval-box">
                <div class="eval-title">Summary of work done</div>
                <div class="eval-content">{{ $repIntern->weekly_summary ?: 'Weekly summary not provided.' }}</div>
            </div>

            {{-- ── 5. Mentor's Remarks Section ── --}}
            <div class="eval-box">
                <div class="eval-title">Mentor's remarks</div>
                <div class="eval-content">{{ $repIntern->supervisor_remarks ?: 'Mentor remarks not recorded.' }}</div>
            </div>
        @endif

        {{-- ── 6. Mentor Name, Signature & Date Section (Generous Space for Signing) ── --}}
        <div class="signature-container">
            <div class="mentor-name-row" style="margin-bottom: 40px;">
                <strong>Mentor Name:</strong> {{ $mentorName }}
            </div>
            <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 50%; text-align: left; vertical-align: bottom; border: none !important; padding: 0;">
                        <table style="border-collapse: collapse; border: none; margin: 0; padding: 0;">
                            <tr>
                                <td style="font-size: 14px; font-weight: bold; color: #000; padding: 0 6px 0 0; vertical-align: bottom; border: none !important; white-space: nowrap;">Signature:</td>
                                <td style="font-size: 14px; color: #000; vertical-align: bottom; border: none !important; white-space: nowrap;">________________________</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; text-align: right; vertical-align: bottom; border: none !important; padding: 0;">
                        <table style="margin-left: auto; border-collapse: collapse; border: none; margin-right: 0; padding: 0;">
                            <tr>
                                <td style="font-size: 14px; font-weight: bold; color: #000; padding: 0 6px 0 0; vertical-align: bottom; border: none !important; white-space: nowrap;">Date:</td>
                                <td style="font-size: 14px; color: #000; vertical-align: bottom; border: none !important; white-space: nowrap;">________________________</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if (! $loop->last && ! ($isSingle ?? false))
        <div class="page-break"></div>
    @endif
@endforeach
</main>

</body>
</html>
