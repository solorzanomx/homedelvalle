<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('captaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->text('property_address')->nullable();
            $table->tinyInteger('portal_etapa')->default(1);
            $table->timestamp('etapa1_completed_at')->nullable();
            $table->timestamp('etapa2_completed_at')->nullable();
            $table->timestamp('etapa3_completed_at')->nullable();
            $table->timestamp('etapa4_completed_at')->nullable();
            $table->foreignId('etapa3_valuation_id')->nullable()->constrained('property_valuations')->nullOnDelete();
            $table->foreignId('etapa4_signature_id')->nullable()->constrained('google_signature_requests')->nullOnDelete();
            $table->decimal('precio_acordado', 15, 2)->nullable();
            $table->enum('status', ['activo', 'completado', 'cancelado'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captaciones');
    }
};
