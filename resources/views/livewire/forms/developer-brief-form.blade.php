<div class="w-full max-w-3xl mx-auto">
    @if ($submitted)
        <div class="text-center py-10 px-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 mx-auto mb-5">
                <svg class="w-7 h-7 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Recibimos tu brief, {{ $clientName }}.</h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed max-w-sm mx-auto">
                Un miembro de nuestra dirección general te contactará en <strong>menos de 48 horas hábiles</strong> para agendar la llamada de calificación. Información tratada bajo confidencialidad.
            </p>
            @if($folio)
            <p class="mt-4 text-xs font-mono text-gray-400 bg-gray-50 rounded-lg px-3 py-2 inline-block">
                Folio: {{ $folio }}
            </p>
            @endif
            <p class="mt-5 text-xs text-gray-400">
                Si prefieres, puedes enviarnos el brief directamente a <a href="mailto:leads@homedelvalle.mx" class="text-brand-500 font-medium underline">leads@homedelvalle.mx</a>.
            </p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            {{-- Encabezado --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Solicita tu brief calificador</h2>
                <p class="text-gray-500 mt-2">Una vez recibido, agendamos llamada de calificación en menos de 48 horas.</p>
            </div>

            {{-- Tipo de operación --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Tipo de operación <span class="text-red-600">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach (['compra_predio' => 'Compra de predio', 'compra_terminado' => 'Compra de producto terminado', 'coinversion' => 'Coinversión / JV', 'asesoria' => 'Asesoría puntual'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                            <input type="checkbox" wire:model.live="tipo_operacion" value="{{ $value }}" class="rounded">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('tipo_operacion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Uso objetivo --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Uso objetivo <span class="text-red-600">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                    @foreach (['vertical' => 'Habitacional vertical', 'horizontal' => 'Habitacional horizontal', 'mixto' => 'Mixto', 'comercial' => 'Comercial', 'oficinas' => 'Oficinas', 'industrial' => 'Industrial ligero'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                            <input type="checkbox" wire:model.live="uso" value="{{ $value }}" class="rounded">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('uso') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- M² de terreno --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Rango de m² de terreno buscado <span class="text-red-600">*</span>
                </label>
                <select wire:model.live="m2_terreno" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    <option value="">Selecciona una opción</option>
                    <option value="menos_200">< 200</option>
                    <option value="200_400">200–400</option>
                    <option value="400_800">400–800</option>
                    <option value="800_1500">800–1500</option>
                    <option value="1500_plus">1500+</option>
                </select>
                @error('m2_terreno') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Zonas de interés --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Zonas de Benito Juárez de interés <span class="text-red-600">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                    @foreach (['del_valle' => 'Del Valle', 'narvarte' => 'Narvarte', 'napoles' => 'Nápoles', 'portales' => 'Portales', 'alamos' => 'Álamos & Xoco', 'roma_sur' => 'Roma Sur', 'ciudad_deportes' => 'Ciudad de los Deportes', 'moderna' => 'Moderna', 'cualquier' => 'Cualquier zona BJ'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                            <input type="checkbox" wire:model.live="zonas" value="{{ $value }}" class="rounded">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('zonas') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Presupuesto --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Presupuesto disponible para captación (MXN) <span class="text-red-600">*</span>
                </label>
                <select wire:model.live="presupuesto" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    <option value="">Selecciona una opción</option>
                    <option value="menos_20m">< $20M</option>
                    <option value="20m_50m">$20M–$50M</option>
                    <option value="50m_120m">$50M–$120M</option>
                    <option value="120m_300m">$120M–$300M</option>
                    <option value="300m_plus">$300M+</option>
                </select>
                @error('presupuesto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Horizonte de inversión --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    Horizonte de inversión <span class="text-red-600">*</span>
                </label>
                <select wire:model.live="horizonte" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    <option value="">Selecciona una opción</option>
                    <option value="6m">≤ 6 meses</option>
                    <option value="6_12m">6 a 12 meses</option>
                    <option value="12_24m">12 a 24 meses</option>
                    <option value="24m_plus">24+ meses</option>
                </select>
                @error('horizonte') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Brief PDF --}}
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    ¿Hay un brief técnico previo que podamos revisar?
                </label>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6">
                    <input type="file" wire:model="brief_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full">
                    <p class="text-gray-500 text-xs mt-2">PDF, JPG o PNG · Máximo 10 MB</p>
                    @if ($brief_file)
                        <p class="text-emerald-600 text-xs mt-2">✓ {{ $brief_file->getClientOriginalName() }}</p>
                    @endif
                </div>
                @error('brief_file') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <hr class="my-6">

            {{-- Datos de la empresa y contacto --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Tu información</h3>

                {{-- Empresa --}}
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">
                        Empresa o entidad <span class="text-red-600">*</span>
                    </label>
                    <input type="text" wire:model.blur="empresa" placeholder="Nombre de tu empresa" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    @error('empresa') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Nombre y rol --}}
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">
                        Nombre y rol <span class="text-red-600">*</span>
                    </label>
                    <input type="text" wire:model.blur="nombre_rol" placeholder="Ej: Juan Pérez, Director de Inversiones" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    @error('nombre_rol') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email corporativo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">
                        Email corporativo <span class="text-red-600">*</span>
                    </label>
                    <input type="email" wire:model.blur="email" placeholder="nombre@empresa.com" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">
                        Teléfono / WhatsApp <span class="text-red-600">*</span>
                    </label>
                    <input type="tel" wire:model.blur="telefono" placeholder="+52 55 1234 5678" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                    @error('telefono') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- NDA --}}
            <div>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="nda" class="rounded mt-1 flex-shrink-0">
                    <span class="text-xs text-gray-600">
                        Solicito que la conversación se maneje bajo acuerdo de confidencialidad (NDA).
                    </span>
                </label>
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
                    <span wire:loading.remove>Enviar brief calificador</span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enviando...
                    </span>
                </button>
                <p class="text-center text-gray-500 text-xs mt-3">
                    Respuesta en < 48 horas hábiles · Información tratada bajo confidencialidad
                </p>
            </div>
        </form>
    @endif
</div>
