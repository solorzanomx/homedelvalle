<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\SiteSetting;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Browsershot\Browsershot;

class PropertyFichaController extends Controller
{
    /**
     * Download property ficha as PDF.
     *
     * Query params:
     *   ?broker=0  — force institutional mode (no broker block)
     *   ?broker=1  — force broker mode (overrides default)
     */
    public function pdf(Request $request, Property $property)
    {
        $property->load(['photos', 'broker', 'qrCode']);

        $siteSetting   = SiteSetting::current();
        $siteName      = $siteSetting?->site_name ?? 'Home del Valle';

        $includeBroker = $property->broker_id && $property->broker;
        if ($request->has('broker')) {
            $includeBroker = (bool) $request->integer('broker');
        }

        $html = view('properties.partials.ficha', [
            'property'      => $property,
            'siteName'      => $siteName,
            'siteSetting'   => $siteSetting,
            'includeBroker' => $includeBroker,
            'mode'          => 'pdf',
        ])->render();

        $pdf = Browsershot::html($html)
            ->setChromePath(config('browsershot.chrome_path'))
            ->setNodeBinary(config('browsershot.node_path'))
            ->setNpmBinary(config('browsershot.npm_path'))
            ->noSandbox()
            ->format('A4')
            ->pdf();

        $filename = 'Ficha-' . \Illuminate\Support\Str::slug($property->title) . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
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
            'name'  => 'nullable|string|max:255',
        ]);

        $property->load(['photos', 'broker', 'qrCode']);

        $siteSetting   = SiteSetting::current();
        $siteName      = $siteSetting?->site_name ?? 'Home del Valle';
        $includeBroker = $property->broker_id && $property->broker;

        $html = view('properties.partials.ficha', [
            'property'      => $property,
            'siteName'      => $siteName,
            'siteSetting'   => $siteSetting,
            'includeBroker' => $includeBroker,
            'mode'          => 'email',
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
