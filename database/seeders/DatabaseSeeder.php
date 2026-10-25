<?php

namespace Database\Seeders;

use App\Models\CompanyStat;
use App\Models\Highlight;
use App\Models\Page;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Production-safe seed: structure and starter copy only.
 * It never invents projects, reviews, jobs or achievements. For sample data in a
 * local/demo environment run: php artisan db:seed --class=DemoSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->categories();
        $this->services();
        $this->highlights();
        $this->pages();
        $this->stats();
        $this->admin();
    }

    /** Registers every built-in page so admins can reorder, rename or hide it (Admin > Pages). */
    private function pages(): void
    {
        Page::ensureBuiltins();
    }

    private function categories(): void
    {
        foreach (['Residential', 'Commercial', 'Infrastructure', 'Renovation', 'Other'] as $i => $name) {
            ProjectCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $i]);
        }
    }

    /** The six services from the company flyer. Admins can edit, reorder or unpublish them. */
    private function services(): void
    {
        $services = [
            [
                'name' => 'Residential Construction',
                'icon' => 'home',
                'image' => 'images/demo/flyer-house.jpg',
                'description' => 'Custom homes, additions and renovations.',
                'content' => "From custom homes to additions and full renovations, we build living spaces that are comfortable, well-finished and made to last.\n\nWe take time to understand how you live, then deliver craftsmanship you can see and feel in the finished home.",
                'benefits' => ['Tailored to your needs and budget', 'Clear communication from concept to completion', 'Exceptional workmanship you can see in the finish'],
                'features' => ['Custom homes', 'Home additions', 'Full home renovations', 'Outdoor and exterior works'],
            ],
            [
                'name' => 'General Contracting',
                'icon' => 'tools',
                'image' => 'images/demo/demo-1.jpg',
                'description' => 'Project management from start to finish.',
                'content' => "As your general contractor we manage the whole project: planning, scheduling, trades, materials and inspections, so you deal with one accountable team from start to finish.\n\nYou stay informed with clear updates at every stage.",
                'benefits' => ['One accountable team from start to finish', 'Clear scheduling and regular progress updates', 'Careful supervision of trades and materials'],
                'features' => ['Project planning and scheduling', 'Trade and supplier coordination', 'Site supervision', 'Quality checks and handover'],
            ],
            [
                'name' => 'Commercial Construction',
                'icon' => 'city',
                'image' => 'images/demo/demo-2.jpg',
                'description' => 'Office spaces, retail and commercial projects.',
                'content' => "We build and fit out offices, retail spaces and other commercial properties that work for the businesses inside them. We plan around your operations, budget and opening date.\n\nWhether it is a new build or an upgrade of an existing space, we focus on practical layouts and dependable delivery.",
                'benefits' => ['Planning around your operating needs', 'Practical layouts and durable finishes', 'Delivery focused on your timeline'],
                'features' => ['Office spaces', 'Retail spaces', 'Commercial fit-outs', 'Tenant improvements'],
            ],
            [
                'name' => 'Interior & Exterior Finishing',
                'icon' => 'tiles',
                'image' => 'images/demo/demo-6.jpg',
                'description' => 'Kitchens, bathrooms, painting, flooring and more.',
                'content' => "The finishing is what you see and use every day. We handle kitchens, bathrooms, painting, flooring and other interior and exterior finishes with careful attention to detail.\n\nGood finishing makes a space feel complete, and we do not rush it.",
                'benefits' => ['Careful attention to detail', 'Clean, tidy work areas', 'Finishes chosen to suit how you live'],
                'features' => ['Kitchens and bathrooms', 'Painting', 'Flooring', 'Exterior finishing'],
            ],
            [
                'name' => 'Renovations & Remodeling',
                'icon' => 'roller',
                'image' => 'images/demo/demo-7.jpg',
                'description' => 'Upgrade and transform your space.',
                'content' => "Renovating can transform how a space looks, feels and works. We help you plan the right scope, then carry out the work with as little disruption as possible.\n\nFrom a single room to a whole-home remodel, we bring your vision to life.",
                'benefits' => ['Careful assessment before work begins', 'Minimised disruption while we work', 'Results that transform the space'],
                'features' => ['Whole-home remodeling', 'Kitchen and bathroom renovations', 'Basement and room conversions', 'Layout changes and upgrades'],
            ],
            [
                'name' => 'Maintenance & Repairs',
                'icon' => 'bricks',
                'image' => 'images/demo/demo-5.jpg',
                'description' => 'Reliable solutions for long-lasting results.',
                'content' => "Properties need care to keep performing. We carry out maintenance and repairs that fix the problem properly, so the result lasts.\n\nFrom small repairs to ongoing maintenance, you can count on reliable service.",
                'benefits' => ['Reliable, responsive service', 'Repairs done properly the first time', 'Long-lasting results'],
                'features' => ['General repairs', 'Ongoing property maintenance', 'Structural and surface repairs', 'Upkeep of finishes'],
            ],
        ];

        foreach ($services as $i => $s) {
            Service::firstOrCreate(['slug' => Str::slug($s['name'])], [
                'name' => $s['name'],
                'icon' => $s['icon'],
                'description' => $s['description'],
                'main_image' => $s['image'],
                'content' => $s['content'],
                'benefits' => $s['benefits'],
                'features' => $s['features'],
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }
    }

    /** Starter list items (editable in Admin > Highlights). Only created when the table is empty. */
    private function highlights(): void
    {
        if (Highlight::count() > 0) {
            return;
        }

        $why = [
            ['tools', 'Quality Workmanship', 'Skilled trades and careful supervision so the finished work looks right and lasts.'],
            ['handshake', 'Reliable Service', 'Clear communication and honest advice from the first meeting to final handover.'],
            ['check-circle', 'Client Satisfaction', 'We are not finished until you are happy with the result.'],
            ['users', 'Professional Expertise', 'Experienced people guiding every stage of your project, from concept to completion.'],
            ['shield', 'Safety First', 'Safe, well-organised sites protect our people, your property and the community around it.'],
            ['clock', 'Timely Delivery', 'Realistic schedules, tracked progress and a firm commitment to finishing when promised.'],
        ];

        foreach ($why as $i => [$icon, $title, $text]) {
            Highlight::create(['group' => 'why', 'icon' => $icon, 'title' => $title, 'text' => $text, 'sort_order' => $i]);
        }

        foreach (['Quality Workmanship', 'Reliable Service', 'Client Satisfaction'] as $i => $title) {
            Highlight::create(['group' => 'commitments', 'icon' => 'check', 'title' => $title, 'sort_order' => $i]);
        }
    }

    /** Statistic slots exist but stay hidden until an admin enters real figures. */
    private function stats(): void
    {
        $stats = [
            ['Projects Completed', 'building', '+'],
            ['Years of Experience', 'hardhat', '+'],
            ['Happy Clients', 'handshake', null],
            ['Team Members', 'users', null],
        ];

        foreach ($stats as $i => [$label, $icon, $suffix]) {
            CompanyStat::firstOrCreate(['label' => $label], [
                'icon' => $icon,
                'value' => 0,
                'suffix' => $suffix,
                'is_visible' => false,
                'sort_order' => $i,
            ]);
        }
    }

    /** Creates the first administrator from ADMIN_* env values; nothing is hardcoded. */
    private function admin(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set: no administrator created. Run `php artisan admin:create`.');

            return;
        }

        User::firstOrCreate(['email' => $email], [
            'name' => env('ADMIN_NAME', 'Administrator'),
            'password' => $password,
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }
}
