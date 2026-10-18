@props(['project', 'delay' => 0])
<article class="group card overflow-hidden transition duration-500 hover:-translate-y-1.5 hover:shadow-lift" data-reveal style="--reveal-delay: {{ $delay }}ms">
    <a href="{{ route('projects.show', $project) }}" class="block">
        <div class="img-zoom relative aspect-[4/3] bg-mist">
            <img src="{{ media_url($project->featured_image, 'images/demo/demo-1.jpg') }}" alt="{{ $project->name }}" loading="lazy" decoding="async" width="900" height="675" class="size-full object-cover">
            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-navy backdrop-blur">{{ $project->category?->name ?? 'Project' }}</span>
            <span @class([
                'absolute right-4 top-4 rounded-full px-3 py-1 text-xs font-semibold text-white',
                'bg-brand' => $project->status === 'ongoing',
                'bg-emerald-600' => $project->status === 'completed',
                'bg-navy' => $project->status === 'planned',
            ])>{{ $project->status_label }}</span>
        </div>
    </a>
    <div class="p-6">
        <h3 class="text-lg font-bold leading-snug"><a href="{{ route('projects.show', $project) }}" class="hover:text-brand">{{ $project->name }}</a></h3>
        @if ($project->location)
            <p class="mt-2 flex items-center gap-1.5 text-sm text-slate-500"><x-icon name="map-pin" class="size-4 text-brand" />{{ $project->location }}</p>
        @endif
        <a href="{{ route('projects.show', $project) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-navy transition group-hover:gap-3 group-hover:text-brand">
            View Project <x-icon name="arrow-right" class="size-4" />
        </a>
    </div>
</article>
