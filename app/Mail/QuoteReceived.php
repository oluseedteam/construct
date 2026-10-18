<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuoteReceived extends Mailable
{
    public function __construct(public QuoteRequest $quote) {}

    public function envelope(): Envelope
    {
        $replyTo = filter_var(setting('email'), FILTER_VALIDATE_EMAIL) ? [new Address(setting('email'), setting('company_name'))] : [];

        return new Envelope(
            replyTo: $replyTo,
            subject: 'We received your quotation request ('.$this->quote->reference.')',
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.quote-received');
    }
}
