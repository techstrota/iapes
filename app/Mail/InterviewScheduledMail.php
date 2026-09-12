<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $batch;
    public $application;
    public $isRescheduled;

    public function __construct($batch, $application, bool $isRescheduled = false)
    {
        $this->batch = $batch;
        $this->application = $application;
        $this->isRescheduled = $isRescheduled;
    }

    public function build()
    {
        $subject = $this->isRescheduled 
            ? 'Interview Rescheduled - TechStrota' 
            : 'Interview Scheduled - TechStrota';

        return $this->subject($subject)
                    ->view('emails.interview_scheduled')
                    ->with([
                        'batch' => $this->batch,
                        'application' => $this->application,
                        'isRescheduled' => $this->isRescheduled,
                    ]);
    }
}
