# Recibo de Comisión (renta)

> 2026-10-09. Léelo antes de tocar `ReciboComisionGeneratorService`, `pdf/recibo-comision.blade.php`,
> `ReciboComisionController`, o `Document::CATEGORIES['recibo_comision']`.

## Qué resuelve
Cuando el propietario paga a Home del Valle la comisión por colocar al inquilino, no había ningún
documento que lo dejara constancia. Caso real que lo originó: comisión de la Renta #6 (Carlos
Sánchez Fernández, propietario Roberto Vargas Arreola, $35,000, entrega 2026-10-09).

## Diferencia clave con Recibo de Pago Parcial (venta)
Ahí el VENDEDOR recibe dinero del comprador y firma. Aquí es al revés: **Home del Valle es quien
recibe el dinero** (la comisión) del propietario. Por eso quien firma el recibo no es el propietario
sino el representante legal de Home del Valle — mismo patrón que Acuerdo de Representación
(`REPRESENTANTE_NOMBRE = 'Ana Laura Monsivais Flores'`, `REPRESENTANTE_CARGO = 'Directora General'`,
duplicado aquí como constantes propias, mismo valor que en `ContratoExclusivaGeneratorService` por
convención del proyecto — no se comparte la constante entre servicios).

## Cómo funciona
- Monto precargado con `RentalProcess.commission_amount`, editable por si el pago es parcial.
- Método de pago y fecha capturados al generar (igual que Recibo de Pago Parcial).
- Folio con secuencia propia por renta: `COM-00006-01`, `COM-00006-02`... — soporta varios recibos
  si la comisión se cobra en partes.
- Monto en letras vía `App\Support\NumeroALetras::pesosMonedaNacional()` (NO reimplementar — ya
  existe y lo usan 4 documentos más).
- Nombres de propietario/inquilino y dirección vía `PurchaseOfferGeneratorService::buyerInfo()` /
  `::propertyInfo()` (mismo patrón que Inventario de Entrega).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Servicio generador | `app/Services/ReciboComisionGeneratorService.php` |
| Plantilla PDF | `resources/views/pdf/recibo-comision.blade.php` (mismo header "BUENO POR" que Recibo de Pago Parcial, una sola firma) |
| Controlador | `app/Http/Controllers/ReciboComisionController.php` |
| Rutas | `routes/web.php` → `rentals.recibo-comision.generar`, `rentals.recibo-comision.pdf` (con `{document}` en la URL — puede haber varios recibos por renta) |
| Categoría de documento | `app/Models/Document.php` → `CATEGORIES['recibo_comision']` |
| UI (tarjeta en la pestaña Documentos) | `resources/views/rentals/show.blade.php`, tab `#tab-documents`, entre Inventario de Entrega y el formulario genérico "Subir Documento" |
| Artículo del Manual del Broker | `database/seeders/help-articles/recibo-comision.md` (categoría "rentas") |

## Verificado (2026-10-09)
- Render con datos desechables (rollback): monto, letras exactas ("TREINTA Y CINCO MIL PESOS
  00/100 MONEDA NACIONAL"), propietario que paga, inquilino mencionado en el texto, confirmado que
  quien firma es Ana Laura (HDV) y no el propietario.
- PDF real: 1 página tamaño carta.
- Test suite completo: mismos 9 fallos preexistentes (Mail/V4, ajenos), 121 passed, sin regresiones.
