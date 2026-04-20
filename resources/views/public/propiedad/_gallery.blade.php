@php
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
    $hasPhotos = $photoCount > 0;
@endphp

@if($hasPhotos)
<style>
.pg-wrap { position: relative; border-radius: 16px; overflow: hidden; background: #f3f4f6; }
.pg-main { position: relative; aspect-ratio: 16/10; overflow: hidden; cursor: pointer; }
.pg-main img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.5s ease, transform 0.5s ease; transform: scale(1.04); }
.pg-main img.pg-active { opacity: 1; transform: scale(1); z-index: 1; }
.pg-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.85); backdrop-filter: blur(8px); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 3; transition: transform 0.2s, box-shadow 0.2s; }
.pg-btn:hover { transform: translateY(-50%) scale(1.1); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
.pg-btn:active { transform: translateY(-50%) scale(0.95); }
.pg-btn-prev { left: 14px; }
.pg-btn-next { right: 14px; }
.pg-counter { position: absolute; top: 14px; left: 14px; background: rgba(0,0,0,0.45); backdrop-filter: blur(8px); color: #fff; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; z-index: 3; letter-spacing: 0.5px; }
.pg-expand { position: absolute; top: 14px; right: 14px; width: 38px; height: 38px; border-radius: 50%; background: rgba(0,0,0,0.35); backdrop-filter: blur(8px); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 3; transition: background 0.2s, transform 0.2s; }
.pg-expand:hover { background: rgba(0,0,0,0.55); transform: scale(1.1); }
.pg-thumbs { display: flex; gap: 6px; padding: 10px 14px; overflow-x: auto; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
.pg-thumbs::-webkit-scrollbar { display: none; }
.pg-thumb { flex-shrink: 0; width: 64px; height: 48px; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: border-color 0.2s, transform 0.2s, opacity 0.2s; opacity: 0.6; }
.pg-thumb:hover { opacity: 0.9; transform: scale(1.05); }
.pg-thumb.pg-thumb-active { border-color: var(--color-primary, #667eea); opacity: 1; transform: scale(1.05); }
.pg-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pg-dots { position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 3; }
.pg-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.4); border: none; cursor: pointer; padding: 0; transition: all 0.3s; }
.pg-dot-active { width: 20px; border-radius: 4px; background: #fff; }
/* Lightbox */
.pg-lb { position: fixed; inset: 0; z-index: 100; background: rgba(0,0,0,0.92); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; }
.pg-lb img { max-height: 88vh; max-width: 92vw; object-fit: contain; border-radius: 10px; user-select: none; animation: pgFadeZoom 0.3s ease; }
@keyframes pgFadeZoom { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.pg-lb-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); border: none; cursor: pointer; padding: 12px; border-radius: 50%; transition: background 0.2s; color: rgba(255,255,255,0.7); }
.pg-lb-btn:hover { background: rgba(255,255,255,0.2); color: #fff; }
.pg-lb-close { position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.1); border: none; cursor: pointer; padding: 8px; border-radius: 50%; color: rgba(255,255,255,0.7); transition: background 0.2s; z-index: 10; }
.pg-lb-close:hover { background: rgba(255,255,255,0.2); color: #fff; }
.pg-lb-counter { position: absolute; top: 20px; left: 20px; color: rgba(255,255,255,0.6); font-size: 14px; font-weight: 600; z-index: 10; }
.pg-progress { position: absolute; bottom: 0; left: 0; height: 3px; background: var(--color-primary, #667eea); z-index: 3; transition: width 0.3s ease; border-radius: 0 2px 0 0; }
</style>

<div x-data="propertyGallery()" class="pg-wrap" x-intersect.once="$el.classList.add('animate-fade-in')">
    {{-- Main image area --}}
    <div class="pg-main" @click="openLightbox()">
        @foreach($photos as $i => $photo)
        <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description ?? $property->title }}" :class="current === {{ $i }} ? 'pg-active' : ''" loading="{{ $i < 2 ? 'eager' : 'lazy' }}">
        @endforeach

        {{-- Progress bar --}}
        @if($photoCount > 1)
        <div class="pg-progress" :style="'width: ' + ((current + 1) / {{ $photoCount }} * 100) + '%'"></div>
        @endif
    </div>

    @if($photoCount > 1)
    {{-- Nav buttons --}}
    <button @click.stop="prev()" class="pg-btn pg-btn-prev">
        <x-icon name="chevron-left" class="w-5 h-5" style="color: #333;" />
    </button>
    <button @click.stop="next()" class="pg-btn pg-btn-next">
        <x-icon name="chevron-right" class="w-5 h-5" style="color: #333;" />
    </button>
    @endif

    {{-- Counter --}}
    <div class="pg-counter"><span x-text="current + 1"></span> / {{ $photoCount }}</div>

    {{-- Expand --}}
    <button @click.stop="openLightbox()" class="pg-expand" title="Pantalla completa">
        <x-icon name="maximize-2" class="w-4 h-4" style="color: #fff;" />
    </button>

    {{-- Dots (mobile) --}}
    @if($photoCount > 1 && $photoCount <= 12)
    <div class="pg-dots">
        @for($i = 0; $i < $photoCount; $i++)
        <button @click.stop="goTo({{ $i }})" class="pg-dot" :class="current === {{ $i }} ? 'pg-dot-active' : ''"></button>
        @endfor
    </div>
    @endif

    {{-- Thumbnail strip --}}
    @if($photoCount > 1)
    <div class="pg-thumbs" x-ref="thumbs">
        @foreach($photos as $i => $photo)
        <button @click="goTo({{ $i }})" class="pg-thumb" :class="current === {{ $i }} ? 'pg-thumb-active' : ''">
            <img src="{{ asset('storage/' . $photo->path) }}" alt="" loading="lazy">
        </button>
        @endforeach
    </div>
    @endif

    {{-- Lightbox --}}
    <div x-show="lightbox" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="pg-lb" @click.self="closeLightbox()" @keydown.escape.window="closeLightbox()" @keydown.left.window="if(lightbox) prev()" @keydown.right.window="if(lightbox) next()">
        <button @click="closeLightbox()" class="pg-lb-close"><x-icon name="x" class="w-6 h-6" /></button>
        <div class="pg-lb-counter"><span x-text="current + 1"></span> / {{ $photoCount }}</div>
        @if($photoCount > 1)
        <button @click="prev()" class="pg-lb-btn" style="left: 16px;"><x-icon name="chevron-left" class="w-7 h-7" /></button>
        <button @click="next()" class="pg-lb-btn" style="right: 16px;"><x-icon name="chevron-right" class="w-7 h-7" /></button>
        @endif
        <img :src="photos[current]" :key="current">
    </div>
</div>

<script>
function propertyGallery() {
    return {
        current: 0,
        lightbox: false,
        total: {{ $photoCount }},
        photos: @json($photos->map(fn($p) => asset('storage/' . $p->path))->values()),
        touchStartX: 0,
        autoplayTimer: null,

        init() {
            // Touch/swipe
            var main = this.$el.querySelector('.pg-main');
            if (main) {
                main.addEventListener('touchstart', (e) => { this.touchStartX = e.touches[0].clientX; this.stopAutoplay(); }, { passive: true });
                main.addEventListener('touchend', (e) => {
                    var diff = this.touchStartX - e.changedTouches[0].clientX;
                    if (Math.abs(diff) > 40) { diff > 0 ? this.next() : this.prev(); }
                }, { passive: true });
            }
            // Autoplay
            if (this.total > 1) this.startAutoplay();
        },

        goTo(i) {
            this.current = i;
            this.scrollThumb();
            this.restartAutoplay();
        },
        prev() { this.current = (this.current - 1 + this.total) % this.total; this.scrollThumb(); this.restartAutoplay(); },
        next() { this.current = (this.current + 1) % this.total; this.scrollThumb(); this.restartAutoplay(); },

        scrollThumb() {
            var thumbs = this.$refs.thumbs;
            if (!thumbs) return;
            var active = thumbs.children[this.current];
            if (active) active.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        },

        startAutoplay() { this.autoplayTimer = setInterval(() => { this.current = (this.current + 1) % this.total; this.scrollThumb(); }, 5000); },
        stopAutoplay() { if (this.autoplayTimer) { clearInterval(this.autoplayTimer); this.autoplayTimer = null; } },
        restartAutoplay() { this.stopAutoplay(); this.startAutoplay(); },

        openLightbox() { this.lightbox = true; this.stopAutoplay(); document.body.style.overflow = 'hidden'; },
        closeLightbox() { this.lightbox = false; this.startAutoplay(); document.body.style.overflow = ''; },
    };
}
</script>
@else
<div style="aspect-ratio: 16/10; border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f0f4ff, #e0e7ff);">
    <x-icon name="home" class="w-16 h-16" style="color: #a5b4fc;" />
</div>
@endif
