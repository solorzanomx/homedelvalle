# CONTEXTO DEL PROYECTO — Home del Valle CRM Inmobiliario

> Documento de referencia para continuar el desarrollo. Última actualización: **2026-04-29**.
>
> **Documentos hermanos:** `IMPLEMENTATION_RULES.md`, `CRITICAL_VERSIONS.md`, `DEPLOYMENT_GUIDE.md`, `docs/02-MANUAL-IMPLEMENTACION-SITIO.md`, `docs/04-ROADMAP-Y-ARQUITECTURA.md`, `docs/05-PROCESO-DE-RENTA.md`, `docs/06-PORTAL-DEL-CLIENTE.md`, `.claude/SCHEMA_QUICK_REFERENCE.md`, `.claude/DATABASE_SCHEMA.md`.

---

## 1. Resumen General

**Home del Valle** es una plataforma boutique de bienes raíces que combina **CRM operacional + sitio web público + Portal del Cliente**, construida con Laravel 13.6. Todo está pensado bajo el slogan "Pocos inmuebles. Más control. Mejores resultados.".

- **CRM interno** (`homedelvalle.mx/admin`): Operaciones unificadas (venta/renta/captacion) + funnel de rentas separado en `/admin/rentas` (captaciones, activas, gestión post-cierre), propiedades, clientes, brokers, deals, tareas, finanzas, marketing, automatizaciones y lead scoring. UI custom con sidebar oscuro, Blade + CSS puro con variables CSS (NO Tailwind, NO Filament como admin primario).
- **Sitio público** (`homedelvalle.mx`): Homepage con selector de intención, 4 funnels (vendedor / comprador / renta / desarrollo e inversión), propiedades, observatorio de precios `/mercado`, nosotros, servicios, blog, contacto, landing pages, páginas legales, formularios dinámicos. Blade + Tailwind 4 + Alpine.js. SEO-first.
- **Portal del Cliente** (`miportal.homedelvalle.mx` — subdominio dedicado): pieza diferenciadora del producto. Cada cliente que firma con HDV recibe cuenta automática y accede a su operación, documentos, pagos, mensajes con el equipo y reportes en cualquier momento. Blade + Tailwind 4 + **Livewire 4** para componentes reactivos autenticados. 4 perfiles: propietario, inquilino, comprador, vendedor. Spec: `docs/06-PORTAL-DEL-CLIENTE.md`.
- **Sistema de email:** Templates editables (WYSIWYG), galería de assets, configuración SMTP dinámica desde DB, tracking de apertura. PHPMailer (no Laravel Mail).
- **Módulo legal:** Documentos versionados con tracking de aceptación, contratos generados con DomPDF + variables, integraciones Mifiel y Google Signature Requests.
- **Marketing automation:** Motor de automatizaciones multi-paso, segmentos de clientes, lead scoring con grados A/B/C/D, campañas y atribución por canal.
- **Carousel Instagram:** Sistema robusto con templates Blade, versiones, prompts de imagen IA, publicación multi-canal.
- **Seguridad:** Autenticación manual con rate limiting, RBAC granular, 2FA por email preparado, audit logs en `portal_audit_logs`.

---

## 2. Tecnologías

| Componente        | Tecnología                                                                                  |
|-------------------|---------------------------------------------------------------------------------------------|
| Backend           | **Laravel 13.6.0** (require ^13.0)                                                          |
| PHP               | **8.3.30** (require ^8.2)                                                                   |
| Base de datos     | SQLite (local) / MySQL (producción, base `sql_homedelvalle_mx`)                             |
| Frontend CRM admin| Blade + CSS puro (variables CSS) — sidebar custom en `layouts/app-sidebar.blade.php`        |
| Frontend Público  | Blade + Tailwind CSS 4.2.2 + Typography + Alpine.js                                         |
| Frontend Portal   | **Blade + Tailwind 4.2.2 + Livewire 4.2.4** — subdominio `miportal.homedelvalle.mx`         |
| JS Framework      | Alpine.js (CDN, sitio público) · Livewire 4.2.4 (portal del cliente)                        |
| Filament          | 5.6.1 instalado pero **NO** es admin primario. Uso puntual con aprobación de Alex.          |
| Iconos            | Lucide-static SVG inline                                                                    |
| Build             | Vite 8.0.3 + laravel-vite-plugin ^3.0                                                       |
| Fuente            | Inter (sitio público + CRM + portal)                                                        |
| Autenticación     | Manual (`Auth::attempt`, sin Breeze ni Jetstream). 2FA por email preparado.                 |
| Email             | PHPMailer 6.9 (SMTP dinámico desde tabla `email_settings`, password encriptado)             |
| Editor WYSIWYG    | TinyMCE 8.3.2 (self-hosted GPL en `public/vendor/tinymce/`)                                 |
| PDF               | DomPDF 3.1.5 (contratos, recibos, reportes mensuales, fichas técnicas)                      |
| Screenshots/PDFs  | Spatie Browsershot 5.2.3 + Puppeteer 24.42                                                  |
| Imágenes          | Intervention Image 3.11.7 (resize, compress, optimización)                                  |
| Media Library     | Spatie Laravel Media Library 11.21.2 (uploads de propiedades, briefs, documentos portal)    |
| QR codes          | endroid/qr-code 6.0.9 (regenerable, persistente)                                            |
| Almacenamiento    | Laravel Storage disk `public` + Spatie Media Library                                        |
| Hosting           | cPanel compartido (sin queue worker — jobs corren síncronos vía `schedule:run`)             |
| Timezone          | America/Mexico_City                                                                         |
| Locale            | es (Carbon, app, faker: es_MX)                                                              |
| Servidor dev      | `php artisan serve` (127.0.0.1:8000)                                                        |
| Subdominios prod  | `homedelvalle.mx` (sitio + CRM admin) · `miportal.homedelvalle.mx` (Portal del Cliente)     |

---

## 3. Arquitectura

```
Patron: MVC (Model-View-Controller)

Layouts:
  layouts/app-sidebar.blade.php    # CRM admin: sidebar fijo + topbar + content (CSS puro)
  layouts/public.blade.php         # Sitio publico: Tailwind 4 + Alpine.js + componentes
  layouts/landing.blade.php        # Landing pages de captacion de leads
  layouts/portal.blade.php         # Portal del Cliente: Tailwind 4 + Livewire 4 (miportal.*)
  layouts/portal-empty.blade.php   # Login y vistas sin sesion del portal
  layouts/app.blade.php            # Layout basico (legacy, sin sidebar)

Componentes publicos (resources/views/components/public/):
  navbar.blade.php                 # Navegacion dinamica desde menus DB
  footer.blade.php                 # Footer con info de contacto desde SiteSetting
  hero.blade.php                   # Hero section con buscador
  property-card.blade.php          # Card de propiedad reutilizable
  contact-form.blade.php           # Formulario de contacto con honeypot anti-spam
  whatsapp-button.blade.php        # Boton flotante WhatsApp + burbuja despues de 20s
  lead-popup.blade.php             # Chatbot calificador de leads (Alpine.js)
  seo-meta.blade.php               # Meta tags SEO dinamicos
  json-ld.blade.php                # Schema.org structured data
  breadcrumbs.blade.php            # Breadcrumbs SEO

Secciones publicas (components/public/sections/):
  8 componentes para secciones del homepage

CSS: Variables CSS en :root con colores dinamicos desde base de datos (SiteSetting)
Email: PHPMailer con configuracion SMTP desde tabla email_settings (password encriptado)
Templates email: Variables {{Nombre}}, {{Email}}, etc. reemplazadas al enviar
Cache: Base de datos (tabla `cache`), modelos almacenan arrays (no objetos)
Scheduler: Cron de cPanel ejecuta `artisan schedule:run` cada minuto
Jobs: Todos corren sincronamente (NO ShouldQueue — cPanel no tiene queue worker)
```

### Flujo de autenticacion
1. Login manual con `Auth::attempt()` en `LoginController` (rate limited: 5/min)
2. Post-login en `homedelvalle.mx/login` redirige a `admin.dashboard` (panel con sidebar) para usuarios internos
3. Usuarios con role `client` que entran a `homedelvalle.mx` son redirigidos al portal en `miportal.homedelvalle.mx` (subdominio dedicado)
4. Login del portal vive en `miportal.homedelvalle.mx/login` (Auth con scope `client`)
5. Registro público deshabilitado en UI; cuentas de portal se crean automáticamente al firmar primer hito (captación, contrato de arrendamiento, oferta presentada en venta) — ver `docs/06-PORTAL-DEL-CLIENTE.md` sección 5
6. RBAC granular: tabla `roles`, tabla `permissions`, pivot `role_user` y `permission_role`
7. Gate::before otorga acceso total a super admins
8. Middleware: `admin`, `editor`, `viewer`, `broker`, `client`, `permission:{slug}`, `portal.subdomain`
9. Recuperación de contraseña vía email con token seguro (30 min expiración) — disponible en sitio principal y en portal
10. SESSION_DOMAIN=`.homedelvalle.mx` para cookies cross-subdomain entre sitio y portal

### Flujo publico (visitantes no autenticados)
1. `/` muestra homepage publica con hero, segmentacion por perfil, propiedades destacadas, blog, etc.
2. Usuarios autenticados en `/` son redirigidos a `admin.dashboard`
3. Navegacion del sitio publico es dinamica desde tablas `menus` + `menu_items`
4. Formularios de contacto crean `ContactSubmission` con tracking UTM
5. Chatbot calificador aparece a los 25s, captura leads en `newsletter_subscribers`
6. WhatsApp flotante con burbuja a los 20s (numero configurable desde admin)
7. Integraciones de tracking: GTM, GA4, Facebook Pixel (configurables desde admin)

---

## 4. Estructura de Carpetas (resumen)

```
app/
  Http/
    Controllers/           # 25 controladores principales
    Controllers/Admin/     # 34 controladores de administracion
    Controllers/Auth/      # 4 controladores de autenticacion
    Controllers/Portal/    # 3 controladores del portal de clientes
    Middleware/             # 6 middleware (Admin, Editor, Viewer, Broker, Client, Permission)
  Models/                  # 69 modelos
  Policies/                # 1 politica (ClientPolicy)
  Providers/
    AppServiceProvider.php # Rate limiters, View::composer, Gates, Carbon locale
  Services/                # 11 servicios
    EasyBrokerService.php
    EmailService.php
    ContractService.php
    OperationChecklistService.php
    ClientPortalService.php
    LeadScoringService.php
    SegmentService.php
    AutomationEngine.php
    AutomationService.php
    WhatsAppService.php
    ImageOptimizer.php
  Jobs/                    # 4 jobs (todos sincronos, sin ShouldQueue)
    PublishScheduledPosts.php       # Auto-publica posts programados
    EvaluateSegments.php            # Evalua segmentos de clientes
    ProcessAutomationEnrollments.php # Procesa automatizaciones activas
    RecalculateLeadScores.php       # Recalcula lead scoring diario

resources/views/           # ~172 archivos blade
  layouts/                 # 5 layouts
  components/public/       # 11 componentes + 8 secciones
  admin/                   # 65+ vistas de administracion
  public/                  # 15+ vistas publicas
  portal/                  # 5 vistas del portal
  auth/                    # 4 vistas de autenticacion
  blog/                    # 4 vistas de blog
  operations/              # 4 vistas de operaciones
  rentals/                 # 4 vistas de rentas
  brokers/                 # 4 vistas de brokers
  broker-companies/        # 3 vistas de empresas broker
  referrers/               # 4 vistas de comisionistas
  clients/                 # 6 vistas de clientes
  properties/              # 5 vistas de propiedades
  deals/                   # 3 vistas de deals
  tasks/                   # 3 vistas de tareas

routes/
  web.php                  # ~230 declaraciones de rutas
  console.php              # Scheduler: 4 jobs programados

database/
  migrations/              # 112 archivos de migracion
  seeders/                 # 8 seeders

public/
  vendor/tinymce/          # TinyMCE 8.3.2 self-hosted GPL
```

---

## 5. Modelos Principales (69 total)

### Nucleo del CRM
| Modelo | Descripcion | Relaciones clave |
|--------|-------------|------------------|
| User | Usuarios con roles RBAC, avatar, perfil web | hasMany(Operation, Client, Notification), belongsToMany(Role) |
| Property | Propiedades inmobiliarias | belongsTo(Broker, Client as owner), hasMany(PropertyPhoto, Deal, Operation) |
| Client | Clientes/leads con scoring | belongsTo(Broker, User), hasMany(Deal, Operation, LeadEvent), hasOne(LeadScore), belongsToMany(Segment) |
| Broker | Brokers independientes | belongsTo(BrokerCompany), hasMany(Client, Property, Deal, Operation, Commission) |
| BrokerCompany | Empresas de brokers | hasMany(Broker) |
| Deal | Pipeline de ventas | belongsTo(Property, Client, Broker), hasMany(Commission, Task) |
| Operation | Pipeline unificado (venta/renta/captacion) | belongsTo(Property, Client, Broker, User), hasMany(Task, Document, Contract, Commission, OperationComment) |
| Task | Tareas vinculadas | belongsTo(User, Deal, Operation, RentalProcess, Client, Property) |
| Interaction | Interacciones con clientes | belongsTo(Client, Property, User) |

### Rentas y Procesos
| Modelo | Descripcion |
|--------|-------------|
| RentalProcess | Pipeline de rentas con stages |
| Document | Documentos de operaciones/rentas |
| Contract | Contratos generados o subidos |
| ContractTemplate | Templates de contratos con variables |
| PolizaJuridica | Polizas juridicas de garantia |
| PolizaEvent | Eventos/timeline de polizas |
| OperationStageLog | Historial de cambios de fase |
| OperationChecklistItem | Items de checklist por etapa |
| StageChecklistTemplate | Templates de checklist configurables |
| OperationComment | Comentarios con @mentions |
| RentalStageLog | Historial de cambios en rentas |

### Comisionistas y Referidos
| Modelo | Descripcion |
|--------|-------------|
| Referrer | Comisionistas (portero, vecino, broker hipotecario, etc.) |
| Referral | Referidos vinculados a operaciones con comision |

### Blog y CMS
| Modelo | Descripcion |
|--------|-------------|
| Post | Posts con status: draft, scheduled, published, archived |
| PostCategory | Categorias de posts |
| Tag | Tags (many-to-many) |
| Page | Paginas CMS con navegacion + landing pages |
| Media | Biblioteca de medios centralizada |
| Menu / MenuItem | Menus dinamicos con items anidados |
| Form / FormSubmission | Formularios dinamicos con builder |

### Marketing y Automatizacion
| Modelo | Descripcion |
|--------|-------------|
| MarketingChannel / MarketingCampaign | Canales y campanas de marketing |
| Segment | Segmentos de clientes basados en reglas |
| Automation / AutomationStep / AutomationEnrollment | Motor de automatizacion multi-paso |
| AutomationRule / AutomationLog | Reglas de automatizacion simples |
| LeadScore / LeadScoreRule / LeadEvent | Sistema de lead scoring |
| Message | Mensajes enviados por automatizaciones |
| NewsletterSubscriber | Suscriptores capturados por chatbot |

### Legal y Ayuda
| Modelo | Descripcion |
|--------|-------------|
| LegalDocument / LegalDocumentVersion / LegalAcceptance | Documentos legales versionados |
| HelpCategory / HelpArticle / HelpTip / HelpOnboardingProgress | Centro de ayuda |

### Configuracion y Sistema
| Modelo | Descripcion |
|--------|-------------|
| SiteSetting | Configuracion global (60+ campos), integraciones, CMS homepage |
| EmailSetting / EmailTemplate / EmailAsset | Sistema de email |
| UserMailSetting | Configuracion SMTP por usuario |
| ClientEmail | Emails enviados a clientes con tracking |
| ContactSubmission | Leads de formularios con UTM |
| Notification | Notificaciones in-app |
| EasyBrokerSetting | Integracion EasyBroker |
| PasswordResetToken | Tokens de reset custom |
| Role / Permission | RBAC granular |
| Transaction / Commission / ExpenseCategory | Finanzas |

---

## 6. Rutas (~230 declaraciones)

### Publicas
| Grupo | Rutas principales |
|-------|-------------------|
| Homepage | GET / (redirect a admin si autenticado) |
| Propiedades | GET /propiedades, /propiedades/{id}/{slug?} |
| Paginas | GET /nosotros, /servicios, /contacto, POST /contacto |
| Blog | GET /blog, /blog/{slug} |
| Landing | GET /vende-tu-propiedad |
| Legal | GET /legal/{slug} |
| Formularios | GET/POST /form/{slug} |
| Newsletter | POST /newsletter/subscribe |
| Email tracking | GET /track/{trackingId}.gif |
| Auth | GET/POST /login, /forgot-password, /reset-password |

### Autenticadas (middleware: auth)
| Grupo | Rutas principales |
|-------|-------------------|
| Propiedades | Resource CRUD + publicar EasyBroker + fotos |
| Clientes | Resource CRUD + portal management (crear/toggle/reset/eliminar) |
| Brokers | Resource CRUD |
| Empresas Broker | Resource CRUD |
| Comisionistas | Resource CRUD + referidos + cambio status + pago |
| Deals | Resource CRUD + cambio de stage |
| Operaciones | Resource CRUD + checklist + documentos + poliza + contratos + comentarios |
| Rentas | Resource CRUD + documentos + poliza + contratos |
| Tareas | Resource CRUD + toggle completado |
| Perfil | GET/POST /profile + avatar + password |
| Notificaciones | GET/PATCH notificaciones + mark all read |
| Centro de ayuda | Categorias, articulos, tips, onboarding |

### Admin (middleware: auth + viewer, prefix: /admin)
| Grupo | Rutas principales |
|-------|-------------------|
| Dashboard | GET /admin |
| Analytics | GET /admin/analytics |
| Usuarios | Resource CRUD + permisos + avatar + cambio rol |
| Configuracion | GET/POST settings (solo admin) |
| CMS Homepage | GET/POST homepage + editores servicios/nosotros/vender |
| Email | Settings + templates (CRUD + preview + test) + assets |
| EasyBroker | Settings + test conexion |
| Integraciones | GTM, GA4, Facebook Pixel, scripts custom |
| Automatizaciones | Rules CRUD + logs + toggle + Engine CRUD + enrollments |
| Templates contratos | CRUD + preview |
| Templates checklist | CRUD por etapa/tipo |
| Finanzas | Dashboard + transacciones CRUD + comisiones (aprobar/pagar) |
| Blog CMS | Posts CRUD + categorias + tags + calendario de contenido |
| Paginas CMS | Resource CRUD |
| Submissions | Contacto + formularios |
| Media Library | Upload multiple + browse API + editar + eliminar |
| Menus | CRUD menus + items |
| Footer | Editor de footer |
| Form Builder | CRUD formularios dinamicos |
| Marketing | Dashboard + canales + campanas + segmentos + lead scoring |
| Centro de ayuda | Admin CRUD articulos + tips |
| Legal | CRUD documentos + versiones + aceptaciones |
| Brokers Mgmt | Aprobar/revocar brokers |

### Portal de Clientes (middleware: auth + client, prefix: /portal)
| Ruta | Descripcion |
|------|-------------|
| GET /portal | Dashboard con rentas y documentos |
| GET /portal/rentals | Lista de rentas del cliente |
| GET /portal/rentals/{id} | Detalle de renta |
| GET /portal/documents | Lista de documentos |
| POST /portal/documents | Subir documento |
| GET /portal/documents/{id}/download | Descargar documento |
| GET /portal/account | Datos de cuenta |
| POST /portal/password | Cambiar contrasena |

---

## 7. Funcionalidades Implementadas

### 7.1 Sitio Publico
- **Layout publico** con Tailwind CSS 4.0 + Typography plugin, Alpine.js, Inter font
- **Navbar dinamico** desde tablas `menus` + `menu_items` con items anidados
- **Homepage:** Hero con buscador, seccion de segmentacion por perfil (Propietario/Comprador/Desarrollador), beneficios, propiedades destacadas, servicios, testimonios, formulario contacto, blog preview
- **Propiedades:** Listado publico + detalle individual con galeria
- **Blog:** Listado + detalle con tipografia prose, posts renderizados como HTML desde TinyMCE
- **Paginas CMS:** Paginas estaticas editables, landing pages
- **Formularios dinamicos:** Builder de formularios con URLs publicas
- **SEO:** Meta tags, JSON-LD Schema.org, breadcrumbs, meta titulo/descripcion por pagina/post
- **Chatbot calificador:** Widget bottom-left que califica leads como vendedor/comprador/desarrollador, captura email
- **WhatsApp flotante:** Boton bottom-right con burbuja a los 20s, numero configurable desde admin
- **Integraciones tracking:** GTM, GA4, Facebook Pixel inyectados automaticamente
- **Paginas legales:** /legal/{slug} con documentos versionados y tracking de aceptacion
- **Footer:** Info de contacto + redes sociales + menus desde DB

### 7.2 CRM — Operaciones Unificadas
- **Pipeline unificado** para venta, renta y captacion con stages configurables
- **Kanban board** con operaciones agrupadas por etapa, filtrable por tipo y usuario
- **Checklist por etapa** desde templates configurables, auto-avance cuando items requeridos completos
- **Auto-spawn:** Captacion completada genera automaticamente operacion de venta o renta
- **Timeline:** Historial de cambios de fase, documentos, tareas y comentarios
- **Comentarios con @mentions** que disparan notificaciones
- **Documentos:** Upload, verificacion, categorias
- **Contratos:** Generacion desde templates con variables, firma digital, descarga PDF
- **Poliza juridica:** Tracking completo de polizas de garantia con eventos
- **Comisiones:** Auto-calculo de comisiones de referidos en cierre

### 7.3 CRM — Rentas
- **Pipeline de rentas** con stages independientes
- **Datos financieros:** Renta mensual, deposito, comision, garantia, duracion
- **Poliza juridica y contratos** integrados
- **Documentos y tareas** asociados

### 7.4 Comisionistas / Referidos
- **CRUD de comisionistas** con tipos: portero, vecino, broker hipotecario, cliente pasado, comisionista, otro
- **Referidos vinculados** a operaciones con comision auto-calculada
- **Workflow de pago:** registrado → en_proceso → por_pagar → pagado

### 7.5 Portal de Clientes
- **Dashboard** con rentas y documentos del cliente
- **Vista de rentas** donde es propietario o inquilino
- **Documentos:** Listar, descargar, subir (con verificacion de acceso)
- **Gestion de cuenta:** Cambio de contrasena
- **Acceso controlado** desde admin: crear/toggle/reset/eliminar cuenta portal

### 7.6 Blog y Calendario de Contenido
- **CRUD posts** con titulo, slug, excerpt, body (TinyMCE), imagen destacada, categoria, tags, SEO
- **Status:** draft, scheduled, published, archived
- **Programacion:** Campo published_at para programar publicacion futura
- **Auto-publish:** Job sincronico `PublishScheduledPosts` ejecutado cada minuto via cron
- **Calendario de contenido:** Vistas mes/semana/dia, drag-and-drop, chips con color por status
- **Tipografia blog:** Plugin @tailwindcss/typography con estilos prose extensivos
- **Body renderizado como HTML** directo desde TinyMCE (`{!! $post->body !!}`)

### 7.7 Marketing y Automatizacion
- **Canales y campanas** con presupuesto, gasto, fechas, status
- **Segmentos de clientes** basados en reglas (campo + operador + valor), evaluacion periodica
- **Lead scoring** con reglas configurables por evento, puntos, limite diario, grados (A/B/C/D)
- **Motor de automatizacion** multi-paso: triggers, condiciones, acciones, enrollments, step logs
- **Automatizaciones simples:** Rules con trigger/conditions/action + logs

### 7.8 Modulo Legal
- **CRUD documentos legales** con tipos definidos
- **Versionamiento automatico** al editar contenido
- **Tracking de aceptacion** con email, IP, user agent, contexto
- **Vista publica** en /legal/{slug}
- **Proteccion:** No se puede eliminar documento con aceptaciones

### 7.9 Centro de Ayuda
- **Categorias y articulos** publicables, con conteo de vistas
- **Tips contextuales** (tip/warning/pro_tip) para diferentes contextos UI
- **Onboarding** con progreso por usuario (pasos completados, porcentaje)
- **Admin:** CRUD de articulos y tips

### 7.10 Finanzas
- **Dashboard financiero** con metricas e ingresos/egresos
- **Transacciones:** CRUD con categorias, vinculadas a deals/propiedades/brokers
- **Comisiones:** Lista con workflow aprobar/pagar, vinculadas a deals/operaciones

### 7.11 Sistema de Email
- **Configuracion SMTP** dinamica desde panel admin (password encriptado)
- **SMTP por usuario** (UserMailSetting) para envios personalizados
- **Templates** con editor WYSIWYG y variables ({{Nombre}}, {{Email}}, etc.)
- **Galeria de assets** con upload drag & drop
- **Emails a clientes** con tracking de apertura (pixel 1x1)
- **EmailService:** send(), sendTemplate(), sendWelcomeEmail(), testConnection()

### 7.12 Media Library
- **Upload multiple** (hasta 20 archivos, max 10MB)
- **Organizacion** por carpetas (año/mes)
- **Metadata** de imagenes (ancho, alto)
- **Browse API** para TinyMCE y otros pickers
- **Editar** alt text, titulo, carpeta

### 7.13 Integraciones
- **EasyBroker:** Publicar/despublicar propiedades, config API, test conexion
- **Google Tag Manager:** ID configurable con toggle
- **Google Analytics 4:** Measurement ID con toggle
- **Facebook Pixel:** Pixel ID con toggle
- **Scripts personalizados:** Head y body (para cualquier servicio)

### 7.14 Sistema RBAC
- **Tablas:** `roles`, `permissions`, `role_user`, `permission_role`
- **Gate::before** para super admins
- **Blade directive:** @permission('slug')
- **Middleware CheckPermission** para rutas
- **Seeder:** RolesAndPermissionsSeeder con roles y permisos predeterminados

### 7.15 Notificaciones In-App
- **Modelo Notification** con tipo, titulo, cuerpo, datos, from_user
- **Mark as read** individual y masivo
- **Triggered by** @mentions en comentarios de operaciones

### 7.16 Sidebar CRM (app-sidebar.blade.php)
- Sidebar fijo a la izquierda (260px), fondo oscuro (#1e1b4b)
- Secciones: Principal, CRM, Administracion
- Administracion solo visible segun permisos
- Card de usuario inferior con avatar, nombre, rol y logout
- Logo dinamico: imagen o texto segun site_settings
- Responsive: sidebar oculto en mobile con overlay
- Alertas con auto-dismiss 5 segundos
- Iconos Lucide integrados

### 7.17 Login
- Degradado azul-navy (#3B82C4 → #1E3A5F)
- Icono de casita junto a "Bienvenido"
- Sin enlace de registro (sitio privado)
- Subtitulo "Accede a tu cuenta"

---

## 8. Scheduler y Jobs (routes/console.php)

```php
// Marketing Automation
Schedule::job(new ProcessAutomationEnrollments)->everyMinute();
Schedule::job(new EvaluateSegments)->everyFiveMinutes();
Schedule::job(new RecalculateLeadScores)->daily();

// Blog Content
Schedule::job(new PublishScheduledPosts)->everyMinute();
```

**IMPORTANTE:** Todos los jobs son clases simples (NO implementan `ShouldQueue`) porque cPanel no tiene queue worker. El cron ejecuta `artisan schedule:run` y los jobs corren sincronamente.

Cron de cPanel:
```
* * * * * /opt/cpanel/ea-php83/root/usr/bin/php /home2/homed0b1/repositories/homedelvalle/artisan schedule:run
```

---

## 9. Decisiones Importantes

### Stack y arquitectura
1. **Sin Breeze/Jetstream:** Autenticación manual para control total.
2. **SQLite local / MySQL producción:** SQLite para desarrollo rápido, MySQL en cPanel.
3. **Tres ambientes UI con stacks distintos:** CSS puro + Blade en CRM admin · Tailwind 4 + Alpine en sitio público · Tailwind 4 + **Livewire 4** en Portal del Cliente.
4. **Filament 5.6.1 instalado pero NO es admin primario:** El CRM custom se mantiene. Cualquier Resource Filament nuevo requiere aprobación explícita.
5. **Bootstrap minimalista de Laravel 13:** middleware se registra por alias en `bootstrap/app.php` (NO con `$this->middleware()` en controlador).
6. **Doble sistema de brokers:** Tabla `brokers` standalone + usuarios con roles. Intencional.
7. **Login redirige a admin.dashboard:** Excepto role=client → redirige a `miportal.homedelvalle.mx`.
8. **Homepage redirige autenticados:** `HomeController@index` redirige a `admin.dashboard` si hay sesión interna.
9. **Navegación desde menús DB:** Tablas `menus` + `menu_items` con items anidados.
10. **Cache almacena arrays (no Eloquent):** Para evitar `__PHP_Incomplete_Class`.
11. **PHPMailer en vez de Laravel Mail:** Control directo sobre SMTP dinámico desde tabla `email_settings`.
12. **TinyMCE self-hosted (GPL):** En `public/vendor/tinymce/`.
13. **Tokens de reset custom:** Tabla `custom_password_resets` con `used` y `expiration_date`.
14. **Rate limiting:** login (5/min), forgot-password (3/min), portal login (5/min).
15. **Honeypot anti-spam:** Campo trampa obligatorio en TODO formulario público.
16. **CMS secciones en SiteSetting:** JSON fields + headings/subheadings por sección.
17. **Jobs síncronos:** cPanel no tiene queue worker, todos los jobs sin `ShouldQueue` — corren vía `schedule:run` cada minuto.
18. **Blade @json() no soporta arrow functions:** Datos complejos se preparan en controller.
19. **Blog body como HTML crudo:** `{!! $post->body !!}` sin escapar (viene de TinyMCE controlado por el equipo).
20. **Timezone forzado:** America/Mexico_City en config + Carbon locale `es` + setlocale.
21. **Operaciones unificadas:** Venta/renta/captacion en un solo modelo `Operation` con auto-spawn entre tipos.
22. **Funnel de rentas separado en admin:** Vista dedicada `/admin/rentas` con sub-vistas Captaciones / Activas / Gestión post-cierre. Pipeline distinto al genérico de venta. Spec: `docs/05-PROCESO-DE-RENTA.md`.
23. **Contratos con DomPDF:** Generación desde templates HTML con variables, conversión a PDF.
24. **Imágenes optimizadas:** Intervention Image para resize/compress.

### Portal del Cliente y experiencia post-firma
25. **Portal del Cliente en subdominio dedicado** `miportal.homedelvalle.mx`, no en sub-ruta. Razones: marca, separación visual app vs sitio, políticas de cookies/seguridad independientes.
26. **Cuenta de portal automática:** Cada cliente que firma primer hito (captación, contrato, oferta) recibe email de bienvenida con activación. NO hay activación manual ni opt-in. Servicio responsable: `ClientPortalService`.
27. **Si el cliente debería verlo, el portal lo muestra.** Documentos generados por HDV se persisten con `documents.portal_visible=true` y vinculación correcta a `client_id`. WhatsApp y email son notificación, no almacenamiento. Cero documentos sueltos en chats.
28. **Comunicación bidireccional centralizada:** Tabla `message_threads` + `message_thread_messages` para conversaciones HDV ↔ cliente. SLA respuesta de HDV: 4 horas hábiles.
29. **Privacidad estricta entre las partes:** Inquilino no ve datos personales completos del propietario y viceversa. Toda comunicación pasa por HDV (no chat directo).
30. **Vista "preview as client":** Admin con permiso `clients.preview_portal` puede impersonar al cliente desde `/admin/clients/{id}` para soporte. Banner amarillo visible, escritura deshabilitada, audit log automático en `portal_audit_logs`.

### Marca y voz
31. **Slogan oficial:** "Pocos inmuebles. Más control. Mejores resultados." Obligatorio en header, footer, OG image y firmas de email.
32. **Marca escrita siempre "Home del Valle"** (V mayúscula). Cero "Home del valle" minúscula en titles, footers ni OG.
33. **Paleta navy + neutros + verde sistema.** Cero dorado, cero cobre. Verde sólo para estados positivos (success, pills, badges, WhatsApp).
34. **Tipografía Inter** en todos los ambientes. Cargada vía fonts.bunny.net.
35. **Tono editorial:** sobrio, preciso, confiado, cuidadoso. Manual completo en `01-Manual-Marca-y-Voz.docx` (carpeta Cowork del proyecto).

---

## 10. Deploy (cPanel)

Script de deploy:
```bash
cd ~/repositories/homedelvalle && git pull && bash cpanel-deploy.sh
```

El archivo `cpanel-deploy.sh` maneja:
- Composer install (--no-dev)
- Migraciones
- Cache de config/routes/views
- Symlink de storage
- Build de assets con Vite

---

## 11. Almacenamiento de Archivos

```
storage/app/public/
  avatars/          # Fotos de perfil de usuarios
  logos/            # Logo del sitio
  brokers/          # Fotos de brokers
  clients/          # Fotos de clientes
  properties/       # Fotos de propiedades
  posts/            # Imagenes destacadas de posts
  cms-images/       # Imagenes subidas desde TinyMCE
  email-images/     # Imagenes desde editor de templates
  email-assets/     # Galeria de assets de email
  media/            # Biblioteca de medios (organizado por YYYY/MM)
  documents/        # Documentos de operaciones/rentas
  contracts/        # Contratos PDF generados
```

Symlink activo: `public/storage -> storage/app/public`

---

## 12. Dependencias

> Versiones detalladas y reglas de actualización en `CRITICAL_VERSIONS.md`.

### PHP (composer.json)
- `laravel/framework` ^13.0 (instalado 13.6.0)
- `laravel/tinker` ^3.0
- `livewire/livewire` ^4.2 (instalado 4.2.4) — usado en Portal del Cliente
- `filament/filament` ^5.6 (instalado 5.6.1) — instalado pero NO admin primario
- `filament/spatie-laravel-media-library-plugin` 5.6.1
- `phpmailer/phpmailer` ^6.9
- `dompdf/dompdf` ^3.0 (instalado 3.1.5)
- `intervention/image` ^3.0 (instalado 3.11.7)
- `spatie/laravel-medialibrary` ^11.0 (instalado 11.21.2)
- `spatie/browsershot` ^5.2 (instalado 5.2.3)
- `endroid/qr-code` ^6.0 (instalado 6.0.9)
- `google/apiclient` ^2.15

### JavaScript (package.json)
- `tailwindcss` ^4.0 (instalado 4.2.2) + `@tailwindcss/vite` + `@tailwindcss/typography`
- `tinymce` ^8.3.2 (copiado a `public/vendor/tinymce/`)
- `lucide-static` ^1.7
- `puppeteer` ^24.42
- `axios` ^1.11, `vite` ^8.0 (instalado 8.0.3), `laravel-vite-plugin` ^3.0
- `concurrently` ^9.0 (dev)

### CDN
- Alpine.js (sitio público)
- Inter font (fonts.bunny.net)

---

## 13. Seeders

| Seeder | Descripcion |
|--------|-------------|
| DatabaseSeeder | Orquestador principal |
| TestDataSeeder | Datos de prueba |
| NavPageSeeder | 6 paginas de navegacion por defecto |
| EmailTemplateSeeder | BienvenidaUsuario, RecuperarPassword, PasswordCambiado |
| MarketingChannelSeeder | Canales de marketing predeterminados |
| RolesAndPermissionsSeeder | Roles y permisos del sistema RBAC |
| MarketingAutomationSeeder | Automatizaciones de marketing predeterminadas |
| HelpCenterSeeder | Categorias y articulos del centro de ayuda |

---

## 14. Pendientes / Deuda Técnica

### Pendientes operativos
- [ ] Modelo `BrokerPhoto` sin implementar (tabla solo tiene id + timestamps).
- [ ] Controladores vacíos: `AdminController`, `BrokerPhotoController`.
- [ ] Migración duplicada: `add_role_enum_to_users` duplica `add_role_to_users_table`.
- [ ] Migraciones stub vacías: `add_parking_to_properties`, `add_relationships_to_properties`, `add_broker_id_to_clients`.
- [ ] Layout `admin/layout.blade.php` sin usar (reemplazado por `layouts/app-sidebar.blade.php`).
- [ ] Tabla default `password_reset_tokens` existe pero no se usa.
- [ ] Directorio `CRM-VBNet/` es proyecto de referencia anterior.
- [ ] Sin email verification.
- [ ] 2FA por email preparado para futuro.
- [ ] Preview en create/edit de posts pendiente.
- [ ] Colores de status en vistas semana/día del calendario pendientes de fix.
- [ ] Overflow de títulos en vista mensual del calendario pendiente de fix.

### Pendientes de Fase 3.5 — Portal del Cliente (en construcción)
Spec completa en `docs/06-PORTAL-DEL-CLIENTE.md`. Roadmap de 6 sub-fases.
- [ ] Migrar `/portal` actual a subdominio `miportal.homedelvalle.mx`.
- [ ] Layout dedicado `layouts/portal.blade.php` con Tailwind 4 + Livewire 4.
- [ ] 4 perfiles de usuario (propietario, inquilino, comprador, vendedor) con vistas dedicadas.
- [ ] Tablas nuevas: `message_threads`, `message_thread_messages`, `portal_audit_logs`.
- [ ] Listeners de creación automática de cuenta al firmar captación / arrendamiento / oferta.
- [ ] Componentes Livewire: `DocumentUploader`, `MessageComposer`, `PaymentTracker`, `IncidentReportForm`, `NotificationsBell`.
- [ ] Vista "preview as client" desde admin con audit log.
- [ ] Reportes mensuales al propietario (job programado, día 3 hábil).
- [ ] Cobranza automática con recordatorios y ejecución de garantía.

### Pendientes de funnel de rentas (en construcción)
Spec completa en `docs/05-PROCESO-DE-RENTA.md`. Vistas separadas en `/admin/rentas`.
- [ ] Vistas `/admin/rentas/captaciones`, `/admin/rentas/activas`, `/admin/rentas/gestion`.
- [ ] Sidebar admin actualizado con sección "Rentas" y sus 3 sub-items.
- [ ] Auto-spawn de operaciones entre fases (captación cerrada → operación de renta → RentalProcess).
- [ ] 5 automations: nurturing owner rental, nurturing renter, collection reminder, renewal workflow, monthly report.
- [ ] 10 templates de contrato de renta seedeados.
- [ ] Jobs programados: `CheckRentalRenewals`, `ProcessMonthlyRentalBilling`, `GenerateMonthlyOwnerReports`.

---

## 15. Documentación de referencia rápida

| Necesitas... | Mira... |
|---|---|
| Versiones de librerías y reglas de actualización | `CRITICAL_VERSIONS.md` |
| Reglas obligatorias antes de implementar | `IMPLEMENTATION_RULES.md` |
| Procedimiento de deploy | `DEPLOYMENT_GUIDE.md` |
| Stack, estructura del repo, convenciones técnicas | `docs/02-MANUAL-IMPLEMENTACION-SITIO.md` |
| Roadmap del producto, fases, decisiones | `docs/04-ROADMAP-Y-ARQUITECTURA.md` |
| Proceso completo de renta (captación, colocación, gestión) | `docs/05-PROCESO-DE-RENTA.md` |
| Spec del Portal del Cliente | `docs/06-PORTAL-DEL-CLIENTE.md` |
| Cheat sheet del esquema (108 tablas, 93 modelos) | `.claude/SCHEMA_QUICK_REFERENCE.md` |
| Esquema completo con módulos | `.claude/DATABASE_SCHEMA.md` |
| Sistema QR de propiedades | `QR_IMPLEMENTATION.md` |
| Galería premium de propiedades | `GALLERY_PREMIUM_DOCS.md` |
| Manual de marca y voz (incluye microcopy del portal) | `01-Manual-Marca-y-Voz.docx` (carpeta Cowork del proyecto) |
| Manual de operaciones CRM y leads | `03-Manual-Operaciones-CRM.docx` (carpeta Cowork del proyecto) |
