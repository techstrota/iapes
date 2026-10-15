@include('certificate.intern-completion-certificate', [
    'isPdf'       => $isPdf ?? false,
    'logo'        => $logo ?? null,
    'interns'     => $interns ?? (isset($offers) ? $offers->map(fn($o) => $o->intern ?: $o)->filter() : collect()),
    'offers'      => $offers ?? collect(),
    'qrCodes'     => $qrCodes ?? null,
    'displayOnly' => $displayOnly ?? false,
])