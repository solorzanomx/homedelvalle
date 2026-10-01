<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Incidente real 2026-10-01: al eliminar el acceso al portal de un cliente (Client::deletePortalAccess,
 * borra el User con role='client'), TODOS los documentos que ese cliente había subido él mismo
 * mientras tenía sesión en el portal se borraban en cascada — porque `documents.uploaded_by`
 * apuntaba a su propio user_id con `cascadeOnDelete()`. Un cliente (Carlos Sánchez Fernández,
 * client_id=99, proceso de renta #6) perdió así 6 documentos de su expediente (INE, 3 estados de
 * cuenta, comprobante de domicilio) — recuperables solo porque los archivos seguían en disco y
 * nunca se les borró el registro de `documents` por completo en otra parte del código.
 *
 * `documents.verified_by` YA estaba bien (`nullOnDelete()`, ver migración original) — solo
 * `uploaded_by` tenía el problema. Mismo arreglo: la fila de `documents` nunca debe depender de
 * que el usuario que la subió siga existiendo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE documents DROP FOREIGN KEY documents_uploaded_by_foreign');
            DB::statement('ALTER TABLE documents MODIFY COLUMN uploaded_by BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE documents ADD CONSTRAINT documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL');
        } else {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropForeign(['uploaded_by']);
            });
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedBigInteger('uploaded_by')->nullable()->change();
                $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // No se revierte a cascadeOnDelete a propósito — ese era el bug. down() solo deshace la
        // nulabilidad por si hace falta recrear la tabla desde cero en otro entorno, nunca para
        // reintroducir el borrado en cascada en producción.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('UPDATE documents SET uploaded_by = (SELECT id FROM users ORDER BY id LIMIT 1) WHERE uploaded_by IS NULL');
            DB::statement('ALTER TABLE documents DROP FOREIGN KEY documents_uploaded_by_foreign');
            DB::statement('ALTER TABLE documents MODIFY COLUMN uploaded_by BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE documents ADD CONSTRAINT documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE');
        } else {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropForeign(['uploaded_by']);
            });
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedBigInteger('uploaded_by')->nullable(false)->change();
                $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};
