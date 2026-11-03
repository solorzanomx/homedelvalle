# Pagarés (renta)

> 2026-11-02. Léelo antes de tocar `PagaresGeneratorService`, `pdf/pagares.blade.php`,
> `PagaresController`, o `Document::CATEGORIES['pagare']`.

## Qué resuelve

Cuando la garantía del inquilino incluye pagarés (`guarantee_type` = `pagares` o `aval_pagares`),
se le pedía firmar una serie de pagarés en papel — uno por cada mes de la renta — a mano, con
Word. Caso real que lo originó: serie "PAGARÉS INDIANA 2026-2027" (12 pagarés, Daphne Presenda
Barrios + Raúl Alejandro Flores Luna como co-arrendatarios, a favor del propietario Marcelino
Rodríguez León, Calle Indiana 53-301-A).

## Cómo funciona

- Un pagaré por cada mes de `lease_duration_months`, por el monto de `monthly_rent`, vence el
  `payment_day` de cada mes (o el día del mes de `lease_start_date` si `payment_day` no está
  capturado) **+ 5 días naturales de prórroga** (`PagaresGeneratorService::PRORROGA_DIAS` —
  instrucción de Alejandro, 2026-11-02: si la renta se paga el día 1, el pagaré vence el día 6).
  Se suma con `addDays()`, no fijando el día directo, para que corra bien el mes si el día de pago
  + la prórroga ya no cabe en el mes (ej. día 28 + 5 → día 2 o 3 del mes siguiente, según los días
  del mes).
- Si hay co-arrendatario (`co_tenant_client_id`), firman **ambos** — "DEBEMOS Y PAGAREMOS...
  MANCOMUNADAMENTE" (plural). Sin co-arrendatario, un solo firmante — "DEBO Y PAGARÉ
  INCONDICIONALMENTE" (singular). Reutiliza `RentalProcess::coTenant()`, mismo patrón que
  [[co-arrendatario]].
- A favor del **propietario** (`ownerClient`), con su domicilio legal (`Client::legal_address`) —
  no de Home del Valle, Home del Valle no es parte del pagaré.
- Monto en letras vía `App\Support\NumeroALetras::pesosMonedaNacional()` (ya existía, no se
  reimplementa).
- Se generan **todos los de la serie de una vez** en un solo PDF (nunca de uno en uno) — así se
  hacía en papel, y así se usan (se imprimen y se firman todos en la misma cita).
- **Tamaño media carta** (215.9 × 139.7mm, una carta cortada a la mitad) — un pagaré por página,
  listo para imprimir. Primer documento del sistema que no usa tamaño carta completo.
- Nombre/dirección del propietario e inmueble vía `PurchaseOfferGeneratorService::buyerInfo()`/
  `::propertyInfo()` (mismo patrón que Recibo de Comisión e Inventario de Entrega).
- Cláusulas (aceleración + intereses moratorios 5% mensual, jurisdicción CDMX, sin protesto) vía
  `DocumentClause::text('pagares', ...)` — editables desde `/admin/documentos` como cualquier otro
  documento de marca, si el abogado quiere ajustar el texto.

## Mapa de archivos

| Qué | Dónde |
|---|---|
| Servicio generador | `app/Services/PagaresGeneratorService.php` (`buildSeries()` calcula fechas/montos sin persistir nada, `renderHtml()`/`generatePdf()` generan el documento) |
| Plantilla PDF | `resources/views/pdf/pagares.blade.php` — `@page { size: 215.9mm 139.7mm; }`, un `<div class="page">` por pagaré en loop |
| Controlador | `app/Http/Controllers/PagaresController.php` |
| Rutas | `routes/web.php` → `rentals.pagares.generar`, `rentals.pagares.pdf` |
| Categoría de documento | `app/Models/Document.php` → `CATEGORIES['pagare']` (ya existía) |
| UI (tarjeta en la pestaña Documentos) | `resources/views/rentals/show.blade.php`, tab `#tab-documents` — solo visible si `guarantee_type` es `pagares` o `aval_pagares` |

## Pendiente / límites conocidos

- No hay un campo distinto de "número de unidad" en `Property` — el pagaré dice "LA RENTA DEL
  INMUEBLE UBICADO EN {{dirección completa}}" en vez de "...DE SU PROPIEDAD NÚMERO 301-A UBICADO
  EN...". Si se necesita ese formato exacto con el número de unidad resaltado aparte, hay que
  agregar un campo nuevo a `Property` (no existe hoy).
- Un solo PDF, una sola `Document` por generación — regenerar crea una NUEVA serie completa
  (`Document` nueva), no reemplaza la anterior. Si se regenera (ej. cambió la renta mensual), la
  serie vieja queda en el historial — revisar manualmente cuál es la vigente.
- No hay tests formales (`tests/Feature/`) — verificado con datos desechables en consola
  (transacción con rollback): serie de 12 con co-arrendatario (plural, fechas correctas
  cruzando fin de año), serie sin co-arrendatario (singular, fallback de `payment_day`).

## Verificado (2026-11-02)

- PDF real de 12 páginas (306KB), header/footer de marca, montos y fechas de vencimiento
  correctos mes a mes (incluyendo el cruce de año nuevo), ambos co-arrendatarios firmando.
- Suite completa: mismos 9 fallos preexistentes (Mail/V4, ajenos), 121 passed, sin regresiones.
