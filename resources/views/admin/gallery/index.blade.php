<x-layouts.admin title="Gallery">
    <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="card mb-8 p-6 sm:p-8" novalidate>
        @csrf
        <h2 class="text-lg font-bold">Upload images</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-3">
            <x-field name="images" type="file" :multiple="true" label="Images" accept="image/*" required hint="JPG, PNG or WebP, up to 5 MB each (max 20 at once)." class="md:col-span-1" />
            <x-field name="project_category_id" type="select" label="Category" :options="$categories->pluck('name', 'id')->all()" placeholder="No category" />
            <x-field name="title" label="Caption (optional)" />
        </div>
        @error('images.*')<p class="field-error">{{ $message }}</p>@enderror
        <button class="btn btn-primary mt-6"><x-icon name="upload" class="size-4" /> Upload</button>
    </form>

    <form method="GET" class="mb-6 flex items-center gap-3">
        <label for="cat" class="text-sm font-semibold text-navy">Category</label>
        <select id="cat" name="category" class="input !w-auto" onchange="this.form.submit()">
            <option value="">All</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </form>

    @if ($images->isEmpty())
        <div class="card px-6 py-14 text-center text-slate-500"><x-icon name="photo" class="mx-auto size-10 text-slate-300" /><p class="mt-3">No gallery images yet.</p></div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($images as $image)
                <div class="card overflow-hidden">
                    <img src="{{ media_url($image->path) }}" alt="{{ $image->title ?: 'Gallery image' }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                    <div class="space-y-3 p-4">
                        <form method="POST" action="{{ route('admin.gallery.update', $image) }}" class="space-y-3">
                            @csrf @method('PATCH')
                            <input type="text" name="title" value="{{ $image->title }}" placeholder="Caption" class="input !py-2" aria-label="Caption">
                            <div class="flex gap-2">
                                <select name="project_category_id" class="input !py-2" aria-label="Category">
                                    <option value="">No category</option>
                                    @foreach ($categories as $c)<option value="{{ $c->id }}" @selected($image->project_category_id === $c->id)>{{ $c->name }}</option>@endforeach
                                </select>
                                <input type="number" name="sort_order" value="{{ $image->sort_order }}" min="0" class="input !w-20 !py-2" aria-label="Order" title="Display order">
                            </div>
                            <button class="btn btn-navy btn-sm w-full">Save</button>
                        </form>
                        @can('delete', $image)
                            <form method="POST" action="{{ route('admin.gallery.destroy', $image) }}" onsubmit="return confirm('Delete this image?')">@csrf @method('DELETE')<button class="btn btn-outline btn-sm w-full !text-red-600 hover:!border-red-600"><x-icon name="trash" class="size-4" /> Delete</button></form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $images->links() }}</div>
    @endif
</x-layouts.admin>
