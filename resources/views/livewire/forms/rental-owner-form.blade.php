<div class="w-full">
    @if ($submitted)
        <div class="text-center py-10 px-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 mx-auto mb-5">
                <svg class="w-7 h-7 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">¡Recibimos tu solicitud, {{ $clientName }}!</h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed max-w-sm mx-auto">
                Un asesor te contactará en <strong>menos de 24 horas hábiles</strong> con un rango de renta y un plan personalizado. Sin compromiso, sin spam.
            </p>
            @if($folio)
            <p class="mt-4 text-xs font-mono text-gray-400 bg-gray-50 rounded-lg px-3 py-2 inline-block">
                Folio: {{ $folio }}
            </p>
            @endif
        </div>
    @else
        <form wire:submit="submit" class="space-y-5">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Solicita tu asesoría gratuita</h2>
                <p class="text-gray-500 mt-1 text-sm">Responderemos en menos de 24 horas con un rango de renta y un plan personalizado.</p>
            </div>

            {{-- Datos de contacto --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.live="nombre" placeholder="Tu nombre" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" wire:model.live="email" placeholder="tu@email.com" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" wire:model.live="whatsapp" placeholder="5512345678" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    @error('whatsapp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Propiedad --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Tipo de propiedad <span class="text-red-500">*</span></label>
                    <select wire:model.live="tipo_propiedad" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="departamento">Departamento</option>
                        <option value="casa">Casa</option>
                        <option value="estudio">Estudio</option>
                        <option value="loft">Loft</option>
                        <option value="oficina">Oficina</option>
                        <option value="local_comercial">Local comercial</option>
                    </select>
                    @error('tipo_propiedad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Colonia o dirección <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.live="colonia" placeholder="Del Valle Norte, Narvarte..." class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    @error('colonia') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Superficie aproximada (m²) <span class="text-xs font-normal text-gray-400">opcional</span></label>
                    <input type="number" wire:model.live="superficie_m2" min="1" placeholder="Ej: 90" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Recámaras <span class="text-xs font-normal text-gray-400">opcional</span></label>
                    <select wire:model.live="recamaras" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4_plus">4+</option>
                        <option value="na">No aplica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">¿Está amueblado? <span class="text-red-500">*</span></label>
                    <select wire:model.live="amueblado" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="completo">Sí, completo</option>
                        <option value="parcial">Sí, parcial</option>
                        <option value="no">No</option>
                    </select>
                    @error('amueblado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Renta mensual esperada <span class="text-red-500">*</span></label>
                    <select wire:model.live="renta_esperada" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="hasta_15k">Hasta $15,000</option>
                        <option value="15k_25k">$15,000 – $25,000</option>
                        <option value="25k_40k">$25,000 – $40,000</option>
                        <option value="40k_70k">$40,000 – $70,000</option>
                        <option value="70k_plus">$70,000+</option>
                        <option value="no_se">No estoy seguro</option>
                    </select>
                    @error('renta_esperada') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Plazo mínimo de contrato <span class="text-red-500">*</span></label>
                    <select wire:model.live="plazo_minimo" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="6m">6 meses</option>
                        <option value="12m">12 meses</option>
                        <option value="24m">24 meses</option>
                        <option value="sin_preferencia">Sin preferencia</option>
                    </select>
                    @error('plazo_minimo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">¿Aceptas inquilinos con mascotas? <span class="text-red-500">*</span></label>
                    <select wire:model.live="mascotas_acepta" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="si">Sí</option>
                        <option value="no">No</option>
                        <option value="depende">Depende del inquilino</option>
                    </select>
                    @error('mascotas_acepta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Estado documental <span class="text-red-500">*</span></label>
                    <select wire:model.live="estado_doc" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="al_corriente">Escrituras al corriente</option>
                        <option value="pendientes">Pendientes / por regularizar</option>
                        <option value="sucesion">En sucesión</option>
                        <option value="no_se">No estoy seguro</option>
                    </select>
                    @error('estado_doc') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Timing para colocar <span class="text-red-500">*</span></label>
                    <select wire:model.live="timing" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="inmediato">Inmediato (≤ 2 semanas)</option>
                        <option value="2_4sem">2 – 4 semanas</option>
                        <option value="1_3m">1 – 3 meses</option>
                        <option value="sin_prisa">Sin prisa</option>
                    </select>
                    @error('timing') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Preferencias de gestión --}}
            <div class="pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">¿Te interesa administración integral? <span class="text-red-500">*</span></label>
                    <select wire:model.live="administracion" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="si_quiero">Sí, quiero que la administren</option>
                        <option value="solo_inquilino">No, solo busco inquilino</option>
                        <option value="quiero_conocer">Quiero conocer la opción primero</option>
                    </select>
                    @error('administracion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">¿Buscas póliza jurídica? <span class="text-red-500">*</span></label>
                    <select wire:model.live="poliza" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="obligatoria">Sí, obligatoria</option>
                        <option value="si_sin_aval">Sí, si el inquilino no tiene aval</option>
                        <option value="prefiero_aval">Prefiero aval tradicional</option>
                        <option value="no_se">No estoy seguro</option>
                    </select>
                    @error('poliza') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Aviso --}}
            <div>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model.live="aviso" class="mt-0.5 rounded text-brand-500">
                    <span class="text-xs text-gray-500 leading-relaxed">
                        Acepto el <a href="/legal/aviso-de-privacidad" target="_blank" class="text-brand-500 underline">Aviso de Privacidad</a> y autorizo el uso de mis datos para recibir asesoría sobre renta de mi inmueble.
                    </span>
                </label>
                @error('aviso') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 px-6 transition-all duration-300 hover:-translate-y-0.5 shadow-lg hover:shadow-xl disabled:opacity-60 disabled:cursor-not-allowed disabled:translate-y-0">
                <span wire:loading.remove>Quiero mi asesoría gratuita</span>
                <span wire:loading>Enviando...</span>
                <x-icon name="arrow-right" class="w-4 h-4" wire:loading.remove />
            </button>
            <p class="text-center text-xs text-gray-400 mt-2">Respuesta en &lt; 24 horas hábiles · Sin compromiso · Sin spam</p>
        </form>
    @endif
</div>
