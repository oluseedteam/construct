<?php

namespace Tests\Feature;

use App\Models\CompanyStat;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_every_admin_route(): void
    {
        foreach (['', '/projects', '/projects/create', '/services', '/quotes', '/messages', '/gallery', '/testimonials', '/stats', '/content', '/settings'] as $path) {
            $this->get('/admin'.$path)->assertRedirect(route('admin.login'));
        }

        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_sign_in_and_out(): void
    {
        $this->admin();

        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'correct-horse-battery-1'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->admin();

        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_repeated_failures(): void
    {
        $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'wrong']);
        }

        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'correct-horse-battery-1'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_accounts_cannot_sign_in(): void
    {
        $this->admin(['is_active' => false]);

        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'correct-horse-battery-1'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_session_is_kicked_out(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->get('/admin')->assertOk();

        $user->update(['is_active' => false]);

        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_there_is_no_public_registration(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'x', 'email' => 'x@x.com', 'password' => 'password123'])->assertStatus(404);
    }

    public function test_admin_pages_are_not_indexable_or_cacheable(): void
    {
        $this->actingAs($this->admin())->get('/admin')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_every_admin_screen_renders_for_an_admin(): void
    {
        $admin = $this->admin();
        $project = $this->project();
        $service = $this->service();
        CompanyStat::create(['label' => 'Projects', 'value' => 1]);
        Testimonial::create(['author_name' => 'A', 'content' => 'Great work here.', 'rating' => 5]);

        foreach (['', '/projects', '/projects/create', "/projects/{$project->slug}/edit", '/services', '/services/create',
            "/services/{$service->slug}/edit", '/quotes', '/messages', '/gallery', '/testimonials', '/testimonials/create',
            '/stats', '/content', '/settings'] as $path) {
            $this->actingAs($admin)->get('/admin'.$path)->assertOk();
        }
    }

    public function test_editor_cannot_open_settings_or_delete_content(): void
    {
        $editor = $this->editor();
        $project = $this->project();
        $service = $this->service();

        $this->actingAs($editor)->get('/admin/settings')->assertForbidden();
        $this->actingAs($editor)->put('/admin/settings', ['company_name' => 'Hacked'])->assertForbidden();
        $this->actingAs($editor)->delete("/admin/projects/{$project->slug}")->assertForbidden();
        $this->actingAs($editor)->delete("/admin/services/{$service->slug}")->assertForbidden();

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('services', ['id' => $service->id]);

        // ...but editors can still do day-to-day work.
        $this->actingAs($editor)->get('/admin/projects')->assertOk();
        $this->actingAs($editor)->patch("/admin/services/{$service->slug}/toggle")->assertRedirect();
    }

    public function test_user_without_a_dashboard_role_is_refused(): void
    {
        $user = $this->admin(['role' => 'customer']);

        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
