<x-layouts.admin title="Job applications">
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3" role="search">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, email, phone, reference…" class="input !w-80" aria-label="Search applications">
        <select name="status" class="input !w-auto" aria-label="Status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Models\JobApplication::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
        </select>
        <select name="job" class="input !w-auto" aria-label="Position" onchange="this.form.submit()">
            <option value="">All positions</option>
            <option value="general" @selected(request('job') === 'general')>General applications</option>
            @foreach ($jobs as $j)<option value="{{ $j->id }}" @selected((string) request('job') === (string) $j->id)>{{ $j->title }}</option>@endforeach
        </select>
        <button class="btn btn-navy btn-sm">Search</button>
        @if (request()->hasAny(['q', 'status', 'job']))<a href="{{ route('admin.applications.index') }}" class="text-sm font-semibold text-brand hover:underline">Clear</a>@endif
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line">
                <thead class="bg-mist"><tr><th class="th">Applicant</th><th class="th">Position</th><th class="th">Experience</th><th class="th">Status</th><th class="th">Applied</th><th class="th text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @forelse ($applications as $app)
                        <tr class="hover:bg-mist/60">
                            <td class="td"><p class="font-semibold text-navy">{{ $app->name }}</p><p class="text-xs text-slate-500">{{ $app->email }} · {{ $app->phone }}</p></td>
                            <td class="td">{{ $app->position }}</td>
                            <td class="td text-slate-600">{{ $app->experience ?: '—' }}</td>
                            <td class="td"><x-status-badge :status="$app->status" /></td>
                            <td class="td whitespace-nowrap text-slate-500">{{ $app->created_at->format('M j, Y') }}</td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.applications.resume', $app) }}" class="btn btn-outline btn-sm" aria-label="Download resume of {{ $app->name }}"><x-icon name="download" class="size-4" /></a>
                                    <a href="{{ route('admin.applications.show', $app) }}" class="btn btn-navy btn-sm">Open <x-icon name="arrow-right" class="size-4" /></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-14 text-center text-slate-500">No applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $applications->links() }}</div>
</x-layouts.admin>
