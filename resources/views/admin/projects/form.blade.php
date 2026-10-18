@php $editing = $project->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit project' : 'New project'">
    <a href="{{ route('admin.projects.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All projects</a>

    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $editing ? route('admin.projects.update', $project) : route('admin.projects.store') }}" class="grid gap-8 xl:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-6 xl:col-span-2">
            <div class="card space-y-5 p-6 sm:p-8">
                <h2 class="text-lg font-bold">Project details</h2>
                <x-field name="name" label="Project name" :value="$project->name" required />
                <x-field name="slug" label="URL slug" :value="$project->slug" hint="Leave blank to generate from the name. Lowercase letters, numbers and dashes." />
                <x-field name="description" type="textarea" :rows="3" label="Short description" :value="$project->description" required hint="Shown on cards and in search results (max 1000 characters)." />
                <x-field name="content" type="textarea" :rows="9" label="Full description" :value="$project->content" hint="Separate paragraphs with a blank line." />
            </div>

            <div class="card space-y-5 p-6 sm:p-8">
                <h2 class="text-lg font-bold">Images</h2>
                <x-field name="featured_image" type="file" label="Featured image" accept="image/*" hint="JPG, PNG or WebP up to 5 MB. Replaces the current image." />
                @if ($project->featured_image)
                    <img src="{{ media_url($project->featured_image) }}" alt="Current featured image" class="h-40 w-auto rounded-xl object-cover ring-1 ring-line">
                @endif
                <x-field name="images" type="file" :multiple="true" label="Add gallery images" accept="image/*" hint="Select several files at once (up to 20)." />
                @error('images.*')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="space-y-6">
            <div class="card space-y-5 p-6 sm:p-8">
                <h2 class="text-lg font-bold">Publishing</h2>
                <x-field name="project_category_id" type="select" label="Category" :options="$categories->pluck('name', 'id')->all()" :value="$project->project_category_id" placeholder="Uncategorised" />
                <x-field name="status" type="select" label="Project status" :options="\App\Models\Project::STATUSES" :value="$project->status" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field name="start_date" type="date" label="Start date" :value="$project->start_date?->format('Y-m-d')" />
                    <x-field name="completion_date" type="date" label="Completion date" :value="$project->completion_date?->format('Y-m-d')" />
                </div>
                <x-field name="location" label="Location" :value="$project->location" />
                <x-field name="sort_order" type="number" label="Display order" :value="$project->sort_order ?? 0" hint="Lower numbers appear first." />
                <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $project->is_featured)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Featured on the homepage</label>
                <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $project->is_published)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Published (visible on the website)</label>
                <button class="btn btn-primary w-full">{{ $editing ? 'Save changes' : 'Create project' }}</button>
            </div>
        </div>
    </form>

    @if ($editing && $project->images->isNotEmpty())
        <section class="card mt-8 p-6 sm:p-8" aria-labelledby="gallery-title">
            <h2 id="gallery-title" class="text-lg font-bold">Gallery images ({{ $project->images->count() }})</h2>
            <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($project->images as $image)
                    <form method="POST" action="{{ route('admin.projects.images.destroy', [$project, $image]) }}" onsubmit="return confirm('Remove this image?')" class="group relative">
                        @csrf @method('DELETE')
                        <img src="{{ media_url($image->path) }}" alt="Gallery image {{ $loop->iteration }}" class="aspect-square w-full rounded-xl object-cover ring-1 ring-line" loading="lazy">
                        <button class="absolute right-2 top-2 inline-flex size-9 items-center justify-center rounded-full bg-red-600 text-white opacity-100 shadow-lg transition hover:bg-red-700 sm:opacity-0 sm:group-hover:opacity-100 sm:focus:opacity-100" aria-label="Remove image {{ $loop->iteration }}"><x-icon name="trash" class="size-4" /></button>
                    </form>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.admin>
