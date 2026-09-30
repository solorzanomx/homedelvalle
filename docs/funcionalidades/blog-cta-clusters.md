# Blog: CTAs por cluster (formulario corto + WhatsApp)

> 2026-09-28. Fase 3 de `prompt-claude-code-blog-leads.md` (Alejandro). Léelo antes de tocar
> `BlogCluster`, `BlogCtaConfig`, `CtaCapture`, o los `blog/_cta-*.blade.php`.

## Decisión de diseño (confirmada por Alejandro)
El post ya traía 4 módulos de conversión (CTA de valuación tras la tabla, form de valuación a media
lectura, banner predio, CTA final automático por categoría). El nuevo CTA por cluster **reemplaza al
CTA final automático** cuando el post tiene cluster — los otros 3 módulos siguen igual, el post no se
satura más de lo que ya estaba.

## Qué se construyó
- **`posts.cluster`** (nullable): el asesor puede fijarlo a mano desde el editor del post; si está
  vacío, `BlogCluster::forPost()` lo sigue derivando de categoría+slug (heurístico de la Fase 2).
- **`blog_cta_configs`**: una fila fija por cluster (5, no se crean ni se borran), editable en
  `/admin/blog-ctas` — título, texto, botón, mensaje de WhatsApp, y el tipo de lead (`form_type`) que
  genera. Herencias trae además una variante "ya decidió vender".
- **3 posiciones**, todas con `data-track-location`/`data-cta-variant` (Fase 2 las recoge solas):
  - **Inline** (`blog/_cta-cluster-inline.blade.php`): tras la primera sección h2 completa
    (`BlogBodyEnhancer::injectAfterFirstHeadingSection`). Es un link de WhatsApp, no un formulario.
  - **Final** (`<livewire:blog.cta-capture location="final">`): el formulario de verdad — WhatsApp
    obligatorio, nombre opcional, sin email. Al enviar, la respuesta es en pantalla (no "en 24h") con
    un botón para seguir la conversación por WhatsApp.
  - **Sticky móvil** (`blog/_cta-sticky-whatsapp.blade.php`): link de WhatsApp, solo en `sm:hidden`
    (móvil). El botón flotante genérico del sitio (`<x-public.whatsapp-float>`) se oculta EN MÓVIL
    en estos posts para no apilar dos flotantes — sigue igual en desktop y en el resto del sitio.
- **Solo un formulario Livewire por post** (en "final"): Livewire no puede montarse dentro de HTML
  crudo de BD (mismo motivo por el que ya existe el split de `blog-quick-valuation-form`), así que el
  inline es deliberadamente un link, no un segundo form — dos forms independientes en la misma página
  no sumaba nada y sí sumaba riesgo/mantenimiento.
- **`isr-venta-propiedad-heredada-mexico-2026`, `hermano-no-quiere-vender-...`, `vender-*`**: en el
  cluster herencias, estos slugs activan la variante "ya decidió vender" (`BlogCluster::showsSellCta`).
- **`App\Support\BlogWhatsapp::urlFor()`**: arma el link de WhatsApp con el número del sitio, el
  mensaje del cluster (`{titulo}` → título del post) y `(ref: p{id})` al final — así Alejandro sabe de
  qué artículo viene la conversación con solo leerla, sin depender de n8n/Chatwoot (no hay integración
  hoy, ver decisión de Fase 0).

## Leads sin email
Ningún formulario del sitio pedía menos que nombre+email — este es el primero. Se tocó lo mínimo para
soportarlo sin romper nada:
- `form_submissions.email` ahora es nullable (migración `2026_09_29_150002`).
- `full_name` sigue NOT NULL: sin nombre se guarda `"Lead del blog"` (no se tocó esa columna).
- `SendAcuseMail` no manda nada si no hay email (antes intentaba `Mail::to(null)`).
- `FormDataMapper::toLeadInternoData()` resuelve `email: $submission->email ?? ''` — el correo interno
  al equipo sigue llegando, el link "Responder" del email queda sin destino si no hay correo (cosmético,
  el teléfono sí se ve).
- **No se llama `AutomationEngine::processFormSubmitted()`**: esa función exige email y retorna null
  sin hacer nada si no lo hay — sería una llamada muerta. `FormSubmission::create()` ya dispara
  `FormSubmitted` (avisa al equipo, notifica admins) sin necesitarlo.
- `lead_tag = 'LEAD_BLOG'`, `client_type = 'owner'` solo si el `form_type` es vendedor/vendedor_predio.

## ⚠️ Actualización 2026-09-30 — colonia obligatoria + filtro de teléfonos falsos
Alejandro reportó leads con teléfono inventado (`1111111111`) y sin forma de saber si eran
prospectos reales de Benito Juárez. Dos arreglos, en `CtaCapture` y `SuccessionCalculator`:
- **Colonia obligatoria**, con el catálogo real de `MarketZone`/`MarketColonia` (el mismo que ya usa
  el sistema de precios/valuación — no se inventó una lista aparte). Ver
  `App\Support\BenitoJuarezColonias`. La última opción del select es
  **"Otra colonia (fuera de Benito Juárez)"** — un catch-all explícito para quien no es de la zona,
  en vez de dejarlo adivinar o forzarlo a mentir. Se guarda en `payload.colonia`, visible directo en
  la ficha del lead (el panel ya renderiza cualquier key del payload).
- **`App\Rules\RealisticMexicanPhone`**: rechaza teléfonos con el mismo dígito repetido
  (`1111111111`) o secuencias consecutivas en cualquier rotación (`1234567890`, `0123456789`,
  `9876543210`…). No verifica que el número sea real (eso requeriría SMS) — solo descarta los casos
  evidentes de alguien tecleando cualquier cosa.
- El correo sigue opcional, pero el copy cambió para sonar a beneficio real en vez de solo "otro
  campo": "¿Prefieres tener esto también por correo, por si no ves el WhatsApp?" (CtaCapture) /
  "¿Quieres tu estimado también por correo?" (calculadora).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Cluster + variante "decidió vender" | `app/Support/BlogCluster.php` |
| Copy por cluster, editable | `app/Models/BlogCtaConfig.php`, `Admin\BlogCtaConfigController`, `/admin/blog-ctas` |
| Link de WhatsApp con ref | `app/Support/BlogWhatsapp.php` |
| Formulario corto (final) | `app/Livewire/Blog/CtaCapture.php` + `livewire/blog/cta-capture.blade.php` |
| Posicionamiento inline | `BlogBodyEnhancer::injectAfterFirstHeadingSection()`, `blog/_cta-cluster-inline.blade.php` |
| Sticky + supresión del flotante genérico en móvil | `blog/_cta-sticky-whatsapp.blade.php`, `components/public/whatsapp-float.blade.php` (`hideOnMobile`), `blog/show.blade.php` (`@section('hideWhatsappFloatMobile', ...)`) |
| Selector de cluster manual | `resources/views/admin/posts/{create,edit}.blade.php` |

## ⚠️ Actualización 2026-09-28 — el post ya traía MÁS de 4 módulos, y uno estaba duplicado

La cuenta de "4 módulos de conversión" de arriba (de cuando se escribió esta fase) no incluía un
quinto sistema, más viejo todavía: **`{{CTA1}}`/`{{CTA2}}`/`{{CTA3}}`**, tarjetas estáticas
(`blog/_cta.blade.php`, sin captura real — solo un link) que `BlogAIService` seguía insertando en
**cada post nuevo** desde antes de que existiera esta fase, resueltas por
`Post::getRenderedBodyAttribute()` desde la columna `posts.ctas` (no desde el texto de `body`).
Con eso sumado, un post con cluster + calculadora + fusión de varios posts podía llegar a **5-6
CTAs apilados** — hallazgo real en `como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026`
(auditoría de conversión, ver `docs/funcionalidades/blog-optimizaciones-post-lanzamiento.md`, sección 7).

Arreglado: `ctas` se vació en todos los posts publicados (migración
`2026_09_30_190000_clear_legacy_cta_shortcodes_from_posts`) y `BlogAIService` ya no le pide a la IA
que use `{{CTA1}}/{{CTA2}}/{{CTA3}}` en posts nuevos. El inventario real de módulos de conversión
por post (post con cluster + herencias) quedó en 4: inline (link), a media lectura
(form/calculadora), predio→desarrolladora (solo herencias), final (form). Sin cluster: 2 (a media
lectura + el `ctaMap` automático).

## INVARIANTES — no romper
- El CTA final por cluster **reemplaza** al `ctaMap` automático (decisión confirmada) — no sumar un
  tercero sin revisar con Alejandro; un post con cluster ya tiene 4 módulos de conversión (ver
  arriba) — cualquier sistema nuevo de CTA (incluido cualquier variante futura de `{{CTAn}}`) debe
  revisar primero cuántos ya hay, no sumar a ciegas.
- `{{CTA1}}/{{CTA2}}/{{CTA3}}` (el sistema pre-Fase-3) queda desactivado a propósito — no volver a
  pedirle a `BlogAIService` que los use, ni reactivar `posts.ctas` en masa. Si un post puntual
  necesita un CTA editorial extra, se edita a mano en `/admin/posts/{id}/edit` (el campo sigue
  funcionando, solo se vació el contenido viejo).
- `CtaCapture` es el único formulario Livewire de captura en la página — si se agrega otro, revisar
  que no se dupliquen honeypot/spam/legal en la misma vista sin necesidad.
- **`colonia` es obligatoria en `CtaCapture` y `SuccessionCalculator`** — solo acepta valores del
  catálogo real (`BenitoJuarezColonias::validValues()`) o el catch-all "fuera de BJ", nunca texto
  libre (evita que alguien invente una colonia igual que antes inventaba el teléfono).
- El botón flotante genérico solo se oculta **en móvil** y solo en posts **con cluster** — en desktop
  y en el resto del sitio sigue exactamente igual.
- `BlogCluster::showsSellCta()` solo aplica al cluster herencias — en el resto de clusters siempre
  usa el copy general (no tienen variante "decidió vender").
- Render completo de `blog/show` demasiado pesado para SQLite en memoria (mismo criterio que Fase 1/2);
  `BlogCtaClustersTest` prueba `BlogCluster`, `BlogCtaConfig` y `CtaCapture` de forma aislada. Verificado
  a mano contra la BD local (ver historial de la sesión) para el render completo (inline, final, sticky,
  supresión del flotante).

## Pendiente / para ti (Alejandro)
1. Revisa/edita el copy de los 5 clusters en `/admin/blog-ctas` — el de herencias es casi textual del
   prompt, los otros 4 reusan el tono del `ctaMap` anterior pero no los validé contigo palabra por palabra.
2. GA4: registra `cta_variant` como dimensión cuando quieras medir por cluster (pendiente desde la
   Fase 2 a propósito, ya hay datos reales que mandarle).
3. Fase 4 (calculadora) puede montarse dentro del CTA final o aparte — se decide cuando llegue esa fase.
