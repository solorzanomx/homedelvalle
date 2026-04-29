<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Fix accent erratas in testimonials content (Salvador's quotes)
        // "Asesoria" → "Asesoría"
        // "operacion" → "operación"  (lowercase — mid-sentence)
        // "Operacion" → "Operación"  (uppercase — start of sentence or title)
        DB::statement("
            UPDATE testimonials
            SET content = REPLACE(
                REPLACE(
                    REPLACE(content, 'Asesoria', 'Asesoría'),
                    'operacion',  'operación'
                ),
                'Operacion', 'Operación'
            )
            WHERE content LIKE '%Asesoria%'
               OR content LIKE '%operacion%'
               OR content LIKE '%Operacion%'
        ");

        // Also fix name field in case it contains erratas
        DB::statement("
            UPDATE testimonials
            SET name = REPLACE(name, 'Asesoria', 'Asesoría')
            WHERE name LIKE '%Asesoria%'
        ");
    }

    public function down(): void
    {
        // Accent fixes are not reversible
    }
};
