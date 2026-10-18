<x-layouts.admin title="Dashboard">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as [$label, $value, $icon, $url])
            <a href="{{ $url }}" class="group card flex items-center gap-5 p-6 transition hover:-translate-y-1 hover:shadow-lift">
                <span class="inline-flex size-14 shrink-0 items-center justify-center rounded-2xl bg-navy text-brand transition group-hover:bg-brand group-hover:text-white"><x-icon :name="$icon" class="size-7" /></span>
                <div>
                    <p class="font-display text-3xl font-extrabold text-navy">{{ number_format($value) }}</p>
                    <p class="text-sm text-slate-500">{{ $label }}</p>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-8 grid gap-8 xl:grid-cols-3">
        <section class="card min-w-0 xl:col-span-2" aria-labelledby="recent-title">
            <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 border-b border-line px-5 py-4 sm:px-6">
                <h2 id="recent-title" class="text-lg font-bold">Recent inquiries</h2>
                <div class="flex gap-3 text-sm font-semibold"><a href="{{ route('admin.quotes.index') }}" class="text-brand hover:underline">Quotes</a><a href="{{ route('admin.messages.index') }}" class="text-brand hover:underline">Messages</a><a href="{{ route('admin.applications.index') }}" class="text-brand hover:underline">Applications</a></div>
            </div>
            @if ($recent->isEmpty())
                <div class="px-6 py-14 text-center text-slate-500">
                    <x-icon name="inbox" class="mx-auto size-10 text-slate-300" />
                    <p class="mt-3">No inquiries yet. New messages and quote requests will appear here.</p>
                </div>
            @else
                <ul class="divide-y divide-line">
                    @foreach ($recent as $item)
                        <li>
                            <a href="{{ $item['url'] }}" class="flex items-center gap-4 px-6 py-4 transition hover:bg-mist">
                                <span @class(['inline-flex size-10 shrink-0 items-center justify-center rounded-xl', 'bg-sky-100 text-sky-700' => $item['type'] === 'Message', 'bg-brand-50 text-brand' => $item['type'] === 'Quote', 'bg-violet-100 text-violet-700' => $item['type'] === 'Application'])><x-icon :name="['Message' => 'mail', 'Quote' => 'document', 'Application' => 'users'][$item['type']]" class="size-5" /></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-navy">{{ $item['title'] }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $item['type'] }} from {{ $item['who'] }} · {{ $item['at']->diffForHumans() }}</p>
                                </div>
                                <x-status-badge :status="$item['status']" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="card min-w-0 p-5 sm:p-6" aria-labelledby="quick-title">
            <h2 id="quick-title" class="text-lg font-bold">Quick actions</h2>
            <div class="mt-5 grid gap-3">
                <a href="{{ route('admin.projects.create') }}" class="btn btn-primary justify-start"><x-icon name="plus" class="size-4" /> Add project</a>
                <a href="{{ route('admin.services.create') }}" class="btn btn-navy justify-start"><x-icon name="plus" class="size-4" /> Add service</a>
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-navy justify-start"><x-icon name="plus" class="size-4" /> Post a job opening</a>
                <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline justify-start"><x-icon name="upload" class="size-4" /> Upload gallery images</a>
                <a href="{{ route('admin.content.edit') }}" class="btn btn-outline justify-start"><x-icon name="pencil" class="size-4" /> Edit homepage content</a>
            </div>
        </section>
    </div>
</x-layouts.admin>
