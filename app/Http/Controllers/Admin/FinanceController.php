<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Commission;
use App\Models\Deal;
use App\Models\Property;
use App\Models\Broker;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function dashboard()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $stats = [
            'income_month' => Transaction::where('type', 'income')->whereMonth('date', $currentMonth)->whereYear('date', $currentYear)->sum('amount'),
            'expense_month' => Transaction::where('type', 'expense')->whereMonth('date', $currentMonth)->whereYear('date', $currentYear)->sum('amount'),
            'pending_commissions' => Commission::where('status', 'pending')->sum('amount'),
            'total_commissions_paid' => Commission::where('status', 'paid')->sum('amount'),
        ];

        // Last 6 months income vs expense
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;
            $monthlyData[] = [
                'label' => $date->format('M Y'),
                'income' => Transaction::where('type', 'income')->whereMonth('date', $m)->whereYear('date', $y)->sum('amount'),
                'expense' => Transaction::where('type', 'expense')->whereMonth('date', $m)->whereYear('date', $y)->sum('amount'),
            ];
        }

        $recentTransactions = Transaction::with(['deal', 'property', 'broker'])->latest('date')->limit(10)->get();
        $pendingCommissions = Commission::with(['deal', 'broker'])->where('status', 'pending')->latest()->limit(10)->get();

        return view('admin.finance.dashboard', compact('stats', 'monthlyData', 'recentTransactions', 'pendingCommissions'));
    }

    public function transactions(Request $request)
    {
        $query = Transaction::with(['deal', 'property', 'broker', 'user'])->latest('date');

        if ($request->type) $query->where('type', $request->type);
        if ($request->category) $query->where('category', $request->category);
        if ($request->from) $query->whereDate('date', '>=', $request->from);
        if ($request->to) $query->whereDate('date', '<=', $request->to);

        $transactions = $query->paginate(20)->appends($request->query());
        $categories = ['commission', 'rent', 'maintenance', 'marketing', 'salary', 'office', 'tax', 'other'];

        return view('admin.finance.transactions', compact('transactions', 'categories'));
    }

    public function createTransaction()
    {
        $deals = Deal::all();
        $properties = Property::all();
        $brokers = Broker::all();
        $categories = ['commission' => 'Comision', 'rent' => 'Renta', 'maintenance' => 'Mantenimiento', 'marketing' => 'Marketing', 'salary' => 'Salario', 'office' => 'Oficina', 'tax' => 'Impuesto', 'other' => 'Otro'];
        return view('admin.finance.transaction-form', compact('deals', 'properties', 'brokers', 'categories'));
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:MXN,USD',
            'date' => 'required|date',
            'deal_id' => 'nullable|exists:deals,id',
            'property_id' => 'nullable|exists:properties,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'payment_method' => 'required|in:cash,transfer,check,card,other',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        $validated['user_id'] = auth()->id();

        Transaction::create($validated);
        return redirect()->route('admin.finance.transactions')->with('success', 'Transaccion registrada exitosamente');
    }

    public function editTransaction(string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $deals = Deal::all();
        $properties = Property::all();
        $brokers = Broker::all();
        $categories = ['commission' => 'Comision', 'rent' => 'Renta', 'maintenance' => 'Mantenimiento', 'marketing' => 'Marketing', 'salary' => 'Salario', 'office' => 'Oficina', 'tax' => 'Impuesto', 'other' => 'Otro'];
        return view('admin.finance.transaction-form', compact('transaction', 'deals', 'properties', 'brokers', 'categories'));
    }

    public function updateTransaction(Request $request, string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:MXN,USD',
            'date' => 'required|date',
            'deal_id' => 'nullable|exists:deals,id',
            'property_id' => 'nullable|exists:properties,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'payment_method' => 'required|in:cash,transfer,check,card,other',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $transaction->update($validated);
        return redirect()->route('admin.finance.transactions')->with('success', 'Transaccion actualizada');
    }

    public function destroyTransaction(string $id)
    {
        Transaction::findOrFail($id)->delete();
        return redirect()->route('admin.finance.transactions')->with('success', 'Transaccion eliminada');
    }

    public function commissions(Request $request)
    {
        $query = Commission::with(['deal.property', 'deal.client', 'broker'])->latest();
        if ($request->status) $query->where('status', $request->status);
        if ($request->broker_id) $query->where('broker_id', $request->broker_id);

        $commissions = $query->paginate(20)->appends($request->query());
        $brokers = Broker::all();

        return view('admin.finance.commissions', compact('commissions', 'brokers'));
    }

    public function approveCommission(string $id)
    {
        $commission = Commission::findOrFail($id);
        $commission->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Comision aprobada');
    }

    public function payCommission(string $id)
    {
        $commission = Commission::findOrFail($id);
        $commission->update(['status' => 'paid', 'paid_at' => now()]);
        return redirect()->back()->with('success', 'Comision marcada como pagada');
    }
}
