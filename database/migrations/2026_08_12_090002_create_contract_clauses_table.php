<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_clauses', function (Blueprint $table) {
            $table->id();
            $table->morphs('clauseable'); // ContractTemplate or Contract
            $table->string('key')->nullable();
            $table->string('title');
            $table->longText('body');
            $table->string('section')->default('clausula'); // declaracion | clausula | firma
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_clauses');
    }
};
