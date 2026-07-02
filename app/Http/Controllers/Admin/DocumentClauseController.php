<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentClause;
use App\Services\PurchaseOfferGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Edición de las cláusulas legales editables de un documento de marca.
 * Hoy solo Carta Oferta de Compra — ver PurchaseOfferGeneratorService::DEFAULT_CLAUSES.
 */
class DocumentClauseController extends Controller
{
    public function editOfertaCompra()
    {
        $clauses = collect(PurchaseOfferGeneratorService::DEFAULT_CLAUSES)->map(function ($default, $key) {
            return [
                'key'     => $key,
                'label'   => PurchaseOfferGeneratorService::CLAUSE_LABELS[$key],
                'default' => $default,
                'value'   => DocumentClause::where('document_key', 'oferta_compra')->where('clause_key', $key)->value('value') ?? $default,
            ];
        });

        $lastUpdated = DocumentClause::where('document_key', 'oferta_compra')
            ->with('updatedBy')
            ->latest('updated_at')
            ->first();

        return view('admin.documentos.clausulas', compact('clauses', 'lastUpdated'));
    }

    public function updateOfertaCompra(Request $request)
    {
        $keys = array_keys(PurchaseOfferGeneratorService::DEFAULT_CLAUSES);

        $validated = $request->validate(
            collect($keys)->mapWithKeys(fn ($key) => [$key => 'required|string|max:5000'])->all()
        );

        foreach ($validated as $clauseKey => $value) {
            DocumentClause::updateOrCreate(
                ['document_key' => 'oferta_compra', 'clause_key' => $clauseKey],
                ['value' => $value, 'updated_by' => Auth::id()]
            );
        }

        return redirect()->route('admin.documentos.oferta-compra.clausulas')->with('success', 'Cláusulas actualizadas correctamente.');
    }
}
