# CONTEXTO DEL PROYECTO — Home del Valle CRM Inmobiliario

> Documento de referencia para continuar el desarrollo. Ultima actualizacion: 2026-03-31.

---

## 1. Resumen General

**Home del Valle** es una plataforma CRM inmobiliaria + sitio web publico construida con Laravel. Combina:

- **CRM interno:** Gestion de propiedades, clientes, brokers, deals, tareas, finanzas y marketing
- **Sitio publico:** Homepage, propiedades, nosotros, blog, contacto, landing pages para captacion de leads
- **Panel de administracion:** Sidebar moderno (estilo HubSpot/Salesforce) con CMS, email marketing, automatizaciones, analytics y finanzas
- **Sistema de email:** Templates editables (WYSIWYG), galeria de assets, configuracion SMTP dinamica
- **Seguridad:** Autenticacion manual con rate limiting, recuperacion de contrasena con tokens seguros

---

## 2. Tecnologias

| Componente       | Tecnologia                          |
|------------------|-------------------------------------|
| Backend          | Laravel 13.2.0                      |
| PHP              | 8.5.4                               |
| Base de datos    | SQLite (local)                      |
| Frontend CRM     | Blade + CSS puro (variables CSS)    |
| Frontend Publico | Blade + Tailwind CSS 4.0            |
| JS Framework     | Alpine.js (CDN, sitio publico)      |
| Build            | Vite 8.0                            |
| Fuente           | Inter (panel + publico)             |
| Autenticacion    | Manual (Auth::attempt, sin Breeze)  |
| Email            | PHPMailer 7.x (SMTP dinamico desde DB) |
| Editor WYSIWYG   | TinyMCE 8.3.2 (self-hosted GPL)    |
| Almacenamiento   | Laravel Storage disk `public`       |
| Servidor dev     | `php artisan serve` (127.0.0.1:8000)|

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
  navbar.blade.php                 # Navegacion dinamica desde DB (tabla pages)
  footer.blade.php                 # Footer con info de contacto desde SiteSetting
  hero.blade.php                   # Hero section con buscador
  property-card.blade.php          # Card de propiedad reutilizable
  contact-form.blade.php           # Formulario de contacto con honeypot anti-spam
  whatsapp-button.blade.php        # Boton flotante de WhatsApp
  seo-meta.blade.php               # Meta tags SEO dinamicos
  json-ld.blade.php                # Schema.org structured data
  breadcrumbs.blade.php            # Breadcrumbs SEO

CSS: Variables CSS en :root con colores dinamicos desde base de datos (SiteSetting)
Email: PHPMailer con configuracion SMTP desde tabla email_settings (password encriptado)
Templates email: Variables {{Nombre}}, {{Email}}, etc. reemplazadas al enviar
Cache: Base de datos (tabla `cache`), modelos almacenan arrays (no objetos) para evitar __PHP_Incomplete_Class
```

### Flujo de autenticacion
1. Login manual con `Auth::attempt()` en `LoginController` (rate limited: 5/min)
2. Post-login redirige a `admin.dashboard` (panel con sidebar) para todos los usuarios
3. Registro crea usuario con `role=user` por defecto
4. Middleware por alias protege rutas segun rol
5. Jerarquia: admin > editor > viewer > broker > user
6. Recuperacion de contrasena via email con token seguro (30 min expiracion)

### Flujo publico (visitantes no autenticados)
1. `/` muestra homepage publica con propiedades destacadas, blog preview, etc.
2. Usuarios autenticados en `/` son redirigidos a `admin.dashboard`
3. Navegacion del sitio publico es dinamica desde tabla `pages`
4. Formularios de contacto crean `ContactSubmission` con tracking UTM

---

## 4. Estructura de Carpetas (archivos principales)

```
app/
  Http/
    Controllers/
      Admin/
        AnalyticsController.php        # Dashboard de analiticas
        AutomationController.php       # CRUD reglas de automatizacion + logs + toggle
        BrokerManagementController.php # Aprobar/revocar brokers
        DashboardController.php        # Panel admin con estadisticas
        EasyBrokerSettingsController.php # Integracion EasyBroker API
        EmailAssetController.php       # CRUD galeria de imagenes para email
        EmailSettingsController.php    # Config SMTP + test conexion + envio prueba
        EmailTemplateController.php    # CRUD templates email + WYSIWYG + envio prueba
        FinanceController.php          # Dashboard financiero, transacciones, comisiones
        HomepageController.php         # CMS: editar secciones del homepage publico
        MarketingController.php        # Dashboard marketing, canales, campanas
        PageController.php             # CRUD paginas + campos de navegacion
        PostController.php             # CRUD posts del blog
        SettingsController.php         # Configuracion del sitio (logo, colores, nombre)
        UserAdminController.php        # CRUD usuarios + avatar + permisos + roles
      Auth/
        ForgotPasswordController.php   # Solicitar reset de contrasena
        LoginController.php            # Login/logout (redirige a admin.dashboard)
        RegisterController.php         # Registro publico
        ResetPasswordController.php    # Validar token y cambiar contrasena
      BlogController.php               # Blog publico (index, show, page)
      BrokerController.php             # CRUD brokers (entidad standalone)
      ClientController.php             # CRUD clientes
      DealController.php               # CRUD deals (pipeline de ventas)
      HomeController.php               # Homepage publico / redirect autenticados a admin
      LandingController.php            # Landing pages de captacion de leads
      ProfileController.php            # Perfil del usuario autenticado
      PropertyController.php           # CRUD propiedades + publicar en EasyBroker
      PublicController.php             # Paginas publicas: propiedades, nosotros, contacto
      TaskController.php               # CRUD tareas vinculadas a deals/clientes/propiedades
    Middleware/
      CheckAdminRole.php               # Solo admin
      CheckBrokerRole.php              # admin + broker
      CheckEditorRole.php              # admin + editor
      CheckViewerRole.php              # admin + editor + viewer
  Models/ (27 modelos)
    User.php                           # Usuarios con roles y permisos
    Property.php                       # Propiedades inmobiliarias (scope: available)
    Client.php                         # Clientes (belongsTo Broker)
    Broker.php                         # Brokers (hasMany Client)
    Deal.php                           # Pipeline de ventas (stages, amounts, close dates)
    Task.php                           # Tareas vinculadas a deals/clientes/propiedades
    Transaction.php                    # Transacciones financieras
    Commission.php                     # Comisiones de brokers
    Interaction.php                    # Interacciones con clientes
    Post.php                           # Posts del blog (scope: published)
    PostCategory.php                   # Categorias de posts
    Tag.php                            # Tags (many-to-many con posts via post_tag)
    Page.php                           # Paginas + navegacion dinamica (scopes: published, inNav)
    ContactSubmission.php              # Leads de formularios (tracking UTM)
    SiteSetting.php                    # Configuracion global + CMS secciones homepage
    EmailSetting.php                   # Config SMTP (password encriptado)
    EmailTemplate.php                  # Templates con render() de variables
    EmailAsset.php                     # Imagenes para templates (url, human_size)
    PasswordResetToken.php             # Tokens de reset (generateToken, findValidToken, markAsUsed)
    EasyBrokerSetting.php              # Config integracion EasyBroker
    MarketingChannel.php               # Canales de marketing
    MarketingCampaign.php              # Campanas de marketing
    AutomationRule.php                 # Reglas de automatizacion (trigger, conditions, action)
    AutomationLog.php                  # Logs de ejecucion de automatizaciones
    ExpenseCategory.php                # Categorias de gastos
    BrokerPhoto.php                    # (sin implementar)
    PropertyPhoto.php                  # (sin implementar)
  Providers/
    AppServiceProvider.php             # Rate limiters + View::composer global (siteSettings + navItems)
  Services/
    EmailService.php                   # send(), sendTemplate(), sendWelcomeEmail(), testConnection()

resources/views/
  layouts/
    app-sidebar.blade.php              # Layout CRM con sidebar
    public.blade.php                   # Layout sitio publico (Tailwind + Alpine)
    landing.blade.php                  # Layout landing pages
    app.blade.php                      # Layout basico (legacy)
  components/public/
    navbar.blade.php                   # Menu dinamico desde DB (3 estilos: link, button, muted)
    footer.blade.php                   # Footer con contacto + redes desde SiteSetting
    hero.blade.php                     # Hero section
    property-card.blade.php            # Card de propiedad
    contact-form.blade.php             # Formulario de contacto con honeypot
    whatsapp-button.blade.php          # Boton flotante WhatsApp
    seo-meta.blade.php                 # Meta tags SEO
    json-ld.blade.php                  # Schema.org JSON-LD
    breadcrumbs.blade.php              # Breadcrumbs
  public/
    home.blade.php                     # Homepage publico
    propiedades.blade.php              # Listado de propiedades
    propiedad.blade.php                # Detalle de propiedad
    nosotros.blade.php                 # Pagina nosotros
    contacto.blade.php                 # Pagina de contacto
    landing.blade.php                  # Landing page de captacion
  admin/
    dashboard.blade.php                # Panel de control admin
    settings.blade.php                 # Configuracion del sitio
    homepage.blade.php                 # CMS editor del homepage publico
    brokers.blade.php                  # Gestion de brokers
    layout.blade.php                   # (legacy, sin usar)
    analytics/
      index.blade.php                  # Dashboard de analiticas
    automations/
      index.blade.php                  # Lista de automatizaciones
      create.blade.php                 # Crear automatizacion
      edit.blade.php                   # Editar automatizacion
      logs.blade.php                   # Logs de automatizaciones
    easybroker/
      settings.blade.php              # Config EasyBroker
    email/
      settings.blade.php              # Config SMTP
      assets.blade.php                # Galeria de imagenes
      templates/
        index.blade.php               # Lista templates
        create.blade.php              # Editor WYSIWYG
        edit.blade.php                # Editor WYSIWYG
    finance/
      dashboard.blade.php             # Dashboard financiero
      transactions.blade.php          # Lista transacciones
      transaction-form.blade.php      # Crear/editar transaccion
      commissions.blade.php           # Lista comisiones
    marketing/
      dashboard.blade.php             # Dashboard marketing
      channels.blade.php              # Canales de marketing
      campaigns.blade.php             # Campanas
      campaign-form.blade.php         # Crear/editar campana
    pages/
      index.blade.php                 # Lista de paginas
      create.blade.php                # Crear pagina + campos navegacion
      edit.blade.php                  # Editar pagina + campos navegacion
      _nav-fields.blade.php           # Partial: campos de navegacion reutilizable
    posts/
      index.blade.php                 # Lista de posts
      create.blade.php                # Crear post
      edit.blade.php                  # Editar post
    users/
      index.blade.php                 # Lista de usuarios
      show.blade.php                  # Detalle de usuario
      edit.blade.php                  # Editar usuario
      create.blade.php                # Crear usuario
      permissions.blade.php           # Permisos
  auth/
    login.blade.php                   # Login
    register.blade.php                # Registro
    forgot-password.blade.php         # Solicitar reset
    reset-password.blade.php          # Nueva contrasena
  blog/
    index.blade.php                   # Blog publico
    show.blade.php                    # Post individual
    page.blade.php                    # Pagina estatica (/p/{slug})
  brokers/
    index.blade.php, create.blade.php, edit.blade.php, show.blade.php
  clients/
    index.blade.php, create.blade.php, edit.blade.php
  properties/
    index.blade.php, create.blade.php, edit.blade.php, show.blade.php
  deals/
    index.blade.php, create.blade.php, edit.blade.php
  tasks/
    index.blade.php, create.blade.php, edit.blade.php
  home/
    dashboard.blade.php               # Dashboard post-login (legacy, redirige a admin)
  profile/
    index.blade.php                   # Perfil con avatar + cambio contrasena

routes/
  web.php                             # 151 rutas totales

database/
  migrations/                         # 50 archivos de migracion
  seeders/
    DatabaseSeeder.php
    EmailTemplateSeeder.php           # BienvenidaUsuario + RecuperarPassword + PasswordCambiado
    MarketingChannelSeeder.php        # Canales de marketing predeterminados
    NavPageSeeder.php                 # 6 paginas de navegacion: Inicio, Propiedades, Nosotros, Blog, Contacto, Office
    TestDataSeeder.php

public/
  vendor/
    tinymce/                          # TinyMCE 8.3.2 self-hosted GPL
```

---

## 5. Base de Datos (SQLite)

### Tablas activas: 36
`users`, `properties`, `clients`, `brokers`, `deals`, `tasks`, `transactions`, `commissions`, `interactions`, `posts`, `post_categories`, `tags`, `post_tag`, `pages`, `contact_submissions`, `site_settings`, `email_settings`, `email_templates`, `email_assets`, `easybroker_settings`, `marketing_channels`, `marketing_campaigns`, `automation_rules`, `automation_logs`, `expense_categories`, `custom_password_resets`, `broker_photos`, `property_photos`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`, `password_reset_tokens`

### Tabla: `users`
| Columna            | Tipo                | Notas                                    |
|--------------------|---------------------|------------------------------------------|
| id                 | bigint (PK)         | Auto-increment                           |
| name               | string              |                                          |
| last_name          | string (nullable)   |                                          |
| email              | string (unique)     |                                          |
| email_verified_at  | timestamp (nullable)|                                          |
| password           | string              | Cast: hashed (bcrypt automatico)         |
| role               | enum                | user, broker, admin, editor, viewer      |
| phone              | string (nullable)   |                                          |
| whatsapp           | string (nullable)   |                                          |
| address            | text (nullable)     |                                          |
| avatar_path        | string (nullable)   | Ruta en storage/app/public/avatars/      |
| can_read           | boolean             | Default: true                            |
| can_edit           | boolean             | Default: false                           |
| can_delete         | boolean             | Default: false                           |
| remember_token     | string (nullable)   |                                          |
| timestamps         |                     |                                          |

### Tabla: `properties`
| Columna     | Tipo               | Notas                            |
|-------------|--------------------|---------------------------------|
| id          | bigint (PK)        |                                 |
| title       | string             |                                 |
| description | text (nullable)    |                                 |
| price       | decimal(12,2)      |                                 |
| city        | string             |                                 |
| colony      | string             |                                 |
| address     | string (nullable)  |                                 |
| zipcode     | string (nullable)  |                                 |
| area        | decimal(8,2)       |                                 |
| parking     | integer (nullable) |                                 |
| status      | string             | Default: 'available'            |
| bedrooms    | integer            |                                 |
| bathrooms   | integer            |                                 |
| photo       | string (nullable)  | Ruta en storage/app/public/properties/ |
| timestamps  |                    |                                 |

### Tabla: `clients`
| Columna       | Tipo              | Notas                            |
|---------------|-------------------|---------------------------------|
| id            | bigint (PK)       |                                 |
| name          | string            |                                 |
| email         | string (unique)   |                                 |
| phone         | string (nullable) |                                 |
| address       | string (nullable) |                                 |
| city          | string (nullable) |                                 |
| budget_min    | decimal(12,2)     |                                 |
| budget_max    | decimal(12,2)     |                                 |
| property_type | string (nullable) |                                 |
| broker_id     | bigint (FK)       | Relacion con brokers            |
| photo         | string (nullable) | Ruta en storage/app/public/clients/ |
| timestamps    |                   |                                 |

### Tabla: `brokers`
| Columna         | Tipo               | Notas                           |
|-----------------|--------------------|---------------------------------|
| id              | bigint (PK)        |                                 |
| name            | string             |                                 |
| email           | string (unique)    |                                 |
| phone           | string (nullable)  |                                 |
| license_number  | string (unique, nullable) |                          |
| commission_rate | decimal(5,2)       |                                 |
| company_name    | string (nullable)  |                                 |
| bio             | text (nullable)    |                                 |
| status          | enum               | active, inactive (default)      |
| photo           | string (nullable)  | Ruta en storage/app/public/brokers/ |
| timestamps      |                    |                                 |

### Tabla: `deals`
| Columna            | Tipo              | Notas                          |
|--------------------|-------------------|--------------------------------|
| id                 | bigint (PK)       |                                |
| property_id        | bigint (FK)       | Propiedad del deal             |
| client_id          | bigint (FK)       | Cliente comprador              |
| broker_id          | bigint (FK)       | Broker asignado                |
| stage              | string            | Etapa del pipeline             |
| amount             | decimal           | Monto del deal                 |
| commission_amount  | decimal           | Comision calculada             |
| notes              | text (nullable)   |                                |
| expected_close_date| date (nullable)   |                                |
| closed_at          | timestamp (nullable)|                               |
| timestamps         |                   |                                |

### Tabla: `tasks`
| Columna        | Tipo              | Notas                          |
|----------------|-------------------|--------------------------------|
| id             | bigint (PK)       |                                |
| user_id        | bigint (FK)       | Asignado a                     |
| deal_id        | bigint (FK, nullable) | Vinculado a deal           |
| client_id      | bigint (FK, nullable) | Vinculado a cliente        |
| property_id    | bigint (FK, nullable) | Vinculado a propiedad      |
| title          | string            |                                |
| description    | text (nullable)   |                                |
| priority       | string            | Prioridad de la tarea          |
| status         | string            | Estado                         |
| due_date       | date (nullable)   |                                |
| completed_at   | timestamp (nullable)|                               |
| timestamps     |                   |                                |

### Tabla: `transactions`
| Columna        | Tipo              | Notas                          |
|----------------|-------------------|--------------------------------|
| id             | bigint (PK)       |                                |
| type           | string            | Tipo de transaccion            |
| category       | string            |                                |
| description    | string            |                                |
| amount         | decimal           |                                |
| currency       | string            |                                |
| date           | date              |                                |
| deal_id        | bigint (FK, nullable) |                            |
| property_id    | bigint (FK, nullable) |                            |
| broker_id      | bigint (FK, nullable) |                            |
| user_id        | bigint (FK, nullable) |                            |
| payment_method | string (nullable) |                                |
| reference      | string (nullable) |                                |
| notes          | text (nullable)   |                                |
| timestamps     |                   |                                |

### Tabla: `commissions`
| Columna        | Tipo              | Notas                          |
|----------------|-------------------|--------------------------------|
| id             | bigint (PK)       |                                |
| deal_id        | bigint (FK)       |                                |
| broker_id      | bigint (FK)       |                                |
| amount         | decimal           |                                |
| percentage     | decimal           |                                |
| status         | string            | pending, approved, paid        |
| paid_at        | timestamp (nullable)|                               |
| transaction_id | bigint (FK, nullable)|                              |
| notes          | text (nullable)   |                                |
| timestamps     |                   |                                |

### Tabla: `posts`
| Columna          | Tipo              | Notas                          |
|------------------|-------------------|--------------------------------|
| id               | bigint (PK)       |                                |
| user_id          | bigint (FK)       | Autor                          |
| title            | string            |                                |
| slug             | string (unique)   |                                |
| excerpt          | text (nullable)   |                                |
| body             | longText          |                                |
| featured_image   | string (nullable) |                                |
| category_id      | bigint (FK, nullable) |                            |
| status           | string            | draft, published               |
| published_at     | timestamp (nullable) |                             |
| meta_title       | string (nullable) | SEO                            |
| meta_description | string (nullable) | SEO                            |
| views_count      | integer           | Default: 0                     |
| timestamps       |                   |                                |

### Tabla: `pages`
| Columna      | Tipo              | Notas                          |
|--------------|-------------------|--------------------------------|
| id           | bigint (PK)       |                                |
| title        | string            |                                |
| slug         | string (unique)   |                                |
| body         | longText          |                                |
| is_published | boolean           | Default: false                 |
| sort_order   | unsigned int      | Default: 0                     |
| show_in_nav  | boolean           | Default: false                 |
| nav_order    | unsigned int      | Default: 0                     |
| nav_label    | string(50, nullable) | Texto en el menu            |
| nav_url      | string (nullable) | URL custom (relativa o absoluta) |
| nav_route    | string (nullable) | Nombre de ruta Laravel (prioridad sobre nav_url) |
| nav_style    | string(20)        | link, button, muted (default: link) |
| timestamps   |                   |                                |

### Tabla: `site_settings`
| Columna               | Tipo              | Notas                          |
|-----------------------|-------------------|--------------------------------|
| id                    | bigint (PK)       |                                |
| site_name             | string            | Default: 'CRM Platform'       |
| site_tagline          | string (nullable) |                                |
| primary_color         | string            | Default: '#4f46e5'             |
| secondary_color       | string            | Default: '#7c3aed'             |
| home_welcome_text     | text (nullable)   |                                |
| logo_path             | string (nullable) | Ruta en storage/app/public/logos/ |
| logo_type             | string            | 'text' o 'image' (default: text) |
| whatsapp_number       | string (nullable) |                                |
| contact_email         | string (nullable) |                                |
| contact_phone         | string (nullable) |                                |
| address               | string (nullable) |                                |
| facebook_url          | string (nullable) |                                |
| instagram_url         | string (nullable) |                                |
| tiktok_url            | string (nullable) |                                |
| about_text            | text (nullable)   |                                |
| google_maps_embed     | text (nullable)   |                                |
| hero_image_path       | string (nullable) |                                |
| hero_heading          | string (nullable) |                                |
| hero_subheading       | string (nullable) |                                |
| benefits_section      | json (nullable)   | Cast: array                    |
| services_section      | json (nullable)   | Cast: array                    |
| testimonials_section  | json (nullable)   | Cast: array                    |
| benefits_heading      | string (nullable) | Titulo seccion beneficios      |
| benefits_subheading   | string (nullable) |                                |
| services_heading      | string (nullable) | Titulo seccion servicios       |
| services_subheading   | string (nullable) |                                |
| testimonials_heading  | string (nullable) | Titulo seccion testimonios     |
| testimonials_subheading| string (nullable)|                                |
| featured_heading      | string (nullable) | Titulo seccion propiedades dest.|
| featured_subheading   | string (nullable) |                                |
| blog_heading          | string (nullable) | Titulo seccion blog            |
| blog_subheading       | string (nullable) |                                |
| cta_heading           | string (nullable) | Titulo seccion CTA             |
| cta_subheading        | string (nullable) |                                |
| contact_heading       | string (nullable) | Titulo seccion contacto        |
| contact_subheading    | string (nullable) |                                |
| timestamps            |                   |                                |

### Tabla: `contact_submissions`
| Columna        | Tipo              | Notas                          |
|----------------|-------------------|--------------------------------|
| id             | bigint (PK)       |                                |
| name           | string            |                                |
| email          | string            |                                |
| phone          | string (nullable) |                                |
| message        | text              |                                |
| property_id    | bigint (FK, nullable) | Propiedad de interes       |
| ip_address     | string (nullable) |                                |
| user_agent     | string (nullable) |                                |
| utm_source     | string (nullable) | Tracking UTM                   |
| utm_medium     | string (nullable) |                                |
| utm_campaign   | string (nullable) |                                |
| is_read        | boolean           | Default: false                 |
| timestamps     |                   |                                |

### Tabla: `email_settings`
| Columna      | Tipo              | Notas                          |
|--------------|-------------------|--------------------------------|
| id           | bigint (PK)       |                                |
| smtp_server  | string            |                                |
| port         | integer           | Cast: integer                  |
| from_email   | string            |                                |
| from_name    | string (nullable) |                                |
| password     | text              | Cast: encrypted                |
| enable_ssl   | boolean           | Cast: boolean                  |
| timestamps   |                   |                                |

### Tabla: `email_templates`
| Columna   | Tipo              | Notas                          |
|-----------|-------------------|--------------------------------|
| id        | bigint (PK)       |                                |
| name      | string (unique)   | Identificador del template     |
| subject   | string            | Soporta variables {{Var}}      |
| body      | text              | HTML con variables {{Var}}     |
| body_text | text (nullable)   | Texto plano alternativo        |
| timestamps|                   |                                |

### Tabla: `custom_password_resets`
| Columna         | Tipo              | Notas                          |
|-----------------|-------------------|--------------------------------|
| id              | bigint (PK)       |                                |
| email           | string (index)    | Email del usuario              |
| token           | string(128) unique| bin2hex(random_bytes(64))      |
| expiration_date | timestamp         | created_at + 30 minutos        |
| used            | boolean           | Default: false (un solo uso)   |
| timestamps      |                   |                                |

### Tablas adicionales (resumen)
| Tabla               | Columnas clave                                                |
|---------------------|---------------------------------------------------------------|
| interactions        | client_id, property_id, user_id, type, description, scheduled_at, completed_at |
| marketing_channels  | name, type, color, icon, is_active, sort_order                |
| marketing_campaigns | marketing_channel_id, name, budget, spent, start_date, end_date, status |
| automation_rules    | name, trigger, conditions, action, action_config, is_active, trigger_count |
| automation_logs     | automation_rule_id, trigger_data, action_result, status, error_message |
| easybroker_settings | api_key, base_url, auto_publish, default_property_type/operation_type/currency |
| expense_categories  | name, type, color, icon, is_active                            |
| post_categories     | name, slug, description, color                                |
| tags                | name, slug                                                     |
| post_tag            | post_id, tag_id (pivot)                                       |

### Relaciones principales
```
Client -> belongsTo -> Broker (broker_id)
Broker -> hasMany -> Client
Deal -> belongsTo -> Property, Client, Broker
Task -> belongsTo -> User, Deal, Client, Property
Transaction -> belongsTo -> Deal, Property, Broker, User
Commission -> belongsTo -> Deal, Broker, Transaction
Interaction -> belongsTo -> Client, Property, User
Post -> belongsTo -> User, PostCategory
Post -> belongsToMany -> Tag (via post_tag)
MarketingCampaign -> belongsTo -> MarketingChannel
AutomationLog -> belongsTo -> AutomationRule
ContactSubmission -> belongsTo -> Property
```

---

## 6. Rutas (routes/web.php) — 151 rutas totales

### Publicas
| Metodo | URI                         | Nombre            | Controlador                      |
|--------|-----------------------------|--------------------|----------------------------------|
| GET    | /                           | home               | HomeController@index             |
| GET    | /propiedades                | propiedades.index  | PublicController@propiedades      |
| GET    | /propiedades/{id}/{slug?}   | propiedades.show   | PublicController@propiedadShow    |
| GET    | /nosotros                   | nosotros           | PublicController@nosotros         |
| GET    | /contacto                   | contacto           | PublicController@contacto         |
| POST   | /contacto                   | contacto.store     | PublicController@contactoStore    |
| GET    | /blog                       | blog.index         | BlogController@index              |
| GET    | /blog/{slug}                | blog.show          | BlogController@show               |
| GET    | /p/{slug}                   | page.show          | BlogController@page               |
| GET    | /vende-tu-propiedad         | landing.vende      | LandingController@show            |
| POST   | /landing/submit             | landing.submit     | LandingController@submit          |

### Auth (guest middleware)
| Metodo | URI              | Nombre           | Controlador                     | Middleware extra     |
|--------|------------------|------------------|---------------------------------|---------------------|
| GET    | /login           | login            | LoginController@show            |                     |
| POST   | /login           | —                | LoginController@store           | throttle:login      |
| GET    | /register        | register         | RegisterController@create       |                     |
| POST   | /register        | —                | RegisterController@store        |                     |
| GET    | /forgot-password | password.forgot  | ForgotPasswordController@show   |                     |
| POST   | /forgot-password | —                | ForgotPasswordController@store  | throttle:forgot-password |
| GET    | /reset-password  | password.reset   | ResetPasswordController@show    |                     |
| POST   | /reset-password  | —                | ResetPasswordController@store   | throttle:forgot-password |

### Autenticadas (middleware: auth)
| Metodo   | URI              | Nombre           | Controlador                |
|----------|------------------|------------------|----------------------------|
| POST     | /logout          | logout           | LoginController@logout     |
| Resource | /properties      | properties.*     | PropertyController         |
| Resource | /clients         | clients.*        | ClientController           |
| Resource | /brokers         | brokers.*        | BrokerController           |
| Resource | /deals           | deals.*          | DealController             |
| PATCH    | /deals/{deal}/stage | deals.update-stage | DealController@updateStage |
| Resource | /tasks           | tasks.*          | TaskController             |
| PATCH    | /tasks/{task}/toggle-complete | tasks.toggleComplete | TaskController |
| GET/POST | /profile         | profile(.update) | ProfileController          |
| POST     | /profile/photo   | profile.photo    | ProfileController@uploadPhoto |
| POST     | /profile/password| profile.password | ProfileController@changePassword |
| POST     | /properties/{id}/publish-easybroker   | — | PropertyController |
| POST     | /properties/{id}/unpublish-easybroker | — | PropertyController |

### Panel Admin (middleware: auth + viewer, prefix: admin, name: admin.*)
| Grupo                     | Rutas principales                                            |
|---------------------------|--------------------------------------------------------------|
| Dashboard                 | GET /admin                                                   |
| Users                     | Resource + permissions, avatar, changeRole                   |
| Brokers Management        | GET /admin/brokers-mgmt + approve/revoke/makeAdmin           |
| Settings (admin only)     | GET/POST /admin/settings                                     |
| Homepage CMS (admin only) | GET/POST /admin/homepage                                     |
| Email Settings            | GET/POST settings + test + send-test                         |
| Email Templates           | Resource + preview, upload-image, send-test                  |
| Email Assets              | GET/POST + gallery (JSON) + DELETE                           |
| Pages                     | Resource                                                     |
| Posts                     | Resource                                                     |
| Finance                   | dashboard, transactions (CRUD), commissions (approve/pay)    |
| Marketing                 | dashboard, channels (CRUD), campaigns (CRUD)                 |
| Analytics                 | GET /admin/analytics                                         |
| Automations               | Resource + logs, toggle                                      |
| EasyBroker                | GET/POST settings + test                                     |

---

## 7. Funcionalidades Implementadas

### 7.1 Sitio Publico (Tailwind + Alpine.js)
- **Layout publico** (`layouts/public.blade.php`): Tailwind CSS 4.0, Alpine.js, Inter font, colores dinamicos via CSS variables
- **Navbar dinamico:** Menu editable desde DB (tabla `pages`), 3 estilos (link, button, muted), active state, scroll effect
- **Homepage:** Hero con buscador, beneficios, propiedades destacadas, servicios, testimonios, formulario contacto, blog preview
- **Propiedades:** Listado publico + detalle individual
- **Nosotros:** Pagina institucional con contenido desde SiteSetting
- **Contacto:** Formulario con honeypot anti-spam, tracking UTM, crea ContactSubmission
- **Blog:** Listado de posts publicados + detalle + paginas estaticas (/p/{slug})
- **Landing pages:** Captacion de leads, layout independiente (layouts/landing.blade.php)
- **SEO:** Componentes seo-meta, json-ld (Schema.org), breadcrumbs, meta tags dinamicos
- **WhatsApp:** Boton flotante con numero configurable desde admin
- **Footer:** Info de contacto + redes sociales desde SiteSetting

### 7.2 CMS / Homepage Editor (/admin/homepage)
- **Secciones editables desde admin:** Hero, beneficios, servicios, testimonios, propiedades destacadas, blog, CTA, contacto
- **Campos JSON:** benefits_section, services_section, testimonials_section (cast: array)
- **Headings/subheadings** configurables por seccion
- **Hero:** Heading, subheading, imagen de fondo configurable
- **Controlador:** HomepageController (index + update)

### 7.3 Navegacion Dinamica (Pages + Nav)
- **Modelo Page:** Scopes `published()`, `inNav()`, metodos `navItems()`, `navHref()`, `isActive()`
- **Campos de navegacion:** show_in_nav, nav_order, nav_label, nav_url, nav_route, nav_style
- **Prioridad URL:** nav_route > nav_url > /p/{slug}
- **Cache:** navItems cacheados 5 min en tabla `cache`, almacenados como arrays (no objetos)
- **Admin:** Partial `_nav-fields.blade.php` en create/edit de pages
- **Seeder NavPageSeeder:** 6 paginas por defecto (Inicio, Propiedades, Nosotros, Blog, Contacto[button], Office[muted])
- **View::composer global** comparte `$navItems` a todas las vistas via AppServiceProvider

### 7.4 Sidebar CRM (app-sidebar.blade.php)
- Sidebar fijo a la izquierda (260px), fondo oscuro (#1e1b4b)
- Secciones: Principal, CRM, Administracion
- Administracion solo visible para role=admin
- Card de usuario en la parte inferior con avatar, nombre, rol y boton logout
- Logo dinamico: muestra imagen o texto segun `logo_type` en site_settings
- Topbar con titulo de pagina y toggle hamburguesa en mobile
- Alertas de exito/error con auto-dismiss a los 5 segundos
- CSS variables con colores dinamicos desde site_settings
- Responsive: sidebar oculto en mobile con overlay

### 7.5 Sistema de Usuarios (admin/users/*)
- **Lista:** Tabla con avatar mini, nombre, email, rol (badge), permisos (badges), acciones
- **Detalle:** Avatar circular grande (110px) con click-to-upload tipo Facebook
- **Editar:** Avatar circular con click-to-upload + formulario de datos
- **Crear:** Formulario con seleccion de rol que auto-asigna permisos. Envia email de bienvenida
- **Eliminar:** Solo admin, con confirmacion, limpia avatar del storage
- **Upload de avatar:** Via AJAX, preview inmediato, maximo 2MB, formatos JPG/PNG/GIF

### 7.6 Sistema de Roles y Permisos
- **5 roles:** admin, editor, viewer, broker, user
- **3 permisos booleanos:** can_read, can_edit, can_delete
- **Auto-asignacion por rol:** admin (all), editor (read+edit), viewer/broker/user (read)
- **Middleware por alias:** admin, editor, viewer, broker (registrados en bootstrap/app.php)

### 7.7 Deals / Pipeline de Ventas
- CRUD completo con stages, montos, comisiones, notas
- Vinculado a Property, Client, Broker
- Cambio de stage via PATCH
- Vista con pipeline visual

### 7.8 Tareas
- CRUD completo vinculado a deals, clientes, propiedades, usuarios
- Prioridad, status, due_date
- Toggle completado via PATCH

### 7.9 Finanzas (/admin/finance)
- **Dashboard financiero** con metricas
- **Transacciones:** CRUD completo (ingresos/egresos), vinculadas a deals/propiedades/brokers
- **Comisiones:** Lista con aprobar/pagar, vinculadas a deals y brokers

### 7.10 Marketing (/admin/marketing)
- **Dashboard** con metricas
- **Canales:** CRUD (nombre, tipo, color, icono, activo)
- **Campanas:** CRUD (presupuesto, gasto, fechas, status), vinculadas a canales
- **Seeder:** MarketingChannelSeeder con canales predeterminados

### 7.11 Blog (/admin/posts + /blog)
- **Admin:** CRUD posts con titulo, slug, excerpt, body, featured_image, categoria, tags, status, published_at, meta SEO
- **Publico:** Listado de posts publicados, detalle con vistas count
- **Categorias y tags:** Relaciones many-to-many
- **Paginas estaticas:** /p/{slug} muestra paginas publicadas

### 7.12 Automatizaciones (/admin/automations)
- CRUD reglas con trigger, conditions (JSON), action, action_config (JSON)
- Toggle activo/inactivo
- Logs de ejecucion con status y errores
- Contador de ejecuciones (trigger_count)

### 7.13 Integracion EasyBroker
- **Configuracion:** API key, base URL, auto-publish, defaults
- **Publicar/despublicar** propiedades desde el CRM hacia EasyBroker
- **Test de conexion** desde panel admin

### 7.14 Propiedades, Clientes, Brokers
- CRUD completo con fotos para cada entidad
- Propiedades: titulo, descripcion, precio, ubicacion, area, parking, status, recamaras, banos
- Clientes: nombre, email, telefono, presupuesto, tipo propiedad, broker asignado
- Brokers: nombre, email, licencia, comision, empresa, bio, status

### 7.15 Autenticacion y Seguridad
- **Login/logout** manual con Auth::attempt (rate limited: 5/min). Post-login redirige a `admin.dashboard`
- **Registro** publico (role=user por defecto)
- **Recuperacion de contrasena:** token seguro (128 chars, 30 min), un solo uso, rate limited (3/min)
- **Anti-enumeracion de emails:** Mensaje generico en forgot-password
- **Hashing:** bcrypt automatico via cast `hashed`
- **Honeypot:** Formulario de contacto publico incluye campo trampa anti-spam

### 7.16 Sistema de Email
- **Configuracion SMTP** dinamica desde panel admin (email_settings en DB)
- **EmailService:** send(), sendTemplate(), sendWelcomeEmail(), testConnection()
- **Templates de Email** con editor WYSIWYG (TinyMCE 8.3.2 self-hosted)
- **Galeria de Assets** con drag & drop upload
- **Templates seeded:** BienvenidaUsuario, RecuperarPassword, PasswordCambiado
- **Variables:** {{Nombre}}, {{Apellido}}, {{Email}}, {{Password}}, {{Fecha}}, {{Rol}}, {{Sitio}}, {{EnlaceReset}}, {{Expiracion}}

### 7.17 Analytics (/admin/analytics)
- Dashboard de analiticas

### 7.18 Perfil de Usuario (/profile)
- Avatar circular con overlay tipo Facebook
- Formulario de datos personales
- Cambio de contrasena con verificacion

### 7.19 Configuracion del Sitio (/admin/settings)
- Logo: toggle entre modo texto y modo imagen
- Nombre del sitio, eslogan, colores primario/secundario, texto de bienvenida
- Contacto: WhatsApp, email, telefono, direccion
- Redes sociales: Facebook, Instagram, TikTok
- Acerca de, Google Maps embed

---

## 8. Decisiones Importantes

1. **Sin Breeze/Jetstream:** Autenticacion manual para control total del flujo y las vistas.

2. **SQLite:** Base de datos local sin necesidad de servidor MySQL.

3. **Dual CSS strategy:** CSS puro con variables en el CRM (app-sidebar), Tailwind 4.0 en el sitio publico.

4. **Doble sistema de brokers:** Tabla `brokers` standalone + usuarios con `role=broker`. Intencional.

5. **Middleware por alias en bootstrap/app.php:** Laravel 13 no soporta `$this->middleware()` en controllers.

6. **Login redirige a admin.dashboard:** Todos los usuarios autenticados van al panel con sidebar.

7. **Homepage publica redirige autenticados:** `HomeController@index` redirige a `admin.dashboard` si hay sesion activa.

8. **Navegacion dinamica desde DB:** Tabla `pages` con campos nav_* controla el menu publico. Cache 5 min.

9. **Cache almacena arrays (no Eloquent):** `SiteSetting::current()` y `Page::navItems()` almacenan `toArray()` en cache y reconstruyen con `forceFill()` para evitar `__PHP_Incomplete_Class` al deserializar.

10. **PHPMailer en vez de Laravel Mail:** Control directo sobre SMTP con config dinamica desde base de datos.

11. **TinyMCE self-hosted (GPL):** Editor WYSIWYG en `public/vendor/tinymce/`.

12. **Tokens de reset custom:** Tabla `custom_password_resets` con campo `used` y `expiration_date`.

13. **Rate limiting:** login (5/min), forgot-password (3/min).

14. **Honeypot anti-spam:** Campo `website_url` oculto en formulario de contacto publico.

15. **CMS sections en SiteSetting:** Campos JSON (benefits_section, services_section, testimonials_section) + headings/subheadings por seccion.

16. **Rutas especificas antes de Resource:** En web.php, rutas como `users/permissions` se declaran antes de `Route::resource()`.

17. **NavPageSeeder:** 6 paginas por defecto con estilos diferenciados (Office como `muted`).

---

## 9. Usuarios de Prueba

| Email             | Contrasena   | Rol     |
|-------------------|-------------|---------|
| admin@crm.com     | Admin123!   | admin   |
| editor@crm.com    | Editor123!  | editor  |
| viewer@crm.com    | Viewer123!  | viewer  |

---

## 10. Almacenamiento de Archivos

```
storage/app/public/
  avatars/          # Fotos de perfil de usuarios
  logos/            # Logo del sitio
  brokers/          # Fotos de brokers
  clients/          # Fotos de clientes
  properties/       # Fotos de propiedades
  email-images/     # Imagenes subidas desde editor de templates
  email-assets/     # Imagenes de la galeria de assets
```

Symlink activo: `public/storage -> storage/app/public`

---

## 11. Dependencias

### PHP (composer.json)
- `laravel/framework` ^13.0
- `laravel/tinker` ^3.0
- `phpmailer/phpmailer` ^7.0

### JavaScript (package.json)
- `tailwindcss` ^4.0.0 + `@tailwindcss/vite`
- `tinymce` ^8.3.2 (copiado a public/vendor/tinymce/)
- `axios`, `vite` ^8.0.0, `laravel-vite-plugin` ^3.0.0 (dev)

### CDN
- Alpine.js (sitio publico)
- Inter font (fonts.bunny.net)

---

## 12. Pendientes / Deuda Tecnica

- [ ] Modelos `BrokerPhoto` y `PropertyPhoto` sin implementar (galeria multiple)
- [ ] Tablas `broker_photos` y `property_photos` solo tienen id + timestamps
- [ ] Controladores vacios: `AdminController`, `BrokerPhotoController`, `PropertyPhotoController`
- [ ] Migracion duplicada: `add_role_enum_to_users` (2026_03_30) duplica `add_role_to_users_table` (2026_03_29)
- [ ] Migraciones stub vacias: `add_parking_to_properties`, `add_relationships_to_properties`, `add_broker_id_to_clients`
- [ ] Layout `admin/layout.blade.php` queda sin usar (reemplazado por `layouts/app-sidebar.blade.php`)
- [ ] Sin email verification
- [ ] Sin busqueda/filtros en listados de propiedades publicas
- [ ] Sin paginacion en la mayoria de los listados (solo users tiene paginate)
- [ ] Tabla default `password_reset_tokens` existe pero no se usa (se usa `custom_password_resets`)
- [ ] Directorio `CRM-VBNet/` es un proyecto de referencia anterior, no relacionado con Laravel
- [ ] 2FA por email (preparado para futuro)
- [ ] Vista `clients/show.blade.php` pendiente
