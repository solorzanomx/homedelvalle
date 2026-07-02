<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained()->cascadeOnDelete();
            $table->decimal('precio_ofertado', 14, 2);
            $table->decimal('monto_apartado', 14, 2)->nullable();
            $table->decimal('pago_firma_contrato', 14, 2)->nullable();
            $table->decimal('pago_firma_escritura', 14, 2)->nullable();
            $table->string('forma_pago')->nullable();
            $table->unsignedSmallInteger('vigencia_dias')->default(5);
            $table->string('folio_real')->nullable();
            $table->text('comentarios')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->timestamp('offered_at')->useCurrent();
            $table->string('last_pdf_path')->nullable();
            $table->timestamps();

            $table->index(['operation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_offers');
    }
};
