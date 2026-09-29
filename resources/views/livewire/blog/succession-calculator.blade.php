<div class="not-prose my-10 rounded-2xl border border-gray-200 bg-gray-50/60 p-6 sm:p-8" wire:key="calc-{{ $postId }}">
    <h3 class="text-xl font-extrabold text-gray-900">Calculadora de costo de sucesión</h3>
    <p class="mt-1 text-sm text-gray-500 leading-relaxed">Un estimado en segundos, con tus datos. Es una referencia, no una cotización.</p>

    @if(! $calculated)
        <form wire:submit.prevent="calculate" class="mt-5 grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Valor aproximado del inmueble</label>
                <input type="number" wire:model="valorInmueble" placeholder="Ej. 3500000" min="100000"
                       class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 outline-none">
                @error('valorInmueble')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Número de herederos</label>
                <input type="number" wire:model="numHerederos" min="1" max="20"
                       class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">¿Hay testamento?</label>
                <select wire:model="conTestamento" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">
                    <option value="">Selecciona…</option>
                    <option value="si">Sí, hay testamento</option>
                    <option value="no">No hay testamento</option>
                </select>
                @error('conTestamento')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">¿El inmueble ya está escriturado a nombre del difunto?</label>
                <select wire:model="tieneEscrituras" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">
                    <option value="si">Sí</option>
                    <option value="no">No / no estoy seguro</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold transition-all duration-300 hover:-translate-y-0.5 shadow-brand disabled:opacity-60"
                        style="background: var(--color-primary, #3B82C4);">
                    <span wire:loading.remove wire:target="calculate">Calcular</span>
                    <span wire:loading wire:target="calculate">Calculando…</span>
                </button>
            </div>
        </form>
    @else
        <div class="mt-5">
            @if(! $result['validated'])
            <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800 mb-4">
                ⚠ Estimación de referencia, todavía no confirmada con un notario para tu caso exacto.
            </div>
            @endif

            <div class="divide-y divide-gray-200 bg-white rounded-xl border border-gray-200 overflow-hidden">
                @foreach($result['items'] as $item)
                <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                    <span class="text-gray-600">{{ $item['label'] }}</span>
                    <span class="font-semibold text-gray-900">${{ number_format($item['min']) }} – ${{ number_format($item['max']) }}</span>
                </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between rounded-xl bg-brand-50 border border-brand-100 px-4 py-3">
                <span class="text-sm font-bold text-gray-900">Total estimado</span>
                <span class="text-lg font-extrabold text-brand-700">${{ number_format($result['total_min']) }} – ${{ number_format($result['total_max']) }}</span>
            </div>
            <p class="mt-2 text-xs text-gray-500">Tiempo estimado del trámite: {{ $result['tiempo_min'] }} a {{ $result['tiempo_max'] }} meses. Esto es una estimación, no una cotización — los montos exactos dependen de tu caso.</p>

            <button type="button" wire:click="$set('calculated', false)" class="mt-3 text-xs font-bold text-brand-600 underline">Volver a calcular</button>

            <div class="mt-6 border-t border-gray-200 pt-5">
                @if($submitted)
                    <div class="flex items-start gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-500 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-extrabold text-gray-900">¡Listo! Ya lo recibimos.</h4>
                            <p class="mt-1 text-sm text-gray-500 leading-relaxed">Un asesor va a escribirte con el desglose exacto para tu caso.</p>
                            @if($whatsappContinueUrl)
                            <a href="{{ $whatsappContinueUrl }}" target="_blank" rel="noopener" data-track-location="cta_calculadora_whatsapp_continue"
                               class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold transition-all duration-300 hover:-translate-y-0.5" style="background:#25D366;">
                                <x-icon name="brands/whatsapp" class="w-4 h-4" /> Seguir por WhatsApp
                            </a>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-sm font-bold text-gray-900">¿Quieres el desglose exacto para tu caso y cuánto pagarías de ISR si vendes después?</p>
                    <p class="mt-1 text-xs text-gray-500">Déjanos tu WhatsApp.</p>
                    <form wire:submit.prevent="submitLead" class="mt-3 flex flex-col sm:flex-row gap-3" data-track-location="cta_calculadora" data-cta-variant="calculadora_sucesion">
                        <input type="text" wire:model="website_url" tabindex="-1" autocomplete="off"
                               style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;" aria-hidden="true">
                        <input type="text" wire:model="name" placeholder="Tu nombre (opcional)"
                               class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">
                        <input type="tel" wire:model="whatsapp" placeholder="Tu WhatsApp (10 dígitos)" required
                               class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold shrink-0 disabled:opacity-60" style="background: var(--color-primary, #3B82C4);">
                            <span wire:loading.remove wire:target="submitLead">Quiero el desglose exacto</span>
                            <span wire:loading wire:target="submitLead">Enviando…</span>
                        </button>
                    </form>
                    @error('whatsapp')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    <label class="mt-3 flex items-start gap-2 text-xs text-gray-400 leading-relaxed">
                        <input type="checkbox" wire:model="aviso" class="mt-0.5">
                        Acepto el <a href="{{ url('/legal/aviso-de-privacidad') }}" target="_blank" class="underline">aviso de privacidad</a>.
                    </label>
                    @error('aviso')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @endif
            </div>
        </div>
    @endif
</div>
