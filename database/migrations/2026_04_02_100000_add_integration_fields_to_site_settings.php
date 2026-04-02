<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'gtm_id')) {
                $table->boolean('gtm_enabled')->default(false);
                $table->string('gtm_id', 50)->nullable();
                $table->boolean('ga_enabled')->default(false);
                $table->string('google_analytics_id', 50)->nullable();
                $table->boolean('fb_pixel_enabled')->default(false);
                $table->string('facebook_pixel_id', 50)->nullable();
                $table->text('custom_head_scripts')->nullable();
                $table->text('custom_body_scripts')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'gtm_enabled', 'gtm_id',
                'ga_enabled', 'google_analytics_id',
                'fb_pixel_enabled', 'facebook_pixel_id',
                'custom_head_scripts', 'custom_body_scripts',
            ]);
        });
    }
};
