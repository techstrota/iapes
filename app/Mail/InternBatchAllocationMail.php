<?php

namespace App\Mail;

use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InternBatchAllocationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $internCode;
    public string $durationDates;
    public string $shiftLabel;
    public string $portalUrl;

    public function __construct(
        public Intern $intern,
        public InternshipBatch $batch,
        public ?string $customNote = null,
        public ?string $customSubject = null
    ) {
        $this->internCode = $intern->intern_code ?: ('INT-' . $intern->id);

        // Resolve Duration Dates
        $startDate = $intern->joining_date ?: $intern->offerletter?->joining_date;
        $endDate = $intern->completion_date ?: $intern->offerletter?->completion_date;
        $durationLabel = $intern->offerletter?->duration_label ?: ($batch->duration ?: '3 Months');

        if ($startDate && $endDate) {
            $formattedStart = Carbon::parse($startDate)->format('d M Y');
            $formattedEnd = Carbon::parse($endDate)->format('d M Y');
            $this->durationDates = "{$formattedStart} to {$formattedEnd} ({$durationLabel})";
        } elseif ($startDate) {
            $formattedStart = Carbon::parse($startDate)->format('d M Y');
            $this->durationDates = "From {$formattedStart} ({$durationLabel})";
        } else {
            $this->durationDates = $durationLabel;
        }

        // Shift Label Resolution
        $rawHour = 9;
        if (!empty($batch->batch_timing)) {
            $parts = preg_split('/(\s+to\s+|\s*-\s*)/i', trim($batch->batch_timing));
            if (!empty($parts[0])) {
                try {
                    $rawHour = (int) Carbon::parse(trim($parts[0]))->format('H');
                } catch (\Throwable $e) {}
            }
        }

        if ($rawHour < 12) {
            $this->shiftLabel = 'Morning Shift';
        } elseif ($rawHour < 17) {
            $this->shiftLabel = 'Afternoon Shift';
        } else {
            $this->shiftLabel = 'Evening Shift';
        }

        $this->portalUrl = url('/intern/login');
    }

    public function build()
    {
        $subject = $this->customSubject ?: "Techstrota Internship — Batch Allocation & Schedule Details ({$this->batch->batch_name})";

        return $this->subject($subject)
                    ->view('emails.interns.batch-allocation');
    }
}
