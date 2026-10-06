# Respuesta sugerida de IA: no repreguntar datos que ya vienen en el brief

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
- Guía específica para `propietario_renta`: preguntar la **dirección exacta** (calle y número) —
  es el dato real que falta para poder dar de alta la captación (la colonia sola no alcanza).

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

## Cómo probarlo
Regenerar la respuesta sugerida ("🤖 Sugerir respuesta" / "Regenerar") en un lead cuyo brief ya
conteste el dato típico de su tipo (ej. `timing` en un `propietario_renta`) y confirmar que la
pregunta calificadora NO repite ese dato. Verificado a mano con el lead real #179 tras el fix.
