<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ledger de correos ya procesados por el escaner IMAP, por Message-ID
     * (header RFC822, no el UID de IMAP que cambia por carpeta/conexion).
     * Existe para NO depender del flag IMAP \Seen: la libreria hace fetch
     * en modo PEEK por defecto (no marca como leido), y aunque marcaramos
     * como leido, tocar esa bandera ensuciaria el uso normal de Gmail del
     * dueno de la cuenta. Este ledger es la unica fuente de "ya lo vi".
     */
    public function up(): void
    {
        Schema::create('imap_processed_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 500)->unique();
            $table->string('type', 30); // client_reply | inmuebles24_lead | skipped
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imap_processed_messages');
    }
};
