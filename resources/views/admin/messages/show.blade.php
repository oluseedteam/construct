<x-layouts.admin title="Message">
    <a href="{{ route('admin.messages.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All messages</a>

    <article class="card mx-auto max-w-3xl p-6 sm:p-10">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold">{{ $message->subject }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $message->created_at->format('M j, Y \a\t g:i A') }}</p>
            </div>
            <x-status-badge :status="$message->status" class="!px-4 !py-1.5 !text-sm" />
        </div>

        <dl class="mt-6 grid gap-4 rounded-2xl bg-mist p-5 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">From</dt><dd class="font-semibold text-navy">{{ $message->name }}</dd></div>
            <div><dt class="text-slate-500">Email</dt><dd><a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject) }}" class="font-semibold text-brand hover:underline">{{ $message->email }}</a></dd></div>
            @if ($message->phone)<div><dt class="text-slate-500">Phone</dt><dd><a href="{{ tel_href($message->phone) }}" class="font-semibold text-brand hover:underline">{{ $message->phone }}</a></dd></div>@endif
        </dl>

        <p class="mt-8 whitespace-pre-line leading-relaxed text-slate-700">{{ $message->message }}</p>

        <div class="mt-10 flex flex-wrap gap-3 border-t border-line pt-6">
            <a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject) }}" class="btn btn-primary"><x-icon name="mail" class="size-4" /> Reply by email</a>
            @if ($message->status !== 'new')
                <form method="POST" action="{{ route('admin.messages.update', $message) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="new"><button class="btn btn-outline">Mark as unread</button></form>
            @endif
            @if ($message->status !== 'archived')
                <form method="POST" action="{{ route('admin.messages.update', $message) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="archived"><button class="btn btn-outline"><x-icon name="archive" class="size-4" /> Archive</button></form>
            @else
                <form method="POST" action="{{ route('admin.messages.update', $message) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="read"><button class="btn btn-outline">Restore</button></form>
            @endif
            @can('delete', $message)
                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message permanently?')" class="sm:ml-auto">@csrf @method('DELETE')<button class="btn btn-danger"><x-icon name="trash" class="size-4" /> Delete</button></form>
            @endcan
        </div>
    </article>
</x-layouts.admin>
