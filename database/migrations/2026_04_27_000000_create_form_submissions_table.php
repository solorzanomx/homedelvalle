<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla creada por migración de actualización 2026_04_27_000001
        // Esta migración es un marcador para mantener el historial
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
