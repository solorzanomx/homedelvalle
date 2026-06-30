<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\V4\Data\LeadInternoData;
use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Data\CitaData;
use App\Mail\V4\Data\CompradorData;
use App\Mail\V4\Data\BienvenidaData;
use App\Mail\V4\Data\RecordatorioCitaData;
use App\Mail\V4\Mailables\LeadInternoMail;
use App\Mail\V4\Mailables\AcuseMail;
use App\Mail\V4\Mailables\CitaMail;
use App\Mail\V4\Mailables\CompradorMail;
use App\Mail\V4\Mailables\BienvenidaMail;
use App\Mail\V4\Mailables\DbTemplateMail;
use App\Mail\V4\Mailables\RecordatorioCitaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TransactionalEmailController extends Controller
{
    public function index()
    {
        $v4Templates = [
            [
                'id' => 'lead-interno',
                'name' => 'Notificación de Lead',
                'description' => 'Email interno cuando se recibe un nuevo lead',
            ],
            [
                'id' => 'acuse',
                'name' => 'Acuse de Recibido',
                'description' => 'Confirmación enviada al cliente tras contacto',
            ],
            [
                'id' => 'cita',
                'name' => 'Confirmación de Cita',
                'description' => 'Confirmación de cita agendada',
            ],
            [
                'id' => 'comprador',
                'name' => 'Listado de Propiedad',
                'description' => 'Envío de propiedad sugerida al cliente',
            ],
            [
                'id' => 'bienvenida',
                'name' => 'Bienvenida a Área de Clientes',
                'description' => 'Email de bienvenida con credenciales',
            ],
            [
                'id' => 'presentation-initial',
                'name' => 'Presentación Inicial · Captación',
                'description' => 'Se envía al propietario junto con el PDF de presentación de HDV',
            ],
            [
                'id' => 'captacion-declined',
                'name' => 'Gracias por tu confianza · Declive amistoso',
                'description' => 'Se envía al propietario cuando se declina una captación',
            ],
            [
                'id' => 'recordatorio-cita',
                'name' => 'Recordatorio de Cita',
                'description' => 'Se envía automáticamente el día de la visita para que el cliente confirme',
            ],
        ];

        return view('admin.email.templates.v4-index', compact('v4Templates'));
    }

    public function preview(string $templateId)
    {
        $mailable = $this->getMailable($templateId);

        if (!$mailable) {
            abort(404, 'Template no encontrado');
        }

        $templates = $this->getTemplateInfo();
        $template = $templates[$templateId] ?? null;

        if (!$template) {
            abort(404);
        }

        // Render the HTML before passing to view to avoid auto-conversion
        $html = $mailable->render();

        return view('admin.email.templates.v4-preview', [
            'templateId' => $templateId,
            'templateName' => $template['name'],
            'description' => $template['description'],
            'mailableHtml' => $html,
        ]);
    }

    public function sendTest(Request $request, string $templateId)
    {
        $request->validate(['email' => 'required|email']);

        $mailable = $this->getMailable($templateId);

        if (!$mailable) {
            return redirect()
                ->route('admin.transactional-emails.preview', $templateId)
                ->with('error', 'Template no encontrado');
        }

        try {
            Mail::to($request->email)->send($mailable);

            return redirect()
                ->route('admin.transactional-emails.preview', $templateId)
                ->with('success', "✓ Email de prueba enviado a {$request->email}");
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.transactional-emails.preview', $templateId)
                ->with('error', "✗ Error: {$e->getMessage()}");
        }
    }

    public function renderHtml(string $templateId)
    {
        $mailable = $this->getMailable($templateId);

        if (!$mailable) {
            return response('Template no encontrado', 404);
        }

        return $mailable->render();
    }

    private function getMailable($templateId)
    {
        return match($templateId) {
            'lead-interno' => new LeadInternoMail(new LeadInternoData(
                nombre: 'Juan Pérez López',
                email: 'juan.perez@example.com',
                telefono: '+52 55 1234 5678',
                origen: 'Contacto web',
                fecha: now()->format('Y-m-d H:i'),
                mensaje: 'Estoy muy interesado en vender mi propiedad ubicada en Benito Juárez. He visto que manejan pocos inmuebles y eso me atrae.'
            )),
            'acuse' => new AcuseMail(new AcuseData(
                folio: 'lead-' . uniqid(),
                email: 'cliente@example.com'
            )),
            'cita' => new CitaMail(new CitaData(
                email: 'cliente@example.com',
                nombre: 'Juan Pérez López',
                dia_semana: 'Lunes',
                dia: '15',
                mes: 'abril',
                anio: '2026',
                hora: '10:00 AM',
                duracion: '30',
                direccion: 'Paseo de los Tamarindos 400, Depto 1501',
                colonia: 'Bosques de las Lomas',
                asesor: 'María García Rodríguez',
                visit_token: 'preview-token-123',
                maps_url: 'https://maps.google.com',
            )),
            'recordatorio-cita' => new RecordatorioCitaMail(new RecordatorioCitaData(
                email: 'cliente@example.com',
                nombre: 'Juan Pérez López',
                dia_semana: 'Lunes',
                dia: '15',
                mes: 'abril',
                anio: '2026',
                hora: '10:00 AM',
                duracion: '30',
                direccion: 'Paseo de los Tamarindos 400, Depto 1501',
                colonia: 'Bosques de las Lomas',
                asesor: 'María García Rodríguez',
                visit_token: 'preview-token-123',
                maps_url: 'https://maps.google.com',
                is_today: 'hoy',
            )),
            'comprador' => new CompradorMail(new CompradorData(
                email: 'cliente@example.com',
                colonia: 'Benito Juárez',
                titulo: 'Casa moderna con azotea y jardín',
                metros: '350',
                recamaras: '3',
                banos: '2',
                estacionamientos: '2',
                precio: '4500000',
                foto_url: null
            )),
            'bienvenida' => new BienvenidaMail(new BienvenidaData(
                email: 'cliente@example.com',
                usuario: 'juan.perez@homedelvalle.com',
                password_temporal: 'Temp123!@#Secure',
                url_acceso: 'https://app.homedelvalle.mx/login'
            )),
            'presentation-initial' => new DbTemplateMail(
                templateName:   'presentation_initial',
                previewSubject: 'Tu presentación de Home del Valle — Av. División del Norte 123',
                sampleVars: [
                    'NombrePropietario' => 'Carlos Mendoza',
                    'NombreInmueble'    => 'Av. División del Norte 123, Col. del Valle',
                    'NombreAgente'      => 'Alejandro García',
                    'PresentationUrl'   => 'https://homedelvalle.mx',
                    'TrackingPixel'     => 'https://homedelvalle.mx/favicon.ico',
                ],
            ),
            'captacion-declined' => new DbTemplateMail(
                templateName:   'captacion_declined_friendly',
                previewSubject: 'Gracias por tu confianza, Carlos',
                sampleVars: [
                    'NombrePropietario' => 'Carlos Mendoza',
                    'NombreInmueble'    => 'Av. División del Norte 123, Col. del Valle',
                    'NombreAgente'      => 'Alejandro García',
                ],
            ),
            default => null,
        };
    }

    private function getTemplateInfo()
    {
        return [
            'lead-interno' => [
                'name' => 'Notificación de Lead',
                'description' => 'Email interno cuando se recibe un nuevo lead',
                'variables' => [
                    'nombre' => 'Nombre del lead',
                    'email' => 'Email del contacto',
                    'telefono' => 'Teléfono',
                    'origen' => 'Fuente del lead',
                    'fecha' => 'Fecha/hora del contacto',
                    'mensaje' => 'Mensaje del cliente',
                ],
            ],
            'acuse' => [
                'name' => 'Acuse de Recibido',
                'description' => 'Confirmación enviada al cliente tras contacto',
                'variables' => [
                    'folio' => 'Número de folio único',
                    'email' => 'Email del cliente',
                ],
            ],
            'cita' => [
                'name' => 'Confirmación de Cita',
                'description' => 'Confirmación de cita agendada',
                'variables' => [
                    'email'       => 'Email del cliente',
                    'nombre'      => 'Nombre del cliente',
                    'dia_semana'  => 'Día de la semana',
                    'dia'         => 'Número del día',
                    'mes'         => 'Nombre del mes',
                    'hora'        => 'Hora de la cita',
                    'duracion'    => 'Duración en minutos',
                    'direccion'   => 'Dirección del inmueble',
                    'colonia'     => 'Colonia/zona',
                    'asesor'      => 'Nombre del asesor',
                    'visit_token' => 'Token único para confirm/reagendar',
                    'maps_url'    => 'URL de Google Maps (opcional)',
                ],
            ],
            'recordatorio-cita' => [
                'name' => 'Recordatorio de Cita',
                'description' => 'Se envía automáticamente el día de la visita para que el cliente confirme',
                'variables' => [
                    'nombre'      => 'Nombre del cliente',
                    'hora'        => 'Hora de la visita',
                    'direccion'   => 'Dirección del inmueble',
                    'visit_token' => 'Token único para confirm/reagendar',
                ],
            ],
            'comprador' => [
                'name' => 'Listado de Propiedad',
                'description' => 'Envío de propiedad sugerida al cliente',
                'variables' => [
                    'email' => 'Email del cliente',
                    'colonia' => 'Ubicación',
                    'titulo' => 'Título de la propiedad',
                    'metros' => 'Metros cuadrados',
                    'recamaras' => 'Número de recámaras',
                    'banos' => 'Número de baños',
                    'estacionamientos' => 'Cajones',
                    'precio' => 'Precio en MXN',
                ],
            ],
            'bienvenida' => [
                'name' => 'Bienvenida a Área de Clientes',
                'description' => 'Email de bienvenida con credenciales',
                'variables' => [
                    'email' => 'Email del usuario',
                    'usuario' => 'Usuario de login',
                    'password_temporal' => 'Contraseña temporal',
                    'url_acceso' => 'URL del portal',
                ],
            ],
            'presentation-initial' => [
                'name'        => 'Presentación Inicial · Captación',
                'description' => 'Se envía al propietario junto con el PDF adjunto tras la llamada inicial',
                'variables'   => [
                    'NombrePropietario' => 'Nombre del propietario',
                    'NombreInmueble'    => 'Dirección o nombre del inmueble',
                    'NombreAgente'      => 'Nombre del agente que envía',
                    'PresentationUrl'   => 'URL para ver la presentación en línea',
                    'TrackingPixel'     => 'Pixel de seguimiento (automático)',
                ],
            ],
            'captacion-declined' => [
                'name'        => 'Gracias por tu confianza · Declive amistoso',
                'description' => 'Se envía al propietario cuando se declina la captación desde el CRM',
                'variables'   => [
                    'NombrePropietario' => 'Nombre del propietario',
                    'NombreInmueble'    => 'Dirección o nombre del inmueble',
                    'NombreAgente'      => 'Nombre del agente que declina',
                ],
            ],
        ];
    }
}
