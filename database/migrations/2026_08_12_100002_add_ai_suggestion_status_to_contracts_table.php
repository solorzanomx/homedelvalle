<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('ai_suggestion_status')->default('idle')->after('folio'); // idle | pending | ready | failed
            $table->timestamp('ai_suggestion_requested_at')->nullable()->after('ai_suggestion_status');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['ai_suggestion_status', 'ai_suggestion_requested_at']);
        });
    }
};
