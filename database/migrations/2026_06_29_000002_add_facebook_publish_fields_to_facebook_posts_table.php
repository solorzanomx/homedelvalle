<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_posts', function (Blueprint $table) {
            $table->string('fb_page_post_id', 100)->nullable()->after('published_at');
            $table->string('fb_post_url', 500)->nullable()->after('fb_page_post_id');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_posts', function (Blueprint $table) {
            $table->dropColumn(['fb_page_post_id', 'fb_post_url']);
        });
    }
};
