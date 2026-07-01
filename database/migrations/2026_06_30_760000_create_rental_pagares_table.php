<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rental_pagares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_process_id')->constrained('rental_processes')->cascadeOnDelete();
            $table->unsignedTinyInteger('quantity')->default(1);        // cuántos pagarés
            $table->decimal('amount_each', 12, 2);                      // monto por pagaré
            $table->decimal('total_amount', 12, 2)->storedAs('quantity * amount_each'); // calculado
            $table->string('currency', 3)->default('MXN');
            $table->date('issue_date')->nullable();                      // fecha de firma
            $table->string('beneficiary_name', 200)->nullable();        // nombre del arrendador
            $table->enum('status', ['pending','signed','held','returned'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rental_pagares'); }
};
