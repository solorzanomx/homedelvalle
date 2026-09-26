# Obligado solidario (garantía por póliza)

> 2026-09-28 (rediseñado). Léelo antes de tocar `ObligadoSolidarioService`, el paso "Tu obligado solidario" de `TenantRoadmap`, `?para=obligado` del Portal o `DocumentUploader`.

## Regla de negocio (decidida por Alejandro)
- **Cuando el inquilino NO tiene aval en CDMX (ruta de póliza) se pide un obligado solidario**, con **el mismo cuestionario y documentos que el arrendatario**: datos personales, identificación y domicilio, **nombre de su trabajo (teléfono y antigüedad)**, ingresos, **antiguo arrendador**, **3 referencias personales**; y 3 documentos (INE por ambos lados o pasaporte, comprobante de domicilio, ingresos de los **últimos 3 meses**). Solo se omite "información del hogar".
- **Obligatorio con los 3 planes**; el **asesor puede exentarlo por trato** (`rental_processes.obligado_required = false`; `null` = lo define la ruta).
- **Lo llena el INQUILINO desde su propio Portal.** El obligado NO tiene cuenta ni Portal ni recibe correos: es un `Client` sin `user_id`. El inquilino lo registra (nombre + celular; correo y relación opcionales), le pide la información y la captura (`?para=obligado`) y sube sus documentos.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Alta, estado, avance y **autorización** | `app/Services/ObligadoSolidarioService.php` (`isRequired`, `register`, `status`, `dataProgress`, `tenantMayActFor`) |
| Columnas | `rental_processes.obligado_client_id`, `obligado_required` (`obligado_invited_at` quedó sin uso) — migración `2026_09_28_100000` |
| Paso del inquilino (registro, avance, 2 botones) | `TenantRoadmap::obligado()/nextForObligado()`, `portal/_tenant_roadmap.blade.php`, `PortalRentalController::storeObligado` (`portal.rentals.obligado.store`) |
| Cuestionario a nombre del obligado | `PortalExpedienteController` (`?para=obligado`, `subject()`, `wizardSteps($obligado)`), `portal/expediente.blade.php` (`$obligadoMode`, banner) |
| Documentos a nombre del obligado | `PortalDocumentController::index` (`?para=obligado`), `portal/documents/tenant.blade.php`, `Livewire/Portal/DocumentUploader` (`forClientId`) |
| Campos que cuentan (una sola lista) | `app/Support/ExpedienteFields.php` (`PERSONAL`, `IDENTIFICATION`, `INCOME_TENANT`, `INCOME_OBLIGADO`, `REFERENCES_REQUIRED`) |
| Documentos por persona | `TenantDocumentRows::build($rental,$client,$open,$forObligado)`; `RentalExpedienteStatus::missing($rental,$clientId)` / `isFullyComplete()` |
| CRM: tarjeta (registrar otra persona, exentar, ficha, documentos) y sección de documentos | `rentals/_guarantee_route.blade.php`, `RentalProcessController::registerObligado/toggleObligado`, `RentalDocumentChecklist` (sección `obligado`) |

## Rechazo de referencias personales
El asesor puede **rechazar** una referencia (mamá/familiar directo, vive en la misma casa, no contesta…) desde Renta → Investigación (`rentals/_personal_references.blade.php`, del inquilino y del obligado; avisa si coincide teléfono/domicilio). Columnas `client_references.status|rejection_reason|rejected_at`. Una rechazada **no cuenta** para las 3 (`ClientReference::valid()`, `calcSections`, `dataProgress`), el inquilino recibe una notificación y en su Portal el hueco muestra el motivo y pide **otra persona** (guardar la misma no la reactiva). Rutas `rentals.references.reject|restore` (limitadas a referencias del inquilino/obligado de esa renta). El asesor también puede **capturar o corregir** referencias desde ahí (`rentals.references.save`, `RentalProcessController::saveReference`) para ayudar al cliente por teléfono; guardar en un hueco rechazado lo reemplaza. El parcial está dentro del formulario de investigación: **no anidar `<form>`** (usa `hdvRefAction`).

## INVARIANTES — no romper
- **Toda acción "a nombre del obligado" pasa por `ObligadoSolidarioService::tenantMayActFor($cliente, $obligadoId)`**: solo el inquilino de la renta activa cuyo `obligado_client_id` coincide y que aún lo exige. Es la única puerta (expediente, documentos, uploader, ver/descargar sus archivos). Cualquier endpoint nuevo `para=obligado` debe usarla.
- **`DocumentUploader::$forClientId` es público (el navegador lo puede alterar): se revalida en cada `getClient()`.** Nunca confiar en él sin `tenantMayActFor`.
- **El obligado NO tiene cuenta: jamás `ClientPortalService::createPortalAccount`** ni crear `User`s para personas que escribe un cliente (reutiliza al usuario con ese correo y le cambia rol y contraseña). El correo del obligado es opcional y se descarta si ya existe (`clients.email` es único).
- El cuestionario del obligado **conserva** trabajo, antiguo arrendador y 3 referencias (el usuario ya lo corrigió una vez porque se habían eliminado); `hogar` es lo único que se quita.
- El asesor, al registrar, crea una persona NUEVA (no mezcla datos/documentos de otra); el inquilino solo edita el contacto de la misma persona.
- **El aviso "expediente completo" exige al obligado** cuando es requerido (`isFullyComplete`). Si el asesor lo exenta, el paso desaparece.
- Tests: `tests/Feature/ObligadoSolidarioTest.php`.

## Pendiente / ideas
1. Recordatorio al inquilino si su obligado sigue incompleto pasados N días.
2. Autorización de consulta de Buró del obligado.
3. Que el propietario vea que el obligado ya está completo.
4. Marca "verificada" por referencia.
