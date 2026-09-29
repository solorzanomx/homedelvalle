# Blog: fusión de artículos que compiten entre sí (en borrador)

> 2026-09-30. Fase 6 de `prompt-claude-code-blog-leads.md` (Alejandro). Trabajado por SSH contra la
> BD real de producción. **Nada se despublicó ni se borró** — todos los posts originales (pilares y
> absorbidos) siguen exactamente igual en vivo.

## Fechas de publicación (verificadas antes de fusionar)
Ningún post está por debajo del umbral de 60 días al 2026-09-30 — el más nuevo de los 21 posts
involucrados es `vender-predio-napoles-desarrolladora-2026` (30 de julio, 62 días). No se excluyó
ningún grupo por antigüedad. Tabla completa en el comentario de cabecera de la migración
`2026_09_30_150004_seed_blog_fusion_drafts_fase6`.

## Qué se construyó
Por cada uno de los 6 grupos: un **post nuevo en `status='draft'`** con el contenido fusionado
(slug `{pilar}-borrador-fusion`, título con prefijo `[BORRADOR FUSIÓN]`) — el pilar en vivo no se
toca — y un **`BlogRedirect` por cada post absorbido, con `active=false`**, apuntando al slug del
pilar en vivo. Ningún redirect se activa solo; Alejandro los activa desde `/admin/blog-redirects`
cuando aprueba cada fusión, y entonces reemplaza el contenido del pilar con el del borrador.

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

## ⚠️ Hallazgo importante — corregir independientemente de la fusión
El post pilar en vivo del Grupo 1 (id=1, `...guía completa`) tiene una explicación de **ISR
incorrecta**: dice que la venta de un inmueble heredado está "exenta" si es la "primera venta" en
"2-3 años" — eso no es lo que dice la ley (la exención de casa-habitación depende de que el heredero
haya vivido ahí, con el límite de 700,000 UDIs y la regla de 3 años, como ya explica correctamente
`isr-venta-propiedad-heredada-mexico-2026`). El borrador de fusión usa la versión correcta (de
`vender-propiedad-heredada-benito-juarez-2026`), **no** la del pilar. Alejandro debe corregir el
pilar en vivo cuando active esta fusión — el detalle completo está comentado en
`fusion-vender-heredada.html`.

También se detectó (y se evitó propagar) contenido de dudosa exactitud geográfica en el pilar del
Grupo 3 (H5/H6): mencionaba "Los Morales" como zona de Benito Juárez — esa colonia es de Miguel
Hidalgo — y un ejemplo de retorno con cifras muy específicas (800% ROI) sin fuente. El borrador de
fusión los omite; ver el comentario de cabecera en `fusion-h5-h6.html`.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Contenido de cada borrador | `database/seeders/blog-posts/fusion-{grupo}.html` |
| Migración que siembra los drafts + redirects inactivos | `2026_09_30_150004_seed_blog_fusion_drafts_fase6` |
| Cross-link entre los 2 posts de ISR | `2026_09_30_150003_link_isr_posts_fase6` |

## INVARIANTES — no romper
- Los borradores se crean con `status='draft'` — nunca `'published'` ni `'scheduled'`. Confirmar
  siempre que un draft nuevo no aparezca en `/blog`, en `/sitemap.xml` ni sea accesible por URL
  directa (`Post::published()` los excluye en los tres casos).
- Los redirects de esta fase nacen con `active=false` y `notes` empieza con `'Fase 6:'` — nunca se
  activan desde una migración; solo Alejandro los activa a mano.
- Si el post absorbido o el pilar no existen (local, o si alguno se borra/renombra antes de
  desplegar), la migración es no-op para ese grupo — nunca crea un draft a medias ni un redirect
  huérfano.

## Cómo revisar y activar una fusión (para Alejandro)
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
