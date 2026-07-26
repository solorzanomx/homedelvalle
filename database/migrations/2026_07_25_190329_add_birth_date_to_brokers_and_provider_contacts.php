<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('referral_source');
        });

        Schema::table('provider_contacts', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            $table->dropColumn('birth_date');
        });

        Schema::table('provider_contacts', function (Blueprint $table) {
            $table->dropColumn('birth_date');
        });
    }
};
