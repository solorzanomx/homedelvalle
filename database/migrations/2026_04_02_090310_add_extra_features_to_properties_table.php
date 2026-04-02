<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'half_bathrooms')) {
                $table->unsignedTinyInteger('half_bathrooms')->nullable()->after('bathrooms');
            }
            if (!Schema::hasColumn('properties', 'construction_area')) {
                $table->decimal('construction_area', 10, 2)->nullable()->after('area');
            }
            if (!Schema::hasColumn('properties', 'lot_area')) {
                $table->decimal('lot_area', 10, 2)->nullable()->after('construction_area');
            }
            if (!Schema::hasColumn('properties', 'floors')) {
                $table->unsignedTinyInteger('floors')->nullable()->after('parking');
            }
            if (!Schema::hasColumn('properties', 'year_built')) {
                $table->unsignedSmallInteger('year_built')->nullable()->after('floors');
            }
            if (!Schema::hasColumn('properties', 'maintenance_fee')) {
                $table->decimal('maintenance_fee', 10, 2)->nullable()->after('year_built');
            }
            if (!Schema::hasColumn('properties', 'furnished')) {
                $table->string('furnished', 20)->nullable()->after('maintenance_fee');
            }
            if (!Schema::hasColumn('properties', 'amenities')) {
                $table->json('amenities')->nullable()->after('furnished');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $cols = ['half_bathrooms', 'construction_area', 'lot_area', 'floors', 'year_built', 'maintenance_fee', 'furnished', 'amenities'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
