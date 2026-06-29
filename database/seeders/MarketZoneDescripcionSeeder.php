<?php

namespace Database\Seeders;

use App\Models\MarketZone;
use Illuminate\Database\Seeder;

/**
 * Pobla el campo descripcion_seo en market_zones.
 * Idempotente: usa updateOrCreate por slug.
 */
class MarketZoneDescripcionSeeder extends Seeder
{
    public function run(): void
    {
        $descripciones = [
            'narvarte' => 'Narvarte y Vértiz forman una de las zonas residenciales más consolidadas de la alcaldía Benito Juárez. Con alta conectividad al Metrobús y Metro, oferta gastronómica y comercial diversa, y una demografía de profesionistas y familias jóvenes, la zona mantiene una demanda de compra y renta sostenida que se refleja en su plusvalía constante. Los departamentos seminuevos son el segmento más activo del mercado, con precios que superan el promedio de la CDMX.',

            'del-valle' => 'Del Valle es la colonia insignia de Benito Juárez y una de las más buscadas de la Ciudad de México. Su traza urbana planificada, avenidas arboladas y amplia oferta de servicios, colegios y transporte la convierten en destino preferente para familias y profesionistas. La plusvalía histórica de Del Valle supera consistentemente el promedio de la ciudad, con alta demanda tanto en venta como en renta de departamentos de 2 a 3 recámaras.',

            'portales' => 'Portales representa la mejor relación precio-calidad en Benito Juárez. Con precios por m² aún por debajo de colonias vecinas como Narvarte y Del Valle, y una tendencia de plusvalía acelerada, es la opción estratégica para compradores que buscan maximizar su inversión en el mediano plazo. La colonia cuenta con infraestructura sólida: Metro, Metrobús, mercados y una vida de barrio auténtica que atrae cada vez más a nuevas generaciones.',

            'alamos-xoco' => 'Álamos y Xoco conforman una zona en plena transición dentro de Benito Juárez. La cercanía con el World Trade Center, Insurgentes y la Zona Rosa impulsa la demanda de oficinas y vivienda para profesionistas. El proceso de redensificación ha traído nuevos desarrollos con amenidades modernas, haciendo de esta zona una de las más interesantes para inversión en la alcaldía.',

            'roma-sur-doctores' => 'Roma Sur y Doctores integran un corredor urbano dinámico en el límite de Benito Juárez y Cuauhtémoc. La proximidad con Insurgentes y el Hospital General, sumada a la oferta cultural y gastronómica heredada de Roma Norte, genera una demanda creciente de renta entre estudiantes y profesionistas de salud. Los precios siguen por debajo de colonias premium vecinas, ofreciendo oportunidades de inversión con alto potencial de apreciación.',

            'napoles-florida' => 'Nápoles y Florida son colonias familiares consolidadas con acceso directo al World Trade Center y a las principales arterias de la ciudad. Su oferta de casas y departamentos amplios, parques bien mantenidos y bajo índice delictivo las hacen ideales para familias con hijos. La demanda de renta es constante gracias a la cercanía con polos corporativos sobre Insurgentes.',

            'ciudad-deportes-san-pedro' => 'Ciudad de los Deportes y San Pedro de los Pinos ofrecen una alternativa residencial tranquila en el sur de Benito Juárez. Bien comunicadas mediante el Metrobús y Metro Mixcoac, estas colonias combinan precios accesibles con la calidad de vida característica de la alcaldía. Ideales para quien busca espacio, tranquilidad y buena conectividad sin alejarse del corazón de la ciudad.',

            'moderna-letran-valle' => 'Moderna y Letrán Valle son colonias residenciales clásicas de Benito Juárez, con arquitectura de mediados del siglo XX y calles amplias. Bien comunicadas y con acceso a múltiples líneas de transporte, ofrecen precios competitivos frente a colonias más cotizadas de la alcaldía. Son ideales para compradores de primera vivienda que buscan un buen punto de entrada en la Benito Juárez.',
        ];

        $updated = 0;
        foreach ($descripciones as $slug => $texto) {
            $rows = MarketZone::where('slug', $slug)->update(['descripcion_seo' => $texto]);
            if ($rows) $updated++;
        }

        $this->command->info("[OK] MarketZoneDescripcionSeeder: {$updated} zonas actualizadas.");
    }
}
