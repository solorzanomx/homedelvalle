<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Force-set site_name to the correct capitalization regardless of current value.
     * The previous migration used a WHERE clause that didn't match the production value.
     */
    public function up(): void
    {
        // Update all rows — site_settings is a single-row config table
        DB::table('site_settings')->update(['site_name' => 'Home del Valle']);
    }

    public function down(): void
    {
        // No meaningful rollback for a brand name fix
    }
};
