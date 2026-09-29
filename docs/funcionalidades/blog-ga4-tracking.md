# Blog: eventos GA4 con contexto (post_slug, post_cluster, cta_variant)

> 2026-09-28. Fase 2 de `prompt-claude-code-blog-leads.md` (Alejandro). Léelo antes de tocar
> `hdvTrack`, `BlogCluster`, o el bloque de tracking de `layouts/public.blade.php`.

## Qué resuelve
Ya existía `window.hdvTrack` (whatsapp_click, generate_lead, form_start/form_step) pero sin saber
**de qué post** venía el clic ni **en qué etapa del funnel** está el lector. Sin eso no se puede medir
qué artículos de herencias realmente convierten en lead, solo que "el blog" convierte.

## Cómo funciona
1. `blog/show.blade.php` inyecta `window.hdvBlogContext = { post_slug, post_cluster }` en el `<head>`
   de CADA post (no en el resto del sitio).
2. `hdvTrack(name, params)` (en `layouts/public.blade.php`) mezcla ese contexto en TODO evento que
   dispare, sin que cada CTA tenga que repetirlo: `Object.assign({}, window.hdvBlogContext, params)`
   — los `params` explícitos del call site ganan si hay choque.
3. `cta_variant` se lee de `data-cta-variant` en el link clicado (mismo patrón que ya existía para
   `data-track-location` → `cta_location`). Hoy ningún CTA lo trae todavía — lo asigna la Fase 3
   (`<x-blog.cta>` por cluster). El plumbing ya está listo para que Fase 3 no toque `hdvTrack`.
4. **`cta_view`**: `IntersectionObserver` sobre todo `[data-track-location]` (una vez por elemento,
   umbral 50% visible). Cubre YA los CTAs existentes (`cta_auto_final`, `cta_valuacion`, `cta_predio`)
   sin esperar a la Fase 3.
5. `whatsapp_click`/`whatsapp_share`/`phone_click`/`email_click`/`cta_click` no cambiaron de lógica —
   solo ahora heredan `post_slug`/`post_cluster` cuando ocurren dentro de un post.

## `post_cluster`: de dónde sale
`App\Support\BlogCluster::forPost(?Post $post): ?string` — **no es una columna nueva**, se deriva de
`category` (que ya existe y ya enruta el CTA automático) + un ajuste por slug para herencias (mismo
heurístico que antes vivía duplicado a mano en `blog/_cta-predio.blade.php`, ahora es la única fuente).
Clusters: `herencias`, `terreno_desarrolladora`, `precios_inversion`, `guias_colonia`, `proceso_venta`.
Si el negocio pide poder fijar el cluster por post sin depender de la categoría, ahí sí se vuelve
columna editable (decisión para cuando se entre a Fase 3 de verdad).

## Leads: UTM/referrer/landing/post_slug
Ya existía (`HasAttribution`, ver [[project_homedelvalle_attribution]]): `FormSubmission` guarda
`utm_*`, `referrer`, `landing_post_id`, `landing_label` del primer touch de la sesión. Se agregaron
dos accessors de conveniencia para el panel de la Fase 7: `FormSubmission::post_slug` y `::post_cluster`
(leen de `landingPost`, sin tocar el schema).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Cluster de un post | `app/Support/BlogCluster.php` |
| Contexto por post | `resources/views/blog/show.blade.php` (`window.hdvBlogContext`) |
| Merge + cta_variant + cta_view | `resources/views/layouts/public.blade.php` (bloque de tracking, dentro del `@if(ga_enabled\|\|gtm_enabled)`) |
| Accessors de lead | `app/Models/FormSubmission.php` (`post_slug`, `post_cluster`) |

## INVARIANTES — no romper
- El bloque de tracking entero sigue condicionado a `ga_enabled`/`gtm_enabled` — en local (ambos en
  false por defecto) el script ni se imprime; probado forzando ambos flags en una transacción.
- `hdvBlogContext` **solo existe en `/blog/{slug}`** — en cualquier otra página `window.hdvBlogContext`
  es `undefined` y `hdvTrack` simplemente no agrega nada (`if (window.hdvBlogContext)`).
- `BlogCluster::forPost()` nunca lanza si `$post` o `$post->category` son null — devuelve `null`.
- **`calculator_start`/`calculator_complete` NO están wireados todavía** — no existe la calculadora
  (Fase 4). Documentado aquí para que Fase 4 solo tenga que llamar `hdvTrack('calculator_start', {...})`
  / `hdvTrack('calculator_complete', {...})` desde el componente Livewire nuevo; el contexto de post ya
  se mezcla solo.

## Cómo probarlo
`php artisan test --filter=BlogGa4TrackingTest`. El render completo de `blog/show` (con
`layouts.public`, footer, menús, colonias…) es demasiado pesado para SQLite en memoria — se verificó
a mano contra la BD local real vía tinker (con `ga_enabled`/`gtm_enabled` forzados a true en una
transacción con rollback) y los tests cubren la lógica de `BlogCluster` + la fuente exacta que expone
el contexto.

## Pendiente / para ti (Alejandro)
Antes de que estos eventos sirvan de algo en reportes, **crea en GA4 (Admin → Definiciones personalizadas
→ Dimensiones) las 2 dimensiones que faltan**: `post_slug` y `post_cluster` (ámbito Evento) — la API no
permite crearlas, es el mismo procedimiento manual que ya hiciste para `form_id`/`form_type`/`step`/`cta_location`.
`cta_variant` lo dejo pendiente de crear hasta la Fase 3 (cuando por fin haya CTAs con variantes reales
que probar) para no registrar una dimensión que todavía no recibe datos útiles.

Sobre marcar eventos como clave: **`cta_view` es de volumen, no de conversión — no lo marques como
clave** (ensuciaría el reporte de conversiones con "vio un botón"). Cuando lleguen la Fase 3/4, te doy
la lista completa a estrellar.
