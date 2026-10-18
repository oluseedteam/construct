<?php

namespace Tests\Feature;

use App\Livewire\GalleryGrid;
use App\Livewire\ProjectFilter;
use App\Models\CompanyStat;
use App\Models\GalleryImage;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_public_page_loads(): void
    {
        $service = $this->service();
        $project = $this->project();

        foreach (['/', '/about', '/services', '/projects', '/gallery', '/why-choose-us', '/contact', '/request-a-quote',
            "/services/{$service->slug}", "/projects/{$project->slug}", '/sitemap.xml', '/robots.txt'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_homepage_shows_hero_copy_and_navigation_links(): void
    {
        $this->service();

        $this->get('/')
            ->assertSee('Building Better Spaces')
            ->assertSee('for a Brighter Tomorrow')
            ->assertSee('At R Square Construction, we deliver quality construction and renovation services', false)
            ->assertSee('Get a Quote')
            ->assertSee('Explore Our Projects')
            ->assertSee('Ideas Today.')
            ->assertSee(route('quote'), false)
            ->assertSee(route('projects.index'), false)
            ->assertSee(route('contact'), false)
            ->assertSee(route('why-us'), false);
    }

    public function test_unpublished_content_returns_404(): void
    {
        $service = $this->service(['is_published' => false]);
        $project = $this->project(['is_published' => false]);

        $this->get("/services/{$service->slug}")->assertNotFound();
        $this->get("/projects/{$project->slug}")->assertNotFound();
        $this->get('/services')->assertDontSee($service->name);
        $this->get('/projects')->assertDontSee($project->name);
        $this->get('/sitemap.xml')->assertDontSee($project->slug)->assertDontSee($service->slug);
    }

    public function test_unknown_page_renders_branded_404(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertSee('This page could not be found');
    }

    public function test_statistics_are_hidden_until_admin_enters_and_enables_them(): void
    {
        $stat = CompanyStat::create(['label' => 'Projects Completed', 'value' => 0, 'is_visible' => false]);
        $this->get('/')->assertDontSee('Projects Completed');

        $stat->update(['value' => 42, 'is_visible' => true]);
        $this->get('/')->assertSee('Projects Completed')->assertSee('counter(42)', false);
    }

    public function test_only_approved_testimonials_are_displayed(): void
    {
        Testimonial::create(['author_name' => 'Pending Person', 'content' => 'Not yet approved text.', 'rating' => 5, 'is_approved' => false]);
        Testimonial::create(['author_name' => 'Approved Person', 'content' => 'Approved praise text.', 'rating' => 5, 'is_approved' => true]);

        $this->get('/')->assertSee('Approved praise text.')->assertDontSee('Not yet approved text.');
    }

    public function test_project_detail_lists_related_projects_and_gallery(): void
    {
        $project = $this->project();
        $project->images()->create(['path' => 'images/demo/demo-1.jpg']);
        $other = $this->project(['name' => 'Harbour Plaza', 'slug' => 'harbour-plaza']);

        $this->get("/projects/{$project->slug}")
            ->assertSee('Project Gallery')
            ->assertSee('Harbour Plaza')
            ->assertSee('images/demo/demo-1.jpg', false);
    }

    public function test_project_filter_by_category_and_status(): void
    {
        $this->project();
        $this->project(['name' => 'Metro Office', 'slug' => 'metro-office', 'project_category_id' => $this->category('Commercial')->id, 'status' => 'completed']);

        Livewire::test(ProjectFilter::class)
            ->assertSee('Lakeview Towers')->assertSee('Metro Office')
            ->call('setCategory', 'commercial')
            ->assertSee('Metro Office')->assertDontSee('Lakeview Towers')
            ->call('clear')
            ->set('status', 'ongoing')
            ->assertSee('Lakeview Towers')->assertDontSee('Metro Office');
    }

    public function test_gallery_filter(): void
    {
        GalleryImage::create(['path' => 'images/demo/demo-1.jpg', 'title' => 'Residential shot', 'project_category_id' => $this->category()->id]);
        GalleryImage::create(['path' => 'images/demo/demo-2.jpg', 'title' => 'Commercial shot', 'project_category_id' => $this->category('Commercial')->id]);

        Livewire::test(GalleryGrid::class)
            ->assertSee('Residential shot')->assertSee('Commercial shot')
            ->call('setCategory', 'commercial')
            ->assertSee('Commercial shot')->assertDontSee('Residential shot');
    }

    public function test_seo_endpoints_and_meta_tags(): void
    {
        $project = $this->project();

        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('projects.show', $project), false);

        $this->get('/robots.txt')->assertSee('Disallow: /admin')->assertSee('Sitemap: '.route('sitemap'));

        $this->get("/projects/{$project->slug}")
            ->assertSee('<title>Lakeview Towers | R Square Construction</title>', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('"@type":"GeneralContractor"', false);
    }

    public function test_hero_uses_a_real_image_element_not_text_baked_into_the_background(): void
    {
        $this->get('/')->assertSee('images/hero-1280.jpg', false)->assertSee('fetchpriority="high"', false);
    }
}
