<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewQuoteRequest extends Mailable
{
    public function __construct(public QuoteRequest $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->quote->email, $this->quote->name)],
            subject: "New quotation request {$this->quote->reference}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.new-quote-request');
    }
}
