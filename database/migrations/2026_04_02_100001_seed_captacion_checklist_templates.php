<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\StageChecklistTemplate;

return new class extends Migration
{
    public function up(): void
    {
        $items = [
            // Lead
            ['captacion', 'lead', 'Registrar datos del propietario', 'Nombre, telefono, email, propiedad de interes', 1, true],
            ['captacion', 'lead', 'Identificar tipo de servicio (venta/renta)', null, 2, true],
            ['captacion', 'lead', 'Verificar titularidad de la propiedad', null, 3, true],
            ['captacion', 'lead', 'Calificar nivel de urgencia', null, 4, false],

            // Contacto
            ['captacion', 'contacto', 'Realizar primera llamada al propietario', null, 1, true],
            ['captacion', 'contacto', 'Explicar proceso de captacion y servicios', null, 2, true],
            ['captacion', 'contacto', 'Enviar presentacion de la inmobiliaria', null, 3, true],
            ['captacion', 'contacto', 'Agendar visita a la propiedad', null, 4, true],

            // Visita
            ['captacion', 'visita', 'Realizar visita presencial a la propiedad', null, 1, true],
            ['captacion', 'visita', 'Levantar inventario y condiciones del inmueble', null, 2, true],
            ['captacion', 'visita', 'Tomar fotos preliminares y medidas', null, 3, true],
            ['captacion', 'visita', 'Identificar reparaciones o mejoras necesarias', null, 4, false],
            ['captacion', 'visita', 'Presentar analisis comparativo de mercado (CMA)', null, 5, true],

            // Revision Docs
            ['captacion', 'revision_docs', 'Solicitar escritura publica o titulo de propiedad', null, 1, true],
            ['captacion', 'revision_docs', 'Obtener predial actualizado y constancia de no adeudo', null, 2, true],
            ['captacion', 'revision_docs', 'Verificar libertad de gravamen', null, 3, true],
            ['captacion', 'revision_docs', 'Revisar identificacion oficial del propietario', null, 4, true],
            ['captacion', 'revision_docs', 'Obtener comprobante de domicilio del propietario', null, 5, false],
            ['captacion', 'revision_docs', 'Verificar regimen de propiedad (en caso de condominio)', null, 6, false],

            // Avaluo
            ['captacion', 'avaluo', 'Solicitar avaluo comercial de la propiedad', null, 1, true],
            ['captacion', 'avaluo', 'Coordinar visita del perito valuador', null, 2, true],
            ['captacion', 'avaluo', 'Recibir y revisar dictamen de avaluo', null, 3, true],
            ['captacion', 'avaluo', 'Definir precio de salida con el propietario', null, 4, true],

            // Mejoras
            ['captacion', 'mejoras', 'Elaborar lista de mejoras recomendadas', null, 1, true],
            ['captacion', 'mejoras', 'Obtener cotizaciones de reparaciones/limpieza', null, 2, false],
            ['captacion', 'mejoras', 'Supervisar ejecucion de mejoras aprobadas', null, 3, false],
            ['captacion', 'mejoras', 'Confirmar que la propiedad esta lista para fotos', null, 4, true],

            // Exclusiva
            ['captacion', 'exclusiva', 'Preparar contrato de exclusiva/comision', null, 1, true],
            ['captacion', 'exclusiva', 'Revisar y negociar terminos con el propietario', null, 2, true],
            ['captacion', 'exclusiva', 'Firmar contrato de exclusiva', null, 3, true],
            ['captacion', 'exclusiva', 'Definir comision y condiciones de pago', null, 4, true],

            // Fotos/Video
            ['captacion', 'fotos_video', 'Programar sesion fotografica profesional', null, 1, true],
            ['captacion', 'fotos_video', 'Realizar sesion de fotos profesionales', null, 2, true],
            ['captacion', 'fotos_video', 'Grabar video o recorrido virtual (tour 360)', null, 3, false],
            ['captacion', 'fotos_video', 'Editar y aprobar material visual final', null, 4, true],
            ['captacion', 'fotos_video', 'Redactar descripcion comercial de la propiedad', null, 5, true],

            // Carpeta Lista
            ['captacion', 'carpeta_lista', 'Verificar expediente documental completo', null, 1, true],
            ['captacion', 'carpeta_lista', 'Confirmar precio de salida final', null, 2, true],
            ['captacion', 'carpeta_lista', 'Aprobar material fotografico y descripcion', null, 3, true],
            ['captacion', 'carpeta_lista', 'Cargar propiedad al sistema con toda la informacion', null, 4, true],
            ['captacion', 'carpeta_lista', 'Confirmar datos de publicacion en portales', null, 5, true],
        ];

        foreach ($items as [$type, $stage, $title, $description, $sort, $required]) {
            StageChecklistTemplate::create([
                'operation_type' => $type,
                'stage' => $stage,
                'title' => $title,
                'description' => $description,
                'sort_order' => $sort,
                'is_required' => $required,
                'is_active' => true,
            ]);
        }
    }

    public function down(): void
    {
        StageChecklistTemplate::where('operation_type', 'captacion')->delete();
    }
};
