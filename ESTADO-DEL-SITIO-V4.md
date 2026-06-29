# Home del Valle — Documento Maestro del Estado del Sitio
## Versión 4 · Auditoría completa · Junio 2026

> **Propósito:** Este es el documento de referencia vivo del estado actual de la plataforma. Describe cada sección, flujo, modelo y funcionalidad implementada, y al final incluye un catálogo priorizado de mejoras. Para convenciones técnicas, ver `IMPLEMENTATION_RULES.md`. Para el esquema de DB, ver `.claude/SCHEMA_QUICK_REFERENCE.md`.

---

## 1. Stack tecnológico (versiones instaladas)

| Componente | Versión |
|---|---|
| PHP | 8.3.30 |
| Laravel | 13.6.0 |
| Livewire | 4.2.4 |
| Filament (instalado, no primario) | 5.6.1 |
| Tailwind CSS | 4.2.2 |
| Vite | 8.0.3 |
| TinyMCE (self-hosted GPL) | 8.3.2 |
| Alpine.js | CDN (sitio público) |
| Lucide-static | 1.7.0 |
| Spatie Media Library | 11.21.2 |
| Spatie Browsershot | 5.2.3 |
| DomPDF | 3.1.5 |
| Intervention Image | 3.11.7 |
| PHPMailer | 6.12.0 |
| endroid/qr-code | 6.0.9 |
| Puppeteer | 24.42.0 |

**Base de datos:** SQLite (local) · MySQL `sql_homedelvalle_mx` (producción)
**Hosting:** Ubuntu 22.04 en `/www/wwwroot/homedelvalle.mx`
**Timezone:** America/Mexico_City · Locale: es_MX

---

## 2. Arquitectura general

```
┌─────────────────────────────────────────────────────────────────────┐
│                        SITIO PÚBLICO                                │
│  homedelvalle.mx  —  Tailwind 4 + Alpine.js + Blade                 │
│                                                                     │
│  /              Home (selector 4 intenciones)                       │
│  /propiedades   Catálogo público                                    │
│  /mercado       Observatorio de precios BJ                          │
│  /blog          Blog / Guía inmobiliaria                            │
│  /vende-tu-propiedad  /comprar  /rentar  /renta-tu-propiedad        │
│  /desarrolladores-e-inversionistas  /contacto  /nosotros /servicios │
└──────────────┬──────────────────────────────────────────────────────┘
               │  leads → FormSubmission → Client → Operation
               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      CRM ADMIN CUSTOM                               │
│  homedelvalle.mx/admin  —  Blade + CSS puro + Livewire              │
│                                                                     │
│  Pipeline venta · Pipeline renta · Captaciones                      │
│  Clientes · Propiedades · Tareas · Finanzas                         │
│  Blog · Marketing · Email · Automatizaciones · Legal                │
└─────────────────────────────────────────────────────────────────────┘
```

**Layouts:**
- `layouts/public.blade.php` — sitio público (navbar, footer, Alpine.js, tracking)
- `layouts/app-sidebar.blade.php` — CRM admin (sidebar 260px oscuro, topbar)
- `layouts/landing.blade.php` — landing pages de captación (sin navbar completo)
- `layouts/portal.blade.php` — Portal del Cliente (subdominio `miportal.homedelvalle.mx`, Livewire 4)

---

## 3. Sitio Público — Secciones y estado

### 3.1 Homepage `/`
**Controller:** `HomeController@index`
**Vista:** `public/home.blade.php`

Secciones implementadas:
1. **Hero con selector de intención** — 4 tarjetas:
   - Propietario → Quiero vender → `/vende-tu-propiedad`
   - Propietario → Quiero rentar mi inmueble → `/renta-tu-propiedad`
   - Busco inmueble → Comprar → `/comprar`
   - Busco inmueble → Rentar para vivir → `/rentar`
2. **Diferenciadores** — 4 tarjetas: Dominio Territorial, Estructura Boutique, Inteligencia de Datos, Respaldo Senior
3. **Modelo de negocio** — 3 pasos: Identificar demanda → Capturar activos → Ejecutar operación
4. **Propiedades destacadas** — Grid de `Property` con `is_featured=true` (dinámico desde DB)
5. **Líneas de negocio** — 4 tarjetas con CTAs a cada funnel
6. **Estadísticas** — 4 métricas (30+ años, 200+ propiedades, 98% satisfacción, 45 días promedio)
7. **Testimonios** — 3 testimonios desde tabla `testimonials` (con fallbacks)
8. **Blog preview** — Últimos 3 posts publicados
9. **CTA final + formulario de contacto** — Livewire `contact-segmented-form`
10. **Footer con 4 columnas** — Marca · Explorar · Recursos · Contacto rápido

**Notas:** Hero copy es neutro (slogan como H1). Selector de intención arriba del fold con 4 tarjetas. Botón del navbar es "Hablemos" (neutro, no solo vendedor).

---

### 3.2 Catálogo de propiedades `/propiedades`
**Controller:** `PublicController@propiedades`
**Vista:** `public/propiedades.blade.php`

- Listado con filtros: `operation_type` (venta/renta), `property_type`, `search`, `sort`
- Cards con: foto, tipo, precio, recámaras, baños, m², colonia
- Precio muestra `/mes` para rentas
- Paginación
- Filtros como URL params

**Vista individual** `/propiedades/{id}/{slug}`:
- Galería de fotos (Spatie Media Library)
- Datos completos del inmueble
- Formulario lateral de interés (crea `Client` + `LeadEvent`)
- Para propiedades en renta: muestra precio mensual, mascotas, amueblado, garantías
- QR Code descargable (endroid/qr-code)
- Link a EasyBroker si está publicado

**Estado actual:** 1 propiedad pública. Decisión estratégica pendiente (A: 5-10 curadas · B: sin catálogo público · C: "selección curada del mes").

---

### 3.3 Landing — Vendedor `/vende-tu-propiedad`
**Controller:** `LandingController@show` (GET) · `LandingController@storeVendedor` (POST)
**Vista:** `public/vende-tu-propiedad.blade.php`

Formulario de 12 campos:
| Campo | Tipo | Validación |
|---|---|---|
| nombre | texto | required, max 120 |
| email | email | required |
| whatsapp | teléfono | required, regex MX +52 |
| tipo_propiedad | select | required: departamento, casa, terreno, oficina, comercial |
| colonia | texto | required, max 160 |
| superficie_m2 | número | optional |
| recamaras | select | optional: 1,2,3,4+,na |
| precio_esperado | select | required: rangos $4M–$14M+ |
| motivo | select | required: mudanza, sucesion, liquidez, patrimonio, otro |
| estado_doc | select | required: al_corriente, pendientes, sucesion, no_se |
| timing | select | required: inmediato, 1_3m, 3_6m, sin_prisa |
| aviso | checkbox | required: accepted |

**Honeypot:** campo `website_url` (debe estar vacío).
**Al enviar:** crea `FormSubmission` (tipo=vendedor, tag=LEAD_VENDEDOR) → redirige `/gracias` con folio `HDV-XXXX-{id}`.

---

### 3.4 Landing — Comprador `/comprar`
**Controller:** `LandingController@compra` (GET) · `LandingController@storeComprador` (POST)
**Vista:** `public/comprar.blade.php`

Formulario de 12 campos (multi-selects con Alpine.js):
- tipo_inmueble (multi-select), operacion (radio), zonas (multi-select)
- recamaras, presupuesto, pago, timing, must_have (textarea 280 chars)
- nombre, email, whatsapp, aviso

Secciones de la landing: Hero · Stats "72h / 6 colonias / 30+ años / 0 spam" · Cómo funciona (3 pasos) · Por qué buscar con nosotros (4 ventajas) · Formulario · FAQ (6 preguntas) · Banda final CTA.

**Al enviar:** crea `FormSubmission` (tipo=comprador, tag=LEAD_COMPRADOR) → `/gracias`.

---

### 3.5 Landing — Inquilino `/rentar`
**Controller:** `LandingController@rentar`
**Vista:** `public/rentar.blade.php`
**Formulario:** Livewire `forms.renter-search-form`

Campos del formulario:
- tipo_inmueble (multi-select), zonas (multi-select), recamaras, renta_mensual
- plazo_contrato, mascotas (radio), garantia, timing, must_have
- nombre, email, whatsapp, aviso

Secciones: Hero · Stats "30+ / <72h / 8 zonas / 0 letras chicas" · Cómo funciona (3 pasos) · Por qué rentar con nosotros · FAQ (6 preguntas) · CTA final.

---

### 3.6 Landing — Propietario que renta `/renta-tu-propiedad`
**Controller:** `LandingController@rentaTuPropiedad`
**Vista:** `public/renta-tu-propiedad.blade.php`
**Formulario:** Livewire `forms.rental-owner-form`

Campos del formulario (16):
- nombre, email, whatsapp, tipo_propiedad, colonia, superficie_m2, recamaras
- amueblado (radio), renta_mensual, plazo_minimo
- mascotas_acepta (radio), estado_doc, quiere_administracion (radio)
- busca_poliza (radio), timing, aviso

Secciones: Hero "Renta segura · Cupo limitado" · Stats "30+ / <30 días / 98% / 50+ administración" · Por qué rentar con nosotros · Proceso 3 pasos · Formulario · FAQ (6 preguntas) · CTA final.

---

### 3.7 Landing — B2B `/desarrolladores-e-inversionistas`
**Controller:** `LandingController@desarrolladores` (GET) · `LandingController@storeDesarrollador` (POST)
**Vista:** `public/desarrolladores.blade.php`

Formulario (13 campos):
- tipo_operacion (multi-select), uso (multi-select), m2_terreno, zonas (multi-select)
- presupuesto, horizonte, brief_file (PDF/JPG/PNG, max 10MB, Spatie Media Library)
- empresa, nombre_rol, email, telefono, nda (checkbox), aviso

Secciones: Hero B2B · Banda de credibilidad · Cómo trabajamos (4 pasos) · Líneas que atendemos · Por qué HDV para B2B · Formulario · CTA final.

**Al enviar:** crea `FormSubmission` (tipo=b2b, tag=LEAD_B2B) → `/gracias`.

---

### 3.8 Contacto `/contacto`
**Controller:** `PublicController@contacto` (GET) · `PublicController@contactoStore` (POST)

Formulario segmentado (7 campos):
1. **¿En qué te podemos ayudar?** (select) — 8 opciones que generan `lead_tag` automático:
   - Quiero vender → LEAD_VENDEDOR
   - Estoy buscando dónde comprar → LEAD_COMPRADOR
   - Estoy buscando dónde rentar → LEAD_COMPRADOR (renta)
   - Quiero rentar mi propiedad → LEAD_ARRENDADOR
   - Soy desarrollador o inversionista → LEAD_B2B
   - Administración de un inmueble → LEAD_ADMIN
   - Asesoría legal o notarial → LEAD_LEGAL (→ Ana Laura Monsivais)
   - Otro → LEAD_OTRO
2. nombre, email, whatsapp
3. colonia (select, todas las zonas BJ + Otra)
4. mensaje (textarea opcional)
5. aviso (checkbox)

Página incluye también: dirección, teléfono, email, mapa embebido, WhatsApp directo, redes sociales.

---

### 3.9 Observatorio de Precios `/mercado`
**Controller:** `MarketController`

**`/mercado` (index):**
- Lista todas las `MarketZone` publicadas con sus `MarketColonia`
- Muestra precio m² referencia por zona (snapshot de depto seminuevo, venta)
- Grid de zonas de BJ con pills de colonias

**`/mercado/{zona}` (zone):**
- Hero con precio m² promedio de la zona
- Nav de zonas (pills horizontales)
- Tabla de precios por tipo × antigüedad (tabs Venta / Renta)
  - Tipo: Departamento · Casa · Local/Oficina (renta)
  - Antigüedad: Nuevo (0-5 años) · Seminuevo (6-20 años) · Antiguo (+20 años)
- Gráfica de evolución mensual (Chart.js) — últimos 12 meses, venta y renta
- Badge de confianza (alta / media + # anuncios analizados)
- Badge "Verificado por agente HDV" si `isValidated=true`
- CTA intermedio de valuación
- Colonias de la zona (pills clickeables → `/mercado/{zona}/{colonia}`)
- FAQ con rich results schema.org (5 preguntas dinámicas)
- CTA final

**`/mercado/{zona}/{colonia}` (colonia):**
- Hero con precio m² de la zona (referencia)
- Aviso: "datos son de zona, no colonia específica"
- Tabla de precios (igual que zona pero con aviso)
- Nota metodológica
- Colonias vecinas (siblings)
- CTA final

**`/mercado/opinion-de-valor` (formulario):**
- Vista `public/mercado/opinion.blade.php`
- Livewire component o controller `ValuationLeadController@store`
- Crea lead con intención de valuación

**Modelos involucrados:**
- `MarketZone` — zonas publicadas (Del Valle, Narvarte, Nápoles, Portales, etc.)
- `MarketColonia` — colonias dentro de cada zona (30+ colonias de BJ)
- `MarketZoneSnapshot` — precios m² por period/operation_type/property_type/age_category con confidence + sample_size

---

### 3.10 Blog `/blog`
**Controller:** `BlogController`

- `/blog` — listado con paginación, 15 artículos por página
- `/blog/{slug}` — detalle del artículo con tipografía prose (Tailwind Typography)
- 15 artículos publicados con 6 categorías:
  - Inversión Inmobiliaria
  - Vender tu Propiedad
  - Colonias de Benito Juárez
  - Expertos & Insights
  - Mercado Inmobiliario CDMX
  - Zonificación & Desarrollo
- Body renderizado como HTML crudo (`{!! $post->body !!}`) desde TinyMCE
- Meta tags SEO dinámicos por artículo

---

### 3.11 Otras páginas públicas

| Ruta | Controller | Notas |
|---|---|---|
| `/nosotros` | PublicController@nosotros | Equipo, historia, valores |
| `/servicios` | PublicController@servicios | 5 líneas de servicio |
| `/testimonios` | PublicController@testimonios | 3+ testimonios desde DB |
| `/gracias` | PublicController@gracias | Página de confirmación post-form con folio |
| `/legal/{slug}` | LegalPageController@show | Documentos legales versionados |
| `/form/{slug}` | PublicFormController | Formularios dinámicos (form builder) |
| `/sitemap.xml` | SitemapController | Sitemap dinámico |

---

### 3.12 Presentaciones Públicas `/presentaciones/{token}`
**Controller:** `PresentationPublicController`

- Vista pública de presentaciones de captación (sin auth)
- Se comparte por WhatsApp/email con link tokenizado
- El propietario puede ver su presentación sin cuenta
- Descarga en PDF disponible
- Pixel de tracking (email open)

---

### 3.13 Firma de Contratos `/firma/{token}`
**Controller:** `ContratoPublicoController`

- Firma digital de contratos sin cuenta de portal
- Vista pública con token único por contrato

---

## 4. CRM Admin — Secciones y estado

**Acceso:** `homedelvalle.mx/admin` · middleware: `auth` + `viewer`
**Layout:** `layouts/app-sidebar.blade.php` (sidebar 260px fondo #1e1b4b)

### 4.1 Dashboard `/admin`
- Métricas principales: leads nuevos, captaciones activas, operaciones activas, rentas activas
- Pipeline de operaciones por stage (visual)
- Leads sin contactar > 24h (badge de urgencia)
- Últimas actividades del equipo
- Acceso rápido: "Nueva captación" (Ctrl+Shift+N)

### 4.2 Leads / Form Submissions `/admin/form-submissions`
**Controller:** `FormSubmissionController`

- Lista todos los `FormSubmission` con badges por tipo y estado
- Filtros: tipo (vendedor/comprador/b2b/rentar/arrendador/contacto), estado, fecha
- Estados: new → contacted → qualified → won → lost
- Acciones: ver detalle, cambiar estado, agregar notas, **convertir a Cliente** (→ crea `Client`)
- Delete individual y bulk
- Muestra folio, fecha, nombre, email, teléfono, tag, estado, asignado a

### 4.3 Clientes `/clients`
**Controller:** `ClientController`
**Modelo:** `Client`

- Lista con filtros: temperatura, tipo, canal, broker asignado, usuario asignado
- CRUD completo (create/edit/show/delete)
- Perfil completo: datos, budget, intereses, scoring, segmentos
- Sección de Interacciones (llamadas, visitas, emails, notas)
- Historial de operaciones y deals vinculados
- Emails enviados con tracking de apertura
- Gestión de cuenta del Portal del Cliente:
  - Crear cuenta → `ClientPortalService`
  - Activar/desactivar acceso
  - Reset de contraseña
  - Eliminar cuenta portal

**Lead Scoring:**
- Grados A/B/C/D calculados por `RecalculateLeadScores` (job diario)
- Reglas configurables en `/admin/marketing/scoring`
- Eventos: visita, consulta, interés expresado, documento entregado, etc.

### 4.4 Propiedades `/properties`
**Controller:** `PropertyController`
**Modelo:** `Property`

- CRUD completo
- Galería de fotos con drag-and-drop (Spatie Media Library, hasta 20 fotos)
- Optimización automática de imágenes (Intervention Image)
- Publicación a EasyBroker (API externa para portales)
- Toggle "Propiedad destacada"
- QR Code regenerable (endroid/qr-code) — página de ficha técnica
- Ficha técnica PDF (DomPDF) — descargable y enviable por email
- Vinculación con cliente propietario y broker
- Vinculación con `MarketColonia` para observatorio de precios

### 4.5 Captaciones de Venta `/admin/captaciones`
**Controller:** `CaptacionAdminController`

Pipeline de captación (Fase 1 antes de operar):
```
lead → contacto → visita → revision_docs → avaluo → mejoras → exclusiva → fotos_video → carpeta_lista
```

- Vista de lista con métricas: días en stage actual, etapa, responsable
- Vista de detalle con timeline completo
- Generación de **Presentación de Captación** (PDF vía Browsershot):
  - Incluye: valuación referenciada al Observatorio, análisis comparativo de mercado, propuesta de precio, plan de comercialización
  - Se comparte por WhatsApp con link tokenizado a `/presentaciones/{token}`
- Checklist por etapa (plantillas configurables)
- Documentos: upload, verificación, categorías
- Vinculación con `PropertyValuation` (Opinión de Valor)
- Botón "Generar exclusiva" → genera contrato de exclusividad
- Botón "Declinar" → cierra captación sin avanzar

**Herramientas de valuación** (desde sidebar):
- **Valor Rápido** `/admin/quick-quote` — estimación rápida con datos básicos
- **Valuación Constructor** `/admin/constructor-valuation` — Livewire: calcula precio para proyectos de nueva construcción con SEDUVI (COS/CUS/pisos), análisis de sensibilidad por precio, escenarios comparativos
- **Opinión de Valor** `/admin/valuations` — valuaciones formales vinculadas a captaciones

### 4.6 Operaciones `/operations`
**Controller:** `OperationController`
**Modelo:** `Operation`

Tipos de operación: `venta` · `renta` · `captacion`

Pipeline de Operación (Fase 2):
```
publicacion → busqueda → investigacion → contrato → entrega → cierre
(renta: + activo → renovacion)
```

- Kanban por stage filtrable por tipo y usuario asignado
- **Checklist automático** por etapa (desde `StageChecklistTemplate`)
  - Auto-avance de stage cuando ítems requeridos están completados
- **Auto-spawn:** captación completada → genera operación de venta o renta automáticamente
- **Timeline:** historial de cambios de stage + documentos + tareas + comentarios
- **Comentarios con @mentions** → notificación in-app al usuario mencionado
- **Documentos:** upload, verificación, categorías
- **Contratos:** generación desde templates con variables (DomPDF) + firma digital
- **Póliza Jurídica:** tracking completo con eventos
- **Comisiones:** auto-cálculo de comisiones de referidos al cierre
- **Campos clave para renta:** monthly_rent, deposit_amount, lease_start_date, lease_end_date, lease_duration_months, guarantee_type

### 4.7 Rentas `/admin/rentas`
**Controller:** `RentalsAdminController`

Vista dedicada del funnel de rentas con 3 sub-vistas:

**Captaciones de Renta** `/admin/rentas/captaciones`
- Propiedades en proceso de captación con `metadata.intent=rental`
- Pipeline: lead → contacto → visita → exclusiva → fotos_video → carpeta_lista

**Colocación Activa** `/admin/rentas/activas`
- Inmuebles publicados buscando inquilino
- Métricas: días en mercado, visitas agendadas, briefs de inquilinos activos
- Match manual: vincular brief de inquilino con propiedad disponible

**Gestión Post-Cierre** `/admin/rentas/gestion`
- Contratos activos con: fechas, monto, próxima cobranza, días hasta vencimiento
- Alertas automáticas: vencimiento < 60 días → badge de renovación
- Vista individual `/admin/rentas/gestion/{rental}` con historial completo

### 4.8 Tareas `/tasks`
**Controller:** `TaskController`
**Modelo:** `Task`

- Tareas vinculables a: operación, cliente, propiedad, rental process, deal
- Toggle completado (PATCH)
- Filtros por: usuario, prioridad, estado, vencimiento
- Creación rápida desde cualquier entidad (inline)

### 4.9 Equipo

**Brokers** `/brokers` — CRUD, foto, comisiones, relación con BrokerCompany
**Empresas Broker** `/broker-companies` — CRUD
**Comisionistas** `/referrers` — portero, vecino, broker hipotecario, cliente pasado, otro
**Referidos** — vinculados a operaciones con comisión auto-calculada
**Workflow de pago:** registrado → en_proceso → por_pagar → pagado
**Usuarios** `/admin/users` — CRUD, avatar, roles, permisos granulares (RBAC)

### 4.10 Analytics `/admin/analytics`
**Controller:** `AnalyticsController`

- Métricas de tráfico y conversión
- Fuentes de leads por canal
- Performance del pipeline (% conversión por etapa)
- Datos del Observatorio de precios

### 4.11 Marketing

**Canales** `/admin/marketing/channels` — canales activos, presupuesto, gasto
**Campañas** `/admin/marketing/campaigns` — fechas, status, atribución
**Segmentos** `/admin/segments` — reglas campo+operador+valor, evaluación periódica (`EvaluateSegments` job)
**Lead Scoring** `/admin/marketing/scoring` — reglas por evento, puntos, límite diario, grados A/B/C/D
**Automatizaciones Engine** `/admin/automations-engine` — motor multi-paso con triggers/condiciones/acciones/enrollments
**Automatizaciones simples** `/admin/automations` — reglas con trigger/conditions/action + logs
**Mensajes** `/admin/messages` — mensajes enviados por automatizaciones
**Suscriptores** `/admin/newsletters/subscribers` — leads del chatbot + export CSV

### 4.12 Blog & Content

**Posts** `/admin/posts` — CRUD con TinyMCE, status: draft/scheduled/published/archived
**Generador de Blog IA** `/admin/blog/generar` — genera borradores con IA basado en temas del observatorio
**Descubrir temas** `/admin/blog/descubrir` — sugiere temas SEO
**Imágenes IA** `/admin/blog/{post}/imagenes` — genera imagen destacada con DALL-E
**Calendario de Contenido** `/admin/content-calendar` — vista mes/semana/día, drag-and-drop
**Categorías y Tags** — CRUD
**Carruseles Instagram** `/admin/carousels` — templates Blade, generación con IA, renders con Browsershot, aprobación
**Posts Facebook** `/admin/facebook-posts` — generación y programación

### 4.13 Sistema de Email

**Configuración SMTP** `/admin/email/settings` — dinámico desde DB, password encriptado, test de conexión
**SMTP por usuario** — `UserMailSetting` para envíos personalizados
**Templates Legacy** `/admin/email/templates` — WYSIWYG, variables {{Nombre}}, galería de assets
**Email Templates V4** (transaccionales, hardcoded, inmutables):
- `lead_interno` — notificación interna de nuevo lead
- `acuse` — confirmación al lead
- `cita` — confirmación de cita
- `comprador` — bienvenida comprador
- `bienvenida` — bienvenida general
**Custom Email Templates** `/admin/email/custom-templates` — sistema custom DB-backed para marketing:
- Tipos: custom, marketing, newsletter, promotional
- Status: draft → published → archived
- Detección automática de placeholders `{{nombre}}`
- Test email con audit trail
- Asignación a triggers/eventos
**Assets de Email** `/admin/email/assets` — galería de imágenes para templates
**Tracking de apertura** — pixel 1x1 por email enviado a cliente

### 4.14 Sitio Web CMS

**Homepage** `/admin/homepage` — secciones editables: hero, stats, servicios, nosotros, CTA
**Página Servicios** `/admin/servicios-page`
**Página Nosotros** `/admin/nosotros-page` — edición de equipo, toggle visibilidad por usuario, orden drag-and-drop
**Página Vender** `/admin/vender-page`
**Páginas CMS** `/admin/pages` — páginas estáticas y landing pages dinámicas
**Menus** `/admin/menus` — `menus` + `menu_items` (navbar dinámico, items anidados)
**Footer** `/admin/footer`
**Media Library** `/admin/media` — upload múltiple (20 archivos, max 10MB), organizado por YYYY/MM
**Formularios** `/admin/forms` — form builder dinámico con URLs públicas
**Testimonios** `/admin/testimonials` — CRUD de testimonios para sitio público

### 4.15 Finanzas

**Dashboard** `/admin/finance` — ingresos, egresos, comisiones pendientes
**Transacciones** — CRUD con categorías, vinculadas a deals/propiedades/brokers
**Comisiones** — workflow aprobar → pagar, vinculadas a deals/operaciones

### 4.16 Legal

**Documentos legales** `/admin/legal` — CRUD con versionamiento automático
**Tracking de aceptación** — email, IP, user_agent, contexto, timestamp
**Vista pública** `/legal/{slug}` — aviso de privacidad, términos, cookies
**Plantillas de Contratos** `/admin/contract-templates` — HTML con variables, preview, generación PDF (DomPDF)
**Checklists** `/admin/checklists` — templates por etapa/tipo de operación

### 4.17 Configuración

**General** `/admin/settings` — SiteSetting: logo, colores, contacto, SEO global, integraciones
**Agentes IA** `/admin/ai-config` — configuración de modelos IA para generación de contenido
**Precios Mercado** `/admin/market/prices` — interface para actualizar snapshots del Observatorio
**EasyBroker** `/admin/easybroker/settings` — API key, test conexión, mapeo de ubicaciones
**Integraciones** `/admin/integrations` — GTM, GA4, Facebook Pixel, Google Workspace, scripts personalizados
**Centro de Ayuda** `/admin/help/manage` — CRUD de artículos, tips y onboarding

---

## 5. Flujos completos (end-to-end)

### Flujo A — Venta de propiedad

```
1. Propietario llega a /vende-tu-propiedad
   └─ LandingController@storeVendedor
      ├─ Crea FormSubmission (tipo=vendedor, tag=LEAD_VENDEDOR)
      ├─ Email transaccional al lead (V4: acuse)
      ├─ Notificación interna al equipo (email a leads@homedelvalle.mx)
      └─ Redirige /gracias con folio HDV-XXXX-{id}

2. Agente abre /admin/form-submissions
   └─ Revisa lead, llama, registra interacción
      └─ "Convertir a Cliente" → crea Client (client_type=owner)

3. Agente crea Captación desde /admin/captaciones
   └─ Operation (type=captacion, stage=lead, client_id)
      └─ Pipeline: lead → contacto → visita → revision_docs → avaluo
                  → mejoras → exclusiva → fotos_video → carpeta_lista

4. En stage "avaluo" → genera Opinión de Valor
   └─ Herramienta: Valuación Constructor o Valor Rápido
      └─ Presenta al propietario con Presentación PDF (link tokenizado)

5. En stage "exclusiva" → genera Contrato de Exclusividad (DomPDF)
   └─ Firma digital o firma manual escaneada

6. En stage "carpeta_lista" → AUTO-SPAWN
   └─ Crea Operation (type=venta, stage=publicacion)
      └─ Pipeline: publicacion → busqueda → investigacion → contrato → entrega → cierre

7. En "publicacion" → publica a EasyBroker (API) y en sitio /propiedades
8. En "investigacion" → genera Contrato de Compraventa
9. En "cierre" → calcula comisiones, actualiza Operation.closed_at
```

### Flujo B — Búsqueda asistida (comprador)

```
1. Comprador llega a /comprar
   └─ LandingController@storeComprador
      ├─ Crea FormSubmission (tipo=comprador, tag=LEAD_COMPRADOR)
      ├─ Email transaccional (V4: comprador)
      └─ Redirige /gracias

2. Agente abre /admin/form-submissions
   └─ Convierte a Client (client_type=buyer)
      └─ Registra budget_min/max, zonas, timing en metadata

3. Agente crea Operation (type=venta, stage=lead) vinculada al comprador
   └─ Pipeline: lead → contacto → visita → investigacion → contrato → entrega → cierre

4. Matching manual: agente busca propiedades disponibles que coincidan con brief
   └─ Envía selección curada por email/WhatsApp

5. Al aceptar una opción → vincula Property a la Operation
6. En "investigacion" → due diligence legal, generación de contrato
7. En "cierre" → operación completada
```

### Flujo C — Renta (inquilino + propietario)

```
LADO PROPIETARIO:
1. Propietario llega a /renta-tu-propiedad
   └─ Livewire rental-owner-form → crea FormSubmission (tag=LEAD_ARRENDADOR)
      └─ Si quiere_administracion=sí → etiqueta para seguimiento dual

2. Agente convierte a Client (client_type=owner, metadata.intent=rental)
   └─ Crea Operation (type=captacion, metadata.intent=rental, stage=lead)
   └─ Pipeline: lead → contacto → visita → exclusiva → fotos_video → carpeta_lista

3. Al completar captacion → AUTO-SPAWN Operation (type=renta, stage=publicacion)
   └─ Propiedad entra a /propiedades con operation_type=rental

LADO INQUILINO:
4. Inquilino llega a /rentar
   └─ Livewire renter-search-form → crea FormSubmission (tag=LEAD_INQUILINO)

5. Agente convierte a Client (client_type=renter)
   └─ Registra metadata: zonas, plazo, mascotas, garantia, timing

MATCHING:
6. Agente vincula Operation de renta + Client inquilino
   └─ Pipeline: busqueda → investigacion → contrato → entrega → activo → renovacion
   └─ En "activo" → crea RentalProcess (move-in, pagos, cobranza, póliza)

7. RentalProcess gestiona:
   └─ Cobranza mensual + recordatorios automáticos
   └─ Alertas de vencimiento (job CheckRentalRenewals)
   └─ Reportes mensuales al propietario (job GenerateMonthlyOwnerReports)
```

### Flujo D — B2B Developer/Inversor

```
1. Llega a /desarrolladores-e-inversionistas
   └─ LandingController@storeDesarrollador
      ├─ Crea FormSubmission (tipo=b2b, tag=LEAD_B2B)
      ├─ Upload de brief PDF → Spatie Media Library (collection=briefs)
      ├─ Si NDA marcado → registra en legal_acceptances (tipo=nda_b2b_initial)
      ├─ Email transaccional: confirmación con promesa de llamada < 48h
      ├─ Notificación interna directa a Dirección General (email prioritario)
      └─ Redirige /gracias

2. Dirección recibe notificación in-app + email [B2B PRIORITARIO]
   └─ Convierte a Client (client_type=investor, lead_temperature=hot)
      └─ Asignado a: Ana Laura / Dirección General

3. Llamada de calificación → registra interacción
   └─ Si aplica → crea Operation (type=captacion, intent=b2b)
```

### Flujo E — Observatorio de Precios

```
1. Admin carga datos en /admin/market/prices
   └─ Crea/actualiza MarketZoneSnapshot por zone + period + operation_type + property_type + age_category
      └─ Campos: price_m2_avg, price_m2_low, price_m2_high, sample_size, confidence (low/medium/high)

2. Job mensual (1° de cada mes) → actualiza snapshots automáticamente (si implementado)

3. Visitante accede a /mercado → /mercado/{zona} → /mercado/{zona}/{colonia}
   └─ Ve precios de referencia + gráfica histórica + badge de confianza
   └─ Puede solicitar Opinión de Valor → ValuationLeadController@store
      └─ Crea lead con intención de valuación → aparece en /admin/valuations

4. Agente en /admin/captaciones puede vincular una valuación a una captación
   └─ La presentación de captación incluye datos del observatorio como respaldo
```

---

## 6. Scheduler (jobs programados)

**Configuración:** cPanel ejecuta `artisan schedule:run` cada minuto.
**Todos los jobs son síncronos** (sin `ShouldQueue`) — cPanel no tiene queue worker.

| Job | Frecuencia | Función |
|---|---|---|
| `ProcessAutomationEnrollments` | Cada minuto | Procesa enrollments activos del motor de automatizaciones |
| `PublishScheduledPosts` | Cada minuto | Auto-publica posts con `published_at` <= now() |
| `EvaluateSegments` | Cada 5 min | Re-evalúa segmentos de clientes según reglas |
| `CheckGoogleSignatureRequests` | Cada 30 min | Verifica estado de firmas digitales en Google |
| `RecalculateLeadScores` | Diario | Recalcula scores y grados A/B/C/D de todos los clientes |
| `CheckRentalRenewals` | (definido, verificar) | Alerta sobre contratos de renta por vencer |
| `ProcessMonthlyRentalBilling` | (definido, verificar) | Cobranza mensual de rentas |
| `GenerateMonthlyOwnerReports` | (definido, verificar) | Reportes mensuales para propietarios |
| `UpdateMarketPrices` | Mensual (día 1) | Actualiza snapshots del observatorio |

---

## 7. Integraciones externas

| Integración | Estado | Uso |
|---|---|---|
| **EasyBroker** | ✅ Activa | Publicar/despublicar propiedades en portales inmobiliarios |
| **Google Workspace** | ✅ Activa | Google Docs (contratos), Google Drive (storage), Google Signature Requests |
| **DALL-E / OpenAI** | ✅ Activa | Generación de imágenes para carouseles y blog |
| **Spatie Browsershot + Puppeteer** | ✅ Activa | PDFs de presentaciones, fichas técnicas, carouseles |
| **Google Tag Manager** | Configurable | Inyección de scripts de tracking |
| **Google Analytics 4** | Configurable | Analytics de tráfico |
| **Facebook Pixel** | Configurable | Tracking de conversiones |
| **WhatsApp** | Manual + deep link | Presentaciones tokenizadas, contacto directo |
| **PHPMailer + SMTP dinámico** | ✅ Activa | Todos los emails del sistema |

---

## 8. Seguridad y autenticación

- **Autenticación manual** con `Auth::attempt()` (sin Breeze/Jetstream)
- **Rate limiting:** login (5/min), forgot-password (3/min), formularios públicos (10/min)
- **Honeypot anti-spam** en todos los formularios públicos (campo `website_url`)
- **RBAC granular:** tablas `roles`, `permissions`, `role_user`, `permission_role`
- **Middleware:** admin, editor, viewer, broker, client, permission:{slug}
- **Tokens de reset custom:** tabla `custom_password_resets` con `used` + expiration 30 min
- **SESSION_DOMAIN=`.homedelvalle.mx`** para cookies cross-subdomain
- **Portal del Cliente:** scope `client`, login en subdominio dedicado, audit logs

---

## 9. SEO implementado

- **Meta tags dinámicos** por página/post (título, descripción, canonical)
- **Open Graph** (og:title, og:description, og:image) en todas las páginas
- **Schema.org JSON-LD:**
  - `RealEstateAgent` en homepage
  - `FAQPage` en páginas de zona del Observatorio
  - `BreadcrumbList` en páginas de zona y colonia
- **Sitemap.xml dinámico** (`/sitemap.xml`)
- **Breadcrumbs** en Observatorio
- **Tailwind Typography** para blog (prose)
- **Canonical URLs** en Observatorio

---

## 10. Mejoras recomendadas (priorizadas)

### 🔴 Alta prioridad — Funcionalidad rota o crítica

1. **Script cpanel-deploy.sh desactualizado**
   El script asume `~/repositories/homedelvalle` + `~/public_html` pero el servidor real usa `/www/wwwroot/homedelvalle.mx` como web root directo. Los deploys fallan silenciosamente (index.php y .htaccess no se crean). Actualizar las rutas en el script o reescribirlo para el servidor actual.

2. **No hay POST handler en /rentar ni /renta-tu-propiedad en routes/web.php**
   Las rutas solo tienen GET. Los formularios Livewire (`renter-search-form`, `rental-owner-form`) deben persistir datos correctamente. Verificar que los componentes Livewire existen y funcionan, y que crean el `FormSubmission` + notificaciones correctamente.

3. **Catálogo público con 1 sola propiedad**
   El sitio lleva funcionando con 1 propiedad pública. Esto contradice el discurso de la marca y deja el catálogo visualmente muerto. Tomar decisión A/B/C del Roadmap y ejecutar.

4. **Build de assets (CSS/JS) no incluido en el repo**
   El deploy muestra `[WARN] No existe public/build/`. Si Vite no está configurado en el servidor para compilar, los assets no cargan. Opciones: a) compilar en local y commitear `public/build/`, b) configurar npm+vite en el servidor, c) usar CDN.

### 🟡 Media prioridad — UX y conversión

5. **Formulario /contacto: separar "comprar" y "rentar"**
   Hoy la opción 2 dice "Estoy buscando dónde comprar o rentar". El brief v3 pide separar en 2 opciones distintas para mejorar el routing del lead en el CRM.

6. **WhatsApp flotante con mensaje contextual por página**
   El botón de WhatsApp usa el mismo mensaje genérico en todas las páginas. Con una modificación pequeña en `whatsapp-button.blade.php` (leer `request()->path()`), se puede personalizar el mensaje según el funnel donde está el visitante.

7. **Footer columna EXPLORAR incompleta**
   Faltan: Comprar, Rentar, Renta tu propiedad, Inversión & Desarrollo, Mercado. La columna actual no incluye los 4 funnels del sitio.

8. **Erratas pendientes en home**
   Sección "Operamos desde la demanda" paso 03: `Ejecutamos la operacion`, `Negociacion, blindaje legal y cierre eficiente`. Tarjeta Compra: `catalogo exclusivo`, `Busqueda personalizada`, `Analisis de inversion`, `Acompanamiento legal`. Tarjeta Venta: `Mas solicitado`, `Valuacion profesional`, `Marketing y fotografia`. Sección stats: `Anos de experiencia senior`.

9. **Erratas en footer**
   `© 2026 Home del valle Bienes Raíces` (V minúscula), `Asociacion Mexicana de Profesionales Inmobiliarios`, `Politica de cookies`, `Terminos y condiciones`, `Heriberto Frias 903-A`.

10. **Erratas en /testimonios (quotes de DB)**
    Quote de Salvador: `Asesoria inmobiliaria`, `Cerramos una buena operacion`. Requiere editar registro en la tabla `testimonials` directamente.

11. **`/nosotros`: "Quienes somos" → "Quiénes somos"**

12. **`/contacto`: "Heriberto Frias" → "Heriberto Frías"**
    Además, el teléfono `+5215513450978` se muestra muy denso → formatear como `+52 55 1345 0978`.

### 🟢 Mejoras estratégicas — Conversión y producto

13. **Navbar con dropdowns para los 4 funnels**
    Con 4 funnels + mercado + servicios + nosotros + blog + contacto el navbar plano tiene demasiados items. Implementar 2 dropdowns:
    - "Buscar inmueble ▾" → Comprar / Rentar / Ver propiedades
    - "Soy propietario ▾" → Vender / Rentar mi propiedad / Administración

14. **Casos de éxito en landings**
    - `/comprar`: 1-2 casos anclados a zona ("Comprador en Narvarte cerró en 32 días")
    - `/desarrolladores-e-inversionistas`: 1 caso de estudio B2B anonimizado (zona, m², ticket, plazo)
    - Estos son la barrera de conversión principal en ambas landings.

15. **Workflow automático de captura de testimonios post-cierre**
    Cuando una `Operation.status='completed'`, disparar email/WhatsApp al cliente con link a mini-form `/testimonio-cliente/{token}`. Persistir en tabla `testimonials` con `client_id`, `operation_id`, `authorized_at`. Actualmente los testimonios se cargan manualmente.

16. **Slot de fotos en /vende-tu-propiedad**
    Permitir subir 3 fotos del inmueble desde el formulario (Spatie Media Library). Mejora la calidad del lead y permite al agente llegar mejor preparado a la primera llamada.

17. **Validación dinámica del precio en /vende-tu-propiedad**
    Cuando el usuario seleccione colonia, mostrar rango orientativo del Observatorio de Precios ("Inmuebles en Del Valle de 100-150 m² se publican entre $X M y $Y M"). No vinculante, pero educativo y aumenta calidad del lead.

18. **Indicador de "Estamos abiertos" en el header**
    Punto verde si es L-V 9:00-19:00 Mexico City. Gris fuera de horario. Reduce expectativas de respuesta off-hours y da credibilidad operativa.

19. **Mini-calculadora de renta** en `/renta-tu-propiedad`
    CTA secundario "¿Cuánto rentaría mi inmueble?" → calculadora rápida que cruza zona + m² + amueblado con datos del Observatorio. Genera engagement antes del formulario.

20. **Integración Calendly en /desarrolladores-e-inversionistas**
    Actualmente se promete "agendamos llamada < 48h" pero no hay auto-servicio. Agregar enlace de Calendly o formulario de fecha preferida en la banda final.

21. **Vista "Funnel por origen" en /admin/analytics**
    Gráfico simple: por cada `lead_source` (`/comprar`, `/desarrolladores`, `/vende`, `/contacto`, chatbot), cuántos leads llegaron → calificados → cerraron. La DB ya tiene los datos (`form_submissions.source_page`, `operations`), solo falta la visualización.

22. **Sección "Renta + Administración integral" en /servicios**
    La administración de inmuebles existe como servicio pero no está conectada visualmente al funnel de renta. Destacarla como combo en la página de servicios.

23. **Categoría de blog "Renta e Inquilinos"**
    Publicar 3-4 artículos para apoyar el SEO del funnel de rentas recién lanzado (keywords: "cómo rentar departamento Benito Juárez", "póliza jurídica arrendamiento CDMX", etc.).

24. **Portal del Cliente — completar migración a subdominio**
    El Portal del Cliente (`miportal.homedelvalle.mx`) está parcialmente implementado. La spec completa está en `docs/06-PORTAL-DEL-CLIENTE.md`. Pendiente: layout dedicado con Livewire 4, 4 perfiles de usuario, mensajería bidireccional, cobranza automática, reportes mensuales.

25. **OG Image dinámica por página**
    Actualmente el OG image es genérico. Con Browsershot se puede generar una imagen 1200x630 con fondo navy, nombre de la zona/colonia y el precio m² para cada URL del Observatorio. Aumenta CTR al compartir en WhatsApp.

---

## 11. Deuda técnica conocida

| Item | Impacto | Solución |
|---|---|---|
| `BrokerPhotoController` vacío | Bajo | Implementar o eliminar |
| `AdminController` vacío | Bajo | Eliminar |
| Migración duplicada `add_role_enum_to_users` | Bajo | Eliminar la duplicada |
| Tabla `password_reset_tokens` de Laravel existe pero no se usa | Bajo | Mantener o limpiar |
| Directorio `CRM-VBNet/` en el repo (proyecto de referencia anterior) | Bajo | Mover fuera del repo |
| Sin email verification | Bajo | Pendiente de implementar |
| 2FA por email preparado pero no activo | Medio | Activar cuando se priorice seguridad |
| Layout `admin/layout.blade.php` sin usar | Bajo | Eliminar |
| Script `cpanel-deploy.sh` con rutas incorrectas para el servidor actual | Alto | Actualizar rutas |
| `public/build/` no en repo + sin npm en servidor | Alto | Resolver estrategia de build |

---

## 12. Referencia rápida de archivos clave

| Necesitas... | Archivo |
|---|---|
| Convenciones técnicas obligatorias | `IMPLEMENTATION_RULES.md` |
| Versiones y reglas de actualización | `CRITICAL_VERSIONS.md` |
| Esquema DB (108 tablas, 93 modelos) | `.claude/SCHEMA_QUICK_REFERENCE.md` |
| Arquitectura completa y modelos | `CONTEXTO_PROYECTO.md` |
| Proceso completo de renta | `docs/05-PROCESO-DE-RENTA.md` |
| Spec del Portal del Cliente | `docs/06-PORTAL-DEL-CLIENTE.md` |
| Brief original (funnels, copy, formularios) | `Brief para Claude Code - Implementacion Opcion C.md` |
| Estado auditoría 2026-04-28 | `Brief para Claude Code - Implementacion Opcion C_1.md` |
| Estado auditoría + 4to funnel rentas | `Brief para Claude Code - Implementacion Opcion C2.md` |
| Deploy en servidor | `DEPLOYMENT_GUIDE.md` |
| Sistema QR | `QR_IMPLEMENTATION.md` |
| Galería premium de propiedades | `GALLERY_PREMIUM_DOCS.md` |

---

*Documento generado: 2026-06-29 · Commit base: `a1703b5` · Auditoría completa del codebase*
