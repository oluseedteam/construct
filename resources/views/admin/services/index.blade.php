<x-layouts.admin title="Services">
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add service</a>
    </div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line">
                <thead class="bg-mist"><tr><th class="th">Order</th><th class="th">Service</th><th class="th">Status</th><th class="th text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @forelse ($services as $service)
                        <tr class="hover:bg-mist/60">
                            <td class="td text-slate-500">{{ $service->sort_order }}</td>
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-navy text-brand"><x-icon :name="$service->icon" class="size-5" /></span>
                                    <div><p class="font-semibold text-navy">{{ $service->name }}</p><p class="max-w-md truncate text-xs text-slate-500">{{ $service->description }}</p></div>
                                </div>
                            </td>
                            <td class="td"><x-status-badge :status="$service->is_published ? 'published' : 'draft'" /></td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.services.toggle', $service) }}">@csrf @method('PATCH')<button class="btn btn-outline btn-sm">{{ $service->is_published ? 'Unpublish' : 'Publish' }}</button></form>
                                    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-navy btn-sm"><x-icon name="pencil" class="size-4" /> Edit</a>
                                    @can('delete', $service)
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Delete this service? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" aria-label="Delete {{ $service->name }}"><x-icon name="trash" class="size-4" /></button></form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-14 text-center text-slate-500">No services yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
