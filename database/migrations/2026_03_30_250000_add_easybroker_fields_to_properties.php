<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('properties', 'property_type')) {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('property_type')->default('House')->after('photo');
            $table->string('operation_type')->default('sale')->after('property_type');
            $table->string('currency')->default('MXN')->after('operation_type');
            $table->string('easybroker_id')->nullable()->after('currency');
            $table->string('easybroker_status')->nullable()->after('easybroker_id');
            $table->timestamp('easybroker_published_at')->nullable()->after('easybroker_status');
            $table->string('easybroker_public_url')->nullable()->after('easybroker_published_at');

            $table->index('easybroker_id');
        });
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['easybroker_id']);
            $table->dropColumn([
                'property_type',
                'operation_type',
                'currency',
                'easybroker_id',
                'easybroker_status',
                'easybroker_published_at',
                'easybroker_public_url',
            ]);
        });
    }
};
