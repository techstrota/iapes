@php 
    $isPdf = $isPdf ?? true; 
    
    if (isset($registration)) {
        $registrations = collect([$registration]);
    } else {
        $registrations = $registrations ?? collect();
    }

    $logoPath = $isPdf ? public_path('images/TsLogo.png') : asset('images/TsLogo.png');
    $faviconPath = $isPdf ? public_path('favicon.ico') : asset('favicon.ico');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @if(!$isPdf)
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Event Certificate - TechStrota</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=UnifrakturMaguntia&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none; background: #525659;">
    <div id="certificate-wrapper">
        <div id="certificate-container">
            <style>
                @media screen {
                    #certificate-wrapper {
                        background-color: #525659;
                        min-height: 100vh;
                        padding: 60px 20px;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: flex-start;
                        box-sizing: border-box;
                    }

                    #certificate-container {
                        width: 100%;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
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
                        background: white;
                    }

                    .no-print {
                        display: none !important;
                    }

                    * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    body * {
                        visibility: hidden !important;
                    }

                    #certificate-container,
                    #certificate-container * {
                        visibility: visible !important;
                    }

                    #certificate-container {
                        position: absolute !important;
                        left: 0 !important;
                        top: 0 !important;
                        width: 297mm !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                }

                .cert {
                    position: relative;
                    width: 297mm;
                    height: 210mm;
                    background: #ffffff !important;
                    overflow: hidden;
                    box-sizing: border-box;
                    font-family: 'Inter', sans-serif;
                    color: #000000;
                }

                .b-outer {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    border: 4mm solid #073c70;
                    z-index: 10;
                    pointer-events: none;
                }

                .b-inner {
                    position: absolute;
                    top: 6mm;
                    left: 6mm;
                    right: 6mm;
                    bottom: 6mm;
                    border: 1mm solid #FF7043;
                    z-index: 10;
                    pointer-events: none;
                }

                .corner-svg {
                    position: absolute;
                    width: 55mm;
                    height: 55mm;
                    z-index: 1;
                    pointer-events: none;
                }

                .corner-svg.tl {
                    top: 6.5mm;
                    left: 6.5mm;
                }

                .corner-svg.br {
                    bottom: 6.5mm;
                    right: 6.5mm;
                }

                .left-content {
                    position: absolute;
                    top: 15mm;
                    left: 20mm;
                    width: 185mm;
                    z-index: 5;
                }

                .logo-img {
                    height: 25mm;
                    margin-bottom: 8mm;
                    margin-left: 30%;
                }

                .title {
                    font-size: 55pt;
                    font-weight: 400;
                    margin: 0 0 2mm 0;
                    font-family: 'UnifrakturMaguntia', 'Old English Text MT', 'Blackletter', serif;
                    line-height: 1;
                    color: #073c70;
                }

                .subtitle {
                    font-size: 16pt;
                    font-weight: 500;
                    color: #444;
                    margin: 0 0 8mm 0;
                }

                .recipient {
                    font-size: 28pt;
                    font-weight: 800;
                    text-transform: uppercase;
                    margin: 0 0 2mm 0;
                    line-height: 1.2;
                    color: #db4f03;
                }

                .recipient-underline {
                    width: 150mm;
                    height: 2pt;
                    background: linear-gradient(90deg, #db4f03, #FF7043, transparent);
                    margin-bottom: 6mm;
                    border-radius: 2px;
                }

                .description {
                    font-size: 14pt;
                    line-height: 1.6;
                    color: #333;
                    max-width: 165mm;
                }
                
                .description ul {
                    text-align: left;
                    display: block;
                    margin: 4mm auto;
                    padding-left: 8mm;
                    list-style-type: disc !important;
                }
                
                .description ol {
                    text-align: left;
                    display: block;
                    margin: 4mm auto;
                    padding-left: 8mm;
                    list-style-type: decimal !important;
                }

                .description li {
                    margin-bottom: 1.5mm;
                    display: list-item !important;
                }

                .signatures-area {
                    position: absolute;
                    bottom: 20mm;
                    right: 40mm;
                    display: flex;
                    gap: 35mm;
                    z-index: 5;
                }

                .sig-box {
                    width: 55mm;
                    text-align: center;
                }

                .sig-line {
                    border-bottom: 1.5pt solid #073c70;
                    height: 15mm;
                    margin-bottom: 2mm;
                    position: relative;
                }

                .sig-name {
                    font-size: 12pt;
                    font-weight: 700;
                    margin: 0;
                    color: #073c70;
                }

                .sig-title {
                    font-size: 10pt;
                    color: #666;
                    margin: 0;
                }

                .banner {
                    position: absolute;
                    top: 6mm;
                    right: 25mm;
                    width: 55mm;
                    height: 130mm;
                    z-index: 2;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding-top: 15mm;
                    box-sizing: border-box;
                }

                .banner-bg {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: -1;
                    overflow: visible;
                }

                .banner-text {
                    font-size: 14pt;
                    font-weight: 800;
                    text-align: center;
                    text-transform: uppercase;
                    line-height: 1.3;
                    width: 85%;
                    color: #ffffff;
                }

                .banner-qr {
                    margin-top: 15mm;
                    width: 35mm;
                    height: 35mm;
                    background: #ffffff;
                    padding: 1.5mm;
                    z-index: 2;
                }
                
                .banner-qr svg {
                    width: 100% !important;
                    height: 100% !important;
                }

                .issued-on {
                    margin-top: auto;
                    margin-bottom: 22mm;
                    text-align: center;
                    font-size: 11pt;
                    font-weight: 700;
                    color: #073c70;
                }

                .issued-on-val {
                    font-size: 12pt;
                    font-weight: 700;
                    margin-top: 1mm;
                    color: #ffffff;
                }


                
                .footer-text {
                    position: absolute;
                    bottom: 10mm;
                    right: 40mm;
                    font-size: 9pt;
                    color: #888;
                    text-align: center;
                }
            </style>
            @if(isset($registrations) && $registrations->isNotEmpty())
                @foreach($registrations as $reg)
                    <div class="cert-scale-wrapper" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
                        <div class="cert">
                            <div class="b-outer"></div>
                            <div class="b-inner"></div>
                            
                            <!-- Corner Designs -->
                            <svg class="corner-svg tl" viewBox="0 0 100 100">
                                <path d="M0,0 L100,0 L92,8 L8,8 L8,92 L0,100 Z" fill="#073c70" />
                                <path d="M14,14 L80,14 L76,18 L18,18 L18,76 L14,80 Z" fill="#db4f03" />
                                <path d="M24,24 L60,24 L58,26 L26,26 L26,58 L24,60 Z" fill="#FCC14B" />
                            </svg>
                            
                            <svg class="corner-svg br" viewBox="0 0 100 100">
                                <path d="M100,100 L0,100 L8,92 L92,92 L92,8 L100,0 Z" fill="#073c70" />
                                <path d="M86,86 L20,86 L24,82 L82,82 L82,24 L86,20 Z" fill="#db4f03" />
                                <path d="M76,76 L40,76 L42,74 L74,74 L74,42 L76,40 Z" fill="#FCC14B" />
                            </svg>
                            
                            <div class="left-content">
                                <img src="{{ $logoPath }}" alt="TechStrota" class="logo-img">
                                
                                <h1 class="title">Certificate</h1>
                                <p class="subtitle">of Participation Awarded to</p>
                                
                                @php
                                    $recipientName = Str::title($reg->name);
                                    $fontSize = strlen($recipientName) > 23 ? '22pt' : '28pt';
                                @endphp
                                <h2 class="recipient" style="font-size:{{ $fontSize }};">
                                    {{ $recipientName }}
                                </h2>
                                <div class="recipient-underline"></div>
                                
                                <div class="description">
                                    on successfully participating in <b>{{ $reg->event->event_title }}</b><br><br>
                                    <!-- This {{ $reg->event->type ?? '' }} {{ $reg->event->event_type ?? 'event' }} was conducted by <b>TechStrota</b> on <b>{{ \Carbon\Carbon::parse($reg->event->event_start_date ?? now())->format('dS F Y') }}</b>.<br> -->
                                    This certificate holder can
                                    {!! $reg->event->skills ?? '' !!}
                                </div>
                            </div>
                            
                            <div class="signatures-area">
                                <div class="sig-box">
                                    <div class="sig-line"></div>
                                    <p class="sig-name">JAMOD BADAL</p>
                                    <p class="sig-title">Founder & CEO</p>
                                </div>
                                <!-- <div class="sig-box">
                                    <div class="sig-line"></div>
                                    <p class="sig-name">TechStrota</p>
                                    <p class="sig-title">Organization</p>
                                </div> -->
                            </div>
                            
                            <div class="banner">
                                <svg class="banner-bg" viewBox="0 0 100 100" preserveAspectRatio="none">
                                    <path d="M 0 0 L 0 100 L 50 90 L 100 100 L 100 0" fill="#FCC14B" stroke="#073c70" stroke-width="8" vector-effect="non-scaling-stroke" />
                                </svg>
                                <div class="banner-text">
                                    @php
                                        $bannerTitle = $reg->event->event_type ?? 'WORKSHOP';
                                        if(isset($reg->event->event_title) && strlen($reg->event->event_title) <= 25) {
                                            $bannerTitle = $reg->event->event_title;
                                        }
                                    @endphp
                                    {{ strtoupper($bannerTitle) }}
                                </div>
                                
                                <div class="banner-qr">
                                    @php $certNum = $reg->certificate_number ?? $reg->generateCertificateNumber(); @endphp
                                    {!! QrCode::size(200)
                                        ->color(7, 60, 112)
                                        ->margin(0)
                                        ->generate(url('/verify-certificate/' . $certNum)) !!}
                                </div>
                                <div style="color: #fff; font-size: 8pt; margin-top: 2mm; z-index: 2; font-weight: bold; letter-spacing: 0.5px;">
                                    ID: {{ $certNum }}
                                </div>
                                
                                <div class="issued-on">
                                    Issued on:<br>
                                    <div class="issued-on-val">{{ \Carbon\Carbon::parse($reg->event->event_end_date ?? $reg->event->event_start_date ?? now())->format('F jS, Y') }}</div>
                                </div>
                            </div>
                            
                            <div class="footer-text">
                                
                                <br> <a href="https://techstrota.com/" style="color:inherit; text-decoration:none;">WWW.TECHSTROTA.COM</a> | This is a system generated certificate
                            </div>
                            
                        </div>{{-- /.cert --}}
                    </div>{{-- /.cert-scale-wrapper --}}
                @endforeach
            @endif
        </div>
    </div>
</body>
</html>
