<x-layouts.public>

    {{-- ============ Hero ============ --}}
    @php $heroPath = content('home.hero_image'); $isDefaultHero = $heroPath === 'images/hero.jpg'; @endphp
    <section class="relative isolate overflow-hidden bg-sky-100" aria-labelledby="hero-title">
        <img src="{{ media_url($heroPath, 'images/hero.jpg') }}"
             @if ($isDefaultHero)
                srcset="{{ asset('images/hero-768.jpg') }} 768w, {{ asset('images/hero-1280.jpg') }} 1280w, {{ asset('images/hero.jpg') }} 1774w"
                sizes="100vw"
             @endif
             alt="" width="1774" height="887" fetchpriority="high" decoding="async"
             class="absolute inset-0 -z-20 size-full origin-center animate-kenburns object-cover object-[72%_center] sm:object-[68%_center] lg:object-center">
        {{-- Readability overlays: white wash on the text side, soft navy at the base --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-white/90 via-white/70 to-white/20 lg:bg-gradient-to-r lg:from-white/95 lg:via-white/70 lg:to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 -z-10 h-40 bg-gradient-to-t from-navy/25 to-transparent"></div>

        <div class="container-x relative pb-52 pt-32 sm:pt-36 lg:pb-72 lg:pt-44">
            <div class="max-w-4xl">
                <p class="eyebrow animate-fade-up">{{ content('home.hero_eyebrow') }}</p>
                <h1 id="hero-title" class="mt-5 font-display text-[2.5rem] font-black leading-[1.05] tracking-tight sm:text-6xl lg:text-[3.75rem]">
                    <span class="block animate-fade-up !text-navy [animation-delay:100ms]">{{ content('home.hero_title') }}</span>
                    <span class="block animate-fade-up text-brand [animation-delay:220ms]">{{ content('home.hero_title_accent') }}</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-700 animate-fade-up [animation-delay:340ms]">{{ content('home.hero_text') }}</p>
                <div class="mt-9 flex flex-wrap items-center gap-4 animate-fade-up [animation-delay:460ms]">
                    <a href="{{ route('quote') }}" class="btn btn-primary px-7 py-3.5">{{ content('home.hero_btn_primary') }} <x-icon name="arrow-right" class="size-4" /></a>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline px-7 py-3.5">{{ content('home.hero_btn_secondary') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Services (overlapping the hero) ============ --}}
    @if ($services->isNotEmpty())
        <section class="relative z-10 -mt-44 lg:-mt-60" aria-labelledby="services-title">
            <h2 id="services-title" class="sr-only">Our Services</h2>
            <div class="container-x">
                <div class="flex flex-wrap justify-center gap-4">
                    @foreach ($services as $i => $service)
                        <a href="{{ route('services.show', $service) }}" data-reveal style="--reveal-delay: {{ ($i % 3) * 90 }}ms"
                           class="group glass flex w-full items-center gap-4 rounded-2xl p-5 transition duration-500 hover:-translate-y-1.5 hover:bg-navy hover:shadow-lift sm:w-[calc(50%-0.5rem)] lg:w-[calc(33.333%-0.7rem)]">
                            <span class="inline-flex size-14 shrink-0 items-center justify-center rounded-2xl bg-navy text-brand transition group-hover:bg-brand group-hover:text-white">
                                <x-icon :name="$service->icon" class="size-7" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-display text-base font-bold leading-snug text-navy transition group-hover:text-white">{{ $service->name }}</span>
                                <span class="mt-1 block text-sm leading-snug text-slate-600 transition group-hover:text-white/75">{{ $service->description }}</span>
                            </span>
                            <x-icon name="arrow-right" class="size-5 shrink-0 text-navy/40 transition group-hover:translate-x-1 group-hover:text-brand" />
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ Statistics (only real, admin-entered figures) ============ --}}
    @if ($stats->isNotEmpty())
        <section class="mt-14 bg-navy" aria-label="Company statistics">
            <div class="container-x">
                @php $statCols = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'][min($stats->count(), 4)]; @endphp
                <dl class="grid grid-cols-2 gap-y-8 py-10 {{ $statCols }} lg:divide-x lg:divide-white/10">
                    @foreach ($stats as $stat)
                        <div class="flex flex-col items-center justify-center gap-2 px-2 text-center sm:flex-row sm:gap-4 sm:px-4 sm:text-left" x-data="counter({{ $stat->value }})" x-intersect.once="start()" data-reveal>
                            <x-icon :name="$stat->icon" class="size-8 shrink-0 text-brand sm:size-10" />
                            <div>
                                <dd class="whitespace-nowrap font-display text-xl font-extrabold text-white min-[400px]:text-3xl sm:text-4xl">{{ $stat->prefix }}<span x-text="display">{{ number_format($stat->value) }}</span>{{ $stat->suffix }}</dd>
                                <dt class="text-xs text-white/70 sm:text-sm">{{ $stat->label }}</dt>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    {{-- ============ About / Approach ============ --}}
    <section class="section {{ $stats->isEmpty() ? 'mt-6' : '' }} overflow-hidden" aria-labelledby="about-title">
        <div class="container-x grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div data-reveal="left">
                <p class="eyebrow">{{ content('home.about_eyebrow') }}</p>
                <h2 id="about-title" class="mt-4 text-4xl font-extrabold leading-tight sm:text-5xl">
                    {{ content('home.about_title') }}<br><span class="text-brand">{{ content('home.about_title_accent') }}</span>
                </h2>
                <p class="mt-6 text-lg leading-relaxed text-slate-600">{{ content('home.about_text') }}</p>
                @if ($commitments->isNotEmpty())
                    <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                        @foreach ($commitments as $item)
                            <li class="flex items-center gap-3 font-medium text-navy">
                                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-brand text-white"><x-icon name="check" class="size-3.5 !stroke-[3]" /></span>{{ $item->title }}
                            </li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('about') }}" class="btn btn-primary mt-10">{{ content('home.about_btn') }} <x-icon name="arrow-right" class="size-4" /></a>
            </div>

            <div class="relative" data-reveal="right">
                <div class="absolute -bottom-5 -right-5 -z-10 size-3/4 rounded-3xl bg-brand/90"></div>
                <div class="img-zoom group overflow-hidden rounded-3xl shadow-lift">
                    <img src="{{ media_url(content('home.about_image'), 'images/demo/flyer-house.jpg') }}" alt="{{ setting('company_name') }} work" loading="lazy" width="900" height="675" class="aspect-[4/3] size-full object-cover">
                </div>
                <div class="glass absolute -bottom-6 left-4 hidden rounded-2xl px-5 py-4 sm:block">
                    <p class="font-display text-sm font-extrabold uppercase tracking-wider text-navy">{{ setting('tagline') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Featured Projects ============ --}}
    @if ($projects->isNotEmpty())
        <section class="section bg-mist" aria-labelledby="projects-title">
            <div class="container-x">
                <div class="mb-10 flex flex-wrap items-end justify-between gap-6 sm:mb-14">
                    <div data-reveal>
                        <p class="eyebrow">{{ content('home.projects_eyebrow') }}</p>
                        <h2 id="projects-title" class="mt-3 text-3xl font-extrabold sm:text-4xl lg:text-[2.6rem]">{{ content('home.projects_title') }} <span class="text-brand">{{ content('home.projects_accent') }}</span></h2>
                    </div>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline" data-reveal>{{ content('home.projects_btn') }} <x-icon name="arrow-right" class="size-4" /></a>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $i => $project)
                        <x-project-card :project="$project" :delay="$i * 100" />
                    @endforeach
                </div>
                @if ($projects->contains('is_demo', true))
                    <p class="mt-6 text-center text-xs text-slate-400">Sample projects shown for demonstration. Replace them from the admin dashboard.</p>
                @endif
            </div>
        </section>
    @endif

    {{-- ============ Why Choose Us ============ --}}
    @if ($why->isNotEmpty())
        <section class="section" aria-labelledby="why-title">
            <div class="container-x">
                <x-section-heading id="why-title" :eyebrow="content('why.eyebrow')" :title="content('why.title')" :accent="content('why.accent')" :text="content('why.text')" center />
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($why as $i => $item)
                        <div class="group card p-7 transition duration-500 hover:-translate-y-1.5 hover:shadow-lift" data-reveal style="--reveal-delay: {{ ($i % 3) * 100 }}ms">
                            <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-navy text-brand transition duration-500 group-hover:rotate-6 group-hover:bg-brand group-hover:text-white"><x-icon :name="$item->icon" class="size-7" /></span>
                            <h3 class="mt-5 text-lg font-bold">{{ $item->title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $item->text }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ Testimonials (admin-approved only) ============ --}}
    @if ($testimonials->isNotEmpty())
        <section class="section bg-navy" aria-labelledby="testi-title">
            <div class="container-x">
                <x-section-heading id="testi-title" :eyebrow="content('home.testimonials_eyebrow')" :title="content('home.testimonials_title')" :accent="content('home.testimonials_accent')" center light />
                <div class="mx-auto max-w-3xl" x-data="carousel({{ $testimonials->count() }})" @mouseenter="stop()" @mouseleave="play()" data-reveal>
                    <div class="relative min-h-[16rem]">
                        @foreach ($testimonials as $i => $t)
                            <figure x-show="index === {{ $i }}" x-cloak x-transition.opacity.duration.500ms class="glass rounded-3xl p-8 text-center sm:p-10" @if ($i) style="display:none" @endif>
                                <x-icon name="quote" class="mx-auto size-9 text-brand" />
                                <blockquote class="mt-5 text-lg leading-relaxed text-navy sm:text-xl">{{ $t->content }}</blockquote>
                                <div class="mt-5 flex justify-center gap-1 text-brand" aria-label="{{ $t->rating }} out of 5 stars">
                                    @for ($s = 0; $s < $t->rating; $s++)<x-icon name="star" class="size-4" />@endfor
                                </div>
                                <figcaption class="mt-3"><span class="font-bold text-navy">{{ $t->author_name }}</span>@if ($t->author_role)<span class="text-slate-500"> · {{ $t->author_role }}</span>@endif</figcaption>
                            </figure>
                        @endforeach
                    </div>
                    @if ($testimonials->count() > 1)
                        <div class="mt-6 flex items-center justify-center gap-4">
                            <button type="button" @click="prev()" class="inline-flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-brand" aria-label="Previous testimonial"><x-icon name="chevron-left" class="size-5" /></button>
                            <div class="flex gap-2">
                                @foreach ($testimonials as $i => $t)
                                    <button type="button" @click="index = {{ $i }}" class="h-2.5 rounded-full transition-all" :class="index === {{ $i }} ? 'w-8 bg-brand' : 'w-2.5 bg-white/40'" aria-label="Show testimonial {{ $i + 1 }}"></button>
                                @endforeach
                            </div>
                            <button type="button" @click="next()" class="inline-flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-brand" aria-label="Next testimonial"><x-icon name="chevron-right" class="size-5" /></button>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <x-cta-band />
</x-layouts.public>
