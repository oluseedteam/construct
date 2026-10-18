<x-layouts.public title="About Us" :description="content('about.hero_subtitle').' '.\Illuminate\Support\Str::limit(content('footer.about'), 120)">
    <x-page-hero :title="content('about.hero_title')" :subtitle="content('about.hero_subtitle')" :crumbs="['About Us' => null]" />

    <section class="section">
        <div class="container-x grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div data-reveal="left">
                <p class="eyebrow">{{ content('about.story_eyebrow') }}</p>
                <h2 class="mt-4 text-3xl font-extrabold sm:text-4xl">{{ content('home.about_title') }} <span class="text-brand">{{ content('home.about_title_accent') }}</span></h2>
                <div class="rich mt-6 text-lg text-slate-600">
                    @foreach (paragraphs(content('about.story')) as $p)<p>{{ $p }}</p>@endforeach
                </div>
                @if ($commitments->isNotEmpty())
                    <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                        @foreach ($commitments as $item)
                            <li class="flex items-center gap-3 font-medium text-navy"><span class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-brand text-white"><x-icon name="check" class="size-3.5 !stroke-[3]" /></span>{{ $item->title }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="relative" data-reveal="right">
                <div class="absolute -bottom-5 -left-5 -z-10 size-3/4 rounded-3xl bg-brand/90"></div>
                <div class="overflow-hidden rounded-3xl shadow-lift">
                    <img src="{{ media_url(content('home.about_image'), 'images/demo/flyer-house.jpg') }}" alt="{{ setting('company_name') }} work" loading="lazy" width="900" height="675" class="aspect-[4/3] size-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <section class="section bg-mist">
        <div class="container-x grid gap-6 md:grid-cols-2">
            <div class="card p-8 sm:p-10" data-reveal>
                <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-navy text-brand"><x-icon name="shield" class="size-7" /></span>
                <h2 class="mt-5 text-2xl font-extrabold">{{ content('about.mission_title') }}</h2>
                <p class="mt-3 leading-relaxed text-slate-600">{{ content('about.mission') }}</p>
            </div>
            <div class="card p-8 sm:p-10" data-reveal style="--reveal-delay: 120ms">
                <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-brand text-white"><x-icon name="eye" class="size-7" /></span>
                <h2 class="mt-5 text-2xl font-extrabold">{{ content('about.vision_title') }}</h2>
                <p class="mt-3 leading-relaxed text-slate-600">{{ content('about.vision') }}</p>
            </div>
        </div>
    </section>

    @if ($stats->isNotEmpty())
        <section class="bg-navy" aria-label="Company statistics">
            <div class="container-x">
                <dl class="grid grid-cols-2 gap-y-8 py-12 lg:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="text-center" x-data="counter({{ $stat->value }})" x-intersect.once="start()" data-reveal>
                            <x-icon :name="$stat->icon" class="mx-auto size-9 text-brand" />
                            <dd class="mt-2 whitespace-nowrap font-display text-2xl font-extrabold text-white min-[400px]:text-3xl sm:text-4xl">{{ $stat->prefix }}<span x-text="display">{{ number_format($stat->value) }}</span>{{ $stat->suffix }}</dd>
                            <dt class="text-sm text-white/70">{{ $stat->label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    <x-cta-band />
</x-layouts.public>
