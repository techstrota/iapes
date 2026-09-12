<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateSelectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $assignment;

    public function __construct($record)
    {
        if ($record instanceof \App\Models\InterviewManagement\InterviewAssignment) {
            $this->assignment = $record;
            $this->application = $record->application;
        } else {
            $this->application = $record;
            $this->assignment = $record->interviewAssignments()->latest()->first();
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations! You Are Selected 🎉 - TechStrota',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.candidate-selected',
            with: [
                'application' => $this->application,
                'assignment' => $this->assignment,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
