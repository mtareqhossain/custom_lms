<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/..._create_site_settings_table.php
public function up()
{
    Schema::create('site_settings', function (Blueprint $table) {
        $table->id();
        $table->string('site_name')->default(config('app.name'));
        $table->string('logo_path')->nullable(); // null = show text name instead
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
