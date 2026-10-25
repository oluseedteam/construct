@php $editing = $page->exists; $builtin = $page->is_builtin; @endphp
<x-layouts.admin :title="$editing ? 'Edit page: '.$page->title : 'New page'">
    <a href="{{ route('admin.pages.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-brand"><x-icon name="arrow-left" class="size-4" /> All pages</a>

    <form method="POST" novalidate class="grid gap-8 xl:grid-cols-3" action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="card space-y-5 p-6 sm:p-8 xl:col-span-2">
            @if ($builtin)
                <h2 class="text-lg font-bold">{{ $page->title }} <span class="badge ml-2 bg-slate-200 text-slate-700">Built-in</span></h2>
                <p class="text-sm text-slate-600">This page's address is <code class="rounded bg-mist px-1.5 py-0.5">{{ parse_url($page->url(), PHP_URL_PATH) ?: '/' }}</code>. Its headings and text are edited under <a href="{{ route('admin.content.edit') }}" class="font-semibold text-brand hover:underline">Website Content</a>; here you control its menu label, position and visibility.</p>
                <x-field name="nav_label" label="Menu label" :value="$page->nav_label" required hint="Shown in the navigation bar." />
            @else
                <h2 class="text-lg font-bold">Page content</h2>
                <x-field name="title" label="Page title" :value="$page->title" required />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="nav_label" label="Menu label" :value="$page->nav_label" required hint="Short name for the navigation bar." />
                    <x-field name="slug" label="Address (slug)" :value="$page->slug" :placeholder="'e.g. our-process'" hint="The page will be at /slug. Leave blank to generate from the title." />
                </div>
                <x-field name="subtitle" label="Banner subtitle" :value="$page->subtitle" />
                <x-field name="content" type="textarea" :rows="16" label="Content" :value="$page->content" hint='Separate paragraphs with a blank line. Start a line with "## " for a heading, or use "- " at the start of each line for a bullet list.' />
                <x-field name="meta_description" type="textarea" :rows="2" label="Search engine description (optional)" :value="$page->meta_description" hint="Up to 200 characters." />
            @endif
        </div>

        <div class="card space-y-5 self-start p-6 sm:p-8">
            <h2 class="text-lg font-bold">Visibility</h2>
            <x-field name="sort_order" type="number" label="Menu position" :value="$page->sort_order ?? 0" hint="Lower numbers appear first." />
            @if ($page->isLocked())
                <p class="rounded-xl bg-mist px-4 py-3 text-sm text-slate-600">The homepage is always published.</p>
                <input type="hidden" name="is_published" value="1">
            @else
                <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Published (visible on the website)</label>
            @endif
            <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="show_in_nav" value="0"><input type="checkbox" name="show_in_nav" value="1" @checked(old('show_in_nav', $page->show_in_nav)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Show in the navigation menu</label>
            <label class="flex items-center gap-3 text-sm font-medium text-navy"><input type="hidden" name="show_in_footer" value="0"><input type="checkbox" name="show_in_footer" value="1" @checked(old('show_in_footer', $page->show_in_footer)) class="size-4 rounded border-slate-300 text-brand focus:ring-brand"> Show in the footer links</label>
            <button class="btn btn-primary w-full">{{ $editing ? 'Save changes' : 'Create page' }}</button>
        </div>
    </form>
</x-layouts.admin>
