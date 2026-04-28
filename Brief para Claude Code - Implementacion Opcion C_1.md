# Brief de implementación para Claude Code · v2
## Home del Valle — Opción C: estado actual + pendientes + sugerencias

> **Cómo usar este documento:** está diseñado para pegarse directamente como prompt a Claude Code. La estructura es nueva: cada sección dice qué ya está hecho (✅), qué quedó pendiente (❌) y qué sugerencias adicionales (✨) salieron de la auditoría del 28 abr 2026.
>
> **Última auditoría:** 2026-04-28 sobre `homedelvalle.mx` en producción.

---

## Stack confirmado (no ha cambiado)

PHP 8.3.30 · Laravel 13.6.0 · Livewire 4.2.4 · Filament 5.6.1 instalado pero **no** es admin primario · MySQL `sql_homedelvalle_mx` · Tailwind 4.2.2 (sólo sitio público) · Vite 8 · TinyMCE 8.3 · Alpine.js (sitio público) · Lucide-static · Spatie Media Library · DomPDF · Intervention/Image · PHPMailer.

CRM admin custom con sidebar `layouts/app-sidebar.blade.php`. Sitio público con Tailwind + Alpine. Jobs corren síncronos vía `schedule:run` (cPanel sin queue worker).

**Convenciones obligatorias (no cambian):**
1. Formularios públicos = Alpine + controlador, NO Livewire.
2. Uploads = Spatie Media Library.
3. Admin = Blade custom con CSS variables, NO Filament.
4. Iconos = Lucide-static SVG inline.
5. Estilos = Tailwind 4 con `@theme` en CSS.
6. Email = PHPMailer + SMTP dinámico desde `email_settings`.

---

## Paleta correcta (no ha cambiado, recordatorio)

```css
@theme {
  --color-navy-950: #0A1A2F;  /* hero deep */
  --color-navy-900: #1F3A5F;  /* institucional */
  --color-navy-700: #1E1B4B;  /* sidebar CRM */
  --color-blue-500: #3B82F6;  /* funcional */
  --color-text:     #0F172A;
  --color-muted:    #64748B;
  --color-border:   #E2E8F0;
  --color-surface:  #F1F5F9;
  --color-success:  #16A34A;  /* SOLO estados positivos */
  --color-error:    #DC2626;
  --color-warning:  #D97706;
  --font-sans: "Inter", "ui-sans-serif", system-ui, sans-serif;
}
```

**Prohibido en cualquier pieza:** dorado, cobre, mostaza, naranja, púrpura saturado decorativo. La sobriedad es la marca.

---

## Resumen ejecutivo del avance

**Lo bueno:** las dos landings nuevas (`/comprar`, `/desarrolladores-e-inversionistas`) están publicadas con el copy del brief original; `/vende-tu-propiedad` ya tiene los 12 campos segmentados; el slogan aparece en header y footer; el sitio incluye sellos de credibilidad (HTTPS, AMPI); en el CRM admin ya existe `/admin/form-submissions` y `/admin/submissions`; el blog pasó de 1 a 2 artículos; el home tiene una sección "Soluciones para cada perfil" como primer paso del selector de intención; el footer está organizado en 4 columnas.

**Lo que falta:** el selector de intención del home tiene sólo 2 perfiles (Propietarios y Compradores), falta la tarjeta de Inversionistas/Desarrolladores; el navbar y el footer no incluyen las dos landings nuevas (descubribilidad cero desde la home); el formulario de `/contacto` sigue genérico (no segmenta intención); muchas erratas y problemas de marca ("Home del valle" en `<title>`, "Juarez" sin tilde, "Direccion", "Anos", "Negociacion", "operacion", "catalogo", "Busqueda", "Analisis", "Valuacion", "Asociacion", "Politica", "Terminos") siguen sin tocar; el catálogo sigue con 1 propiedad pública.

**Nueva oportunidad detectada:** el hero del home actualmente le habla SOLO al vendedor ("¿Quieres vender tu propiedad?"). Esto es el cuello de botella número uno: cualquier visitante que no sea vendedor llega a un mensaje que no es para él. El selector de intención debe subir al hero, no quedar como tercera sección de scroll.

---

## Estado por sección

### 1. Home (`/`)

**✅ Hecho**
- Slogan en header desktop ("Pocos inmuebles. Más control.").
- Slogan completo en footer ("Pocos inmuebles. Más control. Mejores resultados.").
- Pill "Firma boutique en Benito Juárez · 30+ años" arriba del hero.
- Sección "¿Qué necesitas? — Soluciones para cada perfil" con 2 tarjetas (Propietarios, Compradores).
- Sección "¿Por qué Home del Valle?" con 2 columnas de beneficios.
- Sección "Operamos desde la demanda, no desde la oferta" con pasos 01-02-03.
- Sección "Inventario seleccionado — Propiedades seleccionadas" mostrando inventario.
- Sección "Líneas de negocio" con tarjetas Compra y Venta.
- Sección "Resultados que hablan" con stats.
- Sección "Lo que dicen nuestros clientes" con testimonios.
- Sección "Últimos artículos" con 2 posts del blog.

**❌ Pendiente — alta prioridad**
- **Hero le habla sólo al vendedor.** Cambiar a un hero neutro con selector de intención de 3 CTAs visibles arriba del fold.
- **Falta tarjeta Inversionistas/Desarrolladores** en "Soluciones para cada perfil". Tiene que ser la 3ra tarjeta junto a Propietarios y Compradores.
- **Falta tarjeta Inversión/Desarrollo** en "Líneas de negocio". Hoy sólo Compra y Venta.
- Slogan en header está abreviado ("Pocos inmuebles. Más control."). Mostrar completo en desktop ≥ 1280 px ("Pocos inmuebles. Más control. Mejores resultados.").
- "Resultados que hablan": confirmar que se vean los 4 stats completos (30+, 200+, 45 días, 98%). En la captura sólo se vieron 30+ y 200+ truncados.

**❌ Erratas a corregir en el home**
| Buscar | Reemplazar | Dónde |
|---|---|---|
| `MAS SOLICITADO` | `MÁS SOLICITADO` | Badge en tarjeta destacada |
| `Mas solicitado` | `Más solicitado` | Badge alterno |
| `Busqueda personalizada` | `Búsqueda personalizada` | Línea de negocio Compra |
| `Analisis de inversion` | `Análisis de inversión` | Línea de negocio Compra |
| `catalogo exclusivo` | `catálogo exclusivo` | Línea de negocio Compra |
| `Valuacion profesional` | `Valuación profesional` | Línea de negocio Venta |
| `Solicitar valuacion gratis` | `Solicitar valuación gratis` | CTA línea Venta |
| `Negociacion, blindaje legal y cierre eficiente` | `Negociación, blindaje legal y cierre eficiente` | Paso 03 "Operamos desde la demanda" |
| `Ejecutamos la operacion` | `Ejecutamos la operación` | Título paso 03 |
| `Anos de experiencia senior` | `Años de experiencia senior` | Bloque "Resultados que hablan" — CRÍTICO |

**❌ Title tag del home (CRÍTICO de marca)**

Actual: `Firma Inmobiliaria Boutique en Benito Juárez — Home del valle | Home del valle`
Cambiar a: `Firma inmobiliaria boutique en Benito Juárez | Home del Valle`

Notas: marca con V mayúscula, sin duplicar el nombre de marca al final. Aplicar el patrón a todas las páginas (ver sección 9 más abajo).

**✨ Sugerencias nuevas para el home**

1. **Selector de intención al hero.** Reorganizar:
   - Hero compacto: eyebrow + headline corto + subheadline + 3 tarjetas de intención visibles arriba del fold.
   - El bloque "Soluciones para cada perfil" pasa a ser un summary expandido más abajo, no el primer encuentro con la intención.

   ```
   Eyebrow: FIRMA BOUTIQUE EN BENITO JUÁREZ · 30+ AÑOS
   H1:      Pocos inmuebles. Más control. Mejores resultados.
   Sub:     Operamos desde la demanda, no desde la oferta. ¿Cómo podemos ayudarte?

   [Tarjeta 1]              [Tarjeta 2]              [Tarjeta 3]
   PROPIETARIOS             COMPRADORES              DESARROLLO E INVERSIÓN
   Quiero vender            Estoy buscando dónde     Soy desarrollador o
   mi propiedad             vivir o invertir         inversionista
   Solicitar valuación →    Iniciar búsqueda →       Solicitar brief →
   ```

2. **Eliminar el botón sólido "Vende tu propiedad" del navbar.** Hoy ocupa el espacio de CTA principal y le habla a 1 de 3 funnels. Sustituir por un botón neutro `Hablemos` que abra un menú con las 3 opciones, o por un link sutil al `/contacto`.

3. **Botón flotante (chatbot)** que vi en la esquina con badge "1": confirmar que sus preguntas estén alineadas con los 3 perfiles (Propietario / Comprador / Inversionista) y que el resultado se persista en `clients` con `client_type` correcto y `lead_source='chatbot_home'`.

---

### 2. Landing `/comprar`

**✅ Hecho**
- URL publicada y respondiendo 200.
- Hero con eyebrow "Búsqueda asistida", H1, sub, lista de beneficios.
- Formulario brief estructurado con todos los campos del brief original (tipo de inmueble multiselect, operación, zonas multiselect, recámaras, presupuesto, forma de pago, timing, must-have textarea, datos de contacto, aviso de privacidad).
- Sección "¿Por qué buscar con nosotros?" con 3 ventajas.
- Sección "Cómo funciona" con 3 pasos.
- FAQ con 4 preguntas.
- Microcopy de respuesta < 72 horas.

**❌ Pendiente**
- Title tag actual: `Búsqueda asistida de inmuebles en Benito Juárez | Home del Valle | Home del valle`. Quitar el "| Home del valle" duplicado al final.
- No aparece en el navbar principal (descubribilidad cero desde el resto del sitio).
- Verificar que el submit cree `Client` (`client_type='buyer'`, `lead_temperature='warm'`, `budget_min/max` derivado del rango, metadata JSON con zonas/timing/pago, `lead_source='/comprar'`) y dispare email transaccional + notificación interna a corretaje.
- Si el lead viene con `timing=inmediato` y `presupuesto≥6m_9m`, marcar `lead_temperature='hot'` desde el primer evento.

**✨ Sugerencias nuevas**

1. **Casos de éxito anclados a zonas.** Después de "Cómo funciona", agregar 1–2 casos: "Comprador en Narvarte cerró depto en 32 días con crédito INFONAVIT" + foto de referencia (no del cliente real, sino genérica de la zona). Eleva conversión inmediata.
2. **Banner de inventario contextual.** Antes del FAQ, mini-grid con las propiedades disponibles que matchean el brief promedio (ej. "3 inmuebles disponibles en Del Valle entre $6M y $9M"). Si el catálogo crece, esto pasa a ser dinámico.
3. **Mini-comparativo "buscar contigo vs por tu cuenta"** en formato tabla, 4 filas. Convierte indecisos.

---

### 3. Landing `/desarrolladores-e-inversionistas`

**✅ Hecho**
- URL publicada y respondiendo 200.
- Hero con eyebrow "Captación B2B", H1, sub.
- Sección "Líneas de Captación" con 4 tarjetas (Terrenos / Producto Terminado / Coinversión / Asesoría).
- Sección "¿Por qué asociarse con nosotros?" con 3 ventajas (Demanda Verificada / Expertise Integral / Red Consolidada).
- Sección "Cómo trabajamos juntos" con 3 pasos.
- FAQ con 5 preguntas detalladas.
- Formulario brief calificador con todos los campos del brief original (tipo operación, uso, m², zonas, presupuesto, horizonte, upload PDF/JPG/PNG, datos empresa/rol, NDA opcional, aviso).
- Microcopy de respuesta < 48 horas y mención de confidencialidad.

**❌ Pendiente**
- Title tag actual: `Captación de predios e inversión inmobiliaria en Benito Juárez | Home del Valle | Home del valle`. Quitar el "| Home del valle" duplicado.
- No aparece en navbar ni en footer "EXPLORAR".
- Verificar que el submit cree `Client` (`client_type='investor'`, `lead_temperature='hot'`, `assigned_user_id` = id de Ana Laura/Dirección General), guarde el upload con Spatie Media Library en collection `briefs`, y dispare:
  - Email transaccional al lead con confirmación y promesa de llamada en 48 horas.
  - Notificación interna **directa** a Dirección General (no round-robin).
  - Si NDA marcado, registrar en `legal_acceptances` con tipo `nda_b2b_initial`.

**✨ Sugerencias nuevas**

1. **Calendly o link de agendar llamada** después del submit del brief. Hoy se promete "agendamos llamada de calificación en menos de 48 horas" pero no se ofrece auto-servicio para acelerar. Mostrar opción de agendar si Dirección tiene calendario público.
2. **Caso de estudio anonimizado.** Sección con un caso real ejecutado (zona, m², ticket aproximado, plazo, lección). Sin datos del cliente. Es la barrera principal de conversión en B2B.
3. **Indicador de saturación.** Si tienen un calendario activo: "Próxima fecha disponible: jueves 30 abr · 11:00". Genera urgencia legítima sin presión falsa.

---

### 4. Landing `/vende-tu-propiedad`

**✅ Hecho** (avance grande aquí)
- Formulario actualizado con los 12 campos del brief original (nombre, email, WhatsApp, tipo, colonia, m², recámaras, precio esperado, motivo, estado documental, timing, aviso).
- Erratas previas corregidas: "Días", "rápida", "jurídica", "Análisis", "comercialización", "fotografía", "Valuación", "documentación".
- Hero con stats (30+, 200+, 45 días, 98%) y sub copy actualizado.
- Sección "¿Por qué vender con nosotros?" con 4 beneficios.
- Sección "Proceso 01-02-03" con copy correcto.
- FAQ de 6 preguntas con tildes correctas.
- Microcopy "Respuesta en menos de 24 horas hábiles · Sin compromiso · Sin spam".

**❌ Pendiente**
- Title tag actual: `Vende tu propiedad en Benito Juarez | Home del Valle | Home del valle`. Cambiar a: `Vende tu propiedad en Benito Juárez | Home del Valle` (tilde en Juárez, sin duplicar marca al final).
- Verificar que el submit cree `Client` (`client_type='owner'`, `lead_temperature='warm'`, mapeo de campos a `clients.budget_min/max` con el `precio_esperado` del rango) **y** una `Operation` (`type='captacion'`, `stage='inquiry'`, `status='active'`) vinculada. Esto es el flujo de captación nativo del CRM y debe mantenerse.

**✨ Sugerencias nuevas**

1. **Validación dinámica del precio**. Cuando el usuario seleccione colonia y m², mostrar un rango orientativo basado en `/mercado` ("Inmuebles en Del Valle entre 100–150 m² se publican entre $X M y $Y M") junto al campo "Precio que te gustaría obtener". No vinculante pero educativo. Aumenta calidad del lead.
2. **Slot de fotos opcional**. Permitir que el lead suba 3 fotos del inmueble desde el formulario (Spatie Media Library, collection `valuation_request_photos`). Con eso la valuación inicial es más precisa y el agente llega mejor preparado a la primera llamada.
3. **Link directo a WhatsApp con mensaje precargado contextual** según las opciones que ya seleccionó: "Hola, quiero vender mi [tipo] en [colonia] de aprox [m²], timing [timing]". Reduce fricción para el lead que prefiere hablar antes de llenar todo.

---

### 5. Landing `/contacto`

**✅ Hecho**
- Mantiene su estructura.
- Datos de contacto visibles (teléfono, email, dirección, WhatsApp).
- Mapa embebido.
- CTA secundario "Valúa tu propiedad" al fondo.

**❌ Pendiente — alta prioridad**
- **Formulario sigue genérico** (Nombre, Email, Teléfono, Mensaje, Aviso). El brief pidió segmentar con un primer campo "¿En qué te podemos ayudar?" para enrutar al CRM con el `lead_tag` correcto. Esto sigue sin hacerse.
- **Erratas pendientes:**
  - `Direccion` → `Dirección`
  - `Siguenos en redes` → `Síguenos en redes`
  - `Heriberto Frias` → `Heriberto Frías` (también en footer y email)
  - `Ubicacion` → `Ubicación`
  - `Visitanos` → `Visítanos`
- Title actual: `Contacto | Home del valle`. Cambiar a: `Contacto | Home del Valle`.

**Implementación del formulario segmentado (sin cambios respecto al brief original):**

Primer campo (select obligatorio): **¿En qué te podemos ayudar?**
- `Quiero vender mi propiedad` → tag `LEAD_VENDEDOR`, redirige al `clients.client_type='owner'`.
- `Estoy buscando dónde comprar o rentar` → tag `LEAD_COMPRADOR`, `client_type='buyer'`.
- `Soy desarrollador o inversionista` → tag `LEAD_B2B`, `client_type='investor'`.
- `Administración de un inmueble` → tag `LEAD_ADMIN`.
- `Asesoría legal o notarial` → tag `LEAD_LEGAL`.
- `Otro` → tag `LEAD_OTRO`.

Después: Nombre, Email, WhatsApp, Colonia de tu interés en BJ (select opcional), Mensaje (textarea opcional), Aviso de privacidad. Con campos condicionales si la primera opción es "vender" o "comprar" (mostrar tipo de propiedad y/o presupuesto).

---

### 6. Navbar y Footer

**✅ Hecho**
- Slogan visible en header desktop (versión corta).
- Slogan completo en footer.
- Footer reorganizado en 4 columnas (EXPLORAR / SERVICIOS / CONTACTO + redes).
- Sellos de credibilidad (Sitio Seguro HTTPS, Miembro AMPI) — buena adición no contemplada en brief original.
- Lista de líneas de servicio en footer SERVICIOS.

**❌ Pendiente — alta prioridad**
- **Navbar no incluye las dos landings nuevas.** Hoy: Propiedades / Precios de Mercado / Servicios / Nosotros / Testimonios / Guía Inmobiliaria / Contacto + botón sólido "Vende tu propiedad". Recomendado:

  ```
  Comprar | Vender | Inversión & Desarrollo | Mercado | Servicios | Nosotros | Blog | Contacto
  ```

  Con dropdown para "Mercado" si el espacio aprieta. El botón sólido del navbar no debe ser sólo de vendedor — sustituir por `Hablemos` neutro o por menú con las 3 intenciones.

- **Footer columna EXPLORAR no incluye:** Comprar, Inversión & Desarrollo, Precios de Mercado.
- **Footer disclaimer legal con erratas:**
  - `© 2026 Home del valle Bienes Raíces` → `© 2026 Home del Valle Bienes Raíces`
  - `Asociacion Mexicana de Profesionales Inmobiliarios` → `Asociación Mexicana de Profesionales Inmobiliarios`
  - `Politica de cookies` → `Política de cookies`
  - `Terminos y condiciones` → `Términos y condiciones`
- **Footer datos de contacto:**
  - `Heriberto Frias 903-A` → `Heriberto Frías 903-A`

**✨ Sugerencias nuevas**

1. **Slogan completo en header desktop ≥ 1280px.** Hoy se muestra "Pocos inmuebles. Más control." truncado. En anchos cómodos debería verse el slogan completo. En anchos medianos, abreviado. En mobile, oculto.
2. **Indicador de "Estamos abiertos · L-V 9:00–19:00"** en el header con punto verde si dentro de horario, gris si fuera. Marca seriedad y reduce expectativas en horarios off.
3. **WhatsApp flotante con mensaje precargado por página.** Hoy el `?text=` del WhatsApp es genérico. Modificar `whatsapp-button.blade.php` para leer `request()->path()` y precargar:
   - `/comprar` → "Hola, vi su sitio. Estoy buscando inmueble en Benito Juárez."
   - `/desarrolladores-e-inversionistas` → "Hola, soy [rol]. Quiero conocer su proceso de captación."
   - `/vende-tu-propiedad` → "Hola, quiero vender mi inmueble. ¿Pueden orientarme con la valuación?"
   - default → "Hola, vi su sitio. Quiero saber más sobre Home del Valle."

---

### 7. /testimonios

**❌ Pendiente — erratas (no se ha tocado desde el brief original)**

| Buscar | Reemplazar |
|---|---|
| `Cada operacion es unica` | `Cada operación es única` |
| `Aqui comparten su experiencia` | `Aquí comparten su experiencia` |
| `Asesoria inmobiliaria` | `Asesoría inmobiliaria` |
| `Cerramos una buena operacion` | `Cerramos una buena operación` |
| `Contactanos y descubre` | `Contáctanos y descubre` |
| `por que nuestros clientes` | `por qué nuestros clientes` |

**❌ Pendiente — estructura**
- Sólo 3 testimonios. Sin foto, sin metadata de operación, sin video.
- Title actual: `Testimonios — Home del valle | Home del valle`.

**✨ Sugerencias**
1. **Captura sistemática de testimonio post-cierre.** Implementar en el CRM (Manual de Operaciones, sección 9): cuando una `operation` pasa a `status='completed'`, se dispara un workflow que pide testimonio al cliente vía WhatsApp con un link a una mini-form (`/testimonio-cliente/{token}`) donde el cliente sube quote, foto opcional, autoriza. Persistir en una tabla `testimonials` con `client_id`, `operation_id`, `quote`, `media_id`, `authorized_at`. Esto resuelve el problema estructural a 6 meses.
2. **Mientras tanto, reescribir los 3 actuales** con estructura mínima: quote + zona + tipo operación + ticket aproximado + tiempo en mercado.

---

### 8. /nosotros

**❌ Pendiente — erratas**
- `Quienes somos` → `Quiénes somos` (interrogativo indirecto, lleva tilde)

**❌ Pendiente — title**
- Actual: `Nosotros — Home del valle | Home del valle` → `Nosotros | Home del Valle`

**✨ Sugerencias**
- Agregar foto del equipo (Alex y Ana Laura) — hoy sólo aparecen los nombres y descripciones.
- Agregar carrusel "Línea del tiempo" con 3-5 hitos de la firma (fundación, primera operación grande, hitos legales, etc.).

---

### 9. Title tags y meta SEO (CRÍTICO de marca)

**Patrón estándar a aplicar** (cambio global en el layout o en `site_settings.brand_suffix`):

`{Título de la página} | Home del Valle`

NO incluir "Home del valle" minúscula. NO duplicar el sufijo de marca al final. Mantener tildes.

**Cambios concretos detectados:**

| Página | Title actual | Title correcto |
|---|---|---|
| `/` (home) | `Firma Inmobiliaria Boutique en Benito Juárez — Home del valle \| Home del valle` | `Firma inmobiliaria boutique en Benito Juárez \| Home del Valle` |
| `/vende-tu-propiedad` | `Vende tu propiedad en Benito Juarez \| Home del Valle \| Home del valle` | `Vende tu propiedad en Benito Juárez \| Home del Valle` |
| `/comprar` | `Búsqueda asistida de inmuebles en Benito Juárez \| Home del Valle \| Home del valle` | `Búsqueda asistida de inmuebles en Benito Juárez \| Home del Valle` |
| `/desarrolladores-e-inversionistas` | `Captación de predios e inversión inmobiliaria en Benito Juárez \| Home del Valle \| Home del valle` | `Captación de predios e inversión inmobiliaria en Benito Juárez \| Home del Valle` |
| `/contacto` | `Contacto \| Home del valle` | `Contacto \| Home del Valle` |
| `/propiedades` | `Propiedades en Ciudad de México \| Home del valle` | `Propiedades en Ciudad de México \| Home del Valle` |
| `/nosotros` | `Nosotros — Home del valle \| Home del valle` | `Nosotros \| Home del Valle` |
| `/testimonios` | `Testimonios — Home del valle \| Home del valle` | `Testimonios \| Home del Valle` |
| `/blog` | `Blog \| Home del valle` | `Blog \| Home del Valle` |
| `/mercado` | `Observatorio de precios inmobiliarios en Benito Juárez, CDMX \| Home del Valle` | (ya está bien) |
| `/servicios` | (verificar) | `Servicios \| Home del Valle` |

**Acciones recomendadas:**

1. Buscar dónde se concatena el sufijo de marca en el layout maestro o en el helper de SEO. Probable: `layouts/public.blade.php`, componente `seo-meta.blade.php`, o columna `site_settings.site_name`/`brand_suffix`.
2. Reemplazar la cadena `'Home del valle'` por `'Home del Valle'` en la fuente.
3. Eliminar la duplicación al final (probablemente hay dos `<title>` o un `<title>` y un `<meta property="og:title">` que se concatenan al template name).
4. Limpiar caché de vistas: `php artisan view:clear`.
5. Validar con `curl -s https://homedelvalle.mx/comprar | grep '<title>'`.

---

### 10. CRM admin — flujo de leads

**✅ Detectado en el sidebar admin**
- `/admin/form-submissions` (sección "Principal" → "Leads")
- `/admin/submissions` (sección "Marketing" → "Leads")
- `/admin/captaciones` (sección "Procesos" → "Evaluación de Propiedad")
- `/admin/valuations` (sección "Procesos" → "Opinión de Valor")

Esto sugiere que ya existe la infraestructura para recibir los leads de las landings nuevas.

**❌ Verificación pendiente**
1. Que los formularios de `/comprar` y `/desarrolladores-e-inversionistas` lleguen a `/admin/form-submissions` con el `form_type` correcto.
2. Que cada lead se cree también como `Client` en la tabla principal (no sólo como `form_submission`) para entrar al pipeline operacional.
3. Que la asignación automática esté configurada:
   - Vendedor → round-robin entre captadores.
   - Comprador → round-robin entre corredores.
   - Inversionista → directo a Dirección General.
4. Que el SLA de "leads sin contactar > 24h" del dashboard esté contando bien (la captura del dashboard mostraba 3 leads sin ciudad — es probable que no estén llegando con la zona del formulario nuevo).

**✨ Sugerencias**
1. **Vista de "Funnel por origen"** en el admin: gráfico simple que muestre, para cada `lead_source` (`/comprar`, `/desarrolladores`, `/vende-tu-propiedad`, `/contacto`, `chatbot`), cuántos leads llegaron, cuántos se calificaron, cuántos cerraron. Aprovecha que ya tienen `marketing_campaigns` y `lead_events`.
2. **Notificación interna por canal**: cuando llega un B2B, además de la notificación in-app, mandar email a Dirección General (Ana Laura) con asunto `[B2B PRIORITARIO] Nuevo brief calificador · {empresa}`.

---

### 11. Catálogo de propiedades

**❌ Pendiente — decisión estratégica**
- Sigue con 1 sola propiedad pública (la del Parque Hundido).

Tres alternativas (recordatorio del Roadmap, fase 1):
- **A.** Curar 5–10 propiedades con narrativa editorial fuerte, manteniendo el discurso "pocos inmuebles".
- **B.** Eliminar el listado público y sustituirlo por "Búsqueda asistida" (todo el flujo de comprador pasa por `/comprar`).
- **C.** Mantener listado pero hacerlo evidente de que es "selección curada del mes" con un contador transparente ("3 propiedades activas · próxima incorporación 5 mayo").

**Recomendación:** A o C, para evitar el problema de "el catálogo se ve abandonado". Es la conversación que tienes pendiente con Ana Laura.

---

### 12. Páginas no auditadas en esta pasada

Para próxima revisión:
- `/servicios` — confirmar que sus erratas (si las tiene) estén corregidas.
- `/blog` (detalle de cada post) — revisar tildes y SEO de cada artículo.
- Páginas legales (`/legal/aviso-de-privacidad`, `/legal/terminos`, `/legal/cookies`) — confirmar que existen y están redactadas.
- Detalle de propiedad — confirmar que el formulario lateral mapea al `client_type='buyer'` con `lead_source='property_inquiry'` y `property_id` capturado.
- Mobile en cualquier pantalla — la auditoría fue desktop.

---

## Plan de acción priorizado (orden recomendado)

### Bloque 1 — Hygiene y marca (1 semana)

1. **Title tags + brand consistency.** Cambio masivo `Home del valle` → `Home del Valle`. Eliminar duplicación al final del `<title>`. Esto es lo de mayor impacto SEO/marca y de menor esfuerzo técnico.
2. **Erratas globales.** Aplicar la lista busca/reemplaza de las secciones 1, 5, 6, 7, 8 sobre las plantillas Blade y sobre la base de datos (`pages.body`, `posts.title`, `site_settings.*`, `menu_items.label`).
3. **Footer disclaimer legal.** Corregir las 4 erratas (Asociacion, Politica, Terminos, Home del valle).

### Bloque 2 — Descubribilidad (1–2 semanas)

4. **Navbar con 3 funnels.** Reorganizar a: Comprar / Vender / Inversión & Desarrollo / Mercado / Servicios / Nosotros / Blog / Contacto. Sustituir el botón sólido "Vende tu propiedad" por menú o CTA neutro.
5. **Footer EXPLORAR completo.** Agregar Comprar, Inversión & Desarrollo, Precios de Mercado.
6. **Selector de intención al hero del home.** Mover las 3 tarjetas (Propietarios / Compradores / Inversionistas) arriba del fold; ajustar el copy del hero a algo neutro.
7. **Tarjeta Inversionistas/Desarrolladores en "Soluciones para cada perfil"** (3ra tarjeta) y en "Líneas de negocio" (3ra tarjeta).

### Bloque 3 — Captura segmentada (1 semana)

8. **Formulario `/contacto` segmentado** con campo "¿En qué te podemos ayudar?" como primer filtro y routing a `lead_tag` correcto.
9. **WhatsApp flotante con mensaje precargado por página.**
10. **Verificar mapeo de los 3 formularios** (`/comprar`, `/desarrolladores`, `/vende-tu-propiedad`) al CRM con `Client` + `Operation` correctos.

### Bloque 4 — Confianza y conversión (2–4 semanas)

11. **Caso de estudio en `/desarrolladores-e-inversionistas`** (1 caso anonimizado).
12. **Casos en `/comprar`** (1–2 anclados a zona).
13. **Re-escritura de testimonios actuales** con quote + zona + tipo + ticket + tiempo.
14. **Workflow de captura post-cierre** de testimonios.
15. **Decidir A/B/C en catálogo** y publicar 5–10 propiedades curadas si decidimos A o C.

### Bloque 5 — UX y refinamiento (continuo)

16. **Slot de fotos opcional en `/vende-tu-propiedad`.**
17. **Validación dinámica del precio** en el formulario del vendedor con datos de `/mercado`.
18. **Calendly/agenda B2B** en `/desarrolladores`.
19. **Indicador de "Estamos abiertos"** en el header.
20. **Vista "Funnel por origen"** en `/admin/analytics`.

---

## Checklist de QA actualizado (corre después de cada bloque)

### Marca y SEO
- [ ] `grep -r "Home del valle"` (con v minúscula) sobre `resources/views/` devuelve 0.
- [ ] `SELECT * FROM pages WHERE LOWER(title) LIKE '%del valle%' AND title NOT LIKE '%del Valle%'` devuelve 0.
- [ ] `curl -s https://homedelvalle.mx/comprar | grep '<title>'` muestra `Búsqueda asistida... | Home del Valle` (un solo sufijo, V mayúscula).
- [ ] OG image al compartir cualquier URL en WhatsApp se ve correctamente.
- [ ] sitemap.xml incluye `/comprar`, `/desarrolladores-e-inversionistas`.

### Funnels
- [ ] Selector de intención del home tiene 3 tarjetas visibles arriba del fold.
- [ ] Navbar incluye los 3 funnels.
- [ ] Footer EXPLORAR incluye los 3 funnels.
- [ ] WhatsApp precarga mensaje contextual por página.

### Formularios y CRM
- [ ] Submit de `/comprar` crea `Client` con `client_type=buyer` y campos correctos.
- [ ] Submit de `/desarrolladores-e-inversionistas` crea `Client` con `client_type=investor`, asigna a Dirección, registra NDA si aplica.
- [ ] Submit de `/vende-tu-propiedad` crea `Client` (`client_type=owner`) **y** `Operation` (`type=captacion, stage=inquiry`).
- [ ] Submit de `/contacto` con campo segmentado aplica `lead_tag` correcto.
- [ ] Email transaccional al lead llega < 60 s.
- [ ] Dashboard de `/admin` muestra los nuevos leads con ciudad/zona poblada.

### Erratas
- [ ] `grep -ir "operacion\|valuacion\|busqueda\|analisis\|negociacion\|catalogo\|asociacion\|politica\|terminos\|direccion\|ubicacion\|visitanos\|siguenos\|anos de\|frias 903\|juarez"` sobre `resources/views/` y tablas de DB (after intentional matches whitelist) devuelve 0.

---

**Fin del brief v2.**

Si Claude Code va a implementar varios bloques, recomendado partirlo en 5 PRs separadas (uno por bloque) para que cada cambio sea revisable. El orden es secuencial: bloque 1 antes que el 2, y así.

Si hay duda sobre dónde vive el sufijo de marca o cómo está armado el patrón actual del `<title>`, preguntar **antes** de hacer reemplazo masivo (los `<title>` con duplicación sugieren un layout que está concatenando dos veces; cambiarlo en un solo lugar puede arreglar todo).
