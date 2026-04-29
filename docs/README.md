# Documentación maestra · Home del Valle

> Manuales fuente de verdad de la empresa. Todo lo que se construye en HDV se valida contra estos documentos.
>
> **Última actualización del índice:** 2026-04-27.

---

## Cómo está organizada la documentación

Home del Valle tiene **dos capas de documentación** que conviven:

| Carpeta | Audiencia | Naturaleza |
|---|---|---|
| `.claude/` | Senior engineers, arquitectos | Análisis del esquema y arquitectura del sistema (108 tablas, 93 modelos). Detalle técnico bajo el capó. |
| `docs/` (esta carpeta) | Alex, equipo interno, devs externos | Manuales operativos y de marca. Cómo trabajamos, cómo escribimos, cómo construimos. |

Ambas capas son fuente de verdad. La de `.claude/` se actualiza cuando cambia el esquema de la base de datos. La de `docs/` se actualiza cuando cambian procesos, marca, copy o estrategia.

---

## Los cuatro manuales maestros

### 1. Manual de Marca y Voz
`docs/01-MANUAL-MARCA-Y-VOZ.docx` (vive en la carpeta de Cowork del proyecto, no en el repo, porque es un documento presentable que se comparte fuera del equipo técnico).

**Cuándo usarlo:** antes de escribir cualquier copy, slogan, email, post, ficha de propiedad, anuncio o microcopy. Define identidad, tono, slogan, paleta, tipografías, do's y don'ts.

**Mantenedor:** Alex.

### 2. Manual de Implementación del Sitio
[`docs/02-MANUAL-IMPLEMENTACION-SITIO.md`](./02-MANUAL-IMPLEMENTACION-SITIO.md)

**Cuándo usarlo:** antes de tocar código en el sitio público o el CRM. Cubre 16 secciones: stack, estructura, cómo agregar páginas, formularios, propiedades, cómo deployar, qué patrones evitar.

**Audiencia:** Alex y desarrolladores externos (Claude Code u otros).

**Mantenedor:** Alex.

### 3. Manual de Operaciones CRM y Leads
`docs/03-MANUAL-OPERACIONES-CRM.docx` (vive en la carpeta de Cowork del proyecto).

**Cuándo usarlo:** define SLAs, scripts de primer contacto, ciclo de vida de un lead, asignación, follow-ups, KPIs por agente. Es el "manual de operaciones" del equipo.

**Audiencia:** Alex, Ana Laura, agentes y futuros colaboradores.

**Mantenedor:** Alex (revisado por Ana Laura).

### 4. Roadmap y Arquitectura Objetivo
[`docs/04-ROADMAP-Y-ARQUITECTURA.md`](./04-ROADMAP-Y-ARQUITECTURA.md)

**Cuándo usarlo:** para entender qué se está construyendo y en qué orden. Cubre snapshot del estado actual, visión Opción C, fases 0–5, decisiones técnicas tomadas, decisiones pendientes, métricas de éxito, riesgos.

**Audiencia:** Alex y stakeholders técnicos/comerciales.

**Mantenedor:** Alex.

### 5. Proceso de Renta
[`docs/05-PROCESO-DE-RENTA.md`](./05-PROCESO-DE-RENTA.md)

**Cuándo usarlo:** para diseñar, implementar y operar el funnel de renta como un proceso de tres fases (Captación / Colocación / Gestión post-cierre). Documento operativo + técnico que sirve como blueprint para el CRM (`/admin/rentas`) y para el equipo. Incluye stages detallados, SLAs, documentos por etapa, automatizaciones, comisiones y casos especiales.

**Audiencia:** Alex, Ana Laura, agentes de captación y corretaje, administradores, Claude Code.

**Mantenedor:** Alex (revisado por Ana Laura).

### 6. Portal del Cliente
[`docs/06-PORTAL-DEL-CLIENTE.md`](./06-PORTAL-DEL-CLIENTE.md)

**Cuándo usarlo:** para diseñar, implementar y operar `miportal.homedelvalle.mx` — el subdominio dedicado donde cada cliente (propietario, inquilino, comprador, vendedor) puede acceder en cualquier momento a su operación, documentos, pagos y comunicaciones con HDV. Es la pieza diferenciadora del producto. Incluye 4 perfiles de usuario con qué ve cada uno, alta de cuenta, comunicaciones bidireccionales, notificaciones, seguridad, vista "preview as client" desde el admin, y roadmap de implementación en 6 fases.

**Audiencia:** Alex, Ana Laura, agentes, administradores, Claude Code.

**Mantenedor:** Alex (revisado por Ana Laura).

---

## Documentación complementaria existente en el repo

Antes de leer los manuales nuevos, conviene tener presente lo que ya estaba documentado:

| Documento | Qué cubre |
|---|---|
| [`README.md`](../README.md) (raíz) | Boilerplate Laravel; placeholder. |
| [`CONTEXTO_PROYECTO.md`](../CONTEXTO_PROYECTO.md) | Snapshot del estado del CRM (módulos, modelos, rutas, decisiones). |
| [`CRITICAL_VERSIONS.md`](../CRITICAL_VERSIONS.md) | Versiones de librerías críticas y reglas de actualización. |
| [`IMPLEMENTATION_RULES.md`](../IMPLEMENTATION_RULES.md) | Reglas obligatorias antes de implementar cualquier feature. |
| [`DEPLOYMENT_GUIDE.md`](../DEPLOYMENT_GUIDE.md) | Procedimiento paso a paso para desplegar a cPanel. |
| [`DOCUMENTATION_INDEX.md`](../DOCUMENTATION_INDEX.md) | Índice antiguo de documentación técnica. |
| [`GALLERY_PREMIUM_DOCS.md`](../GALLERY_PREMIUM_DOCS.md) | Documentación de la galería premium de propiedades. |
| [`QR_IMPLEMENTATION.md`](../QR_IMPLEMENTATION.md) | Sistema de generación y reutilización de QRs. |
| [`.claude/DATABASE_SCHEMA.md`](../.claude/DATABASE_SCHEMA.md) | Esquema completo de las 108 tablas. |
| [`.claude/SCHEMA_QUICK_REFERENCE.md`](../.claude/SCHEMA_QUICK_REFERENCE.md) | Cheat sheet del esquema para devs. |
| [`.claude/ARCHITECTURE_ANALYSIS.md`](../.claude/ARCHITECTURE_ANALYSIS.md) | Análisis estratégico del sistema. |
| [`.claude/SYSTEM_DOCUMENTATION_INDEX.md`](../.claude/SYSTEM_DOCUMENTATION_INDEX.md) | Navegación entre los docs de `.claude/`. |

**Importante:** los documentos del repo (`CRITICAL_VERSIONS`, `IMPLEMENTATION_RULES`, `DEPLOYMENT_GUIDE`) **siguen siendo fuente de verdad** para temas de versiones, reglas de implementación y deploy. Los manuales nuevos en `docs/` no los reemplazan, los complementan.

---

## Mapa de "qué leer cuando"

| Necesito… | Voy primero a… | Y referencio… |
|---|---|---|
| Escribir copy nuevo | Manual de Marca y Voz | Roadmap (para saber a qué fase aporta) |
| Implementar una feature técnica | Manual de Implementación + `IMPLEMENTATION_RULES.md` | `CRITICAL_VERSIONS.md`, `.claude/SCHEMA_QUICK_REFERENCE.md` |
| Atender un lead que llegó al CRM | Manual de Operaciones CRM y Leads | — |
| Decidir si construir feature X ahora o después | Roadmap | Estado actual en `.claude/ARCHITECTURE_ANALYSIS.md` |
| Desplegar a producción | `DEPLOYMENT_GUIDE.md` | `IMPLEMENTATION_RULES.md` |
| Subir una propiedad | Manual de Implementación, sección 5 | `.claude/SCHEMA_QUICK_REFERENCE.md` (tabla `properties`) |
| Editar el navbar o footer | Manual de Implementación, sección 6 | — |
| Modificar el chatbot calificador | Manual de Implementación, sección 7 | Manual de Marca y Voz (preguntas y tono) |
| Crear un nuevo template de email | Manual de Implementación, sección 10 | Manual de Marca y Voz (tono) |
| Diseñar o ajustar el flujo de renta (captación, colocación, gestión) | Proceso de Renta | Manual de Operaciones (SLAs generales) |
| Implementar `/admin/rentas` o vistas separadas del CRM | Proceso de Renta sección 6 y 13 | Manual de Implementación |
| Configurar cobranza mensual o renovación automática | Proceso de Renta sección 10 | Manual de Implementación, sección 10 |
| Implementar o ajustar el portal del cliente (`miportal.homedelvalle.mx`) | Portal del Cliente | Manual de Implementación |
| Activar acceso de portal a un cliente específico | Portal del Cliente sección 5 | Manual de Operaciones |
| Diseñar UX o pantalla nueva del portal | Portal del Cliente sección 8 | Manual de Marca y Voz |
| Resolver acceso o privacidad entre cliente A y cliente B en el portal | Portal del Cliente sección 9 | — |
| Resolver un bug en QR | `QR_IMPLEMENTATION.md` + `CRITICAL_VERSIONS.md` | — |
| Onboarding de un agente nuevo | Manual de Operaciones (proceso) + Manual de Marca (cómo hablamos) | Proceso de Renta si trabajará rentas |
| Onboarding de un dev nuevo | Este README + Manual de Implementación + `.claude/SCHEMA_QUICK_REFERENCE.md` | — |

---

## Disciplina de mantenimiento

Para que estos documentos sigan siendo útiles a 6, 12, 24 meses:

- **Revisión mensual** durante fases 0–2 del Roadmap (abril–septiembre 2026).
- **Revisión trimestral** después.
- **Actualización inmediata** cuando:
  - Cambia el slogan, marca, dirección, equipo. → Manual de Marca.
  - Cambia el stack (Laravel, Tailwind, PHP). → Manual de Implementación + `CRITICAL_VERSIONS.md`.
  - Cambia un proceso de captación o seguimiento. → Manual de Operaciones.
  - Se cierra una fase o se abre una nueva. → Roadmap.
  - Se agrega una tabla nueva. → `.claude/DATABASE_SCHEMA.md`.

**Cualquier cambio sustantivo en este conjunto se commitea con `docs:` como prefijo del commit y se anota en el changelog del documento afectado.**

---

## Convenciones de los documentos

- Idioma: español neutro de México.
- Tono: directo, sobrio, sin adornos. Mismo tono que el sitio público (ver Manual de Marca).
- Formato técnico: markdown para los que viven en el repo; docx para los que se imprimen o comparten fuera.
- Versiones: cada documento tiene `Estado: vN`, `Última revisión: YYYY-MM-DD`, `Mantenedor: nombre`.
- Cross-referencia: los documentos se citan entre sí con su nombre completo y ruta relativa.

---

## Roadmap de la documentación misma

- [x] v0 de los 4 manuales maestros (abril 2026).
- [ ] v1 con feedback del equipo y casos reales (junio 2026).
- [ ] Manual de Marketing y Contenido (cuándo: cuando se contrate responsable de marketing).
- [ ] Manual de Onboarding de Cliente (Portal) (cuándo: fase 3 del Roadmap).
- [ ] Manual de Producto Inmobiliario (qué inmuebles aceptamos, cómo se evalúan) (cuándo: fase 1–2).
- [ ] Manual Legal (procedimientos notariales, blindaje, custodias) (lo escribe Ana Laura).

---

**Inicio del recorrido recomendado para alguien que llega nuevo:**
1. Lee este README.
2. Lee el `04-ROADMAP-Y-ARQUITECTURA.md` para entender hacia dónde vamos.
3. Lee el `01-MANUAL-MARCA-Y-VOZ.docx` para entender la voz.
4. Si trabajarás técnico, lee el `02-MANUAL-IMPLEMENTACION-SITIO.md`.
5. Si trabajarás con leads, lee el `03-MANUAL-OPERACIONES-CRM.docx`.
6. Lee `.claude/SCHEMA_QUICK_REFERENCE.md` y `CONTEXTO_PROYECTO.md` para tener el mapa técnico.

Bienvenido a Home del Valle Bienes Raíces. Pocos inmuebles. Más control. Mejores resultados.
