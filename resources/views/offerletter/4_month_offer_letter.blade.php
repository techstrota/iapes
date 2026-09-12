<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page {
        margin: 100px 60px 120px 60px;
    }

    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        color: #000; /* Text strictly black */
        text-align: justify;
    }

    /* Professional Header UI with Techstrota Branding */
    header {
        position: fixed;
        top: -70px;
        left: 0;
        right: 0;
        height: 65px;
        border-bottom: 2.5px solid #f39200; /* Techstrota Orange */
    }

    .header-logo {
        height: 55px;
        width: auto;
    }

    /* Main Content Layout */
    main {
        margin-left: 50px;
        margin-right: 50px;
        margin-top: 20px;
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
       /* border-top: 1.5px solid #0e72b4; /* Techstrota Blue */
       /* border-bottom: 1.5px solid #f39200; /* Techstrota Orange */
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

    /* Signature UI */
    .signature-container {
        margin-top: 180px;
        width: 100%;
    }

    .signature-line {
        border-top: 1px solid #000;
        margin-top: 60px;
        padding-top: 5px;
        width: 180px;
    }

    /* Clean Footer matching header aesthetic */
    footer {
        position: fixed;
        bottom: -90px;
        left: 50px;
        right: 50px;
        height: 100px;
        text-align: center;
        font-size: 11px;
        border-top: 2px solid #f39200;
        padding-top: 15px;
    }

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
        width: 500px;
        height: auto;
    }

    .page-break {
        page-break-after: always;
    }
</style>
</head>

<body>

    <div class="watermark">
        <img src="{{ public_path('images/TsLogo.png') }}" alt="Techstrota">
    </div>

    <header>
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; font-size: 13px; width: 30%; vertical-align: bottom;">
                    <strong>Email:</strong> info@techstrota.com 
                </td>
                <td style="text-align: center; width: 40%; vertical-align: middle;">
                    <img src="{{ public_path('images/TsLogo.png') }}" class="header-logo" alt="Techstrota">
                </td>
                <td style="text-align: right; font-size: 13px; width: 30%; vertical-align: bottom;">
                    <strong>Tel:</strong> +91 81288 40055
                </td>
            </tr>
        </table>
    </header>

    <main>
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
                    Date: {{ \Carbon\Carbon::parse($offer->offer_issue_date ?? now())->format('d/m/Y') }} 
                </div>

                <div class="address-section">
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
                    <strong>{{ $offer->internship_role }} (4-Month Duration)</strong>.  
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

                <div style="margin-top: 80px">
                    @if(!empty(trim(strip_tags($offer->description ?? ''))))
                        {!! $offer->description !!}
                    @else
                        @php
                            $commenceDate = $offer->joining_date ? \Carbon\Carbon::parse($offer->joining_date)->format('jS F, Y') : '18th December, 2025';
                            $concludeDate = $offer->completion_date ? \Carbon\Carbon::parse($offer->completion_date)->format('jS F, Y') : '30th April, 2026';
                            $workingHours = !empty($offer->working_hours) ? $offer->working_hours : '42 hours per week';
                        @endphp
                        <p>The internship will commence on <strong>{{ $commenceDate }}</strong> and will conclude on <strong>{{ $concludeDate }}</strong>. You will be expected to work <strong>{{ $workingHours }}</strong>, from <strong>Monday to Saturday</strong>, between <strong>10:30 AM to 5:30 PM</strong>.</p>
                        <p>Upon successful completion of the internship, you will receive a <strong>Certificate of Completion</strong> and a <strong>Letter of Recommendation</strong>. You will also be eligible for certain benefits, including access to the company’s facilities, events, and training programs.</p>
                    @endif
                </div>

                <!-- <p>We are excited to have you join our team and look forward to working with you. </p> -->

                <p>We are excited to have you join our team and look forward to working with you. </p>

                <div class="signature-container">
                    <div style="float: left; width: 60%;">
                        For, <strong>TECHSTROTA</strong><br>
                        Yours Sincerely, <br><br><br><br>
                        <div class="signature-line">
                            <strong>Jamod Badal</strong><br>
                            CEO 
                        </div>
                    </div>
                    <div style="float: right; width: 30%; text-align: center;">
                        <div class="signature-line">
                            Intern Signature
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                </div>

                @if(!$loop->last)
                    <div class="page-break"></div>
                @endif
            @endforeach
        @endif
    </main>

    <footer>
        <div style="font-weight: bold; font-size: 13px; margin-bottom: 5px;">TECHSTROTA</div>
        <div style="text-decoration: underline; margin-bottom: 5px; color:blue;">www.techstrota.com</div>
        <div>503, Sterling Centre, R C Dutt Road, Near Fairfield Hotel, Alkapuri,</div>
        <div>Vadodara, Gujarat - 390007 | Tel: +91 81288 40055 | CIN: GJ240114897</div>
    </footer>

</body>
</html>