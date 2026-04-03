<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new columns to referrals (with safety checks)
        Schema::table('referrals', function (Blueprint $table) {
            if (!Schema::hasColumn('referrals', 'referred_name')) {
                $table->string('referred_name', 255)->nullable()->after('referral_type');
            }
            if (!Schema::hasColumn('referrals', 'referred_phone')) {
                $table->string('referred_phone', 20)->nullable()->after('referred_name');
            }
            if (!Schema::hasColumn('referrals', 'referred_context')) {
                $table->text('referred_context')->nullable()->after('referred_phone');
            }
            if (!Schema::hasColumn('referrals', 'agreed_at')) {
                $table->timestamp('agreed_at')->nullable()->after('notes');
            }
        });

        // 2. Fix referrers type enum to include cliente_pasado (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE referrers MODIFY COLUMN type ENUM('portero','vecino','broker_hipotecario','cliente_pasado','comisionista','otro') DEFAULT 'comisionista'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE referrers MODIFY COLUMN type ENUM('portero','vecino','broker_hipotecario','comisionista','otro') DEFAULT 'comisionista'");
        }

        Schema::table('referrals', function (Blueprint $table) {
            $columns = Schema::getColumnListing('referrals');
            $drop = array_intersect(['referred_name', 'referred_phone', 'referred_context', 'agreed_at'], $columns);
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
