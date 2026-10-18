<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectFilter extends Component
{
    use WithPagination;

    #[Url(as: 'category', except: '')]
    public string $category = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $slug): void
    {
        $this->category = $slug;
        $this->resetPage();
    }

    public function clear(): void
    {
        $this->reset('category', 'status');
        $this->resetPage();
    }

    public function render(): View
    {
        $projects = Project::published()
            ->with('category')
            ->when($this->category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $this->category)))
            ->when(array_key_exists($this->status, Project::STATUSES), fn ($q) => $q->where('status', $this->status))
            ->ordered()
            ->paginate(9);

        return view('livewire.project-filter', [
            'projects' => $projects,
            'categories' => ProjectCategory::whereHas('projects', fn ($q) => $q->where('is_published', true))->orderBy('sort_order')->get(),
            'statuses' => Project::STATUSES,
        ]);
    }
}
