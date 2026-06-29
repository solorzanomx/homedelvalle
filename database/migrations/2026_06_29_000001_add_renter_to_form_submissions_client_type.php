<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Amplía el enum client_type en form_submissions para incluir 'renter'.
     * Necesario para los formularios de /rentar y /renta-tu-propiedad (Livewire).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE form_submissions MODIFY COLUMN client_type ENUM('owner','buyer','investor','renter') NULL");
    }

    public function down(): void
    {
        // Primero limpiar filas con el valor que se va a eliminar para evitar error
        DB::statement("UPDATE form_submissions SET client_type = NULL WHERE client_type = 'renter'");
        DB::statement("ALTER TABLE form_submissions MODIFY COLUMN client_type ENUM('owner','buyer','investor') NULL");
    }
};
