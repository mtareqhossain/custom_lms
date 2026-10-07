<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g. "welcome_student", "course_completed_student"
            $table->string('name'); // human-readable label shown in admin
            $table->string('subject');
            $table->longText('body'); // HTML, supports {{placeholder}} tokens
            $table->string('description')->nullable(); // short help text: what this template is for
            $table->json('available_placeholders')->nullable(); // list of tokens usable in this template
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // system templates can't be deleted, only edited
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
