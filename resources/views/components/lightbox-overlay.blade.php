{{-- Must sit inside an element using x-data="lightbox" --}}
<div x-show="current !== null" x-cloak x-transition.opacity.duration.200ms
     @keydown.escape.window="close()" @keydown.arrow-right.window="current !== null && next()" @keydown.arrow-left.window="current !== null && prev()"
     class="fixed inset-0 z-[90] flex items-center justify-center bg-navy-900/95 p-4 backdrop-blur-sm"
     role="dialog" aria-modal="true" aria-label="Image viewer" @click.self="close()">
    <button type="button" @click="close()" class="absolute right-4 top-4 inline-flex size-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-brand" aria-label="Close"><x-icon name="x" class="size-6" /></button>
    <button type="button" @click="prev()" class="absolute left-3 top-1/2 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-brand sm:left-6" aria-label="Previous image"><x-icon name="chevron-left" class="size-6" /></button>
    <button type="button" @click="next()" class="absolute right-3 top-1/2 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-brand sm:right-6" aria-label="Next image"><x-icon name="chevron-right" class="size-6" /></button>
    <figure class="max-h-full max-w-5xl">
        <img :src="item.src" :alt="item.title || 'Gallery image'" class="max-h-[80vh] w-auto rounded-xl object-contain shadow-2xl">
        <figcaption class="mt-3 text-center text-sm text-white/80" x-text="item.title"></figcaption>
    </figure>
</div>
