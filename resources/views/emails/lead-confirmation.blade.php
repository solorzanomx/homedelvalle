@component('mail::message')
# Recibimos tu solicitud, {{ $submission->full_name }}

Agradecemos tu interés en **Home del Valle Bienes Raíces**. Hemos recibido tu {{ strtolower($formTypeLabel) }} y uno de nuestros especialistas la revisará de inmediato.

## Tiempo estimado de respuesta
Nos pondremos en contacto contigo por WhatsApp en **menos de {{ $responseTime }} hábiles**.

Si tu caso es urgente o prefieres hablar directamente, contáctanos:
- **WhatsApp:** [55 1345 0978](https://wa.me/5215513450978)
- **Email:** contacto@homedelvalle.mx

## Mientras tanto
Puedes explorar nuestras propiedades disponibles y el observatorio de precios de Benito Juárez en [homedelvalle.mx/mercado]({{ url('/mercado') }}).

---

**Pocos inmuebles. Más control. Mejores resultados.**

Home del Valle Bienes Raíces
Heriberto Frías 903-A, Colonia del Valle
Benito Juárez, CDMX 03100
@endcomponent
