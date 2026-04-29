<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * If whatsapp_number is null/empty, copy from contact_phone.
     * Also clears stale settings cache so the new value is served immediately.
     */
    public function up(): void
    {
        // Copy contact_phone → whatsapp_number when whatsapp_number is empty
        DB::statement("
            UPDATE site_settings
            SET whatsapp_number = contact_phone
            WHERE (whatsapp_number IS NULL OR whatsapp_number = '')
              AND contact_phone IS NOT NULL
              AND contact_phone != ''
        ");

        // Clear settings cache so component picks up the value immediately
        cache()->forget('site_settings');
    }

    public function down(): void {}
};
