<x-layouts.admin title="Messages">
    @php $current = (string) request('status', ''); @endphp
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Message status">
            @foreach (['' => 'Inbox', 'new' => 'New', 'read' => 'Read', 'archived' => 'Archived'] as $key => $label)
                <a href="{{ route('admin.messages.index', array_filter(['status' => $key, 'q' => request('q')])) }}" @class(['rounded-full px-4 py-2 text-sm font-semibold transition', 'bg-navy text-white' => $current === (string) $key, 'bg-white text-navy ring-1 ring-line hover:text-brand' => $current !== (string) $key])>
                    {{ $label }}@if ($key && ($counts[$key] ?? 0))<span class="ml-1.5 text-xs opacity-70">{{ $counts[$key] }}</span>@endif
                </a>
            @endforeach
        </div>
        <form method="GET" role="search" class="flex w-full gap-2 sm:w-auto">
            @if ($current)<input type="hidden" name="status" value="{{ $current }}">@endif
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search messages…" class="input min-w-0 flex-1 sm:!w-64 sm:flex-none" aria-label="Search messages">
            <button class="btn btn-navy btn-sm">Search</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <ul class="divide-y divide-line">
            @forelse ($messages as $message)
                <li>
                    <a href="{{ route('admin.messages.show', $message) }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-mist sm:px-6">
                        <span @class(['size-2.5 shrink-0 rounded-full', 'bg-brand' => $message->status === 'new', 'bg-transparent' => $message->status !== 'new']) aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p @class(['truncate text-sm', 'font-bold text-navy' => $message->status === 'new', 'font-medium text-slate-700' => $message->status !== 'new'])>{{ $message->subject }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $message->name }} · {{ $message->email }} — {{ \Illuminate\Support\Str::limit($message->message, 80) }}</p>
                        </div>
                        <span class="hidden whitespace-nowrap text-xs text-slate-400 sm:block">{{ $message->created_at->diffForHumans() }}</span>
                        <x-status-badge :status="$message->status" />
                    </a>
                </li>
            @empty
                <li class="px-6 py-14 text-center text-slate-500"><x-icon name="inbox" class="mx-auto size-10 text-slate-300" /><p class="mt-3">No messages here.</p></li>
            @endforelse
        </ul>
    </div>
    <div class="mt-6">{{ $messages->links() }}</div>
</x-layouts.admin>
