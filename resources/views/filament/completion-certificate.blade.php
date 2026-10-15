@php
    $targetInterns = collect();
    if (isset($record)) {
        $targetInterns = collect([$record]);
    } elseif (isset($intern)) {
        $targetInterns = collect([$intern]);
    } elseif (isset($interns) && count($interns) > 0) {
        $targetInterns = collect($interns);
    } elseif (isset($offers) && count($offers) > 0) {
        $targetInterns = collect($offers)->map(fn($o) => $o->intern ?: $o)->filter();
    }
@endphp

@include('certificate.intern-completion-certificate', [
    'isPdf'       => $isPdf ?? false,
    'logo'        => $logo ?? null,
    'interns'     => $targetInterns,
    'offers'      => $offers ?? collect(),
    'qrCodes'     => $qrCodes ?? null,
    'displayOnly' => $displayOnly ?? false,
])
