/**
 * Portal del Cliente — JS Bundle
 * miportal.homedelvalle.mx
 *
 * Livewire 4 ya incluye Alpine.js internamente.
 * Este bundle solo agrega utilidades del portal.
 */
import './bootstrap';

// Livewire 4 se carga vía @livewireScripts en el layout.
// No importar aquí para evitar duplicados.

// ── Portal utilities ─────────────────────────────────────────────

/**
 * Formatea moneda MXN para mostrar en el portal.
 */
window.formatMXN = (centavos) => {
    if (!centavos) return '—';
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        maximumFractionDigits: 0,
    }).format(centavos / 100);
};

/**
 * Copia texto al portapapeles y muestra feedback temporal.
 */
window.copyToClipboard = async (text, btnEl) => {
    try {
        await navigator.clipboard.writeText(text);
        if (btnEl) {
            const original = btnEl.textContent;
            btnEl.textContent = '¡Copiado!';
            setTimeout(() => { btnEl.textContent = original; }, 2000);
        }
    } catch {
        // fallback silencioso
    }
};
