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
| **Portal del Cliente** | 🟠 Funcional pero subutilizado | Existe `/portal` (dashboard básico, rentas, documentos, cuenta). **Decisión abr 2026: migrar a `miportal.homedelvalle.mx` como pieza diferenciadora del producto.** No tiene mensajería bidireccional, ni notificaciones in-portal robustas, ni vista "preview as client" desde el admin, ni cobertura para los 4 perfiles (propietario / inquilino / comprador / vendedor). Spec en `05-PROCESO-DE-RENTA.md` y `06-PORTAL-DEL-CLIENTE.md`. |
| **Help center / Onboarding** | 🔴 Vacío | Tablas creadas, sin contenido. |
| **Referidos / partners** | 🔴 Esqueleto | Tabla `referrers` y `referrals` sin integración productiva. |
| **Analítica / atribución** | 🟠 Parcial | UTM se capturan en `clients` pero no se conectan con campañas. ROI es null. |
| **Documentación** | 🟢 Madurando | `.claude/` con DATABASE_SCHEMA, SCHEMA_QUICK_REFERENCE, ARCHITECTURE_ANALYSIS. `/docs/` con 6 manuales maestros + índice. |

**Lectura corta:** la maquinaria está construida y funciona. La fricción está en tres capas: **discurso, captación y atribución** (Opción C), y **experiencia del cliente post-firma** (Portal del Cliente). El portal es el activo digital que más nos diferencia y debe ascender a pieza fundacional, no a feature secundario.

---

## 2. Visión objetivo (Opción C + Portal)

Cuatro funnels paralelos al frente, un CRM operacional al centro y un portal del cliente como destino terminal de cada relación.

```
                    ┌──────────────────────────────────────┐
                    │  HOME (selector de intención)        │
                    │  Pocos inmuebles. Más control.       │
                    │  Mejores resultados.                 │
                    └──────────────┬───────────────────────┘
                                   │
   ┌──────────────────┬────────────┼────────────┬──────────────────┐
   │                  │            │            │                  │
   ▼                  ▼            ▼            ▼                  ▼
┌──────────┐  ┌──────────────┐ ┌─────────┐ ┌──────────┐ ┌────────────────────┐
│ /vende-  │  │ /renta-tu-   │ │/comprar │ │ /rentar  │ │ /desarrolladores-  │
│ tu-prop. │  │ propiedad    │ │         │ │          │ │ e-inversionistas   │
│ Vendedor │  │ Propietario  │ │Comprador│ │Inquilino │ │ B2B                │
│          │  │ que renta    │ │         │ │          │ │                    │
└────┬─────┘  └──────┬───────┘ └────┬────┘ └────┬─────┘ └──────┬─────────────┘
     │               │               │            │               │
     └───────────────┴───────┬───────┴────────────┴───────────────┘
                             │
                             ▼
                   ┌─────────────────────┐
                   │   CRM operacional   │  ← agentes, dirección, automations
                   │   (operations,      │
                   │   clients,          │
                   │   rental_processes) │
                   └──────────┬──────────┘
                              │
                              │ al firmar el primer hito (captación,
                              │  contrato, oferta), se crea cuenta de
                              │  portal automáticamente
                              ▼
                   ┌──────────────────────────────────┐
                   │  miportal.homedelvalle.mx        │
                   │                                  │
                   │  Mi inmueble · Mis pagos ·       │
                   │  Mis documentos · Mensajes ·     │
                   │  Mi operación · Mi cuenta        │
                   │                                  │
                   │  Acceso continuo del cliente     │
                   │  durante toda la relación con HDV│
                   └──────────────────────────────────┘
```

### Principios que esto respeta

1. **No reescribir el CRM.** Toda la maquinaria que ya existe (operations, clients, lead_scores, automations) absorbe el flujo nuevo sin migración mayor.
2. **Discurso boutique consistente.** Cada funnel tiene su tono pero respeta el slogan, valores y tono definidos en `01-MANUAL-MARCA-Y-VOZ.docx`.
3. **Atribución desde el primer toque.** Cada formulario captura UTM, source, referrer, must-have del lead.
4. **Calificación inmediata.** Los formularios B2B llegan al CRM como `hot` con asignación a dirección general; los residenciales como `warm`.
5. **El home es el filtro.** Un visitante decide en 3 segundos por dónde entrar, no recibe el mismo mensaje genérico todos.
6. **El portal es el destino terminal.** Cada cliente que firma el primer hito recibe acceso al portal automáticamente. La relación HDV ↔ cliente se vuelve continua y trazable, no episódica. El portal es el activo digital que más nos diferencia: si un cliente debería verlo, el portal lo muestra.

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

### Fase 3.5 — Portal del Cliente (8–12 semanas, paralelo con fase 1 y 2 una vez arranque la 1)

Construir `miportal.homedelvalle.mx` como subdominio dedicado y migrar el actual `/portal`. Esto es la primera vez que un cliente nuevo de HDV entra a un destino digital propio el mismo día que firma. Spec completo en `06-PORTAL-DEL-CLIENTE.md`.

- [ ] **Sub-fase A (2 sem):** subdominio en cPanel con SSL, layout Blade nuevo, auth + login + recuperar contraseña, dashboard básico para inquilino y propietario.
- [ ] **Sub-fase B (2 sem):** documentos descargables, subida de docs por el cliente, threads de mensajes con HDV (Livewire), notificaciones in-portal, plantilla `portal_welcome` y trigger automático al firmar captación o arrendamiento.
- [ ] **Sub-fase C (3 sem):** vistas detalladas de "Mi renta" (inquilino), reportes mensuales descargables (propietario), recibos PDF, recordatorios de pago automáticos, banner de pago vencido.
- [ ] **Sub-fase D (2 sem):** timelines de operación de venta y captación, reportar incidente con upload de fotos, onboarding del primer login.
- [ ] **Sub-fase E (1 sem):** vista "preview as client" desde el admin con audit log, centro de preferencias de notificación, búsqueda global, pruebas de accesibilidad y mobile.
- [ ] **Sub-fase F (continuo):** pasarela de pago, firma electrónica integrada vía Mifiel, calendario de visitas, push notifications.

**Criterio de hecho de la fase:** un cliente que firma captación recibe email a los pocos segundos, activa cuenta, entra al portal, descarga su contrato, ve su agente, manda un mensaje y recibe respuesta — todo desde mobile y sin llamar.

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
              Fase 1 (comprador) ───┬─────────────────┐
                    │               │                 │
                    ▼               ▼                 ▼
           Fase 2 (B2B)        Fase 3 (atribución)   Fase 3.5 (Portal del Cliente)
                    │               │                 │
                    └───────┬───────┴─────────┬───────┘
                            ▼                 │
                    Fase 4 (refactor)         │
                            │                 │
                            └────────┬────────┘
                                     ▼
                            Fase 5 (escalamiento)
```

Fase 1 y Fase 2 pueden correr en paralelo si hay capacidad de implementación. Fase 3 requiere tener al menos Fase 1 publicada porque sin tráfico al funnel no hay datos para atribuir. **Fase 3.5 (Portal del Cliente) puede arrancar en cuanto Fase 1 publica los primeros formularios** — el portal se enriquece de los datos que generan los funnels nuevos. Fase 4 y 5 esperan crecimiento real (>5,000 ops, >300 MB de DB).

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
| 9 | Portal del Cliente vive en subdominio dedicado `miportal.homedelvalle.mx`, no en sub-ruta del sitio. | Marca y memorabilidad, separación visual app vs sitio web, políticas de cookies/seguridad independientes. | Si Google penaliza subdominios de SEO o si decidimos consolidar a un solo dominio por costos de SSL/DNS. |
| 10 | Cuenta de portal se crea automáticamente al firmar el primer hito (captación, contrato, oferta). No es opcional ni manual. | La promesa "Más control" exige que el cliente tenga acceso desde día 1. La activación manual genera fricción y desuso. | Si los costos operativos de cuentas inactivas se vuelven relevantes (>10,000 cuentas dormidas). |
| 11 | Toda comunicación HDV ↔ cliente que no sea WhatsApp o llamada se centraliza en `MessageThread` del portal. Email se usa como notificación, no como canal primario. | Centraliza historial, evita "yo nunca recibí ese mensaje", reduce dependencia del email del cliente. | Si una integración (ej. Slack Connect, WhatsApp Business API formal) cambia el balance. |
| 12 | Privacidad estricta entre las partes en el portal: inquilino no ve datos completos del propietario y viceversa. Toda comunicación pasa por HDV. | Refuerza el rol de HDV como intermediario profesional y evita conflictos directos. | No aplica revisar; es una política permanente. |

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
| **% de clientes activos que entran al portal mensualmente** | n/d (baseline al lanzar) | ≥ 60% | ≥ 80% |
| **% de mensajes HDV ↔ cliente vía portal vs WhatsApp/email** | n/d | ≥ 40% portal | ≥ 60% portal |
| **% de documentos descargados por el cliente desde el portal vs reenvío manual** | n/d | ≥ 70% portal | ≥ 90% portal |
| **NPS del cliente sobre el portal** | n/d (preguntar al cierre y a 90 días) | ≥ 8/10 | ≥ 9/10 |

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
