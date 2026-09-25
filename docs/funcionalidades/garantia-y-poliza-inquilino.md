# Garantía del inquilino: póliza jurídica vs. aval, "¿qué sigue?" y contrato

> Construido 2026-09-26. Léelo completo antes de tocar `TenantRoadmap`, las vistas `portal/_tenant_roadmap` y `rentals/_guarantee_route`, el catálogo de planes o la garantía de una renta.

## Regla de negocio (decidida por Alejandro)
- Terminados y aprobados los documentos, se define la **garantía**:
  - **Sin aval con propiedad en CDMX → póliza jurídica, definitivamente** (Previsión Legal: Básica, Superior, Integral; Previsión Legal hace su propia investigación).
  - **Con aval en CDMX → investigación de Home del Valle**: cuota de **$3,500 MXN** (no reembolsable) + datos y documentos del aval; el propietario aprueba al candidato.
- **Con póliza, DECIDE EL PROPIETARIO (2026-09-27), no el inquilino:** desde su Portal (`Mi renta`) elige el **plan** —ve el precio calculado con la renta de SU trato— y **quién la paga: el inquilino al 100% o mitad y mitad** (`PolizaPricing::SHARE_OPTIONS`; solo esas dos). Después el inquilino solo VE la decisión: plan, **lo que le toca pagar** ($ y %), gastos de emisión y qué cubre. El asesor puede decidir/corregir a nombre del dueño (`decided_by = advisor`).
- **Precio = tarifa de la Hoja de Servicios "AM QRO NL 2026" de Previsión Legal** (Área Metropolitana, Querétaro y Nuevo León) según la **renta mensual**: rangos de precio fijo hasta $30,000 y desde $30,001 un **% de la renta mensual** (Básica 21%, Superior 29.5%, Integral 50%). Datos en `poliza_tariff_sheets` / `poliza_rates` (editables en CRM → Planes de póliza → Tarifario y cobertura). **Gastos de emisión $1,700**: se cubren al iniciar el trámite; **se acreditan al precio si la operación se concreta y no se reembolsan si no** (decisión de Alejandro).
- **Cobertura = la matriz oficial de la hoja** (16 conceptos × 3 planes, `poliza_coverages` + pivote). Ojo: una primera carga se tomó de la página web de Previsión Legal y NO coincidía con la hoja (p. ej. Básica ya incluye el juicio por falta de pago/abandono; Integral solo añade la cobranza judicial/pagarés); ya corregida. La fuente de verdad es la hoja.
- **Cómo se paga se define por trato** (`poliza_payment_mode`): `direct` (cada parte paga su parte directo a Previsión Legal, por defecto) o `hdv` (Home del Valle cobra y liquida); el asesor marca `poliza_tenant_paid_at` / `poliza_owner_paid_at`.
- Contrato: con póliza lo emite el proveedor y el asesor lo **sube**; sin póliza se **genera con un clic**.

## Página pública de los 3 planes (2026-09-26)
- `/rentar/polizas-juridicas` (`landing.rentar.polizas`, vista `public/polizas-juridicas.blade.php`) muestra Básica / Superior / Integral con su cobertura, leída del **mismo catálogo** que usa el Portal. Está en el sitemap y enlazada desde `/rentar` y `/rentar/requisitos`.
- **Coberturas tomadas de previsionlegal.mx** (póliza jurídica de arrendamiento habitacional): Básica = investigación legal/laboral, contrato a la medida, asesoría jurídica, firma digital, intervención extrajudicial. Superior = todo lo de Básica + investigación crediticia + cobranza extrajudicial + abogado a la firma del convenio. Integral = todo lo de Superior + investigación laboral del fiador/obligado solidario + abogado en la firma del contrato + 4 procesos judiciales (adeudo, abandono, vencimiento, extinción de dominio) + cobranza judicial o ejecución de pagarés + honorarios + gastos de juicio y de desalojo. **La página de origen NO publica montos, plazos ni precios**, y presenta los planes de forma progresiva ("cada plan incluye progresivamente"). No inventes montos de cobertura.
- Dos banderas por plan en el CRM: **`show_on_website`** (aparece en la página pública, default sí) y **`show_price_public`** (muestra el precio en el sitio, default **no**: las tarifas varían por estado — encender solo cuando Alejandro confirme las tarifas). El precio del Portal (`price` + `is_active`) es independiente.
- Los textos públicos ya no dicen "afianzadora": Previsión Legal vende **pólizas jurídicas**, no fianzas (regla de copy).

## Mapa de archivos
| Pieza | Archivo |
|---|---|
| Precio por renta, reparto y opciones | `app/Support/PolizaPricing.php` (`quote`, `quotes`, `split`, `SHARE_OPTIONS`) |
| Decisión del propietario/asesor (con foto del precio) y avisos | `app/Services/PolizaDecisionService.php` |
| Portal del propietario: elegir plan y reparto | `resources/views/portal/_owner_poliza.blade.php` + `PortalRentalController::decidePolicy` (ruta `portal.rentals.poliza.decide`, solo el propietario de esa renta) |
| CRM: decidir a nombre del dueño, forma de pago, recordatorio | `rentals/_guarantee_route.blade.php` + `RentalProcessController::setPolizaDecision/setPolizaPayment/remindOwnerPoliza` |
| Editor de tarifas y matriz de cobertura | `PolizaPlanController::tarifario/saveTarifario` + `poliza-plans/tarifario.blade.php` |
| Lógica de ruta y pasos (solo LEE estado) | `app/Support/TenantRoadmap.php` — `route()`, `build()`; pasos: apartado (paralelo) → documentos → garantía → contrato → entrega |
| Catálogo de planes | `app/Models/PolizaPlan.php`, tabla `poliza_plans`; CRUD admin `PolizaPlanController` + `poliza-plans/index.blade.php` (rutas `poliza-plans.*`, middleware `admin`) |
| Columnas en la renta | `rental_processes.tenant_has_aval`, `guarantee_declared_at`, `poliza_plan_id`, `poliza_plan_selected_at` |
| Acciones del inquilino (Portal) | `PortalRentalController::declareGuarantee` y `::selectPlan` (solo el INQUILINO de esa renta; 403 para cualquier otro) |
| Vista del inquilino | `resources/views/portal/_tenant_roadmap.blade.php` (incluida en `portal/rentals/show` y `portal/expediente`) |
| Vista del asesor | `resources/views/rentals/_guarantee_route.blade.php` (arriba de la pestaña Investigación) + tarjeta guiada en la pestaña Contratos |
| El asesor corrige la ruta | `RentalProcessController::setGuaranteeRoute` (ruta `rentals.guarantee-route`) |
| Contrato con un clic | `ContractController::autoGenerate` (ruta `rentals.contracts.auto-generate`) |

## INVARIANTES — no romper
- **`tenant_has_aval === false` SIEMPRE es póliza**, aunque `guarantee_type` diga aval/pagarés/depósito. `guarantee_type='deposito'` es el **default de la columna** y NO significa que el inquilino no tenga aval: por eso la ruta es "indefinida" y el Portal le pregunta.
- **El apartado NO bloquea** el resto del camino (decisión 2026-09-24): en `TenantRoadmap` es un paso `parallel` (estado `pending`, nunca `active`).
- **El inquilino paga la póliza directo al proveedor**: no registres cobros de póliza como si Home del Valle los recibiera. La cuota de investigación ($3,500) sí es nuestra y **no aplica con póliza** (commit `5ad97bd`).
- **Decidir plan** (`PolizaDecisionService::decide`) congela una FOTO (`poliza_quote_amount`, `poliza_emission_fee`, `poliza_tariff_sheet_id`) para que un cambio de tarifario no altere lo ya acordado, crea/actualiza el `PolizaJuridica` (`pending`, costo = precio) y avisa al asesor. No se puede cambiar una vez `approved`. Al declarar el inquilino "sin aval" se le avisa al propietario (portal + correo, una vez).
- **El inquilino YA NO elige plan**: no reintroduzcas tarjetas de elegir plan en su Portal. Solo el propietario de esa renta puede decidir (403 para el inquilino y cualquier otro cliente).
- **Reparto: solo 100% inquilino o 50/50** (`PolizaPricing::SHARE_OPTIONS`). Si se agregan otras opciones, cámbialo ahí y en el `in:` de validación.
- Rentas anteriores donde el inquilino ya había elegido plan siguen válidas: sin reparto guardado se asume 100% inquilino y el monto se calcula con la tarifa vigente.
- **Solo el inquilino de esa renta** puede declarar garantía/elegir plan (`tenantRental()` → 403). No lo aflojes.
- **Con póliza no se genera el contrato propio**: el asesor **sube** el del proveedor (`ContractController::upload`). `autoGenerate` prefiere una plantilla que NO sea "con póliza".
- **Los nombres de los planes vienen del proveedor** (Básica / Superior / Integral). No inventes coberturas por plan: el detalle lo captura el asesor en el catálogo.
- El estado de la póliza (`PolizaJuridica.status`) lo actualiza el asesor a mano; el roadmap solo lo refleja.

## Cómo probar
`php artisan test --filter="TenantRoadmapTest"` + el patrón de tinker con transacción/rollback (crear inquilino con `user_id`, renta, documentos aprobados; llamar `declareGuarantee`/`selectPlan` como el usuario del Portal; comprobar que otro cliente recibe 403).

## Pendiente
1. Actualizar el estado de la póliza automáticamente (hoy manual) y recordatorios al inquilino si tarda en elegir plan.
2. Detalle de coberturas por plan (lo captura Alejandro en el CRM cuando tenga el tabulador con precios por estado).
3. Firma del contrato propio dentro del Portal (hoy se firma con Google eSignature/enlace, como el resto de contratos).
