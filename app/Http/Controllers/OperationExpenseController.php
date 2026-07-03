<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\OperationExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationExpenseController extends Controller
{
    public function store(Request $request, Operation $operation)
    {
        $validated = $request->validate([
            'category'    => 'required|in:fotografia,publicidad,staging,otro',
            'description' => 'nullable|string|max:200',
            'amount'      => 'required|numeric|min:0',
        ]);

        $operation->expenses()->create($validated + ['created_by' => Auth::id()]);

        return back()->with('success', 'Gasto registrado.');
    }

    public function destroy(Operation $operation, OperationExpense $expense)
    {
        abort_unless($expense->operation_id === $operation->id, 404);

        $expense->delete();

        return back()->with('success', 'Gasto eliminado.');
    }
}
