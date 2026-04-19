<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('webhook_enabled')->default(false)->after('fb_pixel_enabled');
            $table->string('webhook_api_key', 500)->nullable()->after('webhook_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['webhook_enabled', 'webhook_api_key']);
        });
    }
};
