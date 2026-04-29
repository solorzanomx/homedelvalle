# Prompt para Claude Code · Home del Valle
## Migrar el Portal del Cliente existente + Implementar funnel de rentas en backend

> **Para Claude Code:** este prompt es la orden completa de trabajo. Léelo entero antes de tocar código. Los pasos están ordenados para minimizar riesgo y permitir review por PR. **No combines fases en un solo PR**. Si una fase requiere decisión, pregunta antes de implementar.
>
> **Preparado:** 2026-04-29 · Alex (Director de Estrategia y Crecimiento)
> **Repo:** `/Users/alejandro/homedelvalle`
> **Stack:** Laravel 13.6.0 · PHP 8.3.30 · Livewire 4.2.4 · Tailwind 4.2.2 · MySQL · cPanel.

---

## 0. Lo primero que tienes que hacer

Antes de tocar UNA SOLA línea de código, lee estos documentos del repo en este orden. Sin esta lectura, no entiendes el contexto y vas a romper convenciones.

### Lectura obligatoria (en orden)

1. `IMPLEMENTATION_RULES.md` (raíz del repo) — convenciones que NUNCA se rompen.
2. `CRITICAL_VERSIONS.md` (raíz) — versiones de librerías y reglas de actualización.
3. `CONTEXTO_PROYECTO.md` (raíz) — snapshot del proyecto.
4. `docs/02-MANUAL-IMPLEMENTACION-SITIO.md` — stack, estructura, convenciones técnicas.
5. `docs/04-ROADMAP-Y-ARQUITECTURA.md` — fases del producto (estamos en Fase 3.5).
6. `docs/05-PROCESO-DE-RENTA.md` — proceso completo de renta en 3 fases (es la spec del Track B).
7. `docs/06-PORTAL-DEL-CLIENTE.md` — spec completo del portal (es la spec del Track A).
8. `.claude/SCHEMA_QUICK_REFERENCE.md` — cheat sheet del esquema.
9. `.claude/DATABASE_SCHEMA.md` — referencia completa.

### Reglas no negociables que extraerás de esos documentos

- Stack del Portal del Cliente: **Blade + Tailwind 4 + Livewire 4** (a diferencia del sitio público que usa Alpine.js).
- Stack del CRM admin: **Blade + CSS puro con variables CSS** (NO Tailwind, NO Filament).
- Jobs corren **síncronos** vía `schedule:run` (cron cPanel). NO uses `ShouldQueue`.
- Cache **NUNCA** almacena objetos Eloquent — sólo arrays/IDs.
- Email vía **PHPMailer + SMTP dinámico** desde tabla `email_settings` (no Laravel Mail).
- Uploads vía **Spatie Media Library** (no `Storage::put` a mano).
- Iconos: **Lucide-static** SVG inline.
- Marca se escribe **"Home del Valle"** (V mayúscula).
- Paleta: **navy + neutros + verde sistema** (cero dorado, cero cobre).
- Formularios públicos del sitio: Alpine + controlador. Pero **el portal sí usa Livewire 4**.

---

## 1. Objetivo de este prompt

Tienes que entregar **dos tracks paralelos**:

**Track A — Portal del Cliente:** auditar el portal existente en `/portal` y migrarlo / extenderlo a `miportal.homedelvalle.mx` con Livewire 4, los 4 perfiles de usuario (propietario, inquilino, comprador, vendedor) y todas las funcionalidades del documento `06-PORTAL-DEL-CLIENTE.md`.

**Track B — Funnel de Rentas en backend:** separar el funnel de renta del genérico `/admin/operations` y construir las 3 fases del proceso (captación, colocación, gestión post-cierre) con vistas dedicadas en `/admin/rentas/*` siguiendo `05-PROCESO-DE-RENTA.md`.

Los dos tracks están conectados: cada hito del funnel de rentas dispara una vista en el portal del cliente. No los implementes como silos.

---

## 2. Fase 0 — Auditoría (sin tocar código)

Antes de cualquier PR, ejecuta esta auditoría y reporta hallazgos al equipo. **No tomes decisiones ni hagas cambios todavía**.

### 2.1 Inventario del portal actual

```bash
# Lo que existe hoy en /portal
grep -rn "portal" routes/web.php
ls -la app/Http/Controllers/Portal/
ls -la resources/views/portal/
grep -rn "ClientPortalService" app/
```

Reporta:
- Lista de rutas actuales bajo `/portal`.
- Lista de controladores en `Portal/`.
- Lista de vistas existentes.
- Métodos públicos del `ClientPortalService` actual.
- Si hay tests del portal en `tests/Feature/Portal/` o similar.
- Quién es la base de usuarios actual (cuántos `User` con `role='client'`, cuántos tienen `client_id` poblado).

### 2.2 Inventario del esquema relevante

Verifica con SQL o reflejo de migraciones:

```sql
-- Stages permitidos en operations
DESCRIBE operations;
SELECT DISTINCT type, stage FROM operations LIMIT 50;

-- Estructura clients
DESCRIBE clients;
SELECT DISTINCT client_type FROM clients;

-- Estructura properties
DESCRIBE properties;
SELECT DISTINCT operation_type, status FROM properties;

-- RentalProcess existente
DESCRIBE rental_processes;
DESCRIBE rental_stage_logs;

-- Plantillas de contrato existentes
SELECT id, slug, name FROM contract_templates ORDER BY id;

-- Automations existentes
SELECT id, slug, name, is_active FROM automations;

-- Tablas de mensajería existentes (si hay)
SHOW TABLES LIKE 'message%';
SHOW TABLES LIKE 'interaction%';
```

Reporta:
- ¿`clients.client_type` admite `'renter'`? Si no, qué valores admite hoy.
- ¿`operations.metadata` es JSON? ¿Se usa hoy con `intent='rental'`?
- ¿Cuáles son los stages permitidos hoy en `operations` para `type='captacion'` y `type='renta'`?
- ¿`properties.operation_type` admite `'rental'`? ¿Existen `allows_pets`, `is_furnished`, `minimum_lease_months`?
- ¿`rental_processes` tiene los stages que espera `05-PROCESO-DE-RENTA.md`?
- ¿Existen plantillas de contrato de renta o sólo de venta?
- ¿Hay tabla de mensajería (`message_threads`, `interactions`, similar)?
- ¿`users` tiene `client_id` como FK opcional?
- ¿Hay listener / observer que cree cuenta de portal automáticamente al firmar?

### 2.3 Inventario del admin actual

```bash
grep -rn "operations" routes/web.php | grep -i "admin"
ls app/Http/Controllers/Admin/ | grep -i "operation\|rental\|capta"
```

Reporta:
- Rutas actuales de `/admin/operations` y sub-vistas.
- Si existe `/admin/captaciones` o `/admin/rentals` ya.
- Cómo se renderiza el kanban actual (Blade puro, Livewire, JS vanilla).

### 2.4 Entrega de la auditoría

Genera un archivo `docs/AUDITORIA-PORTAL-Y-RENTAS-2026-04.md` con todos los hallazgos. **Espera aprobación de Alex** antes de empezar la Fase 1.

Si encuentras decisiones técnicas que romperían convenciones (ej. el portal actual usa Vue, no Blade; o `clients.client_type` es un string libre, no enum), señálalo explícitamente como "**Decisión requerida**" y propón alternativas.

---

## 3. Fase 1 — Schema upgrades (1 PR independiente)

PR llamado: `feat: schema upgrades para portal v2 y funnel de rentas`

### 3.1 Migraciones a crear (sólo si la auditoría confirma que faltan)

```php
// database/migrations/2026_05_xx_xxxxxx_extend_clients_for_renter.php
Schema::table('clients', function (Blueprint $table) {
    // Sólo si client_type es enum sin 'renter'
    // Si es string libre, omitir esta migración
    DB::statement("ALTER TABLE clients MODIFY COLUMN client_type ENUM('lead','buyer','seller','owner','investor','renter') DEFAULT 'lead'");
});

// database/migrations/2026_05_xx_xxxxxx_extend_properties_for_rental.php
Schema::table('properties', function (Blueprint $table) {
    if (! Schema::hasColumn('properties', 'allows_pets')) {
        $table->boolean('allows_pets')->default(false)->after('status');
    }
    if (! Schema::hasColumn('properties', 'is_furnished')) {
        $table->enum('is_furnished', ['none','partial','full'])->default('none');
    }
    if (! Schema::hasColumn('properties', 'minimum_lease_months')) {
        $table->unsignedSmallInteger('minimum_lease_months')->nullable();
    }
    if (! Schema::hasColumn('properties', 'included_services')) {
        $table->json('included_services')->nullable();
    }
});

// database/migrations/2026_05_xx_xxxxxx_create_message_threads.php
Schema::create('message_threads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained()->cascadeOnDelete();
    $table->foreignId('operation_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('rental_process_id')->nullable()->constrained()->nullOnDelete();
    $table->string('subject')->nullable();
    $table->timestamp('last_message_at')->nullable();
    $table->enum('status', ['open','closed','archived'])->default('open');
    $table->timestamps();
    $table->index(['client_id','status']);
    $table->index('last_message_at');
});

Schema::create('message_thread_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('thread_id')->constrained('message_threads')->cascadeOnDelete();
    $table->string('author_type', 50);
    $table->unsignedBigInteger('author_id')->nullable();
    $table->text('body');
    $table->enum('type', ['text','system_event','attachment'])->default('text');
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    $table->index(['thread_id','created_at']);
});

// database/migrations/2026_05_xx_xxxxxx_create_portal_audit_logs.php
Schema::create('portal_audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('action', 80);
    $table->string('target_type', 80)->nullable();
    $table->unsignedBigInteger('target_id')->nullable();
    $table->ipAddress('ip')->nullable();
    $table->text('user_agent')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->index(['user_id','action','created_at']);
});

// database/migrations/2026_05_xx_xxxxxx_add_portal_visibility_flags.php
Schema::table('notifications', function (Blueprint $table) {
    if (! Schema::hasColumn('notifications', 'portal_visible')) {
        $table->boolean('portal_visible')->default(false);
    }
});
Schema::table('documents', function (Blueprint $table) {
    if (! Schema::hasColumn('documents', 'portal_visible')) {
        $table->boolean('portal_visible')->default(true);
    }
});

// database/migrations/2026_05_xx_xxxxxx_add_client_id_to_users.php
Schema::table('users', function (Blueprint $table) {
    if (! Schema::hasColumn('users', 'client_id')) {
        $table->foreignId('client_id')->nullable()->after('id')->constrained()->nullOnDelete();
    }
});
```

### 3.2 Stages que `operations` y `rental_processes` deben aceptar

Verifica que estén permitidos (si son enum o validación). Si no lo están, agrégalos:

**`operations.stage` para `type='captacion'`:**
`lead_received`, `qualification_call`, `property_visit`, `proposal_sent`, `agreement_signed`, `property_preparation`, `published`, `captacion_closed`.

**`operations.stage` para `type='renta'`:**
`lead`, `prospect_matched`, `viewing_scheduled`, `viewing_completed`, `application_received`, `tenant_qualification`, `offer_presented`, `guarantee_processing`, `contract_signed`, `property_delivered`.

**`rental_processes.current_stage`:**
`move_in`, `active`, `monthly_billing`, `incident_handling`, `renewal_window`, `renewal_signed`, `move_out_scheduled`, `move_out_completed`.

### 3.3 Pruebas de la Fase 1

- `php artisan migrate` corre sin error.
- `php artisan migrate:rollback` revierte limpio.
- Tests de regresión: ningún test del repo se rompe.
- SQL de verificación posterior: `SELECT COUNT(*) FROM clients` igual antes y después de migración.

---

## 4. Track A — Portal del Cliente (8 PRs)

> Spec completa en `docs/06-PORTAL-DEL-CLIENTE.md`. Esta sección lista qué entregar en cada PR. **No te saltes pasos**.

### PR Portal-1 — Subdominio + auth + dashboard mínimo (2 sem)

**Objetivo:** que un cliente pueda entrar a `miportal.homedelvalle.mx`, ver un dashboard básico con su info, y cerrar sesión. Aún sin documentos, sin mensajes, sin pagos.

**Entregables:**

1. **Configuración del subdominio:**
   - Documenta los pasos en `docs/SETUP-SUBDOMINIO-PORTAL.md` para que Alex los ejecute en cPanel.
   - Configura `bootstrap/app.php` para registrar `Route::domain('miportal.homedelvalle.mx')->group(base_path('routes/portal.php'))`.
   - Ajusta `.env`: `APP_URL`, `SESSION_DOMAIN=.homedelvalle.mx`.

2. **Rutas y controladores:**
   - Crea `routes/portal.php` con rutas de auth (login, logout, recover, reset, accept-invitation) y dashboard.
   - Crea `app/Http/Controllers/Portal/AuthController.php` con métodos `showLogin`, `login`, `logout`, `showRecover`, `recover`, `showReset`, `reset`, `showAcceptInvitation`, `acceptInvitation`.
   - Crea `app/Http/Controllers/Portal/DashboardController.php` con método `index`.
   - Crea middleware `app/Http/Middleware/PortalSubdomain.php` para asegurar host correcto y middleware `PortalRedirectLegacy.php` para redirigir 301 de `homedelvalle.mx/portal/*` a `miportal.homedelvalle.mx/*`.

3. **Layout:**
   - Crea `resources/views/layouts/portal.blade.php` con header (logo, nav, bell de notificaciones, avatar dropdown), navbar superior (variable según perfil del usuario), footer minimalista. Mobile-first.
   - Crea `resources/views/layouts/portal-empty.blade.php` para vistas sin sesión (login, recover).
   - Estilos en `resources/css/portal.css` con `@theme` de Tailwind 4 (paleta navy + neutros + verde, cero dorado).
   - Configura Vite para bundle dedicado del portal.

4. **Vistas:**
   - `resources/views/portal/auth/login.blade.php`, `recover.blade.php`, `reset.blade.php`, `accept-invitation.blade.php`.
   - `resources/views/portal/dashboard/index.blade.php` con:
     - Saludo dinámico por hora ("Buenos días, [Nombre]").
     - Cards básicas según los perfiles del usuario (vacías si no aplica).
     - Estado vacío si no hay operaciones activas.

5. **Servicio extendido:**
   - Extiende `app/Services/ClientPortalService.php` con:
     - `createAccount(Client $client): User` (idempotente).
     - `sendWelcomeInvitation(User $user): void` con plantilla `portal_welcome`.
     - `generateInvitationToken(User $user): string` (token con expiración 7 días).
     - `acceptInvitation(string $token, string $password): User`.
     - `impersonate(User $admin, User $clientUser): void` (con audit log).
     - `endImpersonation(): void`.

6. **Plantilla de email `portal_welcome`:**
   - Seeder en `database/seeders/PortalEmailTemplatesSeeder.php` que crea la plantilla en `email_templates` con variables `{{Nombre}}`, `{{ActivationLink}}`, `{{Email}}`.
   - Copy según `01-Manual-Marca-y-Voz.docx` sección 16.2.

7. **Listeners (sin disparar todavía, sólo definidos):**
   - `app/Listeners/CreatePortalAccountOnCaptacionSigned.php`.
   - `app/Listeners/CreatePortalAccountOnRentalSigned.php`.
   - `app/Listeners/CreatePortalAccountOnOfferStage.php`.
   - Registrarlos en `EventServiceProvider` pero **dejar configurable con flag** `config('portal.auto_create_accounts')` para activarlos en PR Portal-2.

8. **Tests:**
   - `tests/Feature/Portal/AuthTest.php`: login, logout, recover, accept invitation, rate limit.
   - `tests/Feature/Portal/DashboardTest.php`: redirige no autenticado, muestra cards correctas según perfil, estado vacío.
   - `tests/Feature/Portal/SubdomainTest.php`: rutas no responden en `homedelvalle.mx`, sí responden en `miportal.homedelvalle.mx`.

9. **No olvidar:**
   - Marca "Home del Valle" en `<title>`.
   - Slogan en footer.
   - OG image apuntando al portal.

**Criterio de hecho:** un cliente con cuenta activa puede entrar a `miportal.homedelvalle.mx`, ver "Hola [Nombre]" y cerrar sesión. Mobile 360px funciona. Tests pasan.

### PR Portal-2 — Documentos + uploads (2 sem)

**Objetivo:** el cliente puede ver y descargar sus documentos, y subir documentos que HDV le pidió.

**Entregables:**

1. **Modelos y policies:**
   - Crea `app/Policies/PortalPolicy.php` con métodos `viewDocument(User $user, Document $doc)`, `viewProperty(User $user, Property $property)`, `viewRental(User $user, RentalProcess $rental)`, `viewOperation(User $user, Operation $op)`.
   - Registra el policy en `AuthServiceProvider`.

2. **Controlador y rutas:**
   - `app/Http/Controllers/Portal/DocumentController.php` con `index`, `download($id)`, `upload`.
   - Rutas en `routes/portal.php`.

3. **Componente Livewire:**
   - `app/Livewire/Portal/DocumentUploader.php` usando `WithFileUploads` de Livewire 4.
   - Validación: tipos permitidos (PDF, JPG, PNG), max 10MB.
   - Persistencia vía Spatie Media Library en collection `client_uploads`.
   - Vista en `resources/views/livewire/portal/document-uploader.blade.php`.

4. **Vistas Blade:**
   - `resources/views/portal/documents/index.blade.php` con grid de documentos agrupados por categoría (contratos, recibos, identificaciones, otros).
   - Microcopy según `01-Manual-Marca-y-Voz.docx` sección 16.4 (estados vacíos) y 16.5 (mensajes de éxito).
   - Botón "Subir documento" abre modal con el componente Livewire.

5. **Triggers desde el admin:**
   - Cuando un user del admin sube un `Document` desde `/admin/clients/{id}` con `portal_visible=true`, dispara `Notification` con `portal_visible=true` para el cliente.

6. **Tests:**
   - Cliente A no puede ver/descargar documento que es de Cliente B.
   - Cliente puede subir documento; aparece en su lista.
   - Cliente puede descargar documento (verificar link no es público predecible — es por route + policy).

**Criterio de hecho:** un cliente puede ver su contrato, descargarlo, subir su identificación, y cuando HDV sube un nuevo documento, el cliente lo ve listado.

### PR Portal-3 — Threads de mensajes (2 sem)

**Objetivo:** comunicación bidireccional cliente ↔ HDV centralizada en threads con polling reactivo.

**Entregables:**

1. **Modelos:**
   - `app/Models/MessageThread.php` con relaciones: `client`, `operation`, `rental`, `messages`.
   - `app/Models/MessageThreadMessage.php` con relación `thread` y morfo `author` (User o Client).

2. **Controladores:**
   - `app/Http/Controllers/Portal/MessageController.php` con `index`, `show($threadId)`, `reply($threadId)`.
   - `app/Http/Controllers/Admin/MessageThreadController.php` para que HDV responda desde admin.

3. **Componentes Livewire:**
   - `app/Livewire/Portal/MessageList.php` con polling cada 30s para mostrar mensajes nuevos.
   - `app/Livewire/Portal/MessageComposer.php` con autoguardado del borrador y envío reactivo.
   - `app/Livewire/Admin/MessageThreadView.php` para el lado del admin (aunque admin sigue siendo Blade puro, este componente es excepción justificada por necesidad de tiempo real).

4. **Eventos del sistema:**
   - Crea servicio `app/Services/MessageThreadService.php` con método `addSystemEvent(MessageThread $thread, string $body, array $metadata = [])` que mete una entrada con `type='system_event'` cuando ocurre un cambio de stage, pago confirmado, visita agendada, etc.
   - Conecta este servicio a los listeners de `OperationStageChanged`, `PaymentConfirmed`, `VisitScheduled`, etc.

5. **Notificaciones:**
   - Cuando un cliente envía mensaje, dispara `Notification` al user admin asignado (con email + in-CRM).
   - Cuando HDV envía mensaje, dispara `Notification` al cliente con `portal_visible=true` + email.

6. **Vistas:**
   - `resources/views/portal/messages/index.blade.php` (lista de threads).
   - `resources/views/portal/messages/show.blade.php` (thread individual con mensajes y composer).
   - Tono según `01-Manual-Marca-y-Voz.docx` sección 16.9 (mensajes humanos del agente).

7. **SLA configurado:**
   - Si HDV no responde un thread en 4 horas hábiles, alerta automática al supervisor.

8. **Tests:**
   - Cliente A no puede acceder al thread de Cliente B.
   - Mensaje del cliente llega al admin con notificación.
   - Mensaje del admin llega al portal con notificación.
   - Polling refresca sin recargar página.

**Criterio de hecho:** cliente y agente conversan dentro del portal con experiencia tipo chat moderno.

### PR Portal-4 — Mi renta + pagos + recibos + reportes (3 sem)

**Objetivo:** inquilino y propietario operan en producción sus rentas a través del portal.

**Entregables:**

1. **Vistas:**
   - `resources/views/portal/rentals/index.blade.php` (lista si tiene varias).
   - `resources/views/portal/rentals/show.blade.php` con tabs:
     - **Inquilino:** contrato, próximo pago, recibos, reglamento, reportar incidente.
     - **Propietario:** datos del inquilino (filtrados), reporte mensual, historial de pagos, incidentes.
   - Etiquetas humanas de stage según `01-Manual-Marca-y-Voz.docx` sección 16.8.

2. **Componentes Livewire:**
   - `app/Livewire/Portal/PaymentTracker.php` con polling cada 30s.
   - `app/Livewire/Portal/IncidentReportForm.php` con `WithFileUploads` (fotos, urgencia).

3. **Recibos PDF:**
   - Servicio `app/Services/ReceiptGeneratorService.php` que genera PDF con DomPDF a partir de un `Transaction` confirmado.
   - Plantilla Blade `resources/views/pdf/rental-receipt.blade.php`.
   - Persiste en `documents` con `portal_visible=true` y categoría `rental_receipt`.

4. **Reporte mensual al propietario:**
   - Job `app/Jobs/GenerateMonthlyOwnerReports.php` (síncrono).
   - Genera PDF con: renta cobrada, gastos asociados, comisión, neto entregado, incidentes resueltos.
   - Plantilla Blade `resources/views/pdf/owner-monthly-report.blade.php`.
   - Persiste en `documents` y notifica al propietario.
   - Programar en `routes/console.php`: `Schedule::job(new GenerateMonthlyOwnerReports)->monthlyOn(3, '08:00');` (día 3 hábil del mes).

5. **Cobranza mensual:**
   - Job `app/Jobs/ProcessMonthlyRentalBilling.php` (síncrono, diario).
   - Por cada `RentalProcess` activa con `payment_day` igual al día actual, crea `Transaction` pendiente y dispara automation `rental_collection_reminder`.
   - Programar en `routes/console.php`: `Schedule::job(new ProcessMonthlyRentalBilling)->dailyAt('07:00');`.

6. **Plantillas de email (en `email_templates`):**
   - `rental_payment_reminder_5d`
   - `rental_payment_confirmed`
   - `rental_payment_overdue`
   - `rental_monthly_report_available`
   - `lead_renter_received`
   - `lead_rental_owner_received`
   - Todas con CTA al portal y tono según `01-Manual-Marca-y-Voz.docx` sección 16.7.

7. **Tests:**
   - Inquilino ve sólo su renta, no la de otros inquilinos.
   - Propietario ve datos del inquilino filtrados (nombre completo permitido, no detalles personales sensibles fuera de contrato).
   - Recibo PDF se genera correctamente con datos reales.
   - Reporte mensual se genera el día 3 hábil.

**Criterio de hecho:** un ciclo mensual completo (recordatorio → pago confirmado → recibo descargable → reporte al propietario) funciona end-to-end.

### PR Portal-5 — Operaciones venta/captación + timelines + onboarding (2 sem)

**Objetivo:** propietario, comprador y vendedor ven el avance de su operación en una pantalla clara.

**Entregables:**

1. **Vistas:**
   - `resources/views/portal/operations/index.blade.php` (lista).
   - `resources/views/portal/operations/show.blade.php` con timeline visual de stages, tareas pendientes (las del cliente y las de HDV), agente asignado.
   - Componente Livewire `app/Livewire/Portal/OperationTimeline.php` con polling.

2. **Onboarding del primer login:**
   - Modal en `resources/views/portal/components/welcome-modal.blade.php` con saludo personalizado, 3 bullets de qué puede hacer, CTA "Empezar".
   - Persiste flag `users.completed_onboarding=true` para no volver a mostrar.

3. **Activación de listeners:**
   - Activar el flag `config('portal.auto_create_accounts')=true` que se preparó en PR Portal-1.
   - Probar end-to-end: firmar una captación dispara la creación de cuenta, envía email, el propietario activa y entra.

4. **Tests:**
   - Modal se muestra sólo en primer login.
   - Cliente puede ver timeline de su operación con stage actual destacado.
   - Cliente NO puede ver operaciones que no le pertenecen.

**Criterio de hecho:** un cliente que firma hoy recibe email a los pocos segundos, entra al portal y ve el estado real de su operación con próximos pasos claros.

### PR Portal-6 — Vista "preview as client" + audit logs (1 sem)

**Objetivo:** el equipo HDV puede abrir el portal "como" un cliente para soporte.

**Entregables:**

1. **Botón en admin:**
   - Agregar botón "Vista previa del portal" en `/admin/clients/{id}` (sólo visible si user admin tiene permiso `clients.preview_portal`).
   - Click abre nueva pestaña en `miportal.homedelvalle.mx` con sesión impersonada.

2. **Lógica de impersonación:**
   - Método `impersonate(User $admin, User $clientUser)` ya creado en PR Portal-1.
   - Banner amarillo persistente arriba de cualquier vista del portal cuando hay impersonación activa: "Estás viendo el portal como [Nombre del cliente]. [Salir de la vista previa]".
   - **Modo lectura:** subida de docs, envío de mensajes, pagos están deshabilitados durante impersonación.

3. **Audit logs:**
   - Cada apertura/cierre de impersonación se registra en `portal_audit_logs`.
   - Cada acceso a documento sensible (escritura, identificación) también se registra.
   - Vista admin `/admin/audit-logs` para Dirección.

4. **Permisos:**
   - Por default sólo Dirección General y Supervisores tienen `clients.preview_portal`.
   - Configurable desde `/admin/users/{id}/permissions`.

5. **Tests:**
   - User sin permiso no ve el botón ni puede impersonar.
   - Impersonación crea audit log.
   - Acciones de escritura están bloqueadas durante impersonación.
   - Banner es visible.

**Criterio de hecho:** un agente que recibe llamada de cliente con duda puede abrir vista preview en 5 segundos y ver lo mismo que el cliente.

### PR Portal-7 — Notificaciones in-portal + preferencias + búsqueda (1 sem)

**Objetivo:** centro de notificaciones y configuración de preferencias.

**Entregables:**

1. **Bell icon:**
   - Componente Livewire `app/Livewire/Portal/NotificationsBell.php` con polling cada 60s.
   - Dropdown con últimas 10 notificaciones, link a centro completo.
   - Marcar como leída individual y bulk.

2. **Centro de preferencias:**
   - `resources/views/portal/account/preferences.blade.php`.
   - Toggles por tipo de notificación: Pagos, Documentos, Mensajes, Marketing.
   - Canales por tipo: email, WhatsApp, push (preparado).

3. **Búsqueda global:**
   - Componente Livewire `app/Livewire/Portal/GlobalSearch.php` en el header.
   - Busca en: documentos del cliente, inmuebles del cliente, mensajes, pagos.
   - Resultados agrupados por categoría.

4. **Anti-spam:**
   - Servicio `app/Services/NotificationDigestService.php` que agrupa notificaciones del sistema si hay 3+ en 24h.
   - Mensajes humanos nunca se agrupan.

5. **Tests:**
   - Bell icon refleja count correcto.
   - Preferencias se respetan al disparar notificaciones.
   - Búsqueda devuelve sólo recursos del cliente autenticado.

**Criterio de hecho:** un cliente puede silenciar marketing, mantener pagos activos, y encontrar cualquier doc de su histórico en 3 segundos.

### PR Portal-8 — Refinamiento, mobile, accesibilidad, performance (1 sem, opcional)

- Auditoría Lighthouse mobile > 85 en Performance, Accessibility, Best Practices.
- Pruebas en pantallas 360, 414, 768 px.
- Linter de accesibilidad: labels en todos los inputs, aria-required, mensajes de error visibles.
- Optimización de queries N+1.
- Optimización de imágenes con Intervention/Image.

---

## 5. Track B — Funnel de Rentas en backend (6 PRs)

> Spec completa en `docs/05-PROCESO-DE-RENTA.md`. La separación de `/admin/operations` genérico a `/admin/rentas` dedicado es el delta principal.

### PR Rentas-1 — Vistas /admin/rentas/* + sidebar (1 sem)

**Objetivo:** separar visualmente el funnel de renta del genérico de operaciones.

**Entregables:**

1. **Rutas:**
   - `routes/web.php` agregar grupo `Route::prefix('admin/rentas')` con rutas `captaciones`, `activas`, `gestion`, `gestion/{rental}`.

2. **Controlador:**
   - `app/Http/Controllers/Admin/RentalsController.php` con métodos `captaciones`, `activas`, `gestion`, `show(RentalProcess $rental)`.

3. **Vistas:**
   - `resources/views/admin/rentals/captaciones.blade.php` con kanban placeholder de 8 columnas (PR Rentas-2 lo poblará).
   - `resources/views/admin/rentals/activas.blade.php` con kanban placeholder de 10 columnas (PR Rentas-3).
   - `resources/views/admin/rentals/gestion.blade.php` con tabs: Activas / En renovación / Move-out / Cerradas (PR Rentas-4).
   - Estilos en CSS puro siguiendo el sistema del CRM admin (sin Tailwind).

4. **Sidebar:**
   - Modifica `resources/views/layouts/app-sidebar.blade.php` para agregar la sección "Rentas" con sub-items "Captaciones de renta", "Rentas activas", "Gestión post-cierre".
   - Mantén el item genérico "Operaciones" (queda para venta + captaciones de venta).

5. **Permisos:**
   - Crea permission `rentals.view` y asígnala a roles existentes (admin, supervisor, agent).

6. **Tests:**
   - Rutas responden 200 con permiso correcto.
   - Sidebar muestra los nuevos items.

**Criterio de hecho:** Alex puede entrar a `/admin/rentas/captaciones` y ver una página con título "Captaciones de Renta", aunque el contenido aún no esté poblado.

### PR Rentas-2 — Kanban Fase 1 captación de renta (1 sem)

**Objetivo:** kanban funcional con 8 stages de Fase 1 con drag & drop entre columnas.

**Entregables:**

1. **Query:**
   - `Operation::where('type','captacion')->whereJsonContains('metadata->intent','rental')->get()` (verificar índice JSON).

2. **Componente:**
   - Kanban renderizado con Blade puro + JS vanilla (mantener convención del CRM admin) o, si se justifica reactividad, **Livewire SOLO en este componente** (excepción documentada en `02-MANUAL-IMPLEMENTACION-SITIO.md`). Discutir con Alex antes.

3. **Cards:**
   - Nombre del propietario, colonia, m², renta esperada, agente asignado, días en stage, semáforo de SLA (verde < 50%, amarillo 50-100%, rojo > 100%).
   - Click abre detalle del Operation.

4. **Filtros:**
   - Por agente asignado, por colonia, por rango de renta esperada, por antigüedad.

5. **Bulk actions:**
   - Reasignar agente, mover a stage, marcar como cold.

6. **Drag & drop:**
   - Mover card de columna actualiza `operation.stage` y crea entrada en `operation_stage_logs`.
   - Requiere checklist completo del stage anterior antes de avanzar (usar `StageChecklistTemplate` existente).

7. **Tests:**
   - Drag & drop dispara update correcto en DB.
   - Si falta checklist, no permite avanzar.
   - Filtros funcionan correctamente.

**Criterio de hecho:** Alex puede ver todas las captaciones de renta en un kanban claro y mover cards entre stages.

### PR Rentas-3 — Kanban Fase 2 colocación de renta (1 sem)

**Objetivo:** kanban funcional con 10 stages de Fase 2.

**Entregables:**

1. **Query:**
   - `Operation::where('type','renta')->where('status','active')->get()`.

2. **Componente:** mismo patrón que PR Rentas-2 con 10 columnas.

3. **Cards:**
   - Dirección del inmueble, foto principal, días en mercado, leads inquilinos asociados, oferta vigente si aplica.
   - Click abre detalle del inmueble + operación.

4. **Filtros:**
   - Por agente, colonia, rango de renta, días en mercado.

5. **Sub-vista por inmueble:**
   - Timeline completo Fase 1 → Fase 2 en una pantalla (link desde card).

6. **Tests** equivalentes a PR Rentas-2.

**Criterio de hecho:** Alex y los agentes ven todas las rentas activas con su pipeline completo y pueden mover entre stages con la lógica de checklist.

### PR Rentas-4 — Vista RentalProcess Fase 3 (1 sem)

**Objetivo:** vista de gestión post-cierre con tabs y vista detallada por contrato vigente.

**Entregables:**

1. **Lista:**
   - Tabla con columnas: dirección, inquilino, propietario, fecha inicio, fecha fin, días para vencer, status pago del mes, semáforos.
   - Tabs: Activas, En renovación (60d antes), Move-out programado, Cerradas.

2. **Alertas visibles:**
   - Pagos vencidos (rojo).
   - Incidentes abiertos (amarillo).
   - Contratos por vencer < 60 días (azul).

3. **Vista detalle del RentalProcess:**
   - Timeline de pagos (todos los meses con su estado).
   - Lista de incidentes con estado y fecha.
   - Comunicaciones (link al `MessageThread`).
   - Documentos asociados.

4. **Acciones rápidas:**
   - Marcar pago como recibido (genera `Transaction` confirmada y dispara recibo PDF + notificación).
   - Crear adenda de renovación.
   - Programar move-out.

5. **Widgets en dashboard admin:**
   - Card "Rentas activas" con conteo total y morosidad del mes.
   - Card "Contratos por vencer en 60 días".

6. **Tests** equivalentes.

**Criterio de hecho:** un administrador puede ver todas las rentas activas con su estado real y operar la cobranza desde aquí.

### PR Rentas-5 — Auto-spawn + listeners + automations (1 sem)

**Objetivo:** las transiciones críticas disparan creación automática de operaciones e invocan workflows.

**Entregables:**

1. **Listeners de auto-spawn:**
   - `app/Listeners/SpawnRentaOperationOnCaptacionClosed.php`: cuando `Operation type='captacion' metadata.intent='rental'` pasa a `captacion_closed`, crea `Operation type='renta' stage='lead'` vinculada al `property_id` y al `client_id` (owner).
   - `app/Listeners/SpawnRentalProcessOnPropertyDelivered.php`: cuando `Operation type='renta'` pasa a `property_delivered`, crea `RentalProcess` con `lease_start`, `lease_end`, `monthly_rent`, `deposit_amount`, etc.
   - Listeners del Track A para creación de cuenta de portal (ya creados, ahora se conectan a estos triggers).

2. **Automations (5 workflows):**
   - `nurturing_owner_rental_lead` (8 pasos según `05-PROCESO-DE-RENTA.md` sección 10).
   - `nurturing_renter_lead` (5 pasos).
   - `rental_collection_reminder` (5 sub-eventos por mes).
   - `rental_renewal_workflow` (60 días antes).
   - `rental_monthly_report` (día 3 hábil del mes).
   - Seeder en `database/seeders/RentalAutomationsSeeder.php`.

3. **Plantillas de email** ya creadas en PR Portal-4 — verificar que estén disponibles.

4. **Tests:**
   - Cerrar captación dispara creación de operación de renta.
   - Entregar inmueble dispara creación de RentalProcess.
   - Cliente recibe email de bienvenida al portal.

**Criterio de hecho:** end-to-end un lead de `/renta-tu-propiedad` recorre Fase 1 → Fase 2 → Fase 3 sin que un humano cree manualmente entidades intermedias.

### PR Rentas-6 — Templates de contrato + jobs programados (1 sem)

**Objetivo:** infraestructura de contratos y cobranza completamente operativa.

**Entregables:**

1. **Templates de contrato (10 listados en `05-PROCESO-DE-RENTA.md` sección 9):**
   - Seeder en `database/seeders/RentalContractTemplatesSeeder.php` con todos los 10 templates en `contract_templates`.
   - Cada uno con variables Blade (`{{owner_name}}`, `{{tenant_name}}`, `{{monthly_rent}}`, etc.).
   - Generación con DomPDF al llegar al stage correspondiente.

2. **Jobs programados (verificar que existan o crear):**
   - `CheckRentalRenewals` (diario): mueve a `renewal_window` los `RentalProcess` cuyo `lease_end` está a 60 días o menos.
   - `ProcessMonthlyRentalBilling` (diario, 7am): genera transactions del día y dispara recordatorios.
   - `GenerateMonthlyOwnerReports` (mensual día 3 hábil 8am): PDF para cada propietario.
   - Registrar en `routes/console.php`.

3. **Tests:**
   - Templates renderizan sin error con datos de prueba.
   - Jobs corren sin error.

**Criterio de hecho:** todos los contratos y procesos automáticos funcionan en el ambiente de desarrollo. Listo para staging.

---

## 6. Reglas que NO puedes romper

### 6.1 Convenciones técnicas
- **Sitio público** = Alpine + controlador. No metas Livewire.
- **Portal del Cliente** = Livewire 4 + Tailwind 4. No metas Vue ni React.
- **CRM admin** = Blade + CSS puro con variables. No metas Tailwind ni Filament. Excepción documentada y aprobada por Alex: kanban de rentas si requiere reactividad puede usar Livewire en componente aislado.
- Cero `ShouldQueue` en jobs. Síncronos siempre.
- Cero objetos Eloquent en cache. Sólo arrays.

### 6.2 Convenciones de marca
- "Home del Valle" siempre (V mayúscula).
- Slogan completo en footer y email signatures.
- Paleta navy + neutros + verde sistema. **Cero dorado**, cero cobre.
- Tipografía Inter.

### 6.3 Privacidad
- Inquilino no ve datos personales completos del propietario y viceversa.
- Toda comunicación pasa por HDV (no chat directo entre las partes).
- Documentos sensibles servidos por controlador + policy, no como link público.

### 6.4 Datos
- Nunca borres `User`. Sólo `is_active=false`.
- Nunca modifiques migraciones existentes. Crea nuevas.
- Honeypot en TODO formulario público.

### 6.5 Email
- Todo email a cliente con cuenta activa incluye CTA al portal.
- Tono según `01-Manual-Marca-y-Voz.docx`.

---

## 7. Cuándo preguntar antes de implementar

Detente y pregunta a Alex si:

1. **El stack del portal actual NO es Blade.** Si encuentras que `/portal` actual usa Vue, React o cualquier otra tecnología, no asumas migración trivial — discute alternativas.
2. **Hay un listener o observer ya activo** que crea cuentas de portal — para no duplicar lógica.
3. **El esquema de `clients`, `operations` o `rental_processes`** difiere significativamente de lo que documentamos — confirma antes de migrar.
4. **Necesitas instalar un paquete nuevo** (ej. una librería de calendario, una pasarela de pago) — confirma versión y compatibilidad con `CRITICAL_VERSIONS.md`.
5. **Una decisión técnica del PR rompe algo del sitio público o del CRM admin existente** — pregunta antes de hacer breaking change.
6. **El diseño visual del portal va a usar Tailwind clases que no están en el theme actual** — confirma que es el lugar correcto para extenderlas.
7. **El subdominio no está disponible o el SSL no se puede emitir** — Alex tiene que ejecutar pasos en cPanel.
8. **El kanban del backend de rentas requiere Livewire en el CRM admin** — esa excepción a la convención necesita aprobación explícita.
9. **Tienes que correr migraciones que afectan datos en producción** — siempre confirma.
10. **Detectas que el portal actual ya está siendo usado por clientes reales** — el plan de migración necesita comunicación a clientes.

---

## 8. Cómo entregar

### Por cada PR

1. **Branch:** `feature/portal-N` o `feature/rentas-N`.
2. **Commits:** mensaje en español, formato `tipo: mensaje corto`. Ej: `feat: agregar componente Livewire MessageComposer`.
3. **Descripción del PR:** qué problema resuelve, qué archivos toca, qué tests agregaste, qué requirió decisión que documentaste.
4. **Tests:** todos pasan en local.
5. **Migrations:** corren limpio en local. `migrate:rollback` no rompe.
6. **Documentación:** si tocaste algo de los manuales en `/docs/`, actualiza el archivo y mencionalo en el PR.
7. **Checklist:** al final del PR, copia el "Criterio de hecho" de la fase y marca cada uno.

### Al cerrar el track A o B completo

1. Genera reporte de qué quedó implementado vs. qué quedó pendiente.
2. Actualiza el roadmap (`docs/04-ROADMAP-Y-ARQUITECTURA.md`) marcando Fase 3.5 sub-fases A-E como completadas.
3. Crea archivo `docs/CHANGELOG-PORTAL-Y-RENTAS-2026.md` con resumen ejecutivo de cambios.

---

## 9. Recursos del repo a citar al implementar

| Necesitas… | Mira… |
|---|---|
| Stack y convenciones | `docs/02-MANUAL-IMPLEMENTACION-SITIO.md` secciones 1, 17 |
| Spec del portal | `docs/06-PORTAL-DEL-CLIENTE.md` |
| Spec de rentas | `docs/05-PROCESO-DE-RENTA.md` |
| Tono de copy | `docs/` y manual de marca en Cowork (sección 16 sobre portal) |
| Esquema actual | `.claude/SCHEMA_QUICK_REFERENCE.md` y `.claude/DATABASE_SCHEMA.md` |
| Reglas de versión | `CRITICAL_VERSIONS.md` |
| Reglas de implementación | `IMPLEMENTATION_RULES.md` |
| Pasos de deploy | `DEPLOYMENT_GUIDE.md` |

---

## 10. Orden de ejecución recomendado

```
Fase 0  (auditoría, sin código)        ──► espera aprobación de Alex
   │
   ▼
Fase 1  (schema upgrades)              ──► PR único, mergeable solo
   │
   ▼
PR Portal-1 (subdominio + auth)        ──┬──► PR Rentas-1 (vistas + sidebar)
   │                                     │
   ▼                                     ▼
PR Portal-2 (documentos)              PR Rentas-2 (kanban Fase 1)
   │                                     │
   ▼                                     ▼
PR Portal-3 (mensajes)                PR Rentas-3 (kanban Fase 2)
   │                                     │
   ▼                                     ▼
PR Portal-4 (mi renta + pagos)        PR Rentas-4 (RentalProcess Fase 3)
   │                                     │
   ▼                                     ▼
PR Portal-5 (operaciones + onboarding)  PR Rentas-5 (auto-spawn + automations)
   │                                     │
   ▼                                     ▼
PR Portal-6 (preview as client)       PR Rentas-6 (templates + jobs)
   │                                     │
   ▼                                     ▼
PR Portal-7 (notificaciones)         (Track B cerrado)
   │
   ▼
PR Portal-8 (refinamiento, opcional)
```

Los dos tracks pueden correr en paralelo a partir de PR Portal-3 / PR Rentas-2 (cuando el portal ya tiene base sólida). Cada track entrega valor incremental sin necesidad de esperar al otro.

---

## 11. Definición de "listo" del producto completo

Cuando todos los PRs están en producción y verificados:

**Para un cliente que firma a partir del go-live:**
- Recibe email de bienvenida en menos de 60 segundos.
- Activa cuenta y entra al portal en menos de 2 minutos.
- Ve su contrato, agente asignado, próximos pasos.
- Manda mensaje y recibe respuesta dentro del SLA.
- Sube un documento sin perderse.
- Mobile 360px funciona.

**Para Alex y Ana Laura:**
- `/admin/rentas/captaciones`, `/activas`, `/gestion` separan visualmente el funnel de venta.
- Pueden ver pipeline de renta sin ruido.
- Auto-spawn funciona end-to-end.
- Reportes mensuales a propietarios se generan solos cada mes.
- Cobranza dispara recordatorios automáticos.
- Pueden abrir vista preview as client cuando un cliente llama.

**Para el equipo:**
- Onboarding técnico de un dev nuevo se hace siguiendo `/docs/` sin lagunas.
- Operación diaria sigue las reglas del manual de operaciones.
- KPIs del portal y de rentas se pueden medir desde `/admin/analytics`.

---

**Fin del prompt.**

Si llegas hasta aquí y todo tiene sentido, empieza por la Fase 0 (auditoría). No empieces por la Fase 1 hasta tener el reporte de auditoría aprobado por Alex.

Si algo de este prompt es contradictorio con un documento del repo, el documento del repo gana y me avisas para corregir el prompt.

Si algo de este prompt requiere decisión que sólo Alex puede tomar, márcalo como "**Decisión requerida**" y espera respuesta antes de avanzar.
