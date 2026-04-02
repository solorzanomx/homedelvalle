@php $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal']; @endphp
<div class="rounded-2xl border border-gray-200/60 overflow-hidden shadow-premium-lg" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
    <div class="h-1.5 gradient-brand"></div>
    <div class="p-6">
        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $property->formatted_price }}</p>
        <h1 class="mt-1.5 text-lg font-bold text-gray-700">{{ $property->title }}</h1>
        @if($property->colony || $property->city)
        <p class="mt-1.5 text-sm text-gray-400 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ collect([$property->colony, $property->city])->filter()->join(', ') }}
        </p>
        @endif
        @if($property->operation_type)
        <span class="mt-3 inline-flex items-center rounded-lg gradient-brand px-3 py-1.5 text-xs font-semibold text-white shadow-brand">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>
        @endif
    </div>
</div>
