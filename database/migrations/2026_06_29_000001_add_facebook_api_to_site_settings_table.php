<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('fb_api_enabled')->default(false)->after('fb_pixel_enabled');
            $table->string('fb_app_id', 100)->nullable()->after('fb_api_enabled');
            $table->string('fb_app_secret', 100)->nullable()->after('fb_app_id');
            $table->string('fb_page_id', 100)->nullable()->after('fb_app_secret');
            $table->text('fb_page_access_token')->nullable()->after('fb_page_id');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['fb_api_enabled', 'fb_app_id', 'fb_app_secret', 'fb_page_id', 'fb_page_access_token']);
        });
    }
};
