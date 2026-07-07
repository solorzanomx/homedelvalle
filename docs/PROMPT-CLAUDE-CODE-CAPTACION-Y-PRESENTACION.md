# Prompt para Claude Code · Módulo de Captación + Presentación Inicial
## Home del Valle — Wizard "Nueva captación desde llamada" + Generador de Presentación PDF

> **Para Claude Code:** este prompt es la orden completa de trabajo. Léelo entero antes de tocar código. Las decisiones de negocio (Capa 1), experiencia (Capa 2) y técnicas (Capa 3) ya fueron tomadas por Alex el 2026-04-29 y están consignadas abajo. **No re-abras decisiones tomadas** — si encuentras conflicto técnico, pregunta antes de cambiar de rumbo.
>
> **Preparado:** 2026-04-29 · Alex (Director de Estrategia y Crecimiento)
> **Repo:** `/Users/alejandro/homedelvalle`
> **Stack:** Laravel 13.6.0 · PHP 8.3.30 · Livewire 4.2.4 · Tailwind 4.2.2 · Browsershot 5.2.3 + Puppeteer 24.42 · MySQL · cPanel.

---

## 0. Lectura obligatoria antes de tocar código

Lee estos archivos del repo en este orden. Sin esa lectura no entiendes el contexto y vas a romper convenciones.

1. `IMPLEMENTATION_RULES.md` (raíz) — convenciones que NUNCA se rompen.
2. `CRITICAL_VERSIONS.md` (raíz) — versiones de librerías.
3. `CONTEXTO_PROYECTO.md` (raíz) — snapshot del proyecto.
4. `docs/00-PLAYBOOK-REVISION-3-CAPAS.md` — método de revisión aplicado a este módulo.
5. `docs/02-MANUAL-IMPLEMENTACION-SITIO.md` — stack, estructura, convenciones técnicas.
6. `docs/04-ROADMAP-Y-ARQUITECTURA.md` — visión Opción C, fase actual.
7. `docs/05-PROCESO-DE-RENTA.md` — proceso de renta (espejo conceptual de este módulo).
8. `docs/06-PORTAL-DEL-CLIENTE.md` — spec del portal (este módulo se integra con él).
9. `.claude/SCHEMA_QUICK_REFERENCE.md` — cheat sheet del esquema.
10. `app/Models/Captacion.php` — modelo que ya existe con 4 etapas (etapa1 docs, etapa2 valuación, etapa3 precio, etapa4 firma).
11. `app/Http/Controllers/Admin/CaptacionAdminController.php` — CRUD existente.
12. `app/Services/CaptacionService.php` — lógica del wizard actual.
13. `routes/web.php` líneas 624–642 — rutas de captaciones y rentas.

### Reglas no negociables que extraerás de esos documentos

- Ambiente: **CRM admin** (`homedelvalle.mx/admin`). Stack: Blade + CSS puro + **Livewire 4 donde aporte UX**. Aprobado por Alex 2026-04-29 — Livewire ES la regla para componentes interactivos en admin.
- Jobs corren **síncronos** vía `schedule:run` (cron cPanel). NO uses `ShouldQueue`.
- Cache NUNCA almacena objetos Eloquent — sólo arrays/IDs.
- Email vía **PHPMailer + SMTP dinámico** desde tabla `email_settings`. No Laravel Mail.
- Uploads vía **Spatie Media Library**. Nunca `Storage::put` a mano.
- Iconos: **Lucide-static** SVG inline.
- Marca: **"Home del Valle"** (V mayúscula). Cero "Home del valle" minúscula.
- Paleta: **navy + neutros + verde sistema**. Cero dorado, cero cobre.
- **Generación de PDF: Browsershot** (no DomPDF). Las presentaciones tienen fotos, layout boutique, Tailwind.

---

## 1. Objetivo del módulo

Construir en `/admin` una experiencia completa para que un agente de HDV, **después de colgar la primera llamada con un propietario**, pueda en pocos minutos:

1. **Dar de alta al cliente** con todos los datos que recolectó en la llamada (lo mínimo + lo extra si lo tiene).
2. **Dar de alta el inmueble** asociado con tipo, ubicación, características.
3. **Definir la intención** de la captación (venta a constructor / venta residencial / venta comercial / renta residencial / renta comercial / general).
4. **Generar una "presentación inicial"** en PDF (brief comercial boutique) con preview inline editable.
5. **Enviar la presentación** por email y/o WhatsApp (escritorio, vía `wa.me`) al propietario.
6. **Trackear todo** — tiempo a envío, apertura del email, click en link, vista del PDF, descarga.

**Más tarde** (no en este módulo), el agente puede marcar el caso como "no encaja con HDV" (declinar amistosamente) — eso queda como acción en la vista de la captación, fuera del wizard de alta.

**SLA objetivo:** del colgar la llamada a presentación enviada en **< 10 minutos**. Esto cambia el funnel: hoy esto toma días.

---

## 2. Lo que YA existe (NO recrear)

| Pieza | Estado | Cómo se reusa |
|---|---|---|
| Modelo `Captacion` con 4 etapas | ✅ Existe | Extender con campos nuevos vía migración (intent, commission_pct, marketing_plan, notes_from_call, source, status) |
| Modelos `Client`, `Property`, `PropertyValuation`, `Document` | ✅ Existen | Reusar. `Client::findOrCreate` por email/teléfono. |
| Modelo `Operation` con `type='captacion'` | ✅ Existe | Crear una `Operation` por cada `Captacion` con `metadata.intent` correspondiente. |
| `Admin/CaptacionAdminController` | ✅ Existe con index/show/etc. | Agregar métodos: `createFromCall`, `storeFromCall`, `presentation`, `sendPresentation`, `declineCase`. |
| Rutas `/admin/captaciones/*` | ✅ Activas (líneas 631–642 de routes/web.php) | Agregar 4 rutas nuevas (`create-from-call`, `store-from-call`, `presentation`, `send-presentation`, `decline`). |
| Sidebar admin con "Captaciones" | ✅ Existe | Agregar botón global "+ Nueva captación" en topbar (decisión Capa 2.A). |
| `ContractTemplate` y `Contract` | ✅ Existen | Extender `ContractTemplate` con columna `type` que admita `contract` y `presentation`. Las presentaciones son templates con variables, mismo motor. |
| `ContractService` | ✅ Existe | Crear `PresentationGeneratorService` separado para no mezclar lógica legal con comercial. |
| `EmailService` con PHPMailer + templates | ✅ Existe | Usar `EmailService::sendTemplate('presentation_initial', ...)` con PDF attach. |
| `WhatsAppService` | ✅ Existe | Verificar capacidades; usaremos modo `wa.me` (no API). |
| Patrón `/firma/{token}` para URL pública | ✅ Existe (línea 658) | Replicar para `/presentaciones/{token}` con PDF público y tracking. |
| Spatie Media Library 11.21.2 | ✅ Instalado | Usar para fotos del inmueble subidas en wizard. Collection `property_photos` o `presentation_attachments`. |
| Browsershot 5.2.3 + Puppeteer 24.42 | ✅ Instalado | Motor de PDF para las presentaciones. |
| Livewire 4.2.4 | ✅ Instalado y aprobado en admin | Wizard de 3 pasos + preview inline = ideal para Livewire. |

---

## 3. Lo que se construye (nuevo)

### 3.1 Tablas / migraciones

```php
// database/migrations/2026_05_xx_extend_captaciones_for_intake.php
Schema::table('captaciones', function (Blueprint $table) {
    $table->foreignId('property_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
    $table->foreignId('operation_id')->nullable()->after('property_id')->constrained('operations')->nullOnDelete();
    $table->enum('intent', [
        'general',
        'venta_constructor',
        'venta_residencial',
        'venta_comercial',
        'renta_residencial',
        'renta_comercial',
    ])->default('general')->after('operation_id');
    $table->decimal('commission_pct', 5, 2)->default(5.00)->after('intent');
    $table->text('marketing_plan')->nullable()->after('commission_pct');
    $table->text('notes_from_call')->nullable()->after('marketing_plan');
    $table->enum('source', ['phone_call','whatsapp_inbound','web_form','referral','other'])
          ->default('phone_call')->after('notes_from_call');
    // status ya existe; ampliar valores si es enum: 'new','active','declined','converted'
    $table->foreignId('created_by_user_id')->nullable()->after('source')->constrained('users');
    $table->timestamp('declined_at')->nullable();
    $table->text('declined_reason')->nullable();
});

// database/migrations/2026_05_xx_extend_contract_templates_for_presentations.php
Schema::table('contract_templates', function (Blueprint $table) {
    $table->enum('type', ['contract','presentation'])->default('contract')->after('id');
    $table->string('intent_target', 40)->nullable()->after('type');
    // intent_target: 'general' | 'venta_constructor' | etc.
    $table->index(['type','intent_target']);
});

// database/migrations/2026_05_xx_create_presentation_sends.php
Schema::create('presentation_sends', function (Blueprint $table) {
    $table->id();
    $table->foreignId('captacion_id')->constrained()->cascadeOnDelete();
    $table->enum('channel', ['email','whatsapp','download']);
    $table->foreignId('sent_by_user_id')->nullable()->constrained('users');
    $table->string('recipient_email')->nullable();
    $table->string('recipient_phone', 30)->nullable();
    $table->string('tracking_token', 64)->unique();
    $table->timestamp('sent_at')->useCurrent();
    $table->timestamp('email_opened_at')->nullable();
    $table->timestamp('link_clicked_at')->nullable();
    $table->timestamp('pdf_viewed_at')->nullable();
    $table->unsignedInteger('pdf_view_count')->default(0);
    $table->timestamp('pdf_downloaded_at')->nullable();
    $table->ipAddress('last_view_ip')->nullable();
    $table->string('last_view_user_agent')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->index(['captacion_id','channel']);
    $table->index('tracking_token');
});
```

### 3.2 Modelos nuevos / extendidos

```php
// app/Models/PresentationSend.php
class PresentationSend extends Model {
    protected $fillable = [
        'captacion_id','channel','sent_by_user_id','recipient_email','recipient_phone',
        'tracking_token','sent_at','email_opened_at','link_clicked_at','pdf_viewed_at',
        'pdf_view_count','pdf_downloaded_at','last_view_ip','last_view_user_agent','metadata',
    ];
    protected $casts = [
        'sent_at' => 'datetime',
        'email_opened_at' => 'datetime',
        'link_clicked_at' => 'datetime',
        'pdf_viewed_at' => 'datetime',
        'pdf_downloaded_at' => 'datetime',
        'metadata' => 'array',
    ];
    public function captacion() { return $this->belongsTo(Captacion::class); }
    public function sentBy() { return $this->belongsTo(User::class, 'sent_by_user_id'); }
}

// app/Models/Captacion.php — agregar relaciones y métodos
public function operation() { return $this->belongsTo(Operation::class); }
public function property() { return $this->belongsTo(Property::class); }
public function sends() { return $this->hasMany(PresentationSend::class); }
public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }

public function timeToFirstSend(): ?int {
    // minutos desde created_at hasta primer send registrado
    $first = $this->sends()->orderBy('sent_at')->first();
    return $first ? $this->created_at->diffInMinutes($first->sent_at) : null;
}

public function isDeclined(): bool {
    return $this->status === 'declined' || ! is_null($this->declined_at);
}
```

### 3.3 Servicios nuevos

```php
// app/Services/CaptacionIntakeService.php
class CaptacionIntakeService {
    public function createFromCall(array $data, User $agent): Captacion {
        // 1. Buscar o crear Client (por email O teléfono)
        // 2. Crear Property con status='draft'
        // 3. Crear Operation type='captacion', stage='qualification_call', metadata.intent
        // 4. Crear Captacion vinculando los tres
        // 5. Log en operation_stage_logs
        // 6. Retornar Captacion
    }
}

// app/Services/PresentationGeneratorService.php
class PresentationGeneratorService {
    public function __construct(
        protected EmailService $email,
    ) {}

    public function selectTemplate(Captacion $captacion): ContractTemplate {
        // 1. Buscar template type='presentation' intent_target=$captacion->intent
        // 2. Si no existe, fallback a intent_target='general'
    }

    public function renderHtml(Captacion $captacion, array $overrides = []): string {
        // Reemplaza variables del template Blade con datos del captacion
        // Variables disponibles: {{NombrePropietario}}, {{TelefonoCliente}}, {{Inmueble.*}},
        // {{ComisionPct}}, {{PrecioSugerido}}, {{PlanMarketing}}, {{Agente.*}}, {{Fecha}}
    }

    public function generatePdf(Captacion $captacion, array $overrides = []): string {
        // 1. Render HTML con renderHtml()
        // 2. Browsershot::html($html)->setOption('format','Letter')->setMargins(...)
        //    ->showBackground()->emulateMedia('print')->pdf()
        // 3. Guardar en storage/app/presentations/{captacion_id}/{timestamp}.pdf
        // 4. Retornar path
    }

    public function publicUrl(PresentationSend $send): string {
        // route('presentation.public', ['token' => $send->tracking_token])
    }

    public function sendByEmail(Captacion $captacion, string $email, User $agent, array $overrides = []): PresentationSend {
        // 1. Generar PDF
        // 2. Crear PresentationSend con channel='email', tracking_token, recipient_email
        // 3. EmailService::sendTemplate('presentation_initial', $email, [...])
        //    con attach del PDF y pixel de tracking en HTML
        // 4. Retornar send
    }

    public function sendByWhatsApp(Captacion $captacion, string $phone, User $agent, array $overrides = []): array {
        // 1. Generar PDF
        // 2. Crear PresentationSend con channel='whatsapp', recipient_phone, tracking_token
        // 3. Construir URL pública con token
        // 4. Construir mensaje precargado:
        //    "Hola [Nombre], te envío la presentación de Home del Valle para tu inmueble: [URL]"
        // 5. Retornar ['send_id' => $send->id, 'wa_me_url' => 'https://wa.me/52'.$phone.'?text=...']
        // 6. El frontend abre esa URL en nueva pestaña; el agente envía desde su WhatsApp escritorio
    }
}
```

---

## 4. Decisiones de Capa 1 — Negocio (APROBADAS POR ALEX 2026-04-29)

### A. Perfil de cliente — completo, no minimal
El wizard captura **lo mínimo obligatorio** (nombre + teléfono) y **deja abiertos** los campos extra (email, estado civil, copropietarios, gravámenes, motivo, timing, RFC, dirección actual). Si el agente los tiene de la llamada, los captura. Si no, los completa después en la vista de la captación.

**Implicación:** ningún campo del wizard es obligatorio salvo nombre + teléfono. Email es opcional **pero condicionante**: si no hay email, la opción "enviar por email" queda deshabilitada y sólo se ofrece WhatsApp. Mostrar leyenda: *"Tip: pídele al propietario su correo y WhatsApp antes de colgar para enviarle la presentación en seguida."*

### B. SLA objetivo: lo más rápido posible
Meta: **< 10 minutos** desde colgar la llamada hasta presentación enviada. Esto es lo que define la calidad de servicio HDV. La velocidad importa.

**Implicación:** el wizard debe estar diseñado para terminar en 3 minutos. El preview del PDF debe regenerarse en < 2 segundos cuando el agente edita una variable. El botón de envío no debe requerir pasos adicionales.

### C. Comisión editable, default 5%
La presentación incluye **comisión propuesta**. Default 5%. **Editable por el agente** al captar (slider o input numérico de 0–10%). Si el agente acordó otra comisión en la llamada, la cambia. Si no, deja 5%.

**Implicación:** campo `Captacion.commission_pct` decimal(5,2) default 5.00. En el wizard, paso 3, input visible con valor sugerido 5%. En la presentación PDF, el % aparece destacado.

### D. La presentación es un "brief comercial poderoso", no un contrato
La presentación NO es legalmente vinculante. Es la herramienta de cierre comercial post-llamada. Diseño recomendado (6 páginas):

1. **Portada:** logo HDV + slogan + foto del inmueble (si el cliente la dio) + nombre del propietario + fecha + nombre del agente. Tono boutique, navy de fondo.
2. **Quiénes somos:** HDV en 100 palabras. 30+ años. Boutique. Diferenciadores (pocos inmuebles, dirección general involucrada, blindaje legal Ana Laura).
3. **Lo que proponemos para tu inmueble:** sección variable según `intent`. Para venta_constructor habla de "potencial de desarrollo y nuestra red de constructores activos". Para venta_residencial habla de "comprador final calificado y plan de marketing". Para renta habla de "calificación seria de inquilinos y póliza jurídica".
4. **Plan de marketing:** 4–5 bullets de qué hace HDV (fotos profesionales, ficha editorial, distribución dirigida, red de contactos privados, observatorio de precios `/mercado`). El agente puede editar este texto en el wizard.
5. **Servicios incluidos sin costo extra:** valuación profesional, blindaje legal, gestoría documental, asesoría fiscal básica, acompañamiento al notario.
6. **Comisión y próximos pasos:** % editado en wizard + propuesta de "agendar visita técnica esta semana" + datos del agente (foto, teléfono, email, WhatsApp).

**Disclaimer pequeño al pie de la última página:**
*"Este documento es informativo y no constituye oferta vinculante. Los términos comerciales se formalizan al firmar el Acuerdo de Representación con Home del Valle Bienes Raíces."*

### E. Stage "declinar caso" (filtro boutique)
**Después** del wizard (no dentro), en la vista de la captación, debe existir un botón **"Declinar amistosamente"** con campo de razón obligatoria. Esto:
- Cambia `Captacion.status` a `declined`.
- Persiste `declined_at` y `declined_reason`.
- Mueve la `Operation` asociada a `status='cancelled'` con motivo en `operation_comments`.
- NO genera ni envía presentación (si se intentó previamente, no se envía a futuro).
- Dispara email automático al propietario con mensaje cuidadoso de "no es el caso correcto para HDV en este momento" (template `captacion_declined_friendly`).

### F. 1 inmueble por captación
Confirmado. Si el propietario tiene 3 inmuebles, se crean 3 captaciones distintas. El `Client` se reutiliza (`Client::findOrCreate` por email/teléfono). Cada captación tiene su propia presentación.

**Implicación:** el wizard NO ofrece "agregar otro inmueble en esta captación". Si el agente lo necesita, sale del wizard, va a `/admin/captaciones/create-from-call` y arranca otro flujo.

### G. 1 presentación por captación, 1 destinatario
Sólo se envía a 1 contacto (el principal). Si hay copropiedad, el contacto principal se encarga de distribuir internamente.

**Implicación:** `PresentationSend.recipient_email` y `recipient_phone` son escalares, no JSON. Un envío = un destinatario.

### H. Métricas — todo medible
Tracking exhaustivo en `presentation_sends`:
- `sent_at` — cuándo se envió.
- `email_opened_at` — pixel 1x1 en email HTML.
- `link_clicked_at` — click en cualquier link dentro del email/WhatsApp.
- `pdf_viewed_at` + `pdf_view_count` — visitas a la URL pública del PDF.
- `pdf_downloaded_at` — click en botón descargar dentro de la vista pública.
- `last_view_ip` + `last_view_user_agent` — contexto de la última vista.

**Vista de métricas:** dashboard nuevo en `/admin/captaciones/{id}` con tab "Presentaciones enviadas" mostrando timeline de cada envío con cada estado. Además agregado en `/admin/analytics` con KPIs: tiempo promedio llamada→envío, tasa de apertura email, tasa de view del PDF, tasa de conversión envío → etapa 2 (valuación vinculada).

---

## 5. Decisiones de Capa 2 — UX (APROBADAS POR ALEX 2026-04-29)

### A. Punto de entrada — botón global en topbar
Agregar botón **"+ Nueva captación"** en el header del CRM admin (`layouts/app-sidebar.blade.php`), visible siempre, lado derecho del topbar. Atajo de teclado sugerido: `Ctrl/Cmd + Shift + N`.

Click → `/admin/captaciones/create-from-call` (wizard Livewire).

### B. Wizard de 3 pasos
- **Paso 1 — Cliente** (obligatorio: nombre + teléfono; opcional: email, estado civil, RFC, dirección actual, copropietarios, notas).
- **Paso 2 — Inmueble** (obligatorio: tipo + colonia; opcional: m², recámaras, baños, estacionamientos, precio esperado, dirección exacta, fotos del cliente vía Spatie Media Library).
- **Paso 3 — Intención y propuesta** (intent — default `general`; comisión — default 5% editable; plan de marketing — texto editable con template inicial según intent; notas de la llamada).

Indicador de progreso visible (1 de 3, 2 de 3, 3 de 3). Botones "Anterior" y "Siguiente". En el último paso: "Guardar y generar presentación" o "Guardar sin presentación".

### C. Preview del PDF — inline
Después del wizard (o desde la vista de la captación), pantalla con **split view**:
- Lado izquierdo: variables editables de la presentación (precio sugerido, comisión, plan de marketing, foto principal si hay varias). Livewire reactivo.
- Lado derecho: **iframe con preview del PDF**, regenerado en < 2 segundos al cambiar cualquier variable.

Debajo: 3 botones de acción.
- `Enviar por email` (deshabilitado si no hay email del cliente).
- `Enviar por WhatsApp` (deshabilitado si no hay teléfono).
- `Descargar PDF` (siempre disponible).

### D. Diseño del PDF
Estructura 6 páginas según Capa 1.D. **Mobile-friendly al visualizarlo en el celular del propietario** (porque la mayoría lo abrirá desde WhatsApp). Tipografía Inter. Paleta navy + neutros + verde sistema. **Cero dorado**.

### E. Mobile (360px)
El wizard funciona en mobile (agente entre visitas). Paso a paso ocupa la pantalla completa. Preview del PDF queda como modal full-screen en mobile (no split view).

### F. Leyenda de captura de datos
En el paso 1 del wizard, mostrar tip pequeño debajo del campo email:
*"Si el propietario te dio su correo y WhatsApp, captúralos ahora — la presentación se envía en seguida."*
Y debajo del campo teléfono:
*"Pregunta el medio preferido para recibir la presentación (correo / WhatsApp / ambos)."*

---

## 6. Decisiones de Capa 3 — Técnicas

### A. Stack — Livewire 4 en wizard y preview
- Componente `CreateCaptacionFromCall` en `app/Livewire/Admin/CreateCaptacionFromCall.php` con 3 pasos navegables.
- Componente `PresentationEditor` en `app/Livewire/Admin/PresentationEditor.php` con preview inline.
- Usar `wire:model.live.debounce.500ms` en campos del editor para regenerar PDF preview.
- Usar `WithFileUploads` trait para fotos en paso 2 del wizard.

### B. PDF — Browsershot + Tailwind
- Plantilla Blade en `resources/views/pdf/presentations/{intent}.blade.php`. Una por intent + `general.blade.php` como fallback.
- Tailwind 4 compilado para PDF en `resources/css/pdf.css` con clases utilitarias específicas para impresión.
- `Browsershot::html($html)->setNodeBinary(config('browsershot.node_binary'))->format('Letter')->margins(15,15,15,15)->showBackground()->emulateMedia('print')->savePdf($path)`.
- Si Browsershot da problemas con la versión de Puppeteer, fallback a DomPDF pero **menos boutique**. Reportar antes de aceptar.

### C. URL pública con token
Ruta nueva en `routes/web.php`:
```php
Route::get('/presentaciones/{token}', [PresentationPublicController::class, 'show'])
    ->name('presentation.public');
Route::get('/presentaciones/{token}/descargar', [PresentationPublicController::class, 'download'])
    ->name('presentation.download');
```

`PresentationPublicController@show`:
- Busca `PresentationSend::where('tracking_token', $token)->firstOrFail()`.
- Si `pdf_viewed_at` es null, lo setea ahora.
- Incrementa `pdf_view_count`.
- Persiste `last_view_ip`, `last_view_user_agent`.
- Renderiza vista pública con iframe del PDF + botón "Descargar" + footer con datos del agente.

`PresentationPublicController@download`:
- Persiste `pdf_downloaded_at`.
- Sirve el archivo con `response()->download(...)`.

### D. Email — EmailService con attach
```php
$pdfPath = $presentationService->generatePdf($captacion, $overrides);
$send = PresentationSend::create([
    'captacion_id' => $captacion->id,
    'channel' => 'email',
    'sent_by_user_id' => $agent->id,
    'recipient_email' => $email,
    'tracking_token' => Str::random(40),
    'sent_at' => now(),
]);

EmailService::sendTemplate(
    template: 'presentation_initial',
    to: $email,
    variables: [
        'NombrePropietario' => $captacion->client->name,
        'NombreInmueble'    => $captacion->property_address,
        'NombreAgente'      => $agent->name,
        'TrackingPixel'     => route('email.tracking', $send->tracking_token),
        'PresentationUrl'   => route('presentation.public', $send->tracking_token),
    ],
    attachments: [$pdfPath],
);
```

Pixel de tracking en el HTML del email: `<img src="{{TrackingPixel}}" width="1" height="1" />`. La ruta `email.tracking` persiste `email_opened_at` en el `PresentationSend` correspondiente.

### E. WhatsApp — wa.me (modo escritorio del agente)
```php
public function sendByWhatsApp(Captacion $captacion, string $phone, User $agent, array $overrides): array {
    $pdfPath = $this->generatePdf($captacion, $overrides);
    $send = PresentationSend::create([
        'captacion_id' => $captacion->id,
        'channel' => 'whatsapp',
        'sent_by_user_id' => $agent->id,
        'recipient_phone' => $phone,
        'tracking_token' => Str::random(40),
        'sent_at' => now(),
    ]);

    $publicUrl = route('presentation.public', $send->tracking_token);
    $message = sprintf(
        "Hola %s, soy %s de Home del Valle. Te comparto la presentación inicial para tu inmueble en %s.\n\nPuedes verla aquí: %s\n\nQuedo atento a tus comentarios.",
        $captacion->client->name,
        $agent->name,
        $captacion->property_address,
        $publicUrl,
    );

    $phoneClean = preg_replace('/\D+/', '', $phone);
    if (substr($phoneClean, 0, 2) !== '52') $phoneClean = '52'.$phoneClean;

    return [
        'send_id' => $send->id,
        'wa_me_url' => 'https://wa.me/'.$phoneClean.'?text='.urlencode($message),
    ];
}
```

El componente Livewire abre `wa_me_url` en nueva pestaña (`window.open(...)`). El agente envía desde su WhatsApp Web/escritorio.

### F. Tracking — dashboard en /admin/captaciones/{id}
Tab "Presentaciones enviadas" con tabla:
| Fecha | Canal | Destinatario | Estado | Aperturas | Última vista |
|---|---|---|---|---|---|
| 2026-05-01 14:30 | Email | juan@example.com | ✅ Visto | 2 | hace 3h |
| 2026-05-01 14:32 | WhatsApp | +52 55 1234 5678 | 👀 No abierto | 0 | — |

Click en una fila abre detalle del send con timeline completo.

### G. Métricas en /admin/analytics
Widgets nuevos:
- "Tiempo promedio llamada → presentación enviada (últimos 30 días)".
- "Tasa de apertura de emails de presentación".
- "Tasa de vista del PDF (todos los canales)".
- "Conversión: presentación enviada → captación pasa a etapa 2 (valuación)".

---

## 7. Plantillas de presentación (seeders)

Seeder `database/seeders/PresentationTemplatesSeeder.php`. Crear 6 templates en `contract_templates` con `type='presentation'`:

1. `intent_target='general'` — fallback genérico cuando el agente no decide.
2. `intent_target='venta_constructor'` — foco en potencial de desarrollo, H5/H6, red de constructores.
3. `intent_target='venta_residencial'` — foco en comprador final, marketing dirigido, plazos.
4. `intent_target='venta_comercial'` — foco en perfil de inversionista, rentabilidad.
5. `intent_target='renta_residencial'` — foco en calificación de inquilino, póliza jurídica, administración integral.
6. `intent_target='renta_comercial'` — foco en arrendamiento comercial, plazos largos, ajustes.

Cada template tiene variables `{{NombrePropietario}}`, `{{InmuebleTipo}}`, `{{InmuebleColonia}}`, `{{ComisionPct}}`, `{{PrecioSugerido}}`, `{{PlanMarketing}}`, `{{NombreAgente}}`, `{{TelefonoAgente}}`, `{{EmailAgente}}`, `{{FechaPresentacion}}`, `{{LogoUrl}}`, `{{SloganHDV}}`.

Templates Blade en `resources/views/pdf/presentations/`:
- `_layout.blade.php` — layout maestro con header/footer común.
- `general.blade.php`
- `venta_constructor.blade.php`
- `venta_residencial.blade.php`
- `venta_comercial.blade.php`
- `renta_residencial.blade.php`
- `renta_comercial.blade.php`

---

## 8. Plantilla de email transaccional

Seeder `database/seeders/PresentationEmailTemplateSeeder.php` crea template `presentation_initial` en `email_templates`:

```
Asunto: Tu presentación de Home del Valle — {{NombreInmueble}}

Hola {{NombrePropietario}},

Gracias por la llamada de hoy. Como te comenté, te envío la presentación
inicial de Home del Valle para tu inmueble en {{NombreInmueble}}.

→ Verla en línea: {{PresentationUrl}}
→ Adjunta también en PDF

Si tienes dudas o quieres que agendemos visita técnica, respóndeme este
correo o escríbeme por WhatsApp.

{{NombreAgente}}
Home del Valle Bienes Raíces
Pocos inmuebles. Más control. Mejores resultados.
Heriberto Frías 903-A · Col. del Valle · CDMX

<img src="{{TrackingPixel}}" width="1" height="1" alt="" />
```

---

## 9. Plan de PRs sugerido

### PR 1 — Schema + modelos (1 sem)
- 3 migraciones (extend captaciones, extend contract_templates, create presentation_sends).
- Modelo `PresentationSend`.
- Extensiones de modelo `Captacion`.
- Seeder de plantillas de presentación (6 entries).
- Seeder de plantilla de email `presentation_initial`.
- Tests: migraciones corren limpio, modelos validan.

### PR 2 — Wizard Livewire CreateCaptacionFromCall (1 sem)
- Componente Livewire 4 con 3 pasos.
- `CaptacionIntakeService::createFromCall()`.
- Ruta `/admin/captaciones/create-from-call`.
- Botón global "+ Nueva captación" en topbar.
- Atajo de teclado Ctrl/Cmd+Shift+N.
- Tests: paso a paso, validaciones mínimas, creación end-to-end de Client+Property+Operation+Captacion.

### PR 3 — Generador de PDF con Browsershot (1 sem)
- `PresentationGeneratorService` con métodos `selectTemplate`, `renderHtml`, `generatePdf`.
- Plantilla Blade `_layout.blade.php` con header/footer común.
- Plantilla `general.blade.php` (la primera; resto en PR 4).
- Configuración de Browsershot probada en local + cPanel.
- Tests: generación con datos reales, PDF válido, paginación correcta.

### PR 4 — Plantillas de presentación por intent (1 sem)
- 5 plantillas Blade más (`venta_constructor`, `venta_residencial`, `venta_comercial`, `renta_residencial`, `renta_comercial`).
- Lógica de selección de template según `Captacion.intent` con fallback a `general`.
- Revisión visual de cada plantilla en formato Letter.

### PR 5 — Editor con preview inline (1 sem)
- Componente Livewire `PresentationEditor` con split view (editable izq, preview iframe der).
- Ruta `/admin/captaciones/{id}/presentacion`.
- Regeneración del PDF preview en < 2s al cambiar variables (debounce 500ms).
- Mobile: preview pasa a modal full-screen.
- Tests: regeneración funciona, datos persisten.

### PR 6 — Envío por email + WhatsApp + URL pública (1 sem)
- `PresentationGeneratorService::sendByEmail` y `sendByWhatsApp`.
- `PresentationPublicController` para `/presentaciones/{token}` y `/presentaciones/{token}/descargar`.
- Vista pública con iframe del PDF + tracking.
- Pixel de tracking en email + ruta `email.tracking`.
- Integración con `EmailService::sendTemplate` con attach.
- Tests: envío email, generación URL wa.me, tracking funciona.

### PR 7 — Tracking dashboard + métricas en analytics (1 sem)
- Tab "Presentaciones enviadas" en `/admin/captaciones/{id}`.
- Vista detalle del `PresentationSend` con timeline.
- Widgets nuevos en `/admin/analytics` con los 4 KPIs.
- Tests: counts correctos, fechas correctas.

### PR 8 — Declinar caso (1 sem)
- Botón "Declinar amistosamente" en vista de captación.
- Modal con razón obligatoria.
- Servicio `CaptacionDeclineService` que actualiza `Captacion`, `Operation` y dispara email `captacion_declined_friendly`.
- Plantilla de email amistosa.
- Tests: declinar bloquea envío futuro, email se manda.

---

## 10. Casos de uso a probar end-to-end

Antes de cerrar el módulo, estos 8 escenarios deben pasar:

1. **Captación rápida ideal:** agente entra, captura cliente con nombre+teléfono+email, captura inmueble básico, define intent=`venta_residencial`, comisión=5%, genera presentación, envía por email + WhatsApp. Tiempo total < 5 min.

2. **Captación incompleta:** agente captura sólo nombre+teléfono. Email es null. Botón "Enviar por email" deshabilitado. WhatsApp habilitado. Agente envía sólo por WhatsApp.

3. **Captación general:** agente no decide intent. Default `general`. Plantilla genérica se aplica. Presentación se genera correctamente sin contenido específico de tipo de venta.

4. **Edición del precio sugerido:** en el editor, agente teclea precio sugerido distinto del que tenía el cliente en mente. PDF preview se regenera en < 2s. Se envía.

5. **Comisión negociada:** en el wizard paso 3, agente captura comisión 4% (negociada en llamada). PDF refleja 4%, no 5%.

6. **Foto del cliente:** cliente mandó foto del inmueble por WhatsApp. Agente la sube en paso 2 (Spatie Media Library). Aparece en portada del PDF.

7. **Tracking completo:** propietario abre email → `email_opened_at` se llena. Clickea link → `link_clicked_at` se llena. Ve el PDF en el navegador → `pdf_viewed_at` y `pdf_view_count` se llenan. Descarga → `pdf_downloaded_at` se llena.

8. **Declinar caso:** agente captura, genera presentación, **antes** de enviar decide declinar (en la vista de la captación). Click "Declinar amistosamente" → razón "inmueble fuera de zona estratégica". Operación se cancela. Propietario recibe email amistoso. La presentación queda guardada pero no se envía.

---

## 11. Checklist de QA

### Schema
- [ ] `php artisan migrate` corre limpio.
- [ ] `php artisan migrate:rollback` revierte limpio.
- [ ] Seeders crean 6 plantillas de presentación + 2 plantillas de email (`presentation_initial`, `captacion_declined_friendly`).

### Wizard
- [ ] Botón global "+ Nueva captación" visible en topbar.
- [ ] Atajo de teclado funciona.
- [ ] 3 pasos navegables, indicador de progreso.
- [ ] Validaciones mínimas (nombre+teléfono).
- [ ] Email opcional con leyenda visible.
- [ ] Upload de fotos vía Spatie Media Library funciona.
- [ ] Submit crea Client + Property (status='draft') + Operation + Captacion vinculados.
- [ ] Mobile 360px funciona.

### Editor + PDF
- [ ] Split view en desktop, modal full-screen en mobile.
- [ ] Preview se regenera en < 2s al cambiar variable.
- [ ] PDF Letter con 6 páginas según intent.
- [ ] Marca "Home del Valle" (V mayúscula) en portada.
- [ ] Slogan en footer.
- [ ] Paleta navy + neutros + verde. Cero dorado.
- [ ] Disclaimer pequeño al pie de última página.

### Envío y tracking
- [ ] Email con PDF attach llega < 60s.
- [ ] Pixel de tracking en email registra apertura.
- [ ] `wa.me` URL se construye correctamente, abre WhatsApp escritorio del agente.
- [ ] URL pública `/presentaciones/{token}` carga el PDF en iframe.
- [ ] Vista pública registra `pdf_viewed_at` y `pdf_view_count`.
- [ ] Botón descargar persiste `pdf_downloaded_at`.

### Dashboard + métricas
- [ ] Tab "Presentaciones enviadas" muestra todos los envíos.
- [ ] Timeline detalle muestra estados por envío.
- [ ] Widgets de `/admin/analytics` muestran KPIs correctos.

### Declinar
- [ ] Botón "Declinar amistosamente" visible en vista de captación.
- [ ] Modal pide razón obligatoria.
- [ ] Cambia status, cancela operación, dispara email.
- [ ] Después de declinar, botones de envío de presentación quedan deshabilitados.

### Marca y SEO
- [ ] PDF dice "Home del Valle" con V mayúscula.
- [ ] Tipografía Inter en todo el PDF.
- [ ] Cero dorado, cero cobre.

---

## 12. Cuándo preguntar antes de implementar

Detente y pregunta a Alex si:

1. **Browsershot no funciona en cPanel** (Puppeteer requiere Chrome headless). Si no se puede instalar, ¿caemos a DomPDF o buscamos VPS?
2. **El esquema actual de `captaciones`** no admite las columnas nuevas (ej. `status` es enum estricto sin `declined`). Confirmar antes de migrar.
3. **El esquema de `contract_templates`** ya tiene una columna `type` con semántica distinta. Confirmar antes de extender.
4. **`EmailService::sendTemplate`** no acepta attachments hoy. Si el método actual no los soporta, hay que extenderlo (no rehacerlo).
5. **El observatorio `/mercado`** ya tiene un endpoint reusable para precios por colonia. Aunque por ahora los datos van manuales, **prepara la integración para que el agente pueda autollenar el precio sugerido con un click "Tomar de mercado"** — esto es un nice-to-have para PR 5 o posterior.
6. **WhatsApp link con PDF público:** verifica que la URL pública del PDF se previsualice correctamente en WhatsApp Web (rich preview con thumbnail). Si no, considerar agregar Open Graph tags al endpoint público.
7. **Decisión de fonts en PDF:** Inter desde fonts.bunny.net puede no cargar en Browsershot. Si hay problema, embed la fuente localmente desde `public/fonts/`.

---

## 13. Cómo entregar

### Por cada PR

1. **Branch:** `feature/captacion-presentation-NN`.
2. **Commits:** mensaje en español, `tipo: mensaje corto`. Ej: `feat: agregar PresentationGeneratorService con Browsershot`.
3. **Descripción del PR:** qué problema resuelve, qué archivos toca, qué tests agregaste.
4. **Tests:** todos pasan.
5. **Migrations:** corren limpio. Rollback no rompe.
6. **Documentación:** si tocaste manuales de `/docs/`, actualízalos.

### Al cerrar el módulo

1. Crear `docs/07-PROCESO-DE-CAPTACION-Y-PRESENTACION.md` con la spec final (espejo de `05-PROCESO-DE-RENTA.md`).
2. Actualizar `docs/README.md` agregando el nuevo manual al índice.
3. Actualizar `docs/04-ROADMAP-Y-ARQUITECTURA.md` marcando esta feature como Fase 3.6 completada.

---

## 14. Definición de "listo" del módulo

Cuando todos los PRs están en producción:

**Para un agente:**
- Cuelga llamada con propietario, abre `Ctrl+Shift+N`, captura datos en 3 minutos, genera presentación, edita comisión a 5% (o lo que negoció), revisa preview, envía por email + WhatsApp, sale del flujo en menos de 8 minutos totales desde colgar.
- Ve métrica en su dashboard: "Tiempo promedio llamada→envío esta semana: 7 min". Antes era 2 días.

**Para Alex:**
- Ve en `/admin/analytics` la conversión real "captación creada → presentación enviada → cliente pasa a etapa 2 (valuación)".
- Ve en `/admin/captaciones` listado completo con filtros por intent, agente, estado.
- Puede declinar amistosamente con razón documentada.

**Para el propietario:**
- Recibe email + WhatsApp con presentación profesional en < 10 min después de colgar.
- Abre el PDF en su teléfono, lo entiende en 5 segundos, ve foto + propuesta + comisión clara + datos del agente.
- Si quiere avanzar, responde por WhatsApp al agente directamente.

---

**Fin del prompt.**

Si llegas hasta aquí y todo tiene sentido, empieza por PR 1 (schema + modelos). No te saltes pasos.

Si algo de este prompt es contradictorio con un documento del repo (`IMPLEMENTATION_RULES.md`, `CRITICAL_VERSIONS.md`, `docs/*`), el documento del repo gana y me avisas para corregir el prompt.

Si algo de este prompt requiere decisión que sólo Alex puede tomar, márcalo como "**Decisión requerida**" y espera respuesta antes de avanzar.
