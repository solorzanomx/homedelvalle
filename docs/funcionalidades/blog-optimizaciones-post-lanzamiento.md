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

## 7. Auditoría de conversión 2026-09-28 — hallazgo grave y 3 arreglos

Con las 7 fases + la ronda de optimizaciones ya en vivo, medí en serio: **19,236 vistas acumuladas
del blog → solo 2 leads en 90 días** (0.01%). Verificado contra la BD real de producción (no
supuestos) y probado en vivo con el navegador. Causa principal encontrada:

**Bug: el `cta-capture` final se apagaba en el 58% del tráfico del blog.** `blog/show.blade.php`
suprimía el formulario final (`<livewire:blog.cta-capture location="final">`) cada vez que el
cuerpo del post ya terminaba con un bloque de clase `not-prose my-10` — pensado para evitar dos
tarjetas de CTA encimadas (un bug real de antes). El problema: **50 de 59 posts publicados todavía
traen un `{{CTA2}}` heredado** de antes de la Fase 3, y en **9 de ellos cae justo al final** —
incluidos los **2 posts con más tráfico de todo el blog** (`isr-venta-propiedad-heredada-mexico-2026`,
4,242 vistas, y `precio-metro-cuadrado-colonias-benito-juarez-2026`, 3,495 vistas). En esos 9 posts
lo único que quedaba al final era el `{{CTA2}}` viejo — un **link estático, no un formulario** — así
que quien llegaba al final del artículo no tenía ningún formulario real de cierre. Confirmado en
vivo navegando a `isr-venta-propiedad-heredada-mexico-2026`: el `cta-capture` nunca se montaba
(0 componentes Livewire de ese tipo en la página).

**Arreglo 1 — el `cta-capture` ahora se muestra siempre que el post tenga cluster**, sin importar
cómo termine el cuerpo (`resources/views/blog/show.blade.php`). `$bodyEndsWithCta` sigue protegiendo
solo al CTA genérico de respaldo (el de posts sin cluster) — ahí sí seguía siendo una tarjeta
duplicada real.

**Arreglo 2 — calculadora activada en `isr-venta-propiedad-heredada-mexico-2026`** (el post #1 de
tráfico de todo el blog). Los 2 únicos leads reales de 90 días vinieron del otro post de herencias
que sí tiene calculadora — es la única señal dura de qué convierte, y este post responde justo la
pregunta que la calculadora resuelve. Migración
`2026_09_30_180000_add_calculator_to_isr_herencia_post`.

**Arreglo 3 — calculadora marcada como validada.** Alejandro confirmó las cifras de los 2 escenarios
(con/sin testamento) con notario — ya no son placeholder. `validated=true` en
`succession_calculator_configs` quita el aviso "⚠ Estimación de referencia, todavía no confirmada"
que veía cada persona justo después de dejar su WhatsApp. Migración
`2026_09_30_180001_validate_succession_calculator_configs`. Las cifras en sí no se tocaron (son
admin-editables en `/admin/succession-calculator`).

**Pendiente de esta auditoría, fuera de alcance de código** (ver conversación, no bloquea nada):
Pixel de Meta configurado pero apagado (`fb_pixel_enabled=0`, el Pixel ID sí está cargado) — decisión
de Alejandro, no bug; revisar el copy de los 5 CTA por cluster en `/admin/blog-ctas`; registrar
dimensiones GA4 personalizadas (acción en el admin de GA4, no en este repo).

## 8. Auditoría 2026-09-28, parte 2 — el post fusionado tenía 6 CTAs apilados

Alejandro pidió analizar en vivo `como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026`
(uno de los 6 pilares fusionados) y preguntó por qué tenía tantos CTAs. Conteo real en la página:
**6 bloques de conversión** en un solo artículo. Causa: 4 sistemas construidos en fases distintas
(Fase 2 `ctaMap`/CTA por categoría, el sistema previo `{{CTA1}}/{{CTA2}}/{{CTA3}}` — más viejo
todavía, nunca documentado en las fases de este prompt —, Fase 3 cluster CTA, Fase 4 calculadora,
Fase 5 predio→desarrolladora) sin que ninguna sumara el total acumulado por post. El culpable
principal: **`{{CTA1}}`/`{{CTA2}}`/`{{CTA3}}`**, presentes en 58/50/41 de los 59 posts publicados —
`BlogAIService` seguía pidiéndole a la IA que los insertara en CADA post nuevo. Se resuelven desde
`posts.ctas` (columna JSON), no desde el texto del body — `Post::getRenderedBodyAttribute()` los
reemplaza por cadena vacía si esa entrada no tiene `title`.

**Arreglo 4 — vaciar `ctas` en todos los posts publicados** (migración
`2026_09_30_190000_clear_legacy_cta_shortcodes_from_posts`): apaga los 3 shortcodes de golpe, sin
tocar `body` (el `{{CTAn}}` literal se queda en el texto pero ya no renderiza nada). No es
reversible con datos — ese contenido era placeholder redundante, no vale la pena respaldarlo.

**Arreglo 5 — `BlogAIService` ya no le pide a la IA insertar `{{CTA1}}/{{CTA2}}/{{CTA3}}`** ni un
campo `ctas` en el JSON de generación — si no se corrige el prompt, cada post nuevo publicado vuelve
a acumular el mismo ruido. Detalle completo y el inventario final de CTAs por post en
`docs/funcionalidades/blog-cta-clusters.md`.

Después de los 2 arreglos, el post analizado queda en 4 CTAs (inline, a media lectura, predio,
final) — cada uno con un propósito distinto, sin duplicados.

## INVARIANTES — no romper
- `SuccessionCalculator::calculate()` es la única acción del componente — no reintroducir un
  segundo paso de captura separado del cálculo.
- El correo sigue siendo opcional en los dos formularios — nunca `required`.
- `blog/show.blade.php` nunca debe mostrar `<livewire:blog.succession-calculator>` Y
  `<livewire:forms.blog-quick-valuation-form>` en el mismo post (son mutuamente excluyentes,
  guardado con test — `BlogLeadOptimizationsTest`).
- Cualquier clase de Tailwind nueva (sobre todo combinaciones responsivas como `sm:block`) necesita
  `npm run build` antes de deploy — verificado con test (`test_compiled_css_includes_the_class...`).
- **El `<livewire:blog.cta-capture location="final">` se muestra siempre que el post tenga cluster
  (`@if($cluster)`), nunca condicionado a `$bodyEndsWithCta`.** Ese flag solo protege al CTA genérico
  de respaldo (posts sin cluster). Si se reintroduce la condición vieja, se vuelve a apagar el único
  formulario de cierre real en cualquier post cuyo `{{CTA2}}` heredado caiga al final del cuerpo —
  guardado con test (`test_final_cta_capture_never_depends_on_body_ending_in_legacy_cta`).
- **`{{CTA1}}/{{CTA2}}/{{CTA3}}` quedan desactivados a propósito** (`posts.ctas` vacío en todos los
  posts publicados) y `BlogAIService` ya no los pide en posts nuevos — no reactivar ninguno de los
  dos sin revisar primero cuántos CTAs ya tiene un post con cluster (ver
  `docs/funcionalidades/blog-cta-clusters.md`). Guardado con test
  (`test_legacy_cta_shortcodes_get_cleared_from_all_published_posts`,
  `test_ai_blog_generator_no_longer_requests_legacy_cta_shortcodes`).

## Cómo probarlo
`php artisan test --filter=BlogLeadOptimizationsTest` (Pixel de Meta, exclusión mutua
calculadora/form genérico, CSS compilado, activación en hermano-no-quiere-vender y en isr-venta,
calculadora validada, cta-capture final nunca condicionado al cuerpo, CTAs legacy desactivados,
recordatorios) + `BlogSuccessionCalculatorTest` y `BlogCtaClustersTest` actualizados al nuevo flujo.
