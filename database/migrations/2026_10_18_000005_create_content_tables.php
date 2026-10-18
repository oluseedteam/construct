<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_category_id')->nullable()->constrained('project_categories')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('path');
            $table->boolean('is_demo')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_role')->nullable();
            $table->text('content');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_approved')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('company_stats', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('icon', 40)->default('check');
            $table->unsignedInteger('value')->default(0);
            $table->string('prefix', 10)->nullable();
            $table->string('suffix', 10)->nullable();
            $table->boolean('is_visible')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. home.hero_title
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_contents');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('company_stats');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('gallery_images');
    }
};
