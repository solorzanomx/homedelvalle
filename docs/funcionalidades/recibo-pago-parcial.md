# Recibo de Pago Parcial (venta)

> 2026-10-08. Léelo antes de tocar `ReciboPagoParcialGeneratorService`, `pdf/recibo-pago-parcial.blade.php`,
> `ReciboPagoParcialController`, `Document::CATEGORIES['recibo_pago_parcial']`, o `App\Support\NumeroALetras`.

## Qué resuelve
Una venta casi nunca se cobra en un solo pago — hay anticipo, después el banco, después el crédito
hipotecario (Infonavit, Fovissste, etc.). Cada pago necesita quedar documentado con su propio recibo
firmado por el vendedor, no uno solo al final. Caso real que lo originó: Oscar Nogues → Roberto Ruiz
Ramírez, pago parcial de $2,078,558.67 vía BANORTE el 25/09/2026, y al día siguiente el resto vía
Infonavit — dos recibos, no uno.

Basado en el recibo real que ya usaban en la práctica (mismo caso) — texto casi idéntico, solo
generalizado con tokens.

## ⚠️ Gotcha real de esta sesión: `App\Support\NumeroALetras` ya existía
Al construir el conversor de número a letras para el monto, por poco lo reimplementé desde cero sin
darme cuenta de que **ya existía** y lo usan 3 documentos legales reales:
`PurchaseOfferGeneratorService` (Carta Oferta de Compra), `AdendumComisionGeneratorService`,
`RentalDepositReceiptGeneratorService`. Alcancé a revertir antes de hacer commit — el archivo quedó
exactamente igual que antes, solo con un método nuevo agregado (`pesosMonedaNacional()`, mismo
cálculo que `pesos()` pero termina en "MONEDA NACIONAL" en vez de "M.N.", para el texto parentético
del recibo). **Antes de tocar este archivo de nuevo: revisa los 3 usos existentes, no solo el tuyo.**

## Cómo funciona
- Cada generación pide: **monto recibido**, **método/origen del pago** (texto libre — "mediante
  transferencia interbancaria efectuada a través de BANORTE", "mediante dispersión de crédito
  hipotecario a través de INFONAVIT", etc., porque cambia en cada pago), y **fecha del recibo**
  (editable, default hoy — el recibo real se fechó 25/09 aunque se haya generado después).
- El folio lleva secuencia propia por operación: `RPP-00028-01`, `RPP-00028-02`, ... — se calcula
  contando cuántos `recibo_pago_parcial` ya existen para esa Operation al momento de generar.
- Solo firma el **vendedor** (`Operation.client`) — es un recibo unilateral de su parte, no un
  acuerdo entre las partes. El comprador (`Operation.secondaryClient`) se menciona en el texto como
  quien hizo/originó el pago, pero no firma.
- El inmueble se describe igual que en el Acta de Entrega: dirección + colonia + Alcaldía (vía
  `property->marketColonia->alcaldia`, NO `property->city`) + "Ciudad de México".
- Encabezado "BUENO POR: $X M.N." destacado arriba del documento, como en el original.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Servicio generador | `app/Services/ReciboPagoParcialGeneratorService.php` |
| Conversor de número a letras (compartido, NO crear uno nuevo) | `app/Support/NumeroALetras.php` |
| Plantilla PDF | `resources/views/pdf/recibo-pago-parcial.blade.php` |
| Controlador | `app/Http/Controllers/ReciboPagoParcialController.php` |
| Rutas | `routes/web.php` → `operations.recibo-pago-parcial.generar`, `operations.recibo-pago-parcial.pdf` (esta última lleva `{document}` en la URL porque puede haber varios recibos por operación — a diferencia del Acta de Entrega, no sirve "el último") |
| Categoría de documento | `app/Models/Document.php` → `CATEGORIES['recibo_pago_parcial']` |
| UI (tarjeta en la pestaña Documentos) | `resources/views/operations/show.blade.php`, bloque `@if($operation->type === 'venta')` junto a Acta de Entrega — lista TODOS los recibos generados, cada uno con su propio link |
| Cláusulas editables | `/admin/documentos/recibo_pago_parcial/clausulas` |

## Verificado (2026-10-08)
- Monto en letras verificado carácter por carácter contra el recibo real: `$2,078,558.67` →
  `"DOS MILLONES SETENTA Y OCHO MIL QUINIENTOS CINCUENTA Y OCHO PESOS 67/100 MONEDA NACIONAL"`.
- Render con datos desechables (rollback), PDF real 1 página, formato estándar del sistema.
- Los 3 documentos que ya usaban `NumeroALetras::pesos()` no cambiaron de comportamiento (diff del
  archivo: solo inserciones, cero líneas tocadas del código existente).
- Test suite completo: mismos 9 fallos preexistentes (Mail/V4, ajenos), 121 passed, sin regresiones.

## Pendiente / posible mejora futura (no pedida, no construida)
No hay ningún lugar que sume "cuánto se ha recibido en total" vs. el precio de venta — cada recibo
es independiente. Si se quiere un acumulado (ej. "$2,078,558.67 de $4,850,000 recibidos"), haría
falta guardar el monto en una columna propia de `documents` (hoy no existe) en vez de solo en el
`label` de texto libre.
