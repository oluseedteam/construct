<?php

namespace App\Http\Controllers;

use App\Models\JobOpening;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function () {
            $urls = collect([
                [route('home'), now(), '1.0'],
                [route('about'), null, '0.7'],
                [route('services.index'), null, '0.9'],
                [route('projects.index'), null, '0.9'],
                [route('gallery'), null, '0.6'],
                [route('careers.index'), null, '0.6'],
                [route('why-us'), null, '0.6'],
                [route('contact'), null, '0.7'],
                [route('quote'), null, '0.8'],
            ]);

            Service::published()->ordered()->get()->each(
                fn ($s) => $urls->push([route('services.show', $s), $s->updated_at, '0.8'])
            );
            Project::published()->ordered()->get()->each(
                fn ($p) => $urls->push([route('projects.show', $p), $p->updated_at, '0.7'])
            );

            JobOpening::open()->ordered()->get()->each(
                fn ($j) => $urls->push([route('careers.show', $j), $j->updated_at, '0.5'])
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
