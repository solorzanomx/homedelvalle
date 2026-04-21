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
            // Información de perfil
            $table->string('last_name')->nullable()->after('name');
            $table->string('position')->nullable()->after('last_name'); // Cargo: Gerente, Agente, etc.

            // Contacto
            $table->string('phone')->nullable()->after('email');
            $table->string('mobile')->nullable()->after('phone');

            // Fotos de perfil
            $table->string('profile_photo_url')->nullable()->after('mobile');
            $table->string('photo_path')->nullable()->after('profile_photo_url');

            // Metadata
            $table->text('bio')->nullable();
            $table->boolean('is_broker')->default(false);
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
