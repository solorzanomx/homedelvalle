<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiAgentConfig;
use App\Models\MarketPromptTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiConfigController extends Controller
{
    public function index(Request $request): View
    {
        $agents    = AiAgentConfig::orderBy('id')->get();
        $activeTab = $request->input('tab', 'agents'); // 'agents' | 'prompts'

        MarketPromptTemplate::seedDefaults();
        $prompts = MarketPromptTemplate::orderByRaw(
            "FIELD(`key`, 'sale.search', 'sale.analysis', 'rent.search', 'rent.analysis')"
        )->get()->keyBy('key');

        return view('admin.settings.ai', compact('agents', 'prompts', 'activeTab'));
    }

    public function update(Request $request, AiAgentConfig $agent): RedirectResponse
    {
        $data = $request->validate([
            'provider'    => 'required|in:anthropic,perplexity,openai',
            'model'       => 'required|string|max:100',
            'max_tokens'  => 'required|integer|min:64|max:32000',
            'temperature' => 'required|numeric|min:0|max:2',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $agent->update($data);

        return back()->with('success', "Agente «{$agent->label}» actualizado.");
    }

    // ─── Prompts del Observatorio ─────────────────────────────────────────────

    public function updatePrompt(Request $request, string $key): RedirectResponse
    {
        $request->validate(['prompt_text' => 'required|string|min:50']);

        MarketPromptTemplate::where('key', $key)
            ->update(['prompt_text' => $request->input('prompt_text')]);

        return redirect()->route('admin.ai-config', ['tab' => 'prompts'])
            ->with('success', "Prompt «{$key}» actualizado.");
    }

    public function resetPrompt(string $key): RedirectResponse
    {
        $prompt = MarketPromptTemplate::where('key', $key)->firstOrFail();
        $prompt->resetToDefault();

        return redirect()->route('admin.ai-config', ['tab' => 'prompts'])
            ->with('success', "Prompt «{$key}» restaurado al default original.");
    }
}
