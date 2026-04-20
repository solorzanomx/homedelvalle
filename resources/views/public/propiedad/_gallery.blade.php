@php
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
    $hasPhotos = $photoCount > 0;
@endphp

<div x-data="propertyCarousel({{ $photoCount }})" class="relative aspect-[16/10] rounded-2xl overflow-hidden bg-gray-100 shadow-premium group" x-intersect.once="$el.classList.add('animate-fade-in')">
    @if($hasPhotos)
        {{-- Slides container --}}
        <div class="flex h-full transition-transform duration-300 ease-out" :style="'transform: translateX(-' + (current * 100) + '%)'">
            @foreach($photos as $photo)
            <div class="w-full h-full flex-shrink-0">
                <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description ?? $property->title }}" class="w-full h-full object-cover" loading="{{ $loop->index < 2 ? 'eager' : 'lazy' }}">
            </div>
            @endforeach
        </div>

        @if($photoCount > 1)
        {{-- Prev/Next --}}
        <button @click="prev()" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm text-gray-700 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-white">
            <x-icon name="chevron-left" class="w-5 h-5" />
        </button>
        <button @click="next()" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm text-gray-700 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-white">
            <x-icon name="chevron-right" class="w-5 h-5" />
        </button>

        {{-- Counter --}}
        <div class="absolute bottom-3 right-3 bg-black/50 backdrop-blur-sm text-white text-xs font-medium px-2.5 py-1 rounded-lg">
            <span x-text="current + 1"></span> / {{ $photoCount }}
        </div>

        {{-- Dots --}}
        @if($photoCount <= 10)
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
            @for($i = 0; $i < $photoCount; $i++)
            <button @click="current = {{ $i }}" class="w-2 h-2 rounded-full transition-all" :class="current === {{ $i }} ? 'bg-white w-4' : 'bg-white/50'"></button>
            @endfor
        </div>
        @endif
        @endif

        {{-- Lightbox on click --}}
        <button @click="openLightbox()" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm text-gray-700 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-white" title="Ver en pantalla completa">
            <x-icon name="maximize-2" class="w-4 h-4" />
        </button>
    @else
        <div class="w-full h-full bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center">
            <x-icon name="home" class="w-16 h-16 text-brand-300" />
        </div>
    @endif

    {{-- Lightbox --}}
    @if($photoCount > 0)
    <div x-show="lightbox" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm" @click.self="closeLightbox()" @keydown.escape.window="closeLightbox()" @keydown.left.window="if(lightbox) prev()" @keydown.right.window="if(lightbox) next()">
        <button @click="closeLightbox()" class="absolute top-4 right-4 text-white/70 hover:text-white transition-colors z-10">
            <x-icon name="x" class="w-7 h-7" />
        </button>
        <div class="absolute top-4 left-4 text-white/60 text-sm font-medium z-10">
            <span x-text="current + 1"></span> / {{ $photoCount }}
        </div>
        <button @click="prev()" class="absolute left-3 sm:left-6 text-white/60 hover:text-white transition-colors z-10 p-2">
            <x-icon name="chevron-left" class="w-8 h-8" />
        </button>
        <img :src="photos[current]" class="max-h-[85vh] max-w-[90vw] object-contain rounded-lg shadow-2xl select-none">
        <button @click="next()" class="absolute right-3 sm:right-6 text-white/60 hover:text-white transition-colors z-10 p-2">
            <x-icon name="chevron-right" class="w-8 h-8" />
        </button>
    </div>
    @endif
</div>

@if($hasPhotos)
<script>
function propertyCarousel(total) {
    return {
        current: 0,
        lightbox: false,
        total: total,
        photos: @json($photos->map(fn($p) => asset('storage/' . $p->path))->values()),
        touchStartX: 0,
        init() {
            this.$el.addEventListener('touchstart', (e) => { this.touchStartX = e.touches[0].clientX; }, { passive: true });
            this.$el.addEventListener('touchend', (e) => {
                var diff = this.touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) { diff > 0 ? this.next() : this.prev(); }
            }, { passive: true });
        },
        prev() { this.current = (this.current - 1 + this.total) % this.total; },
        next() { this.current = (this.current + 1) % this.total; },
        openLightbox() { this.lightbox = true; document.body.style.overflow = 'hidden'; },
        closeLightbox() { this.lightbox = false; document.body.style.overflow = ''; },
    };
}
</script>
@endif
