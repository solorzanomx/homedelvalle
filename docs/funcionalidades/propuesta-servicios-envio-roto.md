# Propuesta de Servicios: enviar por WhatsApp/email nunca había funcionado

> 2026-10-06. Léelo antes de tocar `ServiciosGeneratorService::sendByEmail()/sendByWhatsApp()`.

## El bug

Al darle "WhatsApp" en la tarjeta "💼 Propuesta de Servicios" de una captación real (Alonso Aldama,
#28), truena: `SQLSTATE[HY000]: General error: 1364 Field 'tracking_token' doesn't have a default
value`. **Este flujo de envío nunca se había probado de punta a punta** — generar/ver el PDF sí
funcionaba (lo usamos varias veces esta semana), pero los botones de enviar jamás se habían
ejecutado hasta hoy.

**Causa**: `ServiciosGeneratorService::sendByEmail()`/`sendByWhatsApp()` escribían en
`presentation_sends` con nombres de columna y un valor de `channel` que **no existen** en la tabla
real:
- `channel` → usaban `'servicios_email'`/`'servicios_whatsapp'`; la columna es un ENUM que solo
  acepta `email`, `whatsapp`, `download`.
- `sent_to` → la columna real es `recipient_email` o `recipient_phone` (según el canal).
- `sent_by` → la columna real es `sent_by_user_id`.
- Faltaba `tracking_token` por completo — es `NOT NULL` y `UNIQUE`, sin default; lo genera la app
  (`Str::random(48)`), nadie lo rellena solo.

El servicio hermano, `PresentationGeneratorService::sendByEmail()/sendByWhatsApp()` (la Presentación
normal, no la de Servicios), **sí usa las columnas correctas** — este archivo se escribió aparte y
nunca se alineó.

## El arreglo
Mismas columnas y mismo patrón que `PresentationGeneratorService`: `channel: 'email'|'whatsapp'`,
`sent_by_user_id`, `recipient_email`/`recipient_phone`, `tracking_token: Str::random(48)`. Se agregó
`metadata: ['kind' => 'propuesta_servicios']` para poder distinguir estos envíos de los de la
Presentación normal en `presentation_sends` (comparten la misma tabla, el `channel` no alcanza para
diferenciarlos).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| El fix | `app/Services/ServiciosGeneratorService.php::sendByEmail()/sendByWhatsApp()` |
| El patrón correcto (de donde se copió) | `app/Services/PresentationGeneratorService.php::sendByEmail()/sendByWhatsApp()` |
| Columnas reales de la tabla | migración `presentation_sends` (busca `Schema::create('presentation_sends'`) |

## INVARIANTES — no romper
- **`presentation_sends.channel` es un ENUM `email|whatsapp|download`** — cualquier código nuevo que
  escriba ahí debe usar exactamente esos 3 valores. Para distinguir "Presentación" de "Propuesta de
  Servicios" (comparten tabla), usar `metadata.kind`, no inventar un valor de `channel` nuevo.
- **`tracking_token` siempre se genera en la app** (`Str::random(48)`) — no tiene default en la BD.
- Si se agrega un tercer documento que también use `PresentationSend` (ej. Opinión de Valor), seguir
  el mismo patrón: columnas reales + `metadata.kind` para distinguirlo.

## Cómo probarlo
Desde una captación real: tarjeta "Propuesta de Servicios" → botón "WhatsApp" (o "Enviar" para
email) → debe redirigir a `wa.me` con el mensaje (WhatsApp) o enviar el correo sin error. Antes de
este fix, ambos truenan con el mismo `SQLSTATE[HY000]: ... tracking_token`. Verificado con la
captación real #28 (Alonso Aldama) tras el fix.
