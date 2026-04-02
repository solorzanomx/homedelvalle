<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('site_settings', 'footer_about')) {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->text('footer_about')->nullable();
            $table->string('footer_bottom_text')->nullable();
            $table->json('footer_bottom_links')->nullable();
        });
        }
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['footer_about', 'footer_bottom_text', 'footer_bottom_links']);
        });
    }
};
