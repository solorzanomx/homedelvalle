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

## Aviso al cliente al rechazar (2026-09-25)
| Pieza | Archivo |
|---|---|
| Lógica (correo, WhatsApp, agrupado, marcas) | `app/Services/DocumentRejectionNotifier.php` |
| Envío automático | `app/Console/Commands/NotifyDocumentRejections.php` (`documents:notify-rejections`, cada 5 min, espera 10 min desde el rechazo) |
| "Avisar ahora" / WhatsApp | `RentalDocumentController::notifyRejection` (ruta `documents.notify-rejection`) + aviso (toast) en `rentals/_doc_viewer.blade.php` |
| Columnas | `documents.rejected_at`, `rejection_notified_at`, `rejection_notified_via` (email / whatsapp / skipped / no_email / failed) |

## Bloque 2 (2026-09-25, commits `eddb614`, `b97f5fd` y el de asistente de subida)
| Pieza | Archivo |
|---|---|
| Único punto de aprobar/rechazar (avisos, captación, historial, "expediente completo") | `app/Services/DocumentReviewService.php` — TODO cambio de estado pasa por `apply()`; aprobar en bloque = `bulkApprove()` |
| Aviso "expediente del inquilino completo y aprobado" (una vez por renta, no avanza etapa solo) | `app/Support/RentalExpedienteStatus.php` |
| Recordatorios de re-subida (3 y 6 días; tras el 2.º escala al asesor) | `DocumentRejectionNotifier::remindDue()` + `documents:remind-reupload` (10:30 diario) |
| Historial por documento | tabla `document_events`, `App\Models\DocumentEvent::log()`; se ve en el visor |
| "Captado vs. documento" en el visor | `DocumentReviewInbox::comparison()` (usa `documents.ai_extracted_data`) |
| Validación por contenido de estados de cuenta y nóminas (titular, tipo, periodo ≤3 meses; imagen o PDF) | `app/Services/StatementDocumentAIExtractionService.php` (se llama en `DocumentUploader::upload`); reglas en `evaluate()` (probadas sin red) |
| Métricas | `DocumentReviewController::metrics` → `documents/metrics.blade.php` (`/revision-documentos/metricas`); bloqueos en `document_quality_blocks` |
| Pestaña Docs de Ventas filtrada por tipo | `app/Support/OperationDocumentChecklist.php` + `DocumentChecklist` (compartido con Rentas) en `operations/show` |
| Asistente de subida del Portal (vista previa "¿se lee bien?", girar, varias fotos → UN PDF, reduce fotos >2000 px) | `resources/views/portal/_upload_assist.blade.php` (incluido en `layouts/portal.blade.php`; se activa con `data-hdv-assist` en los `<input type=file>`). PDF armado en JS sin librerías |
| Ejemplos "así sí / así no" | `portal/_upload_examples.blade.php` (SVG en línea), dentro de `_upload_tips` y `_upload_guide_card` |

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
- **Un cliente = un correo de rechazo** (agrupa todos sus rechazados sin avisar). Nunca mandes un correo por documento. El "debounce" es el scheduler (no hay queue worker en producción).
- **WhatsApp es un enlace `wa.me`** que abre el chat con el texto listo; `WhatsAppService` NO envía de verdad (no hay proveedor conectado). Se abre la pestaña dentro del clic antes del `fetch` para evitar el bloqueo de pop-ups.
- **Rechazar reinicia el aviso** (`rejected_at=now`, notificación en null); aprobar lo limpia. Si cambias `updateStatus`, conserva ese reinicio o el cliente recibirá avisos de rechazos viejos.
- **Los estados `skipped/no_email/failed` siguen siendo reenviables a mano** (`pendingFor`), pero el scheduler solo toma los de `rejection_notified_at` nulo (no reintenta infinito).
- **Nunca cambies `documents.status` directamente para aprobar/rechazar: usa `DocumentReviewService::apply()`** (si no, se pierden reinicio de avisos, flujo de Captación, historial y aviso de expediente completo).
- **El asistente de subida solo intercepta cambios REALES del usuario (`e.isTrusted`) sobre inputs con `data-hdv-assist`.** La cámara guiada de INE dispara eventos sintéticos y debe pasar directo; el reenvío propio va marcado `__hdv`. Los PDF/doc pasan sin asistente. Si una imagen no decodifica (HEIC) se envía tal cual. Los inputs nuevos de subida deben llevar `data-hdv-assist`.
- **El PDF del asistente es de imágenes:** el servidor lo acepta porque empieza con `%PDF` y no trae `/Encrypt`; la revisión de calidad con IA lo lee como documento.
- **Aprobar en bloque nunca aprueba `comprobante_apartado` de una renta sin apartado confirmado** (se omite y se avisa); rechazar siempre es individual y con motivo.
- **La bandeja y las métricas cuentan solo lo subido por `role='client'`** y excluyen `DocumentReviewInbox::GENERATED`.
- **Los archivos están en el disco `public`** (URL directa). Pendiente de seguridad: servirlos con permiso. No prometas privacidad de esos archivos.

## Cómo probar sin romper (checklist)
1. `php artisan view:cache` (compila TODOS los Blade — detecta errores de sintaxis) y luego `php artisan view:clear`.
2. `php artisan test --filter="DocumentUploadTest|SmokeTest"`.
3. Renderizar `rentals.show` con datos reales o sintéticos (ver el patrón de tinker en la sesión del 2026-09-25: transacción + rollback).
4. En navegador: Renta → Documentos → clic en un archivo → aprobar/rechazar sin recargar; en el Portal (celular) subir un PDF y una foto.

## Deploy
`git pull && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && /etc/init.d/php-fpm-83 restart` (ruta del proyecto en el servidor: ver `DEPLOYMENT_GUIDE.md`). Las migraciones de esta función: `add_quality_to_documents` y `seed_help_revision_documentos`.

## Pendiente / ideas (no construidas)
1. **Enlace mágico** (entrar al Portal sin contraseña por WhatsApp): decisión de seguridad, se dejó fuera a propósito — tocar autenticación exige revisar `project_homedelvalle_seguridad` y añadir expiración/un solo uso.
2. WhatsApp real (Twilio/Meta): hoy todo es enlace `wa.me`.
3. Lista de documentos del **propietario** que renta es un default — pendiente de confirmar con Alejandro.
4. Validar por contenido otros documentos (predial, escritura, constancia fiscal) con el mismo patrón de `StatementDocumentAIExtractionService`.
5. Servir los archivos con permiso (hoy en disco `public`, URL directa).
6. Afinar umbrales de calidad con la página de métricas después de unas semanas de uso real.
7. Los formularios simples del expediente (no Livewire) no pasan por la validación de contenido de estados de cuenta, solo por la de calidad.
