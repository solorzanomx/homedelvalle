<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketPromptTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketPromptsController extends Controller
{
    public function index(): View
    {
        // Sembrar defaults si no existen
        MarketPromptTemplate::seedDefaults();

        $prompts = MarketPromptTemplate::orderByRaw("FIELD(key, 'sale.search','sale.analysis','rent.search','rent.analysis')")
            ->get()
            ->keyBy('key');

        return view('admin.market.prompts', compact('prompts'));
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'prompt_text' => 'required|string|min:50',
        ]);

        MarketPromptTemplate::seedDefaults(); // asegura que exista

        MarketPromptTemplate::where('key', $key)
            ->update(['prompt_text' => $validated['prompt_text']]);

        return back()->with('success', "Prompt '{$key}' actualizado correctamente.");
    }

    public function reset(string $key): RedirectResponse
    {
        $prompt = MarketPromptTemplate::where('key', $key)->firstOrFail();
        $prompt->resetToDefault();

        return back()->with('success', "Prompt '{$key}' restaurado al default original.");
    }
}
