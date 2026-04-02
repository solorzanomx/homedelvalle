<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('email_templates', 'body_text')) {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->text('body_text')->nullable()->after('body');
        });
        }
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('body_text');
        });
    }
};
