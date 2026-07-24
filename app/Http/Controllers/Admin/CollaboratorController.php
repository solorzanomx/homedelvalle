<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CollaboratorController extends Controller
{
    public function index()
    {
        $collaborators = Collaborator::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.collaborators.index', compact('collaborators'));
    }

    public function create()
    {
        return view('admin.collaborators.form', ['collaborator' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('collaborators', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Collaborator::create($data);

        return redirect()->route('admin.collaborators.index')->with('success', 'Colaborador creado. Ya puedes copiarle su link de autorización.');
    }

    public function edit(Collaborator $collaborator)
    {
        return view('admin.collaborators.form', compact('collaborator'));
    }

    public function update(Request $request, Collaborator $collaborator)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            if ($collaborator->photo_path) {
                Storage::disk('public')->delete($collaborator->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('collaborators', 'public');
        } else {
            unset($data['photo_path']);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $collaborator->fill($data);

        // Si ya había autorizado y cambió algo de lo que vio, la autorización
        // anterior ya no cubre el contenido nuevo — se le pide de nuevo.
        $wasAuthorized = $collaborator->getOriginal('consent_status') === 'authorized';
        $changedConsentRelevant = collect(Collaborator::CONSENT_RELEVANT_FIELDS)
            ->contains(fn ($field) => $collaborator->isDirty($field));

        $resetMessage = null;
        if ($wasAuthorized && $changedConsentRelevant) {
            $collaborator->resetConsent();
            $resetMessage = ' Como cambiaste algo de lo que ya había autorizado, se reinició su autorización — necesita ver y aprobar la nueva versión antes de volver a publicarse.';
        }

        $collaborator->save();

        return redirect()->route('admin.collaborators.index')
            ->with('success', 'Colaborador actualizado.' . $resetMessage);
    }

    public function destroy(Collaborator $collaborator)
    {
        if ($collaborator->photo_path) {
            Storage::disk('public')->delete($collaborator->photo_path);
        }
        $collaborator->delete();

        return redirect()->route('admin.collaborators.index')->with('success', 'Colaborador eliminado.');
    }

    /**
     * Marca que el link de autorización se compartió (para mostrar
     * "enviado el ..." en el listado) — se llama vía JS al copiar el link.
     */
    public function markLinkSent(Collaborator $collaborator)
    {
        $collaborator->update(['link_sent_at' => now()]);

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:255',
            'role'        => 'required|string|max:255',
            'bio'         => 'nullable|string|max:600',
            'photo'       => 'nullable|image|max:2048',
            'link_url'    => 'nullable|url|max:500',
            'link_label'  => 'nullable|string|max:100',
            'email'       => 'nullable|email|max:255',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);
    }
}
