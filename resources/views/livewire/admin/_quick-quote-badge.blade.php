{{-- Badge de confianza reutilizable --}}
@php
    $confColor = match($s['confidence'] ?? '') {
        'high'   => '#16a34a',
        'medium' => '#d97706',
        default  => '#94a3b8',
    };
    $confLabel = match($s['confidence'] ?? '') {
        'high'   => 'Alta confianza',
        'medium' => 'Confianza media',
        default  => 'Confianza baja',
    };
@endphp
<div style="display:flex;align-items:center;gap:.3rem;font-size:.68rem;color:#6b7280;">
    <span style="width:6px;height:6px;border-radius:50%;background:{{ $confColor }};display:inline-block;flex-shrink:0;"></span>
    {{ $confLabel }}
    @if(($s['samples'] ?? 0) > 0)
    · {{ $s['samples'] }} anuncios
    @endif
</div>
