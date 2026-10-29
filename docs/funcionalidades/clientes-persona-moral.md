# Clientes persona moral (empresas)

> 2026-10-29. Léelo antes de tocar `Client::is_company`/`company_name`, `TenantDocumentRows`,
> `TenantDocumentChecklist`, o el checklist de documentos del Portal.

## Qué resuelve

Caso real que lo originó: Renta #10, oficina Av. Universidad 526 (Vértiz Narvarte), arrendada
a **Andromeda Services SA de CV**, representada por Aída Leticia Ornelas Godoy. El sistema no
distinguía clientes persona moral — el `Client` siempre era tratado como una persona física, y
el checklist de documentos del inquilino (`TenantDocumentRows`) le pedía a Aída su **nómina
personal** y un comprobante de domicilio **"a tu nombre"**, cosas que no aplican porque quien
renta es la empresa, no ella. Tampoco pedía nada de lo que sí hace falta: acta constitutiva,
poder notarial del representante, constancia de situación fiscal de la empresa.

## Cómo funciona

- `clients.is_company` (boolean) + `clients.company_name` (razón social) — el resto del
  registro (`name`, `first_name`/`last_name_*`, `curp`, `rfc`, `address_*`, identificación)
  sigue siendo del **representante legal** que firma, no de la empresa. El RFC que importa
  para el contrato (el de la empresa) se captura en el mismo campo `rfc` de siempre.
- Toggle "Es una empresa (persona moral)" en `clients/_legal_fields.blade.php`, **sin** el
  `data-types` que gatea el resto de esa sección (ese bloque solo se muestra para
  `venta,renta_propietario` — el toggle de persona moral aplica también a `renta_inquilino`,
  que es justo el caso que lo originó).
- `Client::getFullNameLegalAttribute()` / `getFullNameAttribute()` devuelven la razón social
  cuando `is_company` es true. Nuevo accessor `Client::contractPartyName` →
  `"RAZÓN SOCIAL, representada por NOMBRE DEL REPRESENTANTE"`, pensado para los documentos
  legales (Acuerdo de Representación / Arrendamiento) — **pendiente de cablear** en los
  `*GeneratorService` de documentos, que hoy siguen leyendo `full_name_legal` directo.
- `Client::getLegalCompletenessAttribute()` usa una lista de campos distinta si `is_company`
  (razón social + RFC + identificación del representante + domicilio), sin estado civil, CURP
  propia ni datos de empleo.
- 4 categorías nuevas en `Document::CATEGORIES`: `acta_constitutiva`,
  `poder_representante_legal`, `constancia_situacion_fiscal_empresa`,
  `comprobante_domicilio_fiscal`.
- `TenantDocumentChecklist::PERSONA_MORAL` (nueva const) + `clientFacing(bool $isCompany)` —
  reemplaza `DATOS_LABORALES + INGRESOS + REFERENCIAS` por las 4 categorías de arriba cuando
  `$isCompany` es true.
- **El cambio que de verdad importa:** `TenantDocumentRows::build()` (lo que arma la página
  real "Mis documentos" del Portal) ahora recibe el `$client` y, si `$client->is_company`,
  sustituye los grupos "Comprobante de domicilio" + "Comprobante de ingresos" por un único
  grupo "Documentos de la empresa" con las 4 categorías de `PERSONA_MORAL`. El grupo
  "Identificación oficial" (del representante) y "Apartado" no cambian.

## Pendiente (no lo resuelve esta pasada — alcance deliberadamente acotado)

- **`resources/views/portal/expediente.blade.php`** (1598 líneas, paso "Completa mis datos" del
  Portal) sigue siendo 100% persona física: pide estado civil, CURP, empleo, referencias
  personales, datos del aval incluyendo "si aparece casado en la escritura". No se tocó en esta
  pasada — es un rework grande. Mientras tanto, para un inquilino persona moral, el asesor debe
  guiar la captura de esos datos manualmente (o dejar el paso incompleto y avanzar con
  documentos subidos directo por el admin).
- Los 4 documentos de empresa no tienen badge de "Falta/Aprobado" tan pulido como
  identificación/ingresos (reusan `row()` genérico sin `hint`/`chips` — se puede afinar si hace
  falta).
- El grupo "Documentos de tu aval" (si `guarantee_type = aval`) sigue siendo 100% persona
  física (acta de matrimonio, INE del cónyuge) — no se evaluó si tiene sentido un aval para un
  inquilino persona moral (probablemente no: lo normal ahí es póliza jurídica o depósito).
- `*GeneratorService` de documentos legales (Acuerdo de Arrendamiento, Acuerdo de
  Representación) todavía no usan `Client::contractPartyName` — si se genera un contrato real
  para un inquilino/propietario persona moral, hay que revisar que el nombre de la parte salga
  bien ("RAZÓN SOCIAL, representada por...") y no solo el nombre del representante a secas.

## Mapa de archivos

| Qué | Dónde |
|---|---|
| Migración | `database/migrations/2026_10_29_120000_add_company_fields_to_clients.php` |
| Modelo | `app/Models/Client.php` (`is_company`, `company_name`, accessors) |
| Categorías de documento | `app/Models/Document.php` → `CATEGORIES` |
| Checklist (invitación/WhatsApp) | `app/Support/TenantDocumentChecklist.php` → `PERSONA_MORAL`, `clientFacing()` |
| Checklist real del Portal | `app/Support/TenantDocumentRows.php` → `build()` |
| Formulario de cliente | `resources/views/clients/_legal_fields.blade.php` (toggle), `ClientController::store()`/`update()` |
| Ficha del cliente | `resources/views/clients/show.blade.php` |

## Bug aparte encontrado y corregido en la misma sesión

`rental_processes.guarantee_type` es `NOT NULL DEFAULT 'deposito'`, pero
`RentalProcessController::store()`/`update()` mandaban `NULL` explícito cuando el select del
formulario quedaba vacío (la validación era `'nullable'`) — eso pisa el default de la columna y
truena con `SQLSTATE[23000]`. Fix: si `guarantee_type` llega vacío, se quita del array antes de
`create()`/`update()` para que aplique el default de la BD, en vez de forzarlo a requerido (que
rompería el patrón de captura progresiva del resto del formulario).

## Verificado (2026-10-29)

- Prueba real con datos desechables (transacción con rollback): `TenantDocumentRows::build()`
  devuelve los grupos correctos (`Apartado, Identificación oficial, Documentos de la empresa`)
  para un cliente con `is_company = true`; `clientFacing(true)` devuelve la lista de 8 ítems
  correcta; `full_name_legal`/`contract_party_name`/`legal_completeness` calculan bien.
- Suite completa: mismos 9 fallos preexistentes (Mail/V4, ajenos), 121 passed, sin regresiones.
- Pendiente de verificar en producción con Aída/Andromeda real (marcar `is_company = true` +
  `company_name` en su ficha y confirmar que `/documentos` del Portal ya muestra el grupo
  correcto).
