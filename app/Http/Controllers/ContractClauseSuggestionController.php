<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractClauseSuggestion;
use Illuminate\Support\Facades\Auth;

class ContractClauseSuggestionController extends Controller
{
    public function request(string $contractId)
    {
        $contract = Contract::with('template')->findOrFail($contractId);

        if (!$contract->template || !$contract->template->uses_clauses) {
            return back()->with('error', 'Este contrato no usa cláusulas estructuradas.');
        }

        if ($contract->ai_suggestion_status === 'pending') {
            return back()->with('error', 'Ya hay una solicitud de sugerencias en proceso para este contrato.');
        }

        $contract->update([
            'ai_suggestion_status' => 'pending',
            'ai_suggestion_requested_at' => now(),
        ]);

        return back()->with('success', 'Sugerencias solicitadas — aparecerán en unos minutos.');
    }

    public function approve(string $suggestionId)
    {
        $suggestion = ContractClauseSuggestion::with('clause')->findOrFail($suggestionId);

        if ($suggestion->status !== 'pending') {
            return back()->with('error', 'Esta sugerencia ya fue revisada.');
        }

        switch ($suggestion->suggestion_type) {
            case 'edit':
                if ($suggestion->clause) {
                    $suggestion->clause->update([
                        'title' => $suggestion->proposed_title ?? $suggestion->clause->title,
                        'body' => $suggestion->proposed_body ?? $suggestion->clause->body,
                    ]);
                }
                break;

            case 'add':
                $contract = $suggestion->contract;
                $nextOrder = $contract->clauses()->max('sort_order') + 1;
                $contract->clauses()->create([
                    'title' => $suggestion->proposed_title ?? 'Cláusula sugerida por IA',
                    'body' => $suggestion->proposed_body ?? '',
                    'section' => 'clausula',
                    'sort_order' => $nextOrder,
                ]);
                break;

            case 'remove':
                if ($suggestion->clause && !$suggestion->clause->is_locked) {
                    $suggestion->clause->delete();
                }
                break;
        }

        $suggestion->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Sugerencia aplicada.');
    }

    public function reject(string $suggestionId)
    {
        $suggestion = ContractClauseSuggestion::findOrFail($suggestionId);

        if ($suggestion->status !== 'pending') {
            return back()->with('error', 'Esta sugerencia ya fue revisada.');
        }

        $suggestion->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Sugerencia rechazada.');
    }
}
