<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: ampliar el ENUM para incluir 'scheduled'
        // SQLite no usa ENUM real, acepta cualquier valor — se omite en SQLite
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE facebook_posts MODIFY COLUMN status ENUM('draft','review','scheduled','published') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('facebook_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('facebook_posts', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('published_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facebook_posts', function (Blueprint $table) {
            $table->dropColumn('scheduled_at');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE facebook_posts MODIFY COLUMN status ENUM('draft','review','published') NOT NULL DEFAULT 'draft'");
        }
    }
};
