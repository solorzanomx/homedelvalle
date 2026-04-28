<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('form_submissions', 'client_id')) {
            Schema::table('form_submissions', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->after('user_agent')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }
};
