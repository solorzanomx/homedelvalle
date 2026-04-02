<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('site_settings', 'property_listing_template')) {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('property_listing_template', 50)->nullable()->default('grid');
            $table->string('property_detail_template', 50)->nullable()->default('sidebar');
            $table->string('blog_template', 50)->nullable()->default('grid');
        });
        }
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['property_listing_template', 'property_detail_template', 'blog_template']);
        });
    }
};
