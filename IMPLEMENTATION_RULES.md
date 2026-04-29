# IMPLEMENTATION RULES - homedelvalle.mx

Reglas obligatorias para implementar cualquier feature o cambio en el proyecto.

> **Última actualización:** 2026-04-29
> **Documentos hermanos:** `CRITICAL_VERSIONS.md`, `CONTEXTO_PROYECTO.md`, `DEPLOYMENT_GUIDE.md`, `docs/02-MANUAL-IMPLEMENTACION-SITIO.md`, `docs/04-ROADMAP-Y-ARQUITECTURA.md`, `docs/05-PROCESO-DE-RENTA.md`, `docs/06-PORTAL-DEL-CLIENTE.md`.

## 1️⃣ SIEMPRE Verificar Compatibilidad de Versiones

**Antes de escribir UNA SOLA LÍNEA de código:**

```
1. Abrir CRITICAL_VERSIONS.md
2. Buscar la librería que vas a usar
3. Ver versión exacta instalada
4. Revisar si hay cambios de API en esa versión
5. Revisar documentación oficial de esa versión
6. ENTONCES escribir el código
```

**Ejemplo:**
```
❌ INCORRECTO:
- Usuario: "Agrega soporte para generar imágenes dinamicamente"
- Yo: "Ok, voy a usar intervention/image"
- (escribo código sin verificar)

✅ CORRECTO:
- Usuario: "Agrega soporte para generar imágenes dinamicamente"
- Yo: "Verificando intervention/image v3.0... [reviso docs] ... Listo, estos son los métodos disponibles..."
- (escribo código confiable)
```

### Librerías Críticas que REQUIEREN Verificación

Estas librerías tienen historia de cambios de API importantes:

- ✅ **endroid/qr-code** (v4 vs v5 vs v6 completamente diferentes)
- ✅ **intervention/image** (v2 vs v3 breaking changes)
- ✅ **dompdf** (v2 vs v3 cambios)
- ✅ **laravel/framework** (cambios entre versiones LTS — actualmente 13.6, breaking changes esperables en 14+)
- ✅ **livewire/livewire** (v3 a v4 cambia sintaxis de propiedades, WithFileUploads, validación reactiva)
- ✅ **filament/filament** (v4 a v5 cambia Resources, Forms, Actions)
- ✅ **tailwindcss** (v3 a v4 cambia el config: `@theme` en CSS reemplaza `tailwind.config.js`)
- ✅ **spatie/laravel-medialibrary** (v10 a v11 cambia algunas APIs de Media)
- ✅ **phpmailer** (si se usa directamente; aquí lo usamos en EmailService)

### Librerías de Menor Riesgo (pero igual revisar)

- laravel/tinker (complementaria)
- fakerphp/faker (solo dev)
- phpunit/phpunit (solo testing)
- mockery/mockery (solo testing)

## 2️⃣ Tres ambientes UI con stacks distintos

Antes de implementar, identifica a cuál de los 3 ambientes pertenece la feature:

| Ambiente | URL | Stack | Notas |
|---|---|---|---|
| **Sitio público** | `homedelvalle.mx` | Blade + Tailwind 4 + Alpine.js | Formularios públicos = Alpine + controlador. NO Livewire en sitio público. |
| **CRM admin** | `homedelvalle.mx/admin` | Blade + CSS puro con variables CSS + **Livewire 4 donde aporte UX** | Livewire aprobado globalmente para componentes reactivos (kanban, búsqueda, filtros). |
| **Portal del Cliente** | `miportal.homedelvalle.mx` | Blade + Tailwind 4 + **Livewire 4** | App autenticada, reactiva. SÍ Livewire en todo. Subdominio activo. |

> **Decisión aprobada por Alex 2026-04-29:** Livewire 4 está aprobado en el CRM admin y en el Portal del Cliente para maximizar la experiencia de usuario. El objetivo es usar Livewire en cualquier lugar donde aporte reactividad útil (kanban, búsqueda en tiempo real, formularios multi-paso, notificaciones, etc.). No es una excepción — es la regla para componentes interactivos.

Filament 5.6.1 está instalado pero NO es el admin primario. Cualquier nuevo Resource Filament requiere aprobación explícita de Alex.

## 3️⃣ Convenciones que NO se rompen

1. **Jobs corren síncronos** vía `php artisan schedule:run` (cron cPanel cada minuto). NO uses `ShouldQueue`.
2. **Cache** sólo almacena arrays/IDs, NUNCA objetos Eloquent (evita `__PHP_Incomplete_Class`).
3. **Email** vía PHPMailer + SMTP dinámico desde tabla `email_settings`. NO Laravel Mail.
4. **Uploads** vía Spatie Media Library. NUNCA `Storage::put` a mano.
5. **Iconos** = Lucide-static SVG inline. NO CDN, NO emojis.
6. **Marca** = "Home del Valle" (V mayúscula) en todo. Cero `Home del valle` minúscula.
7. **Paleta** = navy + neutros + verde sistema. Cero dorado, cero cobre.
8. **Tipografía** = Inter en todos los ambientes.
9. **Migraciones** = nunca modificar las existentes; crear nuevas para `ALTER`.
10. **Borrar usuarios** = NO. Sólo `is_active=false`.
11. **Honeypot** obligatorio en todo formulario público.
12. **Cliente que firma con HDV** = recibe cuenta de portal automáticamente. Sin opt-in manual.
13. **Si el cliente debería verlo** = el portal lo muestra. Los documentos, pagos, mensajes y cambios de stage tienen contraparte visible en `miportal.homedelvalle.mx`.

## 4️⃣ Secuencia Obligatoria para Cualquier Feature

```
PASO 1: VERIFICAR VERSIONES Y CONVENCIONES
├─ Abrir CRITICAL_VERSIONS.md
├─ Buscar cada librería que usarás
├─ Identificar el ambiente UI (público / CRM admin / portal)
└─ Documentar qué métodos existen en esa versión

PASO 2: ENTENDER ARQUITECTURA
├─ Revisar documentos relevantes:
│  ├─ docs/02-MANUAL-IMPLEMENTACION-SITIO.md (siempre)
│  ├─ docs/05-PROCESO-DE-RENTA.md (si toca rentas)
│  ├─ docs/06-PORTAL-DEL-CLIENTE.md (si toca portal)
│  ├─ .claude/SCHEMA_QUICK_REFERENCE.md (si toca DB)
│  ├─ QR_IMPLEMENTATION.md, GALLERY_PREMIUM_DOCS.md (módulos específicos)
├─ Ver ejemplos de código similar
└─ Entender patrón del proyecto

PASO 3: PENSAR EN EL CLIENTE
├─ ¿Esta feature genera algo que el cliente debería ver en el portal?
├─ ¿Qué notificación dispara y por qué canal?
├─ ¿Hay datos sensibles a filtrar entre las partes?
└─ Si la respuesta es ambigua, preguntar antes de implementar.

PASO 4: IMPLEMENTAR LOCALMENTE
├─ Escribir código respetando las convenciones del ambiente UI correcto
├─ Usar métodos correctos para la versión
└─ Testear (incl. mobile si aplica)

PASO 5: DESPLEGAR
├─ Seguir DEPLOYMENT_GUIDE.md
├─ Hacer post-deployment testing
└─ Monitorear 24h

PASO 6: DOCUMENTAR
└─ Actualizar CRITICAL_VERSIONS.md, docs/* si hay cambios sustantivos
```

## 5️⃣ Checklist Antes de Ejecutar Cualquier Código

```
[ ] ¿Verifiqué versiones en CRITICAL_VERSIONS.md?
[ ] ¿Identifiqué el ambiente UI correcto (público / CRM admin / portal)?
[ ] ¿Verifiqué que los métodos existan en esa versión?
[ ] ¿Revisé documentación oficial de esa versión?
[ ] ¿Esta feature genera algo que el cliente debería ver en el portal?
[ ] ¿Estoy respetando las 13 convenciones de la sección 3?
[ ] ¿Testée localmente (incl. mobile si aplica)?
[ ] ¿Estoy 100% seguro de que funcionará en producción?
```

Si alguno es NO → DETENERME Y REVISAR PRIMERO.

## 6️⃣ Cómo Reportar si Hay Duda

**Cuando haya incertidumbre sobre una librería o ambiente:**

Yo digo:
```
"Necesito verificar compatibilidad de [librería] v[versión] / convención de [ambiente].
Consultando CRITICAL_VERSIONS.md / docs/02-MANUAL-IMPLEMENTACION-SITIO.md / documentación oficial...
[pausa]
Encontré: [métodos disponibles] / [convención aplicable]
Puedo proceder seguramente implementando: [solución]"
```

**O si hay problema:**
```
"Esta librería tiene breaking changes en v[X].
El código que funciona en v[X-1] no funcionará aquí.
Necesito reescribir [componente] para v[X].
Procediendo...

Cambios específicos:
- Método A → Método B
- Remover método C (no existe en v[X])
- Usar método D en su lugar
```

**O si hay decisión que requiere aprobación:**
```
"Esta feature toca [ambiente]. Las convenciones dicen [X], pero el caso requiere [Y].
Necesito tu aprobación antes de seguir.
Opciones:
A. [opción] — implicaciones [...]
B. [opción] — implicaciones [...]
Recomiendo [A o B] porque [...]."
```

## 7️⃣ Consecuencias de No Seguir Estas Reglas

### Pasado (QR Code incident, abril 2026)
```
❌ No verificar → Código para v4/v5
❌ No testear métodos → `create()` no existe en v6
❌ No revisar docs → `getStream()` no existe
❌ No desplegar correctamente → 4 intentos fallidos

COSTO: ~45 minutos de debugging
LECCIONES: Ahora hay CRITICAL_VERSIONS.md
```

### Futuro (Si no se sigue la regla)
```
❌ Alguien implementa feature X sin verificar versiones
❌ Código funciona en desarrollo (versión diferente)
❌ Falla en producción (versión exacta diferente)
❌ Requiere rollback de emergencia
❌ Afecta usuarios reales

COSTO: Incidente en producción, confianza, tiempo
SOLUCIÓN: SEGUIR ESTAS REGLAS
```

## 8️⃣ Interfaz de Usuario para Estas Reglas

Cuando me pidas algo:

**Yo puedo responder:**

1. "Verificando compatibilidad y convenciones... ✅ Puedo hacerlo" (procedo)
2. "Verificando... ❌ Hay breaking change en v[X]" (explico problema)
3. "Verificando... ⚠️ Puedo hacerlo pero requiere cambios en [archivo]" (explico)
4. "Esta feature requiere decisión que sólo Alex puede tomar — espero respuesta antes de avanzar" (pauso)

**Tú puedes confiar en que:**
- Antes de escribir código, verifiqué versiones y convenciones.
- Antes de sugerir implementación, revisé compatibilidad y ambiente UI.
- Antes de desplegar, testée la solución.
- Los cambios FUNCIONARÁN en producción con v[X] y respetarán las convenciones del ambiente.

## 9️⃣ Ejemplos de Aplicación

### Ejemplo 1: Agregar "Generar PDF dinámicamente"

```
USUARIO: "Agrega generación de PDF con datos dinámicos"

YO: "Verificando compatibilidad...
- dompdf: ^3.0 (instalado 3.1.5) ✓
- laravel/framework: ^13.0 (instalado 13.6.0) ✓
- Ambiente: depende del caso. Si es ficha pública → controlador del sitio.
  Si es recibo del portal → controlador del portal con verificación de policy.
  Si es para un Resource Filament → Action de Filament.
- No hay breaking changes relevantes en dompdf 3 vs 2.
- Métodos disponibles:
  * Dompdf\Dompdf
  * loadHtml()
  * render()
  * output()

¿Para cuál ambiente lo necesitas? (público / portal / admin / Filament Resource)"

[Espero respuesta y entonces escribo código respetando convenciones]
```

### Ejemplo 2: Componente reactivo en el sitio público

```
USUARIO: "Quiero que el formulario de /comprar tenga validación en tiempo real"

YO: "Las convenciones del sitio público dicen Alpine.js + controlador, NO Livewire.
Livewire está reservado para el Portal del Cliente.

Opciones:
A. Implementar con Alpine.js (recomendado, respeta convención)
B. Si requiere reactividad muy compleja, sacar excepción documentada y usar Livewire
   sólo en ese formulario (requiere aprobación explícita).

Recomiendo A. Voy a implementar con Alpine. ¿Confirmas?"

[Espero confirmación]
```

### Ejemplo 3: Actualizar a Laravel 14 (Hipotético)

```
USUARIO: "Actualiza a Laravel 14 cuando salga"

YO: "No recomendado sin verificación exhaustiva:
- Laravel 13.6 es la versión actual estable.
- Laravel 14 puede tener breaking changes en:
  * Storage API
  * Blade
  * Routing
  * Middleware (ya cambió de 11 a 13, puede cambiar otra vez)
  * Compatibilidad con Livewire 4 y Filament 5

Antes de tocar nada, necesito:
1. Revisar CHANGELOG oficial de Laravel 14
2. Verificar que Livewire 4 y Filament 5 soporten Laravel 14
3. Actualizar composer.json a ^14.0 en branch separado
4. Revisar cada break change
5. Adaptar código (probablemente 10+ cambios)
6. Testear completamente (incluyendo el Portal del Cliente)
7. Deploy cuidadoso con plan de rollback

¿Procedemos por etapas?"
```

### Ejemplo 4: Crear nueva landing pública

```
USUARIO: "Crea una landing /comprar"

YO: "Verificando convenciones...
- Sitio público → Blade + Tailwind 4 + Alpine.js.
- Layout: layouts/landing.blade.php existente.
- Formulario: Alpine.js + controlador, persistir en `clients`
  con client_type='buyer'.
- Honeypot obligatorio.
- Title: 'Búsqueda asistida... | Home del Valle' (V mayúscula).
- Paleta: navy + neutros + verde sistema. Cero dorado.
- ¿El cliente debería tener vista en el portal?
  Sí, cuando se convierta de lead a cliente firmado.

Procedo siguiendo docs/02-MANUAL-IMPLEMENTACION-SITIO.md sección 12 y
docs/06-PORTAL-DEL-CLIENTE.md para el handoff."
```

## 📋 Checklist Rápido

Antes de **CUALQUIER cosa**, responderé:

- ¿Qué versión de [librería] usas? → CRITICAL_VERSIONS.md
- ¿En qué ambiente UI vive esta feature? → docs/02-MANUAL-IMPLEMENTACION-SITIO.md sección 1
- ¿Existe el método [X]? → Documentación oficial v[Y]
- ¿Esto se refleja en el portal del cliente? → docs/06-PORTAL-DEL-CLIENTE.md
- ¿Esto funcionará en producción? → Testeo local + DEPLOYMENT_GUIDE.md

Si no puedo responder alguno con 100% certeza → ME DETENGO Y VERIFICO.

---

**Última actualización:** 2026-04-29
**Razón:** Alineación con stack real (Laravel 13.6 + Livewire 4 + Filament 5 instalado pero no admin primario), tres ambientes UI con stacks distintos, integración del Portal del Cliente como pieza fundacional.
**Responsable:** Alex (Director de Estrategia y Crecimiento).
