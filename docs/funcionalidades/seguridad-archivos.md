# Seguridad de archivos y accesos (documentos, contratos)

> 2026-09-26. Leer completo antes de tocar subidas, descargas, contratos o el grupo de rutas `auth` del CRM.

## Qué se corrigió (había dos huecos reales)
1. **El CRM solo exigía estar autenticado.** 229 rutas (`/clients`, `/rentals`, `/operations`, `/documents/{id}/download`…) no pedían rol de personal: un usuario del Portal (`role='client'`) las abría cambiando la URL (simulación: HTTP 200 en `/clients`; podía descargar, aprobar y borrar documentos ajenos). Ahora el grupo `auth` del CRM lleva **`viewer`** (admin/editor/viewer/broker). Solo quedan fuera `logout`, `contracts.download` (autoriza por pertenencia) y las de salida del Portal.
2. **Los archivos de clientes eran públicos por URL.** INE, estados de cuenta, escrituras y PDFs de contratos vivían en el disco `public` (`/storage/...`): quien tuviera la URL exacta los veía sin sesión. Ahora van al disco **privado** (`storage/app/private`, sin URL) y solo se sirven por rutas con autorización.

## Cómo funciona
| Pieza | Archivo |
|---|---|
| Capa de almacenamiento: `store`, `put`, `locate`, `get`, `delete`, `response` (cabeceras `no-store`, `nosniff`, `Referrer-Policy: no-referrer`) | `app/Support/SecureFiles.php` |
| Resolución en 3 niveles: ruta absoluta (PDFs que genera el sistema en `storage/app/…`) → disco privado → disco público LEGACY | `SecureFiles::locate()` |
| Migración de lo ya subido | `php artisan files:secure-migrate [--dry-run] [--keep-public] [--include-orphans]` |
| Autorización del Portal (documentos): suyo, o de la captación/renta **solo si el documento no tiene dueño individual** — el propietario NO ve el INE/estados de cuenta del inquilino | `PortalDocumentController::authorizedDocument()` |
| Autorización de contratos: personal siempre; cliente solo si es parte (propietario/inquilino de la renta o cliente de la operación) | `ContractController::authorizeContractAccess()` |
| Bitácora de acceso ("quién abrió/descargó qué y cuándo"), en el historial del visor | `DocumentEvent::logAccess()` (tipo `viewed`, sin repetir 10 min) |
| Límite de velocidad en descargas/visor (anti-barrido) | `throttle` en `documents.download|preview` (CRM y Portal) |

## INVARIANTES — no romper
- **Toda subida de un archivo sensible usa `SecureFiles::store/put`, NUNCA `->store(..., 'public')`.** El disco público es solo para imágenes de marketing (fotos de propiedades, blog, logos).
- **Toda lectura usa `SecureFiles::locate/response/get`**; jamás `Storage::disk('public')->url()` ni `asset('storage/...')` con un `file_path` de documento/contrato (usa `route('documents.preview', $id)` / `portal.documents.preview`).
- **Ninguna ruta con `auth` puede quedar sin rol.** `SmokeTest::test_no_authenticated_route_is_open_to_any_logged_in_user` falla si se agrega una; solo se justifica en su lista `$allowed`.
- **Una ruta que sirve un archivo debe autorizar por PERTENENCIA**, no solo por estar autenticado (IDOR): Portal → dueño/parte; CRM → personal.
- **El propietario nunca ve los documentos personales del inquilino** (INE, estados de cuenta, comprobantes de ingresos): solo el resumen de la investigación.
- **Livewire MUEVE su archivo temporal al guardarlo** (vive en el mismo disco privado): en `DocumentUploader::upload()` nombre, tamaño y tipo se leen ANTES de `SecureFiles::store()`. Leerlos después lanza `UnableToRetrieveMetadata` y la subida del cliente falla (error real de 2026-09-26, corregido).
- **Las listas piden miniaturas** (`?thumb=1` → `SecureFiles::thumbnail`, ~240 px, caché en `storage/app/private/thumbs`), no la foto completa. El visor sí pide la original.
- Los archivos privados no tienen URL: si algo "ya no carga" tras migrar, casi seguro una vista sigue apuntando a `/storage/...` — cámbiala por la ruta autorizada.

## Deploy de este cambio
1. `git pull` + comando de deploy normal (el código lee de privado **y** de público legacy, así que nada se rompe entre el deploy y la migración).
2. `php artisan files:secure-migrate --dry-run` (revisa el reporte) y luego `php artisan files:secure-migrate --include-orphans`.
3. Comprobar en el navegador que un documento antiguo abre desde el CRM y desde el Portal, y que la URL directa `https://homedelvalle.mx/storage/documents/...` da 404.

## Pendiente / ideas
- Cifrado en reposo de los archivos (hoy protege el acceso; el disco del servidor no está cifrado por la app).
- Descargas con URL firmada de corta duración y expiración.
- Endurecer más el resto del sitio: cabeceras de seguridad globales (CSP, HSTS), 2FA para personal, rotación de sesiones.
- Las fotos de perfil de clientes (`clients/`) siguen en el disco público (son imágenes, no documentos).
