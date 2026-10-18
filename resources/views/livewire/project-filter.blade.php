<div>
    <div class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between" data-reveal>
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filter by category">
            <button type="button" wire:click="setCategory('')" @class(['rounded-full px-5 py-2 text-sm font-semibold transition', 'bg-navy text-white shadow-lg' => $category === '', 'bg-white text-navy ring-1 ring-line hover:ring-brand hover:text-brand' => $category !== ''])>All</button>
            @foreach ($categories as $cat)
                <button type="button" wire:key="cat-{{ $cat->id }}" wire:click="setCategory('{{ $cat->slug }}')" @class(['rounded-full px-5 py-2 text-sm font-semibold transition', 'bg-navy text-white shadow-lg' => $category === $cat->slug, 'bg-white text-navy ring-1 ring-line hover:ring-brand hover:text-brand' => $category !== $cat->slug])>{{ $cat->name }}</button>
            @endforeach
        </div>
        <div class="flex items-center gap-3">
            <label for="status-filter" class="text-sm font-semibold text-navy">Status</label>
            <select id="status-filter" wire:model.live="status" class="input !w-auto !py-2.5">
                <option value="">All</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div wire:loading.delay.class="opacity-50" class="transition-opacity duration-300">
        @if ($projects->isEmpty())
            <div class="card mx-auto max-w-lg p-10 text-center">
                <x-icon name="building" class="mx-auto size-12 text-brand" />
                <h3 class="mt-4 text-xl font-bold">No projects found</h3>
                <p class="mt-2 text-slate-600">{{ $category || $status ? 'Try a different category or status.' : 'Projects will appear here once they are published.' }}</p>
                @if ($category || $status)
                    <button type="button" wire:click="clear" class="btn btn-primary mt-6">Clear filters</button>
                @endif
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $i => $project)
                    <div wire:key="project-{{ $project->id }}"><x-project-card :project="$project" :delay="($i % 3) * 90" /></div>
                @endforeach
            </div>
            <div class="mt-12">{{ $projects->links() }}</div>
        @endif
    </div>
</div>
