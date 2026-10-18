<?php

namespace Tests\Feature;

use App\Mail\ApplicationReceived;
use App\Mail\NewJobApplication;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareersTest extends TestCase
{
    use RefreshDatabase;

    private function job(array $over = []): JobOpening
    {
        return JobOpening::create(array_merge([
            'title' => 'Site Carpenter', 'slug' => 'site-carpenter', 'employment_type' => 'Full-time',
            'location' => 'Ontario', 'summary' => 'Frame and finish residential builds.',
            'description' => "Paragraph one.\n\nParagraph two.", 'requirements' => ['Own tools', '3 years experience'],
            'is_published' => true,
        ], $over));
    }

    private function applicant(array $over = []): array
    {
        return array_merge([
            'name' => 'Sam Builder', 'email' => 'sam@example.com', 'phone' => '437-555-0100', 'city' => 'Toronto',
            'experience' => '3-5 years', 'cover_letter' => 'I have framed many homes.',
            'resume' => UploadedFile::fake()->create('resume.pdf', 300, 'application/pdf'),
        ], $over);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_careers_pages_load_and_list_open_jobs_only(): void
    {
        $open = $this->job();
        $hidden = $this->job(['title' => 'Hidden Role', 'slug' => 'hidden-role', 'is_published' => false]);
        $expired = $this->job(['title' => 'Expired Role', 'slug' => 'expired-role', 'closes_at' => now()->subDay()]);

        $this->get('/careers')->assertOk()->assertSee('Site Carpenter')->assertDontSee('Hidden Role')->assertDontSee('Expired Role');
        $this->get("/careers/{$open->slug}")->assertOk()->assertSee('Own tools')->assertSee('Paragraph two.')->assertSee('Submit Application');
        $this->get("/careers/{$hidden->slug}")->assertNotFound();
        $this->get("/careers/{$expired->slug}")->assertNotFound();
        $this->get('/careers/apply')->assertOk()->assertSee('General application');
    }

    public function test_careers_page_invites_general_applications_when_nothing_is_open(): void
    {
        $this->get('/careers')->assertOk()->assertSee('No open positions right now')->assertSee(route('careers.apply'), false);
    }

    public function test_careers_is_a_top_level_link_in_the_main_navbar(): void
    {
        $html = $this->get('/')->getContent();

        preg_match('#<nav[^>]*aria-label="Primary".*?</nav>#s', $html, $nav);
        $this->assertNotEmpty($nav, 'primary nav not found');
        $this->assertMatchesRegularExpression('#<a href="'.preg_quote(route('careers.index'), '#').'"[^>]*>\s*Careers#', $nav[0]);
        $this->assertLessThan(strpos($nav[0], '>Contact'), strpos($nav[0], '>Careers'), 'Careers should come before Contact');

        // ...and in the mobile menu
        preg_match('#id="mobile-nav".*?</nav>#s', $html, $mobile);
        $this->assertStringContainsString('Careers', $mobile[0]);
    }

    public function test_careers_is_in_the_navigation_and_sitemap(): void
    {
        $job = $this->job();

        $this->get('/')->assertSee(route('careers.index'), false);
        $this->get('/sitemap.xml')->assertSee(route('careers.show', $job), false)->assertSee(route('careers.index'), false);
    }

    public function test_applying_to_a_job_saves_everything_privately_and_sends_emails(): void
    {
        Mail::fake();
        $job = $this->job();

        $this->followingRedirects()->post('/careers/apply', $this->applicant(['job_opening_id' => $job->id]))
            ->assertSee('Application received');

        $app = JobApplication::first();
        $this->assertMatchesRegularExpression('/^APP-\d{8}-[A-Z0-9]{6}$/', $app->reference);
        $this->assertSame($job->id, $app->job_opening_id);
        $this->assertSame('Site Carpenter', $app->position);
        $this->assertSame('resume.pdf', $app->resume_name);
        $this->assertSame('new', $app->status);
        Storage::disk('local')->assertExists($app->resume_path);

        Mail::assertSent(NewJobApplication::class, fn ($m) => $m->hasTo('rsquareconstruction@gmail.com') && $m->hasReplyTo('sam@example.com'));
        Mail::assertSent(ApplicationReceived::class, fn ($m) => $m->hasTo('sam@example.com'));
    }

    public function test_general_application_has_no_job(): void
    {
        Mail::fake();

        $this->post('/careers/apply', $this->applicant())->assertSessionHasNoErrors();

        $app = JobApplication::first();
        $this->assertNull($app->job_opening_id);
        $this->assertSame(JobApplication::GENERAL, $app->position);
    }

    public function test_application_is_rejected_for_a_closed_opening(): void
    {
        Mail::fake();
        $job = $this->job(['is_published' => false]);

        $this->post('/careers/apply', $this->applicant(['job_opening_id' => $job->id]))->assertSessionHas('error');
        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_application_validation_and_dangerous_uploads(): void
    {
        $this->post('/careers/apply', [])->assertSessionHasErrors(['name', 'email', 'phone', 'resume']);
        $this->post('/careers/apply', $this->applicant(['resume' => UploadedFile::fake()->create('shell.php', 5, 'text/x-php')]))->assertSessionHasErrors('resume');
        $this->post('/careers/apply', $this->applicant(['resume' => UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf')]))->assertSessionHasErrors('resume');
        $this->post('/careers/apply', $this->applicant(['profile_url' => 'javascript:alert(1)']))->assertSessionHasErrors('profile_url');

        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_application_is_never_lost_when_email_fails(): void
    {
        $this->admin();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP is down'));

        $this->post('/careers/apply', $this->applicant())->assertRedirect()->assertSessionHas('application_reference');

        $app = JobApplication::first();
        $this->assertNotNull($app);
        $this->assertNull($app->admin_notified_at);
        Storage::disk('local')->assertExists($app->resume_path);
    }

    public function test_honeypot_and_rate_limit(): void
    {
        Mail::fake();

        $this->post('/careers/apply', $this->applicant(['website' => 'http://spam.example']))->assertRedirect();
        $this->assertDatabaseCount('job_applications', 0);

        // The honeypot request above already used 1 of the 5 allowed per minute.
        for ($i = 0; $i < 4; $i++) {
            $this->post('/careers/apply', $this->applicant())->assertRedirect();
        }
        $this->post('/careers/apply', $this->applicant())->assertStatus(429);
    }

    public function test_job_opening_admin_crud_and_toggle(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/jobs', [
            'title' => 'Project Manager', 'employment_type' => 'Contract', 'location' => 'Ontario', 'summary' => 'Run our sites.',
            'requirements' => "PMP preferred\n\nValid licence", 'is_published' => 1,
        ])->assertSessionHasNoErrors();

        $job = JobOpening::firstWhere('slug', 'project-manager');
        $this->assertSame(['PMP preferred', 'Valid licence'], $job->requirements);
        $this->get('/careers')->assertSee('Project Manager');

        $this->patch("/admin/jobs/{$job->slug}/toggle")->assertRedirect();
        $this->get('/careers')->assertDontSee('Project Manager');

        $this->put("/admin/jobs/{$job->slug}", ['title' => 'Senior Project Manager', 'slug' => 'project-manager', 'employment_type' => 'Full-time', 'summary' => 'Run sites', 'is_published' => 1])->assertSessionHasNoErrors();
        $this->assertSame('Senior Project Manager', $job->fresh()->title);

        $this->post('/admin/jobs', ['title' => '', 'employment_type' => 'Hobby', 'summary' => ''])->assertSessionHasErrors(['title', 'employment_type', 'summary']);

        // Deleting an opening keeps its applications.
        Mail::fake();
        $this->post('/careers/apply', $this->applicant(['job_opening_id' => $job->id]));
        $this->delete("/admin/jobs/{$job->slug}")->assertRedirect(route('admin.jobs.index'));
        $this->assertDatabaseCount('job_applications', 1);
        $this->assertNull(JobApplication::first()->job_opening_id);
        $this->assertSame('Senior Project Manager', JobApplication::first()->position);
    }

    public function test_admin_can_review_filter_update_and_download_applications(): void
    {
        $this->actingAs($this->admin());
        $job = $this->job();
        Storage::disk('local')->put('resumes/a.pdf', 'PDFDATA');
        $a = JobApplication::create(['job_opening_id' => $job->id, 'position' => $job->title, 'name' => 'Alice Framer', 'email' => 'alice@example.com', 'phone' => '437-555-0001', 'resume_path' => 'resumes/a.pdf', 'resume_name' => 'alice-cv.pdf', 'cover_letter' => 'Hire me please.']);
        $b = JobApplication::create(['position' => JobApplication::GENERAL, 'name' => 'Bob Painter', 'email' => 'bob@example.com', 'phone' => '437-555-0002', 'resume_path' => 'resumes/b.pdf', 'resume_name' => 'b.pdf', 'status' => 'interview']);

        $this->get('/admin/applications')->assertOk()->assertSee('Alice Framer')->assertSee('Bob Painter');
        $this->get('/admin/applications?q=Alice')->assertSee('Alice Framer')->assertDontSee('Bob Painter');
        $this->get('/admin/applications?status=interview')->assertSee('Bob Painter')->assertDontSee('Alice Framer');
        $this->get('/admin/applications?job=general')->assertSee('Bob Painter')->assertDontSee('Alice Framer');
        $this->get("/admin/applications?job={$job->id}")->assertSee('Alice Framer')->assertDontSee('Bob Painter');

        $this->get("/admin/applications/{$a->id}")->assertOk()->assertSee('Hire me please.')->assertSee('alice-cv.pdf');

        $this->patch("/admin/applications/{$a->id}", ['status' => 'reviewing', 'internal_notes' => 'Strong portfolio'])->assertRedirect();
        $this->assertSame('reviewing', $a->fresh()->status);
        $this->assertSame('Strong portfolio', $a->fresh()->internal_notes);
        $this->patch("/admin/applications/{$a->id}", ['status' => 'bogus'])->assertSessionHasErrors('status');

        $this->get("/admin/applications/{$a->id}/resume")->assertOk()->assertDownload('alice-cv.pdf');

        $this->delete("/admin/applications/{$a->id}")->assertRedirect(route('admin.applications.index'));
        Storage::disk('local')->assertMissing('resumes/a.pdf');
        $this->assertDatabaseMissing('job_applications', ['id' => $a->id]);
    }

    public function test_resumes_and_applications_require_login_and_editors_cannot_delete(): void
    {
        Storage::disk('local')->put('resumes/a.pdf', 'x');
        $a = JobApplication::create(['position' => 'X', 'name' => 'A', 'email' => 'a@a.com', 'phone' => '437-555-0001', 'resume_path' => 'resumes/a.pdf', 'resume_name' => 'a.pdf']);

        $this->get("/admin/applications/{$a->id}/resume")->assertRedirect(route('admin.login'));
        $this->get('/admin/applications')->assertRedirect(route('admin.login'));

        $this->actingAs($this->editor())->get("/admin/applications/{$a->id}")->assertOk();
        $this->delete("/admin/applications/{$a->id}")->assertForbidden();
        $this->assertDatabaseHas('job_applications', ['id' => $a->id]);
    }

    public function test_dashboard_surfaces_new_applications(): void
    {
        $this->actingAs($this->admin());
        JobApplication::create(['position' => 'Site Carpenter', 'name' => 'Alice Framer', 'email' => 'a@a.com', 'phone' => '437-555-0001', 'resume_path' => 'resumes/a.pdf', 'resume_name' => 'a.pdf']);

        $this->get('/admin')->assertOk()->assertSee('New Job Applications')->assertSee('Alice Framer');
    }
}
