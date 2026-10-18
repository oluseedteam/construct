<x-layouts.admin title="Staff accounts">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="max-w-xl text-sm text-slate-600"><strong>Administrators</strong> can do everything. <strong>Editors</strong> can edit content and handle inquiries but cannot delete records or change settings and accounts.</p>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add staff account</a>
    </div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line">
                <thead class="bg-mist"><tr><th class="th">Name</th><th class="th">Email</th><th class="th">Role</th><th class="th">Status</th><th class="th text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach ($users as $u)
                        <tr class="hover:bg-mist/60">
                            <td class="td font-semibold text-navy">{{ $u->name }}@if ($u->is(auth()->user()))<span class="ml-2 badge bg-sky-100 text-sky-800">You</span>@endif</td>
                            <td class="td text-slate-600">{{ $u->email }}</td>
                            <td class="td capitalize">{{ $u->role }}</td>
                            <td class="td"><x-status-badge :status="$u->is_active ? 'published' : 'archived'" /> <span class="text-xs text-slate-500">{{ $u->is_active ? 'active' : 'disabled' }}</span></td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-navy btn-sm"><x-icon name="pencil" class="size-4" /> Edit</a>
                                    @unless ($u->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Delete this account?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" aria-label="Delete {{ $u->name }}"><x-icon name="trash" class="size-4" /></button></form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
