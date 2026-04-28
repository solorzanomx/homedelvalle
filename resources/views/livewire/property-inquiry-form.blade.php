<div class="w-full max-w-md mx-auto">
    @if($submitted)
        <div class="rounded-2xl bg-white p-8 shadow-premium-xl text-center" x-data x-intersect.once="$el.classList.add('animate-scale-in')">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 mx-auto mb-4">
                <x-icon name="check" class="w-6 h-6 text-emerald-600" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 mt-4">¡Solicitud enviada!</h3>
            <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                Gracias por tu interés en <strong>{{ $propertyTitle }}</strong>. Un asesor especializado te contactará en menos de 24 horas.
            </p>
            <button
                @click="$dispatch('closeModal')"
                type="button"
                class="mt-6 w-full inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"
                style="background: linear-gradient(135deg, var(--color-brand-500), var(--color-brand-700));"
            >
                Cerrar
            </button>
        </div>
    @else
        <div class="rounded-2xl bg-white p-8 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-scale-in')">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900">Solicita información</h3>
                <p class="text-sm text-gray-500 mt-2">{{ $propertyTitle }}</p>
            </div>

            @if($error)
                <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200">
                    <p class="text-sm text-red-700">{{ $error }}</p>
                </div>
            @endif

            <form wire:submit="submit" class="space-y-4">
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo</label>
                    <input
                        type="text"
                        wire:model="name"
                        id="name"
                        placeholder="Tu nombre"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all duration-200"
                        required
                    />
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                    <input
                        type="email"
                        wire:model="email"
                        id="email"
                        placeholder="tu@email.com"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all duration-200"
                        required
                    />
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono</label>
                    <input
                        type="tel"
                        wire:model="phone"
                        id="phone"
                        placeholder="+52 55 1234 5678"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all duration-200"
                        required
                    />
                    @error('phone')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Message --}}
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1.5">Mensaje (opcional)</label>
                    <textarea
                        wire:model="message"
                        id="message"
                        placeholder="Cuéntanos más sobre tu interés..."
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all duration-200"
                    ></textarea>
                    @error('message')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Privacy Checkbox --}}
                <div class="flex items-start gap-3 pt-2">
                    <input
                        type="checkbox"
                        wire:model="accept_privacy"
                        id="accept_privacy"
                        class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0 transition-colors duration-200"
                        required
                    />
                    <label for="accept_privacy" class="text-xs text-gray-500 flex-1 leading-relaxed">
                        Acepto la <a href="{{ url('/legal/aviso-de-privacidad') }}" target="_blank" class="text-brand-600 hover:text-brand-700 font-medium">política de privacidad</a> y autorizo el contacto vía WhatsApp, teléfono o email.
                    </label>
                </div>
                @error('accept_privacy')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                {{-- Honeypot --}}
                <input type="hidden" wire:model="website_url" style="display: none;" />

                {{-- reCAPTCHA --}}
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" wire:ignore></div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg, var(--color-brand-500), var(--color-brand-700));"
                >
                    <span wire:loading.remove>Solicitar información</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Enviando...
                    </span>
                </button>

                <p class="text-xs text-gray-400 text-center mt-3">
                    Responderemos en menos de 24 horas hábiles
                </p>
            </form>
        </div>
    @endif
</div>
