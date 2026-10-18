<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Highlight;
use App\Support\Icons;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HighlightController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Highlight::class);

        return view('admin.highlights.index', [
            'groups' => Highlight::GROUPS,
            'highlights' => Highlight::orderBy('sort_order')->orderBy('id')->get()->groupBy('group'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Highlight::class);

        $group = array_key_exists($request->query('group'), Highlight::GROUPS) ? $request->query('group') : 'why';

        return view('admin.highlights.form', ['highlight' => new Highlight(['group' => $group, 'icon' => 'check-circle', 'is_visible' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Highlight::class);

        Highlight::create($this->validated($request));

        return redirect()->route('admin.highlights.index')->with('status', 'Item added.');
    }

    public function edit(Highlight $highlight): View
    {
        $this->authorize('update', $highlight);

        return view('admin.highlights.form', compact('highlight'));
    }

    public function update(Request $request, Highlight $highlight): RedirectResponse
    {
        $this->authorize('update', $highlight);

        $highlight->update($this->validated($request));

        return redirect()->route('admin.highlights.index')->with('status', 'Item updated.');
    }

    public function destroy(Highlight $highlight): RedirectResponse
    {
        $this->authorize('delete', $highlight);

        $highlight->delete();

        return redirect()->route('admin.highlights.index')->with('status', 'Item deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'group' => ['required', 'in:'.implode(',', array_keys(Highlight::GROUPS))],
            'title' => ['required', 'string', 'max:120'],
            'text' => ['nullable', 'string', 'max:500'],
            'icon' => ['required', 'in:'.implode(',', array_keys(Icons::MAP))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $data['is_visible'] = $request->boolean('is_visible');
        $data['sort_order'] ??= 0;

        return $data;
    }
}
