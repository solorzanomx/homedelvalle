# Revisión en 3 Capas · `05-PROCESO-DE-RENTA.md`
## Aplicación del playbook 00-PLAYBOOK-REVISION-3-CAPAS.md

> **Documento revisado:** `docs/05-PROCESO-DE-RENTA.md` (versión v0, 872 líneas, abril 2026).
> **Fecha de revisión:** 2026-04-29.
> **Responsable de la revisión:** Alex (con asistencia de Claude / análisis sistemático).
> **Próxima revisión:** trimestral o tras la implementación del Track B del prompt para Claude Code.

---

## Veredicto general

| Capa | Estado | Implicación |
|---|---|---|
| Capa 1 — Especialista Inmobiliario | ⚠️ Avanzar con observaciones | El proceso es sólido en su columna vertebral pero le faltan 4 casos atípicos frecuentes y ajustes de SLAs en 2 puntos. Alex y Ana Laura deben validar y ajustar antes de implementación. |
| Capa 2 — UX Architect | ⚠️ Avanzar con observaciones | El mapa del cliente está bien cubierto en sección 14 pero hay 3 huecos de experiencia (gap pre-portal, no-respuesta, sin match prolongado) que requieren diseño explícito. |
| Capa 3 — Código | ✅ Listo para ejecutar | La sección 13 es sólida; sólo falta detallar la API portal↔admin y los webhooks externos. Se puede arrancar ya. |

**Recomendación:** ejecutar las 12 acciones priorizadas de la sección "Acciones recomendadas" antes de implementar Track B en producción. Algunas son ediciones del documento, otras son decisiones que Ana Laura debe tomar.

---

## Capa 1 — Especialista Inmobiliario

### A. Realidad operativa — ⚠️ Observaciones

**Lo que está bien:**
- Las 3 fases (captación → colocación → gestión post-cierre) reflejan correctamente la operación de un corredor boutique en Benito Juárez.
- La separación de captación de renta (`metadata.intent='rental'`) del genérico de venta es alineada con la práctica real.
- Stages 1.3 (`property_visit`) y 1.4 (`proposal_sent`) están en orden correcto: visita técnica antes de propuesta de precio.

**Lo que falta o está mal:**
1. **Stage de "ir / no ir" antes de la propuesta.** Hoy el flujo asume que si HDV visita, automáticamente lleva propuesta. En la realidad, después de la visita técnica el agente decide si HDV toma o no el inmueble (precio inflado, condiciones inviables, perfil del propietario problemático). Falta un stage `internal_review` entre 1.3 y 1.4 donde HDV decide internamente avanzar o declinar amistosamente.

2. **Falta verificación de buró de crédito en Fase 2.6 (`tenant_qualification`).** En el mercado residencial CDMX, particularmente en BJ con tickets > $20K/mes, el buró es práctica común. El documento menciona "historial crediticio (cuando aplica)" — debe ser obligatorio para rentas > $25K/mes, opcional debajo.

3. **Falta walkthrough técnico de mantenimiento al firmar captación.** Stage 1.5 (`agreement_signed`) salta directo a 1.6 (`property_preparation`). Debería existir una mini-revisión documentada de "qué se entrega al inquilino y en qué estado" — esto previene disputas en el move-out.

### B. Tiempos y SLAs — ⚠️ Observaciones

**Lo que está bien:**
- Primer contacto al propietario en 30 min: agresivo pero alcanzable para boutique.
- 60 min al inquilino: razonable.
- 24 horas para presentar oferta tras docs completos: realista.
- 14 días para captación firmada: bien para mercado residencial.

**Lo que falta o está mal:**
1. **SLA de 72h para emisión de garantía es optimista.** Las afianzadoras serias (Sofimex, Insurgentes, etc.) tardan 3–7 días hábiles para emitir póliza, especialmente con buró del inquilino. Cambiar a "5 días hábiles desde aprobación del propietario" o segmentar: 72h si el inquilino tiene historial pre-aprobado, 5–7 días si es primera vez.

2. **No hay SLA para "decisión interna ir/no ir" propuesto en 1.A.** Si se incorpora el stage `internal_review`, debería tener SLA propio (24h desde la visita técnica).

3. **SLA del propietario para responder propuesta** (1.4 → 1.5) no está definido. La realidad es que los propietarios tardan días en decidir. Recomendado: 7 días hábiles, después de los cuales se mueve a `cold` y entra en nurturing.

### C. Comisiones, splits y modelo económico — ⚠️ Observación

**Lo que está bien:**
- Comisión de colocación 1 mes de renta: estándar CDMX.
- Administración integral 6–10% mensual: en rango de mercado (5–12%).
- Split 40/40/20 (captación/corretaje/HDV): conservador y alineado con prácticas boutique.
- Liberación condicionada a Fase 2.10: correcto.

**Lo que falta o está mal:**
1. **Comisión de renovación al 50% es alta.** El mercado CDMX típicamente cobra 30–50% según el caso. Recomendado: 30% por default, escalando a 50% si hubo intervención compleja (renegociación de precio, mediación de conflictos).

2. **Falta política de comisiones en casos atípicos:** ¿qué pasa si el inquilino sale antes de 6 meses? ¿qué pasa si HDV captura y otro broker coloca? Hoy no está documentado y genera ruido en el equipo.

3. **Falta comisión de "ruptura amistosa anticipada"** — cuando el contrato se cancela antes del plazo y HDV media. Mercado típico: 50% de un mes de renta.

### D. Documentos legales obligatorios — ⚠️ Observaciones

**Lo que está bien:**
- Lista del inquilino (sección 4): identificación, comprobante ingresos, comprobante domicilio, RFC, referencias, aval o póliza. Cubre lo esencial.
- Lista del propietario (sección 3): identificación, escrituras, predial, no adeudo de mantenimiento, reglamento. Bien.
- Templates de contrato (sección 9): los 10 listados son razonables.

**Lo que falta:**
1. **Comprobante de no adeudo de servicios** (luz, agua, gas) — práctica obligatoria en BJ para evitar que el inquilino herede deudas. Falta en lista del propietario.

2. **Identificación del fiador con vigencia y no vencida** — específico, falta mencionar.

3. **Carta de exposición de motivos del inquilino** — para rentas > $40K/mes o corporativas, donde la afianzadora la pide. Falta mencionar.

4. **Para extranjeros, falta:** copia visa/residencia válida, FM3 si aplica, RFC con homoclave.

5. **Falta template de "Acta de move-in"** distinta del "acta de entrega" — formalmente es la misma pero con observaciones del inquilino al recibir, no sólo del agente al entregar.

### E. Pólizas, garantías y cobertura — ✅ Mayormente OK

**Lo que está bien:**
- Distinción clara entre póliza jurídica, aval con propiedad y depósito ampliado.
- Mención explícita de "afianzadoras autorizadas y reconocidas".

**Lo que falta:**
1. **Tope de cobertura típico no especificado.** Las pólizas residenciales típicas cubren 12–18 meses de renta. Documentarlo.

2. **¿Qué pasa si la afianzadora rechaza al inquilino propuesto?** Falta el ramaje del flujo: ¿se vuelve al stage `application_received`? ¿se ofrece aval como plan B? Documentar.

3. **Falta mencionar fianza compañía afianzadora vs fianza individual** (en renta corporativa).

### F. Riesgos operativos cubiertos — ⚠️ Observaciones

**Lo que está bien:**
- Mora bien escalada por días (sección 10, automation 3).
- Daños cubiertos en move-out con inventario.
- Conflictos cubiertos en sección 12 con los principales casos.

**Lo que falta (riesgos frecuentes en BJ):**
1. **Subarriendo no autorizado** — el inquilino subarrienda la habitación o el inmueble completo sin permiso. Falta cláusula contractual y proceso de detección/sanción.

2. **Uso distinto al pactado** — residencial usado como Airbnb, oficina o local. Frecuente en zonas como Roma Sur y Nápoles.

3. **Inquilino que se va sin avisar** ("ghosting") — distinto de salida anticipada formal. Cobertura: aviso al fiador + ejecución de garantía + cobranza extrajudicial.

4. **Caída a último minuto antes de firmar contrato** — el propietario o el inquilino se arrepienten en stage 2.9. Falta protocolo de reembolso de gastos, comisiones perdidas y reactivación del flujo.

### G. Casos atípicos contemplados — ⚠️ Observaciones

**Cubiertos (sección 12):**
- Amueblada vs sin amueblar ✅
- Mascotas ✅
- Renta corporativa ✅
- Extranjero ✅
- Cambio de propietario durante contrato ✅
- Incumplimiento del propietario ✅
- Incumplimiento del inquilino ✅
- Salida anticipada ✅

**Falta agregar:**
1. **Copropiedad** (varios dueños del inmueble) — autorización de todos los copropietarios, distribución de renta y comisión, casos de disputa entre copropietarios.

2. **Sucesión vigente** — el propietario renta mientras se resuelve sucesión. Riesgo legal si la sucesión se resuelve a favor de tercero.

3. **Renta con opción a compra** (lease-to-own) — cláusulas, valuación al momento de ejercer opción, comisión adicional.

4. **Renta vacacional/temporada corta** — aunque el documento dice "no es nuestro foco", debe haber al menos un párrafo de cuándo se acepta excepcionalmente y cómo (plataformas, registro fiscal, comisión).

5. **Subarriendo solicitado formalmente** — caso legal donde el inquilino quiere subarrendar parte del inmueble (ej. una habitación). Procedimiento para autorizar y los nuevos contratos.

6. **Propietario fuera de México** — apoderado legal, contratos a distancia, manejo de pagos en USD.

### H. Stakeholders y sus tiempos — ⚠️ Observaciones

**Cubiertos:**
- Agente de captación, agente de corretaje, administrador, dirección general, marketing.

**Falta:**
1. **Notario** — sólo se menciona implícitamente. Definir cuándo interviene (rentas largas con cláusula notarial, propiedades en sucesión, copropiedad). SLA esperado.

2. **Afianzadora como stakeholder formal** — hoy se menciona como proveedor pero no como stakeholder con SLAs. Definir cuál(es) afianzadora(s) son socios preferidos, qué documentación entregamos, qué SLA tienen.

3. **Apoderado legal del propietario** (si aplica) — proceso de validación del poder, cómo firma a distancia.

4. **Notificador / abogado externo en mora** — quién hace las notificaciones formales si el inquilino entra en mora >30 días, costo y proceso.

### I. Métricas reales medibles — ⚠️ Observación

**Lo que está bien:**
- KPIs sólidos por fase.
- Cubren tiempo, calidad y dinero.
- "Tasa de aprobación de inquilino" es un buen indicador de calidad de filtro.

**Lo que falta:**
1. **NPS del propietario al move-out** — distinto del NPS al cierre del primer mes. Indica satisfacción a 12+ meses.

2. **Costo de adquisición de inquilino (CAC)** — leads de `/rentar` / inquilinos colocados, segmentado por canal.

3. **Lifetime Value del propietario** — propietarios con múltiples ciclos de renta o que abren venta posterior.

4. **% de renovaciones sin re-marketing** — indicador de salud del contrato; alto = bien gestionado.

5. **Días promedio de vacancia entre contratos** (para inmuebles administrados) — menos = mejor.

### J. Diferenciación boutique — ⚠️ Observaciones

**Lo que está bien:**
- "Pocos inmuebles" se respeta en la captación selectiva.
- Calificación seria del inquilino diferencia.
- Administración integral como combo es boutique.

**Lo que falta explicitar como diferenciador:**
1. **"No inflamos precio para captar"** — hoy implícito. Hacerlo explícito: HDV declina captaciones donde el propietario insiste en precio fuera de mercado, como filtro de calidad.

2. **"No aceptamos cualquier inmueble"** — checklist de "no captamos si": estado físico crítico, problemas legales irresolubles, propietario con historial conflictivo, ubicación fuera de zona estratégica. Hoy no está en el documento.

3. **"Acompañamiento legal premium incluido"** — Ana Laura interviene en operaciones complejas sin costo adicional. Esto no lo hace una inmobiliaria de volumen.

4. **"Cero inquilinos descartados sin retroalimentación"** — cuando un inquilino no califica, HDV le explica por qué y cómo podría calificar en el futuro. Diferenciador humano que retiene confianza.

### Capa 1 — Resumen

✅ Avanzar a Capa 2 con las siguientes 12 observaciones a resolver:

1. Agregar stage `internal_review` entre 1.3 y 1.4 (decisión interna ir/no ir).
2. Reglamentar buró de crédito según ticket de renta.
3. Agregar walkthrough técnico de mantenimiento en 1.5.
4. Ajustar SLA de garantía a 5 días hábiles (segmentado por historial).
5. Agregar SLA del propietario para responder propuesta (7 días).
6. Bajar comisión de renovación default a 30% (50% en casos complejos).
7. Documentar política de comisiones en casos atípicos.
8. Agregar documentos faltantes (no adeudo de servicios, exposición de motivos, etc.).
9. Especificar tope de cobertura de pólizas y proceso de rechazo de afianzadora.
10. Agregar 4 riesgos operativos (subarriendo, uso distinto, ghosting, caída a último minuto).
11. Agregar 6 casos atípicos (copropiedad, sucesión, lease-to-own, vacacional, subarriendo formal, propietario fuera de México).
12. Agregar stakeholders faltantes (notario, afianzadora, apoderado, notificador).

---

## Capa 2 — UX Architect

### A. Mapa del usuario — ⚠️ Observación

**Lo que está bien:**
- Sección 14 cubre exhaustivamente lo que ve cada perfil (propietario, inquilino) en cada fase.
- Las acciones del cliente están listadas (sección 14.4).

**Lo que falta:**
1. **Journey map visual** del propietario y del inquilino. El documento es texto exhaustivo pero sin diagrama de flujo. Recomendado: diagrama tipo "Customer Journey Map" con stages, emociones esperadas, puntos de fricción y oportunidades de deleite.

2. **Touchpoints fuera del portal** no están mapeados. Ej: ¿qué pasa cuando el inquilino llama al agente sin abrir el portal? ¿qué pasa cuando llega un correo del banco confirmando depósito? Esos son touchpoints reales que hoy quedan implícitos.

### B. Punto de entrada — ⚠️ Observación

**Lo que está bien:**
- Origen de leads bien especificado en sección 3.

**Lo que falta:**
1. **¿Qué pasa con leads pre-portal?** El cliente que entra por `/renta-tu-propiedad` y aún no firma captación está en stages 1.1–1.4 sin acceso al portal. ¿Cómo recibe información, propuesta, fechas de visita? Hoy queda implícito en email/WhatsApp pero el flujo no lo documenta.

2. **Leads de ficha de propiedad** — cuando un inquilino abre la ficha de un inmueble específico y deja brief, ¿el flujo de búsqueda asistida pierde ese contexto? Falta documentar la persistencia del `property_id` en el lead.

3. **Leads referidos** — cuando un cliente alumni refiere a un nuevo cliente, ¿cómo se preserva la relación de referido y qué premios recibe? Falta integración con `referrers` / `referrals`.

### C. Onboarding y primeros 5 minutos — ⚠️ Observación

**Lo que está bien:**
- Sección 14.1 documenta lo que ve el propietario al activar cuenta en stage 1.5.
- Sección 14.2 (inquilino) documenta lo que ve al activar en stage 2.9.

**Lo que falta:**
1. **Onboarding pre-portal** (stages 1.1–1.4 para propietario, 2.1–2.8 para inquilino). El cliente no tiene cuenta de portal aún pero está en proceso. ¿Qué nivel de información recibe? ¿Cómo se le mantiene comprometido sin abandonar?

2. **Diagrama del primer login al portal** — qué pantalla ve, qué hace primero, qué decisión toma. El manual de marca lo cubre parcialmente (modal de bienvenida) pero falta pasarela visual.

3. **Onboarding del agente que recibe el lead** — del lado HDV. Cuando entra un lead de `/renta-tu-propiedad`, el agente asignado recibe notificación, ¿pero qué hace en sus primeros 5 minutos? ¿Hay checklist?

### D. Comunicación clave por fase — ⚠️ Observación

**Lo que está bien:**
- Tabla 14.5 está muy bien construida con cuándo, para quién y por qué canal.
- Cobertura completa de los hitos críticos.

**Lo que falta:**
1. **Cliente que NO responde** — si el lead no contesta WhatsApp del agente en 24h, 48h, 1 semana, ¿qué pasa visualmente para él y para HDV? ¿Pausa silenciosa? ¿Comunicación de re-engagement? El documento no lo aclara.

2. **Inquilino con visita confirmada que no llega** (no-show) — falta protocolo: notificación al propietario, oportunidad de reagendar, registro en buró interno.

3. **Cancelación / pausa del proceso** — ¿qué pasa si el propietario decide pausar la captación a mitad del proceso? ¿Qué notificación recibe, qué puede hacer en el portal?

### E. Lectura cognitiva — ⚠️ Observación

**Lo que está bien:**
- Tabla de etiquetas humanas para stages (manual de marca sección 16.8).
- Estructura de dashboard del portal con cards prioritarias.

**Lo que falta:**
1. **Test de "3 segundos" no realizado** — el documento describe lo que el cliente verá pero no se ha validado con usuarios reales si entienden lo más importante en 3 segundos. Recomendado: hacer prueba con 3 propietarios actuales y 3 inquilinos antes del go-live.

2. **Pantallas de decisión crítica** mal jerarquizadas — ej. cuando el propietario recibe una oferta de inquilino (stage 2.7), la decisión es "Aceptar / Pedir cambios / Rechazar". Esa pantalla debe ser la más clara del portal y hoy no tiene wireframe específico.

### F. Estados vacíos, errores y bloqueos — ⚠️ Observación

**Lo que está bien:**
- Manual de marca sección 16.4 cubre estados vacíos y 16.6 cubre errores.

**Lo que falta:**
1. **"Sin match" prolongado** — un inmueble que está en mercado >60 días sin ofertas. ¿Qué ve el propietario? ¿Qué oferta proactiva HDV? ¿Cuándo se dispara conversación de "ajustamos precio o reposicionamos"?

2. **"En revisión interna"** — cuando una decisión de HDV está pendiente (ej. evaluar al inquilino, validar documentos del propietario, decidir si se acepta caso atípico), el cliente queda en limbo. Necesita banner de "Estamos revisando · Te respondemos antes de [fecha]".

3. **"Bloqueado por terceros"** — cuando la afianzadora tarda, cuando el banco no procesa, cuando el notario no responde. El cliente ve estado "esperando" y no entiende quién es responsable. Mostrar el stakeholder bloqueante explícitamente.

### G. Mobile-first — ⚠️ Observación

**Lo que está bien:**
- Documento del portal sección 8 menciona mobile-first.
- Reportar incidente con upload de fotos pensado para mobile.

**Lo que falta:**
1. **Admin del CRM en mobile** — el agente que está en visita técnica necesita poder registrar la visita, subir fotos, marcar el stage como completado desde el celular. Hoy el CRM admin no es responsive. Proceso de renta requiere admin mobile-friendly mínimo en pantallas críticas (visita, comentarios, cambio de stage).

2. **Firma de contrato en mobile** — propietario o inquilino que firma desde el celular. El flujo de Mifiel debe estar probado en mobile.

3. **Subir documentos largos** (escrituras de varias páginas) desde mobile — UX típica del cliente. Asegurar que se puede subir múltiples páginas combinadas o por separado.

### Capa 2 — Resumen

⚠️ Avanzar a Capa 3 con las siguientes 9 observaciones a resolver:

1. Crear journey map visual del propietario e inquilino.
2. Documentar flujo pre-portal (qué recibe el cliente antes de tener cuenta).
3. Documentar persistencia de contexto en leads (property_id, referrer).
4. Definir flujo de re-engagement para clientes que no responden.
5. Crear protocolo de no-show de visitas.
6. Definir comunicación de pausa / cancelación del proceso.
7. Realizar test "3 segundos" con clientes reales antes del go-live.
8. Diseñar UX explícita para decisión crítica de stage 2.7 (oferta al propietario).
9. Cubrir tres estados de espera: "sin match prolongado", "en revisión interna", "bloqueado por terceros".

---

## Capa 3 — Código

### Cumplimiento de criterios — ✅ Mayormente OK

1. **Alineación con `IMPLEMENTATION_RULES.md` y `CRITICAL_VERSIONS.md`:** ✅ Sí. Stack Laravel 13.6 + Livewire 4 + Filament 5 instalado, todo respetado.
2. **Ambiente UI:** ✅ El proceso vive en CRM admin (`/admin/rentas`) y se refleja en Portal del Cliente (`miportal.homedelvalle.mx`). Documento sección 6 y 14 lo separan correctamente.
3. **13 convenciones obligatorias:** ✅ Respetadas. Jobs síncronos (sección 10), uploads vía Spatie Media Library implícitos, cuenta de portal automática (sección 14.1), regla "si el cliente debería verlo, el portal lo muestra" (sección 14).
4. **Cambios al esquema:** ✅ Documentados claramente en sección 13.1 (clients.client_type='renter', properties.allows_pets/is_furnished/minimum_lease_months, índices recomendados).
5. **Impacto en jobs y automations:** ✅ Sección 10 cubre las 5 automations y los 2 jobs programados.
6. **Impacto en portal del cliente:** ✅ Sección 14 lo cubre exhaustivamente.
7. **Tests cubren happy paths y atípicos:** ⚠️ Sección 13.7 menciona que se debe verificar antes de implementar pero no detalla casos de test. Recomendado: agregar lista de tests por sub-fase (capa 3 listo cuando se cierre la lista).
8. **PRs revisables (<600 líneas):** ✅ El brief de Claude Code v4 los parte en 14 PRs.
9. **Dependencias técnicas externas:** ⚠️ Falta documentar las APIs de las afianzadoras, Mifiel y eventualmente pasarelas de pago. Documento las menciona pero sin contrato técnico claro.
10. **Plan de rollback:** ⚠️ No documentado explícitamente. Cada PR del Track B debería tener plan de rollback (especialmente las migraciones).

### Lo que falta para que Capa 3 esté 100%

1. **Spec de la API portal ↔ admin** — qué endpoints existen, qué payload, qué auth. Parcialmente cubierto en `06-PORTAL-DEL-CLIENTE.md` sección 11 pero falta detalle. Recomendado: agregar archivo `docs/API-PORTAL-ADMIN.md` cuando se inicie PR Portal-3.

2. **Spec de webhooks externos** — afianzadora notifica emisión de póliza, banco notifica pago recibido (cuando se integre pasarela), Mifiel notifica firma. Falta sección dedicada en `05-PROCESO-DE-RENTA.md`.

3. **Lista detallada de tests** por sub-fase (E2E y unitarios). Hoy hay menciones generales pero no exhaustiva.

4. **Plan de rollback** por cada PR — especialmente migraciones que toquen `clients`, `operations`, `properties`. Anexar al PR.

5. **Carga de prueba** — cuando llegue a producción, ¿cuántos `RentalProcess` activos podrá manejar el job mensual de cobranza sin timeout en cPanel? Documentar el límite probado y el plan si se rebasa.

### Capa 3 — Resumen

✅ Listo para ejecutar con las siguientes 5 anotaciones para Track B:

1. Cada PR debe documentar su plan de rollback antes del merge.
2. Cuando se inicie PR Portal-3, agregar `docs/API-PORTAL-ADMIN.md` con spec de la API.
3. Agregar sección 13.X en `05-PROCESO-DE-RENTA.md` documentando webhooks externos cuando se integre cada uno.
4. Por cada sub-fase del PR de Track B, listar tests E2E mínimos antes del merge.
5. Hacer carga de prueba del job de cobranza con 50, 200 y 500 `RentalProcess` activos antes del go-live.

---

## Acciones recomendadas (priorizadas)

> Combinación de las 12 observaciones de Capa 1 + 9 de Capa 2 + 5 de Capa 3, ordenadas por impacto y urgencia.

### Prioridad ALTA (bloquean implementación)

1. **Validar con Ana Laura los 12 puntos de Capa 1** — especialmente buró de crédito, ajuste de SLA de garantía, comisión de renovación, política de casos atípicos. Decisión de Dirección General antes de implementar Track B.
2. **Editar `05-PROCESO-DE-RENTA.md` con los puntos aprobados** — agregar stage `internal_review`, casos atípicos faltantes, riesgos operativos faltantes, stakeholders faltantes (notario, afianzadora). Resultado: documento v1.
3. **Definir flujo pre-portal del cliente** — qué información recibe en stages 1.1–1.4 sin tener cuenta. Decisión de UX + Operaciones.
4. **Diseñar pantallas de decisión crítica** — stage 2.7 (propietario recibe oferta) con wireframe explícito.

### Prioridad MEDIA (mejoran calidad antes del go-live)

5. **Crear journey map visual** propietario e inquilino. Útil para onboarding del equipo y para presentaciones a clientes.
6. **Documentar protocolos faltantes:** no-show de visitas, pausa/cancelación del proceso, re-engagement de leads que no responden.
7. **Cubrir 3 estados de espera** en el portal (sin match, en revisión interna, bloqueado por terceros).
8. **Test "3 segundos"** con 3 propietarios actuales y 3 inquilinos antes del go-live de la sub-fase correspondiente del Track A.
9. **Detallar lista de tests E2E** por cada PR del Track B antes del merge.
10. **Plan de rollback** por cada PR con migración de schema.

### Prioridad BAJA (mejora continua)

11. **Agregar KPIs de NPS al move-out, CAC del inquilino, LTV del propietario** al dashboard analytics.
12. **Definir relación con afianzadoras y notarios preferidos** con SLAs explícitos.
13. **Documentar webhooks externos** cuando se integre cada uno (Mifiel, afianzadora, pasarela).
14. **Carga de prueba del job de cobranza** antes de superar 50 `RentalProcess` activos.
15. **Hacer responsive el CRM admin para móviles** (al menos pantallas críticas: cambio de stage, comentarios, fotos).

---

## Próximos pasos

1. Alex y Ana Laura se reúnen 30 min para revisar las 12 observaciones de Capa 1 y decidir cuáles aceptar / rechazar / matizar.
2. Una vez aprobadas, Alex actualiza `docs/05-PROCESO-DE-RENTA.md` a v1 reflejando los cambios.
3. Alex revisa las 9 observaciones de Capa 2 y decide cuáles requieren wireframes / diseño antes del go-live y cuáles pueden esperar.
4. Una vez todo aprobado, el prompt para Claude Code (`Prompt-Claude-Code-Portal-y-Rentas.md` en Cowork) se actualiza con los cambios y se da go-live al Track B.

---

**Documento generado:** 2026-04-29
**Playbook usado:** `docs/00-PLAYBOOK-REVISION-3-CAPAS.md`
**Próxima aplicación recomendada:** después de implementar Track B (post mortem) y trimestral durante la operación.
