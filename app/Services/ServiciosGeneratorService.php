<?php

namespace App\Services;

use App\Models\Captacion;
use App\Models\Document;
use App\Models\PresentationSend;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Spatie\Browsershot\Browsershot;

class ServiciosGeneratorService
{
    /**
     * Genera el PDF de propuesta de servicios y lo guarda en storage.
     * Devuelve la ruta absoluta.
     */
    public function generatePdf(Captacion $captacion): string
    {
        set_time_limit(120);
        $captacion->loadMissing(['client', 'property', 'createdBy']);

        $html = $this->renderHtml($captacion);

        $dir  = storage_path('app/servicios/' . $captacion->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/servicios-' . time() . '.pdf';

        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->windowSize(816, 1056)
            ->paperSize(215.9, 279.4)
            ->landscape(false)
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->timeout(90)
            ->savePdf($path);

        // Persistir ruta en captacion
        $captacion->update(['last_servicios_pdf_path' => $path]);

        // Registrar en documentos del cliente
        Document::updateOrCreate(
            ['captacion_id' => $captacion->id, 'category' => 'propuesta_servicios'],
            [
                'client_id'    => $captacion->client_id,
                'uploaded_by'  => auth()->id() ?? 1,
                'label'        => 'Propuesta de Servicios',
                'file_path'    => 'servicios/' . $captacion->id . '/servicios-' . time() . '.pdf',
                'file_name'    => 'HDV-Servicios.pdf',
                'mime_type'    => 'application/pdf',
                'status'       => 'received',
            ]
        );

        return $path;
    }

    public function renderHtml(Captacion $captacion): string
    {
        $captacion->loadMissing(['client', 'property', 'createdBy']);
        $agent  = $captacion->createdBy ?? User::find(1);
        $vars   = $this->buildVars($captacion, $agent);
        return view('pdf.servicios', array_merge($vars, ['captacion' => $captacion, 'agent' => $agent]))->render();
    }

    public function sendByEmail(Captacion $captacion, string $email, User $agent): void
    {
        if (empty($captacion->last_servicios_pdf_path) || !file_exists($captacion->last_servicios_pdf_path)) {
            $this->generatePdf($captacion);
        }

        $path    = $captacion->last_servicios_pdf_path;
        $client  = $captacion->client;
        $subject = 'Propuesta de Servicios — Home del Valle';

        Mail::send('emails.servicios-attachment', [
            'clientName' => $client?->name ?? 'Estimado propietario',
            'agentName'  => $agent->name,
            'address'    => $captacion->property?->address ?? $captacion->property_address,
        ], function ($m) use ($email, $subject, $path, $agent) {
            $m->to($email)
              ->from(config('mail.from.address'), $agent->name . ' — Home del Valle')
              ->subject($subject)
              ->attach($path, ['as' => 'HDV-Propuesta-Servicios.pdf', 'mime' => 'application/pdf']);
        });

        PresentationSend::create([
            'captacion_id' => $captacion->id,
            'channel'      => 'servicios_email',
            'sent_to'      => $email,
            'sent_by'      => $agent->id,
            'sent_at'      => now(),
        ]);
    }

    public function sendByWhatsApp(Captacion $captacion, string $phone, User $agent): array
    {
        if (empty($captacion->last_servicios_pdf_path) || !file_exists($captacion->last_servicios_pdf_path)) {
            $this->generatePdf($captacion);
        }

        $address = $captacion->property?->address ?? $captacion->property_address ?? 'su inmueble';
        $msg = "Hola {$captacion->client?->name}, le comparto la propuesta de servicios de Home del Valle para la comercialización de {$address}. Quedo a sus órdenes. {$agent->name}";

        $phone   = preg_replace('/[^0-9]/', '', $phone);
        $waUrl   = 'https://wa.me/' . $phone . '?text=' . urlencode($msg);

        PresentationSend::create([
            'captacion_id' => $captacion->id,
            'channel'      => 'servicios_whatsapp',
            'sent_to'      => $phone,
            'sent_by'      => $agent->id,
            'sent_at'      => now(),
        ]);

        return ['wa_me_url' => $waUrl];
    }

    private function buildVars(Captacion $captacion, ?User $agent): array
    {
        $client   = $captacion->client;
        $property = $captacion->property;
        $intent   = $captacion->intent ?? 'general';
        $esRenta  = str_starts_with($intent, 'renta_');

        // Logo
        $logoUrl = null;
        $siteSettings = \App\Models\SiteSetting::first();
        if ($siteSettings?->logo_path) {
            $logoUrl = url('storage/' . $siteSettings->logo_path);
        }

        // Comisión
        $comision = $captacion->commission_pct ?? ($esRenta ? 1 : 5);
        $comisionLabel = PresentationGeneratorService::formatCommission((float)$comision, $intent);

        // Foto del inmueble
        $photoUrl = null;
        try {
            $media = $captacion->getMedia('property_photos')->first();
            if ($media) $photoUrl = $media->getFullUrl();
        } catch (\Throwable) {}

        if (!$photoUrl && config('services.google_maps.key')) {
            $svParts = array_filter([
                $property?->address,
                $property?->colony,
                $property?->city ?: 'Benito Juárez, CDMX',
                'México',
            ]);
            if (count($svParts) >= 2) {
                $photoUrl = 'https://maps.googleapis.com/maps/api/streetview?' . http_build_query([
                    'size'              => '800x450',
                    'location'         => implode(', ', $svParts),
                    'fov'              => '90',
                    'pitch'            => '5',
                    'key'              => config('services.google_maps.key'),
                    'return_error_code' => 'true',
                ]);
            }
        }

        // Servicios de marketing según intent
        $servicios = $this->getServiciosMarketing($esRenta);

        // Proceso paso a paso
        $proceso = $this->getProcesoPasoAPaso($esRenta);

        return [
            'nombrePropietario' => $client?->name ?? 'Estimado propietario',
            'direccionInmueble' => $property?->address ?? $captacion->property_address ?? '',
            'coloniaInmueble'   => $property?->colony ?? '',
            'tipoInmueble'      => $property ? ($property->property_type_label ?? $property->property_type) : '',
            'm2Total'           => $property?->total_area ?? null,
            'comisionLabel'     => $comisionLabel,
            'esRenta'           => $esRenta,
            'nombreAgente'      => $agent?->name ?? 'Home del Valle',
            'telefonoAgente'    => $agent?->phone ?? '',
            'emailAgente'       => $agent?->email ?? '',
            'logoUrl'           => $logoUrl,
            'photoUrl'          => $photoUrl,
            'fechaDocumento'    => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'servicios'         => $servicios,
            'proceso'           => $proceso,
            'vigenciaExclusiva' => '6 meses',
        ];
    }

    private function getServiciosMarketing(bool $esRenta): array
    {
        $base = [
            ['icono' => '📸', 'titulo' => 'Fotografía Profesional', 'desc' => 'Sesión fotográfica con equipo profesional para mostrar tu inmueble en su mejor ángulo.'],
            ['icono' => '🌐', 'titulo' => 'Publicación en Portales', 'desc' => 'Presencia en Inmuebles24, Lamudi, EasyBroker y los principales portales inmobiliarios de México.'],
            ['icono' => '📲', 'titulo' => 'Marketing Digital', 'desc' => 'Campañas en redes sociales, contenido orgánico y publicidad segmentada a tu zona.'],
            ['icono' => '👤', 'titulo' => 'Asesor Dedicado', 'desc' => 'Un asesor exclusivo que gestiona cada visita, negociación y proceso contigo.'],
            ['icono' => '📊', 'titulo' => 'Reporte de Actividad', 'desc' => 'Informes periódicos de visitas, interés de compradores y posicionamiento en el mercado.'],
            ['icono' => '🔒', 'titulo' => 'Seguridad Jurídica', 'desc' => 'Revisión y elaboración de contratos, verificación de clientes e investigación de antecedentes.'],
        ];

        if ($esRenta) {
            $base[] = ['icono' => '🏠', 'titulo' => 'Investigación de Inquilino', 'desc' => 'Revisión de buró de crédito, referencias laborales y análisis de solvencia del candidato.'];
            $base[] = ['icono' => '📋', 'titulo' => 'Garantías del Arrendamiento', 'desc' => 'Gestión de aval, pagarés o póliza jurídica para proteger tu patrimonio.'];
        } else {
            $base[] = ['icono' => '🏦', 'titulo' => 'Asesoría en Financiamiento', 'desc' => 'Orientamos al comprador en crédito hipotecario, Infonavit o Fovissste para agilizar el cierre.'];
            $base[] = ['icono' => '📝', 'titulo' => 'Coordinación Notarial', 'desc' => 'Acompañamiento completo en el proceso notarial y coordinación con todas las partes.'];
        }

        return $base;
    }

    private function getProcesoPasoAPaso(bool $esRenta): array
    {
        if ($esRenta) {
            return [
                ['num' => '1', 'titulo' => 'Firma de Exclusiva', 'desc' => 'Formalizamos el acuerdo y comenzamos el proceso.'],
                ['num' => '2', 'titulo' => 'Fotografía y Publicación', 'desc' => 'Sesión fotográfica y publicación en portales en 48 hrs.'],
                ['num' => '3', 'titulo' => 'Visitas y Candidatos', 'desc' => 'Atendemos visitas y preseleccionamos inquilinos calificados.'],
                ['num' => '4', 'titulo' => 'Investigación', 'desc' => 'Verificamos buró, solvencia y referencias del candidato elegido.'],
                ['num' => '5', 'titulo' => 'Contrato y Firma', 'desc' => 'Elaboramos el contrato de arrendamiento y coordinamos la firma.'],
                ['num' => '6', 'titulo' => 'Entrega de Llaves', 'desc' => 'Acta de entrega con inventario y condición del inmueble.'],
            ];
        }
        return [
            ['num' => '1', 'titulo' => 'Firma de Exclusiva', 'desc' => 'Formalizamos el acuerdo de comercialización exclusiva.'],
            ['num' => '2', 'titulo' => 'Preparación', 'desc' => 'Fotografía profesional, descripción y carpeta del inmueble.'],
            ['num' => '3', 'titulo' => 'Publicación', 'desc' => 'Publicación en portales y campaña de marketing activa.'],
            ['num' => '4', 'titulo' => 'Visitas y Ofertas', 'desc' => 'Coordinamos visitas y presentamos ofertas calificadas.'],
            ['num' => '5', 'titulo' => 'Negociación', 'desc' => 'Te asesoramos en cada oferta para obtener el mejor precio.'],
            ['num' => '6', 'titulo' => 'Contrato y Notaría', 'desc' => 'Elaboramos promesa/contrato y coordinamos el proceso notarial.'],
            ['num' => '7', 'titulo' => 'Cierre y Escrituración', 'desc' => 'Acompañamiento en la escritura y entrega formal del inmueble.'],
        ];
    }
}
