# Roadmap y Arquitectura Objetivo · Home del Valle

> **Audiencia:** Alex y stakeholders técnicos.
> **Estado:** v0 — abril 2026.
> **Última revisión:** 2026-04-27.

Este documento responde tres preguntas: **¿qué tenemos hoy?, ¿hacia dónde vamos?, ¿en qué orden lo construimos?**. No reemplaza el detalle técnico de `.claude/ARCHITECTURE_ANALYSIS.md`; lo conecta con la decisión estratégica de Opción C.

---

## 1. Snapshot del estado actual

| Eje | Estado | Comentario |
|---|---|---|
| **CRM operacional** | 🟢 Sólido | 108 tablas, 93 modelos, pipeline unificado venta/renta/captación con auto-spawn, kanban, checklists configurables. |
| **Sitio público** | 🟡 Funcional, copy desalineado | Layout y componentes listos. Copy con erratas y posicionamiento difuso. Catálogo con una sola propiedad pública. |
| **Captación de leads** | 🟠 Una sola ruta sólida | `/vende-tu-propiedad` está bien. `/comprar` y `/desarrolladores-e-inversionistas` no existen. Chatbot calificador captura email pero no enruta a un funnel completo. |
| **Marketing automation** | 🟡 Motor instalado, uso parcial | Automations, segments, lead scoring existen como tablas/clases. Lead scoring se recalcula a diario pero las reglas no están todas configuradas. |
| **Carousel de Instagram** | 🟢 Excelente | Sistema robusto con templates, versiones, AI prompts y publicación multi-canal. |
| **Legal y contratos** | 🟢 Bien | DomPDF + templates con variables, Mifiel, tracking de aceptaciones. |
| **Portal de clientes** | 🟡 Funcional | Dashboard, rentas, documentos. Falta acceso para compradores activos. |
| **Help center / Onboarding** | 🔴 Vacío | Tablas creadas, sin contenido. |
| **Referidos / partners** | 🔴 Esqueleto | Tabla `referrers` y `referrals` sin integración productiva. |
| **Analítica / atribución** | 🟠 Parcial | UTM se capturan en `clients` pero no se conectan con campañas. ROI es null. |
| **Documentación** | 🟢 Madurando | `.claude/` con DATABASE_SCHEMA, SCHEMA_QUICK_REFERENCE, ARCHITECTURE_ANALYSIS. `/docs/` arrancando. |

**Lectura corta:** la maquinaria está construida y funciona. La fricción está en la capa de **discurso, captación y atribución** — exactamente lo que la Opción C resuelve.

---

## 2. Visión objetivo (Opción C)

Tres funnels paralelos con narrativa propia, todos alimentando el mismo CRM operacional.

```
                    ┌──────────────────────────────────────┐
                    │  HOME (selector de intención)        │
                    │  Pocos inmuebles. Más control.       │
                    │  Mejores resultados.                 │
                    └──────────────┬───────────────────────┘
                                   │
        ┌──────────────────────────┼──────────────────────────┐
        │                          │                          │
        ▼                          ▼                          ▼
┌───────────────┐         ┌────────────────┐         ┌────────────────────┐
│ /vende-tu-    │         │ /comprar       │         │ /desarrolladores-  │
│  propiedad    │         │                │         │  e-inversionistas  │
│ Vendedor      │         │ Comprador      │         │ B2B                │
│ residencial   │         │ residencial    │         │                    │
└───────┬───────┘         └────────┬───────┘         └─────────┬──────────┘
        │                          │                           │
        ▼                          ▼                           ▼
   Client                     Client                       Client
   (owner, warm)              (buyer, warm)                (investor, hot)
   + Operation                 + Lead Event                 + Operation_comments
   (captacion, inquiry)                                      + Asignación dirección
        │                          │                           │
        └──────────────┬───────────┴───────────┬───────────────┘
                       │                       │
                       ▼                       ▼
               Pipeline operacional    Marketing automation
               (kanban, stages,        (segments, scoring,
               checklists)             nurturing emails)
```

### Principios que esto respeta

1. **No reescribir el CRM.** Toda la maquinaria que ya existe (operations, clients, lead_scores, automations) absorbe el flujo nuevo sin migración mayor.
2. **Discurso boutique consistente.** Cada funnel tiene su tono pero respeta el slogan, valores y tono definidos en `01-MANUAL-MARCA-Y-VOZ.docx`.
3. **Atribución desde el primer toque.** Cada formulario captura UTM, source, referrer, must-have del lead.
4. **Calificación inmediata.** Los formularios B2B llegan al CRM como `hot` con asignación a dirección general; los residenciales como `warm`.
5. **El home es el filtro.** Un visitante decide en 3 segundos por dónde entrar, no recibe el mismo mensaje genérico todos.

---

## 3. Roadmap por fases

### Fase 0 — Hygiene (1–2 semanas)

Limpieza de cosas que sangran hoy y bloquean el resto.

- [ ] Corrección ortográfica del sitio público (lista en `docs/05-AUDITORIA-ORTOGRAFICA.md` o en el brief de Claude Code).
- [ ] Brand consistency: `Home del valle` → `Home del Valle` en todos los `<title>` y `og:title`. Buscar en `pages.title`, `posts.meta_title`, `site_settings.site_name`, vistas Blade.
- [ ] Slogan visible en header (desktop ≥ 1024px) y en footer.
- [ ] OG image global con slogan, refrescar `site_settings.og_image_default`.
- [ ] Auditar y eliminar páginas legacy/staging del navbar (revisar `menu_items` con `is_active=false`).
- [ ] Revisar tablas vacías (`help_articles`, `referrers`, `expense_categories`) y decidir: poblar o esconder de la UI hasta nuevo aviso.

**Criterio de hecho:** `grep -irE "Home del valle|juarez|operacion|valuacion"` sobre `resources/views/` y `pages.body` devuelve 0 falsos positivos.

### Fase 1 — Funnel comprador (3–6 semanas)

Es la brecha más grande, por eso va primero.

- [ ] Landing `/comprar` con copy del Manual de Marca y formulario brief estructurado (ver Manual de Implementación, sección 12).
- [ ] Endpoint `LandingController@storeBuyerSearch` que crea `Client` (`client_type=buyer`) con `budget_min/max`, `property_type`, metadata de zonas y must-have.
- [ ] Email transaccional al lead (`lead_buyer_received`) y notificación interna a corretaje.
- [ ] Integrar el resultado del chatbot calificador → si responde "Comprador" y deja email, redirigir o postear al endpoint anterior con `lead_source='chatbot'`.
- [ ] Sección "Búsqueda asistida" en home y banda final del observatorio `/mercado` con CTA al funnel.
- [ ] Publicar 5–10 propiedades curadas en `/propiedades` con narrativa por ficha (no más, para respetar el slogan).
- [ ] Plantilla automation "Comprador nuevo": serie de 5 emails (welcome, brief de mercado de su zona, propiedad sugerida, caso de éxito, llamada).

**Criterio de hecho:** un visitante que llena el brief en `/comprar` aparece en `/admin/clients` como `buyer warm` con todos los campos llenos, recibe email y dispara una notificación a un agente asignado.

### Fase 2 — Funnel B2B (4–8 semanas, paralelo con fase 1)

Mayor ticket potencial, requiere casos y cuidado.

- [ ] Landing `/desarrolladores-e-inversionistas` con copy del Manual de Marca y brief calificador (m², zonas, presupuesto, horizonte).
- [ ] Upload de brief PDF opcional (Spatie Media Library, collection `briefs`).
- [ ] Endpoint que crea `Client` (`client_type=investor`, `lead_temperature=hot`, `assigned_user_id` = dirección general) y dispara nota interna de prioridad.
- [ ] Dos casos de estudio publicables (con NDA suavizado: zona, m², ticket aproximado, plazo). Sin estos, la landing no convence.
- [ ] Workflow "B2B nuevo": email transaccional + slot de calendario para llamada en 48 horas.
- [ ] NDA en línea (versión corta) opt-in en el formulario, persistido en `legal_acceptances`.

**Criterio de hecho:** un lead B2B llega al CRM con todos los campos del brief, dirección general recibe notificación inmediata y el calendario se llena en menos de 48 horas.

### Fase 3 — Atribución y nurturing (4–6 semanas, después de fase 1)

- [ ] Implementar `campaign_attribution` (`client_id`, `campaign_id`, `attributed_at`, `attribution_model`).
- [ ] Marketing dashboard real con ROI por canal (`marketing_campaigns.actual_roi` poblado por job mensual).
- [ ] Cohort tracking en `clients` (`cohort_month`).
- [ ] Reglas de lead scoring por evento (form_submit:+15, email_open:+5, link_click:+10, viewing_scheduled:+25, viewing_completed:+30).
- [ ] Auto-update `lead_temperature` según `lead_score` (≥90 hot, 50–89 warm, <50 cold).
- [ ] Dashboard de leads sin contactar > 24h (ya existe en CRM admin) con segmentación por funnel.

**Criterio de hecho:** Dirección puede responder "qué canal trajo el lead que cerró este mes" en una sola pantalla de admin.

### Fase 4 — Refactor y escalabilidad (Q3–Q4 2026)

Cuando el negocio crezca, atender la deuda técnica documentada en `.claude/ARCHITECTURE_ANALYSIS.md`.

- [ ] Split de `operations` en `venta_operations`, `renta_operations`, `captacion_operations` (single-table inheritance o polimórfica).
- [ ] Consolidar fotos: `property_photos` ↔ `property_images`.
- [ ] Geospatial indexing en `properties` (lat, long).
- [ ] Archive de `carousel_versions` y `lead_events` > 6/12 meses.
- [ ] Encriptar `clients.phone` at-rest.
- [ ] Policy de operaciones por agente (un agente sólo ve sus operaciones).
- [ ] Tests automatizados de integridad referencial.

### Fase 5 — Escalamiento y diferenciación (2027)

- [ ] Multi-oficina / white-label.
- [ ] App móvil para agentes (consultar pipeline, registrar visitas, fotos).
- [ ] Mejora del observatorio `/mercado` con `market_colonia_metrics` (días promedio en mercado, tendencia precio).
- [ ] Recomendador ML de propiedades a compradores según brief.
- [ ] Predicción de churn de clientes activos.

---

## 4. Dependencias entre fases

```
Fase 0 (hygiene) ───┐
                    ▼
              Fase 1 (comprador) ───┐
                    │               │
                    ▼               ▼
           Fase 2 (B2B)        Fase 3 (atribución)
                    │               │
                    └───────┬───────┘
                            ▼
                    Fase 4 (refactor)
                            │
                            ▼
                    Fase 5 (escalamiento)
```

Fase 1 y Fase 2 pueden correr en paralelo si hay capacidad de implementación. Fase 3 requiere tener al menos Fase 1 publicada porque sin tráfico al funnel no hay datos para atribuir. Fase 4 y 5 esperan crecimiento real (>5,000 ops, >300 MB de DB).

---

## 5. Decisiones técnicas tomadas

Estas decisiones están cerradas. Si alguna se cuestiona, traerla a discusión explícita.

| # | Decisión | Razón | Cuándo revisar |
|---|---|---|---|
| 1 | El CRM admin se mantiene custom (Blade + CSS variables), **no** se migra a Filament. | Inversión hecha, equipo lo conoce, performance buena, librería pesada cambia poco. | Si Filament 6 ofrece ventaja clara (q3 2026). |
| 2 | Sitio público sigue con Blade + Tailwind 4 + Alpine.js. **No** se migra a SPA ni Inertia. | SEO, SSR nativo, tiempo de carga rápido, equipo lo domina. | Si surge necesidad de UX altamente reactiva en muchas páginas. |
| 3 | Jobs corren síncronos vía `schedule:run`. **No** se introduce queue worker. | cPanel actual no lo soporta. Hostings con worker disponible cambian el cálculo. | Cuando se migre a hosting con worker (VPS / managed). |
| 4 | PHPMailer + SMTP dinámico, **no** Laravel Mail. | Control directo sobre SMTP por cliente y por usuario. Tracking de apertura. | Si Laravel introduce tracking nativo. |
| 5 | Los formularios públicos usan **Alpine.js** + controlador, no Livewire. | Coherencia con el resto del sitio. SSR mantiene SEO. Livewire reservado para vistas internas. | Si se quiere validación reactiva en formularios largos (ej. brief B2B). |
| 6 | Los leads del sitio se persisten como `Client` directamente, **no** en una tabla intermedia tipo `form_submissions`. | El CRM ya está diseñado para que `Client` sea la entidad de lead. Crear una tabla intermedia duplicaría datos y rompería automatizaciones existentes. | Si los formularios crecen tanto que necesitamos staging antes de calificar. |
| 7 | El slogan oficial **no** se traduce. Se mantiene en español neutro. | Marca operando en CDMX. Mensaje pierde fuerza traducido. | Si abrimos a otros mercados hispanohablantes (es-AR, es-ES). |
| 8 | Tabla `clients` absorbe vendedores, compradores e inversionistas con campo `client_type`. | Coherencia con el modelo actual. Splittear ahora rompería operaciones en curso. | Cuando `clients` > 50,000 filas. |

---

## 6. Decisiones pendientes

Estas necesitan resolución antes de cerrar las fases correspondientes.

| Decisión | Bloqueo | Recomendación inicial | Quién decide |
|---|---|---|---|
| Tamaño objetivo del catálogo público (compatible con slogan "pocos inmuebles"). | Fase 1. | 5–10 propiedades curadas con narrativa editorial. | Alex + Ana Laura. |
| ¿Se publica el catálogo a EasyBroker? | Fase 1 / Fase 5. | Sí para descubribilidad SEO, pero con sólo las 5–10 propiedades curadas. | Alex. |
| ¿Aceptamos compradores con crédito INFONAVIT? | Fase 1 (afecta copy del FAQ). | Sí. Tenemos experiencia. | Ana Laura. |
| Política de NDA en B2B: ¿NDA bilateral o unilateral del cliente al vendedor? | Fase 2. | Unilateral del lado del cliente, simple, en línea. | Ana Laura. |
| ¿Se permite a un comprador acceder al portal de clientes para ver matchings? | Fase 1+. | Sí en fase 3, no en fase 1 para simplificar lanzamiento. | Alex. |
| Migración de hosting cPanel → VPS / managed para tener queue worker, Redis, websockets. | Fase 4. | Cuando cerremos 100 ops/mes consistentes. | Alex. |
| ¿Se crea un Filament `LeadResource` para complementar el CRM admin? | Fase 3. | No por ahora; mantener custom. | Alex. |

---

## 7. Métricas de éxito (norte estratégico)

| Indicador | Hoy (estimado) | Meta 6 meses | Meta 12 meses |
|---|---|---|---|
| Leads/mes (todos los funnels) | Bajo, no calificados | +50% | +120% |
| % leads calificados (campos completos) | <30% | ≥70% | ≥85% |
| Tiempo a primer contacto | >24h | <60 min | <30 min |
| Tasa de conversión vendedor → captación firmada | n/d | ≥15% | ≥22% |
| Tasa de conversión comprador → operación cerrada | n/d | ≥5% | ≥8% |
| Días promedio de venta (validar promesa pública 45 días) | n/d | ≤50 | ≤45 |
| ROI atribuible por canal (% campañas con dato) | 0% | ≥80% | 100% |
| Tráfico orgánico mensual a `/mercado` | n/d (baseline pendiente) | +40% | +150% |
| NPS interno del equipo (qué tan útil es el CRM) | n/d | 8/10 | 9/10 |

Estos KPIs viven en `/admin/analytics` (parte de la fase 3 cuando se conecte la atribución).

---

## 8. Riesgos identificados

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| El catálogo de 1 propiedad mata conversión de comprador antes de fase 1. | Alta | Alto | Curar 5–10 propiedades en fase 0; recortar al slogan con narrativa. |
| `operations.user_id` con CASCADE delete: borrar usuario borra historial. | Media | Crítico | Política: nunca borrar usuarios, sólo `is_active=false`. Documentar. |
| Migrar `Home del valle` → `Home del Valle` rompe tests/links si están hardcoded. | Baja | Medio | grep antes de reemplazar; deploy en staging. |
| Hosting cPanel sin queue worker: jobs largos pueden timeoutear el cron. | Media | Medio | Mantener jobs ligeros; partir tareas grandes en sub-jobs. |
| Carga masiva de propiedades por EasyBroker contradice el slogan boutique. | Media | Medio | Filtro manual editorial; sólo se publican propiedades aprobadas. |
| Lead scoring se calcula 1 vez al día → temperatura desactualizada. | Media | Medio | Disparar recálculo on-demand al detectar evento de alto valor. |
| PII (teléfonos, emails) no encriptada at-rest. | Media | Alto | Fase 4: `Crypt::encrypt()` en `clients.phone`. |
| Equipo sin documentación operativa cuando crezca. | Alta | Alto | Este conjunto de manuales (`docs/01–05`). Revisar trimestralmente. |

---

## 9. Roles y dueños

| Eje | Dueño actual | Backup |
|---|---|---|
| Estrategia, copy, marketing | Alex | — |
| Legal, contratos, escrituración | Ana Laura Monsivais | — |
| Implementación técnica | Alex (con apoyo de Claude Code) | TBD |
| CRM operativo (uso diario) | Equipo de agentes (TBD) | Alex |
| Contenido del blog | TBD (responsable de marketing) | Alex |
| Carousel Instagram | TBD (responsable de marketing) | Alex |
| Análisis y KPIs | Alex | — |

A medida que el equipo crezca, este apartado debe actualizarse con personas concretas y SLAs por rol.

---

## 10. Cómo este roadmap interactúa con los otros documentos

- **`01-MANUAL-MARCA-Y-VOZ.docx`** — fuente de verdad para cualquier copy que produzcamos en estas fases.
- **`02-MANUAL-IMPLEMENTACION-SITIO.md`** — el "cómo" técnico de cada cambio que este roadmap propone.
- **`03-MANUAL-OPERACIONES-CRM.docx`** — define los SLAs y procesos del equipo cuando un lead llega del sitio al CRM.
- **`.claude/ARCHITECTURE_ANALYSIS.md`** — base técnica que respalda las decisiones de fase 4.
- **`.claude/SCHEMA_QUICK_REFERENCE.md`** — referencia diaria para implementar cada feature.

---

**Mantenedor:** Alex.
**Revisión:** mensual durante fases 0–2, trimestral después.
**Próxima revisión sugerida:** 27 de mayo de 2026.
