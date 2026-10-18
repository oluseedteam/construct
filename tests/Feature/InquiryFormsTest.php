<?php

namespace Tests\Feature;

use App\Mail\NewContactMessage;
use App\Mail\NewQuoteRequest;
use App\Mail\QuoteReceived;
use App\Models\ContactMessage;
use App\Models\QuoteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InquiryFormsTest extends TestCase
{
    use RefreshDatabase;

    private function contactData(array $over = []): array
    {
        return array_merge([
            'name' => 'Jane Client', 'email' => 'jane@example.com', 'phone' => '+1 555 010 2000',
            'subject' => 'Office fit-out', 'message' => 'We would like to discuss a fit-out project.',
        ], $over);
    }

    private function quoteData(array $over = []): array
    {
        return array_merge([
            'name' => 'Jane Client', 'email' => 'jane@example.com', 'phone' => '+1 555 010 2000',
            'project_type' => 'Commercial', 'project_location' => 'Downtown',
            'estimated_budget' => '$150,000 - $500,000', 'preferred_start_date' => now()->addMonth()->toDateString(),
            'description' => 'A three storey office building with parking.',
        ], $over);
    }

    public function test_contact_message_is_saved_and_admin_is_emailed(): void
    {
        Mail::fake();
        $this->admin();

        $this->post('/contact', $this->contactData())->assertRedirect(route('contact'))->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', ['email' => 'jane@example.com', 'status' => 'new']);
        Mail::assertSent(NewContactMessage::class, fn ($m) => $m->hasTo('rsquareconstruction@gmail.com') && $m->hasReplyTo('jane@example.com'));
        $this->assertNotNull(ContactMessage::first()->admin_notified_at);
    }

    public function test_contact_validation_rejects_bad_input(): void
    {
        $this->post('/contact', ['email' => 'not-an-email', 'phone' => 'abc'])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_message_is_never_lost_when_email_fails(): void
    {
        $this->admin();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP is down'));

        $this->post('/contact', $this->contactData())->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', ['email' => 'jane@example.com']);
        $this->assertNull(ContactMessage::first()->admin_notified_at);
    }

    public function test_quote_request_generates_unique_reference_and_sends_both_emails(): void
    {
        Mail::fake();
        $this->admin();

        $this->post('/request-a-quote', $this->quoteData())->assertRedirect(route('quote'));
        $this->post('/request-a-quote', $this->quoteData(['email' => 'second@example.com']));

        $refs = QuoteRequest::pluck('reference');
        $this->assertCount(2, $refs->unique());
        $this->assertMatchesRegularExpression('/^RSQ-\d{8}-[A-Z0-9]{6}$/', $refs->first());

        Mail::assertSent(NewQuoteRequest::class, 2);
        Mail::assertSent(QuoteReceived::class, fn ($m) => $m->hasTo('jane@example.com'));

        $this->followingRedirects()->get('/request-a-quote');
        $this->assertDatabaseHas('quote_requests', ['email' => 'jane@example.com', 'status' => 'new']);
    }

    public function test_success_page_shows_the_reference_number(): void
    {
        Mail::fake();

        $response = $this->followingRedirects()->post('/request-a-quote', $this->quoteData());

        $reference = QuoteRequest::first()->reference;
        $response->assertSee('Request received')->assertSee($reference);
    }

    public function test_quote_document_is_stored_privately(): void
    {
        Mail::fake();
        Storage::fake('local');
        Storage::fake('public');

        $this->post('/request-a-quote', $this->quoteData() + ['document' => UploadedFile::fake()->create('plans.pdf', 200, 'application/pdf')])
            ->assertSessionHasNoErrors();

        $quote = QuoteRequest::first();
        $this->assertSame('plans.pdf', $quote->document_name);
        Storage::disk('local')->assertExists($quote->document_path);
        Storage::disk('public')->assertMissing($quote->document_path);
    }

    public function test_quote_rejects_dangerous_or_oversized_documents(): void
    {
        Storage::fake('local');

        $this->post('/request-a-quote', $this->quoteData() + ['document' => UploadedFile::fake()->create('shell.php', 10, 'text/x-php')])
            ->assertSessionHasErrors('document');
        $this->post('/request-a-quote', $this->quoteData() + ['document' => UploadedFile::fake()->create('huge.pdf', 9000, 'application/pdf')])
            ->assertSessionHasErrors('document');

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_quote_validation(): void
    {
        $this->post('/request-a-quote', $this->quoteData(['project_type' => 'Spaceship', 'preferred_start_date' => '2001-01-01', 'description' => 'short']))
            ->assertSessionHasErrors(['project_type', 'preferred_start_date', 'description']);

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_quote_is_never_lost_when_email_fails(): void
    {
        $this->admin();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP is down'));

        $this->post('/request-a-quote', $this->quoteData())->assertRedirect()->assertSessionHas('quote_reference');

        $quote = QuoteRequest::first();
        $this->assertNotNull($quote);
        $this->assertNull($quote->admin_notified_at);
        $this->assertNull($quote->customer_notified_at);
    }

    public function test_honeypot_submissions_are_silently_discarded(): void
    {
        Mail::fake();

        $this->post('/contact', $this->contactData(['website' => 'http://spam.example']))->assertRedirect();
        $this->post('/request-a-quote', $this->quoteData(['website' => 'http://spam.example']))->assertRedirect();

        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertDatabaseCount('quote_requests', 0);
        Mail::assertNothingSent();
    }

    public function test_forms_are_rate_limited(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $this->contactData())->assertRedirect();
        }

        $this->post('/contact', $this->contactData())->assertStatus(429);
        $this->assertDatabaseCount('contact_messages', 5);
    }
}
