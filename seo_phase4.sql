-- =============================================================
-- SEO Phase 4 — FAQPage Schema: Post 7 (ISR herencia BJ)
-- Ejecutar: mysql -u USER -p DB < seo_phase4.sql
-- =============================================================

SET NAMES utf8mb4;

UPDATE posts SET faq_schema = JSON_ARRAY(
    JSON_OBJECT(
        'q', '¿Pago ISR cuando heredo una propiedad?',
        'a', 'No. Recibir un inmueble por herencia no genera ISR, según el artículo 93, fracción XXII de la Ley del ISR. La obligación fiscal surge después, cuando decides vender ese inmueble.'
    ),
    JSON_OBJECT(
        'q', '¿La venta de una propiedad heredada siempre está exenta de ISR?',
        'a', 'No siempre. La exención aplica solo si cumples tres requisitos: que el inmueble haya sido tu residencia habitual (no la del fallecido), que su valor no exceda 700,000 UDIs (aproximadamente $6.17 millones de pesos en 2026), y que no hayas aplicado esta misma exención en los últimos tres años.'
    ),
    JSON_OBJECT(
        'q', '¿Cuánto ISR se paga si no vivía en la propiedad heredada?',
        'a', 'Se calcula sobre la ganancia real: precio de venta menos el costo de adquisición original del causante, actualizado por inflación (INPC). La tasa puede llegar hasta el 35% sobre esa ganancia, aunque se reducen con deducciones: gastos notariales, mejoras documentadas con facturas y comisiones inmobiliarias de la venta.'
    ),
    JSON_OBJECT(
        'q', '¿El ISAI lo paga el vendedor de una propiedad heredada?',
        'a', 'No. El Impuesto Sobre Adquisición de Inmuebles (ISAI) lo paga el comprador, no el vendedor. En CDMX equivale entre el 2% y el 4.5% sobre el valor del inmueble. Puede influir en la negociación del precio porque el comprador lo contempla en su costo total, pero no es tu obligación fiscal.'
    )
)
WHERE id = 7;

-- Verificación
-- SELECT id, JSON_LENGTH(faq_schema) AS faq_count FROM posts WHERE id = 7;
