<x-layouts.public title="Request a Quote" :description="content('quote.hero_subtitle')">
    <x-page-hero :title="content('quote.hero_title')" :subtitle="content('quote.hero_subtitle')" :crumbs="['Request a Quote' => null]" />

    <section class="section bg-mist">
        <div class="container-x max-w-4xl">
            @if (session('quote_reference'))
                <div class="card p-8 text-center sm:p-12" data-reveal>
                    <span class="mx-auto inline-flex size-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><x-icon name="check" class="size-9 !stroke-[2.5]" /></span>
                    <h2 class="mt-6 text-3xl font-extrabold">{{ content('quote.success_title') }}</h2>
                    <p class="mt-3 text-slate-600">{{ content('quote.success_text') }}</p>
                    <div class="mx-auto mt-8 max-w-sm rounded-2xl bg-navy px-6 py-5 text-white">
                        <p class="text-xs uppercase tracking-widest text-white/60">Your reference number</p>
                        <p class="mt-1 font-display text-2xl font-extrabold tracking-wide" data-testid="quote-reference">{{ session('quote_reference') }}</p>
                    </div>
                    <p class="mt-5 text-sm text-slate-500">Keep this number handy if you contact us about your request.</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('home') }}" class="btn btn-navy">Back to Home</a>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline">Explore Our Projects</a>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('quote.store') }}" enctype="multipart/form-data" class="card space-y-6 p-6 sm:p-10" novalidate x-data="{ sending: false }" @submit="sending = true">
                    @csrf
                    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                        <label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <div>
                        <h2 class="text-xl font-extrabold">About you</h2>
                        <div class="mt-4 grid gap-5 sm:grid-cols-2">
                            <x-field name="name" label="Customer Name" required placeholder="Your full name" />
                            <x-field name="email" type="email" label="Email" required placeholder="you@example.com" />
                            <x-field name="phone" type="tel" label="Phone Number" required placeholder="+1 555 000 0000" class="sm:col-span-2" />
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-extrabold">About the project</h2>
                        <div class="mt-4 grid gap-5 sm:grid-cols-2">
                            <x-field name="project_type" type="select" label="Project Type" required :options="\App\Models\QuoteRequest::PROJECT_TYPES" :value="$prefillType" placeholder="Select project type" />
                            <x-field name="project_location" label="Project Location" required placeholder="City / area" />
                            <x-field name="estimated_budget" type="select" label="Estimated Budget" :options="\App\Models\QuoteRequest::BUDGETS" placeholder="Select a range" />
                            <x-field name="preferred_start_date" type="date" label="Preferred Start Date" />
                            <x-field name="description" type="textarea" :rows="6" label="Project Description" required placeholder="Describe the scope, size and any special requirements…" class="sm:col-span-2" />
                            <x-field name="document" type="file" label="Supporting Document (optional)" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" hint="Plans, drawings or photos. PDF, Word, JPG or PNG up to 8 MB." class="sm:col-span-2" />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 border-t border-line pt-6">
                        <button type="submit" class="btn btn-primary px-8 py-3.5" :disabled="sending">
                            <span x-show="!sending" class="inline-flex items-center gap-2">Submit Request <x-icon name="arrow-right" class="size-4" /></span>
                            <span x-show="sending" x-cloak>Submitting…</span>
                        </button>
                        <p class="text-xs text-slate-500">We use your details only to respond to your request.</p>
                    </div>
                </form>
            @endif
        </div>
    </section>
</x-layouts.public>
