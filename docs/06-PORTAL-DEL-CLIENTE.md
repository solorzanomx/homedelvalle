# Portal del Cliente · miportal.homedelvalle.mx

> **Audiencia:** Alex, Ana Laura, agentes, administradores, futuros colaboradores y desarrolladores que toquen el CRM.
> **Estado:** v0 — abril 2026.
> **Documento hermano:** `02-MANUAL-IMPLEMENTACION-SITIO.md`, `03-MANUAL-OPERACIONES-CRM.docx`, `04-ROADMAP-Y-ARQUITECTURA.md`, `05-PROCESO-DE-RENTA.md`.
> **Mantenedor:** Alex (revisado por Ana Laura).

Este documento describe el diseño operativo y técnico del **Portal del Cliente** de Home del Valle — un subdominio dedicado (`miportal.homedelvalle.mx`) donde cualquier cliente de HDV (propietario, inquilino, comprador, vendedor) puede acceder en cualquier momento para ver el estado de su operación, descargar documentos, hacer pagos, reportar incidentes y comunicarse con el equipo.

Es el activo digital que más nos diferencia de las inmobiliarias tradicionales, y debe construirse con esa intención.

---

## Índice

1. Por qué el portal es el diferenciador
2. Arquitectura técnica del subdominio
3. Los cuatro perfiles de usuario y qué ve cada uno
4. Funcionalidades transversales (todos los perfiles)
5. Alta y vida de la cuenta del cliente
6. Comunicaciones bidireccionales (cliente ↔ HDV)
7. Notificaciones y emails al cliente
8. Diseño visual, UX y layout
9. Permisos, seguridad y privacidad
10. Vista "preview as client" desde el admin
11. Implicaciones técnicas para Claude Code
12. Roadmap de implementación en fases
13. Checklist de QA

---

## 1. Por qué el portal es el diferenciador

La promesa de Home del Valle es **transparencia total y control de cada operación**. La frase del slogan ("Más control") no es para nosotros — es para el cliente. El portal es la materialización de esa promesa.

Lo que la mayoría de inmobiliarias hace:
- El cliente recibe un PDF por WhatsApp.
- El cliente llama para preguntar "¿cómo va mi operación?".
- El cliente no sabe qué está pasando entre fechas hito.
- El cliente pierde documentos y pide copias.
- El cliente no tiene historial centralizado de la relación.

Lo que ofrece HDV con el portal:
- El cliente entra cuando quiere, ve el estado real, descarga documentos sin pedirlos.
- Los pagos, contratos, incidentes y comunicaciones viven en un solo lugar.
- La relación HDV ↔ cliente se vuelve **continua y trazable**, no episódica.
- Cada interacción genera registro: cero "yo nunca recibí ese documento", cero "no me dijeron nada".

Para HDV el beneficio es operativo: menos llamadas pidiendo updates, menos PDFs perdidos, menos disputas por información, mejor calidad de datos para el CRM.

Para el cliente el beneficio es psicológico: certeza, profesionalismo y la sensación de estar tratando con una firma seria, no con un agente individual.

---

## 2. Arquitectura técnica del subdominio

### 2.1 Decisión: subdominio dedicado, no sub-ruta

Hoy existe `homedelvalle.mx/portal` (con middleware `auth + client`). La decisión es **migrar a `miportal.homedelvalle.mx`** por tres razones:

1. **Marca y memorabilidad:** "miportal" es más fácil de comunicar que "/portal". El cliente lo recuerda como un destino, no como una sección.
2. **Separación visual:** el portal tiene UX de aplicación, no de sitio web público. Subdominio refuerza esa diferencia.
3. **Seguridad y aislamiento de cookies:** el dominio del CRM admin (`/admin`) y el portal pueden tener políticas distintas; sub-dominios facilitan eso.

### 2.2 Configuración del subdominio en cPanel

```
miportal.homedelvalle.mx → mismo public/ del Laravel (apunta a la app)
```

En `bootstrap/app.php` o en `app/Providers/RouteServiceProvider.php`, registrar el grupo de rutas con `Route::domain('miportal.homedelvalle.mx')->...`.

Alternativamente, usar middleware que detecte el host:

```php
// app/Http/Middleware/PortalSubdomain.php
public function handle($request, Closure $next) {
    if ($request->getHost() !== 'miportal.homedelvalle.mx') {
        return abort(404);
    }
    return $next($request);
}
```

### 2.3 Rutas

Un archivo dedicado `routes/portal.php` cargado desde `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    then: function () {
        Route::domain('miportal.homedelvalle.mx')
            ->middleware('web')
            ->group(base_path('routes/portal.php'));
    },
)
```

Estructura de `routes/portal.php`:

```php
Route::middleware('guest')->group(function () {
    Route::get('/', [PortalAuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [PortalAuthController::class, 'login']);
    Route::get('/recuperar', [PortalAuthController::class, 'showRecover']);
    Route::post('/recuperar', [PortalAuthController::class, 'recover']);
    Route::get('/restablecer/{token}', [PortalAuthController::class, 'showReset']);
    Route::post('/restablecer', [PortalAuthController::class, 'reset']);
});

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/inicio', [PortalDashboardController::class, 'index'])->name('portal.home');

    // Inmuebles del cliente
    Route::get('/mis-inmuebles', [PortalPropertyController::class, 'index']);
    Route::get('/mis-inmuebles/{property}', [PortalPropertyController::class, 'show']);

    // Operaciones (venta, captación, renta)
    Route::get('/operaciones', [PortalOperationController::class, 'index']);
    Route::get('/operaciones/{operation}', [PortalOperationController::class, 'show']);

    // Renta activa (si aplica)
    Route::get('/mi-renta', [PortalRentalController::class, 'index']); // lista si hay varias
    Route::get('/mi-renta/{rental}', [PortalRentalController::class, 'show']);
    Route::post('/mi-renta/{rental}/incidente', [PortalRentalController::class, 'reportIncident']);

    // Pagos
    Route::get('/pagos', [PortalPaymentController::class, 'index']);
    Route::get('/pagos/{payment}/recibo', [PortalPaymentController::class, 'downloadReceipt']);

    // Documentos
    Route::get('/documentos', [PortalDocumentController::class, 'index']);
    Route::get('/documentos/{document}/descargar', [PortalDocumentController::class, 'download']);
    Route::post('/documentos', [PortalDocumentController::class, 'upload']);

    // Mensajes (cliente ↔ HDV)
    Route::get('/mensajes', [PortalMessageController::class, 'index']);
    Route::get('/mensajes/{thread}', [PortalMessageController::class, 'show']);
    Route::post('/mensajes/{thread}', [PortalMessageController::class, 'reply']);

    // Cuenta y configuración
    Route::get('/cuenta', [PortalAccountController::class, 'index']);
    Route::post('/cuenta', [PortalAccountController::class, 'update']);
    Route::post('/cuenta/contrasena', [PortalAccountController::class, 'changePassword']);

    // Cerrar sesión
    Route::post('/salir', [PortalAuthController::class, 'logout'])->name('portal.logout');
});
```

URLs en español, semánticas, fáciles de recordar y compartir.

### 2.4 Layout dedicado

Crear `resources/views/layouts/portal.blade.php` independiente del layout público y del CRM admin.

Características:
- Header limpio con logo HDV y nombre del usuario.
- Navbar superior con 5–6 secciones principales según perfil del usuario.
- Sin sidebar oscuro (a diferencia del CRM admin) — el portal del cliente debe sentirse cercano, no técnico.
- Footer minimalista con link a soporte y cerrar sesión.
- Mobile-first: la mayoría de los clientes accederán desde teléfono.

### 2.5 Stack

Mismo stack que el CRM público:
- PHP 8.3, Laravel 13.6
- **Livewire 4** para los componentes interactivos del portal (subir documento, reportar incidente, pagar). Aquí sí usamos Livewire — el portal es una app autenticada con UX reactiva, no un sitio público SEO.
- Tailwind 4 para estilos.
- Lucide-static para iconos.
- Spatie Media Library para uploads de documentos.

---

## 3. Los cuatro perfiles de usuario y qué ve cada uno

Un mismo usuario (`User` con `role='client'`) puede tener varios perfiles activos al mismo tiempo. Por ejemplo, un cliente puede ser propietario de un inmueble que tiene rentado **y** estar buscando comprar otro. El portal debe acomodar esto.

### 3.1 Perfil: Propietario (con inmueble vivo en HDV)

**Cuándo se le activa:** cuando firma una `Operation type='captacion'` (de venta o renta).

**Qué ve:**

- **Dashboard:** card por cada inmueble con su estado (en captación / publicado / con oferta / firmado / en renta activa).
- **Mis inmuebles:** lista de inmuebles del propietario con estado del listing, tiempo en mercado, número de visitas, leads recibidos.
- **Detalle del inmueble:** fotos publicadas, ficha vigente, link a la página pública, estadísticas de tráfico (cuando se implemente analytics), próximas visitas agendadas.
- **Operación activa:** timeline visual del pipeline de captación o de venta, próximos hitos, agente asignado con foto y contacto directo.
- **Documentos:** contrato de captación, propuesta de marketing, fotos profesionales descargables, escrituras y predial subidos.
- **Si su inmueble está rentado (RentalProcess activa):**
  - Datos del inquilino (nombre, perfil de pago, fecha de inicio de contrato).
  - Estado de pago del mes en curso (al corriente / atrasado / en proceso).
  - Reportes mensuales descargables (PDF generado).
  - Incidentes reportados por el inquilino.
  - Próximas fechas: vencimiento de contrato, ventana de renovación.
  - Si tiene administración integral: estado financiero (renta cobrada, gastos, comisión, neto).
- **Mensajes:** conversaciones con su agente asignado y con dirección.
- **Pagos:** comisiones liberadas, depósitos en custodia (si aplica), histórico de transacciones del inmueble.

### 3.2 Perfil: Inquilino (en contrato vigente)

**Cuándo se le activa:** cuando firma contrato de arrendamiento (Fase 2.9 del proceso de renta).

**Qué ve:**

- **Dashboard:** card grande con el inmueble que renta (foto, dirección), próximo pago destacado, contrato vigente (fecha inicio / fin / días restantes).
- **Mi renta:**
  - Datos del contrato: renta mensual, depósito, fecha de inicio, fecha de fin, plazo restante.
  - Datos de la propiedad: dirección, características.
  - Reglamento del condominio (si aplica).
  - Información del propietario filtrada por privacidad: nombre o iniciales, datos para contacto en emergencia (si la política lo permite). Por defecto, **toda la comunicación pasa por HDV**, no entre inquilino y propietario.
- **Pagos:**
  - Estado del mes en curso: al corriente / próximo / vencido.
  - Próximo monto y fecha.
  - Botón "Pagar ahora" (cuando se integre pasarela; mientras, mostrar CLABE y referencia).
  - Recibos descargables del histórico.
  - Estado del depósito en garantía (en custodia / aplicado).
- **Documentos:**
  - Contrato firmado.
  - Póliza jurídica vigente o documento del aval.
  - Inventario fotográfico de entrega.
  - Reglamentos.
- **Reportar incidente / pedir mantenimiento:** formulario para abrir un ticket con descripción, fotos opcionales, urgencia. Tracking del estado del ticket.
- **Mensajes:** conversaciones con su agente o con administrador.
- **Renovación (cuando aplique 60 días antes del vencimiento):** banner con CTA "Renovar mi contrato" o "No renovaré, programar move-out".

### 3.3 Perfil: Comprador (en operación activa)

**Cuándo se le activa:** opcional. Por defecto, los compradores en stage temprano (lead, viewing) no tienen portal. Se le activa cuando avanza a `offer` o `signed` para que pueda ver documentos y firmar digitalmente.

**Qué ve:**

- **Dashboard:** card de la operación de compra activa con stage actual y próximo paso.
- **Mi operación:**
  - Inmueble que está comprando (foto, dirección, datos).
  - Timeline visual: lead → viewing → offer → signed → funded → closed.
  - Próxima tarea (si depende del cliente): firmar oferta, entregar comprobante de fondos, asistir a notario.
  - Tareas pendientes de HDV.
- **Documentos:**
  - Carta de oferta.
  - Avalúo (cuando se haya realizado).
  - Documentos legales del inmueble que está comprando.
  - Borradores de contrato.
- **Mensajes:** con agente y con legal.
- **Pagos:** apartado para depositar señas o anticipos cuando el flujo lo requiera.

### 3.4 Perfil: Vendedor (en operación activa)

Se solapa con el perfil propietario (sección 3.1) cuando hay una `Operation type='venta'` vigente sobre su inmueble. La vista que ve es similar a la de propietario pero con énfasis en pipeline de venta.

**Qué ve adicional:**

- Ofertas recibidas con detalle (cuando aplique).
- Estado de avalúo, due diligence legal, gestoría notarial.
- Próximas visitas agendadas con compradores.
- Carta de oferta aceptada y plan al cierre.

### 3.5 Multiperfil

Si el usuario tiene varios perfiles activos simultáneamente, el dashboard muestra **secciones separadas y claramente etiquetadas**:

```
Bienvenido, María.

[ Sección 1: Inmueble que rentas ]
   → Tu próximo pago, contrato, documentos del inmueble.

[ Sección 2: Inmueble en venta ]
   → Estado de la captación, próximas visitas, ofertas.

[ Sección 3: Inmueble que rentas a un tercero ]
   → Estado de pago del inquilino, reportes mensuales.
```

El navbar superior se mantiene igual; el dashboard hace el routing visual.

---

## 4. Funcionalidades transversales (todos los perfiles)

### 4.1 Cuenta y configuración

- Editar datos básicos (teléfono, foto, preferencias de comunicación).
- Cambiar contraseña.
- Activar/desactivar 2FA por email.
- Configurar canal preferido de notificación (email / WhatsApp / ambos).
- Idioma (default español; preparar para inglés futuro).
- Eliminar cuenta o cerrar sesión en todos los dispositivos.

### 4.2 Notificaciones in-portal

Banner persistente arriba del dashboard con conteo de notificaciones nuevas. Al hacer clic, muestra el centro de notificaciones con:
- Icono por tipo (pago / documento / mensaje / hito de operación).
- Texto descriptivo.
- Link directo al recurso relacionado.
- Marcar como leída individual o "marcar todas".

### 4.3 Centro de ayuda contextual

En cada vista hay un botón flotante "?" o "¿Necesitas ayuda?" que abre:
- FAQ relevante para el contexto (si está en pagos, FAQ de pagos).
- Botón "Hablar con mi agente" → abre conversación directa.
- Botón "WhatsApp HDV" → con mensaje precargado.
- Link al artículo del help center si aplica (ya hay sistema de `help_articles`).

### 4.4 Historial completo de la operación

Cada operación tiene una **vista de timeline** que muestra:
- Cada cambio de stage con fecha y autor.
- Cada documento subido o firmado.
- Cada mensaje intercambiado.
- Cada pago realizado o pendiente.

Es el "expediente digital" que el cliente puede consultar en cualquier momento.

### 4.5 Búsqueda global

Barra superior con búsqueda en:
- Documentos por nombre o tipo.
- Inmuebles del cliente.
- Mensajes con HDV.
- Pagos por fecha o concepto.

### 4.6 Exportar / descargar

Cada cliente puede descargar:
- Su carpeta completa de documentos en ZIP.
- Reporte anual de pagos (para declaración fiscal del propietario).
- PDF resumen de su operación.

Esto refuerza el discurso de **transparencia y portabilidad de información**: el cliente es dueño de sus datos, no rehén del CRM.

---

## 5. Alta y vida de la cuenta del cliente

### 5.1 Trigger de creación

La cuenta de portal se crea **automáticamente** al ocurrir alguno de estos eventos:

| Trigger | Perfil que se activa |
|---|---|
| Firma `Operation type='captacion'` | Propietario |
| Firma contrato de arrendamiento como inquilino | Inquilino |
| Pasa a stage `offer_presented` o `signed` en `Operation type='venta'` siendo comprador | Comprador |
| Pasa a stage `offer_presented` siendo vendedor | Vendedor |

El sistema:
1. Verifica si ya existe `User` para ese `Client.email`.
2. Si no existe, crea `User` con `role='client'`, password aleatorio temporal, `client_id` vinculado.
3. Envía email de bienvenida con link a `miportal.homedelvalle.mx` y un link único para establecer su contraseña inicial (token con expiración de 7 días).
4. Crea registro en `notifications` interno: "Cuenta de portal creada para {nombre}".

### 5.2 Trigger manual desde admin

El admin (`/admin/clients/{id}`) tiene botones:

- **Crear acceso al portal:** activa la cuenta si no existe.
- **Reenviar invitación:** envía nuevo email de bienvenida.
- **Resetear contraseña:** envía link de reset.
- **Activar / desactivar cuenta:** sin borrar datos, sólo bloquea login.
- **Vista previa del portal:** abre el portal "como" el cliente para ver lo que él ve (sección 10).

### 5.3 Email de bienvenida

Plantilla `portal_welcome` con tono de marca (sobrio, sin emojis):

```
Asunto: Bienvenido al portal de Home del Valle

Hola [Nombre],

Acabamos de habilitar tu acceso a Mi Portal, el espacio personal donde
podrás ver el estado de tu operación, descargar tus documentos y
comunicarte con nosotros en cualquier momento.

   → Activa tu cuenta: [Establecer mi contraseña]
   → Después podrás entrar en: miportal.homedelvalle.mx

Tu correo de acceso es: [email]

Una vez dentro, encontrarás tu inmueble, contratos, pagos, documentos
y un canal directo con tu agente asignado.

Si tienes dudas, escríbenos por WhatsApp al 55 1345 0978.

Equipo Home del Valle Bienes Raíces
Pocos inmuebles. Más control. Mejores resultados.
```

### 5.4 Onboarding de primer login

Al primer login, el portal muestra un **modal corto de bienvenida** con:
1. Saludo personalizado.
2. 3 bullets con qué puede hacer ("ver tu operación", "descargar tus documentos", "comunicarte con nosotros").
3. CTA "Empezar".

No es un tour invasivo; un modal que se cierra y no vuelve a aparecer.

### 5.5 Vida de la cuenta

- La cuenta **no se desactiva** al cerrar una operación. El cliente queda como **alumni** y puede entrar a ver su histórico cuando quiera.
- Si el cliente vuelve a operar con HDV, la misma cuenta se reutiliza.
- Después de 5 años sin actividad, la cuenta se archiva (banner de "Cuenta inactiva, contacta soporte para reactivar").

### 5.6 Eliminación / "derecho a ser olvidado"

A petición explícita del cliente y con verificación de identidad:
- Datos de operaciones cerradas se anonimizan pero se mantienen por requisito fiscal (mínimo 5 años en México).
- Datos personales no obligados se eliminan.
- Se entrega copia de los datos al cliente antes de eliminar.

---

## 6. Comunicaciones bidireccionales (cliente ↔ HDV)

### 6.1 Modelo

Cada cliente tiene un "canal" persistente con HDV. Internamente es una `MessageThread` (entidad nueva o usar `interactions` existente como base). Cada `MessageThread` está vinculada a un `Client` y opcionalmente a una `Operation`.

Mensajes dentro del thread tienen:
- Autor (Cliente o User HDV).
- Timestamp.
- Contenido (texto, imágenes, archivos adjuntos vía Spatie Media Library).
- Estado (enviado / leído).
- Tipo (texto / sistema — eventos automáticos).

### 6.2 Lo que ve el cliente

- Lista de threads (uno por operación activa, uno general con HDV).
- Cada thread con conteo de mensajes no leídos.
- Componer nuevo mensaje con texto y attachments.
- Marcar como urgente (alerta al agente).

### 6.3 Lo que ve HDV en el admin

- Cada thread aparece como una tarea o entrada en `/admin/clients/{id}` y en `/admin/messages`.
- Notificación push al agente asignado cuando entra mensaje del cliente.
- Capacidad de responder desde el admin (escribe agente, llega al portal).
- SLA de respuesta de HDV: 4 horas hábiles (debe quedar configurado en `automation_rules`).

### 6.4 Eventos del sistema dentro del thread

El thread también muestra eventos automáticos (no mensajes humanos):

```
[Sistema · 14 abr 2026]
   Tu pago de abril fue recibido. Recibo: [descargar].

[Agente: María Pérez · 15 abr 2026]
   Hola Juan, ya agendamos la visita técnica para mañana 11am. ¿Te queda?

[Sistema · 16 abr 2026]
   Visita técnica programada para 17 abr 2026, 11:00.
```

Esto da contexto continuo y no rompe la experiencia con tabs separados.

---

## 7. Notificaciones y emails al cliente

### 7.1 Tipos de notificación

| Evento | Canal | Plantilla |
|---|---|---|
| Cuenta de portal creada | Email | `portal_welcome` |
| Nuevo mensaje de HDV | Email + push | `portal_new_message` |
| Documento nuevo disponible | Email + push | `portal_document_available` |
| Cambio de stage en operación | Email + push | `portal_stage_change` |
| Recordatorio de pago próximo | Email | `rental_payment_reminder_5d` |
| Pago recibido | Email + push | `rental_payment_confirmed` |
| Pago atrasado | Email + WhatsApp | `rental_payment_overdue` |
| Reporte mensual disponible | Email | `rental_monthly_report_available` |
| Renovación de contrato próxima (60d) | Email + push | `rental_renewal_window_open` |
| Move-out programado | Email | `rental_moveout_scheduled` |
| Visita agendada (vendedor) | Email + push | `seller_viewing_scheduled` |
| Oferta recibida (vendedor) | Email + push | `seller_offer_received` |
| Cuenta inactiva > 90 días | Email | `portal_inactivity_reminder` |

### 7.2 Centro de preferencias

El cliente puede en `/cuenta` activar/desactivar canales por tipo:
- Pagos: email + WhatsApp (recomendado activos).
- Documentos: email (recomendado activo).
- Mensajes: email + push (recomendado activo).
- Marketing: email (opt-in explícito).

### 7.3 Frecuencia y anti-spam

- Si el cliente recibió 3+ emails en 24h del sistema, los siguientes se agrupan en un digest diario.
- Mensajes humanos (de agente) nunca se agrupan; siempre llegan inmediatos.
- Recordatorios de pago se cierran al recibir el pago (cero spam post-pago).

---

## 8. Diseño visual, UX y layout

### 8.1 Tono visual

- **Cercano, no técnico.** El portal del cliente no es el CRM admin: tiene fondo claro, navbar limpia, mucho espacio en blanco.
- **Densidad de información media.** Cards en grid, no tablas exhaustivas. La tabla detallada queda para vistas de "ver todo".
- **Micro-animaciones suaves** al cambiar de stage, al confirmar pago, al subir documento. Refuerzan la sensación de pulcritud.
- **Tipografía Inter, mismas familias** que el sitio público.
- **Paleta:** la institucional de HDV (navy + neutros + verde sistema). Sin dorado, sin cobre.

### 8.2 Header

```
[Logo HDV] [Pocos inmuebles. Más control.]    [Búsqueda global ▢]    [🔔 3] [Avatar] ▾
```

- En desktop: header compacto, una sola fila.
- En mobile: hamburguesa + logo + bell + avatar.

### 8.3 Navbar principal (desktop)

```
Inicio · Mis inmuebles · Mi renta · Pagos · Documentos · Mensajes · Soporte
```

Items se ocultan según perfil:
- "Mis inmuebles" sólo si tiene rol propietario.
- "Mi renta" sólo si tiene rol inquilino.

### 8.4 Mobile navigation

Bottom nav con 5 iconos: Inicio · Mis inmuebles / Mi renta · Pagos · Documentos · Mensajes. Soporte y cuenta accesibles desde header.

### 8.5 Dashboard (la pantalla más importante)

Estructura jerárquica clara:

```
[Saludo personalizado: "Hola María, buenas tardes."]
[Banner si hay algo urgente: pago vencido, mensaje sin responder, doc pendiente]

[Card: Operación principal]   [Card: Próximo pago / próximo hito]
                              [Card: Documentos nuevos]

[Timeline: actividad reciente últimos 7 días]

[CTA: ¿Necesitas algo? Hablar con mi agente]
```

Cada card es clicable y lleva a la vista detallada.

### 8.6 Vacío visual

Cuando no hay operaciones activas (cliente alumni), mostrar un estado vacío amistoso:

```
Por ahora no tienes operaciones activas.

Tu historial está siempre disponible en:
   → Documentos      → Mensajes

¿Quieres iniciar una nueva operación?
   [ Vender un inmueble ] [ Comprar ] [ Rentar ]
```

### 8.7 Mobile-first

Más del 70% de los clientes accederán desde teléfono. Cada vista debe probarse en pantallas de 360–414 px antes de declarar "lista".

---

## 9. Permisos, seguridad y privacidad

### 9.1 Autenticación

- Login con email + password.
- Rate limit: 5 intentos por minuto por IP.
- 2FA por email opcional (preparado).
- Recuperación de contraseña con token expirante (30 min).
- Sesión: cookie HTTPS only, secure, SameSite=Lax.
- Logout cierra sesión global; opción "cerrar todas las sesiones" en `/cuenta`.

### 9.2 Autorización

- Middleware `auth + role:client` en todas las rutas del portal.
- A nivel de modelo, `Policy` que verifica que el `Client.id` del usuario autenticado corresponda al recurso solicitado.

```php
// app/Policies/PortalPolicy.php
public function viewProperty(User $user, Property $property): bool {
    return $property->owner_client_id === $user->client_id;
}

public function viewRental(User $user, RentalProcess $rental): bool {
    return $rental->tenant_client_id === $user->client_id
        || $rental->owner_client_id === $user->client_id;
}
```

### 9.3 Privacidad entre partes

**Reglas críticas:**

- Inquilino **no ve** datos personales completos del propietario (sólo nombre o iniciales).
- Propietario **no ve** datos personales completos del inquilino fuera del contrato firmado (donde son obligatorios para identificación).
- Toda comunicación pasa por HDV; no hay "chat directo" propietario-inquilino dentro del portal.
- Excepción: contacto de emergencia (si el propietario lo autorizó explícitamente y el contrato lo permite).

### 9.4 Datos sensibles

- Identificaciones, comprobantes de ingreso, escrituras: almacenados en `documents` con acceso restringido por `Policy`.
- Storage path no predecible (UUID, no autoincrement).
- Servir archivos vía controlador con verificación de permisos, nunca como link público directo.
- Cifrado en reposo para campos `phone` y `rfc` en `clients` (Roadmap fase 4).

### 9.5 Cumplimiento

- Aviso de privacidad accesible desde footer del portal.
- Opción "Descargar mis datos" en `/cuenta`.
- Logs de acceso al portal con IP y user agent (auditoría).
- Exportación de logs disponible para Dirección a petición.

### 9.6 Sesión de admin "como cliente"

Cuando un user del CRM hace "Vista previa del portal" (sección 10), la sesión se etiqueta visualmente:

```
[Banner amarillo arriba]
   Estás viendo el portal como [Nombre del cliente].
   [Salir de la vista previa]
```

Esto evita confusiones y deja registro en `audit_logs`.

---

## 10. Vista "preview as client" desde el admin

Funcionalidad clave para que el equipo HDV pueda:
- Apoyar al cliente que llama con duda ("¿qué ves en pantalla?").
- Verificar que la información del cliente está al día.
- Detectar bugs visuales antes de que el cliente los reporte.

### 10.1 Flujo

1. Usuario admin va a `/admin/clients/{id}` y hace clic en "Vista previa del portal".
2. El sistema valida que el admin tiene permiso `clients.preview_portal`.
3. Se abre nueva pestaña en `miportal.homedelvalle.mx` con sesión impersonada y banner amarillo visible.
4. Audit log registra: `admin_X opened portal preview for client_Y at timestamp`.
5. Admin puede navegar todo el portal pero **no puede** subir documentos, pagar, ni enviar mensajes (el banner deshabilita escritura).
6. Botón "Salir" cierra la sesión impersonada y regresa a `/admin`.

### 10.2 Restricciones

- No se loguea al cliente real (su sesión sigue como esté).
- No se modifican datos de estado (lectura cuando se entra como preview).
- Sólo Dirección General y Supervisores tienen este permiso por default; agentes individuales no, salvo asignación explícita.

---

## 11. Implicaciones técnicas para Claude Code

### 11.1 Tablas que se necesitan o se deben verificar

```sql
-- 1. Verificar que users tiene client_id como FK opcional
ALTER TABLE users ADD COLUMN IF NOT EXISTS client_id BIGINT NULL,
                  ADD CONSTRAINT fk_users_client FOREIGN KEY (client_id) REFERENCES clients(id);

-- 2. Tabla message_threads (nueva) o aprovechar interactions existente
CREATE TABLE message_threads (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  client_id BIGINT NOT NULL,
  operation_id BIGINT NULL,
  rental_process_id BIGINT NULL,
  subject VARCHAR(255) NULL,
  last_message_at TIMESTAMP NULL,
  status ENUM('open','closed','archived') DEFAULT 'open',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_client_status (client_id, status),
  INDEX idx_last_message (last_message_at)
);

CREATE TABLE message_thread_messages (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  thread_id BIGINT NOT NULL,
  author_type VARCHAR(50) NOT NULL, -- 'client' | 'user' | 'system'
  author_id BIGINT NULL,
  body TEXT NOT NULL,
  type ENUM('text','system_event','attachment') DEFAULT 'text',
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
  INDEX idx_thread_created (thread_id, created_at)
);

-- 3. Tabla portal_audit_logs para impersonations y accesos sensibles
CREATE TABLE portal_audit_logs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  action VARCHAR(80) NOT NULL,
  target_type VARCHAR(80) NULL,
  target_id BIGINT NULL,
  ip VARCHAR(45) NULL,
  user_agent TEXT NULL,
  metadata JSON NULL,
  created_at TIMESTAMP NULL,
  INDEX idx_user_action (user_id, action, created_at)
);

-- 4. Verificar columnas en notifications para targeting al portal
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS portal_visible BOOLEAN DEFAULT FALSE;
```

### 11.2 Modelos nuevos

```php
// app/Models/MessageThread.php
class MessageThread extends Model {
    protected $fillable = ['client_id','operation_id','rental_process_id','subject','last_message_at','status'];
    public function client() { return $this->belongsTo(Client::class); }
    public function messages() { return $this->hasMany(MessageThreadMessage::class, 'thread_id'); }
    public function operation() { return $this->belongsTo(Operation::class); }
    public function rental() { return $this->belongsTo(RentalProcess::class, 'rental_process_id'); }
}

// app/Models/MessageThreadMessage.php
class MessageThreadMessage extends Model {
    protected $fillable = ['thread_id','author_type','author_id','body','type','read_at'];
    public function thread() { return $this->belongsTo(MessageThread::class, 'thread_id'); }
    public function author() {
        return $this->morphTo();
    }
}

// app/Models/PortalAuditLog.php
class PortalAuditLog extends Model {
    protected $fillable = ['user_id','action','target_type','target_id','ip','user_agent','metadata'];
    protected $casts = ['metadata' => 'array'];
}
```

### 11.3 Servicio existente a extender: `ClientPortalService`

```php
// app/Services/ClientPortalService.php (extender el existente)

public function createAccount(Client $client): User {
    if ($user = User::where('email', $client->email)->where('client_id', $client->id)->first()) {
        return $user;
    }
    $user = User::create([
        'name' => $client->name,
        'email' => $client->email,
        'password' => Hash::make(Str::random(20)),
        'role' => 'client',
        'client_id' => $client->id,
        'is_active' => true,
    ]);
    $this->sendWelcomeInvitation($user);
    return $user;
}

public function sendWelcomeInvitation(User $user): void {
    $token = $this->generateInvitationToken($user);
    EmailService::sendTemplate('portal_welcome', $user->email, [
        'Nombre' => $user->name,
        'ActivationLink' => route('portal.invitation.accept', ['token' => $token]),
    ]);
}

public function impersonate(User $admin, User $clientUser): void {
    // validar permiso
    if (! $admin->can('clients.preview_portal')) abort(403);

    Auth::guard('web')->logout();
    Auth::guard('web')->login($clientUser);
    session(['impersonator_id' => $admin->id, 'impersonating' => true]);

    PortalAuditLog::create([
        'user_id' => $admin->id,
        'action' => 'impersonate_start',
        'target_type' => User::class,
        'target_id' => $clientUser->id,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
}
```

### 11.4 Triggers automáticos (listeners)

Crear `app/Listeners/`:

- `CreatePortalAccountOnCaptacionSigned` — escucha `OperationStageChanged` y dispara `ClientPortalService::createAccount` cuando la captación firma.
- `CreatePortalAccountOnRentalSigned` — lo mismo cuando se firma contrato de arrendamiento.
- `CreatePortalAccountOnOfferStage` — para compradores/vendedores.

### 11.5 Layouts y vistas

```
resources/views/portal/
  layouts/
    portal.blade.php         (header + navbar + footer + container)
    portal-empty.blade.php   (login, recover, accept invitation)
  auth/
    login.blade.php
    recover.blade.php
    reset.blade.php
    accept-invitation.blade.php
  dashboard/
    index.blade.php
  properties/
    index.blade.php
    show.blade.php
  rentals/
    index.blade.php
    show.blade.php
    incident-form.blade.php
  payments/
    index.blade.php
  documents/
    index.blade.php
  messages/
    index.blade.php
    show.blade.php
  account/
    index.blade.php
```

### 11.6 Componentes Livewire en el portal

A diferencia del sitio público (que usa Alpine), el portal del cliente sí aprovecha Livewire 4 para:

- `Portal\IncidentReportForm` (con upload de fotos vía `WithFileUploads`).
- `Portal\MessageComposer` (con autoguardado del borrador, envío reactivo).
- `Portal\PaymentTracker` (con polling cada 30s para reflejar cambios de estado).
- `Portal\NotificationsBell` (cuenta y dropdown con polling).

### 11.7 Subdominio en producción (cPanel)

En cPanel:
1. Crear subdominio `miportal.homedelvalle.mx` apuntando al mismo `public/`.
2. SSL: emitir certificado para el subdominio (Let's Encrypt automático en cPanel).
3. En `.htaccess` o configuración Laravel: detectar el host y rutear correctamente.
4. Actualizar `APP_URL` y `SESSION_DOMAIN=.homedelvalle.mx` para que las cookies funcionen entre subdominios si se necesita SSO con `/admin`.

### 11.8 Tests críticos

Antes de declarar el portal "listo":

```php
// tests/Feature/Portal/PortalAccessTest.php
public function test_client_cannot_view_another_clients_property() {
    $client1 = Client::factory()->withUser()->create();
    $client2 = Client::factory()->withUser()->create();
    $property = Property::factory()->create(['owner_client_id' => $client2->id]);

    $this->actingAs($client1->user)
         ->get("/mis-inmuebles/{$property->id}")
         ->assertForbidden();
}

public function test_tenant_does_not_see_owner_personal_data() {
    // ...verificar campos masked
}

public function test_impersonation_creates_audit_log() {
    // ...
}
```

---

## 12. Roadmap de implementación en fases

### Fase 1 — Base (2 semanas)

- [ ] Configurar subdominio `miportal.homedelvalle.mx` en cPanel.
- [ ] Crear `routes/portal.php` con rutas de auth + dashboard.
- [ ] Crear layout `portal.blade.php` con navbar y header.
- [ ] Migrar la lógica del actual `/portal` al subdominio (no romper accesos existentes).
- [ ] Auth: login, logout, recuperar contraseña, aceptar invitación.
- [ ] Dashboard con cards básicas para inquilino y propietario.

### Fase 2 — Documentos y mensajes (2 semanas)

- [ ] Lista de documentos del cliente con descarga segura.
- [ ] Subida de documentos por el cliente con Spatie Media Library.
- [ ] Threads de mensajes con HDV (modelos + UI Livewire).
- [ ] Notificaciones básicas in-portal (bell icon).
- [ ] Plantilla de email `portal_welcome` y trigger automático al firmar captación o arrendamiento.

### Fase 3 — Pagos y renta activa (3 semanas)

- [ ] Vista detallada de "Mi renta" para inquilino con próximo pago, historial, recibos.
- [ ] Vista para propietario de inmueble rentado con datos del inquilino, reportes mensuales descargables.
- [ ] Generación de recibo PDF al confirmar pago (DomPDF).
- [ ] Reporte mensual al propietario (job programado, sección 10 del Proceso de Renta).
- [ ] Notificaciones de pago: recordatorio, confirmación, atraso.

### Fase 4 — Operaciones activas y timelines (2 semanas)

- [ ] Vista timeline de operación de venta con stages, próximas tareas, documentos.
- [ ] Vista timeline de captación.
- [ ] Reportar incidente para inquilino con upload de fotos.
- [ ] Vista de incidentes para propietario con seguimiento.
- [ ] Onboarding del primer login.

### Fase 5 — Vista "preview as client" y refinamiento (1 semana)

- [ ] Botón "Vista previa del portal" en `/admin/clients/{id}`.
- [ ] Banner de impersonación visible en el portal.
- [ ] Audit logs activos.
- [ ] Centro de preferencias de notificación en `/cuenta`.
- [ ] Búsqueda global en el header.
- [ ] Pruebas de accesibilidad y mobile.

### Fase 6 — Integraciones avanzadas (continuo)

- [ ] Pasarela de pago (Stripe / Conekta / Mercado Pago) para pagar renta desde el portal.
- [ ] Firma electrónica integrada (Mifiel ya está en CRM) para contratos.
- [ ] Calendario integrado para agendar visitas (vendedores y compradores).
- [ ] Push notifications nativas (mobile app futura o PWA).
- [ ] App nativa iOS/Android (long term).

---

## 13. Checklist de QA

### Acceso y seguridad
- [ ] Login funciona y redirige a `/inicio`.
- [ ] Recuperar contraseña envía email con link válido por 30 min.
- [ ] Aceptar invitación con token válido permite establecer contraseña.
- [ ] Token de invitación caduca a los 7 días.
- [ ] Rate limit de login funciona (5 intentos/min/IP).
- [ ] Cliente A no puede ver datos del cliente B (test de policy).
- [ ] Inquilino no ve datos personales completos del propietario.
- [ ] Logout cierra sesión y previene back-button con caché.

### Funcional
- [ ] Dashboard muestra cards correctas según perfil.
- [ ] Multiperfil (cliente con 2 roles) muestra ambas secciones.
- [ ] Documentos descargables sirven sólo a su dueño.
- [ ] Subida de documentos funciona y guarda con Spatie Media Library.
- [ ] Mensajes llegan al CRM admin en tiempo real (o con polling razonable).
- [ ] Mensajes de HDV llegan al portal del cliente.
- [ ] Notificaciones por email se envían según las preferencias del cliente.
- [ ] Banner "pago vencido" aparece cuando aplica.
- [ ] Reporte mensual se genera y descarga en PDF.

### Datos
- [ ] La cuenta de portal se crea automáticamente al firmar captación o arrendamiento.
- [ ] El email de bienvenida llega < 60 s.
- [ ] Cliente alumni puede entrar a ver historial sin operaciones activas.
- [ ] Audit log registra cada impersonation y acceso a documento sensible.

### UX y rendimiento
- [ ] Mobile-first verificado en 360, 414, 768 px.
- [ ] Lighthouse mobile > 85 en Performance, Accessibility, Best Practices.
- [ ] Modal de bienvenida aparece sólo en primer login.
- [ ] Banner de impersonación visible en cualquier pantalla cuando aplica.
- [ ] Centro de ayuda contextual abre con FAQ relevante a la sección.
- [ ] Búsqueda global funciona en documentos, inmuebles, mensajes, pagos.

### Marca
- [ ] Logo HDV en header.
- [ ] Slogan en footer.
- [ ] Paleta navy + neutros + verde sistema (sin dorado).
- [ ] Tipografía Inter consistente.
- [ ] Tono del copy alineado con Manual de Marca y Voz.

### Subdominio
- [ ] `miportal.homedelvalle.mx` resuelve correctamente.
- [ ] SSL activo y certificado válido.
- [ ] Cookies de sesión funcionan en el subdominio.
- [ ] Redirección desde `/portal` (vieja URL) al subdominio nuevo (preservar bookmarks de clientes existentes).

---

**Fin del documento.**

Cualquier ajuste sustantivo (nuevas vistas, nuevos perfiles, nuevas integraciones de pago) requiere actualizar este documento antes de implementarse en código.

**Mantenedor:** Alex.
**Próxima revisión sugerida:** mensual durante el primer trimestre de implementación, luego trimestral.
