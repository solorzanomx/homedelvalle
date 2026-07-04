<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\ProviderCharge;
use App\Models\RentalProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderChargeController extends Controller
{
    private function rules(): array
    {
        return [
            'provider_company_id'   => 'required|exists:provider_companies,id',
            'provider_contact_id'   => 'nullable|exists:provider_contacts,id',
            'flow'                  => 'required|in:cargo,comision',
            'service_description'   => 'required|string|max:255',
            'amount'                => 'nullable|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'notes'                 => 'nullable|string|max:2000',
        ];
    }

    public function storeForOperation(Request $request, Operation $operation)
    {
        $validated = $request->validate($this->rules());
        $operation->providerCharges()->create($validated + ['created_by' => Auth::id()]);

        return back()->with('success', 'Proveedor agregado al proceso.');
    }

    public function storeForRental(Request $request, RentalProcess $rental)
    {
        $validated = $request->validate($this->rules());
        $rental->providerCharges()->create($validated + ['created_by' => Auth::id()]);

        return back()->with('success', 'Proveedor agregado al proceso.');
    }

    public function updateStatus(Request $request, ProviderCharge $charge)
    {
        $validated = $request->validate([
            'status' => 'required|in:registrado,confirmado,liquidado',
        ]);

        $charge->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'liquidado' ? now() : $charge->paid_at,
        ]);

        return back()->with('success', 'Estatus actualizado.');
    }

    public function destroy(ProviderCharge $charge)
    {
        $charge->delete();
        return back()->with('success', 'Registro eliminado.');
    }
}
