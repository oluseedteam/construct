<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\ContactMessage;
use App\Services\InquiryNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact');
    }

    public function store(StoreContactRequest $request, InquiryNotifier $notifier): RedirectResponse
    {
        $thanks = content('contact.success');

        // Honeypot: real visitors never see or fill this field. Pretend success, store nothing.
        if ($request->filled('website')) {
            return back()->with('status', $thanks);
        }

        $message = ContactMessage::create([
            ...$request->validated(),
            'ip_address' => $request->ip(),
        ]);

        $notifier->contactReceived($message);

        return redirect()->route('contact')->with('status', $thanks);
    }
}
