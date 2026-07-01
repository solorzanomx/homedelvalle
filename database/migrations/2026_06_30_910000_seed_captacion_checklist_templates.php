<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar checklists de captacion anteriores para re-semillar limpio
        DB::table('stage_checklist_templates')
            ->where('operation_type', 'captacion')
            ->delete();

        $items = [
            // lead
            ['stage' => 'lead',          'title' => 'Registrar datos del inmueble (dirección, tipo, M²)',             'is_required' => true,  'sort_order' => 1],
            ['stage' => 'lead',          'title' => 'Confirmar interés del propietario en vender/rentar',             'is_required' => true,  'sort_order' => 2],
            ['stage' => 'lead',          'title' => 'Agendar llamada o visita inicial',                               'is_required' => false, 'sort_order' => 3],

            // contacto
            ['stage' => 'contacto',      'title' => 'Enviar presentación de Home del Valle (email o WhatsApp)',       'is_required' => true,  'sort_order' => 1],
            ['stage' => 'contacto',      'title' => 'Confirmar recepción de presentación',                           'is_required' => false, 'sort_order' => 2],
            ['stage' => 'contacto',      'title' => 'Agendar visita al inmueble',                                    'is_required' => true,  'sort_order' => 3],

            // visita
            ['stage' => 'visita',        'title' => 'Confirmar visita con el propietario',                           'is_required' => true,  'sort_order' => 1],
            ['stage' => 'visita',        'title' => 'Realizar visita y documentar estado del inmueble',              'is_required' => true,  'sort_order' => 2],
            ['stage' => 'visita',        'title' => 'Tomar fotografías preliminares del inmueble',                   'is_required' => false, 'sort_order' => 3],
            ['stage' => 'visita',        'title' => 'Enviar propuesta de servicios al propietario',                  'is_required' => true,  'sort_order' => 4],
            ['stage' => 'visita',        'title' => 'Iniciar valuación del inmueble',                                'is_required' => false, 'sort_order' => 5],

            // revision_docs
            ['stage' => 'revision_docs', 'title' => 'Solicitar escritura o título de propiedad',                    'is_required' => true,  'sort_order' => 1],
            ['stage' => 'revision_docs', 'title' => 'Solicitar boleta predial al corriente',                        'is_required' => true,  'sort_order' => 2],
            ['stage' => 'revision_docs', 'title' => 'Verificar que el propietario esté en el título',               'is_required' => true,  'sort_order' => 3],
            ['stage' => 'revision_docs', 'title' => 'Solicitar identificación oficial del propietario',             'is_required' => false, 'sort_order' => 4],
            ['stage' => 'revision_docs', 'title' => 'Revisar certificado de libertad de gravamen (si aplica)',      'is_required' => false, 'sort_order' => 5],

            // avaluo
            ['stage' => 'avaluo',        'title' => 'Completar valuación del inmueble en el sistema',               'is_required' => true,  'sort_order' => 1],
            ['stage' => 'avaluo',        'title' => 'Vincular valuación a la captación',                            'is_required' => true,  'sort_order' => 2],
            ['stage' => 'avaluo',        'title' => 'Presentar opinión de valor al propietario',                    'is_required' => true,  'sort_order' => 3],
            ['stage' => 'avaluo',        'title' => 'Acordar precio de lista con el propietario',                   'is_required' => false, 'sort_order' => 4],

            // mejoras
            ['stage' => 'mejoras',       'title' => 'Recomendaciones de home staging entregadas al propietario',   'is_required' => false, 'sort_order' => 1],
            ['stage' => 'mejoras',       'title' => 'Reparaciones menores identificadas y comunicadas',            'is_required' => false, 'sort_order' => 2],
            ['stage' => 'mejoras',       'title' => 'Verificar que el inmueble está listo para fotografía',        'is_required' => true,  'sort_order' => 3],

            // exclusiva
            ['stage' => 'exclusiva',     'title' => 'Precio final acordado con el propietario',                    'is_required' => true,  'sort_order' => 1],
            ['stage' => 'exclusiva',     'title' => 'Generar contrato de exclusiva en el sistema',                 'is_required' => true,  'sort_order' => 2],
            ['stage' => 'exclusiva',     'title' => 'Enviar contrato al propietario para revisión',                'is_required' => true,  'sort_order' => 3],
            ['stage' => 'exclusiva',     'title' => 'Obtener firma del contrato de exclusiva',                     'is_required' => true,  'sort_order' => 4],
            ['stage' => 'exclusiva',     'title' => 'Invitar al propietario al Portal del Cliente',                'is_required' => false, 'sort_order' => 5],

            // fotos_video
            ['stage' => 'fotos_video',   'title' => 'Programar sesión fotográfica profesional',                    'is_required' => true,  'sort_order' => 1],
            ['stage' => 'fotos_video',   'title' => 'Realizar sesión fotográfica',                                 'is_required' => true,  'sort_order' => 2],
            ['stage' => 'fotos_video',   'title' => 'Editar y aprobar fotografías finales',                       'is_required' => true,  'sort_order' => 3],
            ['stage' => 'fotos_video',   'title' => 'Subir fotografías al sistema y portal del cliente',          'is_required' => false, 'sort_order' => 4],
            ['stage' => 'fotos_video',   'title' => 'Grabar video o tour virtual (si aplica)',                    'is_required' => false, 'sort_order' => 5],

            // carpeta_lista
            ['stage' => 'carpeta_lista', 'title' => 'Descripción del inmueble redactada y revisada',              'is_required' => true,  'sort_order' => 1],
            ['stage' => 'carpeta_lista', 'title' => 'Todas las fotos subidas y en orden',                        'is_required' => true,  'sort_order' => 2],
            ['stage' => 'carpeta_lista', 'title' => 'Precio final confirmado con el propietario',                 'is_required' => true,  'sort_order' => 3],
            ['stage' => 'carpeta_lista', 'title' => 'Publicar en EasyBroker y portales inmobiliarios',            'is_required' => true,  'sort_order' => 4],
            ['stage' => 'carpeta_lista', 'title' => 'Notificar al propietario que el inmueble está publicado',    'is_required' => false, 'sort_order' => 5],
        ];

        $now = now();
        foreach ($items as $item) {
            DB::table('stage_checklist_templates')->insert([
                'operation_type' => 'captacion',
                'stage'          => $item['stage'],
                'title'          => $item['title'],
                'description'    => null,
                'sort_order'     => $item['sort_order'],
                'is_required'    => $item['is_required'],
                'is_active'      => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('stage_checklist_templates')
            ->where('operation_type', 'captacion')
            ->delete();
    }
};
