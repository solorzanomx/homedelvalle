<div class="w-full">
    @if ($submitted)
        <div class="text-center py-10 px-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 mx-auto mb-5">
                <svg class="w-7 h-7 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">¡Recibimos tu búsqueda, {{ $clientName }}!</h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed max-w-sm mx-auto">
                Vamos a curar opciones que coincidan con tu brief y te las enviamos en <strong>menos de 72 horas hábiles</strong>. Sin spam, sin catálogos masivos.
            </p>
            @if($folio)
            <p class="mt-4 text-xs font-mono text-gray-400 bg-gray-50 rounded-lg px-3 py-2 inline-block">
                Folio: {{ $folio }}
            </p>
            @endif
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Cuéntanos qué buscas</h2>
                <p class="text-gray-500 mt-1 text-sm">Toma 2 minutos. Te respondemos en menos de 72 horas con opciones curadas.</p>
            </div>

            {{-- Tipo de inmueble --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Tipo de inmueble <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach(['departamento'=>'Departamento','casa'=>'Casa','estudio'=>'Estudio','loft'=>'Loft','oficina'=>'Oficina (habitacional)','casa_jardin'=>'Casa con jardín'] as $val=>$lbl)
                    <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition {{ in_array($val, $tipo_inmueble) ? 'border-brand-400 bg-brand-50' : '' }}">
                        <input type="checkbox" wire:model.live="tipo_inmueble" value="{{ $val }}" class="rounded text-brand-500">
                        <span class="text-sm text-gray-700">{{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
                @error('tipo_inmueble') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Zonas --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Zonas de interés <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach(['del_valle_centro'=>'Del Valle Centro','del_valle_norte'=>'Del Valle Norte','del_valle_sur'=>'Del Valle Sur','narvarte'=>'Narvarte','napoles'=>'Nápoles','portales'=>'Portales','alamos'=>'Álamos & Xoco','roma_sur'=>'Roma Sur','ciudad_deportes'=>'Cd. Deportes','moderna'=>'Moderna & Letrán','otra'=>'Otra colonia BJ'] as $val=>$lbl)
                    <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition {{ in_array($val, $zonas) ? 'border-brand-400 bg-brand-50' : '' }}">
                        <input type="checkbox" wire:model.live="zonas" value="{{ $val }}" class="rounded text-brand-500">
                        <span class="text-sm text-gray-700">{{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
                @error('zonas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Recámaras --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Recámaras <span class="text-red-500">*</span></label>
                    <select wire:model.live="recamaras" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="1">1 recámara</option>
                        <option value="2">2 recámaras</option>
                        <option value="3">3 recámaras</option>
                        <option value="4_plus">4 o más</option>
                        <option value="sin_preferencia">Sin preferencia</option>
                    </select>
                    @error('recamaras') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Renta mensual --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Renta mensual deseada <span class="text-red-500">*</span></label>
                    <select wire:model.live="renta_mensual" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="hasta_15k">Hasta $15,000</option>
                        <option value="15k_25k">$15,000 – $25,000</option>
                        <option value="25k_40k">$25,000 – $40,000</option>
                        <option value="40k_70k">$40,000 – $70,000</option>
                        <option value="70k_plus">$70,000+</option>
                    </select>
                    @error('renta_mensual') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Plazo --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Plazo del contrato <span class="text-red-500">*</span></label>
                    <select wire:model.live="plazo_contrato" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="6m">6 meses</option>
                        <option value="12m">12 meses</option>
                        <option value="24m_plus">24 meses o más</option>
                        <option value="flexible">Flexible</option>
                    </select>
                    @error('plazo_contrato') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Mascotas --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">¿Vives con mascotas? <span class="text-red-500">*</span></label>
                    <select wire:model.live="mascotas" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="perro">Sí, perro</option>
                        <option value="gato">Sí, gato</option>
                        <option value="otra">Sí, otra mascota</option>
                        <option value="no">No</option>
                    </select>
                    @error('mascotas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Garantía --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Forma de garantizar la renta <span class="text-red-500">*</span></label>
                    <select wire:model.live="garantia" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="aval_propiedad">Aval con propiedad</option>
                        <option value="poliza_juridica">Póliza jurídica</option>
                        <option value="deposito_ampliado">Depósito ampliado</option>
                        <option value="no_decido">Aún no decido</option>
                    </select>
                    @error('garantia') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Timing --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">¿Cuándo planeas mudarte? <span class="text-red-500">*</span></label>
                    <select wire:model.live="timing" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        <option value="">Selecciona</option>
                        <option value="inmediato">Inmediato (≤ 2 semanas)</option>
                        <option value="2_4sem">2 – 4 semanas</option>
                        <option value="1_3m">1 – 3 meses</option>
                        <option value="explorando">Solo explorando</option>
                    </select>
                    @error('timing') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Must have --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">¿Qué no puede faltar en tu próximo inmueble? <span class="text-xs font-normal text-gray-400">(opcional)</span></label>
                <textarea wire:model.live="must_have" rows="2" maxlength="280" placeholder="Ej: estacionamiento cubierto, luz natural, planta baja, pet-friendly..." class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all resize-none"></textarea>
            </div>

            {{-- Datos de contacto --}}
            <div class="pt-4 border-t border-gray-100">
                <p class="text-sm font-semibold text-gray-900 mb-4">Datos de contacto</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live="nombre" placeholder="Tu nombre" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" wire:model.live="email" placeholder="tu@email.com" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp <span class="text-red-500">*</span></label>
                        <input type="tel" wire:model.live="whatsapp" placeholder="5512345678" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        @error('whatsapp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Aviso --}}
            <div>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model.live="aviso" class="mt-0.5 rounded text-brand-500">
                    <span class="text-xs text-gray-500 leading-relaxed">
                        Acepto el <a href="/legal/aviso-de-privacidad" target="_blank" class="text-brand-500 underline">Aviso de Privacidad</a> y autorizo el uso de mis datos para recibir información sobre inmuebles en renta.
                    </span>
                </label>
                @error('aviso') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 px-6 transition-all duration-300 hover:-translate-y-0.5 shadow-lg hover:shadow-xl disabled:opacity-60 disabled:cursor-not-allowed disabled:translate-y-0">
                <span wire:loading.remove>Recibir mi selección curada</span>
                <span wire:loading>Enviando...</span>
                <x-icon name="arrow-right" class="w-4 h-4" wire:loading.remove />
            </button>
            <p class="text-center text-xs text-gray-400 mt-2">Respuesta en &lt; 72 horas hábiles · Sin compromiso · Sin spam</p>
        </form>
    @endif
</div>
