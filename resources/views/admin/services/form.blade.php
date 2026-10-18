@php $editing = $service->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit service' : 'New service'">
    <a href="{{ route('admin.services.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All services</a>

    <form method="POST" enctype="multipart/form-data" novalidate class="grid gap-8 xl:grid-cols-3"
          action="{{ $editing ? route('admin.services.update', $service) : route('admin.services.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-6 xl:col-span-2">
            <div class="card space-y-5 p-6 sm:p-8">
                <h2 class="text-lg font-bold">Service details</h2>
                <x-field name="name" label="Service name" :value="$service->name" required />
                <x-field name="slug" label="URL slug" :value="$service->slug" hint="Leave blank to generate from the name." />
                <x-field name="description" type="textarea" :rows="3" label="Short description" :value="$service->description" required hint="Shown on service cards (max 500 characters)." />
                <x-field name="content" type="textarea" :rows="8" label="Detailed content" :value="$service->content" hint="Separate paragraphs with a blank line." />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="features" type="textarea" :rows="6" label="Features" :value="implode(PHP_EOL, $service->features ?? [])" hint="One feature per line." />
                    <x-field name="benefits" type="textarea" :rows="6" label="Benefits" :value="implode(PHP_EOL, $service->benefits ?? [])" hint="One benefit per line." />
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card space-y-5 p-6 sm:p-8">
                <h2 class="text-lg font-bold">Publishing</h2>
                <x-field name="main_image" type="file" label="Main image" accept="image/*" hint="JPG, PNG or WebP up to 5 MB." />
                @if ($service->main_image)<img src="{{ media_url($service->main_image) }}" alt="Current main image" class="h-36 w-auto rounded-xl object-cover ring-1 ring-line">@endif

                <fieldset>
                    <legend class="label">Icon</legend>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach (\App\Support\Icons::SERVICE_ICONS as $icon)
                            <label class="cursor-pointer" title="{{ $icon }}">
                                <input type="radio" name="icon" value="{{ $icon }}" class="peer sr-only" @checked(old('icon', $service->icon) === $icon)>
                                <span class="flex aspect-square items-center justify-center rounded-xl bg-mist text-navy ring-1 ring-line transition peer-checked:bg-brand peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand"><x-icon :name="$icon" class="size-6" /></span>
                                <span class="sr-only">{{ $icon }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('icon')<p class="field-error">{{ $message }}</p>@enderror
                </fieldset>

                <x-field name="sort_order" type="number" label="Display order" :value="$service->sort_order ?? 0" hint="Lower numbers appear first." />
                <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $service->is_published)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Published</label>
                <button class="btn btn-primary w-full">{{ $editing ? 'Save changes' : 'Create service' }}</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
