# Nombre legal en documentos legales (Recibo de Apartado, Oferta de Compra, Adéndum de Comisión)

> 2026-10-03. Léelo antes de tocar `PurchaseOfferGeneratorService::buyerInfo()` o cualquier generador de PDF legal que lo use.

## El bug

`buyerInfo()` arma el nombre que aparece en el PDF a partir de un `Client`. Hasta ahora priorizaba `Client.name`
(el nombre informal, capturado en el primer contacto — "como se presentó por teléfono") sobre los campos
divididos `first_name` / `last_name_paterno` / `last_name_materno` (los que se llenan durante la verificación de
identidad, y que deberían coincidir con la identificación oficial o la escritura).

Reportado por Alejandro: en el Recibo de Apartado del trato de Marco Arturo Berger, el PDF salió con el nombre
común ("Arturo Berger") en vez del nombre legal del propietario — un documento legal real debe llevar el nombre
tal como está en la documentación oficial del inmueble, no un apodo o la forma en que el cliente se presentó.

## El arreglo

`buyerInfo()` ahora usa los campos **legales** (`first_name` + al menos un apellido) **cuando están completos**;
solo cae de vuelta a `Client.name` si esos campos no se han llenado (para no dejar el documento sin nombre
mientras no se ha hecho la verificación de identidad de ese cliente).

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Resolución del nombre (prioridad legal → informal) | `app/Services/PurchaseOfferGeneratorService.php::buyerInfo()` |
| Usan `buyerInfo()` | `RentalDepositReceiptGeneratorService` (Recibo de Apartado), `PurchaseOfferGeneratorService` (Oferta de Compra), `AdendumComisionGeneratorService` (Adéndum de Comisión) |

## INVARIANTES — no romper
- **Para documentos legales, los campos divididos (`first_name`/`last_name_paterno`/`last_name_materno`) tienen
  prioridad sobre `Client.name`** cuando están completos (nombre + al menos un apellido). Si se agrega un generador
  de documento legal nuevo, debe usar `buyerInfo()` (o el mismo criterio), no `$client->name` directo.
- Si los campos legales están incompletos (falta `first_name`, o no hay ningún apellido), se usa `Client.name` como
  respaldo — nunca dejar el nombre vacío en un documento real.

## Hallazgo de datos pendiente (no es un bug de código)
El cliente de este caso real (`id=90`, `name="Marco Arturo Berger Castro"`) tiene sus campos legales llenos con
**un nombre de otra persona**: `first_name="María del Carmen"`, `last_name_paterno="Salas"`,
`last_name_materno="Jiménez"` — no coincide ni con el nombre común del cliente ni con el nombre legal real que
Alejandro espera ("María Elizabeth…"). No hay ningún documento (INE, escritura) cargado en el expediente de este
cliente que explique de dónde salieron esos datos. **Con este fix, el PDF ahora mostrará "María Del Carmen Salas
Jiménez" — que sigue sin ser el nombre correcto.** Hace falta que Alejandro confirme el nombre legal real del
propietario para corregir el registro (`clients.first_name/last_name_paterno/last_name_materno` del cliente 90)
antes de volver a generar el recibo.

## Cómo probarlo
`PurchaseOfferGeneratorService::buyerInfo($client)` con un `Client` cuyos campos legales y `name` difieran: debe
devolver el nombre legal si está completo, y `name` si los campos legales están vacíos o incompletos (probado a
mano en consola con 4 combinaciones: legal completo con name distinto, solo name, first_name sin apellidos, legal
completo sin name).
