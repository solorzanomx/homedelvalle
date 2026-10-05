# Home del Valle — Guía obligatoria para cualquier sesión de Claude Code

CRM inmobiliario + sitio público + Portal del Cliente (Laravel 13, PHP 8.3; SQLite local / MySQL producción).
Hosts: **admin.homedelvalle.mx** (CRM) · **homedelvalle.mx** (sitio) · **miportal.homedelvalle.mx** (Portal).
Dueño: Alejandro Solórzano — broker activo que opera el negocio él mismo (no solo pide código: da consejo de negocio cuando describe un caso).

## 0. ANTES de tocar nada (2 minutos que evitan romper cosas)
1. Lee tu **memoria de Claude Code** (`MEMORY.md`, se carga sola): ~40 notas de decisiones, incidentes y gotchas. Abre las del área que vas a tocar. *(Está fuera del repo a propósito, ver §5.)*
2. Si el área tiene doc en **`docs/funcionalidades/`**, léelo COMPLETO — trae mapa de archivos e **INVARIANTES** (lo que no se debe romper).
3. Contexto general: `CONTEXTO_PROYECTO.md`, `IMPLEMENTATION_RULES.md`, `CRITICAL_VERSIONS.md`.
4. No asumas que "esto se ve raro, lo simplifico": casi siempre hay un incidente detrás. Busca el porqué (comentarios en código con fecha, memoria, `git log -S`).

## 1. Índice de funcionalidades documentadas
| Área | Doc | Tocar con cuidado si… |
|---|---|---|
| Documentos: subida guiada + asistente, calidad, visor, bandeja "Docs por revisar", avisos/recordatorios, métricas (Rentas y Ventas), `uploaded_by` nunca en cascada hacia `users` | `docs/funcionalidades/documentos-y-revision.md` | tocas uploads del Portal, `rentals/show` u `operations/show` (Documentos), `Document`, `capture=`, estados de documentos, borrado de usuarios/portal |
| Seguridad de archivos y accesos: almacenamiento privado, autorización por pertenencia, rutas del CRM solo personal | `docs/funcionalidades/seguridad-archivos.md` | tocas subidas/descargas, contratos, o agregas rutas con `auth` |
| Portal del inquilino: "Mi camino", menú corto, barra inferior móvil, "Mis documentos" con estados | `docs/funcionalidades/portal-inquilino-navegacion.md` | tocas `layouts/portal`, `TenantRoadmap`, `TenantDocumentRows`, `portal/journey`, `portal/expediente` |
| Obligado solidario (póliza sin aval): sin cuenta propia, lo captura el inquilino desde su Portal (`?para=obligado`) | `docs/funcionalidades/obligado-solidario.md` | tocas `ObligadoSolidarioService`, `?para=obligado`, `DocumentUploader`, `RentalExpedienteStatus` |
| Co-arrendatario (contrato a nombre de dos personas, ej. una pareja): mismo cuestionario completo del titular, sin cuenta propia, `?para=co_tenant` | `docs/funcionalidades/co-arrendatario.md` | tocas `CoTenantService`, `?para=co_tenant`, `$secondaryRole` en `PortalExpedienteController`/`PortalDocumentController`, o cualquier código que hoy distinga `para=obligado` |
| Garantía del inquilino: póliza (Previsión Legal, 3 planes) vs. aval + $3,500, "¿qué sigue?", contrato del proveedor / con un clic | `docs/funcionalidades/garantia-y-poliza-inquilino.md` | tocas `TenantRoadmap`, planes de póliza, `guarantee_type`, pasos del inquilino en el Portal |
| Blog: salud de URLs — redirects 301/410, fallback difuso a slugs parecidos, 404 útil | `docs/funcionalidades/blog-redirects.md` | tocas `BlogUrlHealth`, `BlogController@show`, `blog_redirects`, o cambias el slug de un post |
| Blog: eventos GA4 con contexto (post_slug/post_cluster/cta_variant), cta_view | `docs/funcionalidades/blog-ga4-tracking.md` | tocas `hdvTrack`, `BlogCluster`, o el bloque de tracking de `layouts/public.blade.php` |
| Blog: CTAs por cluster (form corto sin email, WhatsApp con ref, sticky móvil) | `docs/funcionalidades/blog-cta-clusters.md` | tocas `BlogCluster`, `BlogCtaConfig`, `CtaCapture`, o los `blog/_cta-*.blade.php` |
| Blog: calculadora de costo de sucesión (cifras validadas por Alejandro con notario, activa en 4 posts) | `docs/funcionalidades/blog-calculadora-sucesion.md` | tocas `SuccessionCalculatorConfig`, `SuccessionCalculator`, o `/admin/succession-calculator` |
| Blog: contenido (respuesta corta, titles/metas, FAQPage, enlazado interno de herencias) | `docs/funcionalidades/blog-contenido-fase5.md` | tocas `BlogBodyEnhancer::injectBeforeFirstHeading`, o el contenido de los posts de herencias |
| Blog: fusión de artículos (6 grupos, ACTIVADA — pilares en vivo con el body fusionado, 15 redirects activos) | `docs/funcionalidades/blog-fusion-fase6.md` | tocas `database/seeders/blog-posts/fusion-*.html`, un pilar de los 6 grupos, o los `blog_redirects` de esta fase |
| Blog: panel "Blog → Leads" (leads por post/cluster/CTA, hits de redirects, 404 más frecuentes) | `docs/funcionalidades/blog-leads-panel.md` | tocas `BlogLeadsController`, `BlogNotFoundHit`, o `/admin/blog-leads` |
| Agendar visitas: 4 pantallas (cliente, propiedad, captación, lead sin convertir), confirmación por correo, fecha nunca pasada (`after_or_equal:today`) | `docs/funcionalidades/agendar-visitas.md` | tocas `scheduleVisit`/`storeInteraction`, `VisitSchedulingService`, o `CitaMail`/`RecordatorioCitaMail` |
| Convertir lead a cliente: adopta leads/visitas duplicadas del mismo contacto, detecta la propiedad de interés, timeline del cliente muestra su historial de lead | `docs/funcionalidades/conversion-leads-a-cliente.md` | tocas `convertToClient` (en `FormSubmissionsTable` o `Admin\FormSubmissionController`), `LeadConversionService`, o el timeline de `clients/show` |
| Contacto de leads trackeado (WhatsApp marca "contactado") + checkbox de reservar la propiedad al crear un trato de renta | `docs/funcionalidades/contacto-leads-y-reserva-propiedad.md` | agregas un link de WhatsApp nuevo en la ficha de un lead, tocas `LeadWhatsAppMessage`, o `RentalProcessController::store()` |
| Propiedad de interés: selector en crear/editar cliente + Deal automático al convertir un lead con propiedad detectada (alimenta la pestaña "Propiedades") | `docs/funcionalidades/propiedad-de-interes.md` | tocas `clients/create`\|`edit`, `ClientController::store/update`, o `LeadConversionService` |
| Propuesta de Servicios (PDF): logo de fondo oscuro correcto, la versión de renta cabe en 2 páginas (no 3) | `docs/funcionalidades/propuesta-servicios-pdf.md` | tocas `ServiciosGeneratorService` o `pdf/servicios.blade.php` |
| Blog: optimizaciones post-lanzamiento + auditoría de conversión (Pixel de Meta, calculadora en 1 paso, correo opcional, recordatorios, fix del cta-capture final apagado en 58% del tráfico, `{{CTA1/2/3}}` legacy desactivados en todo el blog, colonia obligatoria + filtro de teléfonos falsos) | `docs/funcionalidades/blog-optimizaciones-post-lanzamiento.md` | tocas `SuccessionCalculator`, `CtaCapture`, `blog:remind-stale-leads`, el bloque de CTA final de `blog/show.blade.php`, `BlogAIService`, `BenitoJuarezColonias`/`RealisticMexicanPhone`, o agregas una clase de Tailwind nueva (correr `npm run build`) |
| Nombre legal en documentos legales (Recibo de Apartado, Oferta de Compra, Adéndum de Comisión): usa first_name/last_name_* cuando están completos, no el nombre informal | `docs/funcionalidades/nombre-legal-en-documentos.md` | tocas `PurchaseOfferGeneratorService::buyerInfo()` o agregas un generador de documento legal nuevo |
| Recibo de Apartado: regenerar sin recapturar el formulario, rastro de envíos en `Message`, candado antes de enviar y banner si los datos cambiaron después de generarlo | `docs/funcionalidades/recibo-apartado-regenerar-y-candado-envio.md` | tocas la tarjeta "Apartado" de `rentals/show.blade.php`, `storeApartado`, `sendApartadoReceipt` o `regenerateApartadoReceipt` |
| income_proof_type se sincroniza solo al subir el comprobante de ingresos (nómina/edo. cuenta/CFDI) — antes dejaba "Tus datos" atorado sin avanzar el camino | `docs/funcionalidades/sincronizar-tipo-comprobante-ingresos.md` | tocas `Livewire/Portal/DocumentUploader::upload()`, `Client::income_proof_type`, o el % de "Tus datos" |
| Cierre automático de rentas tras la Entrega (sin administración) + panel de "renta activa" (con administración): próximo pago, vigencia, reportar incidencia | `docs/funcionalidades/cierre-automatico-y-administracion-renta.md` | tocas `management_contracted`, `rentals:close-after-entrega`, o la condicional de `portal/journey.blade.php` |
| Todo lo demás | memoria de Claude Code (`MEMORY.md`) | — |

> **Al terminar una función nueva, agrega su fila aquí** (ver §4).

## 2. Reglas de oro (aprendidas con incidentes reales)
- **NUNCA rehabilitar `/register`** ni abrir roles/middleware de auth sin leer `project_homedelvalle_seguridad.md` (por un incidente previo).
- **Deploy manual** (Claude no tiene SSH): commit + push en local, y entregar a Alejandro el comando para el servidor (§3). Un `git pull` solo NO refleja vistas Blade: hay que limpiar caché de vistas **y reiniciar php-fpm** (OPcache).
- **Comisión de venta: siempre 5%** (no 6%). Ver nota de comisiones antes de tocar documentos/propuestas.
- **Documentos legales (adéndum, contratos, acuerdos):** cambios quirúrgicos; NO restilizar sin mostrar PDF de muestra antes. Verifica el PDF real con `/Count`, no solo el HTML. Cláusulas ya guardadas en `document_clauses` ignoran el default del código.
- **Documentos de marca** (`/admin/documentos`): al tocar uno de los 5, actualiza `config/document_registry.php`.
- **Copy y sitio público:** lee `docs/posicionamiento-marca.md` + nota de modelo de negocio (constructor-primero; predios→desarrolladoras es el ingreso #1).
- **Blade:** no anidar `<style>` dentro de `@section('styles')`; un `{{token}}` literal en una vista se interpreta. Compila con `php artisan view:cache` para detectar errores.
- **CSS es un bundle estático (`npm run build`, sin npm en el servidor):** si agregas una clase de Tailwind que ningún otro archivo del repo usa todavía (sobre todo combinaciones responsivas tipo `sm:block`), `view:cache` no lo detecta — corre `npm run build` y commitea `public/build/` antes de dar la vista por buena. Bug real (2026-09-30): el botón flotante de WhatsApp quedó invisible en todo post con cluster desde que se desplegó la Fase 3 del blog, porque `sm:block` nunca se agregó al bundle.
- **Middleware con sesión** va en `$middleware->web()`, no `->append()`. **Schedules** viven en `routes/console.php` (`Kernel.php` no corre).
- **Toda `Operation` nueva pasa por `OperationObserver`** (autocorrige phase/type/stage). Lee la nota antes de crear una por un camino nuevo.
- **Propiedades públicas:** `reservada/vendida/rentada` se ven con letrero; `archived` se oculta.
- **Cada módulo/feature nuevo entrega su artículo del Manual del Broker en la misma sesión:** `database/seeders/help-articles/{slug}.md` + migración que lo siembra (patrón de `2026_09_25_130000_seed_help_revision_documentos.php`; editar el .md después NO actualiza la BD, requiere migración de resync).
- **SEGURIDAD de archivos:** todo archivo sensible se guarda/lee con `App\Support\SecureFiles` (disco privado), nunca en `public`; toda ruta `auth` del CRM lleva rol (`viewer`); toda ruta que sirve un archivo autoriza por pertenencia. Ver `docs/funcionalidades/seguridad-archivos.md`.
- **Ningún FK de `documents` hacia `users` lleva `cascadeOnDelete()`** (incidente real 2026-10-01: borrar el portal de un cliente le borró sus documentos). Siempre `nullOnDelete()` — un documento nunca debe depender de que el usuario que lo subió siga existiendo.
- **Agendar visita siempre valida `scheduled_at_date` con `after_or_equal:today`** (incidente real 2026-10-01: un lead que ya había visitado recibió un correo de "tu visita está agendada" para una fecha ya pasada). Ver `docs/funcionalidades/agendar-visitas.md`.
- **Altas de personas desde el Portal (obligado solidario, etc.): NUNCA `ClientPortalService::createPortalAccount`** (reutiliza al usuario con ese correo y le cambia rol y contraseña). Ver `docs/funcionalidades/obligado-solidario.md`.
- **Subidas de documentos:** jamás `capture=` en `<input type=file>`; todo punto de subida del Portal pasa por `DocumentQualityService` y lleva `data-hdv-assist`. **Aprobar/rechazar un documento SIEMPRE por `DocumentReviewService::apply()`.**

## 3. Deploy (se lo entregas a Alejandro; él lo corre en el servidor aaPanel, `/www/wwwroot/homedelvalle.mx`)
```
cd /www/wwwroot/homedelvalle.mx && git pull && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && /etc/init.d/php-fpm-83 restart
```
(Quita `migrate` si no hay migraciones; añade pasos extra —seeders, backfills— aparte, con `--dry-run` primero si tocan datos.)

## 4. Flujo de trabajo para NO perder contexto ni romper lo existente
**Antes de push, siempre:**
1. `php artisan test --filter="DocumentUploadTest|SmokeTest"` (SmokeTest compila TODOS los Blade y carga rutas; hay ~9 tests de correo ya rotos y ajenos — no los confundas con regresiones tuyas).
2. Si tocaste una vista, renderízala con datos (tinker en transacción con rollback es el patrón usado).

**Al terminar cualquier feature o cambio de comportamiento:**
1. Escribe/actualiza `docs/funcionalidades/<tema>.md`: qué resuelve, mapa de archivos, **INVARIANTES**, cómo probarlo, pendientes.
2. Añade/actualiza su fila en la tabla del §1 de este archivo.
3. Guarda la memoria personal si aplica (ver §5 sobre dónde vive y por qué).
4. Si hay una regla nueva, un test barato que la proteja (patrón: `tests/Feature/DocumentUploadTest.php`).
5. Artículo del Manual del Broker (§2). Commit + push (sin preguntar) y entrega el comando de deploy.

## 5. ⚠️ Este repositorio es PÚBLICO en GitHub
- **Nunca** subas al repo: credenciales, llaves, `.env`, host/usuario/IP del servidor, detalles de incidentes de seguridad, cuentas de intrusos, estrategia comercial interna o datos de clientes. Un intento de versionar la memoria con esos datos ya hubo que revertir (2026-09-25).
- Por eso la **memoria de Claude Code NO va en el repo**: vive en `~/.claude/projects/-Users-alejandro/memory/`. Ojo: Claude Code guarda memoria **por carpeta de lanzamiento**; para que una sesión abierta en `~/homedelvalle` vea la misma memoria, esa carpeta (`~/.claude/projects/-Users-alejandro-homedelvalle/memory`) es un enlace simbólico a la principal.
- Lo que SÍ va en el repo y es seguro: este archivo, `docs/funcionalidades/*.md` (mapa de archivos, invariantes, pendientes técnicos) y los tests.
