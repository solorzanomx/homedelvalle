# Obligado solidario (garantía por póliza)

> 2026-09-28. Léelo antes de tocar `ObligadoSolidarioService`, `ObligadoRoadmap`, el paso "Tu obligado solidario" de `TenantRoadmap`, o el modo obligado del Portal.

## Regla de negocio (decidida por Alejandro)
- **Cuando el inquilino NO tiene aval en CDMX (ruta de póliza) se pide un obligado solidario**, con **los mismos datos y documentos que el arrendatario**: datos personales, identificación y domicilio, trabajo/ingresos, y sus 3 documentos (INE por ambos lados o pasaporte, comprobante de domicilio, ingresos de los **últimos 3 meses**). Sin "información del hogar" ni referencias.
- **Es obligatorio con los 3 planes**; el **asesor puede exentarlo por trato** (`rental_processes.obligado_required = false`; `null` = lo define la ruta).
- **Él mismo captura y sube, en su PROPIO Portal**: el inquilino solo escribe nombre, celular y correo (y relación, opcional); el obligado recibe una invitación por correo (enlace de activación de 7 días). **El inquilino nunca ve sus datos ni documentos, solo su avance.**

## Mapa de archivos
| Pieza | Archivo |
|---|---|
| Alta segura, invitación, estado y avance | `app/Services/ObligadoSolidarioService.php` (`isRequired`, `register`, `resendInvitation`, `status`, `dataProgress`) |
| Columnas | `rental_processes.obligado_client_id`, `obligado_required`, `obligado_invited_at` (migración `2026_09_28_100000`); el obligado es un `Client` normal con su `User` (rol `client`) |
| Paso "Tu obligado solidario" del inquilino | `TenantRoadmap::obligado()` / `nextForObligado()` + bloque en `portal/_tenant_roadmap.blade.php`; endpoints `PortalRentalController::storeObligado/resendObligado` (`portal.rentals.obligado.*`) |
| "Mi camino" del obligado (datos → documentos → revisión) | `app/Support/ObligadoRoadmap.php` + `PortalJourneyController` (`mode = obligado`) |
| Modo obligado del Portal (menú corto sin "Mi renta", barra inferior, "Mis documentos", asistente de 3 pasos) | `layouts/portal` (`$isObligadoNav`), `PortalDocumentController::index`, `PortalExpedienteController::show` (`$obligadoRental`, `wizardSteps($obligado)`) |
| Campos que cuentan para el avance (una sola lista) | `app/Support/ExpedienteFields.php` (`PERSONAL`, `IDENTIFICATION`, `INCOME_TENANT`, `INCOME_OBLIGADO`) |
| Documentos por persona | `TenantDocumentRows::build($rental,$client,$open,$forObligado)`; `RentalExpedienteStatus::missing($rental, $clientId)` y `isFullyComplete()` |
| CRM: tarjeta (registrar/cambiar, reenviar, exentar, ver ficha/documentos) y sección de documentos | `rentals/_guarantee_route.blade.php`, `RentalProcessController::registerObligado/resendObligado/toggleObligado`, `RentalDocumentChecklist` (sección `obligado`) |

## INVARIANTES — no romper
- **NUNCA uses `ClientPortalService::createPortalAccount` para dar de alta a un obligado (ni a nadie que un cliente escriba):** reutiliza al usuario con ese correo y le **cambia rol y contraseña**; un inquilino podría degradar una cuenta interna. `ObligadoSolidarioService::register` rechaza correos de cuentas no-`client`, del propio inquilino y del propietario, y crea el usuario con una contraseña aleatoria que nunca se muestra (entra por la invitación).
- **Privacidad:** el inquilino ve nombre, avance de datos (%) y documentos aprobados/total; **nunca** correo, datos ni archivos del obligado (`PortalDocumentController::authorizedDocument` solo permite al dueño del documento). El correo de invitación lo promete.
- **No se cambia al obligado si ya subió documentos** (`hasStarted`); habría que pasar por el asesor.
- El obligado NO tiene aval, apartado, hogar ni referencias (`forObligado`, `wizardSteps($obligado)`); su ingreso se mide con `INCOME_OBLIGADO`.
- **El aviso "expediente completo" exige al obligado** cuando es requerido (`isFullyComplete`).
- El obligado no puede abrir la renta del inquilino (`PortalRentalController::show` solo deja al propietario/inquilino: 403).
- Si el asesor exenta al obligado, el paso desaparece del camino y de la completitud.

## Pendiente / ideas
1. Recordatorio automático al obligado si no activa su cuenta en N días (hoy: reenviar invitación a mano, o WhatsApp desde el camino del inquilino).
2. Autorización de consulta de Buró de Crédito del obligado (el inquilino la da en el Portal; falta el equivalente para el obligado).
3. Que el propietario vea que el obligado ya está completo (hoy solo el asesor y el inquilino).
