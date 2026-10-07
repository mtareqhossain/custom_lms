<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('homepage_content', function (Blueprint $t) {
            $t->id();
            $t->string('hero_headline');
            $t->text('hero_subheadline');
            $t->string('hero_image')->nullable();

            $t->string('business_headline');
            $t->text('business_subheadline');
            $t->json('business_features'); // [{icon, title, text}]

            $t->json('feature_strip'); // [{icon, title, text}]
            $t->json('why_us_points'); // ["point", ...]

            $t->string('instructor_cta_headline');
            $t->text('instructor_cta_text');

            $t->json('client_logos'); // ["name", ...]
            $t->json('testimonials'); // [{quote, name, role}]
            $t->json('certifications'); // ["name", ...]

            $t->string('final_cta_headline');
            $t->text('final_cta_subheadline');

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_content');
    }
};
