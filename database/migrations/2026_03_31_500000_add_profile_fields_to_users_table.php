<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'bio')) {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio', 200)->nullable()->after('avatar_path');
            $table->string('title', 100)->nullable()->after('bio');
            $table->string('branch', 150)->nullable()->after('title');
            $table->string('language', 10)->nullable()->default('es')->after('branch');
            $table->string('timezone', 50)->nullable()->default('America/Mexico_City')->after('language');
            $table->text('email_signature')->nullable()->after('timezone');
            $table->boolean('show_phone_on_properties')->default(true)->after('email_signature');
            $table->string('shared_card_type', 20)->nullable()->default('ficha_simple')->after('show_phone_on_properties');
        });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio', 'title', 'branch', 'language', 'timezone',
                'email_signature', 'show_phone_on_properties', 'shared_card_type',
            ]);
        });
    }
};
