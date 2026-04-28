<div>
    @if($submitted)
        <div class="py-8 px-2 text-center">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 mx-auto mb-5">
                <x-icon name="check" class="w-7 h-7 text-emerald-600" />
            </div>
            <h3 class="text-xl font-bold text-gray-900">¡Recibimos tu solicitud, {{ $clientName }}!</h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed max-w-xs mx-auto">
                Un asesor especializado revisará tu propiedad y te contactará en <strong>menos de 24 horas</strong> por WhatsApp con tu valuación gratuita.
            </p>
            @if($folio)
            <p class="mt-4 text-xs font-mono text-gray-400 bg-gray-50 rounded-lg px-3 py-2 inline-block">
                Folio: {{ $folio }}
            </p>
            @endif
            <p class="mt-5 text-xs text-gray-400">
                Mientras tanto, consulta el <a href="{{ url('/mercado') }}" class="text-brand-500 hover:text-brand-600 font-medium underline">observatorio de precios de Benito Juárez</a>.
            </p>
        </div>
    @else
        <h2 class="text-xl font-bold text-gray-900">Solicita tu valuación gratuita</h2>
        <p class="text-sm text-gray-500 mt-1.5 mb-6">Responderemos en menos de 24 horas.</p>

        <form wire:submit="submit" class="space-y-4">
            {{-- Row 1: Nombre completo --}}
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo</label>
                <input
                    type="text"
                    wire:model="nombre"
                    id="nombre"
                    placeholder="Tu nombre completo"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    required
                />
                @error('nombre')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Row 2: Email & WhatsApp --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input
                        type="email"
                        wire:model="email"
                        id="email"
                        placeholder="tu@email.com"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    />
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp</label>
                    <input
                        type="tel"
                        wire:model="whatsapp"
                        id="whatsapp"
                        placeholder="+52 55 1234 5678"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    />
                    @error('whatsapp')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 3: Tipo de propiedad & Colonia --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tipo_propiedad" class="block text-sm font-medium text-gray-700 mb-1.5">Tipo de propiedad</label>
                    <select
                        wire:model="tipo_propiedad"
                        id="tipo_propiedad"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    >
                        <option value="">Selecciona un tipo</option>
                        <option value="departamento">Departamento</option>
                        <option value="casa">Casa</option>
                        <option value="terreno">Terreno</option>
                        <option value="oficina">Oficina</option>
                        <option value="comercial">Local comercial</option>
                    </select>
                    @error('tipo_propiedad')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="colonia" class="block text-sm font-medium text-gray-700 mb-1.5">Colonia o dirección</label>
                    <input
                        type="text"
                        wire:model="colonia"
                        id="colonia"
                        placeholder="Ej: Colonia del Valle"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    />
                    @error('colonia')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 4: Superficie & Recámaras --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="superficie_m2" class="block text-sm font-medium text-gray-700 mb-1.5">Superficie aproximada (m²)</label>
                    <input
                        type="number"
                        wire:model="superficie_m2"
                        id="superficie_m2"
                        placeholder="Ej: 250"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    />
                    @error('superficie_m2')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="recamaras" class="block text-sm font-medium text-gray-700 mb-1.5">Recámaras</label>
                    <select
                        wire:model="recamaras"
                        id="recamaras"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    >
                        <option value="">Selecciona</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4+">4 o más</option>
                        <option value="na">No aplica</option>
                    </select>
                    @error('recamaras')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 5: Precio esperado --}}
            <div>
                <label for="precio_esperado" class="block text-sm font-medium text-gray-700 mb-1.5">Precio que te gustaría obtener</label>
                <select
                    wire:model="precio_esperado"
                    id="precio_esperado"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    required
                >
                    <option value="">Selecciona un rango</option>
                    <option value="hasta_4m">Hasta $4M</option>
                    <option value="4m_6m">$4M – $6M</option>
                    <option value="6m_9m">$6M – $9M</option>
                    <option value="9m_14m">$9M – $14M</option>
                    <option value="14m_plus">$14M+</option>
                    <option value="no_se">No estoy seguro</option>
                </select>
                @error('precio_esperado')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Row 6: Motivo & Estado documental --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1.5">Motivo de la venta</label>
                    <select
                        wire:model="motivo"
                        id="motivo"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    >
                        <option value="">Selecciona un motivo</option>
                        <option value="mudanza">Mudanza</option>
                        <option value="sucesion">Sucesión / herencia</option>
                        <option value="liquidez">Liquidez</option>
                        <option value="patrimonio">Mejora patrimonial</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('motivo')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="estado_doc" class="block text-sm font-medium text-gray-700 mb-1.5">Estado documental</label>
                    <select
                        wire:model="estado_doc"
                        id="estado_doc"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    >
                        <option value="">Selecciona un estado</option>
                        <option value="al_corriente">Escrituras al corriente</option>
                        <option value="pendientes">Pendientes / por regularizar</option>
                        <option value="sucesion">En sucesión</option>
                        <option value="no_se">No estoy seguro</option>
                    </select>
                    @error('estado_doc')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 7: Timing --}}
            <div>
                <label for="timing" class="block text-sm font-medium text-gray-700 mb-1.5">Timing deseado de cierre</label>
                <select
                    wire:model="timing"
                    id="timing"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    required
                >
                    <option value="">Selecciona un timing</option>
                    <option value="inmediato">Inmediato (≤ 1 mes)</option>
                    <option value="1_3m">1 a 3 meses</option>
                    <option value="3_6m">3 a 6 meses</option>
                    <option value="sin_prisa">Sin prisa</option>
                </select>
                @error('timing')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Row 8: Privacy checkbox --}}
            <div class="flex items-start gap-2 pt-2">
                <input
                    type="checkbox"
                    wire:model="aviso"
                    id="aviso_privacidad"
                    class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500 transition-colors"
                    required
                />
                <label for="aviso_privacidad" class="text-xs text-gray-500 flex-1 leading-relaxed">
                    Acepto la <a href="{{ url('/legal/aviso-de-privacidad') }}" target="_blank" class="text-brand-500 hover:text-brand-600 font-medium">política de privacidad</a> y autorizo el contacto vía WhatsApp, teléfono o email.
                </label>
            </div>
            @error('aviso')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            {{-- Submit button --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full rounded-xl gradient-brand px-6 py-3.5 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2"
            >
                <span wire:loading.remove>Quiero mi valuación gratuita</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Enviando...
                </span>
            </button>

            <p class="text-xs text-gray-400 text-center mt-3">
                Respuesta en menos de 24 horas hábiles · Sin compromiso · Sin spam
            </p>
        </form>
    @endif
</div>
