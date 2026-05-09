@php 
    $isPdf = $isPdf ?? true; 
    
    if (isset($registration)) {
        $registrations = collect([$registration]);
    } else {
        $registrations = $registrations ?? collect();
    }

    $logoPath = $isPdf ? public_path('images/TsLogo.png') : asset('images/TsLogo.png');
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
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Allura&family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;600&family=Cinzel+Decorative:wght@400;700;900&family=Great+Vibes&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap&family=IBM+Plex+Serif:wght@300;400;"
        rel="stylesheet">
</head>

<body style="margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none;">
    <div id="certificate-wrapper">

        <div id="certificate-container">
            <style>
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
                        padding: 60px 20px;
                        font-family: 'Outfit', sans-serif;
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
                    color: #2d3748;
                }

                /* Vibrant Warm Border Styles */
                .b-outer {
                    position: absolute;
                    top: 12mm;
                    left: 12mm;
                    right: 12mm;
                    bottom: 12mm;
                    border: 3.5pt solid #FFA000;
                    z-index: 1;
                }

                .b-inner {
                    position: absolute;
                    top: 14.5mm;
                    left: 14.5mm;
                    right: 14.5mm;
                    bottom: 14.5mm;
                    border: 1pt solid #880E4F;
                    z-index: 1;
                }

                /* Enhanced Corner SVGs */
                .corner-svg {
                    position: absolute;
                    width: 45mm;
                    height: 45mm;
                    z-index: 2;
                    pointer-events: none;
                }

                .corner-svg.tr {
                    top: -2mm;
                    right: 2mm;
                    width: 70mm;
                    height: 60mm;
                    transform: rotate(180deg) scaleX(-1);
                }

                .corner-svg.bl {
                    bottom: 16mm;
                    left: -16mm;
                    width: 70mm;
                    height: 60mm;
                    transform: rotate(270deg) scaleY(-1);
                }

                .wm-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 1;
                    pointer-events: none;
                }

                .content-wrapper {
                    position: relative;
                    z-index: 10;
                    width: 100%;
                    height: 100%;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 25mm 40mm;
                    text-align: center;
                    box-sizing: border-box;
                }

                .main-title {
                    font-family: 'Cormorant Garamond', serif;
                    font-size: 34pt;
                    font-weight: 900;
                    color: #073c70ff;
                    text-transform: uppercase;
                    letter-spacing: 0pt;
                    margin-top: 1mm;
                }

                .recipient {
                    font-family: 'Cinzel Decorative';
                    font-weight: 600;
                    color: #db4f03ff;
                    margin: -4mm 0 2mm;
                    text-transform: capitalize;
                    -webkit-text-stroke: 1pt #4d1d03ff;
                    letter-spacing: 0.5pt;
                }

                .underline {
                    width: 100%;
                    height: 2.5pt;
                    background: linear-gradient(90deg, transparent, #FF7043, #FF7043, transparent);
                    margin-bottom: 12mm;
                }

                .body-text {
                    font-size: 16pt;
                    line-height: 1.8;
                    color: #020202ff;
                    max-width: 90%;
                    margin-top: -4mm;
                }

                .footer {
                    position: absolute;
                    bottom: 25mm;
                    left: 35mm;
                    right: 35mm;
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-end;
                }

                .sig-box {
                    width: 60mm;
                    text-align: center;
                    margin-bottom: -5mm;
                }

                .qr {
                    width: 30mm;
                    height: 30mm;
                    margin-bottom: 2mm;
                    margin-right: -15mm;
                    background: white;
                    padding: 2mm;
                    border: 1pt solid #FF7043;
                    border-radius: 4px;
                }

                .qr svg,
                .qr img {
                    width: 100% !important;
                    height: 100% !important;
                }

                .sig {
                    margin-bottom: -2mm;
                }

                .sig-line-top {
                    border-top: 2pt solid #880E4F;
                    margin-bottom: 4mm;
                    margin-left: 10mm;
                }

                .outer-footer {
                    position: absolute;
                    bottom: 4mm;
                    left: 10mm;
                    right: 10mm;
                    z-index: 10;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    color: #000000;
                    font-family: 'Inter', sans-serif;
                }

                .outer-footer .website {
                    font-size: 18pt;
                    position: absolute;
                    left: 50%;
                    transform: translateX(-50%);
                    font-weight: 600;
                    text-transform: lowercase;
                    letter-spacing: 0.8pt;
                }

                .outer-footer .system-gen {
                    font-size: 10pt;
                    margin-left: auto;
                    font-style: italic;
                    color: #880E4F;
                }
            </style>
            @if(isset($registrations) && $registrations->isNotEmpty())
                @foreach($registrations as $reg)
                    <div class="cert-scale-wrapper" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
                        <div class="cert">
                            <div class="b-outer"></div>
                            <div class="b-inner"></div>
                            
                            <!-- Dynamic Wave Patterns -->
                            <svg class="corner-svg tr" viewBox="0 0 600 500" fill="none" style="width: 600px; height: 500px;">
                                <path d="M600 0 Q600 480 0 480 L600 480 Z" fill="#0e72b4" opacity="1" />
                                <path d="M600 100 Q600 480 200 480 L600 480 Z" fill="#FF7043" opacity="1" />
                                <path d="M600 160 Q560 360 300 480" stroke="#FFCA28" stroke-width="5" fill="none" />
                            </svg>
                            <svg class="corner-svg bl" viewBox="0 0 600 500" fill="none" style="width: 600px; height: 500px;">
                                <path d="M0 0 Q0 480 600 480 L0 480 Z" fill="#FF7043" opacity="1" />
                                <path d="M0 100 Q0 480 400 480 L0 480 Z" fill="#0e72b4" opacity="1" />
                                <path d="M0 160 Q40 360 300 480" stroke="#FFCA28" stroke-width="5" stroke-linecap="round" fill="none" />
                            </svg>
                            
                            @php
                                $icons = ['php', 'laravel', 'mysql', 'python', 'nodedotjs', 'react', 'git', 'tailwindcss', 'typescript', 'javascript', 'docker', 'html5'];
                                $slots = [];
                                for ($r = 0; $r < 4; $r++) {
                                    for ($c = 0; $c < 6; $c++) {
                                        $slots[] = ['r' => $r, 'c' => $c];
                                    }
                                }
                                shuffle($slots);
                            @endphp
                            <div class="wm-overlay">
                                @foreach($icons as $index => $icon)
                                    @if(isset($slots[$index]))
                                        @php
                                            $slot = $slots[$index];
                                            $top = ($slot['r'] * 22) + 6 + mt_rand(0, 4);
                                            $left = ($slot['c'] * 15) + 5 + mt_rand(0, 4);
                                            $size = mt_rand(11, 16);
                                            $rot = mt_rand(-30, 30);
                                            $iconUrl = "https://cdn.simpleicons.org/{$icon}/FF7043";
                                        @endphp
                                        <img src="{{ $iconUrl }}" style="position:absolute; top:{{ $top }}%; left:{{ $left }}%; width:{{ $size }}mm; opacity:0.18; transform:rotate({{ $rot }}deg);">
                                    @endif
                                @endforeach
                            </div>
                            
                            <div class="content-wrapper">
                                <img src="{{ $logoPath }}" alt="TechStrota" style="height:75px;margin-bottom:6mm;margin-top:-7mm;">
                                
                                <div class="main-title">Certificate of Completion</div>
                                
                                <p style="font-size:22pt;color:black;margin:6mm 0;font-weight:900;font-family:'Cormorant Garamond',serif;">
                                    This is to certify that
                                </p>
                                
                                @php
                                    $recipientName = Str::title($reg->name);
                                    $fontSize = strlen($recipientName) > 23 ? '45pt' : '52pt';
                                @endphp
                                <div class="recipient" style="font-size:{{ $fontSize }};">
                                    {{ $recipientName }}
                                </div>
                                
                                <div class="underline"></div>
                                
                                <div class="body-text">
                                    has successfully completed the <b>{{ $reg->event->event_title }}</b>. 
                                    This {{ $reg->event->type }} {{ $reg->event->event_type }} was conducted by <b>TechStrota</b> on 
                                    <b>{{ \Carbon\Carbon::parse($reg->event->event_date)->format('dS F Y') }}</b>. 
                                    We appreciate the dedication and active involvement shown during the event.
                                </div>
                                
                                <div class="footer">
                                    <div class="sig-box sig">
                                        <div class="sig-line-top"></div>
                                        <b style="color:#000000;font-size:12.5pt;font-weight:800;letter-spacing:1pt;margin-left:10mm;">JAMOD BADAL</b><br>
                                        <span style="font-size:11.5pt;color:#000000;font-weight:600;margin-left:10mm;">Founder & CEO</span>
                                    </div>
                                    <div class="sig-box" style="display:flex;flex-direction:column;align-items:center;">
                                        <div class="qr">
                                            @php $certNum = $reg->certificate_number ?? $reg->generateCertificateNumber(); @endphp
                                            {!! QrCode::size(200)
                                                ->color(0, 0, 0)
                                                ->margin(1)
                                                ->generate(url('/verify-certificate/' . $certNum)) !!}
                                        </div>
                                        <span style="font-size:12pt;color:#000000;margin-top:1.5mm;margin-right:-15mm;font-family:monospace;font-weight:700;">
                                            ID:{{ $certNum }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="outer-footer">
                                    <div class="website">
                                        <a href="https://techstrota.com" style="color:inherit;text-decoration:none;">WWW.TECHSTROTA.COM</a>
                                    </div>
                                    <div class="system-gen">This is a system generated certificate</div>
                                </div>
                            </div>
                        </div>{{-- /.cert --}}
                    </div>{{-- /.cert-scale-wrapper --}}
                @endforeach
            @endif
        </div>
    </div>
</body>

</html>
