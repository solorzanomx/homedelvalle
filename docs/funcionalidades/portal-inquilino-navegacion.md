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

## Reglas de documentos del inquilino (definidas por Alejandro, 2026-09-26)
- **Identificación: una u otra.** Si sube **INE**, se piden **frente y vuelta** y NO se ofrece pasaporte; si sube **pasaporte**, solo la hoja de datos; si aún no sube nada, la fila es un selector ("¿Qué identificación vas a usar? INE / Pasaporte"). Lógica en `TenantDocumentRows::idMode()`.
- **Ingresos: los ÚLTIMOS 3** (uno por mes) de un mismo tipo (nómina, estado de cuenta o CFDI de honorarios). La fila muestra el avance ("Faltan 2 de 3 · 1 de 3 subidos") y no se da por completa con menos de 3 (`TenantDocumentRows::INCOME_MONTHS`, `stateFor()`).
- **La aprobación del asesor también exige 3:** `RentalExpedienteStatus` solo da el grupo de ingresos por completo con **3 archivos aprobados** del mismo tipo (o 1 "otro comprobante"), y el aviso "expediente completo" depende de eso. Si cambias el número, cámbialo en las DOS constantes `INCOME_MONTHS`.
- Domicilio: una sola pieza (luz, agua o gas de los últimos 3 meses).

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

## Fase 3: "Tus datos" como asistente por pasos (2026-09-26)
- `/mi-expediente?paso=<paso>` para el inquilino: **un paso por pantalla** — `datos` → `identificacion` (identificación y domicilio) → `hogar` → `referencias` → `ingresos` → `garantia` (solo si su ruta es aval). Sin `paso`, abre en el **primer incompleto**. Lógica: `PortalExpedienteController::wizardSteps()`.
- **Barra fija** "← Anterior / Guardar y continuar →" (sobre la barra inferior del celular), puntos de avance, y **cada guardado sigue al siguiente paso** (`next` → `saved()`); el último ("Guardar y terminar") regresa a **Mi camino**. Los botones de guardar de cada formulario se ocultan solo cuando hay JS (sin JS siguen funcionando).
- **Los documentos ya no se suben aquí** (bloques `.exp-doc-block` ocultos y sustituidos por una nota a "Mis documentos"); los campos ocultos se siguen enviando con su valor actual.
- **Prellenado con IA** (`prefillFromDocuments`): CURP, vigencia de la INE y dirección leídos de los documentos subidos, **solo en pantalla** (no se guarda hasta que el cliente confirma) y después de calcular el avance para no inflarlo.
- **Borrador en el teléfono** (`localStorage`, `hdv-draft-<usuario>-<paso>`): se guarda al escribir, se restaura solo en campos vacíos y se borra al enviar. Teclado/autocompletado por nombre de campo (`tel`, `email`, CURP en mayúsculas, CP numérico, `street-address`…), inputs a 16 px (evita el zoom de iOS).
- **El inquilino se detecta por su renta activa** también en `PortalExpedienteController::show()` (`activeTenantRental`), no solo por `interest_types`.
- El apartado pendiente y su tarjeta de pago se abren desde Mi camino con `?apartado=1`; en el asistente no se repite.

- **Referencias en el CRM:** las 3 referencias personales que el inquilino captura en su Portal (paso "Referencias personales") se ven en **Renta → Investigación → tarjeta Referencias** (nombre, celular con Llamar/WhatsApp, fijo, correo, domicilio, y "N de 3"); el asesor anota abajo cuántas verificó y el resultado.

## Pendiente (fase 4 acordada)
1. ~~Partir "Tus datos" en pantallas cortas~~ ✅ hecho (arriba). Falta: guardado automático en el SERVIDOR (hoy es un borrador local) y dividir los pasos más largos (p. ej. identificación y domicilio, trabajo e ingresos) si las pruebas con clientes lo piden.
2. Planes de póliza como tarjetas deslizables con "ver qué incluye"; firma del contrato dentro del Portal.
3. Probar con un teléfono real y el caso de Carlos; luego decidir si se replica el patrón a propietario, comprador y vendedor.
