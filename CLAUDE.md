# Home del Valle — Guía obligatoria para cualquier sesión de Claude Code

CRM inmobiliario + sitio público + Portal del Cliente (Laravel 13, PHP 8.3; SQLite local / MySQL producción).
Hosts: **admin.homedelvalle.mx** (CRM) · **homedelvalle.mx** (sitio) · **miportal.homedelvalle.mx** (Portal).
Dueño: Alejandro Solórzano — broker activo que opera el negocio él mismo (no solo pide código: da consejo de negocio cuando describe un caso).

## 0. ANTES de tocar nada (2 minutos que evitan romper cosas)
1. Lee tu **memoria de Claude Code** (`MEMORY.md`, se carga sola): ~40 notas de decisiones, incidentes y gotchas. Abre las del área que vas a tocar. *(Está fuera del repo a propósito, ver §5.)*
2. Si el área tiene doc en **`docs/funcionalidades/`**, léelo COMPLETO — trae mapa de archivos e **INVARIANTES** (lo que no se debe romper).
3. Contexto general: `CONTEXTO_PROYECTO.md`, `IMPLEMENTATION_RULES.md`, `CRITICAL_VERSIONS.md`.
4. No asumas que "esto se ve raro, lo simplifico": casi siempre hay un incidente detrás. Busca el porqué (comentarios en código con fecha, memoria, `git log -S`).

## 1. Índice de funcionalidades documentadas
| Área | Doc | Tocar con cuidado si… |
|---|---|---|
| Documentos: subida guiada + asistente, calidad, visor, bandeja "Docs por revisar", avisos/recordatorios, métricas (Rentas y Ventas) | `docs/funcionalidades/documentos-y-revision.md` | tocas uploads del Portal, `rentals/show` u `operations/show` (Documentos), `Document`, `capture=`, estados de documentos |
| Todo lo demás | memoria de Claude Code (`MEMORY.md`) | — |

> **Al terminar una función nueva, agrega su fila aquí** (ver §4).

## 2. Reglas de oro (aprendidas con incidentes reales)
- **NUNCA rehabilitar `/register`** ni abrir roles/middleware de auth sin leer `project_homedelvalle_seguridad.md` (por un incidente previo).
- **Deploy manual** (Claude no tiene SSH): commit + push en local, y entregar a Alejandro el comando para el servidor (§3). Un `git pull` solo NO refleja vistas Blade: hay que limpiar caché de vistas **y reiniciar php-fpm** (OPcache).
- **Comisión de venta: siempre 5%** (no 6%). Ver nota de comisiones antes de tocar documentos/propuestas.
- **Documentos legales (adéndum, contratos, acuerdos):** cambios quirúrgicos; NO restilizar sin mostrar PDF de muestra antes. Verifica el PDF real con `/Count`, no solo el HTML. Cláusulas ya guardadas en `document_clauses` ignoran el default del código.
- **Documentos de marca** (`/admin/documentos`): al tocar uno de los 5, actualiza `config/document_registry.php`.
- **Copy y sitio público:** lee `docs/posicionamiento-marca.md` + nota de modelo de negocio (constructor-primero; predios→desarrolladoras es el ingreso #1).
- **Blade:** no anidar `<style>` dentro de `@section('styles')`; un `{{token}}` literal en una vista se interpreta. Compila con `php artisan view:cache` para detectar errores.
- **Middleware con sesión** va en `$middleware->web()`, no `->append()`. **Schedules** viven en `routes/console.php` (`Kernel.php` no corre).
- **Toda `Operation` nueva pasa por `OperationObserver`** (autocorrige phase/type/stage). Lee la nota antes de crear una por un camino nuevo.
- **Propiedades públicas:** `reservada/vendida/rentada` se ven con letrero; `archived` se oculta.
- **Cada módulo/feature nuevo entrega su artículo del Manual del Broker en la misma sesión:** `database/seeders/help-articles/{slug}.md` + migración que lo siembra (patrón de `2026_09_25_130000_seed_help_revision_documentos.php`; editar el .md después NO actualiza la BD, requiere migración de resync).
- **Subidas de documentos:** jamás `capture=` en `<input type=file>`; todo punto de subida del Portal pasa por `DocumentQualityService` y lleva `data-hdv-assist`. **Aprobar/rechazar un documento SIEMPRE por `DocumentReviewService::apply()`.**

## 3. Deploy (se lo entregas a Alejandro; él lo corre en el servidor aaPanel, `/www/wwwroot/homedelvalle.mx`)
```
cd /www/wwwroot/homedelvalle.mx && git pull && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && /etc/init.d/php-fpm-83 restart
```
(Quita `migrate` si no hay migraciones; añade pasos extra —seeders, backfills— aparte, con `--dry-run` primero si tocan datos.)

## 4. Flujo de trabajo para NO perder contexto ni romper lo existente
**Antes de push, siempre:**
1. `php artisan test --filter="DocumentUploadTest|SmokeTest"` (SmokeTest compila TODOS los Blade y carga rutas; hay ~9 tests de correo ya rotos y ajenos — no los confundas con regresiones tuyas).
2. Si tocaste una vista, renderízala con datos (tinker en transacción con rollback es el patrón usado).

**Al terminar cualquier feature o cambio de comportamiento:**
1. Escribe/actualiza `docs/funcionalidades/<tema>.md`: qué resuelve, mapa de archivos, **INVARIANTES**, cómo probarlo, pendientes.
2. Añade/actualiza su fila en la tabla del §1 de este archivo.
3. Guarda la memoria personal si aplica (ver §5 sobre dónde vive y por qué).
4. Si hay una regla nueva, un test barato que la proteja (patrón: `tests/Feature/DocumentUploadTest.php`).
5. Artículo del Manual del Broker (§2). Commit + push (sin preguntar) y entrega el comando de deploy.

## 5. ⚠️ Este repositorio es PÚBLICO en GitHub
- **Nunca** subas al repo: credenciales, llaves, `.env`, host/usuario/IP del servidor, detalles de incidentes de seguridad, cuentas de intrusos, estrategia comercial interna o datos de clientes. Un intento de versionar la memoria con esos datos ya hubo que revertir (2026-09-25).
- Por eso la **memoria de Claude Code NO va en el repo**: vive en `~/.claude/projects/-Users-alejandro/memory/`. Ojo: Claude Code guarda memoria **por carpeta de lanzamiento**; para que una sesión abierta en `~/homedelvalle` vea la misma memoria, esa carpeta (`~/.claude/projects/-Users-alejandro-homedelvalle/memory`) es un enlace simbólico a la principal.
- Lo que SÍ va en el repo y es seguro: este archivo, `docs/funcionalidades/*.md` (mapa de archivos, invariantes, pendientes técnicos) y los tests.
