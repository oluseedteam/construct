<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_logo_and_icon_files_exist_and_are_real_images(): void
    {
        foreach (['images/logo-mark.png', 'images/logo-mark-light.png', 'favicon-32.png', 'favicon-192.png', 'apple-touch-icon.png', 'images/og-image.jpg'] as $file) {
            $this->assertFileExists(public_path($file));
            $this->assertNotFalse(getimagesize(public_path($file)), "$file is not a valid image");
        }

        // favicon.ico is a real ICO (reserved=0, type=1, 3 images) not Laravel's placeholder
        $header = unpack('vreserved/vtype/vcount', file_get_contents(public_path('favicon.ico'), false, null, 0, 6));
        $this->assertSame([0, 1, 3], array_values($header));
        [$w, $h] = getimagesize(public_path('apple-touch-icon.png'));
        $this->assertSame([180, 180], [$w, $h]);
        [$w, $h] = getimagesize(public_path('images/og-image.jpg'));
        $this->assertSame([1200, 630], [$w, $h]);
    }

    public function test_every_layout_links_the_site_icons(): void
    {
        $this->admin();

        foreach (['/', '/about', '/careers', '/admin/login'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('favicon.ico', false)
                ->assertSee('favicon-32.png', false)
                ->assertSee('apple-touch-icon.png', false);
        }

        // Standalone error pages carry the icon too.
        $this->get('/nope')->assertNotFound()->assertSee('favicon.ico', false);

        $this->actingAs($this->admin(['email' => 'a2@example.test']))->get('/admin')->assertSee('favicon-32.png', false);
    }

    public function test_the_navbar_and_footer_show_the_real_logo_mark(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('images/logo-mark.png', $html);        // header (dark mark on light bar)
        $this->assertStringContainsString('images/logo-mark-light.png', $html);  // footer (white mark on navy)
        $this->assertStringContainsString('R SQUARE', $html);
    }

    public function test_default_social_image_and_structured_data_use_the_brand_assets(): void
    {
        $this->get('/')
            ->assertSee('property="og:image" content="'.asset('images/og-image.jpg').'"', false)
            ->assertSee('"logo":"'.asset('favicon-192.png').'"', false);
    }

    public function test_custom_favicon_can_be_uploaded_and_removed(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->put('/admin/settings', ['company_name' => 'R Square Construction', 'favicon' => UploadedFile::fake()->image('icon.png', 256, 256)])->assertSessionHasNoErrors();

        $path = SiteSetting::where('key', 'favicon')->value('value');
        Storage::disk('public')->assertExists($path);
        $this->get('/')->assertSee(Storage::disk('public')->url($path), false)->assertDontSee('favicon-32.png', false);

        $this->put('/admin/settings', ['company_name' => 'R Square Construction', 'remove_favicon' => 1])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($path);
        $this->get('/')->assertSee('favicon-32.png', false);
    }

    public function test_favicon_rejects_tiny_svg_and_non_images(): void
    {
        $this->actingAs($this->admin());

        $this->put('/admin/settings', ['company_name' => 'X', 'favicon' => UploadedFile::fake()->image('tiny.png', 16, 16)])->assertSessionHasErrors('favicon');
        $this->put('/admin/settings', ['company_name' => 'X', 'favicon' => UploadedFile::fake()->create('icon.svg', 2, 'image/svg+xml')])->assertSessionHasErrors('favicon');
        $this->put('/admin/settings', ['company_name' => 'X', 'favicon' => UploadedFile::fake()->create('icon.php', 2, 'text/x-php')])->assertSessionHasErrors('favicon');
    }

    public function test_an_uploaded_logo_replaces_the_built_in_mark_everywhere(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->put('/admin/settings', ['company_name' => 'R Square Construction', 'logo' => UploadedFile::fake()->image('logo.png', 400, 120)])->assertSessionHasNoErrors();

        $this->get('/')->assertDontSee('images/logo-mark.png', false);
        $this->get('/admin')->assertDontSee('images/logo-mark-light.png', false);
    }

    public function test_google_site_verification_tag_is_on_the_public_site(): void
    {
        $tag = '<meta name="google-site-verification" content="oJRZCJwTgFHG26tPNM2Rmtx2sAIDlygBjQ42O2EqcD0" />';

        $this->get('/')->assertOk()->assertSee($tag, false);
        $this->get('/about')->assertSee($tag, false);
    }

    public function test_logo_mark_roof_is_orange_not_gold(): void
    {
        $im = imagecreatefrompng(public_path('images/logo-mark.png'));
        $orange = 0;
        $gold = 0;
        for ($y = 0; $y < imagesy($im); $y += 2) {
            for ($x = 0; $x < imagesx($im); $x += 2) {
                $c = imagecolorat($im, $x, $y);
                if ((($c >> 24) & 0x7F) > 100) {
                    continue;
                }
                [$r, $g, $b] = [($c >> 16) & 255, ($c >> 8) & 255, $c & 255];
                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                if ($max - $min < 60 || $max !== $r) {
                    continue; // grey/navy/white or not a warm colour
                }
                $hue = 60 * fmod(($g - $b) / ($max - $min), 6); // red is 0°, yellow 60°
                if ($hue >= 8 && $hue <= 34) {
                    $orange++;
                } elseif ($hue >= 38 && $hue <= 65) {
                    $gold++;
                }
            }
        }

        $this->assertGreaterThan(500, $orange, 'expected a large orange area (the roof)');
        $this->assertLessThan($orange / 20, $gold, 'gold pixels should be gone');
    }

    public function test_viewport_meta_and_responsive_basics_are_present_on_every_layout(): void
    {
        $viewport = '<meta name="viewport" content="width=device-width, initial-scale=1">';

        // public layout and the login page (as a guest)
        foreach (['/', '/careers', '/admin/login'] as $url) {
            $this->get($url)->assertSee($viewport, false);
        }

        // admin layout (signed in)
        $this->actingAs($this->admin())->get('/admin')->assertSee($viewport, false);
    }
}
