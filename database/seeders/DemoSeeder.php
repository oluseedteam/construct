<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Clearly-marked SAMPLE content so the site looks complete in local/demo
 * environments. Every record is flagged is_demo and named "Sample ...".
 * Delete it from the admin dashboard before launch.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        $cat = fn (string $slug) => ProjectCategory::where('slug', $slug)->value('id');

        $projects = [
            ['Sample Project: Mid-Rise Residential Block', 'residential', 'ongoing', 'Sample location', 1, true],
            ['Sample Project: Commercial Office Tower', 'commercial', 'ongoing', 'Sample location', 2, true],
            ['Sample Project: Retail & Warehouse Complex', 'commercial', 'completed', 'Sample location', 3, true],
            ['Sample Project: Urban Renovation', 'renovation', 'completed', 'Sample location', 4, false],
            ['Sample Project: Community Infrastructure', 'infrastructure', 'planned', 'Sample location', 5, false],
            ['Sample Project: Family Housing Estate', 'residential', 'completed', 'Sample location', 6, false],
        ];

        foreach ($projects as $i => [$name, $category, $status, $location, $image, $featured]) {
            $project = Project::firstOrCreate(['slug' => Str::slug($name)], [
                'project_category_id' => $cat($category),
                'name' => $name,
                'description' => 'Demonstration entry. Replace this with a real project description from the admin dashboard.',
                'content' => "This is sample content used to preview the portfolio layout. It does not describe a real project.\n\nEdit or delete it from Admin > Projects.",
                'location' => $location,
                'status' => $status,
                'start_date' => now()->subMonths(10 + $i),
                'completion_date' => $status === 'completed' ? now()->subMonths($i) : null,
                'featured_image' => "images/demo/demo-$image.jpg",
                'is_featured' => $featured,
                'is_published' => true,
                'is_demo' => true,
                'sort_order' => $i,
            ]);

            if ($project->images()->count() === 0) {
                foreach ([$image, (($image + 2) % 8) + 1, (($image + 4) % 8) + 1] as $order => $n) {
                    $project->images()->create(['path' => "images/demo/demo-$n.jpg", 'sort_order' => $order]);
                }
            }
        }

        $gallery = [[1, 'residential'], [2, 'commercial'], [3, 'commercial'], [4, 'infrastructure'], [5, 'renovation'], [6, 'residential'], [7, 'other'], [8, 'infrastructure']];
        foreach ($gallery as $i => [$n, $category]) {
            GalleryImage::firstOrCreate(['path' => "images/demo/demo-$n.jpg"], [
                'project_category_id' => $cat($category),
                'title' => "Sample image $n",
                'is_demo' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
