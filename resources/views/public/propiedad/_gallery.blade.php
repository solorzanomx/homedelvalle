@php
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
    $hasPhotos = $photoCount > 0;
@endphp

<div x-data="propertyCarousel({{ $photoCount }})" class="relative rounded-2xl bg-gray-100 shadow-premium" style="overflow: hidden; aspect-ratio: 16/10;" x-intersect.once="$el.classList.add('animate-fade-in')">
    @if($hasPhotos)
        {{-- Slides --}}
        <div style="display: flex; height: 100%; transition: transform 0.3s ease-out;" :style="'transform: translateX(-' + (current * 100) + '%)'">
            @foreach($photos as $photo)
            <div style="min-width: 100%; height: 100%; flex-shrink: 0;">
                <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description ?? $property->title }}" style="width: 100%; height: 100%; object-fit: cover; display: block;" loading="{{ $loop->index < 2 ? 'eager' : 'lazy' }}">
            </div>
            @endforeach
        </div>

        @if($photoCount > 1)
        {{-- Prev --}}
        <button @click="prev()" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.7); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 2;">
            <x-icon name="chevron-left" class="w-5 h-5" style="color: #333;" />
        </button>
        {{-- Next --}}
        <button @click="next()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.7); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 2;">
            <x-icon name="chevron-right" class="w-5 h-5" style="color: #333;" />
        </button>

        {{-- Counter --}}
        <div style="position: absolute; bottom: 12px; right: 12px; background: rgba(0,0,0,0.5); color: #fff; font-size: 12px; font-weight: 500; padding: 3px 10px; border-radius: 8px; z-index: 2;">
            <span x-text="current + 1"></span> / {{ $photoCount }}
        </div>

        {{-- Dots --}}
        @if($photoCount <= 10)
        <div style="position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 2;">
            @for($i = 0; $i < $photoCount; $i++)
            <button @click="current = {{ $i }}" style="border: none; cursor: pointer; border-radius: 50%; padding: 0; height: 8px; transition: all 0.2s;" :style="current === {{ $i }} ? 'width: 16px; background: #fff; border-radius: 4px;' : 'width: 8px; background: rgba(255,255,255,0.5);'"></button>
            @endfor
        </div>
        @endif
        @endif

        {{-- Fullscreen --}}
        <button @click="openLightbox()" style="position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.7); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 2;" title="Pantalla completa">
            <x-icon name="maximize-2" class="w-4 h-4" style="color: #333;" />
        </button>
    @else
        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f0f4ff, #e0e7ff);">
            <x-icon name="home" class="w-16 h-16" style="color: #a5b4fc;" />
        </div>
    @endif

    {{-- Lightbox --}}
    @if($photoCount > 0)
    <div x-show="lightbox" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.9);" @click.self="closeLightbox()" @keydown.escape.window="closeLightbox()" @keydown.left.window="if(lightbox) prev()" @keydown.right.window="if(lightbox) next()">
        <button @click="closeLightbox()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; color: rgba(255,255,255,0.7); cursor: pointer; z-index: 10;">
            <x-icon name="x" class="w-7 h-7" />
        </button>
        <div style="position: absolute; top: 16px; left: 16px; color: rgba(255,255,255,0.6); font-size: 14px; z-index: 10;">
            <span x-text="current + 1"></span> / {{ $photoCount }}
        </div>
        <button @click="prev()" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255,255,255,0.6); cursor: pointer; padding: 8px; z-index: 10;">
            <x-icon name="chevron-left" class="w-8 h-8" />
        </button>
        <img :src="photos[current]" style="max-height: 85vh; max-width: 90vw; object-fit: contain; border-radius: 8px; user-select: none;">
        <button @click="next()" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255,255,255,0.6); cursor: pointer; padding: 8px; z-index: 10;">
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
