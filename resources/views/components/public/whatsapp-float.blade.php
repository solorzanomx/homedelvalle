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
        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-5.031 1.378c-3.055 2.2-4.982 5.973-4.982 10.147 0 1.593.292 3.163.851 4.65L2.36 22.557l4.903-1.526c1.396.757 2.996 1.156 4.604 1.156 5.523 0 10.031-4.507 10.031-10.031 0-2.722-1.063-5.29-2.994-7.214a10.014 10.014 0 00-7.117-2.941"/>
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
        class="absolute bottom-20 right-0 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
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
