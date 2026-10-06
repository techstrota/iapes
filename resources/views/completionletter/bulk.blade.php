<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        @page {
            margin: 0;
            size: A4;
        }

        body {
            font-family: 'Times New Roman', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.5;
            background: #a4a4a4;
        }

        .page-wrapper {
            position: relative;
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 10mm 20mm;
            box-sizing: border-box;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        header,
        main,
        footer {
            position: relative;
            z-index: 10;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            z-index: 0;
            opacity: 0.25;
            pointer-events: none;
        }

        /* Header Styles */
        .header {
            position: relative;
            margin-bottom: 30px;
            padding-bottom: 5px;
            border-bottom: 2pt solid #f39200;
            /* Orange Line */
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }

        .header-item {
            display: table-cell;
            vertical-align: bottom;
            font-size: 13px;
            font-weight: 600;
        }

        .header-email {
            text-align: left;
            width: 35%;
        }

        .header-logo {
            text-align: center;
            width: 30%;
        }

        .header-tel {
            text-align: right;
            width: 35%;
        }

        .header-logo img {
            height: 55px;
            display: inline-block;
        }

        /* Main Content Styles */
        .content {
            padding: 10px 0;
            flex-grow: 1;
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            text-decoration: underline;
            margin: 10px 0 30px 0;
            text-transform: uppercase;
        }

        .meta-row {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            font-size: 15px;
        }

        .meta-left {
            display: table-cell;
            width: 60%;
            line-height: 1.4;
        }

        .meta-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }

        p,
        .content-p {
            font-size: 15.5px;
            margin-bottom: 18px;
            text-align: justify;
        }

        .skills-title {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 15.5px;
        }

        .skills-list {
            margin-left: 0;
            margin-bottom: 30px;
        }

        .skills-list ul {
            margin-left: 35px;
            padding-left: 0;
        }

        .skills-list li {
            margin-bottom: 5px;
            font-size: 15px;
        }

        /* Signature Styles */
        .sig-container {
            margin-top: 40px;
            width: 250px;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-bottom: 8px;
            width: 100%;
        }

        .sig-name {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 2px;
        }

        .sig-title {
            font-size: 14px;
            font-weight: 500;
        }

        /* Footer Styles */
        .footer {
            margin-top: auto;
            text-align: center;
            padding-top: 20px;
        }

        .footer-line {
            border-top: 2pt solid #f39200;
            /* Orange Line */
            margin-bottom: 15px;
        }

        .footer-content {
            font-size: 13px;
            color: #000;
            line-height: 1.4;
        }

        .footer-brand {
            font-weight: 700;
            color: #000;
            font-size: 14px;
        }

        .footer-link {
            color: #2808f8;
            text-decoration: none;
        }

        .system-remark {
            font-size: 10px;
            color: maroon;
            font-style: italic;
            margin-top: 10px;
            display: block;
            text-align: right;
        }

        @media print {
            .page-wrapper {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    @foreach($interns as $intern)
        @php
            $internName = $intern->name ?: ($intern->offer_letters->name ?? $intern->application->name ?? 'Intern');
            $internUniversity = $intern->university ?: ($intern->offer_letters->university ?? '');
            $internCollege = $intern->college ?: ($intern->offer_letters->college ?? $intern->application->college ?? '');
            $internDegree = $intern->degree ?: ($intern->offerLetter->degree ?? $intern->application->degree ?? '');
            $uni = $internCollege ?: $internUniversity;
            $startDate = \Carbon\Carbon::parse($intern->joining_date ?: ($intern->offer_letters?->joining_date ?? now()));
            $endDate = \Carbon\Carbon::parse($intern->completion_date ?: ($intern->offer_letters?->completion_date ?? now()));
            $internRole = $intern->internship_role ?: ($intern->offer_letters?->internship_role ?? 'Software Development');

            // Count working days (excluding Sundays)
            $workingDays = 0;
            $tempDate = $startDate->copy();
            while ($tempDate <= $endDate) {
                if ($tempDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
                    $workingDays++;
                }
                $tempDate->addDay();
            }

            // One month or less check (calendar days <= 31)
            $calendarDays = $startDate->diffInDays($endDate) + 1;
            $isShortTerm = ($calendarDays <= 31);

            $workingHoursPerDay = 5;
            $totalHours = $workingDays * $workingHoursPerDay;
        @endphp
        <div class="page-wrapper">
            <img src="{{ $logo }}" class="watermark" alt="Watermark">
            <div class="header">
                <div class="header-top">
                    <div class="header-item header-email">Email: info@techstrota.com</div>
                    <div class="header-item header-logo">
                        <img src="{{ $logo }}" alt="Techstrota">
                    </div>
                    <div class="header-item header-tel">Tel: +91 81288 40055</div>
                </div>
            </div>

            <div class="content">
                <div class="title">INTERNSHIP COMPLETION LETTER</div>

                <div class="meta-row">
                    <div class="meta-left">
                        <strong>From: Techstrota</strong><br>
                        <strong>Issued on: {{ \Carbon\Carbon::parse($intern->issuing_date)->format('d/m/Y') }}</strong>
                    </div>
                    <div class="meta-right">
                        <strong>Certificate ID: {{ $intern->intern_code }}</strong>
                    </div>
                </div>

                <div class="content-p">
                    This is to certify that <strong>{{ $internName }}</strong>@if($internCollege || $internUniversity), a student of
                    <strong>{{ $internDegree }}</strong>,@endif has successfully completed 
                    @if($isShortTerm)
                        the <strong>{{ $workingDays }} Days ({{ $totalHours }} Hours)</strong> internship with Grade <strong>{{ $intern->grade ?? 'A' }}</strong>.
                    @else
                        the internship.
                    @endif
                    The internship was carried out for the course titled
                    <strong>“{{ $internRole }}”</strong>, conducted by
                    <strong>Techstrota</strong>@if($internCollege || $internUniversity) and facilitated by
                        <strong>{{ $uni }}</strong>@endif.
                    The internship duration was from <strong>{{ $startDate->format('d/m/Y') }}</strong> to
                    <strong>{{ $endDate->format('d/m/Y') }}</strong> at Techstrota. 503, Sterling Centre, R C Dutt Road,
                    Near Fairfield Hotel, Alkapuri, Vadodara, Gujarat - 390007
                </div>

                @if($intern->project_description)
                    <div class="skills-list">
                        {!! $intern->project_description !!}
                    </div>
                @endif
            </div>

            <div class="sig-container">
                <div class="sig-line"></div>
                <div class="sig-name">Mr. Badal Jamod</div>
                <div class="sig-title">CEO/CTO - Techstrota</div>
            </div>

            <div class="footer">
                <div class="footer-line"></div>
                <div class="footer-content">
                    <span class="footer-brand">Techstrota</span> | <a href="https://www.techstrota.com"
                        class="footer-link">www.techstrota.com</a><br>
                    503, Sterling Centre, R C Dutt Road, Near Fairfield Hotel, Alkapuri, Vadodara, Gujarat - 390007
                </div>
                <span class="system-remark">This is a system-generated document.</span>
            </div>
        </div>
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>