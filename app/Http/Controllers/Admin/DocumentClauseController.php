<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentClause;
use App\Services\ContratoCompraventaGeneratorService;
use App\Services\ContratoExclusivaGeneratorService;
use App\Services\PurchaseOfferGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Edición de las cláusulas legales editables de los documentos de marca:
 * Carta Oferta de Compra (PurchaseOfferGeneratorService::DEFAULT_CLAUSES) y
 * Acuerdo de Representación (ContratoExclusivaGeneratorService::DEFAULT_CLAUSES).
 */
class DocumentClauseController extends Controller
{
    public function editOfertaCompra()
    {
        $documentTitle = 'Carta Oferta de Compra';
        $updateRoute   = route('admin.documentos.oferta-compra.clausulas.update');
        $tokenHint     = null;

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

        return view('admin.documentos.clausulas', compact('clauses', 'lastUpdated', 'documentTitle', 'updateRoute', 'tokenHint'));
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

    public function editContratoExclusiva()
    {
        $documentTitle = 'Acuerdo de Representación';
        $updateRoute   = route('admin.documentos.contrato-exclusiva.clausulas.update');
        $legalHint     = 'Se recomienda que un abogado revise cualquier cambio a estas cláusulas antes de usarlas con propietarios reales — especialmente la de comisión.';
        $tokenHint     = null;

        $clauses = collect(ContratoExclusivaGeneratorService::DEFAULT_CLAUSES)->map(function ($default, $key) {
            return [
                'key'     => $key,
                'label'   => ContratoExclusivaGeneratorService::CLAUSE_LABELS[$key],
                'default' => $default,
                'value'   => DocumentClause::where('document_key', 'contrato_exclusiva')->where('clause_key', $key)->value('value') ?? $default,
            ];
        });

        $lastUpdated = DocumentClause::where('document_key', 'contrato_exclusiva')
            ->with('updatedBy')
            ->latest('updated_at')
            ->first();

        return view('admin.documentos.clausulas', compact('clauses', 'lastUpdated', 'documentTitle', 'updateRoute', 'legalHint', 'tokenHint'));
    }

    public function updateContratoExclusiva(Request $request)
    {
        $keys = array_keys(ContratoExclusivaGeneratorService::DEFAULT_CLAUSES);

        $validated = $request->validate(
            collect($keys)->mapWithKeys(fn ($key) => [$key => 'required|string|max:5000'])->all()
        );

        foreach ($validated as $clauseKey => $value) {
            DocumentClause::updateOrCreate(
                ['document_key' => 'contrato_exclusiva', 'clause_key' => $clauseKey],
                ['value' => $value, 'updated_by' => Auth::id()]
            );
        }

        return redirect()->route('admin.documentos.contrato-exclusiva.clausulas')->with('success', 'Cláusulas actualizadas correctamente.');
    }

    public function editAdendumComision()
    {
        $documentTitle = 'Adéndum de Comisión Mercantil';
        $updateRoute   = route('admin.documentos.adendum-comision.clausulas.update');
        $legalHint     = 'Se recomienda que un abogado revise cualquier cambio a estas cláusulas antes de usarlas con propietarios reales — especialmente las de comisión.';
        $tokenHint     = 'Tokens disponibles: {{contrato_nombre}}, {{contrato_fecha}}, {{comprador}}, {{precio}}, {{precio_letras}}, {{comision}}, {{comision_letras}}, {{comision_contrato}}, {{comision_contrato_letras}}, {{comision_escritura}}, {{comision_escritura_letras}}.';

        $clauses = collect(\App\Services\AdendumComisionGeneratorService::DEFAULT_CLAUSES)->map(function ($default, $key) {
            return [
                'key'     => $key,
                'label'   => \App\Services\AdendumComisionGeneratorService::CLAUSE_LABELS[$key],
                'default' => $default,
                'value'   => DocumentClause::where('document_key', 'adendum_comision')->where('clause_key', $key)->value('value') ?? $default,
            ];
        });

        $lastUpdated = DocumentClause::where('document_key', 'adendum_comision')
            ->with('updatedBy')
            ->latest('updated_at')
            ->first();

        return view('admin.documentos.clausulas', compact('clauses', 'lastUpdated', 'documentTitle', 'updateRoute', 'legalHint', 'tokenHint'));
    }

    public function updateAdendumComision(Request $request)
    {
        $keys = array_keys(\App\Services\AdendumComisionGeneratorService::DEFAULT_CLAUSES);

        $validated = $request->validate(
            collect($keys)->mapWithKeys(fn ($key) => [$key => 'required|string|max:5000'])->all()
        );

        foreach ($validated as $clauseKey => $value) {
            DocumentClause::updateOrCreate(
                ['document_key' => 'adendum_comision', 'clause_key' => $clauseKey],
                ['value' => $value, 'updated_by' => Auth::id()]
            );
        }

        return redirect()->route('admin.documentos.adendum-comision.clausulas')->with('success', 'Cláusulas actualizadas correctamente.');
    }

    public function editContratoCompraventa()
    {
        $documentTitle = 'Contrato de Compraventa';
        $updateRoute   = route('admin.documentos.contrato-compraventa.clausulas.update');
        $legalHint     = 'Se recomienda que un abogado revise cualquier cambio a estas cláusulas antes de usarlas en operaciones reales.';
        $tokenHint     = null;

        $clauses = collect(ContratoCompraventaGeneratorService::DEFAULT_CLAUSES)->map(function ($default, $key) {
            return [
                'key'     => $key,
                'label'   => ContratoCompraventaGeneratorService::CLAUSE_LABELS[$key],
                'default' => $default,
                'value'   => DocumentClause::where('document_key', 'contrato_compraventa')->where('clause_key', $key)->value('value') ?? $default,
            ];
        });

        $lastUpdated = DocumentClause::where('document_key', 'contrato_compraventa')
            ->with('updatedBy')
            ->latest('updated_at')
            ->first();

        return view('admin.documentos.clausulas', compact('clauses', 'lastUpdated', 'documentTitle', 'updateRoute', 'legalHint', 'tokenHint'));
    }

    public function updateContratoCompraventa(Request $request)
    {
        $keys = array_keys(ContratoCompraventaGeneratorService::DEFAULT_CLAUSES);

        $validated = $request->validate(
            collect($keys)->mapWithKeys(fn ($key) => [$key => 'required|string|max:5000'])->all()
        );

        foreach ($validated as $clauseKey => $value) {
            DocumentClause::updateOrCreate(
                ['document_key' => 'contrato_compraventa', 'clause_key' => $clauseKey],
                ['value' => $value, 'updated_by' => Auth::id()]
            );
        }

        return redirect()->route('admin.documentos.contrato-compraventa.clausulas')->with('success', 'Cláusulas actualizadas correctamente.');
    }
}
