<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 6.7 del prompt de leads del blog: isr-venta-casa-habitacion-benito-juarez-2026 e
 * isr-venta-propiedad-heredada-mexico-2026 NO se fusionan (intenciones distintas: vender tu propia
 * casa vs. vender algo que heredaste) — solo se enlazan entre sí. Inserción quirúrgica, defensiva
 * (no-op si el párrafo ancla ya cambió).
 */
return new class extends Migration
{
    private function rows(): array
    {
        return [
            'isr-venta-casa-habitacion-benito-juarez-2026' => [
                'anchor' => '<p>Vender tu casa habitación en Benito Juárez en 2026 puede ser una operación fiscalmente eficiente si llegas bien preparado: documentación del costo original, acreditación de residencia principal y asesoría notarial desde el inicio del proceso.',
                'insert_before' => '<p>Si la propiedad que vas a vender no fue tu casa habitación sino algo que heredaste, las reglas de exención son distintas — revisa <a href="https://homedelvalle.mx/blog/isr-venta-propiedad-heredada-mexico-2026">cuánto ISR pagas al vender una propiedad heredada</a>.</p>',
            ],
            'isr-venta-propiedad-heredada-mexico-2026' => [
                'anchor' => '<p class="font-claude-response-body break-words whitespace-normal leading-[1.7]">Si tu propiedad heredada está en Benito Juárez, podemos orientarte sobre el proceso de venta y conectarte con el tipo de comprador que más conviene según el perfil de tu inmueble.</p>',
                'insert_before' => '<p class="font-claude-response-body break-words whitespace-normal leading-[1.7]">Si la propiedad sí fue tu casa habitación (no algo que heredaste sin vivir ahí), las reglas de exención son otras — revisa <a href="https://homedelvalle.mx/blog/isr-venta-casa-habitacion-benito-juarez-2026">ISR al vender tu casa habitación en Benito Juárez</a>.</p>',
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->rows() as $slug => $row) {
            $post = DB::table('posts')->where('slug', $slug)->first();
            if (! $post || ! str_contains($post->body, $row['anchor']) || str_contains($post->body, 'las reglas de exención son')) {
                continue;
            }

            DB::table('posts')->where('id', $post->id)->update([
                'body' => str_replace($row['anchor'], $row['insert_before'] . $row['anchor'], $post->body),
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->rows() as $slug => $row) {
            DB::table('posts')->where('slug', $slug)->update([
                'body' => str_replace($row['insert_before'], '', DB::table('posts')->where('slug', $slug)->value('body') ?? ''),
            ]);
        }
    }
};
