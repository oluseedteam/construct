<x-layouts.public title="Why Choose Us" :description="content('why.hero_subtitle').' '.content('why.text')">
    <x-page-hero :title="content('why.hero_title')" :subtitle="content('why.hero_subtitle')" :crumbs="['Why Choose Us' => null]" />

    <section class="section">
        <div class="container-x">
            @if ($why->isEmpty())
                <p class="text-center text-slate-600">{{ content('why.text') }}</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($why as $i => $item)
                        <div class="group card p-8 transition duration-500 hover:-translate-y-1.5 hover:shadow-lift" data-reveal style="--reveal-delay: {{ ($i % 3) * 100 }}ms">
                            <span class="inline-flex size-16 items-center justify-center rounded-2xl bg-navy text-brand transition duration-500 group-hover:rotate-6 group-hover:bg-brand group-hover:text-white"><x-icon :name="$item->icon" class="size-8" /></span>
                            <h2 class="mt-6 text-xl font-bold">{{ $item->title }}</h2>
                            <p class="mt-3 leading-relaxed text-slate-600">{{ $item->text }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($testimonials->isNotEmpty())
        <section class="section bg-mist">
            <div class="container-x">
                <x-section-heading :eyebrow="content('home.testimonials_eyebrow')" :title="content('home.testimonials_title')" :accent="content('home.testimonials_accent')" center />
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($testimonials as $t)
                        <figure class="card p-7" data-reveal>
                            <x-icon name="quote" class="size-8 text-brand" />
                            <blockquote class="mt-4 leading-relaxed text-slate-700">{{ $t->content }}</blockquote>
                            <figcaption class="mt-4 text-sm"><span class="font-bold text-navy">{{ $t->author_name }}</span>@if ($t->author_role)<span class="text-slate-500"> · {{ $t->author_role }}</span>@endif</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band />
</x-layouts.public>
