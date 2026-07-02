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
        'changelog' => [
            ['fecha' => '2026-07-02', 'resumen' => 'Creado — a partir de la plantilla de referencia del usuario, con identidad de marca, vigencia de la oferta, condición suspensiva, identificación completa del oferente y monto en letra. Pendiente de revisión por un abogado antes de uso definitivo.'],
        ],
    ],

];
