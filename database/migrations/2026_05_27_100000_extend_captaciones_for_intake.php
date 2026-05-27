<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('captaciones', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->foreignId('operation_id')->nullable()->after('property_id')->constrained('operations')->nullOnDelete();
            $table->enum('intent', [
                'general',
                'venta_constructor',
                'venta_residencial',
                'venta_comercial',
                'renta_residencial',
                'renta_comercial',
            ])->default('general')->after('operation_id');
            $table->decimal('commission_pct', 5, 2)->default(5.00)->after('intent');
            $table->text('marketing_plan')->nullable()->after('commission_pct');
            $table->text('notes_from_call')->nullable()->after('marketing_plan');
            $table->enum('source', ['phone_call', 'whatsapp_inbound', 'web_form', 'referral', 'other'])
                  ->default('phone_call')->after('notes_from_call');
            $table->foreignId('created_by_user_id')->nullable()->after('source')->constrained('users')->nullOnDelete();
            $table->timestamp('declined_at')->nullable();
            $table->text('declined_reason')->nullable();
        });

        // Ampliar el enum status con los nuevos valores (solo en MySQL — SQLite acepta cualquier string)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE captaciones MODIFY COLUMN `status` ENUM('activo','completado','cancelado','nuevo','declinado','convertido') NOT NULL DEFAULT 'activo'");
        }
    }

    public function down(): void
    {
        // Restaurar enum antes de quitar columnas que podrían tener valores nuevos
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE captaciones SET `status` = 'cancelado' WHERE `status` IN ('nuevo','declinado','convertido')");
            DB::statement("ALTER TABLE captaciones MODIFY COLUMN `status` ENUM('activo','completado','cancelado') NOT NULL DEFAULT 'activo'");
        }

        Schema::table('captaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_id');
            $table->dropConstrainedForeignId('operation_id');
            $table->dropColumn(['intent', 'commission_pct', 'marketing_plan', 'notes_from_call', 'source']);
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropColumn(['declined_at', 'declined_reason']);
        });
    }
};
