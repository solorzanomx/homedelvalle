<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Siembra los 11 posts del plan editorial (auditoría 2026-07-08) — el
 * contenido vive en database/seeders/blog-posts/{slug}.html.
 *
 * Se crean con status='scheduled' escalonados ~2 por semana (empezando 3
 * días después del deploy): publicar 11 de golpe es una señal antinatural
 * para Google, y el escalonado da ventana para revisar/editar cada uno en
 * el admin antes de su fecha. El comando blog:publish-scheduled (ya
 * agendado cada minuto en routes/console.php) los publica solo.
 *
 * Sin imagen destacada a propósito (las vistas tienen fallback @if) — se
 * agregan desde el admin; los posts funcionan y se publican sin ella.
 *
 * Defensiva: omite cualquier slug que ya exista, y omite todo si faltan
 * las tablas o el archivo de contenido.
 */
return new class extends Migration
{
    /**
     * [slug => [title, category_slug, tags, excerpt, meta_title,
     *           meta_description, focus_keyword, cta, day_offset]]
     */
    private function plan(): array
    {
        $ctaPredio = [[
            'title'       => '¿Cuánto vale tu propiedad para una desarrolladora?',
            'description' => 'Tenemos cartera propia de constructoras buscando predios en Benito Juárez ahora mismo. Evaluación gratuita, confidencial y sin compromiso — te representamos a ti, no a la constructora.',
            'button_text' => 'Evaluar mi propiedad sin compromiso',
            'link'        => '/vende-a-desarrolladora',
        ]];

        $ctaHerencia = [[
            'title'       => '¿Heredaste una propiedad y no sabes por dónde empezar?',
            'description' => 'Te acompañamos de la sucesión a la venta: orientación con notarías aliadas, valuación imparcial gratuita y venta segura. La primera conversación no cuesta nada.',
            'button_text' => 'Recibir orientación gratuita',
            'link'        => '/vende-tu-propiedad',
        ]];

        $ctaColonia = fn (string $zona, string $slug) => [[
            'title'       => "¿Cuánto cuesta el m² en {$zona} hoy?",
            'description' => 'Consulta precios reales de venta y renta por tipo de inmueble y antigüedad, actualizados con fuentes verificadas — no promedios de portal.',
            'button_text' => "Ver precios de {$zona}",
            'link'        => "/precios/{$slug}",
        ]];

        $ctaMercado = [[
            'title'       => '¿Cuánto vale TU propiedad hoy, con datos reales?',
            'description' => 'Recibe una opinión de valor gratuita con los factores específicos de tu inmueble y los precios actuales de tu colonia. Sin costo, sin compromiso.',
            'button_text' => 'Recibir mi opinión de valor',
            'link'        => '/precios/opinion-de-valor',
        ]];

        return [
            // ── Funnel predio → desarrolladora (prioridad #1) ──
            'cuanto-pagan-constructoras-terreno-del-valle-2026' => [
                'title' => '¿Cuánto pagan las constructoras por un terreno en Del Valle en 2026?',
                'category' => 'zonificacion-desarrollo',
                'tags' => ['#VenderAConstructora', '#ParaPropietarios', '#DelValle'],
                'excerpt' => 'Una desarrolladora no compra tu casa: compra los metros que puede construir sobre tu terreno. Así se calcula lo que realmente pueden pagar por un predio en Del Valle — y qué lo hace valer más.',
                'meta_title' => '¿Cuánto pagan las constructoras por un terreno en Del Valle?',
                'meta_description' => 'Qué pagan las desarrolladoras por predios en Del Valle en 2026: cómo funciona el valor residual, qué hace valer más tu terreno y cómo saberlo para tu propiedad gratis.',
                'focus_keyword' => 'cuánto pagan constructoras terreno del valle',
                'ctas' => $ctaPredio,
                'day' => 3,
            ],
            'vender-casa-constructora-proceso-tiempos-cdmx' => [
                'title' => 'Vender tu casa a una constructora: proceso paso a paso y tiempos reales en CDMX',
                'category' => 'zonificacion-desarrollo',
                'tags' => ['#VenderAConstructora', '#ParaPropietarios'],
                'excerpt' => 'Evaluación, presentación a la demanda real, negociación y cierre: los 4 pasos de vender tu casa a una desarrolladora, cuánto tarda cada uno, y los 3 errores más caros del dueño que va solo.',
                'meta_title' => 'Vender tu casa a una constructora: proceso y tiempos reales',
                'meta_description' => 'El proceso completo de vender tu casa a una constructora en CDMX: pasos, tiempos reales (3 a 5 meses), qué se negocia además del precio y los errores que cuestan millones.',
                'focus_keyword' => 'vender casa a constructora proceso',
                'ctas' => $ctaPredio,
                'day' => 7,
            ],
            'como-consultar-uso-de-suelo-benito-juarez-seduvi' => [
                'title' => 'Uso de suelo en Benito Juárez: cómo consultar el de tu casa en 5 minutos (y qué significa)',
                'category' => 'zonificacion-desarrollo',
                'tags' => ['#VenderAConstructora', '#ParaPropietarios'],
                'excerpt' => 'El uso de suelo define cuánto vale tu propiedad para una desarrolladora. Guía paso a paso para consultarlo gratis en SEDUVI — y cómo leer la clave H/3/20 o HM/6/20 que te aparezca.',
                'meta_title' => 'Uso de suelo en Benito Juárez: consúltalo en 5 minutos',
                'meta_description' => 'Cómo consultar gratis el uso de suelo de tu casa en Benito Juárez en el portal de SEDUVI, qué significa la clave de zonificación y por qué define el valor de tu predio.',
                'focus_keyword' => 'uso de suelo benito juárez consultar',
                'ctas' => $ctaPredio,
                'day' => 10,
            ],
            'vender-terreno-junto-con-vecinos-desarrolladora' => [
                'title' => 'Mi vecino vendió a una desarrolladora: ¿me conviene vender junto o solo?',
                'category' => 'zonificacion-desarrollo',
                'tags' => ['#VenderAConstructora', '#ParaPropietarios'],
                'excerpt' => 'Los predios contiguos vendidos en paquete casi siempre valen más por metro que por separado — si los vecinos se coordinan ANTES de negociar. Cómo hacerlo sin conflicto y sin malbaratar.',
                'meta_title' => 'Vender tu predio junto con tus vecinos a una desarrolladora',
                'meta_description' => 'Por qué un paquete de predios contiguos vale más por m² que las casas por separado, el error de negociar cada quien por su cuenta, y cómo coordinarse con los vecinos sin conflicto.',
                'focus_keyword' => 'vender terreno con vecinos desarrolladora',
                'ctas' => $ctaPredio,
                'day' => 14,
            ],
            'fideicomiso-o-venta-directa-desarrolladora' => [
                'title' => '¿Fideicomiso o venta directa a una desarrolladora? Qué te conviene como dueño',
                'category' => 'zonificacion-desarrollo',
                'tags' => ['#VenderAConstructora', '#ParaPropietarios'],
                'excerpt' => 'Vender tu predio de contado o aportarlo al proyecto a cambio de departamentos o un porcentaje de ventas: la comparación honesta de los dos esquemas, sus riesgos y las preguntas que deciden.',
                'meta_title' => 'Fideicomiso o venta directa a desarrolladora: qué conviene',
                'meta_description' => 'Venta directa vs aportar tu terreno en fideicomiso a una desarrolladora: ventajas, riesgos reales, qué revisar en el contrato y qué perfil de dueño conviene a cada esquema.',
                'focus_keyword' => 'fideicomiso terreno desarrolladora',
                'ctas' => $ctaPredio,
                'day' => 17,
            ],

            // ── Herencias y Sucesiones ──
            'cuanto-cuesta-sucesion-cdmx-2026' => [
                'title' => '¿Cuánto cuesta una sucesión en CDMX en 2026? Notaría vs juicio, tiempos y costos',
                'category' => 'herencias-y-sucesiones',
                'tags' => ['#Herencias', '#VenderPropiedad'],
                'excerpt' => 'Con testamento, la sucesión es notarial: 2 a 6 meses. Sin testamento, casi siempre juicio: 1 a 3 años. De qué dependen los costos, el costo invisible de dejar pasar años, y cómo abaratarlo.',
                'meta_title' => '¿Cuánto cuesta una sucesión en CDMX? Notaría vs juicio',
                'meta_description' => 'Costos y tiempos reales de una sucesión en CDMX en 2026: testamentaria ante notario vs juicio intestamentario, de qué depende el precio y las 3 decisiones que lo abaratan.',
                'focus_keyword' => 'cuánto cuesta una sucesión cdmx',
                'ctas' => $ctaHerencia,
                'day' => 21,
            ],
            'vender-propiedad-heredada-entre-hermanos-sin-conflicto' => [
                'title' => 'Heredamos entre hermanos: cómo vender la propiedad sin pelearnos',
                'category' => 'herencias-y-sucesiones',
                'tags' => ['#Herencias', '#VenderPropiedad'],
                'excerpt' => 'La copropiedad entre hermanos es la causa #1 de propiedades congeladas en Benito Juárez. El orden correcto de las conversaciones, los caminos cuando hay acuerdo, y qué hacer cuando uno no quiere vender.',
                'meta_title' => 'Vender una propiedad heredada entre hermanos sin conflicto',
                'meta_description' => 'Cómo vender una propiedad heredada entre hermanos sin romper a la familia: valuación neutral primero, los 4 caminos posibles y las reglas de oro que destraban el 90% de los casos.',
                'focus_keyword' => 'vender propiedad heredada entre hermanos',
                'ctas' => $ctaHerencia,
                'day' => 24,
            ],

            // ── Colonias de Benito Juárez ──
            'vivir-en-narvarte-precios-pros-contras-2026' => [
                'title' => 'Vivir en Narvarte: precios, pros y contras en 2026',
                'category' => 'colonias-de-benito-juarez',
                'tags' => ['#Narvarte', '#Precios'],
                'excerpt' => 'La colonia con mejor relación calidad-precio de Benito Juárez: vida de barrio real, conectividad y precios aún accesibles. La guía honesta — con contras incluidos — para comprar, rentar o invertir.',
                'meta_title' => 'Vivir en Narvarte: precios, pros y contras 2026',
                'meta_description' => 'Cómo es vivir en la colonia Narvarte en 2026: precios por m² reales, conectividad, perfil de la colonia, sus contras honestos y si conviene como inversión.',
                'focus_keyword' => 'vivir en narvarte',
                'ctas' => $ctaColonia('Narvarte', 'narvarte'),
                'day' => 28,
            ],
            'vivir-en-del-valle-precios-pros-contras-2026' => [
                'title' => 'Vivir en Del Valle: precios, pros y contras en 2026',
                'category' => 'colonias-de-benito-juarez',
                'tags' => ['#DelValle', '#Precios'],
                'excerpt' => 'La colonia aspiracional de Benito Juárez: parques, escuelas y el m² más sólido de la alcaldía. La guía honesta para comprar o invertir — y el secreto a voces sobre sus casas unifamiliares.',
                'meta_title' => 'Vivir en Del Valle: precios, pros y contras 2026',
                'meta_description' => 'Cómo es vivir en la colonia Del Valle en 2026: precios por m² actualizados, perfil de la zona, contras honestos, y por qué sus casas valen más de lo que parece como terreno.',
                'focus_keyword' => 'vivir en del valle',
                'ctas' => $ctaColonia('Del Valle', 'del-valle'),
                'day' => 31,
            ],
            'vivir-en-portales-precios-pros-contras-2026' => [
                'title' => 'Vivir en Portales: precios, pros y contras en 2026',
                'category' => 'colonias-de-benito-juarez',
                'tags' => ['#Precios', '#ParaPropietarios'],
                'excerpt' => 'La puerta de entrada real a Benito Juárez: barrio auténtico, Metro dentro de la colonia y los precios más accesibles de la zona central. Guía honesta de una colonia en plena transformación.',
                'meta_title' => 'Vivir en Portales: precios, pros y contras 2026',
                'meta_description' => 'Cómo es vivir en la colonia Portales en 2026: precios por m² reales, conectividad, la transformación en curso, sus contras honestos y por qué atrae a compradores e inversionistas.',
                'focus_keyword' => 'vivir en portales cdmx',
                'ctas' => $ctaColonia('Portales', 'portales'),
                'day' => 35,
            ],

            // ── Mercado ──
            'van-a-bajar-precios-departamentos-benito-juarez-2026' => [
                'title' => '¿Van a bajar los precios de los departamentos en Benito Juárez? Análisis honesto 2026',
                'category' => 'mercado-inmobiliario-cdmx',
                'tags' => ['#Precios', '#ParaPropietarios'],
                'excerpt' => 'La pregunta que más escuchamos de dueños y compradores. Por qué BJ no se comporta como "el mercado", lo que sí puede pasar (y pasa), y qué hacer según tu caso — sin adornos.',
                'meta_title' => '¿Van a bajar los precios en Benito Juárez? Análisis 2026',
                'meta_description' => 'Análisis honesto de si van a bajar los precios de departamentos en Benito Juárez en 2026: escasez de suelo, demanda estructural, lo que sí cambia con el ciclo y qué hacer según tu caso.',
                'focus_keyword' => 'van a bajar los precios departamentos cdmx',
                'ctas' => $ctaMercado,
                'day' => 38,
            ],
        ];
    }

    public function up(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasTable('post_categories') || ! Schema::hasTable('tags')) {
            return;
        }

        $userId = DB::table('users')->orderBy('id')->value('id');
        if (! $userId) {
            return;
        }

        foreach ($this->plan() as $slug => $p) {
            if (DB::table('posts')->where('slug', $slug)->exists()) {
                continue;
            }

            $file = database_path('seeders/blog-posts/' . $slug . '.html');
            if (! file_exists($file)) {
                continue;
            }

            $categoryId = DB::table('post_categories')->where('slug', $p['category'])->value('id');
            if (! $categoryId) {
                // Existe en producción, pero un entorno fresco puede no tenerla
                // — un post sin categoría se queda sin CTA final (el ctaMap de
                // blog/show es por categoría), así que se crea en vez de null.
                $categoryId = DB::table('post_categories')->insertGetId([
                    'name'       => \Illuminate\Support\Str::of($p['category'])->replace('-', ' ')->title(),
                    'slug'       => $p['category'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $postId = DB::table('posts')->insertGetId([
                'user_id'          => $userId,
                'title'            => $p['title'],
                'slug'             => $slug,
                'excerpt'          => $p['excerpt'],
                'body'             => file_get_contents($file),
                'category_id'      => $categoryId,
                'status'           => 'scheduled',
                'published_at'     => now()->addDays($p['day'])->setTime(9, 0),
                'meta_title'       => $p['meta_title'],
                'meta_description' => $p['meta_description'],
                'focus_keyword'    => $p['focus_keyword'],
                'ctas'             => json_encode($p['ctas'], JSON_UNESCAPED_UNICODE),
                'ai_generated'     => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            foreach ($p['tags'] as $tagName) {
                $tagId = DB::table('tags')->where('name', $tagName)->value('id');
                if ($tagId && ! DB::table('post_tag')->where('post_id', $postId)->where('tag_id', $tagId)->exists()) {
                    DB::table('post_tag')->insert(['post_id' => $postId, 'tag_id' => $tagId]);
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        // Solo elimina los que siguen sin publicar (scheduled) — un post ya
        // publicado y posiblemente editado no se destruye en un rollback.
        $slugs = array_keys($this->plan());
        $ids = DB::table('posts')->whereIn('slug', $slugs)->where('status', 'scheduled')->pluck('id');
        DB::table('post_tag')->whereIn('post_id', $ids)->delete();
        DB::table('posts')->whereIn('id', $ids)->delete();
    }
};
