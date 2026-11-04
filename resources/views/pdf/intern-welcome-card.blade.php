<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>TechStrota Welcome Greeting Cards</title>
<style>
    @page {
        size: 297mm 210mm; /* A4 Landscape (297mm width x 210mm height) */
        margin: 0;
    }
    * {
        box-sizing: border-box;
    }
    html, body {
        margin: 0;
        padding: 0;
        font-family: "DejaVu Sans", "Helvetica Neue", Arial, sans-serif;
        background: #ffffff;
        color: #1e293b;
        -webkit-print-color-adjust: exact;
    }
    .page-sheet {
        width: 297mm;
        page-break-after: always;
        overflow: hidden;
    }
    .page-sheet.last-page {
        page-break-after: avoid;
    }

    /* ── 2x2 Master Grid: Exactly 2 rows and 2 columns (4 cards total per sheet) ── */
    table.cards-grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    tr.grid-row {
        height: 98mm;
    }
    td.grid-cell {
        width: 50%;
        height: 98mm;
        vertical-align: middle;
        text-align: center;
        padding: 0;
        box-sizing: border-box;
    }

    /* Light dotted/dashed gray cutting guides separating the 4 quadrants */
    .cut-line-r {
        border-right: 1.5px dashed #cbd5e1;
    }
    .cut-line-b {
        border-bottom: 1.5px dashed #cbd5e1;
    }

    /* ── Self-Contained Welcome Greeting Card (138mm x 92mm) ── */
    .welcome-card {
        width: 138mm;
        height: 92mm;
        margin: 0 auto;
        position: relative;
        /* Subtle pale blue-white tinted gradient (#F4F8FB to #FFFFFF) */
        background: #ffffff;
        background: -webkit-linear-gradient(top, #F4F8FB 0%, #FFFFFF 55%, #F4F8FB 100%);
        /* Crisp Brand Orange (#F5921E) border on card edge */
        border: 2px solid #F5921E;
        border-radius: 8px;
        padding: 0;
        text-align: center;
        overflow: hidden;
        box-sizing: border-box;
    }

    /* Vertical and Horizontal Centering Table */
    table.card-body-table {
        width: 100%;
        height: 92mm;
        border-collapse: collapse;
        table-layout: fixed;
    }
    td.card-body-td {
        width: 100%;
        height: 92mm;
        vertical-align: middle;
        text-align: center;
        padding: 4px 14px;
        box-sizing: border-box;
    }

    /* Semi-transparent logo.png emblem centered as visible watermark */
    .card-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 130px;
        height: 180px;
        margin-top: -90px;
        margin-left: -65px;
        opacity: 0.16;
        z-index: 1;
        pointer-events: none;
    }

    /* Digital pixel / tech circuit elements & dots in brand orange (#F5921E) and blue (#0284C7) */
    .deco-dots-tl {
        position: absolute;
        top: 8px;
        left: 10px;
        font-size: 8px;
        color: #0284C7;
        letter-spacing: 2.2px;
        line-height: 1;
        z-index: 2;
    }
    .deco-pixels-tr {
        position: absolute;
        top: 7px;
        right: 9px;
        line-height: 0;
        z-index: 2;
    }
    .deco-pixels-bl {
        position: absolute;
        bottom: 7px;
        left: 9px;
        line-height: 0;
        z-index: 2;
    }
    .sparkle-tl {
        position: absolute;
        top: 22px;
        left: 11px;
        line-height: 0;
        z-index: 2;
    }
    .sparkle-tr {
        position: absolute;
        top: 15px;
        right: 32px;
        line-height: 0;
        z-index: 2;
    }
    .sparkle-mr {
        position: absolute;
        top: 54px;
        right: 9px;
        line-height: 0;
        z-index: 2;
    }
    .sparkle-br-orange {
        position: absolute;
        bottom: 24px;
        right: 13px;
        line-height: 0;
        z-index: 2;
    }
    .sparkle-br-blue {
        position: absolute;
        bottom: 9px;
        right: 8px;
        line-height: 0;
        z-index: 2;
    }

    .card-content {
        position: relative;
        z-index: 3;
        width: 100%;
        margin: 0 auto;
    }

    .brand-logo {
        height: 28px;
        margin-top: 0;
        margin-bottom: 2px;
    }

    .card-title {
        font-size: 16px;
        font-weight: 900;
        color: #0B3558;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin-bottom: 2px;
        line-height: 1.15;
    }

    .card-greeting {
        font-size: 11px;
        font-weight: 800;
        color: #0B3558;
        margin-bottom: 3px;
        line-height: 1.2;
    }

    .card-msg {
        font-size: 9px;
        color: #1E293B;
        line-height: 1.38;
        margin: 0 auto 6px auto;
        max-width: 96%;
    }

    /* ── Intern Details Section with Rounded Edges & Orange Bracket Borders ── */
    .details-box {
        position: relative;
        width: 96%;
        margin: 0 auto;
        padding: 5px 10px 4px 10px;
        background: rgba(255, 255, 255, 0.90);
        border-radius: 6px;
        box-sizing: border-box;
    }

    /* 4 Orange Bracket Borders (#F5921E) */
    .bracket-tl {
        position: absolute;
        top: 0;
        left: 0;
        width: 16px;
        height: 16px;
        border-top: 2.4px solid #F5921E;
        border-left: 2.4px solid #F5921E;
        border-top-left-radius: 6px;
    }
    .bracket-tr {
        position: absolute;
        top: 0;
        right: 0;
        width: 16px;
        height: 16px;
        border-top: 2.4px solid #F5921E;
        border-right: 2.4px solid #F5921E;
        border-top-right-radius: 6px;
    }
    .bracket-bl {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 16px;
        height: 16px;
        border-bottom: 2.4px solid #F5921E;
        border-left: 2.4px solid #F5921E;
        border-bottom-left-radius: 6px;
    }
    .bracket-br {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 16px;
        height: 16px;
        border-bottom: 2.4px solid #F5921E;
        border-right: 2.4px solid #F5921E;
        border-bottom-right-radius: 6px;
    }

    table.detail-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    table.detail-table td {
        padding: 2.5px 0;
        vertical-align: middle;
    }
    .ico-col {
        width: 18px;
        text-align: left;
        vertical-align: middle;
        padding-right: 2px;
    }
    .lbl-icon {
        width: 13px;
        height: 13px;
        display: block;
    }
    .lbl-col {
        width: 114px;
        font-weight: 800;
        color: #0B3558;
        white-space: nowrap;
        font-size: 10px;
        vertical-align: middle;
    }
    .val-col {
        border-bottom: 1.6px solid #F5921E;
        padding-left: 6px;
        font-weight: 700;
        color: #0f172a;
        font-size: 11px;
        vertical-align: middle;
    }
    .sign-col {
        white-space: nowrap;
        text-align: right;
        font-weight: 800;
        color: #0B3558;
        font-size: 10px;
        padding-left: 6px;
        vertical-align: middle;
    }
    .star-icon {
        width: 12px;
        height: 12px;
        vertical-align: -1px;
        margin-left: 3px;
    }

    /* Blank card outline placeholder */
    .blank-card {
        width: 138mm;
        height: 92mm;
        margin: 0 auto;
        border: 1.5px dashed #cbd5e1;
        border-radius: 8px;
        background: #fafafa;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }
</style>
</head>
<body>

@php
    $userIcon = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#0B3558"><circle cx="12" cy="7" r="4"/><path d="M4 21v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2"/></svg>');
    $idIcon   = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#0B3558"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="10" r="2.2" fill="#ffffff"/><path d="M5.5 16c0-1.6 1.3-3 3-3s3 1.4 3 3" fill="#ffffff"/><rect x="13.5" y="8" width="5.5" height="1.8" rx="0.5" fill="#ffffff"/><rect x="13.5" y="11.5" width="5.5" height="1.8" rx="0.5" fill="#ffffff"/><rect x="13.5" y="15" width="4" height="1.8" rx="0.5" fill="#ffffff"/></svg>');
    $calIcon  = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#0B3558"><rect x="3" y="4" width="18" height="18" rx="2.5"/><rect x="7" y="2" width="2" height="4" rx="1" fill="#0B3558"/><rect x="15" y="2" width="2" height="4" rx="1" fill="#0B3558"/><rect x="3" y="9" width="18" height="2" fill="#ffffff"/><circle cx="8" cy="14" r="1.2" fill="#ffffff"/><circle cx="12" cy="14" r="1.2" fill="#ffffff"/><circle cx="16" cy="14" r="1.2" fill="#ffffff"/><circle cx="8" cy="18" r="1.2" fill="#ffffff"/><circle cx="12" cy="18" r="1.2" fill="#ffffff"/><circle cx="16" cy="18" r="1.2" fill="#ffffff"/></svg>');
    $starIcon = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0B3558" stroke-width="2.2"><path d="M12 2 L14.5 9.5 L22 12 L14.5 14.5 L12 22 L9.5 14.5 L2 12 L9.5 9.5 Z"/></svg>');
    $sparkleOrange = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#F5921E"><path d="M12 0 L14.5 9.5 L24 12 L14.5 14.5 L12 24 L9.5 14.5 L0 12 L9.5 9.5 Z"/></svg>');
    $sparkleBlue   = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#0284C7"><path d="M12 0 L14.5 9.5 L24 12 L14.5 14.5 L12 24 L9.5 14.5 L0 12 L9.5 9.5 Z"/></svg>');

    $cardBgWatermark = $watermarkBase64 ?? ($bgLogoBase64 ?? null);

    $chunks = $interns->chunk(4);
    $totalChunks = $chunks->count();
@endphp

@foreach ($chunks as $pageIdx => $chunk)
    @php
        $isLastPage = ($pageIdx === $totalChunks - 1);
        $chunkList = $chunk->values();
    @endphp

    <div class="page-sheet {{ $isLastPage ? 'last-page' : '' }}">
        <table class="cards-grid">
            <tr class="grid-row">
                @for ($i = 0; $i < 4; $i++)
                    @if ($i === 2)
                        </tr><tr class="grid-row">
                    @endif

                    @php
                        $isLeft = ($i % 2 === 0);
                        $isTop  = ($i < 2);

                        $cellClasses = ['grid-cell'];
                        if ($isLeft) $cellClasses[] = 'cut-line-r';
                        if ($isTop)  $cellClasses[] = 'cut-line-b';
                        $cClassStr = implode(' ', $cellClasses);

                        $intern = $chunkList->get($i);
                    @endphp

                    <td class="{{ $cClassStr }}">
                        @if ($intern)
                            @php
                                $internName = $intern->name ?: ($intern->offerletter?->name ?? 'Intern');
                                $internCode = $intern->intern_code ?: ('INT-' . str_pad($intern->id, 3, '0', STR_PAD_LEFT));
                                $batchName  = $intern->batch?->batch_name ?? ($intern->offerletter?->batch_name ?? 'Internship Batch');
                            @endphp

                            <div class="welcome-card">
                                @if (!empty($cardBgWatermark))
                                    <img src="{{ $cardBgWatermark }}" class="card-watermark" />
                                @endif

                                <div class="deco-dots-tl">■ ■ ■ ■ ■</div>

                                <div class="deco-pixels-tr">
                                    <svg width="28" height="20" viewBox="0 0 34 26">
                                        <rect x="22" y="0" width="6" height="6" fill="#F5921E"/>
                                        <rect x="28" y="0" width="6" height="6" fill="#0284C7"/>
                                        <rect x="16" y="6" width="6" height="6" fill="#0284C7"/>
                                        <rect x="22" y="6" width="6" height="6" fill="#F5921E"/>
                                        <rect x="28" y="6" width="6" height="6" fill="#0284C7"/>
                                        <rect x="22" y="12" width="6" height="6" fill="#0284C7"/>
                                        <rect x="28" y="12" width="6" height="6" fill="#F5921E"/>
                                        <rect x="28" y="18" width="6" height="6" fill="#0284C7"/>
                                    </svg>
                                </div>

                                <div class="deco-pixels-bl">
                                    <svg width="28" height="20" viewBox="0 0 34 26">
                                        <rect x="0" y="0" width="6" height="6" fill="#0284C7"/>
                                        <rect x="0" y="6" width="6" height="6" fill="#F5921E"/>
                                        <rect x="6" y="6" width="6" height="6" fill="#0284C7"/>
                                        <rect x="0" y="12" width="6" height="6" fill="#0284C7"/>
                                        <rect x="6" y="12" width="6" height="6" fill="#F5921E"/>
                                        <rect x="12" y="12" width="6" height="6" fill="#0284C7"/>
                                        <rect x="0" y="18" width="6" height="6" fill="#F5921E"/>
                                        <rect x="6" y="18" width="6" height="6" fill="#F5921E"/>
                                        <rect x="12" y="18" width="6" height="6" fill="#F5921E"/>
                                        <rect x="18" y="18" width="6" height="6" fill="#0284C7"/>
                                    </svg>
                                </div>

                                <img src="{{ $sparkleOrange }}" class="sparkle-tl" style="width: 13px; height: 13px;" />
                                <img src="{{ $sparkleOrange }}" class="sparkle-tr" style="width: 11px; height: 11px;" />
                                <img src="{{ $sparkleOrange }}" class="sparkle-mr" style="width: 11px; height: 11px;" />
                                <img src="{{ $sparkleOrange }}" class="sparkle-br-orange" style="width: 13px; height: 13px;" />
                                <img src="{{ $sparkleBlue }}" class="sparkle-br-blue" style="width: 16px; height: 16px;" />

                                <table class="card-body-table">
                                    <tr>
                                        <td class="card-body-td">
                                            <div class="card-content">
                                                @if (!empty($logoBase64))
                                                    <img src="{{ $logoBase64 }}" class="brand-logo" /><br>
                                                @endif

                                                <div class="card-title">WELCOME TO TECHSTROTA!</div>
                                                <div class="card-greeting">Hey, Future Professionals!</div>
                                                <div class="card-msg">
                                                    A big welcome to all our new interns at TechStrota!<br>
                                                    Get ready to learn, explore, share ideas, take on new challenges, and grow with us.<br>
                                                    Your journey starts NOW. Let's make it amazing!<br>
                                                    <strong>Welcome aboard!</strong>
                                                </div>

                                                <div class="details-box">
                                                    <span class="bracket-tl"></span>
                                                    <span class="bracket-tr"></span>
                                                    <span class="bracket-bl"></span>
                                                    <span class="bracket-br"></span>

                                                    <table class="detail-table">
                                                        <tr>
                                                            <td class="ico-col">
                                                                <img src="{{ $userIcon }}" class="lbl-icon" />
                                                            </td>
                                                            <td class="lbl-col">
                                                                INTERN NAME:
                                                            </td>
                                                            <td class="val-col" colspan="2">
                                                                {{ $internName }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ico-col">
                                                                <img src="{{ $idIcon }}" class="lbl-icon" />
                                                            </td>
                                                            <td class="lbl-col">
                                                                INTERNSHIP ID:
                                                            </td>
                                                            <td class="val-col" colspan="2">
                                                                {{ $internCode }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ico-col">
                                                                <img src="{{ $calIcon }}" class="lbl-icon" />
                                                            </td>
                                                            <td class="lbl-col">
                                                                BATCH:
                                                            </td>
                                                            <td class="val-col">
                                                                {{ $batchName }}
                                                            </td>
                                                            <td class="sign-col">
                                                                Team TechStrota <img src="{{ $starIcon }}" class="star-icon" />
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @else
                            <div class="blank-card">
                                <span style="color: #94a3b8; font-size: 10px;">(Blank Card Space)</span>
                            </div>
                        @endif
                    </td>
                @endfor
            </tr>
        </table>
    </div>
@endforeach

</body>
</html>
