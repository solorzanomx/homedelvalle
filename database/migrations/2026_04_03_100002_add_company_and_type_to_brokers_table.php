<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            if (!Schema::hasColumn('brokers', 'broker_company_id')) {
                $table->foreignId('broker_company_id')->nullable()->after('company_name')
                      ->constrained('broker_companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('brokers', 'type')) {
                $table->string('type', 20)->default('external')->after('status');
            }
            if (!Schema::hasColumn('brokers', 'specialty')) {
                $table->string('specialty')->nullable()->after('type');
            }
            if (!Schema::hasColumn('brokers', 'referral_source')) {
                $table->string('referral_source')->nullable()->after('specialty');
            }
        });

        // Migrate existing company_name values to broker_companies
        $companies = DB::table('brokers')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->distinct()
            ->pluck('company_name');

        foreach ($companies as $name) {
            $trimmed = trim($name);
            if (empty($trimmed)) continue;

            $companyId = DB::table('broker_companies')->insertGetId([
                'name' => $trimmed,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('brokers')
                ->where('company_name', $name)
                ->update(['broker_company_id' => $companyId]);
        }
    }

    public function down(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            if (Schema::hasColumn('brokers', 'broker_company_id')) {
                $table->dropConstrainedForeignId('broker_company_id');
            }
            if (Schema::hasColumn('brokers', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('brokers', 'specialty')) {
                $table->dropColumn('specialty');
            }
            if (Schema::hasColumn('brokers', 'referral_source')) {
                $table->dropColumn('referral_source');
            }
        });
    }
};
