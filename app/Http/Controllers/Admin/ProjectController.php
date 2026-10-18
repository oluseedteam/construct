<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectImage;
use App\Support\MediaStorage;
use App\Support\UniqueSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::with('category')
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('location', 'like', "%{$term}%")))
            ->when($request->query('category'), fn ($q, $id) => $q->where('project_category_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('admin.projects.index', [
            'projects' => $projects,
            'categories' => ProjectCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('admin.projects.form', [
            'project' => new Project(['status' => 'planned', 'is_published' => true]),
            'categories' => ProjectCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make(Project::class, $data['slug'] ?? $data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] ??= 0;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = MediaStorage::storeImage($request->file('featured_image'), 'projects');
        }

        $project = Project::create($data);
        $this->storeGallery($request, $project);
        $this->flush();

        return redirect()->route('admin.projects.edit', $project)->with('status', 'Project created.');
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('admin.projects.form', [
            'project' => $project->load('images'),
            'categories' => ProjectCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make(Project::class, $data['slug'] ?? $data['name'], $project->id);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] ??= 0;

        if ($request->hasFile('featured_image')) {
            MediaStorage::delete($project->featured_image);
            $data['featured_image'] = MediaStorage::storeImage($request->file('featured_image'), 'projects');
        }

        $project->update($data);
        $this->storeGallery($request, $project);
        $this->flush();

        return redirect()->route('admin.projects.edit', $project)->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        MediaStorage::delete($project->featured_image);
        MediaStorage::deleteMany($project->images->pluck('path'));
        $project->delete();
        $this->flush();

        return redirect()->route('admin.projects.index')->with('status', 'Project deleted.');
    }

    public function destroyImage(Project $project, ProjectImage $image): RedirectResponse
    {
        $this->authorize('update', $project);
        abort_unless($image->project_id === $project->id, 404);

        MediaStorage::delete($image->path);
        $image->delete();

        return back()->with('status', 'Image removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'status' => ['required', 'in:'.implode(',', array_keys(Project::STATUSES))],
            'location' => ['nullable', 'string', 'max:190'],
            'start_date' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['required', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:20000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'featured_image' => ['nullable', ...MediaStorage::IMAGE_RULES],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => MediaStorage::IMAGE_RULES,
        ]);

        // Uploads are stored separately; keep file objects out of the model data.
        unset($data['featured_image'], $data['images']);

        return $data;
    }

    private function storeGallery(Request $request, Project $project): void
    {
        $next = (int) $project->images()->max('sort_order') + 1;

        foreach ($request->file('images', []) as $file) {
            $project->images()->create([
                'path' => MediaStorage::storeImage($file, 'projects/gallery'),
                'sort_order' => $next++,
            ]);
        }
    }

    private function flush(): void
    {
        Cache::forget('sitemap.xml');
    }
}
