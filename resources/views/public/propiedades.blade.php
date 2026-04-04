@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Propiedades en Ciudad de México"
        description="Explora nuestro catálogo de propiedades en CDMX. Casas, departamentos, terrenos y más en venta y renta."
        :canonical="route('propiedades.index')"
    />
@endsection

@php
    $template = $siteSettings?->property_listing_template ?? 'grid';
    $typeLabels = ['House'=>'Casa','Apartment'=>'Depto','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial','Warehouse'=>'Bodega'];
    $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal'];
@endphp

@section('content')
    <x-public.hero
        heading="{{ $siteSettings?->featured_heading ?? 'Propiedades en CDMX' }}"
        subheading="{{ $siteSettings?->featured_subheading ?? 'Explora nuestro catálogo de casas, departamentos y terrenos disponibles.' }}"
        :show-search="true"
        :compact="false"
        :breadcrumb-items="[['label' => 'Propiedades']]"
    />

    <section class="py-16 sm:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Results bar --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-10" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div>
                    <p class="text-sm text-gray-500">
                        <span class="font-bold text-gray-900">{{ number_format($totalCount) }}</span> propiedades encontradas
                        @if(request('operation_type') || request('property_type') || request('search'))
                        <span class="text-brand-500">con filtros activos</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Active filter pills --}}
                    @if(request('operation_type'))
                    <a href="{{ route('propiedades.index', request()->except('operation_type')) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-600 hover:bg-brand-100 transition-colors duration-200">
                        {{ $opLabels[request('operation_type')] ?? request('operation_type') }}
                        <x-icon name="x" class="w-3 h-3" />
                    </a>
                    @endif
                    @if(request('property_type'))
                    <a href="{{ route('propiedades.index', request()->except('property_type')) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-600 hover:bg-brand-100 transition-colors duration-200">
                        {{ $typeLabels[request('property_type')] ?? request('property_type') }}
                        <x-icon name="x" class="w-3 h-3" />
                    </a>
                    @endif

                    {{-- Sort dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:border-brand-200 hover:text-brand-600 transition-all duration-200">
                            <x-icon name="list-filter" class="w-4 h-4" />
                            {{ request('sort') === 'price_asc' ? 'Precio: menor' : (request('sort') === 'price_desc' ? 'Precio: mayor' : 'Más recientes') }}
                            <span class="transition-transform duration-200" :class="open && 'rotate-180'"><x-icon name="chevron-down" class="w-3.5 h-3.5" /></span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 rounded-xl border border-gray-200/60 bg-white shadow-premium-lg z-20 overflow-hidden">
                            <a href="{{ route('propiedades.index', array_merge(request()->all(), ['sort' => 'latest'])) }}" class="block px-4 py-2.5 text-sm {{ request('sort', 'latest') === 'latest' ? 'text-brand-600 font-semibold bg-brand-50/50' : 'text-gray-600 hover:bg-gray-50' }} transition-colors duration-150">Más recientes</a>
                            <a href="{{ route('propiedades.index', array_merge(request()->all(), ['sort' => 'price_asc'])) }}" class="block px-4 py-2.5 text-sm {{ request('sort') === 'price_asc' ? 'text-brand-600 font-semibold bg-brand-50/50' : 'text-gray-600 hover:bg-gray-50' }} transition-colors duration-150">Precio: menor a mayor</a>
                            <a href="{{ route('propiedades.index', array_merge(request()->all(), ['sort' => 'price_desc'])) }}" class="block px-4 py-2.5 text-sm {{ request('sort') === 'price_desc' ? 'text-brand-600 font-semibold bg-brand-50/50' : 'text-gray-600 hover:bg-gray-50' }} transition-colors duration-150">Precio: mayor a menor</a>
                        </div>
                    </div>
                </div>
            </div>

            @if($properties->isNotEmpty())

                @if($template === 'list')
                    {{-- LIST TEMPLATE --}}
                    <div class="space-y-5">
                        @foreach($properties as $index => $property)
                        <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ ($index % 5) * 100 }}ms">
                            <a href="{{ route('propiedades.show', ['id' => $property->id, 'slug' => $property->slug]) }}"
                               class="group flex flex-col sm:flex-row gap-5 rounded-2xl border border-gray-200/60 bg-white p-3 hover:shadow-premium-lg hover:border-brand-100 hover:-translate-y-0.5 transition-all duration-500">
                                <div class="sm:w-72 shrink-0 aspect-[4/3] rounded-xl overflow-hidden bg-gray-100 relative">
                                    @if($property->photo_url)
                                        <img src="{{ $property->photo_url }}" alt="{{ $property->title }}" class="w-full h-full object-cover img-zoom" loading="lazy">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center">
                                            <x-icon name="home" class="w-10 h-10 text-brand-300" />
                                        </div>
                                    @endif
                                    {{-- Badges --}}
                                    <div class="absolute top-2.5 left-2.5 flex gap-1.5">
                                        @if($property->operation_type)
                                        <span class="inline-flex items-center rounded-lg bg-brand-600/90 backdrop-blur-sm px-2.5 py-1 text-xs font-semibold text-white shadow-sm">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col justify-center py-1 flex-1">
                                    <p class="text-xl font-extrabold text-gray-900 tracking-tight">{{ $property->formatted_price }}</p>
                                    <h3 class="mt-1 text-sm font-semibold text-gray-600 group-hover:text-brand-600 transition-colors duration-300">{{ $property->title }}</h3>
                                    @if($property->colony || $property->city)
                                    <p class="mt-1.5 text-xs text-gray-400 flex items-center gap-1.5">
                                        <x-icon name="map-pin" class="w-3.5 h-3.5 text-brand-400" />
                                        {{ collect([$property->colony, $property->city])->filter()->join(', ') }}
                                    </p>
                                    @endif
                                    <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500 font-medium">
                                        @if($property->bedrooms)<span class="flex items-center gap-1">{{ $property->bedrooms }} rec.</span>@endif
                                        @if($property->bathrooms)<span class="flex items-center gap-1">{{ $property->bathrooms }} baños</span>@endif
                                        @if($property->area)<span class="flex items-center gap-1">{{ number_format($property->area) }} m²</span>@endif
                                        @if($property->parking)<span class="flex items-center gap-1">{{ $property->parking }} est.</span>@endif
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>

                @elseif($template === 'magazine')
                    {{-- MAGAZINE TEMPLATE --}}
                    @if($properties->first())
                    <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" class="mb-8">
                        <a href="{{ route('propiedades.show', ['id' => $properties->first()->id, 'slug' => $properties->first()->slug]) }}"
                           class="group relative block rounded-2xl overflow-hidden aspect-[21/9] bg-gray-100">
                            @if($properties->first()->photo_url)
                                <img src="{{ $properties->first()->photo_url }}" alt="{{ $properties->first()->title }}" class="w-full h-full object-cover img-zoom" loading="lazy">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-brand-200 to-brand-400"></div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-10">
                                <div class="flex gap-2 mb-3">
                                    @if($properties->first()->operation_type)
                                    <span class="inline-flex items-center rounded-lg bg-brand-500/90 backdrop-blur-sm px-3 py-1.5 text-xs font-semibold text-white">{{ $opLabels[$properties->first()->operation_type] ?? $properties->first()->operation_type }}</span>
                                    @endif
                                    @if($properties->first()->property_type)
                                    <span class="inline-flex items-center rounded-lg bg-white/20 backdrop-blur-sm px-3 py-1.5 text-xs font-semibold text-white">{{ $typeLabels[$properties->first()->property_type] ?? $properties->first()->property_type }}</span>
                                    @endif
                                </div>
                                <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $properties->first()->formatted_price }}</p>
                                <h3 class="mt-1 text-lg text-white/90 font-medium">{{ $properties->first()->title }}</h3>
                                @if($properties->first()->colony || $properties->first()->city)
                                <p class="mt-1 text-sm text-white/60">{{ collect([$properties->first()->colony, $properties->first()->city])->filter()->join(', ') }}</p>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($properties->count() > 1)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($properties->slice(1) as $index => $property)
                        <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 100 }}ms">
                            <x-public.property-card :property="$property" />
                        </div>
                        @endforeach
                    </div>
                    @endif

                @else
                    {{-- GRID TEMPLATE (default) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($properties as $index => $property)
                        <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ ($index % 6) * 100 }}ms">
                            <x-public.property-card :property="$property" />
                        </div>
                        @endforeach
                    </div>
                @endif

                {{-- Pagination --}}
                <div class="mt-14 flex justify-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                    {{ $properties->links() }}
                </div>
            @else
                {{-- Empty state --}}
                <div class="text-center py-20 rounded-2xl gradient-brand-soft" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-100 mb-5">
                        <x-icon name="home" class="w-8 h-8 text-brand-500" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">No encontramos propiedades</h3>
                    <p class="mt-2 text-gray-500 mb-6">Intenta con otros filtros de búsqueda.</p>
                    <a href="{{ route('propiedades.index') }}" class="inline-flex items-center gap-2 rounded-xl gradient-brand px-6 py-3 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300">
                        Ver todas las propiedades
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
