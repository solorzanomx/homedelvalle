{{-- CTA inline por cluster (Fase 3 del prompt de leads del blog) — inyectado por BlogBodyEnhancer
     después de la primera sección h2. Es un link de WhatsApp, no un form: el form de verdad (el que
     crea el lead en el CRM) vive una sola vez por post, al final — dos forms Livewire independientes
     en la misma página sería redundante y más frágil sin ganar nada. --}}
@php
    /** @var \App\Models\Post $post */
    /** @var ?\App\Models\BlogCtaConfig $ctaConfig */
    $copy = $ctaConfig?->copyFor($decided ?? false) ?? [
        'headline' => '¿Tienes una propiedad en la Benito Juárez?',
        'body' => 'Platícanos tu caso. Asesoría personalizada, sin costo y sin compromiso.',
        'button_label' => 'Escríbenos por WhatsApp',
        'whatsapp_message' => 'Hola, vengo del artículo "{titulo}".',
    ];
    $waUrl = \App\Support\BlogWhatsapp::urlFor($post, $copy['whatsapp_message']);
@endphp
@if($waUrl)
<div class="not-prose my-8">
    <a href="{{ $waUrl }}" target="_blank" rel="noopener"
       data-track-location="cta_inline" data-cta-variant="{{ $cluster ?? 'default' }}"
       class="group flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl bg-brand-50 border border-brand-100 px-6 py-5 hover:bg-brand-100/70 hover:border-brand-300 transition-all duration-300">
        <div class="flex-1">
            <p class="text-base font-extrabold text-gray-900">{{ $copy['headline'] }}</p>
            <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ $copy['body'] }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-600 group-hover:text-brand-700 whitespace-nowrap transition-colors duration-300">
            <x-icon name="brands/whatsapp" class="w-4 h-4" />
            {{ $copy['button_label'] }}
        </span>
    </a>
</div>
@endif
