<x-layouts.admin title="Testimonials">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="max-w-xl text-sm text-slate-600">Only <strong>approved</strong> testimonials appear on the website. Add genuine client feedback only.</p>
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add testimonial</a>
    </div>
    <div class="card overflow-hidden">
        <ul class="divide-y divide-line">
            @forelse ($testimonials as $t)
                <li class="flex flex-wrap items-start justify-between gap-4 px-6 py-5">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-navy">{{ $t->author_name }}@if ($t->author_role)<span class="font-normal text-slate-500"> · {{ $t->author_role }}</span>@endif</p>
                        <p class="mt-1 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($t->content, 180) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-status-badge :status="$t->is_approved ? 'published' : 'draft'" />
                        <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-navy btn-sm"><x-icon name="pencil" class="size-4" /> Edit</a>
                        @can('delete', $t)
                            <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" onsubmit="return confirm('Delete this testimonial?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" aria-label="Delete testimonial from {{ $t->author_name }}"><x-icon name="trash" class="size-4" /></button></form>
                        @endcan
                    </div>
                </li>
            @empty
                <li class="px-6 py-14 text-center text-slate-500">No testimonials yet. The testimonials section stays hidden on the website until you approve one.</li>
            @endforelse
        </ul>
    </div>
</x-layouts.admin>
