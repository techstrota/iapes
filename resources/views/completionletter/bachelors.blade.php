@extends('completionletter.wrapper')

@section('content')
    @php
        $internName = $intern->offer_letters->name ?? $intern->application->name ?? 'Intern';
        $internUniversity = $intern->offer_letters->university ??  '';
        $internCollege = $intern->offer_letters->college ?? $intern->application->college ?? '';
        $internDegree = $intern->offerLetter->degree ?? $intern->application->degree ?? '';
        $uni = $internCollege ?: $internUniversity;
        $startDate = \Carbon\Carbon::parse($intern->offer_letters->joining_date);
        $endDate = \Carbon\Carbon::parse($intern->offer_letters->completion_date);

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

        $workingHoursPerDay = $intern->offer_letters->working_hours /6 ?: 5;
        // Total hours calculation: Working Days * Hours per day
        $totalHours = round(($workingHoursPerDay > 40) ? $workingHoursPerDay : ($workingDays * $workingHoursPerDay));
    @endphp

    <div class="title">INTERNSHIP COMPLETION LETTER</div>

    <div class="meta-row">
        <div class="meta-left">
            <strong>From: Techstrota</strong><br>
            <strong>Issued on: {{ \Carbon\Carbon::parse($intern->issuing_date)->format('d/m/Y') }}</strong>
        </div>
        <div class="meta-right">
            <strong>Certificate ID: {{ $intern->intern_code }}</strong>
        </div>
    </div>

    <div class="content-p">
        This is to certify that <strong>{{ $internName }}</strong>@if($internCollege || $internUniversity), a student of
        <strong>{{ $internDegree }}</strong>,@endif has successfully completed 
        @if($isShortTerm)
            the <strong>{{ $workingDays }} Days ({{ $totalHours }} Hours)</strong> internship{!! $intern->grade ? ' with Grade <strong>' . e($intern->grade) . '</strong>' : '' !!}.
        @else
        the internship {!! $intern->grade ? 'with Grade <strong>' . e($intern->grade) . '</strong>' : '' !!}.
        @endif
        The internship was carried out for the course titled
        <strong>“{{ $intern->offer_letters->internship_role }}”</strong>, conducted by
        <strong>Techstrota</strong>@if($internCollege || $internUniversity) and facilitated by
            <strong>{{ $uni }}</strong>@endif.
        The internship duration was from <strong>{{ $startDate->format('d/m/Y') }}</strong> to
        <strong>{{ $endDate->format('d/m/Y') }}</strong> at Techstrota. 503, Sterling Centre, R C Dutt Road, Near Fairfield
        Hotel, Alkapuri, Vadodara, Gujarat - 390007
    </div>

    @if($intern->project_description)
        <div class="skills-list">
            {!! $intern->project_description !!}
        </div>
    @endif
@endsection