<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', JobApplication::class);

        $applications = JobApplication::query()
            ->when($request->query('q'), function ($query, $term) {
                $query->where(fn ($w) => $w
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('position', 'like', "%{$term}%"));
            })
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('job'), function ($q, $job) {
                $job === 'general' ? $q->whereNull('job_opening_id') : $q->where('job_opening_id', $job);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'jobs' => JobOpening::ordered()->get(['id', 'title']),
        ]);
    }

    public function show(JobApplication $application): View
    {
        $this->authorize('view', $application);

        return view('admin.applications.show', ['application' => $application->load('job')]);
    }

    public function update(Request $request, JobApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $application->update($request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(JobApplication::STATUSES))],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]));

        return back()->with('status', 'Application updated.');
    }

    public function resume(JobApplication $application): StreamedResponse
    {
        $this->authorize('view', $application);
        abort_unless(Storage::disk('local')->exists($application->resume_path), 404);

        return Storage::disk('local')->download($application->resume_path, $application->resume_name);
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        $this->authorize('delete', $application);

        Storage::disk('local')->delete($application->resume_path);
        $application->delete();

        return redirect()->route('admin.applications.index')->with('status', 'Application deleted.');
    }
}
