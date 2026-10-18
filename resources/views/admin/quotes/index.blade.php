<x-layouts.admin title="Quote requests">
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3" role="search">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search reference, name, email, phone…" class="input !w-80" aria-label="Search quote requests">
        <select name="status" class="input !w-auto" aria-label="Status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Models\QuoteRequest::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
        </select>
        <select name="type" class="input !w-auto" aria-label="Project type" onchange="this.form.submit()">
            <option value="">All project types</option>
            @foreach (\App\Models\QuoteRequest::PROJECT_TYPES as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>@endforeach
        </select>
        <button class="btn btn-navy btn-sm">Search</button>
        @if (request()->hasAny(['q', 'status', 'type']))<a href="{{ route('admin.quotes.index') }}" class="text-sm font-semibold text-brand hover:underline">Clear</a>@endif
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line">
                <thead class="bg-mist"><tr><th class="th">Reference</th><th class="th">Customer</th><th class="th">Project</th><th class="th">Status</th><th class="th">Received</th><th class="th text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @forelse ($quotes as $quote)
                        <tr class="hover:bg-mist/60">
                            <td class="td font-mono text-xs font-semibold text-navy">{{ $quote->reference }}</td>
                            <td class="td"><p class="font-semibold text-navy">{{ $quote->name }}</p><p class="text-xs text-slate-500">{{ $quote->email }}</p></td>
                            <td class="td"><p>{{ $quote->project_type }}</p><p class="text-xs text-slate-500">{{ $quote->project_location }}</p></td>
                            <td class="td"><x-status-badge :status="$quote->status" /></td>
                            <td class="td whitespace-nowrap text-slate-500">{{ $quote->created_at->format('M j, Y') }}</td>
                            <td class="td text-right"><a href="{{ route('admin.quotes.show', $quote) }}" class="btn btn-navy btn-sm">Open <x-icon name="arrow-right" class="size-4" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-14 text-center text-slate-500">No quote requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $quotes->links() }}</div>
</x-layouts.admin>
