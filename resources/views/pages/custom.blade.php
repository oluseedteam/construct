<x-layouts.public :title="$page->title" :description="$page->meta_description ?: ($page->subtitle ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', (string) $page->content)), 160))">
    <x-page-hero :title="$page->title" :subtitle="$page->subtitle" :crumbs="[$page->title => null]" />

    <section class="section">
        <div class="container-x max-w-3xl">
            @if (filled($page->content))
                <div class="rich text-lg text-slate-600" data-reveal>{!! render_content($page->content) !!}</div>
            @else
                <p class="text-center text-slate-500">This page has no content yet.</p>
            @endif
        </div>
    </section>

    <x-cta-band />
</x-layouts.public>
