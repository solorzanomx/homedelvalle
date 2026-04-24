-- =============================================================
-- SEO Phase 1 — Home del Valle Blog
-- Generado: 2026-04-24
-- Optimiza: meta_title, meta_description, focus_keyword,
--           secondary_keywords, schema_type
--           + title (fix año 2025→2026 en IDs 16, 18, 19)
-- Idempotente: puede ejecutarse múltiples veces sin daño
-- =============================================================

SET NAMES utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- ID 1: Vender propiedad heredada CDMX
-- BUG FIX: meta_description tenía "URL slug/blog/..." literalmente
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Vender Propiedad Heredada CDMX 2026 | Home del Valle',
    meta_description  = 'Guía paso a paso 2026 para vender una propiedad heredada en CDMX: documentos necesarios, impuestos y cómo obtener el mejor precio. Asesoría gratuita.',
    focus_keyword     = 'vender propiedad heredada CDMX',
    secondary_keywords = JSON_ARRAY(
        'vender casa heredada CDMX',
        'sucesión CDMX 2026',
        'impuestos herencia inmueble México',
        'documentos venta propiedad heredada'
    ),
    schema_type = 'Article'
WHERE id = 1;

-- ─────────────────────────────────────────────────────────────
-- ID 3: Valor terreno desarrollable Benito Juárez
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Valor Real de tu Terreno en Benito Juárez 2026 | HDV',
    meta_description  = 'Calcula el valor de tu propiedad en Benito Juárez como terreno para constructores. Factores clave y datos reales del mercado 2026 para decidir bien antes de vender.',
    focus_keyword     = 'valor terreno desarrollable Benito Juárez',
    secondary_keywords = JSON_ARRAY(
        'terreno para constructores Benito Juárez',
        'suelo desarrollable CDMX',
        'precio terreno BJ 2026',
        'vender terreno desarrolladores CDMX'
    ),
    schema_type = 'Article'
WHERE id = 3;

-- ─────────────────────────────────────────────────────────────
-- ID 4: Zonificación H5/H6 Benito Juárez
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Zonificación H5 y H6 en Benito Juárez | Home del Valle',
    meta_description  = 'Descubre si tu casa en Benito Juárez tiene potencial de desarrollo con zonificación H5 o H6. Guía completa sobre valor inmobiliario y oportunidades de venta.',
    focus_keyword     = 'zonificación H5 H6 Benito Juárez',
    secondary_keywords = JSON_ARRAY(
        'uso de suelo H5 CDMX',
        'potencial desarrollo propiedad BJ',
        'vender casa constructores Benito Juárez',
        'SEDUVI zonificación H6 CDMX'
    ),
    schema_type = 'Article'
WHERE id = 4;

-- ─────────────────────────────────────────────────────────────
-- ID 5: Herencia en Benito Juárez — guía legal y fiscal
-- (Diferenciado de ID 1: este es BJ-específico + enfoque fiscal)
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Herencia en Benito Juárez: Guía Legal y Fiscal 2026 | HDV',
    meta_description  = 'Guía legal y fiscal 2026 para vender una propiedad heredada en Benito Juárez: trámites notariales, impuestos reales y cómo obtener el mejor precio de venta.',
    focus_keyword     = 'vender herencia Benito Juárez 2026',
    secondary_keywords = JSON_ARRAY(
        'tramitar herencia Benito Juárez',
        'impuestos venta propiedad heredada BJ',
        'notario herencia CDMX',
        'guía fiscal herencia México 2026'
    ),
    schema_type = 'Article'
WHERE id = 5;

-- ─────────────────────────────────────────────────────────────
-- ID 6: Propiedad sin testamento CDMX
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Propiedad Sin Testamento CDMX: Cómo Vender 2026 | HDV',
    meta_description  = 'Familiar murió sin testamento en CDMX. Guía real 2026: trámites, tiempos, costos y el beneficio fiscal poco conocido que puede reducir tus impuestos al vender.',
    focus_keyword     = 'propiedad sin testamento CDMX',
    secondary_keywords = JSON_ARRAY(
        'intestado CDMX 2026',
        'regularizar propiedad sin testamento',
        'sucesión intestada México',
        'ISR exención propiedad heredada'
    ),
    schema_type = 'Article'
WHERE id = 6;

-- ─────────────────────────────────────────────────────────────
-- ID 7: ISR venta propiedad heredada México
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'ISR al Vender Herencia en México 2026 | Home del Valle',
    meta_description  = '¿Cuánto ISR pagas al vender una propiedad heredada en México? La respuesta en 2026: no siempre aplica la exención. Conoce exactamente cuándo y cuánto pagarás.',
    focus_keyword     = 'ISR venta propiedad heredada México',
    secondary_keywords = JSON_ARRAY(
        'exención ISR venta casa heredada',
        'impuesto venta herencia 2026',
        'cálculo ISR inmueble México',
        'SAT venta propiedad heredada'
    ),
    schema_type = 'Article'
WHERE id = 7;

-- ─────────────────────────────────────────────────────────────
-- ID 8: Coheredero no quiere vender
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Coheredero No Quiere Vender: Opciones Legales CDMX | HDV',
    meta_description  = '¿Uno de los herederos bloquea la venta de la propiedad en CDMX? Conoce tus opciones legales reales en 2026: desde la negociación hasta la partición judicial.',
    focus_keyword     = 'coheredero no quiere vender propiedad',
    secondary_keywords = JSON_ARRAY(
        'conflicto herencia CDMX',
        'copropietario no vende inmueble',
        'partición judicial propiedad México',
        'juicio sucesorio CDMX 2026'
    ),
    schema_type = 'Article'
WHERE id = 8;

-- ─────────────────────────────────────────────────────────────
-- ID 9: Vender con inquilinos Benito Juárez
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Vender con Inquilinos en Benito Juárez 2026 | HDV',
    meta_description  = 'Quieres vender tu propiedad en Benito Juárez pero tienes inquilinos. Guía legal 2026: derecho del tanto, plazos exactos y cómo proceder sin conflictos.',
    focus_keyword     = 'vender propiedad con inquilinos CDMX',
    secondary_keywords = JSON_ARRAY(
        'derecho del tanto inquilino México',
        'vender casa rentada CDMX 2026',
        'plazo preferencia arrendatario',
        'contrato arrendamiento venta propiedad'
    ),
    schema_type = 'Article'
WHERE id = 9;

-- ─────────────────────────────────────────────────────────────
-- ID 10: Vender o rentar Benito Juárez
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = '¿Vender o Rentar en Benito Juárez? Análisis 2026 | HDV',
    meta_description  = '¿Vender o rentar tu propiedad en Benito Juárez? Análisis real 2026 con números de Del Valle, Nápoles y Narvarte. La respuesta depende de un factor clave.',
    focus_keyword     = 'vender o rentar propiedad Benito Juárez',
    secondary_keywords = JSON_ARRAY(
        'rendimiento renta Del Valle 2026',
        'cap rate Benito Juárez',
        'plusvalía vs renta CDMX',
        'retorno inversión Narvarte Nápoles'
    ),
    schema_type = 'Article'
WHERE id = 10;

-- ─────────────────────────────────────────────────────────────
-- ID 11: Precios por colonia Benito Juárez
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Precio por m² en Benito Juárez por Colonia 2026 | HDV',
    meta_description  = 'Precios reales por m² en Benito Juárez 2026: Del Valle, Nápoles, Narvarte, Xoco, Acacias y Álamos. Datos verificados para saber cuánto vale tu propiedad hoy.',
    focus_keyword     = 'precio m2 colonias Benito Juárez 2026',
    secondary_keywords = JSON_ARRAY(
        'precio m2 Del Valle 2026',
        'precio m2 Nápoles CDMX 2026',
        'precio m2 Narvarte 2026',
        'cuánto vale mi casa Benito Juárez'
    ),
    schema_type = 'Article'
WHERE id = 11;

-- ─────────────────────────────────────────────────────────────
-- ID 12: Vender como terreno a constructores (BORRADOR)
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = '¿Tu Casa Vale Más como Terreno en BJ? Precios 2026 | HDV',
    meta_description  = '¿Tu casa en Nápoles, Del Valle o Xoco vale más como terreno? Lo que pagan los constructores en 2026 y qué características necesita tu propiedad para calificar.',
    focus_keyword     = 'vender casa como terreno constructores Benito Juárez',
    secondary_keywords = JSON_ARRAY(
        'precio terreno Nápoles 2026',
        'vender a constructores Del Valle',
        'suelo desarrollable Xoco',
        'H5 H6 precio terreno BJ'
    ),
    schema_type = 'Article'
WHERE id = 12;

-- ─────────────────────────────────────────────────────────────
-- ID 15: Valuar propiedad México
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    meta_title        = 'Cómo Valuar una Propiedad en México 2026 | HDV',
    meta_description  = 'Aprende a valuar tu propiedad en México con esta guía paso a paso 2026. Conoce el precio justo antes de vender y toma decisiones con datos reales del mercado.',
    focus_keyword     = 'valuar propiedad México 2026',
    secondary_keywords = JSON_ARRAY(
        'valuación inmobiliaria CDMX',
        'avalúo propiedad gratuito',
        'precio justo inmueble México',
        'valor catastral vs comercial'
    ),
    schema_type = 'Article'
WHERE id = 15;

-- ─────────────────────────────────────────────────────────────
-- ID 16: Invertir Narvarte — FIX AÑO 2025→2026 en title y meta
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    title             = '¿Conviene invertir en Narvarte en 2026? Guía completa',
    meta_title        = 'Invertir en Narvarte 2026 — ¿Vale la Pena? | HDV',
    meta_description  = '¿Vale la pena invertir en Narvarte en 2026? Precios reales, plusvalía y CAP rate por tipo de inmueble. Asesoría gratuita con Home del Valle, expertos en BJ.',
    focus_keyword     = 'invertir en Narvarte 2026',
    secondary_keywords = JSON_ARRAY(
        'precio m2 Narvarte 2026',
        'plusvalía Narvarte CDMX',
        'rentabilidad departamento Narvarte',
        'cap rate Narvarte Benito Juárez'
    ),
    schema_type = 'Article'
WHERE id = 16;

-- ─────────────────────────────────────────────────────────────
-- ID 18: Invertir Nápoles/Acacias — FIX AÑO 2025→2026
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    title             = 'Invertir en Nápoles o Acacias: ¿cuál da mejor retorno en 2026?',
    meta_title        = 'Invertir en Nápoles o Acacias 2026 | Home del Valle',
    meta_description  = '¿Invertir en Nápoles o Acacias en 2026? Compara plusvalía, precios reales y rentabilidad en Benito Juárez. Asesoría gratuita con Home del Valle.',
    focus_keyword     = 'invertir en Nápoles Acacias Benito Juárez',
    secondary_keywords = JSON_ARRAY(
        'precio m2 Nápoles 2026',
        'precio m2 Acacias CDMX',
        'plusvalía Nápoles Benito Juárez',
        'rentabilidad inversión Acacias'
    ),
    schema_type = 'Article'
WHERE id = 18;

-- ─────────────────────────────────────────────────────────────
-- ID 19: Invertir inmuebles BJ — FIX AÑO 2025→2026
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET
    title             = 'Invertir en inmuebles en Benito Juárez: ¿vale la pena en 2026?',
    meta_title        = 'Invertir en Inmuebles Benito Juárez 2026 | HDV',
    meta_description  = '¿Vale la pena invertir en inmuebles en Benito Juárez en 2026? Precios reales, plusvalía y ROI por colonia: Del Valle, Nápoles, Narvarte. Asesoría sin costo.',
    focus_keyword     = 'invertir inmuebles Benito Juárez 2026',
    secondary_keywords = JSON_ARRAY(
        'inversión inmobiliaria BJ 2026',
        'plusvalía Benito Juárez colonias',
        'mejor colonia invertir CDMX',
        'ROI departamento Del Valle'
    ),
    schema_type = 'Article'
WHERE id = 19;

-- =============================================================
-- VERIFICACIÓN — ejecutar después para confirmar
-- =============================================================
-- SELECT id, title, meta_title, focus_keyword,
--        CHAR_LENGTH(meta_title) AS mt_len,
--        CHAR_LENGTH(meta_description) AS md_len
-- FROM posts
-- WHERE id IN (1,3,4,5,6,7,8,9,10,11,12,15,16,18,19)
-- ORDER BY id;
