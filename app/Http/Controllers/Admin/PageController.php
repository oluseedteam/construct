<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\UniqueSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/**
 * Admin > Pages: publish/unpublish and reorder the built-in pages, and create custom pages.
 * Unpublished pages disappear from the menu, footer and sitemap and return 404.
 */
class PageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Page::class);

        // Self-heal: a database imported from an older SQL export has no page rows yet.
        if (Page::where('is_builtin', true)->count() < count(Page::BUILTIN)) {
            Page::ensureBuiltins();
        }

        return view('admin.pages.index', ['pages' => Page::ordered()->get()]);
    }

    public function create(): View
    {
        $this->authorize('create', Page::class);

        $next = ((int) Page::max('sort_order')) + 10;

        return view('admin.pages.form', ['page' => new Page(['is_published' => true, 'show_in_nav' => true, 'show_in_footer' => true, 'sort_order' => $next])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Page::class);

        $data = $this->validatedCustom($request);
        $data['slug'] = UniqueSlug::make(Page::class, ($data['slug'] ?? '') ?: $data['title']);

        if ($error = $this->reservedSlugError($data['slug'])) {
            return back()->withInput()->withErrors(['slug' => $error]);
        }

        $page = Page::create($data + ['is_builtin' => false]);
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.pages.index')->with('status', "Page \"{$page->title}\" created.");
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);

        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        if ($page->is_builtin) {
            $data = $request->validate([
                'nav_label' => ['required', 'string', 'max:40'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            ]);
            $data['sort_order'] ??= 0;
        } else {
            $data = $this->validatedCustom($request, $page);
            $data['slug'] = UniqueSlug::make(Page::class, ($data['slug'] ?? '') ?: $data['title'], $page->id);

            if ($error = $this->reservedSlugError($data['slug'])) {
                return back()->withInput()->withErrors(['slug' => $error]);
            }
        }

        $data['show_in_nav'] = $request->boolean('show_in_nav');
        $data['show_in_footer'] = $request->boolean('show_in_footer');
        // The homepage is always published; the checkbox is ignored for it.
        $data['is_published'] = $page->isLocked() ? true : $request->boolean('is_published');

        $page->update($data);
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.pages.index')->with('status', "Page \"{$page->title}\" updated.");
    }

    public function toggle(Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        if ($page->isLocked()) {
            return back()->with('error', 'The homepage cannot be unpublished.');
        }

        $page->update(['is_published' => ! $page->is_published]);
        Cache::forget('sitemap.xml');

        return back()->with('status', $page->is_published
            ? "\"{$page->title}\" is now published and visible on the website."
            : "\"{$page->title}\" is unpublished: it is hidden from the menu and returns a 404 page.");
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);

        if ($page->is_builtin) {
            return back()->with('error', 'Built-in pages cannot be deleted. Unpublish it to hide it instead.');
        }

        $page->delete();
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.pages.index')->with('status', 'Page deleted.');
    }

    private function validatedCustom(Request $request, ?Page $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('pages', 'slug')->ignore($page?->id)],
            'nav_label' => ['required', 'string', 'max:40'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'content' => ['nullable', 'string', 'max:50000'],
            'meta_description' => ['nullable', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ], ['slug.regex' => 'Use lowercase letters, numbers and dashes only.']);

        $data['sort_order'] ??= 0;

        return $data;
    }

    /** Custom pages live at /{slug}, so the slug must not clash with an existing URL. */
    private function reservedSlugError(string $slug): ?string
    {
        $reserved = ['admin', 'login', 'logout', 'storage', 'build', 'images', 'livewire', 'up', 'favicon.ico', 'home', 'index', 'page', 'pages'];

        foreach (Route::getRoutes() as $route) {
            $first = explode('/', $route->uri())[0];
            if ($first !== '' && ! str_starts_with($first, '{')) {
                $reserved[] = $first;
            }
        }

        return in_array($slug, array_unique($reserved), true)
            ? "The address /{$slug} is already used by the website. Choose a different slug."
            : null;
    }
}
