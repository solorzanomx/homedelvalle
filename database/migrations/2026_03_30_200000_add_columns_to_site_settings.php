<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('site_settings', 'site_name')) {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('site_name')->default('CRM Platform')->after('id');
            $table->string('site_tagline')->nullable()->after('site_name');
            $table->string('primary_color')->default('#4f46e5')->after('site_tagline');
            $table->string('secondary_color')->default('#7c3aed')->after('primary_color');
            $table->text('home_welcome_text')->nullable()->after('secondary_color');
        });
        }
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['site_name', 'site_tagline', 'primary_color', 'secondary_color', 'home_welcome_text']);
        });
    }
};
