# Respuesta sugerida de IA: no repreguntar datos que ya vienen en el brief + estrategia propietario_renta

> 2026-10-06. Léelo antes de tocar `AILeadClassifierService::suggestReply()` o `reglasDeRespuesta()`.

## El bug

Alonso Aldama (lead #179, `propietario_renta`) llenó el formulario completo: tipo de propiedad,
colonia, m², **timing: "2-4 semanas"**, etc. La respuesta sugerida por IA le preguntó *"¿cuándo
estarías listo para que entre en operación?"* — exactamente el dato que **ya había dado**. Se ve como
que Home del Valle no leyó su formulario, mal primer contacto.

**Causa**: el prompt (`reglasDeRespuesta()`) le decía a la IA "incluye UNA pregunta calificadora
natural" con ejemplos por tipo de lead, pero nunca le decía que **primero revisara el brief** para no
repetir algo ya contestado. Tampoco había guía específica para `propietario_renta` — la regla solo
cubría compra/renta (inquilino)/vendedor.

## El arreglo
- Regla nueva: antes de elegir la pregunta calificadora, revisar el brief completo — si el dato ya
  viene contestado, no volver a preguntarlo, usarlo para avanzar.
- **Segunda vuelta (mismo día, decisión de Alejandro sobre el caso real)**: para `propietario_renta`
  el primer mensaje no debe pedir datos de entrada — primero da valor, pide después. La primera
  versión de este fix hacía que el primer mensaje pidiera la dirección exacta; Alejandro decidió que
  lo lógico es que el propietario primero sepa qué servicios da Home del Valle y cuánto cuesta (eso
  es lo que de verdad está preguntando implícitamente), no que le pidamos más datos de entrada.
  Estructura nueva del primer mensaje para este tipo de lead: (1) presentarse y generar confianza
  (buenos resultados/presencia en la zona), (2) ofrecer ayuda concreta para poner el inmueble en
  renta, (3) invitar a ampliar información si gusta (sin exigir un dato puntual), (4) ofrecer mandar
  una Propuesta de Servicios para que la revise y, si le interesa, agendar una visita. La dirección
  exacta y demás datos para dar de alta el inmueble se piden DESPUÉS, cuando ya conteste mostrando
  interés — nunca en el primer mensaje.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| El prompt completo (incluye `$brief` con TODOS los campos del payload, ya se le pasaba — el problema era la regla, no la falta de datos) | `app/Services/AILeadClassifierService::suggestReply()` |
| Las reglas de redacción | `app/Services/AILeadClassifierService::reglasDeRespuesta()` |

## INVARIANTES — no romper
- El `$brief` que recibe la IA ya incluye **todos** los campos escalares del `payload` (excepto los
  internos del sistema) — si la respuesta repite algo que debería saber, el problema casi siempre es
  la REGLA, no que falte el dato en el prompt. Revisar `reglasDeRespuesta()` primero.
- Si se agrega un tipo de lead nuevo (`form_type`) a `$tipos`, agregar también su propia guía de "qué
  preguntar" en la regla de pregunta calificadora — dejarlo genérico es lo que causó este bug para
  `propietario_renta`.

## Tercera vuelta (mismo día): nombre completo + sin modismos
Alejandro pidió dos ajustes más al revisar el mensaje ya corregido:
- **Firma con nombre completo, no solo de pila**: `FormSubmissionController::aiSuggest()` armaba
  `$asesor` tomando las 2 primeras palabras de `auth()->user()->name` — para un usuario cuyo `name`
  solo trae el nombre de pila (el apellido vive aparte, en `last_name`), eso cortaba el apellido por
  completo. Cambiado a `auth()->user()->full_name` (el accessor que ya junta `name` + `last_name`).
- **Sin modismos/jerga informal**: el mensaje decía "¿si te late...?" — no refleja el tono formal de
  la empresa. Regla nueva explícita: nunca usar "te late", "no manches", "va que va", "órale" — usar
  "¿te interesa?" o "¿te gustaría?" en su lugar.

## Cómo probarlo
Regenerar la respuesta sugerida ("🤖 Sugerir respuesta" / "Regenerar") en un lead cuyo brief ya
conteste el dato típico de su tipo (ej. `timing` en un `propietario_renta`) y confirmar que la
pregunta calificadora NO repite ese dato. Para `propietario_renta`: confirmar que el primer mensaje
NO pide ningún dato (ni la dirección) y en vez de eso ofrece ayuda + propuesta de servicios + visita.
Verificado a mano con el lead real #179 (Alonso Aldama) en ambas vueltas del fix.
