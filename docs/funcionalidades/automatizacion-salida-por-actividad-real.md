# Automatizaciones: salida automática cuando el cliente ya tiene actividad real

> 2026-10-06. Léelo antes de tocar `AutomationEngine::executeStep()`, `Automation.exit_on_engagement`,
> o antes de crear una automatización de "lead sin respuesta".

## El bug real

A **Yarlin Nava** le llegó un correo "¿Seguimos en contacto?" el 6 de octubre mientras Alejandro
estaba en plena negociación activa de su renta (apartado pagado, documentos en revisión, trato
avanzando) — justo lo opuesto de lo que el correo decía ("no hemos logrado contactarte").

**Causa**: al convertirse en cliente el 2 de octubre, el trigger `new_client` la inscribió en la
automatización **"Seguimiento de lead nuevo — sin respuesta"** (creada por Alejandro el 4 de agosto):
una cadena ciega por tiempo (día 2 → tarea de llamar, día 4 → este correo, día 7 → marcarla **"Frío"**
+ tarea de archivar) que **nunca revisaba si había actividad real** — ni al inscribirse ni en cada
paso. De no corregirse, 3 días después también la habría marcado "Frío".

## El arreglo

Campo nuevo **`automations.exit_on_engagement`** (opt-in, default `false`, checkbox en el editor de
automatizaciones: "Cancelar sola si hay actividad real"). Cuando está activo, `AutomationEngine::
executeStep()` revisa, **antes de ejecutar cualquier paso**, si el cliente ya tiene:
- un `RentalProcess` **activo** (como inquilino o propietario), o
- una `Operation` **activa**, o
- una `Interaction` registrada en los **últimos 14 días**

Si cualquiera es cierto, la inscripción se cancela (`status = cancelled`) en vez de ejecutar el paso
— queda un `AutomationStepLog` (`status: skipped`, con el motivo) y se notifica al asesor asignado.
Es **intencionalmente independiente del momento de la inscripción**: un trato activo HOY basta, sin
importar cuándo empezó — por eso capturó el caso real de Yarlin (su `RentalProcess` se creó un día
DESPUÉS de inscribirse, y aun así la señal es válida porque solo importa el estado actual).

**Dato corregido a mano en producción**: `AutomationEnrollment#85` (Yarlin) → `cancelled`.
`Automation#15` ("Seguimiento de lead nuevo — sin respuesta") → `exit_on_engagement = true`.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Columna + checkbox del editor | `automations.exit_on_engagement` (migración `2026_10_06_100000`), `admin/automations/engine-form.blade.php` |
| La revisión en sí | `AutomationEngine::hasRealEngagement()`, llamada al inicio de `executeStep()` |
| Validación/guardado | `Admin\AutomationEngineController::store()/update()` |

## INVARIANTES — no romper
- **`exit_on_engagement` es opt-in, no el default.** Una automatización que debe correr pase lo que
  pase (ej. una serie de bienvenida, un recordatorio de cumpleaños) NO debe tener esto activado —
  cancelarla por actividad real rompería su propósito. Es específico de secuencias "chasing a alguien
  que no contesta".
- La revisión corre **antes de CUALQUIER tipo de paso** (no solo `send_email`) — incluye `delay`,
  `create_task`, `update_field`, etc. Si algún día se necesita que solo aplique a ciertos tipos de
  paso, hay que decidirlo explícitamente, hoy aplica parejo a toda la cadena.
- Las 3 señales (`RentalProcess` activo, `Operation` activa, `Interaction` en 14 días) viven en
  `AutomationEngine::hasRealEngagement()` — si se agrega un nuevo tipo de "trato" al sistema (ej. un
  módulo nuevo fuera de `RentalProcess`/`Operation`), agregarlo ahí también.
- La cancelación deja rastro: `AutomationStepLog` (`status: skipped`, `result.reason` explica por
  qué) + `Notification` al asesor asignado (`type: automation_cancelled_engagement`) — no es un
  cambio silencioso.

## Cómo probarlo
Verificado en consola (transacción con rollback): un `Client` con `RentalProcess` activo bajo una
automatización con `exit_on_engagement=true` → la inscripción se cancela, el paso NO se ejecuta
(el correo no se manda), queda el log y la notificación. Un `Client` sin actividad → la inscripción
sigue su curso normal, sin cancelarse. Suite completa sin regresiones (9 fallas preexistentes en
tests de Mail, no relacionadas). Pendiente: test formal en `tests/Feature/`.

## Pendiente / ideas
1. Tests formales (`tests/Feature/AutomationExitOnEngagementTest.php`).
2. Revisar si otras automatizaciones existentes de "lead sin respuesta" (si las hay) también deberían
   activar `exit_on_engagement` — hoy solo se activó en la #15, la que causó el caso real.
3. La ventana de 14 días para `Interaction` es un default razonable, no pedido explícitamente — si
   se necesita ajustar, está centralizado en `hasRealEngagement()`.
