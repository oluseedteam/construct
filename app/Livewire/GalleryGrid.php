<?php

namespace App\Livewire;

use App\Models\GalleryImage;
use App\Models\ProjectCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class GalleryGrid extends Component
{
    #[Url(as: 'category', except: '')]
    public string $category = '';

    public function setCategory(string $slug): void
    {
        $this->category = $slug;
    }

    public function render(): View
    {
        $images = GalleryImage::with('category')
            ->when($this->category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $this->category)))
            ->orderBy('sort_order')->orderByDesc('id')
            ->get();

        return view('livewire.gallery-grid', [
            'images' => $images,
            'categories' => ProjectCategory::whereHas('galleryImages')->orderBy('sort_order')->get(),
        ]);
    }
}
