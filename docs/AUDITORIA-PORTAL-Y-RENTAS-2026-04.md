# Auditoría — Portal del Cliente y Funnel de Rentas
**Fecha:** 2026-04-29  
**Responsable:** Claude Code  
**Solicitado por:** Alex (Director de Estrategia y Crecimiento)  
**Estado:** Borrador — pendiente aprobación de Alex antes de iniciar Fase 1

---

## 1. Inventario del Portal Actual (§2.1)

### 1.1 Rutas bajo `/portal`

El portal **ya existe** en `homedelvalle.mx/portal` (mismo dominio, NO subdominio). Rutas actuales:

```
GET  /portal/terminos                      portal.terminos
POST /portal/terminos/aceptar              portal.terminos.aceptar
GET  /portal/                              portal.dashboard
GET  /portal/rentals                       portal.rentals.index
GET  /portal/rentals/{id}                  portal.rentals.show
GET  /portal/documents                     portal.documents.index
GET  /portal/documents/{id}/download       portal.documents.download
POST /portal/documents/upload              portal.documents.upload
GET  /portal/account                       portal.account
PUT  /portal/account/password              portal.account.password
GET  /portal/captacion                     portal.captacion
POST /portal/captacion/documentos          portal.captacion.upload
DELETE /portal/captacion/documentos/{doc}  portal.captacion.document.delete
POST /portal/captacion/confirmar-precio    portal.captacion.confirm-price
GET  /portal/valuacion                     (ver rutas valuacion)
```

Rutas de gestión de portal desde admin (en `/admin/clients/{id}`):
```
POST   /admin/clients/{client}/create-portal        clients.create-portal
PATCH  /admin/clients/{client}/toggle-portal        clients.toggle-portal
DELETE /admin/clients/{client}/delete-portal        clients.delete-portal
POST   /admin/clients/{client}/reset-portal-password clients.reset-portal-password
```

### 1.2 Controladores en `Portal/`

```
PortalCaptacionController.php     — funnel captación propietario vendedor
PortalDashboardController.php     — dashboard + account
PortalDocumentController.php      — listado, download, upload
PortalLegalController.php         — aceptación de términos
PortalRentalController.php        — vistas de renta (inquilino/propietario)
PortalValuacionController.php     — valuación de propiedad
```

### 1.3 Vistas existentes

```
resources/views/portal/
├── account.blade.php
├── dashboard.blade.php
├── terminos.blade.php
├── captacion/           (vistas de captación)
├── documents/           (index + upload)
├── rentals/             (index + show)
└── valuacion/           (valuación de propiedad)
```

### 1.4 ClientPortalService — métodos públicos

```php
createPortalAccount(Client $client, ?string $password = null): array
getClientForUser(User $user): ?Client
getRentalsForClient(Client $client)
getDocumentsForClient(Client $client)
```

**Falta:** `sendWelcomeInvitation()`, `generateInvitationToken()`, `acceptInvitation()`, `impersonate()`, `endImpersonation()`.

### 1.5 Tests del portal

**NO EXISTEN.** `tests/Feature/Portal/` no existe.

### 1.6 Base de usuarios

- Usuarios con `role='client'`: **0**
- Usuarios con `client_id` (FK a clients): campo **NO existe** en tabla `users`
- El portal actual usa `user_id` en `clients` (relación inversa)

---

## 2. Inventario del Esquema Relevante (§2.2)

### 2.1 `clients.client_type`

- Columna existe. Tipo: string libre (NO es ENUM).
- Valores actuales en BD: **vacío** (0 registros con client_type poblado).
- `'renter'` no está como opción formal pero tampoco hay restricción que lo impida.
- **Acción necesaria:** migración que aclare semántica si queremos forzar enum; por ahora string libre lo admite todo.

### 2.2 `operations`

Columnas: `id, type, stage, phase, status, property_id, client_id, secondary_client_id, broker_id, user_id, amount, monthly_rent, currency, deposit_amount, commission_amount, commission_percentage, guarantee_type, lease_start_date, lease_end_date, lease_duration_months, expected_close_date, closed_at, completed_at, cancelled_at, notes, created_at, updated_at, target_type, source_operation_id`

**`operations.metadata` NO EXISTE.** El brief asume `metadata->intent='rental'` para distinguir captaciones de renta vs. venta; esa columna no está. Los tipos existentes son `venta`, `renta`, `captacion` — **`captacion`** ya abarca ambas intenciones.

**> ⚠️ DECISIÓN REQUERIDA (A):** El brief propone usar `metadata->intent='rental'` para separar captaciones de renta de captaciones de venta. Pero la estructura actual usa `type='captacion'` genérico para ambas. Opciones:
> - **A1:** Agregar columna `intent` (string/enum) a `operations` para distinguir intención de captación.
> - **A2:** Crear `type='captacion_renta'` (nuevo tipo separado).
> - **A3:** Reusar `type='captacion'` con filtro por `target_type` (ya existe en tabla).
> Recomiendo **A1** como la menos invasiva.

**Stages actuales por tipo:**

| Tipo | Stages disponibles |
|------|-------------------|
| `captacion` | lead, contacto, visita, revision_docs, avaluo, mejoras, exclusiva, fotos_video, carpeta_lista |
| `renta` | lead, contacto, visita, exclusiva, publicacion, busqueda, investigacion, contrato, entrega, cierre, activo, renovacion |
| `venta` | lead, contacto, visita, exclusiva, publicacion, busqueda, investigacion, contrato, entrega, cierre |

El brief en §3.2 propone stages distintos (`lead_received`, `qualification_call`, `property_visit`, etc.). Los nombres difieren de los existentes.

**> ⚠️ DECISIÓN REQUERIDA (B):** Los stages propuestos en el brief (snake_case, inglés) difieren de los implementados (snake_case, español). ¿Renombramos los existentes (breaking change) o el brief se adapta a los nombres actuales?

### 2.3 `rental_processes`

Columnas: `id, property_id, owner_client_id, tenant_client_id, broker_id, user_id, stage, monthly_rent, currency, deposit_amount, commission_amount, commission_percentage, guarantee_type, lease_start_date, lease_end_date, lease_duration_months, notes, status, completed_at, cancelled_at, created_at, updated_at`

Stages definidos en `RentalProcess::STAGES`:
```
captacion, verificacion, publicacion, busqueda, investigacion,
contrato, entrega, activo, renovacion, cerrado
```

El brief en §3.2 propone stages de Fase 3 (`move_in`, `active`, `monthly_billing`, `incident_handling`, `renewal_window`, `renewal_signed`, `move_out_scheduled`, `move_out_completed`). Los actuales cubren hasta `activo` y `renovacion` pero NO la gestión post-cierre detallada.

**Falta en `rental_processes`:**
- `payment_day` (día del mes para cobranza)
- `payment_amount_confirmed` (monto definitivo)
- Campos de move-out

### 2.4 `properties`

Campos existentes relevantes: `operation_type, furnished, amenities`  
**Faltan:** `allows_pets` (bool), `is_furnished` (enum none/partial/full), `minimum_lease_months`, `included_services` (json)  
`operation_type` ya admite `'rental'` (string libre, no enum).

### 2.5 Mensajería

**NO existe** ninguna tabla de mensajería (`message_threads`, `interactions` ni similar). Hay un `MessageController` en admin pero apunta a otro contexto.

### 2.6 `users.client_id`

**NO existe.** La relación inversa existe: `clients.user_id` apunta al User. Para el portal se usa eso.  
El brief propone agregar `users.client_id` como FK optional. Es un cambio de dirección de la relación.

**> ⚠️ DECISIÓN REQUERIDA (C):** ¿Agregamos `users.client_id` (FK nueva) o usamos `clients.user_id` existente? Agregar la FK nueva es más limpio para el portal pero requiere sincronización bidireccional. Recomiendo usar `clients.user_id` existente y NO duplicar.

### 2.7 Portal visibility flags

- `notifications.portal_visible`: **NO existe**
- `documents.portal_visible`: **NO existe**

### 2.8 Automations

Tabla `automations` existe pero está **vacía** (0 registros). Sin workflows configurados todavía.

### 2.9 Contract templates

Solo 2 registros en `contract_templates`, ambos con `slug='slug'` (parecen datos de prueba). No hay contratos de renta reales configurados.

---

## 3. Inventario del Admin Actual (§2.3)

### 3.1 Rutas admin para operaciones/rentas

```
# Captaciones (funnel venta)
GET  /admin/captaciones         captaciones.index  (CaptacionAdminController)
GET  /admin/captaciones/{id}    captaciones.show
POST /admin/captaciones/{id}/... (acciones de captación)

# Rental Processes (funnel renta — backend existente)
GET    /admin/rentals              rentals.index      (RentalProcessController)
GET    /admin/rentals/{id}         rentals.show
POST   /admin/rentals              rentals.store
PUT    /admin/rentals/{id}         rentals.update
DELETE /admin/rentals/{id}         rentals.destroy
PATCH  /admin/rentals/{id}/stage   rentals.update-stage
POST   /admin/rentals/{id}/documents
POST   /admin/rentals/{id}/poliza
POST   /admin/rentals/{id}/contracts/generate
POST   /admin/rentals/{id}/contracts/upload
```

**NO existe** `/admin/rentas/*` dedicado (el brief propone crearlo). El existente es `/admin/rentals`.

### 3.2 Kanban

La vista de captaciones usa Blade puro (sin Livewire, sin drag&drop activo). No hay kanban visual implementado para rentas.

### 3.3 Sidebar

El sidebar ya tiene items para:
- `admin.captaciones.index` → "Captaciones"
- `rentals.index` → "Rentas" (si la ruta existe)

Falta separación visual Track A (captación renta) / Track B (colocación / gestión).

---

## 4. Decisiones Requeridas (resumen)

| ID | Decisión | Opciones | Recomendación |
|----|----------|----------|---------------|
| **A** | Separar captaciones de renta vs. venta | A1: columna `intent`; A2: tipo nuevo; A3: `target_type` | **A1** — mínimo invasivo |
| **B** | Nombres de stages en inglés vs. español | Renombrar existentes o adaptar brief | **Mantener español** — ya hay datos en prod |
| **C** | Dirección de FK users↔clients | Nueva `users.client_id` vs. existente `clients.user_id` | **Mantener `clients.user_id`** existente |
| **D** | Subdominio `miportal.*` | ¿Cambiar ya o mantener `/portal` por ahora? | Alex decide según cPanel/SSL |
| **E** | Kanban con Livewire en admin | Excepción aprobada? | Requiere confirmación explícita |

---

## 5. Gap Analysis — Lo que falta vs. lo que existe

### Track A — Portal

| Funcionalidad | ¿Existe? | Estado |
|---|---|---|
| Rutas base `/portal` | ✅ | Funcional en mismo dominio |
| Dashboard | ✅ | Básico |
| Documentos (ver/bajar) | ✅ | Funcional |
| Upload documentos | ✅ | Funcional |
| Vista rentals (inquilino) | ✅ | Básico |
| Captación (propietario) | ✅ | Funcional |
| Valuación | ✅ | Funcional |
| Auth (login/recover/reset) | ✅ | Usa Laravel auth estándar |
| Aceptación de términos | ✅ | Middleware `portal.legal` |
| Mensajería bidireccional | ❌ | No existe (tablas ni vistas) |
| Pagos / recibos PDF | ❌ | No existe |
| Reporte mensual propietario | ❌ | No existe |
| Notificaciones in-portal bell | ❌ | No existe |
| Impersonación admin→cliente | ❌ | No existe |
| Onboarding modal primer login | ❌ | No existe |
| Subdominio `miportal.*` | ❌ | Pendiente cPanel |
| Tests Feature | ❌ | 0 tests de portal |
| `users.client_id` FK | ❌ | No existe (usa `clients.user_id`) |
| `notifications.portal_visible` | ❌ | No existe |
| `documents.portal_visible` | ❌ | No existe |
| Welcome invitation email | ❌ | No existe (plantilla ni servicio) |

### Track B — Backend Rentas

| Funcionalidad | ¿Existe? | Estado |
|---|---|---|
| `RentalProcess` model + stages | ✅ | 10 stages definidos |
| `/admin/rentals` CRUD | ✅ | Funcional |
| Documentos de renta | ✅ | `RentalDocumentController` |
| Póliza jurídica | ✅ | `PolizaJuridicaController` |
| Contratos generación | ✅ | `ContractController` |
| Kanban visual captaciones renta | ❌ | No existe |
| Kanban visual colocación renta | ❌ | No existe |
| `/admin/rentas/*` dedicado | ❌ | Solo existe `/admin/rentals` |
| Auto-spawn Operation→RentalProcess | ❌ | No existe listener |
| `operations.intent` (renta vs venta) | ❌ | No hay columna de intención |
| Stages Fase 3 post-cierre detallados | ❌ | Solo `activo`, `renovacion`, `cerrado` |
| Cobranza mensual (Job) | ❌ | No existe |
| Reporte propietario (Job) | ❌ | No existe |
| Automations de renta | ❌ | Tabla vacía |
| Templates contrato renta reales | ❌ | Solo 2 plantillas de prueba |
| `properties.allows_pets` | ❌ | No existe |
| `properties.is_furnished` (enum) | ❌ | Solo `furnished` bool |
| `properties.minimum_lease_months` | ❌ | No existe |
| Mensajería `message_threads` | ❌ | No existe |

---

## 6. Orden Recomendado de Implementación

Dado el estado actual, el orden más seguro es:

```
Fase 1 (Schema upgrades) — PR independiente
  ├─ ADD operations.intent (string nullable) ← decisión A
  ├─ ADD rental_processes.payment_day, move_out_scheduled_at
  ├─ ADD properties.allows_pets, is_furnished(enum), minimum_lease_months
  ├─ ADD notifications.portal_visible, documents.portal_visible
  ├─ CREATE message_threads + message_thread_messages
  └─ CREATE portal_audit_logs

PR Portal-1 (no subdominio todavía — usar /portal existente hasta resolver cPanel)
  ├─ Extender ClientPortalService con invitation flow
  ├─ Crear listeners (desactivados por flag)
  └─ Plantilla email portal_welcome

PR Rentas-1 (vistas /admin/rentas/* + sidebar)
  → Paralelo a Portal-1

... resto según orden del brief ...
```

---

## 7. Notas para Alex antes de aprobar

1. **El portal ya existe y funciona** en `homedelvalle.mx/portal`. La migración a subdominio es independiente del desarrollo de nuevas funcionalidades y puede hacerse al final (PR Portal-1 puede desplegarse en `/portal` hasta que el subdominio esté listo en cPanel).

2. **0 clientes activos** en el portal hoy — no hay riesgo de disrupción al migrar.

3. **Los stages de operations/rentals están en español** y hay datos en producción. El brief usa nombres en inglés. Recomiendo adaptar el brief a los nombres existentes para evitar migraciones disruptivas.

4. **`users.client_id`**: la relación inversa ya existe (`clients.user_id`). Agregar la FK en `users` duplica información. Recomiendo **no agregarla** y usar la existente.

5. **Kanban con Livewire en admin**: es una excepción explícita a las convenciones. Necesita aprobación antes de implementar PR Rentas-2.

---

**Fin de la auditoría. Espera aprobación de Alex antes de iniciar Fase 1.**
