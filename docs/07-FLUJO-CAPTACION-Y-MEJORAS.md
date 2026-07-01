# Flujo de Captación y Mejoras Propuestas · Home del Valle

> **Audiencia:** Alex, Ana Laura, brokers, futuros colaboradores y desarrolladores que toquen el CRM.
> **Estado:** v1 — 30 de junio 2026.
> **Documento hermano:** `05-PROCESO-DE-RENTA.md`, `06-PORTAL-DEL-CLIENTE.md`, `08-MANUAL-BROKER-CAPTACION.md`.
> **Mantenedor:** Alex.

Este documento describe el flujo de **Captación** (desde que un propietario nos contacta hasta que firma la exclusiva) tal como funciona hoy en el código, y propone mejoras concretas por etapa orientadas a un solo objetivo: **maximizar la probabilidad de que cada lead termine en exclusiva firmada.**

**Alcance:** Captación termina en la firma de exclusiva. A partir de ahí se abre un pipeline nuevo (Preparación → Promoción, potencialmente con varios candidatos comprador/inquilino en paralelo) que es un proceso distinto, no cubierto en detalle aquí — ver la nota al final de este documento.

**Complemento humano:** este documento es técnico/de producto. El manual dirigido al broker, con guiones y comportamiento esperado, vive en `08-MANUAL-BROKER-CAPTACION.md`.

---

## Índice

1. Principio rector
2. El flujo, etapa por etapa (qué pasa hoy + qué proponemos)
3. Mejoras transversales (broker)
4. Mejoras transversales (propietario)
5. Priorización en fases
6. Después de la exclusiva (nota)

---

## 1. Principio rector

En la venta de exclusividad inmobiliaria, lo que más mueve la conversión es:

1. **Velocidad de respuesta al lead** — el primer contacto define gran parte de la percepción de profesionalismo.
2. **Percepción de expertise y preparación en la visita** — es el momento de mayor impacto de todo el proceso.
3. **Reducir la ansiedad del propietario** de "voy a atarme en exclusiva y no van a hacer nada" — la razón #1 por la que un propietario duda en firmar.
4. **Fricción cero para firmar en el momento de máximo interés** — cada día de espera entre la visita y la firma es una ventana abierta para que el propietario hable con otra inmobiliaria.

Cada propuesta de este documento ataca uno o más de estos cuatro puntos.

---

## 2. El flujo, etapa por etapa

El pipeline vive en `/admin/captaciones/pipeline`, modelado como `Operation` (`type=captacion`), con `Operation::CAPTACION_STAGES`:

```
lead → contacto → visita → revision_docs → avaluo → exclusiva
```

### 2.1 Lead

**Qué pasa hoy:** el interesado llega por `/vende-tu-propiedad`, `/renta-tu-propiedad`, referido o alta manual. Se crea un `FormSubmission` → email automático de acuse. El broker ve el lead en la columna LEAD del kanban. El dashboard marca como urgente un lead sin contactar después de 24 horas.

**Propuestas:**
- Bajar el SLA de alerta de 24h a 30-60 minutos, con notificación push/WhatsApp al broker asignado (reusa el motor de automatizaciones ya existente).
- Acuse por WhatsApp inmediato, no solo email — mensaje tipo "Recibimos tu solicitud, [Nombre] te llama en breve."
- Brief instantáneo en la tarjeta del lead: foto de Street View (ya se auto-genera al crear la propiedad) + precio de referencia del Observatorio para esa colonia (`/mercado`), para que el broker llegue informado a la primera llamada.

### 2.2 Contacto

**Qué pasa hoy:** el broker llama (`tel:` link en la tarjeta), crea la captación con el wizard "Nueva captación desde llamada", y envía la Presentación por WhatsApp o email con link tokenizado y tracking (`PresentationSend`: `email_opened_at`, `link_clicked_at`, `pdf_viewed_at`). Ese tracking existe en la base de datos pero **nadie lo ve en tiempo real** — es un campo pasivo.

**Propuestas:**
- Notificación al broker cuando el propietario abre la presentación ("Juan acaba de ver tu presentación") — el momento perfecto para un follow-up natural. Reusa la tabla `Notification` + los timestamps que ya se guardan en `PresentationSend`.
- Autoagendar la visita desde el mismo link de la Presentación (selector de horarios simple), en vez de coordinar por WhatsApp de ida y vuelta.
- Prueba social: 1-2 testimonios de la misma zona insertados en la Presentación (la tabla `testimonials` ya existe, falta filtrar por colonia).

### 2.3 Visita — la etapa que más importa

**Qué pasa hoy:** se confirma/agenda la visita, se documenta el inmueble, se inicia la valuación, se envía la Propuesta de Servicios. El sistema de confirmación/recordatorio/reagendado de visitas con token (`Interaction` + `visit_token`, `VisitResponseController`) ya existe y está probado, pero fue construido para visitas de **compradores/inquilinos a una propiedad publicada** — la visita de captación (broker visita al propietario) no lo usa hoy.

**Propuestas (el mayor apalancamiento de todo el flujo):**
- Reusar el sistema de confirmación/recordatorio de visitas también para la visita de captación — mismo patrón probado, contexto distinto.
- Brief pre-visita para el broker: comparables del Observatorio, historial de interacciones y notas de la llamada, en un solo lugar.
- **Opinión de Valor en vivo**, no "te aviso en unos días": usar Valor Rápido / Valuación Constructor (ya existen) para dar un número con el propietario presente.
- **Propuesta de Servicios interactiva** en tablet/laptop durante la visita (no solo un PDF que se manda después): precio ya calculado, comparables reales de respaldo, plan de marketing específico de esa propiedad.
- **Firma de exclusiva en el momento**, si el propietario está listo — firma digital desde el mismo dispositivo, ahí mismo.

### 2.4 Revisión de documentos / Avalúo

**Qué pasa hoy:** documentos requeridos (identificación, CURP, comprobante de domicilio) + valuación formal vinculada a la captación. En la práctica puede correr en paralelo a la negociación, no necesariamente como paso secuencial bloqueante.

**Propuestas:**
- Solicitud de documentos vía Portal del Cliente desde el día 1 (ya existe el upload en `/captacion`), con recordatorios automáticos si faltan — para que no sea un cuello de botella justo cuando el propietario ya quiere avanzar a firmar.

### 2.5 Exclusiva

**Qué pasa hoy:** se genera el contrato (Google Docs), se envía, se espera la firma.

**Propuestas:**
- Mini-roadmap visual entregado junto con el contrato: fechas estimadas de fotos, publicación, primer reporte — ataca directamente el miedo #1 del propietario ("¿y si no hacen nada?").
- Recordatorio automático si no ha firmado en X días (el trigger de automatización ya existe, solo falta la regla).
- Reconocimiento interno al equipo cuando se firma (leaderboard/notificación) — refuerzo cultural.

---

## 3. Mejoras transversales (broker)

- Plantillas de WhatsApp pre-armadas por etapa — velocidad y consistencia de marca.
- KPI "días de lead a exclusiva" por broker, visible en `/admin/analytics`.

## 4. Mejoras transversales (propietario)

- Notificación automática en cada avance de etapa — **no requiere código nuevo**: `OperationChecklistService::changeStage()` ya dispara `AutomationEngine::processStageChange()` en cada cambio; solo falta crear la regla en `/admin/automations` con trigger `stage_changed`.
- Sección "Mi Proceso" en el Portal del Cliente — un timeline que lee `Operation.stage` y muestra Presentación, Opinión de Valor, Propuesta de Servicios y estado de la Exclusiva en un solo lugar (ver `06-PORTAL-DEL-CLIENTE.md`).

---

## 5. Priorización en fases

**Fase 1 — bajo esfuerzo, alto impacto (reusa infraestructura existente) — ✅ implementada 2026-07-01:**
1. ✅ Notificación al broker cuando se abre la Presentación (`PresentationPublicController::show()`).
2. ✅ SLA de contacto de lead a 60 min + notificación al broker (`php artisan leads:check-uncontacted`, cada 15 min).
3. ✅ Automatizaciones: notificar al propietario por WhatsApp al entrar a contacto/visita/avaluo/exclusiva (4 `Automation` sembradas); recordatorio diario si la exclusiva lleva >3 días sin firmar (`php artisan captaciones:check-exclusiva-pending`).
4. ✅ Atajo "Agendar visita" de un clic en la ficha de captación, reusando `Interaction` + `visit_token` (`VisitSchedulingService`).

**Fase 2 — esfuerzo medio (nuevas vistas sobre datos/servicios existentes):**
5. Brief pre-visita para el broker.
6. Propuesta de Servicios en modo "presentación en vivo".
7. Autoagendar visita desde el link de la Presentación.
8. Testimonios filtrados por zona en la Presentación.

**Fase 3 — mayor esfuerzo:**
9. Firma de exclusiva in-situ desde tablet/móvil.
10. Mini-roadmap visual post-firma.
11. KPI "días a exclusiva" + plantillas WhatsApp por etapa.

Fase 1 implementada y verificada localmente (commit ver historial de `docs/`), pendiente de desplegar y de correr en producción. Fases 2 y 3 no autorizadas todavía — ver estado real en la memoria de proyecto (`project_homedelvalle_flujo_captacion`) antes de asumir que algo ya se implementó.

---

## 6. Después de la exclusiva (nota)

Firmada la exclusiva, se abre un pipeline nuevo (`Operation` tipo `venta` o `renta`, según corresponda) que cubre: preparación del inmueble (mejoras, fotos/video) → promoción activa, donde pueden coexistir varios candidatos compradores/inquilinos en paralelo (cada uno su propia `Operation` compartiendo `property_id`, descartados vía `status=cancelled`) → cierre. Este proceso posterior tiene su propio diseño y no es el foco de este documento — está descrito con más detalle en la memoria de proyecto y se documentará aparte cuando se implemente.
