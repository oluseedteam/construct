<x-layouts.admin title="Pages">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="max-w-2xl text-sm text-slate-600">Every page on the website, in menu order. <strong>Unpublish</strong> a page to hide it: it leaves the menu, footer and sitemap and visitors get a "not found" page. Built-in pages can be hidden but not deleted; their text is edited under <a href="{{ route('admin.content.edit') }}" class="font-semibold text-brand hover:underline">Website Content</a>.</p>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add new page</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line">
                <thead class="bg-mist"><tr><th class="th">Order</th><th class="th">Page</th><th class="th">Type</th><th class="th">Status</th><th class="th">Menu</th><th class="th">Footer</th><th class="th text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach ($pages as $page)
                        <tr @class(['hover:bg-mist/60', 'opacity-60' => ! $page->is_published])>
                            <td class="td text-slate-500">{{ $page->sort_order }}</td>
                            <td class="td">
                                <p class="font-semibold text-navy">{{ $page->title }}@if ($page->nav_label !== $page->title)<span class="ml-2 text-xs font-normal text-slate-500">menu: {{ $page->nav_label }}</span>@endif</p>
                                <p class="text-xs text-slate-500">{{ parse_url($page->url(), PHP_URL_PATH) ?: '/' }}</p>
                            </td>
                            <td class="td"><span @class(['badge', 'bg-slate-200 text-slate-700' => $page->is_builtin, 'bg-violet-100 text-violet-800' => ! $page->is_builtin])>{{ $page->is_builtin ? 'Built-in' : 'Custom' }}</span></td>
                            <td class="td"><x-status-badge :status="$page->is_published ? 'published' : 'hidden'" /></td>
                            <td class="td text-sm">{{ $page->show_in_nav ? 'Yes' : '—' }}</td>
                            <td class="td text-sm">{{ $page->show_in_footer ? 'Yes' : '—' }}</td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    @if ($page->is_published)<a href="{{ $page->url() }}" target="_blank" class="btn btn-outline btn-sm" aria-label="View {{ $page->title }}"><x-icon name="eye" class="size-4" /></a>@endif
                                    @unless ($page->isLocked())
                                        <form method="POST" action="{{ route('admin.pages.toggle', $page) }}">@csrf @method('PATCH')<button class="btn btn-outline btn-sm">{{ $page->is_published ? 'Unpublish' : 'Publish' }}</button></form>
                                    @endunless
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-navy btn-sm"><x-icon name="pencil" class="size-4" /> Edit</a>
                                    @if (! $page->is_builtin)
                                        @can('delete', $page)
                                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page permanently? Unpublish it instead if you may need it again.')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" aria-label="Delete {{ $page->title }}"><x-icon name="trash" class="size-4" /></button></form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
