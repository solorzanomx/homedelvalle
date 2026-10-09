# Inventario de Entrega (renta)

> 2026-10-09. Léelo antes de tocar `InventarioEntregaGeneratorService`, `pdf/inventario-entrega.blade.php`,
> `InventarioEntregaController`, o `Document::CATEGORIES['inventario_entrega']`.

## Qué resuelve
El "Anexo A" de entrega de rentas se manejaba 100% en Word, copiado y editado a mano por cada
propiedad (archivo real: "INVENTARIO MELBOURNE 1201 2026-2027"). Caso real que lo originó
(2026-10-09, entrega de Carlos Sánchez Fernández el mismo día): el Word traía pegado el nombre del
inquilino anterior en la firma, no tenía firma de quien entrega, no tenía lecturas de medidores, y
los renglones (cristales, pisos, instalaciones…) describían OTRO inmueble (Melbourne), no el que
realmente se estaba entregando.

## Diferencia clave con Acta de Entrega / Recibo de Pago Parcial
Ahí el texto es jurídico y fijo (cláusulas con tokens). Aquí el contenido real — qué hay y en qué
estado — **cambia por completo según el inmueble**: no son cláusulas legales, son observaciones de
un recorrido físico. Por eso el "Estado del inmueble" es un solo bloque de texto editable
(`items_detalle`), precargado con una lista genérica de 19 renglones vía `DocumentClause` (clave
`items_default`, editable desde `/admin/documentos/inventario_entrega/clausulas` sin tocar código) —
el asesor la ajusta a mano según lo que encuentre: agrega defectos en mayúsculas, borra lo que no
aplique (ej. estufa si el inmueble no la incluye).

## Firman ambas partes directamente
A diferencia del Acta de Entrega de venta (donde Home del Valle firma "por cuenta del vendedor"),
aquí **propietario e inquilino firman directamente** — decisión explícita de Alejandro
(2026-10-09): la renta no la entrega la inmobiliaria, la entrega el propietario. Nombres via
`PurchaseOfferGeneratorService::buyerInfo()` (ya resuelve nombre legal con prioridad a
first_name/last_name_*) sobre `RentalProcess.ownerClient` / `.tenantClient`.

## Campos adicionales que no tenía el Word
- **Lecturas de medidores** (luz, gas, agua) — 3 campos de texto libre, capturados el mismo día.
- **Llaves y accesos entregados** — 4 campos (llaves de recámaras, entrada principal, chips/tarjetas,
  controles de estacionamiento) en vez de líneas en blanco para llenar a mano.
- **Fecha de entrega** editable (default hoy), mismo patrón que Acta de Entrega / Recibo de Pago Parcial.
- **Observaciones** libres aparte del checklist.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Servicio generador | `app/Services/InventarioEntregaGeneratorService.php` |
| Checklist genérico por defecto | `InventarioEntregaGeneratorService::DEFAULT_ITEMS` (19 renglones), editable vía `DocumentClause` clave `items_default` |
| Plantilla PDF | `resources/views/pdf/inventario-entrega.blade.php` (lista de estado en 2 columnas para caber en 1 página) |
| Controlador | `app/Http/Controllers/InventarioEntregaController.php` |
| Rutas | `routes/web.php` → `rentals.inventario-entrega.generar`, `rentals.inventario-entrega.pdf` |
| Categoría de documento | `app/Models/Document.php` → `CATEGORIES['inventario_entrega']` |
| UI (tarjeta en la pestaña Documentos) | `resources/views/rentals/show.blade.php`, tab `#tab-documents`, antes del formulario genérico "Subir Documento" |
| Reutiliza (sin reescribir) | `PurchaseOfferGeneratorService::buyerInfo()` y `::propertyInfo()` — ya resuelven nombre legal y Alcaldía (vía `marketColonia`, con fallback por nombre de colonia) |
| Artículo del Manual del Broker | `database/seeders/help-articles/inventario-entrega.md` (categoría "rentas") |

## Verificado (2026-10-09)
- `php -l` en los 2 archivos nuevos — sin errores.
- `php artisan view:cache` — compila sin errores.
- Render funcional con datos desechables (rollback): nombres, dirección con Alcaldía, fecha,
  lecturas de medidores, los 19 renglones del checklist por defecto, confirmado que NO aparece
  ninguna mención de Home del Valle como firmante (ambas partes firman directo).
- PDF real: 1 página tamaño carta, checklist en 2 columnas.
- Test suite completo: mismos 9 fallos preexistentes (Mail/V4, ajenos), 121 passed, sin regresiones.

## Pendiente / fuera de alcance de esta versión
No hay todavía una "versión de salida" que se compare automáticamente contra la de entrega al
terminar el contrato (quedó señalado como mejora de fondo en el análisis que originó este
feature, pero no se pidió construirlo ahora). Si se construye, lo natural es un segundo folio
(`INV-00008-salida` o similar) que renderice ambos inventarios lado a lado.
