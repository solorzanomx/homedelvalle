# Manual de Implementación del Sitio · Home del Valle

> **Audiencia:** Alex, desarrolladores externos (Claude Code y otros), futuros agentes técnicos.
> **Estado:** v0 — abril 2026.
> **Última revisión:** 2026-04-27.
> **Documento hermano:** `01-MANUAL-MARCA-Y-VOZ.docx`, `03-MANUAL-OPERACIONES-CRM.docx`, `04-ROADMAP-Y-ARQUITECTURA.md`.

Este manual responde a la pregunta **"¿cómo se hace X en el sitio de Home del Valle sin romper nada?"**. Cada módulo está documentado con: qué tablas y modelos toca, qué archivos editar, cómo probarlo y cómo desplegarlo.

Antes de tocar código, lee también `IMPLEMENTATION_RULES.md` (raíz del repo) y `CRITICAL_VERSIONS.md`. Este manual asume que ya las leíste.

---

## Índice

1. Stack y convenciones de proyecto
2. Estructura del repo y dónde vive cada cosa
3. Cómo agregar una página nueva (CMS o estática)
4. Cómo agregar/modificar un formulario público
5. Cómo subir y publicar una propiedad
6. Cómo gestionar el menú y el footer
7. Cómo modificar el chatbot calificador y el WhatsApp flotante
8. Cómo crear o editar un post del blog
9. Cómo configurar SEO, OG image y tracking
10. Cómo trabajar con el sistema de email (templates, SMTP, tracking)
11. Cómo modificar la apariencia (colores, tipografías, CSS variables)
12. Cómo crear una landing page nueva
13. Cómo integrar un nuevo formulario al pipeline de operaciones
14. Cómo deployar a producción
15. Patrones a evitar y errores comunes
16. Convenciones de git y nombrado
17. Portal del Cliente — qué considerar al construir cualquier feature

---

## 1. Stack y convenciones de proyecto

| Componente | Versión confirmada |
|---|---|
| PHP | 8.3.30 (require ^8.2) |
| Laravel | 13.6.0 |
| Livewire | 4.2.4 (sitio público usa Alpine.js; **el Portal del Cliente sí usa Livewire** para componentes reactivos autenticados) |
| Filament | 5.6.1 (instalado pero **no** es el admin principal; el admin custom vive en `layouts/app-sidebar.blade.php`) |
| Tailwind CSS | 4.2.2 (sitio público y portal del cliente; el CRM admin usa CSS puro con variables CSS) |
| Vite | 8.0.3 |
| TinyMCE | 8.3.2 (self-hosted GPL en `public/vendor/tinymce/`) |
| Alpine.js | CDN (sitio público) |
| DomPDF | 3.x (contratos y documentos) |
| Intervention/Image | 3.x (procesado de imágenes) |
| PHPMailer | 6.x (SMTP dinámico desde DB) |
| Lucide-static | 1.7+ (iconos SVG) |
| Spatie Media Library | 11.21.2 |
| Endroid/qr-code | 6.0.9 (cuidado con la API — ver `CRITICAL_VERSIONS.md`) |
| Base de datos | SQLite local · MySQL producción (`sql_homedelvalle_mx`) |
| Hosting | cPanel compartido — sin queue worker (jobs corren síncronos) |
| Timezone | `America/Mexico_City` |
| Subdominios productivos | `homedelvalle.mx` (sitio público + CRM admin) · `miportal.homedelvalle.mx` (Portal del Cliente — ver `06-PORTAL-DEL-CLIENTE.md`) |

**Convenciones del repo:**

- Layouts: `layouts/public.blade.php` (sitio público), `layouts/app-sidebar.blade.php` (CRM admin), `layouts/landing.blade.php` (landings de captación), `layouts/portal.blade.php` (**Portal del Cliente**), `layouts/app.blade.php` (legacy).
- El CRM admin se renderiza con **Blade + CSS puro + variables CSS**, no con Tailwind ni con Filament.
- El sitio público se renderiza con **Blade + Tailwind 4 + Alpine.js** (formularios públicos = Alpine + controlador, no Livewire).
- **El Portal del Cliente** (`miportal.homedelvalle.mx`) se renderiza con **Blade + Tailwind 4 + Livewire 4** (es app autenticada, no SEO; los componentes reactivos son la regla).
- Iconos: componente Blade que inyecta SVG inline desde Lucide-static (igual en sitio, CRM y portal).
- Jobs **NO** usan `ShouldQueue` — corren síncronos vía `php artisan schedule:run` cada minuto en cron de cPanel.
- Cache no almacena objetos Eloquent — sólo arrays (evita `__PHP_Incomplete_Class`).
- Autenticación es manual con `Auth::attempt()` (no Breeze ni Jetstream).
- **Cada cliente que firma con HDV recibe cuenta de portal automáticamente.** No es opcional. Ver sección 17 abajo y `06-PORTAL-DEL-CLIENTE.md` sección 5.

---

## 2. Estructura del repo y dónde vive cada cosa

```
app/
  Http/Controllers/                 # 25 controladores principales
    Admin/                          # 34 controladores del panel admin
    Auth/                           # 4 (Login, Register, Forgot, Reset)
    Portal/                         # 3 controladores del portal de clientes
  Models/                           # 93 modelos
  Services/                         # 11 servicios (EasyBroker, Email, Contract, …)
  Jobs/                             # 4 jobs SÍNCRONOS (sin ShouldQueue)
  Policies/                         # 1 (ClientPolicy) — ampliar conforme se documente
  Providers/AppServiceProvider.php  # Rate limiters, View::composer, Gates, Carbon locale

resources/
  views/
    layouts/                        # 5 layouts
    components/public/              # 11 componentes + 8 secciones del homepage
    admin/                          # 65+ vistas del panel
    public/                         # 15+ vistas públicas
    portal/                         # Portal de clientes
    auth/                           # Vistas de login y reset

routes/
  web.php                           # ~230 declaraciones de rutas (sitio público + CRM admin)
  portal.php                        # Rutas del Portal del Cliente (subdominio miportal.*)
  console.php                       # Scheduler con jobs (incl. cobranza, renovaciones)

database/
  migrations/                       # 164 migraciones, ordenadas por fecha
  seeders/                          # 8 seeders

public/
  vendor/tinymce/                   # TinyMCE 8.3.2 (no tocar a mano)

storage/app/public/                 # Disco "public" — symlink a public/storage
  avatars/ logos/ brokers/ clients/ properties/ posts/
  cms-images/ email-images/ email-assets/ media/ documents/ contracts/
  qr-codes/

.claude/                            # Documentación viva del esquema y arquitectura
  DATABASE_SCHEMA.md
  SCHEMA_QUICK_REFERENCE.md
  ARCHITECTURE_ANALYSIS.md
  SYSTEM_DOCUMENTATION_INDEX.md

docs/                               # Este manual + manual de marca/operaciones/roadmap
```

**Regla rápida:** todo lo que toca el sitio **público** vive bajo `resources/views/public/`, `resources/views/components/public/` o `resources/views/layouts/public.blade.php`. Todo lo que toca el **CRM** vive bajo `resources/views/admin/` y se enlaza con `layouts/app-sidebar.blade.php`.

---

## 3. Cómo agregar una página nueva (CMS o estática)

El sitio tiene **dos rutas** para crear páginas. Usa la que aplique:

### 3a. Página CMS (recomendado para contenido editable por el equipo)

Las páginas CMS viven en la tabla `pages` y se editan desde el admin (`/admin/pages`). El navbar las lee dinámicamente de `menus` + `menu_items`.

**Pasos:**

1. Login en `/admin` → Páginas → Nueva.
2. Llenar:
   - `title`, `slug` (sin `/`, ej: `comprar`).
   - `nav_label` (texto que se ve en el menú).
   - `nav_url` o `nav_route` (ruta destino, ej: `/comprar`).
   - `nav_style` (clases CSS opcionales).
   - `show_in_nav` = sí, `is_published` = sí, `nav_order` (entero).
   - `body` con TinyMCE para el contenido.
   - SEO: meta title, meta description, OG image (Media Library).
3. Guardar y verificar que aparece en el navbar (recarga la home).

**Cuándo usar este flujo:**
- Páginas que el equipo de marketing modificará sin tocar código.
- Páginas con contenido sencillo (texto largo, embeds, imágenes).

### 3b. Página estática con controlador y vista (recomendado para páginas con lógica)

Para páginas que requieren un controlador (formularios complejos, queries, condicionales):

1. Crear vista: `resources/views/public/{slug}.blade.php` extendiendo `layouts/public.blade.php`.
2. Agregar ruta en `routes/web.php` (sección "Rutas Públicas"):
   ```php
   Route::get('/comprar', [PublicController::class, 'comprar'])->name('public.comprar');
   ```
3. Agregar método en el controlador:
   ```php
   public function comprar()
   {
       return view('public.comprar', [
           'colonias' => MarketColonia::active()->get(),
       ]);
   }
   ```
4. Para que aparezca en el menú, crear el `MenuItem` correspondiente desde `/admin/menus`.
5. Actualizar `seo-meta.blade.php` con `<x-public.seo-meta />` en la vista para meta tags.

**Cuándo usar este flujo:**
- Landings con formularios estructurados (`/comprar`, `/desarrolladores-e-inversionistas`).
- Páginas que dependen de datos de DB (catálogo, observatorio de precios).
- Páginas con interacción Alpine.js compleja.

### Pruebas obligatorias para una página nueva

- Carga sin errores en local y producción.
- Aparece en el navbar (si así se configuró).
- Meta tags y JSON-LD presentes (ver `<head>` con DevTools).
- Mobile: probar hamburguesa, scroll, formularios.
- Lighthouse desktop > 85.

---

## 4. Cómo agregar/modificar un formulario público

El sitio tiene tres mecanismos para formularios. Elegir el que aplique:

### 4a. Formulario embebido (controlador + view)

Usado en `/contacto` y `/vende-tu-propiedad`. Patrón:

1. View con `<form>` Blade + Alpine.js para validación reactiva + honeypot escondido.
2. Controlador valida con `Request` + `validate()`.
3. Persiste en `ContactSubmission` (formulario genérico) o crea `Client` + `Operation` (formulario calificado tipo vendedor/comprador/desarrollador).
4. Dispara email al lead (transaccional) y notificación interna al equipo (vía `Notification` model).
5. Redirige a la misma página con flash de éxito o muestra estado inline con Alpine.

**Honeypot anti-spam (obligatorio en cualquier formulario público):**

```blade
<input type="text" name="website" style="position:absolute;left:-9999px" tabindex="-1" autocomplete="off" />
```

En el controlador:

```php
if ($request->filled('website')) abort(422); // bot detected
```

### 4b. Form Builder dinámico (formularios creados desde admin)

El sitio tiene `Form` + `FormSubmission` con builder visual. El admin crea formularios en `/admin/forms`, define campos, valida, y los expone en `/form/{slug}`.

**Cuándo usar:** encuestas, formularios temporales, captación experimental que el equipo de marketing quiere armar sin pedir cambio de código.

**Cómo agregar un campo nuevo al builder:** ver `app/Http/Controllers/Admin/FormController.php` y la vista `admin/forms/builder.blade.php`. Soporta texto, select, checkbox, radio, textarea, email, teléfono, fecha.

### 4c. Chatbot calificador (lead-popup.blade.php)

Es un componente Alpine.js que aparece a los 25 segundos en el sitio público. Pregunta perfil (Propietario / Comprador / Desarrollador), captura email y crea un `NewsletterSubscriber` con segmentación.

**Modificarlo:** editar `resources/views/components/public/lead-popup.blade.php`. Las preguntas, opciones y endpoints están dentro del componente. Cualquier cambio aquí impacta a todos los visitantes — probar a fondo en staging.

### Mapeo lead → CRM

Independientemente del mecanismo, todo lead público debe llegar al CRM con:

- `client_type` o etiqueta clara (vendedor/comprador/desarrollador/general).
- `lead_source` (página de origen).
- UTM tracking (`utm_source`, `utm_medium`, `utm_campaign`).
- `referrer` (página previa).
- `lead_temperature` inicial (default `warm`; el formulario de B2B llega como `hot`).
- `assigned_user_id` por reglas de routing definidas en el Manual de Operaciones.

---

## 5. Cómo subir y publicar una propiedad

Las propiedades viven en la tabla `properties` (38 columnas) con relaciones a `property_photos`, `property_qr_codes`, `clients` (owner) y `brokers`.

### Flujo desde el CRM (uso normal)

1. Login en `/admin` → Propiedades → Nueva.
2. Datos básicos: título, descripción (TinyMCE), precio, m², recámaras, baños, estacionamientos.
3. Tipo (`house`, `apartment`, `land`, `commercial`, `development`) y operación (`venta`, `renta`, `venta_anticipada`).
4. Ubicación: colonia (referencia a `market_colonias`), dirección, geo (lat/long).
5. Asignar a `Broker` y/o `Client` (propietario).
6. Subir fotos: drag & drop hasta 20 archivos, optimiza con Intervention. Marcar la portada.
7. SEO: meta title, meta description, slug.
8. Status `active` para publicar.
9. Si requiere QR: botón "Generar QR" en la sidebar de la ficha (genera y guarda en `storage/app/public/qr-codes/properties/{id}/qr.png`).
10. Si se sincroniza con EasyBroker: botón "Publicar en EasyBroker" (verifica `EasyBrokerSetting`).

### Flujo programático (cargas masivas o integraciones)

```php
$property = Property::create([
    'title' => 'Departamento Del Valle',
    'price' => 6450000,
    'currency' => 'MXN',
    'property_type' => 'apartment',
    'operation_type' => 'venta',
    'bedrooms' => 2,
    'bathrooms' => 3,
    'area' => 165,
    'status' => 'active',
    // …
]);

$property->addMediaFromUrl($url)->toMediaCollection('photos');
```

**Ojo con la duplicación `property_photos` vs `property_images`** — está documentada como deuda técnica en `ARCHITECTURE_ANALYSIS.md`. Hasta que se consolide, escribir en `property_photos` (la primaria).

### Pruebas obligatorias

- La propiedad aparece en `/propiedades`.
- La ficha pública en `/propiedades/{id}/{slug}` carga galería, formulario de contacto, datos.
- El QR generado lleva al slug correcto.
- Si se publicó en EasyBroker, aparece sincronizada (revisar `easybroker_status`).

---

## 6. Cómo gestionar el menú y el footer

**Navbar y footer** se renderizan dinámicamente desde la base de datos:

- Tablas: `menus` (location: `header`, `footer`) y `menu_items` (con `parent_id` para sub-items).
- Editor: `/admin/menus`.
- Componentes Blade: `components/public/navbar.blade.php` y `components/public/footer.blade.php`.

**Para agregar un ítem al menú:**

1. `/admin/menus` → seleccionar `header` (o `footer`) → "Nuevo ítem".
2. Llenar `label`, `url` o `route`, `sort_order`, `parent_id` (si es sub-ítem), `is_active`.
3. Guardar. El menú se refleja inmediato (no requiere clear cache).

**Slogan en header y footer:** vive en `site_settings.tagline` (60+ campos en esa tabla). Editor: `/admin/settings`. El componente `navbar.blade.php` lee `SiteSetting::tagline()` y lo muestra en desktop ≥ 1024px.

---

## 7. Cómo modificar el chatbot calificador y el WhatsApp flotante

### Chatbot calificador (`lead-popup.blade.php`)

Componente Alpine.js. Aparece a los 25 segundos. Hoy pregunta:

1. ¿Eres propietario, comprador o desarrollador?
2. Email para enviarte info personalizada.

**Modificar la lógica:** editar `resources/views/components/public/lead-popup.blade.php`. La data de Alpine vive en `x-data="leadPopup()"` y la función está al final del componente.

**Endpoint donde envía:** `POST /newsletter/subscribe` → `NewsletterController@subscribe` → crea `NewsletterSubscriber` con `segment` definido por la respuesta.

**Cambiar tiempo de aparición:** variable `delay` dentro de `x-data` (default 25000 ms).

### WhatsApp flotante (`whatsapp-button.blade.php`)

- Número de destino: `site_settings.whatsapp_number`.
- Tiempo de burbuja: 20 segundos (configurable en el componente).
- Mensaje precargado: `?text=` URL-encoded.

**Recomendación pendiente (Roadmap fase 1):** mensaje precargado distinto por página (vendedor/comprador/desarrollador) usando una variable Blade que el componente lea según `request()->path()`.

---

## 8. Cómo crear o editar un post del blog

1. `/admin/blog/posts` → Nuevo.
2. Llenar: título, slug, excerpt, body (TinyMCE), categoría, tags, imagen destacada (Media Library).
3. SEO: meta title, meta description, OG image.
4. Status: `draft`, `scheduled`, `published`, `archived`.
5. Si `scheduled`, definir `published_at`. El job `PublishScheduledPosts` corre cada minuto vía cron y publica automáticamente.
6. Calendario de contenido: `/admin/blog/calendar` muestra posts en mes/semana/día con drag & drop.

**Body como HTML crudo:** el contenido se renderiza con `{!! $post->body !!}` (sin escape), porque viene de TinyMCE controlado por el equipo. **No** introducir HTML de fuentes externas en este campo sin sanitizar.

**Tipografía del blog:** plugin `@tailwindcss/typography` (`prose`). Si quieres ajustar, edita las clases en `resources/views/blog/show.blade.php`.

---

## 9. Cómo configurar SEO, OG image y tracking

### Meta tags y JSON-LD

Toda vista pública debe usar:

```blade
<x-public.seo-meta
    :title="$page->meta_title ?? config('app.name')"
    :description="$page->meta_description"
    :image="$page->og_image_url"
/>
<x-public.json-ld type="RealEstateAgent" />
```

Estos componentes leen de `SiteSetting` por default y permiten override por página.

### OG image

- Default global: `site_settings.og_image_default`.
- Por página: campo `og_image` en `pages` (CMS) o `meta_image` en `posts`.
- Si no hay imagen, se genera una dinámica con `Browsershot` apuntando a una vista privada (`/og-image/{type}/{id}`).

### Tracking (GTM, GA4, Facebook Pixel, scripts custom)

Configurable desde `/admin/settings/integrations`:

- `gtm_id` + toggle `gtm_enabled`.
- `ga4_measurement_id` + toggle.
- `facebook_pixel_id` + toggle.
- `head_script`, `body_script` (texto libre para cualquier integración).

Los scripts se inyectan automáticamente en `layouts/public.blade.php` si el toggle correspondiente está activo.

### sitemap.xml y robots.txt

- `sitemap.xml`: ruta `/sitemap.xml` → `SitemapController` (verifica que exista; si no, crear). Debe incluir todas las páginas con `is_published=true`, todos los posts publicados, todas las propiedades activas.
- `robots.txt`: archivo estático en `public/robots.txt`. Asegurar que `Disallow: /admin/` y `Disallow: /portal/`.

---

## 10. Cómo trabajar con el sistema de email

Home del Valle usa **PHPMailer + SMTP dinámico**, no Laravel Mail. Esto está en `EmailService.php`.

### Configurar SMTP

`/admin/email/settings` → llenar host, puerto, encriptación, usuario, password. Password se guarda encriptado en `email_settings.password_encrypted`.

### Templates editables

`/admin/email/templates` → CRUD con TinyMCE. Variables disponibles: `{{Nombre}}`, `{{Email}}`, `{{Telefono}}`, `{{Mensaje}}`, `{{Empresa}}`, `{{ResetLink}}`, `{{NewPassword}}`, etc. Cada template define su lista en `email_templates.available_variables` (JSON).

### Galería de assets de email

`/admin/email/assets`. Drag & drop. Las imágenes se sirven desde `storage/app/public/email-assets/` y se insertan al template con URL absoluta (necesario para que se vean en clientes de correo externos).

### Tracking de apertura

Cada email enviado a cliente registra `client_emails` con `tracking_id`. El template incluye un pixel `<img src="https://homedelvalle.mx/track/{tracking_id}.gif" />` que dispara el conteo en `EmailTrackingController`.

### SMTP por usuario

Cada usuario admin/agente puede configurar su propio SMTP en `/profile/email`, guardado en `user_mail_settings`. Se usa cuando el email "from" debe ser personal del agente.

---

## 11. Cómo modificar la apariencia (colores, tipografías, CSS variables)

### Sitio público (Tailwind 4)

Tokens definidos en `resources/css/app.css` con sintaxis Tailwind 4. La paleta de Home del Valle es **navy institucional + neutros + verde de sistema**. No usamos dorado, cobre ni acentos cálidos.

```css
@import "tailwindcss";

@theme {
  /* === Marca: navy === */
  --color-navy-950: #0A1A2F;  /* hero deep */
  --color-navy-900: #1F3A5F;  /* institucional (logo, headers, botones primarios) */
  --color-navy-700: #1E1B4B;  /* sidebar del CRM */

  /* === UI funcional === */
  --color-blue-500: #3B82F6;   /* links, botones secundarios */
  --color-text:     #0F172A;
  --color-muted:    #64748B;
  --color-border:   #E2E8F0;
  --color-surface:  #F1F5F9;

  /* === Estados === */
  --color-success: #16A34A;   /* pills, checkmarks, badge "Exclusiva" */
  --color-error:   #DC2626;
  --color-warning: #D97706;

  /* === Tipografía === */
  --font-sans: "Inter", system-ui, sans-serif;
}
```

**Reglas de uso:**

- El color dominante en toda pieza es navy (`navy-900` o `navy-950`).
- El verde se reserva para estados positivos y señales de calidad (boutique, exclusiva, success). NUNCA como acento decorativo ni fondo amplio.
- El azul medio (`blue-500`) es funcional: links y botones secundarios. No se usa en headings ni como color de marca.
- Prohibido en cualquier pieza con marca Home del Valle: dorado, cobre, mostaza, naranja, púrpura saturado.
- Cualquier color nuevo se agrega como token aquí, no como clase ad-hoc.

### CRM (CSS puro con variables)

Los colores del panel admin viven en `:root` de la hoja base del CRM (`resources/css/admin.css` o equivalente). Se pueden sobrescribir desde `site_settings.theme_*`. El sidebar default es `#1e1b4b` con texto blanco.

**Para cambiar el color principal del CRM globalmente:** `/admin/settings` → "Apariencia" → "Color primario". El cambio se persiste en `site_settings.primary_color` y se inyecta como `--color-primary` en el `<head>`.

### Tipografía

Inter en todo el sitio, cargada desde `fonts.bunny.net`. Para cambiar a otra: editar `<link>` en `layouts/public.blade.php` y el token `--font-sans` en CSS.

---

## 12. Cómo crear una landing page nueva

Para landings tipo `/vende-tu-propiedad` o las nuevas `/comprar` y `/desarrolladores-e-inversionistas`:

1. Crear vista en `resources/views/public/landings/{slug}.blade.php` extendiendo `layouts/landing.blade.php`.
2. Crear método en `LandingController` (`app/Http/Controllers/LandingController.php`) que devuelva la vista con datos.
3. Registrar ruta:
   ```php
   Route::get('/comprar', [LandingController::class, 'comprar'])->name('landing.comprar');
   ```
4. Para que aparezca en el menú, crear `MenuItem` desde `/admin/menus`.
5. Componer el copy según el `01-MANUAL-MARCA-Y-VOZ.docx`. NUNCA copiar copy de otra inmobiliaria.
6. Si la landing tiene formulario, ver sección 4 y mapear campos contra el `client_type` correcto.
7. Asegurar:
   - SEO meta tags vía `<x-public.seo-meta>`.
   - JSON-LD apropiado (Service, Product, RealEstateAgent según contenido).
   - Honeypot en el formulario.
   - Microcopy de éxito post-submit (no recargar página, mostrar mensaje en pantalla con Alpine).
8. Probar mobile-first.

---

## 13. Cómo integrar un nuevo formulario al pipeline de operaciones

Cuando un formulario público crea un lead que debe entrar al pipeline (no sólo a la lista de contactos):

### Mapping recomendado

| Tipo de lead | Acción al recibir formulario | Tablas afectadas |
|---|---|---|
| **Vendedor residencial** (`/vende-tu-propiedad`) | Crear `Client` (con `client_type=owner`, `lead_temperature=warm`), crear `Operation` (`type=captacion`, `stage=inquiry`, `status=active`) | `clients`, `operations`, `operation_stage_logs` |
| **Comprador residencial** (`/comprar`) | Crear `Client` (`client_type=buyer`, `budget_min/max`, `property_type`, `lead_temperature=warm`), NO crear operación todavía. La operación se crea cuando hay matching de propiedad. | `clients`, `lead_events` |
| **Desarrollador / inversionista** (`/desarrolladores-e-inversionistas`) | Crear `Client` (`client_type=investor`, `lead_temperature=hot`, `assigned_user_id` = dirección general), crear nota interna de prioridad. | `clients`, `operation_comments` |
| **Contacto general** (`/contacto`) | Crear `ContactSubmission` con UTM. Si el campo "¿En qué te ayudamos?" indica intención clara, también crear `Client` con la etiqueta correspondiente. | `contact_submissions`, opcional `clients` |

### Ejemplo de controlador para `/comprar`

```php
public function storeBuyerSearch(Request $request)
{
    $data = $request->validate([
        'tipo_inmueble' => 'required|array|min:1',
        'zonas' => 'required|array|min:1',
        'recamaras' => 'required|in:1,2,3,4+',
        'presupuesto' => 'required|in:hasta_4m,4m_6m,6m_9m,9m_14m,14m_plus',
        'pago' => 'required|in:contado,credito,infonavit,fovissste,mixto',
        'timing' => 'required|in:inmediato,1_3m,3_6m,explorando',
        'must_have' => 'nullable|string|max:280',
        'nombre' => 'required|string|max:120',
        'email' => 'required|email',
        'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
        'aviso' => 'accepted',
        'website' => 'nullable|max:0', // honeypot
    ]);

    $budgets = [
        'hasta_4m' => [0, 4_000_000],
        '4m_6m' => [4_000_000, 6_000_000],
        '6m_9m' => [6_000_000, 9_000_000],
        '9m_14m' => [9_000_000, 14_000_000],
        '14m_plus' => [14_000_000, null],
    ];

    $client = Client::create([
        'name' => $data['nombre'],
        'email' => $data['email'],
        'phone' => $data['whatsapp'],
        'client_type' => 'buyer',
        'budget_min' => $budgets[$data['presupuesto']][0],
        'budget_max' => $budgets[$data['presupuesto']][1],
        'property_type' => implode(',', $data['tipo_inmueble']),
        'lead_temperature' => 'warm',
        'lead_source' => '/comprar',
        'utm_source' => $request->input('utm_source'),
        'utm_medium' => $request->input('utm_medium'),
        'utm_campaign' => $request->input('utm_campaign'),
        'metadata' => [
            'zonas' => $data['zonas'],
            'recamaras' => $data['recamaras'],
            'pago' => $data['pago'],
            'timing' => $data['timing'],
            'must_have' => $data['must_have'],
        ],
    ]);

    LeadEvent::create([
        'client_id' => $client->id,
        'type' => 'form_submit',
        'source' => '/comprar',
        'weight' => 15,
        'data' => $data,
    ]);

    // Email transaccional al lead + notificación interna al equipo
    EmailService::sendTemplate('lead_buyer_received', $client->email, [
        'Nombre' => $client->name,
    ]);

    Notification::create([
        'type' => 'new_lead',
        'title' => 'Nuevo lead comprador',
        'body' => "{$client->name} busca en " . implode(', ', $data['zonas']),
        'data' => ['client_id' => $client->id],
    ]);

    return back()->with('success', 'Recibimos tu brief. Te contactamos en menos de 72 horas.');
}
```

### Verificación

- El lead aparece en `/admin/clients` con el tag correcto.
- Lead scoring se actualiza (ver `lead_scores` después del próximo job diario).
- El agente asignado recibe notificación in-app.
- El email transaccional llega al lead.
- El campo `lead_source` permite atribución posterior.

---

## 14. Cómo deployar a producción

Producción está en cPanel compartido. **Consulta `DEPLOYMENT_GUIDE.md` para el procedimiento detallado.** Resumen:

```bash
ssh user@servidor
cd ~/repositories/homedelvalle
git pull origin main
bash cpanel-deploy.sh
```

`cpanel-deploy.sh` ejecuta: `composer install --no-dev`, `php artisan migrate --force`, `config:cache`, `route:cache`, `view:clear`, `storage:link`, `npm install && npm run build`.

**Post-deploy:**

- Revisar `storage/logs/laravel.log` (últimas 100 líneas).
- Probar homepage, login, una propiedad, `/contacto`.
- Si hay nuevas migraciones, validar columnas en MySQL.

**Rollback:**

```bash
git revert HEAD
git push origin main
ssh ... && bash cpanel-deploy.sh
```

---

## 15. Patrones a evitar y errores comunes

- **No** uses `ShouldQueue` en jobs — cPanel no tiene queue worker. Todos los jobs corren síncronos vía `schedule:run`.
- **No** caches objetos Eloquent (`Cache::put('user', $user)`). Caches sólo arrays o IDs. Recuperación con `__PHP_Incomplete_Class` ha tomado horas en el pasado.
- **No** uses `@json($complexCallback)` con arrow functions. Prepara la data en el controlador.
- **No** edites `public/vendor/tinymce/` a mano. Si subes versión, copia desde `node_modules/tinymce/`.
- **No** dejes `dd()` ni `dump()` en producción.
- **No** subas claves API o passwords al repo. Usa `.env` y `site_settings` (encriptado).
- **No** dupliques el slogan a mano en cada vista — léelo de `SiteSetting::tagline()`.
- **No** uses `<a href="/admin">` desde el sitio público — usa `route('admin.dashboard')` para que respete prefijos.
- **No** guardes `password` en `clients` ni `users` en plaintext. Bcrypt obligado.
- **No** modifiques migraciones existentes. Crea una nueva migración para `ALTER`.

---

## 16. Convenciones de git y nombrado

### Branches

- `main` — producción.
- `staging` — pre-producción (cuando exista).
- Feature branches: `feature/{slug-corto}`. Ej: `feature/landing-comprar`.
- Bug branches: `fix/{issue-corto}`. Ej: `fix/qr-regenerate`.

### Commits

- Formato: `tipo: mensaje corto en español`.
- Tipos: `feat`, `fix`, `chore`, `docs`, `refactor`, `style`, `test`.
- Ej: `feat: agregar landing /comprar con brief estructurado`.

### Naming

- Modelos: PascalCase singular (`Property`, `Operation`).
- Tablas: snake_case plural (`properties`, `operations`).
- Rutas con nombre: `seccion.accion` (`public.contact`, `admin.properties.index`, `portal.dashboard`).
- Vistas Blade: kebab-case (`vende-tu-propiedad.blade.php`).
- Controladores: PascalCase + `Controller` (`PropertyController`, `Admin\PageController`, `Portal\DashboardController`).

---

## 17. Portal del Cliente — qué considerar al construir cualquier feature

> Documento de referencia completo: [`06-PORTAL-DEL-CLIENTE.md`](./06-PORTAL-DEL-CLIENTE.md). Esta sección lista lo que **cualquier feature nueva** debe contemplar para que el portal siga siendo coherente con el resto del sistema.

El portal del cliente (`miportal.homedelvalle.mx`) es la pieza central de cara al cliente. Toda la lógica de negocio que generes en el sitio público o en el CRM admin **tiene que pensar en cómo se refleja en el portal**. La regla operativa es simple: si un cliente debería verlo, el portal lo muestra.

### 17.1 Cuándo se crea automáticamente la cuenta de portal

| Trigger | Perfil que se activa |
|---|---|
| `Operation type='captacion'` pasa a `agreement_signed` | Propietario |
| Contrato de arrendamiento firmado | Inquilino |
| `Operation type='venta'` pasa a `offer_presented` o `signed` | Comprador y/o vendedor |

La creación pasa por `ClientPortalService::createAccount(Client $client)` y dispara email transaccional `portal_welcome`. Si vas a implementar un nuevo tipo de operación o de relación cliente-HDV, agregar el listener correspondiente.

### 17.2 Cuándo se debe notificar al portal

Cualquier evento operativo que el cliente debería saber genera una entrada en `notifications` con `portal_visible=true`. Eventos típicos:

- Cambio de stage en operación.
- Subida de documento por HDV (contrato, propuesta, recibo).
- Pago recibido o pendiente (renta).
- Mensaje de HDV.
- Visita agendada.
- Renovación próxima (60 días antes).
- Reporte mensual disponible.

Si construyes una feature nueva que cambia el estado visible al cliente, **debe disparar notificación al portal**, no sólo email.

### 17.3 Documentos: regla de oro

Cualquier `Document` que se genere dentro de una operación con un cliente identificado **debe ser visible para ese cliente en el portal**, salvo excepción explícita marcada con `documents.portal_visible=false`.

Esto cambia el flujo de generación de contratos, recibos, reportes mensuales y cualquier PDF: en el momento de guardar el `Document`, también se persiste con flag de visibilidad y `client_id` correctamente vinculado.

### 17.4 Mensajes: el thread es el canal primario

Toda comunicación HDV ↔ cliente que no sea WhatsApp o llamada se centraliza en el `MessageThread` del cliente. El admin tiene UI para responder; el cliente la ve dentro del portal. Si construyes un email transaccional nuevo, considera si la respuesta debería entrar al thread (la mayoría sí).

### 17.5 Privacidad entre partes

Cuando una feature involucra a más de un cliente (típicamente: propietario e inquilino en la misma operación de renta), respeta las reglas de privacidad documentadas en `06-PORTAL-DEL-CLIENTE.md` sección 9.3:

- Inquilino no ve datos personales completos del propietario y viceversa.
- Toda comunicación pasa por HDV.
- Excepción: contacto de emergencia si hay autorización explícita.

Esto se traduce en: **policies a nivel de modelo** (`PortalPolicy::viewProperty()`, `PortalPolicy::viewRental()`), y en **scopes de query** que filtran por `client_id`.

### 17.6 Vista "preview as client"

Toda nueva vista del portal debe ser navegable cuando un admin entra como impersonator. El banner amarillo de impersonación debe ser visible y la escritura (subida de docs, envío de mensajes, pagos) debe estar **deshabilitada** durante la sesión impersonada.

### 17.7 Integración en cada feature nueva

Antes de declarar terminada cualquier feature que toque a clientes, responder estas 5 preguntas:

1. ¿El cliente debería ver esto en el portal?
2. Si sí, ¿en qué sección del portal? (Dashboard, Mi renta, Documentos, Mensajes, etc.)
3. ¿Qué notificación se dispara y por qué canal? (in-portal, email, WhatsApp)
4. ¿Hay datos sensibles a filtrar entre las partes?
5. ¿La vista funciona en mobile y bajo impersonación?

Si alguna respuesta es ambigua, consulta `06-PORTAL-DEL-CLIENTE.md` o pregunta antes de implementar.

### 17.8 Email transaccional: incluir link al portal

Toda plantilla de email enviada a un cliente con cuenta activa debe incluir un CTA al portal:

```
[CTA primario]
   Entra a tu portal: miportal.homedelvalle.mx
```

Esto refuerza el hábito y reduce dependencia del email para encontrar información histórica.

---

**Próximas revisiones:**

- Cuando se documente un módulo nuevo, agregarlo aquí con su sección numerada.
- Cuando se haga refactor mayor (split de `operations`, consolidación de `property_photos`), actualizar las secciones afectadas.
- Cuando cambie el stack (Laravel 14, Tailwind 5), actualizar la sección 1.

**Mantenedor:** Alex (Director de Estrategia y Crecimiento).
**Revisión sugerida:** trimestral.
