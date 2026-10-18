<x-layouts.admin title="Job openings">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="max-w-xl text-sm text-slate-600">Openings appear on the public <a href="{{ route('careers.index') }}" target="_blank" class="font-semibold text-brand hover:underline">Careers page</a> while published and before their closing date. Visitors can always send a general application.</p>
        <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add job opening</a>
    </div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line">
                <thead class="bg-mist"><tr><th class="th">Position</th><th class="th">Type</th><th class="th">Status</th><th class="th">Applications</th><th class="th text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @forelse ($jobs as $job)
                        <tr class="hover:bg-mist/60">
                            <td class="td"><p class="font-semibold text-navy">{{ $job->title }}</p><p class="text-xs text-slate-500">{{ $job->location ?: 'No location' }}@if ($job->closes_at) · closes {{ $job->closes_at->format('M j, Y') }}@endif</p></td>
                            <td class="td">{{ $job->employment_type }}</td>
                            <td class="td"><x-status-badge :status="$job->isOpen() ? 'published' : 'archived'" />@unless ($job->isOpen())<span class="ml-1 text-xs text-slate-500">{{ $job->is_published ? 'expired' : 'hidden' }}</span>@endunless</td>
                            <td class="td"><a href="{{ route('admin.applications.index', ['job' => $job->id]) }}" class="font-semibold text-brand hover:underline">{{ $job->applications_count }}</a></td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.jobs.toggle', $job) }}">@csrf @method('PATCH')<button class="btn btn-outline btn-sm">{{ $job->is_published ? 'Close' : 'Publish' }}</button></form>
                                    <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-navy btn-sm"><x-icon name="pencil" class="size-4" /> Edit</a>
                                    @can('delete', $job)
                                        <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job opening? Existing applications are kept.')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" aria-label="Delete {{ $job->title }}"><x-icon name="trash" class="size-4" /></button></form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-14 text-center text-slate-500">No job openings yet. The Careers page invites general applications until you add one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
