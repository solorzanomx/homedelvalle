@php
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
@endphp

@if($photoCount > 0)
<style>
.gallery-wrap { position: relative; border-radius: 20px; overflow: hidden; background: #f3f4f6; box-shadow: 0 20px 60px rgba(0,0,0,0.12); }
.gallery-main { position: relative; aspect-ratio: 16/10; display: flex; background: #000; }
.gallery-img { position: absolute; inset: 0; opacity: 0; transition: opacity 0.5s ease-in-out; width: 100%; height: 100%; object-fit: cover; }
.gallery-img.active { opacity: 1; z-index: 2; }
.gallery-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.9); border: none; cursor: pointer; z-index: 10; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #1e293b; font-weight: 300; }
.gallery-btn:hover { background: #fff; transform: translateY(-50%) scale(1.15); box-shadow: 0 12px 32px rgba(0,0,0,0.16); }
.gallery-btn:active { transform: translateY(-50%) scale(0.95); }
.gallery-prev { left: 20px; }
.gallery-next { right: 20px; }
.gallery-info { position: absolute; bottom: 20px; left: 20px; background: rgba(30,41,59,0.8); backdrop-filter: blur(12px); color: white; padding: 10px 18px; border-radius: 24px; font-size: 13px; font-weight: 600; z-index: 5; letter-spacing: 0.5px; }
.gallery-expand { position: absolute; top: 20px; right: 20px; width: 48px; height: 48px; border-radius: 50%; background: rgba(30,41,59,0.7); backdrop-filter: blur(12px); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 5; transition: all 0.3s; color: white; font-size: 22px; }
.gallery-expand:hover { background: rgba(30,41,59,0.9); transform: scale(1.12); }
.gallery-counter { position: absolute; top: 20px; left: 20px; background: rgba(30,41,59,0.8); backdrop-filter: blur(12px); color: white; padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; z-index: 5; letter-spacing: 0.5px; }
.gallery-thumbs { display: none; gap: 10px; padding: 16px 18px; overflow-x: auto; scroll-behavior: smooth; background: white; border-top: 1px solid #e2e8f0; scrollbar-width: thin; }
.gallery-thumbs::-webkit-scrollbar { height: 4px; }
.gallery-thumbs::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
.gallery-thumb { flex-shrink: 0; width: 80px; height: 60px; border-radius: 10px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); opacity: 0.65; }
.gallery-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gallery-thumb:hover { opacity: 0.9; transform: scale(1.08); }
.gallery-thumb.active { border-color: var(--color-primary, #667eea); opacity: 1; }
.gallery-lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.95); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.3s ease-in-out; }
.gallery-lightbox.open { display: flex; }
.gallery-lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 8px; animation: zoomIn 0.3s ease-in-out; }
.gallery-lb-close { position: absolute; top: 20px; right: 20px; width: 44px; height: 44px; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); border: none; border-radius: 50%; color: white; font-size: 28px; cursor: pointer; z-index: 10000; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
.gallery-lb-close:hover { background: rgba(255,255,255,0.25); }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes zoomIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
@media (max-width: 768px) { .gallery-main { aspect-ratio: 4/3; } .gallery-btn { width: 44px; height: 44px; font-size: 20px; } .gallery-prev { left: 12px; } .gallery-next { right: 12px; } .gallery-counter { font-size: 11px; padding: 6px 12px; top: 12px; left: 12px; } .gallery-expand { width: 44px; height: 44px; font-size: 18px; top: 12px; right: 12px; } }
</style>

<div class="gallery-wrap">
    <div class="gallery-main" id="galMain" onmousedown="galTouchStart(event)" ontouchstart="galTouchStart(event)">
        @foreach($photos as $index => $photo)
        <img class="gallery-img {{ $index === 0 ? 'active' : '' }}"
             src="{{ asset('storage/' . $photo->path) }}"
             alt="{{ $photo->description ?? $property->title }}"
             loading="{{ $index < 2 ? 'eager' : 'lazy' }}">
        @endforeach
    </div>

    @if($photoCount > 1)
    <button class="gallery-btn gallery-prev" onclick="galPrev()" title="Anterior">‹</button>
    <button class="gallery-btn gallery-next" onclick="galNext()" title="Siguiente">›</button>
    @endif

    <div class="gallery-counter"><span id="galIdx">1</span> / {{ $photoCount }}</div>
    <button class="gallery-expand" onclick="galFullscreen()" title="Pantalla completa">⛶</button>
</div>

@if($photoCount > 1)
<div class="gallery-thumbs" id="galThumbs">
    @foreach($photos as $index => $photo)
    <div class="gallery-thumb {{ $index === 0 ? 'active' : '' }}"
         onclick="galTo({{ $index }})"
         onmouseover="galPreload({{ $index }})">
        <img src="{{ asset('storage/' . $photo->path) }}" alt="Miniatura" loading="lazy">
    </div>
    @endforeach
</div>
@endif

<div class="gallery-lightbox" id="galLightbox" onclick="this.classList.remove('open')">
    <button class="gallery-lb-close" onclick="event.stopPropagation(); galLightbox.classList.remove('open')">×</button>
    <img id="galLbImg" src="" alt="Foto completa">
</div>

<script>
let galCurrent = 0;
const galTotal = {{ $photoCount }};
let galTouchX = 0;

function galShow(n) {
    galCurrent = ((n % galTotal) + galTotal) % galTotal;
    document.querySelectorAll('.gallery-img').forEach((el, i) => {
        el.classList.toggle('active', i === galCurrent);
    });
    document.querySelectorAll('.gallery-thumb').forEach((el, i) => {
        el.classList.toggle('active', i === galCurrent);
    });
    document.getElementById('galIdx').textContent = galCurrent + 1;
    const img = document.querySelectorAll('.gallery-img')[galCurrent];
    document.getElementById('galLbImg').src = img.src;
}

function galNext() { galShow(galCurrent + 1); }
function galPrev() { galShow(galCurrent - 1); }
function galTo(n) { galShow(n); }
function galPreload(n) { const img = document.querySelectorAll('.gallery-img')[n]; if(img) img.src = img.src; }

function galFullscreen() {
    const img = document.querySelectorAll('.gallery-img')[galCurrent];
    document.getElementById('galLbImg').src = img.src;
    document.getElementById('galLightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

document.getElementById('galLightbox').addEventListener('click', () => {
    document.body.style.overflow = '';
});

// Touch/Swipe
function galTouchStart(e) {
    galTouchX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
}

document.getElementById('galMain').addEventListener('touchend', (e) => {
    const endX = e.changedTouches[0].clientX;
    const diff = galTouchX - endX;
    if (Math.abs(diff) > 50) {
        diff > 0 ? galNext() : galPrev();
    }
});

// Keyboard
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') galNext();
    if (e.key === 'ArrowLeft') galPrev();
    if (e.key === 'Escape') document.getElementById('galLightbox').classList.remove('open');
});

// Init
galShow(0);
</script>

@endif
