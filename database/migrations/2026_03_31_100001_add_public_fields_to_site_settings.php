<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('home_welcome_text');
            $table->string('contact_email')->nullable()->after('whatsapp_number');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('address')->nullable()->after('contact_phone');
            $table->string('facebook_url')->nullable()->after('address');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('tiktok_url')->nullable()->after('instagram_url');
            $table->text('about_text')->nullable()->after('tiktok_url');
            $table->text('google_maps_embed')->nullable()->after('about_text');
            $table->string('hero_image_path')->nullable()->after('google_maps_embed');
            $table->string('hero_heading')->nullable()->after('hero_image_path');
            $table->string('hero_subheading')->nullable()->after('hero_heading');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number', 'contact_email', 'contact_phone', 'address',
                'facebook_url', 'instagram_url', 'tiktok_url',
                'about_text', 'google_maps_embed',
                'hero_image_path', 'hero_heading', 'hero_subheading',
            ]);
        });
    }
};
