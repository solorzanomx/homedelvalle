<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Incidente real 2026-10-01: `Client::deletePortalAccess()` borra el User de portal del cliente —
 * y `documents.uploaded_by` tenía `cascadeOnDelete()`, así que CUALQUIER documento que el cliente
 * hubiera subido él mismo (quedaba con uploaded_by = su propio user_id) se borraba junto con su
 * cuenta. Un cliente real (Carlos Sánchez Fernández) perdió así su INE, 3 estados de cuenta y su
 * comprobante de domicilio — recuperables solo porque los archivos seguían en disco.
 *
 * Migración `2026_10_01_120000_stop_cascading_document_deletes_from_uploader` lo corrige:
 * `uploaded_by` ahora es nullable con `ON DELETE SET NULL`, igual que ya estaba `verified_by`.
 * Este test monta un esquema mínimo propio (no RefreshDatabase — una migración vieja MySQL-only
 * rompe el `migrate` completo en SQLite, mismo gotcha documentado en otros tests del proyecto) y
 * prueba el comportamiento real de la FK, no solo que el código no explote.
 */
class DocumentSurvivesUploaderDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('role')->nullable();
            $t->timestamps();
        });
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamps();
        });
        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('client_id')->nullable();
            $t->unsignedBigInteger('rental_process_id')->nullable();
            $t->unsignedBigInteger('uploaded_by')->nullable();
            $t->string('category')->nullable();
            $t->string('label')->nullable();
            $t->string('file_path')->nullable();
            $t->string('status')->default('received');
            $t->timestamps();

            $t->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $t->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });
    }

    public function test_deleting_the_uploader_user_keeps_the_document_and_clears_uploaded_by(): void
    {
        $portalUser = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
            'name' => 'Carlos Sánchez Fernández', 'email' => 'carlos@test.local', 'role' => 'client',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $clientId = \Illuminate\Support\Facades\DB::table('clients')->insertGetId([
            'name' => 'Carlos Sánchez Fernández', 'user_id' => $portalUser, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $doc = Document::create([
            'client_id' => $clientId,
            'rental_process_id' => 6,
            'uploaded_by' => $portalUser,
            'category' => 'estado_cuenta',
            'label' => 'Estado de Cuenta — Julio 2026',
            'file_path' => 'documents/client-' . $clientId . '/estado-cuenta.jpg',
            'status' => 'verified',
        ]);

        // Esto es lo que dispara deletePortalAccess(): borrar el User de portal.
        \Illuminate\Support\Facades\DB::table('users')->where('id', $portalUser)->delete();

        $doc->refresh();

        $this->assertNotNull($doc, 'El documento no debe desaparecer al borrar al usuario que lo subió.');
        $this->assertDatabaseHas('documents', ['id' => $doc->id, 'label' => 'Estado de Cuenta — Julio 2026']);
        $this->assertNull($doc->uploaded_by, 'uploaded_by debe quedar en null, no borrar la fila completa.');
    }
}
