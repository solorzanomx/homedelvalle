<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiAgentConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiConfigController extends Controller
{
    public function index(): View
    {
        $agents = AiAgentConfig::orderBy('id')->get();
        return view('admin.settings.ai', compact('agents'));
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
}
