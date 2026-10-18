<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\Icons;
use App\Support\MediaStorage;
use App\Support\UniqueSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Service::class);

        return view('admin.services.index', ['services' => Service::ordered()->get()]);
    }

    public function create(): View
    {
        $this->authorize('create', Service::class);

        return view('admin.services.form', ['service' => new Service(['icon' => 'building', 'is_published' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Service::class);

        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make(Service::class, $data['slug'] ?? $data['name']);

        if ($request->hasFile('main_image')) {
            $data['main_image'] = MediaStorage::storeImage($request->file('main_image'), 'services');
        }

        $service = Service::create($data);
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.services.edit', $service)->with('status', 'Service created.');
    }

    public function edit(Service $service): View
    {
        $this->authorize('update', $service);

        return view('admin.services.form', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make(Service::class, $data['slug'] ?? $data['name'], $service->id);

        if ($request->hasFile('main_image')) {
            MediaStorage::delete($service->main_image);
            $data['main_image'] = MediaStorage::storeImage($request->file('main_image'), 'services');
        }

        $service->update($data);
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.services.edit', $service)->with('status', 'Service updated.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $service->update(['is_published' => ! $service->is_published]);
        Cache::forget('sitemap.xml');

        return back()->with('status', $service->is_published ? 'Service published.' : 'Service unpublished.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        MediaStorage::delete($service->main_image);
        $service->delete();
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.services.index')->with('status', 'Service deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'icon' => ['required', 'in:'.implode(',', Icons::SERVICE_ICONS)],
            'description' => ['required', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:20000'],
            'benefits' => ['nullable', 'string', 'max:3000'],
            'features' => ['nullable', 'string', 'max:3000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'main_image' => ['nullable', ...MediaStorage::IMAGE_RULES],
        ]);

        // Benefits and features are entered one per line.
        foreach (['benefits', 'features'] as $field) {
            $data[$field] = collect(preg_split('/\R/', (string) ($data[$field] ?? '')))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values()
                ->all();
        }

        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] ??= 0;
        unset($data['main_image']);

        return $data;
    }
}
