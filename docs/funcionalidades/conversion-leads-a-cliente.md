# Convertir un lead a cliente — adopción de duplicados + historial completo

> 2026-10-02. Lee esto antes de tocar `LeadConversionService`, `FormSubmissionsTable::convertToClient`,
> `Admin\FormSubmissionController::convertToClient`, o el timeline de `clients/show`.

## Caso real que lo motivó
Yarlin llegó dos veces por el mismo depa (ESTUPENDO DEPA EN COLONIA OLÍMPICA): un alta manual el
25/sep y el correo real de Inmuebles24 el 28/sep. Alejandro convirtió el segundo a Client — pero:
- Su **visita ya confirmada, con feedback real (5/5 al asesor)**, colgaba del *primer* lead
  (`form_submission_id` distinto al que se convirtió) y quedó huérfana, sin `client_id` —
  invisible en la ficha del cliente nuevo.
- El dato de **qué depa le interesaba** (`payload.propiedad_local_id`, lo pone
  `Inmuebles24LeadImporter`) nunca se leía — había que ir a buscarlo a mano para armar el trato de
  renta.
- La ficha del cliente (`/clients/{id}`) **nunca mostró nada del formulario original** — ni
  siquiera de qué portal vino o cuándo.

## Qué se construyó
**`App\Services\LeadConversionService`** — un solo lugar para "convertir lead → cliente", usado
por los 2 puntos de entrada que ya existían (antes cada uno tenía su propia copia de la lógica,
ninguna hacía esto):

1. **Resuelve o crea el `Client`** (igual que antes: por `client_id` si ya lo tiene, si no por
   email existente, si no lo crea).
2. **Adopta leads hermanos**: cualquier otro `FormSubmission` con el mismo correo o teléfono que
   **todavía no tenga cliente** se liga al mismo `Client` — nunca toca uno que ya pertenece a otro
   cliente.
3. **Reasigna sus interacciones**: todas las `Interaction` (visitas, notas) de esos
   `form_submission_id` adoptados que estuvieran huérfanas (`client_id` null) pasan al cliente
   nuevo — el historial de visitas/feedback deja de perderse.
4. **Detecta la propiedad de interés**: busca `propiedad_local_id` en el payload de cualquiera de
   los leads adoptados (el más reciente que lo tenga gana).

Con eso, si el cliente es un inquilino (`renta_inquilino` en `interest_types`) y se detectó una
propiedad, **la conversión redirige directo a `/rentals/create?property=X&owner=Y&tenant=Z`** —
el trato de renta ya prellenado con inquilino, inmueble y propietario — en vez de perder ese
contexto en la lista de leads.

**En la ficha del cliente** (`ClientController::show`), el timeline ahora incluye una entrada por
cada `FormSubmission` ligado (`dot='lead'`, naranja): de dónde llegó, cuándo, qué le interesaba
(si el payload lo trae) y su presupuesto — con link al formulario original.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Lógica compartida de conversión | `app/Services/LeadConversionService.php` |
| Botón rápido desde la lista de leads | `app/Livewire/Admin/FormSubmissionsTable.php::convertToClient` |
| Botón desde la ficha de un lead (y el que redirige a captación si aplica) | `Admin\FormSubmissionController::convertToClient` |
| Entrada "Lead" en el timeline del cliente | `ClientController::show()`, sección antes de `$timeline->sortByDesc('date')` |

## INVARIANTES — no romper
- **Nunca adoptar un `FormSubmission` que ya tiene `client_id`** (`adoptSiblingLeads` filtra
  `whereNull('client_id')`) — evita robarle un lead a otro cliente por coincidencia de teléfono.
  Guardado con test (`test_never_adopts_a_lead_that_already_belongs_to_another_client`).
- **`processNewClient()` (automatizaciones de cliente nuevo) solo corre si `was_existing` es
  `false`** — re-convertir un lead ya ligado, o vincularlo a un cliente existente, nunca debe
  re-disparar esas automatizaciones.
- El redirect a `/rentals/create` solo aplica a inquilinos (`renta_inquilino`) con propiedad
  detectada — leads de propietario (`vendedor`/`vendedor_predio`) siguen yendo al wizard de
  captación como antes, sin tocar ese flujo.
- El timeline del cliente lee `FormSubmission::where('client_id', ...)` — si algún día se agrega
  otra forma de generar un lead sin pasar por `form_submissions`, necesita su propia entrada aquí.

## Cómo probarlo
`php artisan test --filter=LeadConversionServiceTest` (adopción de leads hermanos + reasignación
de interacciones + detección de propiedad; nunca duplica cliente al reconvertir; nunca roba un
lead ya asignado). Verificado a mano contra la BD local (render completo de `ClientController::show`
con un lead real ligado, en transacción con rollback) que la entrada de timeline y el link al
formulario original aparecen correctamente.
