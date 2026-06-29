<?php

namespace Database\Seeders;

use App\Models\ColoniaPage;
use Illuminate\Database\Seeder;

class ColoniaPageSeeder extends Seeder
{
    /**
     * Idempotente: usa updateOrCreate para poder re-ejecutar sin duplicar.
     */
    public function run(): void
    {
        $colonias = [
            [
                'slug'                => 'del-valle',
                'name'                => 'Del Valle',
                'colony_search_terms' => 'del valle,del valle centro,del valle sur,del valle norte',
                'meta_title'          => 'Propiedades en Del Valle, Benito Juárez | Home del Valle',
                'meta_description'    => 'Casas y departamentos en venta y renta en Del Valle, CDMX. Colonia premium en Benito Juárez con alta plusvalía. Asesoría gratuita de expertos.',
                'heading'             => 'Propiedades en Del Valle',
                'subheading'          => 'Una de las colonias más consolidadas de Benito Juárez. Alta plusvalía, conectividad perfecta y calidad de vida excepcional.',
                'about'               => '<p>Del Valle es la colonia insignia de la Alcaldía Benito Juárez. Con una ubicación estratégica entre Insurgentes y el Periférico, ofrece acceso directo a las principales arterias de la ciudad, amplia oferta de servicios, parques, restaurantes y transporte. Su traza urbana planificada, con calles amplias y avenidas arboladas, la convierte en una de las zonas de mayor demanda inmobiliaria de la CDMX.</p><p>La plusvalía histórica de Del Valle ha superado consistentemente el promedio de la ciudad, con incrementos del 8-12% anual en los últimos cinco años. Es el destino predilecto de familias de clase media-alta que buscan departamentos de 2 a 3 recámaras entre $4.5M y $9M MXN.</p>',
                'faqs'                => [
                    ['q' => '¿Cuánto cuesta un departamento en Del Valle?', 'a' => 'Los precios oscilan entre $3.5M y $12M MXN dependiendo de superficie, recámaras y estado de conservación. El precio promedio por m² en 2026 está entre $45,000 y $65,000 MXN.'],
                    ['q' => '¿Es buena inversión comprar en Del Valle?', 'a' => 'Sí. Del Valle ha mostrado una plusvalía consistente del 8-12% anual. Su alta demanda de renta (3-5% de CAP rate) y la escasez de oferta la hacen una excelente inversión a largo plazo.'],
                    ['q' => '¿Qué colonias rodean Del Valle?', 'a' => 'Del Valle colinda con Narvarte Poniente, Nápoles, Insurgentes Mixcoac, Extremadura Insurgentes y Portales. Todas dentro de Benito Juárez, con acceso similar a servicios.'],
                    ['q' => '¿Cómo vendo mi propiedad en Del Valle?', 'a' => 'Contáctanos para una valuación gratuita. Nuestra estrategia de venta incluye fotografía profesional, difusión segmentada y gestión jurídica completa. Tiempo promedio de venta: 45 días.'],
                ],
                'sort_order'          => 1,
                'is_published'        => true,
            ],
            [
                'slug'                => 'narvarte',
                'name'                => 'Narvarte Poniente',
                'colony_search_terms' => 'narvarte,narvarte poniente,narvarte oriente',
                'meta_title'          => 'Propiedades en Narvarte, Benito Juárez | Home del Valle',
                'meta_description'    => 'Departamentos en venta y renta en Narvarte Poniente y Narvarte Oriente, CDMX. La colonia favorita de jóvenes profesionistas. Valuación gratuita.',
                'heading'             => 'Propiedades en Narvarte',
                'subheading'          => 'La colonia más vibrante de Benito Juárez. Gastronomía de clase mundial, alta demanda de renta y plusvalía acelerada.',
                'about'               => '<p>Narvarte se ha consolidado como la colonia más codiciada por jóvenes profesionistas y familias jóvenes en la CDMX. Su oferta gastronómica, cultural y de servicios es incomparable: cientos de restaurantes, cafés, bares y boutiques se distribuyen por sus calles tranquilas y bien cuidadas.</p><p>La demanda de renta en Narvarte es de las más altas de la ciudad, con tasas de ocupación superiores al 95% en departamentos de 1 y 2 recámaras. Esto lo convierte en un excelente activo de inversión con CAP rates de 4-6% anual.</p>',
                'faqs'                => [
                    ['q' => '¿Cuánto cuesta rentar en Narvarte?', 'a' => 'Un departamento de 1 recámara cuesta entre $12,000 y $18,000/mes. Dos recámaras van de $18,000 a $30,000/mes. Los precios han subido ~15% en el último año por alta demanda.'],
                    ['q' => '¿Cuánto vale comprar un depto en Narvarte?', 'a' => 'Los precios de venta arrancan desde $2.8M para estudios y llegan a $8M+ para departamentos de 3 recámaras con amenidades. El precio por m² está entre $42,000 y $68,000 MXN en 2026.'],
                    ['q' => '¿Es Narvarte segura?', 'a' => 'Narvarte Poniente y Oriente se encuentran entre las colonias con menor índice delictivo de la CDMX. Es una zona familiar con alta presencia peatonal durante todo el día.'],
                    ['q' => '¿Cuál es la diferencia entre Narvarte Poniente y Oriente?', 'a' => 'Narvarte Poniente tiene mayor oferta comercial y gastronómica, precios ligeramente más altos y más actividad. Narvarte Oriente es más tranquila y residencial, con precios ~10% menores.'],
                ],
                'sort_order'          => 2,
                'is_published'        => true,
            ],
            [
                'slug'                => 'napoles',
                'name'                => 'Nápoles',
                'colony_search_terms' => 'napoles,nápoles',
                'meta_title'          => 'Propiedades en Nápoles, Benito Juárez | Home del Valle',
                'meta_description'    => 'Casas y departamentos en venta y renta en Nápoles, CDMX. Colonia familiar consolidada en Benito Juárez con excelente conectividad y servicios.',
                'heading'             => 'Propiedades en Nápoles',
                'subheading'          => 'Colonia familiar con gran conectividad en el corazón de Benito Juárez. Calles tranquilas, parques y acceso rápido a todo.',
                'about'               => '<p>Nápoles es una colonia familiar por excelencia dentro de Benito Juárez. Con acceso directo al World Trade Center, Insurgentes y la Zona Rosa, combina tranquilidad residencial con conectividad metropolitana. Sus calles amplias, parques bien mantenidos y oferta de colegios y servicios la hacen ideal para familias con hijos.</p><p>El mercado inmobiliario en Nápoles ofrece excelente relación precio-calidad: casas y departamentos amplios a precios más accesibles que colonias vecinas como Narvarte o Del Valle, pero con igual calidad de vida y plusvalía creciente.</p>',
                'faqs'                => [
                    ['q' => '¿Cuánto cuesta una casa en Nápoles?', 'a' => 'Las casas en Nápoles van de $5M a $18M MXN dependiendo de superficie, número de niveles y estado de conservación. El precio por m² de terreno oscila entre $35,000 y $55,000 MXN.'],
                    ['q' => '¿Nápoles es buena zona para vivir con familia?', 'a' => 'Sí. Es una de las colonias más recomendadas para familias en Benito Juárez. Cuenta con parques, colegios privados, supermercados, hospitales y bajo índice delictivo.'],
                    ['q' => '¿Hay potencial de desarrollo en Nápoles?', 'a' => 'Sí. El corredor Insurgentes-WTC ha impulsado el redensificamiento de Nápoles. Existen predios con potencial H5/H6 muy interesantes para desarrolladores de proyectos residenciales medianos.'],
                ],
                'sort_order'          => 3,
                'is_published'        => true,
            ],
            [
                'slug'                => 'portales',
                'name'                => 'Portales',
                'colony_search_terms' => 'portales,portales norte,portales sur,portales oriente',
                'meta_title'          => 'Propiedades en Portales, Benito Juárez | Home del Valle',
                'meta_description'    => 'Departamentos y casas en venta y renta en Portales, CDMX. Una de las colonias con mejor precio-calidad en Benito Juárez. Amplia oferta de propiedades.',
                'heading'             => 'Propiedades en Portales',
                'subheading'          => 'La mejor relación precio-calidad en Benito Juárez. Colonia consolidada con alto potencial de plusvalía en los próximos años.',
                'about'               => '<p>Portales representa la mejor oportunidad de precio-calidad en Benito Juárez. Con precios por m² aún por debajo de colonias vecinas como Narvarte y Del Valle, y una tendencia de plusvalía acelerada, es la opción preferida para compradores que buscan maximizar su inversión.</p><p>La colonia cuenta con excelente infraestructura: Metro y Metrobús, hospitales, mercados tradicionales y una vida de barrio auténtica que cada vez atrae más a nuevas generaciones. El proceso de gentrificación moderada está incrementando la demanda y los precios de forma sostenida.</p>',
                'faqs'                => [
                    ['q' => '¿Cuánto cuesta un departamento en Portales?', 'a' => 'Los precios van de $1.8M para estudios o 1 recámara hasta $5M para 3 recámaras. El precio por m² oscila entre $28,000 y $45,000 MXN, notablemente más accesible que Narvarte o Del Valle.'],
                    ['q' => '¿Vale la pena invertir en Portales?', 'a' => 'Sí, especialmente en el mediano plazo. La colonia está en pleno proceso de valorización. Quien compra hoy puede beneficiarse de plusvalías del 10-15% anual en los próximos 3-5 años.'],
                    ['q' => '¿Cuál es la conectividad de Portales?', 'a' => 'Excelente. Cuenta con estaciones del Metro (Portales, Ermita, Zapata) y múltiples líneas de Metrobús. Está a 15 minutos del Centro y 20 minutos de Polanco en transporte público.'],
                ],
                'sort_order'          => 4,
                'is_published'        => true,
            ],
            [
                'slug'                => 'insurgentes-mixcoac',
                'name'                => 'Insurgentes Mixcoac',
                'colony_search_terms' => 'insurgentes mixcoac,mixcoac,extremadura insurgentes',
                'meta_title'          => 'Propiedades en Insurgentes Mixcoac | Home del Valle',
                'meta_description'    => 'Departamentos en venta y renta en Insurgentes Mixcoac y Extremadura, CDMX. Zona de alto tráfico comercial y residencial en Benito Juárez.',
                'heading'             => 'Propiedades en Insurgentes Mixcoac',
                'subheading'          => 'El corredor comercial más dinámico de Benito Juárez. Ubicación premium frente a Insurgentes con alto potencial de desarrollo.',
                'about'               => '<p>Insurgentes Mixcoac y el corredor de Extremadura Insurgentes representan una de las zonas de mayor actividad comercial y residencial en Benito Juárez. La proximidad a Insurgentes Sur, con su oferta de oficinas, centros comerciales y transporte, genera una demanda constante de vivienda en la zona.</p><p>Es una colonia ideal para propietarios que buscan inmuebles mixtos (planta baja comercial + departamentos superiores) y para inversionistas interesados en rentas comerciales de alto tráfico.</p>',
                'faqs'                => [
                    ['q' => '¿Qué tipo de propiedades hay en Insurgentes Mixcoac?', 'a' => 'Predominan departamentos de 1 a 3 recámaras en edificios modernos, locales comerciales y algunos predios para desarrollo. Es una zona con buena mezcla de usos habitacionales y comerciales.'],
                    ['q' => '¿Es una zona para vivir o para invertir?', 'a' => 'Para ambos. Su alta conectividad (Insurgentes, Metro Mixcoac) la hace atractiva para quienes trabajan en Santa Fe, Polanco o el sur de la ciudad. Y su tráfico comercial genera buenas rentas.'],
                ],
                'sort_order'          => 5,
                'is_published'        => true,
            ],
        ];

        foreach ($colonias as $data) {
            ColoniaPage::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $this->command->info('[OK] ColoniaPageSeeder: ' . count($colonias) . ' colonias creadas/actualizadas.');
    }
}
