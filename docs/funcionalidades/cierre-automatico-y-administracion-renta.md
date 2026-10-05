# Cierre automático de rentas + panel de "renta activa" (administración)

> 2026-10-04. Léelo antes de tocar `RentalProcess.management_contracted`, `rentals:close-after-entrega`,
> o la condicional de `portal/journey.blade.php`.

## El diseño (confirmado por Alejandro)
"Mi renta" / "Mi camino" en el Portal es el proceso de **cerrar** la renta (desde captación hasta la
entrega) — tanto el propietario como el inquilino ven ahí su parte. Una vez que se llega a la
Entrega, ese proceso **debe terminar y marcarse como cerrado**, salvo que el propietario haya
contratado a Home del Valle para **administración continua** de la renta (cobro mensual, seguimiento
de pagos, renovaciones — ver la cláusula "Objeto y representación" del Acuerdo de Representación) —
en ese caso el Portal sigue activo durante toda la vigencia del contrato.

Antes de este cambio, **nada de esto existía**: el `status` del trato seguía "active" para siempre
después de la Entrega (solo pasaba a "completed" si alguien movía la etapa a "Cerrado" a mano, cosa
que no tenía ningún disparador natural), y el Portal seguía mostrando el camino de cierre —con todos
los pasos palomeados y un mensaje genérico de "¡Todo en orden!"— indefinidamente, incluso para un
inquilino que ya llevaba meses viviendo ahí y pagando su renta con normalidad. El paso "Entrega"
incluso prometía *"Aquí verás tus pagos y fechas importantes"* sin que existiera ningún feature así.

## El arreglo
1. **`rental_processes.management_contracted`** (+ `management_contracted_at`): lo marca el asesor
   desde la ficha del trato (toggle en el sidebar, junto al cambio de etapa).
2. **Cierre automático** (`rentals:close-after-entrega`, corre diario a las 8:30 am): cierra solo los
   tratos que llevan **3+ días** en etapa "Entrega" **sin** administración contratada — avanza a
   "Cerrado" (`status = completed`), deja el `RentalStageLog` correspondiente, y notifica al asesor.
   El margen de 3 días da tiempo a confirmar que la entrega fue real y a marcar administración si se
   le olvidó al asesor. Si un trato no tiene ningún `RentalStageLog` con `to_stage = 'entrega'` (dato
   viejo, migrado a mano), **no se toca** — mejor no cerrarlo que cerrarlo sin saber desde cuándo.
3. **Panel de "renta activa"** (`portal/journey.blade.php`): cuando `$rental->is_active_under_management`
   es verdadero (administración contratada + etapa entrega/activo/renovación), "Mi camino" deja de
   mostrar el checklist de cierre y en su lugar muestra renta mensual, vigencia del contrato (con
   aviso si vence en ≤60 días) y un botón para **reportar una incidencia** — crea una `Task` para el
   asesor (`priority: high`) y le notifica.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Columnas | `rental_processes.management_contracted`, `management_contracted_at` — migración `2026_10_04_100000` |
| Accessor de conveniencia | `RentalProcess::getIsActiveUnderManagementAttribute()` |
| Toggle desde el CRM | `rentals/show.blade.php` (sidebar, junto al cambio de etapa), `RentalProcessController::toggleManagement()`, ruta `rentals.management.toggle` |
| Cierre automático | `app/Console/Commands/CloseRentalsAfterEntrega.php`, registrado en `routes/console.php` (daily 8:30am) |
| Panel de renta activa + reportar incidencia | `resources/views/portal/journey.blade.php`, `PortalRentalController::reportIssue()`, ruta `portal.rentals.report-issue` |

## INVARIANTES — no romper
- **El cierre automático NUNCA toca un trato con `management_contracted = true`** — es la única señal
  que lo exceptúa. Si se agrega otra forma de "administración" en el futuro (ej. un modelo propio en
  vez de un bool), actualizar tanto el comando como `getIsActiveUnderManagementAttribute()`.
- **`rental_stage_logs.user_id` es NOT NULL** — el comando usa `broker_id ?? user_id` del trato y
  **se salta** (no cierra) cualquier trato sin ninguno de los dos, en vez de fallar o inventar un
  usuario. No debería pasar en un trato real (`user_id` se exige al crear el trato).
- El margen de **3 días** (`CloseRentalsAfterEntrega::DAYS_AFTER_ENTREGA`) se mide desde el `RentalStageLog`
  más reciente con `to_stage = 'entrega'`, no desde `rental.updated_at` (que cambia por cualquier edición).
- El panel de "renta activa" reemplaza TODO el bloque de "siguiente paso" + roadmap — no los dos a la
  vez. Mientras NO haya administración contratada, el inquilino sigue viendo el camino normal incluso
  en etapa "entrega" (los ~3 días de margen antes del cierre automático) — es un estado transitorio
  aceptado, no un bug.
- `reportIssue()` es solo para el INQUILINO titular de la renta (reusa `tenantRental()`, el mismo
  guard que `storeObligado`/`storeCoTenant`) — el propietario no tiene este botón todavía.

## Pendiente / ideas
1. Tests formales (hoy solo verificado en consola, transacción con rollback: 3 casos — sin
   administración cierra tras 5 días, con administración no cierra, recién entregado (1 día) no
   cierra todavía — más el toggle y el reporte de incidencia).
2. El propietario no tiene hoy un panel equivalente de "renta activa" ni botón de incidencia.
3. Si se reabre un trato cerrado por error (ej. el asesor olvidó marcar administración a tiempo), hoy
   se hace a mano desde "Cambiar etapa" — no hay un botón de "reabrir" dedicado.
4. El % de comisión/tarifa de administración ("10% mensual" mencionado en la Propuesta de Servicios)
   no se registra en ningún lado todavía — solo existe el bool de sí/no contratada.
