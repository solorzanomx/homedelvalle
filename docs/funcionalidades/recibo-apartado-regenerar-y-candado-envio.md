# Recibo de Apartado: regenerar, rastrear envíos y candado antes de enviar

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

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Regenerar (sin re-capturar el formulario) | `RentalProcessController::regenerateApartadoReceipt()`, ruta `rentals.apartado.regenerate` |
| Envío con rastro en `Message` | `RentalProcessController::sendApartadoReceipt()` |
| Banner de datos desactualizados, estado de envío, candado | `resources/views/rentals/show.blade.php` (tarjeta "Apartado") |

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

## Cómo probarlo
Verificado en consola (transacción con rollback): generar el recibo, "enviarlo" (loguea el
`Message` con `document_id` aunque el envío real falle por falta de SMTP local), editar el nombre
del propietario, confirmar que el detector de "desactualizado" se activa, regenerar, confirmar que
crea un `Document` nuevo y distinto. Suite completa sin regresiones (9 fallas preexistentes en
tests de Mail, no relacionadas). Pendiente: test formal en `tests/Feature/`.
