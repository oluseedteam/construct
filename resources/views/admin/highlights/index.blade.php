<x-layouts.admin title="Highlights">
    <p class="mb-8 max-w-3xl text-sm text-slate-600">These editable lists appear on the website. Hide an item to remove it without deleting it.</p>

    <div class="space-y-8">
        @foreach ($groups as $group => $label)
            <section class="card overflow-hidden" aria-labelledby="g-{{ $group }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-4">
                    <h2 id="g-{{ $group }}" class="text-lg font-bold">{{ $label }}</h2>
                    <a href="{{ route('admin.highlights.create', ['group' => $group]) }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="size-4" /> Add item</a>
                </div>
                <ul class="divide-y divide-line">
                    @forelse ($highlights[$group] ?? [] as $item)
                        <li class="flex flex-wrap items-center gap-4 px-6 py-4">
                            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-navy text-brand"><x-icon :name="$item->icon" class="size-5" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-navy">{{ $item->title }} <span class="ml-1 text-xs font-normal text-slate-400">#{{ $item->sort_order }}</span></p>
                                @if ($item->text)<p class="truncate text-sm text-slate-500">{{ $item->text }}</p>@endif
                            </div>
                            <x-status-badge :status="$item->is_visible ? 'published' : 'draft'" />
                            <div class="flex gap-2">
                                <a href="{{ route('admin.highlights.edit', $item) }}" class="btn btn-navy btn-sm"><x-icon name="pencil" class="size-4" /> Edit</a>
                                @can('delete', $item)
                                    <form method="POST" action="{{ route('admin.highlights.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" aria-label="Delete {{ $item->title }}"><x-icon name="trash" class="size-4" /></button></form>
                                @endcan
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-10 text-center text-slate-500">Nothing here yet.</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
</x-layouts.admin>
