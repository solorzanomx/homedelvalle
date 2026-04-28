<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateAssignmentRequest;
use App\Models\CustomEmailTemplate;
use App\Models\EmailTemplateAssignment;
use Illuminate\Http\Request;

class TemplateAssignmentController extends Controller
{
}

    public function store(CustomEmailTemplate $custom_template, StoreTemplateAssignmentRequest $request)
    {
        $validated = $request->validated();

        try {
            EmailTemplateAssignment::create([
                'template_id' => $custom_template->id,
                'trigger_type' => $validated['trigger_type'],
                'trigger_name' => $validated['trigger_name'],
                'is_active' => true,
            ]);

            return back()->with('success', 'Assignment creado exitosamente');
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()
                    ->withErrors(['trigger_name' => 'Este trigger ya está asignado a este template'])
                    ->withInput();
            }

            return back()
                ->withErrors(['error' => 'Error al crear assignment: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function toggle(CustomEmailTemplate $custom_template, EmailTemplateAssignment $assignment, Request $request)
    {
        if ($assignment->template_id !== $custom_template->id) {
            abort(403);
        }

        $assignment->toggleActive();

        return back()->with('success', 'Assignment actualizado');
    }

    public function destroy(CustomEmailTemplate $custom_template, EmailTemplateAssignment $assignment)
    {
        if ($assignment->template_id !== $custom_template->id) {
            abort(403);
        }

        $triggerName = $assignment->trigger_name;
        $assignment->delete();

        return back()->with('success', "Assignment '{$triggerName}' eliminado");
    }

    public function getTriggers(Request $request)
    {
        $triggerType = $request->get('trigger_type');

        $triggers = match ($triggerType) {
            'event' => [
                'FormSubmitted' => 'Formulario enviado',
                'UserCreated' => 'Usuario creado',
                'UserActivated' => 'Usuario activado',
                'LeadAssigned' => 'Lead asignado',
                'PropertyListed' => 'Propiedad listada',
            ],
            'form_submission' => [
                'seller_valuation' => 'Solicitud de valuación de vendedor',
                'buyer_search' => 'Búsqueda de comprador',
                'contact_form' => 'Formulario de contacto',
                'developer_brief' => 'Briefing de desarrollador',
            ],
            'user_action' => [
                'first_login' => 'Primer acceso',
                'profile_updated' => 'Perfil actualizado',
                'password_changed' => 'Contraseña cambiada',
                'document_uploaded' => 'Documento cargado',
            ],
            default => [],
        };

        return response()->json($triggers);
    }
}
