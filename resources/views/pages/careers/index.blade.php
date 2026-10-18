<x-layouts.public title="Careers" :description="content('careers.hero_subtitle').' '.content('careers.intro_text')">
    <x-page-hero :title="content('careers.hero_title')" :subtitle="content('careers.hero_subtitle')" :crumbs="['Careers' => null]" />

    <section class="section">
        <div class="container-x max-w-5xl">
            <x-flash />

            @if (session('application_reference'))
                <div class="card mb-12 p-8 text-center sm:p-12" data-reveal>
                    <span class="mx-auto inline-flex size-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><x-icon name="check" class="size-9 !stroke-[2.5]" /></span>
                    <h2 class="mt-6 text-3xl font-extrabold">{{ content('careers.success_title') }}</h2>
                    <p class="mx-auto mt-3 max-w-lg text-slate-600">{{ content('careers.success_text') }}</p>
                    <div class="mx-auto mt-8 max-w-sm rounded-2xl bg-navy px-6 py-5 text-white">
                        <p class="text-xs uppercase tracking-widest text-white/60">Your reference number</p>
                        <p class="mt-1 font-display text-2xl font-extrabold tracking-wide" data-testid="application-reference">{{ session('application_reference') }}</p>
                    </div>
                </div>
            @endif

            <div class="mb-10 max-w-2xl" data-reveal>
                <p class="eyebrow">{{ content('careers.hero_title') }}</p>
                <h2 class="mt-3 text-3xl font-extrabold sm:text-4xl">{{ content('careers.intro_title') }}</h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-600">{{ content('careers.intro_text') }}</p>
            </div>

            @if ($jobs->isEmpty())
                <div class="card p-8 text-center sm:p-12" data-reveal>
                    <x-icon name="users" class="mx-auto size-12 text-brand" />
                    <h3 class="mt-4 text-xl font-bold">No open positions right now</h3>
                    <p class="mx-auto mt-2 max-w-lg text-slate-600">{{ content('careers.empty_text') }}</p>
                    <a href="{{ route('careers.apply') }}" class="btn btn-primary mt-6">Send a general application <x-icon name="arrow-right" class="size-4" /></a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($jobs as $i => $job)
                        <a href="{{ route('careers.show', $job) }}" class="group card flex flex-wrap items-center justify-between gap-4 p-6 transition duration-500 hover:-translate-y-1 hover:shadow-lift" data-reveal style="--reveal-delay: {{ $i * 80 }}ms">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-xl font-bold transition group-hover:text-brand">{{ $job->title }}</h3>
                                <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $job->summary }}</p>
                                <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-semibold text-slate-500">
                                    <span class="badge bg-brand-50 text-brand-700">{{ $job->employment_type }}</span>
                                    @if ($job->location)<span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="size-3.5 text-brand" />{{ $job->location }}</span>@endif
                                    @if ($job->closes_at)<span>Apply by {{ $job->closes_at->format('M j, Y') }}</span>@endif
                                </p>
                            </div>
                            <span class="btn btn-navy btn-sm">View &amp; apply <x-icon name="arrow-right" class="size-4" /></span>
                        </a>
                    @endforeach
                </div>
                <p class="mt-10 text-center text-slate-600">Don't see the right role? <a href="{{ route('careers.apply') }}" class="font-semibold text-brand hover:underline">Send us a general application</a>.</p>
            @endif
        </div>
    </section>
</x-layouts.public>
