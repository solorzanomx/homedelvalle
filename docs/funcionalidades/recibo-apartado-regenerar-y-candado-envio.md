# Recibo de Apartado: regenerar, rastrear envíos, candado antes de enviar y candado antes de confirmar

> 2026-10-03. Léelo antes de tocar la tarjeta "Apartado" de `rentals/show.blade.php` o
> `RentalProcessController::storeApartado/sendApartadoReceipt/regenerateApartadoReceipt`.

## El problema real que lo disparó
El Recibo de Apartado del trato de Marco Arturo Berger salió con el nombre legal incorrecto del
propietario (ver [[nombre-legal-en-documentos]]). Al corregir el dato del cliente, Alejandro se
encontró con 3 huecos:
1. **No había forma de regenerar el PDF** sin volver a llenar todo el formulario de Apartado (monto,
   fecha, forma de pago) — "Ver recibo" solo mostraba el PDF ya generado, con el dato viejo.
2. **No se sabía si el recibo viejo (con el dato incorrecto) ya se le había mandado** al inquilino.
3. **Nada impedía reenviarlo sin revisar** que los datos siguieran siendo correctos.

## El arreglo
- **Regenerar**: botón nuevo que vuelve a renderizar el PDF con los datos ACTUALES del trato y de
  las partes (sin pedir de nuevo monto/fecha/forma de pago, que ya están guardados) y crea una
  nueva versión del `Document` (misma lógica que `storeApartado`, no borra las anteriores — quedan
  como historial). `RentalProcessController::regenerateApartadoReceipt()`.
- **Rastreo de envíos**: `sendApartadoReceipt()` ahora crea un `Message` (la misma tabla que
  alimenta "Mensajes enviados") con `trackable_type=RentalProcess`, `metadata.document_id` = el
  documento exacto que se mandó. La tarjeta de Apartado muestra "Enviado el DD/MM/YYYY HH:mm a
  [correo]" o "Todavía no se ha enviado", y si el documento actual es distinto al que se envió,
  avisa que esa versión enviada ya no es la vigente.
- **Detector de datos desactualizados**: si `ownerClient->updated_at` o `tenantClient->updated_at`
  son posteriores a `created_at` del recibo actual, aparece un banner rojo "Regénéralo antes de
  enviarlo" — detecta automáticamente el caso real que originó todo esto (se corrige el nombre del
  cliente DESPUÉS de haber generado el PDF).
- **Candado antes de enviar**: el botón "Enviar/Reenviar por correo" está deshabilitado hasta
  marcar el checkbox "Ya abrí y revisé el recibo…" (Alpine.js, `x-data="{ revisado: false }"`).
  Abrir "Ver recibo" marca el checkbox automáticamente (atajo, se puede desmarcar).

## Hallazgo 2 (mismo día, caso real con Yarlin): confirmar y generar eran la MISMA acción
`storeApartado()` hace dos cosas en un solo submit: (1) marca `apartado_paid_at` (lo que el Portal
del inquilino lee para pintar el paso "Apartado" como ✓ completado — `TenantRoadmap::apartado()`) y
(2) genera el PDF. Alejandro generó el recibo del trato de Yarlin para ver cómo quedaba, **sin que
el depósito se hubiera recibido de verdad**, y el paso 1 del camino de Yarlin se marcó como hecho
en su Portal sin que fuera cierto. Corregido el dato a mano en producción (`apartado_amount`,
`apartado_paid_at`, `apartado_deadline`, `apartado_payment_method` → `null` en el `RentalProcess`).

**Arreglo**: mismo candado que en el envío por correo — un checkbox "Confirmo que este depósito ya
se recibió" debe marcarse antes de que el botón que SÍ confirma (ahora dice **"✅ Confirmar apartado
y generar recibo"**, no solo "Generar recibo de apartado") esté habilitado. "👁 Vista previa (no
confirma nada)" sigue siempre disponible, sin el candado — nunca toca `apartado_paid_at`
(`previewApartado()` no guarda nada, ver el aviso bajo el botón).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Regenerar (sin re-capturar el formulario) | `RentalProcessController::regenerateApartadoReceipt()`, ruta `rentals.apartado.regenerate` |
| Envío con rastro en `Message` | `RentalProcessController::sendApartadoReceipt()` |
| Banner de datos desactualizados, estado de envío, candado de envío | `resources/views/rentals/show.blade.php` (tarjeta "Apartado", bloque `@if($recibo)`) |
| Candado antes de confirmar el apartado (checkbox + botón deshabilitado) | `resources/views/rentals/show.blade.php` (tarjeta "Apartado", formulario de `rentals.apartado.store`) |
| Lee `apartado_paid_at` para pintar el paso como completado en el Portal | `TenantRoadmap::apartado()` |

## INVARIANTES — no romper
- **"Ver recibo" y "Reenviar" siempre toman el `Document` de categoría `recibo_apartado` más
  reciente** (`sortByDesc('created_at')`) — "Regenerar" depende de esto para que la versión nueva
  se vea sola, sin tocar nada más.
- **El candado (checkbox `revisado`) es solo client-side (Alpine)** — es una barrera contra el
  descuido, no contra un uso malicioso (es una herramienta interna, de un asesor). Si se necesita
  una barrera real server-side en el futuro, hay que validarlo también en el controller.
- El detector de "datos desactualizados" compara contra `recibo_apartado` más reciente — si se
  regenera, el banner desaparece solo (el nuevo `created_at` ya es posterior a la última edición
  del cliente).
- Mismo patrón (`Message` + `trackable_type`/`trackable_id` + `metadata.document_id`) se puede
  reusar para otros recibos (ej. cuota de investigación) si se reporta el mismo problema ahí.
- **`storeApartado()` sigue confirmando pago Y generando el PDF en un solo submit** — el candado del
  checkbox es la única barrera (client-side). Si se separa esto en dos acciones distintas en el
  futuro (generar sin confirmar / confirmar lo ya generado), actualizar este documento.
- Mismo riesgo existe en `storeInvestigacionPago` (cuota de investigación) — no se ha tocado todavía,
  pero es el mismo patrón y puede tener el mismo problema.

## Cómo probarlo
Verificado en consola (transacción con rollback): generar el recibo, "enviarlo" (loguea el
`Message` con `document_id` aunque el envío real falle por falta de SMTP local), editar el nombre
del propietario, confirmar que el detector de "desactualizado" se activa, regenerar, confirmar que
crea un `Document` nuevo y distinto. Suite completa sin regresiones (9 fallas preexistentes en
tests de Mail, no relacionadas). Pendiente: test formal en `tests/Feature/`.
