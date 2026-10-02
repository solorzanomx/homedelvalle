# Contacto de leads trackeado + reserva de propiedad al crear un trato

> 2026-10-02. Dos hallazgos reales de la misma sesión que trabajó el expediente de Yarlin — lee
> esto antes de tocar los botones de WhatsApp de un lead, `RentalProcessController::store()`, o
> `App\Support\LeadWhatsAppMessage`.

## Hallazgo 1 — "Responder por WhatsApp" no dejaba rastro
El botón era un link directo a `wa.me`, sin pasar por el servidor. Con la mayoría del contacto
real siendo por WhatsApp (no por correo), un lead podía llevar días de conversación real y seguir
viéndose **"Nuevo" / "Contactado: —"** en el panel — el dato no reflejaba la realidad.

**Arreglo**: los 2 links de WhatsApp de la ficha del lead (el botón principal "Responder por
WhatsApp" en la tarjeta "Contactar", y el teléfono clicable en "Datos de contacto") ahora pasan
por `Admin\FormSubmissionController::whatsappRedirect()` — marca `contacted_at` + `status` (mismo
criterio que ya usaba `sendEmail()`: solo la primera vez, nunca pisa un `contacted_at` ya puesto)
y de ahí redirige a `wa.me` con el mensaje contextual. El mensaje se arma del lado del servidor
con **`App\Support\LeadWhatsAppMessage::build()`** — extraído de la vista, donde antes vivía
inline — para que el redirect pueda generarlo sin duplicar la lógica.

## Hallazgo 2 — crear un trato no reservaba la propiedad
`RentalProcessController::store()` (y `updateStage()`, en cualquier etapa, incluido `cerrado`)
nunca tocaban `Property.status`. Una propiedad con un trato real en curso (apartado pagado,
contrato en proceso) seguía apareciendo **"Disponible"** en el sitio público y en el buscador
interno — visible para cualquier otro lead o para quien busca inventario.

**Arreglo**: el formulario de `/rentals/create` ahora trae un checkbox **"Marcar esta propiedad
como Reservada"**, marcado por default, junto al selector de propiedad. Si se deja marcado,
`store()` pone `Property.status = 'reserved'` al crear el trato. **No es automático a ciegas** —
sigue siendo una decisión explícita del broker, porque puede haber varios tratos en paralelo sobre
el mismo inmueble a propósito (ej. comparando candidatos antes de decidir).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Arma el mensaje de WhatsApp contextual | `app/Support/LeadWhatsAppMessage.php` |
| Marca contactado y redirige a WhatsApp | `Admin\FormSubmissionController::whatsappRedirect()`, ruta `admin.form-submissions.whatsapp` |
| Checkbox + reserva de la propiedad al crear el trato | `resources/views/rentals/create.blade.php`, `RentalProcessController::store()` |

## INVARIANTES — no romper
- **Cualquier link de WhatsApp nuevo en la ficha de un lead debe pasar por
  `admin.form-submissions.whatsapp`**, no un `href="https://wa.me/..."` directo — si no, vuelve a
  quedar sin trackear. (El link de WhatsApp de una visita específica —confirmación, reagendado— es
  un caso aparte, usa `$visit->whatsappConfirmationUrl()`, no este.)
- `whatsappRedirect()` nunca pisa un `contacted_at` ya existente — mismo criterio que `sendEmail()`.
- El checkbox de reservar la propiedad es opcional y marcado por default — nunca lo conviertas en
  automático sin opción de desmarcarlo (rompe el caso real de varios tratos en paralelo).

## Cómo probarlo
`php artisan test --filter=LeadContactAndReservationTest` (WhatsApp marca contactado una sola vez;
el checkbox sí/no reserva la propiedad). Verificado a mano contra la BD local (render completo +
llamadas directas al controlador, transacción con rollback).
