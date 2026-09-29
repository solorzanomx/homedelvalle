# Blog: contenido — respuesta corta, titles/metas, FAQPage, enlazado interno

> 2026-09-30. Fase 5 de `prompt-claude-code-blog-leads.md` (Alejandro). Trabajado por SSH contra la
> BD real de producción (local no tiene el catálogo de posts) — todos los cambios verificados leyendo
> el contenido real antes de tocarlo, con respaldo de los valores anteriores en cada migración.

## 5.1 — "Respuesta corta" arriba del primer scroll
En vez de una migración que escribe una tabla estática en el `body` (que se desalinearía de la
calculadora si Alejandro edita los parámetros después), se construyó **dinámico**:
- `cuanto-cuesta-sucesion-cdmx-2026`: `blog/_succession-cost-summary.blade.php` — tabla con
  testamento vs sin testamento, leyendo `SuccessionCalculatorConfig` EN VIVO (mismo dato que la
  calculadora unos párrafos más abajo). Aviso "⚠ no confirmado con un notario" mientras
  `validated=false` en cualquiera de los 2 escenarios — desaparece solo cuando Alejandro lo confirme.
- `propiedad-sin-testamento-cdmx-como-regularizar-vender-2026`: `blog/_succession-steps-summary.blade.php`
  — 4 pasos (resumen de lo que el propio post ya explica más abajo, no contenido nuevo) + costo/tiempo
  aproximado del escenario "sin testamento". **El title/H1 de este post NO se tocó** (11.5% de CTR,
  instrucción explícita del prompt).
- Posición: `BlogBodyEnhancer::injectBeforeFirstHeading()` (nueva) — antes del primer `<h2>`, aplica
  solo a estos 2 slugs (`blog/show.blade.php`, `match($post->slug)`).

## 5.2 — Titles y meta descriptions
Migración `2026_09_30_150000_update_blog_meta_ctr_fase5` (con el valor anterior de cada campo
guardado para `down()`). Se revisó CADA title actual antes de tocarlo — 3 de los 9 posts del prompt
**ya estaban bien y no se tocaron** (`uso-de-suelo-h4-benito-juarez`, `vender-propiedad-heredada-sin-escrituras-benito-juarez`,
`vivir-en-del-valle-sur-cdmx-diferencias-centro-norte`: ya en rango, con beneficio concreto).
`precio-metro-cuadrado-colonias-benito-juarez-2026` (que el prompt también mencionaba) **no se tocó**:
ya se había corregido en julio (`2026_07_16_100000_update_blog_meta_for_ctr`) y coincide casi textual
con lo que pedía este prompt.

Se corrigieron 6:
- `usufructo-vitalicio-que-pasa-al-morir-benito-juarez`: el meta_title estaba **roto** (truncado a
  media palabra: `"...Home del Vall..."`) — bug real encontrado al leer la BD, no solo una mejora de CTR.
- `vivir-en-napoles-cdmx-precios-pros-contras-2026`: la meta_description también estaba truncada a
  media palabra (`"...propietarios e inversionista..."`) — mismo tipo de bug, corregido.
- `propiedades-h5-y-h6-en-benito-juarez-...`: title de 72 caracteres (pasaba el límite) y
  descripción de 181 — ambos recortados sin inventar cifras nuevas.
- `vivir-en-narvarte-precios-pros-contras-2026`: nuevo title menciona "Oriente vs Poniente" —
  **verificado que el post sí compara esas dos zonas** antes de usarlo (no se inventó el ángulo).
- `cuanto-cuesta-sucesion-cdmx-2026`: title y descripción actualizados para mencionar la tabla,
  ahora que existe (Fase 5.1).

Todos los meta_title nuevos ≤65 caracteres, meta_description nuevas entre 140–155 (verificado con
test, `BlogContentFase5Test::test_meta_ctr_migration_never_exceeds_the_recommended_lengths`).

## 5.3 — Datos estructurados
**`Article` y `BreadcrumbList` ya existían en TODOS los posts** (`blog/show.blade.php`, sin
condición) — nada que hacer ahí. `FAQPage` también existía como mecanismo (`$post->faq_schema`) y
`isr-venta-propiedad-heredada-mexico-2026` ya lo tenía lleno (4 preguntas) desde antes. Se llenó en
los otros 3 posts de herencias que estaban en `null` (`2026_09_30_150002_add_faq_schema_herencias_fase5`)
— **todas las preguntas y respuestas se tomaron literalmente del texto ya publicado**, ninguna se
inventó (ver el migration file: cada respuesta es una paráfrasis mínima de una oración que ya estaba
en el post).

## 5.4 — Enlazado interno
`cuanto-cuesta-sucesion-cdmx-2026` **ya enlazaba a los otros 3** (verificado leyendo el body real) y
ya aloja la calculadora — no se tocó. Se agregó un párrafo nuevo (nunca se reescribió contenido
existente) en los otros 3, enlazando a los 3 restantes + mención a la calculadora
(`2026_09_30_150001_add_internal_links_herencias_fase5`). La migración es defensiva: **si el párrafo
ancla ya no coincide exactamente con el body actual (contenido editado desde que se escribió esta
migración), no inserta nada** — mejor omitir que insertar en el lugar equivocado. Verificado con
lectura directa de la BD de producción (por SSH, sin escribir nada) que los 3 anclas coinciden byte a
byte antes de escribir las migraciones.

## Respaldo del contenido anterior
- Meta anteriores: dentro de la propia migración (`down()` los restaura) — mismo patrón que
  `2026_07_16_100000_update_blog_meta_for_ctr`.
- Enlaces internos: la migración es su propio respaldo (`down()` quita exactamente el párrafo que
  agregó `up()`, con el mismo `str_replace` en reversa).
- FAQ: `down()` vuelve `faq_schema` a `null` en los 3 posts que se llenaron.
- Cuerpos completos ANTES de esta fase: quedaron guardados en el historial de esta sesión (no en el
  repo — son datos de producción, no algo que deba vivir en un repo público).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Posición "respuesta corta" | `BlogBodyEnhancer::injectBeforeFirstHeading()` |
| Contenido dinámico | `blog/_succession-cost-summary.blade.php`, `blog/_succession-steps-summary.blade.php` |
| Wiring | `blog/show.blade.php` (`$respuestaCortaHtml`, `match($post->slug)`) |
| Meta/CTR | migración `2026_09_30_150000_update_blog_meta_ctr_fase5` |
| Enlaces internos | migración `2026_09_30_150001_add_internal_links_herencias_fase5` |
| FAQPage | migración `2026_09_30_150002_add_faq_schema_herencias_fase5` |

## INVARIANTES — no romper
- El title/H1 de `propiedad-sin-testamento-cdmx-como-regularizar-vender-2026` no se toca sin que
  Alejandro lo pida explícitamente (11.5% de CTR ya probado).
- Las cifras de "respuesta corta" **nunca se hardcodean** — siempre `SuccessionCalculatorConfig` en
  vivo, para no desalinearse de la calculadora del mismo post.
- Las migraciones de contenido (5.1/5.4) son no-op si el post no existe o el ancla no coincide —
  nunca "adivinan" dónde insertar.

## Pendiente / para ti (Alejandro)
1. Los mismos 8 parámetros de la calculadora (Fase 4) — al confirmarlos, el aviso "⚠ no confirmado"
   desaparece también del bloque "respuesta corta" de estos 2 posts, sin tocar código.
2. Fase 6 (fusiones) todavía no toca estos posts.
