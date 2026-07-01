<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rental_avales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_process_id')->nullable()->constrained('rental_processes')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete(); // arrendatario dueño del aval
            // Datos del aval
            $table->string('name', 200);
            $table->string('curp', 18)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('relationship', 80)->nullable(); // familiar, amigo, etc.
            $table->string('id_type', 30)->nullable(); // INE, pasaporte, etc.
            $table->string('id_number', 60)->nullable();
            $table->date('id_expiry')->nullable();
            // Inmueble en garantía
            $table->string('property_address', 200)->nullable();
            $table->string('property_colony', 100)->nullable();
            $table->string('property_municipality', 100)->nullable();
            $table->string('property_state', 60)->nullable();
            $table->string('property_zip', 5)->nullable();
            $table->string('property_folio_real', 80)->nullable(); // folio registral
            $table->decimal('property_value', 14, 2)->nullable();
            $table->boolean('property_has_mortgage')->default(false);
            $table->boolean('property_free_of_liens')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rental_avales'); }
};
