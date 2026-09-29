# Blog: fusión de artículos que compiten entre sí (ACTIVADA)

> 2026-09-30. Fase 6 de `prompt-claude-code-blog-leads.md` (Alejandro). Trabajado por SSH contra la
> BD real de producción. Los 6 grupos se armaron primero en borrador (nada se despublicó ni se
> borró en ese momento) y **el mismo día Alejandro pidió activarlos todos** ("postea los posts que
> estan en revision tambien"). Ya están en vivo: verificado por SSH (lectura) que los 6 pilares
> tienen el body fusionado y siguen `published`, los 6 posts-borrador ya no existen, y los 15
> redirects de los posts absorbidos están `active=true`.

## Fechas de publicación (verificadas antes de fusionar)
Ningún post está por debajo del umbral de 60 días al 2026-09-30 — el más nuevo de los 21 posts
involucrados es `vender-predio-napoles-desarrolladora-2026` (30 de julio, 62 días). No se excluyó
ningún grupo por antigüedad. Tabla completa en el comentario de cabecera de la migración
`2026_09_30_150004_seed_blog_fusion_drafts_fase6`.

## Qué se construyó (y qué se activó después)
Por cada uno de los 6 grupos: un **post nuevo en `status='draft'`** con el contenido fusionado
(slug `{pilar}-borrador-fusion`, título con prefijo `[BORRADOR FUSIÓN]`) — el pilar en vivo no se
tocaba todavía — y un **`BlogRedirect` por cada post absorbido, con `active=false`**, apuntando al
slug del pilar en vivo.

Ese mismo día Alejandro pidió activar las 6 fusiones. Se corrió (por SSH, `activar_fusiones.php`
vía `php artisan tinker`, script temporal borrado tras usarse — **no** una migración, por el
invariante de abajo) para cada grupo: copiar el `body` del post-borrador al pilar en vivo (el
título/slug/metas SEO del pilar NO se tocan — siguen siendo los que ya rankean), borrar el
post-borrador, y activar (`active=true`) los redirects de los posts absorbidos. Verificado después
por lectura directa contra la BD de producción: los 6 pilares en `published` con el body nuevo, 0
borradores restantes, 15/15 redirects activos.

Contenido de cada borrador en `database/seeders/blog-posts/fusion-*.html` (mismo patrón que los
posts sembrados de campañas anteriores) + migración `2026_09_30_150004` que los siembra como draft.
**Ningún grupo es una suma literal de los posts originales** — se condensó lo repetido (casi todos
comparten estructura: uso de suelo, Acuerdo de Representación, opinión de valor gratuita) y se
conservó lo distinto de cada uno.

| Grupo | Pilar (slug en vivo) | Absorbe |
|---|---|---|
| 1. Vender propiedad heredada | `como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026` | `vender-propiedad-heredada-benito-juarez-2026`, `heredar-departamento-benito-juarez-pasos-venderlo` |
| 2. Predio a desarrolladora | `vender-casa-constructora-proceso-tiempos-cdmx` | `vender-predio-napoles-desarrolladora-2026`, `vender-predio-narvarte-desarrolladora-2026`, `vender-predio-portales-constructora-benito-juarez`, `vender-casa-terreno-constructores-benito-juarez-2026`, `que-buscan-desarrolladoras-predio-benito-juarez`, `cuanto-pagan-constructoras-terreno-del-valle-2026` |
| 3. Uso de suelo | `propiedades-h5-y-h6-en-benito-juarez-...` | `uso-de-suelo-h6-benito-juarez` |
| 4. Narvarte | `vivir-en-narvarte-precios-pros-contras-2026` | `narvarte-oriente-vs-narvarte-poniente-2026` |
| 5. Inversión | `invertir-inmuebles-benito-juarez-2026` | `benito-juarez-vs-cuauhtemoc-inversion-inmobiliaria-2026`, `comprar-para-rentar-benito-juarez`, `yield-renta-del-valle-narvarte-benito-juarez`, `mercado-renta-benito-juarez-2026` |
| 6. Escasez de suelo | `escasez-suelo-benito-juarez-que-significa-tu-predio` | `obra-nueva-benito-juarez-2026-escasez-oferta` |

**ISR (punto 7 del prompt): no se fusiona.** `isr-venta-casa-habitacion-benito-juarez-2026` e
`isr-venta-propiedad-heredada-mexico-2026` responden a intenciones distintas (vender tu propia casa
vs. vender algo que heredaste) — solo se enlazaron entre sí, con un párrafo nuevo en cada uno
(migración `2026_09_30_150003_link_isr_posts_fase6`, misma técnica quirúrgica de la Fase 5.4).

## ⚠️ Hallazgo importante — YA CORREGIDO al activar
El post pilar en vivo del Grupo 1 (`...guía completa`) tenía una explicación de **ISR incorrecta**:
decía que la venta de un inmueble heredado está "exenta" si es la "primera venta" en "2-3 años" —
eso no es lo que dice la ley (la exención de casa-habitación depende de que el heredero haya vivido
ahí, con el límite de 700,000 UDIs y la regla de 3 años, como ya explica correctamente
`isr-venta-propiedad-heredada-mexico-2026`). El borrador de fusión usaba la versión correcta (de
`vender-propiedad-heredada-benito-juarez-2026`), no la del pilar — y al activar la fusión (copiar el
body del borrador al pilar) esto se corrigió automáticamente, sin paso aparte. Detalle completo
sigue comentado en `fusion-vender-heredada.html` para referencia histórica.

También se detectó (y se evitó propagar) contenido de dudosa exactitud geográfica en el pilar del
Grupo 3 (H5/H6): mencionaba "Los Morales" como zona de Benito Juárez — esa colonia es de Miguel
Hidalgo — y un ejemplo de retorno con cifras muy específicas (800% ROI) sin fuente. El borrador de
fusión los omitió, así que tampoco llegaron al pilar en vivo; ver el comentario de cabecera en
`fusion-h5-h6.html`.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Contenido de cada borrador | `database/seeders/blog-posts/fusion-{grupo}.html` |
| Migración que siembra los drafts + redirects inactivos | `2026_09_30_150004_seed_blog_fusion_drafts_fase6` |
| Cross-link entre los 2 posts de ISR | `2026_09_30_150003_link_isr_posts_fase6` |

## INVARIANTES — no romper
- La migración `2026_09_30_150004` (sembrar borrador + redirects inactivos) nunca activa un
  redirect ni publica nada por sí sola — eso sigue siendo cierto aunque ya se haya activado a mano
  una vez; si algún día se re-siembra en otro entorno, vuelve a nacer todo en borrador/inactivo.
- Activar una fusión (copiar body al pilar, borrar el borrador, activar redirects) es **siempre un
  paso manual** — a mano desde `/admin`, o un script de una sola vez corrido por Claude a petición
  explícita de Alejandro (como esta vez) — **nunca** dentro de una migración.
- Los posts absorbidos originales NO se tocan al activar — siguen `published` en la BD; el redirect
  301 es lo que efectivamente los saca de circulación para visitantes y buscadores.
- El pilar conserva su propio título/slug/meta_title/meta_description/focus_keyword al activar —
  solo el `body` se reemplaza por el del borrador.

## Estado (2026-09-30): las 6 fusiones ya están activas
Los 6 grupos de la tabla de arriba ya están en vivo con el contenido fusionado; no queda ninguna
fusión pendiente de esta ronda. Pendiente solo si Alejandro quiere, más adelante: borrar (o pasar a
draft) los posts absorbidos originales — no es obligatorio, el redirect ya hace el trabajo aunque
sigan en la BD como `published`.

## Cómo revisar/activar una fusión futura (si se arma otra ronda con este mismo patrón)
1. Entra a `/admin/posts`, filtra por "Borrador" y abre el que dice `[BORRADOR FUSIÓN] ...`.
2. Edítalo como cualquier post: ajusta el copy, revisa las cifras marcadas, corrige lo que haga falta.
3. Cuando esté listo, reemplaza el contenido del post PILAR en vivo con el del borrador (copiar/pegar
   el body, o pídeme que lo haga) y publícalo.
4. Ve a `/admin/blog-redirects`, busca las filas con nota "Fase 6" del grupo que acabas de activar, y
   dales clic en "Activar". A partir de ahí, cada slug absorbido redirige 301 al pilar.
5. Borra (o deja como draft, sin publicar nunca) los posts absorbidos originales cuando quieras —
   no es obligatorio, el redirect ya hace su trabajo aunque sigan en la BD como `published`.

## Cómo probarlo
`php artisan test --filter=BlogFusionFase6Test`. El render completo de `blog/show` en el post
absorbido sigue el mismo criterio que las fases anteriores (demasiado pesado para SQLite en
memoria) — se verificó a mano contra la BD local que un draft nunca es accesible públicamente y que
los redirects de esta fase nacen inactivos.
