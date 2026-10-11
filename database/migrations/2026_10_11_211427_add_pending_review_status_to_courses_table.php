<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If `status` is a MySQL ENUM column, widen it directly.
        DB::statement("ALTER TABLE courses MODIFY status ENUM('draft', 'pending_review', 'published', 'archived') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("UPDATE courses SET status = 'draft' WHERE status = 'pending_review'");
        DB::statement("ALTER TABLE courses MODIFY status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft'");
    }
};
