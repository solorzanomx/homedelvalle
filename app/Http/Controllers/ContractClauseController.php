<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractClause;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;

class ContractClauseController extends Controller
{
    // ---- Cláusulas de un Contract individual (por trato) ----

    public function store(Request $request, string $contractId)
    {
        $contract = Contract::findOrFail($contractId);
        $this->storeClause($request, $contract);
        return back()->with('success', 'Cláusula agregada.');
    }

    public function update(Request $request, string $contractId, string $clauseId)
    {
        $contract = Contract::findOrFail($contractId);
        $clause = $contract->clauses()->findOrFail($clauseId);
        $this->updateClause($request, $clause);
        return back()->with('success', 'Cláusula actualizada.');
    }

    public function destroy(string $contractId, string $clauseId)
    {
        $contract = Contract::findOrFail($contractId);
        $clause = $contract->clauses()->findOrFail($clauseId);
        $this->destroyClause($clause);
        return back()->with('success', 'Cláusula eliminada.');
    }

    public function reorder(Request $request, string $contractId)
    {
        $contract = Contract::findOrFail($contractId);
        $this->reorderClauses($request, $contract->clauses());
        return back()->with('success', 'Orden actualizado.');
    }

    // ---- Cláusulas de una ContractTemplate (plantilla compartida) ----

    public function storeForTemplate(Request $request, string $templateId)
    {
        $template = ContractTemplate::findOrFail($templateId);
        $this->storeClause($request, $template);
        return back()->with('success', 'Cláusula agregada a la plantilla.');
    }

    public function updateForTemplate(Request $request, string $templateId, string $clauseId)
    {
        $template = ContractTemplate::findOrFail($templateId);
        $clause = $template->clauses()->findOrFail($clauseId);
        $this->updateClause($request, $clause);
        return back()->with('success', 'Cláusula de plantilla actualizada.');
    }

    public function destroyForTemplate(string $templateId, string $clauseId)
    {
        $template = ContractTemplate::findOrFail($templateId);
        $clause = $template->clauses()->findOrFail($clauseId);
        $this->destroyClause($clause);
        return back()->with('success', 'Cláusula de plantilla eliminada.');
    }

    public function reorderForTemplate(Request $request, string $templateId)
    {
        $template = ContractTemplate::findOrFail($templateId);
        $this->reorderClauses($request, $template->clauses());
        return back()->with('success', 'Orden actualizado.');
    }

    // ---- Lógica compartida ----

    protected function storeClause(Request $request, Contract|ContractTemplate $clauseable): ContractClause
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'section' => 'required|in:declaracion,clausula,firma',
        ]);

        $nextOrder = $clauseable->clauses()->max('sort_order') + 1;

        return $clauseable->clauses()->create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'section' => $validated['section'],
            'sort_order' => $nextOrder,
        ]);
    }

    protected function updateClause(Request $request, ContractClause $clause): void
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $clause->update($validated);
    }

    protected function destroyClause(ContractClause $clause): void
    {
        if ($clause->is_locked) {
            abort(403, 'Esta cláusula está protegida y no puede eliminarse.');
        }

        $clause->delete();
    }

    protected function reorderClauses(Request $request, $clausesQuery): void
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($validated['order'] as $index => $clauseId) {
            $clausesQuery->clone()->where('id', $clauseId)->update(['sort_order' => $index]);
        }
    }
}
