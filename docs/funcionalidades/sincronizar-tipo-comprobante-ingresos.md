# income_proof_type se sincroniza solo al subir el comprobante de ingresos

> 2026-10-04. Léelo antes de tocar `Livewire/Portal/DocumentUploader.php::upload()`, `Client::income_proof_type`,
> o el % de "Tus datos" (`ExpedienteFields::INCOME_TENANT` / `Client::legal_completeness`).

## El bug

`Client.income_proof_type` (declara CÓMO va a comprobar sus ingresos: nómina / estado de cuenta / CFDI — un
`<select>` en el paso "Ingresos" del wizard de "Tus datos") y el **documento real subido** (categoría `nomina` /
`estado_cuenta` / `cfdi_honorarios` en "Mis documentos") son dos piezas de estado **separadas** que nunca se
sincronizaban entre sí. Un inquilino podía subir y que le aprobaran sus 3 comprobantes de ingresos —"Tus
documentos" quedaba en ✓ — sin haber tocado nunca el `<select>`, porque nada se lo pedía de forma obligatoria ni
visible.

**Consecuencia real (Yarlin Nava, trato #8)**: subió sus 3 estados de cuenta, se los aprobaron, pero
`income_proof_type` seguía `NULL`. Ese único campo cuenta tanto en `ExpedienteFields::INCOME_TENANT` (el % de la
sección "Ingresos" del wizard) como en `Client::legal_completeness` (el % que lee `TenantRoadmap::informacion()`
para decidir si el paso "Tus datos" está completo). Con ese campo vacío, "Tus datos" se quedaba atorado en 96% —
y como `TenantRoadmap::build()` solo activa el SIGUIENTE paso (`Tu co-arrendatario`) cuando el anterior está al
100%, el camino nunca avanzaba. Para ella y su pareja era indescifrable: "ya llenamos todo pero no avanza".

## El arreglo

`DocumentUploader::upload()` ahora sincroniza `Client.income_proof_type` automáticamente cuando la categoría
subida es `nomina`, `estado_cuenta` o `cfdi_honorarios` — ya no hace falta elegir el `<select>` por separado, el
documento real que subió es la fuente de verdad. Esto cubre tanto "Mis documentos" como el uploader embebido
dentro del wizard de "Tus datos" (es el mismo componente Livewire en los dos lugares).

Corregido a mano en producción: `Client#101 (Yarlin).income_proof_type = 'estado_cuenta'` (ya tenía 3 estados de
cuenta verificados) — `legal_completeness` pasó de 96% a 100%.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Sincroniza el campo al subir | `app/Livewire/Portal/DocumentUploader.php::upload()` |
| Campos que cuentan (siguen igual, el bug no era la lista) | `app/Support/ExpedienteFields::INCOME_TENANT`, `Client::getLegalCompletenessAttribute()` |
| Decide si "Tus datos" está completo y cuál es el siguiente paso | `TenantRoadmap::informacion()`/`build()` |

## INVARIANTES — no romper
- **Si se agrega una categoría de documento de ingresos nueva** (hoy solo nómina/estado de cuenta/CFDI tienen chip
  en `TenantDocumentRows`), agregar también su mapeo aquí (`$incomeCategoryToType` en `DocumentUploader::upload()`)
  — si no, vuelve a aparecer el mismo atoro.
- `Client::legal_completeness` y `ExpedienteFields::INCOME_TENANT` siguen siendo **dos cálculos independientes**
  (no se unificaron en este fix) — ambos cuentan `income_proof_type`, así que ambos se resuelven con el mismo
  arreglo, pero si se edita uno hay que revisar el otro a mano (no están conectados en código).
- El `<select>` "¿Cómo vas a comprobar tus ingresos?" del wizard sigue existiendo — ahora es más bien informativo/
  editable manualmente (ej. si quiere cambiar de comprobante antes de subir nada), pero ya no es el único camino
  para llenar el campo.

## Cómo probarlo
Subir un documento de categoría `nomina`/`estado_cuenta`/`cfdi_honorarios` vía el uploader del Portal con
`Client.income_proof_type` vacío: debe quedar sincronizado al tipo correspondiente sin tocar el `<select>`.
Verificado a mano en producción con el caso real de Yarlin. Pendiente: test formal con `Livewire::test()` +
`Storage::fake()`.
