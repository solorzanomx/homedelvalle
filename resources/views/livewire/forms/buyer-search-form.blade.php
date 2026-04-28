<div class="w-full max-w-3xl mx-auto">
    @if ($submitted)
        <div class="text-center py-10 px-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 mx-auto mb-5">
                <svg class="w-7 h-7 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">¡Recibimos tu búsqueda, {{ $clientName }}!</h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed max-w-sm mx-auto">
                Vamos a curar las mejores opciones que coincidan con tu brief y te las enviamos en <strong>menos de 72 horas hábiles</strong>. Sin spam, sin catálogos masivos.
            </p>
            @if($folio)
            <p class="mt-4 text-xs font-mono text-gray-400 bg-gray-50 rounded-lg px-3 py-2 inline-block">
                Folio: {{ $folio }}
            </p>
            @endif
            <p class="mt-5 text-xs text-gray-400">
                ¿Urgente? Escríbenos por <a href="{{ $siteSettings?->whatsapp_number ? 'https://wa.me/'.preg_replace('/[^0-9]/','',$siteSettings->whatsapp_number) : '#' }}" target="_blank" class="text-brand-500 font-medium underline">WhatsApp</a>.
            </p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            {{-- Encabezado --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Cuéntanos qué buscas</h2>
                <p class="text-gray-500 mt-2">Toma 2 minutos. Te respondemos en menos de 72 horas con opciones curadas.</p>
            </div>

            {{-- Tipo de inmueble (multi-select) --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Tipo de inmueble <span class="text-red-600">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                    @foreach (['departamento' => 'Departamento', 'casa' => 'Casa', 'terreno' => 'Terreno', 'oficina' => 'Oficina', 'comercial' => 'Comercial'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition" wire:key="tipo_inmueble_{{ $value }}">
                            <input type="checkbox" wire:model.live="tipo_inmueble" value="{{ $value }}" class="rounded">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('tipo_inmueble') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Operación --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Operación <span class="text-red-600">*</span>
                </label>
                <div class="flex gap-4">
                    @foreach (['compra' => 'Compra', 'renta' => 'Renta'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="operacion" value="{{ $value }}" class="rounded-full">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('operacion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Zonas de interés --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Zonas de interés <span class="text-red-600">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                    @foreach (['del_valle_centro' => 'Del Valle (Centro)', 'del_valle_norte' => 'Del Valle (Norte)', 'del_valle_sur' => 'Del Valle (Sur)', 'narvarte' => 'Narvarte', 'napoles' => 'Nápoles', 'portales' => 'Portales', 'alamos' => 'Álamos & Xoco', 'roma_sur' => 'Roma Sur & Doctores', 'ciudad_deportes' => 'Ciudad de los Deportes', 'moderna' => 'Moderna & Letrán Valle', 'otra' => 'Otra'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                            <input type="checkbox" wire:model.live="zonas" value="{{ $value }}" class="rounded">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('zonas') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Recámaras --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Recámaras mínimas <span class="text-red-600">*</span>
                </label>
                <select wire:model.live="recamaras" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    <option value="">Selecciona una opción</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4+">4 o más</option>
                </select>
                @error('recamaras') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Presupuesto --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Presupuesto <span class="text-red-600">*</span>
                </label>
                <select wire:model.live="presupuesto" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    <option value="">Selecciona una opción</option>
                    <option value="hasta_4m">Hasta $4M</option>
                    <option value="4m_6m">$4M – $6M</option>
                    <option value="6m_9m">$6M – $9M</option>
                    <option value="9m_14m">$9M – $14M</option>
                    <option value="14m_plus">$14M+</option>
                </select>
                @error('presupuesto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Forma de pago --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Forma de pago <span class="text-red-600">*</span>
                </label>
                <select wire:model.live="pago" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    <option value="">Selecciona una opción</option>
                    <option value="contado">Contado</option>
                    <option value="credito">Crédito bancario</option>
                    <option value="infonavit">INFONAVIT</option>
                    <option value="fovissste">FOVISSSTE</option>
                    <option value="mixto">Mixto</option>
                </select>
                @error('pago') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Timing --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Timing <span class="text-red-600">*</span>
                </label>
                <select wire:model.live="timing" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    <option value="">Selecciona una opción</option>
                    <option value="inmediato">Inmediato (≤ 1 mes)</option>
                    <option value="1_3m">1 a 3 meses</option>
                    <option value="3_6m">3 a 6 meses</option>
                    <option value="explorando">Sólo estoy explorando</option>
                </select>
                @error('timing') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Must have --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    ¿Qué te gustaría que tu próximo inmueble tuviera sí o sí?
                </label>
                <textarea wire:model.live="must_have" rows="3" placeholder="Ej: terraza grande, piso de madera, estacionamiento techado..." class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all resize-none"></textarea>
                <p class="text-gray-500 text-xs mt-1">{{ strlen($must_have) }}/280 caracteres</p>
                @error('must_have') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <hr class="my-6">

            {{-- Datos de contacto --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tus datos</h3>

                <div class="space-y-4">
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
                </div>
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
                    <span wire:loading.remove>Recibir mi selección curada</span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enviando...
                    </span>
                </button>
                <p class="text-center text-muted text-xs mt-3">
                    Respuesta en < 72 horas hábiles · Sin compromiso · Sin spam
                </p>
            </div>
        </form>
    @endif
</div>
