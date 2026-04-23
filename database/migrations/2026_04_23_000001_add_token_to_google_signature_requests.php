<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_signature_requests', function (Blueprint $table) {
            $table->uuid('token')->unique()->nullable()->after('signature_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('google_signature_requests', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
