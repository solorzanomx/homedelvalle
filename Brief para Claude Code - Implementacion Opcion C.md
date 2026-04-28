# Brief de implementación para Claude Code
## Home del Valle — Opción C: tres funnels paralelos + corrección ortográfica

> **Cómo usar este documento:** está diseñado para pegarse directamente como prompt a Claude Code. Cada sección es autocontenida y puede implementarse de forma independiente.

### Stack confirmado (abril 2026)

**Backend**
- PHP 8.3.30 (require ^8.2)
- Laravel 13.6.0
- Livewire 4.2.4
- Filament 5.6.1 (panel `/admin`, ya operativo como CRM)
- MySQL — base `sql_homedelvalle_mx`

**Paquetes clave en composer**
- `spatie/laravel-medialibrary` 11.21.2 + `filament/spatie-laravel-media-library-plugin` 5.6.1 (uploads)
- `spatie/browsershot` ^5.2 + `puppeteer` ^24.42 (PDFs/screenshots)
- `dompdf/dompdf` ^3.0 (PDFs alternos)
- `intervention/image` ^3.0 (procesado de imágenes)
- `endroid/qr-code` ^6.0
- `phpmailer/phpmailer` ^6.9
- `google/apiclient` ^2.15

**Frontend**
- Tailwind CSS 4.2.2 (con `@tailwindcss/vite` y `@tailwindcss/typography`) — sintaxis Tailwind 4 (`@theme` en CSS, sin `tailwind.config.js` clásico)
- Vite 8.0.3 + `laravel-vite-plugin` ^3.0
- TinyMCE 8.3.2 (rich text del blog/CMS)
- Lucide-static ^1.7 (iconos SVG)
- Axios ^1.11

**Tablas de base de datos relevantes (ya existentes)**
- `pages` — páginas con `title`, `slug`, `nav_label`, `nav_url`, `nav_route`, `nav_style`, `show_in_nav`, `is_published`, `nav_order`
- `menus` (location: `header`, `footer`) y `menu_items` (`menu_id`, `parent_id`, `sort_order`, `is_active`)
- `site_settings`
- `permissions` (probable Spatie Permission o Filament Shield)
- `properties`, `leads` (gestionados desde Filament)

### Convenciones obligatorias para esta implementación

1. **Formularios = Livewire 4 components** con validación reactiva, campos condicionales y loading state. Usar `wire:model.live` para selects que disparan condicionales y `wire:model.blur` para inputs de texto.
2. **Uploads = Spatie Media Library** + el trait `WithFileUploads` de Livewire. Nunca `Storage::put` a mano.
3. **Admin del lead pipeline = Filament Resource**. Crear un `FormSubmissionResource` con badges por `form_type`, filtros por `lead_tag` y bulk actions de "marcar contactado".
4. **Iconos = Lucide-static**. Cargar el SVG inline desde el helper que ya use el sitio (revisar `vendor/blade-ui-kit` o el wrapper local) en lugar de inyectar `<i class="lucide…">` desde CDN.
5. **Estilos = Tailwind 4**. Cualquier color de marca o token nuevo va en el bloque `@theme` del CSS principal, no en un `tailwind.config.js`.
6. **Rich text del blog** sigue usando TinyMCE 8 — no introducir editores nuevos.

---

## Índice

1. Contexto y objetivos
2. Cambios al Home (selector de intención)
3. Nueva landing `/comprar` — copy completo + estructura del formulario
4. Nueva landing `/desarrolladores-e-inversionistas` — copy completo + brief calificador
5. Modificaciones a `/vende-tu-propiedad` (segmentación del formulario)
6. Modificaciones a `/contacto` (segmentación del formulario)
7. Header y footer (slogan visible + selector de intención)
8. Esquema de datos para los nuevos formularios (campos y validaciones)
9. Arquitectura Livewire + Filament para los nuevos formularios
10. Auditoría ortográfica — lista exhaustiva de busca/reemplaza
11. Brand consistency — corrección de nombre de marca
12. Checklist de QA antes de hacer push

---

## 1. Contexto y objetivos

Home del Valle es una firma boutique de bienes raíces en la Alcaldía Benito Juárez. El sitio actual tiene un funnel sólido para propietarios‑vendedores pero no tiene funnel para compradores ni para desarrolladores/inversionistas. Implementaremos tres carriles de captación en paralelo:

- **Vendedor residencial** — ya existe en `/vende-tu-propiedad`. Sólo agregamos campos al formulario.
- **Comprador residencial** — nueva ruta `/comprar`. Brief estructurado de búsqueda asistida.
- **Desarrollador/Inversionista** — nueva ruta `/desarrolladores-e-inversionistas`. Brief calificador B2B.

El home pasará a ser un **selector de intención** que dirige a cada visitante al funnel correcto sin perder el discurso boutique.

**Slogan oficial (debe aparecer en header, footer y OG image):**

> Pocos inmuebles. Más control. Mejores resultados.

**Marca correcta (uso obligatorio en titles, og:title, footer y copy):**

> Home del Valle Bienes Raíces *(con la "V" de "Valle" en mayúscula)*.

---

## 2. Cambios al Home

### 2.1 Hero del home (selector de intención)

**Eyebrow** *(texto pequeño arriba del título, color acento)*

```
Firma boutique en Benito Juárez · 30+ años
```

**Headline (H1)**

```
Pocos inmuebles. Más control. Mejores resultados.
```

**Sub‑headline**

```
Comercializamos propiedades de alto valor en la Alcaldía Benito Juárez con un esquema de control total: pocos inmuebles, calidad de inventario y seguridad jurídica en cada operación.
```

**Selector de intención (3 tarjetas con CTA, equiespaciadas)**

| Tarjeta | Eyebrow | Título | Descripción | CTA | Destino |
|---|---|---|---|---|---|
| 1 | PROPIETARIOS | Quiero vender mi propiedad | Valuación profesional gratuita y plan de comercialización para vender en 45 días promedio. | Solicitar valuación → | `/vende-tu-propiedad` |
| 2 | COMPRADORES | Estoy buscando dónde vivir | Te ayudamos a encontrar el inmueble correcto en Benito Juárez con búsqueda asistida y propiedades curadas. | Iniciar búsqueda → | `/comprar` |
| 3 | DESARROLLO E INVERSIÓN | Soy desarrollador o inversionista | Captación de predios y producto terminado bajo demanda activa para desarrolladores y portafolios. | Solicitar brief → | `/desarrolladores-e-inversionistas` |

### 2.2 Bloque de stats (debajo del selector)

Mantener los 4 indicadores ya existentes: **30+ años de experiencia · 200+ propiedades gestionadas · 45 días promedio de venta · 98% clientes satisfechos**.

### 2.3 Bloque "Por qué Home del Valle" (versión home, 4 columnas)

```
Control · Gestión precisa de cada operación con seguimiento detallado en cada etapa.
Transparencia · Información clara y oportuna. Sin comisiones ocultas, sin sorpresas.
Seguridad Jurídica · Blindaje legal completo en cada transacción para proteger tu patrimonio.
Ejecución · Resultados consistentes y eficientes. Cerramos operaciones complejas.
```

### 2.4 Banda final del home

```
Headline: ¿Tienes una propiedad en la Benito Juárez o buscas comprar en la zona?
Sub: Cuéntanos tu caso. Respondemos en menos de 24 horas, sin compromiso y sin spam.
CTA primario: Hablemos hoy → /contacto
CTA secundario: Ver propiedades → /propiedades
```

---

## 3. Nueva landing `/comprar`

### 3.1 Meta

- **URL:** `/comprar`
- **Slug en `pages`:** `comprar`
- **Title (SEO):** `Búsqueda asistida de inmuebles en Benito Juárez | Home del Valle`
- **Meta description:** `Encuentra tu próximo hogar en Benito Juárez sin perder fines de semana en visitas. Cuéntanos qué buscas y curamos las mejores opciones para ti en menos de 72 horas.`
- **Mostrar en nav:** sí, etiqueta "Comprar"

### 3.2 Estructura de la página y copy

**Hero**

```
Eyebrow: BÚSQUEDA ASISTIDA · BENITO JUÁREZ
H1: Encuentra tu próximo hogar en Benito Juárez sin perder fines de semana en visitas que no van.
Sub: Cuéntanos qué buscas y curamos las opciones reales en menos de 72 horas. Sin spam, sin agentes que insisten.
CTA primario: Iniciar mi búsqueda
CTA secundario: Ver propiedades disponibles → /propiedades
```

**Stats / prueba social (banda)**

```
72 horas · Tiempo promedio para enviarte tu primera selección
6 colonias · Cobertura especializada en BJ (Del Valle, Narvarte, Nápoles, Portales, Roma Sur, Álamos)
30+ años · De experiencia senior en operaciones complejas
0 spam · Sólo te contactamos si tenemos algo que coincida
```

**Sección "Cómo funciona" (3 pasos numerados)**

```
01. Cuéntanos qué buscas
Llena el brief de 2 minutos: zona, presupuesto, recámaras, financiamiento y timing. Mientras más claro, mejor.

02. Curamos opciones reales
Filtramos nuestro inventario, nuestra red de contactos y el mercado abierto. Sólo te enviamos lo que cumple.

03. Te acompañamos al cierre
Negociación, due diligence legal y firma de escrituras. Con el respaldo notarial completo de Home del Valle.
```

**Sección "Por qué buscar con nosotros"**

```
Acceso a inventario fuera de portales
Trabajamos con propietarios que prefieren venta discreta. Una parte de lo que ofrecemos no aparece publicado.

Filtro inteligente, no catálogo masivo
No vas a recibir 40 opciones. Vas a recibir 3 a 5 que realmente matchean con tu brief.

Asesoría notarial integrada
Revisamos escrituras, sucesiones y régimen de propiedad antes de que firmes algo. Tu blindaje patrimonial es parte del servicio.

Especialistas en Benito Juárez
Conocemos cada colonia, cada calle complicada y cada edificio con historia. No es un mercado genérico, es nuestra zona.
```

**Brief estructurado (formulario principal)**

Encabezado: `Cuéntanos qué buscas`
Sub: `Toma 2 minutos. Te respondemos en menos de 72 horas con opciones curadas.`

Campos (ver sección 8 para validaciones técnicas):

1. **Tipo de inmueble** *(multi-select obligatorio)* — Departamento, Casa, Terreno, Oficina, Comercial.
2. **Operación** *(radio obligatorio)* — Compra, Renta.
3. **Zonas de interés** *(multi-select obligatorio)* — Del Valle (Centro/Norte/Sur), Narvarte, Nápoles, Portales, Álamos & Xoco, Roma Sur & Doctores, Ciudad de los Deportes, Moderna & Letrán Valle, Otra.
4. **Recámaras mínimas** *(select obligatorio)* — 1, 2, 3, 4 o más.
5. **Presupuesto** *(select obligatorio)* — Hasta $4M, $4M – $6M, $6M – $9M, $9M – $14M, $14M+.
6. **Forma de pago** *(select obligatorio)* — Contado, Crédito bancario, INFONAVIT, FOVISSSTE, Mixto.
7. **Timing** *(select obligatorio)* — Inmediato (≤ 1 mes), 1 a 3 meses, 3 a 6 meses, Sólo estoy explorando.
8. **¿Qué te gustaría que tu próximo inmueble tuviera sí o sí?** *(textarea opcional, 280 caracteres)*.
9. **Nombre completo** *(texto obligatorio)*.
10. **Email** *(email obligatorio)*.
11. **WhatsApp** *(teléfono obligatorio, formato MX +52)*.
12. **Aviso de privacidad** *(checkbox obligatorio)* — `He leído y acepto el Aviso de Privacidad`.

**Botón:** `Recibir mi selección curada`
**Microcopy bajo el botón:** `Respuesta en < 72 horas hábiles · Sin compromiso · Sin spam`

**FAQ (acordeón)**

```
¿Cuánto cuesta el servicio de búsqueda asistida?
Es gratuito para el comprador. Nuestra remuneración la cubre el vendedor al cierre, como en una operación tradicional, pero tú accedes al inventario completo y a la asesoría notarial sin costo adicional.

¿Qué tan rápido reciben mi selección?
Enviamos la primera curaduría en menos de 72 horas hábiles desde que llenas el brief. Si tu brief es muy específico y no hay match inmediato, activamos alertas para avisarte cuando entre algo nuevo.

¿Trabajan sólo con su inventario?
No. Filtramos nuestro inventario, nuestra red de contactos privada y el mercado público. Nuestro objetivo es encontrar el inmueble correcto, venga de donde venga.

¿Atienden a compradores con crédito INFONAVIT o FOVISSSTE?
Sí. Tenemos experiencia gestionando los tiempos y requisitos de ambos institutos, incluyendo cofinanciamientos.

¿Puedo cambiar mi brief después de enviarlo?
Por supuesto. Tu brief es un documento vivo. Si después de ver las primeras opciones quieres ajustar zona o presupuesto, lo actualizamos y volvemos a curar.

¿Qué pasa si encuentro una propiedad por mi cuenta?
Si quieres que la asesoremos, te ayudamos con el due diligence legal, la negociación y el cierre. Si decides cerrarla solo, sin problema — no firmas exclusividad con nosotros.
```

**Banda final (CTA repetido)**

```
Headline: ¿Listo para encontrar tu próximo hogar?
Sub: Brief de 2 minutos. Curaduría en 72 horas. Cero compromiso.
CTA primario: Iniciar mi búsqueda (scroll al formulario)
CTA secundario: Hablar por WhatsApp directo
```

---

## 4. Nueva landing `/desarrolladores-e-inversionistas`

### 4.1 Meta

- **URL:** `/desarrolladores-e-inversionistas`
- **Slug en `pages`:** `desarrolladores-e-inversionistas`
- **Title (SEO):** `Captación de predios e inversión inmobiliaria en Benito Juárez | Home del Valle`
- **Meta description:** `Captación de terrenos y producto terminado en Benito Juárez bajo demanda activa. Trabajamos con desarrolladores e inversionistas que necesitan precisión, no catálogo.`
- **Mostrar en nav:** opcional, recomendado en footer y como tarjeta del home

### 4.2 Estructura de la página y copy

**Hero**

```
Eyebrow: B2B · DESARROLLO E INVERSIÓN
H1: Te llevamos a la mesa los predios que sí cumplen.
Sub: Captación de terreno y producto terminado en Benito Juárez bajo demanda activa: no enviamos catálogo, identificamos lo que matchea con tu brief técnico y financiero.
CTA primario: Solicitar brief calificador
CTA secundario: Agendar llamada con dirección
```

**Banda de credibilidad**

```
30+ años · Experiencia senior técnica y legal en operaciones complejas
Demanda activa · Operamos desde el inversionista hacia el activo, no al revés
Red consolidada · Acceso directo a propietarios fuera de portales
Blindaje legal · Due diligence documental en cada captación
```

**Sección "Cómo trabajamos" (4 pasos)**

```
01. Brief técnico y financiero
Definimos contigo qué buscas: tipología, m², uso de suelo, presupuesto, horizonte de inversión y restricciones críticas.

02. Captación dirigida
Activamos nuestra red en Benito Juárez para identificar predios y propiedades que cumplan al 100%, incluyendo activos no listados públicamente.

03. Due diligence integral
Antes de presentarte una opción, validamos escrituración, uso de suelo, gravámenes, factibilidad y régimen fiscal. Si no pasa el filtro, no llega a tu mesa.

04. Cierre y acompañamiento
Negociación, escrituración con respaldo notarial y, si lo requieres, gestión post-cierre del activo.
```

**Sección "Líneas que atendemos" (3 columnas con icono)**

```
Captación de terreno
Predios con potencial habitacional vertical, mixto o comercial en Benito Juárez. Identificamos según tu brief técnico (m² mínimos, frente, uso de suelo, factibilidad).

Producto terminado para portafolio
Departamentos, edificios completos o locales con renta consolidada para inversionistas que buscan flujo y plusvalía en zona AAA.

Coinversión y joint venture
Estructuras de coinversión con propietarios de predios que no quieren vender pero buscan capital para desarrollar.
```

**Sección "Por qué Home del Valle para B2B"**

```
Operamos desde la demanda
A diferencia del modelo tradicional, no captamos en frío. Cada activo que tocamos tiene un comprador alineado en mente. Esto reduce nuestro tiempo de cierre y tu riesgo de oportunidad.

Especialización geográfica
Conocemos las 8 zonas de BJ a nivel calle: dónde está el uso de suelo permitido, qué predios tienen sucesión irregular, qué edificios tienen problemática de régimen condominal. Esto te ahorra meses.

Respaldo legal in-house
Nuestra dirección general dirige el área legal con más de 30 años en operaciones notariales. La revisión documental no se subcontrata, se hace antes de presentarte la opción.

Discreción absoluta
Operaciones B2B se manejan bajo NDA. Tu brief, tu estrategia y los activos que evaluamos no se comparten fuera del equipo asignado.
```

**Brief calificador (formulario)**

Encabezado: `Solicita tu brief calificador`
Sub: `Una vez recibido, agendamos llamada de calificación en menos de 48 horas.`

Campos:

1. **Tipo de operación** *(multi-select obligatorio)* — Compra de predio, Compra de producto terminado, Coinversión / JV, Asesoría puntual.
2. **Uso objetivo** *(multi-select obligatorio)* — Habitacional vertical, Habitacional horizontal, Mixto, Comercial, Oficinas, Industrial ligero.
3. **Rango de m² de terreno buscado** *(select obligatorio)* — < 200, 200–400, 400–800, 800–1500, 1500+.
4. **Zonas de Benito Juárez de interés** *(multi-select obligatorio)* — Del Valle, Narvarte, Nápoles, Portales, Álamos & Xoco, Roma Sur, Ciudad de los Deportes, Moderna, Cualquier zona de BJ.
5. **Presupuesto disponible para captación (MXN)** *(select obligatorio)* — < $20M, $20M–$50M, $50M–$120M, $120M–$300M, $300M+.
6. **Horizonte de inversión** *(select obligatorio)* — ≤ 6 meses, 6 a 12 meses, 12 a 24 meses, 24+ meses.
7. **¿Hay un brief técnico previo que podamos revisar?** *(checkbox + upload opcional, PDF/JPG/PNG, máx 10 MB)*.
8. **Empresa o entidad** *(texto obligatorio)*.
9. **Nombre y rol** *(texto obligatorio)*.
10. **Email corporativo** *(email obligatorio)*.
11. **Teléfono / WhatsApp** *(teléfono obligatorio)*.
12. **NDA** *(checkbox)* — `Solicito que la conversación se maneje bajo acuerdo de confidencialidad`.
13. **Aviso de privacidad** *(checkbox obligatorio)* — `He leído y acepto el Aviso de Privacidad`.

**Botón:** `Enviar brief calificador`
**Microcopy bajo el botón:** `Respuesta en < 48 horas hábiles · Información tratada bajo confidencialidad`

**Banda final**

```
Headline: ¿Tienes capital activo y necesitas precisión, no volumen?
Sub: Hablemos. Una llamada de 30 minutos basta para entender tu brief y decirte si Benito Juárez es el mercado correcto.
CTA primario: Agendar llamada con dirección general
CTA secundario: Enviar brief por correo → leads@homedelvalle.mx
```

---

## 5. Modificaciones a `/vende-tu-propiedad`

Mantener todo el contenido actual *(corregir ortografía según sección 10)*. Únicamente reemplazar el formulario de valuación por la versión segmentada:

### 5.1 Campos del formulario (versión final)

1. **Nombre completo** *(obligatorio)*.
2. **Email** *(obligatorio)*.
3. **WhatsApp** *(obligatorio, formato MX +52)*.
4. **Tipo de propiedad** *(select obligatorio)* — Departamento, Casa, Terreno, Oficina, Local comercial.
5. **Colonia o dirección de la propiedad** *(texto obligatorio)*.
6. **Superficie aproximada (m²)** *(número opcional)*.
7. **Recámaras** *(select opcional)* — 1, 2, 3, 4+, No aplica.
8. **Precio que te gustaría obtener** *(select obligatorio)* — Hasta $4M, $4M – $6M, $6M – $9M, $9M – $14M, $14M+, No estoy seguro.
9. **Motivo de la venta** *(select obligatorio)* — Mudanza, Sucesión / herencia, Liquidez, Mejora patrimonial, Otro.
10. **Estado documental** *(select obligatorio)* — Escrituras al corriente, Pendientes / por regularizar, En sucesión, No estoy seguro.
11. **Timing deseado de cierre** *(select obligatorio)* — Inmediato (≤ 1 mes), 1 a 3 meses, 3 a 6 meses, Sin prisa.
12. **Aviso de privacidad** *(checkbox obligatorio)*.

**Botón:** `Quiero mi valuación gratuita` *(mantener el copy actual)*.

### 5.2 Cambios menores adicionales

- En el bloque de stats, cambiar `45 Dias promedio de venta` → `45 días promedio de venta`.
- En el subheader del bloque "¿Por qué vender con nosotros?", reemplazar el listado actual (Venta rapida / Seguridad juridica / Mejor precio / Transparencia total) por la versión con tildes correctas (ver sección 10).

---

## 6. Modificaciones a `/contacto`

### 6.1 Hero

Mantener el copy actual con corrección de ortografía (sección 10).

### 6.2 Formulario actual → formulario segmentado

Reemplazar los campos genéricos (Nombre / Email / Teléfono / Mensaje) por la versión que **enruta el lead correctamente**:

1. **¿En qué te podemos ayudar?** *(select obligatorio, **primer campo**)* — opciones:
   - `Quiero vender mi propiedad`
   - `Estoy buscando dónde comprar o rentar`
   - `Soy desarrollador o inversionista`
   - `Administración de un inmueble`
   - `Asesoría legal o notarial`
   - `Otro`
2. **Nombre completo** *(obligatorio)*.
3. **Email** *(obligatorio)*.
4. **WhatsApp** *(obligatorio)*.
5. **Colonia de tu interés en BJ** *(select opcional)* — todas las zonas listadas en `/mercado` + Otra.
6. **Mensaje** *(textarea opcional)*.
7. **Aviso de privacidad** *(checkbox obligatorio)*.

### 6.3 Lógica de routing post-submit

Según la opción del campo 1, el lead se etiqueta y enruta así (en el CRM existente):

| Selección | Tag CRM | Acción automática |
|---|---|---|
| Vender mi propiedad | `LEAD_VENDEDOR` | Asignar a captaciones, seguimiento ≤ 24 h |
| Comprar / rentar | `LEAD_COMPRADOR` | Asignar a corretaje, brief de búsqueda en 72 h |
| Desarrollador / inversionista | `LEAD_B2B` | Asignar a dirección general, llamada en ≤ 48 h |
| Administración | `LEAD_ADMIN` | Asignar a área de administración |
| Legal / notarial | `LEAD_LEGAL` | Asignar a Ana Laura Monsivais |
| Otro | `LEAD_OTRO` | Asignar a administración general |

### 6.4 Microcopy de éxito

Después del submit:

```
¡Recibimos tu mensaje, [Nombre]!
Te respondemos por WhatsApp en menos de 24 horas hábiles.
Mientras tanto, puedes echar un ojo al [observatorio de precios de Benito Juárez](/mercado).
```

---

## 7. Header y footer

### 7.1 Header

Agregar el slogan como tagline pequeño debajo o al lado del logo, en la versión desktop. En mobile, sólo el logo.

```
Logo
Tagline (visible ≥ 1024px): Pocos inmuebles. Más control. Mejores resultados.
```

Menú principal, en este orden: `Comprar | Vender | Mercado | Servicios | Nosotros | Testimonios | Guía Inmobiliaria | Contacto`.

CTA del header (botón a la derecha): `Solicitar valuación` → `/vende-tu-propiedad`.

### 7.2 Footer

Estructura sugerida en 4 columnas:

**Columna 1 — Marca**
```
Home del Valle Bienes Raíces
Pocos inmuebles. Más control. Mejores resultados.
Heriberto Frías 903-A, Colonia del Valle, BJ, CDMX 03100.
```

**Columna 2 — Navegación**
```
Comprar
Vender
Desarrollo e inversión
Servicios
Nosotros
```

**Columna 3 — Recursos**
```
Observatorio de precios
Guía inmobiliaria (blog)
Testimonios
Contacto
```

**Columna 4 — Contacto rápido**
```
Tel. 55 1345 0978
contacto@homedelvalle.mx
WhatsApp directo
```

Línea inferior:
```
© 2026 Home del Valle Bienes Raíces. Todos los derechos reservados. · Aviso de Privacidad · Términos
```

### 7.3 Open Graph

Generar OG image del sitio (1200x630) con fondo de marca, slogan y logo:

```
Pocos inmuebles. Más control. Mejores resultados.
Home del Valle Bienes Raíces · Benito Juárez, CDMX
```

---

## 8. Esquema de datos para los nuevos formularios

### 8.1 Tabla sugerida (Laravel 13 migration)

Antes de crear esta tabla, revisar si la tabla `leads` (visible en el dashboard de `/admin`) ya cumple con esta función. Si existe, **extender** esa tabla con las columnas faltantes en lugar de crear `form_submissions`. Si no existe, crear la nueva tabla:

```php
// database/migrations/2026_04_27_000000_create_form_submissions_table.php
return new class extends Migration {
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form_type'); // 'vendedor', 'comprador', 'b2b', 'contacto', 'propiedad'
            $table->string('source_page'); // ruta de origen
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->json('payload'); // estructura específica según form_type
            $table->string('lead_tag')->nullable(); // LEAD_VENDEDOR, LEAD_COMPRADOR, etc.
            $table->enum('status', ['new', 'contacted', 'qualified', 'won', 'lost'])->default('new');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referrer')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['form_type', 'status']);
            $table->index('lead_tag');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
```

Para uploads (brief PDF en B2B), aprovechar Spatie Media Library:

```php
// app/Models/FormSubmission.php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FormSubmission extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'form_type','source_page','full_name','email','phone',
        'payload','lead_tag','status','utm_source','utm_medium',
        'utm_campaign','referrer','ip','user_agent','contacted_at',
        'assigned_to','notes',
    ];

    protected $casts = [
        'payload' => 'array',
        'contacted_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('briefs')
            ->acceptsMimeTypes(['application/pdf','image/jpeg','image/png'])
            ->singleFile();
    }
}
```

### 8.2 Validaciones por formulario

**Comprador (`/comprar`)**
```php
'tipo_inmueble' => 'required|array|min:1',
'tipo_inmueble.*' => 'in:departamento,casa,terreno,oficina,comercial',
'operacion' => 'required|in:compra,renta',
'zonas' => 'required|array|min:1',
'recamaras' => 'required|in:1,2,3,4+',
'presupuesto' => 'required|in:hasta_4m,4m_6m,6m_9m,9m_14m,14m_plus',
'pago' => 'required|in:contado,credito,infonavit,fovissste,mixto',
'timing' => 'required|in:inmediato,1_3m,3_6m,explorando',
'must_have' => 'nullable|string|max:280',
'nombre' => 'required|string|max:120',
'email' => 'required|email',
'whatsapp' => 'required|regex:/^(\+?52)?\s?[0-9]{10}$/',
'aviso' => 'required|accepted',
```

**Desarrollador / inversionista (`/desarrolladores-e-inversionistas`)**
```php
'tipo_operacion' => 'required|array|min:1',
'uso' => 'required|array|min:1',
'm2_terreno' => 'required|in:menos_200,200_400,400_800,800_1500,1500_plus',
'zonas' => 'required|array|min:1',
'presupuesto' => 'required|in:menos_20m,20m_50m,50m_120m,120m_300m,300m_plus',
'horizonte' => 'required|in:6m,6_12m,12_24m,24m_plus',
'brief_file' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
'empresa' => 'required|string|max:160',
'nombre_rol' => 'required|string|max:160',
'email' => 'required|email',
'telefono' => 'required',
'nda' => 'nullable|boolean',
'aviso' => 'required|accepted',
```

**Vendedor (`/vende-tu-propiedad` actualizado)**
```php
'nombre' => 'required|string|max:120',
'email' => 'required|email',
'whatsapp' => 'required|regex:/^(\+?52)?\s?[0-9]{10}$/',
'tipo_propiedad' => 'required|in:departamento,casa,terreno,oficina,comercial',
'colonia' => 'required|string|max:160',
'superficie_m2' => 'nullable|integer|min:1',
'recamaras' => 'nullable|in:1,2,3,4+,na',
'precio_esperado' => 'required|in:hasta_4m,4m_6m,6m_9m,9m_14m,14m_plus,no_se',
'motivo' => 'required|in:mudanza,sucesion,liquidez,patrimonio,otro',
'estado_doc' => 'required|in:al_corriente,pendientes,sucesion,no_se',
'timing' => 'required|in:inmediato,1_3m,3_6m,sin_prisa',
'aviso' => 'required|accepted',
```

### 8.3 Email transaccional al lead (todos los formularios)

Asunto: `Recibimos tu solicitud · Home del Valle`

Cuerpo (HTML):
```
Hola [Nombre],

Recibimos tu [valuación / brief de búsqueda / brief calificador] y vamos a procesarlo.

Tiempo estimado de respuesta: [< 24 / < 72 / < 48] horas hábiles.

Si urge, escríbenos directo por WhatsApp al 55 1345 0978.

Equipo Home del Valle Bienes Raíces
Pocos inmuebles. Más control. Mejores resultados.
Heriberto Frías 903-A · Colonia del Valle · CDMX
```

### 8.4 Notificación interna (al equipo)

Asunto: `Nuevo lead [tag] · [nombre] · [zona]`

Cuerpo: dump completo del payload + UTM + IP + assigned_to.

Canal: email a `leads@homedelvalle.mx` + push a Filament (badge en `/admin/form-submissions` y notificación en bell icon).

---

## 9. Arquitectura Livewire + Filament

### 9.1 Componentes Livewire 4 para los formularios

Cada formulario público es un componente Livewire 4 propio (no Blade puro). Esto habilita validación reactiva, campos condicionales y experiencia sin reload.

**Estructura sugerida**

```
app/Livewire/Forms/
  ├── BuyerSearchForm.php          // /comprar
  ├── DeveloperBriefForm.php       // /desarrolladores-e-inversionistas
  ├── SellerValuationForm.php      // /vende-tu-propiedad (refactor)
  ├── ContactSegmentedForm.php     // /contacto (refactor)
  └── PropertyInquiryForm.php      // ficha de propiedad
resources/views/livewire/forms/
  ├── buyer-search-form.blade.php
  ├── developer-brief-form.blade.php
  ├── seller-valuation-form.blade.php
  ├── contact-segmented-form.blade.php
  └── property-inquiry-form.blade.php
```

**Esqueleto de un componente (ejemplo: BuyerSearchForm)**

```php
namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use App\Notifications\NewLeadNotification;
use Livewire\Component;

class BuyerSearchForm extends Component
{
    // State
    public array $tipo_inmueble = [];
    public string $operacion = 'compra';
    public array $zonas = [];
    public string $recamaras = '';
    public string $presupuesto = '';
    public string $pago = '';
    public string $timing = '';
    public string $must_have = '';
    public string $nombre = '';
    public string $email = '';
    public string $whatsapp = '';
    public bool $aviso = false;

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'tipo_inmueble' => 'required|array|min:1',
            'tipo_inmueble.*' => 'in:departamento,casa,terreno,oficina,comercial',
            'operacion' => 'required|in:compra,renta',
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
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();

        $submission = FormSubmission::create([
            'form_type'   => 'comprador',
            'source_page' => '/comprar',
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => collect($data)->except(['nombre','email','whatsapp','aviso'])->toArray(),
            'lead_tag'    => 'LEAD_COMPRADOR',
            'utm_source'  => request()->query('utm_source'),
            'utm_medium'  => request()->query('utm_medium'),
            'utm_campaign'=> request()->query('utm_campaign'),
            'referrer'    => request()->headers->get('referer'),
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        // Email transaccional + notificación interna
        $submission->notify(new NewLeadNotification());

        $this->reset(); // limpia el form
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.forms.buyer-search-form');
    }
}
```

**Recomendaciones de UX en la vista Blade**

- Usar `wire:model.live` en `operacion` y `tipo_inmueble` para revelar campos contextuales (ej. mostrar "ubicación específica si terreno").
- `wire:loading` y `wire:dirty` para feedback visual del botón de submit.
- Mensaje de éxito condicional con `@if($submitted)`.
- Honeypot oculto + token CSRF nativo de Livewire (ya viene con `@livewireScripts`).
- Rate limit con `RateLimiter::attempt()` en el método `submit()` (5 envíos por hora por IP).

### 9.2 Filament Resource para gestión de leads

Crear `app/Filament/Resources/FormSubmissionResource.php` para que el equipo gestione leads desde `/admin/form-submissions`:

- **List page** con columnas: `created_at`, `form_type` (badge color por tipo), `lead_tag`, `full_name`, `phone`, `status` (badge), `assigned_to.name`.
- **Filtros**: por `form_type`, por `status`, por `lead_tag`, por `created_at` (rango), por `assigned_to`.
- **Bulk actions**: marcar como contactado, asignar a usuario, exportar a CSV.
- **Notificación tipo `Database`** al `assigned_to` cuando se le asigna un lead nuevo (usar `Filament\Notifications\Notification::make()`).
- **View page** con `Infolist` mostrando `payload` decodificado en grid de 2 columnas, link a WhatsApp con número precargado, link al brief PDF si aplica (vía Spatie Media Library).
- **Edit page** para que el agente registre `notes`, `status`, `contacted_at`.
- **Widget en dashboard** con counts: leads nuevos hoy, sin contactar > 24h, por tipo.

### 9.3 Tailwind 4 — tokens de marca

En el CSS principal (probablemente `resources/css/app.css`). Paleta real de Home del Valle: **navy institucional + neutros + verde de sistema**. No usamos dorado, cobre ni acentos cálidos.

```css
@import "tailwindcss";

@theme {
  /* === Marca: navy === */
  --color-navy-950: #0A1A2F;  /* hero deep */
  --color-navy-900: #1F3A5F;  /* institucional */
  --color-navy-700: #1E1B4B;  /* sidebar CRM */

  /* === UI funcional === */
  --color-blue-500: #3B82F6;
  --color-text:     #0F172A;
  --color-muted:    #64748B;
  --color-border:   #E2E8F0;
  --color-surface:  #F1F5F9;

  /* === Estados === */
  --color-success: #16A34A;
  --color-error:   #DC2626;
  --color-warning: #D97706;

  --font-sans: "Inter", "ui-sans-serif", system-ui, sans-serif;
}
```

Con esto, las clases `bg-navy-900`, `text-navy-900`, `border-border`, `bg-surface`, `text-success`, etc., quedan disponibles automáticamente. Eliminar cualquier `tailwind.config.js` heredado de Tailwind 3 si todavía existe.

**Reglas de uso:** el color dominante es navy. El verde se reserva para estados positivos (pills, checkmarks, badge "Exclusiva", WhatsApp) y NUNCA como acento decorativo. El azul medio es funcional para links y secundarios, no se usa en headings.

### 9.4 Iconos con lucide-static

Crear un Blade component `<x-icon name="search" class="w-5 h-5" />` que lea el SVG inline desde `node_modules/lucide-static/icons/{name}.svg` o desde una copia en `resources/svg/lucide/`. Esto evita inyectar `<script>` desde CDN y mantiene SSR limpio.

---

## 10. Auditoría ortográfica — busca/reemplaza

> Aplicar como string replace (case-sensitive) en las plantillas Blade y en la base de datos (campos de `pages`, `menus`, `site_settings`, `properties.description`, etc.). Verificar caso por caso porque algunas palabras existen también con tilde correcta en otros lugares.

### 9.1 Tildes faltantes (errores reales en el sitio)

| Buscar | Reemplazar | Páginas afectadas |
|---|---|---|
| `Dias promedio` | `días promedio` | /vende-tu-propiedad |
| `Venta rapida` | `Venta rápida` | /vende-tu-propiedad |
| `Seguridad juridica` | `Seguridad jurídica` | /vende-tu-propiedad |
| `Analisis de mercado` | `Análisis de mercado` | /vende-tu-propiedad |
| `Valuacion gratuita` | `Valuación gratuita` | /vende-tu-propiedad |
| `comercializacion` | `comercialización` | /vende-tu-propiedad |
| `fotografia profesional` | `fotografía profesional` | /vende-tu-propiedad |
| `gestionamos la documentacion` | `gestionamos la documentación` | /vende-tu-propiedad |
| `Quienes somos` | `Quiénes somos` | /nosotros |
| `Direccion` | `Dirección` | /contacto, /servicios, footer |
| `Siguenos en redes` | `Síguenos en redes` | /contacto |
| `Ubicacion` | `Ubicación` | /contacto |
| `Visitanos` | `Visítanos` | /contacto |
| `Heriberto Frias` | `Heriberto Frías` | /contacto, footer |
| `Cada operacion es unica` | `Cada operación es única` | /testimonios |
| `Aqui comparten su experiencia` | `Aquí comparten su experiencia` | /testimonios |
| `Asesoria inmobiliaria` | `Asesoría inmobiliaria` | /testimonios |
| `Cerramos una buena operacion` | `Cerramos una buena operación` | /testimonios |
| `Benito Juarez` | `Benito Juárez` | title de /vende-tu-propiedad y cualquier otro |

### 9.2 Brand consistency (CRÍTICO)

| Buscar | Reemplazar | Notas |
|---|---|---|
| `Home del valle` *(con "v" minúscula)* | `Home del Valle` | Aparece en múltiples `<title>` y meta tags. La versión correcta es **Valle** capitalizado. Aplicar a toda la base de datos y a las plantillas. |

### 9.3 Patrón a buscar globalmente

Hacer un grep en plantillas y un SELECT en base de datos por las siguientes palabras sin tilde, que típicamente deben llevarla. Revisar caso por caso antes de reemplazar:

```
operacion → operación
gestion → gestión
captacion → captación
valuacion → valuación
ejecucion → ejecución
informacion → información
ubicacion → ubicación
direccion → dirección
descripcion → descripción
solucion → solución
asesoria → asesoría
asesorias → asesorías
practica → práctica (verificar contexto, "práctica" como sustantivo)
basico → básico
publico → público
publica → pública (verificar)
electronico → electrónico
unico → único
unica → única
juridico → jurídico
juridica → jurídica
fotografia → fotografía
videografia → videografía
ortografia → ortografía
metodologia → metodología
estrategia → estrategía? NO, es "estrategia" sin tilde, OK
tecnico → técnico
tecnica → técnica
analisis → análisis
sintesis → síntesis
genesis → génesis
mision → misión
vision → visión
decision → decisión
comision → comisión
condicion → condición
transmision → transmisión
acreditacion → acreditación
documentacion → documentación
comercializacion → comercialización
escrituracion → escrituración
optimizacion → optimización
regularizacion → regularización
acondicionamiento → acondicionamiento (sin tilde, OK)
articulacion → articulación
notarial → notarial (sin tilde, OK)
```

> Para cada hallazgo: si la palabra está dentro de un atributo HTML como `value=`, `href=`, slug, ID o clase CSS, **no reemplazar**. Si está en texto visible, reemplazar.

### 9.4 Comillas tipográficas

Cambiar todas las `"comillas rectas"` por `«comillas españolas»` o `"comillas tipográficas"` según el estilo de marca (recomendación: usar `"…"` para tono boutique). Patrón regex sugerido para Blade:

```regex
"([^"]+?)"   →  "$1"   (en texto visible, no en atributos HTML)
```

### 9.5 Apóstrofes

Reemplazar `'` recto por `'` tipográfico en texto visible.

---

## 11. Brand consistency

- **Nombre de la marca:** siempre `Home del Valle` (con V mayúscula). En contextos formales: `Home del Valle Bienes Raíces`.
- **Slogan oficial:** `Pocos inmuebles. Más control. Mejores resultados.` (capitalización: sólo la primera letra de cada oración, sin caja alta).
- **Geografía:** `Alcaldía Benito Juárez, Ciudad de México`. Abreviación permitida: `BJ` y `CDMX`.
- **Dirección:** `Heriberto Frías 903-A, Colonia del Valle, Benito Juárez, CDMX 03100`.
- **Tono:** boutique, preciso, sobrio. Evitar adjetivos de venta agresiva (`oferta!`, `imperdible`, `el mejor de México`). Preferir números, plazos y procesos verificables.
- **Pronombres:** tuteo profesional (`tu propiedad`, `cuéntanos`). Evitar el `usted` salvo en la landing B2B donde puede mezclarse según el tono del rol del visitante.

---

## 12. Checklist de QA antes de hacer push

**Rutas y rendering**
- [ ] Las dos nuevas rutas (`/comprar`, `/desarrolladores-e-inversionistas`) responden 200 con SSR y registran su entrada en `pages` (slug + title + nav_label) si el sitio así lo requiere.
- [ ] El home muestra el selector de intención y los 3 CTAs llevan al destino correcto.
- [ ] El header muestra el slogan en desktop ≥ 1024px y lo oculta en mobile.
- [ ] El footer tiene las 4 columnas y el slogan visible.

**Livewire / Filament**
- [ ] Los 5 componentes Livewire renderizan sin errores en consola del navegador.
- [ ] `wire:model.live` actualiza correctamente los campos condicionales.
- [ ] Los 5 formularios validan campo por campo (probar happy path y errores).
- [ ] Los formularios escriben en `form_submissions` con el `form_type` correcto y `payload` JSON íntegro.
- [ ] Honeypot oculto + rate limit (5 envíos/h por IP) están activos.
- [ ] Existe el Filament Resource `FormSubmissionResource` accesible en `/admin/form-submissions` con filtros y bulk actions.
- [ ] La asignación de un lead a un usuario dispara `Filament\Notifications\Notification` en el bell icon.
- [ ] Los archivos subidos en el brief B2B se guardan en la collection `briefs` de Spatie Media Library (no en Storage::put manual).
- [ ] El widget de leads nuevos aparece en el dashboard de `/admin`.

**Notificaciones**
- [ ] El email transaccional llega al lead en menos de 60 segundos.
- [ ] La notificación interna llega a `leads@homedelvalle.mx`.
- [ ] El campo "¿En qué te podemos ayudar?" de `/contacto` aplica el `lead_tag` correcto en la base.

**Marca y SEO**
- [ ] Todas las palabras de la sección 10 fueron corregidas (correr `grep -irE` final sobre `resources/views` y `SELECT ... LIKE` en tablas con copy editorial).
- [ ] El title de cada página dice `Home del Valle` (con V mayúscula).
- [ ] La OG image se genera correctamente al compartir cualquier URL en WhatsApp.
- [ ] El sitemap.xml incluye las 2 nuevas rutas.
- [ ] El robots.txt no bloquea las nuevas rutas.
- [ ] Schema.org `RealEstateAgent` incluye dirección, teléfono, email, geo, openingHours.

**Performance y accesibilidad**
- [ ] Lighthouse desktop > 90 en Performance, SEO y Best Practices.
- [ ] Las nuevas rutas pasan el linter de accesibilidad (campos con `<label>`, `aria-required`, mensajes de error visibles).
- [ ] Los iconos de Lucide se cargan inline (no requests externos).
- [ ] Los assets de Tailwind 4 se compilan sin warnings (`vite build`).

---

**Fin del brief.** Si Claude Code necesita decisiones adicionales (ej. nombre exacto de la tabla `leads` existente, esquema de la tabla `properties`, tipografías o estructura de componentes Blade ya en uso en el repo), debe preguntar antes de implementar.
