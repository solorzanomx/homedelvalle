<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            if (!Schema::hasColumn('interactions', 'visit_token')) {
                $table->string('visit_token', 64)->nullable()->unique()->after('completed_at');
            }
            if (!Schema::hasColumn('interactions', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('visit_token');
            }
            if (!Schema::hasColumn('interactions', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('interactions', 'reschedule_requested_at')) {
                $table->timestamp('reschedule_requested_at')->nullable()->after('reminder_sent_at');
            }
            if (!Schema::hasColumn('interactions', 'reschedule_message')) {
                $table->text('reschedule_message')->nullable()->after('reschedule_requested_at');
            }
            if (!Schema::hasColumn('interactions', 'send_confirmation_email')) {
                $table->boolean('send_confirmation_email')->default(true)->after('reschedule_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            $table->dropColumn([
                'visit_token',
                'confirmed_at',
                'reminder_sent_at',
                'reschedule_requested_at',
                'reschedule_message',
                'send_confirmation_email',
            ]);
        });
    }
};
