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
        if ($formSubmission->client_id) {
            return back()->with('success', 'Este lead ya tiene un cliente asociado.');
        }

        $data = [
            'name'             => $formSubmission->full_name,
            'phone'            => $formSubmission->phone,
            'whatsapp'         => $formSubmission->phone,
            'client_type'      => $formSubmission->client_type,
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
            return back()->with('success', "Lead vinculado al cliente existente «{$existing->name}».");
        }

        $client = Client::create(array_merge($data, ['email' => $formSubmission->email]));
        $formSubmission->update(['client_id' => $client->id]);

        return back()->with('success', "Cliente «{$client->name}» creado exitosamente.");
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
