<?php

namespace App\Http\Controllers;

use App\Models\DeveloperContact;
use App\Models\Property;
use App\Services\ConstructorValuationService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class PropertyTechnicalFichaController extends Controller
{
    public function pdf(Property $property)
    {
        $path = $this->generate($property);

        return response(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Ficha-Tecnica-' . \Illuminate\Support\Str::slug($property->title) . '.pdf"',
        ]);
    }

    public function showSend(Property $property)
    {
        $contacts = DeveloperContact::with('developerCompany')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->orderBy('name')
            ->get();

        return view('properties.send-ficha-tecnica', compact('property', 'contacts'));
    }

    public function send(Request $request, Property $property)
    {
        $validated = $request->validate([
            'contact_ids'   => 'required|array|min:1',
            'contact_ids.*' => 'exists:developer_contacts,id',
            'message'       => 'nullable|string|max:1000',
        ]);

        $path = $this->generate($property);
        $emailService = app(EmailService::class);
        $sentCount = 0;

        $contacts = DeveloperContact::whereIn('id', $validated['contact_ids'])->get();

        foreach ($contacts as $contact) {
            if (!$contact->email) {
                continue;
            }

            $body = view('emails.ficha-tecnica-envio', [
                'contact'  => $contact,
                'property' => $property,
                'message'  => $validated['message'] ?? null,
            ])->render();

            try {
                $sent = $emailService->send(
                    $contact->email,
                    'Ficha técnica — ' . $property->title,
                    $body,
                    $contact->name,
                    null,
                    Auth::user(),
                    [$path]
                );
                if ($sent) {
                    $sentCount++;
                }
            } catch (\Throwable $e) {
                Log::warning('PropertyTechnicalFichaController: fallo al enviar', [
                    'contact_id' => $contact->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('properties.show', $property)
            ->with($sentCount > 0 ? 'success' : 'error', $sentCount > 0
                ? "Ficha técnica enviada a {$sentCount} contacto(s)."
                : 'No se pudo enviar la ficha a ningún contacto.');
    }

    private function generate(Property $property): string
    {
        $property->loadMissing(['photos', 'developmentProfile', 'marketColonia']);
        $profile = $property->developmentProfile;

        $vrc = null;
        if ($profile && $profile->cos && $profile->cus && $property->price) {
            $m2Terreno = (float) ($property->lot_area ?: $property->area ?: 0);
            if ($m2Terreno > 0) {
                $vrc = (new ConstructorValuationService())->calculate(
                    m2Terreno: $m2Terreno,
                    cos: (float) $profile->cos,
                    cus: (float) $profile->cus,
                    pisos: (int) ($profile->niveles_permitidos ?: 1),
                    precioTerreno: (float) $property->price,
                    coloniaId: $property->market_colonia_id,
                );
                if (!($vrc['available'] ?? false)) {
                    $vrc = null;
                }
            }
        }

        $folio = 'FT-' . str_pad((string) $property->id, 5, '0', STR_PAD_LEFT);
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $html = view('properties.partials.ficha-tecnica', compact('property', 'profile', 'vrc', 'folio', 'fecha'))->render();

        $dir = storage_path('app/fichas-tecnicas');
        File::ensureDirectoryExists($dir);
        $path = $dir . '/' . $folio . '-' . time() . '.pdf';

        Browsershot::html($html)
            ->setChromePath(config('browsershot.chrome_path'))
            ->setNodeBinary(config('browsershot.node_path'))
            ->setNpmBinary(config('browsershot.npm_path'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->format('Letter')
            ->showBackground()
            ->showBrowserHeaderAndFooter()
            ->headerHtml('<div style="width:100%;font-family:Arial,sans-serif;font-size:8px;padding:0 18mm;display:flex;justify-content:space-between;color:#1e1b4b;"><span style="font-weight:700;">Home del Valle</span><span style="text-transform:uppercase;letter-spacing:1px;color:#64748b;">Ficha Técnica de Predio · Confidencial</span></div>')
            ->footerHtml('<div style="width:100%;font-family:Arial,sans-serif;font-size:8px;padding:0 18mm;display:flex;justify-content:space-between;color:#94a3b8;"><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Página <span class="pageNumber"></span> de <span class="totalPages"></span></span></div>')
            ->margins(22, 18, 18, 18)
            ->emulateMedia('screen')
            ->timeout(120)
            ->savePdf($path);

        return $path;
    }
}
