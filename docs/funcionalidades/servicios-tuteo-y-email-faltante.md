# Propuesta de Servicios: siempre de "tú" + plantilla de email que no existía

> 2026-10-06. Léelo antes de tocar `ServiciosGeneratorService::sendByEmail()/sendByWhatsApp()` o
> `resources/views/emails/servicios-attachment.blade.php`.

## Hallazgo 1 — el mensaje de WhatsApp hablaba de "usted"
Al revisar el WhatsApp real enviado a Alonso Aldama, Alejandro notó el cambio de tono: el primer
mensaje (generado por IA) habla de "tú", pero el mensaje de WhatsApp de la Propuesta de Servicios
(texto fijo en código) decía *"le comparto... Quedo a sus órdenes"* — de "usted". Inconsistente con
el resto de la conversación y con el tono de la empresa.

**Arreglo**: el mensaje fijo de `sendByWhatsApp()` ahora es *"te comparto... Quedo a tus órdenes"*.
Regla de la IA reforzada explícitamente: siempre "tú", nunca "usted"/"le comparto"/"sus órdenes" —
pero sin caer en modismos tampoco (ver [[ai-respuesta-no-repreguntar]]).

**Texto final (aprobado por Alejandro, probado primero con un envío real a un propietario real antes
de meterlo en código)**: además de corregir el tuteo, el mensaje ahora deja la puerta abierta a
llamada/dudas/cita en vez de solo despedirse:
> Hola {cliente}, te comparto la propuesta de servicios de Home del Valle para la comercialización
> de {dirección}. Si tienes dudas, quieres que platiquemos por teléfono o prefieres que agendemos
> una visita, aquí estoy. Quedo a tus órdenes. {asesor}

El mismo texto (adaptado a HTML) se usa en el correo (`emails/servicios-attachment.blade.php`) para
que ambos canales digan lo mismo.

## Hallazgo 2 — el envío por correo nunca había funcionado (vista inexistente)
Al revisar el código de `sendByEmail()` para aplicar el mismo arreglo de tono, se encontró que
renderiza `Mail::send('emails.servicios-attachment', ...)` — **esa vista Blade no existía en el
repo**. El botón "Enviar" (email) de la Propuesta de Servicios habría tronado con un error de
"View not found" la primera vez que alguien lo usara — igual que el bug de WhatsApp
([[propuesta-servicios-envio-roto]]), nunca se había probado de punta a punta.

**Arreglo**: se creó `resources/views/emails/servicios-attachment.blade.php` (mismo estilo visual
que `ficha-tecnica-envio.blade.php`: header navy, tarjeta blanca), de "tú", sin modismos, con las
variables que `sendByEmail()` ya pasaba (`clientName`, `agentName`, `address`).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Mensaje de WhatsApp fijo (ahora de tú) | `app/Services/ServiciosGeneratorService.php::sendByWhatsApp()` |
| Plantilla de email (nueva) | `resources/views/emails/servicios-attachment.blade.php` |
| Regla de tono reforzada para la IA | `AILeadClassifierService::reglasDeRespuesta()` |

## INVARIANTES — no romper
- **Todo texto hacia un lead/cliente de Home del Valle va de "tú"** — nunca "usted", "le comparto",
  "sus órdenes". Si se agrega un mensaje fijo (no generado por IA) en algún servicio nuevo, revisarlo
  a mano — la IA ya lo sabe por su regla, pero el texto fijo en PHP no pasa por esa regla.
- Si se agrega un tercer canal de envío a `ServiciosGeneratorService` (ej. SMS), seguir el mismo tono
  y, si usa una vista Blade nueva, **verificar que el archivo exista de verdad** antes de darlo por
  terminado — este bug pasó desapercibido porque nunca se ejecutó el código, no porque el código se
  viera mal.

## Cómo probarlo
`sendByWhatsApp()`: el mensaje pre-cargado en `wa.me` debe decir "te comparto"/"tus órdenes", nunca
"le comparto"/"sus órdenes". `sendByEmail()`: debe enviar sin "View not found" — probar con una
captación real y confirmar que llega el correo con el PDF adjunto. Verificado a mano: el bug de la
vista faltante se encontró por inspección de código (aún no se mandó un correo real de prueba).
