<div class="not-prose my-10" wire:key="cta-{{ $location }}-{{ $postId }}">
    <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-brand-50 to-white border border-brand-100"
         data-track-location="cta_{{ $location }}" data-cta-variant="{{ $cluster ?: 'default' }}">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-brand-500 rounded-l-2xl"></div>
        <div class="p-6 sm:p-8">
            @if($submitted)
                <div class="flex items-start gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-500 shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">¡Listo! Ya lo recibimos.</h3>
                        <p class="mt-1 text-sm text-gray-500 leading-relaxed">Un asesor va a escribirte. Si quieres, adelanta la conversación por WhatsApp ahora mismo:</p>
                        @if($whatsappContinueUrl)
                        <a href="{{ $whatsappContinueUrl }}" target="_blank" rel="noopener"
                           data-track-location="cta_{{ $location }}_whatsapp_continue"
                           class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold transition-all duration-300 hover:-translate-y-0.5"
                           style="background:#25D366;">
                            <x-icon name="brands/whatsapp" class="w-4 h-4" />
                            Seguir por WhatsApp
                        </a>
                        @endif
                    </div>
                </div>
            @else
                <h3 class="text-xl font-extrabold text-gray-900">{{ $headline }}</h3>
                <p class="mt-1 text-sm text-gray-500 leading-relaxed max-w-2xl">{{ $body }}</p>

                <form wire:submit.prevent="submit" class="mt-5 flex flex-col sm:flex-row gap-3">
                    {{-- Honeypot: oculto por CSS, nunca por type=hidden (los bots sí llenan hidden). --}}
                    <input type="text" wire:model="website_url" tabindex="-1" autocomplete="off"
                           style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;" aria-hidden="true">

                    <input type="text" wire:model="name" placeholder="Tu nombre (opcional)"
                           class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 outline-none">
                    <input type="tel" wire:model="whatsapp" placeholder="Tu WhatsApp (10 dígitos)" required
                           class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 outline-none">

                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold transition-all duration-300 hover:-translate-y-0.5 shrink-0 shadow-brand disabled:opacity-60"
                            style="background: var(--color-primary, #3B82C4);">
                        <span wire:loading.remove wire:target="submit">{{ $buttonLabel }}</span>
                        <span wire:loading wire:target="submit">Enviando…</span>
                    </button>
                </form>
                @error('whatsapp')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

                {{-- Correo: opcional a propósito (no se pide arriba, junto al WhatsApp, para no sumar
                     fricción al campo obligatorio) — quien prefiera que le escribamos por correo
                     además de WhatsApp lo abre aquí. --}}
                <details class="mt-3">
                    <summary class="text-xs font-bold text-brand-600 cursor-pointer">¿Prefieres que también te escribamos por correo? (opcional)</summary>
                    <input type="email" wire:model="email" placeholder="tu@correo.com"
                           class="mt-2 w-full sm:w-64 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 outline-none">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </details>

                <label class="mt-3 flex items-start gap-2 text-xs text-gray-400 leading-relaxed">
                    <input type="checkbox" wire:model="aviso" class="mt-0.5">
                    Acepto el <a href="{{ url('/legal/aviso-de-privacidad') }}" target="_blank" class="underline">aviso de privacidad</a>.
                </label>
                @error('aviso')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            @endif
        </div>
    </div>
</div>
