<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('employment_type', 30)->default('Full-time');
            $table->string('location')->nullable();
            $table->text('summary');
            $table->longText('description')->nullable();
            $table->json('requirements')->nullable();
            $table->boolean('is_published')->default(true);
            $table->date('closes_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 24)->unique();
            // Null for general applications, or if the opening is later deleted.
            $table->foreignId('job_opening_id')->nullable()->constrained('job_openings')->nullOnDelete();
            $table->string('position'); // snapshot of the title at the time of applying
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('city')->nullable();
            $table->string('experience', 40)->nullable();
            $table->date('available_from')->nullable();
            $table->string('profile_url')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume_path');
            $table->string('resume_name');
            $table->string('status', 20)->default('new'); // new | reviewing | interview | hired | rejected
            $table->text('internal_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('admin_notified_at')->nullable();
            $table->timestamp('applicant_notified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_openings');
    }
};
