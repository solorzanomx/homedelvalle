# Garantía del inquilino: póliza jurídica vs. aval, "¿qué sigue?" y contrato

> Construido 2026-09-26. Léelo completo antes de tocar `TenantRoadmap`, las vistas `portal/_tenant_roadmap` y `rentals/_guarantee_route`, el catálogo de planes o la garantía de una renta.

## Regla de negocio (decidida por Alejandro)
- Terminados y aprobados los documentos, el inquilino ve **qué sigue** y define su **garantía**:
  - **Sin aval con propiedad en CDMX → póliza jurídica, definitivamente.** Elige uno de los planes de **Previsión Legal** (Básica, Superior — la "media" de $6,000, la más común —, Integral). **Previsión Legal hace su propia investigación** y **el inquilino les paga DIRECTO** (Home del Valle solo presenta los planes, registra la elección y tramita el alta).
  - **Con aval en CDMX → investigación de Home del Valle**: cuota de **$3,500 MXN** (no reembolsable) + datos y documentos del aval; el propietario aprueba al candidato.
- Después: **contrato y firma**. Con póliza el contrato **lo emite el proveedor**: el asesor lo **sube** y aparece en el Portal del inquilino y del propietario (se firma con el mecanismo de siempre). Sin póliza, el contrato se **genera con un clic** con la plantilla activa.
- Los planes NO son código: son un **catálogo editable** (CRM → Rentas → Planes de póliza). La página de Previsión Legal no publica precios (varían por estado): Superior arranca en $6,000; **Básica e Integral quedan ocultas hasta que se les ponga precio**. El Portal solo muestra planes **activos y con precio**.

## Mapa de archivos
| Pieza | Archivo |
|---|---|
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
- **Elegir plan** crea/actualiza el registro `PolizaJuridica` (`status=pending`, costo del plan, aseguradora = proveedor del plan) y **avisa al asesor**; no se puede cambiar de plan una vez `approved` (solo con el asesor).
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
