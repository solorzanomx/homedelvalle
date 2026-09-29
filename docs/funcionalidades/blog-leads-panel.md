# Blog: panel "Blog → Leads"

> 2026-09-30. Fase 7 (última) de `prompt-claude-code-blog-leads.md` (Alejandro). Léelo antes de tocar
> `BlogLeadsController`, `BlogNotFoundHit`, o `/admin/blog-leads`.

## Qué resuelve
Un panel dedicado en `/admin/blog-leads` (nav "Contenido → Blog → Leads") con lo que pedía el
prompt: leads por post, por cluster y por variante de CTA; conversión por post (cuando hay vistas);
hits de redirects; y los 404 más frecuentes — este último dato **no existía antes**: un slug de
`/blog/*` sin match ni siquiera difuso simplemente renderizaba el 404 sin dejar rastro.

Complementa a `/admin/atribucion` (general, todo el sitio) — no lo reemplaza. Reusa el mismo criterio
de "conversión aprox." (leads del rango ÷ vistas totales históricas, con la misma advertencia de que
no es una tasa periodo-a-periodo estrictamente comparable).

## Cómo funciona
- **Leads por post**: `FormSubmission` con `landing_post_id` no nulo (atribución de sesión, ya
  existente) en el rango de días elegido (7/30/90).
- **Leads por cluster / por variante de CTA**: lee `payload.cluster` y `payload.cta_variant` cuando
  existen (los CTAs por cluster de la Fase 3/4 ya los guardan ahí); si no, deriva el cluster con
  `BlogCluster::forPost()` sobre el post de atribución. Solo los leads de un CTA por cluster traen
  variante — los leads "genéricos" (form de valuación a media lectura, etc.) no.
- **Hits de redirects**: `BlogRedirect` ordenado por `hits`, marca los inactivos.
- **404 más frecuentes**: tabla nueva `blog_not_found_hits` — se registra un hit cada vez que
  `BlogController::notFoundOrFuzzyRedirect()` NO encuentra ni un candidato por similitud (si sí
  encuentra, se resuelve solo con un redirect automático, como ya hacía desde la Fase 1, y **no**
  cuenta como 404).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Agregación | `app/Http/Controllers/Admin/BlogLeadsController.php` |
| Vista | `resources/views/admin/blog-leads/index.blade.php` |
| Registro de 404 sin match | `App\Models\BlogNotFoundHit::register()`, llamado desde `BlogController::notFoundOrFuzzyRedirect()` |
| Tabla | `blog_not_found_hits` (migración `2026_09_30_160000`) |

## INVARIANTES — no romper
- Un slug que SÍ encuentra match difuso (Fase 1) nunca debe registrarse también como 404 — son
  eventos mutuamente excluyentes (`BlogNotFoundHit::register()` solo se llama en la rama sin match).
- "Conversión aprox." usa vistas TOTALES históricas del post, no vistas del mismo rango que los
  leads — mismo trade-off ya documentado en `/admin/atribucion`, no es un bug.
- El render completo del panel (dentro de `layouts.app-sidebar`, que consulta muchas tablas del CRM)
  es demasiado pesado para SQLite en memoria — el test prueba la lógica de agregación del
  controlador directamente; el render con datos reales se verificó a mano contra la BD local.

## Cómo probarlo
`php artisan test --filter=BlogLeadsPanelTest`.

## Cierre del prompt de leads del blog
Con esta fase quedan completadas las 7 fases de `prompt-claude-code-blog-leads.md`. Ver también:
`blog-redirects.md` (F1), `blog-ga4-tracking.md` (F2), `blog-cta-clusters.md` (F3),
`blog-calculadora-sucesion.md` (F4), `blog-contenido-fase5.md` (F5), `blog-fusion-fase6.md` (F6).

**Pendientes que quedaron fuera de alcance de este prompt** (documentados donde corresponde, no se
inventó nada para cerrarlos):
- Webhook saliente a n8n/Chatwoot (decisión de Alejandro: no por ahora).
- Confirmar con notario los 8 parámetros de la calculadora de sucesión.
- Revisar/editar el copy de los 5 clusters en `/admin/blog-ctas`.
- Corregir la explicación de ISR incorrecta en el pilar en vivo de "vender propiedad heredada" (F6).
- Registrar en GA4 las dimensiones `post_slug`/`post_cluster` (y `cta_variant` cuando haya datos).
- Revisar y activar las 6 fusiones en borrador cuando Alejandro las apruebe.
