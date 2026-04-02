<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pages', 'sections')) {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('sections')->nullable()->after('meta_description');
            $table->boolean('use_sections')->default(false)->after('sections');
        });
        }
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['sections', 'use_sections']);
        });
    }
};
