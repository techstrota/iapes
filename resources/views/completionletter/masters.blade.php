@extends('completionletter.wrapper')

@section('content')
    @php
        $internName = $intern->name ?: ($intern->offer_letters->name ?? $intern->application->name ?? 'Intern');
        $internUniversity = $intern->university ?: ($intern->offer_letters->university ?? '');
        $internCollege = $intern->college ?: ($intern->offer_letters->college ?? $intern->application->college ?? '');
        $internDegree = $intern->degree ?: ($intern->offerLetter->degree ?? $intern->application->degree ?? '');
        $uni = $internCollege ?: $internUniversity;
        $startDate = \Carbon\Carbon::parse($intern->joining_date ?: ($intern->offer_letters?->joining_date ?? now()));
        $endDate = \Carbon\Carbon::parse($intern->completion_date ?: ($intern->offer_letters?->completion_date ?? now()));
        $internRole = $intern->internship_role ?: ($intern->offer_letters?->internship_role ?? 'Software Development');

        // Count working days (excluding Sundays)
        $workingDays = 0;
        $tempDate = $startDate->copy();
        while ($tempDate <= $endDate) {
            if ($tempDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
                $workingDays++;
            }
            $tempDate->addDay();
        }

        // One month or less check (calendar days <= 31)
        $calendarDays = $startDate->diffInDays($endDate) + 1;
        $isShortTerm = ($calendarDays <= 31);

        $workingHoursPerDay = 5;
        $totalHours = $workingDays * $workingHoursPerDay;
    @endphp

    <div class="title">INTERNSHIP COMPLETION LETTER</div>

    <div class="meta-row">
        <div class="meta-left">
            <strong>From: Techstrota</strong><br>
            <strong>Issued on: {{ \Carbon\Carbon::parse($intern->issuing_date)->format('d/m/Y') }}</strong>
        </div>
        <div class="meta-right">
            <strong>Reference ID: {{ $intern->letter_ref_id ?: ($intern->completionLetter?->letter_ref_id ?: ('LET-' . ($intern->intern_code ?: 'INT-' . str_pad($intern->id, 3, '0', STR_PAD_LEFT)))) }}</strong>
        </div>
    </div>

    <div class="content-p">
        This is to certify that <strong>{{ $internName }}</strong>@if($internCollege || $internUniversity), a student of
        <strong>{{ $internDegree }}</strong>,@endif has successfully completed 
        @if($isShortTerm)
            the <strong>{{ $workingDays }} Days ({{ $totalHours }} Hours)</strong> internship{!! $intern->grade ? ' with Grade <strong>' . e($intern->grade) . '</strong>' : '' !!}.
        @else
        the internship{!! $intern->grade ? ' with Grade <strong>' . e($intern->grade) . '</strong>' : '' !!}.
        @endif
        The internship was carried out for the course titled
        <strong>“{{ $internRole }}”</strong>, conducted by
        <strong>Techstrota</strong>@if($internCollege || $internUniversity) and facilitated by
            <strong>{{ $uni }}</strong>@endif.
        The internship duration was from <strong>{{ $startDate->format('d/m/Y') }}</strong> to
        <strong>{{ $endDate->format('d/m/Y') }}</strong> at: <strong>Techstrota</strong><br>
        503, Sterling Centre, R C Dutt Road, Near Fairfield
        Hotel, Alkapuri, Vadodara, Gujarat - 390007.
    </div>

    @if($intern->project_description)
        <div class="skills-list">
            {!! $intern->project_description !!}
        </div>
    @endif
@endsection