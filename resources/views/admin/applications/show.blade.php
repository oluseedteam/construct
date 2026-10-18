<x-layouts.admin :title="'Application '.$application->reference">
    <a href="{{ route('admin.applications.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All applications</a>

    <div class="grid gap-8 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="card p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-mono text-sm font-semibold text-brand">{{ $application->reference }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold">{{ $application->name }}</h2>
                        <p class="text-sm text-slate-500">Applied for <strong class="text-navy">{{ $application->position }}</strong> on {{ $application->created_at->format('M j, Y \a\t g:i A') }}</p>
                    </div>
                    <x-status-badge :status="$application->status" class="!px-4 !py-1.5 !text-sm" />
                </div>
                <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Email</dt><dd><a href="mailto:{{ $application->email }}" class="font-semibold text-brand hover:underline">{{ $application->email }}</a></dd></div>
                    <div><dt class="text-slate-500">Phone</dt><dd><a href="{{ tel_href($application->phone) }}" class="font-semibold text-brand hover:underline">{{ $application->phone }}</a></dd></div>
                    <div><dt class="text-slate-500">City</dt><dd class="font-semibold text-navy">{{ $application->city ?: 'Not specified' }}</dd></div>
                    <div><dt class="text-slate-500">Experience</dt><dd class="font-semibold text-navy">{{ $application->experience ?: 'Not specified' }}</dd></div>
                    <div><dt class="text-slate-500">Available from</dt><dd class="font-semibold text-navy">{{ $application->available_from?->format('M j, Y') ?: 'Not specified' }}</dd></div>
                    <div><dt class="text-slate-500">Profile / portfolio</dt><dd>@if ($application->profile_url)<a href="{{ $application->profile_url }}" target="_blank" rel="noopener noreferrer nofollow" class="break-all font-semibold text-brand hover:underline">{{ $application->profile_url }}</a>@else<span class="font-semibold text-navy">Not provided</span>@endif</dd></div>
                </dl>

                @if ($application->cover_letter)
                    <div class="mt-6 border-t border-line pt-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Cover letter</h3>
                        <p class="mt-3 whitespace-pre-line leading-relaxed text-slate-700">{{ $application->cover_letter }}</p>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('admin.applications.resume', $application) }}" class="btn btn-primary"><x-icon name="download" class="size-4" /> Download resume ({{ $application->resume_name }})</a>
                    <a href="mailto:{{ $application->email }}?subject=Your application: {{ rawurlencode($application->position) }}" class="btn btn-outline"><x-icon name="mail" class="size-4" /> Email applicant</a>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.applications.update', $application) }}" class="card space-y-5 p-6 sm:p-8" novalidate>
                @csrf @method('PATCH')
                <h2 class="text-lg font-bold">Status &amp; notes</h2>
                <x-field name="status" type="select" label="Status" :options="\App\Models\JobApplication::STATUSES" :value="$application->status" required />
                <x-field name="internal_notes" type="textarea" :rows="6" label="Internal notes" :value="$application->internal_notes" hint="Only visible to staff." />
                <button class="btn btn-navy w-full">Save</button>
            </form>

            <div class="card p-6 text-sm sm:p-8">
                <h2 class="text-lg font-bold">Delivery</h2>
                <ul class="mt-4 space-y-2 text-slate-600">
                    <li>Admin email: <strong>{{ $application->admin_notified_at ? 'sent' : 'not sent' }}</strong></li>
                    <li>Applicant confirmation: <strong>{{ $application->applicant_notified_at ? 'sent' : 'not sent' }}</strong></li>
                </ul>
            </div>

            @can('delete', $application)
                <form method="POST" action="{{ route('admin.applications.destroy', $application) }}" onsubmit="return confirm('Delete this application and its resume permanently?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger w-full"><x-icon name="trash" class="size-4" /> Delete application</button>
                </form>
            @endcan
        </div>
    </div>
</x-layouts.admin>
