<x-layouts.admin title="My profile">
    <form method="POST" action="{{ route('admin.profile.update') }}" novalidate autocomplete="off" class="card mx-auto max-w-2xl space-y-5 p-6 sm:p-8">
        @csrf @method('PUT')
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="name" label="Name" :value="auth()->user()->name" required />
            <x-field name="email" type="email" label="Email (login)" :value="auth()->user()->email" required />
        </div>
        <div class="border-t border-line pt-5">
            <h2 class="text-lg font-bold">Change password</h2>
            <p class="mt-1 text-sm text-slate-500">Leave blank to keep your current password.</p>
            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                <x-field name="current_password" type="password" label="Current password" />
                <x-field name="password" type="password" label="New password" hint="12+ characters, letters and numbers." />
                <x-field name="password_confirmation" type="password" label="Confirm new password" />
            </div>
        </div>
        <button class="btn btn-primary">Save profile</button>
    </form>
</x-layouts.admin>
