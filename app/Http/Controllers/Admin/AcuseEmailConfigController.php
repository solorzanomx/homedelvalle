<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Mailables\AcuseMail;
use App\Models\AcuseEmailConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AcuseEmailConfigController extends Controller {

    public function index() {
        $types = AcuseEmailConfig::labels();
        $configs = AcuseEmailConfig::whereIn('form_type', array_keys($types))->get()->keyBy('form_type');
        return view('admin.email.acuse-configs.index', compact('types', 'configs'));
    }

    public function edit(string $formType) {
        abort_unless(array_key_exists($formType, AcuseEmailConfig::labels()), 404);
        $config = AcuseEmailConfig::forType($formType);
        $label  = AcuseEmailConfig::labels()[$formType];
        $icons  = ['icon-home.png','icon-chart.png','icon-shield.png','icon-pin.png','icon-check.png','icon-chat.png','icon-mail.png','icon-homestep.png','icon-clock.png','icon-area.png','icon-bed.png','icon-bath.png','icon-car.png'];
        $ctaTypes = [
            'static'               => 'URL fija',
            'precios_colonia'      => 'Precios por colonia (venta) — dinámico',
            'precios_colonia_renta'=> 'Precios por colonia (renta) — dinámico',
            'propiedades_compra'   => 'Propiedades en venta',
            'propiedades_renta'    => 'Propiedades en renta',
            'precios'              => 'Observatorio de precios',
        ];
        return view('admin.email.acuse-configs.edit', compact('config','formType','label','icons','ctaTypes'));
    }

    public function update(Request $request, string $formType) {
        abort_unless(array_key_exists($formType, AcuseEmailConfig::labels()), 404);

        $validated = $request->validate([
            'subject'         => 'required|string|max:255',
            'badge'           => 'required|string|max:100',
            'titulo'          => 'required|string|max:200',
            'bajada'          => 'required|string|max:600',
            'nota'            => 'nullable|string|max:300',
            'cta1_label'      => 'required|string|max:100',
            'cta1_type'       => 'required|string',
            'cta1_url_static' => 'nullable|string|max:500',
            'cta2_label'      => 'nullable|string|max:100',
            'cta2_type'       => 'nullable|string',
            'cta2_url_static' => 'nullable|string|max:500',
            'paso1_icon'      => 'required|string',
            'paso1_titulo'    => 'required|string|max:100',
            'paso1_desc'      => 'required|string|max:300',
            'paso2_icon'      => 'required|string',
            'paso2_titulo'    => 'required|string|max:100',
            'paso2_desc'      => 'required|string|max:300',
            'paso3_icon'      => 'required|string',
            'paso3_titulo'    => 'required|string|max:100',
            'paso3_desc'      => 'required|string|max:300',
        ]);

        AcuseEmailConfig::updateOrCreate(['form_type' => $formType], $validated);

        return redirect()->route('admin.acuse-configs.index')->with('success', 'Configuración guardada.');
    }

    public function preview(string $formType) {
        abort_unless(array_key_exists($formType, AcuseEmailConfig::labels()), 404);

        $samplePayloads = [
            'vendedor'          => ['colonia' => 'Del Valle', 'tipo_propiedad' => 'departamento', 'timing' => 'inmediato'],
            'comprador'         => ['tipo_inmueble' => ['departamento'], 'zonas' => ['Del Valle', 'Narvarte'], 'presupuesto' => '4m_6m'],
            'arrendatario'      => ['tipo_inmueble' => ['departamento'], 'zonas' => ['Del Valle'], 'renta_mensual' => '15k_25k', 'mascotas' => 'perro'],
            'propietario_renta' => ['colonia' => 'Del Valle', 'tipo_propiedad' => 'departamento', 'renta_esperada' => '18000'],
            'b2b'               => ['tipo_operacion' => ['compra_terminado'], 'zonas' => ['Benito Juárez'], 'presupuesto' => '50m_120m'],
            'contacto'          => ['message' => 'Quisiera más información sobre sus servicios.'],
        ];

        $mailable = new AcuseMail(new AcuseData(
            folio:     'preview-001',
            email:     'preview@homedelvalle.mx',
            form_type: $formType,
            nombre:    'Juan Pérez López',
            payload:   $samplePayloads[$formType] ?? [],
        ));

        return $mailable->render();
    }

    public function sendTest(Request $request, string $formType) {
        abort_unless(array_key_exists($formType, AcuseEmailConfig::labels()), 404);

        $email = $request->input('email', auth()->user()->email);

        $samplePayloads = [
            'vendedor'          => ['colonia' => 'Del Valle', 'tipo_propiedad' => 'departamento', 'timing' => 'inmediato'],
            'comprador'         => ['tipo_inmueble' => ['departamento'], 'zonas' => ['Del Valle', 'Narvarte'], 'presupuesto' => '4m_6m'],
            'arrendatario'      => ['tipo_inmueble' => ['departamento'], 'zonas' => ['Del Valle'], 'renta_mensual' => '15k_25k', 'mascotas' => 'perro'],
            'propietario_renta' => ['colonia' => 'Del Valle', 'tipo_propiedad' => 'departamento', 'renta_esperada' => '18000'],
            'b2b'               => ['tipo_operacion' => ['compra_terminado'], 'zonas' => ['Benito Juárez']],
            'contacto'          => ['message' => 'Consulta de prueba.'],
        ];

        Mail::to($email)->send(new AcuseMail(new AcuseData(
            folio:     'test-' . time(),
            email:     $email,
            form_type: $formType,
            nombre:    'Juan Prueba López',
            payload:   $samplePayloads[$formType] ?? [],
        )));

        return back()->with('success', "Correo de prueba enviado a {$email}");
    }
}
