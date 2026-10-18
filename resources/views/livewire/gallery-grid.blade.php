<div>
    @if ($categories->isNotEmpty())
        <div class="mb-10 flex flex-wrap justify-center gap-2" role="group" aria-label="Filter gallery by category">
            <button type="button" wire:click="setCategory('')" @class(['rounded-full px-5 py-2 text-sm font-semibold transition', 'bg-navy text-white shadow-lg' => $category === '', 'bg-white text-navy ring-1 ring-line hover:ring-brand hover:text-brand' => $category !== ''])>All</button>
            @foreach ($categories as $cat)
                <button type="button" wire:key="gcat-{{ $cat->id }}" wire:click="setCategory('{{ $cat->slug }}')" @class(['rounded-full px-5 py-2 text-sm font-semibold transition', 'bg-navy text-white shadow-lg' => $category === $cat->slug, 'bg-white text-navy ring-1 ring-line hover:ring-brand hover:text-brand' => $category !== $cat->slug])>{{ $cat->name }}</button>
            @endforeach
        </div>
    @endif

    @if ($images->isEmpty())
        <div class="card mx-auto max-w-lg p-10 text-center">
            <x-icon name="photo" class="mx-auto size-12 text-brand" />
            <h3 class="mt-4 text-xl font-bold">No images yet</h3>
            <p class="mt-2 text-slate-600">Gallery photos will appear here once they are uploaded.</p>
        </div>
    @else
        <div class="columns-1 gap-4 sm:columns-2 lg:columns-3" wire:loading.delay.class="opacity-50">
            @foreach ($images as $image)
                <button type="button" wire:key="img-{{ $image->id }}" data-lightbox data-src="{{ media_url($image->path) }}" data-title="{{ $image->title }}" @click="open($el)"
                        class="group img-zoom relative mb-4 block w-full break-inside-avoid overflow-hidden rounded-2xl bg-mist shadow-card" aria-label="View {{ $image->title ?: 'gallery image' }}">
                    <img src="{{ media_url($image->path) }}" alt="{{ $image->title ?: 'R-Square Construction gallery image' }}" loading="lazy" decoding="async" class="w-full object-cover">
                    <span class="absolute inset-0 flex items-end bg-gradient-to-t from-navy/70 via-transparent to-transparent p-4 opacity-0 transition duration-300 group-hover:opacity-100">
                        <span class="text-sm font-semibold text-white">{{ $image->title ?: ($image->category?->name ?? 'View') }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    @endif
</div>
