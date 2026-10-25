<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every page on the public site. Built-in pages (About, Projects, Gallery ...) are seeded and
        // can be hidden but not deleted; custom pages are created by admins and rendered from `content`.
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->nullable()->unique(); // built-in identifier, e.g. "projects"; null for custom pages
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('nav_label', 40);
            $table->string('subtitle')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_description', 200)->nullable();
            $table->boolean('is_builtin')->default(false);
            $table->boolean('is_published')->default(true);
            $table->boolean('show_in_nav')->default(true);
            $table->boolean('show_in_footer')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
