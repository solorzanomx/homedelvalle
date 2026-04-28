<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandingFormRequest;
use App\Models\ContactSubmission;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Default campaign data. Override per-campaign as needed.
     */
    private function defaultCampaign(): array
    {
        return [
            'slug' => 'vende-del-valle',
            'badge' => 'Asesoría gratuita — Cupo limitado',
            'heading' => 'Vende tu inmueble en Benito Juárez rápido y al mejor precio',
            'subheading' => 'Conectamos tu propiedad con compradores calificados. Sin exclusivas forzadas, sin comisiones ocultas.',
            'cta_heading' => '¿Listo para vender tu propiedad?',
            'cta_subheading' => 'Solicita tu asesoría gratuita hoy y descubre cuánto vale realmente tu propiedad en el mercado actual.',
            'wa_message' => 'Hola, me interesa vender mi propiedad en Benito Juárez',
        ];
    }

    private function defaultFaqs(): array
    {
        return [
            ['q' => '¿Cuánto cuesta la asesoría?', 'a' => 'La asesoría y valuación inicial son completamente gratuitas y sin compromiso. Solo cobramos una comisión al momento de cerrar exitosamente la venta de tu propiedad.'],
            ['q' => '¿Cuánto tiempo toma vender mi propiedad?', 'a' => 'En promedio, nuestras propiedades se venden en 45 días. Esto depende del precio, ubicación y condiciones del mercado, pero nuestra estrategia de marketing digital acelera el proceso.'],
            ['q' => '¿Necesito firmar un contrato de exclusividad?', 'a' => 'No. Trabajamos sin exclusivas forzadas. Creemos que los resultados hablan por sí solos y preferimos ganarnos tu confianza con nuestro trabajo.'],
            ['q' => '¿Qué documentos necesito para vender?', 'a' => 'Los documentos básicos son: escrituras, boleta predial, boleta de agua, identificación oficial y comprobante de domicilio. Te orientamos sobre cada documento durante el proceso.'],
            ['q' => '¿Cómo determinan el precio de mi propiedad?', 'a' => 'Realizamos un análisis comparativo de mercado basado en ventas reales recientes en la zona, características de tu propiedad y condiciones actuales del mercado inmobiliario en CDMX.'],
            ['q' => '¿Qué zonas cubren?', 'a' => 'Nos especializamos en la Colonia del Valle (Centro, Norte y Sur), Narvarte, Benito Juárez y zonas aledañas en la Ciudad de México.'],
        ];
    }

    private function defaultStats(): array
    {
        return [
            'years' => '10',
            'sold' => '200',
        ];
    }

    private function defaultBenefits(): array
    {
        return [
            ['icon' => 'rocket', 'title' => 'Venta rápida', 'desc' => 'Nuestra red de compradores calificados acelera el proceso de venta.'],
            ['icon' => 'shield', 'title' => 'Seguridad jurídica', 'desc' => 'Blindaje legal completo en cada operación.'],
            ['icon' => 'chart', 'title' => 'Mejor precio', 'desc' => 'Análisis de mercado para maximizar el valor de tu propiedad.'],
            ['icon' => 'eye', 'title' => 'Transparencia total', 'desc' => 'Reportes y actualizaciones constantes del proceso.'],
        ];
    }

    private function defaultMetrics(): array
    {
        return [
            ['value' => '30+', 'label' => 'Años de experiencia'],
            ['value' => '200+', 'label' => 'Propiedades gestionadas'],
            ['value' => '45', 'label' => 'Días promedio de venta'],
            ['value' => '98%', 'label' => 'Clientes satisfechos'],
        ];
    }

    private function defaultProcessSteps(): array
    {
        return [
            ['num' => '01', 'title' => 'Valuación gratuita', 'desc' => 'Analizamos tu propiedad y te damos un precio competitivo basado en datos reales del mercado.'],
            ['num' => '02', 'title' => 'Estrategia personalizada', 'desc' => 'Diseñamos un plan de comercialización con fotografía profesional y marketing digital.'],
            ['num' => '03', 'title' => 'Cierre seguro', 'desc' => 'Negociamos, gestionamos la documentación y te acompañamos hasta la firma de escrituras.'],
        ];
    }

    /**
     * Show the landing page.
     */
    public function show(Request $request)
    {
        $siteSettings = \App\Models\SiteSetting::current();
        $content = $siteSettings?->vender_content ?? [];

        $campaign = [
            'badge' => $content['badge'] ?? $this->defaultCampaign()['badge'],
            'heading' => $content['heading'] ?? $this->defaultCampaign()['heading'],
            'subheading' => $content['subheading'] ?? $this->defaultCampaign()['subheading'],
            'cta_heading' => $content['cta_heading'] ?? $this->defaultCampaign()['cta_heading'],
            'cta_subheading' => $content['cta_subheading'] ?? $this->defaultCampaign()['cta_subheading'],
            'wa_message' => $content['wa_message'] ?? $this->defaultCampaign()['wa_message'],
            'slug' => 'vende-del-valle',
        ];

        return view('public.vende-tu-propiedad', [
            'campaign' => $campaign,
            'benefits' => $content['benefits'] ?? $this->defaultBenefits(),
            'metrics' => $content['metrics'] ?? $this->defaultMetrics(),
            'processSteps' => $content['process_steps'] ?? $this->defaultProcessSteps(),
            'faqs' => $content['faqs'] ?? $this->defaultFaqs(),
            'meta' => [
                'title' => $content['meta_title'] ?? 'Vende tu propiedad en Benito Juarez | Home del Valle',
                'description' => $content['meta_description'] ?? 'Vende tu propiedad rapido y al mejor precio. Asesoria profesional gratuita, compradores calificados y cierre seguro.',
            ],
        ]);
    }

    /**
     * Show the buyer search landing page.
     */
    public function compra(Request $request)
    {
        $page = \App\Models\Page::where('slug', 'comprar')->first();

        return view('public.comprar', [
            'page' => $page ?? (object)[
                'title' => 'Búsqueda asistida de inmuebles en Benito Juárez',
                'meta_title' => 'Búsqueda asistida de inmuebles en Benito Juárez | Home del Valle',
                'meta_description' => 'Encuentra tu próximo hogar en Benito Juárez sin perder fines de semana en visitas. Asesoría personalizada de expertos.',
                'body' => '',
            ],
        ]);
    }

    /**
     * Show the developer/investor landing page.
     */
    public function desarrolladores(Request $request)
    {
        $page = \App\Models\Page::where('slug', 'desarrolladores-e-inversionistas')->first();

        return view('public.desarrolladores', [
            'page' => $page ?? (object)[
                'title' => 'Captación de predios e inversión inmobiliaria en Benito Juárez',
                'meta_title' => 'Captación de predios e inversión inmobiliaria en Benito Juárez | Home del Valle',
                'meta_description' => 'Captación de terrenos y producto terminado en Benito Juárez bajo demanda activa. Red de inversionistas consolidada.',
                'body' => '',
            ],
        ]);
    }

    /**
     * Handle lead form submission.
     */
    public function submit(LandingFormRequest $request, SpamProtectionService $spam, AutomationEngine $engine)
    {
        $validated = $request->validated();

        // Honeypot
        if ($request->filled('website_url')) {
            return back()->with('success', 'Gracias, te contactaremos pronto.');
        }

        // Spam protection
        $spamCheck = $spam->check(
            $validated,
            $request->input('recaptcha_token'),
            $request->ip(),
            'landing'
        );

        if (! $spamCheck['pass']) {
            return back()->with('success', '¡Gracias! Un asesor te contactará en menos de 24 horas.');
        }

        ContactSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'message' => $validated['message'] ?? '',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
        ]);

        // Trigger automation engine — enroll lead
        $engine->processFormSubmitted(array_merge($validated, [
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
        ]), 'landing');

        // Record privacy acceptance
        if ($request->boolean('accept_privacy')) {
            $privacyDoc = \App\Models\LegalDocument::where('type', 'aviso_privacidad')
                ->where('status', 'published')
                ->first();
            if ($privacyDoc && $privacyDoc->current_version_id) {
                \App\Models\LegalAcceptance::record(
                    $privacyDoc->id,
                    $privacyDoc->current_version_id,
                    $validated['email'],
                    $request,
                    'landing',
                    ['name' => $validated['name']]
                );
            }
        }

        return back()->with('success', '¡Gracias! Un asesor te contactará en menos de 24 horas.');
    }
}
