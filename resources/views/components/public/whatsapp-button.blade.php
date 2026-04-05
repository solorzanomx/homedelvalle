@if($siteSettings?->whatsapp_number)
@php
    $phone = preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number);
@endphp
<div class="fixed bottom-6 right-6 z-50"
     x-data="{
        tooltip: false,
        bubble: false,
        dismissed: false,
        init() {
            setTimeout(() => {
                if (!this.dismissed) this.bubble = true;
            }, 20000);
        }
     }">

    {{-- Chat bubble --}}
    <div x-show="bubble && !dismissed" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute bottom-18 right-0 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden mb-2">
        <div class="bg-[#25D366] px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2 text-white">
                <x-icon name="brands/whatsapp" class="w-5 h-5" />
                <span class="text-sm font-semibold">WhatsApp</span>
            </div>
            <button @click="dismissed = true; bubble = false" class="text-white/70 hover:text-white transition-colors">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>
        <div class="p-4">
            <div class="bg-gray-100 rounded-xl rounded-tl-sm px-3 py-2 text-sm text-gray-700 leading-relaxed">
                ¡Hola! 👋 ¿Te puedo ayudar? Escríbeme directamente por WhatsApp.
            </div>
            <a href="https://wa.me/{{ $phone }}?text={{ urlencode('Hola, me interesa información sobre sus propiedades.') }}"
               target="_blank"
               rel="noopener noreferrer"
               class="mt-3 flex items-center justify-center gap-2 w-full rounded-xl bg-[#25D366] text-white py-2.5 text-sm font-semibold hover:bg-[#1ebe57] active:scale-[0.98] transition-all">
                <x-icon name="message-circle" class="w-4 h-4" />
                Chat directo
            </a>
        </div>
    </div>

    {{-- Pulse ring --}}
    <span class="absolute inset-0 rounded-full bg-[#25D366]/30 animate-ping" style="animation-duration: 3s;"></span>

    {{-- Main button --}}
    <a href="https://wa.me/{{ $phone }}?text={{ urlencode('Hola, me interesa información sobre sus propiedades.') }}"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Contactar por WhatsApp"
       @mouseenter="tooltip = true"
       @mouseleave="tooltip = false"
       @click="dismissed = true; bubble = false"
       class="relative flex items-center justify-center w-14 h-14 bg-[#25D366] text-white rounded-full shadow-lg shadow-[#25D366]/30 hover:bg-[#1ebe57] hover:scale-110 hover:shadow-xl hover:shadow-[#25D366]/40 active:scale-100 transition-all duration-300">
        <x-icon name="brands/whatsapp" class="w-7 h-7" />
    </a>

    {{-- Tooltip --}}
    <div x-show="tooltip && !bubble" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-2"
         x-transition:enter-end="opacity-100 translate-x-0"
         class="absolute right-full mr-3 top-1/2 -translate-y-1/2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white shadow-lg">
        Escríbenos por WhatsApp
        <span class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-full border-4 border-transparent border-l-gray-900"></span>
    </div>
</div>
@endif
