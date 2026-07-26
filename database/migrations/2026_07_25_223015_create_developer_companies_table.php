<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cartera de constructoras/desarrolladoras — el negocio principal de la
 * firma (predios → desarrolladoras). Separado de Client (orientado a
 * personas físicas) y de ProviderCompany (proveedores de servicio),
 * mismo patrón de esquema que ambos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 30)->default('desarrolladora'); // desarrolladora, constructora, fondo_inversion, otro
            $table->string('rfc', 20)->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_companies');
    }
};
