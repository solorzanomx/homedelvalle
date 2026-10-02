# Propuesta de Servicios (PDF) — logo correcto, 2 páginas de verdad, nombre completo del asesor

> 2026-10-02. Lee esto antes de tocar `ServiciosGeneratorService` o `resources/views/pdf/servicios.blade.php`.

## Hallazgo 1 — usaba el logo de fondo claro en un header oscuro
`ServiciosGeneratorService::buildVars()` solo leía `SiteSetting::logo_path` ("Logo para fondo
claro") — pero el header del PDF es navy oscuro, así que ese logo casi no se veía. Ya existe
`logo_path_dark` ("Logo para fondo oscuro", configurable en `/admin/settings`) y el servicio
hermano `PresentationGeneratorService` ya lo usaba correctamente — este no.

**Arreglo**: `$logoUrl` ahora resuelve `logo_path_dark` primero, con `logo_path` como fallback
solo si no hay versión oscura cargada.

## Hallazgo 2 — el PDF de renta generaba 3 páginas en vez de 2
El diseño es de 2 páginas, pero la versión de **renta** (8 tarjetas de servicio: las 6 generales
+ "Investigación de Inquilino" + "Garantías del Arrendamiento", contra 6 de venta) desbordaba por
poco el alto de la página 1 — el `.footer` de la página 1 (que vive en el flujo normal del
documento, no en posición fija) se iba solo a una página 3 casi en blanco, empujando "El Proceso
/ Condiciones" (la página 2 real) a quedar como página 3.

**Arreglo**: se recortó espaciado vertical en varios puntos de la página 1 (intro, tarjetas de
diferenciadores, grid de servicios, foto de portada) — sin tocar el contenido ni el diseño, solo
márgenes/padding. Verificado con `pdfinfo` que el PDF generado ahora tiene exactamente 2 páginas
(antes: 3).

## Hallazgo 3 — el nombre del asesor salía sin apellido
`buildVars()` usaba `$agent?->name` ("Ana Laura") en vez de `$agent?->full_name` ("Ana Laura
Monsivais Flores") — `User::getFullNameAttribute()` ya existe y junta `name` + `last_name`, y
`PresentationGeneratorService` (el servicio hermano) ya lo usaba bien; este no.

**Arreglo**: `'nombreAgente' => $agent?->full_name ?: 'Home del Valle'`.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Resolución del logo (ahora usa el oscuro) | `app/Services/ServiciosGeneratorService.php::buildVars()` |
| Plantilla + ajustes de espaciado de la página 1 | `resources/views/pdf/servicios.blade.php` |

## INVARIANTES — no romper
- **El header de este PDF es fondo oscuro — siempre usa `logo_path_dark` (con fallback a
  `logo_path`)**, nunca al revés.
- **La versión de renta tiene 2 tarjetas de servicio más que la de venta (8 vs 6)** — si se agrega
  una tarjeta más a cualquiera de las dos listas (`getServiciosMarketing()`), hay que volver a
  verificar con `pdfinfo` que la página 1 sigue cabiendo en una sola hoja; si no, recortar más
  espaciado antes de la fila nueva, no después.
- Un documento sin `Captacion` real (ej. generado para un prospecto que todavía no está en el
  sistema) muestra `"Estimado/a Estimado propietario"` por el fallback de `buildVars()` — si se
  necesita de nuevo, pasar un `nombrePropietario` explícito más corto en vez del default.

## Cómo probarlo
Generar un PDF (vía `ServiciosGeneratorService::generatePdf()` desde una Captación real, o
`renderHtml()` + Browsershot a mano para un caso sin Captación) y correr `pdfinfo archivo.pdf |
grep Pages` — debe decir `2`, nunca `3`. Revisar visualmente que el logo del header se vea blanco
sobre el navy, no oscuro-sobre-oscuro.
