@php $editing = $user->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit staff account' : 'New staff account'">
    <a href="{{ route('admin.users.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All staff accounts</a>

    <form method="POST" novalidate autocomplete="off" class="card mx-auto max-w-2xl space-y-5 p-6 sm:p-8" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="name" label="Name" :value="$user->name" required />
            <x-field name="email" type="email" label="Email (login)" :value="$user->email" required />
        </div>
        <x-field name="role" type="select" label="Role" :options="['admin' => 'Administrator', 'editor' => 'Editor']" :value="$user->role" required />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="password" type="password" :label="$editing ? 'New password (optional)' : 'Password'" :required="! $editing" hint="At least 12 characters with letters and numbers." />
            <x-field name="password_confirmation" type="password" label="Confirm password" :required="! $editing" />
        </div>
        <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Account active (can sign in)</label>
        <button class="btn btn-primary">{{ $editing ? 'Save changes' : 'Create account' }}</button>
    </form>
</x-layouts.admin>
