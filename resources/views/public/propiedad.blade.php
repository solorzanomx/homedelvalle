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
        <div style="background: #ff0000; color: white; padding: 10px; text-align: center; font-weight: bold; margin-bottom: 10px;">✅ NUEVA GALERÍA CARGADA - Si ves esto, los cambios se están aplicando</div>
        @php $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values(); $pc = $photos->count(); @endphp
        @if($pc > 0)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
        <style>
        .gallery { position: relative; border-radius: 20px; overflow: hidden; background: #f3f4f6; box-shadow: 0 20px 60px rgba(0,0,0,0.08); }
        .gallery .swiper { aspect-ratio: 16/10; width: 100%; }
        .gallery img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .gbtn { position: absolute; top: 50%; transform: translateY(-50%); width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.9); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; transition: all 0.3s; color: #1e293b; box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .gbtn:hover { background: rgba(255,255,255,1); transform: translateY(-50%) scale(1.1); }
        .gbtn svg { width: 24px; height: 24px; stroke-width: 2.5; }
        .gbtn-prev { left: 20px; }
        .gbtn-next { right: 20px; }
        .gctr { position: absolute; top: 20px; left: 20px; background: rgba(30,41,59,0.75); backdrop-filter: blur(12px); color: white; font-size: 13px; font-weight: 600; padding: 8px 16px; border-radius: 24px; z-index: 10; }
        .gexp { position: absolute; top: 20px; right: 20px; width: 44px; height: 44px; border-radius: 50%; background: rgba(30,41,59,0.6); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; color: white; transition: all 0.3s; }
        .gexp:hover { background: rgba(30,41,59,0.85); transform: scale(1.08); }
        .gexp svg { width: 20px; height: 20px; }
        .gthumbs { display: flex; gap: 10px; padding: 16px 20px; overflow-x: auto; scroll-behavior: smooth; background: white; border-top: 1px solid #e2e8f0; }
        .gthumb { flex-shrink: 0; width: 80px; height: 60px; border-radius: 10px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.3s; opacity: 0.6; }
        .gthumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .gthumb:hover { opacity: 0.85; transform: scale(1.05); }
        .gthumb.active { border-color: var(--color-primary, #667eea); opacity: 1; }
        @media (max-width: 768px) { .gallery .swiper { aspect-ratio: 4/3; } .gbtn { width: 40px; height: 40px; } .gbtn svg { width: 20px; height: 20px; } .gbtn-prev { left: 12px; } .gbtn-next { right: 12px; } }
        </style>
        <div class="gallery">
            <div class="swiper" id="gs">
                <div class="swiper-wrapper">
                    @foreach($photos as $photo)
                    <div class="swiper-slide" data-fancybox="g" data-src="{{ asset('storage/' . $photo->path) }}">
                        <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description ?? $property->title }}" loading="lazy">
                    </div>
                    @endforeach
                </div>
            </div>
            @if($pc > 1)
            <button class="gbtn gbtn-prev" onclick="window.gs.slidePrev()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="15 18 9 12 15 6"></polyline></svg></button>
            <button class="gbtn gbtn-next" onclick="window.gs.slideNext()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 18 15 12 9 6"></polyline></svg></button>
            @endif
            <div class="gctr"><span id="gc">1</span> / {{ $pc }}</div>
            <button class="gexp" onclick="document.querySelector('[data-fancybox=g]')?.click()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg></button>
        </div>
        @if($pc > 1)
        <div class="gthumbs">
            @foreach($photos as $i => $photo)
            <div class="gthumb {{ $i === 0 ? 'active' : '' }}" data-i="{{ $i }}" onclick="window.gs.slideToLoop(this.dataset.i)">
                <img src="{{ asset('storage/' . $photo->path) }}" loading="lazy">
            </div>
            @endforeach
        </div>
        @endif
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox.umd.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox.css" />
        <script>
        function ginit() {
            if (!window.Swiper) { setTimeout(ginit, 50); return; }
            window.gs = new Swiper('#gs', { loop: true, effect: 'fade', fadeEffect: { crossFade: true }, autoplay: { delay: 5000 }, speed: 800, on: { slideChange: function() { document.getElementById('gc').textContent = this.realIndex + 1; document.querySelectorAll('.gthumb').forEach((el, i) => el.classList.toggle('active', i === this.realIndex)); } } });
            if (window.Fancybox) Fancybox.bind('[data-fancybox="g"]', { on: { reveal: () => document.body.style.overflow = 'hidden', done: () => document.body.style.overflow = '' } });
        }
        ginit();
        </script>
        @endif
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
            @include('public.propiedad._gallery', ['property' => $property])
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
                    @include('public.propiedad._gallery', ['property' => $property])

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
