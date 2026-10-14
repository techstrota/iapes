<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page {
        margin: 80px 15mm 10mm 15mm;
    }

    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        color: #000; /* Text strictly black */
        text-align: justify;
        margin: 0;
        padding: 0;
        position: relative;
    }

    /* Horizontal Watermark Styling */
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.3;
        z-index: -1000;
        width: 100%;
        text-align: center;
    }

    .watermark img {
        width: 600px;
        height: auto;
    }

    header {
        position: fixed;
        top: -45px;
        left: 0;
        right: 0;
        border-bottom: 2px solid #f39200;
        padding-bottom: 10px;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-logo {
        height: 50px;
    }

    .header-contact {
        font-size: 11px;
        color: #000;
    }

    /* Main Content Layout */
    main {
        position: relative;
    }

    .address-section {
        margin-top: 20px;
        line-height: 1.4;
    }

    .date-section {
        margin-top: 20px;
        text-align: right;
        font-weight: bold;
    }

    /* Modern Subject block utilizing company colors */
    .subject {
        margin: 20px 0;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 0;
        background-color: #fdfdfd;
    }

    .content {
        margin-top: -20px;
    }

    /* List styling for modern readability */
    ul {
        margin-left: 20px;
        padding-left: 0;
        list-style-type: square;
    }

    li {
        margin-bottom: 8px;
    }

    /* Signature Table Styling - aligned and positioned at the bottom above footer */
    .page-two-container {
        position: relative;
        min-height: 820px;
    }

    .signature-table {
        position: absolute;
        bottom: 255px;
        left: 0;
        right: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .signature-table td {
        width: 50%;
        vertical-align: bottom;
    }

    .signature-line {
        border-top: 1px solid #000;
        margin-top: 40px;
        padding-top: 5px;
        width: 180px;
    }

    /* Clean Footer matching single page offer letter aesthetic */
    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 11px;
        border-top: 2px solid #f39200;
        padding-top: 8px;
        color: #000;
        background: white;
    }

    .system-remark {
        font-size: 9px;
        color: maroon;
        font-style: italic;
        margin-top: 5px;
        display: block;
        text-align: right;
    }

    .page-break {
        page-break-after: always;
    }
</style>
</head>

<body>
    <div class="watermark">
        <img src="{{ public_path('images/TsLogo.png') }}" alt="Techstrota Watermark">
    </div>

    <header>
        <table class="header-table">
            <tr>
                <td class="header-contact" style="text-align: left; font-size: 14px; font-weight: bold; vertical-align:bottom;">Email: info@techstrota.com</td>
                <td style="text-align: center;">
                    <img src="{{ public_path('images/TsLogo.png') }}" class="header-logo">
                </td>
                <td class="header-contact" style="text-align: right; font-size: 14px; font-weight: bold; vertical-align:bottom;">Tel: +91 81288 40055</td>
            </tr>
        </table>
    </header>

    <main>
        @if(isset($offers))
            @foreach($offers as $offer)
                @php
                    $internName       = $offer->application?->name   ?? $offer->getRawOriginal('name')   ?? 'Intern';
                    $internCollege    = $offer->application?->college ?? $offer->getRawOriginal('college') ?? '';
                    $internUniversity = $offer->university ?? $offer->application?->college ?? '';
                    $durationText     = $offer->duration_label ?? '4 Months';
                @endphp

                <div class="date-section">
                    Date: {{ \Carbon\Carbon::parse($offer->offer_issue_date ?? now())->format('d/m/Y') }} 
                </div>

                <div class="address-section">To,<br>
                    <strong>{{ strtoupper($internName) }}</strong>, <br>
                    @if($internCollege && $internCollege !== $internUniversity)
                        {{ $internCollege }} <br>
                    @endif
                    @if($internUniversity)
                        {{ $internUniversity }} <br>
                    @endif                   
                </div>

                <div class="subject">
                    <strong>Subject: Intern Offer / Appointment Letter</strong> 
                </div>

                <div class="content">
                    <p>Dear <strong>{{ strtoupper($internName) }}</strong>,</p>

                    <p>
                        We are pleased to offer you an internship position at Techstrota for the role of 
                        <strong>{{ $offer->internship_role }} ({{ $durationText }} Duration)</strong>.  
                        This internship presents an excellent opportunity for you to gain valuable experience and enhance your skills in software development by working with a talented and dynamic team. 
                    </p>
                    
                    <p>As an intern, you will be responsible for: </p>
                    <ul>
                        <li>Developing, testing, and debugging software applications and features using various technologies and tools. </li>
                        <li>Collaborating with other developers, designers, and project managers to deliver high-quality products and services. </li>
                        <li>Following best practices and standards for coding, documentation, and quality assurance. </li>
                        <li>Learning new skills and technologies and applying them to your projects. </li>
                        <li>Contributing to the improvement and innovation of the software development process and culture. </li>
                    </ul>
                </div>

                <div class="page-break"></div>

                <div class="page-two-container">
                    <div style="margin-top: 50px;">
                        @if(!empty(trim(strip_tags($offer->description ?? ''))))
                            {!! $offer->description !!}
                        @else
                            @php
                                $commenceDate = $offer->joining_date ? \Carbon\Carbon::parse($offer->joining_date)->format('jS F, Y') : '18th December, 2025';
                                $concludeDate = $offer->completion_date ? \Carbon\Carbon::parse($offer->completion_date)->format('jS F, Y') : '30th April, 2026';
                                $workingHours = !empty($offer->working_hours) ? $offer->working_hours : '11:00 AM to 4:00 PM, Monday to Saturday';
                            @endphp
                            <p>The internship will commence on <strong>{{ $commenceDate }}</strong> and will conclude on <strong>{{ $concludeDate }}</strong>. You will be expected to work <strong>{{ $workingHours }}</strong>, from <strong>Monday to Saturday</strong>, between <strong>10:30 AM to 5:30 PM</strong>.</p>
                            <p>Upon successful completion of the internship, you will receive a <strong>Certificate of Completion</strong> and a <strong>Letter of Recommendation</strong>. You will also be eligible for certain benefits, including access to the company’s facilities, events, and training programs.</p>
                        @endif

                        <p>We are excited to have you join our team and look forward to working with you. </p>
                    </div>

                    <table class="signature-table">
                        <tr>
                            <td style="text-align: left;">
                                Sincerely,<br><br><br>
                                <div class="signature-line">
                                    <strong>Badal Jamod</strong><br>
                                    CEO/CTO, Techstrota
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div class="signature-line" style="margin-left: auto;">
                                    Intern Signature
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                @if(!$loop->last)
                    <div class="page-break"></div>
                @endif
            @endforeach
        @endif
    </main>

    <footer>
        <strong>Techstrota</strong> | <span style="color: blue;">www.techstrota.com</span><br>
        503, Sterling Centre, R C
        Dutt Road, Near Fairfield Hotel, Alkapuri,
        Vadodara - 390007<br>
        Tel: +91 81288 40055 | CIN: GJ240114897
        <span class="system-remark">This is a system-generated document.</span>
    </footer>
</body>
</html>
