<x-layouts.public title="Our Services" :description="content('services.hero_subtitle')">
    <x-page-hero :title="content('services.hero_title')" :subtitle="content('services.hero_subtitle')" :crumbs="['Services' => null]" />

    <section class="section">
        <div class="container-x">
            @if ($services->isEmpty())
                <p class="text-center text-slate-600">Our services will be listed here shortly.</p>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $i => $service)
                        <article class="group card overflow-hidden transition duration-500 hover:-translate-y-1.5 hover:shadow-lift" data-reveal style="--reveal-delay: {{ ($i % 3) * 100 }}ms">
                            <a href="{{ route('services.show', $service) }}" class="block">
                                <div class="img-zoom relative aspect-[16/10] bg-mist">
                                    <img src="{{ media_url($service->main_image, 'images/demo/demo-1.jpg') }}" alt="{{ $service->name }}" loading="lazy" decoding="async" width="900" height="560" class="size-full object-cover">
                                    <span class="absolute -bottom-6 left-6 inline-flex size-14 items-center justify-center rounded-2xl bg-brand text-white shadow-lg"><x-icon :name="$service->icon" class="size-7" /></span>
                                </div>
                            </a>
                            <div class="p-6 pt-10">
                                <h2 class="text-xl font-bold"><a href="{{ route('services.show', $service) }}" class="hover:text-brand">{{ $service->name }}</a></h2>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $service->description }}</p>
                                <a href="{{ route('services.show', $service) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-navy transition group-hover:gap-3 group-hover:text-brand">{{ content('services.learn_more') }} <x-icon name="arrow-right" class="size-4" /></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-cta-band />
</x-layouts.public>
