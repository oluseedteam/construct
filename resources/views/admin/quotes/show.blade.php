<x-layouts.admin :title="'Quote '.$quote->reference">
    <a href="{{ route('admin.quotes.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All quote requests</a>

    <div class="grid gap-8 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="card p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div><p class="font-mono text-sm font-semibold text-brand">{{ $quote->reference }}</p><h2 class="mt-1 text-2xl font-extrabold">{{ $quote->project_type }} project</h2><p class="text-sm text-slate-500">Received {{ $quote->created_at->format('M j, Y \a\t g:i A') }}</p></div>
                    <x-status-badge :status="$quote->status" class="!px-4 !py-1.5 !text-sm" />
                </div>
                <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Customer</dt><dd class="font-semibold text-navy">{{ $quote->name }}</dd></div>
                    <div><dt class="text-slate-500">Email</dt><dd><a href="mailto:{{ $quote->email }}" class="font-semibold text-brand hover:underline">{{ $quote->email }}</a></dd></div>
                    <div><dt class="text-slate-500">Phone</dt><dd><a href="{{ tel_href($quote->phone) }}" class="font-semibold text-brand hover:underline">{{ $quote->phone }}</a></dd></div>
                    <div><dt class="text-slate-500">Project location</dt><dd class="font-semibold text-navy">{{ $quote->project_location }}</dd></div>
                    <div><dt class="text-slate-500">Estimated budget</dt><dd class="font-semibold text-navy">{{ $quote->estimated_budget ?: 'Not specified' }}</dd></div>
                    <div><dt class="text-slate-500">Preferred start</dt><dd class="font-semibold text-navy">{{ $quote->preferred_start_date?->format('M j, Y') ?: 'Not specified' }}</dd></div>
                </dl>
                <div class="mt-6 border-t border-line pt-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Project description</h3>
                    <p class="mt-3 whitespace-pre-line leading-relaxed text-slate-700">{{ $quote->description }}</p>
                </div>
                @if ($quote->document_path)
                    <a href="{{ route('admin.quotes.document', $quote) }}" class="btn btn-outline mt-6"><x-icon name="download" class="size-4" /> Download {{ $quote->document_name ?: 'document' }}</a>
                @endif
            </section>

            <section class="card p-6 sm:p-8" aria-labelledby="respond-title">
                <h2 id="respond-title" class="text-lg font-bold">Respond to customer</h2>
                <p class="mt-1 text-sm text-slate-500">Sends an email to {{ $quote->email }}. Replies from the customer go to your company email.</p>
                <form method="POST" action="{{ route('admin.quotes.respond', $quote) }}" class="mt-5 space-y-5" novalidate>
                    @csrf
                    <x-field name="subject" label="Subject" :value="'Your quotation request '.$quote->reference" required />
                    <x-field name="body" type="textarea" :rows="7" label="Message" required />
                    <button class="btn btn-primary"><x-icon name="mail" class="size-4" /> Send reply</button>
                </form>

                @if ($quote->responses->isNotEmpty())
                    <div class="mt-8 border-t border-line pt-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Reply history</h3>
                        <ul class="mt-4 space-y-4">
                            @foreach ($quote->responses as $response)
                                <li class="rounded-xl bg-mist p-4 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="font-semibold text-navy">{{ $response->subject }}</p>
                                        @if ($response->sent_at)<span class="badge bg-emerald-100 text-emerald-800">Sent {{ $response->sent_at->diffForHumans() }}</span>@else<span class="badge bg-red-100 text-red-800">Not delivered</span>@endif
                                    </div>
                                    <p class="mt-2 whitespace-pre-line text-slate-600">{{ $response->body }}</p>
                                    <p class="mt-2 text-xs text-slate-400">By {{ $response->user?->name ?? 'unknown' }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="card space-y-5 p-6 sm:p-8" novalidate>
                @csrf @method('PATCH')
                <h2 class="text-lg font-bold">Status &amp; notes</h2>
                <x-field name="status" type="select" label="Status" :options="\App\Models\QuoteRequest::STATUSES" :value="$quote->status" required />
                <x-field name="internal_notes" type="textarea" :rows="6" label="Internal notes" :value="$quote->internal_notes" hint="Only visible to staff." />
                <button class="btn btn-navy w-full">Save</button>
            </form>

            <div class="card p-6 text-sm sm:p-8">
                <h2 class="text-lg font-bold">Delivery</h2>
                <ul class="mt-4 space-y-2 text-slate-600">
                    <li>Admin email: <strong>{{ $quote->admin_notified_at ? 'sent' : 'not sent' }}</strong></li>
                    <li>Customer confirmation: <strong>{{ $quote->customer_notified_at ? 'sent' : 'not sent' }}</strong></li>
                </ul>
            </div>

            @can('delete', $quote)
                <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}" onsubmit="return confirm('Delete this quote request permanently?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger w-full"><x-icon name="trash" class="size-4" /> Delete request</button>
                </form>
            @endcan
        </div>
    </div>
</x-layouts.admin>
