<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Page;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Service;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $recent = collect()
            ->merge(ContactMessage::latest()->take(6)->get()->map(fn ($m) => [
                'type' => 'Message',
                'title' => $m->subject,
                'who' => $m->name,
                'status' => $m->status,
                'at' => $m->created_at,
                'url' => route('admin.messages.show', $m),
            ]))
            ->merge(QuoteRequest::latest()->take(6)->get()->map(fn ($q) => [
                'type' => 'Quote',
                'title' => $q->project_type.' · '.$q->reference,
                'who' => $q->name,
                'status' => $q->status,
                'at' => $q->created_at,
                'url' => route('admin.quotes.show', $q),
            ]))
            ->merge(JobApplication::latest()->take(6)->get()->map(fn ($a) => [
                'type' => 'Application',
                'title' => $a->position.' · '.$a->reference,
                'who' => $a->name,
                'status' => $a->status,
                'at' => $a->created_at,
                'url' => route('admin.applications.show', $a),
            ]))
            ->sortByDesc('at')
            ->take(8)
            ->values();

        return view('admin.dashboard', [
            'stats' => [
                ['Total Projects', Project::count(), 'building', route('admin.projects.index')],
                ['Ongoing Projects', Project::where('status', 'ongoing')->count(), 'cog', route('admin.projects.index', ['status' => 'ongoing'])],
                ['Completed Projects', Project::where('status', 'completed')->count(), 'check-circle', route('admin.projects.index', ['status' => 'completed'])],
                ['Total Services', Service::count(), 'tools', route('admin.services.index')],
                ['New Messages', ContactMessage::where('status', 'new')->count(), 'mail', route('admin.messages.index', ['status' => 'new'])],
                ['Pending Quotes', QuoteRequest::whereIn('status', ['new', 'reviewing'])->count(), 'document', route('admin.quotes.index', ['status' => 'new'])],
                ['New Job Applications', JobApplication::where('status', 'new')->count(), 'users', route('admin.applications.index', ['status' => 'new'])],
                ['Open Job Openings', JobOpening::open()->count(), 'handshake', route('admin.jobs.index')],
            ],
            'recent' => $recent,
            'pages' => $this->pages(),
        ]);
    }

    /** Every page with its visibility, for the one-click Hide/Show buttons on the dashboard. */
    private function pages()
    {
        Page::ensureBuiltinsIfMissing();

        return Page::ordered()->get();
    }
}
