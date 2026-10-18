<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewJobApplication extends Mailable
{
    public function __construct(public JobApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->application->email, $this->application->name)],
            subject: "New job application: {$this->application->position} ({$this->application->reference})",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.new-job-application');
    }
}
