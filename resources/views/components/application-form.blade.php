@props(['job' => null])
<form method="POST" action="{{ route('careers.store') }}" enctype="multipart/form-data" class="card space-y-5 p-6 sm:p-9" novalidate x-data="{ sending: false }" @submit="sending = true" id="apply">
    @csrf
    @if ($job)<input type="hidden" name="job_opening_id" value="{{ $job->id }}">@endif
    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
        <label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div>
        <h2 class="text-2xl font-extrabold">{{ content('careers.form_title') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $job ? 'Applying for: '.$job->title : 'General application. Tell us what kind of work you are looking for.' }}</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-field name="name" label="Full Name" required placeholder="Your full name" />
        <x-field name="email" type="email" label="Email" required placeholder="you@example.com" />
        <x-field name="phone" type="tel" label="Phone Number" required placeholder="437-000-0000" />
        <x-field name="city" label="City" placeholder="Where do you live?" />
        <x-field name="experience" type="select" label="Years of experience" :options="\App\Models\JobApplication::EXPERIENCE" placeholder="Select…" />
        <x-field name="available_from" type="date" label="Available from" />
        <x-field name="profile_url" type="url" label="LinkedIn or portfolio link (optional)" placeholder="https://" class="sm:col-span-2" />
        <x-field name="cover_letter" type="textarea" :rows="6" :label="$job ? 'Why are you a good fit?' : 'Tell us about yourself and the work you do'" placeholder="Share your experience, skills and anything else we should know…" class="sm:col-span-2" />
        <x-field name="resume" type="file" label="Resume / CV" required accept=".pdf,.doc,.docx" hint="PDF or Word document, up to 5 MB." class="sm:col-span-2" />
    </div>

    <button type="submit" class="btn btn-primary w-full px-8 py-3.5 sm:w-auto" :disabled="sending">
        <span x-show="!sending" class="inline-flex items-center gap-2">Submit Application <x-icon name="arrow-right" class="size-4" /></span>
        <span x-show="sending" x-cloak>Submitting…</span>
    </button>
    <p class="text-xs text-slate-500">We use your details only to consider your application.</p>
</form>
