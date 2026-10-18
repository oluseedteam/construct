<?php

namespace App\Services;

use App\Mail\ApplicationReceived;
use App\Mail\NewContactMessage;
use App\Mail\NewJobApplication;
use App\Mail\NewQuoteRequest;
use App\Mail\QuoteReceived;
use App\Mail\QuoteReply;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use App\Models\QuoteResponse;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends inquiry emails. Submissions are always saved first; every send here is
 * wrapped so a mail failure is logged and never loses or blocks customer data.
 */
class InquiryNotifier
{
    /** @return array<int, string> */
    public function adminRecipients(): array
    {
        // Company email from Settings; if none is set yet, fall back to the active admin accounts.
        $primary = setting('email') ?: null;
        $fallback = $primary ? [] : User::where('role', User::ROLE_ADMIN)->where('is_active', true)->pluck('email')->all();

        return collect([$primary, ...$fallback, ...config('rsquare.notify_emails', [])])
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    public function contactReceived(ContactMessage $message): void
    {
        $this->toAdmin(new NewContactMessage($message), fn () => $message->forceFill(['admin_notified_at' => now()])->save());
    }

    public function quoteReceived(QuoteRequest $quote): void
    {
        $this->toAdmin(new NewQuoteRequest($quote), fn () => $quote->forceFill(['admin_notified_at' => now()])->save());

        try {
            Mail::to($quote->email)->send(new QuoteReceived($quote));
            $quote->forceFill(['customer_notified_at' => now()])->save();
        } catch (Throwable $e) {
            Log::error('Quote confirmation email failed', ['reference' => $quote->reference, 'error' => $e->getMessage()]);
        }
    }

    public function applicationReceived(JobApplication $application): void
    {
        $this->toAdmin(new NewJobApplication($application), fn () => $application->forceFill(['admin_notified_at' => now()])->save());

        try {
            Mail::to($application->email)->send(new ApplicationReceived($application));
            $application->forceFill(['applicant_notified_at' => now()])->save();
        } catch (Throwable $e) {
            Log::error('Application confirmation email failed', ['reference' => $application->reference, 'error' => $e->getMessage()]);
        }
    }

    /** Send an admin's written reply to the customer and record the outcome. */
    public function replyToQuote(QuoteRequest $quote, string $subject, string $body, ?int $userId): QuoteResponse
    {
        $response = $quote->responses()->create([
            'user_id' => $userId,
            'subject' => $subject,
            'body' => $body,
        ]);

        try {
            Mail::to($quote->email)->send(new QuoteReply($quote, $subject, $body));
            $response->forceFill(['sent_at' => now()])->save();
        } catch (Throwable $e) {
            Log::error('Quote reply email failed', ['reference' => $quote->reference, 'error' => $e->getMessage()]);
            $response->forceFill(['error' => mb_substr($e->getMessage(), 0, 500)])->save();
        }

        return $response;
    }

    private function toAdmin(object $mailable, callable $onSuccess): void
    {
        $recipients = $this->adminRecipients();

        if ($recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send($mailable);
            $onSuccess();
        } catch (Throwable $e) {
            Log::error('Admin notification email failed', ['mailable' => $mailable::class, 'error' => $e->getMessage()]);
        }
    }
}
