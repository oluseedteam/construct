<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    private function nav(string $html): string
    {
        preg_match('#<nav[^>]*aria-label="Primary".*?</nav>#s', $html, $m);

        return $m[0] ?? '';
    }

    private function footer(string $html): string
    {
        preg_match('#<footer.*?</footer>#s', $html, $m);

        return $m[0] ?? '';
    }

    public function test_projects_and_gallery_are_back_in_the_header_and_footer(): void
    {
        $this->seed(DatabaseSeeder::class);
        $html = $this->get('/')->assertOk()->getContent();

        $nav = $this->nav($html);
        $this->assertStringContainsString('href="'.route('projects.index').'"', $nav);
        $this->assertStringContainsString('href="'.route('gallery').'"', $nav);
        $this->assertMatchesRegularExpression('/>\s*Projects\s*</', $nav);
        $this->assertMatchesRegularExpression('/>\s*Careers\s*</', $nav);

        $footer = $this->footer($html);
        $this->assertStringContainsString('href="'.route('projects.index').'"', $footer);
        $this->assertStringContainsString('href="'.route('gallery').'"', $footer);
        $this->assertStringContainsString('Request a Quote', $footer);

        preg_match('#id="mobile-nav".*?</nav>#s', $html, $mobile);
        $this->assertStringContainsString(route('gallery'), $mobile[0]);
    }

    public function test_menu_falls_back_to_the_built_in_pages_before_seeding(): void
    {
        $this->assertSame(0, Page::count());

        $nav = $this->nav($this->get('/')->assertOk()->getContent());
        foreach (['home', 'about', 'services.index', 'projects.index', 'gallery', 'why-us', 'careers.index', 'contact'] as $route) {
            $this->assertStringContainsString('href="'.route($route).'"', $nav);
        }
    }

    public function test_unpublishing_a_built_in_page_hides_it_everywhere(): void
    {
        $this->seed(DatabaseSeeder::class);
        $project = $this->project();
        $page = Page::firstWhere('key', 'projects');

        $this->actingAs($this->admin())->patch("/admin/pages/{$page->id}/toggle")->assertRedirect()->assertSessionHas('status');
        $this->assertFalse($page->fresh()->is_published);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('href="'.route('projects.index').'"', $this->nav($html));
        $this->assertStringNotContainsString('href="'.route('projects.index').'"', $this->footer($html));
        $this->assertStringNotContainsString('projects-title', $html);           // featured projects section is gone
        $this->assertStringContainsString('Explore Our Services', $html);        // hero button falls back

        // Gallery was nested under Projects; it now shows as its own menu item.
        $this->assertStringContainsString('href="'.route('gallery').'"', $this->nav($html));

        $this->get('/projects')->assertNotFound()->assertSee('This page could not be found');
        $this->get("/projects/{$project->slug}")->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee(route('projects.index'), false)->assertDontSee(route('projects.show', $project), false);

        // ...and publishing again restores it.
        $this->patch("/admin/pages/{$page->id}/toggle")->assertRedirect();
        $this->get('/projects')->assertOk();
        $this->assertStringContainsString('href="'.route('projects.index').'"', $this->nav($this->get('/')->getContent()));
    }

    public function test_unpublishing_services_careers_contact_and_quote(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs($this->admin());

        foreach (['services', 'careers', 'contact', 'quote'] as $key) {
            $this->patch('/admin/pages/'.Page::firstWhere('key', $key)->id.'/toggle');
        }

        $this->get('/services')->assertNotFound();
        $this->get('/services/residential-construction')->assertNotFound();
        $this->get('/careers')->assertNotFound();
        $this->get('/careers/apply')->assertNotFound();
        $this->get('/contact')->assertNotFound();
        $this->post('/contact', ['name' => 'x'])->assertNotFound();
        $this->get('/request-a-quote')->assertNotFound();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString(route('quote'), $html);
        $this->assertStringNotContainsString(route('contact'), $html);
        // The homepage services strip stays, but its cards no longer link to detail pages.
        $this->assertStringContainsString('Residential Construction', $html);
        $this->assertStringNotContainsString(route('services.show', 'residential-construction'), $html);
    }

    public function test_the_homepage_cannot_be_unpublished(): void
    {
        $this->seed(DatabaseSeeder::class);
        $home = Page::firstWhere('key', 'home');
        $this->actingAs($this->admin());

        $this->patch("/admin/pages/{$home->id}/toggle")->assertSessionHas('error');
        $this->put("/admin/pages/{$home->id}", ['nav_label' => 'Home', 'sort_order' => 0, 'is_published' => 0])->assertRedirect();

        $this->assertTrue($home->fresh()->is_published);
        $this->get('/')->assertOk();
    }

    public function test_built_in_pages_can_be_relabelled_reordered_and_hidden_from_menu_or_footer(): void
    {
        $this->seed(DatabaseSeeder::class);
        $gallery = Page::firstWhere('key', 'gallery');
        $this->actingAs($this->admin());

        $this->put("/admin/pages/{$gallery->id}", ['nav_label' => 'Photos', 'sort_order' => 5, 'is_published' => 1, 'show_in_nav' => 1, 'show_in_footer' => 0])->assertSessionHasNoErrors();

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('>Photos<', $this->nav($html));
        $this->assertStringNotContainsString('href="'.route('gallery').'"', $this->footer($html));
        $this->get('/gallery')->assertOk();

        // slug/title of built-ins cannot be changed through the form
        $this->put("/admin/pages/{$gallery->id}", ['nav_label' => 'Photos', 'slug' => 'hacked', 'title' => 'Hacked'])->assertRedirect();
        $this->assertSame('gallery', $gallery->fresh()->slug);
    }

    public function test_admin_can_create_edit_unpublish_and_delete_a_custom_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs($this->admin());

        $this->post('/admin/pages', [
            'title' => 'Our Process', 'nav_label' => 'Process', 'subtitle' => 'How we work',
            'content' => "## Step one\n\nWe listen & plan.\n\n- Site visit\n- Detailed quote\n\n<script>alert(1)</script>",
            'is_published' => 1, 'show_in_nav' => 1, 'show_in_footer' => 1, 'sort_order' => 55,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.pages.index'));

        $page = Page::firstWhere('slug', 'our-process');
        $this->assertFalse($page->is_builtin);

        $html = $this->get('/our-process')->assertOk()->getContent();
        $this->assertStringContainsString('<h2>Step one</h2>', $html);
        $this->assertStringContainsString('<li>Site visit</li>', $html);
        $this->assertStringContainsString('We listen &amp; plan.', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);   // HTML is escaped
        $this->assertStringContainsString('<title>Our Process | R Square Construction</title>', $html);

        $home = $this->get('/')->getContent();
        $this->assertStringContainsString('href="'.route('pages.show', 'our-process').'"', $this->nav($home));
        $this->assertMatchesRegularExpression('/>\s*Process\s*</', $this->nav($home));
        $this->assertStringContainsString('Our Process', $this->footer($home));
        $this->get('/sitemap.xml')->assertSee(route('pages.show', 'our-process'), false);

        // edit
        $this->put("/admin/pages/{$page->id}", ['title' => 'Our Process', 'nav_label' => 'Process', 'slug' => 'how-we-work', 'content' => 'Updated text.', 'is_published' => 1, 'show_in_nav' => 1, 'show_in_footer' => 1])->assertSessionHasNoErrors();
        $this->get('/how-we-work')->assertOk()->assertSee('Updated text.');
        $this->get('/our-process')->assertNotFound();

        // unpublish -> hidden + 404
        $this->patch("/admin/pages/{$page->id}/toggle");
        $this->get('/how-we-work')->assertNotFound();
        $this->assertStringNotContainsString('how-we-work', $this->nav($this->get('/')->getContent()));
        $this->get('/sitemap.xml')->assertDontSee('how-we-work');

        // delete
        $this->delete("/admin/pages/{$page->id}")->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_custom_page_slugs_cannot_clash_with_existing_urls(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs($this->admin());

        foreach (['admin', 'about', 'projects', 'careers', 'storage', 'sitemap.xml'] as $slug) {
            $this->post('/admin/pages', ['title' => 'Clash', 'nav_label' => 'Clash', 'slug' => $slug, 'is_published' => 1])->assertSessionHasErrors('slug');
        }
        $this->post('/admin/pages', ['title' => 'Bad', 'nav_label' => 'Bad', 'slug' => 'Not Valid!'])->assertSessionHasErrors('slug');
        $this->assertSame(0, Page::custom()->count());

        // A blank slug is generated and made unique.
        $this->post('/admin/pages', ['title' => 'Our Team', 'nav_label' => 'Team', 'is_published' => 1]);
        $this->post('/admin/pages', ['title' => 'Our Team', 'nav_label' => 'Team', 'is_published' => 1]);
        $this->assertDatabaseHas('pages', ['slug' => 'our-team']);
        $this->assertDatabaseHas('pages', ['slug' => 'our-team-2']);
    }

    public function test_built_in_pages_cannot_be_deleted_and_editors_cannot_delete_custom_pages(): void
    {
        $this->seed(DatabaseSeeder::class);
        $about = Page::firstWhere('key', 'about');
        $custom = Page::create(['title' => 'Extra', 'slug' => 'extra', 'nav_label' => 'Extra', 'is_builtin' => false]);

        $this->actingAs($this->admin())->delete("/admin/pages/{$about->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('pages', ['id' => $about->id]);

        $editor = $this->editor();
        $this->actingAs($editor)->delete("/admin/pages/{$custom->id}")->assertForbidden();
        $this->assertDatabaseHas('pages', ['id' => $custom->id]);

        // ...but editors can still publish/unpublish and edit.
        $this->actingAs($editor)->patch("/admin/pages/{$custom->id}/toggle")->assertRedirect();
        $this->assertFalse($custom->fresh()->is_published);
    }

    public function test_unknown_and_unpublished_custom_pages_are_not_found(): void
    {
        Page::create(['title' => 'Draft', 'slug' => 'draft-page', 'nav_label' => 'Draft', 'is_builtin' => false, 'is_published' => false]);

        $this->get('/draft-page')->assertNotFound();
        $this->get('/no-such-page')->assertNotFound()->assertSee('This page could not be found');
        $this->get('/admin/pages')->assertRedirect(route('admin.login'));
    }

    public function test_admin_pages_list_self_heals_when_rows_are_missing(): void
    {
        // e.g. a database imported from the older SQL export, or a seed that was never run
        $this->assertSame(0, Page::count());

        $this->actingAs($this->admin())->get('/admin/pages')->assertOk()->assertSee('Project Gallery');
        $this->assertSame(count(Page::BUILTIN), Page::where('is_builtin', true)->count());

        // running it again (and the seeder) never duplicates or resets admin edits
        Page::firstWhere('key', 'gallery')->update(['nav_label' => 'Photos', 'is_published' => false]);
        $this->get('/admin/pages')->assertOk();
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(count(Page::BUILTIN), Page::count());
        $this->assertSame('Photos', Page::firstWhere('key', 'gallery')->nav_label);
        $this->assertFalse(Page::firstWhere('key', 'gallery')->is_published);
    }

    public function test_dashboard_has_one_click_hide_and_show_buttons_for_every_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs($this->admin());

        $html = $this->get('/admin')->assertOk()->assertSee('Page visibility')->assertSee('Always visible')->getContent();
        $gallery = Page::firstWhere('key', 'gallery');
        $this->assertStringContainsString(route('admin.pages.toggle', $gallery), $html);
        $this->assertSame(count(Page::BUILTIN) - 1, substr_count($html, '/toggle"'));   // every page except Home

        // hide Gallery straight from the dashboard
        $this->from('/admin')->patch(route('admin.pages.toggle', $gallery))->assertRedirect('/admin');
        $this->get('/gallery')->assertNotFound();
        $this->get('/admin')->assertSee('Show '.$gallery->title, false);

        // the Pages screen uses the same wording
        $this->get('/admin/pages')->assertSee('Show page')->assertSee('Hide page')->assertSee('>hidden</span>', false)->assertSee('>visible</span>', false);

        // an unseeded database still gets its page list on the dashboard
        Page::query()->delete();
        $this->get('/admin')->assertOk()->assertSee('Project Gallery');
        $this->assertSame(count(Page::BUILTIN), Page::count());
    }

    public function test_admin_pages_screens_render(): void
    {
        $this->seed(DatabaseSeeder::class);
        $custom = Page::create(['title' => 'Extra', 'slug' => 'extra', 'nav_label' => 'Extra', 'is_builtin' => false]);
        $this->actingAs($this->admin());

        $this->get('/admin/pages')->assertOk()->assertSee('Our Projects')->assertSee('Project Gallery')->assertSee('Built-in')->assertSee('Custom');
        $this->get('/admin/pages/create')->assertOk();
        $this->get('/admin/pages/'.Page::firstWhere('key', 'projects')->id.'/edit')->assertOk()->assertSee('Menu label');
        $this->get("/admin/pages/{$custom->id}/edit")->assertOk()->assertSee('Content');
        $this->get('/admin')->assertOk()->assertSee(route('admin.pages.index'), false);
    }
}
