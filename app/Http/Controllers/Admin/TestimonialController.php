<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Testimonial::class);

        return view('admin.testimonials.index', ['testimonials' => Testimonial::orderByDesc('is_approved')->orderBy('sort_order')->latest('id')->get()]);
    }

    public function create(): View
    {
        $this->authorize('create', Testimonial::class);

        return view('admin.testimonials.form', ['testimonial' => new Testimonial(['rating' => 5])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Testimonial::class);

        Testimonial::create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial saved.');
    }

    public function edit(Testimonial $testimonial): View
    {
        $this->authorize('update', $testimonial);

        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('update', $testimonial);

        $testimonial->update($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('delete', $testimonial);

        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:120'],
            'author_role' => ['nullable', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:1500'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        // Only shown on the website once an administrator explicitly approves it.
        $data['is_approved'] = $request->boolean('is_approved');
        $data['sort_order'] ??= 0;

        return $data;
    }
}
