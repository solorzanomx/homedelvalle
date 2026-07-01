<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            if (!Schema::hasColumn('interactions', 'price_perception')) {
                $table->enum('price_perception', ['fair', 'negotiable', 'high'])->nullable()->after('visitor_comment');
            }
            if (!Schema::hasColumn('interactions', 'advisor_rating')) {
                $table->tinyInteger('advisor_rating')->unsigned()->nullable()->after('price_perception');
            }
        });
    }

    public function down(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            $table->dropColumn(['price_perception', 'advisor_rating']);
        });
    }
};
