@if($siteSettings?->whatsapp_number)
@php
    $phone = preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number);
@endphp
<div class="fixed bottom-6 right-6 z-50" x-data="{ tooltip: false }">
    {{-- Pulse ring --}}
    <span class="absolute inset-0 rounded-full bg-[#25D366]/30 animate-ping" style="animation-duration: 3s;"></span>

    <a href="https://wa.me/{{ $phone }}?text={{ urlencode('Hola, me interesa información sobre sus propiedades.') }}"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Contactar por WhatsApp"
       @mouseenter="tooltip = true"
       @mouseleave="tooltip = false"
       class="relative flex items-center justify-center w-14 h-14 bg-[#25D366] text-white rounded-full shadow-lg shadow-[#25D366]/30 hover:bg-[#1ebe57] hover:scale-110 hover:shadow-xl hover:shadow-[#25D366]/40 active:scale-100 transition-all duration-300">
        <x-icon name="brands/whatsapp" class="w-7 h-7" />
    </a>

    {{-- Tooltip --}}
    <div x-show="tooltip" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-2"
         x-transition:enter-end="opacity-100 translate-x-0"
         class="absolute right-full mr-3 top-1/2 -translate-y-1/2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white shadow-lg">
        Escríbenos por WhatsApp
        <span class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-full border-4 border-transparent border-l-gray-900"></span>
    </div>
</div>
@endif
