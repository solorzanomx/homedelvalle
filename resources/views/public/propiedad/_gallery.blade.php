@php
    // Extract YouTube video ID
    $ytId = null;
    if (!empty($property->youtube_url)) {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/', $property->youtube_url, $m);
        $ytId = $m[1] ?? null;
    }

    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
@endphp

<style>
/* ── Shared wrapper ── */
.gallery-wrap { position: relative; border-radius: 20px; overflow: hidden; background: #0f172a; box-shadow: 0 20px 60px rgba(0,0,0,0.12); }

/* ── YouTube hero ── */
.yt-hero { position: relative; aspect-ratio: 16/9; background: #000; }
.yt-hero iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; display: block; }

/* ── Photo carousel (no-video mode) ── */
.gallery-main { position: relative; aspect-ratio: 16/10; display: flex; background: #000; }
.gallery-img { position: absolute; inset: 0; opacity: 0; transition: opacity 0.5s ease-in-out; width: 100%; height: 100%; object-fit: cover; }
.gallery-img.active { opacity: 1; z-index: 2; }
.gallery-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.9); border: none; cursor: pointer; z-index: 10; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #1e293b; font-weight: 300; }
.gallery-btn:hover { background: #fff; transform: translateY(-50%) scale(1.15); box-shadow: 0 12px 32px rgba(0,0,0,0.16); }
.gallery-btn:active { transform: translateY(-50%) scale(0.95); }
.gallery-prev { left: 20px; }
.gallery-next { right: 20px; }
.gallery-counter { position: absolute; top: 20px; left: 20px; background: rgba(30,41,59,0.8); backdrop-filter: blur(12px); color: white; padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; z-index: 5; letter-spacing: 0.5px; }
.gallery-expand { position: absolute; top: 20px; right: 20px; width: 48px; height: 48px; border-radius: 50%; background: rgba(30,41,59,0.7); backdrop-filter: blur(12px); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 5; transition: all 0.3s; color: white; font-size: 22px; }
.gallery-expand:hover { background: rgba(30,41,59,0.9); transform: scale(1.12); }

/* ── Thumbnail strip (shared: used in both modes) ── */
.gallery-thumbs { display: flex; gap: 10px; padding: 16px 18px; overflow-x: auto; scroll-behavior: smooth; background: white; border-top: 1px solid #e2e8f0; scrollbar-width: thin; scrollbar-color: #cbd5e1 #f8fafc; }
.gallery-thumbs::-webkit-scrollbar { height: 4px; }
.gallery-thumbs::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
.gallery-thumb { flex-shrink: 0; width: 80px; height: 60px; border-radius: 10px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); opacity: 0.65; }
.gallery-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gallery-thumb:hover { opacity: 0.9; transform: scale(1.08); }
.gallery-thumb.active { border-color: var(--color-primary, #667eea); opacity: 1; }

/* ── Thumbnail strip label (YouTube mode) ── */
.gallery-thumbs-label { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; padding: 12px 18px 0; background: white; border-top: 1px solid #e2e8f0; }

/* ── Lightbox ── */
.gallery-lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.95); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 9999; }
.gallery-lightbox.open { display: flex; }
.gallery-lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 8px; }
.gallery-lb-close { position: absolute; top: 20px; right: 20px; width: 44px; height: 44px; background: rgba(255,255,255,0.15); border: none; border-radius: 50%; color: white; font-size: 28px; cursor: pointer; z-index: 10000; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.gallery-lb-close:hover { background: rgba(255,255,255,0.25); }
.gallery-lb-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 50px; height: 50px; background: rgba(255,255,255,0.12); border: none; border-radius: 50%; color: white; font-size: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; z-index: 10000; }
.gallery-lb-nav:hover { background: rgba(255,255,255,0.22); }
.gallery-lb-prev { left: 20px; }
.gallery-lb-next { right: 20px; }
.gallery-lb-counter { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 600; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .gallery-main { aspect-ratio: 4/3; }
    .gallery-btn { width: 44px; height: 44px; font-size: 20px; }
    .gallery-prev { left: 12px; }
    .gallery-next { right: 12px; }
    .gallery-counter { font-size: 11px; padding: 6px 12px; top: 12px; left: 12px; }
    .gallery-expand { width: 44px; height: 44px; font-size: 18px; top: 12px; right: 12px; }
    .gallery-thumb { width: 68px; height: 52px; }
}
@media (max-width: 480px) {
    .gallery-thumbs { padding: 12px 14px; gap: 8px; }
    .gallery-thumb { width: 60px; height: 46px; }
}
</style>

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     MODE A — YouTube hero + photo thumbnail strip
     ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
@if($ytId)

<div class="gallery-wrap">
    {{-- YouTube iframe as hero --}}
    <div class="yt-hero">
        <iframe
            src="https://www.youtube.com/embed/{{ $ytId }}?rel=0&modestbranding=1&playsinline=1"
            title="{{ $property->title }}"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>
    </div>

    {{-- Photo thumbnails below video --}}
    @if($photoCount > 0)
    <div class="gallery-thumbs-label">Fotos de la propiedad</div>
    <div class="gallery-thumbs">
        @foreach($photos as $index => $photo)
        <div class="gallery-thumb" onclick="galLbOpen({{ $index }})">
            <img
                src="{{ asset('storage/' . $photo->path) }}"
                alt="{{ $photo->description ?? 'Foto ' . ($index + 1) }}"
                loading="{{ $index < 4 ? 'eager' : 'lazy' }}"
            >
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     MODE B — Photo carousel (no video)
     ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
@elseif($photoCount > 0)

<div class="gallery-wrap">
    <div class="gallery-main" id="galMain">
        @foreach($photos as $index => $photo)
        <img class="gallery-img {{ $index === 0 ? 'active' : '' }}"
             src="{{ asset('storage/' . $photo->path) }}"
             alt="{{ $photo->description ?? $property->title }}"
             loading="{{ $index < 2 ? 'eager' : 'lazy' }}"
             onclick="galFullscreen()">
        @endforeach
    </div>

    @if($photoCount > 1)
    <button class="gallery-btn gallery-prev" onclick="galPrev()" title="Anterior">‹</button>
    <button class="gallery-btn gallery-next" onclick="galNext()" title="Siguiente">›</button>
    @endif

    <div class="gallery-counter"><span id="galIdx">1</span> / {{ $photoCount }}</div>
    <button class="gallery-expand" onclick="galFullscreen()" title="Ver en grande">⛶</button>
</div>

@if($photoCount > 1)
<div class="gallery-thumbs">
    @foreach($photos as $index => $photo)
    <div class="gallery-thumb {{ $index === 0 ? 'active' : '' }}"
         onclick="galTo({{ $index }})">
        <img src="{{ asset('storage/' . $photo->path) }}" alt="Miniatura {{ $index + 1 }}" loading="lazy">
    </div>
    @endforeach
</div>
@endif

@endif

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     Lightbox (used in both modes when photos exist)
     ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
@if($photoCount > 0)
<div class="gallery-lightbox" id="galLightbox" onclick="if(event.target===this)galLbClose()">
    <button class="gallery-lb-close" onclick="galLbClose()">×</button>
    @if($photoCount > 1)
    <button class="gallery-lb-nav gallery-lb-prev" onclick="galLbNav(-1)">‹</button>
    <button class="gallery-lb-nav gallery-lb-next" onclick="galLbNav(1)">›</button>
    @endif
    <img id="galLbImg" src="" alt="Foto completa">
    <div class="gallery-lb-counter"><span id="galLbIdx">1</span> / {{ $photoCount }}</div>
</div>

<script>
(function() {
    // Photo URLs
    const galSrcs = @json($photos->map(fn($p) => asset('storage/' . $p->path))->values());
    const galTotal = galSrcs.length;
    let galCurrent = 0;

    // ── Carousel (Mode B only) ──
    function galShow(n) {
        galCurrent = ((n % galTotal) + galTotal) % galTotal;
        document.querySelectorAll('.gallery-img').forEach((el, i) => el.classList.toggle('active', i === galCurrent));
        document.querySelectorAll('.gallery-thumb').forEach((el, i) => el.classList.toggle('active', i === galCurrent));
        const idx = document.getElementById('galIdx');
        if (idx) idx.textContent = galCurrent + 1;
        document.getElementById('galLbImg').src = galSrcs[galCurrent];
    }

    window.galNext = () => galShow(galCurrent + 1);
    window.galPrev = () => galShow(galCurrent - 1);
    window.galTo   = (n) => galShow(n);

    // Swipe / drag on carousel
    const galMain = document.getElementById('galMain');
    let galTouchX = 0;
    if (galMain) {
        galMain.addEventListener('mousedown',  e => { galTouchX = e.clientX; });
        galMain.addEventListener('touchstart', e => { galTouchX = e.touches[0].clientX; }, { passive: true });
        galMain.addEventListener('touchend',   e => {
            const diff = galTouchX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) diff > 0 ? galNext() : galPrev();
        });
    }

    // ── Lightbox ──
    function galLbShow(n) {
        galCurrent = ((n % galTotal) + galTotal) % galTotal;
        document.getElementById('galLbImg').src = galSrcs[galCurrent];
        const lbIdx = document.getElementById('galLbIdx');
        if (lbIdx) lbIdx.textContent = galCurrent + 1;
        // Sync carousel if in Mode B
        document.querySelectorAll('.gallery-img').forEach((el, i) => el.classList.toggle('active', i === galCurrent));
        document.querySelectorAll('.gallery-thumb').forEach((el, i) => el.classList.toggle('active', i === galCurrent));
        const idx = document.getElementById('galIdx');
        if (idx) idx.textContent = galCurrent + 1;
    }

    window.galLbOpen = function(n) {
        galLbShow(n);
        document.getElementById('galLightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.galFullscreen = function() { galLbOpen(galCurrent); };

    window.galLbClose = function() {
        document.getElementById('galLightbox').classList.remove('open');
        document.body.style.overflow = '';
    };

    window.galLbNav = function(dir) { galLbShow(galCurrent + dir); };

    // Keyboard
    document.addEventListener('keydown', e => {
        const lb = document.getElementById('galLightbox');
        if (!lb.classList.contains('open')) {
            if (e.key === 'ArrowRight') galNext?.();
            if (e.key === 'ArrowLeft')  galPrev?.();
            return;
        }
        if (e.key === 'ArrowRight') galLbNav(1);
        if (e.key === 'ArrowLeft')  galLbNav(-1);
        if (e.key === 'Escape')     galLbClose();
    });

    // Init carousel
    if (document.getElementById('galMain')) galShow(0);
})();
</script>
@endif
