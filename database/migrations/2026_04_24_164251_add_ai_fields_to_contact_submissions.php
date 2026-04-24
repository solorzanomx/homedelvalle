<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->boolean('ai_is_spam')->default(false)->after('is_read');
            $table->string('ai_category', 30)->nullable()->after('ai_is_spam');
            $table->string('ai_urgency', 10)->nullable()->after('ai_category');
            $table->string('ai_summary', 200)->nullable()->after('ai_urgency');
            $table->string('ai_spam_reason', 200)->nullable()->after('ai_summary');
        });
    }

    public function down(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->dropColumn(['ai_is_spam', 'ai_category', 'ai_urgency', 'ai_summary', 'ai_spam_reason']);
        });
    }
};
