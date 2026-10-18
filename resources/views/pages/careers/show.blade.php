<x-layouts.public :title="$job->title.' (Careers)'" :description="$job->summary" type="article">
    <x-page-hero :title="$job->title" :subtitle="$job->summary" :crumbs="['Careers' => route('careers.index'), $job->title => null]" />

    <section class="section">
        <div class="container-x grid gap-12 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <div class="card p-7" data-reveal>
                    <h2 class="text-lg font-bold">Position details</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Type</dt><dd class="font-semibold text-navy">{{ $job->employment_type }}</dd></div>
                        @if ($job->location)<div class="flex justify-between gap-4"><dt class="text-slate-500">Location</dt><dd class="text-right font-semibold text-navy">{{ $job->location }}</dd></div>@endif
                        @if ($job->closes_at)<div class="flex justify-between gap-4"><dt class="text-slate-500">Apply by</dt><dd class="font-semibold text-navy">{{ $job->closes_at->format('M j, Y') }}</dd></div>@endif
                    </dl>
                </div>

                <div class="rich mt-8 text-slate-600" data-reveal>
                    @foreach (paragraphs($job->description) as $p)<p>{{ $p }}</p>@endforeach
                </div>

                @if (! empty($job->requirements))
                    <div class="mt-8" data-reveal>
                        <h2 class="text-xl font-bold">Requirements</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach ($job->requirements as $req)
                                <li class="flex gap-3 text-slate-700"><x-icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-brand" />{{ $req }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3" data-reveal="right">
                <x-application-form :job="$job" />
            </div>
        </div>
    </section>
</x-layouts.public>
