<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Services\InquiryNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CareerController extends Controller
{
    public function index(): View
    {
        return view('pages.careers.index', [
            'jobs' => JobOpening::open()->ordered()->get(),
        ]);
    }

    public function show(JobOpening $job): View
    {
        abort_unless($job->isOpen(), 404);

        return view('pages.careers.show', ['job' => $job]);
    }

    /** General (speculative) application with no specific opening. */
    public function apply(): View
    {
        return view('pages.careers.apply');
    }

    public function store(StoreApplicationRequest $request, InquiryNotifier $notifier): RedirectResponse
    {
        // Honeypot: bots fill hidden fields. Pretend success, store nothing.
        if ($request->filled('website')) {
            return redirect()->route('careers.index')->with('application_reference', 'APP-PENDING');
        }

        $job = $request->filled('job_opening_id') ? JobOpening::find($request->integer('job_opening_id')) : null;

        // Applications to a closed or unpublished opening are not accepted.
        if ($job && ! $job->isOpen()) {
            return redirect()->route('careers.index')->with('error', 'Sorry, that position is no longer open. You can still send us a general application.');
        }

        $data = collect($request->validated())->except(['resume', 'job_opening_id'])->all();

        $file = $request->file('resume');

        // Private disk: resumes are never web-accessible, only via the authenticated admin route.
        $application = JobApplication::create([
            ...$data,
            'job_opening_id' => $job?->id,
            'position' => $job?->title ?? JobApplication::GENERAL,
            'resume_path' => $file->store('resumes', 'local'),
            'resume_name' => mb_substr(preg_replace('/[^\w.\- ]+/u', '_', $file->getClientOriginalName()), 0, 190),
            'ip_address' => $request->ip(),
        ]);

        $notifier->applicationReceived($application);

        return redirect()->route('careers.index')->with('application_reference', $application->reference);
    }
}
