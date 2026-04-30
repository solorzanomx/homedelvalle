# Brief de implementación para Claude Code · v3
## Home del Valle — Opción C: estado actual + sección de rentas (4to funnel) + pendientes

> **Cómo usar este documento:** está diseñado para pegarse directamente como prompt a Claude Code. La estructura sigue siendo: qué ya está hecho (✅), qué quedó pendiente (❌) y qué sugerencias adicionales (✨).
>
> **Cambio principal en v3:** agregamos la **Sección de Rentas** como cuarto funnel del modelo Opción C. Era una decisión que estaba pendiente y ahora se ejecuta. Sección 4 nueva.
>
> **Última auditoría:** 2026-04-29 sobre `homedelvalle.mx` en producción.

---

## Stack confirmado (no ha cambiado)

PHP 8.3.30 · Laravel 13.6.0 · Livewire 4.2.4 · Filament 5.6.1 instalado pero no es admin primario · MySQL `sql_homedelvalle_mx` · Tailwind 4.2.2 · Vite 8 · TinyMCE 8.3 · Alpine.js (sitio público) · Lucide-static · Spatie Media Library · DomPDF · Intervention/Image · PHPMailer.

CRM custom con sidebar `layouts/app-sidebar.blade.php`. Sitio público con Tailwind + Alpine. Jobs síncronos vía `schedule:run` (cPanel sin queue worker).

**Convenciones obligatorias (no cambian):**
1. Formularios públicos = Alpine + controlador, NO Livewire.
2. Uploads = Spatie Media Library.
3. Admin = Blade custom con CSS variables, NO Filament.
4. Iconos = Lucide-static SVG inline.
5. Estilos = Tailwind 4 con `@theme` en CSS.
6. Email = PHPMailer + SMTP dinámico desde `email_settings`.
7. **Leads se persisten directamente en `clients`** (no `form_submissions` aparte). Operations en pipeline de `operations`.

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

## Resumen del avance vs v2

**Lo que se completó del v2 (avance grande):**

- ✅ Title tags corregidos en TODAS las páginas auditadas: "Home del Valle" con V mayúscula, sin duplicación al final. Verificado en `/`, `/contacto`, `/testimonios`, `/blog`, `/servicios`, `/propiedades`.
- ✅ Navbar reorganizado con los 3 funnels: `Comprar | Vender | Inversión & Desarrollo | Mercado | Servicios | Nosotros | Blog | Contacto`.
- ✅ Botón sólido del navbar cambió de "Vende tu propiedad" a "Hablemos" (neutro) — exactamente como sugería la sección 6 del v2.
- ✅ Selector de intención del home con las 3 tarjetas (Propietarios / Compradores / Desarrollo e Inversión) — tarjeta B2B agregada.
- ✅ Badge "MÁS SOLICITADO" con tilde correcta.
- ✅ Slogan en header ahora muestra "Mejores resultados." (más completo).
- ✅ `/contacto` con **formulario segmentado completo**: campo "¿En qué te podemos ayudar?" con las 6 opciones del brief (vender / comprar-rentar / desarrollador-inversionista / administración / legal / otro). Erratas corregidas: Dirección, Síguenos, Ubicación, Visítanos.
- ✅ `/testimonios` con tildes corregidas en el header de sección y CTA: "Cada operación es única", "Aquí comparten su experiencia", "Contáctanos", "por qué nuestros clientes". Metadata de operación agregada (badge "Compra" en testimonios).
- ✅ Blog explotó: de 1 a **15 artículos** con paginación. 6 categorías estructuradas (Inversión Inmobiliaria · Vender tu Propiedad · Colonias de Benito Juárez · Expertos & Insights · Mercado Inmobiliario CDMX · Zonificación & Desarrollo).
- ✅ Sellos de credibilidad en footer (HTTPS, AMPI).

**Lo que aún está pendiente del v2:**

- ❌ Hero del home sigue diciendo sólo "¿Quieres vender tu propiedad en la Benito Juárez?" — copy del hero es 100% para vendedor; el selector de intención abajo está bien pero el primer impacto del fold sigue desbalanceado.
- ❌ Erratas en home no corregidas:
  - Sección "Operamos desde la demanda", paso 03: `Ejecutamos la operacion` y `Negociacion, blindaje legal y cierre eficiente`.
  - Sección "Líneas de negocio" tarjeta Compra: `catalogo exclusivo`, `Busqueda personalizada`, `Analisis de inversion`, `Acompanamiento legal`.
  - Sección "Líneas de negocio" tarjeta Venta: `Mas solicitado` (badge), `Valuacion profesional`, `Marketing y fotografia`, `Solicitar valuacion gratis`.
  - Sección "Resultados que hablan": `Anos de experiencia senior` (la primera vez que vi esto sin tilde, sigue así).
- ❌ `/testimonios`: aún quedan dos quotes con erratas en el cuerpo del testimonio (probablemente vienen del registro de DB del testimonio de Salvador): `Asesoria inmobiliaria`, `Cerramos una buena operacion`.
- ❌ `/contacto`: dirección sigue como `Heriberto Frias 903-A` (sin tilde en Frías). Y `+5215513450978` se muestra raro — formato pretende ser internacional pero queda denso. Sugerencia: `+52 55 1345 0978`.
- ❌ Footer disclaimer legal: `© 2026 Home del valle Bienes Raíces` (V minúscula), `Asociacion Mexicana`, `Politica de cookies`, `Terminos y condiciones`.
- ❌ Footer columna EXPLORAR: no incluye Comprar, Inversión & Desarrollo, Mercado.
- ❌ Sección "Líneas de negocio" sólo tiene 2 tarjetas (Compra y Venta). Falta Inversión + ahora también Renta.
- ❌ Catálogo público: 1 sola propiedad. Decisión A/B/C del Roadmap fase 1 sigue sin tomar.
- ❌ WhatsApp flotante con mensaje contextual por página (sigue genérico).

---

# 🆕 SECCIÓN NUEVA — Bloque 0: Sección de Rentas (4to funnel)

> Esta es la pieza nueva del v3. Hoy el sitio cubre 3 funnels (Vendedor / Comprador / B2B). Falta el funnel de **Renta**, que tiene comportamiento, plazos, scoring y operación distintos a venta. La buena noticia: el CRM ya está construido para esto (el campo `operations.type` admite `'renta'`, existe `RentalProcess`, existen `rental_stage_logs`, existen contratos de renta y pólizas jurídicas). Lo que falta es la capa pública.
>
> **Por qué importa:** rentas son ciclos cortos (15-30 días vs 90 de venta), comisiones más bajas pero más volumen, y son la puerta natural al servicio de Administración de Inmuebles (recurrente, mejor margen) y al fideicomiso B2B de portafolios. Sin funnel de rentas, todo este negocio depende de WhatsApp y referidos.

## 0.1 Arquitectura del 4to funnel

El funnel de renta tiene dos lados, simétrico al funnel de venta:

```
┌─────────────────────────────────────────┐
│  HOME — selector de intención (4 cards) │
└─────────────┬───────────────────────────┘
              │
   ┌──────────┴──────────┐
   ▼                     ▼
┌────────────────┐   ┌──────────────────────┐
│  /rentar       │   │  /renta-tu-propiedad │
│  (Inquilino)   │   │  (Propietario)       │
└──────┬─────────┘   └──────────┬───────────┘
       │                        │
       ▼                        ▼
   Client                    Client
   (renter, warm)            (owner, warm)
   + Lead Event              + Operation type=captacion
                             + metadata.intent=rental
       │                        │
       ▼                        ▼
   Cuando hay match:         Cuando se firma captación:
   Operation type=renta      auto-spawn Operation type=renta
   stage=lead                stage=lead (ya existe en CRM)
       │                        │
       ▼                        ▼
   Pipeline renta (lead → viewing → offer → signed → funded → closed)
       │
       ▼
   RentalProcess (move-in, contrato, póliza, pagos)
```

**Decisión clave:** vamos por landings dedicadas (no toggle interno), por SEO, claridad de mensaje y porque el ciclo de venta es muy distinto al de renta. Esto crea dos URLs nuevas: `/rentar` y `/renta-tu-propiedad`.

## 0.2 Landing `/rentar` — para inquilinos

### Meta
- **URL:** `/rentar`
- **Slug en `pages`:** `rentar`
- **Title:** `Renta de inmuebles en Benito Juárez | Home del Valle`
- **Meta description:** `Encuentra el inmueble correcto para rentar en Benito Juárez. Te ayudamos a calificar, gestionar la póliza jurídica y firmar con seguridad. Sin engaños, sin sorpresas.`
- **Mostrar en nav:** sí

### Hero

```
Eyebrow:   RENTA ASISTIDA · BENITO JUÁREZ
H1:        Encuentra dónde rentar en Benito Juárez sin contratiempos
Sub:       Inmuebles verificados, pólizas jurídicas claras y un proceso transparente. Sin agentes que insisten ni sorpresas en el contrato.
CTA pri:   Iniciar mi búsqueda de renta
CTA sec:   Ver inmuebles disponibles → /propiedades?operation_type=rental
```

### Stats

```
30+ años    Experiencia senior en BJ
< 72 h      Primera selección curada después de tu brief
8 zonas     Cobertura especializada en Benito Juárez
0 letras    Pólizas jurídicas claras, sin cláusulas escondidas
```

### Sección "Cómo funciona" (3 pasos)

```
01. Cuéntanos qué buscas
Brief de 2 minutos: zona, presupuesto mensual, recámaras, mascotas, plazo y forma de garantizar la renta. Mientras más claro, mejor te encontramos lo correcto.

02. Curamos opciones reales
Filtramos nuestro inventario y nuestra red. Te enviamos 3-5 opciones que cumplen con tu brief. Si no hay match, activamos alerta y te avisamos cuando entre algo.

03. Te ayudamos a firmar con seguridad
Revisamos el contrato y la póliza jurídica antes de que firmes. Te explicamos cláusulas, plazos, depósitos y obligaciones. Tu protección es parte del servicio.
```

### Brief de búsqueda (formulario)

Encabezado: `Cuéntanos qué buscas`
Sub: `Toma 2 minutos. Te respondemos en menos de 72 horas con opciones curadas.`

Campos:

1. **Tipo de inmueble** *(multi-select obligatorio)* — Departamento, Casa, Estudio, Loft, Oficina (con uso habitacional permitido), Casa-habitación con jardín.
2. **Zonas de interés** *(multi-select obligatorio)* — las mismas 8 zonas que en `/comprar`.
3. **Recámaras** *(select obligatorio)* — 1, 2, 3, 4 o más, sin preferencia.
4. **Renta mensual deseada** *(select obligatorio)* — Hasta $15,000 · $15,000–$25,000 · $25,000–$40,000 · $40,000–$70,000 · $70,000+.
5. **Plazo del contrato** *(select obligatorio)* — 6 meses, 12 meses, 24 meses o más, flexible.
6. **¿Vives con mascotas?** *(radio obligatorio)* — Sí, perro / Sí, gato / Sí, otra / No.
7. **Forma de garantizar la renta** *(select obligatorio)* — Aval con propiedad, Póliza jurídica, Depósito ampliado, Aún no decido.
8. **Timing de mudanza** *(select obligatorio)* — Inmediato (≤ 2 sem), 2–4 sem, 1–3 meses, sólo explorando.
9. **¿Qué te gustaría que tu próximo inmueble tuviera sí o sí?** *(textarea opcional, 280 caracteres)*.
10. **Nombre completo** *(obligatorio)*.
11. **Email** *(obligatorio)*.
12. **WhatsApp** *(obligatorio, formato MX +52)*.
13. **Aviso de privacidad** *(checkbox obligatorio)*.

**Botón:** `Recibir mi selección curada`
**Microcopy:** `Respuesta en < 72 horas hábiles · Sin compromiso · Sin spam`

### Sección "Por qué rentar con nosotros"

```
Inventario fuera de portales
Trabajamos con propietarios que prefieren publicar discretamente. Una parte del inventario sólo se ofrece a través de nosotros.

Pólizas jurídicas claras
Si optas por póliza, te explicamos qué cubre, qué cuesta y cuál es la cobertura. Trabajamos sólo con afianzadoras autorizadas y reconocidas.

Pet-friendly cuando aplica
Tenemos propietarios que aceptan mascotas. Te matcheamos sólo con inmuebles donde tu mascota es bienvenida desde el día uno.

Sin "comisión por hablar"
No cobramos al inquilino por buscar ni por mostrar. Nuestra remuneración la cubre el propietario al cierre.
```

### FAQ

```
¿Cuánto cobran al inquilino?
Cero. La búsqueda y asesoría son gratuitas para ti. Nuestra comisión la paga el propietario al firmar contrato.

¿Qué necesito para rentar?
Generalmente: identificación oficial, comprobante de ingresos (3 últimos meses) o aval con propiedad, comprobante de domicilio actual y RFC. Si vas con póliza jurídica, los requisitos los marca la afianzadora.

¿Qué es una póliza jurídica y por qué la pedirían?
Es un instrumento que reemplaza al fiador tradicional. Una afianzadora cubre al propietario en caso de incumplimiento. Para ti como inquilino, suele ser más rápido de tramitar que conseguir un aval con propiedad.

¿Aceptan inquilinos con mascotas?
Sí, dentro del inventario que las acepta. Cuando llenas tu brief y marcas que tienes mascota, sólo te enviamos opciones donde se permiten.

¿Puedo cambiar mi brief después?
Sí, en cualquier momento. Si después de la primera curaduría quieres ajustar zona, presupuesto o plazo, lo actualizamos y volvemos a buscar.

¿Cuánto suele tardar todo el proceso?
Desde la primera curaduría hasta firmar contrato, entre 7 y 21 días si el inquilino tiene documentación lista y elige una opción que ya tiene póliza pre-aprobada.
```

### Banda final

```
H:    ¿Listo para encontrar dónde rentar?
Sub:  Brief de 2 minutos. Curaduría en 72 horas. Cero compromiso.
CTA:  Iniciar mi búsqueda
```

## 0.3 Landing `/renta-tu-propiedad` — para propietarios

### Meta
- **URL:** `/renta-tu-propiedad`
- **Slug en `pages`:** `renta-tu-propiedad`
- **Title:** `Renta tu propiedad en Benito Juárez | Home del Valle`
- **Meta description:** `Renta tu inmueble en Benito Juárez con seguridad jurídica, póliza profesional y administración integral si la necesitas. Sin sorpresas, sin morosidad inesperada.`
- **Mostrar en nav:** sí

### Hero

```
Eyebrow:   RENTA SEGURA · CUPO LIMITADO
H1:        Renta tu inmueble en Benito Juárez con cero dolores de cabeza
Sub:       Calificamos al inquilino, gestionamos la póliza jurídica y, si lo prefieres, administramos el inmueble por ti. Sin morosidad inesperada, sin meses vacíos.
CTA pri:   Solicitar asesoría gratuita
CTA sec:   ¿Cuánto rentaría mi inmueble? → calculadora rápida (futuro)
```

### Stats

```
30+ años    Experiencia gestionando rentas en BJ
< 30 días   Tiempo promedio en colocar un inmueble bien presentado
98%         Pago puntual con póliza jurídica activa
50+         Inmuebles bajo administración integral
```

### Sección "Por qué rentar con nosotros"

```
Calificación seria del inquilino
Verificamos identidad, ingresos, historial crediticio (cuando aplica) y referencias. Si no pasa, no firma. Tu inmueble no entra en riesgo por presión de cerrar.

Póliza jurídica profesional
Trabajamos con afianzadoras reconocidas. La póliza protege tu ingreso ante incumplimiento, daños o juicios. Si lo prefieres, podemos estructurar aval con propiedad.

Administración integral si la necesitas
Si no quieres preocuparte por cobranza, mantenimiento o trámites, nosotros lo hacemos. Reportes mensuales, cuenta clara, intervención inmediata cuando algo falla.

Marketing y matching dirigidos
No publicamos en portales saturados. Tu inmueble llega sólo a inquilinos calificados que ya pasaron filtro por nuestro brief. Menos visitas, mejor calidad.
```

### Sección "Proceso 01-02-03"

```
01. Asesoría y precio de salida
Visitamos tu inmueble, analizamos comparables del mercado en tu colonia y te proponemos un rango de renta realista (no inflado para captar la firma).

02. Marketing dirigido y filtro
Tomamos fotos profesionales, redactamos la ficha y la enviamos a inquilinos calificados de nuestra red. Te entregamos una shortlist con perfil, ingresos y referencias.

03. Firma, póliza y entrega
Coordinamos firma de contrato, póliza jurídica activa, entrega del inmueble con inventario fotográfico y, si aplica, arranque de administración integral.
```

### Brief para propietario (formulario)

Encabezado: `Solicita tu asesoría gratuita`
Sub: `Responderemos en menos de 24 horas con un rango de renta y un plan personalizado.`

Campos:

1. **Nombre completo** *(obligatorio)*.
2. **Email** *(obligatorio)*.
3. **WhatsApp** *(obligatorio)*.
4. **Tipo de propiedad** *(select obligatorio)* — Departamento, Casa, Estudio, Loft, Oficina, Local comercial.
5. **Colonia o dirección** *(texto obligatorio)*.
6. **Superficie aproximada (m²)** *(número opcional)*.
7. **Recámaras** *(select opcional)* — 1, 2, 3, 4+, no aplica.
8. **¿Está amueblado?** *(radio obligatorio)* — Sí, completo / Sí, parcial / No.
9. **Renta mensual que te gustaría obtener** *(select obligatorio)* — Hasta $15,000 · $15,000–$25,000 · $25,000–$40,000 · $40,000–$70,000 · $70,000+ · No estoy seguro.
10. **Plazo mínimo de contrato deseado** *(select obligatorio)* — 6 meses, 12 meses, 24 meses, sin preferencia.
11. **¿Aceptas inquilinos con mascotas?** *(radio obligatorio)* — Sí · No · Depende del inquilino.
12. **Estado documental** *(select obligatorio)* — Escrituras al corriente, Pendientes / por regularizar, En sucesión, No estoy seguro.
13. **¿Te interesa administración integral?** *(radio obligatorio)* — Sí, quiero que la administren · No, sólo busco inquilino · Quiero conocer la opción primero.
14. **¿Buscas póliza jurídica?** *(radio obligatorio)* — Sí, obligatoria · Sí, si el inquilino no tiene aval · Prefiero aval tradicional · No estoy seguro.
15. **Timing deseado para colocar** *(select obligatorio)* — Inmediato (≤ 2 sem), 2–4 sem, 1–3 meses, sin prisa.
16. **Aviso de privacidad** *(checkbox obligatorio)*.

**Botón:** `Quiero mi asesoría gratuita`
**Microcopy:** `Respuesta en < 24 horas hábiles · Sin compromiso · Sin spam`

### FAQ

```
¿Cuánto cuesta poner mi inmueble en renta con ustedes?
Cero por adelantado. Cobramos comisión sólo cuando se firma contrato. La comisión estándar de mercado en CDMX es un mes de renta, negociable según el caso.

¿En cuánto tiempo se renta un inmueble bien presentado?
Promedio en BJ: 15–30 días si el precio está alineado al mercado y la presentación es buena. Inmuebles muy específicos pueden tardar más; en la asesoría te decimos plazo realista.

¿Qué pasa si el inquilino no paga?
Si tienes póliza jurídica activa, la afianzadora cubre y procede legalmente. Si optaste por aval, ejecutamos el aval con respaldo legal. En ambos casos, te acompañamos hasta resolver.

¿Necesito firmar exclusividad?
No exigimos exclusividad. Si decides trabajar con varias inmobiliarias, está bien para nosotros. Sólo recuerda que múltiples publicaciones simultáneas pueden enviar señal de inmueble difícil de colocar.

¿Cómo funciona la administración integral?
Cobramos un porcentaje mensual (típicamente 6–10% de la renta dependiendo del servicio). Cubre: cobranza, atención al inquilino, mantenimiento preventivo y correctivo, reportes mensuales, intervención legal si es necesario. Tú recibes la renta neta en tu cuenta.

¿Aceptan rentas vacacionales o por días?
Hoy nuestro foco es renta tradicional (mínimo 6 meses). Si buscas vacacional, podemos referirte a operadores especializados con quienes trabajamos.
```

### Banda final

```
H:    ¿Listo para rentar tu inmueble con tranquilidad?
Sub:  Asesoría gratuita en menos de 24 horas. Sin exclusividad forzada. Cero compromiso.
CTA:  Solicitar mi asesoría
```

## 0.4 Cambios al home para incluir Renta

### Selector de intención: de 3 a 4 tarjetas

Reorganizar la sección "Soluciones para cada perfil" a **4 tarjetas equilibradas** (cada una sin la dominancia visual "MÁS SOLICITADO" que tenía Compradores; o si se mantiene, ponerla en Comprar y Rentar simultáneamente):

| Tarjeta | Eyebrow | Título | Sub | CTA | Destino |
|---|---|---|---|---|---|
| 1 | PROPIETARIOS | Quiero vender | Valuación profesional gratuita y plan de comercialización para vender en 45 días promedio. | Solicitar valuación → | `/vende-tu-propiedad` |
| 2 | PROPIETARIOS | Quiero rentar mi inmueble | Calificación seria de inquilinos, póliza jurídica y administración integral si la necesitas. | Solicitar asesoría → | `/renta-tu-propiedad` |
| 3 | BUSCO INMUEBLE | Comprar para vivir | Búsqueda asistida con propiedades verificadas y acompañamiento legal completo. | Iniciar búsqueda → | `/comprar` |
| 4 | BUSCO INMUEBLE | Rentar para vivir | Inmuebles verificados, pólizas claras y proceso transparente. Sin agentes que insisten. | Buscar para rentar → | `/rentar` |

Más abajo, mantener la tarjeta de DESARROLLO E INVERSIÓN como bloque separado (B2B no entra en este grid de 4 porque tiene dinámica distinta).

### Sección "Líneas de negocio": de 2 a 4 tarjetas

Hoy: Compra y Venta. Cambiar a:

| Tarjeta | Título | Sub | CTA | Destino |
|---|---|---|---|---|
| 1 | Venta | Vendemos tu propiedad al mejor precio del mercado. | Solicitar valuación gratis → | `/vende-tu-propiedad` |
| 2 | Renta tu propiedad | Te conseguimos inquilino calificado con póliza jurídica. | Solicitar asesoría → | `/renta-tu-propiedad` |
| 3 | Compra | Encuentra la propiedad correcta con búsqueda asistida y due diligence legal. | Ver propiedades en venta → | `/propiedades?operation_type=sale` |
| 4 | Renta para vivir | Inmuebles verificados, pólizas claras, sin sorpresas. | Ver propiedades en renta → | `/propiedades?operation_type=rental` |

Esto resuelve el desbalance actual donde sólo aparecen Compra y Venta.

### Hero del home (problema persistente)

El hero sigue diciendo `¿Quieres vender tu propiedad en la Benito Juárez?`. Recomendación: pasar a un copy neutro o dinámico:

```
Eyebrow:   FIRMA BOUTIQUE EN BENITO JUÁREZ · 30+ AÑOS
H1:        Pocos inmuebles. Más control. Mejores resultados.
Sub:       Comercializamos propiedades en Benito Juárez con seguridad jurídica y un proceso transparente. ¿Cómo podemos ayudarte hoy?
CTA pri:   ¿Qué necesitas? (scroll al selector de intención)
CTA sec:   Hablar por WhatsApp
```

Alternativamente, si quieren mantener un hero "vendedor" como hero principal, mover los otros 3 funnels arriba del fold como tarjetas pequeñas en el lado derecho del hero (split hero).

## 0.5 Cambios al navbar y footer

### Navbar — opción A (mantener flat, agregar 2 items)

`Comprar | Rentar | Vender | Renta tu propiedad | Inversión & Desarrollo | Mercado | Servicios | Nosotros | Blog | Contacto`

Total: 10 items. Demasiado. **No recomendado**.

### Navbar — opción B (con dropdowns, RECOMENDADO)

```
Buscar inmueble  ▾    Soy propietario  ▾    Inversión & Desarrollo    Mercado    Servicios    Nosotros    Blog    Contacto

Dropdown "Buscar inmueble":
  → Comprar      → Rentar      → Ver todas las propiedades

Dropdown "Soy propietario":
  → Vender mi propiedad      → Rentar mi propiedad      → Administración de inmuebles
```

Esto da 8 items principales con sub-menús, escalable y más claro para el visitante.

### Footer columna EXPLORAR — agregar items faltantes

```
Comprar
Rentar
Vender
Renta tu propiedad
Inversión & Desarrollo
Propiedades
Precios de Mercado
Nosotros
Blog
Contacto
```

(10 items en footer está bien; no compite con jerarquía visual del header.)

## 0.6 Cambios al CRM admin para Rentas

El CRM ya tiene la maquinaria de renta (`Operation type='renta'`, `RentalProcess`, `rental_stage_logs`, `Contract`, `PolizaJuridica`). Lo que hay que asegurar:

1. **Routing automático del lead de `/rentar`** al asignado correcto (round-robin entre agentes de corretaje, mismo pool que comprador, pero filtrar por capacidad si se considera renta como servicio especializado).

2. **Routing del lead de `/renta-tu-propiedad`**: round-robin entre agentes de captación. Si el lead marca "quiero administración integral", asignar prioritariamente al agente que hoy lleva Administración (cuando exista ese rol; mientras tanto, a Alex/dirección).

3. **Vista en el dashboard admin**: agregar widget "Rentas activas" complementario al "Pipeline activo" y "Captaciones en progreso". Muestra:
   - Inmuebles publicados en renta con días en mercado.
   - Brief de inquilinos esperando matching.
   - Contratos por vencer en 60 días (renovación / re-marketing).

4. **Filament Resource** — si llegan a hacerlo (no es prioritario):
   - Lista de `RentalProcess` con status, fecha de inicio, próxima cobranza, deuda.

5. **Plantillas de email transaccional** (en `email_templates`):
   - `lead_renter_received`: confirma al inquilino que su brief llegó.
   - `lead_rental_owner_received`: confirma al propietario que recibimos su solicitud.
   - `rental_match_notification`: cuando un inmueble matchea con un brief de renta, el inquilino recibe la selección.

## 0.7 Schema de datos para rentas

Ya existe en CRM, pero verificar:

- `clients.client_type` debe permitir el valor `'renter'` (además de buyer/owner/investor). Si no lo permite, agregar a la lista de enums.
- `clients.metadata` (JSON) debe poder almacenar:
  - Para renter: `zonas`, `plazo`, `mascotas`, `garantia` (aval/poliza/deposito), `timing`.
  - Para owner-renter: `amueblado`, `mascotas_aceptadas`, `quiere_administracion`, `prefiere_poliza`.
- `operations.type='renta'` ya existe.
- `RentalProcess` ya existe — verificar que se cree automáticamente al firmar contrato (sí debería ya).
- Confirmar que `properties.operation_type` admite `'rental'` (sí, ya lo confirmamos en `/propiedades`).

## 0.8 Página de propiedad en renta

Cuando una propiedad tiene `operation_type='rental'`, la ficha individual debe:

- Mostrar precio como **renta mensual** (no precio total): `$25,000 / mes`.
- Mostrar **plazo mínimo** del contrato si está definido.
- Mostrar **¿acepta mascotas?** como atributo visible.
- Mostrar **¿está amueblado?**.
- Mostrar **costos asociados**: mantenimiento, agua, gas (si aplica).
- Mostrar **garantías aceptadas**: aval con propiedad / póliza jurídica / depósito.
- Formulario lateral con copy específico de renta: `¿Te interesa esta renta?` (no `¿Te interesa esta propiedad?`).
- Submit del formulario crea `Client` con `client_type='renter'` y `lead_event` con `property_id` para tracking.

## 0.9 SLA y workflow de Operaciones (para Manual de Operaciones)

| Tipo | Primer contacto | Primera selección/valuación | Cierre típico |
|---|---|---|---|
| Inquilino (`/rentar`) | 60 min hábiles | 72 horas | 7–21 días desde primer match |
| Propietario renta (`/renta-tu-propiedad`) | 30 min hábiles | 24 horas (rango orientativo + propuesta) | Colocación 15–30 días |
| Visitas a propiedad de renta | Agendar dentro de 48 h del primer contacto | — | — |
| Firma de contrato + póliza | — | — | 24–72 h después de aprobación |

## 0.10 Casos de uso al lanzar (para validar end-to-end)

Antes de declarar la sección de rentas como "listo", probar estos 6 escenarios:

1. **Inquilino ideal:** llena `/rentar` con presupuesto $25,000, sin mascotas, póliza jurídica, timing inmediato. → debe llegar al CRM como `Client(client_type='renter', lead_temperature='hot')`, asignarse a un agente, dispararse email transaccional, aparecer en dashboard.
2. **Inquilino con mascota:** marca "perro" — el matching automático futuro deberá filtrar por inmuebles que aceptan mascotas (hoy se hace manual desde la metadata).
3. **Propietario con administración:** llena `/renta-tu-propiedad`, marca "quiero administración integral". → en el CRM se etiqueta para seguimiento dual (renta + administración).
4. **Propietario sin póliza preferida:** marca "no estoy seguro". → el agente debe educar en la primera llamada usando el FAQ como guion.
5. **Inquilino solo busca explorar:** `timing='sólo explorando'`. → entrar a automation `nurturing_renter_pasivo` con frecuencia mensual de novedades.
6. **Propiedad publica con mascotas:** crear una propiedad con `metadata.allows_pets=true` y verificar que la ficha individual lo muestra como badge visible.

---

## Estado por sección (resto del sitio, recordatorio v2 con updates)

### 1. Home (`/`)

**✅ Hecho desde v2**
- Title corregido: `Firma inmobiliaria boutique en Benito Juárez | Home del Valle`.
- Navbar reorganizado con 3 funnels + botón "Hablemos" neutro.
- Selector de intención con 3 tarjetas (Propietarios / Compradores / Desarrollo e Inversión).
- Badge "MÁS SOLICITADO" con tilde.
- Slogan en header con "Mejores resultados.".

**❌ Pendiente del v2**
- Hero copy sólo de vendedor. Cambiar al hero neutro de la sección 0.4 de este v3.
- Erratas en home no corregidas:
  - Paso 03 ("Operamos desde la demanda"): `Ejecutamos la operacion`, `Negociacion, blindaje legal y cierre eficiente`.
  - Tarjeta Compra: `catalogo exclusivo`, `Busqueda personalizada`, `Analisis de inversion`, `Acompanamiento legal`.
  - Tarjeta Venta: `Mas solicitado` (badge), `Valuacion profesional`, `Marketing y fotografia`, `Solicitar valuacion gratis`.
  - Sección "Resultados que hablan": `Anos de experiencia senior`.

**❌ Nuevo en v3**
- Selector de intención debe pasar de 3 a 4 tarjetas (agregar "Rentar para vivir" o reorganizar como sección 0.4).
- "Líneas de negocio" debe pasar de 2 a 4 tarjetas (Venta / Renta tu propiedad / Compra / Renta para vivir).

### 2. Landings existentes

`/comprar`, `/desarrolladores-e-inversionistas`, `/vende-tu-propiedad` — todas con copy y formulario completos. Sólo pendiente:
- Title de las 3 ya correcto (sin duplicación).
- Verificar mapeo a CRM (sección 10 del v2 sigue igual).

### 3. /contacto

**✅ Avance enorme**
- Title corregido.
- Erratas corregidas (Dirección, Síguenos, Ubicación, Visítanos).
- **Formulario segmentado completo** con campo "¿En qué te podemos ayudar?" + 6 opciones.

**❌ Pendiente menor**
- `Heriberto Frias` → `Heriberto Frías`.
- Teléfono `+5215513450978` → formato visible: `+52 55 1345 0978` (inserción de espacios para legibilidad).

**🆕 Nuevo en v3 — agregar a las opciones del select**
La opción 2 actual dice "Estoy buscando dónde comprar o rentar" — está bien pero el routing debe distinguir intención. Sugerencia: separar en 2 opciones para enrutar mejor:
- `Estoy buscando dónde comprar`
- `Estoy buscando dónde rentar`

Y considerar agregar también:
- `Quiero rentar mi propiedad` (separar de "Quiero vender mi propiedad").

Total: el select pasa de 6 a 8 opciones. Es razonable.

### 4. /testimonios

**✅ Avance**
- Title corregido.
- Erratas en headers y CTA corregidas.
- Metadata "Compra" agregada en testimonio destacado.

**❌ Pendiente**
- Quote de Salvador con erratas (probablemente vienen de la DB del registro `testimonial`):
  - `Asesoria inmobiliaria` → `Asesoría inmobiliaria`
  - `Cerramos una buena operacion` → `Cerramos una buena operación`
- Sólo 3 testimonios. Falta workflow post-cierre que el v2 sugirió.

### 5. /servicios

**✅ Limpio** — sin erratas detectables, copy completo, las 5 líneas claras.

**🆕 Sugerencia v3**: agregar 6ta línea o destacar "Renta y Administración Integral" como un combo, ya que hoy "Administración de Inmuebles" está en la lista pero el flujo público no la conecta con renta.

### 6. /nosotros

**✅ Title corregido**

**❌ Pendiente del v2**
- `Quienes somos` → `Quiénes somos`.

### 7. /blog

**✅ Avance enorme**
- 15 artículos (era 1).
- 6 categorías estructuradas.
- Title corregido.
- Paginación funcionando.

**🆕 Sugerencia v3**: agregar categoría "Renta e Inquilinos" y empezar a poblarla con 3-4 artículos en los próximos 60 días para apoyar SEO del nuevo funnel.

### 8. /propiedades

**✅ Title corregido**
- Filtros de operación (Venta / Renta) ya existen.

**❌ Pendiente**
- Sigue con 1 sola propiedad. Sin propiedades en renta.

**🆕 v3**
- Cuando se publiquen propiedades de renta, asegurar que la card muestre `$XX,XXX / mes` (no precio total) y badge `Renta` claramente visible.

### 9. Footer

**✅ Avance**
- Slogan completo en footer.
- 4 columnas organizadas.
- Sellos de credibilidad (HTTPS, AMPI).

**❌ Pendiente del v2**
- `© 2026 Home del valle Bienes Raíces` → `© 2026 Home del Valle Bienes Raíces`.
- `Asociacion Mexicana` → `Asociación Mexicana`.
- `Politica de cookies` → `Política de cookies`.
- `Terminos y condiciones` → `Términos y condiciones`.
- `Heriberto Frias 903-A` → `Heriberto Frías 903-A`.

**❌ Pendiente columna EXPLORAR (sigue incompleta)**
- Falta: Comprar, Rentar, Renta tu propiedad, Inversión & Desarrollo, Mercado.

---

## Plan de acción priorizado v3

### Bloque 0 (NUEVO) — Sección de Rentas (3-5 semanas)

1. **Crear landings `/rentar` y `/renta-tu-propiedad`** con copy y formularios de las secciones 0.2 y 0.3.
2. **Agregar las 2 nuevas rutas a `pages` y `menu_items`** (header navbar + footer EXPLORAR).
3. **Agregar el valor `'renter'` a `clients.client_type`** si no lo permite todavía.
4. **Implementar los endpoints** `LandingController@storeRenterSearch` y `LandingController@storeRentalOwnerInquiry` con la lógica de mapeo a `Client` + (cuando aplique) `Operation type='captacion'` con `metadata.intent='rental'`.
5. **Crear plantillas de email** `lead_renter_received` y `lead_rental_owner_received`.
6. **Reorganizar selector de intención del home** a 4 tarjetas (sección 0.4).
7. **Reorganizar "Líneas de negocio" del home** a 4 tarjetas (sección 0.4).
8. **Probar los 6 casos de uso** de la sección 0.10.

### Bloque 1 — Hygiene del home y resto (1 semana, paralelo al bloque 0)

9. **Erratas del home**: corregir las listadas en sección 1 ("Operamos desde la demanda", tarjetas Compra y Venta, "Resultados que hablan").
10. **Hero del home**: reemplazar por copy neutro (sección 0.4 v3).
11. **Footer disclaimer legal**: las 5 erratas pendientes.
12. **`/nosotros`**: `Quienes somos` → `Quiénes somos`.
13. **`/contacto`**: `Heriberto Frias` → `Heriberto Frías`. Considerar formato de teléfono más legible.
14. **`/testimonios`**: corregir las 2 erratas en el quote de Salvador (probablemente edit en DB).

### Bloque 2 — Descubribilidad y conversión (después del bloque 0)

15. **Navbar con dropdowns** (sección 0.5 opción B).
16. **Footer columna EXPLORAR completa** con los 10 items.
17. **WhatsApp flotante con mensaje contextual por página** (incluyendo nuevos contextos de renta).
18. **Catálogo de propiedades**: tomar decisión A/B/C del Roadmap fase 1, publicar 5–10 inmuebles curados (al menos 2 en renta).

### Bloque 3 — Confianza y conversión (continuo)

19. **Casos de éxito por funnel** (incluyendo casos de renta).
20. **Workflow automatizado de captura de testimonio post-cierre**.
21. **Página `/servicios` con sección destacando "Renta + Administración"**.
22. **Categoría de blog "Renta e Inquilinos"** con primeros 3-4 artículos.

---

## Checklist de QA actualizado v3

### Sección de Rentas
- [ ] `/rentar` responde 200 con copy y formulario completo.
- [ ] `/renta-tu-propiedad` responde 200 con copy y formulario completo.
- [ ] Submit de `/rentar` crea `Client(client_type='renter', lead_temperature='warm')` con metadata correcta.
- [ ] Submit de `/renta-tu-propiedad` crea `Client(client_type='owner')` con `metadata.intent='rental'` y `Operation(type='captacion', stage='inquiry')`.
- [ ] Email transaccional al lead llega < 60 s para ambas landings.
- [ ] Las 2 landings aparecen en navbar (con dropdown si aplica) y en footer EXPLORAR.
- [ ] Hero del home incluye Rentar como opción visible (selector 4 tarjetas).
- [ ] Sección "Líneas de negocio" del home muestra 4 tarjetas.
- [ ] `/propiedades?operation_type=rental` filtra correctamente y muestra cards con precio mensual.
- [ ] Ficha de propiedad en renta muestra: precio mensual, plazo, mascotas, amueblado, garantías aceptadas.
- [ ] Los 6 casos de uso de la sección 0.10 pasan.

### Marca y SEO (recordatorio del v2)
- [ ] `grep -ri "Home del valle"` (con v minúscula) sobre el repo devuelve 0.
- [ ] sitemap.xml incluye `/rentar` y `/renta-tu-propiedad`.
- [ ] OG image al compartir cualquier nueva URL en WhatsApp se ve bien.
- [ ] Schema.org `RealEstateAgent` actualizado para incluir mención de servicio de renta.

### Erratas (recordatorio del v2)
- [ ] Todas las erratas del bloque 1 (home, footer, nosotros, contacto, testimonios) corregidas.

---

**Fin del brief v3.**

Si Claude Code va a implementar varios bloques, recomendado partirlo así:

- **PR 1**: Bloque 0 (sección de rentas) — landings + endpoints + emails + cambios al home + nav/footer mínimos.
- **PR 2**: Bloque 1 (hygiene de erratas pendientes).
- **PR 3**: Bloque 2 (navbar con dropdowns, WhatsApp contextual, catálogo).
- **PR 4**: Bloque 3 (casos, testimonios, contenido blog renta).

Si surge duda sobre la estructura del CRM (por ejemplo, si `clients.client_type` ya admite `'renter'`, o si las plantillas de email tienen variables específicas), preguntar **antes** de implementar — es probable que la maquinaria del CRM ya tenga lo necesario y sólo falte cablearlo.
