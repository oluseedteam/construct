<?php

namespace App\Http\Controllers;

use App\Models\JobOpening;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Support\Pages;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function () {
            // Only published pages (Admin > Pages) are listed; unpublished ones return 404.
            $urls = collect([[route('home'), now(), '1.0']]);

            $priorities = ['about' => '0.7', 'services' => '0.9', 'projects' => '0.9', 'gallery' => '0.6', 'careers' => '0.6', 'why-us' => '0.6', 'contact' => '0.7', 'quote' => '0.8'];
            foreach ($priorities as $key => $priority) {
                if (Pages::isPublished($key)) {
                    $urls->push([route(Page::BUILTIN[$key][3]), null, $priority]);
                }
            }

            if (Pages::isPublished('services')) {
                Service::published()->ordered()->get()->each(
                    fn ($s) => $urls->push([route('services.show', $s), $s->updated_at, '0.8'])
                );
            }
            if (Pages::isPublished('projects')) {
                Project::published()->ordered()->get()->each(
                    fn ($p) => $urls->push([route('projects.show', $p), $p->updated_at, '0.7'])
                );
            }
            if (Pages::isPublished('careers')) {
                JobOpening::open()->ordered()->get()->each(
                    fn ($j) => $urls->push([route('careers.show', $j), $j->updated_at, '0.5'])
                );
            }
            Page::custom()->published()->ordered()->get()->each(
                fn ($p) => $urls->push([route('pages.show', $p->slug), $p->updated_at, '0.6'])
            );

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /admin/',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
