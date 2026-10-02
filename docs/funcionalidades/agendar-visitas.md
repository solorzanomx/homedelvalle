# Agendar visitas — confirmación por correo y la fecha pasada

> 2026-10-01. Lee esto antes de tocar cualquiera de los 4 lugares desde donde se puede agendar una
> visita, o `VisitSchedulingService`.

## Qué resuelve
Hay **4 pantallas independientes** que agendan una visita (cada una crea una `Interaction` tipo
`visit` y, salvo que se desmarque la casilla, manda de inmediato un correo "Tu visita está
agendada"):

| Dónde | Controlador | Requiere |
|---|---|---|
| Ficha de cliente | `ClientController::storeInteraction` | `Client` ya existente |
| Ficha de propiedad | `PropertyController::scheduleVisit` | `client_id` existente |
| Ficha de captación | `Admin\CaptacionAdminController::scheduleVisit` | `Captacion` |
| Ficha de lead sin convertir | `Admin\FormSubmissionController::scheduleVisit` | `FormSubmission` (vía `VisitSchedulingService::createVisitForLead`, sin `Client`) |

## ⚠️ Incidente real 2026-10-01 — correo de confirmación con fecha ya pasada
Yarlin (un lead de Inmuebles24) ya había visitado un departamento el 26 de septiembre, quedó
confirmada y dejó feedback real (le gustó, 5/5 al asesor). El 1 de octubre, Ana Laura reabrió el
formulario original del lead (sin darse cuenta de que ya tenía una visita completada) y usó
"Agendar visita" de nuevo — pero la fecha en el campo quedó en "26 de septiembre". **Ninguna de
las 4 pantallas validaba que la fecha fuera hoy o futura**, así que el sistema creó una visita
nueva con esa fecha pasada y le mandó a Yarlin, el mismo día, un correo de "tu visita está
agendada" para un día que ya vivió — confuso y poco profesional para alguien que ya dio su opinión
de la visita real.

**Arreglo**: los 4 lugares ahora validan `scheduled_at_date` con `after_or_equal:today` (con un
mensaje claro en español, no el default de Laravel) — agendar con una fecha ya pasada ahora se
rechaza con error de validación, en vez de crear la visita y mandar el correo igual.

## Lo que esto NO resuelve (a propósito, fuera de alcance de este fix)
- **No impide agendar una segunda visita** para un lead/cliente que ya tiene una visita
  completada/confirmada — puede haber razones legítimas (segunda visita, otra propiedad). Si se
  quiere advertir de eso también, es una decisión de UX aparte (¿bloquear? ¿solo avisar?), no se
  tocó aquí.
- No valida que la hora tenga sentido (ej. agendar a las 3am) — solo la fecha.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Las 4 validaciones (`after_or_equal:today`) | `PropertyController::scheduleVisit`, `Admin\CaptacionAdminController::scheduleVisit`, `Admin\FormSubmissionController::scheduleVisit`, `ClientController::storeInteraction` |
| El envío real del correo de confirmación | `App\Mail\V4\Mailables\CitaMail` (nueva visita) / `RecordatorioCitaMail` (reenvío/recordatorio) |
| Creación de la `Interaction` para un `Client` ya existente | `VisitSchedulingService::createVisit()` |
| Creación de la `Interaction` para un lead sin convertir (`FormSubmission`) | `VisitSchedulingService::createVisitForLead()` |
| Tarjeta "Visitas" (historial + agendar) en la ficha de un lead | `resources/views/admin/form-submissions/show.blade.php`, sidebar — ver abajo |

## Ficha del lead: "Visitas" vive en el sidebar, en orden lógico (2026-10-02)
Pedido real de Alejandro: la tarjeta "Visitas" aparecía como el primer bloque del área central
de `/admin/form-submissions/{id}` — se movió al sidebar derecho. El formulario de "+ Agendar
visita" (fecha, hora, duración, asesor) viene **abierto por default** cuando el lead todavía no
tiene ninguna visita — un clic menos para el caso más común.

El sidebar completo quedó reordenado siguiendo el flujo real de trabajar un lead — **Temperatura
→ Contactar (WhatsApp + correo con respuesta IA) → Visitas → Checklist de requisitos (si es
inquilino) → Convertir a cliente → Estado del lead** (el select + tag/registrado/contactado,
que es bitácora, no una acción, así que va al final). Antes, "Responder por WhatsApp" y "Enviar
por correo" vivían enterrados hasta el fondo, adentro de la tarjeta "Estado del lead" — ahora son
su propia tarjeta "Contactar", justo después de ver qué tan urgente es el lead.

El selector **"Inmueble a visitar" ya preseleccionaba** el inmueble por el que llegó el lead
(`$propiedadLocal`, detectado de `payload.propiedad_local_id` en leads de Inmuebles24) — eso no
es nuevo, solo se le agregó una nota visible ("Preseleccionado — es el inmueble por el que
llegó...") para que quede claro que se puede cambiar si la visita es de otro inmueble.

## INVARIANTES — no romper
- **`scheduled_at_date` SIEMPRE lleva `after_or_equal:today`** en los 4 lugares — si agregas un
  quinto punto de entrada para agendar visitas, cópialo ahí también. Guardado con test
  (`VisitSchedulingPastDateGuardTest`, verifica por código fuente los 4 archivos).
- El mensaje de error es explícito en español (`'La fecha de la visita no puede ser anterior a
  hoy.'`) — no dejar caer al mensaje default de Laravel sin traducir.

## Cómo probarlo
`php artisan test --filter=VisitSchedulingPastDateGuardTest`.
