# Acuerdo de Representación (venta): igualado al nivel legal del de renta

> 2026-10-06. Léelo antes de tocar `ContratoExclusivaGeneratorService`, `pdf/contrato-exclusiva.blade.php`,
> o `CaptacionAdminController::generarExclusiva()`.

## El hallazgo
Al comparar el Acuerdo de Representación real que ya se usa para renta (`AcuerdoRepresentacionRentaGeneratorService`,
folio ARR-00047, con historial de uso real) contra el recién generado para un caso real de venta
(Julio Alejandro, folio AR-00027), el de venta resultó ser un documento **legalmente más débil**,
sin razón de negocio para que lo fuera — para una venta (montos más altos, más riesgo de título) las
protecciones que le faltaban son si acaso más necesarias que en una renta, no menos.

## Qué le faltaba al de venta (ya corregido)
1. **Declaración de Propiedad** (Folio Real, Escritura Pública, Notario) — el propietario declara
   bajo protesta de decir verdad ser dueño legítimo, con los datos registrales reales del inmueble.
   Sin esto, el Acuerdo no tiene ninguna base documental de que quien firma es realmente el dueño.
2. **Cláusula "Precio de Referencia y Recepción de Ofertas"** — sin ella, el precio de lista queda
   como si fuera una condición fija; con ella, HDV queda facultado para presentar cualquier oferta
   (igual, superior o inferior) sin obligación de aceptarla, y el propietario decide en cada caso.
3. **Cláusula "Manifestaciones, garantías y responsabilidad"** — protege a HDV si el propietario
   mintió sobre algo (ej. no es el dueño real, el inmueble está embargado).
4. El **representante legal** (Ana Laura Monsivais Flores, Directora General) ahora se nombra en el
   párrafo inicial del documento, no solo en la firma al final — igual que en renta.

## Cómo se hizo (mismo patrón que renta, sin reinventar)
- Los campos de escritura (`folio_real`, `escritura_numero`, `escritura_fecha`, `notario_nombre`,
  `notario_numero`, `notario_plaza`) **ya existían en `Property`** (los usa renta desde antes) —
  **no hizo falta ninguna migración nueva**. Ya son editables desde `/properties/{id}/edit`.
- `ContratoExclusivaGeneratorService::missingOwnershipFields(Property $property)` — mismo método
  (mismo nombre, misma lista de campos) que `AcuerdoRepresentacionRentaGeneratorService`.
- El gate de "no generar sin estos datos" vive en dos lugares, igual que renta:
  - **Server-side**: `CaptacionAdminController::generarExclusiva()` rechaza la generación si faltan.
  - **UI**: la tarjeta "Etapa 4" en `admin/captaciones/show.blade.php` muestra qué falta y un link
    directo a editar el inmueble; el botón "Generar Contrato" queda deshabilitado mientras falte algo.
- `pdf/contrato-exclusiva.blade.php` migrado del layout de una sola página fija (`height` +
  `overflow:hidden`, recortaba contenido) al layout multi-página de renta (`min-height` +
  `page-break-after`) — con el contenido nuevo, el documento ahora ocupa 2 páginas reales.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Cláusulas, constantes del representante, gate de escritura | `app/Services/ContratoExclusivaGeneratorService.php` |
| Plantilla PDF (2 páginas) | `resources/views/pdf/contrato-exclusiva.blade.php` |
| Gate server-side | `app/Http/Controllers/Admin/CaptacionAdminController.php::generarExclusiva()` |
| Aviso + link a editar inmueble | `resources/views/admin/captaciones/show.blade.php` (Etapa 4) |
| El patrón original, de donde se copió todo | `app/Services/AcuerdoRepresentacionRentaGeneratorService.php`, `resources/views/pdf/acuerdo-representacion-renta.blade.php` |

## INVARIANTES — no romper
- **`REPRESENTANTE_NOMBRE`/`REPRESENTANTE_CARGO` son constantes de la clase**, referenciadas
  directamente en el blade (`\App\Services\ContratoExclusivaGeneratorService::REPRESENTANTE_NOMBRE`)
  — igual que en renta. No volver al patrón anterior de pasar `$representanteName` como variable de
  instancia; si cambia quién firma legalmente por Home del Valle, se cambia en un solo lugar (la
  constante), no en cada llamada.
- **No generar el Acuerdo sin los 5 campos de escritura** — es la única garantía real de que quien
  firma es el dueño. Si algún día se necesita una excepción (ej. "generar de todos modos, sin
  Declaración de Propiedad"), debe ser una decisión explícita de Alejandro, no un bypass silencioso.
- Si se edita el texto de una cláusula compartida conceptualmente con renta (ej. Manifestaciones,
  casi idéntica en ambas), revisar si el cambio también aplica al Acuerdo de renta — hoy son dos
  copias de texto independientes (`DocumentClause` por `document_type` distinto:
  `contrato_exclusiva` vs `contrato_exclusiva_renta`), no comparten una sola fuente.

## Pendiente / ideas
1. La captación real de Julio Alejandro (#27) no tiene datos de escritura — su Acuerdo ya generado
   sigue siendo válido (se generó antes de este cambio), pero regenerarlo desde la UI ahora requiere
   llenar folio real/escritura/notario en la ficha del inmueble primero.
2. No hay un botón de "Regenerar" para el Acuerdo de venta (como sí existe para la Propuesta de
   Servicios) — hoy, para actualizar un Acuerdo ya generado, hay que hacerlo a mano por consola. Si
   se usa seguido, vale la pena construirlo.
3. No hay una "versión imprimible" en blanco para venta (si existe para renta,
   `acuerdo-representacion-renta-imprimible.blade.php`) — útil para cuando el asesor está con el
   propietario sin acceso a computadora.
4. Las cláusulas de ambos documentos (venta y renta) viven duplicadas en código — si en el futuro se
   necesita que un cambio de redacción aplique a ambos a la vez, valdría la pena unificarlas en un
   solo lugar con las variaciones (renta/venta) como parámetro.
