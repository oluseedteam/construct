@php $editing = $highlight->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit item' : 'New item'">
    <a href="{{ route('admin.highlights.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All highlights</a>

    <form method="POST" novalidate class="card mx-auto max-w-2xl space-y-5 p-6 sm:p-8" action="{{ $editing ? route('admin.highlights.update', $highlight) : route('admin.highlights.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <x-field name="group" type="select" label="Where it appears" :options="\App\Models\Highlight::GROUPS" :value="$highlight->group" required />
        <x-field name="title" label="Title" :value="$highlight->title" required />
        <x-field name="text" type="textarea" :rows="3" label="Description" :value="$highlight->text" hint="Optional for the About section tick-list; shown under the title on Why Choose Us cards." />

        <fieldset>
            <legend class="label">Icon</legend>
            <div class="grid grid-cols-6 gap-2 sm:grid-cols-8">
                @foreach (\App\Support\Icons::SERVICE_ICONS as $icon)
                    <label class="cursor-pointer" title="{{ $icon }}">
                        <input type="radio" name="icon" value="{{ $icon }}" class="peer sr-only" @checked(old('icon', $highlight->icon) === $icon)>
                        <span class="flex aspect-square items-center justify-center rounded-xl bg-mist text-navy ring-1 ring-line transition peer-checked:bg-brand peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand"><x-icon :name="$icon" class="size-6" /></span>
                        <span class="sr-only">{{ $icon }}</span>
                    </label>
                @endforeach
            </div>
            @error('icon')<p class="field-error">{{ $message }}</p>@enderror
        </fieldset>

        <x-field name="sort_order" type="number" label="Display order" :value="$highlight->sort_order ?? 0" hint="Lower numbers appear first." />
        <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="is_visible" value="0"><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $highlight->is_visible)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Visible on the website</label>
        <button class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add item' }}</button>
    </form>
</x-layouts.admin>
