<?php

use App\Support\BlogCluster;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copy del CTA por cluster (Fase 3 del prompt de leads del blog), editable en /admin/blog-ctas.
 * Una fila fija por cluster (5) — nunca se crean ni se borran desde el admin, solo se editan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_cta_configs', function (Blueprint $table) {
            $table->id();
            $table->string('cluster', 40)->unique();
            $table->string('form_type', 40)->default('contacto');   // FormSubmission.form_type del lead que genera
            $table->string('headline', 200);
            $table->text('body');
            $table->string('button_label', 80);
            $table->string('whatsapp_message', 400);   // {titulo} se sustituye por el título del post
            // Variante "ya decidió vender" — solo se usa en herencias (BlogCluster::showsSellCta).
            $table->string('sell_headline', 200)->nullable();
            $table->text('sell_body')->nullable();
            $table->string('sell_button_label', 80)->nullable();
            $table->string('sell_whatsapp_message', 400)->nullable();
            $table->timestamps();
        });

        // Copy de herencias es la del prompt (punto 3.6), casi textual. Las otras 4 reusan el tono
        // y las URLs que ya probó el ctaMap por categoría — el asesor las ajusta desde el admin.
        $rows = [
            [
                'cluster' => BlogCluster::HERENCIAS, 'form_type' => 'vendedor_predio',
                'headline' => '¿Heredaste un inmueble en Benito Juárez?',
                'body' => 'Te decimos cuánto vale y qué te cuesta regularizarlo. Sin compromiso.',
                'button_label' => 'Quiero que me orienten',
                'whatsapp_message' => 'Hola, vengo del artículo "{titulo}". Quiero saber cuánto me costaría en mi caso.',
                'sell_headline' => '¿Ya decidiste vender lo que heredaste?',
                'sell_body' => 'Muchas propiedades heredadas en Benito Juárez valen más como terreno. Te acompañamos de la sucesión a la venta: orientación legal, opinión de valor gratuita y venta segura.',
                'sell_button_label' => 'Quiero vender',
                'sell_whatsapp_message' => 'Hola, vengo del artículo "{titulo}" y ya decidí vender la propiedad que heredé.',
            ],
            [
                'cluster' => BlogCluster::TERRENO_DESARROLLADORA, 'form_type' => 'vendedor_predio',
                'headline' => '¿Tu casa o predio tiene potencial de desarrollo?',
                'body' => 'Constructoras de nuestra cartera buscan predios en Benito Juárez ahora mismo. Evaluación gratuita, confidencial y sin compromiso.',
                'button_label' => 'Evaluar mi propiedad como terreno',
                'whatsapp_message' => 'Hola, vengo del artículo "{titulo}". Quiero saber si mi propiedad le interesa a una desarrolladora.',
            ],
            [
                'cluster' => BlogCluster::PRECIOS_INVERSION, 'form_type' => 'contacto',
                'headline' => '¿Cuánto vale tu propiedad hoy?',
                'body' => 'Obtén un precio de mercado actualizado con nuestra opinión de valor sin costo, generada por el Observatorio de precios.',
                'button_label' => 'Valuar mi propiedad',
                'whatsapp_message' => 'Hola, vengo del artículo "{titulo}". Quiero platicar sobre precios en Benito Juárez.',
            ],
            [
                'cluster' => BlogCluster::GUIAS_COLONIA, 'form_type' => 'contacto',
                'headline' => '¿Buscas propiedad en Benito Juárez?',
                'body' => 'Conocemos cada colonia a fondo. Encuentra tu propiedad ideal con asesoría especializada.',
                'button_label' => 'Hablar con un asesor',
                'whatsapp_message' => 'Hola, vengo del artículo "{titulo}". Busco propiedad en la zona.',
            ],
            [
                'cluster' => BlogCluster::PROCESO_VENTA, 'form_type' => 'vendedor',
                'headline' => '¿Quieres vender tu propiedad?',
                'body' => 'Opinión de valor gratuita en 24 horas, venta en 45–60 días y seguridad jurídica completa.',
                'button_label' => 'Solicitar opinión de valor',
                'whatsapp_message' => 'Hola, vengo del artículo "{titulo}". Quiero vender mi propiedad.',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('blog_cta_configs')->insert($row + ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_cta_configs');
    }
};
