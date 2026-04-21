/**
 * Gallery Premium Module
 * Inicialización de Swiper + Fancybox para galerías de propiedades
 */

import Swiper from 'swiper';
import { Navigation, Keyboard, Mousewheel, Autoplay, EffectFade } from 'swiper/modules';
import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox.css';

// Registrar módulos de Swiper
Swiper.use([Navigation, Keyboard, Mousewheel, Autoplay, EffectFade]);

/**
 * Inicializa la galería premium cuando el DOM está listo
 */
export function initPropertyGallery() {
    const swiperContainer = document.getElementById('propertyGallerySwiper');
    if (!swiperContainer) return; // No hay galería en esta página

    console.log('🖼️ Inicializando galería premium...');

    // ========== SWIPER ==========
    const swiper = new Swiper('#propertyGallerySwiper', {
        modules: [Navigation, Keyboard, Mousewheel, Autoplay, EffectFade],
        loop: true,
        speed: 800,
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        autoplay: {
            delay: 5000,
            disableOnInteraction: true
        },
        navigation: {
            nextEl: '.gallery-nav-btn.next',
            prevEl: '.gallery-nav-btn.prev'
        },
        keyboard: {
            enabled: true,
            onlyInViewport: false
        },
        touchEventsTarget: 'container',
        on: {
            slideChange: () => updateGalleryUI(swiper)
        }
    });

    // ========== FANCYBOX ==========
    // El lightbox se renderiza en el body, evitando problemas de stacking context
    Fancybox.bind('[data-fancybox="gallery"]', {
        on: {
            reveal: (fancybox, slide) => {
                // Body bloqueado mientras el lightbox está abierto
                document.body.style.overflow = 'hidden';
            },
            done: (fancybox) => {
                // Restaurar scroll cuando se cierra
                document.body.style.overflow = '';
            }
        },
        // Opciones de presentación
        autoSize: true,
        width: 1400,
        height: 900,
        placeFocusBack: true,
        trapFocus: true
    });

    // ========== UI: CONTADOR Y MINIATURAS ==========
    function updateGalleryUI(swiper) {
        const currentIndex = swiper.realIndex;
        const currentSlideEl = document.getElementById('currentSlide');

        if (currentSlideEl) {
            currentSlideEl.textContent = currentIndex + 1;
        }

        // Sincronizar miniaturas con fade suave
        document.querySelectorAll('.gallery-thumb').forEach((thumb, idx) => {
            const isActive = idx === currentIndex;
            thumb.classList.toggle('active', isActive);

            if (isActive) {
                // Auto-scroll de miniaturas
                setTimeout(() => {
                    thumb.scrollIntoView({
                        behavior: 'smooth',
                        inline: 'center',
                        block: 'nearest'
                    });
                }, 100);
            }
        });
    }

    // ========== MINIATURAS: Navegación por click ==========
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', (e) => {
            const slideIndex = parseInt(e.currentTarget.dataset.slide, 10);
            swiper.slideToLoop(slideIndex);
        });

        // Efecto hover táctil en mobile
        thumb.addEventListener('touchstart', () => {
            thumb.style.opacity = '0.85';
        });

        thumb.addEventListener('touchend', () => {
            thumb.style.opacity = '';
        });
    });

    // ========== BOTÓN EXPANDIR ==========
    const expandBtn = document.getElementById('expandGallery');
    if (expandBtn) {
        expandBtn.addEventListener('click', () => {
            const firstImage = document.querySelector('[data-fancybox="gallery"]');
            if (firstImage) {
                // Simular click en la primera imagen para abrir Fancybox
                firstImage.click();
            }
        });
    }

    // ========== KEYBOARD NAVIGATION ==========
    document.addEventListener('keydown', (e) => {
        if (!document.querySelector('.fancybox__container')) {
            // Solo navegar si el lightbox NO está abierto
            if (e.key === 'ArrowLeft') {
                swiper.slidePrev();
            } else if (e.key === 'ArrowRight') {
                swiper.slideNext();
            }
        }
    });

    // Actualizar UI inicial
    updateGalleryUI(swiper);

    console.log('✅ Galería premium inicializada');
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPropertyGallery);
} else {
    initPropertyGallery();
}
