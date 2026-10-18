<x-layouts.public :title="$service->name" :description="$service->description" :image="media_url($service->main_image)">
    <x-page-hero :title="$service->name" :subtitle="$service->description" :crumbs="['Services' => route('services.index'), $service->name => null]" />

    <section class="section">
        <div class="container-x grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-3xl shadow-card" data-reveal>
                    <img src="{{ media_url($service->main_image, 'images/demo/demo-1.jpg') }}" alt="{{ $service->name }}" width="1200" height="750" class="aspect-[16/9] size-full object-cover">
                </div>

                <div class="rich mt-10 text-lg text-slate-600" data-reveal>
                    @foreach (paragraphs($service->content) as $p)<p>{{ $p }}</p>@endforeach
                </div>

                <div class="mt-12 grid gap-8 sm:grid-cols-2">
                    @if (! empty($service->features))
                        <div class="card p-7" data-reveal>
                            <h2 class="text-xl font-bold">What We Do</h2>
                            <ul class="mt-5 space-y-3">
                                @foreach ($service->features as $feature)
                                    <li class="flex gap-3 text-slate-700"><x-icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-brand" />{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (! empty($service->benefits))
                        <div class="card bg-navy p-7 !ring-0" data-reveal style="--reveal-delay: 120ms">
                            <h2 class="text-xl font-bold !text-white">Benefits</h2>
                            <ul class="mt-5 space-y-3">
                                @foreach ($service->benefits as $benefit)
                                    <li class="flex gap-3 text-white/85"><x-icon name="check" class="mt-0.5 size-5 shrink-0 text-brand" />{{ $benefit }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                <div class="card p-7" data-reveal="right">
                    <h2 class="text-lg font-bold">{{ content('services.cta_title') }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ content('services.cta_text') }}</p>
                    <a href="{{ route('quote', ['type' => $service->name]) }}" class="btn btn-primary mt-5 w-full">Request a Quote <x-icon name="arrow-right" class="size-4" /></a>
                    <a href="{{ route('contact') }}" class="btn btn-outline mt-3 w-full">Contact Us</a>
                </div>
                @if ($others->isNotEmpty())
                    <div class="card p-7" data-reveal="right">
                        <h2 class="text-lg font-bold">Other Services</h2>
                        <ul class="mt-4 divide-y divide-line">
                            @foreach ($others as $other)
                                <li><a href="{{ route('services.show', $other) }}" class="group flex items-center justify-between gap-3 py-3 text-sm font-semibold text-navy transition hover:text-brand">{{ $other->name }}<x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    <x-cta-band />
</x-layouts.public>
