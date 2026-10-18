<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('pages.projects.index');
    }

    public function show(Project $project): View
    {
        abort_unless($project->is_published, 404);

        $project->load(['category', 'images']);

        // Prefer projects in the same category; top up with the latest others.
        $related = Project::published()->with('category')
            ->where('id', '!=', $project->id)
            ->when($project->project_category_id, fn ($q) => $q->orderByRaw('project_category_id = ? desc', [$project->project_category_id]))
            ->ordered()
            ->take(3)
            ->get();

        return view('pages.projects.show', compact('project', 'related'));
    }
}
