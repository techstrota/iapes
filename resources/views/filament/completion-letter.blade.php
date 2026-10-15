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

    $logoBase64 = $logo ?? null;
    if (!$logoBase64) {
        $logoPath = public_path('images/TsLogo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : asset('images/TsLogo.png');
    }
@endphp

@if($targetInterns->count() === 1)
    @php
        $singleIntern = $targetInterns->first();
        $template = $singleIntern->completion_letter_template ?: 'bachelors';
        if (!view()->exists("completionletter.{$template}")) {
            $template = 'bachelors';
        }
    @endphp
    @include("completionletter.{$template}", [
        'intern' => $singleIntern,
        'isPdf'  => $isPdf ?? false,
        'logo'   => $logoBase64,
    ])
@else
    @include('completionletter.bulk', [
        'interns' => $targetInterns,
        'isPdf'   => $isPdf ?? false,
        'logo'    => $logoBase64,
    ])
@endif
