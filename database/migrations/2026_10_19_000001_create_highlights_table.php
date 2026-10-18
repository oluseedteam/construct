<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Editable list items: "why" = Why Choose Us cards, "commitments" = tick-list on the About section.
        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->string('group', 20)->index();
            $table->string('title');
            $table->string('text', 500)->nullable();
            $table->string('icon', 40)->default('check-circle');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('highlights');
    }
};
