<div>
    @if($submitted)
        <div class="py-8 px-2 text-center">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 mx-auto mb-5">
                <x-icon name="check" class="w-7 h-7 text-emerald-600" />
            </div>
            <h3 class="text-xl font-bold text-gray-900">¡Recibimos tu solicitud, {{ $clientName }}!</h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed max-w-xs mx-auto">
                Vamos a evaluar tu propiedad contra la demanda activa de nuestra cartera de constructoras y te contactamos en <strong>menos de 24 horas</strong> por WhatsApp.
            </p>
            @if($folio)
            <p class="mt-4 text-xs font-mono text-gray-400 bg-gray-50 rounded-lg px-3 py-2 inline-block">
                Folio: {{ $folio }}
            </p>
            @endif
            <p class="mt-5 text-xs text-gray-400">
                Mientras tanto, consulta los <a href="{{ route('precios.index') }}" class="text-brand-500 hover:text-brand-600 font-medium underline">precios por colonia en Benito Juárez</a>.
            </p>
        </div>
    @else
        <h2 class="text-xl font-bold text-gray-900">Descubre lo que tu propiedad vale para una desarrolladora</h2>
        <p class="text-sm text-gray-500 mt-1.5 mb-6">Sin compromiso y confidencial. Respondemos en menos de 24 horas.</p>

        <form wire:submit="submit" class="space-y-4">
            {{-- Honeypot anti-spam: invisible para un humano, un bot que recorre el DOM sí lo llena --}}
            <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                <label for="lsf_website_url">Sitio web</label>
                <input type="text" id="lsf_website_url" wire:model="website_url" tabindex="-1" autocomplete="off">
            </div>

            {{-- Row 1: Nombre completo --}}
            <div>
                <label for="lsf_nombre" class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo</label>
                <input
                    type="text"
                    wire:model="nombre"
                    id="lsf_nombre"
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
                    <label for="lsf_email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input
                        type="email"
                        wire:model="email"
                        id="lsf_email"
                        placeholder="tu@email.com"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    />
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="lsf_whatsapp" class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp</label>
                    <input
                        type="tel"
                        wire:model="whatsapp"
                        id="lsf_whatsapp"
                        placeholder="+52 55 1234 5678"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    />
                    @error('whatsapp')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 3: Colonia & Tipo actual --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="lsf_colonia" class="block text-sm font-medium text-gray-700 mb-1.5">Colonia o dirección</label>
                    <input
                        type="text"
                        wire:model="colonia"
                        id="lsf_colonia"
                        placeholder="Ej: Del Valle, Narvarte, Nápoles..."
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    />
                    @error('colonia')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="lsf_tipo_actual" class="block text-sm font-medium text-gray-700 mb-1.5">¿Qué hay hoy en el predio?</label>
                    <select
                        wire:model="tipo_actual"
                        id="lsf_tipo_actual"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    >
                        <option value="">Selecciona</option>
                        <option value="casa_sola">Casa sola</option>
                        <option value="casa_con_local">Casa con local comercial</option>
                        <option value="edificio">Edificio</option>
                        <option value="terreno">Terreno sin construcción</option>
                    </select>
                    @error('tipo_actual')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 4: Superficie & Situación --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="lsf_superficie" class="block text-sm font-medium text-gray-700 mb-1.5">Superficie de terreno aprox. (m²)</label>
                    <input
                        type="number"
                        wire:model="superficie_terreno_m2"
                        id="lsf_superficie"
                        placeholder="Ej: 300"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    />
                    @error('superficie_terreno_m2')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="lsf_situacion" class="block text-sm font-medium text-gray-700 mb-1.5">Situación actual</label>
                    <select
                        wire:model="situacion"
                        id="lsf_situacion"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                        required
                    >
                        <option value="">Selecciona</option>
                        <option value="la_habito">La habito</option>
                        <option value="rentada">Está rentada</option>
                        <option value="desocupada">Está desocupada</option>
                        <option value="sucesion">En sucesión / herencia</option>
                    </select>
                    @error('situacion')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 5: Timing --}}
            <div>
                <label for="lsf_timing" class="block text-sm font-medium text-gray-700 mb-1.5">¿En qué momento estás?</label>
                <select
                    wire:model="timing"
                    id="lsf_timing"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    required
                >
                    <option value="">Selecciona</option>
                    <option value="inmediato">Quiero vender ya (≤ 1 mes)</option>
                    <option value="1_3m">En 1 a 3 meses</option>
                    <option value="3_6m">En 3 a 6 meses</option>
                    <option value="solo_explorar">Solo quiero saber cuánto vale</option>
                </select>
                @error('timing')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Row 6: Privacy checkbox --}}
            <div class="flex items-start gap-2 pt-2">
                <input
                    type="checkbox"
                    wire:model="aviso"
                    id="lsf_aviso_privacidad"
                    class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500 transition-colors"
                    required
                />
                <label for="lsf_aviso_privacidad" class="text-xs text-gray-500 flex-1 leading-relaxed">
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
                <span wire:loading.remove>Evaluar mi propiedad sin compromiso</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Enviando...
                </span>
            </button>

            <p class="text-xs text-gray-400 text-center mt-3">
                Confidencial · Sin compromiso · Respuesta en menos de 24 horas hábiles
            </p>
        </form>
    @endif
</div>
