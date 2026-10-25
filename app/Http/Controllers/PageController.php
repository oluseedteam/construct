<?php

namespace App\Http\Controllers;

use App\Models\CompanyStat;
use App\Models\Highlight;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $projects = Project::published()->featured()->with('category')->ordered()->take(3)->get();

        // Nothing marked as featured yet: show the latest published projects instead.
        if ($projects->isEmpty()) {
            $projects = Project::published()->with('category')->ordered()->take(3)->get();
        }

        return view('pages.home', [
            'services' => Service::published()->ordered()->take(6)->get(),
            'stats' => CompanyStat::visible()->get(),
            'projects' => $projects,
            'testimonials' => Testimonial::approved()->orderBy('sort_order')->latest('id')->get(),
            'why' => Highlight::forGroup('why'),
            'commitments' => Highlight::forGroup('commitments'),
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'stats' => CompanyStat::visible()->get(),
            'commitments' => Highlight::forGroup('commitments'),
        ]);
    }

    public function whyUs(): View
    {
        return view('pages.why-us', [
            'why' => Highlight::forGroup('why'),
            'testimonials' => Testimonial::approved()->orderBy('sort_order')->latest('id')->get(),
        ]);
    }

    public function gallery(): View
    {
        return view('pages.gallery');
    }

    /** A custom page created in Admin > Pages. Unpublished pages are not found. */
    public function show(string $slug): View
    {
        abort_unless(request()->isMethod('GET') || request()->isMethod('HEAD'), 404);

        $page = Page::custom()->published()->where('slug', $slug)->firstOrFail();

        return view('pages.custom', compact('page'));
    }
}
