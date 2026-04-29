<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN is MySQL-only; SQLite uses text columns so no change needed
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Add 'renter' to clients.client_type ENUM
        DB::statement("ALTER TABLE clients MODIFY COLUMN client_type ENUM('owner','buyer','investor','renter') NULL");

        // Add 'renter' to form_submissions.client_type ENUM
        DB::statement("ALTER TABLE form_submissions MODIFY COLUMN client_type ENUM('owner','buyer','investor','renter') NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE clients MODIFY COLUMN client_type ENUM('owner','buyer','investor') NULL");
        DB::statement("ALTER TABLE form_submissions MODIFY COLUMN client_type ENUM('owner','buyer','investor') NULL");
    }
};
