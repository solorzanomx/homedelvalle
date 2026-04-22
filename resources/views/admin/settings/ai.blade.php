@php
    $providerModels = App\Models\AiAgentConfig::$providerModels;
    $providerColors = [
        'anthropic'  => 'bg-orange-50 text-orange-700 border-orange-200',
        'perplexity' => 'bg-blue-50 text-blue-700 border-blue-200',
        'openai'     => 'bg-green-50 text-green-700 border-green-200',
    ];
    $providerIcons = [
        'anthropic'  => '🧠',
        'perplexity' => '🔍',
        'openai'     => '⚡',
    ];
@endphp

<x-layouts.app>
    <x-slot:title>Configuración de Agentes IA</x-slot:title>

    <div class="max-w-5xl mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Agentes de Inteligencia Artificial</h1>
            <p class="mt-1 text-sm text-gray-500">
                Configura qué modelo usa cada función. Los cambios aplican inmediatamente (sin redeploy).
                El caché se limpia automáticamente al guardar.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Cost reference card --}}
        <div class="mb-8 rounded-xl border border-blue-100 bg-blue-50 p-5">
            <h2 class="text-sm font-semibold text-blue-900 mb-3">💡 Referencia de costos aproximados (por millón de tokens)</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div class="bg-white rounded-lg p-3 border border-blue-100">
                    <div class="font-semibold text-gray-800">Claude Opus 4.6</div>
                    <div class="text-orange-600 font-bold mt-1">$15 / $75</div>
                    <div class="text-gray-500">entrada / salida</div>
                </div>
                <div class="bg-white rounded-lg p-3 border border-blue-100">
                    <div class="font-semibold text-gray-800">Claude Sonnet 4.6</div>
                    <div class="text-orange-500 font-bold mt-1">$3 / $15</div>
                    <div class="text-gray-500">entrada / salida</div>
                </div>
                <div class="bg-white rounded-lg p-3 border border-blue-100">
                    <div class="font-semibold text-gray-800">Claude Haiku 4.5</div>
                    <div class="text-green-600 font-bold mt-1">$0.80 / $4</div>
                    <div class="text-gray-500">entrada / salida · más económico</div>
                </div>
                <div class="bg-white rounded-lg p-3 border border-blue-100">
                    <div class="font-semibold text-gray-800">Perplexity Sonar</div>
                    <div class="text-blue-600 font-bold mt-1">$1 / $1</div>
                    <div class="text-gray-500">+ $5 / 1,000 búsquedas</div>
                </div>
            </div>
        </div>

        {{-- Agents grid --}}
        <div class="space-y-4">
            @foreach($agents as $agent)
            <div class="rounded-xl border border-gray-200 bg-white overflow-hidden"
                 x-data="{ editing: false }">

                {{-- Agent header --}}
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">{{ $providerIcons[$agent->provider] ?? '🤖' }}</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900">{{ $agent->label }}</span>
                                @if(!$agent->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Inactivo</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $agent->description }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Current config badges --}}
                        <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border {{ $providerColors[$agent->provider] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                            {{ strtoupper($agent->provider) }}
                        </span>
                        <span class="hidden md:inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
                            {{ $agent->model }}
                        </span>
                        <span class="hidden md:inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-500 border border-gray-200">
                            {{ number_format($agent->max_tokens) }} tk
                        </span>
                        <button @click="editing = !editing"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                            <span x-text="editing ? 'Cancelar' : 'Editar'"></span>
                        </button>
                    </div>
                </div>

                {{-- Edit form --}}
                <div x-show="editing" x-transition class="border-t border-gray-100 bg-gray-50 px-5 py-4">
                    <form method="POST" action="{{ route('admin.ai-config.update', $agent) }}">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            {{-- Provider --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Proveedor</label>
                                <select name="provider"
                                        x-model="provider"
                                        x-data="{ provider: '{{ $agent->provider }}' }"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="anthropic" {{ $agent->provider === 'anthropic' ? 'selected' : '' }}>🧠 Anthropic (Claude)</option>
                                    <option value="perplexity" {{ $agent->provider === 'perplexity' ? 'selected' : '' }}>🔍 Perplexity</option>
                                    <option value="openai" {{ $agent->provider === 'openai' ? 'selected' : '' }}>⚡ OpenAI</option>
                                </select>
                            </div>

                            {{-- Model --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Modelo</label>
                                <input type="text" name="model" value="{{ $agent->model }}"
                                       list="models-{{ $agent->id }}"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                       placeholder="nombre-del-modelo">
                                <datalist id="models-{{ $agent->id }}">
                                    @foreach($providerModels as $prov => $models)
                                        @foreach($models as $modelId => $modelLabel)
                                            <option value="{{ $modelId }}">{{ $modelLabel }}</option>
                                        @endforeach
                                    @endforeach
                                </datalist>
                                <p class="mt-1 text-xs text-gray-400">Puedes escribir cualquier modelo válido</p>
                            </div>

                            {{-- Max tokens --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Máx. tokens</label>
                                <input type="number" name="max_tokens" value="{{ $agent->max_tokens }}"
                                       min="64" max="32000" step="64"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <p class="mt-1 text-xs text-gray-400">Más tokens = más costo</p>
                            </div>

                            {{-- Temperature --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                    Temperatura
                                    <span class="text-gray-400 font-normal normal-case">(0 = exacto, 1 = creativo)</span>
                                </label>
                                <input type="number" name="temperature" value="{{ $agent->temperature }}"
                                       min="0" max="2" step="0.05"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $agent->is_active ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Agente activo</span>
                            </label>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>

            </div>
            @endforeach
        </div>

        {{-- Key legend --}}
        <div class="mt-8 rounded-xl border border-gray-100 bg-gray-50 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Claves de agentes en el código</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($agents as $agent)
                <div class="flex items-center gap-2 text-xs">
                    <code class="bg-white border border-gray-200 rounded px-2 py-0.5 font-mono text-gray-800">{{ $agent->key }}</code>
                    <span class="text-gray-500">→ {{ $agent->label }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</x-layouts.app>
