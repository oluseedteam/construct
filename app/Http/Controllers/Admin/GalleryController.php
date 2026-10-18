<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\ProjectCategory;
use App\Support\MediaStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GalleryImage::class);

        return view('admin.gallery.index', [
            'images' => GalleryImage::with('category')
                ->when($request->query('category'), fn ($q, $id) => $q->where('project_category_id', $id))
                ->orderBy('sort_order')->orderByDesc('id')
                ->paginate(24)
                ->withQueryString(),
            'categories' => ProjectCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GalleryImage::class);

        $data = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => MediaStorage::IMAGE_RULES,
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'title' => ['nullable', 'string', 'max:190'],
        ]);

        $next = (int) GalleryImage::max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            GalleryImage::create([
                'path' => MediaStorage::storeImage($file, 'gallery'),
                'project_category_id' => $data['project_category_id'] ?? null,
                'title' => $data['title'] ?? null,
                'sort_order' => $next++,
            ]);
        }

        return back()->with('status', count($request->file('images')).' image(s) uploaded.');
    }

    public function update(Request $request, GalleryImage $image): RedirectResponse
    {
        $this->authorize('update', $image);

        $image->update($request->validate([
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'title' => ['nullable', 'string', 'max:190'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]));

        return back()->with('status', 'Image updated.');
    }

    public function destroy(GalleryImage $image): RedirectResponse
    {
        $this->authorize('delete', $image);

        MediaStorage::delete($image->path);
        $image->delete();

        return back()->with('status', 'Image deleted.');
    }
}
