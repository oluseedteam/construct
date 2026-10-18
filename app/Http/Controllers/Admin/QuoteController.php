<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Services\InquiryNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', QuoteRequest::class);

        $quotes = QuoteRequest::query()
            ->when($request->query('q'), function ($query, $term) {
                $query->where(fn ($w) => $w
                    ->where('reference', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('project_location', 'like', "%{$term}%"));
            })
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('type'), fn ($q, $type) => $q->where('project_type', $type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    public function show(QuoteRequest $quote): View
    {
        $this->authorize('view', $quote);

        return view('admin.quotes.show', ['quote' => $quote->load('responses.user')]);
    }

    public function update(Request $request, QuoteRequest $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        $quote->update($request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(QuoteRequest::STATUSES))],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]));

        return back()->with('status', 'Quote request updated.');
    }

    public function document(QuoteRequest $quote): StreamedResponse
    {
        $this->authorize('view', $quote);
        abort_unless($quote->document_path && Storage::disk('local')->exists($quote->document_path), 404);

        return Storage::disk('local')->download($quote->document_path, $quote->document_name ?: basename($quote->document_path));
    }

    public function respond(Request $request, QuoteRequest $quote, InquiryNotifier $notifier): RedirectResponse
    {
        $this->authorize('update', $quote);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:190'],
            'body' => ['required', 'string', 'min:10', 'max:8000'],
        ]);

        $response = $notifier->replyToQuote($quote, $data['subject'], $data['body'], $request->user()->id);

        if ($quote->status === 'new') {
            $quote->update(['status' => 'reviewing']);
        }

        return back()->with(
            $response->sent_at ? 'status' : 'error',
            $response->sent_at
                ? 'Reply sent to '.$quote->email.'.'
                : 'Your reply was saved, but the email could not be sent. Check the mail settings (see storage/logs).'
        );
    }

    public function destroy(QuoteRequest $quote): RedirectResponse
    {
        $this->authorize('delete', $quote);

        if ($quote->document_path) {
            Storage::disk('local')->delete($quote->document_path);
        }
        $quote->delete();

        return redirect()->route('admin.quotes.index')->with('status', 'Quote request deleted.');
    }
}
