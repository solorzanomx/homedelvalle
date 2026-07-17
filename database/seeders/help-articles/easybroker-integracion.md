# EasyBroker: qué hace la integración hoy

Estado real de la integración con EasyBroker (actualizado julio 2026). EasyBroker es nuestro canal hacia los portales (Inmuebles24 y demás vinculados).

## Configuración

En **Configuración → EasyBroker**:

- **API Key** — se obtiene en easybroker.com → Integraciones → API. Se guarda cifrada.
- **Probar conexión** — te confirma que la key sirve y **cuántas propiedades tiene la cuenta**.
- **Ubicación por defecto** — EasyBroker ubica por nombre de catálogo ("Del Valle Norte, Benito Juárez, Ciudad de México"), no por IDs. El buscador de esa pantalla navega su catálogo real; si una propiedad no tiene colonia, se usa esta ubicación.

## Lo que funciona hoy

✅ **Ver las propiedades publicadas** — botón "Publicadas en EasyBroker": lista solo lo que está publicado ahora mismo (la cuenta acumula todo el histórico: vendidas, borradores, suspendidas — no te confundas con el total).

✅ **Traer leads de portales** — ver el artículo "Leads de EasyBroker y portales".

✅ **Despublicar** — desde la ficha de una propiedad vinculada.

## Lo que está bloqueado (temporal)

⚠️ **Publicar una propiedad desde el CRM hacia EasyBroker**: el endpoint de creación de EasyBroker está en Beta y tiene un bug de su lado (ignora el campo de operación y responde "operation_type no ha sido seleccionado"). **Ya está reportado a su soporte** con evidencia técnica. El botón "Publicar en EasyBroker" de las propiedades mostrará ese error hasta que ellos lo corrijan — no es un fallo de nuestro sistema. Mientras tanto: las propiedades se publican manualmente en easybroker.com.

## Cuando publiques (al arreglarse el bug)

- La propiedad se crea en EasyBroker como **borrador** (not_published) a propósito: crear publicada la dispararía de inmediato a todos los portales vinculados. La revisas en EasyBroker y publicas desde allá.
- La colonia del CRM se traduce sola al catálogo de EasyBroker.

## Regla operativa

El inventario debe coincidir entre CRM y EasyBroker: si algo se vende o se pausa, se actualiza **en ambos** el mismo día — un anuncio vivo de una propiedad vendida genera leads falsos y quema la reputación con los portales.
