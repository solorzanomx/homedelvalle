# Blog: optimizaciones post-lanzamiento (leads)

> 2026-09-30. Ronda de mejoras pedida por Alejandro después de revisar las 7 fases de
> `prompt-claude-code-blog-leads.md` en vivo ("arregla y optimiza todo"). Complementa, no reemplaza,
> a `blog-cta-clusters.md`, `blog-calculadora-sucesion.md` y `blog-leads-panel.md`.

## 1. Pixel de Meta con eventos reales
Antes solo mandaba `PageView`. Ahora `hdvTrack()` (`layouts/public.blade.php`) también dispara:
- `fbq('track', 'Lead', ...)` en `generate_lead` — evento estándar de Meta, el único que Meta usa
  para optimizar campañas y armar públicos similares.
- `fbq('trackCustom', ...)` en `calculator_complete` y `whatsapp_click`.

## 2. Calculadora: cálculo y captura en UN SOLO paso
**Cambio de diseño real, no solo un ajuste:** antes el cálculo era gratis y sin fricción, y
DESPUÉS de ver el resultado se pedía el WhatsApp — quien veía el desglose y se iba sin dejarlo
quedaba completamente perdido (sin nombre, sin teléfono, sin nada). Ahora el WhatsApp (obligatorio)
va en el MISMO formulario que los datos del inmueble; nombre y correo siguen opcionales. Un solo
`calculate()` valida todo, calcula, crea el lead y muestra el resultado — sigue siendo instantáneo,
sin promesa de "en 24h", y el desglose completo se sigue mostrando gratis (no se puso detrás de
ningún muro). Quien llega al resultado siempre queda como lead capturado.

`app/Livewire/Blog/SuccessionCalculator.php` — ya no existen `submitLead()`/`submitted` por
separado; todo vive en `calculate()`.

## 3. Correo opcional en los 2 formularios del blog
`CtaCapture` y `SuccessionCalculator` ahora aceptan un correo opcional (colapsado bajo "¿Prefieres
que también te escribamos por correo?", no junto al WhatsApp para no sumarle fricción al campo
obligatorio). Si lo dan, además de guardarse:
- Habilita `AutomationEngine::processFormSubmitted()` (antes se omitía siempre porque esa función
  exige correo) — reactiva cualquier automatización de correo que ya exista en el sitio para estos
  leads.
- `LegalAcceptance::record()` usa el correo real como identificador en vez del WhatsApp.

## 4. Calculadora activada en un segundo post de herencias
`hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx` ahora también la muestra — un
coheredero en desacuerdo casi siempre implica una sucesión sin resolver. **`isr-venta-propiedad-
heredada-mexico-2026` NO la recibió a propósito**: ese post trata el ISR de la venta, no el costo
de la sucesión — forzar la misma calculadora ahí sería una herramienta que no responde la pregunta
del artículo (habría que construir una calculadora de ISR distinta, fuera de alcance de esta ronda).

## 5. Recordatorio automático de leads del blog sin contactar
Antes un heredero en "etapa temprana" que no avanzaba se enfriaba solo — nadie se lo recordaba al
asesor. Comando `blog:remind-stale-leads` (diario, 9:00am, `routes/console.php`): notifica al
asignado (o a los admins si no hay asignado) sobre cualquier `FormSubmission` con
`payload.origen` que empiece con `blog_` y siga en `status='new'`, a los **7 y 14 días**. Tabla
`blog_lead_reminders` evita duplicar el mismo aviso — y si el comando no corre un día, se pone al
corriente solo (compara "al menos N días", no "exactamente N").

## 6. QA de móvil — encontró y arregló 2 problemas reales
1. **La calculadora y el form genérico de valuación quedaban uno debajo del otro**, pidiendo lo
   mismo dos veces con promesas distintas (una instantánea, otra "en 24h"). Ahora son mutuamente
   excluyentes: si el post tiene calculadora, no se muestra el form genérico ahí (`blog/show.blade.php`).
2. **El botón flotante de WhatsApp genérico estaba invisible en TODO post con cluster desde que se
   desplegó la Fase 3.** La Fase 3 le agregó las clases `hidden sm:block` (para ocultarlo solo en
   móvil cuando el post trae su propio sticky), pero nunca se reconstruyó el CSS — como ninguna otra
   parte del sitio usaba exactamente `sm:block`, Tailwind nunca lo generó en el bundle commiteado, y
   el botón simplemente no existía visualmente en ningún tamaño de pantalla. Se corrigió con
   `npm run build` (nuevo `public/build/assets/app-*.css`, commiteado). **Lección para la próxima
   vez que se agregue una clase de Tailwind nueva: correr `npm run build` antes de dar por buena la
   vista** — no basta con que compile el Blade, el CSS es un bundle estático que hay que regenerar.

## INVARIANTES — no romper
- `SuccessionCalculator::calculate()` es la única acción del componente — no reintroducir un
  segundo paso de captura separado del cálculo.
- El correo sigue siendo opcional en los dos formularios — nunca `required`.
- `blog/show.blade.php` nunca debe mostrar `<livewire:blog.succession-calculator>` Y
  `<livewire:forms.blog-quick-valuation-form>` en el mismo post (son mutuamente excluyentes,
  guardado con test — `BlogLeadOptimizationsTest`).
- Cualquier clase de Tailwind nueva (sobre todo combinaciones responsivas como `sm:block`) necesita
  `npm run build` antes de deploy — verificado con test (`test_compiled_css_includes_the_class...`).

## Cómo probarlo
`php artisan test --filter=BlogLeadOptimizationsTest` (Pixel de Meta, exclusión mutua
calculadora/form genérico, CSS compilado, activación en hermano-no-quiere-vender, recordatorios) +
`BlogSuccessionCalculatorTest` y `BlogCtaClustersTest` actualizados al nuevo flujo.
