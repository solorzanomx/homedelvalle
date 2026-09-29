# Blog: salud de URLs (redirects 301/410 + fallback difuso)

> 2026-09-28. Fase 1 de `prompt-claude-code-blog-leads.md` (Alejandro). Léelo antes de tocar
> `BlogUrlHealth`, `BlogController@show`, `PostObserver`, o `blog_redirects`.

## Qué resuelve
Google tenía 18 URLs de `/blog/*` indexadas que no existen (slugs viejos, dedazos) — antes daban 404
seco. Ahora: redirect explícito (admin o seed) → normalización de mayúsculas/barra final → si nada de
eso aplica, un slug que se parece mucho a uno real se resuelve solo; si no se parece a nada, 404 útil
con artículos relacionados en vez de un callejón sin salida.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Tabla | `blog_redirects` (`from_path` único normalizado, `to_path`, `status` 301/410, `active`, `hits`, `last_hit_at`, `notes`) — migración `2026_09_28_100000` |
| Modelo | `App\Models\BlogRedirect` (`normalize()`, `registerHit()`, scope `active`) |
| Middleware | `App\Http\Middleware\BlogUrlHealth` — **global** (`bootstrap/app.php`, `$middleware->append()`, no en `web()`), corre para CUALQUIER request a `/blog/*` tenga o no ruta (una URL con barra final nunca hace match con `blog/{slug}`, así que sin ruta resuelta el grupo `web` tampoco correría) |
| Fallback difuso | `BlogController::notFoundOrFuzzyRedirect()` — Levenshtein normalizado (`1 - distancia/longitud`) contra `Post::published()->pluck('slug')`, umbral `FUZZY_THRESHOLD = 0.85`; si pasa, crea el redirect como "auto" y ya no se recalcula la próxima vez |
| Vistas de fallback | `blog/not-found.blade.php` (404, con 4 relacionados + CTA), `blog/gone.blade.php` (410) |
| Redirect automático por cambio de slug | `PostObserver::updated()` (`wasChanged('slug')`) |
| Admin | `Admin\BlogRedirectController` + `admin/blog-redirects/index.blade.php` (`/admin/blog-redirects`, dentro de `['auth','viewer']`), nav "Contenido → Redirects" |
| Seed inicial (17 filas del prompt) | migración `2026_09_28_100001_seed_blog_redirects` — **verifica que el destino exista antes de insertar** (si no, no lo crea; así producción y local pueden divergir sin dejar basura) |
| Sitemap | `SitemapController` ahora usa `Post::published()` (antes duplicaba el filtro a mano — mismo criterio que decide si un post existe públicamente) |

## INVARIANTES — no romper
- **El middleware va en el stack GLOBAL, no en `web()`.** Si algún día necesita sesión, hay que mover
  solo esa parte siguiendo el gotcha ya conocido (`$middleware->web(append:...)`, ver
  [[project_homedelvalle_attribution]]).
- **Un redirect `active=false` no se aplica** (Fase 6 del prompt: fusiones dejan redirects preparados
  pero inactivos, a revisar antes de activarlos).
- **El fallback difuso solo compara contra `Post::published()`** — nunca ofrece un borrador o algo
  agendado como destino.
- **El umbral 0.85 es a propósito conservador**: dos artículos de temas distintos con slugs parecidos
  (p. ej. dos "vivir-en-X-pros-contras-2026") no deben confundirse. Si el negocio pide bajarlo, medir
  con los slugs reales de producción antes de tocar la constante.
- Local no tiene el catálogo real de posts (ver `docs/funcionalidades/*blog*`, memoria del proyecto):
  la migración de seed es defensiva porque el destino puede no existir aquí.

## Cómo probarlo
`php artisan test --filter=BlogRedirectsTest` (no usa `RefreshDatabase`: migra solo lo que este
módulo toca sobre SQLite en memoria — mismo patrón que `PolizaPricingTest`, porque una migración
antigua ajena con sintaxis MySQL-only rompe un `migrate` completo ahí).

## Pendiente / fuera de alcance de esta fase
- Fase 1.6 del prompt (enlaces internos rotos hacia estas variantes): revisado en código — no hay
  ninguno. El contenido de los posts (BD) vive solo en producción; revisar ahí queda para cuando se
  entre a Fase 5/6 por SSH.
- No hay UI para editar `from_path`/`to_path` de un redirect ya creado más que crear uno nuevo con el
  mismo `from_path` (el `update()` del controlador existe pero la vista no trae botón de editar todavía
  — bajo uso esperado, se agrega si hace falta).
