<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PresentationSend;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class PresentationPublicController extends Controller
{
    /** Vista pública de la presentación — accesible sin autenticación. */
    public function show(string $token, Request $request)
    {
        $send = PresentationSend::with(['captacion.client', 'captacion.property', 'sentBy'])
            ->where('tracking_token', $token)
            ->firstOrFail();

        $isFirstView = is_null($send->pdf_viewed_at);

        // Registrar primera vista
        if ($isFirstView) {
            $send->pdf_viewed_at = now();
        }
        $send->pdf_view_count++;
        $send->last_view_ip         = $request->ip();
        $send->last_view_user_agent = substr($request->userAgent() ?? '', 0, 255);
        $send->save();

        if ($isFirstView) {
            $this->notifyBrokerOfFirstView($send);
        }

        $captacion = $send->captacion;

        return view('public.presentacion.show', compact('send', 'captacion'));
    }

    /**
     * El propietario acaba de ver su presentación por primera vez — es el
     * momento perfecto para que el broker haga follow-up. Ver docs/07-FLUJO-
     * CAPTACION-Y-MEJORAS.md sección 2.
     */
    private function notifyBrokerOfFirstView(PresentationSend $send): void
    {
        $broker = $send->sentBy;
        if (!$broker) {
            return;
        }

        $captacion  = $send->captacion;
        $clientName = $captacion?->client?->name ?? 'El propietario';

        Notification::create([
            'user_id' => $broker->id,
            'type'    => 'system',
            'title'   => 'Presentación vista',
            'body'    => "{$clientName} acaba de ver tu presentación. Es un buen momento para dar seguimiento.",
            'data'    => ['url' => $captacion ? route('admin.captaciones.show', $captacion) : null],
        ]);

        if ($broker->whatsapp || $broker->phone) {
            try {
                app(WhatsAppService::class)->send(
                    $broker->whatsapp ?? $broker->phone,
                    "{$clientName} acaba de ver la presentación que le enviaste. Buen momento para darle seguimiento."
                );
            } catch (\Throwable $e) {
                Log::warning('notifyBrokerOfFirstView: WhatsApp failed: ' . $e->getMessage());
            }
        }
    }

    /** Descarga el PDF — registra pdf_downloaded_at. */
    public function download(string $token)
    {
        $send = PresentationSend::with(['captacion.client'])
            ->where('tracking_token', $token)
            ->firstOrFail();

        if (is_null($send->pdf_downloaded_at)) {
            $send->pdf_downloaded_at = now();
            $send->save();
        }

        $captacion = $send->captacion;
        $pdfPath   = $captacion->last_presentation_pdf_path;

        if (!$pdfPath || !file_exists($pdfPath)) {
            // Regenerar si el archivo ya no existe (limpieza de servidor)
            app(\App\Services\PresentationGeneratorService::class)->generatePdf($captacion);
            $captacion->refresh();
            $pdfPath = $captacion->last_presentation_pdf_path;
        }

        $filename = 'HDV-Presentacion-' . \Illuminate\Support\Str::slug($captacion->client->name ?? 'inmueble') . '.pdf';

        return Response::download($pdfPath, $filename, ['Content-Type' => 'application/pdf']);
    }

    /** Sirve el PDF inline para el iframe de la vista pública. */
    public function pdfInline(string $token)
    {
        $send      = PresentationSend::with('captacion')->where('tracking_token', $token)->firstOrFail();
        $captacion = $send->captacion;
        $pdfPath   = $captacion->last_presentation_pdf_path;

        if (!$pdfPath || !file_exists($pdfPath)) {
            app(\App\Services\PresentationGeneratorService::class)->generatePdf($captacion);
            $captacion->refresh();
            $pdfPath = $captacion->last_presentation_pdf_path;
        }

        return Response::make(file_get_contents($pdfPath), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="presentacion-hdv.pdf"',
            'Cache-Control'       => 'no-cache',
        ]);
    }

    /** Pixel de tracking para apertura de email — devuelve GIF 1×1. */
    public function emailTracking(string $token)
    {
        $send = PresentationSend::where('tracking_token', $token)->first();

        if ($send && is_null($send->email_opened_at)) {
            $send->email_opened_at = now();
            $send->save();
        }

        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($pixel, 200, [
            'Content-Type'   => 'image/gif',
            'Content-Length' => strlen($pixel),
            'Cache-Control'  => 'no-cache, no-store, must-revalidate',
            'Pragma'         => 'no-cache',
            'Expires'        => '0',
        ]);
    }
}
