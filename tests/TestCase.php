<?php

namespace Tests;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function admin(array $attrs = []): User
    {
        return User::create(array_merge([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'correct-horse-battery-1',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ], $attrs));
    }

    protected function editor(): User
    {
        return $this->admin(['name' => 'Editor', 'email' => 'editor@example.test', 'role' => User::ROLE_EDITOR]);
    }

    protected function category(string $name = 'Residential'): ProjectCategory
    {
        return ProjectCategory::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]);
    }

    protected function project(array $attrs = []): Project
    {
        return Project::create(array_merge([
            'project_category_id' => $this->category()->id,
            'name' => 'Lakeview Towers',
            'slug' => 'lakeview-towers',
            'description' => 'A sample project description.',
            'status' => 'ongoing',
            'is_published' => true,
        ], $attrs));
    }

    protected function service(array $attrs = []): Service
    {
        return Service::create(array_merge([
            'name' => 'General Construction',
            'slug' => 'general-construction',
            'icon' => 'building',
            'description' => 'Concept to completion.',
            'content' => "First paragraph.\n\nSecond paragraph.",
            'benefits' => ['Benefit one'],
            'features' => ['Feature one'],
            'is_published' => true,
        ], $attrs));
    }
}
