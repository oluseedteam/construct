<?php

namespace Tests\Feature;

use App\Mail\QuoteReply;
use App\Models\CompanyStat;
use App\Models\ContactMessage;
use App\Models\GalleryImage;
use App\Models\PageContent;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->actingAs($this->admin());
    }

    public function test_project_crud_with_featured_and_gallery_uploads(): void
    {
        $category = $this->category();

        $this->post('/admin/projects', [
            'name' => 'Riverside Apartments', 'project_category_id' => $category->id, 'status' => 'completed',
            'description' => 'Apartment block.', 'location' => 'Riverside', 'start_date' => '2025-01-10', 'completion_date' => '2026-02-01',
            'is_featured' => 1, 'is_published' => 1,
            'featured_image' => UploadedFile::fake()->image('hero.jpg', 1600, 900),
            'images' => [UploadedFile::fake()->image('a.jpg', 800, 600), UploadedFile::fake()->image('b.png', 800, 600)],
        ])->assertSessionHasNoErrors();

        $project = Project::firstWhere('slug', 'riverside-apartments');
        $this->assertNotNull($project);
        $this->assertTrue($project->is_featured);
        $this->assertCount(2, $project->images);
        Storage::disk('public')->assertExists($project->featured_image);
        $this->get('/')->assertSee('Riverside Apartments');

        // Edit: change status, replace featured image
        $oldImage = $project->featured_image;
        $this->put("/admin/projects/{$project->slug}", [
            'name' => 'Riverside Apartments', 'slug' => 'riverside-apartments', 'status' => 'ongoing', 'description' => 'Updated.',
            'is_published' => 1, 'featured_image' => UploadedFile::fake()->image('new.jpg', 1200, 800),
        ])->assertSessionHasNoErrors();

        $project->refresh();
        $this->assertSame('ongoing', $project->status);
        $this->assertFalse($project->is_featured);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($project->featured_image);

        // Remove one gallery image
        $image = $project->images->first();
        $this->delete("/admin/projects/{$project->slug}/images/{$image->id}")->assertRedirect();
        $this->assertCount(1, $project->fresh()->images);
        Storage::disk('public')->assertMissing($image->path);

        // Delete project and its files
        $paths = $project->fresh()->images->pluck('path')->push($project->featured_image);
        $this->delete("/admin/projects/{$project->slug}")->assertRedirect(route('admin.projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $paths->each(fn ($p) => Storage::disk('public')->assertMissing($p));
    }

    public function test_project_slugs_are_unique_and_validation_applies(): void
    {
        $this->project(['name' => 'Same Name', 'slug' => 'same-name']);

        $this->post('/admin/projects', ['name' => 'Same Name', 'status' => 'planned', 'description' => 'x'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('projects', ['slug' => 'same-name-2']);

        $this->post('/admin/projects', ['name' => '', 'status' => 'bogus'])->assertSessionHasErrors(['name', 'status', 'description']);
        $this->post('/admin/projects', ['name' => 'Bad Upload', 'status' => 'planned', 'description' => 'x', 'featured_image' => UploadedFile::fake()->create('x.php', 5, 'text/x-php')])
            ->assertSessionHasErrors('featured_image');
    }

    public function test_uploaded_images_are_reencoded_and_resized(): void
    {
        $this->post('/admin/projects', [
            'name' => 'Big Photo', 'status' => 'planned', 'description' => 'x',
            'featured_image' => UploadedFile::fake()->image('big.jpg', 4000, 2000),
        ])->assertSessionHasNoErrors();

        $path = Project::firstWhere('slug', 'big-photo')->featured_image;
        [$w] = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame(2200, $w);
    }

    public function test_service_crud_publish_toggle_and_public_detail_page(): void
    {
        $this->post('/admin/services', [
            'name' => 'Landscaping', 'icon' => 'leaf', 'description' => 'Outdoor works.', 'content' => "Para one.\n\nPara two.",
            'features' => "Lawns\nPaving", 'benefits' => "Looks great\n\nLow upkeep", 'sort_order' => 3, 'is_published' => 1,
            'main_image' => UploadedFile::fake()->image('s.jpg', 900, 600),
        ])->assertSessionHasNoErrors();

        $service = Service::firstWhere('slug', 'landscaping');
        $this->assertSame(['Lawns', 'Paving'], $service->features);
        $this->assertSame(['Looks great', 'Low upkeep'], $service->benefits);
        $this->get('/services/landscaping')->assertOk()->assertSee('Para two.')->assertSee('Paving');

        $this->patch("/admin/services/{$service->slug}/toggle")->assertRedirect();
        $this->assertFalse($service->fresh()->is_published);
        $this->get('/services/landscaping')->assertNotFound();

        $this->put("/admin/services/{$service->slug}", ['name' => 'Landscaping & Paving', 'slug' => 'landscaping', 'icon' => 'leaf', 'description' => 'New', 'is_published' => 1])->assertSessionHasNoErrors();
        $this->assertSame('Landscaping & Paving', $service->fresh()->name);

        $this->delete("/admin/services/{$service->slug}")->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseCount('services', 0);
    }

    public function test_service_rejects_unknown_icon(): void
    {
        $this->post('/admin/services', ['name' => 'X', 'icon' => '<script>', 'description' => 'd'])->assertSessionHasErrors('icon');
    }

    public function test_messages_can_be_read_archived_and_deleted(): void
    {
        $message = ContactMessage::create(['name' => 'A', 'email' => 'a@a.com', 'subject' => 'Hello', 'message' => 'Message body text.']);

        $this->get("/admin/messages/{$message->id}")->assertOk()->assertSee('Message body text.');
        $this->assertSame('read', $message->fresh()->status);

        $this->patch("/admin/messages/{$message->id}", ['status' => 'archived'])->assertRedirect(route('admin.messages.index'));
        $this->get('/admin/messages')->assertDontSee('Hello');
        $this->get('/admin/messages?status=archived')->assertSee('Hello');

        $this->delete("/admin/messages/{$message->id}")->assertRedirect();
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_quote_search_filter_status_notes_document_and_reply(): void
    {
        Mail::fake();
        $a = QuoteRequest::create(['name' => 'Alice Builder', 'email' => 'alice@example.com', 'phone' => '+1 555 1111', 'project_type' => 'Residential', 'project_location' => 'Oak St', 'description' => 'A new family home build.']);
        $b = QuoteRequest::create(['name' => 'Bob Retail', 'email' => 'bob@example.com', 'phone' => '+1 555 2222', 'project_type' => 'Commercial', 'project_location' => 'Main St', 'description' => 'A retail shop fit-out job.', 'status' => 'quoted']);

        $this->get('/admin/quotes?q=Alice')->assertSee('Alice Builder')->assertDontSee('Bob Retail');
        $this->get('/admin/quotes?q='.$b->reference)->assertSee('Bob Retail')->assertDontSee('Alice Builder');
        $this->get('/admin/quotes?status=quoted')->assertSee('Bob Retail')->assertDontSee('Alice Builder');

        $this->patch("/admin/quotes/{$a->id}", ['status' => 'reviewing', 'internal_notes' => 'Call back Monday'])->assertRedirect();
        $this->assertSame('Call back Monday', $a->fresh()->internal_notes);

        // Attached documents download through the authenticated route only
        Storage::disk('local')->put('quote-documents/plan.pdf', 'PDFDATA');
        $a->update(['document_path' => 'quote-documents/plan.pdf', 'document_name' => 'plan.pdf']);
        $this->get("/admin/quotes/{$a->id}/document")->assertOk()->assertDownload('plan.pdf');

        // Respond to the customer
        $this->post("/admin/quotes/{$a->id}/respond", ['subject' => 'Your quote', 'body' => 'Thanks, here is our proposal details.'])->assertSessionHas('status');
        Mail::assertSent(QuoteReply::class, fn ($m) => $m->hasTo('alice@example.com'));
        $this->assertNotNull($a->responses()->first()->sent_at);
    }

    public function test_quote_documents_require_login(): void
    {
        $quote = QuoteRequest::create(['name' => 'A', 'email' => 'a@a.com', 'phone' => '+1 555 1111', 'project_type' => 'Other', 'project_location' => 'X', 'description' => 'Description here long enough.', 'document_path' => 'quote-documents/x.pdf']);
        Storage::disk('local')->put('quote-documents/x.pdf', 'x');

        auth()->logout();
        $this->get("/admin/quotes/{$quote->id}/document")->assertRedirect(route('admin.login'));
    }

    public function test_failed_reply_email_is_recorded_not_lost(): void
    {
        $quote = QuoteRequest::create(['name' => 'A', 'email' => 'a@a.com', 'phone' => '+1 555 1111', 'project_type' => 'Other', 'project_location' => 'X', 'description' => 'Description here long enough.']);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP is down'));

        $this->post("/admin/quotes/{$quote->id}/respond", ['subject' => 'Re', 'body' => 'Long enough reply message.'])->assertSessionHas('error');

        $response = $quote->responses()->first();
        $this->assertNull($response->sent_at);
        $this->assertStringContainsString('SMTP is down', $response->error);
    }

    public function test_gallery_upload_categorise_and_delete(): void
    {
        $category = $this->category('Commercial');

        $this->post('/admin/gallery', [
            'images' => [UploadedFile::fake()->image('1.jpg', 800, 600), UploadedFile::fake()->image('2.jpg', 800, 600)],
            'project_category_id' => $category->id, 'title' => 'Site visit',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, GalleryImage::where('project_category_id', $category->id)->count());

        $image = GalleryImage::first();
        $this->patch("/admin/gallery/{$image->id}", ['title' => 'Renamed', 'project_category_id' => null])->assertRedirect();
        $this->assertSame('Renamed', $image->fresh()->title);

        $this->delete("/admin/gallery/{$image->id}")->assertRedirect();
        Storage::disk('public')->assertMissing($image->path);
        $this->get('/gallery')->assertOk();
    }

    public function test_testimonials_appear_only_after_approval(): void
    {
        $this->post('/admin/testimonials', ['author_name' => 'Pat', 'content' => 'Wonderful team to work with.', 'rating' => 5])->assertSessionHasNoErrors();
        $t = Testimonial::first();
        $this->get('/')->assertDontSee('Wonderful team');

        $this->put("/admin/testimonials/{$t->id}", ['author_name' => 'Pat', 'content' => 'Wonderful team to work with.', 'rating' => 5, 'is_approved' => 1])->assertRedirect();
        $this->get('/')->assertSee('Wonderful team');
    }

    public function test_statistics_are_editable_and_drive_the_homepage(): void
    {
        $stat = CompanyStat::create(['label' => 'Projects Completed', 'value' => 0, 'icon' => 'building']);

        $this->put('/admin/stats', ['stats' => [$stat->id => ['label' => 'Projects Completed', 'value' => 120, 'suffix' => '+', 'prefix' => '', 'icon' => 'building', 'is_visible' => 1]]])
            ->assertSessionHasNoErrors();

        $this->assertSame(120, $stat->fresh()->value);
        $this->get('/')->assertSee('counter(120)', false);
    }

    public function test_website_content_overrides_defaults_and_blank_restores_them(): void
    {
        $this->put('/admin/content', ['home__hero_title' => 'Constructing', 'home__hero_text' => 'Custom hero text.'])->assertSessionHasNoErrors();
        $this->get('/')->assertSee('Constructing')->assertSee('Custom hero text.');

        $this->put('/admin/content', ['home__hero_title' => ''])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('page_contents', ['key' => 'home.hero_title']);
        $this->get('/')->assertSee('Building Better Spaces');
    }

    public function test_hero_background_can_be_replaced_and_reset(): void
    {
        $this->put('/admin/content', ['home__hero_image' => UploadedFile::fake()->image('bg.jpg', 1600, 900)])->assertSessionHasNoErrors();
        $path = PageContent::where('key', 'home.hero_image')->value('value');
        Storage::disk('public')->assertExists($path);
        $this->get('/')->assertSee($path, false);

        $this->put('/admin/content', ['reset__home__hero_image' => 1])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($path);
        $this->get('/')->assertSee('images/hero-1280.jpg', false);
    }

    public function test_settings_update_company_details_logo_and_seo(): void
    {
        $this->get('/admin/settings')
            ->assertOk()
            ->assertSee('name="tiktok"', false);

        $this->put('/admin/settings', [
            'company_name' => 'R-Square Group', 'email' => 'hello@rsquare.example', 'phone' => '+234 800 000 0000',
            'whatsapp' => '+2348000000000', 'address' => '1 Builder Way', 'facebook' => 'https://facebook.com/rsquare',
            'tiktok' => 'https://tiktok.com/@rsquare',
            'seo_title' => 'Custom SEO Title', 'logo' => UploadedFile::fake()->image('logo.png', 400, 120),
        ])->assertSessionHasNoErrors();

        $this->assertSame('R-Square Group', SiteSetting::where('key', 'company_name')->value('value'));
        $this->assertSame('https://tiktok.com/@rsquare', SiteSetting::where('key', 'tiktok')->value('value'));

        $this->get('/')
            ->assertSee('hello@rsquare.example')
            ->assertSee('1 Builder Way')
            ->assertSee('https://facebook.com/rsquare', false)
            ->assertSee('https://tiktok.com/@rsquare', false)
            ->assertSee('R-Square Group on TikTok')
            ->assertSee('https://wa.me/2348000000000', false)
            ->assertSee('<title>Custom SEO Title</title>', false)
            ->assertSee(SiteSetting::where('key', 'logo')->value('value'), false);
    }

    public function test_settings_reject_svg_logos_and_javascript_urls(): void
    {
        $this->put('/admin/settings', ['company_name' => 'X', 'logo' => UploadedFile::fake()->create('logo.svg', 4, 'image/svg+xml')])->assertSessionHasErrors('logo');
        $this->put('/admin/settings', ['company_name' => 'X', 'tiktok' => 'javascript:alert(1)'])->assertSessionHasErrors('tiktok');
    }

    public function test_dashboard_numbers_come_from_the_database(): void
    {
        $this->project(['slug' => 'a', 'name' => 'A', 'status' => 'ongoing']);
        $this->project(['slug' => 'b', 'name' => 'B', 'status' => 'completed']);
        $this->project(['slug' => 'c', 'name' => 'C', 'status' => 'completed']);
        $this->service();
        ContactMessage::create(['name' => 'N', 'email' => 'n@n.com', 'subject' => 'Brand new thing', 'message' => 'Body of message.']);

        $response = $this->get('/admin');

        $response->assertOk()->assertSeeInOrder(['Total Projects', 'Ongoing Projects', 'Completed Projects', 'Total Services', 'New Messages', 'Pending Quotes'])
            ->assertSee('Brand new thing');
        $this->assertSame(3, Project::count());
    }
}
