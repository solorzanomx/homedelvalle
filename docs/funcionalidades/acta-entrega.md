# Acta de Entrega y Recepción de Inmueble (venta)

> 2026-10-07. Léelo antes de tocar `ActaEntregaGeneratorService`, `pdf/acta-entrega.blade.php`,
> `ActaEntregaController`, o `Document::CATEGORIES['acta_entrega']`.

## El hallazgo que lo originó
Al verificar si la compraventa de un caso real ya cerrado (vendedor John Leonardo Díaz Galvis,
compradora Griselda Nieto) tenía Acta de Entrega generada en el sistema, resultó que **no existía
ningún documento ni categoría para esto** — ni la operación tenía un acta ligada, ni `Document::CATEGORIES`
contemplaba el tipo. Ese caso real se resolvió con un documento hecho a mano en Word, fuera del sistema.

Esta función **no es retroactiva**: esa compraventa ya cerró y ya se cobró, no se toca. Es para
operaciones de venta nuevas de aquí en adelante, cuando lleguen a la etapa de entrega.

## Contenido del acta
Alejandro compartió el documento Word real que ya usaban en la práctica para ese caso, y pidió
usarlo como base exacta — **sin agregar protecciones extra** que se habían propuesto inicialmente
(declaración de propiedad, lecturas de medidores, inventario de muebles, testigos, etc.). Son
4 cláusulas, iguales a las del documento real:

1. **Entrega del inmueble** — el vendedor hace entrega material, física y jurídica del inmueble al comprador.
2. **Recepción de conformidad** — el comprador declara recibirlo a satisfacción, en el estado en que se encuentra.
3. **Entrega de llaves y posesión** — cuántos juegos de llaves se entregan (se captura al generar, no vive en ningún modelo porque varía caso por caso) y que con eso queda formalizada la posesión.
4. **Liberación de responsabilidad** — a partir de la firma, el vendedor deja de responder por el inmueble; toda responsabilidad pasa al comprador.

## Formato: el estándar del sistema, no el de Word
Instrucción explícita de Alejandro: *"necesito que le des el formato que usamos en el sistema,
nada de pies de pagina como en word"*. El PDF sigue el mismo patrón que el resto de los documentos
legales del sistema (Acuerdo de Representación, Carta Oferta de Compra, etc.): header navy con logo
y tag "Documento Legal · Confidencial", folio, cajas de partes (Entrega / Recibe), cláusulas con
numeración automática por CSS counter, bloque de firmas, y el footer estándar
("Home del Valle · tagline · Folio") — nada de membrete ni pie de página tipo carta de Word.

## Género dinámico
Igual que el Recibo de Apartado ("arrendataria"/"arrendatario"), el acta usa `Client.gender === 'M'`
para decidir si la compradora se nombra "LA COMPRADORA" o "EL COMPRADOR" en mayúsculas en las
cláusulas y en el bloque de firma. Si `gender` no está capturado, por defecto es "EL COMPRADOR"
(masculino) — vale la pena revisar que el campo esté bien capturado en la ficha del comprador antes
de generar el acta.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Servicio generador | `app/Services/ActaEntregaGeneratorService.php` |
| Plantilla PDF | `resources/views/pdf/acta-entrega.blade.php` |
| Controlador (generar / ver PDF) | `app/Http/Controllers/ActaEntregaController.php` |
| Rutas | `routes/web.php` → `operations.acta-entrega.generar`, `operations.acta-entrega.pdf` |
| Categoría de documento | `app/Models/Document.php` → `CATEGORIES['acta_entrega']` |
| UI (tarjeta en la pestaña Documentos) | `resources/views/operations/show.blade.php`, bloque `@if($operation->type === 'venta')` junto a Carta Oferta de Compra |
| Cláusulas editables | `/admin/documentos/acta_entrega/clausulas` (vía `DocumentClause`, mismo mecanismo que todos los demás documentos legales) |
| Artículo del Manual del Broker | `database/seeders/help-articles/acta-entrega.md` (categoría "venta") |

## Cómo se genera
En la pestaña Documentos de una Operación de venta, tarjeta "Acta de Entrega":
1. Requiere que la Operación ya tenga comprador vinculado (`secondary_client_id`) — si no lo tiene,
   la tarjeta muestra una advertencia y no deja generar.
2. Pide cuántos juegos de llaves se entregan (default 2, 1-10).
3. Genera el PDF (Browsershot, mismo setup que los demás: `windowSize(816,1056)`,
   `paperSize(215.9,279.4)`, márgenes 0, `emulateMedia('screen')`) y crea un `Document` con
   `category: 'acta_entrega'`, ligado al comprador (`client_id: secondary_client_id`).
4. Queda listado debajo con link "Ver PDF", igual que los demás documentos de la operación.

## Verificado (2026-10-07)
- `php -l` en los 4 archivos nuevos/tocados — sin errores de sintaxis.
- `php artisan view:cache` — compila sin errores (`pdf.acta-entrega`, `operations.show`).
- `php artisan test` — mismas 9 fallas preexistentes de `tests/Feature/Mail/V4/*` (ajenas), 121 passed, sin regresiones.
- Render funcional probado con datos de prueba desechables (cliente/operación creados y revertidos
  en una transacción con rollback, nunca tocó datos reales): nombres, cláusulas, género dinámico
  (compradora mujer → "LA COMPRADORA", hombre → "EL COMPRADOR", sin mezclar), conteo de llaves en
  texto (singular/plural), y el caso sin comprador vinculado (no truena, aunque el controlador ya
  bloquea ese caso antes de llegar aquí).
- PDF real generado con Browsershot: 1 página tamaño carta, formato idéntico al resto de documentos
  del sistema, sin membrete tipo Word.

## Pendiente / hallazgo aparte (no corregido, fuera de alcance)
Al buscar dónde vivía la UI de **Contrato de Compraventa** para mirar el patrón de otro documento
de cierre, resultó que esa tarjeta **no existe en ninguna vista** — `ContratoCompraventaController`
y `ContratoCompraventaGeneratorService` existen pero están huérfanos de UI. El documento real de
Griselda Nieto (`contrato_compraventa`, Document existente en producción) debió generarse por fuera
de cualquier botón real del sistema (consola o script directo). Si se quiere corregir, hace falta
agregar su tarjeta en `operations/show.blade.php` siguiendo el mismo patrón que Acta de Entrega y
Carta Oferta de Compra.
