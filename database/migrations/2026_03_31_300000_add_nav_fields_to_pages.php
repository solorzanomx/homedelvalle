<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pages', 'show_in_nav')) {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('show_in_nav')->default(false)->after('is_published');
            $table->unsignedInteger('nav_order')->default(0)->after('show_in_nav');
            $table->string('nav_label', 50)->nullable()->after('nav_order');
            $table->string('nav_url')->nullable()->after('nav_label');
            $table->string('nav_route')->nullable()->after('nav_url');
            $table->string('nav_style', 20)->default('link')->after('nav_route'); // link | button
        });
        }
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['show_in_nav', 'nav_order', 'nav_label', 'nav_url', 'nav_route', 'nav_style']);
        });
    }
};
