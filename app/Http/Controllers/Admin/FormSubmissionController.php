<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = FormSubmission::query()->latest();

        if ($type = $request->get('type')) {
            $query->where('form_type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($tag = $request->get('tag')) {
            $query->where('lead_tag', $tag);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email',    'like', "%{$search}%")
                  ->orWhere('phone',    'like', "%{$search}%");
            });
        }

        $submissions = $query->paginate(25)->withQueryString();

        $counts = [
            'total'     => FormSubmission::count(),
            'new'       => FormSubmission::where('status', 'new')->count(),
            'vendedor'  => FormSubmission::where('form_type', 'vendedor')->count(),
            'predio'    => FormSubmission::where('form_type', 'vendedor_predio')->count(),
            'comprador' => FormSubmission::where('form_type', 'comprador')->count(),
            'b2b'       => FormSubmission::where('form_type', 'b2b')->count(),
            'contacto'  => FormSubmission::where('form_type', 'contacto')->count(),
        ];

        return view('admin.form-submissions.index', compact('submissions', 'counts'));
    }

    public function show(FormSubmission $formSubmission)
    {
        if (! $formSubmission->seen_at) {
            $formSubmission->update(['seen_at' => now()]);
        }
        return view('admin.form-submissions.show', ['submission' => $formSubmission]);
    }

    public function updateStatus(Request $request, FormSubmission $formSubmission)
    {
        $request->validate(['status' => 'required|in:new,contacted,qualified,won,lost']);
        $formSubmission->update([
            'status'       => $request->status,
            'contacted_at' => $request->status === 'contacted' && !$formSubmission->contacted_at ? now() : $formSubmission->contacted_at,
        ]);
        return back()->with('success', 'Estado actualizado');
    }

    public function updateNotes(Request $request, FormSubmission $formSubmission)
    {
        $request->validate(['notes' => 'nullable|string|max:2000']);
        $formSubmission->update(['notes' => $request->notes]);
        return back()->with('success', 'Notas guardadas');
    }

    public function convertToClient(FormSubmission $formSubmission)
    {
        // Leads de propietario (quiere vender/rentar su inmueble, o vender su
        // predio a una desarrolladora) van directo al wizard de captación con
        // el cliente ya cargado — evita re-teclear y duplicar. Otros tipos
        // (comprador, b2b, contacto) solo se convierten a Client, sin
        // captación. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
        $goesToCaptacion = in_array($formSubmission->form_type, ['vendedor', 'vendedor_predio']);

        if ($formSubmission->client_id) {
            if ($goesToCaptacion) {
                return redirect()
                    ->route('admin.captaciones.create-from-call', ['client_id' => $formSubmission->client_id, 'form_submission_id' => $formSubmission->id])
                    ->with('success', 'Este lead ya tiene un cliente asociado.');
            }
            return back()->with('success', 'Este lead ya tiene un cliente asociado.');
        }

        // client_type se re-deriva de interest_types en vez de copiarse tal
        // cual del FormSubmission — mismo bug ya corregido en
        // FormSubmissionsTable::convertToClient() (auditoría 2026-07-04).
        $data = [
            'name'             => $formSubmission->full_name,
            'phone'            => $formSubmission->phone,
            'whatsapp'         => $formSubmission->phone,
            'client_type'      => \App\Models\Client::deriveClientType($formSubmission->interest_types ?? []) ?? $formSubmission->client_type,
            'lead_temperature' => $formSubmission->lead_temperature ?? 'warm',
            'budget_min'       => $formSubmission->budget_min,
            'budget_max'       => $formSubmission->budget_max,
            'property_type'    => $formSubmission->property_type,
            'interest_types'   => $formSubmission->interest_types,
            'utm_source'       => $formSubmission->utm_source,
            'utm_medium'       => $formSubmission->utm_medium,
            'utm_campaign'     => $formSubmission->utm_campaign,
            'lead_source'      => 'form_' . $formSubmission->form_type,
            'initial_notes'    => $formSubmission->payload['mensaje'] ?? null,
        ];

        // Si ya existe un cliente con ese email, vincularlo sin duplicar
        $existing = Client::where('email', $formSubmission->email)->first();

        if ($existing) {
            $formSubmission->update(['client_id' => $existing->id]);

            if ($goesToCaptacion) {
                return redirect()
                    ->route('admin.captaciones.create-from-call', ['client_id' => $existing->id, 'form_submission_id' => $formSubmission->id])
                    ->with('success', "Lead vinculado al cliente existente «{$existing->name}».");
            }
            return back()->with('success', "Lead vinculado al cliente existente «{$existing->name}».");
        }

        $client = Client::create(array_merge($data, ['email' => $formSubmission->email]));
        $formSubmission->update(['client_id' => $client->id]);

        if ($goesToCaptacion) {
            return redirect()
                ->route('admin.captaciones.create-from-call', ['client_id' => $client->id, 'form_submission_id' => $formSubmission->id])
                ->with('success', "Cliente «{$client->name}» creado exitosamente.");
        }
        return back()->with('success', "Cliente «{$client->name}» creado exitosamente.");
    }

    /**
     * Un "lead" de portal que en realidad es otro broker pidiendo colaboración
     * no se convierte en Client: se registra en Brokers Externos (el módulo de
     * comisión compartida) para la red de colaboración — de ahí salen los
     * envíos de inventario cuando hay que vender rápido.
     */
    public function convertToBroker(FormSubmission $formSubmission)
    {
        $existing = \App\Models\Broker::where(function ($q) use ($formSubmission) {
            $q->where('email', $formSubmission->email);
            if ($formSubmission->phone && $formSubmission->phone !== 'sin teléfono') {
                $q->orWhere('phone', $formSubmission->phone);
            }
        })->first();

        if ($existing) {
            $formSubmission->update(['status' => 'qualified', 'notes' => trim(($formSubmission->notes ?? '') . "\nYa registrado en Brokers Externos (#{$existing->id}).")]);

            return redirect()->route('brokers.show', $existing)
                ->with('success', "Este contacto ya estaba en Brokers Externos: «{$existing->name}».");
        }

        // Especialidad inferida de la propiedad por la que preguntó
        $propiedad   = $formSubmission->payload['propiedad_local'] ?? null;
        $especialidad = $propiedad ? "Preguntó por: {$propiedad}" : null;

        $broker = \App\Models\Broker::create([
            'name'            => $formSubmission->full_name,
            'email'           => str_contains($formSubmission->email, '@sin-correo.easybroker') ? null : $formSubmission->email,
            'phone'           => $formSubmission->phone === 'sin teléfono' ? null : $formSubmission->phone,
            'status'          => 'active',
            'specialty'       => $especialidad,
            'referral_source' => 'Lead de portal (' . ($formSubmission->payload['portal_origen'] ?? 'EasyBroker') . ')',
            'bio'             => $formSubmission->payload['mensaje'] ?? null,
        ]);

        $formSubmission->update(['status' => 'qualified', 'lead_tag' => 'LEAD_BROKER']);

        return redirect()->route('brokers.show', $broker)
            ->with('success', "«{$broker->name}» registrado en Brokers Externos — completa su comisión y empresa.");
    }

    /**
     * Analiza el lead con IA bajo demanda: redacta la respuesta sugerida de
     * WhatsApp con todo el contexto (brief/propiedad) y, si es lead de portal
     * sin clasificar, lo clasifica de paso. Para cualquier tipo de lead.
     */
    public function aiSuggest(FormSubmission $formSubmission, \App\Services\AILeadClassifierService $classifier)
    {
        // Firma con el nombre de pila de quien está atendiendo (Alejandro,
        // Ana Laura…) — la respuesta la envía una persona, no "la empresa".
        $asesor = collect(explode(' ', trim((string) auth()->user()?->name)))->take(2)->implode(' ') ?: null;

        $respuesta = $classifier->suggestReply($formSubmission, $asesor);

        if ($respuesta === null) {
            return back()->with('error', 'La IA no respondió — intenta de nuevo en un momento.');
        }

        $payload = $formSubmission->payload ?? [];
        $payload['ai_respuesta'] = $respuesta;

        FormSubmission::withoutEvents(fn () => $formSubmission->update(['payload' => $payload]));

        return back()->with('success', 'Respuesta sugerida generada — revísala antes de enviar.');
    }

    public function destroy(FormSubmission $formSubmission)
    {
        try {
            $formSubmission->delete();
        } catch (\Throwable) {
            // Si falla el cleanup de media, forzar borrado directo
            \DB::table('form_submissions')->where('id', $formSubmission->id)->delete();
        }
        return redirect()->route('admin.form-submissions.index')->with('success', 'Lead eliminado');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        // Borrado directo sin disparar eventos de Media Library
        $count = \DB::table('form_submissions')->whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.form-submissions.index')->with('success', "{$count} leads eliminados");
    }
}
