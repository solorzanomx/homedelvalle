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

## Hallazgo 3 — la página 2 de Leads web daba 404
Reproducido en vivo: al hacer clic en "2" en `/admin/form-submissions`, el navegador terminaba en
`admin.homedelvalle.mx/admin/admin/form-submissions?page=2` — "admin" duplicado, 404 de nginx.

**Causa raíz**: el resolver de paginación que usa `WithPagination` de Livewire
(`Livewire::originalPath()`) devuelve `request()->path()` **sin el "/" inicial**, y
`Paginator::url()` de Laravel lo concatena tal cual (es literalmente `$this->path() . '?' .
query`, sin pasar por `url()->to()`). Con una ruta de un solo segmento el navegador resuelve el
href relativo por accidente y nadie lo nota; con el prefijo `/admin` (dos segmentos: "admin" +
"form-submissions") el navegador lo resuelve relativo al directorio actual y duplica "admin". Es
el único componente de todo el proyecto que usa `WithPagination` — no hay otro lugar con este bug.

**Arreglo**: `FormSubmissionsTable::render()` fuerza una URL absoluta con
`$submissions->withPath(url(\Livewire\Livewire::originalPath()))` — reusa la misma lógica de
Livewire (que ya distingue carga inicial vs. re-render por AJAX), solo le agrega el `url()` que
le faltaba para no quedar relativa.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Arma el mensaje de WhatsApp contextual | `app/Support/LeadWhatsAppMessage.php` |
| Marca contactado y redirige a WhatsApp | `Admin\FormSubmissionController::whatsappRedirect()`, ruta `admin.form-submissions.whatsapp` |
| Checkbox + reserva de la propiedad al crear el trato | `resources/views/rentals/create.blade.php`, `RentalProcessController::store()` |
| Fix de la paginación con URL absoluta | `app/Livewire/Admin/FormSubmissionsTable.php::render()` |

## INVARIANTES — no romper
- **Cualquier link de WhatsApp nuevo en la ficha de un lead debe pasar por
  `admin.form-submissions.whatsapp`**, no un `href="https://wa.me/..."` directo — si no, vuelve a
  quedar sin trackear. (El link de WhatsApp de una visita específica —confirmación, reagendado— es
  un caso aparte, usa `$visit->whatsappConfirmationUrl()`, no este.)
- `whatsappRedirect()` nunca pisa un `contacted_at` ya existente — mismo criterio que `sendEmail()`.
- El checkbox de reservar la propiedad es opcional y marcado por default — nunca lo conviertas en
  automático sin opción de desmarcarlo (rompe el caso real de varios tratos en paralelo).
- **`FormSubmissionsTable::render()` siempre debe forzar `withPath()` con `url()`** — si se quita,
  vuelve el 404 de "admin/admin" en cuanto haya más de 25 leads. Si se agrega un segundo componente
  con `WithPagination` en algún momento, necesita el mismo arreglo (hoy es el único en todo el
  proyecto, por eso el bug nunca se había visto en otro lado).

## Cómo probarlo
`php artisan test --filter=LeadContactAndReservationTest` (WhatsApp marca contactado una sola vez;
el checkbox sí/no reserva la propiedad). `php artisan test --filter=FormSubmissionsPaginationUrlTest`
(guarda el fix de la paginación por código fuente — `Livewire::test()` no sirve para probarlo en
vivo, usa su propio endpoint interno). Verificado a mano contra la BD local (render completo +
llamadas directas al controlador, transacción con rollback) y **reproducido y corregido en vivo en
el navegador contra producción** (clic real en "página 2").
