<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status', 20)->default('new'); // new | read | archived
            $table->timestamp('read_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('admin_notified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 24)->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('project_type', 60);
            $table->string('project_location');
            $table->string('estimated_budget', 60)->nullable();
            $table->date('preferred_start_date')->nullable();
            $table->text('description');
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            $table->string('status', 20)->default('new'); // new | reviewing | quoted | accepted | declined
            $table->text('internal_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('admin_notified_at')->nullable();
            $table->timestamp('customer_notified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('quote_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_responses');
        Schema::dropIfExists('quote_requests');
        Schema::dropIfExists('contact_messages');
    }
};
