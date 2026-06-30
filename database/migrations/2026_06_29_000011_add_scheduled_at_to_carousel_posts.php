<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('carousel_posts', 'scheduled_at')) {
            Schema::table('carousel_posts', function (Blueprint $table) {
                $table->timestamp('scheduled_at')->nullable()->after('published_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('carousel_posts', 'scheduled_at')) {
            Schema::table('carousel_posts', function (Blueprint $table) {
                $table->dropColumn('scheduled_at');
            });
        }
    }
};
