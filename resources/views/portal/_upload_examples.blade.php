{{-- Ejemplos visuales "así sí / así no" (SVG en línea, sin imágenes externas). Se muestran dentro de la leyenda de cada documento. --}}
<div style="display:flex;gap:.5rem;margin:.55rem 0 .15rem;flex-wrap:wrap;">
    {{-- Así sí: hoja plana, completa, con buena luz --}}
    <figure style="margin:0;text-align:center;width:88px;">
        <svg viewBox="0 0 88 88" width="88" height="88" role="img" aria-label="Documento plano y completo">
            <rect width="88" height="88" rx="8" fill="#f1f5f9"/>
            <rect x="18" y="8" width="52" height="72" rx="3" fill="#fff" stroke="#94a3b8"/>
            <rect x="24" y="16" width="30" height="4" fill="#334155"/><rect x="24" y="26" width="40" height="3" fill="#94a3b8"/>
            <rect x="24" y="33" width="40" height="3" fill="#94a3b8"/><rect x="24" y="40" width="34" height="3" fill="#94a3b8"/>
            <rect x="24" y="52" width="40" height="3" fill="#94a3b8"/><rect x="24" y="59" width="28" height="3" fill="#94a3b8"/>
            <circle cx="70" cy="18" r="11" fill="#16a34a"/><path d="M64 18l4 4 8-8" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <figcaption style="font-size:.66rem;color:#166534;line-height:1.25;font-weight:600;">Así sí: plano, completo y con buena luz</figcaption>
    </figure>
    {{-- Así no: foto a la pantalla del celular --}}
    <figure style="margin:0;text-align:center;width:88px;">
        <svg viewBox="0 0 88 88" width="88" height="88" role="img" aria-label="Foto tomada a la pantalla de un teléfono">
            <rect width="88" height="88" rx="8" fill="#f1f5f9"/>
            <rect x="26" y="6" width="36" height="72" rx="6" fill="#1e293b"/>
            <rect x="29" y="14" width="30" height="54" rx="2" fill="#e2e8f0"/>
            <rect x="32" y="20" width="16" height="3" fill="#64748b"/><rect x="32" y="27" width="24" height="2" fill="#94a3b8"/><rect x="32" y="33" width="20" height="2" fill="#94a3b8"/>
            <circle cx="44" cy="10" r="1.5" fill="#475569"/>
            <circle cx="70" cy="18" r="11" fill="#dc2626"/><path d="M65 13l10 10M75 13L65 23" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <figcaption style="font-size:.66rem;color:#991b1b;line-height:1.25;font-weight:600;">No: foto a la pantalla del celular</figcaption>
    </figure>
    {{-- Así no: cortado / incompleto --}}
    <figure style="margin:0;text-align:center;width:88px;">
        <svg viewBox="0 0 88 88" width="88" height="88" role="img" aria-label="Documento cortado o incompleto">
            <rect width="88" height="88" rx="8" fill="#f1f5f9"/>
            <rect x="-10" y="-14" width="66" height="70" rx="3" fill="#fff" stroke="#94a3b8"/>
            <rect x="4" y="6" width="34" height="4" fill="#334155"/><rect x="4" y="16" width="44" height="3" fill="#94a3b8"/>
            <rect x="4" y="23" width="44" height="3" fill="#94a3b8"/><rect x="4" y="30" width="38" height="3" fill="#94a3b8"/>
            <path d="M0 62l30-6 40 8" stroke="#cbd5e1" stroke-width="10" opacity=".5"/>
            <circle cx="70" cy="18" r="11" fill="#dc2626"/><path d="M65 13l10 10M75 13L65 23" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <figcaption style="font-size:.66rem;color:#991b1b;line-height:1.25;font-weight:600;">No: cortado, con sombras o dedos</figcaption>
    </figure>
</div>
