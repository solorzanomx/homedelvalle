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
        Schema::table('email_settings', function (Blueprint $table) {
            $table->boolean('imap_enabled')->default(false)->after('enable_ssl');
            $table->string('imap_host')->nullable()->after('imap_enabled');
            $table->integer('imap_port')->nullable()->after('imap_host');
            $table->string('imap_encryption', 10)->default('ssl')->after('imap_port');
            $table->string('imap_username')->nullable()->after('imap_encryption');
            $table->text('imap_password')->nullable()->after('imap_username');
            $table->timestamp('imap_last_checked_at')->nullable()->after('imap_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->dropColumn([
                'imap_enabled', 'imap_host', 'imap_port', 'imap_encryption',
                'imap_username', 'imap_password', 'imap_last_checked_at',
            ]);
        });
    }
};
