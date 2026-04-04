<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'hero_badge')) {
                // Hero CTA fields
                $table->string('hero_badge', 255)->nullable();
                $table->string('hero_cta_text', 255)->nullable();
                $table->string('hero_cta_url', 255)->nullable();
                $table->string('hero_secondary_cta_text', 255)->nullable();
                $table->string('hero_secondary_cta_url', 255)->nullable();

                // Business model section
                $table->string('business_model_heading', 255)->nullable();
                $table->string('business_model_subheading', 500)->nullable();
                $table->text('business_model_content')->nullable();
                $table->json('business_model_steps')->nullable();

                // Stats section
                $table->string('stats_heading', 255)->nullable();
                $table->string('stats_subheading', 255)->nullable();
                $table->json('stats_section')->nullable();

                // Page content JSON columns
                $table->json('servicios_content')->nullable();
                $table->json('nosotros_content')->nullable();
                $table->json('vender_content')->nullable();

                // Navbar CTA
                $table->string('navbar_cta_text', 100)->nullable();
                $table->string('navbar_cta_url', 255)->nullable();
                $table->boolean('navbar_cta_enabled')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_badge', 'hero_cta_text', 'hero_cta_url',
                'hero_secondary_cta_text', 'hero_secondary_cta_url',
                'business_model_heading', 'business_model_subheading',
                'business_model_content', 'business_model_steps',
                'stats_heading', 'stats_subheading', 'stats_section',
                'servicios_content', 'nosotros_content', 'vender_content',
                'navbar_cta_text', 'navbar_cta_url', 'navbar_cta_enabled',
            ]);
        });
    }
};
