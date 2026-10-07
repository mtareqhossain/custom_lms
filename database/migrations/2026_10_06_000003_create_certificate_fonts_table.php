<?php
// database/migrations/2026_10_06_000003_create_certificate_fonts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_fonts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_path'); // .ttf or .otf, uploaded via Filament
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_fonts');
    }
};
