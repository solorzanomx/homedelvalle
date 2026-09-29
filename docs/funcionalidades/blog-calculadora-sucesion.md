# Blog: calculadora de costo de sucesión

> 2026-09-30. Fase 4 de `prompt-claude-code-blog-leads.md` (Alejandro). Léelo antes de tocar
> `SuccessionCalculatorConfig`, `SuccessionCalculator`, o `/admin/succession-calculator`.

## ⚠️ Ninguna cifra de esta fase viene de un notario real
El prompt pidió explícitamente **no inventar cifras** y dejar todo marcado `PENDIENTE VALIDAR`. Así
quedó: las 2 filas de `succession_calculator_configs` (con testamento / sin testamento) se sembraron
con `validated = false` y rangos de referencia general — estructuralmente correctos (qué conceptos
existen: trámite notarial o juicio, ISAI, registro, avalúo, otros, extra por heredero, regularizar
escrituras), pero **las cifras necesitan que Alejandro las confirme con su notario** antes de que la
calculadora se pueda llamar "precisa". Mientras `validated = false`, la calculadora se lo dice al
lector en pantalla (aviso ámbar "estimación de referencia, no confirmada").

**Lista completa de parámetros pendientes de validar** (editables en `/admin/succession-calculator`,
uno por escenario):
- % del trámite notarial (con testamento) / juicio sucesorio o notarial (sin testamento) sobre el valor del inmueble
- % de ISAI — en CDMX suele haber exención o reducción para herederos directos; hay que confirmar si aplica y cuánto
- Registro Público de la Propiedad (rango en pesos)
- Avalúo (rango en pesos)
- Otros: edictos, gestoría, copias certificadas (rango en pesos)
- Costo por cada heredero adicional al primero
- Costo de regularizar escrituras cuando el inmueble no está a nombre del difunto (solo sin testamento)
- Tiempo estimado del trámite, en meses

## Cómo funciona
1. **Entradas**: valor aproximado del inmueble, con/sin testamento, número de herederos, si ya hay
   escrituras a nombre del difunto.
2. **Salida**: desglose por concepto (rango min-max), tiempo estimado, y el aviso de "es una
   estimación, no una cotización" siempre visible.
3. **Después del resultado**: "¿Quieres el desglose exacto para tu caso y cuánto pagarías de ISR si
   vendes después? Déjanos tu WhatsApp" — mismo patrón sin-email de la Fase 3 (`CtaCapture`): WhatsApp
   obligatorio, nombre opcional, respuesta en pantalla + botón para seguir por WhatsApp (mensaje con
   el estimado y la referencia del post).
4. El lead (`form_type = 'vendedor_predio'`, `lead_tag = 'LEAD_CALCULADORA_SUCESION'`) lleva en su
   `payload` el valor del inmueble, con/sin testamento, herederos, escrituras, el estimado min/max y
   si esos rangos ya estaban validados.
5. GA4: `calculator_start` (primer campo tocado) y `calculator_complete` (al calcular, con
   `con_testamento` y `rangos_validados` como parámetros) — el plumbing ya existía documentado desde
   la Fase 2, aquí se conectó de verdad.

## Dónde vive
Se activa por post con la casilla **"Mostrar la calculadora de costo de sucesión"** en el editor —
sembrada ya en `cuanto-cuesta-sucesion-cdmx-2026` y `propiedad-sin-testamento-cdmx-como-regularizar-vender-2026`
(migración `2026_09_30_100001`), pero "queda disponible para otros posts" tal como pide el prompt:
cualquier post puede activarla desde el admin, no hay lista fija en código.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Parámetros (editables) | `app/Models/SuccessionCalculatorConfig.php`, `Admin\SuccessionCalculatorConfigController`, `/admin/succession-calculator` |
| Cálculo | `SuccessionCalculatorConfig::estimate()` — única función que produce cifras, todo sale de la fila de config |
| Componente | `app/Livewire/Blog/SuccessionCalculator.php` + `livewire/blog/succession-calculator.blade.php` |
| Activación por post | `posts.show_succession_calculator` (migración `2026_09_30_100001`), casilla en `admin/posts/{create,edit}` |
| Posición en el post | `blog/show.blade.php`, justo antes del form de valuación a media lectura (después de la respuesta corta, momento de más intención) |
| GA4 | `layouts/public.blade.php` — `Livewire.on('calculator-event', ...)` |

## INVARIANTES — no romper
- **`estimate()` nunca usa una cifra que no venga de la fila de `SuccessionCalculatorConfig`** — si se
  necesita otro concepto (p. ej. IVA sobre honorarios), se agrega como columna nueva, nunca hardcodeado.
- El aviso "⚠ estimación no validada" se basa en `validated` de la BD, no en un comentario en código —
  cuando Alejandro confirme los rangos y marque la casilla en el admin, el aviso desaparece solo.
- Mismo patrón sin-email que la Fase 3 (`form_submissions.email` nullable, `full_name` con fallback
  "Lead del blog", sin `AutomationEngine::processFormSubmitted()`).
- Render completo de `blog/show` es demasiado pesado para SQLite en memoria (mismo criterio que fases
  anteriores) — verificado a mano contra la BD local; los tests cubren `SuccessionCalculatorConfig` y
  `SuccessionCalculator` de forma aislada.

## Pendiente / para ti (Alejandro)
1. **Confirma con tu notario los 8 parámetros listados arriba**, para los 2 escenarios, y edítalos en
   `/admin/succession-calculator` (marca "Ya confirmé estos rangos" cuando lo hagas — el aviso de la
   calculadora cambia solo).
2. Registra `calculator_start`/`calculator_complete` en GA4 si quieres verlos en el funnel (Admin →
   Definiciones personalizadas → Eventos, se procesan solos tras la primera ejecución real).
3. Decide si quieres activar la calculadora en más posts del cluster herencias además de los 2 iniciales.
