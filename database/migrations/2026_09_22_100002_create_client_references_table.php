<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('address', 200)->nullable();
            $table->string('mobile_phone', 30)->nullable();
            $table->string('landline_phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_references');
    }
};
