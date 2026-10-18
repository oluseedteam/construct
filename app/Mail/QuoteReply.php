<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuoteReply extends Mailable
{
    public function __construct(public QuoteRequest $quote, public string $replySubject, public string $replyBody) {}

    public function envelope(): Envelope
    {
        $replyTo = filter_var(setting('email'), FILTER_VALIDATE_EMAIL) ? [new Address(setting('email'), setting('company_name'))] : [];

        return new Envelope(replyTo: $replyTo, subject: $this->replySubject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.quote-reply');
    }
}
