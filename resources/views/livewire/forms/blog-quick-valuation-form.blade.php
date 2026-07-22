<div class="not-prose my-10" data-variant="{{ $isHerencia ? 'isr' : 'default' }}">
    <div class="rounded-2xl overflow-hidden border border-brand-100 bg-gradient-to-br from-brand-50/80 to-white">
        <div class="p-7 sm:p-9">
            @if($submitted)
                <div class="text-center py-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 mx-auto mb-4">
                        <x-icon name="check" class="w-6 h-6 text-emerald-600" />
                    </div>
                    <h3 class="text-lg font-extrabold text-gray-900">¡Listo{{ $clientName ? ', ' . $clientName : '' }}!</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed max-w-sm mx-auto">
                        @if($isHerencia)
                        Un asesor te contacta por WhatsApp en <strong>menos de 24 horas</strong> con tu estimado de ISR. Mientras tanto, sigue leyendo.
                        @else
                        Un asesor te contacta por WhatsApp en <strong>menos de 24 horas</strong> con el precio real de tu propiedad. Mientras tanto, sigue leyendo.
                        @endif
                    </p>
                </div>
            @else
                @if($isHerencia)
                <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">¿Cuánto ISR pagarías por tu herencia?</h3>
                <p class="mt-1.5 text-sm text-gray-500 leading-relaxed">Cada herencia es distinta. Déjanos 4 datos y te mandamos tu ISR estimado y el valor de tu propiedad gratis por WhatsApp en menos de 24 horas.</p>
                @else
                <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">¿Cuánto vale TU propiedad exactamente?</h3>
                <p class="mt-1.5 text-sm text-gray-500 leading-relaxed">Los promedios orientan; tu número real depende de tu inmueble. Déjanos 4 datos y te lo mandamos gratis por WhatsApp en menos de 24 horas.</p>
                @endif

                <form wire:submit="submit" class="mt-5 space-y-3">
                    {{-- Honeypot anti-spam: invisible para un humano --}}
                    <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                        <label for="bqv_website_url">Sitio web</label>
                        <input type="text" id="bqv_website_url" wire:model="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <input type="text" wire:model="nombre" placeholder="Tu nombre"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all" required />
                            @error('nombre')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <input type="tel" wire:model="whatsapp" placeholder="WhatsApp (10 dígitos)"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all" required />
                            @error('whatsapp')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <input type="email" wire:model="email" placeholder="tu@email.com"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all" required />
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <input type="text" wire:model="colonia" placeholder="Colonia (ej. Del Valle, Nápoles)"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all" required />
                            @error('colonia')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-start gap-2 pt-1">
                        <input type="checkbox" wire:model="aviso" id="bqv_aviso"
                               class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500 transition-colors" required />
                        <label for="bqv_aviso" class="text-xs text-gray-500 flex-1 leading-relaxed">
                            Acepto la <a href="{{ url('/legal/aviso-de-privacidad') }}" target="_blank" class="text-brand-500 hover:text-brand-600 font-medium">política de privacidad</a> y el contacto por WhatsApp o email.
                        </label>
                    </div>
                    @error('aviso')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full sm:w-auto rounded-xl gradient-brand px-8 py-3 text-sm font-bold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50">
                        <span wire:loading.remove>{{ $isHerencia ? 'Recibir mi estimado de ISR gratis' : 'Recibir mi precio real gratis' }}</span>
                        <span wire:loading>Enviando...</span>
                    </button>
                    <p class="text-xs text-gray-400">Sin compromiso · Sin spam · Respuesta en menos de 24 horas hábiles</p>
                </form>
            @endif
        </div>
    </div>
</div>
