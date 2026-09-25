# Documentos: subida guiada, calidad y revisión del asesor

> Construido 2026-09-25 (commits `ddd8bef`, `fb0ffca`, `b01a62e`). **Léelo completo antes de tocar cualquier archivo de la lista de abajo.** Manual del Broker: artículo `revision-documentos-portal`.

## Qué resuelve
1. La pestaña Documentos de una renta mostraba ~75 categorías (venta, compra, avales de otros tratos…). Ahora solo lo que aplica al trato.
2. El asesor no podía ver un documento sin descargarlo. Ahora hay un visor a pantalla completa con aprobar/rechazar.
3. Un rechazo no explicaba nada. Ahora lleva motivo obligatorio que el cliente ve en el Portal.
4. Clientes subían fotos ilegibles (foto de la pantalla del teléfono). Ahora hay guía por tipo + revisión de calidad antes de guardar, y se aceptan PDF.

## Mapa de archivos (fuente de verdad de cada pieza)

| Pieza | Archivo | Notas |
|---|---|---|
| Qué documentos aplican a una renta | `app/Support/RentalDocumentChecklist.php` | `build($rental)` → secciones inquilino/garantía/propietario/pagos/contratos + "otros". Garantía sale de `guarantee_type`. |
| Vista pestaña Documentos | `resources/views/rentals/show.blade.php` (tab-documents) + `rentals/_doc_category.blade.php` + `rentals/_doc_viewer.blade.php` | El visor es un modal con JS `window.hdvDocViewer`. |
| Ver / estado / eliminar | `app/Http/Controllers/RentalDocumentController.php` | `preview` (inline), `updateStatus` (responde JSON si `expectsJson`), `download`, `destroy`. Rutas `documents.preview|update-status|download` en `routes/web.php`. |
| Guía de cómo subir | `app/Support/DocumentUploadGuide.php` | UNA definición por tipo (id, statement, utility, legal, payment, generic). Alimenta las leyendas Y los mensajes de rechazo. |
| Leyendas en el Portal | `resources/views/portal/_upload_tips.blade.php` (por categoría) y `portal/_upload_guide_card.blade.php` (tarjeta general) | Incluidas en Livewire uploader, expediente, Mis documentos y modal de captación (este último por JS: `uploadTipsByKind`/`uploadKindMap`). |
| Revisión de calidad | `app/Services/DocumentQualityService.php` | `gate($file,$category,$clientId)` ANTES de guardar; `record($doc,$gate)` DESPUÉS. |
| Puntos de subida del Portal (los 4 llaman a gate+record) | `app/Livewire/Portal/DocumentUploader.php`, `Portal/PortalDocumentController::upload`, `Portal/PortalCaptacionController::uploadDocument`, `Portal/PortalExpedienteController::uploadDocument` | **Cualquier punto de subida nuevo debe llamar al gate.** |
| Lectura IA de PDFs | `AnthropicProvider::completeVision` (PDF → bloque `document`), `AddressDocumentAIExtractionService` acepta PDF | El servicio de INE sigue solo JPG/PNG. |
| Columnas | `documents.quality_status` (`ok`/`warn`), `documents.quality_notes`; ya existían `rejection_reason`, `ai_verification_*` | Migración `2026_09_25_120000_add_quality_to_documents`. |

## Bandeja central "Docs por revisar" (2026-09-25)
| Pieza | Archivo |
|---|---|
| Qué cuenta como "por revisar" + contexto + dueño | `app/Support/DocumentReviewInbox.php` (`query()`, `count()` con caché 30 s, `context()`, `ownerUserId()`) |
| Página | `DocumentReviewController@index` → `resources/views/documents/inbox.blade.php` (ruta `documents.inbox` = `/revision-documentos`) |
| Fila de documento reutilizable | `resources/views/rentals/_doc_row.blade.php` (la usan la pestaña de la renta Y la bandeja) |
| Contador en el menú | `layouts/app-sidebar.blade.php` ("Docs por revisar") |
| Alerta >24 h | `app/Console/Commands/CheckDocumentsPendingReview.php`, agendado 09:30 en `routes/console.php`; 1 aviso por asesor, sin repetir el mismo día |

## INVARIANTES — no romper
- **No volver a poner `capture="environment"` en ningún `<input type=file>`.** Forzaba la cámara, impedía elegir PDF y provocaba fotos a pantallas. Los 6 formularios del expediente usan `hdvUploadSubmit(this)` (avisa "Subiendo y revisando…"), no `this.form.submit()` directo.
- **El gate de calidad nunca debe tronar una subida:** IA caída/sin key → se acepta (fail-open). Solo se BLOQUEA lo evidente (foto de pantalla, ilegible, imagen diminuta, PDF inválido/con contraseña). Lo dudoso pasa con `quality_status='warn'` ("Calidad dudosa").
- **Salida de falsos positivos:** tras `MAX_BLOCKS`=2 bloqueos de IA en la misma categoría (clave de caché `docq:blocks:{cliente}:{categoria}`), el 3.º pasa con marca. Las fallas duras (PDF con contraseña, imagen que no abre, diminuta) NO son evitables.
- **Comprobantes de pago (`payment`)**: las capturas de pantalla son legítimas → no se bloquea por "foto de pantalla" y el umbral de resolución es menor.
- **Rechazar exige motivo** (el visor lo obliga; el backend lo acepta opcional para no romper el flujo viejo). Aprobar limpia `rejection_reason`.
- **Un documento rechazado NO ocupa casilla** en el Portal (`DocumentUploader::remainingSlots`) y el cliente puede eliminarlo (`canDelete` incluye `rejected`). Si cambias slots o borrado, conserva esto.
- **`comprobante_apartado` no usa el ✓ genérico:** manda a "Confirmar apartado" (genera el recibo). No lo "simplifiques".
- **Nada se oculta por filtrar:** todo documento cae en su sección o en "Otros documentos". Si agregas una categoría a `Document::CATEGORIES` decide en qué lista de `RentalDocumentChecklist` (o `TenantDocumentChecklist`/`SellerDocumentChecklist`) va.
- **Las listas por persona (inquilino/propietario) se separan por `documents.client_id`.** Los documentos subidos desde el CRM deben guardar `client_id` de la parte (ya lo hace `RentalDocumentController::store`).
- **"Por revisar" = subido por un usuario `role='client'` y (`status='received'` o captación `captacion_status='pendiente'`), excluyendo `DocumentReviewInbox::GENERATED`.** Si agregas una categoría que genera el sistema (PDF/recibo), añádela a `GENERATED` o aparecerá como pendiente.
- **`updateStatus` sincroniza el flujo de Captación** (`CaptacionService::approveDocument/rejectDocument` → recalcula etapa). No lo quites: aprobar desde el visor/bandeja dejaría la captación en 'pendiente'.
- **El visor (`hdvDocViewer`) toma TODAS las filas `.doc-item[data-doc-id]` de la página** y emite el evento `hdv:doc-status`; cualquier página que use `_doc_row` + `_doc_viewer` obtiene visor gratis. Las filas ocultas por filtro llevan `.doc-filtered-out`.
- **En `@section('styles')` va CSS crudo, sin `<style>`** (ver reglas de oro); `rentals/show` conserva esa deuda vieja, no la repliques.
- **Los archivos están en el disco `public`** (URL directa). Pendiente de seguridad: servirlos con permiso. No prometas privacidad de esos archivos.

## Cómo probar sin romper (checklist)
1. `php artisan view:cache` (compila TODOS los Blade — detecta errores de sintaxis) y luego `php artisan view:clear`.
2. `php artisan test --filter="DocumentUploadTest|SmokeTest"`.
3. Renderizar `rentals.show` con datos reales o sintéticos (ver el patrón de tinker en la sesión del 2026-09-25: transacción + rollback).
4. En navegador: Renta → Documentos → clic en un archivo → aprobar/rechazar sin recargar; en el Portal (celular) subir un PDF y una foto.

## Deploy
`git pull && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && /etc/init.d/php-fpm-83 restart` (ruta del proyecto en el servidor: ver `DEPLOYMENT_GUIDE.md`). Las migraciones de esta función: `add_quality_to_documents` y `seed_help_revision_documentos`.

## Pendiente / ideas aprobadas por Alejandro (2026-09-25) — aún NO construidas
1. **Aviso al cliente al rechazar** (WhatsApp/correo con el motivo y enlace a la casilla). Hoy el cliente solo lo ve entrando al Portal.
2. Aprobar en bloque; comparar lado a lado con los datos capturados; avance automático de etapa al completar requeridos.
3. Portal: vista previa "¿se lee bien?" antes de enviar, modo "escanear" (varias fotos → un PDF), ejemplos visuales sí/no, recordatorios amables, enlace mágico.
4. Validar por contenido los PDF de estados de cuenta (periodo reciente, nombre coincide).
5. Aplicar la misma limpieza por caso a la pantalla de **venta** (`operations/show`); hoy solo Renta está filtrada (la bandeja sí cubre ventas/expediente).
6. Lista de documentos del **propietario** que renta es un default — pendiente de confirmar con Alejandro.
7. Historial por documento (cuántas veces se rechazó y por qué) y métricas de calidad por categoría.
