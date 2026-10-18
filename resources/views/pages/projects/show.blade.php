<x-layouts.public :title="$project->name" :description="$project->description" :image="media_url($project->featured_image)" type="article">
    <x-page-hero :title="$project->name" :subtitle="$project->location ? 'Location: '.$project->location : null" :crumbs="['Projects' => route('projects.index'), $project->name => null]" />

    <section class="section" x-data="lightbox">
        <div class="container-x grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-3xl shadow-card" data-reveal>
                    <img src="{{ media_url($project->featured_image, 'images/demo/demo-1.jpg') }}" alt="{{ $project->name }}" width="1200" height="800" class="aspect-[3/2] size-full object-cover">
                </div>

                <div class="rich mt-10 text-lg text-slate-600" data-reveal>
                    <p class="font-semibold text-navy">{{ $project->description }}</p>
                    @foreach (paragraphs($project->content) as $p)<p>{{ $p }}</p>@endforeach
                </div>

                @if ($project->images->isNotEmpty())
                    <h2 class="mt-14 text-2xl font-extrabold" data-reveal>Project Gallery</h2>
                    <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3">
                        @foreach ($project->images as $i => $image)
                            <button type="button" data-lightbox data-src="{{ media_url($image->path) }}" data-title="{{ $image->caption ?: $project->name }}" @click="open($el)"
                                    class="group img-zoom aspect-square overflow-hidden rounded-2xl bg-mist shadow-card" data-reveal style="--reveal-delay: {{ ($i % 3) * 80 }}ms" aria-label="View image {{ $i + 1 }} of {{ $project->name }}">
                                <img src="{{ media_url($image->path) }}" alt="{{ $image->caption ?: $project->name.' photo '.($i + 1) }}" loading="lazy" decoding="async" class="size-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                <div class="card p-7" data-reveal="right">
                    <h2 class="text-lg font-bold">Project Details</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Category</dt><dd class="font-semibold text-navy">{{ $project->category?->name ?? 'Other' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><span @class(['badge text-white', 'bg-brand' => $project->status === 'ongoing', 'bg-emerald-600' => $project->status === 'completed', 'bg-navy' => $project->status === 'planned'])>{{ $project->status_label }}</span></dd></div>
                        @if ($project->location)<div class="flex justify-between gap-4"><dt class="text-slate-500">Location</dt><dd class="text-right font-semibold text-navy">{{ $project->location }}</dd></div>@endif
                        @if ($project->start_date)<div class="flex justify-between gap-4"><dt class="text-slate-500">Start date</dt><dd class="font-semibold text-navy">{{ $project->start_date->format('M j, Y') }}</dd></div>@endif
                        @if ($project->completion_date)<div class="flex justify-between gap-4"><dt class="text-slate-500">Completion</dt><dd class="font-semibold text-navy">{{ $project->completion_date->format('M j, Y') }}</dd></div>@endif
                    </dl>
                    @if ($project->is_demo)<p class="mt-5 rounded-lg bg-brand-50 px-3 py-2 text-xs text-brand-700">Sample project for demonstration purposes.</p>@endif
                </div>
                <div class="card bg-navy p-7 !ring-0" data-reveal="right">
                    <h2 class="text-lg font-bold !text-white">{{ content('projects.cta_title') }}</h2>
                    <p class="mt-2 text-sm text-white/75">{{ content('projects.cta_text') }}</p>
                    <a href="{{ route('quote', ['type' => $project->category?->name]) }}" class="btn btn-primary mt-5 w-full">Request a Quote <x-icon name="arrow-right" class="size-4" /></a>
                </div>
            </aside>
        </div>
        <x-lightbox-overlay />
    </section>

    @if ($related->isNotEmpty())
        <section class="section bg-mist">
            <div class="container-x">
                <x-section-heading eyebrow="More Work" title="Related" accent="Projects" />
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $i => $rel)<x-project-card :project="$rel" :delay="$i * 100" />@endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
