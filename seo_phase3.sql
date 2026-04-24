-- =============================================================
-- SEO Phase 3 — FAQPage Schema
-- Ejecutar DESPUÉS de: php artisan migrate (para faq_schema column)
-- =============================================================

SET NAMES utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- POST 15: Cómo valuar una propiedad en México
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET faq_schema = JSON_ARRAY(
    JSON_OBJECT(
        'q', '¿El valor catastral es igual al valor de mercado?',
        'a', 'No. El valor catastral lo determina el gobierno municipal para calcular impuestos como el predial, y generalmente es inferior al valor comercial real. Para vender, siempre debes considerar el valor de mercado.'
    ),
    JSON_OBJECT(
        'q', '¿Cada cuánto tiempo debo actualizar la valuación de mi propiedad?',
        'a', 'Se recomienda actualizar la valuación cada 12 a 18 meses, especialmente en mercados activos donde los precios cambian con frecuencia. Una valuación desactualizada puede llevarte a tomar decisiones financieras incorrectas.'
    ),
    JSON_OBJECT(
        'q', '¿Puedo valuar mi propiedad en línea?',
        'a', 'Existen herramientas digitales que ofrecen estimaciones automáticas basadas en datos públicos. Son útiles como referencia inicial, pero no reemplazan una valuación profesional que considere el estado físico real del inmueble y las condiciones específicas del mercado local.'
    ),
    JSON_OBJECT(
        'q', '¿Qué pasa si el banco avalúa mi propiedad por debajo del precio de venta?',
        'a', 'Cuando el avalúo bancario es inferior al precio pactado, el comprador debe cubrir la diferencia de su propio bolsillo, ya que el banco solo financia hasta el valor del avalúo. Esto puede complicar o cancelar la operación si no se prevé desde el inicio.'
    )
)
WHERE id = 15;

-- ─────────────────────────────────────────────────────────────
-- POST 16: ¿Conviene invertir en Narvarte?
-- ─────────────────────────────────────────────────────────────
UPDATE posts SET faq_schema = JSON_ARRAY(
    JSON_OBJECT(
        'q', '¿Cuánto cuesta un departamento en Narvarte en 2026?',
        'a', 'Un departamento de 90 m² en Narvarte tiene un valor de entre $4.5 y $6.3 millones de pesos, con un precio por metro cuadrado de $50,000 a $70,000 MXN según ubicación y acabados.'
    ),
    JSON_OBJECT(
        'q', '¿Cuánto se puede rentar un departamento en Narvarte?',
        'a', 'Las rentas en Narvarte van de $15,000 a $36,000 MXN/mes para departamentos de 85-90 m². El promedio para una unidad de 2 recámaras con estacionamiento se ubica cerca de $22,000 MXN/mes.'
    ),
    JSON_OBJECT(
        'q', '¿Narvarte tiene buena plusvalía?',
        'a', 'Sí. Narvarte mantiene una plusvalía sostenida por encima del promedio de la CDMX, impulsada por oferta limitada, alta demanda y su posición estratégica dentro de la alcaldía Benito Juárez. Algunos segmentos premium registran crecimientos trimestrales del +2%.'
    ),
    JSON_OBJECT(
        'q', '¿Es mejor invertir en Narvarte Poniente o Narvarte Oriente?',
        'a', 'Narvarte Poniente tiende a tener precios ligeramente más altos por su cercanía con Insurgentes y su consolidación comercial. Narvarte Oriente ofrece precios más accesibles con muy buena conectividad y demanda de renta similar. La elección depende de tu presupuesto y estrategia de inversión.'
    ),
    JSON_OBJECT(
        'q', '¿Cómo sé si el precio de un departamento en Narvarte es justo?',
        'a', 'La mejor forma es obtener una valuación profesional gratuita que compare el inmueble con operaciones recientes en la zona. En Home del Valle hacemos ese análisis sin costo y sin compromiso.'
    )
)
WHERE id = 16;

-- ─────────────────────────────────────────────────────────────
-- Verificación
-- ─────────────────────────────────────────────────────────────
-- SELECT id, title,
--        JSON_LENGTH(faq_schema) AS faq_count
-- FROM posts
-- WHERE id IN (15, 16);
