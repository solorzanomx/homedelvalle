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
            str_contains($currentPath, '/comprar') => self::buyerOptions(),
            str_contains($currentPath, '/vende-tu-propiedad') => self::sellerOptions(),
            str_contains($currentPath, '/desarrolladores') => self::investorOptions(),
            str_contains($currentPath, '/contacto') => self::generalOptions(),
            str_contains($currentPath, '/mercado') => self::marketOptions(),
            str_contains($currentPath, '/servicios') => self::servicesOptions(),
            default => self::homeOptions(),
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
     * Opciones generales (home, etc)
     */
    private static function homeOptions(): array
    {
        return [
            [
                'icon' => '🛒',
                'label' => 'Quiero comprar',
                'subtitle' => 'Búsqueda asistida de propiedades',
                'message' => 'Hola! Estoy buscando una propiedad en Benito Juárez.',
            ],
            [
                'icon' => '💳',
                'label' => 'Quiero vender',
                'subtitle' => 'Valuación y venta en 45 días',
                'message' => 'Hola! Tengo una propiedad y quisiera venderla.',
            ],
            [
                'icon' => '💰',
                'label' => 'Soy inversionista',
                'subtitle' => 'Oportunidades de inversión',
                'message' => 'Hola! Soy inversionista/desarrollador y me interesa explorar oportunidades.',
            ],
            [
                'icon' => '❓',
                'label' => 'Consulta general',
                'subtitle' => 'Pregunta lo que necesites',
                'message' => 'Hola! Tengo una pregunta sobre servicios inmobiliarios.',
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
