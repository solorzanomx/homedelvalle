<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_zone_snapshots', function (Blueprint $table) {
            $table->boolean('is_validated')->default(false)->after('notes');
            $table->string('validated_by')->nullable()->after('is_validated');
            $table->timestamp('validated_at')->nullable()->after('validated_by');
        });
    }

    public function down(): void
    {
        Schema::table('market_zone_snapshots', function (Blueprint $table) {
            $table->dropColumn(['is_validated', 'validated_by', 'validated_at']);
        });
    }
};
