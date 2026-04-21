@php
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
@endphp

@if($photoCount > 0)
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
.property-gallery { position: relative; border-radius: 20px; overflow: hidden; background: #f3f4f6; box-shadow: 0 20px 60px rgba(0,0,0,0.08); }
.property-gallery .swiper { aspect-ratio: 16/10; width: 100%; }
.property-gallery img { width: 100%; height: 100%; object-fit: cover; display: block; }

.gallery-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.9); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; transition: all 0.3s; color: #1e293b; box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.gallery-btn:hover { background: rgba(255,255,255,1); transform: translateY(-50%) scale(1.1); }
.gallery-btn svg { width: 24px; height: 24px; stroke-width: 2.5; }
.gallery-btn.prev { left: 20px; }
.gallery-btn.next { right: 20px; }

.gallery-counter { position: absolute; top: 20px; left: 20px; background: rgba(30,41,59,0.75); backdrop-filter: blur(12px); color: white; font-size: 13px; font-weight: 600; padding: 8px 16px; border-radius: 24px; z-index: 10; }
.gallery-expand { position: absolute; top: 20px; right: 20px; width: 44px; height: 44px; border-radius: 50%; background: rgba(30,41,59,0.6); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; color: white; transition: all 0.3s; }
.gallery-expand:hover { background: rgba(30,41,59,0.85); transform: scale(1.08); }
.gallery-expand svg { width: 20px; height: 20px; }

.gallery-thumbs { display: flex; gap: 10px; padding: 16px 20px; overflow-x: auto; scroll-behavior: smooth; background: white; border-top: 1px solid #e2e8f0; }
.gallery-thumb { flex-shrink: 0; width: 80px; height: 60px; border-radius: 10px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.3s; opacity: 0.6; }
.gallery-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gallery-thumb:hover { opacity: 0.85; transform: scale(1.05); }
.gallery-thumb.active { border-color: var(--color-primary, #667eea); opacity: 1; }

@media (max-width: 768px) {
    .gallery-btn { width: 40px; height: 40px; }
    .gallery-btn svg { width: 20px; height: 20px; }
    .gallery-btn.prev { left: 12px; }
    .gallery-btn.next { right: 12px; }
    .property-gallery .swiper { aspect-ratio: 4/3; }
}
</style>

<div class="property-gallery">
    <div class="swiper" id="propGallerySwiper">
        <div class="swiper-wrapper">
            @foreach($photos as $photo)
            <div class="swiper-slide" data-fancybox="gallery" data-src="{{ asset('storage/' . $photo->path) }}">
                <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description ?? $property->title }}" loading="lazy">
            </div>
            @endforeach
        </div>
    </div>

    @if($photoCount > 1)
    <button class="gallery-btn prev" onclick="window.propGallerySwiper.slidePrev()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <button class="gallery-btn next" onclick="window.propGallerySwiper.slideNext()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>
    @endif

    <div class="gallery-counter"><span id="galleryCount">1</span> / {{ $photoCount }}</div>
    <button class="gallery-expand" onclick="document.querySelector('[data-fancybox=gallery]')?.click()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
    </button>
</div>

@if($photoCount > 1)
<div class="gallery-thumbs">
    @foreach($photos as $index => $photo)
    <div class="gallery-thumb {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}" onclick="window.propGallerySwiper.slideToLoop(this.dataset.index)">
        <img src="{{ asset('storage/' . $photo->path) }}" alt="Thumb {{ $index + 1 }}" loading="lazy">
    </div>
    @endforeach
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox.umd.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox.css" />

<script>
function initGallery() {
    if (!window.Swiper) {
        setTimeout(initGallery, 100);
        return;
    }

    window.propGallerySwiper = new Swiper('#propGallerySwiper', {
        loop: true,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: { delay: 5000, disableOnInteraction: false },
        speed: 800,
        on: {
            slideChange: function() {
                document.getElementById('galleryCount').textContent = this.realIndex + 1;
                document.querySelectorAll('.gallery-thumb').forEach((el, i) => {
                    el.classList.toggle('active', i === this.realIndex);
                });
            }
        }
    });

    if (window.Fancybox) {
        Fancybox.bind('[data-fancybox="gallery"]', {
            on: {
                reveal: () => document.body.style.overflow = 'hidden',
                done: () => document.body.style.overflow = ''
            }
        });
    }
}

initGallery();
</script>

@endif
