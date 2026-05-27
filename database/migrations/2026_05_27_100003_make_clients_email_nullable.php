<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: MODIFY COLUMN directamente.
        // SQLite: recrear la tabla porque no soporta ALTER COLUMN nullable.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE clients MODIFY COLUMN email VARCHAR(255) NULL');
        } else {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('email')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Primero limpiar nulls para no violar NOT NULL al revertir
            DB::statement("UPDATE clients SET email = CONCAT('sin-email-', id, '@placeholder.hdv') WHERE email IS NULL");
            DB::statement('ALTER TABLE clients MODIFY COLUMN email VARCHAR(255) NOT NULL');
        } else {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('email')->nullable(false)->change();
            });
        }
    }
};
