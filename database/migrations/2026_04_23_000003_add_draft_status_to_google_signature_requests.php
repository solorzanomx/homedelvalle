<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE google_signature_requests MODIFY COLUMN status ENUM('draft','pending','completed','declined') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE google_signature_requests MODIFY COLUMN status ENUM('pending','completed','declined') NOT NULL DEFAULT 'pending'");
    }
};
