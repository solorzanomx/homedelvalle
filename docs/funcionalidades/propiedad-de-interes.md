# Propiedad de interés — de lead a cliente, sin perder de qué se trata

> 2026-10-02. Lee esto antes de tocar el selector "Propiedad de interés" en `clients/create` o
> `clients/edit`, o la creación automática de `Deal` en `LeadConversionService`.

## Qué resuelve
Caso real: Yarlin (lead de Inmuebles24, interesada en un depa específico) se convirtió a cliente,
pero su ficha mostraba **"Propiedades · 0"** — nada indicaba de qué inmueble se trataba sin ir a
buscar el formulario original o preguntarle de nuevo. El dato de interés vivía solo dentro del
`payload` de un lead (`propiedad_local_id`, lo pone `Inmuebles24LeadImporter`) — nunca se
convertía en algo que la pestaña "Propiedades" de `clients/show` supiera leer (esa pestaña lee
`Deal`, vía `Property::whereHas('deals', ...)`).

## Qué se construyó
1. **`LeadConversionService::convert()`** ya detectaba `property_id` (Fase anterior, 2026-10-01) —
   ahora, si lo detecta, también crea el `Deal` (`client_id` + `property_id`, `stage='lead'`) que
   alimenta la pestaña "Propiedades". `firstOrCreate`: si el trato ya existe (quizás avanzado de
   etapa), no lo toca.
2. **Selector "Propiedad de interés"** en `clients/create` y `clients/edit` (sección "Preferencias
   de Búsqueda"): lista las propiedades disponibles; al guardar, crea (o confirma, sin resetear
   stage) el mismo tipo de `Deal`. Así cualquier cliente — venga o no de un lead con propiedad
   detectada — puede quedar ligado a mano a la propiedad de la que se trata.

## Mapa de archivos
| Qué | Dónde |
|---|---|
| Crea el Deal al convertir un lead | `app/Services/LeadConversionService.php` |
| Selector + guardado en crear/editar cliente | `ClientController::create/store/edit/update`, `resources/views/clients/{create,edit}.blade.php` |
| Lee el Deal para la pestaña "Propiedades" | `ClientController::show()` (`$dealProperties`, ya existía) |

## INVARIANTES — no romper
- **Siempre `firstOrCreate`, nunca `updateOrCreate`**, para el Deal de "propiedad de interés" —
  si el trato ya avanzó de etapa (negociación, oferta...), guardar el formulario del cliente
  (por cualquier otro motivo) no debe resetearlo a `'lead'`.
- El campo `property_of_interest_id` del form **no es una columna de `clients`** — se descarta del
  array `$validated` antes de `Client::create()`/`update()` y se procesa aparte contra `Deal`.
- Dejar el selector vacío nunca borra un Deal ya existente — solo crea uno nuevo si se elige algo.

## Cómo probarlo
`php artisan test --filter=LeadConversionServiceTest` (incluye la aserción del Deal creado al
convertir). El selector de crear/editar cliente se verificó a mano contra la BD local (llamadas
directas al controlador, transacción con rollback): aparece en ambas vistas, preselecciona el
trato existente al editar, y no se duplica al reeditar.
