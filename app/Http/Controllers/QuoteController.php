<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Models\QuoteRequest;
use App\Services\InquiryNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function show(Request $request): View
    {
        $type = $request->query('type');

        return view('pages.quote', [
            // Allow ?type=Residential style prefill from service/project pages, but only for known types.
            'prefillType' => in_array($type, QuoteRequest::PROJECT_TYPES, true) ? $type : null,
        ]);
    }

    public function store(StoreQuoteRequest $request, InquiryNotifier $notifier): RedirectResponse
    {
        // Honeypot: bots fill hidden fields. Pretend success, store nothing.
        if ($request->filled('website')) {
            return redirect()->route('quote')->with('quote_reference', 'RSQ-'.now()->format('Ymd').'-PENDING');
        }

        $data = collect($request->validated())->except('document')->all();

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            // Private disk: documents are never web-accessible, only via the authenticated admin route.
            $data['document_path'] = $file->store('quote-documents', 'local');
            $data['document_name'] = mb_substr(preg_replace('/[^\w.\- ]+/u', '_', $file->getClientOriginalName()), 0, 190);
        }

        $quote = QuoteRequest::create([...$data, 'ip_address' => $request->ip()]);

        $notifier->quoteReceived($quote);

        return redirect()->route('quote')->with('quote_reference', $quote->reference);
    }
}
