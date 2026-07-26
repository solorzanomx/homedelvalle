<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personas a quienes realmente se les manda una ficha técnica: el
 * responsable de área dentro de una constructora, o un inversionista
 * individual operando solo (developer_company_id nulo en ese caso).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developer_company_id')->nullable()->constrained('developer_companies')->nullOnDelete();
            $table->string('name');
            $table->string('role')->nullable(); // Adquisiciones, Dirección Técnica, Legal... o null si es inversionista individual
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->json('interest_zones')->nullable(); // ["Del Valle","Narvarte","Nápoles"]
            $table->decimal('budget_min', 14, 2)->nullable();
            $table->decimal('budget_max', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_contacts');
    }
};
