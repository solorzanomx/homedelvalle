@php
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
    $hasPhotos = $photoCount > 0;
@endphp

@if($hasPhotos)
{{-- Cargar CSS de Swiper --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    /* ========== GALERÍA PRINCIPAL ========== */
    .gallery-container {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        background: #f3f4f6;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
    }

    .swiper-main {
        aspect-ratio: 16/10;
        width: 100%;
        --swiper-navigation-size: 24px;
    }

    .swiper-main img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* ========== NAVEGACIÓN ========== */
    .gallery-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(12px);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        color: #1e293b;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .gallery-nav-btn:hover {
        background: rgba(255,255,255,1);
        transform: translateY(-50%) scale(1.12);
        box-shadow: 0 12px 32px rgba(0,0,0,0.15);
    }

    .gallery-nav-btn:active {
        transform: translateY(-50%) scale(0.95);
    }

    .gallery-nav-btn.prev { left: 20px; }
    .gallery-nav-btn.next { right: 20px; }

    .gallery-nav-btn svg {
        width: 24px;
        height: 24px;
        stroke-width: 2.5;
    }

    /* ========== CONTADOR Y EXPAND ========== */
    .gallery-counter {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(30,41,59,0.75);
        backdrop-filter: blur(12px);
        color: white;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 24px;
        z-index: 10;
        letter-spacing: 0.5px;
    }

    .gallery-expand-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(30,41,59,0.6);
        backdrop-filter: blur(12px);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.3s ease;
        color: white;
    }

    .gallery-expand-btn:hover {
        background: rgba(30,41,59,0.85);
        transform: scale(1.08);
    }

    .gallery-expand-btn svg {
        width: 20px;
        height: 20px;
    }

    /* ========== MINIATURAS ========== */
    .gallery-thumbnails {
        display: flex;
        gap: 10px;
        padding: 16px 20px;
        overflow-x: auto;
        scroll-behavior: smooth;
        background: white;
        border-top: 1px solid #e2e8f0;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .gallery-thumbnails::-webkit-scrollbar {
        height: 4px;
    }

    .gallery-thumbnails::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    .gallery-thumbnails::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    .gallery-thumbnails::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .gallery-thumb {
        flex-shrink: 0;
        width: 80px;
        height: 60px;
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        background: #f1f5f9;
        opacity: 0.6;
    }

    .gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .gallery-thumb:hover {
        opacity: 0.85;
        transform: scale(1.05);
    }

    .gallery-thumb.active {
        border-color: var(--color-primary, #667eea);
        opacity: 1;
    }

    /* ========== LIGHTBOX ========== */
    .fancybox__container {
        z-index: 99999 !important; /* Asegura que esté por encima de TODO */
    }

    .fancybox__backdrop {
        background: rgba(0,0,0,0.95) !important;
        backdrop-filter: blur(8px) !important;
    }

    .fancybox__image {
        border-radius: 8px;
    }

    .fancybox__button {
        color: white !important;
        opacity: 0.85;
        transition: opacity 0.3s;
    }

    .fancybox__button:hover {
        opacity: 1;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
        .gallery-nav-btn {
            width: 40px;
            height: 40px;
        }

        .gallery-nav-btn.prev { left: 12px; }
        .gallery-nav-btn.next { right: 12px; }

        .gallery-nav-btn svg {
            width: 20px;
            height: 20px;
        }

        .gallery-counter {
            font-size: 12px;
            padding: 6px 12px;
            top: 12px;
            left: 12px;
        }

        .gallery-expand-btn {
            width: 40px;
            height: 40px;
            top: 12px;
            right: 12px;
        }

        .gallery-expand-btn svg {
            width: 18px;
            height: 18px;
        }

        .gallery-thumbnails {
            padding: 12px 16px;
            gap: 8px;
        }

        .gallery-thumb {
            width: 70px;
            height: 52px;
        }
    }

    @media (max-width: 480px) {
        .swiper-main {
            aspect-ratio: 4/3;
        }

        .gallery-thumbnails {
            display: none; /* En móvil muy pequeño, mostrar solo si hay espacio */
        }
    }
</style>

{{-- GALERÍA PRINCIPAL --}}
<div class="gallery-container" x-intersect.once="$el.classList.add('animate-fade-in')">
    {{-- Swiper: Galería deslizable --}}
    <div class="swiper swiper-main" id="propertyGallerySwiper">
        <div class="swiper-wrapper">
            @foreach($photos as $index => $photo)
            <div class="swiper-slide">
                <img
                    src="{{ asset('storage/' . $photo->path) }}"
                    alt="{{ $photo->description ?? $property->title }}"
                    loading="{{ $index < 2 ? 'eager' : 'lazy' }}"
                    data-src="{{ asset('storage/' . $photo->path) }}"
                    data-fancybox="gallery"
                    data-caption="{{ $photo->description ?? $property->title }}"
                >
            </div>
            @endforeach
        </div>
    </div>

    {{-- Navegación (FUERA del swiper) --}}
    @if($photoCount > 1)
    <button class="gallery-nav-btn prev" aria-label="Anterior">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </button>
    <button class="gallery-nav-btn next" aria-label="Siguiente">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
    </button>
    @endif

    {{-- Contador --}}
    <div class="gallery-counter">
        <span id="currentSlide">1</span> / {{ $photoCount }}
    </div>

    {{-- Botón Expandir/Fullscreen --}}
    <button class="gallery-expand-btn" id="expandGallery" title="Pantalla completa">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
        </svg>
    </button>
</div>

{{-- MINIATURAS --}}
@if($photoCount > 1)
<div class="gallery-thumbnails" id="thumbnailScroll">
    @foreach($photos as $index => $photo)
    <div class="gallery-thumb {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
        <img
            src="{{ asset('storage/' . $photo->path) }}"
            alt="Miniatura {{ $index + 1 }}"
            loading="lazy"
        >
    </div>
    @endforeach
</div>
@endif

@endif

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox.umd.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox.css" />

<script>
// Esperar a que Swiper esté disponible en window
function initGallery() {
    if (typeof Swiper === 'undefined') {
        console.log('Esperando Swiper...');
        setTimeout(initGallery, 100);
        return;
    }

    const swiperEl = document.getElementById('propertyGallerySwiper');
    if (!swiperEl) return;

    console.log('✅ Inicializando galería...');

    // Crear instancia de Swiper
    const swiper = new Swiper('#propertyGallerySwiper', {
        loop: true,
        speed: 800,
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        autoplay: {
            delay: 5000,
            disableOnInteraction: false
        },
        navigation: {
            nextEl: '.gallery-nav-btn.next',
            prevEl: '.gallery-nav-btn.prev'
        },
        keyboard: {
            enabled: true,
            onlyInViewport: true
        },
        on: {
            slideChange: () => {
                updateUI();
            }
        }
    });

    // Actualizar UI
    function updateUI() {
        const idx = swiper.realIndex;
        const current = document.getElementById('currentSlide');
        if (current) current.textContent = idx + 1;

        // Miniaturas
        document.querySelectorAll('.gallery-thumb').forEach((el, i) => {
            el.classList.toggle('active', i === idx);
        });
    }

    // Click en miniaturas
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', () => {
            const idx = parseInt(thumb.dataset.slide, 10);
            swiper.slideToLoop(idx);
        });
    });

    // Botón expandir
    const expandBtn = document.getElementById('expandGallery');
    if (expandBtn) {
        expandBtn.addEventListener('click', () => {
            const img = document.querySelector('[data-fancybox="gallery"]');
            if (img) img.click();
        });
    }

    // Fancybox
    if (typeof Fancybox !== 'undefined') {
        Fancybox.bind('[data-fancybox="gallery"]', {
            on: {
                reveal: () => document.body.style.overflow = 'hidden',
                done: () => document.body.style.overflow = ''
            }
        });
    }

    updateUI();
}

// Iniciar cuando DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGallery);
} else {
    initGallery();
}
</script>
