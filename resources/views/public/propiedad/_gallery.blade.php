@php
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
@endphp

@if($photoCount > 0)
<style>
.gal { position: relative; border-radius: 16px; overflow: hidden; background: #f3f4f6; }
.gal-main { position: relative; aspect-ratio: 16/10; display: flex; }
.gal-img { position: absolute; inset: 0; opacity: 0; transition: opacity 0.4s; width: 100%; height: 100%; object-fit: cover; }
.gal-img.show { opacity: 1; z-index: 1; }
.gal-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.85); border: none; cursor: pointer; z-index: 10; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.gal-btn:hover { background: white; transform: translateY(-50%) scale(1.1); }
.gal-prev { left: 16px; }
.gal-next { right: 16px; }
.gal-cnt { position: absolute; top: 16px; left: 16px; background: rgba(0,0,0,0.5); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; z-index: 5; }
.gal-exp { position: absolute; top: 16px; right: 16px; width: 40px; height: 40px; border-radius: 50%; background: rgba(0,0,0,0.5); border: none; color: white; cursor: pointer; z-index: 5; transition: all 0.2s; }
.gal-exp:hover { background: rgba(0,0,0,0.7); }
.gal-thumbs { display: flex; gap: 8px; padding: 12px 16px; overflow-x: auto; background: white; border-top: 1px solid #e2e8f0; }
.gal-thumb { flex-shrink: 0; width: 70px; height: 52px; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; opacity: 0.6; transition: all 0.2s; }
.gal-thumb:hover { opacity: 0.9; }
.gal-thumb.act { border-color: #667eea; opacity: 1; }
.gal-thumb img { width: 100%; height: 100%; object-fit: cover; }
.gal-light { position: fixed; inset: 0; background: rgba(0,0,0,0.9); display: none; align-items: center; justify-content: center; z-index: 999; }
.gal-light.open { display: flex; }
.gal-light img { max-width: 90vw; max-height: 90vh; object-fit: contain; }
.gal-light-close { position: absolute; top: 20px; right: 20px; width: 40px; height: 40px; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 28px; cursor: pointer; border-radius: 50%; }
@media (max-width: 640px) { .gal-main { aspect-ratio: 4/3; } .gal-btn { width: 36px; height: 36px; } .gal-prev { left: 8px; } .gal-next { right: 8px; } }
</style>

<div class="gal">
    <div class="gal-main" id="galMain">
        @foreach($photos as $index => $photo)
        <img class="gal-img {{ $index === 0 ? 'show' : '' }}" src="{{ asset('storage/' . $photo->path) }}" alt="Foto {{ $index + 1 }}" data-idx="{{ $index }}">
        @endforeach
    </div>

    @if($photoCount > 1)
    <button class="gal-btn gal-prev" onclick="galPrev()">❮</button>
    <button class="gal-btn gal-next" onclick="galNext()">❯</button>
    @endif

    <div class="gal-cnt"><span id="galNum">1</span> / {{ $photoCount }}</div>
    <button class="gal-exp" onclick="galLight()" title="Expandir">⛶</button>
</div>

@if($photoCount > 1)
<div class="gal-thumbs" id="galThumbs">
    @foreach($photos as $index => $photo)
    <div class="gal-thumb {{ $index === 0 ? 'act' : '' }}" onclick="galShow({{ $index }})">
        <img src="{{ asset('storage/' . $photo->path) }}" alt="Thumb">
    </div>
    @endforeach
</div>
@endif

<div class="gal-light" id="galLight" onclick="this.classList.remove('open')">
    <button class="gal-light-close" onclick="event.stopPropagation(); document.getElementById('galLight').classList.remove('open')">×</button>
    <img id="galLightImg" src="" alt="Foto completa">
</div>

<script>
let galIdx = 0;
const galCount = {{ $photoCount }};

function galShow(n) {
    galIdx = (n + galCount) % galCount;
    document.querySelectorAll('#galMain .gal-img').forEach(el => el.classList.remove('show'));
    document.querySelectorAll('#galMain .gal-img')[galIdx].classList.add('show');
    document.querySelectorAll('.gal-thumb').forEach((el, i) => el.classList.toggle('act', i === galIdx));
    document.getElementById('galNum').textContent = galIdx + 1;
}

function galNext() { galShow(galIdx + 1); }
function galPrev() { galShow(galIdx - 1); }

function galLight() {
    const img = document.querySelectorAll('#galMain .gal-img')[galIdx];
    document.getElementById('galLightImg').src = img.src;
    document.getElementById('galLight').classList.add('open');
}

document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight') galNext();
    if (e.key === 'ArrowLeft') galPrev();
    if (e.key === 'Escape') document.getElementById('galLight').classList.remove('open');
});
</script>

@endif
