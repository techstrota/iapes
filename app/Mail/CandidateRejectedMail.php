<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    public function __construct($record)
    {
        if ($record instanceof \App\Models\InterviewManagement\InterviewAssignment) {
            $this->application = $record->application;
        } else {
            $this->application = $record;
        }
    }

    public function envelope(): Envelope
    {
        $code = $this->application->application_code ?? null;
        $subject = $code
            ? "Application Status Update ({$code}) - TechStrota"
            : "Application Status Update - TechStrota";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.candidate-rejected',
            with: [
                'application' => $this->application,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
