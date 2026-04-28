@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Testimonios"
        description="Lo que dicen nuestros clientes sobre Home del Valle. Testimonios reales de compradores e inquilinos en Del Valle, Narvarte y la Benito Juárez, CDMX."
        :canonical="route('testimonios')"
    />
@endsection

@section('content')
    <x-public.hero
        :heading="'Lo que dicen nuestros clientes'"
        :subheading="'Experiencias reales de quienes confiaron en nosotros para encontrar su hogar ideal en la Benito Juárez.'"
        :breadcrumb-items="[['label' => 'Testimonios']]"
    />

    {{-- Featured Video --}}
    @if($featured && $featured->type === 'video' && $featured->youtube_embed_url)
    <section class="py-16 sm:py-20 bg-white">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-50 text-brand-600 text-xs font-semibold tracking-wide uppercase">
                    <x-icon name="star" class="w-3.5 h-3.5" /> Testimonio destacado
                </span>
            </div>
            <div class="flex flex-col items-center gap-8">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gray-900" style="width: min(360px, 100%); aspect-ratio: 9/16;" x-data="{ playing: false }">
                    <template x-if="!playing">
                        <button @click="playing = true" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-gray-900/60 hover:bg-gray-900/40 transition-all duration-300 group cursor-pointer">
                            <div class="w-20 h-20 rounded-full bg-white/90 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-brand-600 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <span class="mt-4 text-white font-semibold text-lg">{{ $featured->name }}</span>
                            @if($featured->role)
                                <span class="text-white/70 text-sm">{{ $featured->role }}</span>
                            @endif
                        </button>
                    </template>
                    <template x-if="playing">
                        <iframe src="{{ $featured->youtube_embed_url }}?autoplay=1&rel=0" class="w-full h-full absolute inset-0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </template>
                    @if($featured->avatar)
                        <img src="{{ Storage::url($featured->avatar) }}" alt="{{ $featured->name }}" class="absolute inset-0 w-full h-full object-cover opacity-40">
                    @endif
                </div>
                @if($featured->content)
                    <blockquote class="text-center text-lg text-gray-600 italic max-w-3xl">
                        "{{ $featured->content }}"
                    </blockquote>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- Testimonials Grid --}}
    <section class="py-16 sm:py-20 {{ $featured && $featured->type === 'video' ? 'bg-gray-50/60' : 'bg-white' }}">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Experiencias de nuestros clientes</h2>
                <p class="mt-3 text-gray-500 max-w-2xl mx-auto">Cada operacion es unica. Aqui comparten su experiencia quienes nos confiaron la busqueda, compra o renta de su inmueble.</p>
            </div>

            @if($testimonials->isEmpty())
                <div class="text-center py-12">
                    <p class="text-gray-400 text-lg">Proximamente compartiremos experiencias de nuestros clientes.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($testimonials as $t)
                    <div class="group bg-white rounded-2xl border border-gray-200/60 p-6 hover:shadow-xl hover:border-brand-100 transition-all duration-500"
                         x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 bg-brand-500 flex items-center justify-center text-white font-bold text-lg">
                                @if($t->avatar)
                                    <img src="{{ Storage::url($t->avatar) }}" alt="{{ $t->name }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    {{ strtoupper(substr($t->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 text-sm">{{ $t->name }}</h3>
                                @if($t->role)
                                    <p class="text-xs text-gray-500 truncate">{{ $t->role }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Stars --}}
                        <div class="flex gap-0.5 mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $t->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>

                        {{-- Content --}}
                        @if($t->content)
                            <div x-data="{ expanded: false }">
                                <p class="text-gray-600 text-sm leading-relaxed" :class="expanded ? '' : 'line-clamp-3'">
                                    "{{ $t->content }}"
                                </p>
                                @if(strlen($t->content) > 180)
                                    <button @click="expanded = !expanded" class="text-brand-600 text-xs font-semibold mt-1.5 hover:text-brand-700 transition-colors" x-text="expanded ? 'Ver menos' : 'Ver mas'">Ver mas</button>
                                @endif
                            </div>
                        @endif

                        {{-- Video button --}}
                        @if($t->type === 'video' && $t->youtube_embed_url)
                            <button @click="$dispatch('open-video', { url: '{{ $t->youtube_embed_url }}', name: '{{ addslashes($t->name) }}' })"
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-50 text-brand-600 text-sm font-semibold hover:bg-brand-100 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                Ver experiencia
                            </button>
                        @endif

                        {{-- Location tag --}}
                        @if($t->location)
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                    <x-icon name="map-pin" class="w-3 h-3" /> {{ $t->location }}
                                </span>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-16 sm:py-20 bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">¿Listo para comenzar tu historia?</h2>
            <p class="mt-3 text-brand-200 text-lg">Contactanos y descubre por que nuestros clientes nos recomiendan.</p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('contacto') }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-brand-700 font-bold text-sm hover:bg-brand-50 transition-all shadow-lg hover:shadow-xl">
                    Contactar ahora
                </a>
                <a href="{{ route('propiedades.index') }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white/10 text-white font-semibold text-sm hover:bg-white/20 transition-all border border-white/20">
                    Ver propiedades
                </a>
            </div>
        </div>
    </section>

    {{-- Video Modal (supports vertical reels/shorts) --}}
    <div x-data="{ open: false, url: '', name: '' }"
         @open-video.window="open = true; url = $event.detail.url + '?autoplay=1&rel=0'; name = $event.detail.name"
         @keydown.escape.window="open = false; url = ''"
         x-show="open"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="display: none;">
        <div class="absolute inset-0 bg-black/80" @click="open = false; url = ''"></div>
        <div class="relative z-10 flex flex-col items-center" @click.away="open = false; url = ''">
            <button @click="open = false; url = ''" class="absolute -top-10 right-0 text-white/80 hover:text-white transition-colors z-20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="rounded-2xl overflow-hidden shadow-2xl bg-black" style="width: min(360px, 90vw); height: min(640px, 80vh);">
                <template x-if="open && url">
                    <iframe :src="url" class="w-full h-full" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </template>
            </div>
            <p class="text-center text-white/70 text-sm mt-3" x-text="name"></p>
        </div>
    </div>
@endsection
