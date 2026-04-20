<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\SiteSetting;
use App\Services\EmailService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyFichaController extends Controller
{
    /**
     * Download property ficha as PDF.
     */
    public function pdf(Property $property)
    {
        $property->load(['photos', 'broker']);
        $siteName = SiteSetting::first()?->site_name ?? 'Home del Valle';

        $html = view('properties.partials.ficha', [
            'property' => $property,
            'siteName' => $siteName,
            'mode' => 'pdf',
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'sans-serif');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = 'Ficha-' . \Illuminate\Support\Str::slug($property->title) . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Send property ficha by email.
     */
    public function email(Request $request, Property $property, EmailService $emailService)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $property->load(['photos', 'broker']);
        $siteName = SiteSetting::first()?->site_name ?? 'Home del Valle';

        $html = view('properties.partials.ficha', [
            'property' => $property,
            'siteName' => $siteName,
            'mode' => 'email',
        ])->render();

        $subject = $property->title . ' — ' . $siteName;
        $sent = $emailService->send(
            $request->input('email'),
            $subject,
            $html,
            $request->input('name'),
            null,
            Auth::user()
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => $sent,
                'message' => $sent ? 'Ficha enviada correctamente.' : 'Error al enviar el correo.',
            ]);
        }

        return back()->with($sent ? 'success' : 'error', $sent ? 'Ficha enviada correctamente.' : 'Error al enviar el correo.');
    }
}
