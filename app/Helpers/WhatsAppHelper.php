<?php

namespace App\Helpers;

class WhatsAppHelper
{
    /**
     * Obtiene opciones de WhatsApp contextualizadas según la página actual
     */
    public static function getOptions(): array
    {
        $currentRoute = request()->route()?->getName() ?? '';
        $currentPath = request()->path();

        return match (true) {
            str_contains($currentPath, 'vende-tu-propiedad')   => self::sellerOptions(),
            str_contains($currentPath, 'renta-tu-propiedad')   => self::rentalOwnerOptions(),
            str_contains($currentPath, '/comprar')             => self::buyerOptions(),
            str_contains($currentPath, '/rentar')              => self::renterOptions(),
            str_contains($currentPath, '/desarrolladores')     => self::investorOptions(),
            str_contains($currentPath, '/mercado')             => self::marketOptions(),
            str_contains($currentPath, '/servicios')           => self::servicesOptions(),
            str_contains($currentPath, '/contacto')            => self::generalOptions(),
            default                                            => self::homeOptions(),
        };
    }

    /**
     * Opciones para página de COMPRA
     */
    private static function buyerOptions(): array
    {
        return [
            [
                'icon' => '🔍',
                'label' => 'Estoy buscando propiedad',
                'subtitle' => 'Me gustaría conocer opciones disponibles',
                'message' => 'Hola! Estoy interesado en buscar una propiedad en Benito Juárez. ¿Cuáles son las opciones disponibles?',
            ],
            [
                'icon' => '💰',
                'label' => 'Preguntar sobre presupuesto',
                'subtitle' => 'Asesoría sobre rangos de precios',
                'message' => 'Hola! Quisiera saber qué opciones hay en un cierto rango de presupuesto en Benito Juárez.',
            ],
            [
                'icon' => '📋',
                'label' => 'Requisitos de financiamiento',
                'subtitle' => 'Información sobre créditos e INFONAVIT',
                'message' => 'Hola! ¿Qué opciones de financiamiento tienen disponibles?',
            ],
            [
                'icon' => '📞',
                'label' => 'Agendar asesoría',
                'subtitle' => 'Hablar con un asesor especializado',
                'message' => 'Hola! Me gustaría agendar una llamada con un asesor para hablar sobre mi búsqueda.',
            ],
        ];
    }

    /**
     * Opciones para página de VENTA
     */
    private static function sellerOptions(): array
    {
        return [
            [
                'icon' => '🏠',
                'label' => 'Valuación gratuita',
                'subtitle' => 'Sé cuánto vale tu propiedad',
                'message' => 'Hola! Me gustaría una valuación gratuita de mi propiedad en Benito Juárez.',
            ],
            [
                'icon' => '⚡',
                'label' => 'Proceso de venta',
                'subtitle' => 'Cómo funciona vender con nosotros',
                'message' => 'Hola! ¿Cuál es el proceso para vender mi propiedad con ustedes?',
            ],
            [
                'icon' => '⏱️',
                'label' => 'Tiempo de venta',
                'subtitle' => 'Promedio: 45 días',
                'message' => 'Hola! ¿En cuánto tiempo pueden vender mi propiedad?',
            ],
            [
                'icon' => '📄',
                'label' => 'Documentos necesarios',
                'subtitle' => 'Qué papers necesitarás',
                'message' => 'Hola! ¿Qué documentos necesito para vender mi propiedad?',
            ],
        ];
    }

    /**
     * Opciones para página de INVERSIÓN/DESARROLLO
     */
    private static function investorOptions(): array
    {
        return [
            [
                'icon' => '💼',
                'label' => 'Compartir brief',
                'subtitle' => 'Envía tu brief de requerimientos',
                'message' => 'Hola! Me gustaría enviar mi brief de inversión para que me aconsejen sobre oportunidades.',
            ],
            [
                'icon' => '📊',
                'label' => 'Análisis de inversión',
                'subtitle' => 'ROI y viabilidad de proyectos',
                'message' => 'Hola! ¿Cuál es el análisis de retorno para inversiones en esta zona?',
            ],
            [
                'icon' => '🤝',
                'label' => 'Co-inversión',
                'subtitle' => 'Oportunidades de proyectos',
                'message' => 'Hola! Estoy interesado en co-inversión o coinversión en proyectos. ¿Qué opciones tienen?',
            ],
            [
                'icon' => '📅',
                'label' => 'Agendar llamada',
                'subtitle' => 'Hablar con nuestro equipo B2B',
                'message' => 'Hola! Me gustaría agendar una llamada con el equipo para discutir oportunidades.',
            ],
        ];
    }

    /**
     * Opciones generales (home, etc) — 4 funnels + consulta
     */
    private static function homeOptions(): array
    {
        return [
            [
                'icon'     => '🏠',
                'label'    => 'Quiero vender mi propiedad',
                'subtitle' => 'Valuación gratuita y venta en 45 días',
                'message'  => 'Hola! Tengo una propiedad en Benito Juárez y quisiera venderla. ¿Pueden hacer una valuación?',
            ],
            [
                'icon'     => '🔍',
                'label'    => 'Estoy buscando dónde comprar',
                'subtitle' => 'Búsqueda curada en Benito Juárez',
                'message'  => 'Hola! Estoy buscando una propiedad para comprar en Benito Juárez. ¿Pueden ayudarme?',
            ],
            [
                'icon'     => '🔑',
                'label'    => 'Quiero rentar para vivir',
                'subtitle' => 'Curación personalizada en 72 horas',
                'message'  => 'Hola! Estoy buscando un inmueble en renta en Benito Juárez. ¿Pueden enviarme opciones curadas?',
            ],
            [
                'icon'     => '🏢',
                'label'    => 'Quiero rentar mi inmueble',
                'subtitle' => 'Inquilino calificado + póliza jurídica',
                'message'  => 'Hola! Tengo un inmueble y quisiera ponerlo en renta. ¿Cómo funciona el proceso?',
            ],
            [
                'icon'     => '❓',
                'label'    => 'Consulta general',
                'subtitle' => 'Pregunta lo que necesites',
                'message'  => 'Hola! Tengo una consulta sobre servicios inmobiliarios en Benito Juárez.',
            ],
        ];
    }

    /**
     * Opciones para /rentar (arrendatarios buscando dónde vivir)
     */
    private static function renterOptions(): array
    {
        return [
            [
                'icon'     => '🔑',
                'label'    => 'Quiero rentar un inmueble',
                'subtitle' => 'Envíame opciones curadas en 72 horas',
                'message'  => 'Hola! Estoy buscando un inmueble en renta en Benito Juárez. ¿Pueden enviarme opciones que coincidan con mi perfil?',
            ],
            [
                'icon'     => '📋',
                'label'    => 'Enviar mi brief de búsqueda',
                'subtitle' => 'Zona, presupuesto y preferencias',
                'message'  => 'Hola! Quisiera enviarles mis requerimientos para que me busquen opciones de renta: zona, presupuesto, recámaras y si acepto mascotas.',
            ],
            [
                'icon'     => '🛡️',
                'label'    => 'Preguntar sobre garantías',
                'subtitle' => 'Póliza jurídica, aval o depósito',
                'message'  => 'Hola! ¿Qué opciones de garantía manejan para rentar (póliza jurídica, aval, depósito ampliado)?',
            ],
            [
                'icon'     => '📅',
                'label'    => 'Ver opciones esta semana',
                'subtitle' => 'Listo para visitar y decidir',
                'message'  => 'Hola! Estoy listo para ver opciones de renta esta semana. ¿Pueden contactarme para agendar visitas?',
            ],
        ];
    }

    /**
     * Opciones para /renta-tu-propiedad (propietarios que quieren rentar su inmueble)
     */
    private static function rentalOwnerOptions(): array
    {
        return [
            [
                'icon'     => '🏠',
                'label'    => 'Quiero rentar mi inmueble',
                'subtitle' => 'Asesoría gratuita en menos de 24 horas',
                'message'  => 'Hola! Tengo un inmueble en Benito Juárez y quisiera ponerlo en renta. ¿Pueden contactarme?',
            ],
            [
                'icon'     => '📊',
                'label'    => '¿Cuánto puedo pedir de renta?',
                'subtitle' => 'Rango de renta basado en datos reales',
                'message'  => 'Hola! ¿Pueden decirme cuánto podría pedir de renta por mi inmueble en Benito Juárez?',
            ],
            [
                'icon'     => '🛡️',
                'label'    => 'Quiero póliza jurídica',
                'subtitle' => 'Protección ante incumplimiento',
                'message'  => 'Hola! Me interesa contratar póliza jurídica para mi inmueble en renta. ¿Cómo funciona?',
            ],
            [
                'icon'     => '⚙️',
                'label'    => 'Administración integral',
                'subtitle' => 'Cobranza, mantenimiento y reportes',
                'message'  => 'Hola! Me interesa la administración integral de mi inmueble en renta. ¿Qué incluye el servicio?',
            ],
        ];
    }

    /**
     * Opciones para página de CONTACTO
     */
    private static function generalOptions(): array
    {
        return self::homeOptions();
    }

    /**
     * Opciones para página de MERCADO
     */
    private static function marketOptions(): array
    {
        return [
            [
                'icon' => '📊',
                'label' => 'Tendencias de precios',
                'subtitle' => 'Análisis del mercado actual',
                'message' => 'Hola! ¿Cuáles son las tendencias actuales de precios en Benito Juárez?',
            ],
            [
                'icon' => '🏘️',
                'label' => 'Precios por zona',
                'subtitle' => 'Comparativa por colonia',
                'message' => 'Hola! ¿Cuál es el precio promedio por metro cuadrado en diferentes colonias?',
            ],
            [
                'icon' => '📈',
                'label' => 'Proyección de valores',
                'subtitle' => 'Estimado de crecimiento',
                'message' => 'Hola! ¿Cuál es la proyección de valores para el próximo año?',
            ],
            [
                'icon' => '💬',
                'label' => 'Hablar con analista',
                'subtitle' => 'Consulta personalizada',
                'message' => 'Hola! Me gustaría hablar con un analista sobre el mercado inmobiliario.',
            ],
        ];
    }

    /**
     * Opciones para página de SERVICIOS
     */
    private static function servicesOptions(): array
    {
        return [
            [
                'icon' => '🔑',
                'label' => 'Corretaje premium',
                'subtitle' => 'Venta y renta de inmuebles',
                'message' => 'Hola! Quisiera conocer más sobre el servicio de corretaje premium.',
            ],
            [
                'icon' => '🏗️',
                'label' => 'Desarrollo inmobiliario',
                'subtitle' => 'Captación y colocación de predios',
                'message' => 'Hola! Estoy interesado en el servicio de desarrollo inmobiliario.',
            ],
            [
                'icon' => '📋',
                'label' => 'Administración de inmuebles',
                'subtitle' => 'Gestión integral de propiedades',
                'message' => 'Hola! ¿Cuál es el servicio de administración de inmuebles?',
            ],
            [
                'icon' => '⚖️',
                'label' => 'Legal y gestoría',
                'subtitle' => 'Asesoría legal inmobiliaria',
                'message' => 'Hola! Necesito asesoría legal para una transacción inmobiliaria.',
            ],
        ];
    }
}
