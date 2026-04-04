@props([
    'heading' => null,
    'subheading' => null,
    'bgImage' => null,
    'showSearch' => false,
    'compact' => true,
    'breadcrumbItems' => null,
])

<section class="relative overflow-hidden bg-brand-950">
    {{-- Background image --}}
    @if($bgImage)
    <img src="{{ $bgImage }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-30">
    @endif

    {{-- Gradient overlays --}}
    <div class="absolute inset-0 bg-gradient-to-br from-brand-950 via-brand-900/90 to-brand-800/80"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(59,130,196,0.15)_0%,_transparent_60%)]"></div>

    {{-- Decorative blobs --}}
    <div class="absolute -top-24 -right-24 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl animate-float"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-brand-400/5 rounded-full blur-3xl animate-float animation-delay-300"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 {{ $compact ? 'py-20 sm:py-24' : 'pt-24 pb-32 sm:pt-32 sm:pb-40' }} text-center"
         x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">

        @if($heading)
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">{{ $heading }}</h1>
        @endif

        @if($subheading)
        <p class="mt-4 text-lg sm:text-xl text-brand-200/80 max-w-2xl mx-auto leading-relaxed">{{ $subheading }}</p>
        @endif

        {{-- Breadcrumbs inside hero --}}
        @if($breadcrumbItems)
        <nav class="mt-6 flex items-center justify-center gap-2 text-sm text-brand-300/60" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white transition-colors duration-200">Inicio</a>
            @foreach($breadcrumbItems as $item)
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                @if(isset($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-white transition-colors duration-200">{{ $item['label'] }}</a>
                @else
                    <span class="text-brand-200/80">{{ $item['label'] }}</span>
                @endif
            @endforeach
        </nav>
        @endif

        {{-- Search bar --}}
        @if($showSearch)
        <form action="{{ route('propiedades.index') }}" method="GET" class="mt-10 mx-auto max-w-3xl" x-data>
            <div class="glass rounded-2xl p-2.5 shadow-premium-xl border border-white/10">
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <select name="property_type" class="flex-1 rounded-xl border-0 bg-white/95 px-4 py-3.5 text-gray-900 text-sm font-medium focus:ring-2 focus:ring-brand-400 transition-all duration-200">
                        <option value="">Tipo de propiedad</option>
                        <option value="House" {{ request('property_type') == 'House' ? 'selected' : '' }}>Casa</option>
                        <option value="Apartment" {{ request('property_type') == 'Apartment' ? 'selected' : '' }}>Departamento</option>
                        <option value="Land" {{ request('property_type') == 'Land' ? 'selected' : '' }}>Terreno</option>
                        <option value="Office" {{ request('property_type') == 'Office' ? 'selected' : '' }}>Oficina</option>
                        <option value="Commercial" {{ request('property_type') == 'Commercial' ? 'selected' : '' }}>Comercial</option>
                    </select>
                    <select name="operation_type" class="flex-1 rounded-xl border-0 bg-white/95 px-4 py-3.5 text-gray-900 text-sm font-medium focus:ring-2 focus:ring-brand-400 transition-all duration-200">
                        <option value="">Operación</option>
                        <option value="sale" {{ request('operation_type') == 'sale' ? 'selected' : '' }}>Venta</option>
                        <option value="rental" {{ request('operation_type') == 'rental' ? 'selected' : '' }}>Renta</option>
                    </select>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ciudad o colonia..."
                           class="flex-1 rounded-xl border-0 bg-white/95 px-4 py-3.5 text-gray-900 text-sm placeholder-gray-400 font-medium focus:ring-2 focus:ring-brand-400 transition-all duration-200">
                    <button type="submit" class="rounded-xl gradient-brand px-8 py-3.5 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shrink-0 flex items-center gap-2">
                        <x-icon name="search" class="w-4 h-4" />
                        Buscar
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>
</section>
