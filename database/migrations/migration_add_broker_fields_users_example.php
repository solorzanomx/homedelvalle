<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN DE EJEMPLO - ACTUALIZAR TABLA USERS
 *
 * Añade campos necesarios para información de brokers/asesores
 * php artisan make:migration add_broker_fields_to_users_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_name'))
                $table->string('last_name')->nullable()->after('name');
            if (!Schema::hasColumn('users', 'position'))
                $table->string('position')->nullable()->after('last_name');
            if (!Schema::hasColumn('users', 'phone'))
                $table->string('phone')->nullable()->after('email');
            if (!Schema::hasColumn('users', 'mobile'))
                $table->string('mobile')->nullable()->after('phone');
            if (!Schema::hasColumn('users', 'profile_photo_url'))
                $table->string('profile_photo_url')->nullable()->after('mobile');
            if (!Schema::hasColumn('users', 'photo_path'))
                $table->string('photo_path')->nullable()->after('profile_photo_url');
            if (!Schema::hasColumn('users', 'bio'))
                $table->text('bio')->nullable();
            if (!Schema::hasColumn('users', 'is_broker'))
                $table->boolean('is_broker')->default(false);
            if (!Schema::hasColumn('users', 'is_active'))
                $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_name',
                'position',
                'phone',
                'mobile',
                'profile_photo_url',
                'photo_path',
                'bio',
                'is_broker',
                'is_active',
            ]);
        });
    }
};
