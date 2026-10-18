@if (session('status'))
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
        <x-icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-emerald-600" />
        <p>{{ session('status') }}</p>
    </div>
@endif
@if (session('error'))
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
        <x-icon name="x" class="mt-0.5 size-5 shrink-0 text-red-600" />
        <p>{{ session('error') }}</p>
    </div>
@endif
