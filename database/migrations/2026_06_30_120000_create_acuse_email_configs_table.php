<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('acuse_email_configs', function (Blueprint $table) {
            $table->id();
            $table->string('form_type')->unique(); // vendedor, comprador, arrendatario, propietario_renta, b2b, contacto
            $table->string('subject')->default('Recibimos tu mensaje · Home del Valle');
            $table->string('badge')->default('Mensaje recibido');
            $table->string('titulo')->default('¡Recibimos tu mensaje!');
            $table->text('bajada');
            $table->string('nota')->nullable();
            $table->string('cta1_label')->default('Ver propiedades');
            $table->string('cta1_type')->default('static'); // static|precios_colonia|propiedades_renta|propiedades_compra|precios
            $table->string('cta1_url_static')->default('https://homedelvalle.mx/propiedades');
            $table->string('cta2_label')->nullable();
            $table->string('cta2_type')->nullable(); // static|precios_colonia|propiedades_renta|propiedades_compra|precios
            $table->string('cta2_url_static')->nullable();
            $table->string('paso1_icon')->default('icon-mail.png');
            $table->string('paso1_titulo')->default('Revisamos tu mensaje');
            $table->string('paso1_desc');
            $table->string('paso2_icon')->default('icon-chat.png');
            $table->string('paso2_titulo')->default('Te contactamos');
            $table->string('paso2_desc');
            $table->string('paso3_icon')->default('icon-homestep.png');
            $table->string('paso3_titulo')->default('Asesoría personalizada');
            $table->string('paso3_desc');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('acuse_email_configs'); }
};
