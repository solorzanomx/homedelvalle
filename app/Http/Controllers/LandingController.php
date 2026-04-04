<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
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
            'heading' => 'Vende tu departamento en la <span class="text-indigo-400">Colonia del Valle</span> rápido y al mejor precio',
            'subheading' => 'Conectamos tu propiedad con compradores calificados. Sin exclusivas forzadas, sin comisiones ocultas.',
            'cta_heading' => '¿Listo para vender tu propiedad?',
            'cta_subheading' => 'Solicita tu asesoría gratuita hoy y descubre cuánto vale realmente tu propiedad en el mercado actual.',
            'wa_message' => 'Hola, me interesa vender mi propiedad en Colonia del Valle',
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

    /**
     * Show the landing page.
     */
    public function show(Request $request)
    {
        return view('public.landing', [
            'campaign' => $this->defaultCampaign(),
            'faqs' => $this->defaultFaqs(),
            'stats' => $this->defaultStats(),
            'meta' => [
                'title' => 'Vende tu propiedad en Colonia del Valle | Home del Valle',
                'description' => 'Vende tu departamento en la Colonia del Valle rápido y al mejor precio. Asesoría profesional gratuita, compradores calificados y cierre seguro.',
                'keywords' => 'vender departamento colonia del valle, inmobiliaria cdmx, venta propiedades benito juárez, asesor inmobiliario del valle, vender casa del valle',
            ],
        ]);
    }

    /**
     * Handle lead form submission.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'message' => 'nullable|string|max:2000',
        ]);

        // Honeypot
        if ($request->filled('website_url')) {
            return back()->with('success', 'Gracias, te contactaremos pronto.');
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
