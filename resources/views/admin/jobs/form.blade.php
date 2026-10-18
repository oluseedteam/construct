@php $editing = $job->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit job opening' : 'New job opening'">
    <a href="{{ route('admin.jobs.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All job openings</a>

    <form method="POST" novalidate class="grid gap-8 xl:grid-cols-3" action="{{ $editing ? route('admin.jobs.update', $job) : route('admin.jobs.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="card space-y-5 p-6 sm:p-8 xl:col-span-2">
            <h2 class="text-lg font-bold">Position</h2>
            <x-field name="title" label="Job title" :value="$job->title" required />
            <x-field name="slug" label="URL slug" :value="$job->slug" hint="Leave blank to generate from the title." />
            <x-field name="summary" type="textarea" :rows="3" label="Short summary" :value="$job->summary" required hint="Shown on the careers list (max 500 characters)." />
            <x-field name="description" type="textarea" :rows="9" label="Full description" :value="$job->description" hint="Separate paragraphs with a blank line." />
            <x-field name="requirements" type="textarea" :rows="6" label="Requirements" :value="implode(PHP_EOL, $job->requirements ?? [])" hint="One requirement per line." />
        </div>

        <div class="card space-y-5 self-start p-6 sm:p-8">
            <h2 class="text-lg font-bold">Details</h2>
            <x-field name="employment_type" type="select" label="Employment type" :options="\App\Models\JobOpening::TYPES" :value="$job->employment_type" required />
            <x-field name="location" label="Location" :value="$job->location" placeholder="e.g. Ontario, Canada" />
            <x-field name="closes_at" type="date" label="Closing date (optional)" :value="$job->closes_at?->format('Y-m-d')" hint="Hidden from the website after this date." />
            <x-field name="sort_order" type="number" label="Display order" :value="$job->sort_order ?? 0" hint="Lower numbers appear first." />
            <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $job->is_published)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Published (accepting applications)</label>
            <button class="btn btn-primary w-full">{{ $editing ? 'Save changes' : 'Create job opening' }}</button>
        </div>
    </form>
</x-layouts.admin>
