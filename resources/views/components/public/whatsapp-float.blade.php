@props(['siteSettings' => null])

@php
    // Query directo a BD para evitar cache stale — es un campo crítico
    $freshSettings = \App\Models\SiteSetting::select('whatsapp_number', 'contact_phone')->first();

    $whatsappNumber = $freshSettings?->whatsapp_number
        ?: $freshSettings?->contact_phone
        ?: $siteSettings?->whatsapp_number
        ?: $siteSettings?->contact_phone
        ?: null;

    // Solo dígitos para wa.me
    $waNumber = $whatsappNumber ? preg_replace('/[^0-9]/', '', $whatsappNumber) : null;
@endphp

@if($waNumber)
<div x-data="{ open: false }" class="fixed z-[9999]" style="position:fixed !important;bottom:1.5rem !important;right:1.5rem !important;z-index:9999 !important">
    {{-- Botón flotante --}}
    <button
        @click="open = !open"
        :class="open ? 'scale-110' : 'scale-100'"
        class="flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 bg-[#25D366] hover:bg-[#20BA58] text-white group relative"
        aria-label="Enviar mensaje por WhatsApp"
    >
        {{-- WhatsApp official logo SVG --}}
        <svg class="w-7 h-7 sm:w-8 sm:h-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 3C8.82 3 3 8.82 3 16c0 2.38.65 4.61 1.78 6.54L3 29l6.63-1.74A12.93 12.93 0 0016 29c7.18 0 13-5.82 13-13S23.18 3 16 3z" fill="white"/>
            <path d="M16 5.5c-5.79 0-10.5 4.71-10.5 10.5 0 1.95.54 3.78 1.47 5.35l.24.4-1.01 3.69 3.8-.99.38.22A10.43 10.43 0 0016 26.5c5.79 0 10.5-4.71 10.5-10.5S21.79 5.5 16 5.5z" fill="#25D366"/>
            <path d="M12.1 10.5c-.28 0-.73.1-1.11.52-.38.41-1.47 1.44-1.47 3.5 0 2.07 1.5 4.07 1.71 4.35.2.28 2.95 4.75 7.16 6.47 3.55 1.43 4.27 1.15 5.04 1.08.77-.07 2.49-.99 2.84-1.96.35-.96.35-1.79.24-1.96-.1-.18-.38-.28-.8-.49-.41-.2-2.44-1.19-2.82-1.33-.38-.14-.66-.2-.93.2-.28.41-.93 1.19-1.14 1.43-.2.24-.41.27-.76.1-.35-.18-1.48-.55-2.82-1.75-1.04-.93-1.74-2.08-1.95-2.43-.2-.35-.02-.54.15-.72.16-.16.35-.41.53-.62.17-.2.23-.34.35-.57.12-.23.06-.43-.03-.62-.1-.2-.93-2.24-1.27-3.07-.34-.82-.69-.7-.93-.71-.24-.02-.52-.02-.8-.02z" fill="white"/>
        </svg>

        {{-- Badge animado cuando está cerrado --}}
        <span x-show="!open" x-transition class="absolute -top-2 -right-2 flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold">
            1
        </span>
    </button>

    {{-- Menú de opciones --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false"
        class="absolute bottom-20 right-0 w-72 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
    >
        {{-- Header --}}
        <div class="bg-gradient-to-r from-[#25D366] to-[#20BA58] px-4 py-3">
            <p class="text-white text-sm font-semibold">¿Cómo podemos ayudarte?</p>
            <p class="text-white/80 text-xs mt-1">Respuesta en < 15 minutos</p>
        </div>

        {{-- Opciones --}}
        <div class="space-y-1 p-2 max-h-96 overflow-y-auto">
            @foreach(getWhatsAppOptions() as $option)
            <a
                href="https://wa.me/{{ $waNumber }}?text={{ urlencode($option['message']) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-start gap-3 p-3 rounded-xl hover:bg-green-50 transition-colors group cursor-pointer"
            >
                <span class="text-xl mt-0.5 group-hover:scale-110 transition-transform">{{ $option['icon'] }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 group-hover:text-green-700">{{ $option['label'] }}</p>
                    <p class="text-xs text-gray-500 group-hover:text-gray-700 line-clamp-2">{{ $option['subtitle'] }}</p>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="border-t border-gray-100 px-4 py-2 bg-gray-50 text-center text-xs text-gray-500">
            Horario: L-V 9am-6pm · S 10am-2pm
        </div>
    </div>
</div>

@endif
