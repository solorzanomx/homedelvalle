<?php

/**
 * Registro de los documentos PDF con identidad de marca (Presentación,
 * Opinión de Valor, Propuesta de Servicios, Manual del Broker, Carta
 * Oferta de Compra). Se usa solo para el panel /admin/documentos —
 * lista qué existe, dónde se genera y su changelog.
 *
 * No hay edición de contenido aquí (decisión confirmada: el contenido
 * se sigue editando en el archivo .blade.php correspondiente). Cada vez
 * que se modifique el contenido de uno de estos documentos, agregar una
 * entrada nueva al 'changelog' correspondiente en el mismo commit.
 */

return [

    'presentacion' => [
        'nombre' => 'Presentación Inicial',
        'descripcion' => 'Documento de bienvenida enviado al propietario tras la primera llamada — portada, comparables de mercado, servicios y comisión.',
        'archivo_fuente' => 'resources/views/pdf/presentations/_layout.blade.php',
        'donde_generarlo' => 'Ficha de captación → tarjeta Presentación → Ver PDF',
        'preview_route' => 'admin.documentos.preview.presentacion',
        'changelog' => [
            ['fecha' => '2026-07-02', 'resumen' => 'Acento de marca cambiado de verde a azul — color centralizado en pdf/_brand_data.php, ya no hardcodeado.'],
            ['fecha' => '2026-07-01', 'resumen' => 'Identidad visual unificada (navy #1e1b4b) con Opinión de Valor y Propuesta de Servicios. Header de marca agregado a las 6 páginas interiores (antes en blanco). Saludo directo "Estimado/a {nombre}" agregado al inicio.'],
        ],
    ],

    'opinion_valor' => [
        'nombre' => 'Opinión de Valor',
        'descripcion' => 'Valuación formal del inmueble entregada durante la visita — rango de precio, comparables y consideraciones clave.',
        'archivo_fuente' => 'resources/views/admin/valuations/pdf.blade.php',
        'donde_generarlo' => 'Ficha de la valuación → Ver PDF',
        'preview_route' => 'admin.documentos.preview.opinion-valor',
        'changelog' => [
            ['fecha' => '2026-07-02', 'resumen' => 'Acento de marca cambiado de verde a azul.'],
            ['fecha' => '2026-07-01', 'resumen' => 'Identidad visual unificada (era el navy/fuente de referencia de los otros 2 documentos). Personalización "Preparada para {nombre}" agregada — antes no tenía ningún dato del propietario.'],
        ],
    ],

    'propuesta_servicios' => [
        'nombre' => 'Propuesta de Servicios',
        'descripcion' => 'Plan de comercialización y comisión, presentado en vivo durante la visita.',
        'archivo_fuente' => 'resources/views/pdf/servicios.blade.php',
        'donde_generarlo' => 'Ficha de captación → tarjeta Propuesta de Servicios → Ver PDF / Ver en vivo',
        'preview_route' => 'admin.documentos.preview.servicios',
        'changelog' => [
            ['fecha' => '2026-07-02', 'resumen' => 'Acento de marca cambiado de verde a azul.'],
            ['fecha' => '2026-07-01', 'resumen' => 'Identidad visual unificada. Footer con el eslogan completo de la marca (antes solo decía "Bienes Raíces"). Botones de acción (Ver PDF/Regenerar/Enviar) agregados a la ficha — antes no existía ningún punto de entrada.'],
        ],
    ],

    'manual_broker' => [
        'nombre' => 'Manual del Broker',
        'descripcion' => 'Guía interna del proceso de captación — qué hacer en cada etapa, manejo de objeciones, qué no prometer.',
        'archivo_fuente' => 'resources/views/pdf/manual-broker.blade.php',
        'donde_generarlo' => 'Pipeline de Captación → botón Manual del Broker',
        'preview_route' => 'admin.captaciones.manual-broker',
        'changelog' => [
            ['fecha' => '2026-07-02', 'resumen' => 'Creado — 9 páginas basadas en docs/08-MANUAL-BROKER-CAPTACION.md, con la identidad de marca de los demás documentos. Incluye las decisiones de Portal del Cliente activo desde CONTACTO e impreso del checklist de documentos para la VISITA.'],
        ],
    ],

    'oferta_compra' => [
        'nombre' => 'Carta Oferta de Compra',
        'descripcion' => 'Oferta formal de compraventa que llena un comprador durante la fase de Promoción.',
        'archivo_fuente' => 'resources/views/pdf/oferta-compra.blade.php',
        'donde_generarlo' => 'Ficha de la Operation (comprador) → pestaña Docs → Generar oferta',
        'preview_route' => 'admin.documentos.preview.oferta-compra',
        'editar_route' => 'admin.documentos.oferta-compra.clausulas',
        'imprimible_route' => 'admin.documentos.oferta-compra.imprimible',
        'flash_route' => 'admin.documentos.oferta-compra.flash',
        'changelog' => [
            ['fecha' => '2026-07-02', 'resumen' => 'Versión imprimible: ahora se puede elegir un Cliente y un Inmueble del CRM para prellenar su identificación (precio/pagos/fecha siguen en blanco). Cláusula de vigencia fija a 10 días. Quitada la caja de "Comentarios adicionales". Línea de firma acortada.'],
            ['fecha' => '2026-07-02', 'resumen' => 'Firma subida (menos espacio en blanco tras las cláusulas) y línea de firma acortada — antes ocupaba todo el ancho de la hoja.'],
            ['fecha' => '2026-07-02', 'resumen' => 'Quitados los m² del inmueble. Nombre del oferente y datos del inmueble en formato título (mayúscula inicial). La fila "Inmueble" ahora incluye la colonia. Consolidado de 2 páginas a 1 sola (real e imprimible).'],
            ['fecha' => '2026-07-02', 'resumen' => 'Nuevo modo "Oferta Flash": genera la carta sin necesidad de captación/pipeline previo, solo eligiendo un Cliente y una Property ya existentes. Nueva fila "Inmueble" (dirección) en el recuadro del oferente. Bug corregido: vigencia_dias no tenía cast a integer, fallaba al generarse desde un formulario HTML real (afectaba también al flujo normal).'],
            ['fecha' => '2026-07-02', 'resumen' => 'Quitada la leyenda de "no sustituye asesoría de un abogado" de las 2 versiones (real e imprimible), a petición del usuario.'],
            ['fecha' => '2026-07-02', 'resumen' => 'Vigencia mínima subida a 8 días (antes 5). Cláusula de condición suspensiva ya no dice "al corriente de pago" junto a boleta predial. Campo Folio Real eliminado (la dirección ya es suficiente). Bug corregido: el nombre del oferente podía salir truncado a solo el primer nombre — ahora prioriza Client.name. Nueva versión imprimible en blanco para llenar a mano.'],
            ['fecha' => '2026-07-02', 'resumen' => 'Creado — a partir de la plantilla de referencia del usuario, con identidad de marca, vigencia de la oferta, condición suspensiva, identificación completa del oferente y monto en letra. Pendiente de revisión por un abogado antes de uso definitivo.'],
        ],
    ],

    'contrato_exclusiva' => [
        'nombre' => 'Contrato de Exclusiva',
        'descripcion' => 'Contrato de exclusiva de comercialización que firma el propietario en la Etapa 4 de captación.',
        'archivo_fuente' => 'resources/views/pdf/contrato-exclusiva.blade.php',
        'donde_generarlo' => 'Ficha de captación → Etapa 4 — Exclusiva → Generar Contrato',
        'preview_route' => 'admin.documentos.preview.contrato-exclusiva',
        'editar_route' => 'admin.documentos.contrato-exclusiva.clausulas',
        'changelog' => [
            ['fecha' => '2026-07-03', 'resumen' => 'Creado — reemplaza la integración con Google Drive (Google Doc + template en Legal > Documentos) por un PDF con identidad de marca, mismo patrón que Carta Oferta de Compra. La confirmación de firma sigue siendo manual (sin firma electrónica real), sin cambios ahí. Pendiente de revisión por un abogado antes de uso definitivo — el contenido es una redacción estándar, no viene de un contrato de referencia previo.'],
        ],
    ],

    'contrato_compraventa' => [
        'nombre' => 'Contrato de Compraventa',
        'descripcion' => 'Contrato de compraventa entre vendedor y comprador, firmado en la etapa "Contrato" del pipeline de venta.',
        'archivo_fuente' => 'resources/views/pdf/contrato-compraventa.blade.php',
        'donde_generarlo' => 'Ficha de Operation (venta) → etapa Contrato → Generar Contrato',
        'preview_route' => null,
        'editar_route' => 'admin.documentos.contrato-compraventa.clausulas',
        'changelog' => [
            ['fecha' => '2026-07-03', 'resumen' => 'Creado — mismo patrón que Carta Oferta de Compra/Contrato de Exclusiva (PDF con identidad de marca, cláusulas editables vía DocumentClause). Confirmación de firma manual (sin firma electrónica real), avanza la Operation a Entrega. Pendiente de revisión por un abogado antes de uso definitivo.'],
        ],
    ],

];
