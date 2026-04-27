<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\V4\Data\LeadInternoData;
use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Data\CitaData;
use App\Mail\V4\Data\CompradorData;
use App\Mail\V4\Data\BienvenidaData;
use App\Mail\V4\Mailables\LeadInternoMail;
use App\Mail\V4\Mailables\AcuseMail;
use App\Mail\V4\Mailables\CitaMail;
use App\Mail\V4\Mailables\CompradorMail;
use App\Mail\V4\Mailables\BienvenidaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TransactionalEmailController extends Controller
{
    protected array $templates = [
        'lead-interno' => [
            'name' => 'Notificación de Lead',
            'description' => 'Email interno cuando se recibe un nuevo lead',
            'mailable' => LeadInternoMail::class,
            'data_class' => LeadInternoData::class,
        ],
        'acuse' => [
            'name' => 'Acuse de Recibido',
            'description' => 'Confirmación enviada al cliente tras contacto',
            'mailable' => AcuseMail::class,
            'data_class' => AcuseData::class,
        ],
        'cita' => [
            'name' => 'Confirmación de Cita',
            'description' => 'Confirmación de cita agendada',
            'mailable' => CitaMail::class,
            'data_class' => CitaData::class,
        ],
        'comprador' => [
            'name' => 'Listado de Propiedad',
            'description' => 'Envío de propiedad sugerida al cliente',
            'mailable' => CompradorMail::class,
            'data_class' => CompradorData::class,
        ],
        'bienvenida' => [
            'name' => 'Bienvenida a Área de Clientes',
            'description' => 'Email de bienvenida con credenciales',
            'mailable' => BienvenidaMail::class,
            'data_class' => BienvenidaData::class,
        ],
    ];

    public function index()
    {
        $v4Templates = collect($this->templates)->map(fn($config, $key) => [
            'id' => $key,
            'name' => $config['name'],
            'description' => $config['description'],
            'route' => "admin.transactional-emails.preview.{$key}",
        ]);

        return view('admin.email.templates.v4-index', compact('v4Templates'));
    }

    public function preview(string $templateId)
    {
        if (!isset($this->templates[$templateId])) {
            abort(404);
        }

        $template = $this->templates[$templateId];
        $dummyData = $this->getDummyData($templateId);
        $mailable = new $template['mailable']($dummyData);

        return view('admin.email.templates.v4-preview', [
            'templateId' => $templateId,
            'templateName' => $template['name'],
            'description' => $template['description'],
            'mailable' => $mailable,
            'dummyData' => $dummyData,
        ]);
    }

    public function sendTest(Request $request, string $templateId)
    {
        if (!isset($this->templates[$templateId])) {
            abort(404);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $template = $this->templates[$templateId];
        $dummyData = $this->getDummyData($templateId);
        $mailable = new $template['mailable']($dummyData);

        try {
            Mail::to($request->email)->send($mailable);

            return redirect()
                ->route('admin.transactional-emails.preview', $templateId)
                ->with('success', "Email de prueba enviado a {$request->email}");
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.transactional-emails.preview', $templateId)
                ->with('error', "Error enviando email: {$e->getMessage()}");
        }
    }

    protected function getDummyData(string $templateId): mixed
    {
        return match($templateId) {
            'lead-interno' => new LeadInternoData(
                nombre: 'Juan Pérez López',
                email: 'juan.perez@example.com',
                telefono: '+52 55 1234 5678',
                origen: 'Contacto web',
                fecha: now()->format('Y-m-d H:i'),
                mensaje: 'Estoy muy interesado en vender mi propiedad ubicada en Benito Juárez. He visto que manejan pocos inmuebles y eso me atrae.'
            ),
            'acuse' => new AcuseData(
                folio: 'lead-' . uniqid(),
                email: 'cliente@example.com'
            ),
            'cita' => new CitaData(
                email: 'cliente@example.com',
                dia_semana: 'Lunes',
                dia: '15',
                mes: 'abril',
                anio: '2026',
                hora: '10:00 AM',
                duracion: '30',
                direccion: 'Paseo de los Tamarindos 400, Depto 1501',
                colonia: 'Bosques de las Lomas',
                asesor: 'María García Rodríguez'
            ),
            'comprador' => new CompradorData(
                email: 'cliente@example.com',
                colonia: 'Benito Juárez',
                titulo: 'Casa moderna con azotea y jardín',
                metros: '350',
                recamaras: '3',
                banos: '2',
                estacionamientos: '2',
                precio: '4500000',
                foto_url: null
            ),
            'bienvenida' => new BienvenidaData(
                email: 'cliente@example.com',
                usuario: 'juan.perez@homedelvalle.com',
                password_temporal: 'Temp123!@#Secure',
                url_acceso: 'https://app.homedelvalle.mx/login'
            ),
            default => null,
        };
    }

    public function renderHtml(string $templateId)
    {
        if (!isset($this->templates[$templateId])) {
            abort(404);
        }

        $template = $this->templates[$templateId];
        $dummyData = $this->getDummyData($templateId);
        $mailable = new $template['mailable']($dummyData);

        return $mailable->render();
    }
}
