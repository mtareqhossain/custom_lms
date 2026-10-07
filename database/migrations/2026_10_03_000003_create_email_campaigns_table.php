<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('audience_type'); // all_students, by_course, by_organization, individual
            $table->json('audience_filter')->nullable(); // e.g. {"course_id": 3} or {"user_ids": [1,2,3]}
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('status')->default('pending'); // pending, sending, completed, failed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
