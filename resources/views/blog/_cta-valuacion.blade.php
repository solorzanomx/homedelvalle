{{-- Inyectado por BlogBodyEnhancer justo después de la primera tabla de un
     post — el momento de mayor intención: el lector ya vio el promedio de
     zona y la pregunta natural es "¿y MI propiedad?". --}}
<div class="not-prose my-8">
    <div class="rounded-2xl bg-brand-50/70 border border-brand-100 p-6 sm:p-7 flex flex-col sm:flex-row sm:items-center gap-5">
        <div class="flex-1">
            <p class="text-base font-extrabold text-gray-900">Estos son promedios de zona — tu propiedad puede estar 15–20% arriba o abajo.</p>
            <p class="mt-1 text-sm text-gray-500 leading-relaxed">Piso, orientación, estado y metros de fachada mueven el número. Recibe el de tu propiedad específica, gratis y en menos de 24 horas.</p>
        </div>
        <a href="{{ route('precios.opinion') }}"
           data-track-location="cta_valuacion"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold shrink-0 transition-all duration-300 hover:-translate-y-0.5 shadow-brand"
           style="background: var(--color-primary, #3B82C4);">
            Recibir mi número exacto
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</div>
