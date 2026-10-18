<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use App\Support\UniqueSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class JobController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', JobOpening::class);

        return view('admin.jobs.index', ['jobs' => JobOpening::withCount('applications')->ordered()->get()]);
    }

    public function create(): View
    {
        $this->authorize('create', JobOpening::class);

        return view('admin.jobs.form', ['job' => new JobOpening(['employment_type' => 'Full-time', 'is_published' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JobOpening::class);

        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make(JobOpening::class, $data['slug'] ?? $data['title']);
        $job = JobOpening::create($data);
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.jobs.edit', $job)->with('status', 'Job opening created.');
    }

    public function edit(JobOpening $job): View
    {
        $this->authorize('update', $job);

        return view('admin.jobs.form', compact('job'));
    }

    public function update(Request $request, JobOpening $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make(JobOpening::class, $data['slug'] ?? $data['title'], $job->id);
        $job->update($data);
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.jobs.edit', $job)->with('status', 'Job opening updated.');
    }

    public function toggle(JobOpening $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $job->update(['is_published' => ! $job->is_published]);
        Cache::forget('sitemap.xml');

        return back()->with('status', $job->is_published ? 'Job opening published.' : 'Job opening closed (hidden from the website).');
    }

    public function destroy(JobOpening $job): RedirectResponse
    {
        $this->authorize('delete', $job);

        // Applications are kept (their job link is nulled and the position title is preserved).
        $job->delete();
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.jobs.index')->with('status', 'Job opening deleted. Existing applications were kept.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'employment_type' => ['required', 'in:'.implode(',', JobOpening::TYPES)],
            'location' => ['nullable', 'string', 'max:190'],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'closes_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $data['requirements'] = collect(preg_split('/\R/', (string) ($data['requirements'] ?? '')))
            ->map(fn ($line) => trim($line))->filter()->values()->all();
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] ??= 0;

        return $data;
    }
}
