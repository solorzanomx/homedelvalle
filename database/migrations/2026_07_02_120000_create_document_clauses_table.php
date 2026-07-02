<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_clauses', function (Blueprint $table) {
            $table->id();
            $table->string('document_key');
            $table->string('clause_key');
            $table->text('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_key', 'clause_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_clauses');
    }
};
