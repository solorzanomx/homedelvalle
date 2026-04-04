@props([
    'propertyId' => null,
    'compact' => false,
])

<form method="POST" action="{{ route('contacto.store') }}" class="space-y-5" x-data="{ submitting: false, focused: '' }" @submit.prevent="submitting = true; $el.submit();">
    @csrf

    @if($propertyId)
    <input type="hidden" name="property_id" value="{{ $propertyId }}">
    @endif

    {{-- UTM hidden fields --}}
    <input type="hidden" name="utm_source" value="{{ request('utm_source') }}">
    <input type="hidden" name="utm_medium" value="{{ request('utm_medium') }}">
    <input type="hidden" name="utm_campaign" value="{{ request('utm_campaign') }}">

    {{-- Honeypot --}}
    <div class="absolute opacity-0 top-0 left-0 h-0 w-0 -z-10 overflow-hidden" aria-hidden="true" tabindex="-1">
        <label for="website_url">Deja esto vacío</label>
        <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
    </div>

    <div class="{{ $compact ? '' : 'grid sm:grid-cols-2 gap-5' }}">
        <div class="{{ $compact ? 'mb-5' : '' }}">
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nombre <span class="text-brand-500">*</span></label>
            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                   @focus="focused = 'name'" @blur="focused = ''"
                   :class="focused === 'name' ? 'border-brand-400 ring-2 ring-brand-500/10' : 'border-gray-200'"
                   class="w-full rounded-xl border bg-gray-50/50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-300 hover:border-gray-300 focus:bg-white focus:outline-none"
                   placeholder="Tu nombre completo">
            @error('name') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
        </div>
        <div class="{{ $compact ? 'mb-5' : '' }}">
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email <span class="text-brand-500">*</span></label>
            <input type="email" name="email" id="email" required value="{{ old('email') }}"
                   @focus="focused = 'email'" @blur="focused = ''"
                   :class="focused === 'email' ? 'border-brand-400 ring-2 ring-brand-500/10' : 'border-gray-200'"
                   class="w-full rounded-xl border bg-gray-50/50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-300 hover:border-gray-300 focus:bg-white focus:outline-none"
                   placeholder="tu@email.com">
            @error('email') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
               @focus="focused = 'phone'" @blur="focused = ''"
               :class="focused === 'phone' ? 'border-brand-400 ring-2 ring-brand-500/10' : 'border-gray-200'"
               class="w-full rounded-xl border bg-gray-50/50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-300 hover:border-gray-300 focus:bg-white focus:outline-none"
               placeholder="+52 55 1234 5678">
        @error('phone') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Mensaje <span class="text-brand-500">*</span></label>
        <textarea name="message" id="message" rows="{{ $compact ? 3 : 4 }}" required
                  @focus="focused = 'message'" @blur="focused = ''"
                  :class="focused === 'message' ? 'border-brand-400 ring-2 ring-brand-500/10' : 'border-gray-200'"
                  class="w-full rounded-xl border bg-gray-50/50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-300 hover:border-gray-300 focus:bg-white focus:outline-none resize-none"
                  placeholder="{{ $propertyId ? 'Me interesa esta propiedad. ¿Podrían darme más información?' : 'Escribe tu mensaje...' }}">{{ old('message') }}</textarea>
        @error('message') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
    </div>

    {{-- Privacy acceptance --}}
    @php
        try { $privacyDoc = \App\Models\LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first(); } catch (\Exception $e) { $privacyDoc = null; }
    @endphp
    @if($privacyDoc)
    <div class="flex items-start gap-2 mt-1">
        <input type="checkbox" name="accept_privacy" id="accept_privacy" required class="mt-1 rounded border-gray-300">
        <label for="accept_privacy" class="text-xs text-gray-500 leading-snug">
            He leído y acepto el <a href="{{ route('legal.public', $privacyDoc->slug) }}" target="_blank" class="text-brand-500 underline hover:text-brand-600">Aviso de Privacidad</a>
        </label>
    </div>
    @endif

    <button type="submit" :disabled="submitting"
            class="w-full rounded-xl gradient-brand px-5 py-3.5 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:translate-y-0 disabled:shadow-none">
        <span x-show="!submitting" class="flex items-center justify-center gap-2">
            Enviar mensaje
            <x-icon name="arrow-right" class="w-4 h-4" />
        </span>
        <span x-show="submitting" x-cloak class="flex items-center justify-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Enviando...
        </span>
    </button>
</form>
