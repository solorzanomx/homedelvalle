<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            if (!Schema::hasColumn('interactions', 'visitor_reaction')) {
                $table->enum('visitor_reaction', ['liked', 'neutral', 'disliked'])->nullable()->after('reschedule_message');
            }
            if (!Schema::hasColumn('interactions', 'visitor_comment')) {
                $table->text('visitor_comment')->nullable()->after('visitor_reaction');
            }
            if (!Schema::hasColumn('interactions', 'feedback_submitted_at')) {
                $table->timestamp('feedback_submitted_at')->nullable()->after('visitor_comment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            $table->dropColumn(['visitor_reaction', 'visitor_comment', 'feedback_submitted_at']);
        });
    }
};
