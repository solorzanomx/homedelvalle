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
        if (!Schema::hasColumn('properties', 'address')) {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('address')->nullable()->after('city');
            $table->string('zipcode')->nullable()->after('colony');
            $table->decimal('area', 8, 2)->nullable()->after('bathrooms');
            $table->integer('parking')->nullable()->after('area');
            $table->string('status')->default('available')->after('parking');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            //
        });
    }
};
