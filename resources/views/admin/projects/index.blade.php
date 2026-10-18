<x-layouts.admin title="Projects">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex flex-1 flex-wrap items-center gap-3" role="search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search projects…" class="input !w-64" aria-label="Search projects">
            <select name="category" class="input !w-auto" aria-label="Category" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <select name="status" class="input !w-auto" aria-label="Status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Project::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
            </select>
            <button class="btn btn-navy btn-sm">Search</button>
        </form>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add project</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line">
                <thead class="bg-mist"><tr><th class="th">Project</th><th class="th">Category</th><th class="th">Status</th><th class="th">Visibility</th><th class="th text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @forelse ($projects as $project)
                        <tr class="hover:bg-mist/60">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <img src="{{ media_url($project->featured_image, 'images/demo/demo-1.jpg') }}" alt="" class="size-12 rounded-lg object-cover" loading="lazy">
                                    <div>
                                        <p class="font-semibold text-navy">{{ $project->name }}@if ($project->is_featured)<span class="ml-2 badge bg-brand text-white">Featured</span>@endif @if ($project->is_demo)<span class="ml-1 badge bg-slate-200 text-slate-600">Demo</span>@endif</p>
                                        <p class="text-xs text-slate-500">{{ $project->location ?: 'No location' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td">{{ $project->category?->name ?? '—' }}</td>
                            <td class="td"><x-status-badge :status="$project->status" /></td>
                            <td class="td"><x-status-badge :status="$project->is_published ? 'published' : 'draft'" /></td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    @if ($project->is_published)<a href="{{ route('projects.show', $project) }}" target="_blank" class="btn btn-outline btn-sm" aria-label="View {{ $project->name }} on site"><x-icon name="eye" class="size-4" /></a>@endif
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-navy btn-sm"><x-icon name="pencil" class="size-4" /> Edit</a>
                                    @can('delete', $project)
                                        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Delete this project and its images? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" aria-label="Delete {{ $project->name }}"><x-icon name="trash" class="size-4" /></button></form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-14 text-center text-slate-500">No projects found. <a href="{{ route('admin.projects.create') }}" class="font-semibold text-brand hover:underline">Add your first project</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $projects->links() }}</div>
</x-layouts.admin>
