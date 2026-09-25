# Portal del inquilino: "Mi camino", menú corto y "Mis documentos" (fases 1–2)

> 2026-09-26. Léelo antes de tocar el menú del Portal (`layouts/portal`), `TenantRoadmap`, `TenantDocumentRows` o las vistas `portal/journey`, `portal/documents/tenant`, `portal/expediente`.

## Por qué
El inquilino veía 5 destinos que se traslapaban (Inicio, Renta, Expediente, Documentos, Cuenta), dos barras de avance que se contradecían (el expediente decía "100%" mientras el trámite pedía documentos) y una página de ~1,600 líneas / 83 campos, sobre todo para llenar desde el celular. Principio: **una pantalla = una tarea; el camino ES la navegación; siempre se muestra UN "siguiente paso"**.

## Qué hay (solo para el INQUILINO con renta activa)
- **"Modo inquilino"** (`$tenantNav` en `layouts/portal.blade.php`): cliente con interés `renta_inquilino` y una `RentalProcess` **activa** donde es `tenant_client_id`. Menú: **Mi camino · Mis documentos (con contador de pendientes) · Mi renta · Mi asesor (WhatsApp) · Mi cuenta**. "Mi Expediente" ya no es destino del menú (sus formularios se abren desde el paso "Tus datos").
- **Barra inferior fija en celular** (`portal/_tenant_bottom_nav.blade.php`, ≤768 px): 4 iconos al alcance del pulgar (Mi camino, Documentos, Mi renta, Asesor). En escritorio sigue el menú lateral.
- **Mi camino** (`/mi-camino`, `PortalJourneyController`, `portal/journey.blade.php`): saludo, avance (pasos completos), tarjeta **"Tu siguiente paso"** con UN botón y tiempo estimado (`TenantRoadmap::nextAction`), recordatorio del apartado si falta, el camino compacto (hecho = una línea, actual = expandido, futuro = solo título) y botón de WhatsApp al asesor. **`/inicio` redirige aquí** para inquilinos.
- **Pasos del camino** (`TenantRoadmap::build`): Apartado (paralelo, no bloquea) → **Tus datos** (`legal_completeness`) → Documentos → Garantía → Contrato → Entrega.
- **Mis documentos** (`portal/documents/tenant.blade.php`, `TenantDocumentRows`): lista con estado por documento en lenguaje simple (**Falta / En revisión / Aprobado / Corregir** + motivo del rechazo). Tocar una fila abre **solo ese** documento (`?open=ine_frente`; filas con alternativas —domicilio luz/agua/gas, ingresos nómina/estados/CFDI— piden primero "¿cuál vas a subir?" con `?cat=`). Un solo `document-uploader` de Livewire a la vez (pantalla ligera, tarea única). La cámara guiada de INE vive en `portal/_id_camera_js.blade.php` (extraída de `expediente`, compartida).
- **Tus datos** = `portal/expediente` para el inquilino: encabezado simple con "← Mi camino" y "N de M secciones listas" (sin el "100% completo" que contradecía al camino); el apartado confirmado es una línea discreta.

## INVARIANTES — no romper
- **Un solo indicador de avance para el inquilino: los pasos del camino.** No reintroduzcas un "% completo" del expediente en su vista.
- **"Tu siguiente paso" siempre es UNA acción** (o "esperando", con `cta_url` null). Si agregas un paso al camino, agrega su rama en `TenantRoadmap::nextAction` y su test en `TenantJourneyTest`.
- **El apartado nunca bloquea** (`parallel`): solo es recordatorio secundario.
- **El `document-uploader` se renderiza SOLO para la fila abierta**; no lo pongas en todas las filas (peso y lentitud en celular).
- El modo inquilino depende de una renta **activa** como arrendatario; si no hay, el Portal muestra el menú de siempre. Los demás perfiles (propietario, comprador, vendedor) NO cambiaron.
- La cámara guiada (`idCamOpen…`) debe seguir incluyéndose vía `@include('portal._id_camera_js')` dentro de un `<script>` en toda página que use el uploader de identificaciones.
- Diseño móvil: una columna, botones ≥44 px, sin depender del menú hamburguesa para navegar.

## Cómo se verificó
`TenantJourneyTest` (lógica sin BD) + tinker con inquilino sintético a través del enrutador real (rutas, contenido esperado, sin "Mi Expediente" en el menú) + vista en marcos de 390 px en navegador (capturas de Mi camino, Mis documentos y fila abierta).

## Pendiente (fases 3–4 acordadas)
1. Partir "Tus datos" en pantallas cortas (5–8 campos) con guardado automático, botón "Continuar" fijo y teclado correcto por campo (`inputmode`, `autocomplete`); mostrar solo lo que aplica a un inquilino.
2. Planes de póliza como tarjetas deslizables con "ver qué incluye"; firma del contrato dentro del Portal.
3. Probar con un teléfono real y el caso de Carlos; luego decidir si se replica el patrón a propietario, comprador y vendedor.
