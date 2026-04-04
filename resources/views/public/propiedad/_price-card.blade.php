@php $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal']; @endphp
<div class="rounded-2xl border border-gray-200/60 overflow-hidden shadow-premium-lg" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
    <div class="h-1.5 gradient-brand"></div>
    <div class="p-6">
        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $property->formatted_price }}</p>
        <h1 class="mt-1.5 text-lg font-bold text-gray-700">{{ $property->title }}</h1>
        @if($property->colony || $property->city)
        <p class="mt-1.5 text-sm text-gray-400 flex items-center gap-1.5">
            <x-icon name="map-pin" class="w-3.5 h-3.5 text-brand-400" />
            {{ collect([$property->colony, $property->city])->filter()->join(', ') }}
        </p>
        @endif
        @if($property->operation_type)
        <span class="mt-3 inline-flex items-center rounded-lg gradient-brand px-3 py-1.5 text-xs font-semibold text-white shadow-brand">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>
        @endif
    </div>
</div>
