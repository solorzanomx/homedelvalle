@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="{{ $property->title }}"
        :description="Str::limit(strip_tags($property->description), 160)"
        :canonical="route('propiedades.show', ['id' => $property->id, 'slug' => $property->slug])"
        :og-image="$property->photo_url"
        og-type="product"
    />
    <x-public.json-ld type="Product" :data="[
        'name' => $property->title,
        'description' => strip_tags($property->description ?? ''),
        'image' => $property->photo_url,
        'offers' => [
            '@type' => 'Offer',
            'price' => $property->price,
            'priceCurrency' => $property->currency ?? 'MXN',
            'availability' => 'https://schema.org/InStock',
        ],
    ]" />
@endsection

@php
    $template = $siteSettings?->property_detail_template ?? 'sidebar';
    $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal'];
    $typeLabels = ['House'=>'Casa','Apartment'=>'Depto','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial','Warehouse'=>'Bodega'];

    $specs = collect([
        $property->bedrooms ? ['icon' => 'bed', 'value' => $property->bedrooms, 'label' => 'Recámaras'] : null,
        $property->bathrooms ? ['icon' => 'bath', 'value' => $property->bathrooms, 'label' => 'Baños'] : null,
        $property->area ? ['icon' => 'area', 'value' => number_format($property->area, 0) . ' m²', 'label' => 'Superficie'] : null,
        $property->parking ? ['icon' => 'car', 'value' => $property->parking, 'label' => 'Estacionamientos'] : null,
    ])->filter();
@endphp

@section('content')

@if($template === 'fullwidth')
    {{-- ============================== --}}
    {{-- FULLWIDTH TEMPLATE --}}
    {{-- ============================== --}}

    {{-- Full-width carousel --}}
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pt-6">
        @include('public.propiedad._gallery-simple', ['property' => $property])
    </div>

    <section class="py-12 sm:py-16 bg-white">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <a href="{{ url('/') }}" class="hover:text-brand-600 transition-colors">Inicio</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <a href="{{ route('propiedades.index') }}" class="hover:text-brand-600 transition-colors">Propiedades</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <span class="text-gray-600 truncate">{{ $property->title }}</span>
            </nav>

            {{-- Price + Title --}}
            <div class="mb-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    @if($property->operation_type)
                    <span class="inline-flex items-center rounded-lg gradient-brand px-3 py-1.5 text-xs font-semibold text-white shadow-brand">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>
                    @endif
                    @if($property->property_type)
                    <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">{{ $typeLabels[$property->property_type] ?? $property->property_type }}</span>
                    @endif
                </div>
                <p class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $property->formatted_price }}</p>
                <h1 class="mt-2 text-xl font-bold text-gray-700">{{ $property->title }}</h1>
                @if($property->colony || $property->city)
                <p class="mt-1.5 text-sm text-gray-400 flex items-center gap-1.5">
                    <x-icon name="map-pin" class="w-4 h-4 text-brand-400" />
                    {{ collect([$property->colony, $property->city])->filter()->join(', ') }}
                </p>
                @endif
            </div>

            @include('public.propiedad._share', ['property' => $property])

            {{-- Specs --}}
            @if($specs->count())
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                @foreach($specs as $i => $spec)
                @include('public.propiedad._spec-card', ['spec' => $spec, 'delay' => $i * 100])
                @endforeach
            </div>
            @endif

            @include('public.propiedad._description', ['property' => $property])

            {{-- Contact form --}}
            <div class="mt-12 rounded-2xl border border-gray-200/60 p-8 shadow-premium-lg" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <h3 class="text-lg font-bold text-gray-900 mb-5">¿Te interesa esta propiedad?</h3>
                <x-public.contact-form :property-id="$property->id" source="property" />
            </div>
        </div>
    </section>

@elseif($template === 'gallery')
    {{-- ============================== --}}
    {{-- GALLERY TEMPLATE --}}
    {{-- ============================== --}}

    {{-- Gallery carousel --}}
    <div class="bg-gray-100 pt-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
            @include('public.propiedad._gallery-premium', ['property' => $property])
        </div>
    </div>

    <section class="py-12 sm:py-16 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <a href="{{ url('/') }}" class="hover:text-brand-600 transition-colors">Inicio</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <a href="{{ route('propiedades.index') }}" class="hover:text-brand-600 transition-colors">Propiedades</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <span class="text-gray-600 truncate">{{ $property->title }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">
                {{-- Content (3/5) --}}
                <div class="lg:col-span-3">
                    <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $property->title }}</h1>
                        @if($property->colony || $property->city)
                        <p class="mt-1.5 text-sm text-gray-400 flex items-center gap-1.5">
                            <x-icon name="map-pin" class="w-4 h-4 text-brand-400" />
                            {{ collect([$property->colony, $property->city])->filter()->join(', ') }}
                        </p>
                        @endif
                    </div>

                    @include('public.propiedad._share', ['property' => $property])
                    @include('public.propiedad._description', ['property' => $property])
                </div>

                {{-- Sidebar (2/5) --}}
                <div class="lg:col-span-2">
                    <div class="sticky top-24 space-y-6">
                        {{-- Mobile price (visible on lg in gallery mosaic) --}}
                        <div class="lg:hidden" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                            @include('public.propiedad._price-card', ['property' => $property])
                        </div>

                        {{-- Specs --}}
                        @if($specs->count())
                        <div class="grid grid-cols-2 gap-3" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                            @foreach($specs as $i => $spec)
                            @include('public.propiedad._spec-card', ['spec' => $spec, 'delay' => $i * 100])
                            @endforeach
                        </div>
                        @endif

                        @include('public.propiedad._contact-sidebar', ['property' => $property])
                    </div>
                </div>
            </div>
        </div>
    </section>

@else
    {{-- ============================== --}}
    {{-- SIDEBAR TEMPLATE (default) --}}
    {{-- ============================== --}}

    <section class="py-16 sm:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <a href="{{ url('/') }}" class="hover:text-brand-600 transition-colors">Inicio</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <a href="{{ route('propiedades.index') }}" class="hover:text-brand-600 transition-colors">Propiedades</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <span class="text-gray-600 truncate">{{ $property->title }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                {{-- Main content --}}
                <div class="lg:col-span-2">
                    {{-- Image Carousel --}}
                    @include('public.propiedad._gallery-premium', ['property' => $property])

                    @include('public.propiedad._share', ['property' => $property])

                    {{-- Specs --}}
                    @if($specs->count())
                    <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                        @foreach($specs as $i => $spec)
                        @include('public.propiedad._spec-card', ['spec' => $spec, 'delay' => $i * 100])
                        @endforeach
                    </div>
                    @endif

                    @include('public.propiedad._description', ['property' => $property])
                </div>

                {{-- Sidebar --}}
                <div>
                    <div class="sticky top-24 space-y-6">
                        @include('public.propiedad._price-card', ['property' => $property])
                        @include('public.propiedad._contact-sidebar', ['property' => $property])
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

{{-- Similar Properties --}}
@if(isset($similar) && $similar->count())
<section class="py-20 sm:py-24 bg-gray-50/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Explora más</p>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Propiedades similares</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($similar as $index => $prop)
            <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 150 }}ms">
                <x-public.property-card :property="$prop" />
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
