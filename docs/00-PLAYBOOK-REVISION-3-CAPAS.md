# Playbook de Revisión en 3 Capas · Home del Valle

> **Audiencia:** Alex, Ana Laura, agentes senior, UX, devs externos.
> **Estado:** v0 — abril 2026.
> **Mantenedor:** Alex.

Este playbook es la herramienta estándar para **revisar cualquier propuesta de proceso, feature o documento de HDV antes de implementarlo**. Tres capas en orden estricto: si el flujo de negocio no tiene sentido, no se diseña UX. Si la UX no está validada, no se escribe código.

La regla de oro: **cada capa puede vetar el avance**. Capa 1 puede decir "esto no es como funciona el negocio real, regrésalo". Capa 2 puede decir "el flujo es correcto pero el cliente no lo va a entender". Capa 3 puede decir "los dos están bien pero técnicamente es inviable hoy". El veto es legítimo y no se atropella.

---

## Cuándo aplicar este playbook

Antes de implementar:
- Un proceso operativo nuevo (captación de un nuevo tipo de activo, gestión de una nueva línea).
- Una feature del CRM que cambie el pipeline.
- Una landing nueva con flujo de captación.
- Una sección nueva del Portal del Cliente.
- Una integración con tercero (afianzadora, banco, notario digital).
- Cualquier cambio sustantivo en `docs/05-PROCESO-DE-RENTA.md` o equivalentes.

**No aplica para:** correcciones ortográficas, retoques visuales menores, ajustes de copy puntuales.

---

## Capa 1 — Especialista Inmobiliario

> **Pregunta clave:** ¿Esto es como funciona el negocio inmobiliario real en CDMX?

Si la respuesta es no, no avanzamos a UX. Reescribimos el flujo o lo descartamos.

Diez criterios — A a J. Cada uno con preguntas concretas y forma de evaluación.

### A. Realidad operativa
- ¿El flujo refleja cómo se hace una operación inmobiliaria real en Benito Juárez / CDMX?
- ¿Los pasos están en el orden que hoy usan los corredores serios?
- ¿Hay pasos que existen en la realidad y se están saltando? (ej. valuación previa, due diligence, walkthrough técnico).
- ¿Hay pasos que sobran (burocracia que el mercado no usa)?

**Cómo evaluar:** preguntar a un corredor con 10+ años de experiencia o validar contra AMPI / casos cerrados de HDV.

### B. Tiempos y SLAs
- ¿Los plazos prometidos son alcanzables consistentemente, no en el caso ideal?
- ¿Los SLAs están alineados con la velocidad real del mercado (afianzadora, notaría, banco)?
- ¿Qué pasa si un stakeholder externo no responde en su SLA (ej. afianzadora tarda 5 días en lugar de 72h)?

**Cómo evaluar:** medir en operaciones recientes; si no hay datos, hacer benchmark con 2–3 inmobiliarias boutique de la zona.

### C. Comisiones, splits y modelo económico
- ¿La comisión propuesta es competitiva en el mercado? ¿Sostenible para HDV?
- ¿El split entre roles refleja el esfuerzo real?
- ¿Cuándo se libera la comisión? ¿Qué pasa si la operación se cae después?
- ¿Hay incentivos perversos (ej. cerrar mal por cobrar)?

**Cómo evaluar:** comparar con tabuladores AMPI y con la realidad de retención de talento.

### D. Documentos legales obligatorios
- ¿Están todos los documentos que la ley mexicana exige? (escrituras, predial, identificaciones, comprobantes).
- ¿Están en el orden correcto del proceso? (ej. póliza emitida ANTES de firma, no después).
- ¿Qué documentos opcionales puede el caso necesitar y cuándo se piden?
- ¿Hay templates de contrato listos para cada variante?

**Cómo evaluar:** revisar con Ana Laura (Dirección General y Legal) o con notario de confianza.

### E. Pólizas, garantías y cobertura de riesgo
- ¿Las garantías propuestas (póliza jurídica, aval, depósito) cubren los riesgos reales del propietario?
- ¿Los topes de cobertura son razonables vs el ticket de la operación?
- ¿Qué pasa si la afianzadora rechaza al inquilino?
- ¿Hay alternativa si el inquilino no califica para póliza?

**Cómo evaluar:** revisar con afianzadoras reales, mínimo 2 cotizaciones por caso típico.

### F. Riesgos operativos cubiertos
Confirmar que el flujo contempla:
- Mora (cómo se detecta, cuándo escala, cómo se ejecuta garantía).
- Daños (al move-out, durante el contrato).
- Subarriendo no autorizado.
- Uso distinto al pactado (residencial → comercial, etc.).
- Inquilino que se va sin avisar.
- Conflicto entre las partes (mediación HDV).
- Caída de la operación a último minuto.

**Cómo evaluar:** "qué podría salir mal" como ejercicio explícito antes de firmar el flujo.

### G. Casos atípicos contemplados
Mínimo aceptable: el flujo cubre o señala excepción para:
- Inmueble amueblado vs sin amueblar.
- Mascotas (con depósito adicional).
- Renta corporativa (empresa renta para empleado).
- Extranjeros (con visa, con fiador local, con fianza internacional).
- Copropiedad (varios dueños, autorizaciones múltiples).
- Sucesión vigente.
- Cambio de propietario durante el contrato.
- Incumplimiento del propietario.
- Incumplimiento del inquilino.
- Salida anticipada.
- Subarriendo solicitado formalmente.
- Renta con opción a compra.

**Cómo evaluar:** lista exhaustiva — si falta alguno frecuente, regresa el flujo.

### H. Stakeholders y sus tiempos
- ¿Están todos los stakeholders identificados? (propietario, inquilino, fiador, afianzadora, notario, administrador, dirección, marketing).
- ¿Cada uno tiene SLA explícito de respuesta?
- ¿Quién es el dueño del proceso en cada fase? (no puede haber zonas grises).
- ¿Cómo se comunica entre HDV y stakeholders externos?

**Cómo evaluar:** RACI matrix mental por fase.

### I. Métricas reales medibles
- ¿Los KPIs son medibles desde el sistema actual o requieren tracking nuevo?
- ¿Las metas son alcanzables? ¿Comparables con benchmarks de la industria?
- ¿Cubren los tres ejes: tiempo, calidad, dinero?
- ¿Hay métricas de calidad subjetiva (NPS, satisfacción)?

**Cómo evaluar:** intentar "calcular" el KPI con datos del CRM hoy. Si no se puede, falta tracking.

### J. Diferenciación boutique
- ¿El flujo refleja el discurso "Pocos inmuebles. Más control. Mejores resultados."?
- ¿Hace algo distinto a una inmobiliaria de volumen, o es lo mismo con menos inventario?
- ¿El cliente puede explicar en 30 segundos por qué eligió HDV en lugar de la competencia?

**Cómo evaluar:** comparar lado a lado contra 2 inmobiliarias tradicionales y 1 boutique. Si el flujo es indistinguible, hay un problema de posicionamiento.

### Forma de entrega de la Capa 1

```
=== Capa 1 — Especialista Inmobiliario ===

A. Realidad operativa: ✅ / ⚠️ / ❌
   Hallazgos:
   - [punto 1]
   - [punto 2]

B. Tiempos y SLAs: ✅ / ⚠️ / ❌
   Hallazgos:
   - [punto]

... (A a J)

VEREDICTO: ✅ Avanzar a Capa 2 / ⚠️ Avanzar con observaciones / ❌ Rehacer
```

---

## Capa 2 — UX Architect

> **Pregunta clave:** ¿El cliente entiende qué pasa, qué tiene que hacer y qué espera de HDV?

Sólo si Capa 1 dio ✅ o ⚠️ con observaciones aceptadas.

Siete criterios — A a G.

### A. Mapa del usuario
- Dibujar el viaje completo del cliente desde primer contacto hasta cierre.
- ¿Hay puntos donde el cliente se queda solo sin saber qué hacer?
- ¿Hay loops innecesarios o pasos redundantes?
- ¿El flujo se ramifica de forma natural o forzada según el caso del cliente?

**Forma de entrega:** journey map con stages, decisiones, emociones esperadas.

### B. Punto de entrada
- ¿Cómo descubre el cliente este flujo? (sitio público, referido, anuncio, redes).
- ¿La promesa de la entrada coincide con lo que recibe?
- Si hay múltiples entradas, ¿el flujo se adapta o pierde contexto?

**Forma de entrega:** lista de entradas posibles + qué metadata se preserva.

### C. Onboarding y primeras 5 minutos
- ¿En los primeros 5 minutos el cliente entiende dónde está, qué pasó, qué sigue?
- ¿Hay onboarding asistido (tutorial, modal, llamada de bienvenida)?
- ¿Qué necesita saber el cliente antes de tener cuenta de portal? (mientras está en stages tempranos).

**Forma de entrega:** wireframe del primer encuentro + script de bienvenida.

### D. Comunicación clave por fase
Por cada fase del flujo:
- ¿Qué notificación recibe el cliente?
- ¿Por qué canal? (email, WhatsApp, in-portal, SMS).
- ¿Cuándo? (inmediato, programado, condicional).
- ¿Qué hace el cliente con esa notificación? (informativa, requiere acción, opt-in/opt-out).

**Forma de entrega:** tabla "fase → notificación → canal → acción esperada".

### E. Lectura cognitiva
Para las 3 pantallas o momentos más importantes del flujo:
- ¿El cliente puede entender en 3 segundos qué está pasando?
- ¿La pantalla más importante responde a "¿qué tengo que hacer yo?"?
- ¿La jerarquía visual está alineada con la prioridad de información?

**Forma de entrega:** test "3 segundos" con 2–3 personas no involucradas.

### F. Estados vacíos, errores y bloqueos
- ¿Qué ve el cliente si hay un estado vacío? (sin operaciones, sin documentos, sin mensajes).
- ¿Qué ve si hay un error? (sistema caído, validación, permiso denegado).
- ¿Qué ve si está bloqueado por algo de HDV? (pendiente de aprobación, en revisión).
- ¿El tono del error/bloqueo es coherente con la voz boutique?

**Forma de entrega:** matriz "estado → mensaje → acción".

### G. Mobile-first
- ¿Funciona el flujo en pantalla 360px?
- ¿Los formularios largos se pueden llenar en mobile sin perder contexto?
- ¿Las acciones críticas (firmar, pagar, aprobar) funcionan en mobile sin frustración?
- ¿El cliente puede subir fotos desde su teléfono fácilmente?

**Forma de entrega:** screenshots de cada pantalla crítica en 360 / 414 / 768 px.

### Forma de entrega de la Capa 2

```
=== Capa 2 — UX Architect ===

A. Mapa del usuario: ✅ / ⚠️ / ❌
   Hallazgos:
   - [punto]

... (A a G)

VEREDICTO: ✅ Avanzar a Capa 3 / ⚠️ Avanzar con observaciones / ❌ Rehacer experiencia
```

---

## Capa 3 — Código

> **Pregunta clave:** ¿Es viable técnicamente con el stack actual y las convenciones del proyecto?

Sólo si Capa 1 y Capa 2 dieron ✅ o ⚠️ aceptable.

Esta capa **no inventa** funcionalidad — implementa la que las dos capas anteriores aprobaron. Si en código se descubre que algo es inviable, se regresa a Capa 1 o 2 con la información, **no se cambia el alcance unilateralmente**.

### Criterios de Capa 3

1. ¿Está alineada con `IMPLEMENTATION_RULES.md` y `CRITICAL_VERSIONS.md`?
2. ¿Identifica el ambiente UI correcto (sitio público / CRM admin / portal del cliente)?
3. ¿Respeta las 13 convenciones obligatorias de `IMPLEMENTATION_RULES.md` sección 3?
4. ¿Plantea cambios al esquema de DB que requieren migración?
5. ¿Hay impacto en jobs programados, automations o emails?
6. ¿Hay impacto en el portal del cliente y se contemplan vistas/notificaciones nuevas?
7. ¿Los tests cubren los happy paths y los casos atípicos identificados en Capa 1.G?
8. ¿Se puede entregar en PRs revisables (cada uno < 600 líneas)?
9. ¿Hay dependencias técnicas externas? (paquete nuevo, API externa, servicio).
10. ¿El plan de rollback es realista?

### Forma de entrega de la Capa 3

```
=== Capa 3 — Código ===

1. Stack y convenciones: ✅
   - Ambiente UI: [público / CRM admin / portal]
   - Convenciones aplicables: [lista]

2. Cambios al esquema:
   - [migración 1 propuesta]
   - [migración 2]

3. Plan de PRs:
   - PR 1: [alcance]
   - PR 2: [alcance]
   ...

4. Riesgos técnicos:
   - [riesgo]: [mitigación]

5. Dependencias:
   - [dep externa]: [cómo se gestiona]

VEREDICTO: ✅ Listo para ejecutar / ⚠️ Necesita decisiones de Alex / ❌ Inviable en stack actual
```

---

## Cómo se aplica este playbook en práctica

### Caso 1: Documento operativo nuevo (ej. proceso de venta, gestión de portafolio)

1. Alex redacta primer borrador.
2. Aplica Capa 1 él mismo o pide revisión a Ana Laura / corredor senior.
3. Si Capa 1 OK, pasa a UX Architect (Alex o externo) para Capa 2.
4. Si Capa 2 OK, brief técnico se redacta y se pasa a Claude Code o dev.
5. Capa 3 ejecuta y reporta.

### Caso 2: Feature nueva del CRM o portal

1. Alex redacta brief funcional.
2. Capa 1 valida que la feature aporta al negocio real.
3. Capa 2 diseña la experiencia (wireframes, copy, notificaciones).
4. Capa 3 implementa.

### Caso 3: Integración externa (afianzadora, banco, notario)

1. Capa 1 evalúa qué aporta al flujo y qué riesgo introduce.
2. Capa 2 diseña la experiencia de la integración (qué ve el cliente, qué notificaciones).
3. Capa 3 implementa contra la API/SDK del tercero.

### Caso 4: Revisión periódica de procesos vivos

Cada trimestre, aplicar Capa 1 a los documentos de proceso vigentes (`05-PROCESO-DE-RENTA.md`, etc.) para detectar drift entre el documento y la operación real.

---

## Dueños y SLAs del playbook

| Capa | Quién la aplica | SLA esperado |
|---|---|---|
| Capa 1 | Alex + Ana Laura + corredor senior cuando aplique | 3–5 días hábiles |
| Capa 2 | Alex (con apoyo UX externo si el caso amerita) | 2–4 días hábiles |
| Capa 3 | Claude Code o dev asignado | Variable según tamaño |

Si una capa tarda más, la anterior queda con estado "en revisión" — no se asume aprobación implícita.

---

## Versiones y mejora continua del playbook

- Versionar este documento (`v0`, `v1`, ...) cuando se ajusten criterios.
- Después de cada aplicación, anotar en este archivo qué criterio fue particularmente útil o cuál sobró.
- Si un criterio nuevo aparece (ej. AI ethics, GDPR específico), agregarlo a la capa correspondiente con justificación.

---

**Mantenedor:** Alex.
**Próxima revisión sugerida:** trimestral o después de aplicar el playbook 3 veces (lo que pase primero).
