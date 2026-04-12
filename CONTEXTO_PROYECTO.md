# CONTEXTO DEL PROYECTO — Home del Valle CRM Inmobiliario

> Documento de referencia para continuar el desarrollo. Ultima actualizacion: 2026-04-06.

---

## 1. Resumen General

**Home del Valle** es una plataforma CRM inmobiliaria + sitio web publico construida con Laravel. Combina:

- **CRM interno:** Operaciones unificadas (venta/renta/captacion), propiedades, clientes, brokers, deals, tareas, finanzas, marketing, automatizaciones y lead scoring
- **Sitio publico:** Homepage, propiedades, nosotros, servicios, blog, contacto, landing pages, paginas legales, formularios dinamicos
- **Panel de administracion:** Sidebar moderno con CMS completo, email marketing, automatizaciones, analytics, finanzas, calendario de contenido, media library y centro de ayuda
- **Portal de clientes:** Dashboard, rentas, documentos y gestion de cuenta para propietarios/inquilinos
- **Sistema de email:** Templates editables (WYSIWYG), galeria de assets, configuracion SMTP dinamica, tracking de apertura
- **Modulo legal:** Documentos versionados con tracking de aceptacion
- **Seguridad:** Autenticacion manual con rate limiting, RBAC granular, 2FA preparado

---

## 2. Tecnologias

| Componente        | Tecnologia                                |
|-------------------|-------------------------------------------|
| Backend           | Laravel 11.x                              |
| PHP               | ^8.2                                      |
| Base de datos     | SQLite (local) / MySQL (produccion)       |
| Frontend CRM      | Blade + CSS puro (variables CSS)          |
| Frontend Publico  | Blade + Tailwind CSS 4.0 + Typography     |
| JS Framework      | Alpine.js (CDN, sitio publico)            |
| Iconos            | Lucide (lucide-static)                    |
| Build             | Vite 8.0                                  |
| Fuente            | Inter (panel + publico)                   |
| Autenticacion     | Manual (Auth::attempt, sin Breeze)        |
| Email             | PHPMailer 6.x (SMTP dinamico desde DB)    |
| Editor WYSIWYG    | TinyMCE 8.3.2 (self-hosted GPL)          |
| PDF               | DomPDF 3.x (contratos y documentos)       |
| Imagenes          | Intervention Image 3.x (optimizacion)     |
| Almacenamiento    | Laravel Storage disk `public`             |
| Hosting           | cPanel compartido (produccion)            |
| Timezone          | America/Mexico_City                       |
| Locale            | es (Carbon, app, faker: es_MX)            |
| Servidor dev      | `php artisan serve` (127.0.0.1:8000)      |

---

## 3. Arquitectura

```
Patron: MVC (Model-View-Controller)

Layouts:
  layouts/app-sidebar.blade.php    # CRM: sidebar fijo + topbar + content (CSS puro)
  layouts/public.blade.php         # Sitio publico: Tailwind + Alpine.js + componentes
  layouts/landing.blade.php        # Landing pages de captacion de leads
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
2. Post-login redirige a `admin.dashboard` (panel con sidebar) para usuarios internos
3. Usuarios con role `client` son redirigidos al `portal.dashboard`
4. Registro publico deshabilitado en UI (link removido del login)
5. RBAC granular: tabla `roles`, tabla `permissions`, pivot `role_user` y `permission_role`
6. Gate::before otorga acceso total a super admins
7. Middleware: `admin`, `editor`, `viewer`, `broker`, `client`, `permission:{slug}`
8. Recuperacion de contrasena via email con token seguro (30 min expiracion)

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

1. **Sin Breeze/Jetstream:** Autenticacion manual para control total.
2. **SQLite local / MySQL produccion:** SQLite para desarrollo rapido, MySQL en cPanel.
3. **Dual CSS:** CSS puro con variables en CRM (app-sidebar), Tailwind 4.0 en sitio publico.
4. **Doble sistema de brokers:** Tabla `brokers` standalone + usuarios con roles. Intencional.
5. **Middleware por alias en bootstrap/app.php:** Laravel 11 no soporta `$this->middleware()`.
6. **Login redirige a admin.dashboard:** Excepto role=client → portal.dashboard.
7. **Homepage redirige autenticados:** HomeController@index redirige a admin.dashboard si hay sesion.
8. **Navegacion desde menus DB:** Tablas `menus` + `menu_items` con items anidados.
9. **Cache almacena arrays (no Eloquent):** Para evitar `__PHP_Incomplete_Class`.
10. **PHPMailer en vez de Laravel Mail:** Control directo sobre SMTP dinamico.
11. **TinyMCE self-hosted (GPL):** En `public/vendor/tinymce/`.
12. **Tokens de reset custom:** Tabla `custom_password_resets` con `used` y `expiration_date`.
13. **Rate limiting:** login (5/min), forgot-password (3/min).
14. **Honeypot anti-spam:** Campo trampa en formulario de contacto.
15. **CMS secciones en SiteSetting:** JSON fields + headings/subheadings por seccion.
16. **Jobs sincronos:** cPanel no tiene queue worker, todos los jobs sin `ShouldQueue`.
17. **Blade @json() no soporta arrow functions:** Datos complejos se preparan en controller.
18. **Blog body como HTML crudo:** `{!! $post->body !!}` sin escapar (viene de TinyMCE).
19. **Timezone forzado:** America/Mexico_City en config + Carbon locale `es` + setlocale.
20. **Operaciones unificadas:** Venta/renta/captacion en un solo pipeline con auto-spawn.
21. **Contratos con DomPDF:** Generacion desde templates HTML con variables, conversion a PDF.
22. **Imagenes optimizadas:** Intervention Image para resize/compress.

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

### PHP (composer.json)
- `laravel/framework` ^11.0
- `laravel/tinker` ^2.9
- `phpmailer/phpmailer` ^6.9
- `dompdf/dompdf` ^3.0
- `intervention/image` ^3.0

### JavaScript (package.json)
- `tailwindcss` ^4.0.0 + `@tailwindcss/vite` + `@tailwindcss/typography`
- `tinymce` ^8.3.2 (copiado a public/vendor/tinymce/)
- `lucide-static` ^1.7.0
- `axios` ^1.11.0, `vite` ^8.0.0, `laravel-vite-plugin` ^3.0.0

### CDN
- Alpine.js (sitio publico)
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

## 14. Pendientes / Deuda Tecnica

- [ ] Modelo `BrokerPhoto` sin implementar (tabla solo tiene id + timestamps)
- [ ] Controladores vacios: `AdminController`, `BrokerPhotoController`
- [ ] Migracion duplicada: `add_role_enum_to_users` duplica `add_role_to_users_table`
- [ ] Migraciones stub vacias: `add_parking_to_properties`, `add_relationships_to_properties`, `add_broker_id_to_clients`
- [ ] Layout `admin/layout.blade.php` sin usar (reemplazado por `layouts/app-sidebar.blade.php`)
- [ ] Tabla default `password_reset_tokens` existe pero no se usa
- [ ] Directorio `CRM-VBNet/` es proyecto de referencia anterior
- [ ] Sin email verification
- [ ] 2FA por email preparado para futuro
- [ ] Preview en create/edit de posts pendiente
- [ ] Colores de status en vistas semana/dia del calendario pendientes de fix
- [ ] Overflow de titulos en vista mensual del calendario pendiente de fix
