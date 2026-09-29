{{-- Botón flotante de WhatsApp, solo móvil, discreto (Fase 3.3 del prompt de leads del blog), con
     el mensaje prellenado del artículo. El sitio ya trae <x-public.whatsapp-float> en TODAS las
     páginas (genérico, con menú de opciones) — blog/show.blade.php le manda `hideWhatsappFloatMobile`
     para que se oculte solo en móvil solo en posts con cluster, y no queden los dos apilados. --}}
@php
    $waUrl = \App\Support\BlogWhatsapp::urlFor($post, $copy['whatsapp_message'] ?? 'Hola, vengo del artículo "{titulo}".');
@endphp
@if($waUrl)
<a href="{{ $waUrl }}" target="_blank" rel="noopener"
   data-track-location="cta_sticky" data-cta-variant="{{ $cluster ?? 'default' }}"
   class="sm:hidden fixed bottom-4 right-4 z-40 inline-flex items-center gap-2 px-4 py-3 rounded-full shadow-lg text-white text-sm font-bold"
   style="background:#25D366;">
    <x-icon name="brands/whatsapp" class="w-5 h-5" />
    WhatsApp
</a>
@endif
