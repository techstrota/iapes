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
        line-height: 1.4;
        color: #333;
        margin: 0;
        padding: 0;
        position: relative;
    }

    /* Horizontal Watermark Styling */
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%); /* Removed rotation for horizontal layout */
        opacity: 0.3; /* Adjusted slightly for horizontal, very faint */
        z-index: -1000;
        width: 100%; /* Full content area width */
        text-align: center;
    }

    .watermark img {
        width: 600px; /* Slightly larger for the horizontal orientation */
        height: auto;
    }

    header {
        position: fixed;
        top: -65px;
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

    .main-content {
        position: relative;
    }

    .date-section {
        margin-top: 30px;
        text-align: right;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .recipient-info {
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .subject {
        text-align: center;
        font-weight: 800;
        font-size: 15px;
        text-decoration: underline;
        margin: 10px 0;
        color: #000;
    }

    .details-container {
        background-color: #fef9f2;
        border: 1px solid #f39200;
        border-radius: 4px;
        padding: 12px 20px;
        margin: 15px 0;
    }

    .details-table {
        width: 100%;
        border-collapse: collapse;
    }

    .details-table td {
        padding: 2px 0;
    }

    .label {
        font-weight: bold;
        width: 150px;
        color: #000;
    }

    .requirements-list {
        margin: 10px 0;
        padding-left: 20px;
    }

    .requirements-list li {
        margin-bottom: 3px;
    }

    .signature-section {
        margin-top: 25px;
        width: 100%;
        margin-bottom: 20px;
    }

    .signature-line {
        border-top: 1px solid #000;
        margin-top: 60px;
        padding-top: 5px;
        width: 180px;
    }

    .signature-row td {
        width: 50%;
        vertical-align: top;
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
                <td class="header-contact" style="text-align: left; font-size: 13px; font-weight: bold; vertical-align:bottom;">Email: info@techstrota.com</td>
                <td style="text-align: center;">
                    <img src="{{ public_path('images/TsLogo.png') }}" class="header-logo">
                </td>
                <td class="header-contact" style="text-align: right; font-size: 13px; font-weight: bold; vertical-align:bottom;">Tel: +91 81288 40055</td>
            </tr>
        </table>
    </header>

    <main class="main-content">
        @if(isset($offers))
            @foreach($offers as $offer)
                {{-- 
                    Resolve name: for general offers there is no linked application,
                    so we fall back to the `name` column stored directly on the offer_letter row.
                --}}
                @php
                    $internName       = $offer->application?->name   ?? $offer->getRawOriginal('name')   ?? 'Intern';
                    $internCollege    = $offer->application?->college ?? $offer->getRawOriginal('college') ?? '';
                    $internUniversity = $offer->university ?? $offer->application?->college ?? '';
                @endphp
                <div class="date-section">
                    Date: {{ \Carbon\Carbon::parse($offer->offer_issue_date ?? '2026-03-13')->format('d/m/Y') }}
                </div>

                <div class="recipient-info">
                    To,<br>
                    <strong>{{ strtoupper($internName) }}</strong><br>
                    @if($internCollege && $internCollege !== $internUniversity)
                        {{ $internCollege }} <br>
                    @endif
                    @if($internUniversity)
                        {{ $internUniversity }} <br>
                    @endif
                </div>

                <div class="subject">
                    Subject: Internship Offer/Appointment Letter
                </div>

                <p>Dear {{ strtoupper($internName) }},</p>

                <p>
                    We are pleased to inform you that you have been selected for a 
                    <strong>{{ $offer->duration_text ?? 'three-month' }} {{ $offer->internship_role ?? 'Full Stack' }} Developer Internship Program (Open-source Technology)</strong> at Techstrota.
                </p>

                <div class="details-container">
                    <strong>The details of your internship are as follows:</strong>
                    <table class="details-table">
                        <tr>
                            <td class="label">1) Internship Position:</td>
                            <td>{{ $offer->internship_position ?? 'BCA Intern' }}</td>
                        </tr>
                        <tr>
                            <td class="label">2) Duration:</td>
                            <td>{{ \Carbon\Carbon::parse($offer->joining_date)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($offer->completion_date)->format('d/m/Y') }} </td>
                        </tr>
                        <tr>
                            <td class="label">3) Working Hours:</td>
                            <td>11:00 AM to 4:00 PM, Monday to Saturday</td>
                        </tr>
                        <tr>
                            <td class="label">4) Internship Type:</td>
                            <td>On-site</td>
                        </tr>
                    </table>
                </div>

                <p>During the internship period, you are expected to:</p>
                <ul class="requirements-list">
                    <li>Follow all company rules, regulations, and code of conduct.</li>
                    <li>Complete all assigned tasks and projects within deadlines.</li>
                    <li>Maintain confidentiality and professionalism at all times.</li>
                </ul>

                <p>Upon successful completion of your internship, you will receive an <strong>Internship Completion Certificate</strong> from Techstrota acknowledging your contribution and experience gained during this period.</p>

                <p>We are excited to have you onboard and look forward to your positive participation during your time with us.</p>

                <p>Please confirm your acceptance of this offer by replying to this letter or by signing and returning a copy to us.</p>
                
                <p>Best wishes for a productive internship experience!</p>

                <table class="signature-section">
                    <tr class="signature-row">
                        <td>
                            Sincerely,<br><br><br>
                            <div class="signature-line">
                                <strong>{{ $offer->sender_name ?? 'Badal Jamod' }}</strong><br>
                                CEO/CTO, Techstrota
                            </div>
                        </td>
                        <td style="text-align: right; vertical-align: bottom;">
                            __________________________<br>
                            Candidate Signature
                        </td>
                    </tr>
                </table>

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