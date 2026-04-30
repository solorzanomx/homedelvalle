# Proceso de Renta · Home del Valle

> **Audiencia:** Alex, Ana Laura, agentes de captación y corretaje, administradores, futuros colaboradores y desarrolladores que toquen el CRM.
> **Estado:** v0 — abril 2026.
> **Documento hermano:** `02-MANUAL-IMPLEMENTACION-SITIO.md`, `03-MANUAL-OPERACIONES-CRM.docx`, `04-ROADMAP-Y-ARQUITECTURA.md`.
> **Mantenedor:** Alex (revisado por Ana Laura).

Este documento describe el proceso completo de renta como una **operación de tres fases** —captación del inmueble, colocación con inquilino, y gestión post‑cierre— y especifica cómo debe quedar reflejado en el CRM, qué SLAs aplica, qué documentos se manejan, qué automatizaciones se disparan y qué KPIs se miden.

Es la base operativa para separar el "funnel de renta" del "funnel de venta" en el admin (`/admin/rentas` vs `/admin/operaciones`) y alinear el discurso boutique con la realidad del proceso.

**Importante:** este documento describe el proceso interno. La experiencia del cliente (propietario y/o inquilino) en cada fase vive en el Portal del Cliente (`miportal.homedelvalle.mx`). Cualquier hito documentado aquí dispara una vista, notificación o documento en el portal. Ver sección 14 para el mapeo y `06-PORTAL-DEL-CLIENTE.md` para el detalle del portal.

---

## Índice

1. Por qué la renta es un funnel propio (no una variante de venta)
2. Modelo de tres fases
3. Fase 1 — Captación del inmueble en renta
4. Fase 2 — Colocación con inquilino
5. Fase 3 — Gestión post‑cierre (RentalProcess)
6. Vista admin propuesta — /admin/rentas
7. Roles y responsabilidades
8. SLAs y métricas
9. Documentos por etapa
10. Automatizaciones, emails y alertas
11. Comisiones y splits
12. Casos especiales y excepciones
13. Implicaciones para Claude Code (separar del actual `/operations`)
14. Cómo se vive cada fase en el Portal del Cliente

---

## 1. Por qué la renta es un funnel propio

| Diferencia | Venta | Renta |
|---|---|---|
| **Ciclo total promedio** | 90 días desde captación hasta escritura | 30 días desde captación hasta firma + relación continua durante toda la vigencia del contrato |
| **Comisión** | Un único cobro al cierre (% del valor de venta) | Un cobro al colocar (1 mes de renta) + opcional mensual recurrente si hay administración integral |
| **Documentación** | Escrituras, predial, gravámenes, identidades, estado civil, régimen fiscal | Contrato de arrendamiento, póliza jurídica o aval, comprobantes de ingreso, reglamento, inventario fotográfico |
| **Riesgo principal** | Caída del cierre por gravamen oculto o disputa de propiedad | Morosidad, daños al inmueble, conflicto entre propietario e inquilino |
| **Relación post-cierre** | Termina al firmar escritura | Comienza al firmar; dura 1–5 años con cobranza, mantenimiento, conflictos, renovación o salida |
| **Stakeholders** | Vendedor, comprador, notario | Propietario, inquilino, fiador o afianzadora, eventualmente administrador |
| **Garantía** | No aplica | Crítico (póliza jurídica, aval con propiedad o depósito ampliado) |
| **Métrica de éxito** | Operación cerrada en tiempo y al precio | Operación cerrada **+** pago puntual durante todo el contrato + renovación o re-marketing limpio |

Por eso la renta no se puede pensar como "una venta más corta". Es un proceso con una **fase post-cierre que dura meses o años** y donde la confianza, la administración y la cobertura legal son tan importantes como la captación inicial. Tratarla como una sub-vista del kanban genérico de operaciones diluye el control y esconde KPIs críticos (morosidad, tasa de renovación, días de vacancia entre contratos).

---

## 2. Modelo de tres fases

```
┌──────────────────────────────────────────────────────────────────────────┐
│                                                                          │
│   FASE 1                  FASE 2                   FASE 3                │
│   CAPTACIÓN               COLOCACIÓN               GESTIÓN POST-CIERRE   │
│   (8 stages)              (10 stages)              (8 stages)            │
│                                                                          │
│   Propietario           Inmueble en mercado     Contrato activo          │
│   firma con HDV    →    + inquilino busca   →   + RentalProcess          │
│                                                                          │
│   Output:               Output:                  Output:                 │
│   inmueble activo       contrato firmado +       contrato cumplido +     │
│   en /propiedades       inmueble entregado       renovación o            │
│                                                  desocupación limpia     │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘

         Operation               Operation                RentalProcess
         type=captacion          type=renta               (vinculado a la
         metadata.intent         (auto-spawn al           operación type=renta)
         =rental                 firmar captación)
```

Cada fase tiene su propio pipeline (stages), su propio SLA, sus propios documentos y KPIs. El CRM debe permitir ver las tres fases en vistas separadas pero también poder navegar de una a otra (un agente que abre una `RentalProcess` debe poder ver el historial de captación y colocación que lo precede).

---

## 3. Fase 1 — Captación del inmueble en renta

**Objetivo:** transformar a un propietario interesado en rentar (lead) en un inmueble activo y publicado en `/propiedades?operation_type=rental`, con contrato de captación firmado y plan de marketing en marcha.

**Tabla CRM:** `operations` con `type='captacion'` y `metadata.intent='rental'` (para distinguir de captaciones de venta).

### Stages

| # | Stage | Qué pasa | Quién | Salida |
|---|---|---|---|---|
| 1.1 | `lead_received` | Lead llega de `/renta-tu-propiedad`, referido, llamada o WhatsApp. Se crea `Client(client_type='owner', metadata.intent='rental')` y `Operation(type='captacion', stage='inquiry', metadata.intent='rental')`. | Sistema | Lead asignado |
| 1.2 | `qualification_call` | Primera llamada al propietario en menos de 30 min hábiles. Confirmar datos del brief, agendar visita técnica. | Agente captación | Visita agendada o lead descartado |
| 1.3 | `property_visit` | Visita al inmueble: medición, fotos preliminares, evaluación de estado, valuación de renta. Llenar checklist técnico. | Agente captación | Diagnóstico |
| 1.4 | `proposal_sent` | Propuesta de precio (rango basado en `/mercado` + ajustes), plan de marketing y términos de comisión enviada por escrito. | Agente captación | Propietario revisa |
| 1.5 | `agreement_signed` | Firma de contrato de captación (con o sin exclusividad). Si aplica, gestoría de documentos del inmueble. | Agente + Legal | Captación firmada |
| 1.6 | `property_preparation` | Sesión de fotos profesionales, redacción de ficha, pequeñas mejoras o home staging si aplica. Subida a `properties` con `status='active'` y `operation_type='rental'`. | Agente + Marketing | Inmueble listo para publicar |
| 1.7 | `published` | Inmueble visible en `/propiedades` con badge "Renta". Distribución a la red de inquilinos calificados. | Sistema | Inmueble en mercado |
| 1.8 | `captacion_closed` | Captación cerrada exitosamente. **Auto-spawn** de `Operation(type='renta', stage='lead', status='active')` vinculada al inmueble. | Sistema | Pipeline de colocación abierto |

### Reglas operativas de la Fase 1

- **SLA primer contacto:** 30 minutos hábiles.
- **SLA propuesta enviada:** 48 horas desde la visita técnica.
- **SLA captación firmada:** 14 días desde la propuesta.
- **Si pasa de 14 días sin firmar:** mover a stage `cold` y entrar en automation `nurturing_owner_pasivo` con cadencia mensual.
- **Si en la visita se detecta** que el inmueble necesita reparaciones mayores antes de poder rentarse, el stage se puede pausar en `property_preparation` y enlazarse al servicio Property Transformation.
- **Si el propietario marca "interés en administración integral"** en el brief inicial, agregar tag `wants_admin` al `Client` y notificar al área de administración.

### Documentos requeridos para cerrar Fase 1

- Identificación oficial del propietario (INE/pasaporte).
- Comprobante de propiedad (escrituras o título).
- Predial vigente.
- Comprobante de no adeudo de mantenimiento (si aplica condominio).
- Reglamento del condominio (si aplica) — define qué se permite (mascotas, restricciones).
- Inventario fotográfico inicial del inmueble (lo genera HDV en la visita).
- Contrato de captación firmado (genera HDV con `ContractTemplate`).

### Definition of Done (Fase 1)

- [ ] Contrato de captación firmado y archivado en `documents`.
- [ ] Inmueble creado en `properties` con todos los campos completos.
- [ ] Mínimo 8 fotos profesionales en `property_photos`.
- [ ] Ficha redactada con narrativa boutique (ver Manual de Marca, sección 12).
- [ ] Auto-spawn de `Operation(type='renta')` ejecutado.
- [ ] Notificación interna al equipo de corretaje "nuevo inmueble disponible".
- [ ] **Cuenta de portal del propietario activada** (al firmar 1.5 `agreement_signed`).
- [ ] **Documentos generados (contrato, propuesta, fotos) visibles en el portal del propietario.**
- [ ] **Email de bienvenida `portal_welcome` enviado al propietario.**

---

## 4. Fase 2 — Colocación con inquilino

**Objetivo:** llevar al inmueble desde "publicado" hasta "contrato firmado y entregado" con un inquilino calificado y garantía vigente.

**Tabla CRM:** `operations` con `type='renta'`.

### Stages

| # | Stage | Qué pasa | Quién | Salida |
|---|---|---|---|---|
| 2.1 | `lead` | Inmueble activo, esperando inquilino. | — | Lead inquilino entra |
| 2.2 | `prospect_matched` | Lead inquilino llega de `/rentar`, ficha de propiedad o referido. Sistema o agente identifica matching con inmuebles disponibles. | Sistema/Agente corretaje | Match generado |
| 2.3 | `viewing_scheduled` | Visita agendada con el inquilino. | Agente corretaje | Visita programada |
| 2.4 | `viewing_completed` | Visita realizada. Agente registra observaciones y nivel de interés. | Agente corretaje | Inquilino interesado o descartado |
| 2.5 | `application_received` | Inquilino entrega solicitud formal de renta (formato HDV) con datos personales, ingresos y referencias. | Inquilino | Documentos para validar |
| 2.6 | `tenant_qualification` | Verificación: identidad, ingresos, referencias laborales y personales, historial crediticio (si aplica), validación con afianzadora si va con póliza. | Agente + Legal | Inquilino aprobado o rechazado |
| 2.7 | `offer_presented` | Oferta formal presentada al propietario con perfil del inquilino, garantía propuesta y condiciones. | Agente corretaje | Propietario acepta o rechaza |
| 2.8 | `guarantee_processing` | Trámite de garantía: emisión de póliza jurídica, formalización de aval o coordinación de depósito ampliado. | Legal + Inquilino | Garantía vigente |
| 2.9 | `contract_signed` | Firma de contrato de arrendamiento (3 partes: propietario, inquilino, fiador/afianzadora). Recibo de depósito. | Legal + ambas partes | Contrato vigente |
| 2.10 | `property_delivered` | Entrega física del inmueble al inquilino con inventario fotográfico de salida y acta de entrega. **Auto-spawn** de `RentalProcess` para iniciar Fase 3. | Agente + Inquilino | Operación cerrada, RentalProcess activo |

### Reglas operativas de la Fase 2

- **SLA primer contacto al lead inquilino:** 60 minutos hábiles.
- **SLA agendar visita:** 48 horas desde primer contacto.
- **SLA presentar oferta al propietario:** 24 horas desde recibir documentación completa del inquilino.
- **SLA emitir garantía:** 72 horas desde aprobación del propietario.
- **Inquilinos con mascota:** sólo se le envían inmuebles donde el propietario marcó `metadata.allows_pets=true`.
- **Si la solicitud del inquilino no pasa calificación:** registrar motivo en `operation_comments` (evita re-enviarlo a otros inmuebles del mismo propietario sin revisión) y rechazar con mensaje educado al inquilino.
- **Si el propietario rechaza la oferta:** volver al stage `lead` y registrar motivo en `operation_comments`. El inquilino sigue en su propio `Client` activo y puede recibir matching con otros inmuebles.

### Documentos requeridos para cerrar Fase 2

Del inquilino:
- Identificación oficial.
- Comprobantes de ingresos (3 últimos meses) o carta de trabajo.
- Comprobante de domicilio actual.
- RFC.
- Referencias laborales y personales (mínimo 2 cada una).
- Si va con aval: copia de la escritura del inmueble del aval + identificación del aval.
- Si va con póliza: solicitud y aprobación de la afianzadora.

Del propietario:
- Aprobación firmada del inquilino propuesto.
- Copia actualizada de identificación.
- Cuenta bancaria (CLABE) para depósito de rentas (si HDV administra cobranza).

Generados en proceso:
- Contrato de arrendamiento firmado por las 3 partes.
- Recibo del depósito en garantía.
- Inventario fotográfico y acta de entrega firmada.
- Póliza jurídica vigente o aval formalizado.

### Definition of Done (Fase 2)

- [ ] Contrato de arrendamiento firmado por las 3 partes y archivado.
- [ ] Garantía vigente (póliza activa o aval formalizado).
- [ ] Depósito en garantía recibido.
- [ ] Inventario fotográfico y acta de entrega firmada.
- [ ] `properties.status` cambia a `rented`.
- [ ] Auto-spawn de `RentalProcess` ejecutado.
- [ ] Comisión liberada para aprobación (siguiente sección 11).
- [ ] **Cuenta de portal del inquilino activada** (al firmar 2.9 `contract_signed`).
- [ ] **El inquilino recibe email de bienvenida + ve su contrato, póliza, recibo de depósito y datos de pago en el portal.**
- [ ] **El propietario ve en su portal: nuevo inquilino aprobado + contrato firmado + estado de pago del primer mes.**

---

## 5. Fase 3 — Gestión post‑cierre (RentalProcess)

**Objetivo:** asegurar que el contrato se cumpla en tiempo y forma durante toda su vigencia, anticipar renovación o salida, y dejar el inmueble listo para la siguiente operación con cero conflicto.

**Tabla CRM:** `RentalProcess` (vinculada a `Operation type='renta'`).

> Esta fase aplica **siempre** que se firma un contrato de renta gestionado por HDV. El nivel de involucramiento depende de si el propietario contrató sólo colocación o colocación + administración integral.

### Stages

| # | Stage | Qué pasa | Quién | Salida |
|---|---|---|---|---|
| 3.1 | `move_in` | Mes 1 del contrato. Verificación post-entrega, walkthrough de seguimiento. | Agente o Administrador | Operación estabilizada |
| 3.2 | `active` | Contrato vigente. Cobranza mensual, atención a temas operativos. | Administrador (si aplica) | — |
| 3.3 | `monthly_billing` | Sub-stage repetitivo: emisión de recibo, registro de pago, alerta si pago atrasado. | Administrador | Pago confirmado |
| 3.4 | `incident_handling` | Sub-stage activado por demanda: reparación, reclamo, conflicto. | Administrador + Legal si escala | Incidente resuelto |
| 3.5 | `renewal_window` | 60 días antes del vencimiento del contrato. Iniciar conversación con propietario y con inquilino. | Agente + Administrador | Renovación confirmada o no |
| 3.6 | `renewal_signed` | Firma de adenda o nuevo contrato si renueva. | Legal | Contrato extendido |
| 3.7 | `move_out_scheduled` | Inquilino no renueva. Coordinar entrega de salida y revisión de inmueble. | Agente + Administrador | Move-out programado |
| 3.8 | `move_out_completed` | Inmueble desocupado. Inventario de salida, evaluación de daños, liberación o retención de depósito. **Si propietario quiere re-rentar**, se abre nueva `Operation type='renta'`. | Agente + Legal | RentalProcess cerrado |

### Reglas operativas de la Fase 3

- **SLA cobranza mensual:** recordatorio 5 días antes, recordatorio 2 días después si no paga, llamada al día 5 de retraso, ejecución de garantía al día 15.
- **SLA reportes mensuales al propietario:** primer día hábil del mes siguiente.
- **SLA respuesta a incidentes:** 4 horas hábiles para reportar al propietario, 24 horas para resolver o canalizar a proveedor.
- **SLA inicio de conversación de renovación:** 60 días antes del vencimiento.
- **SLA move-out:** 7 días desde la fecha de entrega para liberar o retener depósito con justificación.

### Documentos durante Fase 3

Generados mensualmente:
- Recibo de renta del mes.
- Reporte mensual al propietario (renta cobrada, gastos asociados, comisión, neto entregado).

Generados al renovar o salir:
- Adenda de renovación o nuevo contrato.
- Acta de move-out con inventario y evaluación de daños.
- Carta de liberación de depósito (o justificación de retención).

### Definition of Done (Fase 3, por evento)

**Por mes activo:**
- [ ] Pago de renta recibido y conciliado.
- [ ] Reporte mensual enviado al propietario antes del día 3 hábil.
- [ ] Cero incidentes abiertos sin asignar.

**Al renovar:**
- [ ] Conversación con ambas partes 60 días antes.
- [ ] Adenda firmada o nuevo contrato firmado antes del vencimiento.
- [ ] Garantía actualizada si aplica.

**Al cerrar:**
- [ ] Move-out con inventario completo.
- [ ] Liberación o retención de depósito justificada y firmada.
- [ ] Inmueble listo para nueva captación o entrega al propietario.
- [ ] **El inquilino recibe en el portal acta de move-out, carta de liberación o retención de depósito.**
- [ ] **El propietario ve en el portal el cierre del ciclo, evaluación de daños y siguiente paso (re-marketing o entrega).**

---

## 6. Vista admin propuesta — `/admin/rentas`

Hoy todo vive en `/admin/operations` (kanban genérico). Propuesta de estructura nueva:

```
/admin/rentas/
  ├── /captaciones    → Fase 1 (operations.type=captacion + metadata.intent=rental)
  ├── /activas        → Fase 2 (operations.type=renta + status=active)
  └── /gestion        → Fase 3 (RentalProcess.status in [active, renewal_window])
```

### Vista `/admin/rentas/captaciones`

- Kanban con las 8 columnas de Fase 1 (`lead_received` → `captacion_closed`).
- Cards: nombre del propietario, colonia, m², renta esperada, agente asignado, días en stage, semáforo de SLA.
- Filtros: agente, colonia, rango de renta esperada, antigüedad.
- Bulk actions: reasignar agente, mover a stage, marcar como cold.

### Vista `/admin/rentas/activas`

- Kanban con las 10 columnas de Fase 2 (`lead` → `property_delivered`).
- Cards: dirección del inmueble, foto principal, días en mercado, leads inquilinos asociados, oferta vigente si aplica.
- Filtros: agente, colonia, rango de renta, días en mercado.
- Sub-vista por inmueble: timeline completo (fase 1 → fase 2) en una sola pantalla.

### Vista `/admin/rentas/gestion`

- Lista de `RentalProcess` activas con columnas: dirección, inquilino, propietario, fecha inicio, fecha vencimiento, estatus de pago del mes en curso, días para vencer contrato.
- Tabs: `Activas`, `En renovación` (60d), `Move-out programado`, `Cerradas`.
- Alertas visibles: pagos vencidos, incidentes abiertos, contratos por vencer < 60 días.
- Vista detalle de un `RentalProcess`: timeline de pagos, incidentes, comunicaciones, documentos.

### Cambios al sidebar del CRM admin

Sección "PROCESOS" actual del sidebar pasaría a:

```
PROCESOS
├── Operaciones (venta + captación de venta)
├── Rentas
│   ├── Captaciones de renta
│   ├── Rentas activas
│   └── Gestión post-cierre
└── Opinión de Valor
```

Esto separa claramente el funnel de venta del de renta, manteniendo el genérico para casos especiales o vistas combinadas.

---

## 7. Roles y responsabilidades

| Rol | Fase 1 (Captación) | Fase 2 (Colocación) | Fase 3 (Gestión) |
|---|---|---|---|
| **Agente de captación de renta** | Dueño del proceso | Apoya con conocimiento del inmueble y del propietario | Acompañamiento en renovación |
| **Agente de corretaje de renta** | — | Dueño del proceso | Acompaña al propietario en move-out si no hay administrador |
| **Administrador de inmuebles** | Notificado si propietario marca interés | Recibe handoff cuando contrato firma con administración integral | Dueño del proceso |
| **Dirección General (legal)** | Revisa contrato de captación, valida documentación del propietario | Valida contrato de arrendamiento y póliza, firma como representante HDV | Interviene en incidentes que escalan, conflictos legales, ejecución de garantía |
| **Marketing / Property Transformation** | Genera ficha, fotos, plan de publicación | Apoyo con re-marketing si días en mercado > umbral | — |

> Roles a contratar a futuro: Coordinador de cobranza (Fase 3 cuando volumen >50 RentalProcess activas), Coordinador de mantenimiento (cuando >30 inmuebles bajo administración).

### Política anti-doble-representación

Por defecto, el mismo agente no representa propietario e inquilino en la misma operación. Si por capacidad ocurre, debe reasignarse uno de los lados antes del stage `offer_presented`, y documentarse en `operation_comments`.

---

## 8. SLAs y métricas

### SLAs por fase

| SLA | Fase 1 | Fase 2 | Fase 3 |
|---|---|---|---|
| Primer contacto | 30 min hábiles (propietario) | 60 min hábiles (inquilino) | — |
| Primer entregable | 48h (propuesta) | 24h (oferta al propietario tras docs completos) | Mensual: día 3 hábil del mes |
| Cierre del stage | 14 días para captación firmada | 21 días desde lead inquilino hasta contrato | Por evento (renovación/move-out) |

### KPIs por fase

**Fase 1 — Captación**
- Tiempo promedio lead → captación firmada (objetivo ≤ 14 días).
- Tasa de conversión lead → captación (objetivo ≥ 60%).
- Días promedio en stage `proposal_sent` (objetivo ≤ 7 días).
- % de captaciones que llegan a publicación efectiva (objetivo ≥ 90%).

**Fase 2 — Colocación**
- Días promedio en mercado de un inmueble bien presentado (objetivo ≤ 30 días).
- Tasa de conversión visita → contrato (objetivo ≥ 25%).
- Tasa de aprobación del inquilino propuesto (objetivo ≥ 70%).
- % de operaciones cerradas con póliza jurídica (objetivo ≥ 60%).
- Vacancia entre contratos para inmuebles administrados (objetivo ≤ 15 días).

**Fase 3 — Gestión**
- Tasa de morosidad mensual (objetivo ≤ 3%).
- Tiempo promedio de resolución de incidentes (objetivo ≤ 48h).
- Tasa de renovación al vencer contrato (objetivo ≥ 65%).
- NPS del propietario en gestión integral (objetivo ≥ 9/10).
- Recuperación promedio de depósito en move-out con daños (objetivo ≥ 80% del costo real de reparación).

### Dashboard ejecutivo de renta (sugerido para `/admin/analytics`)

- Inmuebles activos en mercado, días promedio, tasa de visitas/inmueble.
- Pipeline de colocación: operaciones por stage.
- RentalProcess activas, morosidad acumulada del mes, contratos por vencer en 30/60/90 días.
- Comisiones del mes (colocación + administración).

---

## 9. Documentos por etapa

Resumen consolidado de qué documento se genera, recibe o firma en cada stage. Todos viven en `documents` vinculados a la `operation` o `RentalProcess` correspondiente.

| Stage | Documento | Quién lo genera | Cuándo se valida |
|---|---|---|---|
| 1.3 `property_visit` | Checklist técnico de visita, fotos preliminares | Agente | Al cerrar visita |
| 1.5 `agreement_signed` | Contrato de captación, copia de escrituras, predial, reglamento | Legal + propietario | Antes de avanzar a 1.6 |
| 1.6 `property_preparation` | Fotos profesionales, ficha redactada, video opcional | Marketing | Antes de publicar |
| 2.5 `application_received` | Solicitud del inquilino, identificación, comprobantes ingreso, referencias | Inquilino | Antes de calificación |
| 2.6 `tenant_qualification` | Reporte de calificación (HDV) | Legal + Agente | Antes de presentar oferta |
| 2.7 `offer_presented` | Carta de oferta formal con perfil del inquilino | Agente | El propietario acepta firmando |
| 2.8 `guarantee_processing` | Póliza jurídica emitida o aval formalizado | Afianzadora o Legal | Antes de firma |
| 2.9 `contract_signed` | Contrato de arrendamiento, recibo de depósito | Legal + las 3 partes | Al firmar |
| 2.10 `property_delivered` | Inventario fotográfico de entrega, acta de entrega firmada | Agente + inquilino | Al entregar llaves |
| 3.3 `monthly_billing` | Recibo mensual, reporte mensual al propietario | Administrador | Mes a mes |
| 3.4 `incident_handling` | Bitácora de incidente, cotización, factura | Administrador | Por evento |
| 3.6 `renewal_signed` | Adenda o nuevo contrato, garantía actualizada | Legal | Antes de la fecha de vencimiento |
| 3.8 `move_out_completed` | Inventario de salida, acta de move-out, carta de liberación o retención de depósito | Agente + Legal | Antes de cerrar `RentalProcess` |

### Templates de contrato (en `contract_templates`)

- `captacion_renta_no_exclusiva`
- `captacion_renta_exclusiva`
- `arrendamiento_residencial_con_poliza`
- `arrendamiento_residencial_con_aval`
- `arrendamiento_comercial_simple`
- `adenda_renovacion`
- `acta_entrega_inmueble`
- `acta_move_out`
- `liberacion_deposito`
- `retencion_deposito_con_justificacion`

Todos generados con DomPDF a partir de variables del `Operation` y del `Client`.

---

## 10. Automatizaciones, emails y alertas

Estos workflows se manejan en el motor de automatización ya existente (`automations`, `automation_steps`, `automation_enrollments`).

### Automatización 1 — `nurturing_owner_rental_lead`
**Trigger:** nuevo `Client(client_type='owner', metadata.intent='rental')`.
- Inmediato: email transaccional `lead_rental_owner_received`, notificación interna a captación.
- Día 1: WhatsApp del agente asignado.
- Día 3: si no respondió, segundo recordatorio por WhatsApp.
- Día 7: email educativo "5 cosas a considerar antes de rentar tu inmueble".
- Día 14: llamada del agente.
- Día 21: si no engancha, mover a `cold` y entrar a cadencia mensual.

### Automatización 2 — `nurturing_renter_lead`
**Trigger:** nuevo `Client(client_type='renter')`.
- Inmediato: email `lead_renter_received` + asignación de agente.
- Día 1: primera selección curada por email/WhatsApp.
- Día 7: si no agendó visita, ajuste de brief vía WhatsApp.
- Día 14: oferta de zonas alternativas si no hay match en zonas originales.
- Día 30: alerta automática "nuevo inmueble disponible en tu zona" cuando entre al inventario.

### Automatización 3 — `rental_collection_reminder`
**Trigger:** `RentalProcess.status='active'` y aproximación de fecha de pago.
- 5 días antes del vencimiento mensual: recordatorio amistoso al inquilino.
- Día de vencimiento: confirmación por email al inquilino con CLABE.
- Día +2 si no se registra pago: recordatorio firme.
- Día +5: llamada del administrador.
- Día +10: aviso formal de mora con CC al fiador o afianzadora.
- Día +15: ejecución de la garantía.

### Automatización 4 — `rental_renewal_workflow`
**Trigger:** 60 días antes del vencimiento del contrato.
- Día -60: notificación interna al agente y al administrador.
- Día -55: email/llamada al propietario para conocer su intención (renovar / salir / cambiar precio).
- Día -50: email/llamada al inquilino para conocer su intención.
- Día -30: si ambas partes quieren renovar, generar adenda; si una sale, abrir flujo de move-out o re-marketing paralelo.
- Día -15: alerta diaria si todavía no se firmó adenda o no se programó move-out.

### Automatización 5 — `rental_monthly_report`
**Trigger:** primer día hábil de cada mes.
- Para cada `RentalProcess.status='active'`: generar PDF con estado del mes anterior (renta cobrada, gastos, comisión, neto entregado, incidentes resueltos) y enviarlo al propietario.

### Notificaciones internas (in-app + email)

- Lead nuevo → agente asignado.
- Visita agendada → agente y propietario.
- Inquilino aprobado → propietario y agente.
- Pago atrasado → administrador y agente original.
- Contrato por vencer 60 días → agente, administrador, propietario, inquilino.
- Incidente reportado → administrador y propietario (según severidad).

---

## 11. Comisiones y splits

### Estructura de comisión

| Concepto | Cuándo se cobra | Quién paga | % típico |
|---|---|---|---|
| **Comisión de colocación** | Al firmar contrato (Fase 2.9) | Propietario | 1 mes de renta |
| **Comisión de administración integral** | Mensual mientras el contrato esté activo (Fase 3) | Propietario | 6–10% de la renta mensual |
| **Comisión de renovación** | Al firmar adenda de renovación | Propietario | 50% de un mes de renta (negociable) |
| **Comisión por colocación de comprador eventual** | Si el inquilino decide comprar el inmueble después | Propietario y/o comprador | Comisión estándar de venta |
| **Recargo administrativo por incidentes mayores** | Por evento si excede el alcance | Propietario | Pactado caso por caso |

### Split interno (entre roles HDV)

| Rol | % de la comisión total que le toca |
|---|---|
| Agente de captación | 40% del cobro de Fase 2.9 |
| Agente de corretaje | 40% del cobro de Fase 2.9 |
| HDV (firma) | 20% del cobro de Fase 2.9 |
| Administrador | 60% de la comisión mensual de Fase 3 |
| HDV (firma) | 40% de la comisión mensual de Fase 3 |
| Renovación | 50% al agente original (captación o corretaje), 50% HDV |

> Estos % son la base de discusión, no son política firmada. Ana Laura y Alex deben revisar y formalizar antes de cualquier nueva contratación. Vivirán en un **anexo confidencial** al manual de operaciones del equipo.

### Liberación de comisión

- Al cerrar Fase 2 (stage `property_delivered`), la comisión se mueve a estado `pendiente de aprobación` en la tabla `commissions`.
- Aprobación por dirección dentro de 48 horas si todos los documentos están en orden.
- Pago al equipo en el siguiente ciclo quincenal o mensual.
- Si el contrato cae antes de 30 días por causa atribuible al equipo (mala calificación del inquilino, omisión de información clave), la comisión se devuelve.

---

## 12. Casos especiales y excepciones

### 12.1 Renta amueblada vs sin amueblar
Stage 1.3 (`property_visit`) debe registrar inventario de muebles incluidos. El contrato de arrendamiento incluye anexo de inventario con fotos. Move-out (3.8) requiere validar el inventario completo.

### 12.2 Renta con mascotas
- Inmuebles donde el propietario las acepta: tag `metadata.allows_pets=true` en `properties`.
- Brief del inquilino con mascota: tag `metadata.has_pets` con tipo (perro/gato/otra).
- En el matching de Fase 2, sólo se envían inmuebles con `allows_pets=true` a inquilinos con mascota.
- Contrato debe incluir cláusula de mascota: depósito adicional o cuota de limpieza al move-out.

### 12.3 Renta corporativa (empresa renta para empleado)
- Contrato a nombre de la empresa, no del individuo.
- Calificación se hace con la empresa (estados financieros, RFC, antigüedad).
- Garantía: usualmente fianza corporativa; póliza jurídica con condiciones distintas.
- Impacto fiscal distinto (IVA, retenciones).

### 12.4 Renta para extranjero
- Visa o residencia válida como ID.
- Carta laboral en español o traducida.
- Fiador local (mexicano con propiedad) o póliza jurídica con condiciones internacionales.
- Contrato bilingüe si lo requiere el inquilino.

### 12.5 Cambio de propietario durante contrato vigente
- Si el propietario vende el inmueble durante el contrato de renta, el nuevo propietario hereda el contrato vigente hasta su vencimiento.
- HDV media en la transición y comunica al inquilino.
- Si el inquilino quiere salir antes por cambio, se respeta el contrato y se negocia salida amistosa.

### 12.6 Incumplimiento del propietario (no entrega, condiciones engañosas)
- Cláusula del contrato de captación protege al inquilino: HDV interviene legalmente, devuelve pagos al inquilino y reclama al propietario.
- Cliente afectado registrado en CRM como "lista negra propietario" para evitar futuras captaciones.

### 12.7 Incumplimiento del inquilino (morosidad grave, daños)
- Día +15 de mora: ejecución de garantía con respaldo de afianzadora o aval.
- Si daños exceden depósito: HDV apoya al propietario con asesoría legal para reclamo.
- Cliente afectado registrado en lista interna para evitar futuras rentas.

### 12.8 Salida anticipada del inquilino
- Penalización según contrato (típicamente 1–3 meses de renta o el residual).
- HDV puede apoyar con re-marketing inmediato si el propietario lo autoriza, descontando del adeudo lo que se cobre del nuevo inquilino.

---

## 13. Implicaciones para Claude Code

### 13.1 Cambios al esquema de datos

Verificar y agregar si no existen:

```sql
-- 1. Permitir 'renter' en clients.client_type
-- (verificar el enum o columna actual; agregar si falta)

-- 2. Asegurar que operations.metadata pueda almacenar intent='rental'
-- (debería ser JSON ya, sólo confirmar)

-- 3. Agregar columnas a properties si no existen:
ALTER TABLE properties
  ADD COLUMN allows_pets BOOLEAN DEFAULT FALSE,
  ADD COLUMN is_furnished ENUM('full','partial','none') DEFAULT 'none',
  ADD COLUMN minimum_lease_months INT DEFAULT 12,
  ADD COLUMN included_services JSON NULL; -- {agua, gas, internet, mantenimiento, etc.}

-- 4. Verificar que rental_processes tenga columnas para Fase 3:
-- monthly_rent, deposit_amount, lease_start, lease_end, renewal_window_start,
-- next_payment_due, payment_status, current_stage, has_administration

-- 5. Agregar índices útiles:
CREATE INDEX idx_rental_processes_status_stage ON rental_processes(status, current_stage);
CREATE INDEX idx_rental_processes_next_payment ON rental_processes(next_payment_due) WHERE status = 'active';
CREATE INDEX idx_operations_type_intent ON operations(type, ((metadata->>'$.intent')));
```

### 13.2 Nuevas vistas admin

Crear:

- `app/Http/Controllers/Admin/RentalsController.php` con métodos:
  - `captaciones()` → `/admin/rentas/captaciones`
  - `activas()` → `/admin/rentas/activas`
  - `gestion()` → `/admin/rentas/gestion`
  - `show(RentalProcess $rental)` → `/admin/rentas/gestion/{id}`
- `resources/views/admin/rentals/` con `captaciones.blade.php`, `activas.blade.php`, `gestion.blade.php`, `show.blade.php`.

### 13.3 Modelo `Operation` — Scopes y accessors

```php
// app/Models/Operation.php
public function scopeRentalCaptures($query) {
    return $query->where('type', 'captacion')
                 ->whereJsonContains('metadata->intent', 'rental');
}

public function scopeActiveRentals($query) {
    return $query->where('type', 'renta')->where('status', 'active');
}

public function isRental(): bool {
    return $this->type === 'renta'
        || ($this->type === 'captacion' && data_get($this->metadata, 'intent') === 'rental');
}
```

### 13.4 Auto-spawn de operación de renta al firmar captación

En el listener o servicio que maneja la transición de `captacion` a `captacion_closed`:

```php
if ($operation->type === 'captacion'
    && data_get($operation->metadata, 'intent') === 'rental'
    && $operation->stage === 'captacion_closed') {

    $rentalOp = Operation::create([
        'type' => 'renta',
        'stage' => 'lead',
        'status' => 'active',
        'property_id' => $operation->property_id,
        'client_id' => $operation->client_id, // owner
        'user_id' => null, // se asigna por round-robin de corretaje
        'parent_operation_id' => $operation->id,
    ]);

    // notificar al equipo de corretaje
    Notification::create([...]);
}
```

### 13.5 Auto-spawn de RentalProcess al entregar inmueble

```php
if ($operation->type === 'renta' && $operation->stage === 'property_delivered') {
    RentalProcess::create([
        'operation_id' => $operation->id,
        'property_id' => $operation->property_id,
        'tenant_client_id' => $operation->client_id, // renter
        'owner_client_id' => $operation->property->owner_client_id,
        'lease_start' => now(),
        'lease_end' => now()->addMonths($operation->lease_duration_months),
        'monthly_rent' => $operation->monthly_rent,
        'deposit_amount' => $operation->deposit_amount,
        'has_administration' => data_get($operation->metadata, 'has_administration', false),
        'status' => 'active',
        'current_stage' => 'move_in',
    ]);
}
```

### 13.6 Job programado de renovación

```php
// app/Jobs/CheckRentalRenewals.php (síncrono, sin ShouldQueue)
public function handle() {
    $sixtyDaysFromNow = now()->addDays(60);

    RentalProcess::where('status', 'active')
        ->where('lease_end', '<=', $sixtyDaysFromNow)
        ->where('current_stage', 'active')
        ->each(function ($rp) {
            $rp->update(['current_stage' => 'renewal_window']);
            // disparar automation_5_renewal_workflow
            AutomationEnrollment::create([
                'automation_id' => Automation::where('slug', 'rental_renewal')->first()->id,
                'enrollable_id' => $rp->id,
                'enrollable_type' => RentalProcess::class,
            ]);
        });
}
```

Agregar al scheduler (`routes/console.php`):

```php
Schedule::job(new CheckRentalRenewals)->daily();
```

### 13.7 Cobranza mensual

```php
// app/Jobs/ProcessMonthlyRentalBilling.php
public function handle() {
    $today = now()->day;

    RentalProcess::where('status', 'active')
        ->where('payment_day', $today)
        ->each(function ($rp) {
            // enviar recordatorio + crear registro pendiente
            Transaction::create([
                'type' => 'income',
                'category' => 'rent',
                'amount' => $rp->monthly_rent,
                'related_to' => $rp,
                'status' => 'pending',
                'due_date' => now(),
            ]);
            // disparar automation_3_collection_reminder
        });
}
```

### 13.8 Verificación de accesos

- Agentes de corretaje sólo pueden ver `RentalProcess` que les fueron asignadas o están en pool de corretaje.
- Administrador ve todas las `RentalProcess` con `has_administration=true`.
- Dirección General ve todo.

### 13.9 Estado actual del CRM (verificar antes de implementar)

Antes de tocar código, confirmar en `app/Models/`:

- [ ] `Operation` tiene los stages `property_visit`, `proposal_sent`, `agreement_signed`, `property_preparation`, `published`, `captacion_closed` (o similares) en su lista de stages permitidos.
- [ ] `RentalProcess` tiene los stages `move_in`, `active`, `renewal_window`, `move_out_scheduled`, `move_out_completed`.
- [ ] `Property` tiene `operation_type='rental'` permitido.
- [ ] `ContractTemplate` tiene templates de renta (si no, crearlos siguiendo la lista de la sección 9).
- [ ] `Automation` tiene los 5 workflows listados o requiere crearlos.

Si alguna de estas piezas falta, preguntar a Alex antes de implementar para no duplicar lógica.

---

## 14. Checklist de implementación (para Claude Code)

### Fase 1 — Vista y datos (1 semana)

- [ ] Crear ruta y controlador `/admin/rentas/captaciones`, `/admin/rentas/activas`, `/admin/rentas/gestion`.
- [ ] Crear vistas Blade con kanban por stage (sección 6).
- [ ] Agregar al sidebar del CRM la nueva sección "Rentas" con sus 3 sub-vistas.
- [ ] Verificar y migrar columnas faltantes en `properties`, `rental_processes`, `clients`.
- [ ] Asegurar que el filtro `operation_type=rental` funcione en `/propiedades`.

### Fase 2 — Pipeline operativo (2 semanas)

- [ ] Implementar auto-spawn de `Operation type='renta'` al cerrar captación de renta.
- [ ] Implementar auto-spawn de `RentalProcess` al entregar inmueble.
- [ ] Crear los 10 templates de contrato listados en sección 9.
- [ ] Implementar checklist por stage (Fase 1, Fase 2, Fase 3) usando `StageChecklistTemplate` existente.

### Fase 3 — Automatizaciones (1 semana)

- [ ] Crear las 5 automatizaciones de la sección 10 (`nurturing_owner_rental_lead`, `nurturing_renter_lead`, `rental_collection_reminder`, `rental_renewal_workflow`, `rental_monthly_report`).
- [ ] Crear los 2 jobs programados (`CheckRentalRenewals`, `ProcessMonthlyRentalBilling`).
- [ ] Crear las plantillas de email transaccional (`lead_renter_received`, `lead_rental_owner_received`, `rental_match_notification`, `rental_payment_reminder`, `rental_monthly_report`).

### Fase 4 — Reporting y métricas (1 semana)

- [ ] Crear widget en `/admin/analytics` con KPIs de renta (sección 8).
- [ ] Crear vista detalle de `RentalProcess` con timeline completo.
- [ ] Crear export mensual de reportes a propietarios (PDF con DomPDF).

### Fase 5 — Pulido (continuo)

- [ ] Ajustar UX del kanban con drag & drop entre stages.
- [ ] Notificaciones in-app para cada transición relevante.
- [ ] Permisos y roles validados.
- [ ] Tests unitarios de los auto-spawn y de los jobs programados.

---

---

## 14. Cómo se vive cada fase en el Portal del Cliente

> El proceso operativo (secciones 3–5) describe lo que pasa internamente. Esta sección describe **lo que el cliente ve y hace** en cada momento. Es la traducción del proceso a experiencia de cliente. Spec del portal: [`06-PORTAL-DEL-CLIENTE.md`](./06-PORTAL-DEL-CLIENTE.md).
>
> Regla: **cada hito interno tiene una contraparte visible en el portal**. Si un cliente debería verlo, el portal lo muestra.

### 14.1 Fase 1 — Lo que ve el propietario

| Stage interno | Qué pasa en el portal del propietario |
|---|---|
| 1.1 `lead_received` | (Aún no hay cuenta de portal — el lead se gestiona desde sitio público y CRM admin.) |
| 1.2 `qualification_call` | (Aún sin portal.) |
| 1.3 `property_visit` | (Aún sin portal; el agente registra la visita en el CRM.) |
| 1.4 `proposal_sent` | (Aún sin portal; el propietario recibe la propuesta por email/WhatsApp.) |
| **1.5 `agreement_signed`** | **Disparador: se crea la cuenta de portal del propietario.** Email `portal_welcome` con link de activación. Al activar, ve: contrato de captación firmado, propuesta de marketing, agente asignado con foto y datos. |
| 1.6 `property_preparation` | El propietario ve en el portal: galería de fotos profesionales en cuanto se suben, ficha redactada para revisión. |
| 1.7 `published` | Aparece banner "Tu inmueble está publicado" con link a la página pública (`/propiedades/{id}/{slug}`). Ve estadísticas básicas: días en mercado, visitas a la página. |
| 1.8 `captacion_closed` | El portal cambia el estado del inmueble a "En mercado" y muestra "Buscando inquilino calificado" como próximo paso. |

### 14.2 Fase 2 — Lo que ve el propietario y el inquilino

#### Para el propietario (que tiene cuenta desde Fase 1.5)

| Stage interno | Qué pasa en el portal del propietario |
|---|---|
| 2.1 `lead` | El portal muestra "Inmueble activo · Buscando inquilino · X días en mercado". Sin acción del propietario por ahora. |
| 2.2 `prospect_matched` | (Interno; el propietario no ve nombres de inquilinos prospectos hasta que pase calificación.) |
| 2.3 `viewing_scheduled` | El portal muestra "Visita agendada para fecha X". El propietario puede coordinar acceso o autorizar al agente con llaves. |
| 2.4 `viewing_completed` | El portal registra "Visita realizada · Notas del agente". |
| 2.5 `application_received` | El portal NO muestra documentos personales del prospecto; sólo "Solicitud recibida · En calificación". |
| 2.6 `tenant_qualification` | El portal muestra "Calificando perfil · Resultado en 24 horas". |
| 2.7 `offer_presented` | **Notificación importante.** El portal muestra al propietario una **carta de oferta resumida**: perfil del inquilino (sin datos sensibles), garantía propuesta, condiciones, fecha sugerida. CTA: **Aceptar / Pedir cambios / Rechazar**. La acción del propietario aquí es central. |
| 2.8 `guarantee_processing` | El portal muestra "Trámite de garantía en curso · Fecha estimada de firma". |
| 2.9 `contract_signed` | El portal muestra contrato firmado descargable, recibo de depósito, datos del inquilino aprobado (filtrados según privacidad). |
| 2.10 `property_delivered` | El portal cambia a vista de "Renta activa" con datos del contrato. |

#### Para el inquilino (cuenta creada en 2.9 `contract_signed`)

| Stage interno | Qué pasa en el portal del inquilino |
|---|---|
| Antes de 2.9 | (No tiene cuenta de portal; comunicación pasa por sitio público + WhatsApp + email del agente.) |
| **2.9 `contract_signed`** | **Disparador: se crea la cuenta de portal del inquilino.** Email `portal_welcome` con link. Al activar, ve: contrato firmado, póliza jurídica activa o aval formalizado, datos para depositar el primer mes, fecha de entrega. |
| 2.10 `property_delivered` | El portal muestra inventario fotográfico de entrega, acta firmada, reglamento del condominio. CTA: **Reportar incidente** disponible desde día 1. |

### 14.3 Fase 3 — Lo que ven propietario e inquilino durante toda la vigencia

#### Para el inquilino

| Stage interno | Qué pasa en el portal del inquilino |
|---|---|
| 3.1 `move_in` | Walkthrough de bienvenida en el portal: "Tu primer mes empieza el [fecha]. Próximo pago: [fecha]. Datos: [CLABE]". |
| 3.2 `active` | Vista permanente con: contrato vigente, días para vencer, próximo pago destacado, recibos descargables, contacto del agente y administrador, botón "Reportar incidente". |
| 3.3 `monthly_billing` | 5 días antes del pago: notificación in-portal + email "Tu pago vence el [fecha]". Día del vencimiento: confirmación con CLABE y referencia. Tras pago confirmado: recibo descargable disponible inmediato. |
| 3.4 `incident_handling` | El inquilino reporta incidente desde el portal (formulario con fotos, urgencia). Recibe seguimiento del estado (asignado / en proceso / resuelto) con timestamps. |
| 3.5 `renewal_window` | 60 días antes: banner "Tu contrato vence el [fecha]. ¿Quieres renovar?". CTA: Sí, quiero renovar / No, programaré move-out / Necesito hablar con HDV. |
| 3.6 `renewal_signed` | El portal muestra adenda firmada y nueva fecha de vencimiento. |
| 3.7 `move_out_scheduled` | El portal muestra fecha de move-out coordinada y checklist (qué llevar, qué dejar, datos para devolución del depósito). |
| 3.8 `move_out_completed` | El portal muestra acta de move-out, evaluación de daños (si aplica), carta de liberación o retención del depósito. La cuenta queda activa como **alumni** con histórico accesible. |

#### Para el propietario

| Stage interno | Qué pasa en el portal del propietario |
|---|---|
| 3.1–3.2 | Vista de la renta activa con: foto del inmueble, datos del inquilino (nombre completo permitido aquí, sin datos personales sensibles fuera de contrato), próximo pago, días de contrato restantes. |
| 3.3 `monthly_billing` | Notificación cuando se confirma el pago del mes. Si el inquilino se atrasa, alerta progresiva (día +5, +10, +15). El propietario ve el estado en tiempo real. |
| 3.4 `incident_handling` | Notificación cuando el inquilino reporta incidente. Si tiene **administración integral**, el portal muestra cómo HDV está resolviendo y costo asociado. Si NO tiene administración, recibe notificación con detalle del incidente y CTA "¿Quieres que HDV lo resuelva?". |
| Día 3 hábil del mes | **Reporte mensual descargable** automático en PDF: renta cobrada, gastos asociados (si aplica), comisión, neto entregado. |
| 3.5 `renewal_window` | Banner "Tu contrato vence el [fecha]". CTA: ¿Renovar al mismo precio? · Renovar con ajuste · No renovar / re-marketing. |
| 3.6 `renewal_signed` | Adenda visible en el portal del propietario. |
| 3.8 `move_out_completed` | Acta de move-out, inventario, evaluación de daños, decisión sobre depósito. CTA: "¿Quieres re-rentar este inmueble?" → genera nueva captación con datos heredados. |

### 14.4 Acciones que el cliente puede tomar desde el portal en cualquier fase

- **Descargar cualquier documento** propio (contratos, recibos, reportes, escrituras subidas).
- **Subir un documento** que HDV le solicitó (identificación actualizada, comprobante de ingreso, factura, etc.).
- **Mandar mensaje** al thread con HDV (responde el agente o administrador asignado).
- **Marcar urgencia** en un mensaje (alerta inmediata al equipo).
- **Reportar incidente** (sólo inquilino, durante Fase 3).
- **Pedir cita** (cuando se integre calendario, fase 6 del Roadmap del Portal).
- **Pagar** (cuando se integre pasarela, fase 6).
- **Editar datos personales y preferencias de notificación**.
- **Cerrar sesión / reset password**.

### 14.5 Notificaciones del portal por fase (resumen)

| Cuándo | Para quién | Tipo |
|---|---|---|
| Fase 1.5 firma | Propietario | Email + creación de cuenta de portal |
| Fase 1.6 fotos listas | Propietario | Notificación in-portal |
| Fase 1.7 publicado | Propietario | Notificación in-portal + email |
| Fase 2.3 visita agendada | Propietario | Notificación in-portal + email |
| Fase 2.7 oferta presentada | Propietario | Notificación in-portal + email + WhatsApp |
| Fase 2.9 contrato firmado | Inquilino | Email + creación de cuenta de portal |
| Fase 2.9 contrato firmado | Propietario | Notificación in-portal + email |
| Fase 2.10 entrega | Inquilino | Notificación in-portal "Bienvenido a tu nuevo hogar" |
| Fase 3.3 recordatorio de pago | Inquilino | In-portal + email + (WhatsApp opcional) |
| Fase 3.3 pago confirmado | Inquilino + Propietario | In-portal + email |
| Fase 3.3 pago atrasado | Inquilino | In-portal + email + WhatsApp progresivo |
| Fase 3.4 incidente reportado | Propietario (y administrador si aplica) | In-portal + email |
| Día 3 hábil de cada mes | Propietario | Email "Tu reporte mensual está listo" + disponible en portal |
| Fase 3.5 ventana de renovación | Inquilino + Propietario (60d antes) | In-portal + email |
| Fase 3.8 move-out | Ambos | In-portal + email |

### 14.6 Reglas operativas que el equipo debe respetar

- **Cero documentos por WhatsApp.** Si HDV genera un documento (contrato, recibo, reporte), va al portal. WhatsApp se usa para avisar "ya está en tu portal", no para enviar el archivo.
- **Pagos siempre confirmados en el portal**, aunque el cliente pague por transferencia bancaria fuera del portal. El registro vive ahí.
- **Comunicación operativa importante (cambio de fecha, decisión del propietario, aprobación) queda en el thread del portal**, aunque también se mande WhatsApp para notificar.
- **Vista "preview as client" obligatoria antes de avanzar al cliente a un nuevo stage.** El agente abre el portal como cliente para verificar que la información que el cliente verá esté correcta antes de mover el stage.
- **Si el cliente reporta que "no ve" algo, el agente lo verifica vía preview as client antes de asumir un bug.**

---

**Fin del documento.**

Cualquier ajuste sustantivo (cambio en stages, nuevos roles, nuevos KPIs, nuevas vistas del portal) requiere actualizar este documento antes de implementarlo. Si surge un caso especial repetido, agregarlo a la sección 12 y reflejarlo en el código.

**Mantenedor:** Alex.
**Próxima revisión sugerida:** mensual durante los primeros 6 meses de operación, luego trimestral.
