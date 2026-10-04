# Co-arrendatario (contrato a nombre de dos personas)

> 2026-10-03. Léelo antes de tocar `CoTenantService`, el paso "Tu co-arrendatario" de `TenantRoadmap`, `?para=co_tenant` del Portal, o cualquier lugar que hoy distinga `para=obligado`.

## Regla de negocio (decidida por Alejandro)

Caso real: una inquilina y su pareja quieren rentar juntos, y el contrato debe quedar a nombre de **ambos**. Esto
**NO es un obligado solidario** (ver [[obligado-solidario]]): el obligado es un respaldo que no renta, solo responde
si el inquilino no paga; el co-arrendatario sí es parte directa del contrato, paga y pasa la **misma investigación
completa** (buró, solvencia, referencias) que el titular.

- **Mismo cuestionario completo del titular**: datos personales, identificación y domicilio, trabajo/ingresos **con
  su propio comprobante de ingresos**, antiguo arrendador, 3 referencias personales, y sus documentos (INE o
  pasaporte, comprobante de domicilio, ingresos de los últimos 3 meses). Se omite "información del hogar" (es la
  misma vivienda que el titular) y **garantía propia** (la del trato es una sola, ya capturada por el titular).
- **Siempre opcional y manual** — a diferencia del obligado, ninguna ruta de garantía lo exige. Lo agrega el asesor
  (por teléfono, desde el CRM) o el propio inquilino desde su Portal, solo cuando de verdad van a rentar entre
  varios. No hay "exentar" porque nunca es un requisito automático.
- **Lo llena el INQUILINO TITULAR desde su propio Portal** (mismo patrón que el obligado). El co-arrendatario NO
  tiene cuenta ni Portal: es un `Client` sin `user_id` cuyos datos y documentos captura el titular (`?para=co_tenant`).

## Por qué se generalizó en vez de reusar `obligado_client_id`

El mecanismo de captura (segundo `Client`, sin Portal propio, capturado por el titular desde el suyo) es casi
idéntico al del obligado solidario, así que gran parte del código se **generalizó** en vez de duplicarse:
`PortalExpedienteController`/`PortalDocumentController` ahora manejan un `$secondaryRole` (`null` | `'obligado'` |
`'co_tenant'`) en vez de un bool `$obligadoMode`; `TenantDocumentRows::build()` y `RentalExpedienteStatus::missing()`
ya eran genéricos (reciben cualquier `$clientId` secundario) y no necesitaron cambios.

Lo que **sí** se mantuvo como servicio y columna separados (`CoTenantService`, `co_tenant_client_id`) es el **rol
legal**: mezclar "obligado" y "co-arrendatario" bajo un mismo campo habría etiquetado mal el expediente (alguien que
realmente renta apareciendo como simple respaldo) y el cuestionario de ingresos es distinto (el obligado no cuenta
su comprobante de ingresos aquí — `INCOME_OBLIGADO`; el co-arrendatario sí, usa `INCOME_TENANT` completo, igual que
el titular).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Alta, estado, avance y **autorización** | `app/Services/CoTenantService.php` (`register`, `status`, `dataProgress`, `tenantMayActFor`, `isPresent`) |
| Columna | `rental_processes.co_tenant_client_id` — migración `2026_10_03_100000` |
| Relación | `RentalProcess::coTenant()` |
| Paso del inquilino (solo aparece si ya existe), 2 botones + corrección de contacto | `TenantRoadmap::coTenant()/nextForCoTenant()` (incluido en `build()` solo si `co_tenant_client_id` está presente), `portal/_tenant_roadmap.blade.php` (bloque `$step['key'] === 'co_tenant'`), `PortalRentalController::storeCoTenant` (`portal.rentals.co-tenant.store`) |
| Cuestionario a nombre del co-arrendatario | `PortalExpedienteController` (`?para=co_tenant`, `subject()`, `show()` computa `$secondaryRole`/`$secondaryRental`), `portal/expediente.blade.php` (`$secondaryRole`, `$secondaryLabel`, banner genérico) |
| Documentos a nombre del co-arrendatario | `PortalDocumentController::index/authorizedDocument` (`?para=co_tenant`), `portal/documents/tenant.blade.php` (`$secondaryRole`), `Livewire/Portal/DocumentUploader::getClient()` (prueba `ObligadoSolidarioService` y `CoTenantService`) |
| Campos que cuentan | `app/Support/ExpedienteFields::INCOME_TENANT` (el co-arrendatario usa el set completo, no `INCOME_OBLIGADO`) |
| Documentos por persona (reusado tal cual, sin cambios) | `TenantDocumentRows::build($rental,$client,$open,true)`; `RentalExpedienteStatus::missing($rental,$clientId)` / `isFullyComplete()` (ahora también exige al co-arrendatario si está presente) |
| CRM: tarjeta (registrar/cambiar, ficha, documentos) | `rentals/_co_tenant.blade.php` (incluida en `rentals/show.blade.php`, tab Investigación), `RentalProcessController::registerCoTenant` |
| Referencias y arrendador anterior del co-arrendatario | `RentalProcessController::saveReference/savePreviousLandlord/rejectReference/rentalReference` — `who` ahora acepta `tenant|obligado|co_tenant` |

## INVARIANTES — no romper
- **Toda acción "a nombre del co-arrendatario" pasa por `CoTenantService::tenantMayActFor($cliente, $coTenantId)`**:
  solo el inquilino titular de la renta activa cuyo `co_tenant_client_id` coincide. Es la única puerta. Cualquier
  endpoint nuevo `para=co_tenant` debe usarla (mismo patrón que el obligado).
- **El co-arrendatario NO tiene cuenta**: jamás `ClientPortalService::createPortalAccount`. Su correo es opcional y
  se descarta si ya existe (`clients.email` es único) — mismas reglas que el obligado.
- **Es siempre opcional y manual**: no existe (ni debe agregarse) una condición que lo exija automáticamente como
  pasa con el obligado y la ruta de póliza. Si algún día se necesita "requerirlo", es una decisión nueva, no asumir
  que el patrón del obligado aplica igual.
- El co-arrendatario usa `ExpedienteFields::INCOME_TENANT` (completo, con `income_proof_type`), **no**
  `INCOME_OBLIGADO` — si se confunden, su comprobante de ingresos deja de contar para su avance.
- El paso del camino (`TenantRoadmap`) **solo aparece una vez que ya existe** `co_tenant_client_id` — a diferencia
  del obligado, no hay un estado "pendiente de registrar" en el camino (se agrega desde el CRM o desde el botón de
  corrección una vez creado). Si se quiere un alta 100% self-service desde el Portal antes de que exista, hace falta
  diseñar ese punto de entrada (hoy no existe).
- **`isFullyComplete()`** ahora exige también al co-arrendatario si `co_tenant_client_id` está presente, además del
  obligado si es requerido.
- Verificado en consola (transacción con rollback, sin tests formales todavía): alta, edición por el titular,
  cambio de persona por el asesor (no mezcla datos de la anterior), `tenantMayActFor` niega al propietario y a un
  co-arrendatario ya reemplazado, `TenantRoadmap::build()` incluye el paso en el orden correcto, `isFullyComplete()`
  refleja el estado real. Pendiente: tests formales en `tests/Feature/` (mirror de `ObligadoSolidarioTest.php`).

## Pendiente / ideas
1. Tests formales (`tests/Feature/CoTenantTest.php`), mirror de `ObligadoSolidarioTest.php`.
2. Alta 100% self-service desde el Portal (hoy el paso del camino solo aparece una vez creado desde el CRM o desde
   el formulario de corrección — no hay un "+ Agregar co-arrendatario" visible antes de que exista).
3. Si en el futuro se necesita un tercer co-arrendatario (3+ personas en el contrato), este diseño (una sola
   columna `co_tenant_client_id`) no escala — habría que pasar a una tabla pivote.
