<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>TechStrota Welcome Greeting Cards</title>
@php
    $openSansPath = public_path('fonts/OpenSans-Bold.ttf');
    $openSansBoldBase64 = file_exists($openSansPath)
        ? base64_encode(file_get_contents($openSansPath))
        : null;
@endphp
<style>
    @if (!empty($openSansBoldBase64))
    @font-face {
        font-family: 'Open Sans';
        src: url('data:font/truetype;charset=utf-8;base64,{{ $openSansBoldBase64 }}') format('truetype');
        font-weight: 700;
        font-style: normal;
    }
    @endif

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
        width: 297mm;
        height: 210mm;
        font-family: "Open Sans", "DejaVu Sans", "Helvetica Neue", Arial, sans-serif;
        background: #ffffff;
        color: #1e293b;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .page-sheet {
        width: 297mm;
        height: 210mm;
        page-break-after: always;
        overflow: hidden;
        position: relative;
    }
    .page-sheet.last-page {
        page-break-after: avoid;
    }

    /* ── 2x2 Master Grid: Exactly 2 rows and 2 columns (4 cards total per sheet) ── */
    table.cards-grid {
        width: 297mm;
        height: 210mm;
        border-collapse: collapse;
        table-layout: fixed;
    }
    tr.grid-row {
        height: 104.5mm;
    }
    td.grid-cell {
        width: 148.5mm;
        height: 104.5mm;
        vertical-align: middle;
        text-align: center;
        padding: 0;
        box-sizing: border-box;
    }

    /* Light dotted/dashed cutting guides separating the 4 quadrants */
    .cut-line-r {
        border-right: 1.5px dashed #cbd5e1;
    }
    .cut-line-b {
        border-bottom: 1.5px dashed #cbd5e1;
    }

    /* ── Self-Contained Welcome Greeting Card (138mm x 96.6mm) ── */
    .welcome-card {
        width: 138mm;
        height: 96.6mm;
        margin: 0 auto;
        position: relative;
        overflow: hidden;
        border-radius: 6px;
        background: #ffffff;
        box-sizing: border-box;
    }

    /* Full Card Graphic Artwork */
    .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 138mm;
        height: 96.6mm;
        z-index: 1;
        pointer-events: none;
    }

    /* ── Precise Text Fields mapped to the underlined spaces (Open Sans, 9.5pt) ── */
    /* Underline 1: INTERN NAME */
    .field-name {
        position: absolute;
        left: 43.5%;
        top: 62.0%;
        width: 41.5%;
        font-family: "Open Sans", "DejaVu Sans", sans-serif;
        font-size: 9.5pt;
        font-weight: 700;
        color: #0B3558;
        line-height: 1.2;
        white-space: nowrap;
        text-align: left;
        z-index: 10;
    }

    /* Underline 2: INTERNSHIP ID */
    .field-id {
        position: absolute;
        left: 44.5%;
        top: 68.6%;
        width: 40.5%;
        font-family: "Open Sans", "DejaVu Sans", sans-serif;
        font-size: 9.5pt;
        font-weight: 700;
        color: #0B3558;
        line-height: 1.2;
        white-space: nowrap;
        text-align: left;
        z-index: 10;
    }

    /* Underline 3: BATCH */
    .field-batch {
        position: absolute;
        left: 35.5%;
        top: 75.4%;
        width: 49.5%;
        font-family: "Open Sans", "DejaVu Sans", sans-serif;
        font-size: 9.5pt;
        font-weight: 700;
        color: #0B3558;
        line-height: 1.2;
        white-space: nowrap;
        text-align: left;
        z-index: 10;
    }
</style>
</head>
<body>

@php
    // Fallback image loader if not passed
    if (empty($cardBgBase64)) {
        $cardBgPath = public_path('images/welcome_card_final.jpeg');
        if (!file_exists($cardBgPath)) {
            $cardBgPath = public_path('images/welcome card bg.png');
        }
        if (file_exists($cardBgPath)) {
            $mime = str_ends_with(strtolower($cardBgPath), '.png') ? 'image/png' : 'image/jpeg';
            $cardBgBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($cardBgPath));
        }
    }

    // If no interns are passed or empty collection, render 1 sheet with 4 blank cards
    $internList = (!empty($interns) && count($interns) > 0) ? $interns : collect([null, null, null, null]);
    $chunks = $internList->chunk(4);
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
                        $internName = $intern ? ($intern->name ?: ($intern->offerletter?->name ?? '')) : '';
                        $internCode = $intern ? ($intern->intern_code ?: ('INT-' . str_pad($intern->id, 3, '0', STR_PAD_LEFT))) : '';
                        $batchName  = $intern ? ($intern->batch?->batch_name ?? ($intern->offerletter?->batch_name ?? '')) : '';
                    @endphp

                    <td class="{{ $cClassStr }}">
                        <div class="welcome-card">
                            @if (!empty($cardBgBase64))
                                <img src="{{ $cardBgBase64 }}" class="card-bg" />
                            @endif

                            @if (!empty($internName))
                                <div class="field-name">{{ $internName }}</div>
                            @endif

                            @if (!empty($internCode))
                                <div class="field-id">{{ $internCode }}</div>
                            @endif

                            @if (!empty($batchName))
                                <div class="field-batch">{{ $batchName }}</div>
                            @endif
                        </div>
                    </td>
                @endfor
            </tr>
        </table>
    </div>
@endforeach

</body>
</html>
