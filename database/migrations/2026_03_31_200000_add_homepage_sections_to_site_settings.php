<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // JSON columns for repeating sections
            $table->json('benefits_section')->nullable();
            $table->json('services_section')->nullable();
            $table->json('testimonials_section')->nullable();

            // Section headings
            $table->string('benefits_heading')->nullable();
            $table->string('benefits_subheading')->nullable();
            $table->string('services_heading')->nullable();
            $table->string('services_subheading')->nullable();
            $table->string('testimonials_heading')->nullable();
            $table->string('testimonials_subheading')->nullable();
            $table->string('featured_heading')->nullable();
            $table->string('featured_subheading')->nullable();
            $table->string('blog_heading')->nullable();
            $table->string('blog_subheading')->nullable();
            $table->string('cta_heading')->nullable();
            $table->string('cta_subheading')->nullable();
            $table->string('contact_heading')->nullable();
            $table->string('contact_subheading')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'benefits_section', 'services_section', 'testimonials_section',
                'benefits_heading', 'benefits_subheading',
                'services_heading', 'services_subheading',
                'testimonials_heading', 'testimonials_subheading',
                'featured_heading', 'featured_subheading',
                'blog_heading', 'blog_subheading',
                'cta_heading', 'cta_subheading',
                'contact_heading', 'contact_subheading',
            ]);
        });
    }
};
