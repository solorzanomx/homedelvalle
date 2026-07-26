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
        Schema::table('brokers', function (Blueprint $table) {
            $table->string('verification_token')->nullable()->after('operations_per_year');
            $table->timestamp('verification_sent_at')->nullable()->after('verification_token');
            $table->timestamp('verification_completed_at')->nullable()->after('verification_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            $table->dropColumn(['verification_token', 'verification_sent_at', 'verification_completed_at']);
        });
    }
};
