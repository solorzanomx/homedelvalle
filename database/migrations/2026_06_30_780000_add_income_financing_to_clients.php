<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'income_type')) {
                $table->string('income_type', 30)->nullable()->comment('empleado, independiente, empresario, otro');
            }
            if (! Schema::hasColumn('clients', 'income_amount')) {
                $table->decimal('income_amount', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('clients', 'financing_type')) {
                $table->string('financing_type', 30)->nullable()->comment('contado, hipotecario, infonavit, fovissste, cofinanciamiento');
            }
            if (! Schema::hasColumn('clients', 'financing_preauth_amount')) {
                $table->decimal('financing_preauth_amount', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('clients', 'nss')) {
                $table->string('nss', 11)->nullable()->comment('número seguro social');
            }
            if (! Schema::hasColumn('clients', 'infonavit_balance')) {
                $table->decimal('infonavit_balance', 12, 2)->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('clients', function (Blueprint $table) {
            $columns = [
                'income_type',
                'income_amount',
                'financing_type',
                'financing_preauth_amount',
                'nss',
                'infonavit_balance',
            ];
            $existing = array_filter($columns, fn($col) => Schema::hasColumn('clients', $col));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
