<div class="w-full max-w-2xl mx-auto">
    @if ($submitted)
        <div class="text-center py-10 px-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 mx-auto mb-5">
                <svg class="w-7 h-7 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">¡Recibimos tu mensaje, {{ $clientName }}!</h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed max-w-xs mx-auto">
                Te respondemos por WhatsApp en <strong>menos de 24 horas hábiles</strong>. Sin compromiso y sin spam.
            </p>
            @if($folio)
            <p class="mt-4 text-xs font-mono text-gray-400 bg-gray-50 rounded-lg px-3 py-2 inline-block">
                Folio: {{ $folio }}
            </p>
            @endif
            <p class="mt-5 text-xs text-gray-400">
                Mientras tanto, consulta el <a href="{{ url('/mercado') }}" class="text-brand-500 font-medium underline">observatorio de precios de Benito Juárez</a>.
            </p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            {{-- ¿En qué te podemos ayudar? --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    ¿En qué te podemos ayudar? <span class="text-red-600">*</span>
                </label>
                <select wire:model.live="intento" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    <option value="">Selecciona una opción</option>
                    <option value="vender">Quiero vender mi propiedad</option>
                    <option value="comprar">Estoy buscando dónde comprar o rentar</option>
                    <option value="b2b">Soy desarrollador o inversionista</option>
                    <option value="admin">Administración de un inmueble</option>
                    <option value="legal">Asesoría legal o notarial</option>
                    <option value="otro">Otro</option>
                </select>
                @error('intento') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Nombre completo <span class="text-red-600">*</span>
                </label>
                <input type="text" wire:model.blur="nombre" placeholder="Juan Pérez López" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Email <span class="text-red-600">*</span>
                </label>
                <input type="email" wire:model.blur="email" placeholder="tu@email.com" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- WhatsApp --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    WhatsApp <span class="text-red-600">*</span>
                </label>
                <input type="tel" wire:model.blur="whatsapp" placeholder="+52 55 1234 5678" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                <p class="text-gray-500 text-xs mt-1">Formato: +52 10 dígitos</p>
                @error('whatsapp') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Colonia (condicional) --}}
            @if($intento === 'vender' || $intento === 'comprar')
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">
                        Colonia de tu interés en Benito Juárez
                    </label>
                    <select wire:model.live="colonia" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona una zona</option>
                        <option value="del_valle">Del Valle</option>
                        <option value="narvarte">Narvarte</option>
                        <option value="napoles">Nápoles</option>
                        <option value="portales">Portales</option>
                        <option value="alamos">Álamos & Xoco</option>
                        <option value="roma_sur">Roma Sur & Doctores</option>
                        <option value="ciudad_deportes">Ciudad de los Deportes</option>
                        <option value="moderna">Moderna & Letrán Valle</option>
                        <option value="otra">Otra</option>
                    </select>
                </div>
            @endif

            {{-- Mensaje --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Cuéntanos más (opcional)
                </label>
                <textarea wire:model.live="mensaje" rows="4" placeholder="Nos gustaría saber más detalles sobre tu caso..." class="w-full border border-gray-200 rounded-xl p-3 text-sm resize-none"></textarea>
                <p class="text-gray-500 text-xs mt-1">{{ strlen($mensaje) }}/1000 caracteres</p>
                @error('mensaje') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Aviso de privacidad --}}
            <div>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="aviso" class="rounded mt-1 flex-shrink-0">
                    <span class="text-xs text-gray-600">
                        He leído y acepto el <a href="/legal/aviso-de-privacidad" class="underline text-brand-500 hover:text-brand-600">Aviso de Privacidad</a>.
                    </span>
                </label>
                @error('aviso') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Submit button --}}
            <div class="pt-4">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-xl gradient-brand px-6 py-3.5 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50"
                >
                    <span wire:loading.remove>Enviar mensaje</span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enviando...
                    </span>
                </button>
                <p class="text-center text-gray-500 text-xs mt-3">
                    Respuesta en < 24 horas hábiles
                </p>
            </div>
        </form>
    @endif
</div>
