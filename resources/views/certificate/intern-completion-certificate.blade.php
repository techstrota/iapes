@php
    $isPdf = $isPdf ?? false;
    $displayOnly = $displayOnly ?? false;
    $logoSrc = !empty($logo) ? $logo : asset('images/TsLogo.png');

    // Resolve items (collection of Interns or OfferLetters)
    $items = collect();
    if (isset($interns) && count($interns) > 0) {
        $items = collect($interns);
    } elseif (isset($offers) && count($offers) > 0) {
        $items = collect($offers);
    } elseif (isset($offer)) {
        $items = collect([$offer]);
    } elseif (isset($intern)) {
        $items = collect([$intern]);
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $displayOnly ? 'Internship Certificate Verification - TechStrota' : 'Internship Certificate - TechStrota' }}</title>
    @if(!$isPdf)
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700;800&family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @media screen {
            #certificate-wrapper {
                background-color: #0f172a;
                background-image:
                    radial-gradient(at 0% 0%, rgba(230, 81, 0, 0.25) 0px, transparent 50%),
                    radial-gradient(at 100% 0%, rgba(244, 162, 67, 0.2) 0px, transparent 50%),
                    radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.5) 0px, transparent 100%),
                    linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
                background-size: 100% 100%, 100% 100%, 100% 100%, 40px 40px, 40px 40px;
                min-height: 100vh;
                padding: 40px 20px;
                font-family: 'Inter', sans-serif;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
            }

            .cert-scale-wrapper {
                margin-bottom: 40px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
                border-radius: 2px;
                background: #ffffff;
            }

            .web-toolbar {
                width: 297mm;
                max-width: 95vw;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                color: #ffffff;
            }

            .web-toolbar h2 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
                letter-spacing: 0.5px;
            }

            .web-toolbar .btn-print {
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                color: #ffffff;
                padding: 9px 20px;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                font-size: 14px;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
                transition: all 0.2s;
            }

            .web-toolbar .btn-print:hover {
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(234, 88, 12, 0.45);
            }
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                background: #ffffff;
            }

            .no-print,
            .web-toolbar {
                display: none !important;
            }

            #certificate-wrapper {
                padding: 0 !important;
                background: #ffffff !important;
            }

            .cert-scale-wrapper {
                margin: 0 !important;
                box-shadow: none !important;
                page-break-after: always;
                page-break-inside: avoid;
            }
        }

        /* Certificate Dimensions: Exact A4 Landscape */
        .cert-container {
            position: relative;
            width: 297mm;
            height: 210mm;
            background: #ffffff;
            overflow: hidden;
            box-sizing: border-box;
            border: 2px solid #000000;
        }

        /* Outer Frame Borders */
        .border-gold {
            position: absolute;
            top: 7mm;
            left: 7mm;
            right: 7mm;
            bottom: 10.5mm;
            border: 2.2pt solid #FFA000;
            z-index: 2;
            pointer-events: none;
        }

        .border-wine {
            position: absolute;
            top: 9.2mm;
            left: 9.2mm;
            right: 9.2mm;
            bottom: 12.7mm;
            border: 1pt solid #880E4F;
            z-index: 2;
            pointer-events: none;
        }

        /* Top-Right Swooshes */
        .corner-tr {
            position: absolute;
            top: 0;
            right: 0;
            width: 125mm;
            height: 100mm;
            z-index: 3;
            pointer-events: none;
        }

        /* Bottom-Left Swooshes */
        .corner-bl {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 125mm;
            height: 100mm;
            z-index: 3;
            pointer-events: none;
        }

        /* Tech Watermark SVGs */
        .wm-icon {
            position: absolute;
            z-index: 1;
            opacity: 0.18;
            pointer-events: none;
        }

        /* Main Certificate Content */
        .cert-content {
            position: relative;
            z-index: 5;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 11mm 25mm 10mm 25mm;
            box-sizing: border-box;
        }

        .cert-logo {
            height: 64px;
            margin-bottom: 4mm;
            object-fit: contain;
        }

        .cert-title {
            font-family: 'Playfair Display', 'Cormorant Garamond', Georgia, serif;
            font-size: 30pt;
            font-weight: 800;
            color: #0b3b60;
            letter-spacing: 1.5pt;
            text-transform: uppercase;
            margin: 0 0 2.5mm 0;
            line-height: 1.1;
        }

        .cert-subtitle {
            font-family: 'Playfair Display', 'Cormorant Garamond', Georgia, serif;
            font-size: 19pt;
            font-weight: 700;
            color: #000000;
            margin: 0 0 3mm 0;
            letter-spacing: 0.2pt;
        }

        .cert-recipient {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
            color: #b85d18;
            margin: 0 0 1.5mm 0;
            line-height: 1.15;
            letter-spacing: 0.5pt;
            text-shadow: 1px 1px 1.5px rgba(0, 0, 0, 0.15);
            -webkit-text-stroke: 0.5px #7c2d12;
            text-transform: capitalize;
        }

        .cert-divider {
            width: 68%;
            height: 1.8px;
            background: linear-gradient(90deg, transparent, #FF7043 25%, #FF7043 75%, transparent);
            margin: 0 auto 5.5mm auto;
            opacity: 0.7;
        }

        .cert-body {
            font-family: 'Inter', sans-serif;
            font-size: 15pt;
            line-height: 1.85;
            color: #111827;
            max-width: 78%;
            margin: 0 auto;
        }

        .cert-body b {
            font-weight: 700;
            color: #000000;
        }

        /* Signatures and QR Area */
        .cert-bottom-row {
            position: absolute;
            bottom: 20mm;
            left: 26mm;
            right: 26mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            z-index: 10;
        }

        .signature-group {
            text-align: left;
            margin-bottom: 1.5mm;
            margin-left: 2mm;
        }

        .signature-line {
            width: 44mm;
            border-top: 2.2pt solid #880E4F;
            margin-bottom: 2.5mm;
        }

        .signature-title {
            font-size: 11pt;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.8pt;
            text-transform: uppercase;
        }

        .signature-company {
            font-size: 10pt;
            font-weight: 600;
            color: #000000;
            margin-top: 0.5mm;
        }

        .qr-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 2mm;
        }

        .qr-box {
            width: 29mm;
            height: 29mm;
            padding: 2mm;
            border: 1.4pt solid #FF7043;
            border-radius: 4px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-box img,
        .qr-box svg {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        .qr-ref-code {
            font-family: monospace, -apple-system, sans-serif;
            font-size: 9.5pt;
            font-weight: 700;
            color: #000000;
            letter-spacing: 0.5pt;
            margin-top: 2mm;
            text-align: center;
            white-space: nowrap;
        }

        /* Bottom Footer Strip */
        .cert-footer-strip {
            position: absolute;
            bottom: 3.5mm;
            left: 10mm;
            right: 10mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            font-family: 'Inter', sans-serif;
        }

        .footer-website {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13.5pt;
            font-weight: 800;
            letter-spacing: 0.5pt;
            color: #000000;
            text-decoration: none;
        }

        .footer-system-text {
            margin-left: auto;
            font-size: 8.5pt;
            font-style: italic;
            color: #880E4F;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
    <div id="certificate-wrapper">
        @if(!$isPdf && !$displayOnly && $items->isNotEmpty())
            <div class="web-toolbar no-print">
                <h2>TechStrota Internship Certificate Preview</h2>
                <div style="display: flex; gap: 10px;">
                    <a href="javascript:window.print()" class="btn-print">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Certificate
                    </a>
                </div>
            </div>
        @endif

        @if($items->isNotEmpty())
            @foreach($items as $item)
                @php
                    $intern = $item instanceof \App\Models\InternManagement\Intern ? $item : ($item->intern ?? null);
                    $offer = $item instanceof \App\Models\InterviewManagement\OfferLetter ? $item : ($intern?->offerletter ?? null);
                    $completionCert = $intern?->completionCertificate;

                    // Recipient Name
                    $rawName = $intern?->name
                        ?: ($completionCert?->intern_name
                        ?: ($offer?->name
                        ?: ($intern?->application?->name ?? 'Intern')));
                    $recipientName = \Illuminate\Support\Str::title($rawName);
                    $nameLength = strlen($recipientName);
                    $nameFontSize = $nameLength > 25 ? '38pt' : ($nameLength > 18 ? '44pt' : '48pt');

                    // Role
                    $roleName = $completionCert?->internship_role
                        ?: ($intern?->internship_role
                        ?: ($offer?->internship_role ?? 'developer'));
                    $displayRole = strtolower($roleName) === 'developer' ? 'developer' : $roleName;

                    // Dates
                    $rawJoining = $completionCert?->joining_date ?: ($intern?->joining_date ?: ($offer?->joining_date ?? null));
                    $rawCompletion = $completionCert?->completion_date ?: ($intern?->completion_date ?: ($offer?->completion_date ?? null));

                    $joiningDateFormatted = !empty($rawJoining) ? \Carbon\Carbon::parse($rawJoining)->format('jS F Y') : '15th October 2026';
                    $completionDateFormatted = !empty($rawCompletion) ? \Carbon\Carbon::parse($rawCompletion)->format('jS F Y') : '15th January 2027';

                    // Cert Ref ID
                    $certRefId = $completionCert?->cert_ref_id
                        ?: ($intern?->cert_ref_id
                        ?: ('CERT-' . ($intern?->intern_code ?: ($offer?->offer_code ?: 'TS26/WD/006'))));

                    // Verification Token and QR Code
                    $token = $completionCert?->cert_token
                        ?: ($intern?->cert_token
                        ?: ($intern?->intern_code ? str_replace(['/', '\\'], '-', $intern->intern_code) : 'TS26-WD-006'));
                    $verifyUrl = route('certificate.verify', ['token' => $token]);

                    $qrCodeSvg = null;
                    if (isset($qrCodes)) {
                        if ($intern && isset($qrCodes[$intern->id])) {
                            $qrCodeSvg = $qrCodes[$intern->id];
                        } elseif ($offer && isset($qrCodes[$offer->id])) {
                            $qrCodeSvg = $qrCodes[$offer->id];
                        }
                    }
                @endphp

                <div class="cert-scale-wrapper">
                    <div class="cert-container">
                        <!-- Borders -->
                        <div class="border-gold"></div>
                        <div class="border-wine"></div>

                        <!-- Top-Right Dynamic Swooshes: Blue inner curve, Orange outer corner, Gold line accent -->
                        <svg class="corner-tr" viewBox="0 0 500 400" fill="none" preserveAspectRatio="none">
                            <!-- Blue inner swoop -->
                            <path d="M0 0 C150 0, 500 150, 500 380 L500 0 Z" fill="#0E72B4" />
                            <!-- Orange outer swoop -->
                            <path d="M120 0 C240 0, 500 140, 500 320 L500 0 Z" fill="#FF5722" />
                            <!-- Yellow/Gold accent curve along inner edge of blue swoop -->
                            <path d="M0 0 C150 0, 500 150, 500 380" stroke="#FFCA28" stroke-width="5" fill="none" stroke-linecap="round" />
                        </svg>

                        <!-- Bottom-Left Dynamic Swooshes: Orange inner curve, Blue outer corner, Gold line accent -->
                        <svg class="corner-bl" viewBox="0 0 500 400" fill="none" preserveAspectRatio="none">
                            <!-- Orange inner swoop -->
                            <path d="M0 20 C0 250, 350 400, 500 400 L0 400 Z" fill="#FF5722" />
                            <!-- Blue outer swoop -->
                            <path d="M0 80 C0 260, 260 400, 380 400 L0 400 Z" fill="#0E72B4" />
                            <!-- Yellow/Gold accent curve along inner edge of orange swoop -->
                            <path d="M0 20 C0 250, 350 400, 500 400" stroke="#FFCA28" stroke-width="5" fill="none" stroke-linecap="round" />
                        </svg>

                        <!-- Fixed Tech Stack Watermark Icons matching reference image -->
                        <div class="wm-icon" style="top: 18%; left: 8.5%; width: 14mm; height: 14mm; transform: rotate(-12deg);">
                            <svg viewBox="0 0 24 24" fill="#FF7043"><path d="M3 3h18v18H3V3zm13.7 14.3c1.4 0 2.3-.7 2.3-2 0-1.3-.8-1.8-2.1-2.3l-.7-.3c-.7-.3-1-.5-1-1 0-.4.3-.7.9-.7.6 0 1 .2 1.4.6l1-1c-.6-.7-1.4-1-2.4-1-1.3 0-2.2.7-2.2 1.9 0 1.2.7 1.8 1.9 2.2l.7.3c.8.3 1.2.5 1.2 1.1 0 .5-.4.8-1.1.8-.8 0-1.3-.4-1.7-.9l-1.1.9c.7 1 1.6 1.4 2.8 1.4zm-6.2 0c.9 0 1.6-.4 2-1.1V10h-1.4v6.2c-.2.3-.5.5-.8.5-.4 0-.6-.3-.6-.8V10H9v6.1c0 1.4.6 2.2 1.5 2.2z"/></svg>
                        </div>
                        <div class="wm-icon" style="top: 20%; right: 19%; width: 13.5mm; height: 13.5mm; transform: rotate(10deg);">
                            <svg viewBox="0 0 24 24" fill="#FF7043"><path d="M1.5 0h21l-1.9 21.7L12 24l-8.6-2.3L1.5 0zm15.4 6H7.1l.3 3.4h9.1l-.4 4.3-4.1 1.1-4.1-1.1-.3-3.1H5.4l.5 5.5 6.1 1.7 6.1-1.7 1-10.2H16.9z"/></svg>
                        </div>
                        <div class="wm-icon" style="top: 49%; left: 5%; width: 14mm; height: 14mm; transform: rotate(0deg);">
                            <svg viewBox="0 0 24 24" fill="#FF7043"><path d="M23.9 11.5c-.3-.2-1.3-.7-2.6-.2-.2-.5-.6-.9-1.2-1.2-.8-.4-1.8-.4-2.6-.1-.5-.8-1.5-1.3-2.6-1.3-.2 0-.4 0-.6.1-.3-1.6-1.5-2.7-3-2.7h-1v2.5h1.2v1.2H8.7V8.6h1.2V7.4H8.7V6.1H7.4v1.3H6.1V6.1H4.8v1.3H3.6V6.1H2.3v3.8c-1.2.6-2 1.8-2.2 3.1-.3 1.9.9 3.8 2.7 4.5 3.1 1.3 7 1.5 10.3 1.1 3.2-.4 6.2-1.9 8.2-4.4 1.3-.3 2.3-1.4 2.6-2.7z"/></svg>
                        </div>
                        <div class="wm-icon" style="top: 47%; right: 8%; width: 13mm; height: 13mm; transform: rotate(0deg);">
                            <svg viewBox="0 0 24 24" fill="#FF7043"><path d="M11.9 1.1c-2.8 0-4.6.6-4.6 2.3v1.8h4.6v.6H5.7C3.3 5.8 2 7.3 2 9.8c0 2.5 1.5 3.9 3.8 3.9h1.2v-1.7c0-1.7 1.4-3.1 3.1-3.1h4.6V7.1C14.7 3.6 14.7 1.1 11.9 1.1zm-1.8 1.4c.5 0 .9.4.9.9 0 .5-.4.9-.9.9-.5 0-.9-.4-.9-.9 0-.5.4-.9.9-.9zm2 10.3c-1.7 0-3.1 1.4-3.1 3.1v1.8c0 3.5 0 6 2.8 6 2.8 0 4.6-.6 4.6-2.3v-1.8h-4.6v-.6h6.2c2.4 0 3.7-1.5 3.7-4 0-2.5-1.5-3.9-3.8-3.9h-1.2v1.7c0 1.7-1.4 3.1-3.1 3.1h-1.5zm3.1 8c-.5 0-.9-.4-.9-.9 0-.5.4-.9.9-.9.5 0 .9.4.9.9 0 .5-.4.9-.9.9z"/></svg>
                        </div>
                        <div class="wm-icon" style="top: 66%; right: 21%; width: 12mm; height: 12mm; transform: rotate(-8deg);">
                            <svg viewBox="0 0 24 24" fill="#FF7043"><circle cx="12" cy="12" r="2.2"/><g fill="none" stroke="#FF7043" stroke-width="1.3"><ellipse rx="10" ry="4.2" cx="12" cy="12"/><ellipse rx="10" ry="4.2" cx="12" cy="12" transform="rotate(60 12 12)"/><ellipse rx="10" ry="4.2" cx="12" cy="12" transform="rotate(120 12 12)"/></g></svg>
                        </div>

                        <!-- Main Certificate Content -->
                        <div class="cert-content">
                            <img src="{{ $logoSrc }}" alt="TechStrota" class="cert-logo">

                            <div class="cert-title">Certificate of Internship</div>
                            <div class="cert-subtitle">This is to certify that</div>

                            <div class="cert-recipient" style="font-size: {{ $nameFontSize }};">
                                {{ $recipientName }}
                            </div>
                            <div class="cert-divider"></div>

                            <div class="cert-body">
                                has successfully completed a <b>{{ $displayRole }}</b> internship at <b>TechStrota</b>. The internship was conducted from <b>{{ $joiningDateFormatted }}</b> to <b>{{ $completionDateFormatted }}</b>. During this tenure, the intern demonstrated exceptional professional conduct and technical proficiency.
                            </div>
                        </div>

                        <!-- Signature and QR Row -->
                        <div class="cert-bottom-row">
                            <div class="signature-group">
                                <div class="signature-line"></div>
                                <div class="signature-title">FOUNDER/CEO</div>
                                <div class="signature-company">TechStrota</div>
                            </div>

                            <div class="qr-group">
                                <div class="qr-box">
                                    @if(!empty($qrCodeSvg))
                                        {!! $qrCodeSvg !!}
                                    @else
                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->margin(0)->generate($verifyUrl) !!}
                                    @endif
                                </div>
                                <div class="qr-ref-code">{{ $certRefId }}</div>
                            </div>
                        </div>

                        <!-- Outer Footer Strip -->
                        <div class="cert-footer-strip">
                            <a href="https://techstrota.com" target="_blank" class="footer-website">www.techstrota.com</a>
                            <div class="footer-system-text">This is a system generated certificate</div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</body>
</html>
