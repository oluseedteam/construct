<?php

namespace Tests\Feature;

use App\Mail\NewContactMessage;
use App\Models\Highlight;
use App\Models\JobOpening;
use App\Models\PageContent;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Site;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EditableContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_flyer_details_are_the_defaults(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('437-241-9877')
            ->assertSee('rsquareconstruction@gmail.com')
            ->assertSee('Ontario, Canada')
            ->assertSee('tel:4372419877', false);
        $this->get('/')->assertSee('Build • Renovate • Improve');
    }

    public function test_seeder_creates_the_six_flyer_services_and_highlights(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['Residential Construction', 'General Contracting', 'Commercial Construction', 'Interior & Exterior Finishing', 'Renovations & Remodeling', 'Maintenance & Repairs'] as $name) {
            $this->assertDatabaseHas('services', ['name' => $name, 'is_published' => true]);
        }

        $this->get('/')->assertSee('Custom homes, additions and renovations.')
            ->assertSee('Project management from start to finish.')
            ->assertSee('Quality Workmanship')->assertSee('Reliable Service')->assertSee('Client Satisfaction');
        $this->get('/services')->assertSee('Kitchens, bathrooms, painting, flooring and more.');

        // Seeding twice must not duplicate or overwrite admin edits.
        Highlight::where('title', 'Safety First')->update(['title' => 'Safety Always']);
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(1, Highlight::where('title', 'Safety Always')->count());
        $this->assertSame(0, Highlight::where('title', 'Safety First')->count());
    }

    public function test_every_schema_field_is_editable_and_blank_restores_the_default(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/content')->assertOk();

        // Submit every text field in the schema with a recognisable value.
        $payload = [];
        foreach (config('rsquare.content_schema') as $fields) {
            foreach ($fields as $key => [, $type, $max]) {
                if ($type !== 'image') {
                    $payload[str_replace('.', '__', $key)] = 'EDIT '.$key;
                }
            }
        }

        $this->put('/admin/content', $payload)->assertSessionHasNoErrors();
        $this->assertSame(count($payload), PageContent::count());

        foreach (['/', '/about', '/services', '/projects', '/gallery', '/why-choose-us', '/contact', '/request-a-quote', '/careers'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/')->assertSee('EDIT home.hero_title')->assertSee('EDIT home.cta_title')->assertSee('EDIT footer.about');
        $this->get('/about')->assertSee('EDIT about.hero_title')->assertSee('EDIT about.mission');
        $this->get('/services')->assertSee('EDIT services.hero_title');
        $this->get('/contact')->assertSee('EDIT contact.intro_title');
        $this->get('/careers')->assertSee('EDIT careers.hero_title');
        $this->get('/request-a-quote')->assertSee('EDIT quote.hero_title');

        // Clearing everything restores the defaults.
        $this->put('/admin/content', array_map(fn () => '', $payload))->assertSessionHasNoErrors();
        $this->assertSame(0, PageContent::count());

        // Stale cache would still show the edited text here (the default also appears in the <title>, so check the edit is gone).
        $this->get('/')->assertDontSee('EDIT home.hero_title')->assertDontSee('EDIT home.cta_title')->assertSee('Build Something Extraordinary');
        $this->get('/about')->assertDontSee('EDIT about.hero_title');
    }

    public function test_a_partial_submission_does_not_wipe_other_content(): void
    {
        $this->actingAs($this->admin());
        PageContent::create(['key' => 'home.hero_title', 'value' => 'Keep Me']);

        $this->put('/admin/content', ['home__cta_title' => 'New CTA'])->assertSessionHasNoErrors();

        $this->assertSame('Keep Me', PageContent::where('key', 'home.hero_title')->value('value'));
    }

    public function test_content_schema_defaults_all_resolve(): void
    {
        $defaults = Site::schemaDefaults();

        $this->assertNotEmpty($defaults);
        foreach (['home.hero_title', 'about.story', 'careers.empty_text', 'footer.about', 'home.hero_image'] as $key) {
            $this->assertArrayHasKey($key, $defaults);
        }
        $this->assertSame('Building Better Spaces', content('home.hero_title'));
    }

    public function test_highlights_are_manageable_and_drive_the_website(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/highlights')->assertOk();

        $this->post('/admin/highlights', ['group' => 'why', 'title' => 'Fully Insured', 'text' => 'Coverage you can count on.', 'icon' => 'shield', 'is_visible' => 1])->assertSessionHasNoErrors();
        $item = Highlight::firstWhere('title', 'Fully Insured');
        $this->get('/why-choose-us')->assertSee('Fully Insured')->assertSee('Coverage you can count on.');

        $this->put("/admin/highlights/{$item->id}", ['group' => 'why', 'title' => 'Fully Insured', 'icon' => 'shield', 'is_visible' => 0])->assertSessionHasNoErrors();
        $this->get('/why-choose-us')->assertDontSee('Fully Insured');

        $this->post('/admin/highlights', ['group' => 'commitments', 'title' => 'Licensed Trades', 'icon' => 'check', 'is_visible' => 1]);
        $this->get('/about')->assertSee('Licensed Trades');

        $this->post('/admin/highlights', ['group' => 'nope', 'title' => '', 'icon' => '<svg>'])->assertSessionHasErrors(['group', 'title', 'icon']);

        $this->delete("/admin/highlights/{$item->id}")->assertRedirect();
        $this->assertDatabaseMissing('highlights', ['id' => $item->id]);
    }

    public function test_tagline_and_contact_details_are_editable_in_settings(): void
    {
        $this->actingAs($this->admin());

        $this->put('/admin/settings', ['company_name' => 'R Square Construction', 'tagline' => 'Built Right', 'phone' => '416-000-1111', 'email' => 'hello@rsquare.example'])->assertSessionHasNoErrors();

        $this->get('/')->assertSee('Built Right')->assertSee('416-000-1111');
    }

    public function test_admin_can_manage_staff_accounts_but_editors_cannot(): void
    {
        $admin = $this->admin();
        $this->actingAs($this->editor())->get('/admin/users')->assertForbidden();
        $this->post('/admin/users', ['name' => 'X'])->assertForbidden();

        $this->actingAs($admin);
        $this->get('/admin/users')->assertOk()->assertSee('Editor');

        $this->post('/admin/users', ['name' => 'New Staff', 'email' => 'staff@example.test', 'role' => 'editor', 'password' => 'a-long-password-123', 'password_confirmation' => 'a-long-password-123', 'is_active' => 1])->assertSessionHasNoErrors();
        $staff = User::firstWhere('email', 'staff@example.test');
        $this->assertSame('editor', $staff->role);
        $this->assertNotSame('a-long-password-123', $staff->password);

        $this->post('/admin/users', ['name' => 'Weak', 'email' => 'weak@example.test', 'role' => 'superuser', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors(['role', 'password']);
        $this->post('/admin/users', ['name' => 'Dup', 'email' => 'staff@example.test', 'role' => 'editor', 'password' => 'a-long-password-123', 'password_confirmation' => 'a-long-password-123'])->assertSessionHasErrors('email');

        $this->put("/admin/users/{$staff->id}", ['name' => 'Renamed', 'email' => 'staff@example.test', 'role' => 'editor', 'is_active' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($staff->fresh()->is_active);
        $this->assertTrue(\Hash::check('a-long-password-123', $staff->fresh()->password));

        $this->delete("/admin/users/{$staff->id}")->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_the_last_active_admin_cannot_be_removed_demoted_or_deleted(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->delete("/admin/users/{$admin->id}")->assertSessionHas('error');
        $this->put("/admin/users/{$admin->id}", ['name' => 'Admin', 'email' => $admin->email, 'role' => 'editor', 'is_active' => 1])->assertSessionHas('error');
        $this->put("/admin/users/{$admin->id}", ['name' => 'Admin', 'email' => $admin->email, 'role' => 'admin', 'is_active' => 0])->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->isAdmin() && $admin->fresh()->is_active);
    }

    public function test_staff_can_update_their_own_profile_and_password(): void
    {
        $editor = $this->editor();
        $this->actingAs($editor)->get('/admin/profile')->assertOk();

        $this->put('/admin/profile', ['name' => 'Ed Itor', 'email' => 'editor@example.test'])->assertSessionHasNoErrors();
        $this->assertSame('Ed Itor', $editor->fresh()->name);

        $this->put('/admin/profile', ['name' => 'Ed Itor', 'email' => 'editor@example.test', 'current_password' => 'wrong-password-1', 'password' => 'brand-new-password-9', 'password_confirmation' => 'brand-new-password-9'])
            ->assertSessionHasErrors('current_password');

        $this->put('/admin/profile', ['name' => 'Ed Itor', 'email' => 'editor@example.test', 'current_password' => 'correct-horse-battery-1', 'password' => 'brand-new-password-9', 'password_confirmation' => 'brand-new-password-9'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(\Hash::check('brand-new-password-9', $editor->fresh()->password));
    }

    public function test_notifications_fall_back_to_admin_accounts_when_no_company_email_is_set(): void
    {
        Mail::fake();
        $this->admin();
        SiteSetting::create(['key' => 'email', 'value' => null]);
        config(['rsquare.settings.email' => '']);

        $this->post('/contact', ['name' => 'J', 'email' => 'j@example.com', 'subject' => 'Hello there', 'message' => 'A message long enough.']);

        Mail::assertSent(NewContactMessage::class, fn ($m) => $m->hasTo('admin@example.test'));
    }

    public function test_admin_screens_for_new_sections_render(): void
    {
        $this->actingAs($this->admin());
        $job = JobOpening::create(['title' => 'J', 'slug' => 'j', 'summary' => 'S', 'employment_type' => 'Full-time']);
        $item = Highlight::create(['group' => 'why', 'title' => 'T', 'icon' => 'check']);

        foreach (['/jobs', '/jobs/create', "/jobs/{$job->slug}/edit", '/applications', '/highlights', '/highlights/create', "/highlights/{$item->id}/edit", '/users', '/users/create', '/profile', '/content', '/settings'] as $path) {
            $this->get('/admin'.$path)->assertOk();
        }
    }
}
